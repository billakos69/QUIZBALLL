<?php
include 'config.php';

// Ασφάλεια
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$name = "";
$image = "";

// Αν υπάρχει ID → φόρτωσε τα δεδομένα
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM quiz_data WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $name = $row['name'];
        $image = $row['image'];
    }
    $stmt->close();
}

// Αν γίνει submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $image = $_POST['image'] ?? '';

    if ($id > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE quiz_data SET name = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $image, $id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO quiz_data (name, image) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $image);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin_quiz.php"); // επιστροφή στο quiz panel
    exit;
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<title><?= $id > 0 ? "Επεξεργασία Quiz" : "Προσθήκη Quiz" ?></title>
<style>
body { background:#1a1a1a; color:white; font-family:sans-serif; padding:20px; }
form { max-width:500px; margin:auto; display:flex; flex-direction:column; gap:15px; }
input[type=text] { padding:10px; border-radius:5px; border:none; }
button { padding:10px; background:#00cccc; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#005f5f; color:white; }
a { color:#00cccc; text-decoration:none; }
</style>
</head>
<body>
<h1><?= $id > 0 ? "Επεξεργασία Quiz" : "Προσθήκη Quiz" ?></h1>
<form method="post">
    <label>Όνομα :</label>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

    <label>Εικόνα (URL):</label>
    <input type="text" name="image" value="<?= htmlspecialchars($image) ?>">

    <button type="submit">Αποθήκευση</button>
</form>
<br>
<a href="admin_quiz.php">⬅ Επιστροφή στο Quiz Panel</a>
</body>
</html>
