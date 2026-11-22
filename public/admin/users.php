<?php
require __DIR__ . '/../../src/bootstrap.php';
Auth::requireAdmin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id === Auth::userId()) {
        $error = 'Нельзя изменить или удалить собственную учётную запись.';
    } else {
        if (($_POST['action'] ?? '') === 'delete') {
            UserRepository::delete($id);
        } elseif (($_POST['action'] ?? '') === 'set_role') {
            UserRepository::setRole($id, $_POST['role'] ?? '');
        }
        header('Location: /admin/users.php');
        exit;
    }
}

$users = UserRepository::all();
$pageTitle = 'Пользователи';
require __DIR__ . '/../../templates/header.php';
?>
<h1>Пользователи</h1>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<table>
    <tr><th>ID</th><th>Имя</th><th>Email</th><th>Роль</th><th></th><th></th></tr>
    <?php foreach ($users as $u): ?>
    <tr>
        <td><?= (int)$u['id'] ?></td>
        <td><?= e($u['name']) ?></td>
        <td><?= e($u['email']) ?></td>
        <td><?= e($u['role']) ?></td>
        <td>
            <form method="post">
                <input type="hidden" name="action" value="set_role">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <input type="hidden" name="role" value="<?= $u['role'] === 'admin' ? 'user' : 'admin' ?>">
                <button type="submit"><?= $u['role'] === 'admin' ? 'Сделать user' : 'Сделать admin' ?></button>
            </form>
        </td>
        <td>
            <form method="post" onsubmit="return confirm('Удалить пользователя вместе с его поездками и бронями?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button class="danger" type="submit">Удалить</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<p><a href="/admin/index.php">← В админ-панель</a></p>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
