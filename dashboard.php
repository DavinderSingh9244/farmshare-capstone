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
    <h1>Farm Name: <?php echo htmlspecialchars($farm["farm_name"]); ?></h1>
<p><strong>Location:</strong> <?php echo htmlspecialchars($farm["location"]); ?></p>
    <?php if ($msg) echo "<p>" . htmlspecialchars($msg) . "</p>"; ?>

    <section>
      <h2>Add Product</h2>

      <form method="post" action="dashboard.php">
          <legend>Product details</legend>

          <p>
            <label>
              Product name
              <input type="text" name="product_name" required />
            </label>
          </p>

          <p>
            <label>
              Price
              <input type="number" name="price" step="0.01" min="0" required />
            </label>
          </p>

          <p>
            <label>
              Quantity
              <input type="number" name="quantity" min="0" required />
            </label>
          </p>

          <p><button type="submit">Add Product</button></p>
      </form>
    </section>

    <section>
      <h2>Your Current Products</h2>

      <table>
        <caption>Products listed for your farm</caption>
        <thead>
          <tr>
            <th scope="col">Product</th>
            <th scope="col">Price</th>
            <th scope="col">Quantity</th>
            <th scope="col">Edit quantity</th>
          </tr>
        </thead>
        <tbody>
<?php if ($products->num_rows === 0): ?>
  <tr><td colspan="4">No products added yet.</td></tr>
<?php else: ?>
  <?php while ($p = $products->fetch_assoc()): ?>
    <tr>
      <td><?php echo htmlspecialchars($p["product_name"]); ?></td>
      <td>$<?php echo htmlspecialchars($p["price"]); ?></td>
      <td><?php echo htmlspecialchars($p["quantity"]); ?></td>
      <td>
        <form method="post" action="dashboard.php">
          <p>
            <label>New quantity <input type="number" name="new_quantity" min="0" required /></label>
            <input type="hidden" name="product_id" value="<?php echo (int)$p["product_id"]; ?>" />
            <button type="submit">Update</button>
          </p>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
<?php endif; ?>
</tbody>
      </table>
    </section>
  </main>

  <footer>
    <p>Copyright © 2026 FarmShare. All Rights Reserved.</p>
  </footer>
</body>
</html>