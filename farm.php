<?php
session_start();
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
</head>
<body>
  <header>
    <p><a href="index.php">FarmShare</a></p>
    <nav>
      <ul>
        <li><a href="directory.php">Directory</a></li>
        <li><a href="add-farm.php">Add Your Farm</a></li>
        <?php if (isset($_SESSION["user_id"])): ?>
        <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
        <li><a href="login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </nav>
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