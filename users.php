<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

// Handle filter and pagination
$filterRole = $_GET['role'] ?? '';
$itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 15;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $itemsPerPage;

// Build query
$sql = "SELECT * FROM users";
$params = [];
$types = "";

if ($filterRole) {
    $sql .= " WHERE role = ?";
    $params[] = $filterRole;
    $types .= "s";
}

$sql .= " ORDER BY fullname ASC LIMIT ?, ?";
$params[] = $offset;
$params[] = $itemsPerPage;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get total user count
$countSql = "SELECT COUNT(*) as total FROM users" . ($filterRole ? " WHERE role = ?" : "");
$countStmt = $conn->prepare($countSql);
if ($filterRole) {
    $countStmt->bind_param("s", $filterRole);
}
$countStmt->execute();
$totalUsers = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalUsers / $itemsPerPage);
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
<div class="container my-4">
  <div class="d-flex justify-content-between mb-3 align-items-center flex-wrap gap-2">
    <h4>Users</h4>
    <div class="d-flex gap-2 align-items-center">
      <form method="GET" class="d-flex align-items-center gap-2">
        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All Roles</option>
          <option value="Super admin" <?= $filterRole == 'Super admin' ? 'selected' : '' ?>>Super admin</option>
          <option value="Sale" <?= $filterRole == 'Sale' ? 'selected' : '' ?>>Sale</option>
        </select>
        <input type="hidden" name="itemsPerPage" value="<?= $itemsPerPage ?>">
      </form>
      <a href="add_user.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add User</a>
    </div>
  </div>

  <table class="table table-bordered bg-white align-middle">
    <thead class="table-light">
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>City</th>
        <th>Country</th>
        <th>Role</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows === 0): ?>
        <tr><td colspan="7" class="text-center">No users found</td></tr>
      <?php else: ?>
        <?php while ($user = $result->fetch_assoc()): ?>
          <tr>
            <td class="d-flex align-items-center">
              <?php if (!empty($user['avatar']) && file_exists(__DIR__ . '/uploads/' . $user['avatar'])): ?>
                <img src="uploads/<?= htmlspecialchars($user['avatar']) ?>" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
              <?php else: ?>
                <i class="bi bi-person-circle fs-4 me-2"></i>
              <?php endif; ?>
              <?= htmlspecialchars($user['fullname']) ?>
            </td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['phone']) ?></td>
            <td><?= htmlspecialchars($user['city']) ?></td>
            <td><?= htmlspecialchars($user['country']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td>
              <div class="dropdown">
                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="edit_user.php?id=<?= $user['id'] ?>">Edit</a></li>
                  <li><a class="dropdown-item text-danger" href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a></li>
                </ul>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Pagination -->
  <div class="d-flex justify-content-between align-items-center mt-3">
    <div>
      <label>Items per page:
        <select class="form-select form-select-sm d-inline-block w-auto" onchange="location.href='?role=<?= urlencode($filterRole) ?>&itemsPerPage='+this.value">
          <?php foreach ([5, 10, 15, 25, 50] as $count): ?>
            <option value="<?= $count ?>" <?= $itemsPerPage == $count ? 'selected' : '' ?>><?= $count ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
    <nav>
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="?role=<?= urlencode($filterRole) ?>&itemsPerPage=<?= $itemsPerPage ?>&page=1">&laquo;</a>
        </li>
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="?role=<?= urlencode($filterRole) ?>&itemsPerPage=<?= $itemsPerPage ?>&page=<?= $page - 1 ?>">&lsaquo;</a>
        </li>
        <li class="page-item active"><a class="page-link" href="#"><?= $page ?></a></li>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
          <a class="page-link" href="?role=<?= urlencode($filterRole) ?>&itemsPerPage=<?= $itemsPerPage ?>&page=<?= $page + 1 ?>">&rsaquo;</a>
        </li>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
          <a class="page-link" href="?role=<?= urlencode($filterRole) ?>&itemsPerPage=<?= $itemsPerPage ?>&page=<?= $totalPages ?>">&raquo;</a>
        </li>
      </ul>
    </nav>
    <div>
      Showing <?= $offset + 1 ?> - <?= min($offset + $itemsPerPage, $totalUsers) ?> of <?= $totalUsers ?>
    </div>
  </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
