<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

include 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // ID is missing or invalid
    header("Location: add_category.php");
    exit;
}

$id = intval($_GET['id']);

// Prepare and execute delete statement
$stmt = $conn->prepare("DELETE FROM category WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();

// Redirect back to category page
header("Location: add_category.php");
exit;
