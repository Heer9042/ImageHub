<?php
include '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
  if ($stmt->execute([$name, $email, $password])) {
    $_SESSION['user_id'] = $pdo->lastInsertId();
    header("Location: ./user_login.php");
    exit();
  } else {
    echo "Registration failed.";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>User Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <form method="post" class="bg-white p-6 rounded shadow w-full max-w-sm">
    <h2 class="text-xl font-bold mb-4">User Registration</h2>
    <input type="text" name="name" placeholder="Name" required class="w-full mb-3 p-2 border rounded" />
    <input type="email" name="email" placeholder="Email" required class="w-full mb-3 p-2 border rounded" />
    <input type="password" name="password" placeholder="Password" required class="w-full mb-3 p-2 border rounded" />
    <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded">Register</button>
  </form>
</body>
</html>
