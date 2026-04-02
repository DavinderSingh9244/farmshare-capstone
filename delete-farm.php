<?php
session_start();
require "db.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit();
}

$user_id = (int)$_SESSION["user_id"];

/* Get farm id for logged in user */
$stmt = $conn->prepare("SELECT farm_id, farm_image FROM farms WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$farm = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$farm) {
  header("Location: dashboard.php");
  exit();
}

$farm_id = (int)$farm["farm_id"];

/* Get product images before deleting products */
$stmt = $conn->prepare("SELECT product_image FROM products WHERE farm_id = ?");
$stmt->bind_param("i", $farm_id);
$stmt->execute();
$product_images = $stmt->get_result();
$stmt->close();

/* Delete product image files */
while ($row = $product_images->fetch_assoc()) {
  if (!empty($row["product_image"])) {
    $file = __DIR__ . "/" . $row["product_image"];
    if (file_exists($file)) {
      unlink($file);
    }
  }
}

/* Delete farm image file */
if (!empty($farm["farm_image"])) {
  $farm_file = __DIR__ . "/" . $farm["farm_image"];
  if (file_exists($farm_file)) {
    unlink($farm_file);
  }
}

/* Delete products */
$stmt = $conn->prepare("DELETE FROM products WHERE farm_id = ?");
$stmt->bind_param("i", $farm_id);
$stmt->execute();
$stmt->close();

/* Delete farm */
$stmt = $conn->prepare("DELETE FROM farms WHERE farm_id = ?");
$stmt->bind_param("i", $farm_id);
$stmt->execute();
$stmt->close();

/* Delete user account */
$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

/* Log out */
session_unset();
session_destroy();

header("Location: index.php");
exit();
?>