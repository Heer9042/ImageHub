
<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
  http_response_code(403);
  exit('Unauthorized');
}

$search = $_GET['search'] ?? '';
$filter = $search ? "WHERE name LIKE :search OR email LIKE :search" : '';

$query = "SELECT id, name, email, created_at, profile_photo, is_banned FROM users $filter ORDER BY created_at DESC LIMIT 50";
$stmt = $pdo->prepare($query);
if ($search) {
  $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->execute();
$users = $stmt->fetchAll();

if ($users):
  foreach ($users as $user):
    $avatar = $user['profile_photo']
      ? "../uploads/avatars/" . htmlspecialchars($user['profile_photo'])
      : "https://via.placeholder.com/40x40?text=User";
    $isBanned = (int)($user['is_banned'] ?? 0);
    ?>
    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
      <td class="p-3">
        <img src="<?= $avatar ?>" class="w-10 h-10 rounded-full object-cover border" alt="Avatar" />
      </td>
      <td class="p-3"><?= htmlspecialchars($user['name']) ?></td>
      <td class="p-3"><?= htmlspecialchars($user['email']) ?></td>
      <td class="p-3"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
      <td class="p-3">
        <span class="px-2 py-1 rounded text-sm <?= $isBanned ? 'bg-red-200 text-red-700' : 'bg-green-200 text-green-700' ?>">
          <?= $isBanned ? 'Banned' : 'Active' ?>
        </span>
      </td>
      <td class="p-3 text-center space-x-2">
        <a href="edit_user.php?id=<?= $user['id'] ?>" class="text-blue-500 hover:underline">Edit</a>
        <a href="delete_user.php?id=<?= $user['id'] ?>" class="text-red-500 hover:underline" onclick="return confirm('Delete this user?')">Delete</a>
        <a href="toggle_ban.php?id=<?= $user['id'] ?>" class="text-yellow-500 hover:underline">
          <?= $isBanned ? 'Unban' : 'Ban' ?>
        </a>
      </td>
    </tr>
<?php
  endforeach;
else:
?>
  <tr>
    <td colspan="6" class="p-4 text-center text-gray-500">No users found.</td>
  </tr>
<?php
endif;
