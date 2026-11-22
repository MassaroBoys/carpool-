<?php /** @var string $pageTitle */ ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Попутчики') ?></title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<header>
    <div>
        <a class="logo" href="/index.php">Попутчики</a>
        <a href="/search.php">Поиск</a>
        <?php if (Auth::isLoggedIn()): ?>
            <a href="/trip_create.php">Создать поездку</a>
            <a href="/my.php">Мой кабинет</a>
            <?php if (Auth::isAdmin()): ?><a href="/admin/index.php">Админ-панель</a><?php endif; ?>
        <?php endif; ?>
    </div>
    <div>
        <?php if (Auth::isLoggedIn()): ?>
            <span style="color:#fff"><?= e($_SESSION['user']['name']) ?></span>
            <a href="/logout.php" style="margin-left:12px">Выйти</a>
        <?php else: ?>
            <a href="/login.php">Вход</a>
            <a href="/register.php">Регистрация</a>
        <?php endif; ?>
    </div>
</header>
<main>
