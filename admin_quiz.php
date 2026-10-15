<?php
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// ------------------ CSV PREVIEW ------------------
$previewData = [];
if (isset($_FILES['csv_file']) && is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    $_SESSION['uploaded_csv'] = file_get_contents($file);

    if (($handle = fopen($file, 'r')) !== false) {
        $rowCount = 0;
        while (($row = fgetcsv($handle)) !== false && $rowCount < 20) {
            $previewData[] = $row;
            $rowCount++;
        }
        fclose($handle);
    }
}

// ------------------ Helper Functions ------------------
function insertBatch($conn, $rows) {
    if (empty($rows)) return ['added'=>0,'skipped'=>0];
    $added = 0;
    $skipped = 0;

    foreach ($rows as $r) {
        $name = $r[0];
        $image = $r[1];

        // Έλεγχος αν υπάρχει ήδη
        $stmt = $conn->prepare("SELECT id FROM quiz_data WHERE name=? LIMIT 1");
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $skipped++;
        } else {
            // Εισαγωγή
            $insert = $conn->prepare("INSERT INTO quiz_data (name, image) VALUES (?, ?)");
            $insert->bind_param('ss', $name, $image);
            $insert->execute();
            $insert->close();
            $added++;
        }
        $stmt->close();
    }

    return ['added'=>$added,'skipped'=>$skipped];
}


