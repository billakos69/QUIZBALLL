<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Φόρτωση στοιχείων χρήστη
$stmt = $conn->prepare("SELECT username, email, phone, highscore FROM user WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$username = $user['username'] ?? '';
$email    = $user['email'] ?? '';
$phone    = $user['phone'] ?? '';
$highscore = $user['highscore'] ?? 0;

$message = "";
$success = false;

// Ενημέρωση στοιχείων
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $newUsername = trim($_POST['username'] ?? '');
        $newEmail    = trim($_POST['email'] ?? '');
        $newPhone    = trim($_POST['phone'] ?? '');
        $newPassword = trim($_POST['password'] ?? '');

        if ($newUsername !== "" && $newEmail !== "" && $newPhone !== "") {
            $query = "UPDATE user SET username=?, email=?, phone=?";
            $params = [$newUsername, $newEmail, $newPhone];
            $types  = "sss";

            if ($newPassword !== "") {
                $query .= ", password=?";
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $params[] = $hashed;
                $types .= "s";
            }

            $query .= " WHERE id=?";
            $params[] = $userId;
            $types .= "i";

            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();

            $_SESSION['user'] = $newUsername;
            $username = $newUsername;
            $email    = $newEmail;
            $phone    = $newPhone;

            $message = "Τα στοιχεία ενημερώθηκαν με επιτυχία.";
            $success = true;
        } else {
            $message = "Όλα τα πεδία (εκτός κωδικού) είναι υποχρεωτικά.";
        }
    }

    // Διαγραφή λογαριασμού
    if (isset($_POST['delete'])) {
        $stmt = $conn->prepare("DELETE FROM user WHERE id=?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        session_destroy();
        header("Location: signup.php?msg=account_deleted");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<title>Ο Λογαριασμός μου - QuizBall</title>
  <link rel="icon" href="images/logo.png" type="image/png">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
body { margin:0; font-family:'Poppins',sans-serif; background:#0baa93; color:#fff; }
.container { max-width:700px; margin:60px auto; padding:40px; background: linear-gradient(145deg, #0baa93, #9fd9df); border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,0.7); border:2px solid #ff8431; }
h1 { text-align:center; margin-bottom:25px; color:#ff8431; text-shadow: 0 0 10px #0baa93; }
.highscore { background: linear-gradient(135deg, #0baa93, #ff8431); color:#fff; font-weight:600; font-size:1.2rem; padding:15px; border-radius:15px; text-align:center; margin-bottom:25px; box-shadow:0 0 15px rgba(255,132,49,0.6); border:2px solid #9fd9df; }
.form-group { margin-bottom:20px; }
label { display:block; margin-bottom:6px; font-weight:500; color:#fff; text-shadow:0 0 4px #0baa93; }
input { width:100%; padding:12px 15px; border-radius:12px; border:2px solid #9fd9df; background: rgba(0,0,0,0.15); color:#fff; font-size:14px; transition:0.3s; }
input:focus { outline:none; border-color:#ff8431; box-shadow:0 0 12px #ff8431; }
.password-wrapper { position:relative; }
.password-wrapper input { width:100%; padding-right:40px; }
.password-wrapper i { position:absolute; right:10px; top:50%; transform:translateY(-50%); cursor:pointer; color:#fff; transition:0.3s; }
.password-wrapper i:hover { color:#ff8431; }
.btn { padding:12px 22px; border:none; border-radius:12px; cursor:pointer; font-weight:600; transition:0.3s; box-shadow:0 4px 12px rgba(0,0,0,0.5); }
.btn-save { background: linear-gradient(90deg, #0baa93, #9fd9df); color:#000; }
.btn-save:hover { background: linear-gradient(90deg, #ff8431, #0baa93); color:#fff; transform:scale(1.05); }
.btn-delete { background: linear-gradient(90deg, #d11a2a, #ff8431); color:#fff; }
.btn-delete:hover { background: linear-gradient(90deg, #ff8431, #d11a2a); transform:scale(1.05); }
.msg { padding:12px 15px; border-radius:12px; margin-bottom:20px; font-weight:500; }
.msg.success { background:#003300; color:#00ff99; }
.msg.error { background:#330000; color:#ff4d4d; }
.actions { display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-top:20px; }
a.back { display:block; text-align:center; margin-top:20px; color:#ff8431; text-decoration:none; font-weight:bold; }
a.back:hover { text-decoration:underline; color:#0baa93; }
</style>

</head>
<body>
<div class="container">
<h1>👤 Ο Λογαριασμός μου</h1>

<div class="highscore">🏆 Highscore: <?= htmlspecialchars($highscore) ?></div>

<?php if ($message): ?>
  <p class="msg <?= $success ? 'success' : 'error' ?>"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="post">
  <div class="form-group">
    <label>Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required>
  </div>

  <div class="form-group">
    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
  </div>

  <div class="form-group">
    <label>Κινητό Τηλέφωνο</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" required>
  </div>

  <div class="form-group">
    <label>Νέος Κωδικός (προαιρετικά)</label>
    <div class="password-wrapper">
      <input type="password" name="password" id="password" placeholder="•••••••">
      <i id="togglePassword" class="fas fa-eye"></i>
    </div>
  </div>

  <div class="actions">
    <button type="submit" name="update" class="btn btn-save">💾 Αποθήκευση</button>
    <button type="submit" name="delete" class="btn btn-delete" onclick="return confirmDelete()">🗑️ Διαγραφή Λογαριασμού</button>
  </div>
</form>

<a href="index.php" class="back">← Επιστροφή στην Αρχική</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password');

    if(togglePassword && passwordInput){
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.classList.toggle('fa-eye-slash');
        });
    }
});
</script>


</body>
</html>
