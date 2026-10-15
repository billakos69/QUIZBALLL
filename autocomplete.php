<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

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

$q = trim($_GET['q'] ?? '');
if ($q === '' || mb_strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

// Επιστρέφουμε και το category_id
$stmt = $pdo->prepare("SELECT id, name, image, category_id FROM quiz_data WHERE name LIKE ? LIMIT 10");
$stmt->execute(["%$q%"]);
$results = $stmt->fetchAll();

// Σιγουρευόμαστε ότι κάθε πεδίο είναι string για JSON
foreach($results as &$r){
    $r['id'] = (int)$r['id'];
    $r['category_id'] = (int)$r['category_id'];
    $r['name'] = (string)$r['name'];
    $r['image'] = $r['image'] ?? '';
}

echo json_encode($results, JSON_UNESCAPED_UNICODE);
