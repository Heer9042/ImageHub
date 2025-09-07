<?php
include '../config/db.php';
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/admin_login.php");
  exit();
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id > 0) {
  // Fetch user to get photo filename
  $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  $user = $stmt->fetch();

  if ($user) {
    // Delete profile photo from filesystem if exists
    if (!empty($user['profile_photo'])) {
      $photo_path = '../user/uploads/' . $user['profile_photo'];
      if (file_exists($photo_path)) {
        unlink($photo_path);
      }
    }

    // Optionally delete user's photos too (if applicable)
    // $pdo->prepare("DELETE FROM photos WHERE user_id = ?")->execute([$user_id]);

    // Delete user
    $delete = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $delete->execute([$user_id]);

    $_SESSION['message'] = "🗑️ User deleted successfully.";
  } else {
    $_SESSION['message'] = "⚠️ User not found.";
  }
} else {
  $_SESSION['message'] = "⚠️ Invalid user ID.";
}

header("Location: users.php");
exit();
