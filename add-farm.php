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

  <main class="add-farm">

  <section class="add-farm__header">
    <div class="container">
      <h1 class="add-farm__title">Add Your Farm</h1>
      <p class="add-farm__subtitle">
        Create your listing so customers can discover your farm in the directory.
      </p>
    </div>
  </section>

  <section class="add-farm__content">
    <div class="container ">

      <?php if ($msg): ?>
        <p class="add-farm__message">
          <?php echo htmlspecialchars($msg); ?>
        </p>
      <?php endif; ?>

      <form method="post" action="add-farm.php" class="form">

        <fieldset class="form__fieldset">
          <legend class="form__legend">Farm Details</legend>

          <div class="form__group">
            <label class="form__label">Farm name</label>
            <input class="form__control" type="text" name="farm_name" required>
          </div>

          <div class="form__group">
            <label class="form__label">Location</label>
            <input class="form__control" type="text" name="location" required>
          </div>

          <div class="form__group">
            <label class="form__label">Phone</label>
            <input class="form__control" type="tel" name="phone">
          </div>

          <div class="form__group">
            <label class="form__label">Short description</label>
            <textarea class="form__control" name="short_description" rows="4"></textarea>
          </div>
        </fieldset>

        <fieldset class="form__fieldset">
          <legend class="form__legend">Account</legend>

          <div class="form__group">
            <label class="form__label">Email</label>
            <input class="form__control" type="email" name="email" required>
          </div>

          <div class="form__group">
            <label class="form__label">Password</label>
            <input class="form__control" type="password" name="password" required>
          </div>
        </fieldset>

        <div class="form__actions">
          <button class="btn btn--primary" type="submit">
            Register
          </button>
        </div>

      </form>

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