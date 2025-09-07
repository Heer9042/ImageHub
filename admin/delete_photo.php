<?php
include '../config/db.php';
session_start();

// Allow only admins
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/admin_login.php");
  exit();
}

$photo_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($photo_id > 0) {
  // Fetch photo details
  $stmt = $pdo->prepare("SELECT filename FROM photos WHERE id = ?");
  $stmt->execute([$photo_id]);
  $photo = $stmt->fetch();

  if ($photo) {
    $filepath = '../uploads/' . $photo['filename'];

    // Delete from DB
    $pdo->prepare("DELETE FROM photos WHERE id = ?")->execute([$photo_id]);

    // Delete file from server if exists
    if (file_exists($filepath)) {
      unlink($filepath);
    }

    $_SESSION['message'] = "🗑️ Photo deleted successfully.";
  } else {
    $_SESSION['message'] = "❌ Photo not found.";
  }
} else {
  $_SESSION['message'] = "⚠️ Invalid photo ID.";
}

header("Location: photos.php");
exit();
