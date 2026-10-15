<?php
include 'config.php';
$isLoggedIn = isset($_SESSION['user']);
$username   = $isLoggedIn ? $_SESSION['user'] : null;

$logoutMessage = '';
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    $logoutMessage = "✅ Αποσυνδεθήκατε με επιτυχία!";
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <title>QuizBall - Football Quiz</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" href="images/logo.png" type="image/png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
<?php if($logoutMessage): ?>
<div id="logout-msg" class="logout-msg">
    <?= htmlspecialchars($logoutMessage) ?>
</div>
<?php endif; ?>

<!-- MENU -->
<?php include 'nav.html'; ?>




<section class="hero">
  <img src="images/logo.png" alt="Hero Image" style="width:180px; height:auto;">
  <h1>Welcome to QuizBall</h1>
  <p>Ένα ποδοσφαιρικό κουίζ για αληθινούς φιλάθλους!</p>
  <a href="categories.php">Ξεκίνα τώρα</a>

  <!-- LEADERBOARD BOX -->
  <div class="leaderboard-box">
    <h3>🏆 Top 3</h3>
    <?php
    $result = $conn->query("SELECT username, highscore FROM user ORDER BY highscore DESC LIMIT 3");
    if ($result && $result->num_rows > 0):
      $rank = 1;
      while ($row = $result->fetch_assoc()):
    ?>
      <div class="player">
        <span class="medal"><?= $rank == 1 ? "🥇" : ($rank == 2 ? "🥈" : "🥉") ?></span>
        <span class="name"><?= htmlspecialchars($row['username']) ?></span>
        <span class="score"><?= $row['highscore'] ?> πόντοι</span>
      </div>
    <?php
        $rank++;
      endwhile;
    else:
      echo "<p>Δεν υπάρχουν ακόμη σκορ.</p>";
    endif;
    ?>
  </div>
</section>



<!-- FEATURES -->
<section class="features">
  <div class="feature">
    <h2>⚽ Χιλιάδες Ερωτήσεις</h2>
    <p>Από τα Παγκόσμια Κύπελλα έως τις τοπικές διοργανώσεις.</p>
  </div>
  <div class="feature">
    <h2>👥 Παίξε Μόνος ή με Φίλους</h2>
    <p>Διάλεξε mode και δες ποιος ξέρει καλύτερα μπάλα.</p>
  </div>
  <div class="feature">
    <h2>🏆 Ανέβα στο Leaderboard</h2>
    <p>Κέρδισε πόντους και απόδειξε ποιος είναι ο πρωταθλητής.</p>
  </div>
</section>

<?php include 'footer.html'; ?>

<script src="navbar.js"></script>

<script>
 // Εμφάνιση / Απόκρυψη burger menu
  const menuToggle = document.getElementById('menu-toggle');
  const navLinks = document.getElementById('nav-links');

  menuToggle.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    menuToggle.textContent = navLinks.classList.contains('active') ? '✕' : '☰';
  });

const msg = document.getElementById('logout-msg');
if(msg){
    setTimeout(() => {
        msg.style.opacity = '0';
        setTimeout(() => { msg.remove(); }, 1000);
    }, 3000);
}


// Περιμένει να φορτώσει όλο το περιεχόμενο της σελίδας
window.addEventListener('load', () => {
  const loader = document.querySelector('.main-fader');

  // Διατηρεί τον loader 1.5 δευτερόλεπτα ακόμα πριν εξαφανιστεί
  setTimeout(() => {
    loader.style.opacity = '0';
    setTimeout(() => loader.remove(), 500);
  }, 4000);
});
</script>
</body>
</html>
