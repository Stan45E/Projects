<?php include '../config/config.php';

$articles = $conn->query("
  SELECT a.id, a.article_title, c.name AS category_name
  FROM instruction_articles a
  LEFT JOIN categories c ON a.category_id = c.id
  ORDER BY c.name, a.article_title
");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Instruction Articles</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2 class="mb-4">Instruction Articles</h2>
  <a href="index.php" class="btn btn-light mb-3">← Back to App</a>

  <input type="text" id="searchInput" class="form-control mb-3" placeholder="Search articles...">


  <?php if ($articles->num_rows > 0): ?>
    <div class="list-group" id="articleList">
      <?php while ($row = $articles->fetch_assoc()): ?>
        <a href="instruction_view.php?id=<?= $row['id'] ?>" class="list-group-item list-group-item-action bg-secondary text-white mb-2">
          <strong><?= htmlspecialchars($row['article_title']) ?></strong>
          <br><small>Category: <?= htmlspecialchars($row['category_name']) ?></small>
        </a>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p>No instruction articles found.</p>
  <?php endif; ?>
</div>

<script>
  document.getElementById("searchInput").addEventListener("input", function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll("#articleList a").forEach(item => {
      const text = item.textContent.toLowerCase();
      item.style.display = text.includes(query) ? "block" : "none";
    });
  });
</script>

</body>
</html>
