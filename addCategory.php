<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Step 1: Get used IDs
  $used_ids = [];
  $result = $conn->query("SELECT id FROM category ORDER BY id ASC");
  while ($row = $result->fetch_assoc()) {
    $used_ids[] = (int)$row['id'];
  }

  // Step 2: Find the next available ID from 1–1000
  $available_id = null;
  for ($i = 1; $i <= 1000; $i++) {
    if (!in_array($i, $used_ids)) {
      $available_id = $i;
      break;
    }
  }

  if ($available_id === null) {
    die("All category IDs from 1 to 1000 are used.");
  }

  // Step 3: Insert with custom ID
  $stmt = $conn->prepare("INSERT INTO category (id, name) VALUES (?, ?)");
  $stmt->bind_param("is", $available_id, $_POST['name']);
  $stmt->execute();

  header("Location: add_category.php");
  exit;
}
?>
<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Step 1: Get used IDs
  $used_ids = [];
  $result = $conn->query("SELECT id FROM category ORDER BY id ASC");
  while ($row = $result->fetch_assoc()) {
    $used_ids[] = (int)$row['id'];
  }

  // Step 2: Find the next available ID from 1–1000
  $available_id = null;
  for ($i = 1; $i <= 1000; $i++) {
    if (!in_array($i, $used_ids)) {
      $available_id = $i;
      break;
    }
  }

  if ($available_id === null) {
    die("All category IDs from 1 to 1000 are used.");
  }

  // Step 3: Insert with custom ID
  $stmt = $conn->prepare("INSERT INTO category (id, name) VALUES (?, ?)");
  $stmt->bind_param("is", $available_id, $_POST['name']);
  $stmt->execute();

  header("Location: add_category.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Add Category</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h4 class="mb-4 text-center">Add Category</h4>
          <form method="POST" novalidate>
            <div class="mb-3">
              <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
              <input id="name" name="name" type="text" class="form-control" placeholder="Enter category name" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
          </form>
        </div>
      </div>
      <div class="text-center mt-3">
        <a href="dashboard.php" class="btn btn-link">← Back to Dashboard</a>
      </div>
    </div>
  </div>
</div>

</body>
</html>
