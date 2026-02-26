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
        <li><a href="login.php">Login</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <h1>Add Your Farm</h1>

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