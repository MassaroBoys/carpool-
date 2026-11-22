<?php
return [
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'carpool',
        'user'     => 'root',
        'password' => 'your_password',
        'charset'  => 'utf8mb4',
    ],
    'mail' => [
        'host'     => '127.0.0.1',
        'port'     => 1025,
        'from'     => 'no-reply@carpool.local',
        'base_url' => 'http://localhost:8000',
    ],
];
