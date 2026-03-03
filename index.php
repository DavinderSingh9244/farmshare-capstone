<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);

function nav_active($file, $current_page) {
  return $file === $current_page ? ' navbar__link--active' : '';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/style.css">
  <title>FarmShare — Home</title>
</head>
<body>
  <header class="navbar">
  <div class="navbar__inner container">

    <a href="index.php" class="navbar__logo">FarmShare</a>

    <!-- Desktop nav -->
    <nav class="navbar__nav navbar__nav--desktop" aria-label="Primary navigation">
      <ul class="navbar__list navbar__list--desktop">
        <li class="navbar__item"><a href="directory.php" class="navbar__link<?php echo nav_active('directory.php', $current_page); ?>">Directory</a></li>
        <li class="navbar__item"><a href="add-farm.php" class="navbar__link<?php echo nav_active('add-farm.php', $current_page); ?>">Add Your Farm</a></li>

        <?php if (isset($_SESSION["user_id"])): ?>
          <li class="navbar__item"><a href="logout.php" class="navbar__link<?php echo nav_active('logout.php', $current_page); ?>">Logout</a></li>
        <?php else: ?>
          <li class="navbar__item"><a href="login.php" class="navbar__link<?php echo nav_active('login.php', $current_page); ?>">Login</a></li>
        <?php endif; ?>
      </ul>
    </nav>

    <!-- Mobile hamburger -->
    <button class="navbar__toggle"
            type="button"
            aria-label="Open menu"
            aria-expanded="false"
            aria-controls="mobile-nav">
      <span class="navbar__bar"></span>
      <span class="navbar__bar"></span>
      <span class="navbar__bar"></span>
    </button>

  </div>

  <div class="navbar__panel" id="mobile-nav" aria-hidden="true">
    <div class="navbar__panel-inner container">
      <button class="navbar__close" type="button" aria-label="Close menu">✕</button>

      <nav class="navbar__nav navbar__nav--mobile" aria-label="Mobile navigation">
        <ul class="navbar__list navbar__list--mobile">
          <li class="navbar__item"><a href="directory.php" class="navbar__link<?php echo nav_active('directory.php', $current_page); ?>">Directory</a></li>
          <li class="navbar__item"><a href="add-farm.php" class="navbar__link<?php echo nav_active('add-farm.php', $current_page); ?>">Add Your Farm</a></li>

          <?php if (isset($_SESSION["user_id"])): ?>
            <li class="navbar__item"><a href="logout.php" class="navbar__link<?php echo nav_active('logout.php', $current_page); ?>">Logout</a></li>
          <?php else: ?>
            <li class="navbar__item"><a href="login.php" class="navbar__link<?php echo nav_active('login.php', $current_page); ?>">Login</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </div>
</header>
  <main>
    <section>
      <h1>Discover Fresh Food from Local Farmers Near You</h1>
      <p>Browse local farm listings and see what fresh food they offer.</p>
      <p><a href="directory.php">Explore Local Farms</a></p>
    </section>

    <section>
      <h2>How FarmShare Works</h2>
      <ol>
        <li>Farmers register their farm.</li>
        <li>Farms appear in the directory automatically.</li>
        <li>Farmers add products from the dashboard.</li>
      </ol>
    </section>

    <section>
      <h2>Why Support Local Farmers?</h2>
      <ul>
        <li>Fresher food</li>
        <li>Supports local economy</li>
        <li>More sustainable</li>
      </ul>
    </section>
  </main>

  <footer>
    <p>Copyright © 2026 FarmShare. All Rights Reserved.</p>
  </footer>
</body>
<script>
  const toggle = document.querySelector(".navbar__toggle");
  const panel  = document.querySelector(".navbar__panel");
  const closeBtn = document.querySelector(".navbar__close");

  function openMenu() {
  panel.classList.add("is-open");
  document.body.classList.add("menu-open");
  toggle.setAttribute("aria-expanded", "true");
}

function closeMenu() {
  panel.classList.remove("is-open");
  document.body.classList.remove("menu-open");
  toggle.setAttribute("aria-expanded", "false");
}

  if (toggle && panel && closeBtn) {
    toggle.addEventListener("click", () => {
      panel.classList.contains("is-open") ? closeMenu() : openMenu();
    });

    closeBtn.addEventListener("click", closeMenu);

    panel.addEventListener("click", (e) => {
      if (e.target.matches(".navbar__link")) closeMenu();
    });
  }
</script>
</html>