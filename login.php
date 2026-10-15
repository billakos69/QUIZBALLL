<?php
include 'config.php';

$error = '';

// Admin credentials (προαιρετικά, καλύτερα από βάση)
$admin_email = "admin@admin.com";
$admin_pass  = "admin";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // --- ADMIN CHECK ---
    if($email === $admin_email && $password === $admin_pass){
        $_SESSION['user_id'] = 0;          // Admin ID
        $_SESSION['user'] = "Admin";
        $_SESSION['is_admin'] = 1;         // σημαία admin
        header("Location: admin_panel.php");
        exit;
    }

    // --- USER CHECK ---
    $sql = "SELECT * FROM user WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user['username'];
            $_SESSION['is_admin'] = 0;
            header("Location: index.php");
            exit;
        } else {
            $error = "❌ Λάθος κωδικός";
        }
    } else {
        $error = "❌ Δεν βρέθηκε χρήστης";
    }
}
?>


<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
      form h3{ font-size:32px; text-align:center; }
      label{ display:block; margin-top:20px; font-size:16px; }
      input{ display:block; width:100%; height:50px; background-color: rgba(255,255,255,0.07); border-radius: 3px; padding:0 10px; margin-top:8px; font-size:14px; color:#fff; }
      ::placeholder{ color:#e5e5e5; }
      button{ margin-top:30px; width:100%; background:#fff; color:#080710; padding:15px 0; font-size:18px; font-weight:600; border-radius:5px; cursor:pointer; border:none; }
      .msg{ text-align:center; margin-top:15px; }
      .links{ margin-top:20px; text-align:center; }
      .links a{ color:#23a2f6; text-decoration:none; margin:0 10px; font-size:14px; transition:0.3s; }
      .links a:hover{ text-decoration:underline; color:#ff512f; }
      .password-wrapper {
    position: relative;
    width: 100%;
    margin-top: 8px;
}

.password-wrapper input {
    display: block;
    width: 93%;
    height: 50px;
    background-color: rgba(255,255,255,0.07);
    border-radius: 3px;
    padding: 0 40px 0 10px; /* space for eye icon */
    font-size: 14px;
    color: #000000ff;
    border: none;
}

.password-wrapper i {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #000000ff;
    transition: color 0.3s;
}

.password-wrapper i:hover {
    color: #000000ff;
}
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
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <form method="POST" action="">
        <h3>Σύνδεση</h3>

        <?php if(isset($error)) echo "<p class='msg' style='color:red;'>$error</p>"; ?>

        <label for="email">Email</label>
        <input type="email" name="email" placeholder="Email" required>

        <label for="password">Κωδικός</label>
<div class="password-wrapper">
    <input type="password" id="password" name="password" placeholder="Password" required>
    <i id="togglePassword" class="fas fa-eye"></i>
</div>


        <button type="submit">Σύνδεση</button>

        <div class="links">
    <a href="reset_request.php">Ξέχασα τον κωδικό</a> | 
    <a href="forgot_email.php">Ξέχασα το email</a>
</div>

    </form>
    <script>
const togglePassword = document.querySelector('#togglePassword');
const passwordInput = document.querySelector('#password');

togglePassword.addEventListener('click', () => {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    togglePassword.classList.toggle('fa-eye-slash');
});
</script>

</body>
</html>
