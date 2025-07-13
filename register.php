<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body { display: flex; justify-content: center; align-items: center; height: 100vh; }
    .form-box { width: 100%; max-width: 400px; }
  </style>
</head>
<body>
  <form action="register.php" method="POST" class="form-box text-center shadow p-4">
    <img src="profile.png" alt="Logo" style="width: 60px;" class="mb-3" />
    <input type="text" name="fullname" class="form-control mb-2" placeholder="Full Name" required>
    <input type="text" name="phone" class="form-control mb-2" placeholder="Phone" required>
    <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
    <button type="submit" class="btn btn-primary w-100">Register</button>
    <p class="mt-3"><a href="index.html">Already have an account? Login</a></p>
  </form>
</body>
</html>
<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['fullname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (fullname, phone, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $email, $password);

    if ($stmt->execute()) {
        header("Location: index.html");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
