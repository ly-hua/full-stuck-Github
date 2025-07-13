<?php
session_start();
include 'db.php';

// ✅ Redirect to login if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

// ✅ Validate and sanitize category ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: add_category.php");
    exit;
}

$id = intval($_GET['id']);
$error = '';

// ✅ Fetch existing category data
$stmt = $conn->prepare("SELECT name FROM category WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) {
    header("Location: add_category.php");
    exit;
}

$name = $category['name'];

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $error = "Please enter a category name.";
    } else {
        $stmt = $conn->prepare("UPDATE category SET name = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();

        if ($stmt->affected_rows === 1) {
            header("Location: add_category.php?msg=updated");
            exit;
        } else {
            $error = "No changes made or error occurred.";
        }
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
      <a href="#" class="nav-link"><i class="bi bi-gear"></i> Settings</a>
    </li>
    <li class="nav-item">
      <a href="index.html" class="nav-link"><i class="bi bi-gear"></i> Logout</a>
    </li>
  </ul>
</div>

<div class="main">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-7 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h3 class="mb-4 text-center">Edit Category</h3>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="POST" novalidate>
            <div class="mb-3">
              <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
              <input id="name" type="text" name="name" class="form-control" required value="<?= htmlspecialchars($name) ?>" />
            </div>
            
            <div class="d-flex justify-content-between">
              <button type="submit" class="btn btn-primary">Update Category</button>
              <a href="add_category.php" class="btn btn-secondary">Cancel</a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
