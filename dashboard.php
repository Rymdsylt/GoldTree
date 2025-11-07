<?php 
require_once 'templates/header.php';
require_once 'auth/login_status.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Check if database is PostgreSQL
$isPostgres = (getenv('DATABASE_URL') !== false);

$privacyStmt = $conn->prepare("SELECT privacy_agreement FROM users WHERE id = ?");
$privacyStmt->execute([$_SESSION['user_id']]);
$privacyResult = $privacyStmt->fetch();

// Handle boolean check for both PostgreSQL and MySQL
if ($isPostgres) {
    // PostgreSQL returns boolean as 't'/'f' string or actual boolean
    $privacyValue = $privacyResult['privacy_agreement'];
    $privacyStatus = ($privacyValue === true || $privacyValue === 't' || $privacyValue === 1) ? 1 : null;
} else {
    // MySQL returns as integer (0 or 1) or null
    $privacyStatus = $privacyResult['privacy_agreement'];
}

if (!isset($_SESSION['active_page'])) {
    $_SESSION['active_page'] = 'dashboard';
}

if (isset($_GET['page'])) {
    $_SESSION['active_page'] = $_GET['page'];
}

$stmt = $conn->prepare("SELECT users.*, members.first_name, members.last_name 
    FROM users 
    LEFT JOIN members ON users.member_id = members.id 
    WHERE users.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$isAdmin = isset($user['role']) && $user['role'] === 'admin';
?>



<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card stat-card">
                <div class="card-body p-4">
                    <h4 class="card-title mb-0">
                        Welcome, <?php echo htmlspecialchars($user['username']); ?>!
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Members</h5>
                    <p class="card-text flex-grow-1">View and manage church members.</p>
                    <a href="members.php?page=members" class="btn btn-primary mt-auto w-100 <?php echo ($_SESSION['active_page'] === 'members') ? 'active' : ''; ?>">
                        <i class="bi bi-people-fill"></i> Go to Members
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Events</h5>
                    <p class="card-text flex-grow-1">Manage church events and activities.</p>
                    <a href="events.php?page=events" class="btn btn-primary mt-auto w-100 <?php echo ($_SESSION['active_page'] === 'events') ? 'active' : ''; ?>">
                        <i class="bi bi-calendar-event"></i> Go to Events
                    </a>
                </div>
            </div>
        </div>
  

        <?php if ($isAdmin): ?>
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Administrative Tools</h5>
                    <p class="card-text flex-grow-1">Access admin panel for advanced management options.</p>
                    <a href="admin/manage_accounts.php?page=admin" class="btn btn-primary mt-auto w-100 <?php echo ($_SESSION['active_page'] === 'admin') ? 'active' : ''; ?>">
                        <i class="bi bi-gear"></i> Go to Admin Panel
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
