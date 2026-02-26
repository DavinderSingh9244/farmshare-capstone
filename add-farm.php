<?php
session_start();
require "db.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $farm  = trim($_POST["farm_name"] ?? "");
  $loc   = trim($_POST["location"] ?? "");
  $ph    = trim($_POST["phone"] ?? "");
  $desc  = trim($_POST["short_description"] ?? "");
  $email = trim($_POST["email"] ?? "");
  $pass  = $_POST["password"] ?? "";

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