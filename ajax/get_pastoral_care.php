<?php
require_once '../db/connection.php';

$member_id = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 0;

if (!$member_id) {
    echo '<div class="alert alert-danger">Invalid member ID</div>';
    exit;
}

$query = "SELECT * FROM pastoral_care WHERE member_id = :member_id ORDER BY care_date DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':member_id', $member_id);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($records)) {
    echo '<div class="alert alert-info">No pastoral care records found</div>';
} else {
    echo '<div class="list-group">';
    foreach ($records as $record) {
        $careType = ucfirst(htmlspecialchars($record['care_type']));
        $careDate = htmlspecialchars($record['care_date']);
        $notes = nl2br(htmlspecialchars($record['notes']));
        
        echo '<div class="list-group-item" style="border-left: 4px solid #6a1b9a;">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1" style="color: #6a1b9a;">' . $careType . '</h6>
                    <small class="text-muted">' . $careDate . '</small>
                </div>
                <p class="mb-1">' . $notes . '</p>
            </div>';
    }
    echo '</div>';
}
?>
