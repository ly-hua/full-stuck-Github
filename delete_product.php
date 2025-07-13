<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

// Check if ID is provided and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php?msg=invalid_id");
    exit;
}

$id = intval($_GET['id']);

// Prepare and execute DELETE statement
$stmt = $conn->prepare("DELETE FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();

// Optional: check if row was deleted
if ($stmt->affected_rows === 1) {
    header("Location: dashboard.php?msg=deleted");
} else {
    header("Location: dashboard.php?msg=not_found");
}
exit;
