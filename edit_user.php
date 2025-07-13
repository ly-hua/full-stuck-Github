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

// Fetch user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

$errors = [];
$success = '';

// Update if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation
    if ($fullname === '') $errors[] = "Full name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if ($role === '') $errors[] = "Role is required.";

    // Handle avatar update
    $avatar = $user['avatar'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['avatar']['tmp_name'];
        $fileName = basename($_FILES['avatar']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $newFile = uniqid() . "." . $ext;
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $target = $uploadDir . $newFile;
            if (move_uploaded_file($fileTmp, $target)) {
                $avatar = $newFile;
            } else {
                $errors[] = "Avatar upload failed.";
            }
        } else {
            $errors[] = "Invalid image format.";
        }
    }

    if (empty($errors)) {
        // If password is filled, update it
        if ($password !== '') {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET fullname=?, email=?, phone=?, address=?, city=?, country=?, role=?, description=?, avatar=?, password=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssssi", $fullname, $email, $phone, $address, $city, $country, $role, $description, $avatar, $hashed, $id);
        } else {
            $sql = "UPDATE users SET fullname=?, email=?, phone=?, address=?, city=?, country=?, role=?, description=?, avatar=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssi", $fullname, $email, $phone, $address, $city, $country, $role, $description, $avatar, $id);
        }

        if ($stmt->execute()) {
            header("Location: users.php?updated=1");
            exit;
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container my-4" style="max-width: 700px;">
  <h3>Edit User</h3>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <table class="table table-bordered">
      <tr><th>Full Name</th><td><input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>"></td></tr>
      <tr><th>Email</th><td><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>"></td></tr>
      <tr><th>Password</th><td><input type="password" name="password" class="form-control" placeholder="Leave blank to keep current"></td></tr>
      <tr><th>Phone</th><td><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>"></td></tr>
      <tr><th>Address</th><td><input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address']) ?>"></td></tr>
      <tr><th>City</th><td><input type="text" name="city" class="form-control" value="<?= htmlspecialchars($user['city']) ?>"></td></tr>
      <tr><th>Country</th><td><input type="text" name="country" class="form-control" value="<?= htmlspecialchars($user['country']) ?>"></td></tr>
      <tr><th>Role</th>
        <td>
          <select name="role" class="form-select">
            <option value="">Select Role</option>
            <option value="Super admin" <?= $user['role'] === 'Super admin' ? 'selected' : '' ?>>Super admin</option>
            <option value="Sale" <?= $user['role'] === 'Sale' ? 'selected' : '' ?>>Sale</option>
          </select>
        </td>
      </tr>
      <tr><th>Description</th><td><textarea name="description" class="form-control"><?= htmlspecialchars($user['description']) ?></textarea></td></tr>
      <tr><th>Avatar</th>
        <td>
          <?php if ($user['avatar']): ?>
            <img src="uploads/<?= $user['avatar'] ?>" width="50" class="mb-2" />
          <?php endif; ?>
          <input type="file" name="avatar" class="form-control">
        </td>
      </tr>
      <tr><td colspan="2" class="text-end">
        <button class="btn btn-primary">Update</button>
        <a href="users.php" class="btn btn-secondary">Cancel</a>
      </td></tr>
    </table>
  </form>
</div>
</body>
</html>
