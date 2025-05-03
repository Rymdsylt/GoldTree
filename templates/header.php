<?php
session_start();
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
    $stmt = $conn->prepare("SELECT username, admin_status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $isAdmin = ($user['admin_status'] > 0);
        $username = htmlspecialchars($user['username']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Mater Dolorosa Church Management</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            min-width: 1.5rem;
            text-align: center;
        }
        .sidebar-link {
            position: relative;
            display: inline-block;
        }
    </style>
</head>
<body>
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
                <a href="/GoldTree/Dashboard_intro.php?page=dashboard" 
                   class="sidebar-link <?php echo $_SESSION['active_page'] == 'dashboard' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="/GoldTree/members.php?page=members" 
                   class="sidebar-link <?php echo $_SESSION['active_page'] == 'members' ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i> Members
                </a>
                <a href="/GoldTree/donations.php?page=donations" 
                   class="sidebar-link <?php echo $_SESSION['active_page'] == 'donations' ? 'active' : ''; ?>">
                    <i class="bi bi-cash"></i> Donations
                </a>
                <a href="/GoldTree/events.php?page=events" 
                   class="sidebar-link <?php echo $_SESSION['active_page'] == 'events' ? 'active' : ''; ?>">
                    <i class="bi bi-calendar-event"></i> Events
                </a>
                <a href="/GoldTree/announcements.php?page=announcements" 
                   class="sidebar-link <?php echo $_SESSION['active_page'] == 'announcements' ? 'active' : ''; ?>">
                    <i class="bi bi-megaphone"></i> Notifications
                    <span id="unreadNotificationsBadge" class="notification-badge d-none">0</span>
                </a>
                <a href="/GoldTree/reports.php?page=reports" 
                   class="sidebar-link <?php echo $_SESSION['active_page'] == 'reports' ? 'active' : ''; ?>">
                    <i class="bi bi-graph-up"></i> Reports
                </a>
                <?php if ($isAdmin): ?>
                    <div class="mt-4">
                        <h6 class="text-muted px-3 mb-3">Admin</h6>
                        <a href="/GoldTree/admin/manage_accounts.php" class="sidebar-link <?php echo $current_page == 'manage_accounts.php' ? 'active' : ''; ?>">
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
    <main class="main-content <?php echo !isset($_SESSION['user_id']) ? 'ml-0' : ''; ?>">

    <script>
    function updateNotificationBadge() {
        fetch('/GoldTree/crud/notifications/get_unread_count.php')
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

    document.addEventListener('DOMContentLoaded', updateNotificationBadge);
    setInterval(updateNotificationBadge, 60000);
    </script>
