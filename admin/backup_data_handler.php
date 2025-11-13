<?php
// Start output buffering to catch any stray output
ob_start();

// Start session and set header first
session_start();
header('Content-Type: application/json');

// Require essentials
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';

// Verify admin status
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['admin_status'] != 1) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden - Admin access required']);
    exit;
}

// Helper function to ensure valid UTF-8
function ensureUtf8($str) {
    if (is_string($str)) {
        return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
    }
    return $str;
}

// Log the request for debugging
error_log('Handler called - Method: ' . $_SERVER['REQUEST_METHOD'] . ', POST action: ' . ($_POST['action'] ?? 'NOT SET'));
error_log('POST data: ' . json_encode($_POST));
error_log('REQUEST_CONTENT_TYPE: ' . ($_SERVER['CONTENT_TYPE'] ?? 'NOT SET'));

// Handle AJAX export request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'export') {
    try {
        error_log('Starting database export...');
        
        // Get all tables
        $tables = [];
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        error_log('Found ' . count($tables) . ' tables');

        // Build SQL dump
        $dump = "-- GoldTree Database Backup\n";
        $dump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $dump .= "-- Database: " . DB_NAME . "\n\n";

        foreach ($tables as $table) {
            // Get CREATE TABLE statement
            $createResult = $conn->query("SHOW CREATE TABLE `$table`");
            $createRow = $createResult->fetch(PDO::FETCH_NUM);
            $dump .= "\n-- Table: `$table`\n";
            $dump .= "DROP TABLE IF EXISTS `$table`;\n";
            $dump .= $createRow[1] . ";\n\n";

            // Get table data
            $dataResult = $conn->query("SELECT * FROM `$table`");
            $rows = $dataResult->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';

                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            // Ensure valid UTF-8
                            $cleanValue = ensureUtf8($value);
                            $values[] = "'" . str_replace("'", "''", $cleanValue) . "'";
                        }
                    }
                    $dump .= "INSERT INTO `$table` ($columnList) VALUES (" . implode(', ', $values) . ");\n";
                }
                $dump .= "\n";
            }
        }

        error_log('Dump size: ' . strlen($dump) . ' bytes');

        // Ensure the entire dump is valid UTF-8
        $dump = ensureUtf8($dump);
        
        $response = [
            'success' => true,
            'data' => $dump,
            'filename' => 'goldtree_backup_' . date('Y-m-d_H-i-s') . '.sql'
        ];
        
        $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        if ($json === false) {
            throw new Exception('JSON encoding failed: ' . json_last_error_msg());
        }
        
        header('Content-Length: ' . strlen($json));
        echo $json;
        error_log('Export response sent - JSON size: ' . strlen($json) . ' bytes');
        exit;
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Handle AJAX import request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import') {
    try {
        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No file uploaded or upload error occurred');
        }

        $file_content = file_get_contents($_FILES['backup_file']['tmp_name']);
        if ($file_content === false) {
            throw new Exception('Could not read uploaded file');
        }

        // Parse and execute SQL statements
        $statements = preg_split('/;(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $file_content);

        $executed = 0;
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                // Skip comment lines
                $conn->exec($statement);
                $executed++;
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "Database imported successfully! ($executed statements executed)"
        ]);
        exit;
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// No valid action
error_log('No valid action provided. POST: ' . json_encode($_POST));
http_response_code(400);
echo json_encode(['success' => false, 'error' => 'No valid action provided']);
?>
