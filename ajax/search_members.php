<?php
require_once '../db/connection.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $sql = "SELECT * FROM members WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_fill(0, 4, $searchTerm);
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($members)) {
        echo '<div class="col">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-people" style="font-size: 2rem; color: #6a1b9a;"></i>
                        <h5 class="mt-3">No Members Found</h5>
                        <p class="text-muted">Try adjusting your search criteria</p>
                    </div>
                </div>
            </div>';
    } else {
        foreach ($members as $member) {
            $status_class = match($member['status']) {
                'active' => 'text-success',
                'inactive' => 'text-danger',
                default => 'text-muted'
            };
            ?>
            <div class="col">
                <div class="member-card card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></h5>
                            <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($member['status']); ?></span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($member['email']); ?><br>
                                <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($member['phone']); ?>
                            </small>
                        </div>
                        <div class="member-actions">
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#pastoralCareModal" 
                                    data-member-id="<?php echo $member['id']; ?>" 
                                    data-member-name="<?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>">
                                <i class="bi bi-heart"></i> Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
} catch(PDOException $e) {
    echo '<div class="alert alert-danger">Error retrieving members: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
