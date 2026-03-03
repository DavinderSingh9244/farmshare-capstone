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
    <section class="section section--hero">
  <div class="container hero__content">
    <h1 class="section__title">
      Support Local. Eat Fresh. Live Better.
    </h1>

    <p class="section__text">
      FarmShare connects you directly with trusted local farmers in your area. 
Browse farm listings, explore fresh seasonal products, and support your local community. 
    </p>

    <a href="directory.php" class="btn btn--primary">
      Explore Local Farms
    </a>
  </div>
</section>
<section class="section section--how-it-works">
  <div class="container">

    <h2 class="section__subtitle">How FarmShare Works</h2>

    <div class="how-it-works">

      <div class="how-it-works__item">  
        <h3 class="how-it-works__title">Register Your Farm</h3>
        <p class="how-it-works__text">
          Farmers create an account and add their farm details to the platform.
        </p>
      </div>

      <div class="how-it-works__item">
        <h3 class="how-it-works__title">Get Listed</h3>
        <p class="how-it-works__text">
          Farms automatically appear in the public directory for customers to explore.
        </p>
      </div>

      <div class="how-it-works__item">
        <h3 class="how-it-works__title">Add Products</h3>
        <p class="how-it-works__text">
          Farmers manage their listings and showcase fresh products directly from their dashboard.
        </p>
      </div>

    </div>
  </div>
</section>
<section class="section section--why-support">
  <div class="container">

    <h2 class="section__subtitle">Why Support Local Farmers?</h2>

    <div class="accordion" id="why-accordion">

      <div class="accordion__item">
        <button class="accordion__button" type="button" aria-expanded="false" aria-controls="why-panel-1" id="why-btn-1">
          <span class="accordion__title">Fresher Food</span>
          <span class="accordion__icon" aria-hidden="true">+</span>
        </button>
        <div class="accordion__panel" id="why-panel-1" role="region" aria-labelledby="why-btn-1">
          <p class="accordion__text">
            Local food often reaches you faster, so it tastes better and stays fresh longer.
          </p>
        </div>
      </div>

      <div class="accordion__item">
        <button class="accordion__button" type="button" aria-expanded="false" aria-controls="why-panel-2" id="why-btn-2">
          <span class="accordion__title">Supports Local Economy</span>
          <span class="accordion__icon" aria-hidden="true">+</span>
        </button>
        <div class="accordion__panel" id="why-panel-2" role="region" aria-labelledby="why-btn-2">
          <p class="accordion__text">
            Buying local keeps money in your community and helps small farms grow.
          </p>
        </div>
      </div>

      <div class="accordion__item">
        <button class="accordion__button" type="button" aria-expanded="false" aria-controls="why-panel-3" id="why-btn-3">
          <span class="accordion__title">More Sustainable</span>
          <span class="accordion__icon" aria-hidden="true">+</span>
        </button>
        <div class="accordion__panel" id="why-panel-3" role="region" aria-labelledby="why-btn-3">
          <p class="accordion__text">
            Shorter travel distance means less packaging, less waste, and a smaller footprint.
          </p>
        </div>
      </div>

    </div>

  <footer>
    <p>Copyright © 2026 FarmShare. All Rights Reserved.</p>
  </footer>
</body>
<script>
   const items = document.querySelectorAll(".accordion__item");

  items.forEach((item) => {
    const btn = item.querySelector(".accordion__button");

    btn.addEventListener("click", () => {
      const isOpen = item.classList.toggle("is-open");
      btn.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });
  });
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