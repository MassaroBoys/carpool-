<?php
require __DIR__ . '/../../src/bootstrap.php';
Auth::requireAdmin();

$stats = TripRepository::stats();
$pageTitle = 'Админ-панель';
require __DIR__ . '/../../templates/header.php';
?>
<h1>Админ-панель</h1>
<div class="stats">
    <div><b><?= (int)$stats['trips'] ?></b>актуальных поездок</div>
    <div><b><?= (int)$stats['users'] ?></b>пользователей</div>
    <div><b><?= (int)$stats['bookings'] ?></b>бронирований</div>
</div>
<ul>
    <li><a href="/admin/trips.php">Управление поездками</a> (просмотр, редактирование, удаление)</li>
    <li><a href="/admin/users.php">Управление пользователями</a> (смена роли, удаление)</li>
    <li><a href="/admin/create_admin.php">Создать администратора</a></li>
    <li><a href="/admin/cities.php">Управление городами</a> (добавление, удаление)</li>
</ul>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
