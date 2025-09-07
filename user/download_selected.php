<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/user_login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$username = strtolower(str_replace(' ', '_', $user['name']));
$user_folder = "../uploads/{$username}_id{$user_id}/";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['selected_photos'])) {
  $zip = new ZipArchive();
  $zip_name = "selected_photos_" . time() . ".zip";

  if ($zip->open($zip_name, ZipArchive::CREATE) === TRUE) {
    foreach ($_POST['selected_photos'] as $file) {
      $file_path = $user_folder . basename($file);
      if (file_exists($file_path)) {
        $zip->addFile($file_path, basename($file));
      }
    }
    $zip->close();

    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename=' . basename($zip_name));
    header('Content-Length: ' . filesize($zip_name));
    readfile($zip_name);
    unlink($zip_name);
    exit;
  } else {
    echo "Failed to create zip file.";
  }
} else {
  echo "No photos selected.";
}
?>
