<?php
session_start();
if (isset($_COOKIE['logged_in']) && $_COOKIE['logged_in'] === 'true') {
    header("Location: dashboard.php");
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
                <p class="text-muted">Enter your email to receive a reset link</p>
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
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-envelope"></i> Send Reset Link
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
                submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Sending...');

                $.ajax({
                    type: 'POST',
                    url: 'mailer/forgot_password.php',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        showAlert(response.success ? 'success' : 'danger', response.message);
                        if (response.success) {
                            $('#resetForm')[0].reset();
                        }
                    },
                    error: function() {
                        showAlert('danger', 'An error occurred. Please try again later.');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                });
            });
        });
    </script>
</body>
</html>