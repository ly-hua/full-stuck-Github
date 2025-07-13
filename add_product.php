<?php
include 'db.php';

// Load all categories (id + name)
$category_result = $conn->query("SELECT id, name FROM category ORDER BY name ASC");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // 1. Get used IDs
  $used_ids = [];
  $result = $conn->query("SELECT id FROM products ORDER BY id ASC");
  while ($row = $result->fetch_assoc()) {
    $used_ids[] = (int)$row['id'];
  }

  // 2. Find the smallest unused ID from 1 to 1000
  $available_id = null;
  for ($i = 1; $i <= 1000; $i++) {
    if (!in_array($i, $used_ids)) {
      $available_id = $i;
      break;
    }
  }

  if ($available_id === null) {
    die("All IDs from 1 to 1000 are already used.");
  }

  // 3. Prepare and insert product
  $category_id = intval($_POST['category']); // Convert category to integer (FK)
  $stmt = $conn->prepare("INSERT INTO products (id, name, sku, pax, price, category) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("issids", 
    $available_id, 
    $_POST['name'], 
    $_POST['sku'], 
    $_POST['pax'], 
    $_POST['price'], 
    $category_id
  );
  $stmt->execute();

  header("Location: dashboard.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Add Product</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h4 class="mb-4 text-center">Add Product</h4>
          <form method="POST" novalidate>
            <div class="mb-3">
              <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
              <input id="name" name="name" type="text" class="form-control" placeholder="Enter product name" required>
            </div>
            <div class="mb-3">
              <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
              <input id="sku" name="sku" type="text" class="form-control" placeholder="Enter SKU" required>
            </div>
            <div class="mb-3">
              <label for="pax" class="form-label">Pax <span class="text-danger">*</span></label>
              <input id="pax" name="pax" type="number" min="1" class="form-control" placeholder="Number of pax" required>
            </div>
            <div class="mb-3">
              <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
              <input id="price" name="price" type="number" step="0.01" min="0" class="form-control" placeholder="Enter price" required>
            </div>

            <div class="mb-4">
              <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
              <select id="category" name="category" class="form-select" required>
                <option value="" disabled selected>Select category</option>
                <?php while ($row = $category_result->fetch_assoc()): ?>
                  <option value="<?= $row['id'] ?>">
                    <?= htmlspecialchars($row['name']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Save Product</button>
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
