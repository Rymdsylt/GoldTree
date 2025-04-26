<?php require_once 'templates/header.php'; ?>

<div class="container-fluid py-4">
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Notifications</h6>
                    <h2 class="card-title mb-0" id="totalAnnouncements">0</h2>
                    <small>All time</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Unread</h6>
                    <h2 class="card-title mb-0" id="activeAnnouncements">0</h2>
                    <small>Pending notifications</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Urgent</h6>
                    <h2 class="card-title mb-0" id="highPriorityCount">0</h2>
                    <small>High priority</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Response Rate</h6>
                    <h2 class="card-title mb-0" id="readRate">0%</h2>
                    <small>User responses</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchAnnouncement" placeholder="Search notifications...">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="priorityFilter">
                                <option value="">All Priorities</option>
                                <option value="high">Urgent</option>
                                <option value="medium">Important</option>
                                <option value="low">Normal</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="active">Unread</option>
                                <option value="expired">Read</option>
                                <option value="scheduled">Scheduled</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcements Grid -->
    <div class="row g-4" id="announcementsGrid">
        <!-- Announcements will be loaded dynamically -->
    </div>

    <!-- Pagination -->
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Announcements pagination" class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing <span id="showing">0</span> of <span id="total">0</span> announcements
                </div>
                <ul class="pagination mb-0">
                    <!-- Pagination will be added dynamically -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Add Announcement Modal -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send New Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addAnnouncementForm">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" name="content" rows="4" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select class="form-select" name="priority" required>
                                <option value="low">Normal</option>
                                <option value="medium">Important</option>
                                <option value="high">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Recipients</label>
                            <select class="form-select" name="target_audience" multiple>
                                <option value="all">All Members</option>
                                <option value="ministry_leaders">Ministry Leaders</option>
                                <option value="volunteers">Volunteers</option>
                                <option value="choir">Choir Members</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Schedule For</label>
                            <input type="datetime-local" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expires After</label>
                            <input type="datetime-local" class="form-control" name="end_date">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addAnnouncementForm" class="btn btn-primary">Send Notification</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadAnnouncements();
    loadStats();
    
    // Add event listeners
    const filters = ['searchAnnouncement', 'priorityFilter', 'statusFilter'];
    filters.forEach(id => {
        document.getElementById(id).addEventListener('change', loadAnnouncements);
    });
    document.getElementById('searchAnnouncement').addEventListener('input', debounce(loadAnnouncements, 300));
    
    // Handle form submission
    document.getElementById('addAnnouncementForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('crud/announcements/create_announcement.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addAnnouncementModal')).hide();
                loadAnnouncements();
                loadStats();
                this.reset();
            } else {
                alert(data.message || 'Error adding announcement');
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

function loadStats() {
    fetch('crud/announcements/get_stats.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalAnnouncements').textContent = data.total;
            document.getElementById('activeAnnouncements').textContent = data.active;
            document.getElementById('highPriorityCount').textContent = data.highPriority;
            document.getElementById('readRate').textContent = data.readRate + '%';
        })
        .catch(error => console.error('Error:', error));
}

function loadAnnouncements(page = 1) {
    const search = document.getElementById('searchAnnouncement').value;
    const priority = document.getElementById('priorityFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    fetch(`crud/announcements/read_announcements.php?page=${page}&search=${search}&priority=${priority}&status=${status}`)
        .then(response => response.json())
        .then(data => {
            const grid = document.getElementById('announcementsGrid');
            grid.innerHTML = '';
            
            data.announcements.forEach(announcement => {
                const card = `
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                <span class="badge bg-${getPriorityBadge(announcement.priority)}">
                                    ${capitalizeFirst(announcement.priority)} Priority
                                </span>
                                <div class="dropdown">
                                    <button class="btn btn-link btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button class="dropdown-item" onclick="editAnnouncement(${announcement.id})">
                                                <i class="bi bi-pencil me-2"></i> Edit
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" onclick="deleteAnnouncement(${announcement.id})">
                                                <i class="bi bi-trash me-2"></i> Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">${announcement.title}</h5>
                                <p class="card-text">${announcement.content}</p>
                                ${announcement.attachments ? `
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="bi bi-paperclip me-1"></i> ${announcement.attachments} attachments
                                        </small>
                                    </div>
                                ` : ''}
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Posted ${formatDate(announcement.created_at)}
                                    </small>
                                    <small class="text-muted">
                                        ${announcement.end_date ? `Expires ${formatDate(announcement.end_date)}` : 'No expiration'}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                grid.insertAdjacentHTML('beforeend', card);
            });
            
            updatePagination(data.currentPage, data.totalPages);
            document.getElementById('showing').textContent = data.showing;
            document.getElementById('total').textContent = data.total;
        })
        .catch(error => console.error('Error:', error));
}

function updatePagination(currentPage, totalPages) {
    const pagination = document.querySelector('.pagination');
    pagination.innerHTML = '';
    
    pagination.insertAdjacentHTML('beforeend', `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadAnnouncements(${currentPage - 1})">Previous</a>
        </li>
    `);
    
    for (let i = 1; i <= totalPages; i++) {
        pagination.insertAdjacentHTML('beforeend', `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadAnnouncements(${i})">${i}</a>
            </li>
        `);
    }
    
    pagination.insertAdjacentHTML('beforeend', `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadAnnouncements(${currentPage + 1})">Next</a>
        </li>
    `);
}

function getPriorityBadge(priority) {
    const badges = {
        high: 'danger',
        medium: 'warning',
        low: 'info'
    };
    return badges[priority] || 'secondary';
}

function formatDate(dateString) {
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

<?php require_once 'templates/footer.php'; ?>