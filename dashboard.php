<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);

function nav_active($file, $current_page) {
  return $file === $current_page ? ' navbar__link--active' : '';
}

require "db.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit();
}

$user_id = (int)$_SESSION["user_id"];
$msg = "";
$errors = [];

/* Get farm info */
$stmt = $conn->prepare("SELECT farm_id, farm_name, location, farm_image FROM farms WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$farm = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$farm) {
  die("No farm found for this account.");
}

$farm_id = (int)$farm["farm_id"];

/* Upload farm profile photo */
if (!empty($_FILES["farm_image"]["name"])) {

  $tmp  = $_FILES["farm_image"]["tmp_name"];
  $err  = (int)($_FILES["farm_image"]["error"] ?? 0);
  $size = (int)($_FILES["farm_image"]["size"] ?? 0);

  $ext = strtolower(pathinfo($_FILES["farm_image"]["name"], PATHINFO_EXTENSION));
  $allowed = ["jpg", "jpeg", "png", "webp"];

  if ($err !== 0) {
    $errors[] = "Farm photo upload failed.";
  } elseif (!in_array($ext, $allowed, true)) {
    $errors[] = "Farm photo must be JPG, JPEG, PNG, or WEBP.";
  } elseif ($size > 2 * 1024 * 1024) {
    $errors[] = "Farm photo must be smaller than 2MB.";
  } else {
    $folder = __DIR__ . "/uploads/farms/";

    if (!is_dir($folder)) {
      mkdir($folder, 0755, true);
    }

    $filename = bin2hex(random_bytes(8)) . "." . $ext;
    $dest = $folder . $filename;

    if (move_uploaded_file($tmp, $dest)) {
      $path = "uploads/farms/" . $filename;

      $stmt = $conn->prepare("UPDATE farms SET farm_image = ? WHERE farm_id = ?");
      $stmt->bind_param("si", $path, $farm_id);
      $stmt->execute();
      $stmt->close();

      $farm["farm_image"] = $path;
      $msg = "Farm photo uploaded.";
    } else {
      $errors[] = "Unable to save farm photo.";
    }
  }
}

/* Add product */
if (isset($_POST["product_name"])) {

  $name  = trim($_POST["product_name"] ?? "");
  $price = trim($_POST["price"] ?? "");
  $qty   = trim($_POST["quantity"] ?? "");
  $unit  = trim($_POST["unit"] ?? "");

  $img_path = null;

  /* Product validation */
  if ($name === "") {
    $errors[] = "Product name is required.";
  } elseif (mb_strlen($name) < 2) {
    $errors[] = "Product name must be at least 2 characters.";
  } elseif (mb_strlen($name) > 100) {
    $errors[] = "Product name must be less than 100 characters.";
  }

  if ($price === "") {
    $errors[] = "Price is required.";
  } elseif (!is_numeric($price) || (float)$price <= 0) {
    $errors[] = "Price must be a valid number greater than 0.";
  }

  if ($qty === "") {
    $errors[] = "Quantity is required.";
  } elseif (!ctype_digit($qty) || (int)$qty < 1) {
    $errors[] = "Quantity must be a whole number greater than 0.";
  }

  if (!in_array($unit, ["lb", "number"], true)) {
    $errors[] = "Please select a valid measurement type.";
  }

  $price = (float)$price;
  $qty = (int)$qty;

  if ($unit === "number") {
    $unit_text = ($qty === 1) ? "pc" : "pcs";
  } else {
    $unit_text = "lb";
  }

  $quantity_text = $qty . " " . $unit_text;

  /* Product image validation */
  if (!empty($_FILES["product_image"]["name"])) {

    $tmp  = $_FILES["product_image"]["tmp_name"];
    $err  = (int)($_FILES["product_image"]["error"] ?? 0);
    $size = (int)($_FILES["product_image"]["size"] ?? 0);

    $ext = strtolower(pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION));
    $allowed = ["jpg", "jpeg", "png", "webp"];

    if ($err !== 0) {
      $errors[] = "Product image upload failed.";
    } elseif (!in_array($ext, $allowed, true)) {
      $errors[] = "Product image must be JPG, JPEG, PNG, or WEBP.";
    } elseif ($size > 2 * 1024 * 1024) {
      $errors[] = "Product image must be smaller than 2MB.";
    } else {
      $folder = __DIR__ . "/uploads/products/";

      if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
      }

      $filename = bin2hex(random_bytes(8)) . "." . $ext;
      $dest = $folder . $filename;

      if (move_uploaded_file($tmp, $dest)) {
        $img_path = "uploads/products/" . $filename;
      } else {
        $errors[] = "Unable to save product image.";
      }
    }
  }

  /* Save only if no errors */
  if (empty($errors)) {
    $stmt = $conn->prepare(
      "INSERT INTO products (farm_id, product_name, price, quantity, product_image)
       VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("isdss", $farm_id, $name, $price, $quantity_text, $img_path);
    $stmt->execute();
    $stmt->close();

    $msg = "Product added.";
  }
}

