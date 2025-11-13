<?php
// Disable error display, log instead
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Start session FIRST before any output
session_start();

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// Require configs
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

// Check admin status
$stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['admin_status'] != 1) {
    http_response_code(403);
    exit(json_encode(['success' => false, 'error' => 'Forbidden - Admin access required']));
}

// Helper function to ensure valid UTF-8
function ensureUtf8($str) {
    if (is_string($str)) {
        return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
    }
    return $str;
}

// Log the request
error_log('Handler called - Method: ' . $_SERVER['REQUEST_METHOD'] . ', Action: ' . ($_POST['action'] ?? 'NONE'));

// Route the request
$action = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'error' => 'Method not allowed']));
}

if ($action === 'export') {
    handleExport();
} elseif ($action === 'import') {
    handleImport();
} else {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Invalid action']));
}

/**
 * Handle database export
 */
function handleExport() {
    global $conn;
    
    try {
        error_log('Starting database export...');
        set_time_limit(600);
        
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
            
            // Get row count
            $dataResult = $conn->query("SELECT COUNT(*) as cnt FROM `$table`");
            $countRow = $dataResult->fetch(PDO::FETCH_ASSOC);
            $rowCount = $countRow['cnt'];
            
            if ($rowCount > 0) {
                error_log('  - Exporting ' . $rowCount . ' rows from ' . $table);
                
                // Fetch in chunks
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
                                $cleanValue = ensureUtf8($value);
                                $values[] = "'" . str_replace("'", "''", $cleanValue) . "'";
                            }
                        }
                        $dump .= "INSERT INTO `$table` ($columnList) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    
                    $offset += $chunkSize;
                }
            }
        }
        
        error_log('Dump size: ' . strlen($dump) . ' bytes');
        $dump = ensureUtf8($dump);
        
        $response = [
            'success' => true,
            'data' => $dump,
            'filename' => 'goldtree_backup_' . date('Y-m-d_H-i-s') . '.sql'
        ];
        
        error_log('Export: Building JSON response...');
        $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        error_log('Export: JSON size: ' . strlen($json) . ' bytes');
        
        header('Content-Length: ' . strlen($json));
        exit($json);
        
    } catch (Throwable $e) {
        error_log('Export error: ' . $e->getMessage());
        http_response_code(400);
        exit(json_encode(['success' => false, 'error' => 'Export error: ' . $e->getMessage()]));
    }
}

/**
 * Handle database import
 */
function handleImport() {
    global $conn;
    
    try {
        error_log('Starting database import...');
        
        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No file uploaded or upload error');
        }
        
        $file_content = file_get_contents($_FILES['backup_file']['tmp_name']);
        if ($file_content === false) {
            throw new Exception('Could not read uploaded file');
        }
        
        error_log('Import: File size: ' . strlen($file_content) . ' bytes');
        
        // Simple and robust SQL parsing using explode by lines
        $lines = explode("\n", $file_content);
        $statements = [];
        $current = '';
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            // Skip empty lines and comments
            if (empty($trimmed) || substr($trimmed, 0, 2) === '--') {
                continue;
            }
            
            $current .= $line . "\n";
            
            // Check if line ends with semicolon
            if (substr($trimmed, -1) === ';') {
                $stmt = trim(str_replace("\n", ' ', $current));
                if (!empty($stmt)) {
                    $statements[] = $stmt;
                }
                $current = '';
            }
        }
        
        error_log('Import: Found ' . count($statements) . ' statements');
        
        // Separate DROP and other statements
        $drop_stmts = [];
        $other_stmts = [];
        
        foreach ($statements as $stmt) {
            // Trim and normalize whitespace
            $stmt = preg_replace('/\s+/', ' ', $stmt);
            if (stripos(trim($stmt), 'DROP TABLE') === 0) {
                $drop_stmts[] = $stmt;
            } else {
                $other_stmts[] = $stmt;
            }
        }
        
        // Execute drops first
        $all_stmts = array_merge($drop_stmts, $other_stmts);
        error_log('Import: Executing ' . count($drop_stmts) . ' DROP statements and ' . count($other_stmts) . ' other statements');
        
        $executed = 0;
        $errors = [];
        
        foreach ($all_stmts as $index => $statement) {
            if (empty(trim($statement))) {
                continue;
            }
            
            try {
                error_log('Import: Statement ' . ($index + 1) . ' - ' . substr($statement, 0, 100));
                $result = $conn->exec($statement);
                $executed++;
            } catch (Throwable $e) {
                $msg = $e->getMessage();
                error_log('Import: Error on statement ' . ($index + 1) . ': ' . $msg);
                $errors[] = $msg;
            }
        }
        
        error_log('Import: Completed - ' . $executed . ' executed, ' . count($errors) . ' errors');
        
        $message = "Database imported successfully! ($executed statements executed)";
        if (!empty($errors)) {
            $message .= " with " . count($errors) . " errors";
        }
        
        $response = [
            'success' => true,
            'message' => $message,
            'executed' => $executed,
            'errors' => $errors
        ];
        
        $json = json_encode($response);
        header('Content-Length: ' . strlen($json));
        exit($json);
        
    } catch (Throwable $e) {
        error_log('Import error: ' . $e->getMessage());
        http_response_code(400);
        exit(json_encode(['success' => false, 'error' => 'Import error: ' . $e->getMessage()]));
    }
}
?>
