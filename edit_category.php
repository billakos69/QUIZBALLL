<?php
include 'config.php';

// Έλεγχος admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

// Αρχικοποίηση
$catId = $_GET['id'] ?? null;
$name = '';
$image = '';
$message = '';

// Αν υπάρχει ID, φόρτωσε τα στοιχεία της κατηγορίας
if ($catId) {
    $stmt = $conn->prepare("SELECT name, image FROM categories WHERE id=?");
    $stmt->bind_param("i", $catId);
    $stmt->execute();
    $cat = $stmt->get_result()->fetch_assoc();
    if ($cat) {
        $name = $cat['name'];
        $image = $cat['image'];
    } else {
        $message = "Η κατηγορία δεν βρέθηκε.";
        $catId = null; // για να μην μπει σε update
    }
}

// Όταν υποβάλλεται η φόρμα
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $image = trim($_POST['image'] ?? '');

    if ($name === '') {
        $message = "Το όνομα κατηγορίας είναι υποχρεωτικό.";
    } else {
        if ($catId) {
            // Update υπάρχουσας κατηγορίας
            $stmt = $conn->prepare("UPDATE categories SET name=?, image=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $image, $catId);
            $stmt->execute();
            $message = "Η κατηγορία ενημερώθηκε!";
        } else {
            // Νέα κατηγορία
            $stmt = $conn->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $image);
            $stmt->execute();
            $message = "Η κατηγορία προστέθηκε!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<link rel="icon" href="images/logo.png" type="image/png">
<title><?= $catId ? "Επεξεργασία Κατηγορίας" : "Προσθήκη Κατηγορίας" ?></title>
<style>
body { font-family: Arial,sans-serif; background:#0b0b0b; color:#fff; margin:0; padding:0; }
.container { max-width:500px; margin:50px auto; padding:30px; background:#1a1a1a; border-radius:15px; box-shadow:0 0 20px rgba(0,0,0,0.7); }
h1 { text-align:center; color:#0baa93; margin-bottom:25px; }
label { display:block; margin-bottom:8px; font-weight:bold; }
input { width:100%; padding:10px; border-radius:5px; border:none; margin-bottom:15px; }
input[type="submit"] { background:#0baa93; color:#fff; cursor:pointer; font-weight:bold; transition:0.3s; }
input[type="submit"]:hover { background:#ff8431; }
.msg { margin-bottom:15px; padding:10px; background:#222; border-left:4px solid #0baa93; }
a.back { color:#9fd9df; text-decoration:none; display:block; margin-top:15px; text-align:center; }
a.back:hover { text-decoration:underline; }

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
<script src="navbar.js"></script>

<body>
<div class="container">
<h1><?= $catId ? "Επεξεργασία Κατηγορίας" : "Προσθήκη Κατηγορίας" ?></h1>

<?php if($message): ?>
<p class="msg"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Όνομα Κατηγορίας</label>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

    <label>URL Εικόνας</label>
    <input type="text" name="image" value="<?= htmlspecialchars($image) ?>" placeholder="π.χ. images/cat1.jpg">

    <input type="submit" value="<?= $catId ? "Ενημέρωση" : "Προσθήκη" ?>">
</form>

<a class="back" href="admin_panel.php">← Επιστροφή στο Admin Panel</a>
</div>
</body>
</html>
