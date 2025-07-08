<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Portal</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2>Admin Portal</h2>
  <a href="add.php" class="btn btn-success">Add New Command</a>
  <a href="categories.php" class="btn btn-warning">Manage Categories</a>
  <a href="index.php" class="btn btn-secondary">Back</a>
  <table class="table table-dark mt-4">
    <thead>
      <tr><th>Command</th><th>Description</th><th>Category</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php
      $result = $conn->query("SELECT c.*, cat.name AS cat_name FROM commands c LEFT JOIN categories cat ON c.category_id = cat.id");
      while($row = $result->fetch_assoc()):
      ?>
      <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td><?= htmlspecialchars($row['cat_name']) ?></td>
        <td>
          <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
          <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this command?')">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
