<?php /** @var array[] $trips список поездок */ ?>
<?php if (!$trips): ?>
    <p>Поездок не найдено.</p>
<?php else: ?>
<table>
    <tr><th>Маршрут</th><th>Дата</th><th>Время</th><th>Цена</th><th>Места</th><th></th></tr>
    <?php foreach ($trips as $t): ?>
    <tr>
        <td><?= e($t['from_city']) ?> → <?= e($t['to_city']) ?></td>
        <td><?= e($t['trip_date']) ?></td>
        <td><?= e(substr($t['trip_time'], 0, 5)) ?></td>
        <td><?= e($t['price']) ?> лей</td>
        <td><?= (int)$t['free_seats'] > 0 ? (int)$t['free_seats'] . ' из ' . (int)$t['seats'] : 'Мест нет' ?></td>
        <td><a href="/trip.php?id=<?= (int)$t['id'] ?>">Подробнее</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
