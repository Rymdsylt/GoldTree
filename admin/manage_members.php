<?php 
session_start();
require_once '../templates/admin_header.php'; 
require_once '../db/connection.php';


$query = "SELECT * FROM members ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);


$totalMembers = count($members);
$activeMembers = 0;
$newMembers = 0;
$currentMonth = date('Y-m');

foreach($members as $member) {
    if($member['status'] === 'active') {
        $activeMembers++;
    }
    if(date('Y-m', strtotime($member['created_at'])) === $currentMonth) {
        $newMembers++;
    }
}

?>

<style>
    @media (max-width: 768px) {
        .stat-card {
            margin-bottom: 1rem;
        }
        .chart-container {
            height: 300px !important;
        }
        .table-responsive {
            margin-bottom: 1rem;
        }
        .member-actions .btn-group {
            display: flex;
            width: 100%;
        }
        .member-actions .btn {
            flex: 1;
        }
    }
    @media (max-width: 576px) {
        .member-info {
            flex-direction: column !important;
            text-align: center;
        }
        .member-info .profile-image {
            margin-bottom: 0.5rem;
        }
        .member-contact > div {
            display: block;
            margin-bottom: 0.25rem;
        }
    }
</style>

<div class="container-fluid px-4">
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Members</h6>
                    <h2 class="card-title mb-0"><?php echo $totalMembers; ?></h2>
                    <small>All time</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Active Members</h6>
                    <h2 class="card-title mb-0"><?php echo $activeMembers; ?></h2>
                    <small>Currently active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">New Members</h6>
                    <h2 class="card-title mb-0"><?php echo $newMembers; ?></h2>
                    <small>This month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Regular Attendance</h6>
                    <h2 class="card-title mb-0"><?php echo "0"; ?>%</h2>
                    <small>Past 3 months</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-body chart-container" style="height: 400px;">
                    <h5 class="card-title">Member Growth</h5>
                    <canvas id="memberGrowthChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card">
                <div class="card-body chart-container" style="height: 400px;">
                    <h5 class="card-title">Member Categories</h5>
                    <canvas id="memberCategoriesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-sm-4">
                            <input type="text" class="form-control" id="searchMembers" placeholder="Search members...">
                        </div>
                        <div class="col-12 col-sm-4">
                            <input type="text" class="form-control" id="ministryFilter" placeholder="Filter by ministry type...">
                        </div>
                        <div class="col-12 col-sm-4">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card">
                <div class="card-body d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                        <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Add Member</span>
                    </button>
                    <button class="btn btn-outline-primary" id="exportBtn">
                        <i class="bi bi-download"></i> <span class="d-none d-sm-inline">Export</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Contact</th>
                            <th>Ministry Type</th>
                            <th>Status</th>
                            <th>Last Attendance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($members as $member): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center member-info">
                                    <?php if(!empty($member['profile_image'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($member['profile_image']); ?>" 
                                             class="rounded-circle me-2 profile-image" style="width: 40px; height: 40px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2 profile-image" 
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></div>
                                        <small class="text-muted">Member since <?php echo date('M Y', strtotime($member['membership_date'])); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="member-contact">
                                    <?php if($member['email']): ?>
                                        <div><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($member['email']); ?></div>
                                    <?php endif; ?>
                                    <?php if($member['phone']): ?>
                                        <div><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($member['phone']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $member['category'] ? 'primary' : 'secondary'; ?>">
                                    <?php echo htmlspecialchars($member['category'] ?: 'N/A'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $member['status'] === 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($member['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                  
                                $lastAttendanceQuery = "SELECT MAX(e.start_datetime) as last_attendance 
                                                      FROM event_attendance ea 
                                                      JOIN events e ON ea.event_id = e.id 
                                                      WHERE ea.member_id = ? AND ea.attendance_status = 'present'";
                                $stmt = $conn->prepare($lastAttendanceQuery);
                                $stmt->execute([$member['id']]);
                                $lastAttendance = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                echo $lastAttendance['last_attendance'] 
                                    ? date('M d, Y', strtotime($lastAttendance['last_attendance'])) 
                                    : 'No attendance record';
                                ?>
                            </td>
                            <td class="member-actions">
                                <div class="btn-group">
                                    <button type="button" onclick="viewProfile(<?php echo $member['id']; ?>)" 
                                            class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" onclick="editMember(<?php echo $member['id']; ?>)" 
                                            class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" onclick="viewNotes(<?php echo $member['id']; ?>)" 
                                            class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-journal-text"></i>
                                    </button>
                                    <button type="button" onclick="deleteMember(<?php echo $member['id']; ?>)" 
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4">
                <div class="text-muted mb-3 mb-md-0">
                    Showing <span id="showing">0</span> of <span id="total">0</span> members
                </div>
                <ul class="pagination mb-0">
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addMemberForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ministry Type</label>
                            <input type="text" class="form-control" name="category" placeholder="e.g. Pastor, Elder, Deacon">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Associated User *</label>
                            <select class="form-select" name="associated_user" id="associated_user" required>
                                <option value="">Select Associated User</option>
                            </select>
                            <div class="text-muted small mt-1">User account associated with this member (required)</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Profile Image</label>
                            <input type="file" class="form-control" name="profile_image" accept="image/*">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addMemberForm" class="btn btn-primary">Add Member</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Member Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addNoteForm" class="mb-4">
                    <input type="hidden" name="member_id" id="note_member_id">
                    <div class="mb-3">
                        <label class="form-label">Note Type</label>
                        <select class="form-select" name="note_type" required>
                            <option value="general">General</option>
                            <option value="pastoral">Pastoral</option>
                            <option value="counseling">Counseling</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" name="note_text" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Note</button>
                </form>
                <div class="notes-list">
                    <h6>Previous Notes</h6>
                    <div id="notesList" class="list-group">
                        <!-- Notes will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    loadStats();
    initializeCharts();
    

    document.getElementById('searchMembers').addEventListener('input', debounce(() => loadMembers(1, true), 300));
    document.getElementById('statusFilter').addEventListener('change', () => loadMembers(1, true));
    document.getElementById('ministryFilter').addEventListener('input', debounce(() => loadMembers(1, true), 300));
    

    updatePagination(1, Math.ceil(<?php echo count($members); ?> / 10));
    

    document.getElementById('showing').textContent = <?php echo count($members); ?>;
    document.getElementById('total').textContent = <?php echo count($members); ?>;


    document.getElementById('addMemberForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const isEditing = formData.has('id');
        

        const endpoint = isEditing ? '../crud/members/update_members.php' : '../crud/members/create_member.php';
        
        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload(); 
            } else {
                alert(data.message || `Error ${isEditing ? 'updating' : 'adding'} member`);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Load available users for association
    loadAvailableUsers();

    // Add event listener for associated user selection
    document.getElementById('associated_user').addEventListener('change', function() {
        const selectedUserId = this.value;
        const emailField = document.querySelector('[name="email"]');
        
        if (selectedUserId) {
            // Get the selected option's email data
            const selectedOption = this.options[this.selectedIndex];
            const userEmail = selectedOption.dataset.email;
            
            if (userEmail) {
                emailField.value = userEmail;
                emailField.disabled = true;  // Disable the email field
                emailField.classList.add('bg-light');  // Add visual indicator that field is disabled
            }
        } else {
            // If no user is selected (No Associated User option)
            emailField.disabled = false;  // Enable the email field
            emailField.classList.remove('bg-light');
        }
    });
    
    // Add event listener for form reset (when modal is closed)
    document.getElementById('addMemberModal').addEventListener('hidden.bs.modal', function() {
        const emailField = document.querySelector('[name="email"]');
        emailField.disabled = false;  // Re-enable the email field
        emailField.classList.remove('bg-light');
        // ...existing code...
        const form = document.getElementById('addMemberForm');
        form.reset();
        const idInput = form.querySelector('[name="id"]');
        if (idInput) idInput.remove();
        document.querySelector('#addMemberModal .modal-title').textContent = 'Add New Member';
        document.querySelector('#addMemberModal button[type="submit"]').textContent = 'Add Member';
    });

});

function loadAvailableUsers() {
    fetch('../crud/users/get_available_users.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('associated_user');
                select.innerHTML = '<option value="">No Associated User</option>';
                data.users.forEach(user => {
                    select.insertAdjacentHTML('beforeend', `
                        <option value="${user.id}" data-email="${user.email}">${user.username} (${user.email})</option>
                    `);
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

function loadMembers(page = 1, refreshTable = false) {

    if (!refreshTable) return;

    const search = document.getElementById('searchMembers').value;
    const status = document.getElementById('statusFilter').value;
    const ministry = document.getElementById('ministryFilter').value;
    
    const queryParams = new URLSearchParams({
        page: page,
        search: search,
        status: status,
        ministry: ministry
    });
    
    fetch(`../crud/members/read_members.php?${queryParams}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tbody = document.querySelector('table tbody');
                tbody.innerHTML = '';
                
                data.data.members.forEach(member => {
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>
                                <div class="d-flex align-items-center member-info">
                                    ${member.profile_image ? 
                                        `<img src="data:image/jpeg;base64,${member.profile_image}" 
                                             class="rounded-circle me-2 profile-image" style="width: 40px; height: 40px; object-fit: cover;">` :
                                        `<div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2 profile-image" 
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-person"></i>
                                         </div>`
                                    }
                                    <div>
                                        <div class="fw-bold">${member.first_name} ${member.last_name}</div>
                                        <small class="text-muted">Member since ${formatDate(member.membership_date)}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="member-contact">
                                    ${member.email ? `<div><i class="bi bi-envelope"></i> ${member.email}</div>` : ''}
                                    ${member.phone ? `<div><i class="bi bi-telephone"></i> ${member.phone}</div>` : ''}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-${member.category ? 'primary' : 'secondary'}">
                                    ${member.category || 'N/A'}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-${getStatusBadge(member.status)}">
                                    ${capitalizeFirst(member.status)}
                                </span>
                            </td>
                            <td>${member.last_attendance ? formatDate(member.last_attendance) : 'No record'}</td>
                            <td class="member-actions">
                                <div class="btn-group">
                                    <button type="button" onclick="viewProfile(${member.id})" 
                                            class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" onclick="editMember(${member.id})" 
                                            class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" onclick="viewNotes(${member.id})" 
                                            class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-journal-text"></i>
                                    </button>
                                    <button type="button" onclick="deleteMember(${member.id})" 
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                });


                updatePagination(data.data.currentPage, data.data.totalPages);
                
   
                document.getElementById('showing').textContent = data.data.members.length;
                document.getElementById('total').textContent = data.data.total;
                

                updateCharts();
            }
        })
        .catch(error => console.error('Error:', error));
}

function loadStats() {
    fetch('../crud/members/get_stats.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalMembers').textContent = data.total;
            document.getElementById('activeMembers').textContent = data.active;
            document.getElementById('newMembers').textContent = data.new;
            document.getElementById('regularAttendance').textContent = data.regularAttendance + '%';
        })
        .catch(error => console.error('Error:', error));
}



function initializeCharts() {
    // Member growth chart initialization
    const growthCtx = document.getElementById('memberGrowthChart').getContext('2d');
    window.memberGrowthChart = new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'New Members',
                data: [],
                borderColor: '#6a1b9a',
                backgroundColor: 'rgba(106, 27, 154, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Member categories chart initialization - now dynamic
    const categoriesCtx = document.getElementById('memberCategoriesChart').getContext('2d');
    window.memberCategoriesChart = new Chart(categoriesCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [] // Will be generated dynamically
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });

    updateCharts();
}

// Helper function to generate colors for chart
function generateChartColors(count) {
    const baseColors = ['#6a1b9a', '#9c27b0', '#ba68c8', '#e1bee7', '#4a148c', '#7b1fa2', '#ab47bc', '#ce93d8'];
    const colors = [];
    
    for (let i = 0; i < count; i++) {
        if (i < baseColors.length) {
            colors.push(baseColors[i]);
        } else {
            // Generate random colors if we need more than base colors
            const hue = (i * 137.508) % 360; // Use golden angle approximation for better distribution
            colors.push(`hsl(${hue}, 70%, 60%)`);
        }
    }
    
    return colors;
}

function updateCharts() {
    fetch('../crud/members/get_charts_data.php')
        .then(response => response.json())
        .then(data => {

            window.memberGrowthChart.data.labels = data.growth.labels;
            window.memberGrowthChart.data.datasets[0].data = data.growth.values;
            window.memberGrowthChart.update();


            window.memberCategoriesChart.data.labels = data.categories.labels;
            window.memberCategoriesChart.data.datasets[0].data = data.categories.values;
            window.memberCategoriesChart.data.datasets[0].backgroundColor = generateChartColors(data.categories.labels.length);
            window.memberCategoriesChart.update();
        })
        .catch(error => console.error('Error:', error));
}

function updatePagination(currentPage, totalPages) {
    const pagination = document.querySelector('.pagination');
    pagination.innerHTML = '';
    
    pagination.insertAdjacentHTML('beforeend', `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadMembers(${currentPage - 1})">Previous</a>
        </li>
    `);
    
    for (let i = 1; i <= totalPages; i++) {
        pagination.insertAdjacentHTML('beforeend', `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadMembers(${i})">${i}</a>
            </li>
        `);
    }
    
    pagination.insertAdjacentHTML('beforeend', `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadMembers(${currentPage + 1})">Next</a>
        </li>
    `);
}

function getCategoryBadge(category) {
    const badges = {
        regular: 'primary',
        youth: 'success',
        senior: 'info',
        visitor: 'secondary'
    };
    return badges[category] || 'secondary';
}

function getStatusBadge(status) {
    const badges = {
        active: 'success',
        inactive: 'danger',
        pending: 'warning'
    };
    return badges[status] || 'secondary';
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function capitalizeFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function viewProfile(id) {
    fetch(`../crud/members/get_member_profile.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const member = data.data.member;
                const content = `
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Member Profile</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-4 text-center mb-3">
                                        ${member.profile_image ? 
                                            `<img src="data:image/jpeg;base64,${member.profile_image}" 
                                                  class="rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">` :
                                            `<div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto" 
                                                  style="width: 150px; height: 150px;">
                                                <i class="bi bi-person" style="font-size: 4rem;"></i>
                                             </div>`
                                        }
                                        <h4 class="mt-2">${member.first_name} ${member.last_name}</h4>
                                        <div class="mt-2">
                                            <span class="badge bg-${member.category ? 'primary' : 'secondary'}">${member.category || 'N/A'}</span>
                                            <span class="badge bg-${getStatusBadge(member.status)}">${capitalizeFirst(member.status)}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h5>Contact Information</h5>
                                        <p><i class="bi bi-envelope"></i> ${member.email || 'No email'}</p>
                                        <p><i class="bi bi-telephone"></i> ${member.phone || 'No phone'}</p>
                                        <p><i class="bi bi-geo-alt"></i> ${member.address || 'No address'}</p>
                                        
                                        <h5 class="mt-4">Member Statistics</h5>
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h6 class="card-subtitle mb-2 text-muted">Attendance Rate</h6>
                                                        <h3 class="card-title">${data.data.attendanceRate}%</h3>
                                                        <small>Last 3 months</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h6 class="card-subtitle mb-2 text-muted">Total Contributions</h6>
                                                        <h3 class="card-title">$${(member.total_contribution || 0).toLocaleString()}</h3>
                                                        <small>${member.total_donations || 0} donations</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <h5>Recent Attendance</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Event</th>
                                                        <th>Date</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${data.data.recentAttendance.map(att => `
                                                        <tr>
                                                            <td>${att.title}</td>
                                                            <td>${formatDate(att.start_datetime)}</td>
                                                            <td>
                                                                <span class="badge bg-${att.attendance_status === 'present' ? 'success' : 'danger'}">
                                                                    ${capitalizeFirst(att.attendance_status)}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    `).join('') || '<tr><td colspan="3" class="text-center">No recent attendance</td></tr>'}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Recent Donations</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Amount</th>
                                                        <th>Type</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${data.data.recentDonations.map(don => `
                                                        <tr>
                                                            <td>$${don.amount.toLocaleString()}</td>
                                                            <td>${capitalizeFirst(don.donation_type)}</td>
                                                            <td>${formatDate(don.donation_date)}</td>
                                                        </tr>
                                                    `).join('') || '<tr><td colspan="3" class="text-center">No recent donations</td></tr>'}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.innerHTML = content;
                document.body.appendChild(modal);
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                modal.addEventListener('hidden.bs.modal', () => {
                    modal.remove();
                });
            } else {
                alert(data.message || 'Error loading member profile');
            }
        })
        .catch(error => console.error('Error:', error));
}

function editMember(id) {
    fetch(`../crud/members/read_single_member.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const member = data.data;
                const form = document.getElementById('addMemberForm');
                // ...existing form population code...
                
                // Set associated user if exists
                if (member.user_id) {
                    form.querySelector('[name="associated_user"]').value = member.user_id;
                }

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = member.id;
                form.appendChild(idInput);

                document.querySelector('#addMemberModal .modal-title').textContent = 'Edit Member';
                document.querySelector('#addMemberModal button[type="submit"]').textContent = 'Save Changes';
                
                new bootstrap.Modal(document.getElementById('addMemberModal')).show();
            } else {
                alert(data.message || 'Error loading member data');
            }
        })
        .catch(error => console.error('Error:', error));
}

function deleteMember(id) {
    if (confirm('Are you sure you want to delete this member? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('id', id);
        
        fetch('../crud/members/delete_member.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error deleting member');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function viewNotes(id) {
    document.getElementById('note_member_id').value = id;
    loadMemberNotes(id);
    new bootstrap.Modal(document.getElementById('notesModal')).show();
}

function loadMemberNotes(memberId) {
    fetch(`../crud/members/get_member_notes.php?member_id=${memberId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notesList = document.getElementById('notesList');
                notesList.innerHTML = '';
                
                data.notes.forEach(note => {
                    const date = new Date(note.created_at).toLocaleDateString();
                    const noteTypeClass = {
                        'general': 'info',
                        'pastoral': 'warning',
                        'counseling': 'danger',
                        'other': 'secondary'
                    }[note.note_type];
                    
                    notesList.insertAdjacentHTML('beforeend', `
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    <span class="badge bg-${noteTypeClass}">${capitalizeFirst(note.note_type)}</span>
                                </h6>
                                <small>${date}</small>
                            </div>
                            <p class="mb-1">${note.note_text}</p>
                            <small>Added by: ${note.created_by_name || 'System'}</small>
                        </div>
                    `);
                });
                
                if (data.notes.length === 0) {
                    notesList.innerHTML = '<div class="text-center text-muted">No notes found</div>';
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

document.getElementById('addNoteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('../crud/members/add_member_note.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            this.reset();
            loadMemberNotes(formData.get('member_id'));
        } else {
            alert(data.message || 'Error adding note');
        }
    })
    .catch(error => console.error('Error:', error));
});

document.getElementById('addMemberModal').addEventListener('hidden.bs.modal', function() {
    const form = document.getElementById('addMemberForm');
    form.reset();
    const idInput = form.querySelector('[name="id"]');
    if (idInput) idInput.remove();
    document.querySelector('#addMemberModal .modal-title').textContent = 'Add New Member';
    document.querySelector('#addMemberModal button[type="submit"]').textContent = 'Add Member';
});


function capitalizeFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}
</script>

<?php require_once '../templates/admin_footer.php'; ?>