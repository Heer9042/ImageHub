<?php
include '../config/db.php';
session_start();

// Ensure only admin can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid folder ID");
}

$folder_id = intval($_GET['id']);

// Fetch folder info
$stmt = $pdo->prepare("SELECT f.*, u.name AS owner 
                       FROM folders f 
                       JOIN users u ON f.user_id=u.id 
                       WHERE f.id = ?");
$stmt->execute([$folder_id]);
$folder = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$folder) {
    die("Folder not found.");
}

$folder_path = $folder['folder_path'];
$owner = $folder['owner'];

$message = "";

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $allowed_exts = ['jpg','jpeg','png','gif','jfif','webp','mp4','mov','avi','mkv','webm'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_exts)) {
            $safe_name = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
            $target = $folder_path . "/" . $safe_name;

            if (move_uploaded_file($file['tmp_name'], $target)) {
                $stmt = $pdo->prepare("INSERT INTO photos (user_id, filename, folder_id, uploaded_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$folder['user_id'], $safe_name, $folder_id]);

                $message = "✅ File uploaded successfully!";
            } else {
                $message = "❌ Failed to upload file.";
            }
        } else {
            $message = "❌ Invalid file type.";
        }
    } else {
        $message = "❌ Error during upload.";
    }
}

// Fetch all files for this folder
$file_stmt = $pdo->prepare("SELECT * FROM photos WHERE folder_id = ? ORDER BY uploaded_at DESC");
$file_stmt->execute([$folder_id]);
$files = $file_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <title>Manage Folder - <?= htmlspecialchars($folder['folder_name']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">

<?php include 'sidebar.php'; ?>

<main class="flex-1 p-6">
  <h1 class="text-2xl font-bold mb-6 text-blue-600 dark:text-blue-400">📁 Manage Folder: <?= htmlspecialchars($folder['folder_name']) ?></h1>
  <p class="mb-4 text-gray-600 dark:text-gray-400">Owner: <?= htmlspecialchars($owner) ?></p>

  <!-- Message -->
  <?php if (!empty($message)): ?>
    <div class="bg-yellow-100 text-yellow-800 p-3 rounded mb-4"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <!-- Upload Form -->
  <div class="mb-8 bg-white dark:bg-gray-800 p-5 rounded shadow max-w-lg">
    <h2 class="text-lg font-semibold mb-4">📤 Upload File</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-3">
      <input type="file" name="file" required class="block w-full text-sm text-gray-600 dark:text-gray-300">
      <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Upload</button>
    </form>
  </div>

  <!-- File List -->
  <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">📸 Files in this folder</h2>
    <?php if (empty($files)): ?>
      <p class="text-gray-500">No files uploaded yet.</p>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <?php foreach ($files as $file): 
          $file_path = $folder_path . "/" . $file['filename'];
          $ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
        ?>
          <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded shadow">
            <p class="truncate text-sm mb-2"><?= htmlspecialchars($file['filename']) ?></p>
            
            <?php if (in_array($ext, ['jpg','jpeg','png','gif','webp','jfif'])): ?>
              <img src="<?= str_replace('../','',$file_path) ?>" class="w-full h-40 object-cover rounded mb-2" alt="">
            <?php elseif (in_array($ext, ['mp4','mov','avi','mkv','webm'])): ?>
              <video controls class="w-full h-40 rounded mb-2">
                <source src="<?= str_replace('../','',$file_path) ?>" type="video/<?= $ext ?>">
              </video>
            <?php else: ?>
              <div class="bg-gray-300 text-gray-700 text-sm p-3 rounded mb-2">📄 <?= strtoupper($ext) ?> File</div>
            <?php endif; ?>

            <div class="flex justify-between">
              <a href="<?= str_replace('../','',$file_path) ?>" download class="text-blue-600 hover:underline">⬇ Download</a>
              <a href="delete_file.php?id=<?= $file['id'] ?>&folder=<?= $folder_id ?>" class="text-red-600 hover:underline">🗑 Delete</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
