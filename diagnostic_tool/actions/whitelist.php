<?php include '../config/config.php'; ?>

<?php
// Add command
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['command'])) {
    $cmd = trim($_POST['command']);
    $stmt = $conn->prepare("INSERT IGNORE INTO whitelist_commands (command) VALUES (?)");
    $stmt->bind_param("s", $cmd);
    $stmt->execute();
    header("Location: whitelist.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Whitelist Commands</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2>Manage Whitelisted CMD Commands</h2>

  <form method="POST" class="mb-4">
    <div class="input-group w-50">
      <input type="text" name="command" class="form-control" placeholder="e.g. ipconfig" required>
      <button class="btn btn-success">Add Command</button>
    </div>
  </form>

  <table class="table table-dark table-striped w-50">
    <thead><tr><th>Command</th><th>Action</th></tr></thead>
    <tbody>
    <?php
    $result = $conn->query("SELECT * FROM whitelist_commands ORDER BY command ASC");
    while ($row = $result->fetch_assoc()):
    ?>
      <tr>
        <td><?= htmlspecialchars($row['command']) ?></td>
        <td>
          <a href="delete_whitelist.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
             onclick="return confirm('Delete this command?')">Delete</a>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

  <a href="../views/admin.php" class="btn btn-secondary mt-3">← Back to Admin Panel</a>
</div>
</body>
</html>
