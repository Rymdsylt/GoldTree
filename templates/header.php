<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';

if (!isset($_SESSION['active_page'])) {
    $_SESSION['active_page'] = 'dashboard';
}

if (isset($_GET['page'])) {
    $_SESSION['active_page'] = $_GET['page'];
}

$current_page = basename($_SERVER['PHP_SELF']);
$isAdmin = false;
$username = '';

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT username, admin_status, privacy_agreement FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $isAdmin = ($user['admin_status'] > 0);
        $username = htmlspecialchars($user['username']);
        $privacyStatus = $user['privacy_agreement'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Mater Dolorosa Church Management</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #6a1b9a;
            --primary-hover: #4a148c;
            --header-height: 60px;
            --sidebar-width: 250px;
        }

        body {
            overflow-x: hidden;
        }

        .navbar {
            position: fixed;
            width: 100%;
            z-index: 1030;
        }

        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--header-height));
            overflow-y: auto;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            z-index: 1020;
            transition: transform 0.3s ease-in-out;
        }

        .collapse.navbar-collapse {
            position: static;
            background: transparent !important;
            pointer-events: auto;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 20px;
            min-height: calc(100vh - var(--header-height));
            transition: margin-left 0.3s ease;
        }

        .sidebar-link {
            position: relative;
            display: flex;
            align-items: center;
            color: #495057;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
            margin-bottom: 0.25rem;
        }

        .sidebar-link:hover {
            background: linear-gradient(45deg, var(--primary), var(--primary-hover));
            color: white;
        }

        .sidebar-link.active {
            background: linear-gradient(45deg, var(--primary), var(--primary-hover));
            color: white;
            font-weight: 500;
        }

        .sidebar-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            min-width: 1.5rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: var(--header-height);
                left: 0;
                width: 280px;
                max-width: 85vw;
                height: calc(100vh - var(--header-height));
                background: white;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
                z-index: 1001;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                pointer-events: auto;
            }

            .sidebar.show {
                transform: translateX(0);
                pointer-events: auto;
            }

            .collapse.navbar-collapse {
                position: fixed;
                top: var(--header-height);
                left: 280px;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1000;
                display: none !important;
                pointer-events: none;
            }

            .collapse.navbar-collapse.show {
                display: block !important;
                pointer-events: auto;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }

            .navbar .container-fluid {
                padding-left: 10px;
                padding-right: 10px;
            }

            .navbar-brand {
                font-size: 1.1rem;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
</head>
<body data-base-path="<?php echo BASE_PATH; ?>">

<!-- Privacy Policy Modal -->
<?php if (isset($_SESSION['user_id']) && $privacyStatus === null): ?>
<div class="modal fade" id="privacyPolicyModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="privacyPolicyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyPolicyModalLabel">Privacy Policy</h5>
            </div>
            <div class="modal-body privacy-policy-content">
                <?php 
                require_once __DIR__ . '/../vendor/autoload.php';
                $parsedown = new Parsedown();
                $privacyContent = file_get_contents(__DIR__ . '/../Privacy Policy for Mater Dolorosa Parish.md');
                echo $parsedown->text($privacyContent);
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="disagreeBtn">Disagree</button>
                <button type="button" class="btn btn-primary" id="agreeBtn">Agree</button>
            </div>
        </div>
    </div>
</div>

<style>
.privacy-policy-content {
    padding: 20px;
    line-height: 1.6;
}

.privacy-policy-content h1 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
}

