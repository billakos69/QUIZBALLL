<?php
include 'config.php';

// Ασφάλεια: μόνο admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Σύνδεση DB
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$quiz = null;

// Αν είναι edit mode → φόρτωσε quiz
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM quiz WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $quiz = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Αν υποβληθεί η φόρμα
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $image = trim($_POST['image'] ?? '');

    if ($id > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE quiz SET name = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $image, $id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO quiz (name, image) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $image);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin_panel.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<title><?= $id > 0 ? "Επεξεργασία Quiz" : "Νέο Quiz" ?> - QuizBall</title>
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
.container { width: 90%; max-width: 600px; margin: 40px auto; background: var(--card-bg); padding: 20px; border-radius: 10px; }
h1 { text-align: center; margin-bottom: 25px; color: var(--accent); }
form label { display:block; margin-top:15px; margin-bottom:5px; font-weight:500; }
form input[type="text"] { width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; }
form button { margin-top:20px; padding:10px 20px; background: var(--accent); border:none; border-radius:6px; cursor:pointer; font-weight:600; }
form button:hover { background: var(--hover); color:#fff; }
.preview { margin-top:15px; text-align:center; }
.preview img { max-width:200px; border-radius:8px; }
a.back { display:inline-block; margin-top:20px; color:var(--accent); text-decoration:none; }
a.back:hover { color:var(--hover); }
</style>
</head>
<body>

<div class="container">
    <h1><?= $id > 0 ? "Επεξεργασία Quiz" : "Νέο Quiz" ?></h1>
    <form method="post">
        <label>Όνομα Quiz:</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($quiz['name'] ?? '') ?>">

        <label>Εικόνα (URL):</label>
        <input type="text" name="image" value="<?= htmlspecialchars($quiz['image'] ?? '') ?>">

        <?php if (!empty($quiz['image'])): ?>
        <div class="preview">
            <p>Προεπισκόπηση:</p>
            <img src="<?= htmlspecialchars($quiz['image']) ?>" alt="">
        </div>
        <?php endif; ?>

        <button type="submit">Αποθήκευση</button>
    </form>
    <a class="back" href="admin_panel.php">← Επιστροφή</a>
</div>

</body>
</html>
