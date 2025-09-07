<?php
include '../config/db.php';
session_start();

// Ensure only admin can access
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['folder'])) {
    die("❌ Invalid request");
}

$file_id = intval($_GET['id']);
$folder_id = intval($_GET['folder']);

// Fetch file info
$stmt = $pdo->prepare("SELECT p.*, f.folder_path 
                       FROM photos p 
                       JOIN folders f ON p.folder_id = f.id 
                       WHERE p.id = ?");
$stmt->execute([$file_id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    die("❌ File not found.");
}

// File path
$file_path = $file['folder_path'] . "/" . $file['filename'];

// Delete from disk if exists
if (file_exists($file_path)) {
    unlink($file_path);
}

// Delete from database
$del_stmt = $pdo->prepare("DELETE FROM photos WHERE id = ?");
$del_stmt->execute([$file_id]);

// Redirect back with message
$_SESSION['message'] = "🗑 File deleted successfully.";
header("Location: folder_manage.php?id=" . $folder_id);
exit();
