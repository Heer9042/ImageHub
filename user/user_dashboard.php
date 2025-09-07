<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/user_login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $pdo->prepare("SELECT name, profile_photo FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_name = $user['name'];
$username = strtolower(str_replace(' ', '_', $user_name));
$avatar = $user['profile_photo'];
$avatar_path = $avatar ? "../uploads/avatars/" . htmlspecialchars($avatar) : "../uploads/avatars/default.webp";

// Handle folder creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_folder'])) {
  $folder_name = trim($_POST['new_folder']);
  if ($folder_name !== '') {
    $folder_path = "../uploads/{$username}_id{$user_id}/" . $folder_name;
    if (!is_dir($folder_path)) {
      mkdir($folder_path, 0777, true);
    }
    $stmt = $pdo->prepare("INSERT INTO folders (user_id, folder_name, folder_path, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user_id, $folder_name, $folder_path]);
    header("Location: dashboard.php");
    exit();
  }
}

// Fetch folders
$stmt = $pdo->prepare("SELECT * FROM folders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$folders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <title>User Dashboard - ImageHub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { darkMode: 'class' }</script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">

<!-- Header -->
<header class="bg-white dark:bg-gray-800 shadow sticky top-0 z-50">
  <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
    <div class="flex items-center space-x-2">
      <img src="https://img.icons8.com/color/48/camera--v1.png" alt="logo" class="h-8 w-8" />
      <h1 class="text-xl font-bold text-blue-600 dark:text-blue-400">ImageHub</h1>
    </div>
    <div class="flex items-center gap-3">
      <span class="text-sm font-medium hidden sm:block">Hi, <?= htmlspecialchars($user_name) ?>!</span>
      <a href="./profile.php"><img src="<?= $avatar_path ?>" alt="Avatar" class="w-9 h-9 rounded-full object-cover border" /></a>
      <a href="../auth/logout.php" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 text-sm">Logout</a>
    </div>
  </div>
</header>

<!-- Main -->
<section class="max-w-6xl mx-auto px-4 py-10">
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-blue-600 dark:text-blue-400">📂 Your Folders</h2>
    <form method="POST" class="flex gap-2">
      <input type="text" name="new_folder" placeholder="New folder name" required
             class="px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">➕ Create</button>
    </form>
  </div>

  <?php if (!$folders): ?>
    <div class="bg-yellow-100 text-yellow-800 p-4 rounded">No folders yet. Create one to start uploading!</div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php foreach ($folders as $folder): ?>
        <a href="folder.php?id=<?= $folder['id'] ?>" 
           class="bg-white dark:bg-gray-800 p-4 rounded shadow hover:shadow-md transition flex flex-col items-center justify-center">
          <img src="https://img.icons8.com/fluency/96/folder-invoices.png" class="w-16 h-16 mb-3" alt="folder">
          <span class="font-medium truncate"><?= htmlspecialchars($folder['folder_name']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
</body>
</html>
