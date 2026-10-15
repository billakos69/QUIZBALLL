<?php
require "config.php";

if (!isset($_POST['question_id'], $_POST['answer'])) die("Λείπουν δεδομένα.");

$userId = $_SESSION['user_id'] ?? 1;
$questionId = (int)$_POST['question_id'];
$answer = (int)$_POST['answer'];

$stmt = $pdo->prepare("SELECT * FROM questions WHERE id=?");
$stmt->execute([$questionId]);
$q = $stmt->fetch();
if (!$q) die("Λάθος ερώτηση!");

$isCorrect = ($answer == $q['correct_answer']);

// Υπολογισμός πόντων
$points = 0;
if ($isCorrect) {
    switch ($q['difficulty']) {
        case 'easy': $points=1; break;
        case 'medium': $points=2; break;
        case 'hard': $points=3; break;
        default: $points=1;
    }
    $pdo->prepare("UPDATE user SET score = score + ? WHERE id=?")
        ->execute([$points,$userId]);
}

// Καταγραφή ότι παίχτηκε
$stmt = $pdo->prepare("INSERT IGNORE INTO user_questions (user_id, question_id) VALUES (?,?)");
$stmt->execute([$userId,$questionId]);

header("Location: board.php");
exit;
?>
