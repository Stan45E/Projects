<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get all POST data
    $id = intval($_POST['id']);
    $agent_name = trim($_POST['agent_name']);
    $case_type = trim($_POST['case_type']);
    $occurrence_date = $_POST['occurrence_date'];
    $is_critical = isset($_POST['is_critical']) ? 1 : 0;
    $notes = trim($_POST['notes']);

    // --- File Upload Logic (if new file is provided) ---
    $recording_path_sql = "";
    $types_array = [];
    $params_array = [];
    
    if (isset($_FILES["call_recording"]) && $_FILES["call_recording"]["error"] == 0) {
        // First, get the old file path to delete it after a successful upload
        $old_path_stmt = $conn->prepare("SELECT recording_path FROM bad_call_hits WHERE id = ?");
        $old_path_stmt->bind_param("i", $id);
        $old_path_stmt->execute();
        $old_path_result = $old_path_stmt->get_result()->fetch_assoc();
        $old_file_path = $old_path_result['recording_path'];
        $old_path_stmt->close();
        
        // Process the new file
        $target_dir = "uploads/";
        $unique_name = uniqid() . '_' . basename($_FILES["call_recording"]["name"]);
        $target_file = $target_dir . $unique_name;

        if (move_uploaded_file($_FILES["call_recording"]["tmp_name"], $target_file)) {
            // If upload is successful, delete the old file if it exists
            if (!empty($old_file_path) && file_exists($old_file_path)) {
                unlink($old_file_path);
            }
            // Prepare to update the DB with the new path
            $recording_path_sql = ", recording_path = ?";
            $types_array[] = 's';
            $params_array[] = &$target_file;
        }
    }

    // --- Database Update Logic ---
    $sql = "UPDATE bad_call_hits SET agent_name = ?, case_type = ?, occurrence_date = ?, is_critical = ?, notes = ? {$recording_path_sql} WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        // Base parameters that are always present
        $base_types = 'sssis';
        $base_params = [&$agent_name, &$case_type, &$occurrence_date, &$is_critical, &$notes];

        // Combine base and file upload params
        $final_types = $base_types . implode('', $types_array) . 'i';
        $final_params = array_merge($base_params, $params_array);
        $final_params[] = &$id;

        // Bind parameters dynamically
        $stmt->bind_param($final_types, ...$final_params);
        
        if ($stmt->execute()) {
            // Redirect back to agent details page on success
            header("location: agent_details.php?name=" . urlencode($agent_name) . "&update=success");
        } else {
            echo "Error updating record: " . $stmt->error;
        }
        $stmt->close();
    }
}
$conn->close();
?>