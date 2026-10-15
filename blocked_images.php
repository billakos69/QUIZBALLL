<?php
header('Content-Type: application/json; charset=utf-8');

$DB_HOST = 'localhost';
$DB_NAME = 'movies_db';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo json_encode([]);
    exit;
}

// Πάρε όλα τα IDs που ανήκουν στην category_id 3
$stmt = $pdo->query("SELECT id FROM quiz_data WHERE category_id = 3");
$ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($ids);
?>
