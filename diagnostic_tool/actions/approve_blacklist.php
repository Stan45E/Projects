<?php
include '../config/config.php';
$cmd = $_POST['cmd'] ?? '';

if ($cmd) {
    // Insert into whitelist
    $stmt = $conn->prepare("INSERT INTO whitelist_commands (command) VALUES (?)");
    $stmt->bind_param("s", $cmd);
    $stmt->execute();

    // Remove from blacklist
    $stmt = $conn->prepare("DELETE FROM blacklisted_commands WHERE attempted_command = ?");
    $stmt->bind_param("s", $cmd);
    $stmt->execute();
}
header("Location: ../views/blacklist.php");
