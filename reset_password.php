<?php
include 'config.php';
date_default_timezone_set("Europe/Athens");

if (!isset($_GET['token'])) {
    die("❌ Δεν υπάρχει token.");
}

$token = $conn->real_escape_string($_GET['token']);

// Βρίσκουμε χρήστη με το token
$stmt = $conn->prepare("SELECT id, reset_expires FROM user WHERE reset_token=?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("❌ Το link δεν είναι έγκυρο.");
}

$user = $result->fetch_assoc();

// Έλεγχος λήξης
if (strtotime($user['reset_expires']) < time()) {
    die("❌ Το link έχει λήξει.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE user SET password=?, reset_token=NULL, reset_expires=NULL WHERE id=?");
    $stmt->bind_param("si", $new_password, $user['id']);
    if ($stmt->execute()) {
        // Redirect κατευθείαν στο login
        header("Location: login.php");
        exit;
    } else {
        $error = "❌ Σφάλμα: " . $conn->error;
    }
}

?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Νέος Κωδικός</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="icon" href="images/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <style>
      body{ background-color: #080710; }
      .background{ width: 430px; height: 520px; position: absolute; transform: translate(-50%,-50%); left: 50%; top: 50%; }
      .background .shape{ height: 200px; width: 200px; position: absolute; border-radius: 50%; }
      .shape:first-child{ background: linear-gradient(#1845ad,#23a2f6); left: -80px; top: -80px; }
      .shape:last-child{ background: linear-gradient(to right,#ff512f,#f09819); right: -30px; bottom: -80px; }
      form{ height: auto; width: 400px; background-color: rgba(255,255,255,0.13); position: absolute; transform: translate(-50%,-50%); top: 50%; left: 50%; border-radius: 10px; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.1); box-shadow: 0 0 40px rgba(8,7,16,0.6); padding: 50px 35px; }
      form *{ font-family:'Poppins',sans-serif; color:#fff; }
      form h3{ font-size:28px; text-align:center; margin-bottom:20px; }
      input{ display:block; width:100%; height:50px; background-color: rgba(255,255,255,0.07); border-radius: 3px; padding:0 10px; margin-top:8px; font-size:14px; color:#fff; }
      ::placeholder{ color:#e5e5e5; }
      button{ margin-top:20px; width:100%; background:#fff; color:#080710; padding:15px 0; font-size:18px; font-weight:600; border-radius:5px; cursor:pointer; border:none; }
      .msg{ text-align:center; margin-top:15px; }
      .links{ margin-top:20px; text-align:center; }
      .links a{ color:#23a2f6; text-decoration:none; font-size:14px; transition:0.3s; }
      .links a:hover{ text-decoration:underline; color:#ff512f; }
    </style>
</head>
<body>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <form method="POST">
        <h3>Βάλε νέο κωδικό</h3>
        <input type="password" name="password" placeholder="Νέος κωδικός" required>
        <button type="submit">Αποθήκευση</button>
    </form>
</body>
</html>
