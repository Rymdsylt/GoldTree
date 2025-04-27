<?php
session_start();
require_once __DIR__ . '/../db/connection.php';

// Set default active page if not set
if (!isset($_SESSION['active_page'])) {
    $_SESSION['active_page'] = 'dashboard';
}

// Update active page if page parameter is present
if (isset($_GET['page'])) {
    $_SESSION['active_page'] = $_GET['page'];
}

$current_page = basename($_SERVER['PHP_SELF']);
$isAdmin = false;

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $isAdmin = ($user && $user['admin_status'] > 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mater Dolorosa - <?php echo ucfirst(str_replace('.php', '', $current_page)); ?></title>
    <link rel="stylesheet" href="/GoldTree/css/bootstrap.min.css">
    <link rel="stylesheet" href="/GoldTree/css/theme.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .btn-primary.active {
            background-color: #0056b3;
            border-color: #0056b3;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.5);
        }
    </style>
</head>
<body>
    <!-- Responsive Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <button class="navbar-toggler border-0" type="button" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand" href="/GoldTree/dashboard.php">
                <i class="bi bi-church"></i> Mater Dolorosa
            </a>
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-dark" type="button" id="userMenu" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/GoldTree/profile.php">Profile</a></li>
                            <?php if ($isAdmin): ?>
                                <li><a class="dropdown-item" href="/GoldTree/admin/manage_accounts.php">Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/GoldTree/auth/logout_user.php">Logout</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

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
            </nav>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Container -->
    <main class="main-content <?php echo !isset($_SESSION['user_id']) ? 'ml-0' : ''; ?>"><?php ?>