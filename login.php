<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-lg" style="width: 100%; max-width: 400px;">
            <h3 class="text-center mb-4">Login</h3>
            <div id="alert-container"></div>
            <form id="loginForm" action="auth/login_user.php" method="POST">
                <div class="mb-3">
                    <label for="login" class="form-label">Username or Email</label>
                    <input type="text" class="form-control" id="login" name="login" placeholder="Enter your username or email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="text-center mt-3">
                <a href="register.php">Don't have an account? Register</a>
            </div>
        </div>
    </div>
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.bundle.js"></script>
    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    type: 'POST',
                    url: 'auth/login_user.php',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = 'index.php';
                        } else {
                            showAlert('danger', response.message);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showAlert('danger', errorMessage);
                    }
                });
            });

            function showAlert(type, message) {
                $('#alert-container').html(
                    '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>'
                );
            }
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('error')) {
                const error = urlParams.get('error');
                let message = '';
                switch (error) {
                    case 'empty_fields':
                        message = 'Please fill in all fields.';
                        break;
                    case 'invalid_credentials':
                        message = 'Invalid username or password.';
                        break;
                    case 'database_error':
                        let dbError = urlParams.get('db_message');
                        message = dbError || 'Database connection failed.';
                        break;
                }
                if (message) {
                    showAlert('danger', message);
                }
            }
        });
    </script>
</body>
</html>