<?php include 'config.php';
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['new_category'])) {
  $name = $conn->real_escape_string($_POST['new_category']);
  $conn->query("INSERT INTO categories (name) VALUES ('$name')");
  header("Location: categories.php");
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Categories</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2>Manage Categories</h2>
  <form method="POST" class="mb-3">
    <input type="text" name="new_category" class="form-control w-50 d-inline" required>
    <button class="btn btn-success">Add</button>
    <a href="admin.php" class="btn btn-secondary">Back</a>
  </form>
  <table class="table table-dark">
    <thead><tr><th>Name</th><th>Actions</th></tr></thead>
    <tbody>
      <?php
      $cats = $conn->query("SELECT * FROM categories");
      while ($row = $cats->fetch_assoc()):
      ?>
      <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td>
          <a href="delete_category.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete category?')">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
