<?php

/**
 * Подключение к базе данных через PDO.
 * Соединение создаётся один раз и используется во всём приложении.
 */
class Database
{
    private static ?PDO $pdo = null;

    /**
     * Возвращает подключение к БД, создавая его при первом вызове.
     *
     * @return PDO подключение к базе данных
     */
    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $db = (require __DIR__ . '/../config.php')['db'];
            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";

            self::$pdo = new PDO($dsn, $db['user'], $db['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$pdo;
    }
}
