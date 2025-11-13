<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../templates/admin_header.php';
?>

<div class="fade-in">
    <div class="mb-4">
        <h2 class="mb-3"><i class="bi bi-cloud-arrow-up-down"></i> Backup Data</h2>
        <p class="text-muted">Export your database or import a previously saved backup</p>
    </div>

    <div id="alertContainer"></div>

    <div class="row g-4">
        <!-- Export Section -->
        <div class="col-md-6">
            <div class="card admin-card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-cloud-download"></i> Export Data
                    </h5>
                    <p class="card-text text-muted">Download a complete backup of your database including all tables and data.</p>
                    <div class="mt-4">
                        <button type="button" class="btn btn-success w-100" id="exportBtn" onclick="exportDatabase()" data-handler="<?php echo BASE_PATH; ?>admin/backup_data_handler.php">
                            <i class="bi bi-download"></i> Export Database
                        </button>
                    </div>
                    </div>
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
                    <div class="mt-4">
                        <div class="mb-3">
                            <label for="backup_file" class="form-label">Select Backup File</label>
                            <input type="file" class="form-control" id="backup_file" accept=".sql">
                            <small class="text-muted d-block mt-2">Accepted formats: .sql files only</small>
                        </div>
                        <button type="button" class="btn btn-warning w-100" id="importBtn" onclick="importDatabase()" data-handler="<?php echo BASE_PATH; ?>admin/backup_data_handler.php">
                            <i class="bi bi-upload"></i> Import Database
                        </button>
                    </div>
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

<script>
async function exportDatabase() {
    const btn = document.getElementById('exportBtn');
    const handlerUrl = btn.getAttribute('data-handler');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Exporting...';
    
    try {
        console.log('Starting export...');
        const formData = new FormData();
        formData.append('action', 'export');
        
        const response = await fetch(handlerUrl, {
            method: 'POST',
            body: formData
        });
        
        console.log('Response status:', response.status);
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            // Create blob and download
            const blob = new Blob([data.data], { type: 'application/sql' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = data.filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            showAlert('Database exported successfully!', 'success');
        } else {
            showAlert('Export failed: ' + data.error, 'danger');
        }
    } catch (error) {
        console.error('Export error:', error);
        showAlert('Export error: ' + error.message, 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-download"></i> Export Database';
    }
}

async function importDatabase() {
    const fileInput = document.getElementById('backup_file');
    const btn = document.getElementById('importBtn');
    const handlerUrl = btn.getAttribute('data-handler');
    
    if (!fileInput.files.length) {
        showAlert('Please select a backup file', 'warning');
        return;
    }
    
    if (!confirm('⚠️ WARNING: This will replace all current data. Are you sure?')) {
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Importing...';
    
    try {
        console.log('Starting import...');
        const formData = new FormData();
        formData.append('action', 'import');
        formData.append('backup_file', fileInput.files[0]);
        
        const response = await fetch(handlerUrl, {
            method: 'POST',
            body: formData
        });
        
        console.log('Response status:', response.status);
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            showAlert(data.message, 'success');
            fileInput.value = '';
        } else {
            showAlert('Import failed: ' + data.error, 'danger');
        }
    } catch (error) {
        console.error('Import error:', error);
        showAlert('Import error: ' + error.message, 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-upload"></i> Import Database';
    }
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    alertContainer.innerHTML = alertHTML;
}
