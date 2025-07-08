<?php
$conn = new mysqli("localhost", "root", "", "diagnostic_tool");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>