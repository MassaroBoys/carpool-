<?php
require __DIR__ . '/../src/bootstrap.php';

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requestReset($_POST['email'] ?? '');
    $sent = true;
}

$pageTitle = 'Восстановление пароля';
require __DIR__ . '/../templates/header.php';
?>
<h1>Восстановление пароля</h1>
<?php if ($sent): ?>
    <div class="ok">Если такой email зарегистрирован, мы отправили на него письмо со ссылкой.</div>
<?php endif; ?>
<form method="post">
    <p><label>Email</label><input type="email" name="email" required></p>
    <p><button type="submit">Отправить ссылку</button></p>
</form>
<?php require __DIR__ . '/../templates/footer.php'; ?>
