<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, fullname, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            // ✅ Redirect to dashboard on successful login
            header("Location: dashboard.php");
            exit;
        } else {
            echo "<script>alert('Wrong password'); window.location.href='index.html';</script>";
        }
    } else {
        echo "<script>alert('No user found with this email'); window.location.href='index.html';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
