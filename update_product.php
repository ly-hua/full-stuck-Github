<?php
include 'db.php';
session_start();

// Redirect if user not logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

// Redirect if no product ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id = intval($_GET['id']);

// Load categories first (id + name)
$category_result = $conn->query("SELECT id, name FROM category ORDER BY name ASC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $sku = $_POST['sku'];
    $pax = intval($_POST['pax']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category']); // category ID from dropdown

    if (empty($name) || empty($sku) || $pax < 1 || $price < 0 || empty($category_id)) {
        $error = "Please fill all fields correctly.";
    } else {
    $stmt = $conn->prepare("UPDATE products SET name=?, sku=?, pax=?, price=?, category=? WHERE id=?");
      $stmt->bind_param("ssisii", $name, $sku, $pax, $price, $category_id, $id);
        if ($stmt->execute()) {
            header("Location: dashboard.php?msg=updated");
            exit;
        } else {
            $error = "Error updating product: " . $conn->error;
        }
    }
}

// Load product info for form
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// Redirect if product doesn't exist
if (!$product) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Product</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-7 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h3 class="mb-4 text-center">Edit Product</h3>

          <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="POST" novalidate>
            <div class="mb-3">
              <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
              <input id="name" type="text" name="name" class="form-control" required value="<?= htmlspecialchars($product['name']) ?>" />
            </div>

            <div class="mb-3">
              <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
              <input id="sku" type="text" name="sku" class="form-control" required value="<?= htmlspecialchars($product['sku']) ?>" />
            </div>

            <div class="mb-3">
              <label for="pax" class="form-label">Pax <span class="text-danger">*</span></label>
              <input id="pax" type="number" name="pax" class="form-control" min="1" required value="<?= (int)$product['pax'] ?>" />
            </div>

            <div class="mb-3">
              <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
              <input id="price" type="number" step="0.01" min="0" name="price" class="form-control" required value="<?= (float)$product['price'] ?>" />
            </div>

            <div class="mb-4">
              <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
              <select id="category" name="category" class="form-select" required>
                <option value="" disabled <?= empty($product['category']) ? 'selected' : '' ?>>Select category</option>
                <?php
                // Reset and re-run category query
                $category_result->data_seek(0);
                while ($row = $category_result->fetch_assoc()):
                ?>
                  <option value="<?= $row['id'] ?>" 
                    <?= $row['id'] == $product['category'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['name']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="d-flex justify-content-between">
              <button type="submit" class="btn btn-primary">Update Product</button>
              <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
