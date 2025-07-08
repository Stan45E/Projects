<?php
include '../config/config.php';
$id = intval($_GET['id']);
$conn->query("DELETE FROM blacklisted_commands WHERE id = $id");
header("Location: ../views/blacklist.php");
