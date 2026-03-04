<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);

function nav_active($file, $current_page) {
  return $file === $current_page ? ' navbar__link--active' : '';
}
require "db.php";

if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit(); }

$user_id = (int)$_SESSION["user_id"];
$msg = "";

$stmt = $conn->prepare("SELECT farm_id, farm_name, location FROM farms WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$farm = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$farm) die("No farm for this user.");

/* Add product */
if (isset($_POST["product_name"])) {
  $name = trim($_POST["product_name"] ?? "");
  $price = (float)($_POST["price"] ?? 0);
  $qty = (int)($_POST["quantity"] ?? 0);

  if ($name) {
    $stmt = $conn->prepare("INSERT INTO products (farm_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isdi", $farm["farm_id"], $name, $price, $qty);
    $stmt->execute();
    $stmt->close();
    $msg = "Product added.";
  }
}

/* Update quantity */
if (isset($_POST["product_id"])) {
  $pid = (int)($_POST["product_id"] ?? 0);
  $newq = (int)($_POST["new_quantity"] ?? 0);

  $stmt = $conn->prepare("UPDATE products SET quantity = ? WHERE product_id = ? AND farm_id = ?");
  $stmt->bind_param("iii", $newq, $pid, $farm["farm_id"]);
  $stmt->execute();
  $stmt->close();
  $msg = "Quantity updated.";
}

/* List products */
$stmt = $conn->prepare("SELECT product_id, product_name, price, quantity FROM products WHERE farm_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $farm["farm_id"]);
$stmt->execute();
$products = $stmt->get_result();
$stmt->close();
$stmt = $conn->prepare("SELECT product_name, price, quantity 
                        FROM products 
                        WHERE farm_id = ? 
                        ORDER BY created_at DESC 
                        LIMIT 1");
$stmt->bind_param("i", $farm["farm_id"]);
$stmt->execute();
$newest_product = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>FarmShare — Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="navbar">
  <div class="navbar__inner container">

    <a href="index.php" class="navbar__logo">FarmShare</a>

    <!-- Desktop nav -->
    <nav class="navbar__nav navbar__nav--desktop" aria-label="Primary navigation">
      <ul class="navbar__list navbar__list--desktop">
        <li class="navbar__item"><a href="dashboard.php" class="navbar__link<?php echo nav_active('dashboard.php', $current_page); ?>">Dashboard</a></li>

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
            <li class="navbar__item"><a href="login.php" class="navbar__link<?php echo nav_active('login.php', $current_page); ?>">FarmerLogin</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </div>
</header>

 <!-- ✅ MAIN HTML (Replace your <main>...</main> only) -->
<main class="dashboard">
  <section class="dashboard__content">
    <div class="container dashboard__wrap">

      <?php if ($msg): ?>
        <p class="dashboard__message"><?php echo htmlspecialchars($msg); ?></p>
      <?php endif; ?>

      <!-- TOP: Left 50% (Farm big + 2 cards below) | Right 50% (Form) -->
      <section class="dashboard__top">

        <!-- LEFT COLUMN -->
        <div class="dashboard__left">

          <!-- FARM CARD (full width of left column) -->
          <article class="stat-card stat-card--farm">
            <p class="stat-card__label">Farm</p>
            <h2 class="stat-card__value"><?php echo htmlspecialchars($farm["farm_name"]); ?></h2>
            <p class="stat-card__meta"><?php echo htmlspecialchars($farm["location"]); ?></p>
          </article>

          <!-- TOTAL + NEWEST below farm card -->
          <div class="dashboard__mini-stats">

            <article class="stat-card">
              <p class="stat-card__label">Total Products</p>
              <h2 class="stat-card__value"><?php echo (int)$products->num_rows; ?></h2>
              <p class="stat-card__meta">Listed in your inventory</p>
            </article>

            <article class="stat-card stat-card--newest">
              <p class="stat-card__label">Newest Product</p>

              <?php if (!empty($newest_product)): ?>
                <h2 class="stat-card__value"><?php echo htmlspecialchars($newest_product["product_name"]); ?></h2>
                <p class="stat-card__meta">
                  $<?php echo htmlspecialchars($newest_product["price"]); ?>
                  • Qty: <?php echo htmlspecialchars($newest_product["quantity"]); ?>
                </p>
              <?php else: ?>
                <h2 class="stat-card__value">No products yet</h2>
                <p class="stat-card__meta">Add your first product</p>
              <?php endif; ?>

            </article>

          </div>
        </div>

        <!-- RIGHT COLUMN -->
        <aside class="dashboard__panel dashboard__panel--add">
          <h2 class="dashboard__heading">Add Product</h2>

          <form method="post" action="dashboard.php" class="form form--compact">

            <div class="form__group">
              <label class="form__label">Product name</label>
              <input class="form__control" type="text" name="product_name" required>
            </div>

            <div class="form__group">
              <label class="form__label">Price</label>
              <input class="form__control" type="number" name="price" step="0.01" min="0" required>
            </div>

            <div class="form__group">
              <label class="form__label">Quantity</label>
              <input class="form__control" type="number" name="quantity" min="0" required>
            </div>

            <div class="form__actions">
              <button class="btn btn--primary" type="submit">Add Product</button>
            </div>

          </form>
        </aside>

      </section>

      <!-- PRODUCTS BELOW -->
      <section class="dashboard__bottom">
        <div class="dashboard__panel dashboard__panel--products">
          <h2 class="dashboard__heading">Your Products</h2>

          <?php if ($products->num_rows === 0): ?>
            <p class="dashboard__empty">No products added yet.</p>
          <?php else: ?>
            <div class="product-grid">
              <?php while ($p = $products->fetch_assoc()): ?>
                <article class="product-card">
                  <h3 class="product-card__title"><?php echo htmlspecialchars($p["product_name"]); ?></h3>

                  <p class="product-card__meta">
                    <strong>Price:</strong> $<?php echo htmlspecialchars($p["price"]); ?><br>
                    <strong>Quantity:</strong> <?php echo htmlspecialchars($p["quantity"]); ?>
                  </p>

                  <form method="post" action="dashboard.php" class="product-card__form">
                    <input class="form__control product-card__input"
                           type="number"
                           name="new_quantity"
                           min="0"
                           required
                           placeholder="New qty">
                    <input type="hidden" name="product_id" value="<?php echo (int)$p["product_id"]; ?>">
                    <button class="btn btn--primary btn--small" type="submit">Update</button>
                  </form>
                </article>
              <?php endwhile; ?>
            </div>
          <?php endif; ?>

        </div>
      </section>

    </div>
  </section>
</main>

  <footer>
    <p>Copyright © 2026 FarmShare. All Rights Reserved.</p>
  </footer>
</body>
</html>