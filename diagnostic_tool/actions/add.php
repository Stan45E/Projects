<?php

include '../config/config.php';


$cats = $conn->query("SELECT * FROM categories");
$whitelist = $conn->query("SELECT * FROM whitelist_commands");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $stmt = $conn->prepare("INSERT INTO commands (name, description, example, category_id) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("sssi", $_POST['name'], $_POST['description'], $_POST['example'], $_POST['category_id']);
  $stmt->execute();
  header("Location: ../views/admin.php");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Add Command</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2>Add New Command</h2>
  <form method="POST">
    <div class="mb-3">
      <label>Command Name</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Description</label>
      <textarea name="description" class="form-control" required></textarea>
    </div>
    <div class="mb-3">
      <label>Example</label>
      <textarea name="example" class="form-control"></textarea>
    </div>
    <div class="mb-3">
      <label>Category</label>
      <select name="category_id" class="form-control" required>
        <?php while($cat = $cats->fetch_assoc()): ?>
        <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <button class="btn btn-success">Add Command</button>
    <a href="../views/admin.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
