<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

include 'db.php';

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_sql = '';
$params = [];
$types = '';

if (!empty($search)) {
    $search_sql = "WHERE name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

$sql = "SELECT * FROM category $search_sql ORDER BY id DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Category Dashboard</title>
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
      <a href="dashboard.php" class="nav-link"><i class="bi bi-box-seam"></i> Products</a>
    </li>
    <li class="nav-item">
      <a href="add_category.php" class="nav-link fw-bold"><i class="bi bi-tags"></i> Category</a>
    </li>
    <li class="nav-item">
      <a href="#" class="nav-link"><i class="bi bi-gear"></i> Settings</a>
    </li>
  </ul>
</div>

<div class="main">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="m-0">Categories</h4>
    <a href="addCategory.php" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> Add Category
    </a>
  </div>

  <form class="row mb-4" method="GET" action="">
    <div class="col-md-5 col-12 mb-2 mb-md-0">
      <input
        type="text"
        name="search"
        class="form-control"
        placeholder="Search by name"
        value="<?= htmlspecialchars($search) ?>"
      />
    </div>
  </form>

  <table class="table table-bordered bg-white table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th>Name</th>
        <th style="width: 100px;">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td>
              <a href="editCategory.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                <i class="bi bi-pencil"></i>
              </a>
              <a href="delete_category.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this category?');">
  <i class="bi bi-trash"></i>
</a>

            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="2" class="text-center">No categories found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
