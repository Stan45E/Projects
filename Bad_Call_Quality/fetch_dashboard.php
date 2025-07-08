<?php
// fetch_dashboard.php

require_once 'db_connect.php';

// Get the period from the AJAX request, default to 'overall'
$period = isset($_GET['period']) ? $_GET['period'] : 'overall';

// This is the same logic from index.php
$where_clause = '';
switch ($period) {
    case '7days': $where_clause = "WHERE occurrence_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; break;
    case '15days': $where_clause = "WHERE occurrence_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)"; break;
    case '30days': $where_clause = "WHERE occurrence_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"; break;
}

// The same SQL query to get the aggregated data
$sql_table = "
    SELECT agent_name, COUNT(*) AS total_hits, SUM(is_critical) AS critical_cases, MAX(occurrence_date) AS last_occurrence
    FROM bad_call_hits {$where_clause}
    GROUP BY agent_name ORDER BY total_hits DESC, last_occurrence DESC";
$result_table = $conn->query($sql_table);

// Now, instead of building a full page, we just build the HTML for the table rows
if ($result_table && $result_table->num_rows > 0) {
    while ($row = $result_table->fetch_assoc()) {
        // Echo out each table row
        echo "<tr>";
        echo "    <td><a href='agent_details.php?name=" . urlencode($row['agent_name']) . "'>" . htmlspecialchars($row['agent_name']) . "</a></td>";
        echo "    <td>" . $row['total_hits'] . "</td>";
        echo "    <td>" . $row['critical_cases'] . "</td>";
        echo "    <td>" . date("M d, Y", strtotime($row['last_occurrence'])) . "</td>";
        echo "</tr>";
    }
} else {
    // If no data, echo a row that says so
    echo "<tr><td colspan='4'>No data found for the selected period.</td></tr>";
}

$conn->close();
?>