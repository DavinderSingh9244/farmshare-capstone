<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);

function nav_active($file, $current_page) {
  return $file === $current_page ? ' navbar__link--active' : '';
}
require "db.php";

$farm_id = (int)($_GET["id"] ?? 0);
if ($farm_id <= 0) die("Invalid farm.");

$stmt = $conn->prepare("SELECT farm_name, location, phone, short_description FROM farms WHERE farm_id = ?");
$stmt->bind_param("i", $farm_id);
$stmt->execute();
$farm = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$farm) die("Farm not found.");

$stmt = $conn->prepare("SELECT product_name, price, quantity FROM products WHERE farm_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $farm_id);
$stmt->execute();
$products = $stmt->get_result();
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>FarmShare — Farm</title>
  <link rel="stylesheet" href="css/style.css">
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
          <li class="navbar__item"><a href="login.php" class="navbar__link<?php echo nav_active('login.php', $current_page); ?>">Farmer Login</a></li>
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
            <li class="navbar__item"><a href="login.php" class="navbar__link<?php echo nav_active('login.php', $current_page); ?>">Farmer Login</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </div>
</header>

  <main class="farm">

  <!-- Header area -->
  <section class="farm__header">
    <div class="container farm__wrap">
      <h1 class="farm__title"><?php echo htmlspecialchars($farm["farm_name"]); ?></h1>

      <ul class="farm__meta">
        <li class="farm__meta-item">
          <strong>Location:</strong> <?php echo htmlspecialchars($farm["location"]); ?>
        </li>

        <?php if (!empty($farm["phone"])): ?>
          <li class="farm__meta-item">
            <strong>Phone:</strong> <?php echo htmlspecialchars($farm["phone"]); ?>
          </li>
        <?php endif; ?>
      </ul>

      <?php if (!empty($farm["short_description"])): ?>
        <p class="farm__desc"><?php echo htmlspecialchars($farm["short_description"]); ?></p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Products -->
  <section class="farm__content">
    <div class="container farm__wrap">

      <div class="farm__panel">
        <div class="farm__panel-head">
          <h2 class="farm__heading">Products</h2>
          <a class="farm__back" href="directory.php">← Back to Directory</a>
        </div>

        <?php if ($products->num_rows === 0): ?>
          <p class="farm__empty">No products added yet.</p>
        <?php else: ?>
          <div class="farm-products">
            <?php while ($p = $products->fetch_assoc()): ?>
              <article class="farm-product">
                <h3 class="farm-product__title"><?php echo htmlspecialchars($p["product_name"]); ?></h3>

                <p class="farm-product__meta">
                  <strong>Price:</strong> $<?php echo htmlspecialchars($p["price"]); ?><br>
                  <strong>Quantity:</strong> <?php echo htmlspecialchars($p["quantity"]); ?>
                </p>
              </article>
            <?php endwhile; ?>
          </div>
        <?php endif; ?>

      </div>

    </div>
  </section>

</main>
 <footer class="footer">
  <div class="container footer__inner">
    <p class="footer__text">© 2026 FarmShare. All Rights Reserved.</p>
  </div>
</footer>
</body>
</html>