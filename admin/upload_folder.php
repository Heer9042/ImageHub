<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
  die("Access denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['photos']['name'][0])) {
  $uploadDir = '../uploads/photos/';
  $user_id = $_SESSION['admin_id'];

  foreach ($_FILES['photos']['name'] as $index => $name) {
    $tmp_name = $_FILES['photos']['tmp_name'][$index];

    // Keep original folder structure
    $relativePath = $_FILES['photos']['name'][$index];
    $targetPath = $uploadDir . $relativePath;

    // Create directories if needed
    $targetDir = dirname($targetPath);
    if (!is_dir($targetDir)) {
      mkdir($targetDir, 0777, true);
    }

    // Move file
    if (move_uploaded_file($tmp_name, $targetPath)) {
      $filename = str_replace('../uploads/photos/', '', $targetPath);

      // Insert record
      $stmt = $pdo->prepare("INSERT INTO photos (user_id, filename, uploaded_at) VALUES (?, ?, NOW())");
      $stmt->execute([$user_id, $filename]);
    }
  }

  $_SESSION['message'] = "📂 Folder uploaded successfully!";
  header("Location: photos.php");
  exit();
} else {
  $_SESSION['message'] = "⚠ No files uploaded.";
  header("Location: photos.php");
  exit();
}
?>
