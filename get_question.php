<?php
session_start();
header('Content-Type: application/json');

$DB_HOST='localhost'; $DB_NAME='movies_db'; $DB_USER='root'; $DB_PASS='';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo json_encode(['error'=>$e->getMessage()]); exit;
}

$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : null;
$difficulty = $_GET['difficulty'] ?? null;

// exclude (απαντημένες ερωτήσεις από JS)
$exclude = [];
if (!empty($_GET['exclude'])) {
    $exclude = array_filter(array_map('intval', explode(',', $_GET['exclude'])));
}

if(!$cat_id || !$difficulty){
    echo json_encode(['error'=>'Λείπουν παράμετροι']); exit;
}

$placeholders = $exclude ? implode(',', array_fill(0, count($exclude), '?')) : '';
$whereExclude = $exclude ? " AND id NOT IN ($placeholders)" : '';

// --- Top5 category ---
if($cat_id==4){
    $sql = "SELECT * FROM questions WHERE category_id=? AND difficulty=? $whereExclude ORDER BY RAND() LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $params = $exclude ? array_merge([$cat_id,$difficulty], $exclude) : [$cat_id,$difficulty];
    $stmt->execute($params);
    $q = $stmt->fetch();

    if(!$q){
        echo json_encode(['error'=>'Δεν βρέθηκε ερώτηση']); 
        exit;
    }

    $answers = !empty($q['correct_answer']) ? array_map('trim', explode(',', $q['correct_answer'])) : [];

    function cleanAnswer($ans){
        $ans = preg_replace('/\s*\([^)]*\)/', '', $ans);
        $ans = preg_replace('/[.,:;\'"?!«»–—\-\/]/u', ' ', $ans);
        $ans = mb_strtolower(trim(preg_replace('/\s+/', ' ', $ans)), 'UTF-8');
        return $ans;
    }

    $answersClean = array_map('cleanAnswer', $answers);

    $response = [
        'id' => (int)$q['id'],
        'question_text' => $q['question_text'],
        'answers' => $answers,
        'answers_clean' => $answersClean,
        'difficulty' => $q['difficulty'],
        'type' => 'top5'
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}




// --- Default question ---
$sql = "SELECT * FROM questions WHERE category_id=? AND difficulty=? $whereExclude ORDER BY RAND() LIMIT 1";
$stmt = $pdo->prepare($sql);
$params = array_merge([$cat_id,$difficulty], $exclude);
$stmt->execute($params);
$q = $stmt->fetch();
if(!$q){ echo json_encode(['error'=>'Δεν βρέθηκε ερώτηση']); exit; }

$q['correct_answer'] = $q['correct_answer'] ?? null;
$q['image'] = $q['image'] ?? null;
$q['type'] = $q['type'] ?? 'default';

echo json_encode($q);
exit;

