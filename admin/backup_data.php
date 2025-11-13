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
    <!-- Header Section -->
    <div class="header-section mb-5">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="icon-circle">
                <i class="bi bi-cloud-arrow-up-down"></i>
            </div>
            <div>
                <h1 class="mb-1">Backup & Restore</h1>
                <p class="text-muted mb-0">Manage your database backups with export, import, and data management tools</p>
            </div>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" class="position-sticky" style="top: 20px; z-index: 1000;"></div>

    <!-- Main Operations Grid -->
    <div class="row g-4 mb-5">
        <!-- Export Section -->
        <div class="col-lg-4 col-md-6">
            <div class="card operation-card export-card h-100">
                <div class="card-header bg-success bg-opacity-10 border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="operation-icon bg-success text-white">
                            <i class="bi bi-download"></i>
                        </div>
                        <h5 class="mb-0">Export Database</h5>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted small mb-3">Download a complete backup of your database including all tables, data, and structure.</p>
                    <div class="features-list mb-4">
                        <div class="feature-item">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span>All database tables</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span>Complete data & structure</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span>Timestamped filename</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success w-100 py-2" id="exportBtn">
                        <i class="bi bi-cloud-download"></i> Start Export
                    </button>
                    <small class="d-block text-muted text-center mt-2">May take a few minutes for large databases</small>
                </div>
            </div>
        </div>

        <!-- Import Section -->
        <div class="col-lg-4 col-md-6">
            <div class="card operation-card import-card h-100">
                <div class="card-header bg-warning bg-opacity-10 border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="operation-icon bg-warning text-white">
                            <i class="bi bi-upload"></i>
                        </div>
                        <h5 class="mb-0">Import Database</h5>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted small mb-3">Restore your database from a previously exported backup file.</p>
                    <div class="mb-3">
                        <label for="backup_file" class="form-label fw-500">Select File</label>
                        <input type="file" class="form-control" id="backup_file" accept=".sql">
                        <small class="d-block text-muted mt-2">📄 Only .sql files accepted</small>
                    </div>
                    <button type="button" class="btn btn-warning w-100 py-2" id="importBtn" disabled>
                        <i class="bi bi-cloud-upload"></i> Start Import
                    </button>
                    <div class="alert alert-warning mt-3 py-2 mb-0" style="font-size: 0.85rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i> Replaces all existing data
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete All Data Section -->
        <div class="col-lg-4 col-md-6">
            <div class="card operation-card delete-card h-100 border-danger">
                <div class="card-header bg-danger bg-opacity-10 border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="operation-icon bg-danger text-white">
                            <i class="bi bi-trash"></i>
                        </div>
                        <h5 class="mb-0 text-danger">Delete All Data</h5>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted small mb-3">Permanently delete all records while preserving the root admin account.</p>
                    <div class="alert alert-danger py-2 mb-3" style="font-size: 0.85rem;">
                        <i class="bi bi-exclamation-circle-fill"></i> <strong>Irreversible action</strong>
                    </div>
                    <button type="button" class="btn btn-outline-danger w-100 py-2" id="deleteBtn">
                        <i class="bi bi-trash"></i> Delete All Data
                    </button>
                    <small class="d-block text-danger text-center mt-2">Only root account preserved</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Database Info Section -->
    <div class="row g-4 mb-5">
        <div class="col-12">
            <div class="card info-card">
                <div class="card-header border-bottom-0">
                    <h6 class="mb-0"><i class="bi bi-database"></i> Database Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="info-item">
                                <small class="text-muted d-block mb-1">Database Name</small>
                                <p class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars(DB_NAME); ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <small class="text-muted d-block mb-1">Host</small>
                                <p class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars(DB_HOST); ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <small class="text-muted d-block mb-1">Current Time</small>
                                <p class="mb-0 fw-bold text-dark"><?php echo date('Y-m-d H:i:s'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card help-card">
                <div class="card-header border-bottom-0">
                    <h6 class="mb-0"><i class="bi bi-question-circle"></i> Quick Guide</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="guide-item">
                                <div class="guide-number">1</div>
                                <h6>Export Regularly</h6>
                                <p class="small text-muted mb-0">Create backups before making major changes to ensure data safety.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="guide-item">
                                <div class="guide-number">2</div>
                                <h6>Verify Backups</h6>
                                <p class="small text-muted mb-0">Test your backups periodically by importing them to verify they work properly.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="guide-item">
                                <div class="guide-number">3</div>
                                <h6>Store Safely</h6>
                                <p class="small text-muted mb-0">Keep backup files in a secure location separate from the main database.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</main>
<script src="<?php echo BASE_PATH; ?>js/bootstrap.bundle.min.js"></script>
<style>
    /* General Styles */
    :root {
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --primary-bg: #f8f9fa;
        --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        --card-shadow-hover: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    body {
        background-color: var(--primary-bg);
    }

    .fade-in {
        animation: fadeIn 0.4s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { 
            opacity: 0;
            transform: translateY(10px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Header Section */
    .header-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2.5rem;
        border-radius: 12px;
        color: white;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.15);
    }

    .header-section h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .header-section .icon-circle {
        width: 60px;
        height: 60px;
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        flex-shrink: 0;
    }

    /* Operation Cards */
    .operation-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--card-shadow);
        display: flex;
        flex-direction: column;
    }

    .operation-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--card-shadow-hover);
    }

    .operation-card .card-header {
        padding: 1.25rem;
        background-color: transparent;
    }

    .operation-card h5 {
        font-weight: 700;
        font-size: 1.1rem;
        color: #222;
    }

    .operation-card .card-body {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    /* Operation Icons */
    .operation-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    /* Features List */
    .features-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.9rem;
        color: #666;
    }

    .feature-item i {
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    /* Buttons */
    .btn {
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.2s ease;
        border: none;
        padding: 0.625rem 1.25rem;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn:active {
        transform: translateY(0);
    }

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    .btn-success {
        background-color: var(--success-color);
    }

    .btn-success:hover {
        background-color: #218838;
    }

    .btn-warning {
        background-color: var(--warning-color);
        color: #222;
    }

    .btn-warning:hover {
        background-color: #e0a800;
        color: #222;
    }

    .btn-outline-danger {
        color: var(--danger-color);
        border-color: var(--danger-color);
        border-width: 2px;
    }

    .btn-outline-danger:hover {
        background-color: var(--danger-color);
        color: white;
        border-color: var(--danger-color);
    }

    /* File Input Styling */
    .form-control {
        border-radius: 8px;
        border: 2px solid #e0e0e0;
        padding: 0.65rem 0.875rem;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }

    /* Info Card */
    .info-card {
        border: none;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        background: white;
    }

    .info-card .card-header {
        background-color: transparent;
        border-bottom: 2px solid #f0f0f0;
        padding: 1.25rem;
    }

    .info-card h6 {
        font-weight: 700;
        font-size: 1rem;
        color: #222;
        margin: 0;
    }

    .info-item {
        padding: 0.75rem;
        background-color: #f8f9fa;
        border-radius: 8px;
    }

    /* Help Card */
    .help-card {
        border: none;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    .help-card .card-header {
        background-color: transparent;
        border-bottom: 2px solid rgba(255, 255, 255, 0.3);
        padding: 1.25rem;
    }

    .help-card h6 {
        font-weight: 700;
        font-size: 1rem;
        color: #222;
        margin: 0;
    }

    .guide-item {
        background-color: white;
        padding: 1.25rem;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        transition: all 0.2s ease;
        text-align: center;
    }

    .guide-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }

    .guide-number {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        margin: 0 auto 0.75rem;
    }

    .guide-item h6 {
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #222;
    }

    /* Alert Positioning */
    #alertContainer {
        margin-bottom: 1.5rem;
    }

    .alert {
        border-radius: 8px;
        border: none;
        padding: 1rem 1.25rem;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid var(--success-color);
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid var(--danger-color);
    }

    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        border-left: 4px solid var(--warning-color);
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .header-section {
            padding: 2rem;
        }

        .header-section h1 {
            font-size: 1.75rem;
        }

        .operation-card .card-header {
            padding: 1rem;
        }
    }

    @media (max-width: 768px) {
        .header-section {
            padding: 1.5rem;
        }

        .header-section h1 {
            font-size: 1.5rem;
        }

        .header-section .icon-circle {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }

        .operation-card:hover {
            transform: translateY(-3px);
        }

        .btn {
            padding: 0.55rem 1rem;
            font-size: 0.95rem;
        }

        .guide-item {
            text-align: left;
        }

        .guide-number {
            margin: 0 0 0.5rem 0;
        }
    }

    @media (max-width: 480px) {
        .header-section {
            padding: 1.25rem;
        }

        .header-section h1 {
            font-size: 1.25rem;
        }

        .d-flex.gap-3 {
            flex-direction: column;
        }

        .header-section .icon-circle {
            width: 45px;
            height: 45px;
            font-size: 1.25rem;
        }

        .operation-icon {
            width: 38px;
            height: 38px;
            font-size: 1.1rem;
        }

        .operation-card .card-header {
            padding: 0.875rem;
        }

        .operation-card .card-body {
            padding: 0.875rem;
        }

        .btn {
            padding: 0.5rem 0.875rem;
            font-size: 0.875rem;
        }

        .alert {
            font-size: 0.85rem;
            padding: 0.75rem 1rem;
        }

        .guide-item {
            padding: 1rem;
        }

        .row.g-4 {
            gap: 1rem !important;
        }
    }
