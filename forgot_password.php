<?php
require_once 'auth/login_status.php';
session_start();
require_once __DIR__ . '/config.php';

if (isset($_COOKIE['logged_in']) && $_COOKIE['logged_in'] === 'true') {
    header("Location: " . base_path('dashboard.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Mater Dolorosa</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-lg fade-in" style="width: 100%; max-width: 400px;">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock display-4 text-primary"></i>
                <h3 class="mt-2">Reset Password</h3>
                <p class="text-muted">Enter your email to receive a verification code</p>
            </div>
            
            <div id="alert-container"></div>
            
            <form id="resetForm">
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div id="verification-section" style="display: none;">
                    <div class="mb-4">
                        <label for="verification_code" class="form-label">Verification Code</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-key"></i>
                            </span>
                            <input type="text" class="form-control" id="verification_code" name="verification_code" placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="new_password" class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                    <i class="bi bi-envelope"></i> Send Verification Code
                </button>
            </form>
            
            <div class="text-center mt-4">
                <a href="login.php" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>

    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.bundle.js"></script>
    <script>
        $(document).ready(function() {
            let verificationSent = false;
            
            function showAlert(type, message) {
                const alertClass = 'alert alert-' + type + ' alert-dismissible fade show';
                $('#alert-container').html(`
                    <div class="${alertClass}" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
            }

            $('#resetForm').on('submit', function(e) {
                e.preventDefault();
                const submitBtn = $(this).find('button[type="submit"]');
                const originalBtnText = submitBtn.html();
                submitBtn.prop('disabled', true);

                if (!verificationSent) {
                    submitBtn.html('<i class="bi bi-hourglass-split"></i> Sending...');
                    
                    $.ajax({
                        type: 'POST',
                        url: 'mailer/forgot_password.php',
                        data: { email: $('#email').val() },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                verificationSent = true;
                                $('#verification-section').slideDown();
                                $('#email').prop('readonly', true);
                                submitBtn.html('<i class="bi bi-check-lg"></i> Reset Password');
                                showAlert('success', response.message);
                            } else {
                                showAlert('danger', response.message);
                            }
                        },
                        error: function() {
                            showAlert('danger', 'An error occurred. Please try again later.');
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false);
                        }
                    });
                } else {
                    submitBtn.html('<i class="bi bi-hourglass-split"></i> Resetting...');
                    
                    $.ajax({
                        type: 'POST',
                        url: 'auth/reset_password.php',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showAlert('success', response.message);
                                setTimeout(function() {
                                    window.location.href = 'login.php';
                                }, 2000);
                            } else {
                                showAlert('danger', response.message);
                                submitBtn.prop('disabled', false).html(originalBtnText);
                            }
                        },
                        error: function() {
                            showAlert('danger', 'An error occurred. Please try again later.');
                            submitBtn.prop('disabled', false).html(originalBtnText);
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>