/* Update quantity */
if (isset($_POST["product_id"]) && isset($_POST["new_quantity"])) {

  $pid = (int)($_POST["product_id"] ?? 0);
  $newq_raw = trim($_POST["new_quantity"] ?? "");

  if ($pid < 1) {
    $errors[] = "Invalid product.";
  }

  if ($newq_raw === "") {
    $errors[] = "New quantity is required.";
  } elseif (!ctype_digit($newq_raw) || (int)$newq_raw < 1) {
    $errors[] = "New quantity must be a whole number greater than 0.";
  }

  $newq = (int)$newq_raw;

  if (empty($errors)) {
    $stmt = $conn->prepare("SELECT quantity FROM products WHERE product_id = ? AND farm_id = ?");
    $stmt->bind_param("ii", $pid, $farm_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing) {
      $old_quantity = trim($existing["quantity"] ?? "");

      if (stripos($old_quantity, "lb") !== false) {
        $new_quantity_text = $newq . " lb";
      } else {
        $new_quantity_text = ($newq === 1) ? ($newq . " pc") : ($newq . " pcs");
      }

      $stmt = $conn->prepare(
        "UPDATE products SET quantity = ? WHERE product_id = ? AND farm_id = ?"
      );
      $stmt->bind_param("sii", $new_quantity_text, $pid, $farm_id);
      $stmt->execute();
      $stmt->close();

      $msg = "Quantity updated.";
    } else {
      $errors[] = "Product not found.";
    }
  }
}

/* List products */
$stmt = $conn->prepare(
  "SELECT product_id, product_name, price, quantity, product_image
   FROM products
   WHERE farm_id = ?
   ORDER BY created_at DESC"
);
$stmt->bind_param("i", $farm_id);
$stmt->execute();
$products = $stmt->get_result();
$stmt->close();

/* Newest product */
$stmt = $conn->prepare(
  "SELECT product_name, price, quantity
   FROM products
   WHERE farm_id = ?
   ORDER BY created_at DESC
   LIMIT 1"
);
$stmt->bind_param("i", $farm_id);
$stmt->execute();
$newest_product = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>FarmShare — Dashboard</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<header class="navbar">
<div class="navbar__inner container">

<a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'index.php'; ?>" class="navbar__logo">FarmShare</a>

<nav class="navbar__nav navbar__nav--desktop">
<ul class="navbar__list navbar__list--desktop">

<li class="navbar__item">
<a href="dashboard.php" class="navbar__link<?php echo nav_active('dashboard.php',$current_page); ?>">Dashboard</a>
</li>

<li class="navbar__item">
<a href="logout.php" class="navbar__link">Logout</a>
</li>

</ul>
</nav>

</div>
</header>


<main class="dashboard">

<section class="dashboard__content">

<div class="container dashboard__wrap">

<?php if ($msg): ?>
<p class="dashboard__message"><?php echo htmlspecialchars($msg); ?></p>
<?php endif; ?>


<section class="dashboard__top">

<div class="dashboard__left">

<article class="stat-card stat-card--farm">

<?php if (!empty($farm["farm_image"])): ?>

<div class="farm-profile">

<img class="farm-profile-img"
     src="<?php echo htmlspecialchars($farm["farm_image"]); ?>"
     alt="Farm photo">

