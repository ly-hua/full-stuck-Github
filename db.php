<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'ptour';

// Create a new MySQLi connection
$conn = new mysqli($host, $user, $pass, $db);

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set character set to UTF-8
?>
