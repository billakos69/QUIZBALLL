<?php
session_start();
require "config.php"; // εδώ έχεις $pdo

$categoryId = (int)($_GET['cat'] ?? 1);
$userId = $_SESSION['user_id'] ?? 1;

$stmt = $pdo->prepare("
    SELECT * FROM questions 
    WHERE category_id = :cid
    AND id NOT IN (
        SELECT question_id FROM user_questions WHERE user_id = :uid
    )
    ORDER BY RAND()
    LIMIT 1
");
$stmt->execute([ ':cid' => $categoryId, ':uid' => $userId ]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if ($question) {
    $dataHint = $question['hint'] ?? '';
    echo "<h2 id='question' data-hint='".htmlspecialchars($dataHint)."'>".htmlspecialchars($question['question_text'])."</h2>";
    
    echo "<form method='post' action='answer.php'>";
    foreach (['answer_1','answer_2','answer_3','answer_4'] as $i => $ans) {
        $answerText = $question[$ans] ?? 'Απάντηση δεν διαθέσιμη';
        echo "<label>
                <input type='radio' name='answer' value='".($i+1)."' data-correct='".$question['correct_answer']."'>
                ".htmlspecialchars($answerText)."
              </label><br>";
    }
    echo "<input type='hidden' name='question_id' value='".$question['id']."'>";
    echo "<button type='submit'>Απάντησε</button>";
    echo "</form>";
} else {
    echo "<p>Δεν υπάρχουν άλλες ερωτήσεις σε αυτή την κατηγορία!</p>";
}
?>
