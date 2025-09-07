<?php
include '../config/db.php';
session_start();

// Redirect to login if admin not logged in
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/admin_login.php");
  exit();
}

// Validate user ID
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) {
  $_SESSION['message'] = "⚠ Invalid user ID.";
  header("Location: users.php");
  exit();
}

// Fetch current ban status
$stmt = $pdo->prepare("SELECT is_banned FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user) {
  $new_status = $user['is_banned'] ? 0 : 1;

  // Update the ban status
  $update = $pdo->prepare("UPDATE users SET is_banned = ? WHERE id = ?");
  $update->execute([$new_status, $user_id]);

  $_SESSION['message'] = $new_status 
    ? "🚫 User has been banned." 
    : "✅ User has been unbanned.";
} else {
  $_SESSION['message'] = "⚠ User not found.";
}

// Redirect back to user management page
header("Location: users.php");
exit();
