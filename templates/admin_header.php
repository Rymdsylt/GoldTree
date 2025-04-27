<?php

require_once __DIR__ . '/../db/connection.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['user_id'])) {
    header("Location: /GoldTree/login.php");
    exit();
}

$stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['admin_status'] != 1) {
    header("Location: /GoldTree/events.php?page=events");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo ucfirst(str_replace('.php', '', $current_page)); ?></title>
    <link rel="stylesheet" href="/GoldTree/css/bootstrap.min.css">
    <link rel="stylesheet" href="/GoldTree/css/theme.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #6a1b9a;
            --primary-hover: #4a148c;
            --secondary: #9c27b0;
            --success: #2e7d32;
            --danger: #c62828;
            --warning: #f57f17;
            --info: #1565c0;
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

        .btn-primary.active {
            background-color: #0056b3;
            border-color: #0056b3;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.5);
        }

        /* Updated admin sidebar styles */
        .admin-sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--header-height));
            overflow-y: auto;
            background: var(--white);
            box-shadow: var(--shadow);
            z-index: 1020;
            transition: var(--transition);
        }

        .admin-card {
            transition: transform 0.2s ease;
        }

        .admin-card:hover {
            transform: translateY(-5px);
        }

        .sidebar-collapsed .admin-sidebar {
            transform: translateX(-100%);
        }

        .admin-content {
            transition: margin-left 0.3s ease;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 20px;
            min-height: calc(100vh - var(--header-height));
            transition: var(--transition);
        }

        /* Fade animations */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-hover);
        }

        /* Admin navigation styles */
        .admin-sidebar .sidebar-link {
            color: #495057;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .admin-sidebar .sidebar-link:hover {
            background: linear-gradient(45deg, var(--primary), var(--primary-hover));
            color: white;
        }

        .admin-sidebar .sidebar-link.active {
            background: linear-gradient(45deg, var(--primary), var(--primary-hover));
            color: white;
            font-weight: 500;
        }

        .admin-sidebar .sidebar-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }

            .main-content.sidebar-hidden {
                margin-left: var(--sidebar-width);
            }

            .navbar .container-fluid {
                padding-left: 10px;
                padding-right: 10px;
            }

            .navbar-brand {
                font-size: 1.1rem;
            }

            .btn {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }

            .sidebar {
                width: 100%;
                position: fixed;
                z-index: 1000;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }
            .navbar-brand {
                font-size: 1.2rem;
            }
            .dropdown-menu {
                position: fixed !important;
                width: 100%;
                bottom: 0;
                border-radius: 1rem 1rem 0 0;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            }
        }
    </style>
</head>
<body>
    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <button class="navbar-toggler border-0" type="button" id="adminSidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand" href="/GoldTree/admin.php">
                <i class="bi bi-shield-lock"></i> Admin Panel
            </a>
            <div class="d-flex align-items-center">
                <a href="/GoldTree/Dashboard_intro.php?page=dashboard" class="btn btn-outline-primary me-2 d-none d-md-inline-block">
                    <i class="bi bi-house"></i> Main Dashboard
                </a>
                <a href="/GoldTree/reports.php" class="btn btn-outline-primary me-2 d-none d-md-inline-block">
                    <i class="bi bi-graph-up"></i> Reports
                </a>
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle text-dark" type="button" id="userMenu" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/GoldTree/profile.php">Profile</a></li>
                        <li><a class="dropdown-item d-md-none" href="/GoldTree/Dashboard_intro.php?page=dashboard">Main Dashboard</a></li>
                        <li><a class="dropdown-item d-md-none" href="/GoldTree/reports.php">Reports</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/GoldTree/auth/logout_user.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Admin Sidebar -->
    <div class="sidebar admin-sidebar">
        <div class="p-3">
            <h5 class="mb-4 text-primary">Admin Controls</h5>
            <nav class="nav flex-column">
                <a href="/GoldTree/admin/manage_accounts.php" class="sidebar-link <?php echo $current_page == 'manage_accounts.php' ? 'active' : ''; ?>">
                    <i class="bi bi-person-badge"></i> Manage Accounts
                </a>
                <a href="/GoldTree/admin/manage_members.php" class="sidebar-link <?php echo $current_page == 'manage_members.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i> Manage Members
                </a>
                <a href="/GoldTree/admin/add_donations.php" class="sidebar-link <?php echo $current_page == 'add_donations.php' ? 'active' : ''; ?>">
                    <i class="bi bi-cash-coin"></i> Add Donations
                </a>
                <a href="/GoldTree/admin/add_events.php" class="sidebar-link <?php echo $current_page == 'add_events.php' ? 'active' : ''; ?>">
                    <i class="bi bi-calendar-plus"></i> Add Events
                </a>
                <a href="/GoldTree/admin/notify_members.php" class="sidebar-link <?php echo $current_page == 'notify_members.php' ? 'active' : ''; ?>">
                    <i class="bi bi-envelope"></i> Notify Members
                </a>
                <a href="/GoldTree/admin/notifications.php" class="sidebar-link <?php echo $current_page == 'notifications.php' ? 'active' : ''; ?>">
                    <i class="bi bi-bell"></i> View Notifications
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content Container -->
    <main class="main-content admin-content">