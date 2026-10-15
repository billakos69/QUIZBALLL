<?php
session_start();

// Σύνδεση στη βάση
$DB_HOST = 'localhost';
$DB_NAME = 'movies_db';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die('Σφάλμα DB: ' . htmlspecialchars($e->getMessage()));
}

// Παίρνουμε όλες τις κατηγορίες
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY id");
$categories = $stmt->fetchAll();

// Αν έχει υποβληθεί η φόρμα
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['selected_mode'] = $_POST['mode'] ?? 'single';
    $_SESSION['selected_categories'] = $_POST['categories'] ?? [];

    if ($_SESSION['selected_mode'] === 'single') {
        header("Location: custom_single.php");
    } else {
        header("Location: custom_1v1.php");
    }
    exit;
}

// Προεπιλεγμένες τιμές
$selected = $_SESSION['selected_categories'] ?? [];
$mode = $_SESSION['selected_mode'] ?? 'single';

// User login check για menu
$isLoggedIn = isset($_SESSION['user']);
$username   = $isLoggedIn ? $_SESSION['user'] : null;
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="style.css">
<link rel="icon" href="images/logo.png" type="image/png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Επιλογή Κατηγοριών</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
<style>
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
body {
    font-family: 'Arial', sans-serif;
    background: linear-gradient(135deg, #0b2a33, #07394a);
    color: #eef;
   display: flex;
  flex-direction: column;
  min-height: 100vh; /* γεμίζει όλη την οθόνη */
  margin: 0;
  padding-top: 100px; /* για navbar */
  overflow-x: hidden;
}

.container {
    background: rgba(15, 40, 55, 0.95);
    padding: 40px 30px;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.5);
    width: 90%;
    max-width: 600px;
    text-align: center;
    margin: 0 auto;
}

h1 {
    margin-bottom: 30px;
    color: #f1c40f;
    text-shadow: 1px 1px 4px #000;
}

/* Toggle κουμπιά mode */
.mode-selection {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-bottom: 30px;
}

.mode-selection label {
    padding: 12px 30px;
    border-radius: 30px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
    user-select: none;
    border: 2px solid #f1c40f;
}

.mode-selection input[type="radio"] {
    display: none;
}

.mode-selection label.selected {
    background: #f1c40f;
    color: #0b2a33;
    box-shadow: 0 4px 15px rgba(0,0,0,0.4);
}

.mode-selection label:not(.selected):hover {
    background: rgba(241,196,15,0.2);
}

/* Categories grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.category-card {
    background: #1258c3;
    padding: 18px 10px;
    border-radius: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.2s, background 0.2s;
    text-align: center;
    user-select: none;
    color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

.category-card:hover {
    transform: scale(1.05);
    background: #1a73e8;
}

.category-card.selected {
    background: #2ecc71;
    color: #0b2a33;
    box-shadow: 0 5px 15px rgba(0,0,0,0.5);
}

/* Submit κουμπί */
input[type="submit"] {
    background: #f39c12;
    color: #0b2a33;
    font-weight: bold;
    border: none;
    border-radius: 12px;
    padding: 12px 25px;
    margin-top: 25px;
    cursor: pointer;
    transition: 0.3s;
}

input[type="submit"]:hover {
    background: #e67e22;
}

input[type="checkbox"] {
    display: none;
}


</style>
</head>
<body>

<!-- MENU -->
<nav>
    <div class="logo">QUIZBALL</div>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li>
            <a href="#">CATEGORIES</a>
            <ul class="dropdown">
                <li><a href="single.php?category=single">Single Player</a></li>
                <li><a href="same_screen.php?category=same_screen">1v1</a></li>
                <li><a href="categories.php">All Game Modes</a></li>
            </ul>
        </li>
        <li><a href="contact.php">Contact</a></li>
        <li class="user-menu">
            <i class="fas fa-user-circle"></i>
            <ul class="dropdown">
            <?php if ($isLoggedIn): ?>
                <li><strong><?= htmlspecialchars($username) ?></strong></li>
                <li><a href="account.php">Account</a></li>
                <li><a href="favorites.php">Αγαπημένα</a></li>
                <li><a href="logout.php">Αποσύνδεση</a></li>
            <?php else: ?>
                <li><a href="login.php">Σύνδεση</a></li>
                <li><a href="signup.php">Εγγραφή</a></li>
            <?php endif; ?>
            </ul>
        </li>
    </ul>
</nav>

<div class="container">
    <h1>Επιλογή Κατηγοριών</h1>
    <form method="POST">
        <div class="mode-selection">
            <label class="<?= $mode==='single'?'selected':'' ?>">
                <input type="radio" name="mode" value="single" <?= $mode==='single'?'checked':'' ?>> Single Player
            </label>
            <label class="<?= $mode==='1v1'?'selected':'' ?>">
                <input type="radio" name="mode" value="1v1" <?= $mode==='1v1'?'checked':'' ?>> 1v1
            </label>
        </div>

        <div>
            <strong>Επέλεξε Κατηγορίες:</strong>
            <div class="categories-grid">
                <?php foreach($categories as $cat): 
                    $isChecked = in_array($cat['id'], $selected) ? 'checked' : '';
                ?>
                <label class="category-card <?= $isChecked ? 'selected' : '' ?>">
                    <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>" <?= $isChecked ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <input type="submit" value="Πάμε στο Παιχνίδι">
    </form>
</div>
<script src="navbar.js"></script>

<?php include 'footer.html'; ?>


<script>
// Toggle class selected για mode
document.querySelectorAll('.mode-selection input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function(){
        document.querySelectorAll('.mode-selection label').forEach(lbl => lbl.classList.remove('selected'));
        this.parentElement.classList.add('selected');
    });
});

// Toggle class selected για τις κατηγορίες
document.querySelectorAll('.category-card input[type="checkbox"]').forEach(cb => {
    cb.addEventListener('change', function() {
        if(this.checked){
            this.parentElement.classList.add('selected');
        } else {
            this.parentElement.classList.remove('selected');
        }
    });
});
</script>

</body>
</html>
