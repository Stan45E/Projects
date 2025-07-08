<?php
include '../config/config.php';

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $conn->query("DELETE FROM categories WHERE id = $id");
}
header("Location: categories.php");
?>
