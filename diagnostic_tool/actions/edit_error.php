<?php
include '../config/config.php';

$id = intval($_GET['id']);
$error = $conn->query("SELECT * FROM error_resolutions WHERE id = $id")->fetch_assoc();
$articles = $conn->query("SELECT id, article_title FROM instruction_articles ORDER BY article_title");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $error_code = $_POST['error_code'];
  $error_message = $_POST['error_message'];
  $resolution = $_POST['resolution'];
  $article_id = !empty($_POST['article_id']) ? intval($_POST['article_id']) : null;

  $stmt = $conn->prepare("UPDATE error_resolutions SET error_code=?, error_message=?, resolution=?, article_id=? WHERE id=?");
  $stmt->bind_param("sssii", $error_code, $error_message, $resolution, $article_id, $id);
  $stmt->execute();

  header("Location: add_error.php");
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Error Resolution</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2>Edit Error Resolution</h2>
  <form method="POST">
    <div class="mb-3">
      <label>Error Code</label>
      <input type="text" name="error_code" class="form-control" value="<?= htmlspecialchars($error['error_code']) ?>" required>
    </div>

    <div class="mb-3">
      <label>Error Message / Description</label>
      <textarea name="error_message" class="form-control" rows="3" required><?= htmlspecialchars($error['error_message']) ?></textarea>
    </div>

    <div class="mb-3">
      <label>Resolution Summary</label>
      <textarea name="resolution" class="form-control" rows="5" required><?= htmlspecialchars($error['resolution']) ?></textarea>
    </div>

    <div class="mb-3">
      <label>Linked Instruction Article (optional)</label>
      <select name="article_id" class="form-control">
        <option value="">-- None --</option>
        <?php while ($row = $articles->fetch_assoc()): ?>
          <option value="<?= $row['id'] ?>" <?= ($row['id'] == $error['article_id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($row['article_title']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <button class="btn btn-success">Save Changes</button>
    <a href="add_error.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
