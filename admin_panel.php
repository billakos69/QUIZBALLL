<?php
include 'config.php';

// Ασφάλεια: μόνο admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Σύνδεση στη βάση
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Διαγραφή κατηγορίας
if (isset($_GET['delete_category'])) {
    $id = (int)$_GET['delete_category'];

    // Πρώτα σβήσε όλες τις ερωτήσεις αυτής της κατηγορίας από user_questions
    $stmt = $conn->prepare("DELETE uq FROM user_questions uq 
                            INNER JOIN questions q ON q.id = uq.question_id 
                            WHERE q.category_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Μετά σβήσε τις ερωτήσεις αυτής της κατηγορίας
    $stmt = $conn->prepare("DELETE FROM questions WHERE category_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Τέλος σβήσε την κατηγορία
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_panel.php");
    exit;
}

// Διαγραφή ερώτησης
if (isset($_GET['delete_question'])) {
    $id = (int)$_GET['delete_question'];

    // Πρώτα σβήσε τις σχετικές εγγραφές από user_questions
    $stmt = $conn->prepare("DELETE FROM user_questions WHERE question_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Μετά σβήσε την ίδια την ερώτηση
    $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_panel.php");
    exit;
}

// Φόρτωση κατηγοριών
$categories = $conn->query("SELECT * FROM categories ORDER BY id");

// Φόρτωση ερωτήσεων
$questions = $conn->query("
    SELECT q.*, c.name AS category_name
    FROM questions q
    LEFT JOIN categories c ON c.id = q.category_id
    ORDER BY q.id
");
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<title>Admin Panel - QuizBall</title>
  <link rel="icon" href="images/logo.png" type="image/png">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --bg: #1a1a1a;
    --card-bg: #222;
    --accent: #00cccc;
    --text: #fff;
    --hover: #005f5f;
}
* { box-sizing: border-box; margin:0; padding:0; font-family: 'Poppins', sans-serif; }
body { background: var(--bg); color: var(--text); }
.container { width: 90%; max-width: 1200px; margin: 30px auto; }
h1 { text-align: center; margin-bottom: 40px; color: var(--accent); }
h2 { margin-top: 30px; margin-bottom: 10px; color: var(--accent); }
table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background: var(--card-bg); border-radius: 8px; overflow: hidden; }
th, td { padding: 12px 15px; text-align: left; }
th { background: var(--accent); color: #000; }
tr:nth-child(even) { background: #2a2a2a; }
tr:hover { background: #333; }
a.button { padding:6px 12px; background: var(--accent); color: #000; text-decoration:none; border-radius:4px; transition: 0.3s; }
a.button:hover { background: var(--hover); color:#fff; }
.top-bar { display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.top-bar a { text-decoration: none; padding:8px 15px; background: var(--accent); color:#000; border-radius:4px; transition:0.3s; }
.top-bar a:hover { background: var(--hover); color:#fff; }
@media (max-width:768px){
    .container { width: 95%; }
    table, th, td { font-size:14px; }
}
</style>
</head>
<body>
   
<a href="index.php">
    <img border="0" src="images/home.png" width="100" height="100">
</a>
<div class="top-bar" style="margin-bottom:40px;">
    <a href="admin_quiz.php">Είσοδος στο Quiz Data</a>
</div>

<div class="container">
<h1>Admin Panel</h1>

<!-- Κατηγορίες -->
<div class="top-bar">
    <h2>Κατηγορίες</h2>
    <a href="edit_category.php">+ Προσθήκη Κατηγορίας</a>
</div>
<table>
<tr><th>ID</th><th>Όνομα</th><th>Εικόνα</th><th>Ενέργειες</th></tr>
<?php while($cat = $categories->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($cat['id'] ?? '') ?></td>
    <td><?= htmlspecialchars($cat['name'] ?? '') ?></td>
    <td><?= htmlspecialchars($cat['image'] ?? '') ?></td>
    <td>
        <a class="button" href="edit_category.php?id=<?= $cat['id'] ?>">Edit</a>
        <a class="button" href="?delete_category=<?= $cat['id'] ?>" onclick="return confirm('Σίγουρα;')">Delete</a>
    </td>
</tr>
<?php endwhile; ?>
</table>

<!-- Ερωτήσεις -->
<div class="top-bar">
    <h2>Ερωτήσεις</h2>
    <a href="edit_question.php">+ Προσθήκη Ερώτησης</a>
</div>
<table>
<tr><th>ID</th><th>Κατηγορία</th><th>Ερώτηση</th><th>Απαντήσεις</th><th>Σωστή</th><th>Ενέργειες</th></tr>
<?php while($q = $questions->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($q['id'] ?? '') ?></td>
    <td><?= htmlspecialchars($q['category_name'] ?? 'Ανώνυμο') ?></td>
    <td><?= htmlspecialchars($q['question_text'] ?? '') ?></td>
    <td>
        <?= htmlspecialchars($q['answer_1'] ?? '') ?>, <?= htmlspecialchars($q['answer_2'] ?? '') ?>,
        <?= htmlspecialchars($q['answer_3'] ?? '') ?>, <?= htmlspecialchars($q['answer_4'] ?? '') ?>
    </td>
    <td><?= htmlspecialchars($q['correct_answer'] ?? '') ?></td>
    <td>
        <a class="button" href="edit_question.php?id=<?= $q['id'] ?>">Edit</a>
        <a class="button" href="?delete_question=<?= $q['id'] ?>" onclick="return confirm('Σίγουρα;')">Delete</a>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>