<form method="post"
      enctype="multipart/form-data"
      class="farm-photo-overlay">

<label class="change-photo-btn">

Change Photo

<input type="file"
       name="farm_image"
       accept=".jpg,.jpeg,.png,.webp"
       required
       onchange="this.form.submit()">

</label>

</form>

</div>

<?php else: ?>

<form method="post"
      enctype="multipart/form-data"
      class="farm-photo-form">

<label class="form__label">Upload Farm Photo</label>

<input class="form__control"
       type="file"
       name="farm_image"
       accept=".jpg,.jpeg,.png,.webp"
       required>

<button class="btn btn--primary btn--small">
Upload
</button>

</form>

<?php endif; ?>

<p class="stat-card__label">Farm</p>

<h2 class="stat-card__value">
<?php echo htmlspecialchars($farm["farm_name"]); ?>
</h2>

<p class="stat-card__meta">
<?php echo htmlspecialchars($farm["location"]); ?>
</p>

</article>


<div class="dashboard__mini-stats">

<article class="stat-card">

<p class="stat-card__label">Total Products</p>
<h2 class="stat-card__value"><?php echo (int)$products->num_rows; ?></h2>
<p class="stat-card__meta">Listed in your inventory</p>

</article>


<article class="stat-card stat-card--newest">

<p class="stat-card__label">Newest Product</p>

<?php if (!empty($newest_product)): ?>

<h2 class="stat-card__value">
<?php echo htmlspecialchars($newest_product["product_name"]); ?>
</h2>

<p class="stat-card__meta">
$<?php echo htmlspecialchars($newest_product["price"]); ?>
• Qty: <?php echo htmlspecialchars($newest_product["quantity"]); ?>
</p>

<?php else: ?>

<h2 class="stat-card__value">No products yet</h2>
<p class="stat-card__meta">Add your first product</p>

<?php endif; ?>

</article>

</div>

</div>


<aside class="dashboard__panel form dashboard__panel--add">

  <h2 class="dashboard__heading">Add Product</h2>

  <form method="post" enctype="multipart/form-data" class="form--compact" id="addProductForm" novalidate>

    <div class="form__group">
      <label class="form__label" for="product_name">Product name</label>
      <input
        class="form__control"
        type="text"
        id="product_name"
        name="product_name"
        required
        minlength="2"
        maxlength="100"
        placeholder="Enter product name">
    </div>

    <div class="form__group">
      <label class="form__label" for="price">Price</label>
      <input
        class="form__control"
        type="number"
        id="price"
        name="price"
        step="0.01"
        min="0.01"
        required
        placeholder="Enter price">
    </div>

    <div class="form__group">
      <label class="form__label" for="unit">Measurement Type</label>
      <select class="form__control" id="unit" name="unit" required>
        <option value="lb">Pounds (lb)</option>
        <option value="number">Number of items</option>
      </select>
    </div>

    <div class="form__group">
      <label class="form__label" for="quantity">Quantity</label>
      <input
        class="form__control"
        type="number"
        id="quantity"
        name="quantity"
        min="1"
        step="1"
        required
        placeholder="Enter quantity">
    </div>

    <div class="form__group">
      <label class="form__label" for="product_image">Product photo</label>
      <input
        class="form__control"
        type="file"
        id="product_image"
        name="product_image"
        accept=".jpg,.jpeg,.png,.webp">
    </div>

    <p class="form__error" id="productFormError"></p>

    <div class="form__actions">
      <button class="btn btn--primary" type="submit">Add Product</button>
    </div>

  </form>

</aside>

</section>

<button id="deleteBtn" class=" btn btn--primary">
Delete Farm
</button>

<section class="dashboard__bottom">

<div class=" dashboard__panel--products">

<h2 class="dashboard__heading">Your Products</h2>

<?php if ($products->num_rows === 0): ?>

<p class="dashboard__empty">No products added yet.</p>

<?php else: ?>

<div class="product-grid">

<?php while ($p = $products->fetch_assoc()): ?>

<article class="product-card">

<?php if (!empty($p["product_image"])): ?>

<img class="product-card__img"
src="<?php echo htmlspecialchars($p["product_image"]); ?>"
alt="<?php echo htmlspecialchars($p["product_name"]); ?>">

