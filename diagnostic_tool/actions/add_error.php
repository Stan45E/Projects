<?php

include '../config/config.php';

// Fetch instruction articles for dropdown
$articles = $conn->query("SELECT id, article_title FROM instruction_articles ORDER BY article_title");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $error_code = trim($_POST['error_code']);
  $error_message = trim($_POST['error_message']);
  $resolution = trim($_POST['resolution']);
  $article_id = !empty($_POST['article_id']) ? intval($_POST['article_id']) : null;

  $stmt = $conn->prepare("INSERT INTO error_resolutions (error_code, error_message, resolution, article_id) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE error_message=VALUES(error_message), resolution=VALUES(resolution), article_id=VALUES(article_id)");
  $stmt->bind_param("sssi", $error_code, $error_message, $resolution, $article_id);
  $stmt->execute();

  header("Location: add_error.php?success=1");
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Error Code & Resolution</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2>Add Error Resolution</h2>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Error resolution saved successfully!</div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label>Error Code (e.g., 0x80070005)</label>
      <input type="text" name="error_code" class="form-control" required>
    </div>

    <div class="mb-3">
      <label>Error Message / Description</label>
      <textarea name="error_message" class="form-control" rows="3" required></textarea>
    </div>

    <div class="mb-3">
      <label>Resolution Summary</label>
      <textarea name="resolution" class="form-control" rows="5" required></textarea>
    </div>

    <div class="mb-3">
      <label>Link to Step-by-Step Article (optional)</label>
      <select name="article_id" class="form-control">
        <option value="">-- None --</option>
        <?php while ($row = $articles->fetch_assoc()): ?>
          <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['article_title']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <button class="btn btn-success">Save Resolution</button>
    <a href="../views/admin.php" class="btn btn-secondary">Back</a>
  </form>

  <hr class="my-5">
<h3>Existing Error Resolutions</h3>

<?php
$errors = $conn->query("
  SELECT r.*, a.article_title 
  FROM error_resolutions r 
  LEFT JOIN instruction_articles a ON r.article_id = a.id 
  ORDER BY r.error_code ASC
");

if ($errors->num_rows > 0): ?>
  <table class="table table-dark table-striped">
    <thead>
      <tr>
        <th>Error Code</th>
        <th>Message</th>
        <th>Linked Article</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $errors->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['error_code']) ?></td>
          <td><?= htmlspecialchars($row['error_message']) ?></td>
          <td><?= htmlspecialchars($row['article_title']) ?: '-' ?></td>
          <td>
            <a href="edit_error.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
            <a href="delete_error.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this error?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
<?php else: ?>
  <p class="text-warning">No error entries found.</p>
<?php endif; ?>

</div>
</body>
</html>
