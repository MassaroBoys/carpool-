<?php

/**
 * Работа с пользователями (для админ-зоны).
 */
class UserRepository
{
    /**
     * Возвращает всех пользователей.
     *
     * @return array[] список пользователей без хешей паролей
     */
    public static function all(): array
    {
        return Database::getConnection()
            ->query('SELECT id, name, email, role, created_at FROM users ORDER BY id')
            ->fetchAll();
    }

    /**
     * Удаляет пользователя вместе с его поездками и бронями.
     *
     * @param int $id id пользователя
     * @return void
     */
    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
    }

    /**
     * Меняет роль пользователя.
     *
     * @param int    $id   id пользователя
     * @param string $role новая роль: user или admin
     * @return void
     */
    public static function setRole(int $id, string $role): void
    {
        if (!in_array($role, ['user', 'admin'], true)) {
            return;
        }
        Database::getConnection()->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $id]);
    }
}
