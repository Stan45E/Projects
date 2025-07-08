<?php
include '../config/config.php';
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM whitelist_commands WHERE id = $id");
}
header("Location: whitelist.php");
?>
