<?php
require __DIR__ . '/../src/bootstrap.php';

$id   = (int)($_GET['id'] ?? 0);
$trip = TripRepository::find($id);
if (!$trip) {
    http_response_code(404);
    echo 'Поездка не найдена.';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireLogin();
    $action = $_POST['action'] ?? '';
    if ($action === 'book') {
        $error = BookingRepository::book($id, Auth::userId()) ?? '';
        if (!$error) {
            header('Location: /trip.php?id=' . $id . '&booked=1');
            exit;
        }
    } elseif ($action === 'cancel') {
        BookingRepository::cancel($id, Auth::userId());
        header('Location: /trip.php?id=' . $id . '&cancelled=1');
        exit;
    }
}

$loggedIn   = Auth::isLoggedIn();
$isOwner    = $loggedIn && Auth::userId() === (int)$trip['user_id'];
$hasBooking = $loggedIn && BookingRepository::hasBooking($id, Auth::userId());
$passengers = $isOwner ? BookingRepository::passengers($id) : [];
$free       = (int)$trip['free_seats'];

$pageTitle = $trip['from_city'] . ' → ' . $trip['to_city'];
require __DIR__ . '/../templates/header.php';
?>
<h1><?= e($trip['from_city']) ?> → <?= e($trip['to_city']) ?></h1>

<?php if (isset($_GET['created'])): ?><div class="ok">Поездка опубликована.</div><?php endif; ?>
<?php if (isset($_GET['booked'])): ?><div class="ok">Место забронировано.</div><?php endif; ?>
<?php if (isset($_GET['cancelled'])): ?><div class="ok">Бронь отменена.</div><?php endif; ?>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>

<table>
    <tr><th>Дата</th><td><?= e($trip['trip_date']) ?></td></tr>
    <tr><th>Время</th><td><?= e(substr($trip['trip_time'], 0, 5)) ?></td></tr>
    <tr><th>Цена</th><td><?= e($trip['price']) ?> лей</td></tr>
    <tr><th>Багаж</th><td><?= $trip['luggage'] ? 'Можно' : 'Нельзя' ?></td></tr>
    <tr><th>Водитель</th><td><?= e($trip['driver']) ?></td></tr>
    <tr><th>Контакт</th><td><?= $loggedIn ? e($trip['contact']) : 'Войдите, чтобы увидеть контакт' ?></td></tr>
    <tr><th>Свободных мест</th><td><?= $free > 0 ? $free . ' из ' . (int)$trip['seats'] : 'Мест нет' ?></td></tr>
    <tr><th>Комментарий</th><td><?= nl2br(e($trip['comment'])) ?></td></tr>
</table>

<p>
<?php if (!$loggedIn): ?>
    <a class="btn" href="/login.php">Войдите, чтобы забронировать</a>
<?php elseif ($isOwner): ?>
    Это ваша поездка.
<?php elseif ($hasBooking): ?>
    <form method="post"><input type="hidden" name="action" value="cancel"><button class="danger" type="submit">Отменить бронь</button></form>
<?php elseif ($free <= 0): ?>
    Все места заняты.
<?php else: ?>
    <form method="post"><input type="hidden" name="action" value="book"><button type="submit">Забронировать место</button></form>
<?php endif; ?>
</p>

<?php if ($isOwner): ?>
    <h2>Пассажиры</h2>
    <?php if (!$passengers): ?>
        <p>Пока никто не забронировал.</p>
    <?php else: ?>
        <ul><?php foreach ($passengers as $p): ?><li><?= e($p['name']) ?> (<?= e($p['email']) ?>)</li><?php endforeach; ?></ul>
    <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/../templates/footer.php'; ?>
