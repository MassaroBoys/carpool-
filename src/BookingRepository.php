<?php

/**
 * Работа с бронированием мест в поездках.
 */
class BookingRepository
{
    /**
     * Бронирует место в поездке для пользователя.
     * Проверки и вставка выполняются в транзакции, чтобы не превысить число мест.
     *
     * @param int $tripId id поездки
     * @param int $userId id пассажира
     * @return string|null текст ошибки или null при успехе
     */
    public static function book(int $tripId, int $userId): ?string
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT seats, user_id FROM trips WHERE id = ? FOR UPDATE');
            $stmt->execute([$tripId]);
            $trip = $stmt->fetch();

            if (!$trip) {
                $pdo->rollBack();
                return 'Поездка не найдена.';
            }
            if ((int)$trip['user_id'] === $userId) {
                $pdo->rollBack();
                return 'Нельзя бронировать место в собственной поездке.';
            }

            $stmt = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE trip_id = ?');
            $stmt->execute([$tripId]);
            if ((int)$stmt->fetchColumn() >= (int)$trip['seats']) {
                $pdo->rollBack();
                return 'Свободных мест нет.';
            }

            $stmt = $pdo->prepare('SELECT id FROM bookings WHERE trip_id = ? AND user_id = ?');
            $stmt->execute([$tripId, $userId]);
            if ($stmt->fetch()) {
                $pdo->rollBack();
                return 'Вы уже забронировали место в этой поездке.';
            }

            $pdo->prepare('INSERT INTO bookings (trip_id, user_id) VALUES (?, ?)')->execute([$tripId, $userId]);
            $pdo->commit();
            return null;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Отменяет бронь пользователя.
     *
     * @param int $tripId id поездки
     * @param int $userId id пассажира
     * @return void
     */
    public static function cancel(int $tripId, int $userId): void
    {
        Database::getConnection()
            ->prepare('DELETE FROM bookings WHERE trip_id = ? AND user_id = ?')
            ->execute([$tripId, $userId]);
    }

    /**
     * Проверяет, есть ли у пользователя бронь в поездке.
     *
     * @param int $tripId id поездки
     * @param int $userId id пользователя
     * @return bool true, если бронь есть
     */
    public static function hasBooking(int $tripId, int $userId): bool
    {
        $stmt = Database::getConnection()->prepare('SELECT 1 FROM bookings WHERE trip_id = ? AND user_id = ?');
        $stmt->execute([$tripId, $userId]);
        return (bool)$stmt->fetch();
    }

    /**
     * Возвращает брони пользователя вместе с данными поездок.
     *
     * @param int $userId id пользователя
     * @return array[] список броней
     */
    public static function byUser(int $userId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT b.trip_id, t.trip_date, t.trip_time, t.price, cf.name AS from_city, ct.name AS to_city
             FROM bookings b
             JOIN trips t ON t.id = b.trip_id
             JOIN cities cf ON cf.id = t.from_city_id
             JOIN cities ct ON ct.id = t.to_city_id
             WHERE b.user_id = ? ORDER BY t.trip_date, t.trip_time'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Возвращает пассажиров поездки.
     *
     * @param int $tripId id поездки
     * @return array[] список пассажиров (name, email)
     */
    public static function passengers(int $tripId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT u.name, u.email FROM bookings b JOIN users u ON u.id = b.user_id WHERE b.trip_id = ? ORDER BY b.id'
        );
        $stmt->execute([$tripId]);
        return $stmt->fetchAll();
    }
}
