<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);

function nav_active($file, $current_page) {
  return $file === $current_page ? ' navbar__link--active' : '';
}

require "db.php";
$msg = "";

error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $farm  = trim($_POST["farm_name"] ?? "");
  $loc   = trim($_POST["location"] ?? "");
  $ph    = trim($_POST["phone"] ?? "");
  $desc  = trim($_POST["short_description"] ?? "");
  $email = trim($_POST["email"] ?? "");
  $pass  = $_POST["password"] ?? "";

  $has_error = false;

  if ($farm === "" || strlen($farm) < 2 || strlen($farm) > 100) {
    $msg = "Farm name is invalid.";
    $has_error = true;
  } elseif ($loc === "" || strlen($loc) < 2 || strlen($loc) > 100) {
    $msg = "Location is invalid.";
    $has_error = true;
  } elseif ($ph !== "" && !preg_match('/^[0-9+\-\s()]{7,20}$/', $ph)) {
    $msg = "Phone number is invalid.";
    $has_error = true;
  } elseif ($desc !== "" && strlen($desc) > 300) {
    $msg = "Description is too long.";
    $has_error = true;
  } elseif ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $msg = "Email is invalid.";
    $has_error = true;
  } elseif ($pass === "" || strlen($pass) < 6 || strlen($pass) > 50) {
    $msg = "Password must be 6 to 50 characters.";
    $has_error = true;
  }

  if (!$has_error) {
    try {
      $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
      $check->bind_param("s", $email);
      $check->execute();
      $existing = $check->get_result()->fetch_assoc();
      $check->close();

      if ($existing) {
        $msg = "This email is already registered.";
      } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hash);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO farms (user_id, farm_name, location, phone, short_description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $farm, $loc, $ph, $desc);
        $stmt->execute();
        $stmt->close();

        header("Location: login.php");
        exit();
      }
    } catch (Exception $e) {
      $msg = "Database error: " . $e->getMessage();
    }
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

  <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'index.php'; ?>" class="navbar__logo">FarmShare</a>

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
            <li class="navbar__item"><a href="login.php" class="navbar__link<?php echo nav_active('login.php', $current_page); ?>">FarmerLogin</a></li>
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
      <p class="add-farm__message"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>

      <form method="post" action="add-farm.php" class="form" novalidate>

        <fieldset class="form__fieldset">
          <legend class="form__legend">Farm Details</legend>

          <div class="form__group">
  <label class="form__label">Farm name</label>
  <input class="form__control" type="text" name="farm_name" required minlength="2" maxlength="100">
</div>

<div class="form__group">
  <label class="form__label">Location</label>
  <input class="form__control" type="text" name="location" required minlength="2" maxlength="100">
</div>

<div class="form__group">
  <label class="form__label">Phone</label>
  <input class="form__control" type="tel" name="phone" pattern="[0-9+\-\s()]{7,20}" maxlength="20">
</div>

<div class="form__group">
  <label class="form__label">Short description</label>
  <textarea class="form__control" name="short_description" rows="4" maxlength="300"></textarea>
</div>

<div class="form__group">
  <label class="form__label">Email</label>
  <input class="form__control" type="email" name="email" required maxlength="100">
</div>

<div class="form__group">
  <label class="form__label">Password</label>
  <input class="form__control" type="password" name="password" required minlength="6" maxlength="50">
</div>
        </fieldset>

        <div class="form__actions">
          <button class="btn btn--primary" type="submit">
            Register
          </button>
        </div>
        <p class="login__register">
  Already have an account?
  <a href="login.php" class="login__register-link">Login here</a>
</p>

      </form>

    </div>
  </section>

</main>
  <footer class="footer">
  <div class="container footer__inner">
    <p class="footer__text">© 2026 FarmShare. All Rights Reserved.</p>
  </div>
</footer>
<script>
document.addEventListener("DOMContentLoaded", function () {

  const farm = document.querySelector("[name='farm_name']");
  const location = document.querySelector("[name='location']");
  const phone = document.querySelector("[name='phone']");
  const email = document.querySelector("[name='email']");
  const password = document.querySelector("[name='password']");

  function setError(input){
    input.classList.add("input-error");
    input.classList.remove("input-valid");
  }

  function setValid(input){
    input.classList.remove("input-error");
    input.classList.add("input-valid");
  }

  /* FARM NAME */
  function validateFarm(){
    const value = farm.value.trim();
    if(value.length < 2){
      setError(farm);
    } else {
      setValid(farm);
    }
  }

  /* LOCATION */
  function validateLocation(){
    const value = location.value.trim();
    if(value.length < 2){
      setError(location);
    } else {
      setValid(location);
    }
  }

  /* PHONE */
  function validatePhone(){
    const phonePattern = /^[0-9+\-\s()]{7,20}$/;
    if(!phonePattern.test(phone.value.trim())){
      setError(phone);
    } else {
      setValid(phone);
    }
  }

  /* EMAIL */
  function validateEmail(){
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!emailPattern.test(email.value.trim())){
      setError(email);
    } else {
      setValid(email);
    }
  }

  /* PASSWORD */
  function validatePassword(){
    if(password.value.length < 6){
      setError(password);
    } else {
      setValid(password);
    }
  }

  /* EVENTS */

  farm.addEventListener("blur", validateFarm);
  farm.addEventListener("input", validateFarm);

  location.addEventListener("blur", validateLocation);
  location.addEventListener("input", validateLocation);

  phone.addEventListener("blur", validatePhone);
  phone.addEventListener("input", validatePhone);

  email.addEventListener("blur", validateEmail);
  email.addEventListener("input", validateEmail);

  password.addEventListener("blur", validatePassword);
  password.addEventListener("input", validatePassword);

});
</script>
</body>
</html>