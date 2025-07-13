<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

include 'db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname    = trim($_POST['fullname'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $city        = trim($_POST['city'] ?? '');
    $country     = trim($_POST['country'] ?? '');
    $role        = trim($_POST['role'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $password    = $_POST['password'] ?? '';
    $avatar      = null;

    // === Validation ===
    if ($fullname === '') $errors[] = "Full name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if ($password === '') $errors[] = "Password is required.";
    if ($role === '') $errors[] = "Role is required.";

    // === Check duplicate email ===
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = "Email already exists.";
        $stmt->close();
    }

    // === Handle Avatar Upload ===
    if (empty($errors) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES['avatar']['tmp_name'];
        $name = basename($_FILES['avatar']['name']);
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $newName = uniqid() . "." . $ext;
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $target = $uploadDir . $newName;
            if (move_uploaded_file($tmp, $target)) {
                $avatar = $newName;
            } else {
                $errors[] = "Failed to upload avatar.";
            }
        } else {
            $errors[] = "Invalid avatar type.";
        }
    }

    // === Insert to DB ===
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, phone, address, city, country, role, description, password, avatar)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $fullname, $email, $phone, $address, $city, $country, $role, $description, $hashed, $avatar);

        if ($stmt->execute()) {
            header("Location: users.php?success=1");
            exit;
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Product Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      background: #f5f5f5;
    }
    .sidebar {
      width: 220px;
      height: 100vh;
      position: fixed;
      background: white;
      border-right: 1px solid #ddd;
    }
    .main {
      margin-left: 230px;
      padding: 20px;
    }
    .logo {
      height: 60px;
      margin: 20px auto;
      display: block;
    }
    .nav-link .bi {
      margin-right: 8px;
      font-size: 1.1rem;
      vertical-align: middle;
    }
    @media (max-width: 768px) {
      .sidebar {
        position: relative;
        width: 100%;
        height: auto;
        border-right: none;
        border-bottom: 1px solid #ddd;
      }
      .main {
        margin-left: 0;
        padding: 15px;
      }
    }
  </style>
</head>
<body>

<div class="sidebar p-3">
  <img src="profile.png" class="logo" alt="Logo" />
  <ul class="nav flex-column">
    <li class="nav-item">
      <a href="#" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    </li>
    <li class="nav-item">
      <a href="dashboard.php" class="nav-link fw-bold"><i class="bi bi-box-seam"></i> Products</a>
    </li>
    <li class="nav-item">
      <a href="add_category.php" class="nav-link"><i class="bi bi-tags"></i> Category</a>
    </li>
    <li class="nav-item">
      <a href="users.php" class="nav-link"><i class="bi bi-gear"></i> User</a>
    </li>
    <li class="nav-item">
      <a href="#" class="nav-link"><i class="bi bi-gear"></i> Settings</a>
    </li>
    <li class="nav-item">
      <a href="index.html" class="nav-link"><i class="bi bi-gear"></i> Logout</a>
    </li>
  </ul>
</div>

<div class="main">
<div class="container my-5" style="max-width: 750px;">
  <h4 class="mb-4">Add New User</h4>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="row mb-3">
      <div class="col">
        <label>Full Name *</label>
        <input type="text" name="fullname" class="form-control" required value="<?= htmlspecialchars($fullname ?? '') ?>">
      </div>
      <div class="col">
        <label>Email *</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email ?? '') ?>">
      </div>
    </div>

    <div class="row mb-3">
      <div class="col">
        <label>Password *</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="col">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone ?? '') ?>">
      </div>
    </div>

    <div class="row mb-3">
      <div class="col">
        <label>Address</label>
        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($address ?? '') ?>">
      </div>
      <div class="col">
        <label>City</label>
        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($city ?? '') ?>">
      </div>
    </div>

    <div class="row mb-3">
      <div class="col">
        <label>Country</label>
        <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($country ?? '') ?>">
      </div>
      <div class="col">
        <label>Role *</label>
        <select name="role" class="form-select" required>
          <option value="">Select Role</option>
          <option value="Super admin" <?= ($role ?? '') === 'Super admin' ? 'selected' : '' ?>>Super admin</option>
          <option value="Sale" <?= ($role ?? '') === 'Sale' ? 'selected' : '' ?>>Sale</option>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label>Description</label>
      <textarea name="description" class="form-control"><?= htmlspecialchars($description ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label>Avatar (optional)</label>
      <input type="file" name="avatar" class="form-control" accept=".jpg,.jpeg,.png,.gif">
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-primary">Add User</button>
      <a href="users.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
