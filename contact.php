<?php
session_start();
$isLoggedIn = isset($_SESSION['user']);
$username   = $isLoggedIn ? $_SESSION['user'] : null;
?>

<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<title>Contact - QUIZBALL</title>
<link rel="icon" href="images/logo.png" type="image/png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>

<link rel="stylesheet" href="style.css">
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
  background: linear-gradient(to bottom, #033b3fff, #0a657cff);
  font-family: 'MyCustomFont', sans-serif;
  color: #fff;
  display: flex;
  flex-direction: column;
  min-height: 100vh; /* γεμίζει όλη την οθόνη */
  margin: 0;
  padding-top: 100px; /* για navbar */
  overflow-x: hidden;
}
.container { max-width:600px; margin:0 auto; background:#0e3d4a; padding:20px; border-radius:20px;}
h1 { text-align:center; margin-bottom:20px;}
input, textarea { width:100%; padding:10px; margin:10px 0; border-radius:8px; border:none; }
button { padding:10px 20px; border-radius:8px; border:none; cursor:pointer; background:#1abc9c; color:#0b2a33; font-weight:700;}
</style>
</head>
<body>
    <!-- MENU -->
<?php include 'nav.html'; ?>


<div class="container">
  <h1>Contact</h1>
  <form action="https://formspree.io/f/mrbavyjg" method="POST">
    <input type="text" name="name" placeholder="Όνομα" required>
    <input type="email" name="email" placeholder="Email" required>
    <textarea name="message" rows="5" placeholder="Μήνυμα" required></textarea>
    <button type="submit">Αποστολή</button>
  </form>
</div>

<?php include 'footer.html'; ?>
<script src="navbar.js"></script>


<script>
const msg = document.getElementById('logout-msg');
if(msg){
    setTimeout(() => {
        msg.style.opacity = '0';
        setTimeout(() => { msg.remove(); }, 1000);
    }, 3000);
}
</script>
</body>
</html>
