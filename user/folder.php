<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/user_login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Get folder ID
$folder_id = intval($_GET['id'] ?? 0);
if ($folder_id <= 0) {
  header("Location: dashboard.php");
  exit();
}

// Get folder info (only if belongs to this user)
$stmt = $pdo->prepare("SELECT * FROM folders WHERE id = ? AND user_id = ?");
$stmt->execute([$folder_id, $user_id]);
$folder = $stmt->fetch();

if (!$folder) {
  die("❌ Folder not found or access denied.");
}

$folder_name = $folder['folder_name'];
$folder_path = $folder['folder_path'];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos'])) {
  if (!is_dir($folder_path)) {
    mkdir($folder_path, 0777, true);
  }

  $totalFiles = count($_FILES['photos']['name']);
  for ($i = 0; $i < $totalFiles; $i++) {
    $tmp_name = $_FILES['photos']['tmp_name'][$i];
    $original_name = $_FILES['photos']['name'][$i];
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
      $filename = time() . "_" . rand(1000, 9999) . "." . $ext;
      $uploadPath = $folder_path . "/" . $filename;

      if (move_uploaded_file($tmp_name, $uploadPath)) {
        $stmt = $pdo->prepare("INSERT INTO photos (user_id, folder_id, filename, uploaded_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $folder_id, $filename]);
      }
    }
  }
  header("Location: folder.php?id=" . $folder_id);
  exit();
}

// Fetch photos
$stmt = $pdo->prepare("SELECT * FROM photos WHERE user_id = ? AND folder_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$user_id, $folder_id]);
$photos = $stmt->fetchAll();

// Get user info (for header avatar)
$stmt = $pdo->prepare("SELECT name, profile_photo FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_name = $user['name'];
$avatar = $user['profile_photo'];
$avatar_path = $avatar ? "../uploads/avatars/" . htmlspecialchars($avatar) : "../uploads/avatars/default.webp";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($folder_name) ?> - ImageHub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { darkMode: 'class' }</script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">

<!-- Header -->
<header class="bg-white dark:bg-gray-800 shadow sticky top-0 z-50">
  <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
    <h1 class="text-xl font-bold text-blue-600 dark:text-blue-400">📂 <?= htmlspecialchars($folder_name) ?></h1>
    <div class="flex items-center gap-3">
      <span class="hidden sm:block text-sm">Hi, <?= htmlspecialchars($user_name) ?>!</span>
      <a href="user_dashboard.php" class="text-sm font-medium">dashboard</a>
      <a href="./profile.php"><img src="<?= $avatar_path ?>" class="w-9 h-9 rounded-full border object-cover"></a>
      <a href="../auth/logout.php" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 text-sm">Logout</a>
    </div>
  </div>
</header>

<!-- Main -->
<section class="max-w-6xl mx-auto px-4 py-8">

  <!-- Upload form -->
  <form method="POST" enctype="multipart/form-data" class="mb-6 bg-white dark:bg-gray-800 p-4 rounded shadow">
    <label class="block font-medium mb-2">Upload Photos</label>
    <input type="file" name="photos[]" multiple required class="w-full mb-3 text-sm dark:bg-gray-700 dark:text-white" />
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">⬆ Upload</button>
  </form>

  <!-- Download buttons -->
  <div class="flex items-center justify-between mb-6">
    <a href="download_zip.php?folder_id=<?= $folder_id ?>" 
       class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm">⬇ Download All</a>
    <input type="text" id="searchInput" placeholder="Search photos..."
           class="px-3 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
  </div>

  <?php if (!$photos): ?>
    <div class="bg-yellow-100 text-yellow-800 p-4 rounded">No photos in this folder yet.</div>
  <?php else: ?>
    <form method="POST" action="download_selected.php">
      <input type="hidden" name="folder_id" value="<?= $folder_id ?>">

      <div class="flex items-center space-x-2 mb-4">
        <input type="checkbox" id="selectAll" class="h-5 w-5 text-blue-600">
        <label for="selectAll" class="text-sm font-medium">Select All</label>
      </div>

      <div id="photoGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($photos as $photo): 
          $path = $folder_path . "/" . $photo['filename']; ?>
          <div class="bg-white dark:bg-gray-800 p-3 rounded shadow hover:shadow-md transition photo-card" 
               data-name="<?= htmlspecialchars($photo['filename']) ?>">
            <label class="flex items-center space-x-2 mb-2">
              <input type="checkbox" name="selected_photos[]" value="<?= htmlspecialchars($photo['filename']) ?>" class="h-4 w-4 text-blue-600">
              <span class="text-sm truncate"><?= htmlspecialchars($photo['filename']) ?></span>
            </label>
            <img src="<?= htmlspecialchars($path) ?>" alt="Photo"
                 class="w-full h-48 object-cover rounded cursor-pointer open-lightbox" 
                 data-full="<?= htmlspecialchars($path) ?>" />
          </div>
        <?php endforeach; ?>
      </div>

      <div class="mt-6 text-center">
        <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700">⬇ Download Selected</button>
      </div>
    </form>
  <?php endif; ?>
</section>

<!-- Lightbox -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-80 hidden justify-center items-center z-50">
  <img id="lightbox-img" src="" class="max-h-[90vh] max-w-[90vw] rounded shadow" />
</div>

<script>
  // Search filter
  const searchInput = document.getElementById('searchInput');
  const photoCards = document.querySelectorAll('.photo-card');
  searchInput.addEventListener('input', () => {
    const keyword = searchInput.value.toLowerCase();
    photoCards.forEach(card => {
      const name = card.dataset.name.toLowerCase();
      card.style.display = name.includes(keyword) ? 'block' : 'none';
    });
  });

  // Select all
  document.getElementById('selectAll').addEventListener('change', (e) => {
    document.querySelectorAll('input[name="selected_photos[]"]').forEach(cb => cb.checked = e.target.checked);
  });

  // Lightbox
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightbox-img');
  document.querySelectorAll('.open-lightbox').forEach(img => {
    img.addEventListener('click', () => {
      lightboxImg.src = img.dataset.full;
      lightbox.classList.remove('hidden');
    });
  });
  lightbox.addEventListener('click', () => lightbox.classList.add('hidden'));
</script>
</body>
</html>
