<?php
session_start();
session_unset();
session_destroy();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FarmShare — Logged Out</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<main class="logout-screen">
  <div class="logout-screen__card">
    <h1 class="logout-screen__title">You have been logged out !</h1>

    <div class="logout-screen__actions">
      <a href="index.php" class="btn btn--primary">Go to Home</a>
      <a href="login.php" class="btn btn--secondary">Login Again</a>
    </div>
  </div>
</main>

</body>
</html>