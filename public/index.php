<?php
require __DIR__ . '/../src/bootstrap.php';

$stats  = TripRepository::stats();
$trips  = TripRepository::upcoming(6);
$routes = TripRepository::popularRoutes(5);

$pageTitle = 'Попутчики — главная';
require __DIR__ . '/../templates/header.php';
?>
<h1>Поиск попутчиков</h1>
<p>Находите поездки между городами или предлагайте свою.</p>

<div class="stats">
    <div><b><?= (int)$stats['trips'] ?></b>актуальных поездок</div>
    <div><b><?= (int)$stats['users'] ?></b>пользователей</div>
    <div><b><?= (int)$stats['bookings'] ?></b>бронирований</div>
</div>

<h2>Ближайшие поездки</h2>
<?php require __DIR__ . '/../templates/trip_table.php'; ?>

<h2>Популярные маршруты</h2>
<?php if (!$routes): ?>
    <p>Пока нет данных.</p>
<?php else: ?>
    <ul>
    <?php foreach ($routes as $r): ?>
        <li>
            <a href="/search.php?from=<?= (int)$r['from_city_id'] ?>&to=<?= (int)$r['to_city_id'] ?>">
                <?= e($r['from_city']) ?> → <?= e($r['to_city']) ?>
            </a> (поездок: <?= (int)$r['cnt'] ?>)
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php require __DIR__ . '/../templates/footer.php'; ?>
