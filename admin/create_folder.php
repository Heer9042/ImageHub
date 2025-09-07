<?php
include '../config/db.php';
session_start();

// Ensure only admin can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

$message = "";
$allowed_exts = ['jpg','jpeg','png','gif','jfif','webp','mp4','mov','avi','mkv','webm'];

// Fetch users for dropdown
$user_stmt = $pdo->prepare("SELECT id, name FROM users ORDER BY name ASC");
$user_stmt->execute();
$all_users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle create folder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_folder'])) {
    $user_id = intval($_POST['folder_user_id']);
    $folder_name = trim($_POST['folder_name']);

    if ($user_id > 0 && !empty($folder_name)) {
        // sanitize folder name
        $safe_folder = preg_replace('/[^a-zA-Z0-9_-]/', '_', $folder_name);

        $user_folder = "../uploads/user_" . $user_id;
        $final_folder = $user_folder . "/" . $safe_folder;

        // Ensure user folder exists
        if (!is_dir($user_folder)) {
            mkdir($user_folder, 0777, true);
        }

        // Create only if not exists
        if (!is_dir($final_folder)) {
            mkdir($final_folder, 0777, true);

            $stmt = $pdo->prepare("INSERT INTO folders (user_id, folder_name, folder_path) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $safe_folder, $final_folder]);

            $_SESSION['message'] = "📂 Folder '" . htmlspecialchars($safe_folder) . "' created successfully!";
            header("Location: photos.php");
            exit();
        } else {
            $message = "❌ Folder already exists for this user.";
        }
    } else {
        $message = "❌ Please enter a valid folder name and select a user.";
    }
}

// Fetch all folders
$folder_stmt = $pdo->prepare("SELECT f.*, u.name AS owner 
                              FROM folders f 
                              JOIN users u ON f.user_id=u.id 
                              ORDER BY f.created_at DESC");
$folder_stmt->execute();
$folders = $folder_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <title>Admin - Media Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' }
  </script>
</head>
<body class="flex bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">

<?php include 'sidebar.php'; ?>

<main class="flex-1 p-6">
  <h1 class="text-2xl font-bold mb-6 text-blue-600 dark:text-blue-400">📂 Media Management</h1>

  <!-- Flash Messages -->
  <?php if (!empty($message)): ?>
    <div class="bg-red-100 text-red-800 p-3 rounded mb-4"><?= htmlspecialchars($message) ?></div>
  <?php elseif (isset($_SESSION['message'])): ?>
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4" id="flash-msg">
      <?= $_SESSION['message']; unset($_SESSION['message']); ?>
    </div>
    <script>
      setTimeout(() => { document.getElementById('flash-msg')?.remove(); }, 3000);
    </script>
  <?php endif; ?>

  <!-- Create Folder Form -->
  <div class="mb-8 bg-white dark:bg-gray-800 p-5 rounded shadow max-w-lg">
    <h2 class="text-lg font-semibold mb-4">➕ Create Folder</h2>
    <form method="POST" class="space-y-3">
      <div>
        <label class="block mb-1 text-sm">Select User</label>
        <select name="folder_user_id" required class="w-full p-2 rounded border dark:bg-gray-700 dark:text-white">
          <option value="">-- Select user --</option>
          <?php foreach ($all_users as $user): ?>
            <option value="<?= $user['id'] ?>">
              <?= htmlspecialchars($user['name']) ?> (ID: <?= $user['id'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block mb-1 text-sm">Folder Name</label>
        <input type="text" name="folder_name" placeholder="Folder Name" required
               class="w-full p-2 rounded border dark:bg-gray-700 dark:text-white">
      </div>
      <button type="submit" name="create_folder"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">📂 Create Folder</button>
    </form>
  </div>

  <!-- List Folders -->
  <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">📁 Existing Folders</h2>
    <ul class="space-y-2">
      <?php foreach ($folders as $folder): ?>
        <li class="p-3 bg-gray-100 dark:bg-gray-700 rounded flex justify-between items-center">
          <div>
            <span class="font-medium"><?= htmlspecialchars($folder['folder_name']) ?></span>
            <span class="text-sm text-gray-500"> (Owner: <?= htmlspecialchars($folder['owner']) ?>)</span>
          </div>
          <div class="flex space-x-3">
            <a href="<?= str_replace('../', '', $folder['folder_path']) ?>" 
               target="_blank" 
               class="text-blue-500 hover:underline">Open</a>
            <a href="folder_manage.php?id=<?= $folder['id'] ?>" 
               class="text-green-500 hover:underline">Manage</a>
          </div>
        </li>
      <?php endforeach; ?>
      <?php if (empty($folders)): ?>
        <li class="text-gray-500">No folders created yet.</li>
      <?php endif; ?>
    </ul>
  </div>

</main>
</body>
</html>
