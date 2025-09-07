<?php
include '../config/db.php';
session_start();

// Only allow logged-in admins
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/admin_login.php");
  exit();
}
?>
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <title>Admin - Users Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">

  <?php include 'sidebar.php'; ?>

  <main class="flex-1 p-6 overflow-x-auto">
    <h1 class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-6">👥 Users Management</h1>

    <?php if (isset($_SESSION['message'])): ?>
      <div id="flash-msg" class="bg-green-100 text-green-800 p-3 rounded mb-4">
        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
      </div>
      <script>
        setTimeout(() => {
          const msg = document.getElementById('flash-msg');
          if (msg) msg.style.display = 'none';
        }, 3000);
      </script>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
      <div class="flex items-center gap-2 w-full sm:w-auto">
        <input type="text" id="searchInput" placeholder="Search users..."
               class="px-4 py-2 w-full sm:w-64 border rounded dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
      </div>
      <a href="add_user.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">➕ Add User</a>
    </div>

    <!-- Users Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white dark:bg-gray-800 rounded shadow text-sm">
        <thead class="bg-gray-200 dark:bg-gray-700">
          <tr class="text-left">
            <th class="p-3">Avatar</th>
            <th class="p-3">Name</th>
            <th class="p-3">Email</th>
            <th class="p-3">Joined</th>
            <th class="p-3">Status</th>
            <th class="p-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="userTableBody">
          <!-- Users will be loaded here via AJAX -->
        </tbody>
      </table>
    </div>

    <!-- Pagination Placeholder -->
    <div class="mt-6 flex justify-center items-center gap-4 text-sm" id="pagination">
      <!-- Pagination loaded via JS if needed -->
    </div>
  </main>

  <!-- JavaScript: Fetch Users in Real Time -->
  <script>
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('userTableBody');
    const pagination = document.getElementById('pagination');

    function fetchUsers(query = '') {
      fetch(`search_users.php?search=${encodeURIComponent(query)}`)
        .then(res => res.text())
        .then(data => {
          tableBody.innerHTML = data;
          // Optional: load pagination dynamically
        });
    }

    searchInput.addEventListener('input', () => {
      fetchUsers(searchInput.value);
    });

    // Load users on page load
    window.addEventListener('DOMContentLoaded', () => fetchUsers());
  </script>
</body>
</html>
