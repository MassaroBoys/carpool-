<?php
require __DIR__ . '/../../src/bootstrap.php';
Auth::requireAdmin();

$errors = [];
$name = '';
$email = '';
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $errors = Auth::register($name, $email, $_POST['password'] ?? '', 'admin');
    if (!$errors) {
        $done = true;
        $name = $email = '';
    }
}

$pageTitle = 'Создать администратора';
require __DIR__ . '/../../templates/header.php';
?>
<h1>Создать администратора</h1>
<?php if ($done): ?><div class="ok">Администратор создан.</div><?php endif; ?>
<?php foreach ($errors as $error): ?><div class="error"><?= e($error) ?></div><?php endforeach; ?>
<form method="post">
    <p><label>Имя</label><input type="text" name="name" required minlength="2" maxlength="100" value="<?= e($name) ?>"></p>
    <p><label>Email</label><input type="email" name="email" required value="<?= e($email) ?>"></p>
    <p><label>Пароль (минимум 8 символов)</label><input type="password" name="password" required minlength="8"></p>
    <p><button type="submit">Создать</button></p>
</form>
<p><a href="/admin/index.php">← В админ-панель</a></p>
<?php require __DIR__ . '/../../templates/footer.php'; ?>