// ------------------ Handle POST ------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---------------- Manual Import ----------------
    if (!empty($_POST['action']) && $_POST['action'] === 'import_manual') {
        $bulk_text = trim($_POST['bulk_text'] ?? '');
        
        if ($bulk_text !== '') {
            $lines = explode("\n", $bulk_text);
            $rows = [];
            
            foreach ($lines as $line) {
                $parts = array_map('trim', explode(',', $line));
                if (count($parts) >= 2 && $parts[0] !== '' && $parts[1] !== '') {
                    $rows[] = [$parts[0], $parts[1]];
                }
            }

            if (!empty($rows)) {
                // Η insertBatch πρέπει να επιστρέφει ['added'=>x, 'skipped'=>y]
                $result = insertBatch($conn, $rows);

                $_SESSION['message'] = "✅ ".$result['added']." εγγραφές προστέθηκαν στη βάση!";
                if ($result['skipped'] > 0) {
                    $_SESSION['message'] .= " ⚠️ ".$result['skipped']." εγγραφές υπήρχαν ήδη και παραλείφθηκαν.";
                }

            } else {
                $_SESSION['message'] = "⚠️ Δεν βρέθηκαν έγκυρες γραμμές.";
            }

        } else {
            $_SESSION['message'] = "⚠️ Η περιοχή εισαγωγής είναι άδεια.";
        }

        // Redirect για να μην επανασταλούν τα δεδομένα μετά refresh
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }




    // ---------------- Save ----------------
    if (!empty($_POST['action']) && $_POST['action'] === 'save' && isset($_POST['edit_id'])) {
        $ids = $_POST['edit_id'];
        $names = $_POST['edit_name'] ?? [];
        $images = $_POST['edit_image'] ?? [];
        $logo_ids = $_POST['category_3'] ?? [];

        foreach ($ids as $i => $id) {
            $id = (int)$id;
            $name = trim($names[$i] ?? '');
            $image = trim($images[$i] ?? '');
            $category_id = in_array($id, $logo_ids) ? 3 : 0;

            $stmt = $conn->prepare("UPDATE quiz_data SET name=?, image=?, category_id=? WHERE id=?");
            $stmt->bind_param('ssii', $name, $image, $category_id, $id);
            $stmt->execute();
            $stmt->close();
        }
        $_SESSION['message'] = "✅ Όλες οι εγγραφές ενημερώθηκαν!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    // ---------------- Delete Selected ----------------
    if (!empty($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['delete_ids'])) {
        $delete_ids = $_POST['delete_ids'];
        $placeholders = implode(',', array_fill(0, count($delete_ids), '?'));
        $types = str_repeat('i', count($delete_ids));
        $stmt = $conn->prepare("DELETE FROM quiz_data WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$delete_ids);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "✅ ".count($delete_ids)." εγγραφές διαγράφηκαν!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// ---------------- Delete Single ----------------
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM quiz_data WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "✅ Η εγγραφή διαγράφηκε!";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// ---------------- Load DB Data ----------------
$quizzes = $conn->query("SELECT * FROM quiz_data ORDER BY id DESC");

// ---------------- Messages ----------------
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<title>Admin Quiz Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
body { background-color:#121212; color:#e0e0e0; font-family:'Poppins', sans-serif; padding:20px; line-height:1.5; }
h1,h2 { color:#00e5ff; margin-bottom:15px; }
button { padding:8px 18px; border:none; border-radius:6px; font-weight:600; cursor:pointer; transition:0.2s; }
button:hover { opacity:0.85; }
button[name="action"][value="save"] { background:#00cccc; color:#000; }
button[name="action"][value="delete"] { background:#ff4444; color:#fff; }
.table-container { max-height:80vh; overflow:auto; border:1px solid #333; border-radius:10px; }
table { width:100%; border-collapse:collapse; min-width:700px; }
th, td { padding:12px 15px; border:1px solid #444; text-align:left; white-space:nowrap; }
th { background:#00cccc; color:#000; position:sticky; top:0; z-index:2; }
tr:nth-child(even) { background:#1e1e1e; }
input[type="text"], textarea, select { width:100%; padding:8px 10px; margin:3px 0; border:1px solid #555; border-radius:6px; background:#1a1a1a; color:#fff; font-size:14px; }
input[type="text"]:focus, textarea:focus, select:focus { outline:none; border-color:#00e5ff; box-shadow:0 0 5px #00e5ff; }
img.thumb { max-height:60px; max-width:120px; border-radius:6px; object-fit:cover; display:block; margin-bottom:5px; }
input[type="checkbox"] { transform:scale(1.2); margin-right:5px; }
textarea { resize:vertical; min-height:150px; }
a { color:#00cccc; text-decoration:none; }
a:hover { text-decoration:underline; }
/* Bulk Insert */
.bulk-insert-form { width:100%; max-width:100%; margin:0; padding:0; display:flex; flex-direction:column; gap:10px; }
.bulk-insert-form textarea { width:100%; min-height:150px; resize:vertical; padding:12px; border-radius:8px; border:1px solid #555; background:#1a1a1a; color:#fff; font-size:14px; font-family:'Poppins',sans-serif; box-sizing:border-box; }
.bulk-insert-form button { width:100%; padding:12px; border-radius:8px; border:none; background:#00cccc; color:#000; font-weight:600; cursor:pointer; box-sizing:border-box; }
.message { margin:10px 0; font-weight:600; }
</style>
</head>
<body>

<a href="name_cleaner.php"><button> 🧼 Καθαρισμός Ονομάτων</button></a>
<h1>📊 Admin Panel - Quiz</h1>

<?php if($message): ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>

<!-- CSV Upload Preview -->
<form method="post" enctype="multipart/form-data">
    <input type="file" name="csv_file" accept=".csv" required>
    <button type="submit">Προεπισκόπηση CSV</button>
</form>

<?php if (!empty($previewData)): ?>
<form method="post" action="import_csv.php">
    <p>🧭 Επέλεξε πεδία για κάθε στήλη:</p>
    <table>
        <thead>
        <tr>
            <?php foreach ($previewData[0] as $i => $col): ?>
                <th>
                    <select name="mapping[<?= $i ?>]">
                        <option value="">— Αγνόηση —</option>
                        <option value="name">Όνομα</option>
                        <option value="image">Εικόνα</option>
                        <option value="id">ID</option>
                    </select>
                </th>
            <?php endforeach; ?>
        </tr>
        <tr>
            <?php foreach ($previewData[0] as $i => $col): ?>
                <th>Στήλη <?= $i+1 ?></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
            <?php foreach ($previewData as $row): ?>
            <tr>
                <?php foreach ($row as $val): ?>
                <td><?= htmlspecialchars($val) ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button type="submit">✅ Εισαγωγή στη βάση</button>
</form>
<?php endif; ?>

<hr>

<!-- Manual Bulk Insert -->
<h2>✍️ Μαζική Χειροκίνητη Εισαγωγή</h2>
<form method="post" class="bulk-insert-form">
    <textarea name="bulk_text" placeholder="π.χ. Batman, https://batman.jpg&#10;Superman, https://superman.jpg"><?= htmlspecialchars($_POST['bulk_text'] ?? '') ?></textarea>
    <button type="submit" name="action" value="import_manual">✅ Import</button>
</form>

<hr>

<h2 style="display:flex; align-items:center; justify-content:space-between;">
📋 Εγγραφές στη Βάση
<div>
    <button type="submit" form="bulk_form" name="action" value="save" style="background:#00cccc; color:#000;">💾 Μαζική Αποθήκευση</button>
    <button type="submit" form="bulk_form" name="action" value="delete" style="background:red; color:#fff;">🗑️ Διαγραφή Επιλεγμένων</button>
</div>
</h2>

<input type="text" id="live_search" placeholder="Αναζήτηση ομάδας..." style="padding:8px; width:250px; border-radius:4px; border:none;">

<form method="post" id="bulk_form">
<div class="table-container">
<table>
<thead>
<tr>
    <th><input type="checkbox" id="select_all"></th>
    <th>ID</th>
    <th>Όνομα</th>
    <th>Εικόνα</th>
    <th>LOGO</th>
    <th>Ενέργειες</th>
</tr>
</thead>
<tbody>
<?php while($quiz = $quizzes->fetch_assoc()): ?>
<tr>
    <td><input type="checkbox" class="select_row" name="delete_ids[]" value="<?= $quiz['id'] ?>"></td>
    <td><?= $quiz['id'] ?><input type="hidden" name="edit_id[]" value="<?= $quiz['id'] ?>"></td>
    <td><input type="text" name="edit_name[]" value="<?= htmlspecialchars($quiz['name']) ?>"></td>
    <td>
        <?php if (!empty($quiz['image'])): ?>
        <img src="<?= htmlspecialchars($quiz['image']) ?>" class="thumb"><br>
        <?php endif; ?>
        <input type="text" name="edit_image[]" value="<?= htmlspecialchars($quiz['image'] ?? '') ?>">
    </td>
    <td style="text-align:center;">
        <input type="checkbox" name="category_3[]" value="<?= $quiz['id'] ?>" <?= ($quiz['category_id']==3 ? 'checked' : '') ?>>
    </td>
    <td>
        <button type="button" onclick="if(confirm('Θέλεις σίγουρα να διαγράψεις την εγγραφή;')) window.location='?delete=<?= $quiz['id'] ?>'">🗑️</button>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('live_search');
    const rows = document.querySelectorAll('#bulk_form tbody tr');

    input.addEventListener('input', function() {
        const term = this.value.trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
        rows.forEach(row => {
            const nameInput = row.querySelector('input[name="edit_name[]"]');
            let text = nameInput ? nameInput.value.trim().toLowerCase() : '';
            text = text.normalize('NFD').replace(/[\u0300-\u036f]/g,'');
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });

    const selectAll = document.getElementById('select_all');
    const checkboxes = document.querySelectorAll('.select_row');
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    });
});
</script>

</body>
</html>
