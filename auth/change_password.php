<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/user_login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $current = $_POST['current_password'];
  $new = $_POST['new_password'];
  $confirm = $_POST['confirm_password'];

  if ($new !== $confirm) {
    $error = "New passwords do not match.";
  } else {
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $hashed = $stmt->fetchColumn();

    if (!password_verify($current, $hashed)) {
      $error = "Current password is incorrect.";
    } else {
      $new_hash = password_hash($new, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
      $stmt->execute([$new_hash, $user_id]);
      $success = "Password changed successfully.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <title>Change Password - ImageHub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { darkMode: 'class' }</script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">

  <div class="max-w-lg mx-auto mt-20 p-6 bg-white dark:bg-gray-800 rounded shadow">
    <h2 class="text-2xl font-bold text-center text-blue-600 dark:text-blue-400 mb-6">🔒 Change Password</h2>

    <?php if ($error): ?>
      <div class="bg-red-100 text-red-800 p-3 mb-4 rounded"><?= $error ?></div>
    <?php elseif ($success): ?>
      <div class="bg-green-100 text-green-800 p-3 mb-4 rounded"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
      <div>
        <label class="block mb-1 font-medium">Current Password</label>
        <input type="password" name="current_password" required class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
      </div>

      <div>
        <label class="block mb-1 font-medium">New Password</label>
        <input type="password" name="new_password" required class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
      </div>

      <div>
        <label class="block mb-1 font-medium">Confirm New Password</label>
        <input type="password" name="confirm_password" required class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
      </div>

      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">Update Password</button>
    </form>

    <div class="mt-4 text-center">
      <a href="../user/profile.php" class="text-sm text-blue-500 hover:underline">← Back to Profile</a>
    </div>
  </div>

</body>
</html>
