<?php

/**
 * Работа с поездками: выборки, поиск, создание, изменение, удаление и проверка данных формы.
 */
class TripRepository
{
    /** Базовый запрос: поездка + названия городов + водитель + число свободных мест. */
    private const BASE = 'SELECT t.*, cf.name AS from_city, ct.name AS to_city, u.name AS driver,
            (t.seats - (SELECT COUNT(*) FROM bookings b WHERE b.trip_id = t.id)) AS free_seats
        FROM trips t
        JOIN cities cf ON cf.id = t.from_city_id
        JOIN cities ct ON ct.id = t.to_city_id
        JOIN users u ON u.id = t.user_id';

    /**
     * Возвращает ближайшие поездки.
     *
     * @param int $limit максимальное число поездок
     * @return array[] список поездок
     */
    public static function upcoming(int $limit): array
    {
        $sql = self::BASE . ' WHERE t.trip_date >= CURDATE() ORDER BY t.trip_date, t.trip_time LIMIT ' . (int)$limit;
        return Database::getConnection()->query($sql)->fetchAll();
    }

    /**
     * Ищет актуальные поездки по заданным критериям.
     *
     * @param int|null    $from     id города отправления или null (любой)
     * @param int|null    $to       id города назначения или null (любой)
     * @param string|null $date     дата Y-m-d или null (любая)
     * @param bool        $onlyFree true — только поездки со свободными местами
     * @return array[] список найденных поездок
     */
    public static function search(?int $from, ?int $to, ?string $date, bool $onlyFree): array
    {
        $sql = self::BASE . ' WHERE t.trip_date >= CURDATE()';
        $params = [];
        if ($from) {
            $sql .= ' AND t.from_city_id = ?';
            $params[] = $from;
        }
        if ($to) {
            $sql .= ' AND t.to_city_id = ?';
            $params[] = $to;
        }
        if ($date) {
            $sql .= ' AND t.trip_date = ?';
            $params[] = $date;
        }
        if ($onlyFree) {
            $sql .= ' AND (t.seats - (SELECT COUNT(*) FROM bookings b WHERE b.trip_id = t.id)) > 0';
        }
        $sql .= ' ORDER BY t.trip_date, t.trip_time';

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Находит поездку по id.
     *
     * @param int $id id поездки
     * @return array|null данные поездки или null, если не найдена
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare(self::BASE . ' WHERE t.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Возвращает поездки, созданные пользователем.
     *
     * @param int $userId id пользователя
     * @return array[] список поездок
     */
    public static function byUser(int $userId): array
    {
        $stmt = Database::getConnection()->prepare(self::BASE . ' WHERE t.user_id = ? ORDER BY t.trip_date DESC, t.trip_time');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Возвращает все поездки (для админ-зоны).
     *
     * @return array[] список поездок
     */
    public static function all(): array
    {
        return Database::getConnection()->query(self::BASE . ' ORDER BY t.trip_date DESC, t.trip_time')->fetchAll();
    }

    /**
     * Создаёт поездку.
     *
     * @param int   $userId id водителя
     * @param array $data   проверенные данные из validate()
     * @return int id созданной поездки
     */
    public static function create(int $userId, array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO trips (user_id, from_city_id, to_city_id, trip_date, trip_time, seats, price, luggage, contact, comment)
             VALUES (:user_id, :from_city_id, :to_city_id, :trip_date, :trip_time, :seats, :price, :luggage, :contact, :comment)'
        );
        $stmt->execute($data + ['user_id' => $userId]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Обновляет поездку.
     *
     * @param int   $id   id поездки
     * @param array $data проверенные данные из validate()
     * @return void
     */
    public static function update(int $id, array $data): void
    {
        $stmt = Database::getConnection()->prepare(
            'UPDATE trips SET from_city_id = :from_city_id, to_city_id = :to_city_id, trip_date = :trip_date,
                trip_time = :trip_time, seats = :seats, price = :price, luggage = :luggage,
                contact = :contact, comment = :comment WHERE id = :id'
        );
        $stmt->execute($data + ['id' => $id]);
    }

    /**
     * Удаляет поездку (вместе с бронями).
     *
     * @param int      $id     id поездки
     * @param int|null $userId если указан — удалить только поездку этого владельца
     * @return void
     */
    public static function delete(int $id, ?int $userId = null): void
    {
        $sql = 'DELETE FROM trips WHERE id = ?';
        $params = [$id];
        if ($userId !== null) {
            $sql .= ' AND user_id = ?';
            $params[] = $userId;
        }
        Database::getConnection()->prepare($sql)->execute($params);
    }

    /**
     * Самые популярные маршруты (для главной страницы).
     *
     * @param int $limit сколько маршрутов вернуть
     * @return array[] маршруты с количеством поездок (cnt)
     */
    public static function popularRoutes(int $limit): array
    {
        $sql = 'SELECT t.from_city_id, t.to_city_id, cf.name AS from_city, ct.name AS to_city, COUNT(*) AS cnt
                FROM trips t
                JOIN cities cf ON cf.id = t.from_city_id
                JOIN cities ct ON ct.id = t.to_city_id
                GROUP BY t.from_city_id, t.to_city_id, cf.name, ct.name
                ORDER BY cnt DESC LIMIT ' . (int)$limit;
        return Database::getConnection()->query($sql)->fetchAll();
    }

    /**
     * Общая статистика сайта.
     *
     * @return array ключи: trips (актуальные поездки), users, bookings
     */
    public static function stats(): array
    {
        return Database::getConnection()->query(
            'SELECT (SELECT COUNT(*) FROM trips WHERE trip_date >= CURDATE()) AS trips,
                    (SELECT COUNT(*) FROM users) AS users,
                    (SELECT COUNT(*) FROM bookings) AS bookings'
        )->fetch();
    }

    /**
     * Проверяет данные формы поездки на стороне сервера.
     *
     * @param array $in сырые данные из $_POST
     * @return array ['errors' => string[], 'data' => array] — ошибки и очищенные данные
     */
    public static function validate(array $in): array
    {
        $errors = [];
        $cityIds = array_map('intval', array_column(CityRepository::all(), 'id'));

        $from    = (int)($in['from_city_id'] ?? 0);
        $to      = (int)($in['to_city_id'] ?? 0);
        $date    = trim($in['trip_date'] ?? '');
        $time    = trim($in['trip_time'] ?? '');
        $seats   = filter_var($in['seats'] ?? '', FILTER_VALIDATE_INT);
        $price   = $in['price'] ?? '';
        $luggage = $in['luggage'] ?? '';
        $contact = trim($in['contact'] ?? '');
        $comment = trim($in['comment'] ?? '');

        if (!in_array($from, $cityIds, true)) {
            $errors[] = 'Выберите город отправления.';
        }
        if (!in_array($to, $cityIds, true)) {
            $errors[] = 'Выберите город назначения.';
        }
        if ($from === $to && $from !== 0) {
            $errors[] = 'Города отправления и назначения должны отличаться.';
        }

        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            $errors[] = 'Укажите корректную дату.';
        } elseif ($date < date('Y-m-d')) {
            $errors[] = 'Дата поездки не может быть в прошлом.';
        }

        $t = DateTime::createFromFormat('H:i', $time);
        if (!$t || $t->format('H:i') !== $time) {
            $errors[] = 'Укажите корректное время.';
        }

        if ($seats === false || $seats < 1 || $seats > 8) {
            $errors[] = 'Число мест: от 1 до 8.';
        }
        if (!is_numeric($price) || $price < 0 || $price > 10000) {
            $errors[] = 'Цена: число от 0 до 10000.';
        }
        if ($luggage !== '0' && $luggage !== '1') {
            $errors[] = 'Укажите, можно ли брать багаж.';
        }
        if (mb_strlen($contact) < 3 || mb_strlen($contact) > 100) {
            $errors[] = 'Контакт: от 3 до 100 символов.';
        }
        if (mb_strlen($comment) > 500) {
            $errors[] = 'Комментарий: не более 500 символов.';
        }

        return [
            'errors' => $errors,
            'data'   => [
                'from_city_id' => $from,
                'to_city_id'   => $to,
                'trip_date'    => $date,
                'trip_time'    => $time,
                'seats'        => (int)$seats,
                'price'        => is_numeric($price) ? round((float)$price, 2) : 0,
                'luggage'      => (int)$luggage,
                'contact'      => $contact,
                'comment'      => $comment,
            ],
        ];
    }
}
