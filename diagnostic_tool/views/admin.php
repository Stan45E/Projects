<?php

include '../config/config.php';

?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Portal</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2>Admin - Manage Commands</h2>
  <a href="../actions/add.php" class="btn btn-success mb-3">+ Add New Command</a>
  <a href="../actions/categories.php" class="btn btn-warning mb-3">Manage Categories</a>
  <a href="../actions/whitelist.php" class="btn btn-info mb-3">Manage Whitelist</a>
  <a href="../actions/instructions.php" class="btn btn-secondary mb-3">Category Instructions</a>
  <a href="../actions/add_error.php" class="btn btn-danger mb-3">Error Resolutions</a>
  <a href="blacklist.php" class="btn btn-warning mb-3">Review Blacklisted Commands</a>
  <a href="../views/index.php" class="btn btn-light mb-3">← Back to App</a>

  <table class="table table-dark table-striped">
    <thead><tr><th>Name</th><th>Description</th><th>Category</th><th>Actions</th></tr></thead>
    <tbody>
    <?php
    $result = $conn->query("SELECT c.*, cat.name AS cat_name FROM commands c LEFT JOIN categories cat ON c.category_id = cat.id");
    while ($row = $result->fetch_assoc()):
    ?>
      <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td><?= htmlspecialchars($row['cat_name']) ?></td>
        <td>
          <a href="../actions/edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
          <a href="../actions/delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this command?')">Delete</a>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
