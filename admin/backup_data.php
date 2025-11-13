<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';

// Handle export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'export') {
    try {
        // Get all tables
        $tables = [];
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

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
                            $values[] = "'" . $conn->quote(trim($value)) . "'";
                        }
                    }
                    $dump .= "INSERT INTO `$table` ($columnList) VALUES (" . implode(', ', $values) . ");\n";
                }
                $dump .= "\n";
            }
        }

        // Send as download
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="goldtree_backup_' . date('Y-m-d_H-i-s') . '.sql"');
        echo $dump;
        exit;
    } catch (Exception $e) {
        $error = "Export failed: " . $e->getMessage();
    }
}

// Handle import
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

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                // Skip comment lines
                $conn->exec($statement);
            }
        }

        $success = "Database imported successfully!";
    } catch (Exception $e) {
        $error = "Import failed: " . $e->getMessage();
    }
}

// Now include header (after handling POST requests)
require_once __DIR__ . '/../templates/admin_header.php';
?>

<div class="fade-in">
    <div class="mb-4">
        <h2 class="mb-3"><i class="bi bi-cloud-arrow-up-down"></i> Backup Data</h2>
        <p class="text-muted">Export your database or import a previously saved backup</p>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Export Section -->
        <div class="col-md-6">
            <div class="card admin-card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-cloud-download"></i> Export Data
                    </h5>
                    <p class="card-text text-muted">Download a complete backup of your database including all tables and data.</p>
                    <form method="POST" class="mt-4">
                        <input type="hidden" name="action" value="export">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-download"></i> Export Database
                        </button>
                    </form>
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted">
                            <strong>Includes:</strong><br>
                            • All database tables<br>
                            • Complete data from each table<br>
                            • Table structure and indexes<br>
                            <strong>Timestamp:</strong> Current date and time
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Section -->
        <div class="col-md-6">
            <div class="card admin-card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-cloud-upload"></i> Import Data
                    </h5>
                    <p class="card-text text-muted">Restore your database from a previously exported backup file.</p>
                    <form method="POST" enctype="multipart/form-data" class="mt-4">
                        <input type="hidden" name="action" value="import">
                        <div class="mb-3">
                            <label for="backup_file" class="form-label">Select Backup File</label>
                            <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".sql" required>
                            <small class="text-muted d-block mt-2">Accepted formats: .sql files only</small>
                        </div>
                        <button type="submit" class="btn btn-warning w-100" onclick="return confirm('⚠️ WARNING: This will replace all current data. Are you sure?')">
                            <i class="bi bi-upload"></i> Import Database
                        </button>
                    </form>
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted">
                            <strong>Note:</strong> Importing will replace all existing data.<br>
                            <strong>Requires:</strong> .sql backup file from export
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Section -->
    <div class="card mt-4">
        <div class="card-body">
            <h6 class="card-title"><i class="bi bi-info-circle"></i> Database Information</h6>
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted">Database Name</small>
                    <p class="mb-0"><strong><?php echo htmlspecialchars(DB_NAME); ?></strong></p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Host</small>
                    <p class="mb-0"><strong><?php echo htmlspecialchars(DB_HOST); ?></strong></p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Backup Time</small>
                    <p class="mb-0"><strong><?php echo date('Y-m-d H:i:s'); ?></strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

</main>
<script src="<?php echo BASE_PATH; ?>js/bootstrap.bundle.min.js"></script>
</body>
</html>
