<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <title>ImageHub - Home</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' };
  </script>
  <style>
    .transition-all { transition: all 0.3s ease; }
  </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 transition-all">

  <!-- ✅ Sticky Navbar -->
  <header class="bg-white dark:bg-gray-800 shadow sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
      
      <!-- Logo + Name -->
      <div class="flex items-center space-x-2">
        <img src="https://img.icons8.com/color/48/camera--v1.png" alt="logo" class="h-8 w-8" />
        <h1 class="text-xl font-bold text-blue-600 dark:text-blue-400">ImageHub</h1>
      </div>

      <!-- Nav Links -->
      <nav class="hidden md:flex items-center space-x-6 font-medium">
        <a href="#home" class="hover:text-blue-600 dark:hover:text-blue-400">Home</a>
        <a href="#about" class="hover:text-blue-600 dark:hover:text-blue-400">About</a>
        <a href="#how" class="hover:text-blue-600 dark:hover:text-blue-400">How it Works</a>
      </nav>

      <!-- Buttons + Toggle -->
      <div class="flex items-center space-x-2">
        <a href="auth/user_login.php" class="bg-blue-500 text-white px-3 py-2 rounded hover:bg-blue-600 text-sm">User Login</a>
        <a href="auth/user_register.php" class="bg-green-500 text-white px-3 py-2 rounded hover:bg-green-600 text-sm">Register</a>
        <a href="auth/admin_login.php" class="bg-gray-800 text-white px-3 py-2 rounded hover:bg-gray-900 text-sm">😜</a>

        <!-- 🌙 Toggle -->
        <div id="darkToggle" class="w-14 h-8 flex items-center bg-gray-300 dark:bg-gray-600 rounded-full p-1 cursor-pointer relative ml-3 transition-all">
          <div id="darkCircle" class="w-6 h-6 bg-white dark:bg-gray-800 rounded-full shadow-md transform transition-transform duration-300 flex items-center justify-center">
            <span id="darkIcon" class="text-yellow-400 dark:text-gray-300 text-lg">☀️</span>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- 🏠 Hero -->
  <section id="home" class="bg-blue-50 dark:bg-gray-800 py-20 text-center">
    <h2 class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-4 animate-fade-in">Delivering Memories with Ease</h2>
    <p class="max-w-2xl mx-auto text-lg text-gray-600 dark:text-gray-300 animate-fade-in">
      A secure platform for photographers and event managers to upload and deliver photos. Clients can log in anytime to download their images.
    </p>
  </section>

  <!-- 👨‍💻 About -->
  <section id="about" class="py-16 bg-white dark:bg-gray-900">
    <div class="max-w-5xl mx-auto px-6 text-center">
      <h3 class="text-3xl font-semibold mb-4">About ImageHub</h3>
      <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
        ImageHub simplifies how photos are shared after events. Whether you're a wedding photographer, a school coordinator, or an event studio — ImageHub gives you the tools to organize, protect, and distribute event photos to the right people.
      </p>
    </div>
  </section>

  <!-- ⚙️ How It Works -->
  <section id="how" class="py-16 bg-gray-100 dark:bg-gray-800">
    <div class="max-w-6xl mx-auto px-6 text-center">
      <h3 class="text-3xl font-semibold mb-8">How It Works</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
        <div class="bg-white dark:bg-gray-700 p-6 rounded shadow hover:shadow-lg transition-all">
          <h4 class="font-bold text-xl mb-2">📤 Admin Uploads</h4>
          <p class="text-gray-600 dark:text-gray-300">Admins upload photos for specific users via a secure dashboard.</p>
        </div>
        <div class="bg-white dark:bg-gray-700 p-6 rounded shadow hover:shadow-lg transition-all">
          <h4 class="font-bold text-xl mb-2">🔐 User Logs In</h4>
          <p class="text-gray-600 dark:text-gray-300">Users log in and see only their own photos in a clean gallery.</p>
        </div>
        <div class="bg-white dark:bg-gray-700 p-6 rounded shadow hover:shadow-lg transition-all">
          <h4 class="font-bold text-xl mb-2">📥 Download Anytime</h4>
          <p class="text-gray-600 dark:text-gray-300">Users can view or download their files from anywhere, anytime.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- 📩 Footer -->
  <footer class="bg-gray-800 text-white text-center py-6">
    <p>&copy; <?= date('Y') ?> ImageHub. Made with 💙 by Heer Patel.</p>
  </footer>

  <!-- 🌙 Toggle Script -->
  <script>
    const root = document.documentElement;
    const toggle = document.getElementById("darkToggle");
    const circle = document.getElementById("darkCircle");
    const icon = document.getElementById("darkIcon");

    toggle.addEventListener("click", () => {
      root.classList.toggle("dark");
      const isDark = root.classList.contains("dark");

      circle.style.transform = isDark ? "translateX(24px)" : "translateX(0)";
      icon.textContent = isDark ? "🌙" : "☀️";
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });

    // Load from localStorage
    if (localStorage.getItem('theme') === 'dark') {
      root.classList.add('dark');
      circle.style.transform = "translateX(24px)";
      icon.textContent = "🌙";
    }
  </script>

  <!-- ✨ Animations -->
  <style>
    .animate-fade-in {
      opacity: 0;
      transform: translateY(20px);
      animation: fadeIn 1s ease-out forwards;
    }

    @keyframes fadeIn {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>

</body>
</html>
