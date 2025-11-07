<?php
require_once __DIR__ . '/../auth/session.php';
init_session();
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/theme.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5 text-center">
        <h1>500 - Server Error</h1>
        <p>Something went wrong on our end. Please try again later.</p>
        <a href="/" class="btn btn-primary">Return to Homepage</a>
    </div>
</body>
</html>