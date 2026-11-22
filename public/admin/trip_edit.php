<?php
require __DIR__ . '/../../src/bootstrap.php';
Auth::requireAdmin();

$id   = (int)($_GET['id'] ?? 0);
$trip = TripRepository::find($id);
if (!$trip) {
    http_response_code(404);
    echo 'Поездка не найдена.';
    exit;
}

$errors = [];
$values = $trip;
$values['luggage']   = (string)$trip['luggage'];
$values['trip_time'] = substr($trip['trip_time'], 0, 5);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = $_POST;
    $result = TripRepository::validate($_POST);
    $errors = $result['errors'];
    if (!$errors) {
        TripRepository::update($id, $result['data']);
        header('Location: /admin/trips.php');
        exit;
    }
}

$cities = CityRepository::all();
$submitLabel = 'Сохранить';
$pageTitle = 'Редактирование поездки';
require __DIR__ . '/../../templates/header.php';
?>
<h1>Редактирование поездки №<?= $id ?></h1>
<?php
require __DIR__ . '/../../templates/trip_form.php';
require __DIR__ . '/../../templates/footer.php';
