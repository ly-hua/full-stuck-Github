<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

include 'db.php';

// Prepare search & category filter
$search = isset($_GET['search']) ? '%' . $conn->real_escape_string($_GET['search']) . '%' : '%';
$categoryFilter = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';

// Build SQL with JOIN to get category name
if ($categoryFilter !== '') {
    $stmt = $conn->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN category c ON p.category = c.id
        WHERE (p.name LIKE ? OR p.sku LIKE ?) AND c.name = ?
    ");
    $stmt->bind_param("sss", $search, $search, $categoryFilter);
} else {
    $stmt = $conn->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN category c ON p.category = c.id
        WHERE p.name LIKE ? OR p.sku LIKE ?
    ");
    $stmt->bind_param("ss", $search, $search);
}
$stmt->execute();
$result = $stmt->get_result();
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
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="m-0">Items</h4>
    <a href="add_product.php" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> Add Item
    </a>
  </div>

  <form class="row mb-4" method="GET" action="">
    <div class="col-md-5 col-12 mb-2 mb-md-0">
      <input
        type="text"
        name="search"
        class="form-control"
        placeholder="Search by Name or SKU"
        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
      />
    </div>
    <div class="col-md-3 col-6 mb-2 mb-md-0">
      <select name="category" class="form-select">
        <option value="">All Categories</option>
        <option value="Drink" <?= (isset($_GET['category']) && $_GET['category'] === 'Drink') ? 'selected' : '' ?>>Drink</option>
        <option value="Food" <?= (isset($_GET['category']) && $_GET['category'] === 'Food') ? 'selected' : '' ?>>Food</option>
      </select>
    </div>
    <div class="col-md-2 col-6">
      <button type="submit" class="btn btn-outline-secondary w-100">
        <i class="bi bi-funnel"></i> Filter
      </button>
    </div>
  </form>

  <table class="table table-bordered bg-white table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th>Name</th>
        <th>SKU</th>
        <th>Pax</th>
        <th>Price</th>
        <th>Category</th>
        <th style="width: 100px;">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows === 0): ?>
        <tr><td colspan="6" class="text-center text-muted fst-italic">No products found.</td></tr>
      <?php else: while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['sku']) ?></td>
        <td><?= (int)$row['pax'] ?></td>
        <td>$<?= number_format($row['price'], 2) ?></td>
        <td><?= htmlspecialchars($row['category_name'] ?? 'N/A') ?></td>
        <td>
          <a href="update_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
            <i class="bi bi-pencil"></i>
          </a>
          <a href="delete_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this product?');">
            <i class="bi bi-trash"></i>
          </a>
        </td>
      </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
