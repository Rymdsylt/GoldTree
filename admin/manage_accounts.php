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

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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
                            <option value="0">Staff</option>
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


<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" name="password" id="edit_password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Member ID (Optional)</label>
                        <select class="form-select" name="member_id" id="edit_member_id">
                            <option value="">Select Member</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Admin Status</label>
                        <select class="form-select" name="admin_status" id="edit_admin_status">
                            <option value="0">Staff</option>
                            <option value="1">Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateUserBtn">Update User</button>
            </div>
        </div>
    </div>
</div>

<script>

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
                    let userRole = 'Staff';
                    let badgeClass = 'bg-secondary';
                    
  
                    if (parseInt(user.admin_status) === 1) {
                        userRole = 'Admin';
                        badgeClass = 'bg-primary';
                    }

                    tbody.innerHTML += `
                        <tr>
                            <td>${user.id}</td>
                            <td>${user.username}</td>
                            <td>${user.email}</td>
                            <td>${user.member_id || '-'}</td>
                            <td><span class="badge ${badgeClass}">${userRole}</span></td>
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
        fetch('../crud/users/read_single_user.php?id=' + id)
            .then(response => response.json())
            .then(user => {
                document.getElementById('edit_id').value = user.id;
                document.getElementById('edit_username').value = user.username;
                document.getElementById('edit_email').value = user.email;
                document.getElementById('edit_member_id').value = user.member_id || '';
                document.getElementById('edit_admin_status').value = user.admin_status;
                document.getElementById('edit_password').value = '';
                
                const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
                editModal.show();
            })
            .catch(error => console.error('Error:', error));
    }

    document.getElementById('updateUserBtn').addEventListener('click', function() {
        const form = document.getElementById('editUserForm');
        const formData = new FormData(form);

        fetch('../crud/users/update_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadUsers();
                document.getElementById('editUserModal').querySelector('.btn-close').click();
                form.reset();
            } else {
                alert('Error updating user: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });

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