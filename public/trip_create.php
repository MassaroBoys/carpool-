<?php
require __DIR__ . '/../src/bootstrap.php';
Auth::requireLogin();

$errors = [];
$values = ['luggage' => '0'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = $_POST;
    $result = TripRepository::validate($_POST);
    $errors = $result['errors'];
    if (!$errors) {
        $id = TripRepository::create(Auth::userId(), $result['data']);
        header('Location: /trip.php?id=' . $id . '&created=1');
        exit;
    }
}

$cities = CityRepository::all();
$submitLabel = 'Опубликовать поездку';
$pageTitle = 'Новая поездка';
require __DIR__ . '/../templates/header.php';
?>
<h1>Новая поездка</h1>
<?php
require __DIR__ . '/../templates/trip_form.php';
require __DIR__ . '/../templates/footer.php';
