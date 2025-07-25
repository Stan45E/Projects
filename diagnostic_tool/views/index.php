<?php

include '../config/config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Diagnostic Tool</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="dark-mode">
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 bg-dark text-white vh-100 p-3">
      <h4>Categories</h4>
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link text-white category-link active" href="#" data-category="all">All</a></li>
        <?php
        $cats = $conn->query("SELECT * FROM categories");
        while ($cat = $cats->fetch_assoc()):
        ?>
        <li class="nav-item">
          <a class="nav-link text-white category-link" href="#" data-category="<?= $cat['id'] ?>">
            <?= $cat['name'] ?>
          </a>
        </li>
        <?php endwhile; ?>
            <li class="nav-item">
              <a class="nav-link text-white" href="instructions_index.php">Instructions</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="error_lookup.php">Error Lookup</a>
            </li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="col-md-9 p-4">
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Diagnostic Commands</h2>
              <a href="admin.php" class="btn btn-primary">Admin Portal</a>
          </div>

        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="darkModeSwitch" checked>
          <label class="form-check-label" for="darkModeSwitch">Dark Mode</label>
        </div>
      </div>

      <!-- Search -->
      <input type="text" class="form-control my-3" id="search" placeholder="Search commands...">

      <!-- Syntax Checker -->
      <div class="mb-3">
        <label class="form-label">Check CMD Syntax</label>
        <input type="text" id="syntaxInput" class="form-control" placeholder="e.g. ipconfig">
        <div id="syntaxResult" class="mt-2 text-warning"></div>
      </div>

      <!-- Real Command Runner -->
      <div class="mt-4">
        <label class="form-label">Run CMD (Whitelisted Only)</label>
        <input type="text" id="runInput" class="form-control" placeholder="e.g. ipconfig">
        <button class="btn btn-danger mt-2" onclick="runRealCommand()">Execute</button>
        <div id="runResult" class="mt-3 p-3 bg-dark text-success border rounded d-none">
          <strong>Output:</strong>
          <pre id="runOutput">...</pre>
        </div>
      </div>

      <!-- Command List -->
      <div class="list-group mt-4" id="command-list">
        <?php
        $result = $conn->query("SELECT c.*, cat.name AS cat_name FROM commands c LEFT JOIN categories cat ON c.category_id = cat.id ORDER BY cat.name, c.name");
        while($row = $result->fetch_assoc()):
        ?>
        <div class="list-group-item d-flex justify-content-between align-items-start command-item" data-category="<?= $row['category_id'] ?>">
          <div>
            <strong><?= htmlspecialchars($row['name']) ?></strong>: <?= htmlspecialchars($row['description']) ?>
            <br><small><em><?= $row['cat_name'] ?></em></small>
          </div>
          <div>
            <button class="btn btn-sm btn-info" onclick="copyText('<?= addslashes($row['name']) ?>')">Copy</button>
            <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#exampleModal<?= $row['id'] ?>">Example</button>
          </div>
        </div>

        <!-- Example Modal -->
        <div class="modal fade" id="exampleModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
              <div class="modal-header">
                <h5 class="modal-title"><?= htmlspecialchars($row['name']) ?> Example</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <pre><?= htmlspecialchars($row['example']) ?></pre>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>
</div>

<!-- Toasts -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="syntaxToast" class="toast bg-danger text-white" role="alert">
    <div class="toast-header bg-danger text-white">
      <strong class="me-auto">Syntax Checker</strong>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
    </div>
    <div class="toast-body">Invalid command!</div>
  </div>

  <div id="copyToast" class="toast bg-success text-white" role="alert">
    <div class="toast-header bg-success text-white">
      <strong class="me-auto">Clipboard</strong>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
    </div>
    <div class="toast-body">Command copied!</div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/script.js"></script>
<script>
  console.log("JS Loaded");
</script>
</body>
</html>
