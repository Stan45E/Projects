<?php
// Add these lines at the very top to see any errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php';

// Check if agent name is provided in the URL
if (!isset($_GET['name']) || empty($_GET['name'])) {
    die("No agent specified.");
}

$agent_name = $_GET['name'];

// Use a prepared statement to fetch all hits for the specified agent
$sql = "SELECT * FROM bad_call_hits WHERE agent_name = ? ORDER BY occurrence_date DESC";
$hits_data = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $agent_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $hits_data[] = $row;
        }
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details for <?= htmlspecialchars($agent_name) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="complaint.png">
</head>
<body>
<div class="container">
    <a href="index.php" class="back-link">← Back to Dashboard</a>
    <h1>All Hits for: <strong><?= htmlspecialchars($agent_name) ?></strong></h1>
    
    <?php /* Success/Error Messages */ ?>

    <?php if (!empty($hits_data)): ?>
    <table>
        <thead>
            <tr>
                <th>Occurrence Date</th>
                <th>Critical?</th>
                <th>Remarks</th>
                <!--<th>Call Recording</th>-->
                <th>Network Test</th>
                <th>Last Modified</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hits_data as $hit): ?>
                <tr>
                    <td><?= date("M d, Y", strtotime($hit['occurrence_date'])) ?></td>
                    <td><?= $hit['is_critical'] ? 'Yes' : 'No' ?></td>
                    <td><?= nl2br(htmlspecialchars($hit['notes'])) ?></td>
                    </td> 
                    <td>
                        <?php if (!empty($hit['network_test_path'])): ?>
                            <p style="margin:0; font-size: 0.9em;">
                                Taken: <?= htmlspecialchars(date("M d, Y", strtotime($hit['network_test_date']))) ?>
                            </p>
                            <button class="btn-view-result" data-src="<?= htmlspecialchars($hit['network_test_path']) ?>">
                                View Result
                            </button>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $hit['modified_at'] ? date("M d, Y H:i", strtotime($hit['modified_at'])) : 'Never' ?>
                    </td>
                    <td class="actions-cell">
                        <a href="edit_hit.php?id=<?= $hit['id'] ?>" class="btn-edit">Edit</a>
                        <a href="delete_hit.php?id=<?= $hit['id'] ?>&agent=<?= urlencode($agent_name) ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No hits found for this agent.</p>
    <?php endif; ?>
</div>

<!-- MODAL STRUCTURE for Network Test Popup -->
<div id="resultModal" class="modal">
    <div class="modal-content">
        <span class="close-button">×</span>
        <div id="modal-body"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('resultModal');
    const modalBody = document.getElementById('modal-body');
    const closeButton = document.querySelector('.close-button');

    // --- ENHANCED: Function to close the modal and stop any media ---
    const closeModal = () => {
        // Find any audio or video element inside the modal
        const mediaElement = modalBody.querySelector('audio, video');
        if (mediaElement) {
            mediaElement.pause(); // Stop it from playing in the background
        }
        modal.style.display = 'none';
        modalBody.innerHTML = ''; // Clear the content
    };

    // Close modal event listeners
    closeButton.addEventListener('click', closeModal);
    window.addEventListener('click', (event) => {
        if (event.target == modal) {
            closeModal();
        }
    });

    // --- Event listener for "View Result" (Network Test) Buttons ---
    document.querySelectorAll('.btn-view-result').forEach(button => {
        button.addEventListener('click', function () {
            const filePath = this.getAttribute('data-src');
            const fileExtension = filePath.split('.').pop().toLowerCase();
            
            modalBody.innerHTML = ''; 

            if (['png', 'jpg', 'jpeg', 'gif'].includes(fileExtension)) {
                modalBody.innerHTML = `<img src="${filePath}" style="max-width: 100%; height: auto;">`;
            } else if (fileExtension === 'pdf') {
                modalBody.innerHTML = `<iframe src="${filePath}" style="width: 100%; height: 80vh; border: none;"></iframe>`;
            } else {
                modalBody.innerHTML = `<p>Cannot preview. <a href="${filePath}" target="_blank">Download here</a>.</p>`;
            }
            
            modal.style.display = 'block';
        });
    });

    // --- NEW: Event listener for "Listen" (Call Recording) Buttons ---
   // document.querySelectorAll('.btn-listen-recording').forEach(button => {
    //    button.addEventListener('click', function () {
     //       const filePath = this.getAttribute('data-src');
            
     //       modalBody.innerHTML = ''; // Clear previous content

            // Create an HTML5 audio player
      //      const audioPlayer = document.createElement('audio');
      //      audioPlayer.src = filePath;
      //      audioPlayer.controls = true; // Show player controls (play, pause, volume)
      //      audioPlayer.autoplay = true; // Start playing automatically
     //       audioPlayer.style.width = '100%'; // Make it fit the modal width

      //      modalBody.appendChild(audioPlayer);
      //      modal.style.display = 'block';
      //  });
    //});
});
</script>
</body>
</html>