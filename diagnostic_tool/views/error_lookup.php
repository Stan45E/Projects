<?php include '../config/config.php';

$search = '';
$results = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $search = trim($_POST['search']);
  $stmt = $conn->prepare("
    SELECT r.*, a.article_title 
    FROM error_resolutions r 
    LEFT JOIN instruction_articles a ON r.article_id = a.id
    WHERE r.error_code LIKE CONCAT('%', ?, '%') 
       OR r.error_message LIKE CONCAT('%', ?, '%')
    ORDER BY r.error_code ASC
  ");
  $stmt->bind_param("ss", $search, $search);
  $stmt->execute();
  $results = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Error Lookup</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2>Find Error Code Resolutions</h2>
  <a href="index.php" class="btn btn-light mb-3">← Back to App</a>
  <form method="POST" class="my-4">
    <div class="input-group">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Enter error code or message..." required>
      <button class="btn btn-primary">Search</button>
    </div>
  </form>

  <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    <?php if ($results->num_rows > 0): ?>
      <div class="list-group mt-4">
        <?php while ($row = $results->fetch_assoc()): ?>
          <a href="error_detail.php?id=<?= $row['id'] ?>" class="list-group-item list-group-item-action bg-secondary text-white mb-2">
            <strong><?= htmlspecialchars($row['error_code']) ?></strong> – <?= htmlspecialchars($row['error_message']) ?>
            <?php if (!empty($row['article_title'])): ?>
              <br><small class="text-light">📘 Linked article: <?= htmlspecialchars($row['article_title']) ?></small>
            <?php endif; ?>
          </a>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <p class="text-warning mt-4">No results found for "<?= htmlspecialchars($search) ?>"</p>
    <?php endif; ?>
  <?php endif; ?>
</div>
</body>
</html>
