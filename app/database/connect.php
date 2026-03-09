<?php

$driver = 'mysql';
$host = 'localhost';
$is_local = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', 'localhost:8000', '127.0.0.1']);

if ($is_local) {
    $db_name = 'dinamic-site';
    $db_user = 'root';
    $db_pass = '';
}
else {
    $db_name = 'u3414051_default';
    $db_user = 'u3414051_default';
    $db_pass = 'wJ7u8AHl03szaTZ1'; // Updated from screenshot
}
$charset = 'utf8mb4';
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];

try {
    $pdo = new PDO(
        "$driver:host=$host;dbname=$db_name;charset=$charset", $db_user, $db_pass, $options
        );
}
catch (PDOException $i) {
    die("Ошибка подключения к базе данных");
}