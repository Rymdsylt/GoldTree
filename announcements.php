<?php require_once 'templates/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4 text-center">
                            <h6 class="text-muted mb-1">Total Notifications</h6>
                            <h3 id="totalAnnouncements">0</h3>
                        </div>
                        <div class="col-md-4 text-center">
                            <h6 class="text-muted mb-1">Active Notifications</h6>
                            <h3 id="activeAnnouncements">0</h3>
                        </div>
                        <div class="col-md-4 text-center">
                            <h6 class="text-muted mb-1">Read Rate</h6>
                            <h3 id="readRate">0%</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="searchAnnouncement" placeholder="Search notifications...">
                        </div>
                        <div class="col-md-6">
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
        <div class="col-md-4">
            <div class="d-flex gap-2 justify-content-end">
                <button class="btn btn-primary" onclick="markAllAsRead()">
                    <i class="bi bi-check-all"></i> Mark All as Read
                </button>
                <button class="btn btn-danger" onclick="deleteAllAnnouncements()">
                    <i class="bi bi-trash"></i> Delete All
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4" id="announcementsGrid">
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Announcements pagination" class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing <span id="showing">0</span> of <span id="total">0</span> announcements
                </div>
                <ul class="pagination mb-0">
                </ul>
            </nav>
        </div>
    </div>
</div>

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
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    loadAnnouncements();
    loadStats();

    const filters = ['searchAnnouncement', 'statusFilter'];
    filters.forEach(id => {
        document.getElementById(id).addEventListener('change', loadAnnouncements);
    });
    document.getElementById('searchAnnouncement').addEventListener('input', debounce(loadAnnouncements, 300));

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
            document.getElementById('readRate').textContent = data.readRate + '%';
        })
        .catch(error => console.error('Error:', error));
}

function loadAnnouncements(page = 1) {
    const search = document.getElementById('searchAnnouncement')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    
    fetch(`crud/announcements/read_announcements.php?page=${page}&search=${search}&status=${status}`)
        .then(response => response.json())
        .then(data => {
            const grid = document.getElementById('announcementsGrid');
            if (!grid) return;
            grid.innerHTML = '';

            if (!data.success) {
                grid.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error: ' + (data.message || 'Unknown error') + '</div></div>';
                updateCounters(0, 0);
                return;
            }
            
            if (!data.announcements || data.announcements.length === 0) {
                grid.innerHTML = '<div class="col-12"><div class="alert alert-info">No announcements found.</div></div>';
                updateCounters(0, 0);
                return;
            }

            data.announcements.forEach(announcement => {
                const readStatus = announcement.is_read ? 'Read' : 'Unread';
                const readStatusClass = announcement.is_read ? 'text-muted' : 'fw-bold';
                
                const card = `
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-secondary ms-1 ${readStatusClass}">
                                        ${readStatus}
                                    </span>
                                </div>                                <div class="dropdown" onclick="this.querySelector('.dropdown-menu').classList.toggle('show')">
                                    <button class="btn btn-link btn-sm" type="button">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" style="position: absolute;">
                                        <li>
                                            <button class="dropdown-item" onclick="event.stopPropagation(); markAsRead(${announcement.notification_id})">
                                                <i class="bi bi-check2 me-2"></i> Mark as Read
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item text-danger" onclick="event.stopPropagation(); deleteAnnouncement(${announcement.notification_id})">
                                                <i class="bi bi-trash me-2"></i> Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">${announcement.subject || 'Untitled'}</h5>
                                <p class="card-text">${announcement.message || 'No content'}</p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Posted ${formatDate(announcement.created_at || new Date())}
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
            });            updatePagination(data.currentPage || 1, data.totalPages || 1);
            updateCounters(data.showing || 0, data.total || 0);
        })
        .catch(error => {
            console.error('Error loading announcements:', error);
            const grid = document.getElementById('announcementsGrid');
            if (grid) {
                grid.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error loading announcements. Please try again.</div></div>';
            }
            updateCounters(0, 0);
        });
}

function markAsRead(id) {
    fetch('crud/announcements/mark_as_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadAnnouncements();
            loadStats();
            updateNotificationBadge();
        } else {
            alert('Error marking notification as read: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateCounters(showing, total) {
    const showingEl = document.getElementById('showing');
    const totalEl = document.getElementById('total');
    
    if (showingEl) showingEl.textContent = showing;
    if (totalEl) totalEl.textContent = total;
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

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function capitalizeFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function deleteAnnouncement(id) {
    if (confirm('Are you sure you want to delete this notification?')) {
        fetch('crud/announcements/delete_announcement.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadAnnouncements();
                loadStats();
            } else {
                alert('Error deleting notification: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function markAllAsRead() {
    fetch('crud/announcements/mark_all_as_read.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadAnnouncements();
            loadStats();
            updateNotificationBadge();
        } else {
            alert('Error marking all notifications as read: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteAllAnnouncements() {
    if (confirm('Are you sure you want to delete all notifications?')) {
        fetch('crud/announcements/delete_all_announcements.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadAnnouncements();
                loadStats();
            } else {
                alert('Error deleting all notifications: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
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