<?php
require_once 'db/connection.php';
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
    <title>Register - Mater Dolorosa</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0 fade-in">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="bi bi-church display-4 text-primary"></i>
                            <h3 class="mt-2">Create Account</h3>
                            <p class="text-muted">Join our church community</p>
                        </div>
                        
                        <?php if (!empty($registration_message)): ?>
                            <div class="alert alert-danger mb-3">
                                <?php echo htmlspecialchars($registration_message); ?>
                            </div>
                        <?php endif; ?>

                        <form id="registerForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-person"></i>
                                        </span>
                                        <input type="text" class="form-control" name="first_name" placeholder="John" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-person"></i>
                                        </span>
                                        <input type="text" class="form-control" name="last_name" placeholder="Doe" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control" name="email" placeholder="john.doe@example.com" required>
                                        <button type="button" class="btn btn-primary" id="sendVerificationBtn">
                                            <i class="bi bi-send"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Verification Code</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-shield-lock"></i>
                                        </span>
                                        <input type="text" class="form-control" name="verification_code" placeholder="Enter 6-digit code" maxlength="6" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-telephone"></i>
                                        </span>
                                        <input type="tel" class="form-control" name="phone" placeholder="123-456-7890">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-geo-alt"></i>
                                        </span>
                                        <textarea class="form-control" name="address" rows="2" placeholder="123 Main St, City, Country"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Birth Date</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-calendar"></i>
                                        </span>
                                        <input type="date" class="form-control" name="birthdate">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Membership Date</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-calendar-check"></i>
                                        </span>
                                        <input type="date" class="form-control" name="membership_date" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-person-plus"></i> Create Account
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="text-center mt-4">
                            <span class="text-muted">Already have an account?</span>
                            <a href="login.php" class="ms-1">Login here</a>
                        </div>
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
                $('.alert').remove();
                const alertClass = 'alert alert-' + type + ' mb-3';
                $('.text-center.mb-4').after(`<div class="${alertClass}">${message}</div>`);
            }

            $('#sendVerificationBtn').click(function() {
                const email = $('input[name="email"]').val();
                if (!email) {
                    showAlert('danger', 'Please enter your email address first.');
                    return;
                }

                $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');

                $.ajax({
                    type: 'POST',
                    url: 'auth/send_verification.php',
                    data: { email: email },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', 'Verification code sent! Please check your email.');
                        } else {
                            showAlert('danger', response.message || 'Error sending verification code.');
                        }
                    },
                    error: function() {
                        showAlert('danger', 'An error occurred. Please try again.');
                    },
                    complete: function() {
                        $('#sendVerificationBtn').prop('disabled', false).html('<i class="bi bi-send"></i>');
                    }
                });
            });

            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Creating Account...');

                $.ajax({
                    type: 'POST',
                    url: 'auth/register_user.php',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        showAlert(response.success ? 'success' : 'danger', response.message);

                        if (response.success) {
                            setTimeout(function() {
                                window.location.href = 'login.php';
                            }, 2000);
                        }
                    },
                    error: function() {
                        showAlert('danger', 'An error occurred. Please try again later.');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html('<i class="bi bi-person-plus"></i> Create Account');
                    }
                });
            });
        });
    </script>
</body>
</html>