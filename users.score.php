<?php
session_start();
$userId = $_SESSION['user_id'] ?? 1; // προσωρινά user 1
$stmt = $pdo->prepare("SELECT score FROM user WHERE id=?");
$stmt->execute([$userId]);
$currentScore = $stmt->fetchColumn();
?>
<div class="scoreboard">
    Σκορ: <?= (int)$currentScore ?>
</div>

<style>
.scoreboard {
    position: fixed;
    top: 10px;
    right: 10px;
    background: #006e6d;
    color: #fff;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: bold;
}
</style>
