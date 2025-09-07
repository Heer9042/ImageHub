<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/admin_login.php");
  exit();
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch existing user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
  echo "User not found!";
  exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'] ?? '';
  $email = $_POST['email'] ?? '';

  // Handle image upload
  $profile_photo = $user['profile_photo']; // default to old photo
  if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
    $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
    $uploadPath = '../user/uploads/' . $filename;
    move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadPath);
    $profile_photo = $filename;
  }

  $update = $pdo->prepare("UPDATE users SET name = ?, email = ?, profile_photo = ? WHERE id = ?");
  $update->execute([$name, $email, $profile_photo, $user_id]);

  $_SESSION['message'] = "User updated successfully!";
  header("Location: users.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <title>Edit User - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">

  <div class="max-w-xl mx-auto mt-10 bg-white dark:bg-gray-800 p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-4 text-blue-600">✏️ Edit User</h2>

    <?php if (isset($_SESSION['message'])): ?>
    <div id="flash-msg" class="bg-blue-100 text-blue-800 p-3 rounded mb-4">
      <?= $_SESSION['message']; unset($_SESSION['message']); ?>
    </div>
    <script>
      setTimeout(() => {
        const msg = document.getElementById('flash-msg');
        if (msg) msg.style.display = 'none';
      }, 5000);
    </script>
  <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block font-medium">Full Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required
               class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
      </div>
      <div>
        <label class="block font-medium">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
               class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
      </div>

      <div>
        <label class="block font-medium mb-1">Profile Photo</label>
        <img src="../uploads/avatars/<?= $user['profile_photo'] ?: 'default.jpg' ?>" class="w-20 h-20 rounded-full mb-2 object-cover border" alt="Current Avatar">
        <input type="file" name="profile_photo" accept="image/*"
               class="block w-full text-sm text-gray-600 dark:text-gray-200 file:bg-gray-100 file:border-0 file:rounded file:px-3 file:py-2 file:mr-4" />
      </div>

      <div class="pt-2">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">💾 Update</button>
        <a href="users.php" class="ml-2 text-gray-500 hover:underline">Cancel</a>
      </div>
    </form>
  </div>

</body>
</html>
