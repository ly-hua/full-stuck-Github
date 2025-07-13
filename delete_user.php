<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("Invalid user ID.");
}

// Delete avatar file
$stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($user = $result->fetch_assoc()) {
    if (!empty($user['avatar'])) {
        $avatarPath = __DIR__ . '/uploads/' . $user['avatar'];
        if (file_exists($avatarPath)) {
            unlink($avatarPath);
        }
    }
}

// Delete user
$delete = $conn->prepare("DELETE FROM users WHERE id = ?");
$delete->bind_param("i", $id);
$delete->execute();

header("Location: users.php?deleted=1");
exit;
