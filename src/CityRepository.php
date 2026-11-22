<?php

/**
 * Работа с таблицей городов.
 */
class CityRepository
{
    /**
     * Возвращает все города по алфавиту.
     *
     * @return array[] список городов (id, name)
     */
    public static function all(): array
    {
        return Database::getConnection()->query('SELECT id, name FROM cities ORDER BY name')->fetchAll();
    }

    /**
     * Добавляет город.
     *
     * @param string $name название города
     * @return string|null текст ошибки или null при успехе
     */
    public static function add(string $name): ?string
    {
        $name = trim($name);
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            return 'Название города: от 2 до 100 символов.';
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM cities WHERE name = ?');
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            return 'Такой город уже есть.';
        }
        $pdo->prepare('INSERT INTO cities (name) VALUES (?)')->execute([$name]);
        return null;
    }

    /**
     * Удаляет город, если он не используется в поездках.
     *
     * @param int $id id города
     * @return string|null текст ошибки или null при успехе
     */
    public static function delete(int $id): ?string
    {
        try {
            Database::getConnection()->prepare('DELETE FROM cities WHERE id = ?')->execute([$id]);
            return null;
        } catch (PDOException $e) {
            return 'Нельзя удалить город: он используется в поездках.';
        }
    }
}