.privacy-policy-content h2 {
    color: #34495e;
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.privacy-policy-content ul {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.privacy-policy-content li {
    margin-bottom: 0.5rem;
}

.privacy-policy-content strong {
    color: #2c3e50;
}

.privacy-policy-content em {
    color: #7f8c8d;
}

.privacy-policy-content hr {
    margin: 2rem 0;
    border-top: 1px solid #eee;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('privacyPolicyModal')) {
        var modal = new bootstrap.Modal(document.getElementById('privacyPolicyModal'));
        modal.show();

        document.getElementById('agreeBtn').addEventListener('click', function() {
            handlePrivacyAgreement(true);
        });

        document.getElementById('disagreeBtn').addEventListener('click', function() {
            handlePrivacyAgreement(false);
        });

        function handlePrivacyAgreement(agreed) {
            fetch('auth/handle_privacy_agreement.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'agreed=' + agreed
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (!agreed) {
                        window.location.href = 'login.php';
                    } else {
                        modal.hide();
                        location.reload();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    }
});
</script>
<?php endif; ?>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="assets/img/logo.png" alt="Mater Dolorosa Church Logo" height="30" class="me-2">
                Mater Dolorosa Church
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (!empty($username)): ?>
                        <li class="nav-item">
                            <span class="nav-link">Welcome, <?php echo $username; ?></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['alert'])): ?>
        <div class="alert alert-<?php echo $_SESSION['alert_type']; ?> alert-dismissible fade show m-3" role="alert">
            <?php echo $_SESSION['alert']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['alert']); unset($_SESSION['alert_type']); ?>
    <?php endif; ?>

    <!-- Sidebar -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="sidebar">
        <div class="p-3">
            <h5 class="mb-4 text-primary">Navigation</h5>
            <nav class="nav flex-column">
                <a href="<?php echo base_path('Dashboard_intro.php'); ?>?page=dashboard" 
                   class="sidebar-link <?php echo $_SESSION['active_page'] == 'dashboard' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="<?php echo base_path('members.php'); ?>?page=members" 
                   class="sidebar-link <?php echo $_SESSION['active_page'] == 'members' ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i> Members
                </a>
                <a href="<?php echo base_path('events.php'); ?>?page=events" 
                   class="sidebar-link <?php echo $_SESSION['active_page'] == 'events' ? 'active' : ''; ?>">
                    <i class="bi bi-calendar-event"></i> Events
                </a>
                <?php if ($isAdmin): ?>
                <a href="<?php echo base_path('donations.php'); ?>?page=donations" 
                   class="sidebar-link <?php echo $_SESSION['active_page'] == 'donations' ? 'active' : ''; ?>">
                    <i class="bi bi-cash-coin"></i> Donations
                </a>
                <?php endif; ?>
                <a href="<?php echo base_path('announcements.php'); ?>?page=announcements" 
                   class="sidebar-link <?php echo $_SESSION['active_page'] == 'announcements' ? 'active' : ''; ?>">
                    <i class="bi bi-megaphone"></i> Notifications
                    <span id="unreadNotificationsBadge" class="notification-badge d-none">0</span>
                </a>
                <a href="<?php echo base_path('reports.php'); ?>?page=reports" 
                   class="sidebar-link <?php echo $_SESSION['active_page'] == 'reports' ? 'active' : ''; ?>">
                    <i class="bi bi-graph-up"></i> Reports
                </a>
                <?php if ($isAdmin): ?>
                <a href="<?php echo base_path('sacramental.php'); ?>?page=sacramental" 
                   class="sidebar-link <?php echo $_SESSION['active_page'] == 'sacramental' ? 'active' : ''; ?>">
                    <i class="bi bi-journal-text"></i> Sacramental Records
                </a>
                    <div class="mt-4">
                        <h6 class="text-muted px-3 mb-3">Admin</h6>
                        <a href="<?php echo base_path('admin/manage_accounts.php'); ?>" class="sidebar-link <?php echo $current_page == 'manage_accounts.php' ? 'active' : ''; ?>">
                            <i class="bi bi-gear"></i> Admin Panel
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- User Account Section -->
                <div class="mt-4">
                    <h6 class="text-muted px-3 mb-3">Account</h6>
                    <a href="profile.php" class="sidebar-link">
                        <i class="bi bi-person-circle"></i> Profile
                    </a>
                    <a href="auth/logout_user.php" class="sidebar-link">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </nav>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Container -->
    <main class="main-content">
    <?php 
    if (isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/privacy_policy_modal.php';
    }
    ?>

    <script>
    function updateNotificationBadge() {
        fetch('<?php echo base_path('crud/notifications/get_unread_count.php'); ?>')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('unreadNotificationsBadge');
                if (data.success) {
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                        badge.classList.remove('d-none');
                    } else {
                        badge.classList.add('d-none');
                    }
                }
            })
            .catch(console.error);
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateNotificationBadge();
        setInterval(updateNotificationBadge, 60000);

        // Sidebar toggle logic
        const collapseEl = document.getElementById('navbarNav');
        const sidebar = document.querySelector('.sidebar');
        
        if (!collapseEl || !sidebar) return;
        
        // When collapse is toggled, also toggle sidebar visibility
        collapseEl.addEventListener('show.bs.collapse', function() {
            sidebar.classList.add('show');
        });
        
        collapseEl.addEventListener('hide.bs.collapse', function() {
            sidebar.classList.remove('show');
        });
        
        // Close sidebar when clicking on a link
        const sidebarLinks = sidebar.querySelectorAll('.sidebar-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    // Close the collapse
                    const bsCollapse = new bootstrap.Collapse(collapseEl, { toggle: false });
                    bsCollapse.hide();
                    sidebar.classList.remove('show');
                }
            });
        });
        
        // Close sidebar when clicking overlay (but not sidebar itself)
        collapseEl.addEventListener('click', function(e) {
            // Only close if clicking the overlay background, not the sidebar
            if (e.target === collapseEl && window.innerWidth <= 768) {
                const bsCollapse = new bootstrap.Collapse(collapseEl, { toggle: false });
                bsCollapse.hide();
                sidebar.classList.remove('show');
            }
        });
    });
    </script>
</body>
</html>
