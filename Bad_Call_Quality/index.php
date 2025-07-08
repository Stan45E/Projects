<?php
// Add these lines at the very top to see any errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php';

// --- FETCH MASTER AGENT LIST for the dropdown ---
$agents_list = [];
$sql_agents = "SELECT name, email FROM agents ORDER BY name ASC";
$result_agents = $conn->query($sql_agents);
if ($result_agents && $result_agents->num_rows > 0) {
    while ($row = $result_agents->fetch_assoc()) {
        $agents_list[] = $row;
    }
}

// --- FETCH DATA FOR CRITICAL ALERTS SECTION ---
$sql_alerts = "
    SELECT agent_name, agent_email, COUNT(*) as critical_count 
    FROM bad_call_hits 
    WHERE is_critical = 1 AND occurrence_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
    GROUP BY agent_name, agent_email 
    HAVING critical_count >= 2";
$result_alerts = $conn->query($sql_alerts);
$critical_alerts = [];
if ($result_alerts) {
    while ($row = $result_alerts->fetch_assoc()) {
        $critical_alerts[] = $row;
    }
}

// --- FETCH INITIAL DATA FOR THE DASHBOARD TABLE (for the first page load) ---
$period = isset($_GET['period']) ? $_GET['period'] : 'overall';
$where_clause = '';
switch ($period) {
    case '7days': $where_clause = "WHERE occurrence_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; break;
    case '15days': $where_clause = "WHERE occurrence_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)"; break;
    case '30days': $where_clause = "WHERE occurrence_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"; break;
    default: $where_clause = ""; break;
}
$sql_table = "
    SELECT agent_name, COUNT(*) AS total_hits, SUM(is_critical) AS critical_cases, MAX(occurrence_date) AS last_occurrence
    FROM bad_call_hits {$where_clause}
    GROUP BY agent_name ORDER BY total_hits DESC, last_occurrence DESC";
$result_table = $conn->query($sql_table);
$agent_data = [];
if ($result_table && $result_table->num_rows > 0) {
    while ($row = $result_table->fetch_assoc()) {
        $agent_data[] = $row;
    }
}

