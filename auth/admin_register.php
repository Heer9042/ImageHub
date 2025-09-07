<?php
include '../config/db.php';
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm = $_POST['confirm'];

  if ($password !== $confirm) {
    $error = "Passwords do not match!";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email address!";
  } else {
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
      $error = "Email already registered.";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
      $stmt->execute([$name, $email, $hash]);
      $_SESSION['success'] = "Admin registered successfully. You can now log in.";
      header("Location: admin_login.php");
      exit();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <title>Admin Register - ImageHub</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4">
  <div class="w-full max-w-md bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold mb-4 text-center text-blue-600">🧑‍💼 Admin Register</h2>

    <?php if (!empty($error)): ?>
      <div class="bg-red-100 text-red-800 p-3 rounded mb-4 text-sm"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Full Name</label>
        <input type="text" name="name" required class="w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:ring-blue-300" />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="email" name="email" required class="w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:ring-blue-300" />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Password</label>
        <input type="password" name="password" required minlength="6" class="w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:ring-blue-300" />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Confirm Password</label>
        <input type="password" name="confirm" required class="w-full border px-3 py-2 rounded focus:outline-none focus:ring focus:ring-blue-300" />
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Register</button>
      <p class="text-sm mt-2 text-center">Already have an account? <a href="admin_login.php" class="text-blue-600 hover:underline">Login here</a></p>
    </form>
  </div>
</body>
</html>
