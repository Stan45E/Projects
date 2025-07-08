<?php include '../config/config.php';

$id = intval($_GET['id']);

$error = $conn->query("
  SELECT r.*, a.article_title 
  FROM error_resolutions r 
  LEFT JOIN instruction_articles a ON r.article_id = a.id
  WHERE r.id = $id
")->fetch_assoc();

$steps = [];
if ($error && $error['article_id']) {
  $steps = $conn->query("
    SELECT * FROM instructions 
    WHERE article_id = " . intval($error['article_id']) . "
    ORDER BY id ASC
  ");
}
?>

<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($error['error_code']) ?> - Error Details</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2><?= htmlspecialchars($error['error_code']) ?></h2>
  <p class="text-muted"><?= htmlspecialchars($error['error_message']) ?></p>

  <h4>Resolution</h4>
  <p><?= nl2br(htmlspecialchars($error['resolution'])) ?></p>

  <?php if (!empty($steps) && $steps->num_rows > 0): ?>
    <hr>
    <h4>Step-by-Step Instructions</h4>
    <?php while ($step = $steps->fetch_assoc()): ?>
      <div class="bg-secondary text-white p-3 mb-3 rounded">
        <h5><?= htmlspecialchars($step['step_title']) ?></h5>
        <p><?= nl2br(htmlspecialchars($step['step_detail'])) ?></p>
        <?php if (!empty($step['image_path']) && file_exists($step['image_path'])): ?>
          <img src="<?= $step['image_path'] ?>" class="img-fluid border mt-2" style="max-width: 600px;">
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>

  <a href="error_lookup.php" class="btn btn-light mt-3">← Back to Search</a>
</div>
</body>
</html>
