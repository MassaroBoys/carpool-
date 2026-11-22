<?php

/**
 * Регистрация, вход и проверка прав пользователей.
 */
class Auth
{
    /**
     * Регистрирует нового пользователя.
     *
     * @param string $name     имя пользователя
     * @param string $email    адрес электронной почты
     * @param string $password пароль в открытом виде
     * @param string $role     роль: user или admin
     * @return string[] список ошибок (пустой массив, если регистрация успешна)
     */
    public static function register(string $name, string $email, string $password, string $role = 'user'): array
    {
        $name  = trim($name);
        $email = trim($email);
        $errors = [];

        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $errors[] = 'Имя должно содержать от 2 до 100 символов.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $errors[] = 'Введите корректный email.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Пароль должен содержать минимум 8 символов.';
        }
        if ($errors) {
            return $errors;
        }

        $pdo = Database::getConnection();

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['Пользователь с таким email уже существует.'];
        }

        $role = $role === 'admin' ? 'admin' : 'user';
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);

        return [];
    }

    /**
     * Проверяет email и пароль и, если они верны, запоминает пользователя в сессии.
     *
     * @param string $email    адрес электронной почты
     * @param string $password пароль в открытом виде
     * @return bool true, если вход выполнен
     */
    public static function login(string $email, string $password): bool
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT id, name, password_hash, role FROM users WHERE email = ?'
        );
        $stmt->execute([trim($email)]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'   => (int)$user['id'],
            'name' => $user['name'],
            'role' => $user['role'],
        ];
        return true;
    }

    /**
     * Создаёт токен восстановления пароля и отправляет ссылку на email.
     * Результат не зависит от того, есть ли такой email в системе.
     *
     * @param string $email адрес электронной почты
     * @return void
     */
    public static function requestReset(string $email): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([trim($email)]);
        $user = $stmt->fetch();
        if (!$user) {
            return;
        }

        $token = bin2hex(random_bytes(32));

        $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$user['id']]);
        $pdo->prepare(
            'INSERT INTO password_resets (user_id, token_hash, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
        )->execute([$user['id'], hash('sha256', $token)]);

        $base = (require __DIR__ . '/../config.php')['mail']['base_url'];
        Mailer::send(
            trim($email),
            'Восстановление пароля',
            "Чтобы задать новый пароль, перейдите по ссылке (действует 1 час):\n"
            . $base . '/reset_password.php?token=' . $token
        );
    }

    /**
     * Проверяет, что токен восстановления существует и не истёк.
     *
     * @param string $token токен из ссылки
     * @return bool true, если токен действителен
     */
    public static function isResetTokenValid(string $token): bool
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT id FROM password_resets WHERE token_hash = ? AND expires_at > NOW()'
        );
        $stmt->execute([hash('sha256', $token)]);
        return (bool)$stmt->fetch();
    }

    /**
     * Устанавливает новый пароль по токену восстановления.
     *
     * @param string $token    токен из ссылки
     * @param string $password новый пароль в открытом виде
     * @return string[] список ошибок (пустой массив, если пароль изменён)
     */
    public static function resetPassword(string $token, string $password): array
    {
        if (strlen($password) < 8) {
            return ['Пароль должен содержать минимум 8 символов.'];
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT user_id FROM password_resets WHERE token_hash = ? AND expires_at > NOW()'
        );
        $stmt->execute([hash('sha256', $token)]);
        $row = $stmt->fetch();
        if (!$row) {
            return ['Ссылка недействительна или устарела.'];
        }

        $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $row['user_id']]);
        $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$row['user_id']]);

        return [];
    }

    /**
     * Проверяет, выполнил ли пользователь вход.
     *
     * @return bool true, если пользователь авторизован
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    /**
     * Проверяет, является ли текущий пользователь администратором.
     *
     * @return bool true, если у пользователя роль admin
     */
    public static function isAdmin(): bool
    {
        return self::isLoggedIn() && $_SESSION['user']['role'] === 'admin';
    }

    /**
     * Возвращает id текущего пользователя.
     *
     * @return int id пользователя или 0, если вход не выполнен
     */
    public static function userId(): int
    {
        return (int)($_SESSION['user']['id'] ?? 0);
    }

    /**
     * Завершает сессию пользователя.
     *
     * @return void
     */
    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Закрывает страницу от неавторизованных: перенаправляет на страницу входа.
     *
     * @return void
     */
    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }

    /**
     * Закрывает страницу от всех, кроме администраторов.
     *
     * @return void
     */
    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            echo 'Доступ запрещён.';
            exit;
        }
    }
}
