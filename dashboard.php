<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>FarmShare — Dashboard</title>
</head>
<body>
  <header>
    <p><a href="index.php">FarmShare</a></p>
    <nav>
      <ul>
        <li><a href="directory.php">Directory</a></li>
        <li><a href="farm.php">View My Farm</a></li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <h1>Farm Name: Sunny Fields Farm</h1>
    <p><strong>Location:</strong> Comox, BC</p>

    <section>
      <h2>Add Product</h2>

      <form method="post" action="dashboard.php">
          <legend>Product details</legend>

          <p>
            <label>
              Product name
              <input type="text" name="product_name" required />
            </label>
          </p>

          <p>
            <label>
              Price
              <input type="number" name="price" step="0.01" min="0" required />
            </label>
          </p>

          <p>
            <label>
              Quantity
              <input type="number" name="quantity" min="0" required />
            </label>
          </p>

          <p><button type="submit">Add Product</button></p>
      </form>
    </section>

    <section>
      <h2>Your Current Products</h2>

      <table>
        <caption>Products listed for your farm</caption>
        <thead>
          <tr>
            <th scope="col">Product</th>
            <th scope="col">Price</th>
            <th scope="col">Quantity</th>
            <th scope="col">Edit quantity</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Tomatoes</td>
            <td>$4.99</td>
            <td>10</td>
            <td>
              <form method="post" action="dashboard.php">
                <p>
                  <label>
                    New quantity
                    <input type="number" name="new_quantity" min="0" required />
                  </label>
                  <button type="submit">Update</button>
                </p>
              </form>
            </td>
          </tr>

          <tr>
            <td>Lettuce</td>
            <td>$2.99</td>
            <td>15</td>
            <td>
              <form method="post" action="dashboard.php">
                <p>
                  <label>
                    New quantity
                    <input type="number" name="new_quantity" min="0" required />
                  </label>
                  <button type="submit">Update</button>
                </p>
              </form>
            </td>
          </tr>
        </tbody>
      </table>
    </section>
  </main>

  <footer>
    <p>Copyright © 2026 FarmShare. All Rights Reserved.</p>
  </footer>
</body>
</html>