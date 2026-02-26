<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="styles.css">
  <title>FarmShare — Login</title>
</head>
<body>
  <header>
    <p><a href="index.php">FarmShare</a></p>
    <nav>
      <ul>
        <li><a href="directory.php">Directory</a></li>
        <li><a href="add-farm.php">Add Your Farm</a></li>
        <li><a href="login.php">Login</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <h1>Log In</h1>

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