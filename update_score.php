<?php
session_start();
header('Content-Type: application/json');

$DB_HOST = 'localhost';
$DB_NAME = 'movies_db';
$DB_USER = 'root';
$DB_PASS = '';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error'=>'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$score = isset($_POST['score']) ? (int)$_POST['score'] : 0;

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);

    // Πάρε το προηγούμενο highscore
    $stmt = $pdo->prepare("SELECT highscore FROM user WHERE id=?");
    $stmt->execute([$user_id]);
    $prevHigh = (int)$stmt->fetchColumn();

    if ($score > $prevHigh) {
        $stmt = $pdo->prepare("UPDATE user SET highscore=? WHERE id=?");
        $stmt->execute([$score,$user_id]);
        $prevHigh = $score;
    }

    echo json_encode(['score'=>$score,'highscore'=>$prevHigh]);

} catch(PDOException $e){
    echo json_encode(['error'=>'DB error: '.$e->getMessage()]);
}