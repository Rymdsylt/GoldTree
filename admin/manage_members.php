<?php require_once '../templates/admin_header.php'; ?>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Total Members</h6>
                <h2 class="card-title mb-0" id="totalMembers">0</h2>
                <small>All time</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Active Members</h6>
                <h2 class="card-title mb-0" id="activeMembers">0</h2>
                <small>Currently active</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">New Members</h6>
                <h2 class="card-title mb-0" id="newMembers">0</h2>
                <small>This month</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Regular Attendance</h6>
                <h2 class="card-title mb-0" id="regularAttendance">0%</h2>
                <small>Past 3 months</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Member Growth</h5>
                <canvas id="memberGrowthChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Member Categories</h5>
                <canvas id="memberCategoriesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchMembers" placeholder="Search members...">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="categoryFilter">
                            <option value="">All Categories</option>
                            <option value="regular">Regular</option>
                            <option value="youth">Youth</option>
                            <option value="senior">Senior</option>
                            <option value="visitor">Visitor</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                    <i class="bi bi-plus-lg"></i> Add Member
                </button>
                <button class="btn btn-outline-primary" id="exportBtn">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Members Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Contact</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Last Attendance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="membersTableBody">
                    <!-- Table content will be loaded dynamically -->
                </tbody>
            </table>
        </div>
        <nav aria-label="Members pagination" class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing <span id="showing">0</span> of <span id="total">0</span> members
            </div>
            <ul class="pagination mb-0">
                <!-- Pagination will be added dynamically -->
            </ul>
        </nav>
    </div>
</div>

<!-- Add Member Modal -->
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
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category" required>
                                <option value="regular">Regular</option>
                                <option value="youth">Youth</option>
                                <option value="senior">Senior</option>
                                <option value="visitor">Visitor</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
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

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadMembers();
    loadStats();
    initializeCharts();
    
    // Add event listeners
    document.getElementById('searchMembers').addEventListener('input', debounce(loadMembers, 300));
    document.getElementById('statusFilter').addEventListener('change', loadMembers);
    document.getElementById('categoryFilter').addEventListener('change', loadMembers);
    
    // Handle form submission
    document.getElementById('addMemberForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('../crud/members/create_member.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addMemberModal')).hide();
                loadMembers();
                loadStats();
                updateCharts();
                this.reset();
            } else {
                alert(data.message || 'Error adding member');
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

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

function loadMembers(page = 1) {
    const search = document.getElementById('searchMembers').value;
    const status = document.getElementById('statusFilter').value;
    const category = document.getElementById('categoryFilter').value;
    
    fetch(`../crud/members/read_members.php?page=${page}&search=${search}&status=${status}&category=${category}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('membersTableBody');
            tbody.innerHTML = '';
            
            data.members.forEach(member => {
                const row = `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                ${member.profile_image ? 
                                    `<img src="data:image/jpeg;base64,${member.profile_image}" 
                                          class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">` :
                                    `<div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                          style="width: 40px; height: 40px;">
                                        <i class="bi bi-person"></i>
                                     </div>`
                                }
                                <div>
                                    <div class="fw-bold">${member.first_name} ${member.last_name}</div>
                                    <small class="text-muted">#${member.id}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>${member.email}</div>
                            <small class="text-muted">${member.phone || 'No phone'}</small>
                        </td>
                        <td>
                            <span class="badge bg-${getCategoryBadge(member.category)}">
                                ${capitalizeFirst(member.category)}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-${getStatusBadge(member.status)}">
                                ${capitalizeFirst(member.status)}
                            </span>
                        </td>
                        <td>${formatDate(member.last_attendance_date) || 'Never'}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" onclick="viewProfile(${member.id})">
                                    <i class="bi bi-person-badge"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="editMember(${member.id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteMember(${member.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            });
            
            updatePagination(data.currentPage, data.totalPages);
            document.getElementById('showing').textContent = data.showing;
            document.getElementById('total').textContent = data.total;
        })
        .catch(error => console.error('Error:', error));
}

function initializeCharts() {
    // Member Growth Chart
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
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Member Categories Chart
    const categoriesCtx = document.getElementById('memberCategoriesChart').getContext('2d');
    window.memberCategoriesChart = new Chart(categoriesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Regular', 'Youth', 'Senior', 'Visitor'],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#6a1b9a',
                    '#9c27b0',
                    '#ba68c8',
                    '#e1bee7'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    updateCharts();
}

function updateCharts() {
    fetch('../crud/members/get_charts_data.php')
        .then(response => response.json())
        .then(data => {
            // Update Growth Chart
            window.memberGrowthChart.data.labels = data.growth.labels;
            window.memberGrowthChart.data.datasets[0].data = data.growth.values;
            window.memberGrowthChart.update();

            // Update Categories Chart
            window.memberCategoriesChart.data.datasets[0].data = data.categories.values;
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
</script>

<?php require_once '../templates/admin_footer.php'; ?>