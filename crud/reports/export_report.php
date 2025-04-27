<?php
require_once '../../db/connection.php';

$type = $_GET['type'] ?? '';
$startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end'] ?? date('Y-m-d');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

try {
    switch($type) {
        case 'donations':
            fputcsv($output, ['Date', 'Member Name', 'Type', 'Amount', 'Notes']);
            
            $stmt = $conn->prepare("
                SELECT d.donation_date, CONCAT(m.first_name, ' ', m.last_name) as member_name,
                       d.donation_type, d.amount, d.notes
                FROM donations d
                LEFT JOIN members m ON d.member_id = m.id
                WHERE d.donation_date BETWEEN ? AND ?
                ORDER BY d.donation_date DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($output, [
                    date('Y-m-d', strtotime($row['donation_date'])),
                    $row['member_name'],
                    ucfirst($row['donation_type']),
                    number_format($row['amount'], 2),
                    $row['notes']
                ]);
            }
            break;
            
        case 'complete':
 
            fputcsv($output, ['Donation Summary']);
            fputcsv($output, ['Period:', date('Y-m-d', strtotime($startDate)) . ' to ' . date('Y-m-d', strtotime($endDate))]);
            fputcsv($output, []);
            
          
            fputcsv($output, ['Donations by Type']);
            fputcsv($output, ['Type', 'Total Amount']);
            
            $stmt = $conn->prepare("
                SELECT donation_type, SUM(amount) as total
                FROM donations
                WHERE donation_date BETWEEN ? AND ?
                GROUP BY donation_type
                ORDER BY donation_type
            ");
            $stmt->execute([$startDate, $endDate]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($output, [
                    ucfirst($row['donation_type']),
                    number_format($row['total'], 2)
                ]);
            }

            fputcsv($output, []);
            fputcsv($output, []);
            
        
            fputcsv($output, ['Detailed Transactions']);
            fputcsv($output, ['Date', 'Member Name', 'Type', 'Amount', 'Notes']);
            
            $stmt = $conn->prepare("
                SELECT d.donation_date, CONCAT(m.first_name, ' ', m.last_name) as member_name,
                       d.donation_type, d.amount, d.notes
                FROM donations d
                LEFT JOIN members m ON d.member_id = m.id
                WHERE d.donation_date BETWEEN ? AND ?
                ORDER BY d.donation_date DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($output, [
                    date('Y-m-d', strtotime($row['donation_date'])),
                    $row['member_name'],
                    ucfirst($row['donation_type']),
                    number_format($row['amount'], 2),
                    $row['notes']
                ]);
            }
            break;
    }
    
    fclose($output);
    
} catch(PDOException $e) {
    http_response_code(500);
    die("Database error: " . $e->getMessage());
}