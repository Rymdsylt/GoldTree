<!DOCTYPE html>

<?php

require_once 'auth/login_status.php';
session_start();
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mater Dolorosa Church Management</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-green: #2d5a3f;
            --hover-green: #1a472a;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background-color: var(--primary-green);
            padding: 20px;
            transition: 0.3s;
            z-index: 1000;
        }
        
        .sidebar-link, .sidebar h3 {
            color: white !important;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: 0.3s;
        }
        
        .sidebar-link {
            text-decoration: none;
            display: block;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .sidebar-link:hover {
            background-color: var(--hover-green);
            color: white !important;
        }
        
        .sidebar-link i {
            margin-right: 10px;
        }
        
        .active {
            background-color: var(--hover-green);
        }

        .navbar-toggler {
            display: none;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1001;
            background-color: var(--primary-green);
            border: none;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
                padding-top: 60px;
            }
            
            .navbar-toggler {
                display: block;
            }
        }

        .btn-primary {
            background-color: var(--primary-green) !important;
            border-color: var(--primary-green) !important;
            color: white !important;
        }

        .btn-primary:hover {
            background-color: var(--hover-green) !important;
            border-color: var(--hover-green) !important;
        }

        .btn-primary:focus {
            box-shadow: 0 0 0 0.25rem rgba(45, 90, 63, 0.25) !important;
        }

        a {
            color: var(--primary-green) !important;
        }

        a:hover {
            color: var(--hover-green) !important;
        }
    </style>
</head>
<body>

    <button class="navbar-toggler" type="button">
        <span class="navbar-toggler-icon"></span>
    </button>


    <div class="sidebar">
        <h3 class="mb-4 text-center">Mater Dolorosa</h3>
        <nav>
            <a href="members.php" class="sidebar-link">
                <i class="bi bi-people-fill"></i> Member Management
            </a>
            <a href="donations.php" class="sidebar-link">
                <i class="bi bi-cash"></i> Donations
            </a>
            <a href="events.php" class="sidebar-link">
                <i class="bi bi-calendar-event"></i> Events
            </a>
            <a href="announcements.php" class="sidebar-link">
                <i class="bi bi-megaphone"></i> Announcements
            </a>
            <a href="reports.php" class="sidebar-link">
                <i class="bi bi-graph-up"></i> Reports
            </a>
            <a href="settings.php" class="sidebar-link mt-5">
                <i class="bi bi-gear"></i> Settings
            </a>
            <a href="auth/logout_user.php" class="sidebar-link">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </nav>
    </div>


    <div class="main-content">
        <h2>Dashboard</h2>
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Members</h5>
                        <p class="card-text h3">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Today's Events</h5>
                        <p class="card-text h3">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">This Month's Donations</h5>
                        <p class="card-text h3">₱0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Announcements</h5>
                        <p class="card-text h3">0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.js"></script>
    <script>
    
        const currentPage = window.location.pathname.split('/').pop();
        document.querySelectorAll('.sidebar-link').forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });

        document.querySelector('.navbar-toggler').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggler = document.querySelector('.navbar-toggler');
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggler.contains(event.target) &&
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>