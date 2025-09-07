<?php
include '../config/db.php';
session_start();

// Ensure only admin can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

$message = "";

// Allowed file extensions
$allowed_images = ['jpg', 'jpeg', 'png', 'gif', 'jfif', 'webp'];
$allowed_videos = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
$allowed_exts = array_merge($allowed_images, $allowed_videos);

// Handle uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media'])) {
    $folder_id = isset($_POST['selected_folder_id']) ? intval($_POST['selected_folder_id']) : 0;

    if ($folder_id > 0) {
        // Get folder info
        $folder_stmt = $pdo->prepare("SELECT f.folder_path, f.user_id, f.folder_name, u.name AS user_name 
                                      FROM folders f 
                                      JOIN users u ON f.user_id = u.id 
                                      WHERE f.id = ?");
        $folder_stmt->execute([$folder_id]);
        $folder = $folder_stmt->fetch(PDO::FETCH_ASSOC);

        if ($folder) {
            $user_id = $folder['user_id'];
            $folder_path = $folder['folder_path'];

            if (!is_dir($folder_path)) {
                mkdir($folder_path, 0777, true);
            }

            $totalFiles = count($_FILES['media']['name']);
            for ($i = 0; $i < $totalFiles; $i++) {
                $tmp_name = $_FILES['media']['tmp_name'][$i];
                $original_name = $_FILES['media']['name'][$i];
                $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed_exts)) continue;

                if ($_FILES['media']['error'][$i] === UPLOAD_ERR_OK) {
                    $filename = 'file_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $uploadPath = $folder_path . '/' . $filename;

                    if (move_uploaded_file($tmp_name, $uploadPath)) {
                        $stmt = $pdo->prepare("INSERT INTO photos (user_id, folder_id, filename, file_type, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
                        $file_type = in_array($ext, $allowed_videos) ? 'video' : 'image';
                        $stmt->execute([$user_id, $folder_id, $filename, $file_type]);
                    }
                }
            }

            $_SESSION['message'] = "✅ Files uploaded successfully to folder <b>{$folder['folder_name']}</b>!";
            header("Location: photos.php");
            exit();
        }
    } else {
        $message = "❌ Please select a folder.";
    }
}

// Fetch all files with uploader + folder info
$stmt = $pdo->prepare("SELECT p.*, u.name AS uploader, f.folder_name, f.folder_path
                       FROM photos p 
                       JOIN users u ON p.user_id = u.id 
                       LEFT JOIN folders f ON p.folder_id = f.id 
                       ORDER BY p.uploaded_at DESC");
$stmt->execute();
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch folders for dropdown
$folder_stmt = $pdo->prepare("SELECT f.id, f.folder_name, u.name AS user_name 
                              FROM folders f 
                              JOIN users u ON f.user_id = u.id 
                              ORDER BY u.name, f.folder_name");
$folder_stmt->execute();
$all_folders = $folder_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <title>Admin - Media Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function filterDropdown(inputId, selectId) {
      const input = document.getElementById(inputId).value.toLowerCase();
      const select = document.getElementById(selectId);
      Array.from(select.options).forEach(opt => {
        opt.style.display = opt.text.toLowerCase().includes(input) ? '' : 'none';
      });
    }
  </script>
</head>
<body class="flex bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">

<?php include 'sidebar.php'; ?>

<main class="flex-1 p-6">
  <h1 class="text-3xl font-bold mb-6 text-blue-600 dark:text-blue-400">📂 Media Management</h1>

  <!-- Flash / Error Messages -->
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

  <!-- Upload Form -->
  <form method="POST" enctype="multipart/form-data" class="mb-8 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow max-w-lg">
    <label class="block font-semibold mb-2">📁 Select Folder</label>
    <select id="folderDropdown" name="selected_folder_id" required class="w-full mb-3 p-2 rounded-lg border dark:bg-gray-800 dark:text-white">
      <option value="">-- Choose a folder --</option>
      <?php foreach ($all_folders as $f): ?>
        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['user_name']) ?> → <?= htmlspecialchars($f['folder_name']) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="text" id="folderSearch" onkeyup="filterDropdown('folderSearch','folderDropdown')" 
           placeholder="🔍 Search folder..." 
           class="w-full p-2 mb-4 rounded-lg border dark:bg-gray-800 dark:text-white">

    <label class="block font-semibold mb-2">📤 Upload Files (Images or Videos)</label>
    <input type="file" name="media[]" multiple required 
           accept=".jpg,.jpeg,.png,.gif,.jfif,.webp,.mp4,.mov,.avi,.mkv,.webm,image/*,video/*"
           class="w-full mb-4 text-sm text-gray-800 dark:text-white file:mr-4 file:py-2 file:px-4 
                  file:rounded-lg file:border-0 file:text-sm file:font-semibold
                  file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />

    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow">
      ⬆ Upload Files
    </button>
  </form>

  <!-- Media Table -->
  <div class="overflow-x-auto">
    <table class="min-w-full bg-white dark:bg-gray-800 rounded-2xl shadow text-sm">
      <thead class="bg-gray-200 dark:bg-gray-700 text-left text-gray-700 dark:text-gray-200">
        <tr>
          <th class="p-3">Preview</th>
          <th class="p-3">Filename</th>
          <th class="p-3">Type</th>
          <th class="p-3">Uploader</th>
          <th class="p-3">Folder</th>
          <th class="p-3">Uploaded</th>
          <th class="p-3 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($photos): ?>
          <?php foreach ($photos as $photo): 
            $filePath = rtrim($photo['folder_path'], '/') . '/' . htmlspecialchars($photo['filename']);
          ?>
            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
              <td class="p-3">
                <?php if ($photo['file_type'] === 'image'): ?>
                  <img src="<?= $filePath ?>" alt="Media" class="w-16 h-16 object-cover rounded-lg border" />
                <?php else: ?>
                  <video src="<?= $filePath ?>" class="w-24 h-16 rounded-lg border" controls></video>
                <?php endif; ?>
              </td>
              <td class="p-3"><?= htmlspecialchars($photo['filename']) ?></td>
              <td class="p-3 capitalize"><?= htmlspecialchars($photo['file_type']) ?></td>
              <td class="p-3"><?= htmlspecialchars($photo['uploader']) ?></td>
              <td class="p-3 font-medium text-indigo-500"><?= htmlspecialchars($photo['folder_name'] ?? '-') ?></td>
              <td class="p-3"><?= date('d M Y, h:i A', strtotime($photo['uploaded_at'])) ?></td>
              <td class="p-3 text-center space-x-3">
                <a href="<?= $filePath ?>" target="_blank" class="text-blue-500 hover:underline">View</a>
                <a href="<?= $filePath ?>" download class="text-green-500 hover:underline">Download</a>
                <a href="delete_photo.php?id=<?= $photo['id'] ?>" onclick="return confirm('Delete this file?')" class="text-red-500 hover:underline">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center p-6 text-gray-500">No files uploaded yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
