<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    
    $sql = "SELECT email FROM user WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        $success = "📧 Το email σου είναι: <b>$email</b>";
    } else {
        $error = "❌ Δεν βρέθηκε χρήστης με αυτό το όνομα";
    }
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Ξέχασα το email</title>
    <link rel="icon" href="images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <style>
      body{ background-color: #080710; font-family:'Poppins',sans-serif; }
      .container{ width:400px; margin:100px auto; background:rgba(255,255,255,0.1); padding:30px; border-radius:10px; color:#fff; text-align:center; }
      input{ width:100%; padding:10px; margin:10px 0; border:none; border-radius:5px; background:rgba(255,255,255,0.2); color:#fff; }
      button{ padding:10px 20px; border:none; border-radius:5px; background:#fff; color:#080710; font-weight:600; cursor:pointer; }
      .msg{ margin-top:15px; }
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
    <div class="container">
        <h2>Ξέχασα το Email</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Όνομα χρήστη" required>
            <button type="submit">Ανάκτηση Email</button>
        </form>
        <?php 
          if(isset($success)) echo "<p class='msg' style='color:lightgreen;'>$success</p>"; 
          if(isset($error)) echo "<p class='msg' style='color:red;'>$error</p>"; 
        ?>
    </div>
</body>
</html>
