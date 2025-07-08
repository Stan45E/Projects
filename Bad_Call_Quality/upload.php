<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php';
require_once 'email_service.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Audio recording upload logic has been removed.
    
    // Process Network Test Upload
    $network_test_path = null;
    if (isset($_FILES["network_test_result"]) && $_FILES["network_test_result"]["error"] == 0) {
        $target_dir = "uploads/network_tests/";
        $unique_name = uniqid('net_') . '_' . basename($_FILES["network_test_result"]["name"]);
        $target_file = $target_dir . $unique_name;
        if (move_uploaded_file($_FILES["network_test_result"]["tmp_name"], $target_file)) {
            $network_test_path = $target_file;
        }
    }

    // Prepare Data
    $agent_name = trim($_POST['agent_name']);
    $agent_email = trim($_POST['agent_email']);
    $occurrence_date = $_POST['occurrence_date'];
    $is_critical = isset($_POST['is_critical']) ? 1 : 0;
    $notes = trim($_POST['notes']);
    $network_test_date = !empty($_POST['network_test_date']) ? $_POST['network_test_date'] : null;

    // Database Insertion (Updated SQL)
    $sql = "INSERT INTO bad_call_hits (agent_name, agent_email, occurrence_date, is_critical, notes, network_test_path, network_test_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        // Updated bind_param: sssisss
        $stmt->bind_param("sssisss", $agent_name, $agent_email, $occurrence_date, $is_critical, $notes, $network_test_path, $network_test_date);
        
        if ($stmt->execute()) {
            // Your email trigger logic here...
            header("location: index.php?upload=success");
            exit();
        }
    }
}
$conn->close();
?>