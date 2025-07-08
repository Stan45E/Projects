<?php
include 'config.php';
$id = intval($_GET['id']);
$conn->query("DELETE FROM categories WHERE id = $id");
header("Location: categories.php");
?>