<?php
require_once 'db/connection.php';

$now = date('Y-m-d H:i:s');

$upcomingStmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE start_datetime > ? AND status = 'upcoming'");
$upcomingStmt->execute([$now]);
$upcomingCount = $upcomingStmt->fetch()['count'];

$ongoingStmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE start_datetime <= ? AND end_datetime >= ? AND status = 'ongoing'");
$ongoingStmt->execute([$now, $now]);
$ongoingCount = $ongoingStmt->fetch()['count'];


$totalStmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE status != 'cancelled'");
$totalStmt->execute();
$totalCount = $totalStmt->fetch()['count'];


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Count Test</title>
    <link rel="icon" href="/GoldTree/assets/img/logo.php?v=1" type="image/png">
    <link rel="shortcut icon" href="/GoldTree/assets/img/logo.php?v=1" type="image/png">
    <link rel="apple-touch-icon" sizes="180x180" href="/GoldTree/assets/img/logo.php?v=1">
    <link rel="icon" type="image/png" sizes="32x32" href="/GoldTree/assets/img/logo.php?v=1">
    <link rel="icon" type="image/png" sizes="16x16" href="/GoldTree/assets/img/logo.php?v=1">
    <link rel="icon" href="/GoldTree/assets/img/logo.php?v=1" type="image/x-icon">
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h2>Event Count Test Results</h2>
        <table class="table table-bordered mt-4">
            <thead class="table-light">
                <tr>
                    <th>Count Type</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Upcoming Events</td>
                    <td><?php echo $upcomingCount; ?></td>
                </tr>
                <tr>
                    <td>Ongoing Events</td>
                    <td><?php echo $ongoingCount; ?></td>
                </tr>
                <tr>
                    <td>Total Non-cancelled Events</td>
                    <td><?php echo $totalCount; ?></td>
                </tr>
            </tbody>
        </table>
        
        <div class="mt-4">
            <p><strong>Current Server Time:</strong> <?php echo $now; ?></p>
        </div>
    </div>
</body>
</html>