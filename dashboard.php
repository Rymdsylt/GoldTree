<?php 
require_once 'templates/header.php';
require_once 'auth/login_status.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
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


$page_to_load = 'Dashboard_intro.php';
if (isset($_GET['page'])) {
    switch ($_GET['page']) {
        case 'members':
            $page_to_load = 'members.php';
            break;
        case 'events':
            $page_to_load = 'events.php';
            break;
        case 'donations':
            $page_to_load = 'donations.php';
            break;
        case 'admin':
            $page_to_load = 'admin/manage_accounts.php';
            break;
    }
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card stat-card">
                <div class="card-body p-4">
                    <h4 class="card-title mb-0">
                        Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!
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
                    <a href="?page=members" class="btn btn-primary mt-auto w-100 <?php echo ($_SESSION['active_page'] === 'members') ? 'active' : ''; ?>">
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
                    <a href="?page=events" class="btn btn-primary mt-auto w-100 <?php echo ($_SESSION['active_page'] === 'events') ? 'active' : ''; ?>">
                        <i class="bi bi-calendar-event"></i> Go to Events
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Donations</h5>
                    <p class="card-text flex-grow-1">Track donations and contributions.</p>
                    <a href="?page=donations" class="btn btn-primary mt-auto w-100 <?php echo ($_SESSION['active_page'] === 'donations') ? 'active' : ''; ?>">
                        <i class="bi bi-cash"></i> Go to Donations
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
                    <a href="?page=admin" class="btn btn-primary mt-auto <?php echo ($_SESSION['active_page'] === 'admin') ? 'active' : ''; ?>">
                        <i class="bi bi-gear"></i> Go to Admin Panel
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>


    <div class="row">
        <div class="col-12">
            <iframe id="contentFrame" src="<?php echo htmlspecialchars($page_to_load); ?>" style="width: 100%; height: 800px; border: none;"></iframe>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[href^="?page="]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.getAttribute('href').split('=')[1];
            const iframe = document.getElementById('contentFrame');

            switch(page) {
                case 'members':
                    iframe.src = 'members.php';
                    break;
                case 'events':
                    iframe.src = 'events.php';
                    break;
                case 'donations':
                    iframe.src = 'donations.php';
                    break;
                case 'admin':
                    iframe.src = 'admin/manage_accounts.php';
                    break;
                default:
                    iframe.src = 'Dashboard_intro.php';
            }
            
            document.querySelectorAll('.btn.active').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
        
            history.pushState({}, '', this.getAttribute('href'));
        });
    });
});
</script>

<?php require_once 'templates/footer.php'; ?>