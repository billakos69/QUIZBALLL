<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email    = $conn->real_escape_string($_POST['email']);
    $phone    = $conn->real_escape_string($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO user (username, email, phone, password) 
            VALUES ('$username','$email','$phone','$password')";
    if ($conn->query($sql)) {
        $success = "✅ Εγγραφή επιτυχής! <a href='login.php'>Σύνδεση</a>";
    } else {
        $error = "❌ Σφάλμα: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Εγγραφή</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="icon" href="images/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <style>
      /* Το CSS όπως το έχεις ήδη */
      body{
          background-color: #080710;
          font-family: 'Poppins', sans-serif;
      }
      .background{
          width: 430px;
          height: 520px;
          position: absolute;
          transform: translate(-50%,-50%);
          left: 50%;
          top: 50%;
      }
      .background .shape{
          height: 200px;
          width: 200px;
          position: absolute;
          border-radius: 50%;
      }
      .shape:first-child{
          background: linear-gradient(#1845ad,#23a2f6);
          left: -80px;
          top: -80px;
      }
      .shape:last-child{
          background: linear-gradient(to right,#ff512f,#f09819);
          right: -30px;
          bottom: -80px;
      }
      form{
          height: auto;
          width: 400px;
          background-color: rgba(255,255,255,0.13);
          position: absolute;
          transform: translate(-50%,-50%);
          top: 50%;
          left: 50%;
          border-radius: 10px;
          backdrop-filter: blur(10px);
          border: 2px solid rgba(255,255,255,0.1);
          box-shadow: 0 0 40px rgba(8,7,16,0.6);
          padding: 50px 35px;
      }
      form h3{
          font-size: 32px;
          font-weight: 500;
          line-height: 42px;
          text-align: center;
          color: #fff;
      }
      label{
          display: block;
          margin-top: 20px;
          font-size: 16px;
          font-weight: 500;
          color: #fff;
      }
      input{
          display: block;
          height: 50px;
          width: 100%;
          background-color: rgba(255,255,255,0.07);
          border-radius: 3px;
          padding: 0 10px;
          margin-top: 8px;
          font-size: 14px;
          font-weight: 300;
          color: #fff;
      }
      ::placeholder{ color: #e5e5e5; }
      button{
          margin-top: 30px;
          width: 100%;
          background-color: #ffffff;
          color: #080710;
          padding: 15px 0;
          font-size: 18px;
          font-weight: 600;
          border-radius: 5px;
          cursor: pointer;
          border: none;
      }
      .msg { text-align:center; margin-top:15px; }
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
    </style>
</head>
<body>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <form method="POST" action="">
        <h3>Εγγραφή</h3>

        <?php if(isset($success)) echo "<p class='msg' style='color:lightgreen;'>$success</p>"; ?>
        <?php if(isset($error)) echo "<p class='msg' style='color:red;'>$error</p>"; ?>

        <label for="username">Όνομα χρήστη</label>
        <input type="text" name="username" placeholder="Username" required>

        <label for="email">Email</label>
        <input type="email" name="email" placeholder="Email" required>

        <label for="phone">Τηλέφωνο</label>
        <input type="text" name="phone" placeholder="Phone">

        <<label for="password">Κωδικός</label>
<div class="password-wrapper">
    <input type="password" id="password" name="password" placeholder="Password" required>
    <i id="togglePassword" class="fas fa-eye"></i>
</div>


        <button type="submit">Εγγραφή</button>
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
