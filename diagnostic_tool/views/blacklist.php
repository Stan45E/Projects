<?php include '../config/config.php' ?>
<!DOCTYPE html>
<html>
<head>
  <title>Blacklisted Commands</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2>Blacklisted Command Attempts</h2>
  <?php
  $result = $conn->query("SELECT * FROM blacklisted_commands ORDER BY attempted_at DESC");
  ?>
  <table class="table table-dark table-striped mt-4">
    <thead>
      <tr>
        <th>Command</th>
        <th>Attempted By</th>
        <th>Time</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['attempted_command']) ?></td>
          <td><?= htmlspecialchars($row['attempted_by']) ?: 'Unknown' ?></td>
          <td><?= $row['attempted_at'] ?></td>
          <td>
            <form method="POST" action="../actions/approve_blacklist.php" style="display:inline;">
              <input type="hidden" name="cmd" value="<?= htmlspecialchars($row['attempted_command']) ?>">
              <button class="btn btn-success btn-sm">Whitelist</button>
            </form>
            <a href="../actions/delete_blacklist.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
      <a href="admin.php" class="btn btn-secondary">Back to Admin Portal</a>
    </tbody>
  </table>
</div>
</body>
</html>
