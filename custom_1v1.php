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
<title>1v1 Quiz - Same Screen</title>
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
    background: rgba(0,0,0,0.1); /* optional φόντο αν η εικόνα έχει διάφανο */
}

.quiz-item .title {font-weight:700;}
.quiz-item span.badge {position:absolute; right:10px; top:50%; transform:translateY(-50%); background:#f1f5f9; color:#0b2a33; font-weight:800; padding:6px 10px; border-radius:10px;}
@media(max-width:720px){.quiz-row{flex-direction:column; align-items:center;}.quiz-item{width:92vw;}}

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
.autocomplete-item img.auto-img {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 6px;
    background: rgba(255,255,255,0.1);
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
.answer-btn.locked {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
}

/* Small helpers */
#helpersInfo,#skipInfo{font-size:13px;color:#cfeffa;margin-top:8px;}
.answer-slot{background:#f1f5f9;color:#0b2a33;}
/* small responsiveness tweaks */
/* --- Mobile / Small Screens --- */
@media (max-width: 720px) {
    body {
        padding-top: 60px; /* μικρότερο για κινητά */
    }

    #scoreboard {
        width: 90%;
        font-size: 14px;
        padding: 12px 15px;
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
</style>
</head>
<body>
    
<!-- MENU -->
<?php include 'nav.html'; ?>


<div id="main-content">
  <div id="scoreboard">
    <div style="font-size:28px;font-weight:900;display:flex;justify-content:center;align-items:center;gap:20px;">
      <span id="score1" style="color:#1abc9c;">0</span>
      <span style="font-size:28px;">:</span>
      <span id="score2" style="color:#e74c3c;">0</span>
    </div>

    <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:16px;align-items:center;">
      <span><img src="images/p1.png" alt="P1" style="width:22px;height:22px;vertical-align:middle;margin-right:6px;">Player 1</span>
      <span><img src="images/p2.png" alt="P2" style="width:22px;height:22px;vertical-align:middle;margin-right:6px;">Player 2</span>
    </div>

    <div style="margin-top:8px;font-size:15px;display:flex;justify-content:center;align-items:center;gap:8px;">
      Τώρα παίζει:
      <img id="currentPlayerIcon" src="images/p1.png" alt="Current" style="width:20px;height:20px;vertical-align:middle;margin-left:5px;">
      <span id="currentPlayerDisplay">Player 1</span>
    </div>

    <!-- helpers & skips info -->
    <div id="skipInfo"></div>
    <div id="helpersInfo"></div>

    <div style="margin-top:10px;display:flex;justify-content:center;gap:10px;">
      <button id="restartBtn" class="btn secondary" title="Restart">Restart</button>
      <button id="finishBtn" class="btn secondary" title="Finish game">Finish</button>
    </div>
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

<!-- Modals (Top5 & Quiz) -->
<div id="top5Container" class="modal">
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

<div id="quizModal" class="modal" style="max-width:600px;">
  <div class="title" id="qTitle">Φόρτωση...</div>
  <img id="qImage" src="" style="max-width:420px; display:none; margin:10px auto; border-radius:12px; object-fit:contain;">
<div style="position:relative; width:80%; margin:0 auto;">
  <input id="searchInput" type="text" placeholder="Απάντησε εδώ..." autocomplete="off"
         style="width:100%;padding:10px;border-radius:8px;border:1px solid #ccc;">
  <div id="autocompleteList" class="autocomplete-list" style="display:none;"></div>
</div>  <datalist id="answersList"></datalist>

  <div id="helpBtns" style="display:flex;gap:8px;justify-content:center;margin:10px 0;flex-wrap:wrap;">
    <button id="fiftyBtn" class="btn">50/50</button>
    <button id="doubleBtn" class="btn">x2</button>
    <button id="hintBtn" class="btn">Hint</button>
  </div>

  <!-- fifty modal small inline -->
  <div id="fiftyModal" class="modal" style="max-width:420px;display:none;">
    <div class="title">50/50</div>
    <div id="fiftyOptions" style="display:flex;flex-direction:column;gap:10px;margin:15px 0;"></div>
    <div style="margin-top:8px;"><button onclick="closeFiftyModal()" class="btn secondary">Κλείσιμο</button></div>
  </div>

  <div style="margin-top:6px;">
    <button id="submitQuizBtn" class="btn">Απάντηση</button>
    <button id="closeQuizBtn" class="btn secondary">Κλείσιμο</button>
</div>
<div id="quizFeedback" class="message"></div> <!-- Αλλαγή id εδώ -->
</div>
<div id="feedback" style="margin-top:10px;"></div>

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
/* ------------------ Global Variables ------------------ */
let scores = {player1:0, player2:0};
let currentPlayer = 'player1';
let answeredIds = new Set();
let currentQuestion = null;
let currentTop5Cat = null;
let currentTop5Diff = null;
let currentDoubleUsedBy = null;
let helpers = {player1:{fifty:true,double:true,hint:true},player2:{fifty:true,double:true,hint:true}};
let used5050Ids = new Set();

/* ------------------ Scoreboard ------------------ */
function updateScoreboard(){
    document.getElementById('score1').textContent = scores.player1;
    document.getElementById('score2').textContent = scores.player2;
    document.getElementById('currentPlayerDisplay').textContent = currentPlayer==='player1' ? 'Player 1':'Player 2';
    document.getElementById('currentPlayerIcon').src = currentPlayer==='player1'?'images/p1.png':'images/p2.png';
    document.getElementById('skipInfo').textContent = `Hints: P1 ${helpers.player1.hint ? 1 : 0} | P2 ${helpers.player2.hint ? 1 : 0}`;
    document.getElementById('helpersInfo').textContent = `50/50: P1 ${helpers.player1.fifty?1:0} | P2 ${helpers.player2.fifty?1:0}  ·  x2: P1 ${helpers.player1.double?1:0} | P2 ${helpers.player2.double?1:0}`;
    refreshHelpButtons();
}

function nextPlayer(){
    currentPlayer = currentPlayer==='player1'?'player2':'player1';
    currentDoubleUsedBy = null;
    updateScoreboard();
}

/* ------------------ Quiz ------------------ */
async function startQuiz(catId,difficulty){
    try{
        const res = await fetch(`get_question.php?cat_id=${catId}&difficulty=${difficulty}&exclude=${[...answeredIds].join(',')}`);
        const q = await res.json();
        if(q.error){ alert(q.error); return; }
        currentQuestion = q;
        currentDoubleUsedBy=null;

        document.getElementById('qTitle').textContent = q.question_text||'Εικόνα';
        const qImg = document.getElementById('qImage');
        if(q.image){ qImg.src=q.image; qImg.style.display='block'; } else { qImg.style.display='none'; }

        document.getElementById('searchInput').value='';
        document.getElementById('feedback').innerHTML='';
        document.getElementById('quizModal').style.display='flex';
        refreshHelpButtons();
    } catch(e){ console.error(e); alert('Σφάλμα φόρτωσης quiz'); }
}

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
    // Προσθήκη id για να μην ξαναπαραχθεί
    answeredIds.add(currentQuestion.id);

    // Κλείδωμα κάρτας
    lockQuizItem(currentQuestion.category_id, currentQuestion.difficulty);

    updateScoreboard();

    // Αλλαγή παίκτη μετά από μικρό delay
    setTimeout(()=>{
        document.getElementById('quizModal').style.display='none';
        currentQuestion = null;
        nextPlayer();
    feedbackEl.innerHTML = ""; },3000);
}


/* ------------------ 50/50 ------------------ */
function refreshHelpButtons(){
    const fiftyBtn = document.getElementById('fiftyBtn');
    const doubleBtn = document.getElementById('doubleBtn');
const hintBtn = document.getElementById('hintBtn');
hintBtn.disabled = !helpers[currentPlayer].hint;
hintBtn.classList.toggle('disabled', !helpers[currentPlayer].hint);
    fiftyBtn.disabled=!helpers[currentPlayer].fifty;
    fiftyBtn.classList.toggle('disabled',!helpers[currentPlayer].fifty);
    doubleBtn.disabled=!helpers[currentPlayer].double;
    doubleBtn.classList.toggle('disabled',!helpers[currentPlayer].double);

}

function use5050(){
    if(!currentQuestion || !helpers[currentPlayer].fifty) return;
    if(used5050Ids.has(currentQuestion.id)) return alert('Ήδη χρησιμοποιήθηκε 50/50 για αυτή την ερώτηση.');

    let options=[];
    if(currentQuestion.answer_1) options.push(currentQuestion.answer_1);
    if(currentQuestion.answer_2) options.push(currentQuestion.answer_2);
    if(options.length<2) return alert('Δεν υπάρχουν αρκετές επιλογές για 50/50.');

    const container = document.getElementById('fiftyOptions'); container.innerHTML='';
    options.forEach(opt=>{
        const btn = document.createElement('button');
        btn.textContent = opt;
        btn.className='btn';
        btn.onclick=()=>answerFifty(opt,btn);
        container.appendChild(btn);
    });

    helpers[currentPlayer].fifty=false;
    used5050Ids.add(currentQuestion.id);
    refreshHelpButtons();
    document.getElementById('fiftyModal').style.display='flex';
}

function answerFifty(answer,btn){
    if(!currentQuestion) return;
    const correct = currentQuestion['answer_'+currentQuestion.correct_answer];
    const points = (currentDoubleUsedBy===currentPlayer)?2:1;

    if(answer.toLowerCase()===correct.toLowerCase()){
        scores[currentPlayer]+=points;
        btn.classList.add('important');
        alert(`Σωστό! +${points} πόντος/οι`);
    } else {
        btn.classList.add('secondary');
        alert('Λάθος!');
    }

    // Προσθήκη id για να μην ξαναπαραχθεί
    answeredIds.add(currentQuestion.id);

    // Κλείδωμα κάρτας
    lockQuizItem(currentQuestion.category_id, currentQuestion.difficulty);

    updateScoreboard();

    // Κλείσιμο modal και αλλαγή παίκτη
    setTimeout(()=>{
        document.getElementById('fiftyModal').style.display='none';
        document.getElementById('quizModal').style.display='none';
        currentQuestion = null;
        nextPlayer();
    },900);
}


function useDouble(){
    if(!currentQuestion || !helpers[currentPlayer].double) return;
    helpers[currentPlayer].double=false;
    currentDoubleUsedBy=currentPlayer;
    refreshHelpButtons();
    alert('x2 ενεργοποιήθηκε!');
}

function useHint(){
    if(!currentQuestion || !helpers[currentPlayer].hint) return;

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
        hintModal.classList.add('show');
    } else {
        alert("Δεν υπάρχει διαθέσιμο hint.");
        return;
    }

    helpers[currentPlayer].hint = false;   // ✅ μαρκάρουμε ως χρησιμοποιημένο
    refreshHelpButtons();
    updateScoreboard();
}

function closeHintModal() {
    const modal = document.getElementById('hintModal');
    modal.classList.remove('show');  // απλώς αφαιρούμε το class που το εμφανίζει
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

/* ------------------ Top5 ------------------ */
let top5Answers = [];
let top5AnswersClean = [];
let foundCount=0, wrongCount=0;
let currentTop5Btn=null;
let top5Completed = new Set();


function resetTop5Slots() {
    foundCount = 0;
    wrongCount = 0;
    document.querySelectorAll('#top5Slots .answer-slot').forEach(slot => {
        if(slot.id !== 'wrongSlot') {
            slot.textContent = (parseInt(slot.dataset.index)+1)+'.';
            slot.classList.remove('green','red','revealed','shake');
        }
    });
    const wrongSlot = document.getElementById('wrongSlot');
    wrongSlot.textContent = 'Λάθος: ';
    wrongSlot.classList.add('red');
    document.getElementById('onePointBtn').style.display = 'none';
}

async function loadTop5(catId, diff, btnEl=null) {
    currentTop5Cat = catId;
    currentTop5Diff = diff;
    currentTop5Btn = btnEl;

    try {
        const res = await fetch(`get_question.php?cat_id=${catId}&difficulty=${diff}&exclude=${[...answeredIds].join(',')}`);
        const data = await res.json();
        if(data.error){
            alert('Δεν υπάρχουν άλλες ερωτήσεις για αυτή την κατηγορία/difficulty!');
            document.getElementById('top5Container').style.display='none';
            currentQuestion = null;
            return;
        }

        document.getElementById('top5Question').textContent = data.question_text || "Σφάλμα φόρτωσης";
        top5Answers = data.answers || [];
        top5AnswersClean = (data.answers_clean || []).map(a => normalizeInput(a));

        resetTop5Slots();
        currentQuestion = data;
        document.getElementById('top5Container').dataset.qid = data.id;

        // Κλείσιμο κουμπιού
        const closeBtn = document.getElementById('closeTop5Btn');
        closeBtn.onclick = () => {
            if(currentTop5Btn){
                currentTop5Btn.classList.add('disabled');
                currentTop5Btn.style.pointerEvents = 'none';
                currentTop5Btn.style.opacity = '0.5';
            }
            document.getElementById('top5Container').style.display='none';
            currentQuestion = null;
        };

        document.getElementById('top5Container').style.display = 'flex';

    } catch(e){
        console.error(e);
        alert('Σφάλμα φόρτωσης Top5 ερώτησης!');
        currentQuestion = null;
    }
}



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
                showMessage(document.getElementById('top5Msg'),
                    '❌ Λάθος 2 φορές! Η ερώτηση ολοκληρώθηκε χωρίς πόντους.','error');
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

    if(foundCount === top5Answers.length){
        showMessage(document.getElementById('top5Msg'),
            '🎉 Συγχαρητήρια! Βρήκες και τα 5! Παίρνεις 3 πόντους.','congrats');
        scores[currentPlayer] += 3;
        updateScoreboard();
        answeredIds.add(currentQuestion.id);
        currentQuestion = null;
    }
});



    document.getElementById('onePointBtn').addEventListener('click', () => {
        if(!currentQuestion) return;
        scores[currentPlayer] += 1;
        updateScoreboard();
        document.getElementById('top5Container').style.display='none';
        currentQuestion = null;
        nextPlayer();
    });

    function revealTop5Answers(){
        top5Answers.forEach((ans, idx) => {
            const slot = document.querySelector(`.answer-slot[data-index="${idx}"]`);
            if(!slot.classList.contains('revealed')){
                slot.textContent = (idx+1)+'. '+ans;
                slot.classList.add('revealed','red');
            }
        });
    }






/* ------------------ Quiz Item Click ------------------ */
document.querySelectorAll('.quiz-item').forEach(item=>{
    item.addEventListener('click',()=>{
        const catId=parseInt(item.dataset.catId);
        const diff=item.dataset.diff;
        if(catId===4) loadTop5(catId,diff,item);
        else startQuiz(catId,diff);
    });
});

/* ------------------ Bind Helpers ------------------ */
document.getElementById('fiftyBtn').addEventListener('click',use5050);
document.getElementById('doubleBtn').addEventListener('click',useDouble);
document.getElementById('hintBtn').addEventListener('click',useHint);
document.getElementById('submitQuizBtn').addEventListener('click',submitAnswer);
document.getElementById('closeQuizBtn').addEventListener('click', ()=>{ 
    document.getElementById('quizModal').style.display='none'; 
    document.getElementById('searchInput').value=''; 
    document.getElementById('feedback').innerHTML='';
    currentQuestion = null;
});
/* ------------------ Restart ------------------ */
function restartGame(){
    scores={player1:0,player2:0}; currentPlayer='player1'; answeredIds=new Set();
    currentQuestion=null; currentTop5Cat=null; currentTop5Diff=null; currentDoubleUsedBy=null;
helpers={player1:{fifty:true,double:true,hint:true},player2:{fifty:true,double:true,hint:true}};
    skipsLeft={player1:SKIP_ALLOWED,player2:SKIP_ALLOWED}; used5050Ids=new Set();
    document.getElementById('quizModal').style.display='none';
    document.getElementById('top5Container').style.display='none';
    document.getElementById('fiftyModal').style.display='none';
    resetTop5Slots();
    updateScoreboard();
}

document.getElementById('restartBtn').addEventListener('click',()=>{ if(confirm('Θέλεις νέο παιχνίδι;')) restartGame(); });

updateScoreboard();

document.getElementById('finishBtn').addEventListener('click', () => {
    if(confirm('Θέλεις να επιστρέψεις στο αρχικό μενού; Όποια τρέχουσα ερώτηση ακυρώνεται.')) {
        window.location.href = 'index.php';
    }
});

let selectedCandidateId = null;

searchInput.addEventListener('input', async function() {
    selectedCandidateId = null; // reset όταν πληκτρολογείς
    const q = this.value.trim();
    if (q.length < 1) {
        autocompleteList.style.display = 'none';
        autocompleteList.innerHTML = '';
        return;
    }

    const res = await fetch('autocomplete.php?q=' + encodeURIComponent(q));
    const items = await res.json();

    autocompleteList.innerHTML = '';
    if (!items || items.length === 0) {
        autocompleteList.style.display = 'none';
        return;
    }

    items.forEach(item => {
    const div = document.createElement('div');
    div.className = 'autocomplete-item hidden';
    
    // Αν υπάρχει εικόνα, την εμφανίζουμε
    const imgHtml = item.image ? `<img src="${item.image}" alt="" class="auto-img">` : '';
    
    div.innerHTML = `
        ${imgHtml}
        <div class="meta">
            <div class="name">${item.name}</div>
            <div class="team">${item.team || ''}</div>
        </div>
    `;
    
    div.onclick = () => {
        searchInput.value = item.name;
        selectedCandidateId = parseInt(item.id, 10);
        autocompleteList.style.display = 'none';
    };
    
    autocompleteList.appendChild(div);
    requestAnimationFrame(() => {
        div.classList.remove('hidden');
        div.classList.add('visible');
    });
});


autocompleteList.style.display = 'block';

});

// Κλείσιμο όταν πατήσεις αλλού
document.addEventListener('click', e => {
    if (!autocompleteList.contains(e.target) && e.target !== searchInput) {
        autocompleteList.style.display = 'none';
    }
});


submitBtn.addEventListener('click', () => {
    checkAnswer();          // η υπάρχουσα λειτουργία για έλεγχο
    lockQuestion(document.getElementById('quizButtonsContainer'));
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
