<?php
require_once '../auth/login_status.php';
require_once '../db/connection.php';
session_start();


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
?>

<?php require_once '../templates/admin_header.php'; ?>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">User Accounts</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus"></i> Add New User
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Member ID</th>
                                <th>Admin Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <!-- Users will be loaded here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Member ID (Optional)</label>
                        <select class="form-select" name="member_id" id="memberSelect">
                            <option value="">Select Member</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Admin Status</label>
                        <select class="form-select" name="admin_status">
                            <option value="0">Regular User</option>
                            <option value="1">Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveUserBtn">Save User</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Load users on page load
    document.addEventListener('DOMContentLoaded', () => {
        loadUsers();
        loadMemberOptions();
    });

    function loadUsers() {
        fetch('../crud/users/read_users.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('usersTableBody');
                tbody.innerHTML = '';
                
                data.forEach(user => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${user.id}</td>
                            <td>${user.username}</td>
                            <td>${user.email}</td>
                            <td>${user.member_id || '-'}</td>
                            <td>
                                <span class="badge ${user.admin_status === '1' ? 'bg-danger' : 'bg-secondary'}">
                                    ${user.admin_status === '1' ? 'Admin' : 'User'}
                                </span>
                            </td>
                            <td>${user.last_login || '-'}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editUser(${user.id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(error => console.error('Error:', error));
    }

    function loadMemberOptions() {
        fetch('../crud/members/read_members.php')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('memberSelect');
                data.forEach(member => {
                    const option = document.createElement('option');
                    option.value = member.id;
                    option.textContent = `${member.first_name} ${member.last_name}`;
                    select.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    }

    // Save new user
    document.getElementById('saveUserBtn').addEventListener('click', function() {
        const form = document.getElementById('addUserForm');
        const formData = new FormData(form);

        fetch('../auth/register_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadUsers();
                document.getElementById('addUserModal').querySelector('.btn-close').click();
                form.reset();
            } else {
                alert('Error adding user: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    function editUser(id) {
        // Implementation for editing user
        console.log('Edit user:', id);
    }

    function deleteUser(id) {
        if (confirm('Are you sure you want to delete this user?')) {
            fetch('../crud/users/delete_users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadUsers();
                } else {
                    alert('Error deleting user: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }
</script>

<?php require_once '../templates/admin_footer.php'; ?>