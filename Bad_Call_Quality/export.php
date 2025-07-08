<?php
// export.php - CORRECTED

// Add error reporting for easier debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. BOOTSTRAP THE APPLICATION
require 'vendor/autoload.php';
require_once 'db_connect.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// 2. VALIDATE INPUTS (This part is fine)
$allowed_periods = ['7days', '15days', '30days', 'overall'];
$allowed_formats = ['csv', 'xlsx'];
$period = isset($_GET['period']) && in_array($_GET['period'], $allowed_periods) ? $_GET['period'] : 'overall';
$format = isset($_GET['format']) && in_array($_GET['format'], $allowed_formats) ? $_GET['format'] : 'csv';

// 3. FETCH DATA FROM DATABASE
$where_clause = '';
switch ($period) {
    case '7days': $where_clause = "WHERE occurrence_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; break;
    case '15days': $where_clause = "WHERE occurrence_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)"; break;
    case '30days': $where_clause = "WHERE occurrence_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"; break;
}

// =========================================================================
// THE FIX IS HERE: The SQL query now selects the correct, current columns.
// We removed 'case_type' and added the new ones.
// =========================================================================
$sql = "SELECT id, agent_name, agent_email, is_critical, occurrence_date, notes, recording_path, network_test_path, network_test_date, created_at, modified_at 
        FROM bad_call_hits 
        {$where_clause} 
        ORDER BY occurrence_date DESC";

$result = $conn->query($sql);

// Add a check to see if the query failed, which helps in debugging
if (!$result) {
    die("Database query failed: " . $conn->error);
}

$data_rows = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data_rows[] = $row;
    }
}
$conn->close();

// 4. GENERATE THE FILE BASED ON FORMAT
$filename = 'Bad-Call-Hits-' . $period . '-' . date('Y-m-d') . '.' . $format;

if ($format == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');

    // **FIXED HEADER ROW for CSV**
    fputcsv($output, ['ID', 'Agent Name', 'Agent Email', 'Is Critical', 'Occurrence Date', 'Notes', 'Recording Path', 'Network Test Path', 'Network Test Date', 'Created At', 'Last Modified']);

    foreach ($data_rows as $row) {
        $row['is_critical'] = $row['is_critical'] ? 'Yes' : 'No';
        fputcsv($output, [
            $row['id'], $row['agent_name'], $row['agent_email'], $row['is_critical'], $row['occurrence_date'], $row['notes'], $row['recording_path'], $row['network_test_path'], $row['network_test_date'], $row['created_at'], $row['modified_at']
        ]);
    }
    
    fclose($output);
    exit();

} elseif ($format == 'xlsx') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // **FIXED HEADER ROW for XLSX**
    $headers = ['ID', 'Agent Name', 'Agent Email', 'Is Critical', 'Occurrence Date', 'Notes', 'Network Test Path', 'Network Test Date', 'Created At', 'Last Modified'];
    $sheet->fromArray($headers, NULL, 'A1');
    $sheet->getStyle('A1:K1')->getFont()->setBold(true);

    $rowIndex = 2;
    foreach ($data_rows as $row) {
        $sheet->fromArray([
            $row['id'], $row['agent_name'], $row['agent_email'], ($row['is_critical'] ? 'Yes' : 'No'), $row['occurrence_date'], $row['notes'], $row['network_test_path'], $row['network_test_date'], $row['created_at'], $row['modified_at']
        ], NULL, 'A' . $rowIndex);
        $rowIndex++;
    }

    foreach (range('A', 'K') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}