<?php endif; ?>

<h3 class="product-card__title">
<?php echo htmlspecialchars($p["product_name"]); ?>
</h3>

<p class="product-card__meta">
<strong>Price:</strong> $<?php echo htmlspecialchars($p["price"]); ?><br>
<strong>Available:</strong> <?php echo htmlspecialchars($p["quantity"]); ?>
</p>

<form method="post" class="product-card__form">

<input class="form__control product-card__input"
type="number"
name="new_quantity"
required
placeholder="New qty">

<input type="hidden"
name="product_id"
value="<?php echo (int)$p["product_id"]; ?>">

<button class="btn btn--primary btn--small">
Update
</button>

</form>

</article>

<?php endwhile; ?>

</div>

<?php endif; ?>

</div>

</section>

</div>

</section>

</main>


<footer class="footer">
<div class="container footer__inner">
<p class="footer__text">© 2026 FarmShare. All Rights Reserved.</p>
</div>
</footer>
<div class="delete-popup" id="deletePopup">

  <div class="delete-card">

    <h3>Are you sure you want to permanently delete your farm?</h3>

    <div class="delete-actions">

      <!-- YES button (left) -->
      <form action="delete-farm.php" method="post">
        <button type="submit" class="btn-danger">
        Delete
        </button>
      </form>

      <!-- NO button (right) -->
      <button id="cancelDelete" class="btn-cancel">
      Cancel
      </button>

    </div>

  </div>

</div>
<script>

const deleteBtn = document.getElementById("deleteBtn");
const deletePopup = document.getElementById("deletePopup");
const cancelDelete = document.getElementById("cancelDelete");

deleteBtn.onclick = function(){
  deletePopup.classList.add("active");
}

cancelDelete.onclick = function(){
  deletePopup.classList.remove("active");
}
const addProductForm = document.getElementById("addProductForm");
const productName = document.getElementById("product_name");
const price = document.getElementById("price");
const quantity = document.getElementById("quantity");
const productImage = document.getElementById("product_image");
const productFormError = document.getElementById("productFormError");

function setError(input) {
  input.classList.add("input-error");
  input.classList.remove("input-valid");
}

function setValid(input) {
  input.classList.remove("input-error");
  input.classList.add("input-valid");
}

function validateProductName() {
  const value = productName.value.trim();

  if (value.length < 2 || value.length > 100) {
    setError(productName);
    return false;
  }

  setValid(productName);
  return true;
}

function validatePrice() {
  const value = parseFloat(price.value);

  if (isNaN(value) || value <= 0) {
    setError(price);
    return false;
  }

  setValid(price);
  return true;
}

function validateQuantity() {
  const value = parseInt(quantity.value, 10);

  if (isNaN(value) || value < 1) {
    setError(quantity);
    return false;
  }

  setValid(quantity);
  return true;
}

function validateProductImage() {
  if (!productImage.files.length) {
    productImage.classList.remove("input-error", "input-valid");
    return true;
  }

  const file = productImage.files[0];
  const allowedTypes = ["image/jpeg", "image/png", "image/webp"];
  const maxSize = 2 * 1024 * 1024;

  if (!allowedTypes.includes(file.type) || file.size > maxSize) {
    setError(productImage);
    return false;
  }

  setValid(productImage);
  return true;
}

productName.addEventListener("blur", validateProductName);
price.addEventListener("blur", validatePrice);
quantity.addEventListener("blur", validateQuantity);
productImage.addEventListener("change", validateProductImage);

productName.addEventListener("input", validateProductName);
price.addEventListener("input", validatePrice);
quantity.addEventListener("input", validateQuantity);

addProductForm.addEventListener("submit", function(e) {
  const isNameValid = validateProductName();
  const isPriceValid = validatePrice();
  const isQuantityValid = validateQuantity();
  const isImageValid = validateProductImage();

  if (!isNameValid || !isPriceValid || !isQuantityValid || !isImageValid) {
    e.preventDefault();
    productFormError.textContent = "Please fix the highlighted fields before submitting.";
  } else {
    productFormError.textContent = "";
  }
});
</script>
</body>
</html>