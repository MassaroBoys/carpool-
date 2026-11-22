<?php
require __DIR__ . '/../src/bootstrap.php';

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $errors = Auth::register($name, $email, $_POST['password'] ?? '');
    if (!$errors) {
        header('Location: /login.php?registered=1');
        exit;
    }
}

$pageTitle = 'Регистрация';
require __DIR__ . '/../templates/header.php';
?>
<h1>Регистрация</h1>
<?php foreach ($errors as $error): ?>
    <div class="error"><?= e($error) ?></div>
<?php endforeach; ?>
<form method="post">
    <p><label>Имя</label><input type="text" name="name" required minlength="2" maxlength="100" value="<?= e($name) ?>"></p>
    <p><label>Email</label><input type="email" name="email" required value="<?= e($email) ?>"></p>
    <p><label>Пароль (минимум 8 символов)</label><input type="password" name="password" required minlength="8"></p>
    <p><button type="submit">Зарегистрироваться</button></p>
</form>
<?php require __DIR__ . '/../templates/footer.php'; ?>
