<?php
session_start();

$DB_HOST = 'localhost';
$DB_NAME = 'movies_db';
$DB_USER = 'root';
$DB_PASS = '';

$isLoggedIn = isset($_SESSION['user']);
$username   = $isLoggedIn ? $_SESSION['user'] : null;

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die('Σφάλμα DB: ' . htmlspecialchars($e->getMessage()));
}

$stmt = $pdo->query("SELECT id, name, image FROM categories ORDER BY id");
$categories = $stmt->fetchAll();

$user_id = $_SESSION['user_id'] ?? null;
$highscore = 0;
if ($user_id) {
    $stmt = $pdo->prepare("SELECT highscore FROM user WHERE id=?");
    $stmt->execute([$user_id]);
    $highscore = (int)$stmt->fetchColumn();
}

$difficultyLabels = ['easy'=>'Easy','medium'=>'Mid','hard'=>'Hard'];
$categoryColors = [1=>'#8d7863',2=>'#1258c3',3=>'#892302',4=>'#00553a',5=>'#ba470e',6=>'#1ada28',7=>'#89088dff',8=>'#ff0000ff',9=>'#635c00ff',10=>'#8f004fff'];
$mode = $_SESSION['selected_mode'] ?? 'single';
$selected = $_SESSION['selected_categories'] ?? [];

// Προκαθορισμένα diffs
$allDiffs = [
    1 => ['easy'=>1,'medium'=>1,'hard'=>1],
    2 => ['easy'=>1,'medium'=>1,'hard'=>1],
    3 => ['medium'=>2],
    4 => ['hard'=>2],
    5 => ['easy'=>1,'medium'=>1],
    6 => ['hard'=>2],
    7 => ['medium'=>2],
    8 => ['hard'=>2],
    9 => ['medium'=>2],
    10 => ['medium'=>2]
];

$allowedDiffs = [];
foreach ($selected as $catId) {
    if (isset($allDiffs[$catId])) {
        $allowedDiffs[$catId] = $allDiffs[$catId];
    }
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<title>1v1 Quiz - Single Player</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="1v1.css">
<link rel="icon" href="images/logo.png" type="image/png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
<style>
body{font-family:Arial,sans-serif;background:#0b2a33;margin:0;padding-top:70px;display:flex;flex-direction:column;min-height:100vh;color:#eef;}
#main-content{display:flex;flex-direction:column;align-items:center;width:100%;margin-top:20px;}
#scoreboard{background:#222;color:#fff;padding:15px 20px;border-radius:12px;font-weight:bold;text-align:center;width:320px;margin-bottom:20px;box-shadow:0 5px 15px rgba(0,0,0,0.4);}
.quiz-board {display:flex; flex-direction:column; gap:12px; padding:20px; background:#0e3d4a; border-radius:16px; width:90%; max-width:900px;}
.quiz-row {display:flex; gap:12px; justify-content:center;}
.answer-btn.locked {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
}
#feedback {
    margin-top: 10px;
    min-height: 24px; /* για να υπάρχει χώρος για το μήνυμα */
    font-weight: bold;
    text-align: center;
}

.congrats {
  color: #2ecc71;
  font-size: 22px;
  font-weight: 900;
  animation: pop 0.6s ease forwards;
}

@keyframes pop {
  0%   { transform: scale(0.5); opacity: 0; }
  60%  { transform: scale(1.2); opacity: 1; }
  100% { transform: scale(1); }
}

.quiz-item {
    flex: 1;
    min-width: 100px;
    height: 120px; /* μεγαλύτερο για να φαίνεται η εικόνα */
    display: flex;
    flex-direction: column; /* εικόνα πάνω, τίτλος κάτω */
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #fff;
    border-radius: 12px;
    padding: 8px;
    cursor: pointer;
    border: 1px solid rgba(255,255,255,.06);
    transition: .2s;
    text-align: center;
    overflow: hidden;
}

.quiz-item .quiz-img {
    width: 100%;      /* γεμίζει όλο το πλάτος του container */
    height: 70px;     /* σταθερό ύψος για uniform εμφάνιση */
    object-fit: contain; /* εμφανίζει όλη την εικόνα χωρίς να κοπεί */
    border-radius: 8px;
    background: rgba(0,0,0,0.1); }
.quiz-item .title {font-weight:700;}
.quiz-item span.badge {position:absolute; right:10px; top:50%; transform:translateY(-50%); background:#f1f5f9; color:#0b2a33; font-weight:800; padding:6px 10px; border-radius:10px;}

/* Modals */
.modal {
  display:none;
  position:fixed;
  top:50%;left:50%;
  transform:translate(-50%,-50%);
  z-index:1005;
  background:#fff;
  color:#0b2a33;
  padding:20px;border-radius:20px;
  width:90%;max-width:720px;
  box-shadow:0 12px 25px rgba(0,0,0,0.5);
  flex-direction:column;text-align:center;
}

#hintModal {
display: none; /* αρχικά */
  opacity: 0;
  transition: opacity 0.3s ease; 
   position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 2000;
  background: rgba(255, 255, 255, 0.95);
  padding: 20px;
  border-radius: 16px;
  width: 90%;
  max-width: 520px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.5);
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  transition: opacity 0.3s ease;
  opacity: 0;
}

#hintModal.show {
  display: flex;
  opacity: 1;
}

