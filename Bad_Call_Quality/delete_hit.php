<?php
require_once 'db_connect.php';

// Validate inputs
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['agent'])) {
    die("Invalid parameters.");
}
$id = intval($_GET['id']);
$agent_name_encoded = $_GET['agent'];

// Before deleting the DB record, get the recording path to delete the file
$stmt = $conn->prepare("SELECT recording_path FROM bad_call_hits WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$file_to_delete = $result['recording_path'] ?? null;
$stmt->close();

// Prepare and execute the DELETE statement
$stmt = $conn->prepare("DELETE FROM bad_call_hits WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // If the record is deleted successfully, also delete the associated file
    if ($file_to_delete && file_exists($file_to_delete)) {
        unlink($file_to_delete);
    }
    header("location: agent_details.php?name=" . $agent_name_encoded . "&delete=success");
} else {
    header("location: agent_details.php?name=" . $agent_name_encoded . "&delete=error");
}
$stmt->close();
$conn->close();
?>