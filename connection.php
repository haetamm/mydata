<?php
include('utils.php');

$host = "localhost";
$db   = "dataku";
$user = "root";
$pass = "";

try
{
    $link = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (Exception $e)
{
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}
