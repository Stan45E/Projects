<?php

include '../config/config.php';


if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['category'])) {
  $name = trim($_POST['category']);
  $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
  $stmt->bind_param("s", $name);
  $stmt->execute();
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

  <form method="POST" class="mb-4 w-50">
    <div class="input-group">
      <input type="text" name="category" class="form-control" placeholder="e.g. Networking" required>
      <button class="btn btn-success">Add</button>
    </div>
  </form>

  <table class="table table-dark table-striped w-50">
    <thead><tr><th>Name</th><th>Action</th></tr></thead>
    <tbody>
      <?php
      $result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
      while ($row = $result->fetch_assoc()):
      ?>
      <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td>
          <a href="delete_category.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
             onclick="return confirm('Delete this category?')">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <a href="../views/admin.php" class="btn btn-secondary mt-3">← Back to Admin</a>
</div>
</body>
</html>
