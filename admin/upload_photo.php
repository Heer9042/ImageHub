<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/admin_login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $filename = 'img_' . time() . '.' . $ext;
    $uploadPath = '../uploads/' . $filename;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
      $stmt = $pdo->prepare("INSERT INTO photos (user_id, filename, uploaded_at) VALUES (?, ?, NOW())");
      $stmt->execute([$_SESSION['admin_id'], $filename]);

      $_SESSION['message'] = "✅ Photo uploaded successfully!";
      header("Location: photos.php");
      exit();
    } else {
      $error = "❌ Failed to upload the file.";
    }
  } else {
    $error = "⚠ No file selected or upload error.";
  }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <title>Upload Photo - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">

<?php include 'sidebar.php'; ?>

<main class="flex-1 p-6">
  <h1 class="text-2xl font-bold text-blue-600 mb-4">📤 Upload New Photo</h1>

  <?php if (!empty($error)): ?>
    <div class="bg-red-100 text-red-800 p-3 rounded mb-4"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 p-6 rounded shadow-md max-w-md space-y-4">
    <div>
      <label class="block font-medium mb-1">Select Photo</label>
      <input type="file" name="photo" accept="image/*" required class="w-full text-sm file:px-4 file:py-2 file:rounded file:border-0 dark:text-white">
    </div>
    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Upload</button>
    <a href="photos.php" class="ml-4 text-gray-500 hover:underline">Back to Photos</a>
  </form>
</main>
</body>
</html>
