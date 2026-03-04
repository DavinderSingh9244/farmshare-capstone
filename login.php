<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);

function nav_active($file, $current_page) {
  return $file === $current_page ? ' navbar__link--active' : '';
}
  require "db.php";

  $msg = "";

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $pass  = $_POST["password"] ?? "";

    $stmt = $conn->prepare("SELECT user_id, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($pass, $user["password_hash"])) {
      $_SESSION["user_id"] = (int)$user["user_id"];
      header("Location: dashboard.php");
      exit();
    }
    $msg = "Invalid login.";
  }
  ?>
  <!doctype html>
  <html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FarmShare — Login</title>
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
      <h1>Log In</h1>
      <?php if ($msg) echo "<p>" . htmlspecialchars($msg) . "</p>"; ?>

      <form method="post" action="login.php">
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

          <p><button type="submit">Login</button></p>
      </form>
    </main>

    <footer>
      <p>Copyright © 2026 FarmShare. All Rights Reserved.</p>
    </footer>
  </body>
  </html>