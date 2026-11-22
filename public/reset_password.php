<?php
require __DIR__ . '/../src/bootstrap.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = Auth::resetPassword($token, $_POST['password'] ?? '');
    if (!$errors) {
        header('Location: /login.php?reset=1');
        exit;
    }
}

$valid = Auth::isResetTokenValid($token);

$pageTitle = 'Новый пароль';
require __DIR__ . '/../templates/header.php';
?>
<h1>Новый пароль</h1>
<?php foreach ($errors as $error): ?><div class="error"><?= e($error) ?></div><?php endforeach; ?>
<?php if (!$valid && !$errors): ?>
    <div class="error">Ссылка недействительна или устарела. <a href="/forgot_password.php">Запросить новую</a></div>
<?php elseif ($valid): ?>
    <form method="post">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <p><label>Новый пароль (минимум 8 символов)</label><input type="password" name="password" required minlength="8"></p>
        <p><button type="submit">Сохранить пароль</button></p>
    </form>
<?php endif; ?>
<?php require __DIR__ . '/../templates/footer.php'; ?>