</style>
<script>
// Initialize event listeners when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.getElementById('exportBtn');
    const importBtn = document.getElementById('importBtn');
    const deleteBtn = document.getElementById('deleteBtn');
    const fileInput = document.getElementById('backup_file');
    
    if (exportBtn) {
        exportBtn.addEventListener('click', exportDatabase);
    }
    if (importBtn) {
        importBtn.addEventListener('click', importDatabase);
    }
    if (deleteBtn) {
        deleteBtn.addEventListener('click', deleteAllData);
    }
    
    // Enable/disable import button based on file selection
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            importBtn.disabled = !this.files.length;
        });
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
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Exporting...';
    
    try {
        console.log('Starting export...');
        
        const formData = new FormData();
        formData.append('action', 'export');
        
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
        
        if (!response.ok) {
            const text = await response.text();
            console.error('Error response:', text);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const text = await response.text();
        
        if (!text) {
            throw new Error('Empty response from server');
        }
        
        const data = JSON.parse(text);
        
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
            
            showAlert(`<strong>Export Successful!</strong><br>File: ${data.filename}`, 'success');
        } else {
            showAlert('Export failed: ' + data.error, 'danger');
        }
    } catch (error) {
        if (error.name === 'AbortError') {
            console.error('Export timeout');
            showAlert('Export timed out after 10 minutes. Your database may be too large for export.', 'danger');
        } else {
            console.error('Export error:', error);
            showAlert('Export error: ' + error.message, 'danger');
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cloud-download"></i> Start Export';
    }
}

