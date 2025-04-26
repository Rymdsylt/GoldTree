<?php
require_once 'auth/login_status.php';
require_once 'db/connection.php';


if (!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['admin_status'] != 1) {
    header("Location: dashboard.php");
    exit();
}
?>

<?php require_once 'templates/admin_header.php'; ?>

<div class="container-fluid">
    <div class="row g-4">
        <div class="col-12">
            <h2 class="mb-4">Admin Dashboard</h2>
        </div>
        
        <!-- Quick Stats -->
        <div class="col-md-3">
            <div class="admin-card card mb-4">
                <div class="card-body">
                    <h5 class="card-title text-primary">Total Members</h5>
                    <?php
                    $stmt = $conn->query("SELECT COUNT(*) as count FROM members");
                    $memberCount = $stmt->fetch()['count'];
                    ?>
                    <h3 class="mb-0"><?php echo $memberCount; ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="admin-card card mb-4">
                <div class="card-body">
                    <h5 class="card-title text-primary">Recent Donations</h5>
                    <?php
                    $stmt = $conn->query("SELECT COUNT(*) as count FROM donations WHERE date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                    $recentDonations = $stmt->fetch()['count'];
                    ?>
                    <h3 class="mb-0"><?php echo $recentDonations; ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="admin-card card mb-4">
                <div class="card-body">
                    <h5 class="card-title text-primary">Upcoming Events</h5>
                    <?php
                    $stmt = $conn->query("SELECT COUNT(*) as count FROM events WHERE date >= CURDATE()");
                    $upcomingEvents = $stmt->fetch()['count'];
                    ?>
                    <h3 class="mb-0"><?php echo $upcomingEvents; ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="admin-card card mb-4">
                <div class="card-body">
                    <h5 class="card-title text-primary">New Members</h5>
                    <?php
                    $stmt = $conn->query("SELECT COUNT(*) as count FROM members WHERE date_joined >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                    $newMembers = $stmt->fetch()['count'];
                    ?>
                    <h3 class="mb-0"><?php echo $newMembers; ?></h3>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="col-md-6">
            <div class="admin-card card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Recent Members</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Date Joined</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $conn->query("SELECT * FROM members ORDER BY date_joined DESC LIMIT 5");
                                while ($member = $stmt->fetch()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) . "</td>";
                                    echo "<td>" . date('M d, Y', strtotime($member['date_joined'])) . "</td>";
                                    echo "<td><a href='admin/manage_members.php?id=" . $member['id'] . "' class='btn btn-sm btn-primary'>View</a></td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="admin-card card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Recent Donations</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $conn->query("SELECT d.*, m.first_name, m.last_name 
                                    FROM donations d 
                                    LEFT JOIN members m ON d.member_id = m.id 
                                    ORDER BY d.date DESC LIMIT 5");
                                while ($donation = $stmt->fetch()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($donation['first_name'] . ' ' . $donation['last_name']) . "</td>";
                                    echo "<td>$" . number_format($donation['amount'], 2) . "</td>";
                                    echo "<td>" . date('M d, Y', strtotime($donation['date'])) . "</td>";
                                    echo "<td><a href='admin/add_donations.php?id=" . $donation['id'] . "' class='btn btn-sm btn-primary'>View</a></td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/admin_footer.php'; ?>