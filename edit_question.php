<?php
include 'config.php'; // εδώ κάνεις connect στη βάση

// --- Έλεγχος admin ---
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'Admin') {
    header("Location: login.php");
    exit;
}


// --- Προετοιμασία μεταβλητών ---
$id = $_GET['id'] ?? null;
$editing = !empty($id);

if ($editing) {
    $stmt = $conn->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $question = $res->fetch_assoc();
    $stmt->close();

    if (!$question) die("Δεν βρέθηκε η ερώτηση.");
} else {
    $question = [
        'id' => '',
        'category_id' => '',
        'question_text' => '',
        'answer_1' => '',
        'answer_2' => '',
        'correct_answer' => '',
        'difficulty' => 'easy',
        'image' => ''
    ];
}

// --- Φόρμα POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id    = $_POST['category_id'] ?? '';
    $question_text  = $_POST['question_text'] ?? '';
    $answer_1       = $_POST['answer_1'] ?? '';
    $answer_2       = $_POST['answer_2'] ?? '';
    $correct_answer = $_POST['correct_answer'] ?? '';
    $difficulty     = $_POST['difficulty'] ?? 'easy';
    $image          = $_POST['image'] ?? '';
    $hint_text      = $_POST['hint_text'] ?? '';
    $hint_image     = $_POST['hint_image'] ?? '';

    if ($editing) {
        $stmt = $conn->prepare("UPDATE questions 
            SET category_id=?, question_text=?, answer_1=?, answer_2=?, correct_answer=?, difficulty=?, image=?, hint_text=?, hint_image=?
            WHERE id=?");
        $stmt->bind_param("issssssssi", $category_id, $question_text, $answer_1, $answer_2, $correct_answer, $difficulty, $image, $hint_text, $hint_image, $id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO questions 
            (category_id, question_text, answer_1, answer_2, correct_answer, difficulty, image, hint_text, hint_image) 
            VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("issssssss", $category_id, $question_text, $answer_1, $answer_2, $correct_answer, $difficulty, $image, $hint_text, $hint_image);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin_panel.php?msg=success");
    exit;
}

// --- Φόρτωση κατηγοριών για dropdown ---
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<link rel="icon" href="images/logo.png" type="image/png">
<title><?= $editing ? "Επεξεργασία Ερώτησης" : "Προσθήκη Ερώτησης" ?></title>
<style>
body { font-family: Arial,sans-serif; background:#121212; color:#eaeaea; padding:20px; }
form { background:#1e1e1e; padding:20px; border-radius:12px; max-width:600px; margin:auto; }
h2 { text-align:center; color:#0baa93; }
label { display:block; margin-top:10px; font-weight:bold; }
input, select, textarea { width:100%; padding:10px; margin:6px 0 12px; border-radius:6px; border:1px solid #444; background:#2a2a2a; color:#fff; }
textarea { min-height:80px; resize:vertical; }
button { background:#0baa93; color:#fff; border:none; padding:10px 16px; border-radius:6px; cursor:pointer; font-weight:bold; }
button:hover { background:#ff8431; }
a.back { text-decoration:none; background:#444; padding:10px 16px; border-radius:6px; color:#fff; margin-left:10px; }
.preview img { max-width:150px; border-radius:8px; margin-top:10px; }
@media (max-width: 600px) {
    .leaderboard-box {
        position: relative;
        top: auto;
        right: auto;
        transform: none;
        margin: 20px auto 0 auto;
        width: 90%;
    }
    
    .hero img {
        max-width: 80%;
        height: auto;
    }

    .features {
        padding: 20px 10px;
        gap: 15px;
    }

    .user-menu .dropdown {
        position: relative;
        width: 100%;
        top: 0;
        right: 0;
    }
nav {
        padding: 0 10px; /* από 50px σε 10px */
    }

    nav .logo {
        font-size: 24px; /* μικρότερο logo για να χωράει */
        line-height: 60px; /* λιγότερο ύψος */
    }

    nav ul li a {
        font-size: 16px; /* μικρότερα links */
        line-height: 60px; /* ευθυγράμμιση με logo */
        padding: 6px 10px;
    }

    .user-menu i {
        font-size: 24px;
        line-height: 60px;
    }

    nav ul ul {
        top: 60px; /* αντί για 70px */
    }
    nav ul ul li a {
        font-size: 14px; /* μικρότερα submenu links */
        line-height: 40px; /* λιγότερο ύψος */
        padding: 6px 10px;
    }
    nav ul li:hover > ul {
  display: block;
  opacity: 3;
}
}
</style>
</head>
<body>
<h2><?= $editing ? "✏️ Επεξεργασία Ερώτησης" : "➕ Προσθήκη Ερώτησης" ?></h2>

<form method="post">
    <?php if ($editing): ?>
        <label>ID:</label>
        <input type="text" value="<?= htmlspecialchars($question['id']) ?>" disabled>
    <?php endif; ?>

    <label>Κατηγορία:</label>
    <select name="category_id" required>
        <option value="">-- Επιλογή Κατηγορίας --</option>
        <?php while($cat = $categories->fetch_assoc()): ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id']==$question['category_id']?'selected':'' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Κείμενο Ερώτησης:</label>
    <textarea name="question_text" required><?= htmlspecialchars($question['question_text']) ?></textarea>

    <label>Απάντηση 1:</label>
    <input type="text" name="answer_1" value="<?= htmlspecialchars($question['answer_1']) ?>">

    <label>Απάντηση 2:</label>
    <input type="text" name="answer_2" value="<?= htmlspecialchars($question['answer_2']) ?>">

    <label>Σωστή Απάντηση (γράψε το κείμενο):</label>
    <input type="text" name="correct_answer" value="<?= htmlspecialchars($question['correct_answer']) ?>" required>

    <label>Δυσκολία:</label>
    <select name="difficulty">
        <option value="easy" <?= $question['difficulty']==='easy'?'selected':'' ?>>Easy</option>
        <option value="medium" <?= $question['difficulty']==='medium'?'selected':'' ?>>Medium</option>
        <option value="hard" <?= $question['difficulty']==='hard'?'selected':'' ?>>Hard</option>
    </select>

    <label>URL Εικόνας:</label>
    <input type="text" name="image" id="imageInput" value="<?= htmlspecialchars($question['image']) ?>">
    <div class="preview" id="preview">
        <?php if (!empty($question['image'])): ?>
            <img src="<?= htmlspecialchars($question['image']) ?>" alt="Preview">
        <?php endif; ?>
    </div>

    <label>Hint :</label>
    <input type="text" name="hint_text" value="<?= htmlspecialchars($question['hint_text'] ?? '') ?>">

    <label>Hint Image URL:</label>
    <input type="text" name="hint_image" value="<?= htmlspecialchars($question['hint_image'] ?? '') ?>">

    <button type="submit"><?= $editing ? "💾 Αποθήκευση" : "➕ Δημιουργία" ?></button>
    <a href="admin_panel.php" class="back">⬅ Dashboard</a>
</form>
<script src="navbar.js"></script>

<script>
document.getElementById('imageInput').addEventListener('input', function(){
    document.getElementById('preview').innerHTML = this.value 
      ? '<img src="'+this.value+'" alt="Preview">' 
      : '';
});
</script>
</body>
</html>
