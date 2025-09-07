<?php
include '../config/db.php';
include 'admin_session.php';

$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT name FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin_name = $stmt->fetchColumn();
?>
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>


<!DOCTYPE html>
<html lang="en" class="scroll-smooth dark">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard - ImageHub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' };
  </script>
  <script>
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('-translate-x-full');
  }
</script>

</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">

<div class="flex h-screen">
  <!-- sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-6 overflow-y-auto">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-blue-600 dark:text-blue-400">📊 Admin Dashboard</h2>
      <!-- Dark mode toggle -->
      <div id="darkToggle" class="w-14 h-8 bg-gray-300 dark:bg-gray-700 rounded-full p-1 flex items-center cursor-pointer relative">
        <div id="toggleCircle" class="w-6 h-6 bg-white dark:bg-gray-800 rounded-full shadow transform transition-transform flex items-center justify-center">
          <span id="toggleIcon" class="text-yellow-400 dark:text-white">🌞</span>
        </div>
      </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Total Users</h3>
        <p class="text-3xl font-bold text-blue-500">
          <?= $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(); ?>
        </p>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Total Photos</h3>
        <p class="text-3xl font-bold text-green-500">
          <?= $pdo->query("SELECT COUNT(*) FROM photos")->fetchColumn(); ?>
        </p>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Admins</h3>
        <p class="text-3xl font-bold text-purple-500">
          <?= $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn(); ?>
        </p>
      </div>
    </div>
  </main>
</div>

<script>
  const root = document.documentElement;
  const toggle = document.getElementById("darkToggle");
  const circle = document.getElementById("toggleCircle");
  const icon = document.getElementById("toggleIcon");

  toggle.addEventListener("click", () => {
    root.classList.toggle("dark");
    const isDark = root.classList.contains("dark");
    circle.style.transform = isDark ? "translateX(24px)" : "translateX(0)";
    icon.textContent = isDark ? "🌙" : "☀️";
    localStorage.setItem("theme", isDark ? "dark" : "light");
  });

  if (localStorage.getItem("theme") === "dark") {
    root.classList.add("dark");
    circle.style.transform = "translateX(24px)";
    icon.textContent = "🌙";
  }
</script>

</body>
</html>
