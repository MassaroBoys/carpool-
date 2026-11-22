<?php
require __DIR__ . '/../src/bootstrap.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    if (Auth::login($email, $_POST['password'] ?? '')) {
        header('Location: /my.php');
        exit;
    }
    $error = 'Неверный email или пароль.';
}

$pageTitle = 'Вход';
require __DIR__ . '/../templates/header.php';
?>
<h1>Вход</h1>
<?php if (isset($_GET['registered'])): ?>
    <div class="ok">Регистрация прошла успешно. Теперь войдите.</div>
<?php endif; ?>
<?php if (isset($_GET['reset'])): ?>
    <div class="ok">Пароль изменён. Теперь войдите.</div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="error"><?= e($error) ?></div>
<?php endif; ?>
<form method="post">
    <p><label>Email</label><input type="email" name="email" required value="<?= e($email) ?>"></p>
    <p><label>Пароль</label><input type="password" name="password" required></p>
    <p><button type="submit">Войти</button></p>
</form>
<p><a href="/forgot_password.php">Забыли пароль?</a></p>
<?php require __DIR__ . '/../templates/footer.php'; ?>
