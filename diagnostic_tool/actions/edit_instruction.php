<?php
include '../config/config.php';

$id = intval($_GET['id']);

// Get article
$article = $conn->query("SELECT * FROM instruction_articles WHERE id = $id")->fetch_assoc();
$steps = $conn->query("SELECT * FROM instructions WHERE article_id = $id ORDER BY id ASC");

// Handle article update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $new_title = $_POST['article_title'];
  $conn->query("UPDATE instruction_articles SET article_title = '$new_title' WHERE id = $id");

  foreach ($_POST['step_id'] as $index => $stepId) {
    $title = $_POST['step_title'][$index];
    $detail = $_POST['step_detail'][$index];
    $image_path = $_POST['existing_image'][$index];

    // Check if a new file is uploaded
    if (!empty($_FILES['step_image']['tmp_name'][$index])) {
      $targetDir = "uploads/";
      if (!is_dir($targetDir)) mkdir($targetDir);
      $filename = uniqid() . "_" . basename($_FILES['step_image']['name'][$index]);
      $filepath = $targetDir . $filename;
      move_uploaded_file($_FILES['step_image']['tmp_name'][$index], $filepath);
      $image_path = $filepath;
    }

    $stmt = $conn->prepare("UPDATE instructions SET step_title=?, step_detail=?, image_path=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $detail, $image_path, $stepId);
    $stmt->execute();
  }

  header("Location: instructions.php");
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Instruction Article</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2>Edit Article</h2>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label>Article Title</label>
      <input type="text" name="article_title" class="form-control" value="<?= htmlspecialchars($article['article_title']) ?>" required>
    </div>

    <hr>
    <h4>Edit Steps</h4>

    <?php foreach ($steps as $index => $step): ?>
      <div class="mb-4 p-3 bg-secondary rounded">
        <input type="hidden" name="step_id[]" value="<?= $step['id'] ?>">
        <input type="hidden" name="existing_image[]" value="<?= $step['image_path'] ?>">

        <label>Step Title</label>
        <input type="text" name="step_title[]" class="form-control mb-2" value="<?= htmlspecialchars($step['step_title']) ?>" required>

        <label>Step Detail</label>
        <textarea name="step_detail[]" class="form-control mb-2" required><?= htmlspecialchars($step['step_detail']) ?></textarea>

        <?php if (!empty($step['image_path']) && file_exists($step['image_path'])): ?>
          <p><strong>Current Image:</strong><br>
          <img src="<?= $step['image_path'] ?>" style="max-width: 300px;" class="img-fluid mb-2"></p>
        <?php endif; ?>

        <label>Replace Image (optional)</label>
        <input type="file" name="step_image[]" class="form-control">
      </div>
    <?php endforeach; ?>

    <button class="btn btn-success">Save Changes</button>
    <a href="instructions.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
