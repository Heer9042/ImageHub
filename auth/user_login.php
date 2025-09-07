<?php
include '../config/db.php';
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_banned']) {
                $message = "❌ Your account is banned.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['avatar'] = $user['profile_photo'];
                header("Location: ../user/user_dashboard.php");
                exit();
            }
        } else {
            $message = "❌ Invalid email or password.";
        }
    } else {
        $message = "❌ Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>User Login - ImageHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' };</script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 flex items-center justify-center min-h-screen">

    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold text-center text-blue-600 dark:text-blue-400 mb-6">👋 Welcome Back!</h1>

        <?php if (!empty($message)): ?>
            <div class="p-4 mb-4 text-sm text-red-800 bg-red-100 rounded-lg">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label for="email" class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="password" class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg shadow-md transition">Login</button>
        </form>

        <div class="text-center mt-4 text-sm text-gray-600 dark:text-gray-400">
            <p>Don't have an account? <a href="user_register.php" class="text-blue-500 hover:underline">Register here</a>.</p>
            <p><a href="change_password.php" class="text-blue-500 hover:underline">Forgot password?</a></p>
        </div>
    </div>

</body>
</html>