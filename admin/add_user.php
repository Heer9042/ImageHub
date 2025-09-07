<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/admin_login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  $profile_photo = null;

  // Handle profile photo upload
  if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
    $filename = 'user_' . time() . '.' . $ext;
    $uploadPath = '../user/uploads/' . $filename;

    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadPath)) {
      $profile_photo = $filename;
    }
  }

  // Basic validation
  if ($name && $email && $password) {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, profile_photo, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$name, $email, $hashed_password, $profile_photo]);

    $_SESSION['message'] = "✅ User added successfully!";
    header("Location: users.php");
    exit();
  } else {
    $_SESSION['message'] = "⚠ Please fill in all fields.";
  }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <title>Add User - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">

<?php include 'sidebar.php'; ?>

<main class="flex-1 p-6">
  <h1 class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-4">➕ Add New User</h1>

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

  <form method="POST" enctype="multipart/form-data" class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded shadow-md max-w-md">
    <div>
      <label class="block mb-1 font-medium">Full Name</label>
      <input type="text" name="name" required class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
    </div>
    <div>
      <label class="block mb-1 font-medium">Email</label>
      <input type="email" name="email" required class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
    </div>
    <div>
      <label class="block mb-1 font-medium">Password</label>
      <input type="password" name="password" required class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
    </div>
    <div>
      <label class="block mb-1 font-medium">Profile Photo</label>
      <input type="file" name="profile_photo" accept="image/*"
             class="block w-full file:px-4 file:py-2 file:rounded file:border-0 text-sm text-gray-700 dark:text-gray-200">
    </div>
    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded">➕ Add User</button>
  </form>
</main>

</body>
</html>
