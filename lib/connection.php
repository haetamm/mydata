<?php

declare(strict_types=1);

$host   = 'localhost';
$db     = 'dataku';
$user   = 'root';
$pass   = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try
{
    $pdo = new PDO($dsn, $user, $pass, $options);
}
catch (\PDOException $e)
{
    error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
    http_response_code(500);
    die('Koneksi database gagal.');
}
