<?php
session_start();
require "db.php";

$result = $conn->query("SELECT farm_id, farm_name, location, short_description FROM farms ORDER BY created_at DESC");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>FarmShare — Directory</title>
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
    <h1>Local Farms Directory</h1>

    <section>
      <ul>
<?php if ($result && $result->num_rows > 0): ?>
  <?php while ($row = $result->fetch_assoc()): ?>
    <li>
      <article>
        <h2><?php echo htmlspecialchars($row["farm_name"]); ?></h2>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($row["location"]); ?></p>
        <p><?php echo htmlspecialchars($row["short_description"] ?? ""); ?></p>
        <p><a href="farm.php?id=<?php echo (int)$row["farm_id"]; ?>">View Farm</a></p>
      </article>
    </li>
  <?php endwhile; ?>
<?php else: ?>
  <li><p>No farms available yet.</p></li>
<?php endif; ?>
</ul>
    </section>
  </main>

  <footer>
    <p>Copyright © 2026 FarmShare. All Rights Reserved.</p>
  </footer>
</body>
</html>