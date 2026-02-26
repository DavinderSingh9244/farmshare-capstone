<?php
session_start();
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