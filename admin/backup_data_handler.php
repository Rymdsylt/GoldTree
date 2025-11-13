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
        set_time_limit(600); // 10 minute timeout for export
        
        // Get all tables
        $tables = [];
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        error_log('Found ' . count($tables) . ' tables');

        // Build SQL dump more efficiently
        $dump = "-- GoldTree Database Backup\n";
        $dump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $dump .= "-- Database: " . DB_NAME . "\n";
        $dump .= "-- Tables: " . count($tables) . "\n\n";

        foreach ($tables as $index => $table) {
            error_log('Exporting table ' . ($index + 1) . '/' . count($tables) . ': ' . $table);
            
            // Get CREATE TABLE statement
            $createResult = $conn->query("SHOW CREATE TABLE `$table`");
            $createRow = $createResult->fetch(PDO::FETCH_NUM);
            $dump .= "\n-- Table: `$table`\n";
            $dump .= "DROP TABLE IF EXISTS `$table`;\n";
            $dump .= $createRow[1] . ";\n\n";

            // Get table data in chunks if very large
            $dataResult = $conn->query("SELECT COUNT(*) as cnt FROM `$table`");
            $countRow = $dataResult->fetch(PDO::FETCH_ASSOC);
            $rowCount = $countRow['cnt'];
            
            if ($rowCount > 0) {
                error_log('  - Exporting ' . $rowCount . ' rows from ' . $table);
                
                // Fetch in chunks for very large tables
                $chunkSize = 1000;
                $offset = 0;
                
                while ($offset < $rowCount) {
                    $dataResult = $conn->query("SELECT * FROM `$table` LIMIT " . $chunkSize . " OFFSET " . $offset);
                    $rows = $dataResult->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (empty($rows)) break;
                    
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
                    
                    $offset += $chunkSize;
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

        error_log('Import file size: ' . strlen($file_content) . ' bytes');

        // Split by semicolon but respect quoted strings
        // Remove comments first
        $lines = explode("\n", $file_content);
        $cleanedContent = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Skip comment lines and empty lines
            if (empty($trimmed) || strpos($trimmed, '--') === 0) {
                continue;
            }
            $cleanedContent .= $line . "\n";
        }

        // Now split statements by semicolon
        $statements = array_filter(array_map('trim', explode(';', $cleanedContent)));

        error_log('Found ' . count($statements) . ' SQL statements to execute');

        $executed = 0;
        $errors = [];
        
        foreach ($statements as $index => $statement) {
            if (empty($statement)) {
                continue;
            }
            
            try {
                error_log('Executing statement ' . ($index + 1) . ': ' . substr($statement, 0, 100) . '...');
                $conn->exec($statement);
                $executed++;
            } catch (Exception $e) {
                $errors[] = 'Statement ' . ($index + 1) . ': ' . $e->getMessage();
                error_log('Error executing statement ' . ($index + 1) . ': ' . $e->getMessage());
            }
        }

        if ($executed === 0 && count($statements) > 0) {
            throw new Exception('No statements were executed. File may be invalid or empty.');
        }

        $message = "Database imported successfully! ($executed statements executed)";
        if (!empty($errors)) {
            $message .= " with " . count($errors) . " errors";
            error_log('Import errors: ' . json_encode($errors));
        }

        echo json_encode([
            'success' => true,
            'message' => $message,
            'executed' => $executed,
            'errors' => $errors
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
