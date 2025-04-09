<?php
$host = 'postgres';
$port = '5432';
$dbname = 'hellofresh';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";

$user = 'hellofresh';
$password = 'hellofresh';

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
