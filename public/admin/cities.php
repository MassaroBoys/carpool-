<?php
require __DIR__ . '/../../src/bootstrap.php';
Auth::requireAdmin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'add') {
        $error = CityRepository::add($_POST['name'] ?? '') ?? '';
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $error = CityRepository::delete((int)($_POST['id'] ?? 0)) ?? '';
    }
    if (!$error) {
        header('Location: /admin/cities.php');
        exit;
    }
}

$cities = CityRepository::all();
$pageTitle = 'Города';
require __DIR__ . '/../../templates/header.php';
?>
<h1>Города</h1>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<form method="post">
    <input type="hidden" name="action" value="add">
    <p><label>Новый город</label><input type="text" name="name" required minlength="2" maxlength="100"></p>
    <p><button type="submit">Добавить</button></p>
</form>
<table>
    <tr><th>ID</th><th>Название</th><th></th></tr>
    <?php foreach ($cities as $c): ?>
    <tr>
        <td><?= (int)$c['id'] ?></td>
        <td><?= e($c['name']) ?></td>
        <td>
            <form method="post" onsubmit="return confirm('Удалить город?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                <button class="danger" type="submit">Удалить</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<p><a href="/admin/index.php">← В админ-панель</a></p>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
