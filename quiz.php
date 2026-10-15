<?php
require "config.php";

$userId = $_SESSION['user_id'] ?? 1;
$categoryId = (int)($_GET['cat'] ?? 1);

// Έλεγχος αν ο χρήστης έχει παιχτεί όλες τις ερωτήσεις
$stmt = $pdo->prepare("
    SELECT * FROM questions
    WHERE category_id = :cid
    AND id NOT IN (
        SELECT question_id FROM user_questions WHERE user_id = :uid
    )
    ORDER BY RAND()
    LIMIT 1
");
$stmt->execute([':cid'=>$categoryId, ':uid'=>$userId]);
$question = $stmt->fetch();

if (!$question):
    // Ελέγχουμε αν υπάρχουν ερωτήσεις στην κατηγορία
    $stmtAll = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE category_id=:cid");
    $stmtAll->execute([':cid'=>$categoryId]);
    $totalQuestions = (int)$stmtAll->fetchColumn();
    
    if ($totalQuestions > 0):
        // Όλες παιχτήκαν
        ?>
        <p>Έχεις παίξει όλες τις ερωτήσεις σε αυτή την κατηγορία!</p>
        <form method="post" action="reset_questions.php">
            <input type="hidden" name="category_id" value="<?= $categoryId ?>">
            <button type="submit">Ξεκίνα ξανά την κατηγορία</button>
        </form>
        <a href="test.php">Επιστροφή στο ταμπλό</a>
    <?php
    else:
        echo "<p>Δεν υπάρχουν ερωτήσεις σε αυτή την κατηγορία.</p>";
        echo "<a href='test.php'>Επιστροφή στο ταμπλό</a>";
    endif;
    exit;
endif;
?>
<!DOCTYPE html>
<html lang="el">
<head><meta charset="UTF-8"><title>Quiz</title></head>
<body>
<h2 id="question" data-hint="<?= htmlspecialchars($question['hint'] ?? '') ?>">
    <?= htmlspecialchars($question['question_text']) ?>
</h2>
<form method="post" action="answer.php">
    <?php for ($i=1; $i<=4; $i++): 
        $answerText = $question["answer_$i"] ?? 'Απάντηση δεν διαθέσιμη';
    ?>
        <label>
            <input type="radio" name="answer" value="<?= $i ?>" required>
            <?= htmlspecialchars($answerText) ?>
        </label><br>
    <?php endfor; ?>
    <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
    <button type="submit">Απάντησε</button>
</form>
</body>
</html>
