<?php
require __DIR__ . '/../../src/bootstrap.php';
Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    TripRepository::delete((int)($_POST['id'] ?? 0));
    header('Location: /admin/trips.php');
    exit;
}

$trips = TripRepository::all();
$pageTitle = 'Поездки';
require __DIR__ . '/../../templates/header.php';
?>
<h1>Все поездки</h1>
<table>
    <tr><th>ID</th><th>Маршрут</th><th>Дата</th><th>Водитель</th><th>Места</th><th></th><th></th></tr>
    <?php foreach ($trips as $t): ?>
    <tr>
        <td><?= (int)$t['id'] ?></td>
        <td><?= e($t['from_city']) ?> → <?= e($t['to_city']) ?></td>
        <td><?= e($t['trip_date']) ?> <?= e(substr($t['trip_time'], 0, 5)) ?></td>
        <td><?= e($t['driver']) ?></td>
        <td><?= (int)$t['free_seats'] ?>/<?= (int)$t['seats'] ?></td>
        <td><a href="/admin/trip_edit.php?id=<?= (int)$t['id'] ?>">Изменить</a></td>
        <td>
            <form method="post" onsubmit="return confirm('Удалить поездку?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                <button class="danger" type="submit">Удалить</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<p><a href="/admin/index.php">← В админ-панель</a></p>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
