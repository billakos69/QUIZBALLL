<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['uploaded_csv']) || !isset($_POST['mapping'])) {
    die("Κανένα αρχείο για εισαγωγή.");
}

$file = $_SESSION['uploaded_csv'];
$mapping = $_POST['mapping'];

if (($handle = fopen($file, 'r')) !== false) {
    $batch = [];
    $batchSize = 500;
    $first = true;

    while (($row = fgetcsv($handle)) !== false) {
        $data = ['id' => null, 'name' => null, 'image' => null];

        foreach ($mapping as $colIndex => $fieldName) {
            if ($fieldName && isset($row[$colIndex])) {
                $data[$fieldName] = trim($row[$colIndex]);
            }
        }

        // Πρέπει τουλάχιστον το name να υπάρχει
        if (empty($data['name'])) continue;

        $batch[] = [$data['name'], $data['image']];

        if (count($batch) >= $batchSize) {
            insertBatch($conn, $batch);
            $batch = [];
        }
    }

    if (count($batch) > 0) insertBatch($conn, $batch);
    fclose($handle);

    unset($_SESSION['uploaded_csv']);
    echo "<p style='color:lime;'>✅ Εισαγωγή ολοκληρώθηκε!</p>";
    echo '<a href="admin_quiz.php" style="color:#00cccc;">⬅ Επιστροφή</a>';
}

function insertBatch($conn, $rows) {
    $placeholders = [];
    $values = [];
    foreach ($rows as $r) {
        $placeholders[] = "(?, ?)";
        $values = array_merge($values, $r);
    }
    $sql = "INSERT INTO quiz_data (name, image) VALUES " . implode(',', $placeholders);
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('ss', count($rows)), ...$values);
    $stmt->execute();
    $stmt->close();
}
?>
