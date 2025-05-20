<?php
require_once '../../db/connection.php';

$start_date = $_GET['start'] ?? date('Y-m-d', strtotime('-1 month'));
$end_date = $_GET['end'] ?? date('Y-m-d');

try {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="donations_report.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); 
    $headers = ['Date', 'Type', 'Amount', 'Donor', 'Notes'];
    fputcsv($output, $headers);
    
    $stmt = $conn->prepare("
        SELECT 
            donation_date,
            donation_type,
            amount,
            COALESCE(donor_name, CONCAT(m.first_name, ' ', m.last_name)) as donor,
            notes
        FROM donations d
        LEFT JOIN members m ON d.member_id = m.id
        WHERE donation_date BETWEEN ? AND ?
        ORDER BY donation_date DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['donation_date'],
            ucfirst($row['donation_type']),
            $row['amount'],
            $row['donor'],
            $row['notes']
        ]);
    }
    
    fputcsv($output, []); 
    fputcsv($output, ['Summary']);
    
    $stmt = $conn->prepare("
        SELECT 
            donation_type,
            COUNT(*) as count,
            SUM(amount) as total
        FROM donations
        WHERE donation_date BETWEEN ? AND ?
        GROUP BY donation_type
    ");
    $stmt->execute([$start_date, $end_date]);
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            ucfirst($row['donation_type']),
            $row['count'] . ' donations',
            $row['total'],
            '',
            ''
        ]);
    }
    
   
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_count,
            SUM(amount) as total_amount 
        FROM donations 
        WHERE donation_date BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);
    
    fputcsv($output, []); 
    fputcsv($output, [
        'Total',
        $totals['total_count'] . ' donations',
        $totals['total_amount'],
        '',
        ''
    ]);
    
    fclose($output);
} catch(Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
