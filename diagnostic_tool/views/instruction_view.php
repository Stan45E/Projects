<?php include '../config/config.php';
$article_id = intval($_GET['id']);

$article = $conn->query("
  SELECT a.article_title, c.name AS category_name
  FROM instruction_articles a
  LEFT JOIN categories c ON a.category_id = c.id
  WHERE a.id = $article_id
")->fetch_assoc();

$steps = $conn->query("
  SELECT * FROM instructions
  WHERE article_id = $article_id
  ORDER BY id ASC
");
?>

<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($article['article_title']) ?> - Instructions</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2><?= htmlspecialchars($article['article_title']) ?></h2>
  <p class="text-muted">Category: <?= htmlspecialchars($article['category_name']) ?></p>
  <a href="instructions_index.php" class="btn btn-light mb-4">← Back to Articles</a>

  <?php while ($step = $steps->fetch_assoc()): ?>
    <div class="mb-4 p-3 bg-secondary rounded">
      <h5><?= htmlspecialchars($step['step_title']) ?></h5>
      <p><?= nl2br(htmlspecialchars($step['step_detail'])) ?></p>
      <?php if (!empty($step['image_path']) && file_exists($step['image_path'])): ?>
        <img src="<?= $step['image_path'] ?>" class="img-fluid border mt-2" style="max-width: 600px;">
      <?php endif; ?>
    </div>
  <?php endwhile; ?>
</div>
</body>
</html>
