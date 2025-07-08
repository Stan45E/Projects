<?php
require_once 'db_connect.php';

// Check for ID, if not present, redirect or die
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid record ID.");
}
$id = $_GET['id'];

// Fetch the specific hit from the database
$sql = "SELECT * FROM bad_call_hits WHERE id = ?";
$hit = null;
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $hit = $result->fetch_assoc();
    } else {
        die("Record not found.");
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
    <title>Edit Hit Record</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="complaint.png">
</head>
<body>
<div class="container">
    <a href="agent_details.php?name=<?= urlencode($hit['agent_name']) ?>" class="back-link">← Back to Agent Details</a>
    <h1>Edit Hit Record</h1>
    
    <form action="update_hit.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $hit['id'] ?>">
        
        <div>
            <label for="agent_name">Agent Name:</label>
            <input type="text" id="agent_name" name="agent_name" value="<?= htmlspecialchars($hit['agent_name']) ?>" required>
        </div>
        <div>
            <label for="agent_email">Agent Email:</label>
            <input type="email" id="agent_email" name="agent_email" value="<?= htmlspecialchars($hit['agent_email']) ?>" required>
        </div>
        <div>
            <label for="occurrence_date">Date of Occurrence:</label>
            <input type="date" id="occurrence_date" name="occurrence_date" value="<?= $hit['occurrence_date'] ?>" required>
        </div>
        <div>
            <label><input type="checkbox" name="is_critical" value="1" <?= $hit['is_critical'] ? 'checked' : '' ?>> Is this a Critical Case?</label>
        </div>
        <div>
            <label for="notes">Remarks / Findings (Notes):</label>
            <textarea id="notes" name="notes" rows="4"><?= htmlspecialchars($hit['notes']) ?></textarea>
        </div>
    
        
        <!-- ADDED: Network Test section -->
        <div>
            <label for="network_test_result">Upload New Network Test Result (Optional, replaces existing):</label>
             <?php if (!empty($hit['network_test_path'])): ?>
                <p style="margin-bottom: 5px;">
                    Current result (<?= date("M d, Y", strtotime($hit['network_test_date'])) ?>): 
                    <!-- MODIFIED: Changed link to a button for the modal -->
                    <button type="button" class="btn-view-result" data-src="<?= htmlspecialchars($hit['network_test_path']) ?>">View Result</button>
                </p>
            <?php endif; ?>
            <input type="file" id="network_test_result" name="network_test_result" accept=".pdf,.png,.jpg,.jpeg">
        </div>
        <div>
            <label for="network_test_date">Date of Network Test:</label>
            <input type="date" id="network_test_date" name="network_test_date" value="<?= htmlspecialchars($hit['network_test_date']) ?>">
        </div>

        <button type="submit">Update Record</button>
    </form>
</div>

<!-- NEW: MODAL STRUCTURE (Copied from agent_details.php) -->
<div id="resultModal" class="modal">
    <div class="modal-content">
        <span class="close-button">×</span>
        <div id="modal-body"></div>
    </div>
</div>

<!-- NEW: JAVASCRIPT FOR MODAL (Copied from agent_details.php) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('resultModal');
    const modalBody = document.getElementById('modal-body');
    const closeButton = document.querySelector('.close-button');

    const closeModal = () => {
        const mediaElement = modalBody.querySelector('audio, video');
        if (mediaElement) {
            mediaElement.pause();
        }
        modal.style.display = 'none';
        modalBody.innerHTML = '';
    };

    closeButton.addEventListener('click', closeModal);
    window.addEventListener('click', (event) => {
        if (event.target == modal) {
            closeModal();
        }
    });

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

    //document.querySelectorAll('.btn-listen-recording').forEach(button => {
    //    button.addEventListener('click', function () {
    //        const filePath = this.getAttribute('data-src');
      //      modalBody.innerHTML = '';
      //      modalBody.innerHTML = `<audio src="${filePath}" controls autoplay style="width: 100%;"></audio>`;
      //      modal.style.display = 'block';
      //  });
   // });
});
</script>

</body>
</html>