// --- FETCH DATA FOR HITS PER AGENT CHART ---
$sql_chart_agents = "SELECT agent_name, COUNT(*) as total_hits FROM bad_call_hits GROUP BY agent_name ORDER BY total_hits DESC";
$result_chart_agents = $conn->query($sql_chart_agents);
$chart_agent_labels = [];
$chart_agent_data = [];
if ($result_chart_agents) {
    while ($row = $result_chart_agents->fetch_assoc()) {
        $chart_agent_labels[] = $row['agent_name'];
        $chart_agent_data[] = $row['total_hits'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bad Call Quality Tracker</title>
    <link rel="icon" type="image/png" href="complaint.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
    <h1>Bad Call Quality Hit Tracker</h1>

    <!-- SECTION 1: DATA UPLOAD FORM -->
    <section>
        <h2>Log a New Hit</h2>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div>
                <label for="agent_select">Agent:</label>
                <select id="agent_select" name="agent_name" style="width: 100%;" required>
                    <option value="" disabled selected>Search or select an agent...</option>
                    <?php foreach ($agents_list as $agent): ?>
                        <option value="<?= htmlspecialchars($agent['name']) ?>" data-email="<?= htmlspecialchars($agent['email']) ?>">
                            <?= htmlspecialchars($agent['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <input type="hidden" id="agent_email_input" name="agent_email">
            
            <div><label for="occurrence_date">Date of Occurrence:</label><input type="date" id="occurrence_date" name="occurrence_date" required></div>
            <div><label><input type="checkbox" name="is_critical" value="1"> Is this a Critical Case?</label></div>
            <div><label for="notes">Remarks / Findings:</label><textarea id="notes" name="notes" rows="3"></textarea></div>
            <div><label for="network_test_result">Upload Network Test Result (Optional):</label><input type="file" id="network_test_result" name="network_test_result" accept=".pdf,.png,.jpg,.jpeg"></div>
            <div><label for="network_test_date">Date of Network Test:</label><input type="date" id="network_test_date" name="network_test_date"></div>
            <button type="submit">Log Hit</button>
        </form>
    </section>

    <!-- CRITICAL ALERTS SECTION -->
    <?php if (!empty($critical_alerts)): ?>
    <section class="critical-alerts">
        <h2><img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiNjODIzMzMiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNMTIgMkwxIDIxaDIyTDEyIDJ6Ij48L3BhdGg+PGxpbmUgeDE9IjEyIiB5MT0iOCIgeDI9IjEyIiB5Mj0iMTIiPjwvbGluZT48bGluZSB4MT0iMTIiIHkxPSIxNiIgeDI9IjEyLjAxIiB5Mj0iMTYiPjwvbGluZT48L3N2Zz4=" alt="Alert Icon" style="vertical-align: middle; margin-right: 8px;"/>Critical Alerts (2+ Critical Hits in 7 Days)</h2>
        <ul>
            <?php foreach ($critical_alerts as $alert): 
                $email_subject = urlencode("Action Required: Review of Recent Critical Calls");
                $email_body = urlencode("Hi " . $alert['agent_name'] . ",\n\nThis is an alert... [Your full email body here]");
            ?>
            <li>
                <?= htmlspecialchars($alert['agent_name']) ?> (<?= $alert['critical_count'] ?> criticals)
                <a href="mailto:<?= htmlspecialchars($alert['agent_email']) ?>?subject=<?= $email_subject ?>&body=<?= $email_body ?>" class="btn-email">Send Email</a>
            </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>

    <!-- AGENT DASHBOARD SECTION -->
    <section id="dashboard">
        <h2>Agent Dashboard</h2>
        <div class="filters">
            <a href="#" data-period="7days" class="filter-link <?= $period == '7days' ? 'active' : '' ?>">Past 7 Days</a>
            <a href="#" data-period="15days" class="filter-link <?= $period == '15days' ? 'active' : '' ?>">Past 15 Days</a>
            <a href="#" data-period="30days" class="filter-link <?= $period == '30days' ? 'active' : '' ?>">Past 30 Days</a>
            <a href="#" data-period="overall" class="filter-link <?= $period == 'overall' ? 'active' : '' ?>">Overall</a>
        </div>
        <div class="export-options">
            <span>Export Current View:</span>
            <a href="export.php?period=<?= $period ?>&format=csv" class="btn-export">Export to CSV</a>
            <a href="export.php?period=<?= $period ?>&format=xlsx" class="btn-export">Export to XLSX</a>
        </div>
        <table>
            <thead>
                <tr><th>Agent</th><th>Total Hits</th><th>Critical Cases</th><th>Last Occurrence</th></tr>
            </thead>
            <tbody id="dashboard-tbody">
                <?php if (!empty($agent_data)): ?>
                    <?php foreach ($agent_data as $agent): ?>
                        <tr>
                            <td><a href="agent_details.php?name=<?= urlencode($agent['agent_name']) ?>"><?= htmlspecialchars($agent['agent_name']) ?></a></td>
                            <td><?= $agent['total_hits'] ?></td>
                            <td><?= $agent['critical_cases'] ?></td>
                            <td><?= date("M d, Y", strtotime($agent['last_occurrence'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No data found for the selected period.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

     <!-- CHARTS & GRAPHS SECTION -->
    <section class="charts-container">
        <div>
            <h2>Hits Per Agent (Overall)</h2>
            <canvas id="agentHitsChart"></canvas>
        </div>
        <div><!-- Empty space --></div>
    </section>

</div>

<script>
$(document).ready(function() {
    // --- Select2 Initialization ---
    $('#agent_select').select2({
        placeholder: "Search or select an agent",
        allowClear: true
    });
    $('#agent_select').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var email = selectedOption.data('email');
        $('#agent_email_input').val(email || '');
    });
    
    // --- Chart.js Initialization ---
    const ctxAgent = document.getElementById('agentHitsChart');
    if (ctxAgent) {
        new Chart(ctxAgent.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($chart_agent_labels) ?>,
                datasets: [{
                    label: 'Total Hits',
                    data: <?= json_encode($chart_agent_data) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
    }

    // --- AJAX Logic for Dashboard Filtering ---
    $('.filter-link').on('click', function(event) {
        event.preventDefault(); // Stop the link from navigating/scrolling

        const selectedPeriod = $(this).data('period');
        const dashboardTbody = $('#dashboard-tbody');
        
        $('.filter-link').removeClass('active');
        $(this).addClass('active');
        
        dashboardTbody.html('<tr><td colspan="4" style="text-align:center;">Loading...</td></tr>');
        
        $.ajax({
            url: 'fetch_dashboard.php',
            type: 'GET',
            data: { period: selectedPeriod },
            success: function(response) {
                dashboardTbody.html(response);
                
                $('.btn-export').each(function() {
                    let href = $(this).attr('href').split('?')[0];
                    let format = href.includes('csv') ? 'csv' : 'xlsx';
                    $(this).attr('href', `export.php?period=${selectedPeriod}&format=${format}`);
                });
                
                const newUrl = window.location.pathname + '?period=' + selectedPeriod;
                history.pushState({path: newUrl}, '', newUrl);
            },
            error: function() {
                dashboardTbody.html('<tr><td colspan="4" style="color:red; text-align:center;">Error loading data.</td></tr>');
            }
        });
    });
});
</script>
</body>
</html>