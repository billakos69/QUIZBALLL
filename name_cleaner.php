<?php
$output_lines = ""; // Αρχική τιμή για να μην υπάρχει undefined

if (isset($_POST['raw_text'])) {
    $input = $_POST['raw_text'];

    $lines = preg_split("/[\r\n]+/u", $input);
    $names = [];

    foreach ($lines as $line) {
        $clean = trim($line);

        // Πετάμε άδειες γραμμές ή θέσεις/άσχετες λέξεις
        if ($clean === '' || preg_match('/^(Επιθετικό|Σέντερ|Αριστερό|Δεξί|Τερματοφύλακας|Xad|ΔεΕΙ|Etomep|Σάντερ φορ|Αμυντικό χαφ|Στόπερ|Xa|Χαφ)/u', $clean)) continue;

        // Κρατάμε μόνο γράμματα και κενά
        $clean = preg_replace('/[^A-Za-zΑ-Ωα-ωΆ-Ώά-ώÉéèëïîçÇüÜñÑ\s\-\'\.]/u', '', $clean);
        $clean = trim($clean);

        if (mb_strlen($clean) > 1 && preg_match('/[A-Za-zΑ-Ωα-ω]/u', $clean)) {
            $names[] = $clean;
        }
    }

    $names = array_unique($names);

    // Δημιουργία τελικού output
    $output_lines = "";
    foreach ($names as $n) {
        $output_lines .= $n . ",\n";
    }
}
?>


<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<title>🧼 Καθαρισμός Ονομάτων Παικτών</title>
<style>
body { background:#1a1a1a; color:#fff; font-family:Arial; padding:20px; }
h1, h2 { color:#00cccc; }
textarea { width:100%; background:#222; color:#fff; padding:10px; border:none; font-size:14px; }
button { padding:10px 20px; background:#00cccc; border:none; cursor:pointer; color:#000; font-weight:bold; margin-top:10px; }
button:hover { background:#009999; color:#fff; }
.result-area { height:200px; }
.input-area { height:300px; }
</style>
</head>
<body>

<h1>🧼 Καθαρισμός Ονομάτων Παικτών</h1>

<form method="post">
    <textarea name="raw_text" class="input-area" placeholder="Επικόλλησε εδώ τα δεδομένα (χαοτικά)..."><?= htmlspecialchars($_POST['raw_text'] ?? '') ?></textarea>
    <br>
    <button type="submit">➡️ Καθάρισε</button>
</form>

<?php if (isset($names)): ?>
    <h2>✅ Μορφή με κάθε όνομα σε νέα γραμμή:</h2>
    <textarea readonly class="result-area" onclick="this.select();"><?= htmlspecialchars($output_lines) ?></textarea>

    <h2>✅ Μορφή σε μία γραμμή (έτοιμο για Μαζική Εισαγωγή):</h2>
    <textarea readonly class="result-area" onclick="this.select();"><?= htmlspecialchars($output_inline) ?></textarea>
<?php endif; ?>

</body>
</html>
