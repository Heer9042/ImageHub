<?php
include '../config/db.php';

$currentPage = basename($_SERVER['PHP_SELF']);

// Default admin name
$admin_name = 'Admin';

// Get admin name if logged in
if (!empty($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $stmt = $pdo->prepare("SELECT name FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();
    if ($admin) {
        $admin_name = $admin['name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ImageHub Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' };
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen flex">

<!-- Mobile Header -->
<header class="md:hidden flex items-center justify-between bg-white dark:bg-gray-800 p-4 shadow fixed top-0 left-0 right-0 z-50">
  <div class="text-xl font-bold text-blue-600 dark:text-blue-400">📸 ImageHub</div>
  <button onclick="toggleSidebar()" class="text-gray-700 dark:text-white focus:outline-none">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
  </button>
</header>

<!-- Sidebar -->
<aside id="sidebar" class="fixed md:static z-40 top-0 left-0 w-64 bg-white dark:bg-gray-800 h-full md:h-auto transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out shadow-lg flex flex-col pt-20 md:pt-4">
  
  <!-- Logo -->
  <div class="hidden md:block text-xl font-bold mb-6 px-4 text-blue-600 dark:text-blue-400">
    📸 ImageHub Admin
  </div>

  <!-- Navigation -->
  <nav class="flex-1 space-y-1 px-2">
    <a href="dashboard.php" class="block py-2 px-3 rounded <?= $currentPage === 'dashboard.php' ? 'bg-blue-100 dark:bg-gray-700 font-semibold' : 'hover:bg-blue-50 dark:hover:bg-gray-700' ?>">📊 Dashboard</a>
    <a href="users.php" class="block py-2 px-3 rounded <?= $currentPage === 'users.php' ? 'bg-blue-100 dark:bg-gray-700 font-semibold' : 'hover:bg-blue-50 dark:hover:bg-gray-700' ?>">👥 Users</a>
    <a href="photos.php" class="block py-2 px-3 rounded <?= $currentPage === 'photos.php' ? 'bg-blue-100 dark:bg-gray-700 font-semibold' : 'hover:bg-blue-50 dark:hover:bg-gray-700' ?>">🖼 Media</a>
    <a href="create_folder.php" class="block py-2 px-3 rounded <?= $currentPage === 'folders.php' ? 'bg-blue-100 dark:bg-gray-700 font-semibold' : 'hover:bg-blue-50 dark:hover:bg-gray-700' ?>">📂 Folders</a>
    <a href="settings.php" class="block py-2 px-3 rounded <?= $currentPage === 'settings.php' ? 'bg-blue-100 dark:bg-gray-700 font-semibold' : 'hover:bg-blue-50 dark:hover:bg-gray-700' ?>">⚙️ Settings</a>
  </nav>

  <!-- Admin Info + Logout -->
  <div class="p-4 border-t border-gray-200 dark:border-gray-700 text-sm">
    <div class="flex justify-between items-center">
      <span class="text-gray-600 dark:text-gray-300">Hi, <?= htmlspecialchars($admin_name) ?></span>
      <a href="../auth/logout.php" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Logout</a>
    </div>
  </div>
</aside>

<!-- Push content down on mobile (space for fixed header) -->
<div class="md:hidden h-16"></div>

<!-- Sidebar Toggle Script -->
<script>
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
  }
</script>

</body>
</html>
