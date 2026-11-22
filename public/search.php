<?php
require __DIR__ . '/../src/bootstrap.php';

$from = (int)($_GET['from'] ?? 0) ?: null;
$to   = (int)($_GET['to'] ?? 0) ?: null;
$date = trim($_GET['date'] ?? '');
$onlyFree = isset($_GET['free']);

$d = DateTime::createFromFormat('Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date) {
    $date = '';
}

$cities = CityRepository::all();
$trips  = TripRepository::search($from, $to, $date ?: null, $onlyFree);

$pageTitle = 'Поиск поездок';
require __DIR__ . '/../templates/header.php';
?>
<h1>Поиск поездок</h1>
<form method="get">
    <p>
        <label>Откуда</label>
        <select name="from">
            <option value="">Любой</option>
            <?php foreach ($cities as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= $from === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label>Куда</label>
        <select name="to">
            <option value="">Любой</option>
            <?php foreach ($cities as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= $to === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p><label>Дата</label><input type="date" name="date" value="<?= e($date) ?>"></p>
    <p><label style="font-weight:normal"><input type="checkbox" name="free" <?= $onlyFree ? 'checked' : '' ?>> Только со свободными местами</label></p>
    <p><button type="submit">Найти</button></p>
</form>

<h2>Результаты: <?= count($trips) ?></h2>
<?php
require __DIR__ . '/../templates/trip_table.php';
require __DIR__ . '/../templates/footer.php';
