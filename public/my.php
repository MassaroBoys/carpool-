<?php
require __DIR__ . '/../src/bootstrap.php';
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'delete_trip') {
        TripRepository::delete($id, Auth::userId());
    } elseif ($action === 'cancel_booking') {
        BookingRepository::cancel($id, Auth::userId());
    }
    header('Location: /my.php');
    exit;
}

$trips    = TripRepository::byUser(Auth::userId());
$bookings = BookingRepository::byUser(Auth::userId());

$pageTitle = 'Мой кабинет';
require __DIR__ . '/../templates/header.php';
?>
<h1>Привет, <?= e($_SESSION['user']['name']) ?>!</h1>

<h2>Мои поездки</h2>
<?php if (!$trips): ?>
    <p>Вы ещё не создавали поездок. <a href="/trip_create.php">Создать</a></p>
<?php else: ?>
<table>
    <tr><th>Маршрут</th><th>Дата</th><th>Места</th><th></th><th></th></tr>
    <?php foreach ($trips as $t): ?>
    <tr>
        <td><?= e($t['from_city']) ?> → <?= e($t['to_city']) ?></td>
        <td><?= e($t['trip_date']) ?> <?= e(substr($t['trip_time'], 0, 5)) ?></td>
        <td>свободно <?= (int)$t['free_seats'] ?> из <?= (int)$t['seats'] ?></td>
        <td><a href="/trip.php?id=<?= (int)$t['id'] ?>">Открыть</a></td>
        <td>
            <form method="post" onsubmit="return confirm('Удалить поездку?')">
                <input type="hidden" name="action" value="delete_trip">
                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                <button class="danger" type="submit">Удалить</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<h2>Мои брони</h2>
<?php if (!$bookings): ?>
    <p>У вас нет забронированных мест. <a href="/search.php">Найти поездку</a></p>
<?php else: ?>
<table>
    <tr><th>Маршрут</th><th>Дата</th><th>Цена</th><th></th><th></th></tr>
    <?php foreach ($bookings as $b): ?>
    <tr>
        <td><?= e($b['from_city']) ?> → <?= e($b['to_city']) ?></td>
        <td><?= e($b['trip_date']) ?> <?= e(substr($b['trip_time'], 0, 5)) ?></td>
        <td><?= e($b['price']) ?> лей</td>
        <td><a href="/trip.php?id=<?= (int)$b['trip_id'] ?>">Открыть</a></td>
        <td>
            <form method="post" onsubmit="return confirm('Отменить бронь?')">
                <input type="hidden" name="action" value="cancel_booking">
                <input type="hidden" name="id" value="<?= (int)$b['trip_id'] ?>">
                <button class="danger" type="submit">Отменить</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../templates/footer.php'; ?>
