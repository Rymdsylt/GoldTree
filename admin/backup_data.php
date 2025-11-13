<?php
session_start();
require_once '../auth/login_status.php';
require_once '../db/connection.php';

if (!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['admin_status'] != 1) {
    header("Location: ../dashboard.php");
    exit();
}

require_once '../config.php';
require_once '../templates/admin_header.php';
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
                        <button type="button" class="btn btn-success w-100" id="exportBtn">
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
                        <button type="button" class="btn btn-warning w-100" id="importBtn">
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

</main>
<script src="<?php echo BASE_PATH; ?>js/bootstrap.bundle.min.js"></script>
<style>
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .admin-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .admin-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }

    .btn {
        font-weight: 500;
        border-radius: 6px;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .card-body {
        padding: 1.5rem;
    }

    .card-title {
        margin-bottom: 0.75rem;
        font-weight: 600;
        color: #333;
    }

    .card-text {
        font-size: 0.95rem;
        line-height: 1.5;
    }

    @media (max-width: 768px) {
        .row.g-4 {
            gap: 1rem !important;
        }

        .card-body {
            padding: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .mb-4 {
            margin-bottom: 1.5rem !important;
        }

        h2 {
            font-size: 1.5rem;
        }

        .card {
            margin-bottom: 1rem;
        }

        .col-md-6 {
            margin-bottom: 0.5rem;
        }
    }

    @media (max-width: 480px) {
        h2 {
            font-size: 1.25rem;
        }

        .card-title {
            font-size: 1rem;
        }

        .btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }

        .alert {
            padding: 0.75rem;
            font-size: 0.9rem;
        }

        .row {
            gap: 0.75rem !important;
        }
    }
</style>
<script>
// Initialize event listeners when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.getElementById('exportBtn');
    const importBtn = document.getElementById('importBtn');
    
    if (exportBtn) {
        exportBtn.addEventListener('click', exportDatabase);
    }
    if (importBtn) {
        importBtn.addEventListener('click', importDatabase);
    }
});

// Get the handler URL - construct from current page location
function getHandlerUrl() {
    // Use relative path from current directory
    return './backup_data_handler.php';
}

async function exportDatabase() {
    const btn = document.getElementById('exportBtn');
    const handlerUrl = getHandlerUrl();
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Exporting...';
    
    try {
        console.log('Starting export...');
        console.log('Handler URL:', handlerUrl);
        
        const formData = new FormData();
        formData.append('action', 'export');
        
        console.log('FormData action:', formData.get('action'));
        
        // 10 minute timeout with AbortController
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 600000);
        
        const response = await fetch(handlerUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        console.log('Response status:', response.status);
        console.log('Response headers:', {
            contentType: response.headers.get('content-type'),
            contentLength: response.headers.get('content-length')
        });
        
        if (!response.ok) {
            const text = await response.text();
            console.error('Error response:', text);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const text = await response.text();
        console.log('Response text length:', text.length);
        console.log('Response text (first 300 chars):', text.substring(0, 300));
        
        if (!text) {
            throw new Error('Empty response from server');
        }
        
        const data = JSON.parse(text);
        console.log('Response data keys:', Object.keys(data));
        
        if (data.success) {
            // Create blob and download
            const blob = new Blob([data.data], { type: 'text/plain;charset=utf-8' });
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
        if (error.name === 'AbortError') {
            console.error('Export timeout: Database export exceeded 10 minute limit');
            showAlert('Export timed out after 10 minutes. Database may be too large.', 'danger');
        } else {
            console.error('Export error:', error);
            showAlert('Export error: ' + error.message, 'danger');
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-download"></i> Export Database';
    }
}

async function importDatabase() {
    const fileInput = document.getElementById('backup_file');
    const btn = document.getElementById('importBtn');
    const handlerUrl = getHandlerUrl();
    
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
        console.log('Handler URL:', handlerUrl);
        const formData = new FormData();
        formData.append('action', 'import');
        formData.append('backup_file', fileInput.files[0]);
        
        const response = await fetch(handlerUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            const text = await response.text();
            console.log('Error response:', text);
            throw new Error(`HTTP error! status: ${response.status} - ${text}`);
        }
        
        const text = await response.text();
        console.log('Response text length:', text.length);
        console.log('Response text:', text);
        
        if (!text) {
            throw new Error('Empty response from server');
        }
        
        const data = JSON.parse(text);
        console.log('Response data keys:', Object.keys(data));
        
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
</script>
</body>
</html>
