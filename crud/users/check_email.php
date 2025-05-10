<?php
require_once '../../db/connection.php';
header('Content-Type: application/json');

try {
    $email = $_GET['email'] ?? '';
    $excludeId = $_GET['exclude_id'] ?? null;

    if (empty($email)) {
        throw new Exception('Email is required');
    }


    $query = "SELECT COUNT(*) as count FROM (
        SELECT email FROM users WHERE email = :email
        UNION
        SELECT email FROM members WHERE email = :email
    ) as combined";


    $params = ['email' => $email];
    if ($excludeId) {
        $query = "SELECT COUNT(*) as count FROM (
            SELECT email FROM users WHERE email = :email AND id != :exclude_id
            UNION
            SELECT email FROM members WHERE email = :email AND user_id != :exclude_id
        ) as combined";
        $params['exclude_id'] = $excludeId;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'exists' => $result['count'] > 0
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
