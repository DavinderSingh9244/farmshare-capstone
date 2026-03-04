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
    </header>

    <main class="login">

  <section class="login__header">
    <div class="container login__narrow">
      <h1 class="login__title">Log In</h1>
      <p class="login__subtitle">
        Access your dashboard to manage your farm listing and products.
      </p>
    </div>
  </section>

  <section class="login__content">
    <div class="container login__narrow">

      <?php if ($msg): ?>
        <p class="login__message"><?php echo htmlspecialchars($msg); ?></p>
      <?php endif; ?>

      <form method="post" action="login.php" class="form">

        <div class="form__group">
          <label class="form__label">Email</label>
          <input class="form__control" type="email" name="email" required>
        </div>

        <div class="form__group">
          <label class="form__label">Password</label>
          <input class="form__control" type="password" name="password" required>
        </div>

        <div class="form__actions">
          <button class="btn btn--primary" type="submit">Login</button>
        </div>
        <p class="login__register">
  Don’t have an account?
  <a href="add-farm.php" class="login__register-link">Register here</a>
</p>
<a class="farm__back" href="directory.php">← Back to Home</a>
</p>

      </form>

    </div>
  </section>

</main>
  </body>
  </html>