async function importDatabase() {
    const fileInput = document.getElementById('backup_file');
    const btn = document.getElementById('importBtn');
    const handlerUrl = getHandlerUrl();
    
    if (!fileInput.files.length) {
        showAlert('Please select a backup file to import', 'warning');
        return;
    }
    
    const fileName = fileInput.files[0].name;
    if (!confirm(`⚠️ WARNING: This will replace all current data with the contents of "${fileName}".\n\nThis action cannot be undone. Continue?`)) {
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Importing...';
    
    try {
        console.log('Starting import...');
        console.log('File selected:', fileInput.files[0].name, 'Size:', fileInput.files[0].size);
        
        const formData = new FormData();
        formData.append('action', 'import');
        formData.append('backup_file', fileInput.files[0]);
        
        // 10 minute timeout for import
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 600000);
        
        const response = await fetch(handlerUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            const text = await response.text();
            console.error('Error response:', text);
            throw new Error(`HTTP error! status: ${response.status} - ${text}`);
        }
        
        const text = await response.text();
        
        if (!text) {
            console.log('Empty response but HTTP 200 - import successful');
            showAlert('<strong>Import Successful!</strong><br>Database has been restored from backup.', 'success');
            fileInput.value = '';
            return;
        }
        
        const data = JSON.parse(text);
        
        if (data.success) {
            let message = `<strong>Import Successful!</strong><br>${data.message}`;
            if (data.errors && data.errors.length > 0) {
                message += '<br><strong style="margin-top: 0.5rem; display: block;">Errors encountered:</strong><br>' + data.errors.join('<br>');
            }
            showAlert(message, 'success');
            fileInput.value = '';
        } else {
            showAlert('Import failed: ' + data.error, 'danger');
        }
    } catch (error) {
        if (error.name === 'AbortError') {
            console.error('Import timeout');
            showAlert('Import timed out after 10 minutes. Your database may be too large.', 'danger');
        } else {
            console.error('Import error:', error);
            showAlert('Import error: ' + error.message, 'danger');
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cloud-upload"></i> Start Import';
    }
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const iconMap = {
        'success': 'check-circle-fill',
        'danger': 'exclamation-triangle-fill',
        'warning': 'exclamation-circle-fill',
        'info': 'info-circle-fill'
    };
    
    const icon = iconMap[type] || 'info-circle-fill';
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-${icon} flex-shrink-0 mt-1"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    `;
    alertContainer.innerHTML = alertHTML;
    
    // Auto-dismiss success alerts after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => {
                    alertContainer.innerHTML = '';
                }, 300);
            }
        }, 5000);
    }
}

