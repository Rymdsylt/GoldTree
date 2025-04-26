<?php 
require_once 'templates/header.php';


$stmt = $conn->query("SELECT COUNT(*) as total_members, 
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_members 
    FROM members");
$memberStats = $stmt->fetch(PDO::FETCH_ASSOC);


$stmt = $conn->query("SELECT 
    SUM(amount) as total_donations,
    COUNT(DISTINCT member_id) as unique_donors
    FROM donations 
    WHERE MONTH(donation_date) = MONTH(CURRENT_DATE)
    AND YEAR(donation_date) = YEAR(CURRENT_DATE)");
$donationStats = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT * FROM events 
    WHERE start_datetime >= CURRENT_DATE 
    AND status = 'upcoming'
    ORDER BY start_datetime ASC LIMIT 5");
$upcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $conn->query("SELECT d.*, m.first_name, m.last_name 
    FROM donations d
    LEFT JOIN members m ON d.member_id = m.id
    ORDER BY d.created_at DESC LIMIT 5");
$recentDonations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT * FROM notifications 
    WHERE end_date >= CURRENT_DATE OR end_date IS NULL
    ORDER BY priority DESC, created_at DESC LIMIT 5");
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Members</h6>
                    <h2 class="card-title mb-0"><?php echo number_format($memberStats['total_members']); ?></h2>
                    <small><?php echo number_format($memberStats['active_members']); ?> active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Monthly Donations</h6>
                    <h2 class="card-title mb-0">₱<?php echo number_format($donationStats['total_donations'], 2); ?></h2>
                    <small><?php echo $donationStats['unique_donors']; ?> donors this month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Upcoming Events</h6>
                    <h2 class="card-title mb-0"><?php echo count($upcomingEvents); ?></h2>
                    <small>Next 30 days</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Active Notifications</h6>
                    <h2 class="card-title mb-0"><?php echo count($notifications); ?></h2>
                    <small>Current notices</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Upcoming Events</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($upcomingEvents)): ?>
                        <p class="text-muted text-center">No upcoming events</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                        <?php foreach ($upcomingEvents as $event): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y', strtotime($event['start_datetime'])); ?>
                                    </small>
                                </div>
                                <p class="mb-1"><?php echo htmlspecialchars($event['location']); ?></p>
                                <small class="text-muted">
                                    <?php echo date('h:i A', strtotime($event['start_datetime'])); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Recent Donations</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentDonations)): ?>
                        <p class="text-muted text-center">No recent donations</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                        <?php foreach ($recentDonations as $donation): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <?php echo htmlspecialchars($donation['first_name'] . ' ' . $donation['last_name']); ?>
                                    </h6>
                                    <span class="badge bg-success">₱<?php echo number_format($donation['amount'], 2); ?></span>
                                </div>
                                <p class="mb-1"><?php echo ucfirst($donation['donation_type']); ?></p>
                                <small class="text-muted">
                                    <?php echo date('M d, Y', strtotime($donation['donation_date'])); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Active Notifications</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($notifications)): ?>
                        <p class="text-muted text-center">No active notifications</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="list-group-item notification-priority-<?php echo $notification['priority']; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                    <small class="text-muted">
                                        <?php 
                                        echo $notification['end_date'] 
                                            ? 'Until ' . date('M d, Y', strtotime($notification['end_date']))
                                            : 'No end date';
                                        ?>
                                    </small>
                                </div>
                                <p class="mb-1"><?php echo htmlspecialchars($notification['content']); ?></p>
                                <small class="text-muted">
                                    Priority: <?php echo ucfirst($notification['priority']); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>