<?php
include '../config/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cmd = isset($_POST['cmd']) ? trim($_POST['cmd']) : '';
    $remoteIP = isset($_POST['remote_ip']) ? trim($_POST['remote_ip']) : '';

    if (!$cmd) {
        http_response_code(400);
        echo "❌ Invalid command input.";
        exit;
    }

    // ✅ Step 1: Check if command is in whitelist
    $stmt = $conn->prepare("SELECT * FROM whitelist_commands WHERE LOWER(command) = LOWER(?)");
    $stmt->bind_param("s", $cmd);
    $stmt->execute();
    $result = $stmt->get_result();

    // ❌ Step 2: If not found, block + log to blacklist
    if ($result->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO blacklisted_commands (attempted_command, attempted_by) VALUES (?, ?)");
        $stmt->bind_param("ss", $cmd, $remoteIP);
        $stmt->execute();

        http_response_code(403);
        echo "❌ Command blocked. Sent to admin for review.";
        exit;
    }

    // ✅ Step 3: Whitelisted — safely execute command
    $output = shell_exec(escapeshellcmd($cmd) . " 2>&1");
    echo nl2br(htmlspecialchars($output));
}

