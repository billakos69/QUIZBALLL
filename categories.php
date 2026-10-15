<?php
include 'config.php';

$isLoggedIn = isset($_SESSION['user']);
$username   = $isLoggedIn ? $_SESSION['user'] : '';

// Φέρε gamemode + εικόνα από τη βάση
$catResult = $conn->query("SELECT gamemode, img FROM game_mode ORDER BY gamemode ASC");
$allCategories = [];
while ($row = $catResult->fetch_assoc()) {
    $allCategories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="style.css">
  <title>Όλες οι Κατηγορίες</title>
    <link rel="icon" href="images/logo.png" type="image/png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
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

body {
    margin: 0;
    font-family: 'MyCustomFont', 'Poppins', sans-serif;
    background: linear-gradient(to bottom, #0b2a33, #073136); /* ίδιο σκούρο πράσινο με index */
    color: #fff;
}

h1 {
    text-align: center;
    margin: 40px 0;
    font-size: 2.8rem;
    font-weight: 700;
    color: #00ffff; /* ίδιο με index */
    text-shadow: 1px 1px 3px rgba(0,0,0,0.6);
}

.categories-container {
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
    justify-content: center;
    padding: 20px 40px;
}

.category-card {
    width: 300px;
    height: 220px;
    border-radius: 12px;
    background: #1b1b1b; /* σκουρόχρωμο card για contrast */
    position: relative;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: transform .3s, box-shadow .3s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
}

.category-card img.category-img {
    width: 90%;
    height: 90%;
    object-fit: contain; /* πάντα ολόκληρη εικόνα */
    transition: transform .3s, filter .3s;
}

.category-card::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.6), transparent);
}

.category-title {
    position: absolute;
    bottom: 12px;
    left: 0;
    right: 0;
    text-align: center;
    font-size: 1.3rem;
    font-weight: 700;
    color: #b66d00ff; /* χρυσό accent */
    text-shadow: 1px 1px 2px #000;
    z-index: 2;
}

.category-card:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 8px 20px rgba(0,255,255,0.4); /* μαλακό glow σαν index */
}

.category-card:hover img.category-img {
    transform: scale(1.05);
    filter: brightness(1.05);
}



  

  </style>
</head>
<body>
<!-- MENU -->
<?php include 'nav.html'; ?>


<section>
  <h1>GAME MODES</h1>
  <div class="categories-container">
    <?php foreach ($allCategories as $cat): ?>
        <?php
          $gamemode = strtolower($cat['gamemode']);
          $targetPage = "";

          switch ($gamemode) {
    case "single player":
        $targetPage = "single.php";
        break;
    case "1v1":
        $targetPage = "same_screen.php";
        break;
    case "custom quiz":
        $targetPage = "select_categories.php";
        break;
    default:
        $targetPage = "";
}

        ?>
        <a href="<?= $targetPage ?>?category=<?= urlencode($gamemode) ?>">
  <div class="category-card">
    <img src="<?= htmlspecialchars($cat['img']) ?>" alt="<?= htmlspecialchars($cat['gamemode']) ?>" class="category-img">
    <span class="category-title"><?= htmlspecialchars(ucwords($cat['gamemode'])) ?></span>
  </div>
</a>

    <?php endforeach; ?>
  </div>
  
</section>

<?php include 'footer.html'; ?>


<script>
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

// JS για να δουλεύουν τα dropdown menus στο mobile

document.addEventListener('DOMContentLoaded', () => {

  // ===== USER MENU =====
  const userMenu = document.querySelector('.user-menu');
  const userDropdown = userMenu.querySelector('.dropdown');

  userMenu.addEventListener('click', (e) => {
    e.stopPropagation(); // μην προχωράει στο global click
    userDropdown.style.display = userDropdown.style.display === 'block' ? 'none' : 'block';
  });

  // ===== DROPDOWNS ΓΙΑ LI ΜΕ ΥΠΟΜΕΝΟΥ =====
  const dropdownParents = document.querySelectorAll('nav ul li');
  dropdownParents.forEach(parent => {
    const submenu = parent.querySelector('ul');
    const link = parent.querySelector('a');

    if (!submenu || parent.classList.contains('user-menu')) return;

    link.addEventListener('click', (e) => {
      // Αν υπάρχει submenu, μπλοκάρουμε default href
      e.preventDefault();
      e.stopPropagation();

      const isOpen = submenu.style.display === 'block';

      // Κλείνουμε όλα τα άλλα submenu
      document.querySelectorAll('nav ul li ul').forEach(ul => {
        if (ul !== submenu && !ul.closest('.user-menu')) ul.style.display = 'none';
      });

      // Άνοιγμα/Κλείσιμο αυτού
      submenu.style.display = isOpen ? 'none' : 'block';
    });
  });

  // ===== Κλείσιμο αν πατήσεις έξω =====
  document.addEventListener('click', () => {
    document.querySelectorAll('nav ul li ul').forEach(ul => ul.style.display = 'none');
  });

});




  </script>
</body>
</html>
