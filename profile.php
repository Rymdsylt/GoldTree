<?php
require_once 'templates/header.php';
require_once 'auth/login_status.php';

require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_path('login.php'));
    exit();
}


$stmt = $conn->prepare("
    SELECT u.*, m.*, au.email as user_email 
    FROM users u 
    LEFT JOIN members m ON u.member_id = m.id 
    LEFT JOIN users au ON m.user_id = au.id 
    WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userData = $stmt->fetch();

$displayEmail = $userData['user_email'] ?? $userData['email'] ?? '';
$isAdminUser = ($userData['username'] === 'root' || $userData['email'] === 'admin@materdolorosa.com');
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">My Profile</h4>
                    
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <?php if (!empty($userData['profile_image'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($userData['profile_image']); ?>" 
                                     class="rounded-circle img-thumbnail" style="width: 200px; height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto" 
                                     style="width: 200px; height: 200px;">
                                    <i class="bi bi-person" style="font-size: 5rem;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <h5 class="mt-3"><?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?></h5>
                            <p class="text-muted">
                                <?php echo htmlspecialchars($userData['category'] ?: 'Member'); ?>
                                <span class="badge bg-<?php echo $userData['status'] === 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($userData['status'] ?: 'Active'); ?>
                                </span>
                            </p>
                        </div>
                        
                        <div class="col-md-8">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#profile">Profile Information</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#security">Security Settings</a>
                                </li>
                            </ul>
                            
                            <div class="tab-content mt-3">
                                <div class="tab-pane fade show active" id="profile">
                                    <form id="profileForm">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Username</label>
                                                <input type="text" class="form-control" name="username" id="username" 
                                                       value="<?php echo htmlspecialchars($userData['username']); ?>" 
                                                       <?php echo $isAdminUser ? 'readonly disabled' : 'required'; ?>>
                                                <?php if ($isAdminUser): ?>
                                                    <small class="text-muted">Admin username cannot be changed</small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">First Name</label>
                                                <input type="text" class="form-control" name="first_name" 
                                                       value="<?php echo htmlspecialchars($userData['first_name']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" class="form-control" name="last_name" 
                                                       value="<?php echo htmlspecialchars($userData['last_name']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="email" id="email"
                                                       value="<?php echo htmlspecialchars($displayEmail); ?>" 
                                                       <?php echo $isAdminUser ? 'readonly disabled' : 'required'; ?>>
                                                <?php if ($isAdminUser): ?>
                                                    <small class="text-muted">Admin email cannot be changed</small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" class="form-control" name="phone" 
                                                       value="<?php echo htmlspecialchars($userData['phone']); ?>">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Address</label>
                                                <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($userData['address']); ?></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Profile Image</label>
                                                <input type="file" class="form-control" name="profile_image" accept="image/*">
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-save"></i> Save Changes
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                                <div class="tab-pane fade" id="security">
                                    <form id="passwordForm">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Current Password or "Reset Password" Given by the Admin</label>
                                                <input type="password" class="form-control" name="current_password" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">New Password</label>
                                                <input type="password" class="form-control" name="new_password" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Confirm New Password</label>
                                                <input type="password" class="form-control" name="confirm_password" required>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-key"></i> Change Password
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showAlert(type, message, form) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    

    form.querySelectorAll('.alert').forEach(alert => alert.remove());
    form.insertAdjacentElement('beforebegin', alertDiv);

    if (type === 'success') {
        setTimeout(() => alertDiv.remove(), 3000);
    }
}

document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
    
    const formData = new FormData(this);
    
    $.ajax({
        url: 'crud/users/update_profile.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message, form);
        
                if (formData.get('profile_image').size > 0) {
                    setTimeout(() => location.reload(), 1500);
                }
            } else {
                showAlert('danger', response.message || 'Error updating profile', form);
            }
        },
        error: function(xhr) {
            let errorMessage = 'An error occurred while updating profile';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch(e) {}
            showAlert('danger', errorMessage, form);
        },
        complete: function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
});

document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    const formData = new FormData(this);
    
    if (formData.get('new_password') !== formData.get('confirm_password')) {
        showAlert('danger', 'New passwords do not match', form);
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Updating...';
    
    $.ajax({
        url: 'crud/users/change_password.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message, form);
                form.reset();
            } else {
                showAlert('danger', response.message || 'Error changing password', form);
            }
        },
        error: function(xhr) {
            let errorMessage = 'An error occurred while changing password';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch(e) {}
            showAlert('danger', errorMessage, form);
        },
        complete: function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
});


document.getElementById('associated_user').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const emailField = document.getElementById('email');
    
    if (selectedOption.value) {
 
        emailField.readOnly = false;
        emailField.classList.remove('bg-light');
    }
});


document.addEventListener('DOMContentLoaded', function() {
    const emailField = document.getElementById('email');
    emailField.readOnly = false;
    emailField.classList.remove('bg-light');
});

</script>

<?php require_once 'templates/footer.php'; ?>