<?php
include 'config.php';

$gamemode = $_GET['gamemode'] ?? '';
if (!$gamemode) {
    die("Δεν επιλέχθηκε gamemode.");
}

// Δες σε ποιο PHP θα καταλήξει
$targetPage = "default.php";
switch (strtolower($gamemode)) {
    case "single":
    case "single player":
        $targetPage = "single.php";
        break;
    case "1v1":
    case "same_screen":
        $targetPage = "same_screen.php";
        break;
    case "adventure":
        $targetPage = "coop.php";
        break;
}

// Φέρε κατηγορίες από DB
$stmt = $conn->query("SELECT id, name, image FROM categories WHERE visible=1 ORDER BY id");
$categories = $stmt->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<link rel="icon" href="images/logo.png" type="image/png">
<title>Επιλογή Κατηγοριών</title>
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
  font-family: Arial, sans-serif;
  background: #0b2a33;
  color: #fff;
  padding: 20px;
}
.container {
  max-width: 700px;
  margin: auto;
  background: #133d3c;
  padding: 20px;
  border-radius: 12px;
}
h1 { text-align:center; margin-bottom:20px; }
.category {
  display:flex;
  align-items:center;
  gap:12px;
  padding:10px;
  background:#0e3d4a;
  border-radius:8px;
  margin-bottom:10px;
}
.category img {
  width:50px; height:50px; border-radius:6px; object-fit:cover;
}
button {
  margin-top:20px;
  padding:10px 18px;
  border:none;
  border-radius:8px;
  background:#0ff;
  color:#000;
  font-weight:bold;
  cursor:pointer;
}
button:hover { background:#0cc; }
</style>
</head>
<body>
<div class="container">
  <h1>Διάλεξε Κατηγορίες για <?= htmlspecialchars(ucwords($gamemode)) ?></h1>
  <form method="get" action="<?= $targetPage ?>">
    <input type="hidden" name="gamemode" value="<?= htmlspecialchars($gamemode) ?>">
    <?php foreach ($categories as $cat): ?>
      <div class="category">
        <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>" checked>
        <img src="images/<?= htmlspecialchars($cat['image']) ?>" alt="">
        <span><?= htmlspecialchars($cat['name']) ?></span>
      </div>
    <?php endforeach; ?>
    <button type="submit">Συνέχεια</button>
  </form>
</div>
</body>
</html>
