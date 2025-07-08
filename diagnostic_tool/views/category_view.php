<?php

include '../config/config.php';

$id = intval($_GET['id']);
$cat = $conn->query("SELECT * FROM categories WHERE id = $id")->fetch_assoc();
$steps = $conn->query("SELECT * FROM instructions WHERE category_id = $id ORDER BY id ASC");
?>

<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($cat['name']) ?> Instructions</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2><?= htmlspecialchars($cat['name']) ?> – Step-by-Step Guide</h2>
  <a href="index.php" class="btn btn-light mb-4">← Back to App</a>

  <?php while ($row = $steps->fetch_assoc()): ?>
    <div class="mb-4 p-3 bg-secondary text-white rounded">
      <h5><?= htmlspecialchars($row['step_title']) ?></h5>
      <p><?= nl2br(htmlspecialchars($row['step_detail'])) ?></p>
      <?php if (!empty($row['image_path'])): ?>
        <img src="<?= $row['image_path'] ?>" class="img-fluid border mt-2" style="max-width: 500px;">
      <?php endif; ?>
    </div>
  <?php endwhile; ?>
</div>
</body>
</html>
