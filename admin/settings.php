<?php
include '../config/db.php';
include 'admin_session.php';

$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT name, email FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Settings - ImageHub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { darkMode: 'class' };</script>
</head>
<body class="flex bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">

  <?php include 'sidebar.php'; ?>

<main class="flex-1 p-6 overflow-x-auto">
  <h1 class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-6">⚙️ Admin Settings</h1>

  <!-- Notifications -->
  <section class="mb-10">
    <h2 class="text-xl font-semibold mb-4">🔔 Notifications</h2>
    <form action="update_notifications.php" method="POST">
      <label><input type="checkbox" name="email_alerts" class="mr-2"> Enable Email Alerts</label><br>
      <label class="block mt-2">Auto-delete logs older than (days): <input type="number" name="log_cleanup_days" class="ml-2 p-2 rounded border bg-white dark:bg-gray-800"></label>
      <button type="submit" class="mt-4 bg-yellow-600 text-white px-4 py-2 rounded">Save Notifications</button>
    </form>
  </section>


  <!-- Account Settings -->
  <section class="mb-10">
    <h2 class="text-xl font-semibold mb-4">👤 Account Settings</h2>
    <form action="update_account.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block mb-1">Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" class="w-full p-2 rounded border bg-white dark:bg-gray-800">
      </div>
      <div>
        <label class="block mb-1">Email</label>
        <input type="text" name="site_title" id="site_title"
       class="w-full p-2 rounded border bg-white dark:bg-gray-800"
       value="<?= htmlspecialchars($site_title ?? '') ?>">

      </div>
      <div>
        <label class="block mb-1">New Password</label>
        <input type="password" name="new_password" class="w-full p-2 rounded border bg-white dark:bg-gray-800">
      </div>
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update Account</button>
    </form>
  </section>

</main>
</body>
</html>