.modal .title {font-size:20px;font-weight:800;margin-bottom:10px;}
#top5Container .answer-slot{width:100%;padding:12px;margin:6px 0;border-radius:8px;font-size:18px;font-weight:bold;text-align:left;padding-left:20px;box-sizing:border-box;transition:0.3s;}
.green{background:#2ecc71;color:white;}
.red{background:#e74c3c;color:white;}
.revealed{transform:scale(1.02);}
.shake{animation:shake 0.3s;}
@keyframes shake{0%{transform:translateX(0);}25%{transform:translateX(-5px);}50%{transform:translateX(5px);}75%{transform:translateX(-5px);}100%{transform:translateX(0);}}

/* Buttons */
.btn {padding:8px 14px;border-radius:8px;border:none;cursor:pointer;background:#2c3e50;color:#fff;font-weight:700;}
.btn.secondary{background:#6b7280;}
.btn.important{background:#f39c12;color:#0b2a33;}
.btn.disabled{background:#bfc3c7;color:#6b6f72;cursor:not-allowed;}

/* Small helpers */
#helpersInfo,#skipInfo{font-size:13px;color:#cfeffa;margin-top:8px;}
.answer-slot{background:#f1f5f9;color:#0b2a33;}
/* Μήνυμα με fade-in */
.message {
  opacity: 0;
  transform: translateY(-10px);
  transition: opacity 0.5s ease, transform 0.5s ease;
  font-size: 18px;
  font-weight: bold;
  margin-top: 10px;
  text-align: center;
}

.message.show {
  opacity: 1;
  transform: translateY(0);
}

/* Συγχαρητήρια με confetti-like effect */
.congrats {
  color: #2ecc71;
  font-size: 22px;
  font-weight: 900;
  animation: pop 0.6s ease forwards;
}

@keyframes pop {
  0%   { transform: scale(0.5); opacity: 0; }
  60%  { transform: scale(1.2); opacity: 1; }
  100% { transform: scale(1); }
}

/* --- Mobile / Small Screens --- */
@media (max-width: 720px) {
    body {
        padding-top: 60px; /* μικρότερο για κινητά */
    }

   #scoreboard {
    background: linear-gradient(135deg, #1abc9c, #16a085);
    color: #fff;
    padding: 20px 25px;
    border-radius: 16px;
    font-weight: bold;
    text-align: center;
    width: 320px;
    margin-bottom: 20px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.4);
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

#scoreboard:hover {
    transform: scale(1.05);
    box-shadow: 0 15px 25px rgba(0,0,0,0.5);
}

#scoreboard span {
    display: inline-block;
    font-size: 24px;
    margin: 5px 0;
    transition: transform 0.2s ease;
}

#scoreboard span.update {
    animation: pop 0.5s ease forwards;
}

@keyframes pop {
    0% { transform: scale(1); }
    50% { transform: scale(1.5); }
    100% { transform: scale(1); }
}


    .quiz-board {
        width: 95%;
        padding: 15px;
        gap: 10px;
    }

    .quiz-row {
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .quiz-item {
        width: 92%;
        height: auto;
        min-height: 100px;
        padding: 10px;
    }

    .quiz-item .quiz-img {
        height: 60px;
    }

    .quiz-item .title {
        font-size: 14px;
    }

    .badge {
        padding: 4px 8px;
        font-size: 12px;
    }

    /* Modals */
    .modal {
        width: 95%;
        padding: 15px;
    }

    #top5Input, #searchInput {
        width: 95%;
        font-size: 14px;
        padding: 8px;
    }

    #helpersInfo {
        font-size: 12px;
    }

    #helpBtns button {
        padding: 6px 10px;
        font-size: 13px;
    }

    /* Navbar */
    nav {
        padding: 0 10px;
    }

    nav .logo {
        font-size: 22px;
        line-height: 50px;
    }

    nav ul li a {
        font-size: 14px;
        line-height: 50px;
        padding: 6px 8px;
    }

    nav ul ul {
        top: 50px;
    }

    nav ul ul li a {
        font-size: 13px;
        line-height: 38px;
    }

    .user-menu i {
        font-size: 22px;
        line-height: 50px;
    }

    .user-menu .dropdown {
        position: relative;
        width: 100%;
        top: 0;
        right: 0;
    }
}


.green{background:#2ecc71;color:white;}
.red{background:#e74c3c;color:white;}

</style>
</head>
<body>

<!-- MENU -->
<?php include 'nav.html'; ?>


<div id="main-content">
  <div id="scoreboard">Σκορ: <span id="score">0</span><br>Highscore: <span id="highscore"><?= $highscore ?></span>
    <div id="helpersInfo">50/50: 1 | Skip: 2</div>
  </div>
<div class="quiz-board">
<?php foreach ($categories as $cat): ?>
<div class="quiz-row">
<?php $diffs = $allowedDiffs[$cat['id']] ?? []; foreach($diffs as $diff => $count): for($i=0;$i<$count;$i++): ?>
<div class="quiz-item" style="background:<?= $categoryColors[$cat['id']] ?>" data-cat-id="<?= (int)$cat['id'] ?>" data-diff="<?= $diff ?>">
    <img src="<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="quiz-img">
    <div class="title"><?= htmlspecialchars($cat['name']) ?></div>
    <span class="badge"><?= $difficultyLabels[$diff] ?></span>
</div>

<?php endfor; endforeach; ?>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- Top5 Modal -->
<div id="top5Container" class="modal">
<div id="top5Msg" style="margin-top:12px; font-weight:bold; color:#0b2a33;">
    <div id="feedback" class="message"></div>

</div>
  <button id="closeTop5Btn" style="float:right;border:none;background:transparent;font-size:18px;cursor:pointer;">✖</button>
  <div class="title">Top 5 - Ερώτηση</div>
  <h2 id="top5Question">Φόρτωση ερώτησης...</h2>
<div style="position:relative; width:80%; margin:0 auto;">
  <input type="text" id="top5Input" placeholder="Γράψε απάντηση..."
         style="width:100%;padding:10px;border-radius:8px;border:1px solid #ccc;margin-bottom:15px;">
  <div id="top5AutocompleteList" class="autocomplete-list" style="display:none;"></div>
</div>

  <div id="top5Slots" style="width:100%;max-width:540px;margin:0 auto;">
  <div class="answer-slot green" data-index="0">1.</div>
  <div class="answer-slot green" data-index="1">2.</div>
  <div class="answer-slot green" data-index="2">3.</div>
  <div class="answer-slot green" data-index="3">4.</div>
  <div class="answer-slot green" data-index="4">5.</div>
  <div class="answer-slot red" id="wrongSlot">✘</div>
</div>

  <div style="margin-top:12px;">
    <button id="onePointBtn" class="btn important" style="display:none;">Πάρε 1 πόντο</button>
    <button id="submitBtn" class="btn">Απάντησε</button>
  </div>
</div>

<!-- Quiz Modal -->
<div id="quizModal" class="modal" style="max-width:600px;">
  <div class="title" id="qTitle">Φόρτωση...</div>
  <img id="qImage" src="" style="max-width:420px; display:none; margin:10px auto; border-radius:12px; object-fit:contain;">
<div style="position:relative; width:80%; margin:0 auto;">
  <input id="searchInput" type="text" placeholder="Απάντησε εδώ..." autocomplete="off"
         style="width:100%;padding:10px;border-radius:8px;border:1px solid #ccc;">
  <div id="autocompleteList" class="autocomplete-list" style="display:none;"></div>
</div>
  <datalist id="answersList"></datalist>


  <div id="helpBtns" style="display:flex;gap:8px;justify-content:center;margin:10px 0;flex-wrap:wrap;">
  <button id="fiftyBtn" class="btn">50/50</button>
  <button id="hintBtn" class="btn">Hint</button>
   </div>


  
    <div style="margin-top:10px;">
        <button id="submitQuizBtn" class="btn">Απάντηση</button>
        <button id="closeQuizBtn" class="btn secondary">Κλείσιμο</button>
    </div>

    <div id="quizFeedback" class="message"></div> <!-- Αλλαγή id εδώ -->
</div>
<div id="feedback" style="margin-top:10px;"></div>

<!-- Fifty Modal small -->
<div id="fiftyModal" class="modal" style="max-width:420px;display:none;">
    <div class="title">50/50</div>
    <div id="fiftyOptions" style="display:flex;flex-direction:column;gap:10px;margin:15px 0;"></div>
    <div style="margin-top:8px;"><button onclick="closeFiftyModal()" class="btn secondary">Κλείσιμο</button></div>
</div>

<!-- Hint Modal -->
<div id="hintModal" class="modal" style="max-width:520px;">
  <div class="title">Hint</div>
  <p id="hintText"></p>
  <img id="hintImage" src="" style="max-width:100%; display:none; margin:10px auto; border-radius:8px;">
  <button onclick="closeHintModal()" class="btn secondary">Κλείσιμο</button>
</div>


<!-- End game modal -->
<div id="endGameModal" class="modal" style="max-width:520px;">
  <div class="title">Τέλος Παιχνιδιού</div>
  <div id="endGameText" style="font-size:18px;margin:10px 0;"></div>
  <div style="margin-top:12px;">
    <button id="playAgainBtn" class="btn important">Restart</button>
    <a href="index.php" class="btn secondary" style="text-decoration:none;display:inline-block;margin-left:8px;">Πίσω στο Μενού</a>
  </div>
</div>
<script src="navbar.js"></script>
<script src="top5.js"></script>
<script src="imageblocker.js"></script>

<script>
// --- Global Variables ---
let top5Answers = [];
let foundCount = 0;
let wrongCount = 0;
let scores = { player1: 0, player2: 0 };
let currentPlayer = 'player1';
let answeredIds = new Set();
let currentQuestion = null;
let currentTop5Cat = null;
let currentTop5Diff = null;
let helpers = { fifty: true, hint: true };
let used5050Ids = new Set();

function updateScoreboard() {
    const scoreEl = document.getElementById('score');
    const highscoreEl = document.getElementById('highscore');

    // Ανανεώνουμε σκορ
    scoreEl.textContent = scores[currentPlayer];
    scoreEl.classList.add('update');
    setTimeout(()=> scoreEl.classList.remove('update'), 500);

    // Ανανεώνουμε highscore
    fetch('update_score.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'score=' + encodeURIComponent(scores[currentPlayer])
    })
    .then(r => r.json())
    .then(d => {
        if(d.highscore !== undefined){
            highscoreEl.textContent = d.highscore;
            highscoreEl.classList.add('update');
            setTimeout(()=> highscoreEl.classList.remove('update'), 500);
        }
    });

    // Βοηθήματα
    const helpersInfo = document.getElementById('helpersInfo');
    let fiftyText = helpers.fifty ? '50/50: 1' : '50/50: 0';
    let hintText  = helpers.hint  ? 'Hint: 1'  : 'Hint: 0';
    helpersInfo.textContent = `${fiftyText} | ${hintText}`;
}

// --- Helpers ---
function normalizeInput(str){
    return str
        .toLowerCase()
        .normalize("NFD")           // σπάει χαρακτήρες με τόνους
        .replace(/[\u0300-\u036f]/g, "") // αφαιρεί τόνους
        .replace(/[.,:;'"?!«»–—\-\/]/g, " ") // αφαιρεί σημεία στίξης
        .replace(/\s+/g, " ")       // πολλαπλά κενά → 1
        .trim();
}


function resetTop5Slots() {
    foundCount = 0;
    wrongCount = 0;
    document.querySelectorAll('#top5Slots .answer-slot').forEach(s => {
        if(s.id !== 'wrongSlot') {
            s.textContent = (parseInt(s.dataset.index)+1)+'.';
            s.classList.remove('revealed','shake','red','green');
        }
    });
    const wrongSlot = document.getElementById('wrongSlot');
    wrongSlot.textContent = 'Λάθος: ';
    wrongSlot.classList.add('red');
    document.getElementById('onePointBtn').style.display = 'none';

    // Καθαρίζουμε μήνυμα Top5
    const msgEl = document.getElementById('top5Msg');
    msgEl.textContent = '';
    msgEl.className = '';
}

// --- Load Top5 Question ---
async function loadTop5(catId, diff, btnEl){
    currentTop5Cat = catId;
    currentTop5Diff = diff;

    try {
        const res = await fetch(`get_question.php?cat_id=${catId}&difficulty=${diff}&exclude=${[...answeredIds].join(',')}`);
        const data = await res.json();

        if(data.error){
            alert('Δεν υπάρχουν άλλες ερωτήσεις σε αυτή την κατηγορία/difficulty!');
            document.getElementById('top5Container').style.display='none';
            currentQuestion = null;
            return;
        }

        document.getElementById('top5Question').textContent = data.question_text || "Σφάλμα";

        // Αποθήκευση απαντήσεων
        top5Answers = (data.answers || []);           // για εμφάνιση (με παρενθέσεις)
        top5AnswersClean = (data.answers_clean || []).map(a => normalizeInput(a)); // για σύγκριση

        resetTop5Slots();
        currentQuestion = data;
        document.getElementById('top5Container').dataset.qid = data.id;

        // Κλείσιμο κουμπιού
        const closeBtn = document.getElementById('closeTop5Btn');
        closeBtn.onclick = () => {
            if(btnEl){
                btnEl.classList.add('disabled');
                btnEl.style.pointerEvents = 'none';
                btnEl.style.opacity = '0.5';
            }
            closeTop5();
        };

    } catch(e) {
        console.error(e);
        alert('Σφάλμα φόρτωσης Top5 ερώτησης!');
        currentQuestion = null;
    }
}



// --- Submit Top5 Answer ---
document.getElementById('submitBtn').addEventListener('click', ()=> {
    const input = document.getElementById('top5Input');
    const val = normalizeInput(input.value);
    if(!val || !currentQuestion) return;

    let found = false;
    top5AnswersClean.forEach((ansClean, idx) => {
        const slot = document.querySelector(`.answer-slot[data-index="${idx}"]`);
        if(ansClean === val && !slot.classList.contains('revealed')){
            slot.classList.add('revealed','green');
            slot.textContent = (idx+1)+'. '+top5Answers[idx]; // εμφανίζει με παρενθέσεις
            foundCount++;
            found = true;
        }
    });

    const wrongSlot = document.getElementById('wrongSlot');

    if(!found){
        wrongCount++;
        wrongSlot.textContent += (wrongSlot.textContent === 'Λάθος: ' ? input.value : ', '+input.value);
        wrongSlot.classList.add('shake');
        setTimeout(()=>wrongSlot.classList.remove('shake'),300);

        if(wrongCount >= 2){
            revealTop5Answers();
            setTimeout(() => {
                document.getElementById('top5Msg').textContent = '❌ Λάθος 2 φορές! Η ερώτηση ολοκληρώθηκε χωρίς πόντους.';
                answeredIds.add(currentQuestion.id);
                document.getElementById('onePointBtn').style.display = 'none';
                if(currentTop5Btn){
                    currentTop5Btn.classList.add('disabled');
                    currentTop5Btn.style.pointerEvents = 'none';
                    currentTop5Btn.style.opacity = '0.5';
                }
                document.getElementById('top5Container').style.display = 'none';
                currentQuestion = null;
            }, 1200);
        }
    }

    input.value='';

    if(foundCount === 4){
        document.getElementById('onePointBtn').style.display='inline-block';
    }

    if (foundCount === top5Answers.length) {
        showMessage(document.getElementById('top5Msg'),
                    '🎉 Συγχαρητήρια! Βρήκες και τα 5! Παίρνεις 3 πόντους.',
                    "congrats");
        scores[currentPlayer] += 3;
        updateScoreboard();
        answeredIds.add(currentQuestion.id);
        currentQuestion = null;
    }
});




// --- Reveal all Top5 Answers ---
function revealTop5Answers(){
    top5Answers.forEach((ans, idx)=> {
        const slot = document.querySelector(`.answer-slot[data-index="${idx}"]`);
        slot.textContent = (idx+1)+'. '+ans;
        slot.classList.add('revealed', 'red');
    });
}

// --- One Point Button ---
document.getElementById('onePointBtn').addEventListener('click', ()=> {
    alert('Πάρε 1 πόντο!');
    scores[currentPlayer] += 1;
    updateScoreboard();
    document.getElementById('onePointBtn').style.display='none';
    answeredIds.add(currentQuestion.id);
    document.getElementById('top5Container').style.display='none';
    currentQuestion = null;
    setTimeout(()=>loadTop5(currentTop5Cat, currentTop5Diff), 500);
});

// --- Close Top5 Modal ---
function closeTop5(){
    document.getElementById('top5Container').style.display='none';
    currentQuestion = null;
}
document.getElementById('closeTop5Btn').addEventListener('click', closeTop5);

// --- Quiz Item Click Handler ---
document.querySelectorAll('.quiz-item').forEach(item=>{
    item.addEventListener('click', ()=> {
        const catId = parseInt(item.dataset.catId);
        const diff = item.dataset.diff;
        if(catId === 4){
            if(currentQuestion && answeredIds.has(currentQuestion.id)){
                alert('Αυτή η Top5 ερώτηση έχει ήδη απαντηθεί!');
                return;
            }
            document.getElementById('top5Container').style.display='flex';
            loadTop5(catId,diff,item); // περνάω το κουμπί
        } else {
            startQuiz(catId,diff);
        }
    });
});

// --- Quiz Modal ---
async function startQuiz(catId,difficulty){
    try {
        const res = await fetch(`get_question.php?cat_id=${catId}&difficulty=${difficulty}&exclude=${[...answeredIds].join(',')}`);
        const q = await res.json();
        if(q.error){ alert('Σφάλμα!'); return; }
        if(answeredIds.has(q.id)){ alert('Απαντήθηκε ήδη!'); return; }
        currentQuestion = q;
        document.getElementById('qTitle').textContent = q.question_text || 'Εικόνα';
        const qImg = document.getElementById('qImage');
        if(q.image){ qImg.src=q.image; qImg.style.display='block'; } else qImg.style.display='none';
        const dl=document.getElementById('answersList'); dl.innerHTML='';
        fetch('autocomplete.php').then(r=>r.json()).then(names=>{ names.forEach(n=>{ let o=document.createElement('option'); o.value=n; dl.appendChild(o); }); });
        document.getElementById('searchInput').value=''; document.getElementById('feedback').innerHTML='';
        document.getElementById('quizModal').style.display='flex';
        refreshHelpButtons();
    } catch(e) { console.error(e); alert('Σφάλμα φόρτωσης.'); }
}

document.getElementById('submitQuizBtn').addEventListener('click', submitAnswer);
document.getElementById('closeQuizBtn').addEventListener('click', ()=>{ 
    document.getElementById('quizModal').style.display='none'; 
    document.getElementById('searchInput').value=''; 
    document.getElementById('feedback').innerHTML='';
    currentQuestion = null;
});

function submitAnswer(){
    if(!currentQuestion) return;
    const val = document.getElementById('searchInput').value.trim(); 
    if(!val) return;

    const difficulty = currentQuestion.difficulty || 'easy';
    const basePoints = (difficulty==='easy')?1:(difficulty==='medium')?2:3;
    const correct = currentQuestion['answer_'+currentQuestion.correct_answer];
    const feedbackEl = document.getElementById('quizFeedback'); // νέο id

    if(correct && val.toLowerCase()===correct.toLowerCase()){ 
        scores[currentPlayer] += basePoints; 
        showMessage(feedbackEl, `✔️ Σωστό! Κέρδισες ${basePoints} πόντο!`, 'congrats');
    } else { 
        showMessage(feedbackEl, `❌ Λάθος! Η σωστή απάντηση ήταν: ${correct}`, 'error');
    }

    answeredIds.add(currentQuestion.id);
    lockQuizItem(currentQuestion.category_id, currentQuestion.difficulty);
    currentQuestion = null;
    updateScoreboard();
    setTimeout(()=>{ document.getElementById('quizModal').style.display='none'; 
    feedbackEl.innerHTML = ""; },3000);
}




// --- 50/50 Helper ---
function refreshHelpButtons(){
    document.getElementById('fiftyBtn').disabled = !helpers.fifty;
    document.getElementById('fiftyBtn').classList.toggle('disabled', !helpers.fifty);
    document.getElementById('hintBtn').disabled = !helpers.hint;
    document.getElementById('hintBtn').classList.toggle('disabled', !helpers.hint);
}

function use5050(){
    if(!currentQuestion || !helpers.fifty) return;
    let options = [currentQuestion.answer_1, currentQuestion.answer_2].filter(Boolean);
    if(options.length < 2) return alert('Δεν υπάρχουν αρκετές επιλογές.');
    const container = document.getElementById('fiftyOptions'); container.innerHTML='';
    options.forEach(opt => {
        const btn = document.createElement('button'); 
        btn.textContent = opt; 
        btn.className='btn'; 
        btn.onclick=()=>answerFifty(opt,btn); 
        container.appendChild(btn); 
    });
    helpers.fifty=false; used5050Ids.add(currentQuestion.id); refreshHelpButtons();
    document.getElementById('fiftyModal').style.display='flex';
}

function answerFifty(ans, btn){
    if(!currentQuestion) return;
    const correct=currentQuestion['answer_'+currentQuestion.correct_answer];
    if(ans.toLowerCase()===correct.toLowerCase()){ 
        btn.classList.add('important'); 
        scores[currentPlayer] += 1; 
        alert('Σωστό! (+1)'); 
    } else { 
        btn.classList.add('secondary'); 
        alert('Λάθος!'); 
    }

    // Προσθέτουμε το ID στην απαντηθείσα λίστα
    answeredIds.add(currentQuestion.id);

    // Κλειδώνουμε την κάρτα της ερώτησης
    lockQuizItem(currentQuestion.category_id, currentQuestion.difficulty);

    currentQuestion = null;
    updateScoreboard();
    setTimeout(()=>{
        closeFiftyModal(); 
        document.getElementById('quizModal').style.display='none';
    },800);
}


function closeFiftyModal(){ 
    document.getElementById('fiftyModal').style.display='none'; 
    document.getElementById('fiftyOptions').innerHTML=''; 
}

function useHint(){
    if(!currentQuestion) return;
    const hintModal = document.getElementById('hintModal');
    const hintText = document.getElementById('hintText');
    const hintImage = document.getElementById('hintImage');

    if(currentQuestion.hint_image || currentQuestion.hint_text){
        hintText.textContent = currentQuestion.hint_text || '';
        if(currentQuestion.hint_image){
            hintImage.src = currentQuestion.hint_image;
            hintImage.style.display = 'block';
        } else {
            hintImage.style.display = 'none';
        }
        hintModal.classList.add('show'); // ΑΥΤΟ είναι το κλειδί
    } else {
        alert("Δεν υπάρχει διαθέσιμο hint.");
    }

    helpers.hint = false;
    refreshHelpButtons();
}


function closeHintModal(){
    document.getElementById('hintModal').style.display = 'none';
    document.getElementById('hintModal').style.display = 'flex';

}

function closeHintModal(){
    document.getElementById('hintModal').classList.remove('show');
}


document.getElementById('hintBtn').addEventListener('click', useHint);
document.getElementById('fiftyBtn').addEventListener('click', use5050);

// --- Initial Load ---
updateScoreboard();
// JS για dropdown ανθρωπάκι που μένει μέσα στην οθόνη
document.addEventListener('DOMContentLoaded', function () {
  const userMenu = document.querySelector('.user-menu');
  const dropdown = userMenu.querySelector('.dropdown');

  userMenu.addEventListener('mouseenter', function () {
    const menuRect = userMenu.getBoundingClientRect();
    const dropdownRect = dropdown.getBoundingClientRect();

    if (menuRect.right + dropdownRect.width > window.innerWidth) {
      dropdown.style.left = 'auto';
      dropdown.style.right = '0';
    } else {
      dropdown.style.left = '0';
      dropdown.style.right = 'auto';
    }

    dropdown.style.opacity = '1';
    dropdown.style.visibility = 'visible';
    dropdown.style.transform = 'translateY(0)';
  });

  userMenu.addEventListener('mouseleave', function () {
    dropdown.style.opacity = '0';
    dropdown.style.visibility = 'hidden';
    dropdown.style.transform = 'translateY(8px)';
  });
});

// --- Autocomplete ---
const searchInput = document.getElementById('searchInput');
const autocompleteList = document.getElementById('autocompleteList');

searchInput.addEventListener('input', async function(){
    const q = this.value.trim();
    if(q.length<1){ autocompleteList.style.display='none'; autocompleteList.innerHTML=''; return; }
    const res = await fetch('autocomplete.php?q='+encodeURIComponent(q));
    const items = await res.json();
    autocompleteList.innerHTML='';
    if(!items || items.length===0){ autocompleteList.style.display='none'; return; }
    items.forEach(item=>{
const div = document.createElement('div');
div.className = 'autocomplete-item';

if(!item.image){
    div.classList.add('no-img'); // προσθέτουμε κλάση αν δεν υπάρχει εικόνα
}

let imgHtml = '';
if(item.image) {  
    imgHtml = `<img src="${item.image}" alt="">`;
}

div.innerHTML = `
    ${imgHtml}
    <div class="meta">
        <div class="name">${item.name}</div>
        <div class="team">${item.team || ''}</div>
    </div>
`;
        div.className='autocomplete-item';
div.innerHTML = `
    ${item.image ? `<img src="${item.image}">` : ''}
    <div class="meta">
        <div class="name">${item.name}</div>
        <div class="team">${item.team || ''}</div>
    </div>
`;
        div.onclick=()=>{
            searchInput.value=item.name;
            autocompleteList.style.display='none';
        };
        autocompleteList.appendChild(div);
        requestAnimationFrame(()=>div.classList.add('visible'));
    });
    autocompleteList.style.display='block';
});

document.addEventListener('click', e=>{
    if(!autocompleteList.contains(e.target) && e.target!==searchInput){
        autocompleteList.style.display='none';
    }
});

function lockQuizItem(catId, difficulty){
    const item = document.querySelector(`.quiz-item[data-cat-id="${catId}"][data-diff="${difficulty}"]`);
    if(item){
        item.dataset.answered = '1';
        item.classList.add('disabled');
        item.style.pointerEvents = 'none';
        item.style.opacity = '0.5';
    }
}

function lockQuestion(buttonContainer) {
    const buttons = buttonContainer.querySelectorAll('.answer-btn');
    buttons.forEach(btn => {
        btn.disabled = true;
        btn.classList.add('locked');
        btn.innerHTML += " 🔒";
    });
}
submitBtn.addEventListener('click', () => {
    checkAnswer();          // η υπάρχουσα λειτουργία για έλεγχο
    lockQuestion(document.getElementById('quizButtonsContainer'));
});

item.addEventListener('click', ()=> {
    const catId = parseInt(item.dataset.catId);
    const diff = item.dataset.diff;

    // Ελέγχουμε αν έχει ήδη απαντηθεί
    if(item.dataset.answered === '1'){
        alert('Αυτή η ερώτηση έχει ήδη απαντηθεί!');
        return;
    }

    if(catId === 4){
        document.getElementById('top5Container').style.display='flex';
        loadTop5(catId,diff,item); 
    } else {
        startQuiz(catId,diff);
    }
});



function spawnConfetti() {
    const colors = ['#f39c12','#2ecc71','#e74c3c','#3498db','#9b59b6'];
    for(let i=0;i<100;i++){
        const conf = document.createElement('div');
        conf.style.position = 'fixed';
        conf.style.width = '6px';
        conf.style.height = '6px';
        conf.style.background = colors[Math.floor(Math.random()*colors.length)];
        conf.style.top = '0px';
        conf.style.left = Math.random()*window.innerWidth + 'px';
        conf.style.borderRadius = '50%';
        conf.style.opacity = 0.8;
        conf.style.zIndex = 9999;
        document.body.appendChild(conf);

        let fall = 0;
        const speed = 2 + Math.random()*3;
        const drift = (Math.random()-0.5)*2;

        const interval = setInterval(()=>{
            fall += speed;
            conf.style.top = fall + 'px';
            conf.style.left = (parseFloat(conf.style.left)+drift) + 'px';
            if(fall>window.innerHeight){
                clearInterval(interval);
                conf.remove();
            }
        }, 16);
    }
}

// Χρήση όταν θέλεις να εμφανιστεί το κομφετί:
spawnConfetti();


function showMessage(el, text, type='') {
    el.textContent = text;
    el.className = ''; // καθαρίζουμε προηγούμενα classes
    if(type === 'congrats') el.classList.add('congrats');
    if(type === 'error') el.classList.add('error');

    // Fade-in
    el.classList.add('show'); 

    // Αν θέλουμε κομφετί
    if(type === 'congrats') spawnConfetti();

    // Αυτόματα κρύβουμε μετά από 3 δευτ.
    setTimeout(()=> el.classList.remove('show'), 3000);
}



</script>

</body>
</html>