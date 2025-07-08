<?php
include 'config.php';
$id = intval($_GET['id']);
$conn->query("DELETE FROM commands WHERE id = $id");
header("Location: admin.php");
?>
