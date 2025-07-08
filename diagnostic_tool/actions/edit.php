<?php include '../config/config.php';
$id = $_GET['id'];
$cmd = $conn->query("SELECT * FROM commands WHERE id=$id")->fetch_assoc();
$cats = $conn->query("SELECT * FROM categories");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $stmt = $conn->prepare("UPDATE commands SET name=?, description=?, example=?, category_id=? WHERE id=?");
  $stmt->bind_param("sssii", $_POST['name'], $_POST['description'], $_POST['example'], $_POST['category_id'], $id);
  $stmt->execute();
  header("Location: ../views/admin.php");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Command</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container my-5">
  <h2>Edit Command</h2>
  <form method="POST">
    <div class="mb-3">
      <label>Command Name</label>
      <input type="text" name="name" class="form-control" value="<?= $cmd['name'] ?>" required>
    </div>
    <div class="mb-3">
      <label>Description</label>
      <textarea name="description" class="form-control" required><?= $cmd['description'] ?></textarea>
    </div>
    <div class="mb-3">
      <label>Example</label>
      <textarea name="example" class="form-control"><?= $cmd['example'] ?></textarea>
    </div>
    <div class="mb-3">
      <label>Category</label>
      <select name="category_id" class="form-control" required>
        <?php while($cat = $cats->fetch_assoc()): ?>
        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $cmd['category_id'] ? 'selected' : '' ?>>
          <?= $cat['name'] ?>
        </option>
        <?php endwhile; ?>
      </select>
    </div>
    <button class="btn btn-warning">Update Command</button>
    <a href="admin.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
