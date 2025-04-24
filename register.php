<?php
require_once 'db/connection.php';
require_once 'auth/login_status.php';
session_start();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-primary {
            background-color: #2d5a3f !important;
            border-color: #2d5a3f !important;
            color: white !important;
        }

        .btn-primary:hover {
            background-color: #1a472a !important;
            border-color: #1a472a !important;
        }

        .btn-primary:focus {
            box-shadow: 0 0 0 0.25rem rgba(45, 90, 63, 0.25) !important;
        }

        a {
            color: #2d5a3f !important;
        }

        a:hover {
            color: #1a472a !important;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Register Account</h3>
                        <?php if (!empty($registration_message)): ?>
                            <div class="alert alert-danger mb-3">
                                <?php echo htmlspecialchars($registration_message); ?>
                            </div>
                        <?php endif; ?>
                        <form id="registerForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="John" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Doe" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="john.doe@example.com" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="123-456-7890">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2" placeholder="123 Main St, City, Country"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="birthdate" class="form-label">Birth Date</label>
                                    <input type="date" class="form-control" id="birthdate" name="birthdate">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="membership_date" class="form-label">Membership Date</label>
                                    <input type="date" class="form-control" id="membership_date" name="membership_date" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary shadow-sm" name="register">Register</button>
                            </div>
                            <p class="text-center mt-3">
                                Already have an account? <a href="login.php">Login here</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.js"></script>
    <script>
        $(document).ready(function() {
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                
    
                $('button[type="submit"]').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
                );

                $.ajax({
                    url: 'auth/register_user.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
      
                        $('.alert').remove();
     
                        var alertClass = response.status === 'success' ? 'alert-success' : 'alert-danger';
                        var alertHtml = '<div class="alert ' + alertClass + ' mb-3">' + response.message + '</div>';
                        $('.card-title').after(alertHtml);

                        if (response.status === 'success') {
                            $('#registerForm')[0].reset();
                  
                            setTimeout(function() {
                                window.location.href = 'login.php';
                            }, 2000);
                        }
                    },
                    error: function() {
                        $('.alert').remove();
                        $('.card-title').after(
                            '<div class="alert alert-danger mb-3">An error occurred. Please try again later.</div>'
                        );
                    },
                    complete: function() {
                    
                        $('button[type="submit"]').prop('disabled', false).html('Register');
                    }
                });
            });
        });
    </script>
</body>
</html>