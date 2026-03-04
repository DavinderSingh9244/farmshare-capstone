<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);

function nav_active($file, $current_page) {
  return $file === $current_page ? ' navbar__link--active' : '';
}
require "db.php";
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $farm = trim($_POST["farm_name"] ?? "");
  $loc  = trim($_POST["location"] ?? "");
  $ph   = trim($_POST["phone"] ?? "");
  $desc = trim($_POST["short_description"] ?? "");
  $email= trim($_POST["email"] ?? "");
  $pass = $_POST["password"] ?? "";

  if ($farm && $loc && $email && $pass) {
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
    if ($stmt && $stmt->bind_param("ss", $email, $hash) && $stmt->execute()) {
      $user_id = $stmt->insert_id;
      $stmt->close();

      $stmt = $conn->prepare("INSERT INTO farms (user_id, farm_name, location, phone, short_description) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("issss", $user_id, $farm, $loc, $ph, $desc);
      $stmt->execute();
      $stmt->close();

      header("Location: login.php");
      exit();
    }
    $msg = "Email already exists or error occurred.";
  } else {
    $msg = "Fill all required fields.";
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>FarmShare — Add Your Farm</title>
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
    <h1>Add Your Farm</h1>
    <?php if ($msg) echo "<p>" . htmlspecialchars($msg) . "</p>"; ?>

    <form method="post" action="add-farm.php">
        <legend>Farm details</legend>

        <p>
          <label>
            Farm name
            <input type="text" name="farm_name" required />
          </label>
        </p>

        <p>
          <label>
            Location
            <input type="text" name="location" required />
          </label>
        </p>

        <p>
          <label>
            Phone
            <input type="tel" name="phone" />
          </label>
        </p>

        <p>
          <label>
            Short description
            <textarea name="short_description" rows="4"></textarea>
          </label>
        </p>

        <legend>Account</legend>

        <p>
          <label>
            Email
            <input type="email" name="email" required />
          </label>
        </p>

        <p>
          <label>
            Password
            <input type="password" name="password" required />
          </label>
        </p>

        <p>
          <button type="submit">Register</button>
        </p>
    </form>
  </main>

  <footer>
    <p>Copyright © 2026 FarmShare. All Rights Reserved.</p>
  </footer>
</body>
</html>