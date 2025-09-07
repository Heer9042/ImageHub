<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/user_login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];

  // Remove avatar
  if (isset($_POST['remove_avatar'])) {
    $stmt = $pdo->prepare("UPDATE users SET profile_photo = NULL WHERE id = ?");
    $stmt->execute([$user_id]);
    $_SESSION['avatar'] = '';
  }

  // Upload avatar
  elseif (!empty($_FILES['profile_photo']['name'])) {
    $allowed_types = ['image/jpeg', 'image/png'];
    if (in_array($_FILES['profile_photo']['type'], $allowed_types) && $_FILES['profile_photo']['size'] <= 2 * 1024 * 1024) {
      $target_dir = "../uploads/avatars/";
      if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

      $filename = "user_" . $user_id . "_" . time() . "_" . basename($_FILES["profile_photo"]["name"]);
      $target_file = $target_dir . $filename;

      move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file);

      $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, profile_photo = ? WHERE id = ?");
      $stmt->execute([$name, $email, $filename, $user_id]);
      $_SESSION['avatar'] = $filename;
    } else {
      $_SESSION['message'] = "⚠ Only JPG/PNG images under 2MB are allowed.";
      header("Location: profile.php");
      exit();
    }
  } else {
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $email, $user_id]);
  }

  if (!isset($_SESSION['message'])) {
    $_SESSION['message'] = "✅ Profile updated successfully!";
  }
  header("Location: profile.php");
  exit();
}

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$profile_img = $user['profile_photo'] ? '../uploads/avatars/' . htmlspecialchars($user['profile_photo']) : '../uploads/avatars/default.webp';
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth dark">
<head>
  <meta charset="UTF-8" />
  <title>Profile - ImageHub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { darkMode: 'class' };</script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">

<!-- Navbar -->
<nav class="bg-white dark:bg-gray-800 shadow sticky top-0 z-50">
  <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
    <div class="flex items-center gap-2">
      <img src="https://img.icons8.com/color/48/camera--v1.png" alt="logo" class="h-8 w-8">
      <span class="text-lg font-bold text-blue-600 dark:text-blue-400">ImageHub</span>
    </div>
    <div class="md:flex hidden items-center gap-4 text-sm font-medium">
      <a href="user_dashboard.php" class="hover:text-blue-600 dark:hover:text-blue-300">🏠 Home</a>
      <a href="../auth/change_password.php" class="hover:text-blue-600 dark:hover:text-blue-300">🔑 Forgot Password</a>
      <a href="profile.php" class="hover:text-blue-600 dark:hover:text-blue-300 underline text-blue-500">👤 My Profile</a>
      <img src="<?= $profile_img ?>" class="w-8 h-8 rounded-full object-cover border" alt="Avatar" />
      <a href="../auth/logout.php" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Logout</a>
    </div>
    <!-- Mobile Menu Button -->
    <button class="md:hidden block" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')">
      ☰
    </button>
  </div>
  <div id="mobileMenu" class="md:hidden hidden px-4 pb-4 space-y-2 text-sm">
    <a href="user_dashboard.php" class="block text-blue-600">🏠 Home</a>
    <a href="../auth/forgot_password.php" class="block">🔑 Forgot Password</a>
    <a href="profile.php" class="block">👤 My Profile</a>
    <a href="../auth/logout.php" class="block text-red-500">🚪 Logout</a>
  </div>
</nav>

<div class="max-w-2xl mx-auto px-4 py-12">
  <h1 class="text-3xl font-bold text-center text-blue-600 dark:text-blue-400 mb-8">👤 Edit Your Profile</h1>

  <?php if (isset($_SESSION['message'])): ?>
    <div class="bg-blue-100 text-blue-800 p-4 rounded mb-6 text-center">
      <?= $_SESSION['message']; unset($_SESSION['message']); ?>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="space-y-6 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md" onsubmit="return validateImage();">

    <div class="flex flex-col items-center text-center">
      <img id="avatarPreview" src="<?= $profile_img ?>" class="w-28 h-28 rounded-full object-cover border-4 border-blue-400 dark:border-blue-500 shadow mb-3" alt="Profile Photo" />
      <label class="text-sm text-gray-600 dark:text-gray-300 mb-2">Change Profile Photo (Max 2MB)</label>
      <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png"
             class="text-sm text-gray-700 dark:text-gray-200 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-blue-500 file:text-white hover:file:bg-blue-600"
             onchange="previewAvatar(event)" />
      <?php if ($user['profile_photo']): ?>
        <button type="submit" name="remove_avatar" class="mt-2 text-red-500 hover:underline text-sm">🗑 Remove Avatar</button>
      <?php endif; ?>
    </div>

    <div>
      <label class="block mb-1 font-medium">Full Name</label>
      <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required
             class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring focus:ring-blue-400" />
    </div>

    <div>
      <label class="block mb-1 font-medium">Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
             class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring focus:ring-blue-400" />
    </div>

    <div id="spinner" class="hidden text-center text-blue-500 text-sm">🔄 Uploading, please wait...</div>

    <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg shadow transition">📂 Save Changes</button>
  </form>
</div>

<script>
  function previewAvatar(event) {
    const reader = new FileReader();
    reader.onload = () => {
      document.getElementById('avatarPreview').src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
  }

  function validateImage() {
    const file = document.getElementById('profile_photo').files[0];
    if (file) {
      const validTypes = ['image/jpeg', 'image/png' , 'image/heic', 'image/heif'];

      if (!validTypes.includes(file.type)) {
        alert('❌ Only JPG or PNG images allowed!');
        return false;
      }
      if (file.size > 2 * 1024 * 1024) {
        alert('❌ Max file size is 2MB!');
        return false;
      }
      document.getElementById('spinner').classList.remove('hidden');
    }
    return true;
  }
</script>

</body>
</html>