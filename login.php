<?php
require_once __DIR__ . '/auth/session.php';
init_session();

if (isset($_GET['logout'])) {
    destroy_session();
    init_session();
    if (isset($_COOKIE['logged_in'])) {
        setcookie('logged_in', '', time() - 3600, '/');
    }
}

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mater Dolorosa</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-0 shadow-lg fade-in overflow-hidden" style="width: 100%; max-width: 900px;">
            <div class="row g-0">
               
                <div class="col-md-5 d-none d-md-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #9c27b0 0%, #6a1b9a 100%); position: relative; overflow: hidden;">
                    <div class="text-center p-4">
                        <div class="mb-3" style="position: relative; display: inline-block;">
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 250px; height: 250px; border-radius: 50%; background: rgba(255, 255, 255, 0.1);"></div>
                            <img src="assets/img/favicon.jpg" alt="Mater Dolorosa" class="rounded-circle" style="width: 200px; height: 200px; object-fit: cover; border: 8px solid rgba(255, 255, 255, 0.3); position: relative; z-index: 1;">
                        </div>
                        <h3 class="text-white mb-0">Mater Dolorosa Parish</h3>
                        <p class="text-white-50 mb-0">Archdiocese of Manila</p>
                    </div>
                </div>
                
             
                <div class="col-md-7">
                    <div class="p-4 p-md-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-church display-4 text-primary"></i>
                            <h3 class="mt-2">Welcome Back</h3>
                            <p class="text-muted">Sign in to your account</p>
                        </div>
                        
                        <div id="alert-container"></div>
                        <form id="loginForm" action="auth/login_user.php" method="POST">
                            <div class="mb-3">
                                <label for="login" class="form-label">Username or Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control" id="login" name="login" placeholder="Enter your username or email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right"></i> Sign In
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.bundle.js"></script>
    <script>
        $(document).ready(function() {
            function showAlert(type, message) {
                const alertClass = 'alert alert-' + type + ' alert-dismissible fade show';
                $('#alert-container').html(`
                    <div class="${alertClass}" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
            }

            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    type: 'POST',
                    url: 'auth/login_user.php',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = 'Dashboard_intro.php?page=dashboard';
                        } else {
                            showAlert('danger', response.message);
                        }
                    },
                    error: function() {
                        showAlert('danger', 'An error occurred. Please try again.');
                    }
                });
            });
        });
    </script>
</body>
</html>