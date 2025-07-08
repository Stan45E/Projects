<?php
include '../config/config.php';
$id = intval($_GET['id']);
$conn->query("DELETE FROM commands WHERE id = $id");
header("Location: ../views/admin.php");
?>
