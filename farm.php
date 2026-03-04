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
    <article>
      <h1><?php echo htmlspecialchars($farm["farm_name"]); ?></h1>

      <p><strong>Location:</strong> <?php echo htmlspecialchars($farm["location"]); ?></p>
      <?php if (!empty($farm["phone"])): ?>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($farm["phone"]); ?></p>
    <?php endif; ?>
      <?php if (!empty($farm["short_description"])): ?>
  <p><?php echo htmlspecialchars($farm["short_description"]); ?></p>
<?php endif; ?>

      <section>
        <h2>Products</h2>

        <?php if ($products->num_rows === 0): ?>
  <p>No products added yet.</p>
<?php else: ?>
  <ul>
    <?php while ($p = $products->fetch_assoc()): ?>
      <li>
        <article>
          <p><strong><?php echo htmlspecialchars($p["product_name"]); ?></strong></p>
          <p>Price: $<?php echo htmlspecialchars($p["price"]); ?></p>
          <p>Quantity: <?php echo htmlspecialchars($p["quantity"]); ?></p>
        </article>
      </li>
    <?php endwhile; ?>
  </ul>
<?php endif; ?>
      </section>
    </article>
  </main>

  <footer>
    <p>Copyright © 2026 FarmShare. All Rights Reserved.</p>
  </footer>
</body>
</html>