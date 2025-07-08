<?php
include '../config/config.php';
$id = intval($_GET['id']);

// Delete steps
$conn->query("DELETE FROM instructions WHERE article_id = $id");
// Delete article
$conn->query("DELETE FROM instruction_articles WHERE id = $id");

header("Location: instructions.php");
?>