async function deleteAllData() {
    const btn = document.getElementById('deleteBtn');
    const handlerUrl = getHandlerUrl();
    
    // Multiple confirmation steps for this dangerous action
    if (!confirm('⚠️ WARNING: This will DELETE ALL DATA except the root admin account.\n\nAre you absolutely sure you want to continue?')) {
        return;
    }
    
    if (!confirm('⚠️ FINAL WARNING: This action is IRREVERSIBLE!\n\nAll members, events, donations, and records will be permanently deleted. The root admin account will be preserved.\n\nClick OK only if you are absolutely certain.')) {
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Deleting...';
    
    try {
        console.log('Starting data deletion...');
        
        const formData = new FormData();
        formData.append('action', 'delete_all');
        
        // 5 minute timeout for deletion
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 300000);
        
        const response = await fetch(handlerUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            const text = await response.text();
            console.error('Error response:', text);
            throw new Error(`HTTP error! status: ${response.status} - ${text}`);
        }
        
        const text = await response.text();
        
        if (!text) {
            console.log('Empty response but HTTP 200 - deletion successful');
            showAlert('<strong>Deletion Successful!</strong><br>All data has been deleted. Root admin account preserved.', 'success');
            return;
        }
        
        const data = JSON.parse(text);
        
        if (data.success) {
            showAlert(`<strong>Deletion Successful!</strong><br>${data.message}`, 'success');
        } else {
            showAlert('Deletion failed: ' + data.error, 'danger');
        }
    } catch (error) {
        if (error.name === 'AbortError') {
            console.error('Deletion timeout');
            showAlert('Deletion timed out. Please try again.', 'danger');
        } else {
            console.error('Deletion error:', error);
            showAlert('Deletion error: ' + error.message, 'danger');
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-trash"></i> Delete All Data';
    }
}
</script>
</body>
</html>
