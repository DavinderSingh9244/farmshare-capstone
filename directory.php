<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);

function nav_active($file, $current_page) {
  return $file === $current_page ? ' navbar__link--active' : '';
} 
require "db.php";

$result = $conn->query("SELECT farm_id, farm_name, location, short_description FROM farms ORDER BY created_at DESC");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/style.css">
  <title>FarmShare — Directory</title>
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

  <main class="directory">
  <section class="directory__header">
  <div class="container directory__header-inner">

    <div class="directory__intro">
      <h1 class="directory__title">Local Farms Directory</h1>
      <p class="directory__subtitle">
        Browse local farms and discover fresh products near you.
      </p>
    </div>

    <form class="directory__search" action="#" method="get">
      <input class="directory__input" type="text"
             name="q"
             placeholder="Search by farm name or location..." />
      <button class="directory__button" type="submit">
        Search
      </button>
    </form>

  </div>
</section>

  <section class="directory__list">
    <div class="container">

      <?php if ($result && $result->num_rows > 0): ?>
        <div class="farm-grid">

          <?php while ($row = $result->fetch_assoc()): ?>
            <article class="farm-card">
              <h2 class="farm-card__title"><?php echo htmlspecialchars($row["farm_name"]); ?></h2>

              <p class="farm-card__meta">
                <span class="farm-card__label">Location:</span>
                <?php echo htmlspecialchars($row["location"]); ?>
              </p>

              <?php if (!empty($row["short_description"])): ?>
                <p class="farm-card__text"><?php echo htmlspecialchars($row["short_description"]); ?></p>
              <?php else: ?>
                <p class="farm-card__text farm-card__text--muted">No description added yet.</p>
              <?php endif; ?>

              <a class="farm-card__link btn btn--primary"
                 href="farm.php?id=<?php echo (int)$row["farm_id"]; ?>">
                 View Farm
              </a>
            </article>
          <?php endwhile; ?>

        </div>
      <?php else: ?>
        <div class="directory__empty">
          <p>No farms available yet.</p>
          <a class="btn btn--primary" href="add-farm.php">Add Your Farm</a>
        </div>
      <?php endif; ?>

    </div>
  </section>
</main>

<footer class="footer">
  <div class="container footer__inner">
    <p class="footer__text">© 2026 FarmShare. All Rights Reserved.</p>
    <div class="footer__social">
      <!-- keep your icons here if you already added them -->
    </div>
  </div>
</footer>
</body>
</html>