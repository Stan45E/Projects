<?php
include '../config/config.php';

$id = intval($_GET['id']);
$conn->query("DELETE FROM error_resolutions WHERE id = $id");
header("Location: add_error.php");
