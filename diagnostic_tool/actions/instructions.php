<?php include '../config/config.php';
$cats = $conn->query("SELECT * FROM categories");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $category_id = intval($_POST['category_id']);
  $article_title = $_POST['article_title'];

  // Insert article
  $stmt = $conn->prepare("INSERT INTO instruction_articles (category_id, article_title) VALUES (?, ?)");
  $stmt->bind_param("is", $category_id, $article_title);
  $stmt->execute();
  $article_id = $stmt->insert_id;

  // Insert steps
  for ($i = 1; $i <= $_POST['total_steps']; $i++) {
    $title = $_POST["step_title_$i"];
    $detail = $_POST["step_detail_$i"];
    $image_path = null;

    if (!empty($_FILES["step_image_$i"]['tmp_name'])) {
      $targetDir = "../uploads/";
      if (!is_dir($targetDir)) mkdir($targetDir);
      $filename = uniqid() . "_" . basename($_FILES["step_image_$i"]["name"]);
      $filepath = $targetDir . $filename;
      move_uploaded_file($_FILES["step_image_$i"]["tmp_name"], $filepath);
      $image_path = $filepath;
    }

    $stepStmt = $conn->prepare("INSERT INTO instructions (category_id, article_id, step_title, step_detail, image_path) VALUES (?, ?, ?, ?, ?)");
    $stepStmt->bind_param("iisss", $category_id, $article_id, $title, $detail, $image_path);
    $stepStmt->execute();
  }

  header("Location: instructions.php");
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Instruction Article</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script>
    function showSteps() {
      const count = document.getElementById("step_count").value;
      const container = document.getElementById("step_fields");
      container.innerHTML = "";

      for (let i = 1; i <= count; i++) {
        container.innerHTML += `
          <div class="mb-4 border rounded p-3 bg-secondary text-white">
            <h5>Step ${i}</h5>
            <label>Step Title</label>
            <input type="text" name="step_title_${i}" class="form-control mb-2" required>

            <label>Step Detail</label>
            <textarea name="step_detail_${i}" class="form-control mb-2" required></textarea>

            <label>Screenshot (optional)</label>
            <input type="file" name="step_image_${i}" class="form-control">
          </div>
        `;
      }

      const totalField = document.getElementById("total_steps");
      totalField.value = count;
    }
  </script>
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2>Create Instruction Article</h2>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label>Category</label>
      <select name="category_id" class="form-control" required>
        <?php while ($cat = $cats->fetch_assoc()): ?>
          <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label>Article Title</label>
      <input type="text" name="article_title" class="form-control" required>
    </div>

    <div class="mb-3">
      <label>How many steps?</label>
      <select id="step_count" class="form-control w-25" onchange="showSteps()" required>
        <option value="">-- Select --</option>
        <?php for ($i = 1; $i <= 10; $i++): ?>
          <option value="<?= $i ?>"><?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>

    <input type="hidden" id="total_steps" name="total_steps" value="0">
    <div id="step_fields"></div>

    <button class="btn btn-success mt-3">Save Article</button>
    <a href="../views/admin.php" class="btn btn-secondary">Back</a>
  </form>
    <hr class="my-5">
      <h3>Existing Instruction Articles</h3>

        <?php
      $articles = $conn->query("
            SELECT a.*, c.name AS cat_name
              FROM instruction_articles a
              LEFT JOIN categories c ON a.category_id = c.id
              ORDER BY a.id DESC
              ");
          ?>

        <?php while ($article = $articles->fetch_assoc()): ?>
          <div class="mb-4 p-3 bg-secondary rounded">
          <h5><?= htmlspecialchars($article['article_title']) ?> <small class="text-light">(<?= $article['cat_name'] ?>)</small></h5>
            <a href="edit_instruction.php?id=<?= $article['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
          <a href="delete_instruction.php?id=<?= $article['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this article and all steps?')">Delete</a>
            </div>
          <?php endwhile; ?>

</div>
</body>
</html>
