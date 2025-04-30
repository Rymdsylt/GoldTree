<?php
session_start();
require_once '../templates/admin_header.php';

?>

<style>
    .btn-primary {
        background-color: #6a1b9a !important;
        border-color: #6a1b9a !important;
    }
    .btn-primary:hover {
        background-color: #4a148c !important;
        border-color: #4a148c !important;
    }
</style>

<h2><i class="bi bi-bell"></i> Notifications</h2>
<hr>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">All Notifications</h5>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm" onclick="markAllRead()">
                            <i class="bi bi-check-all"></i> Mark All as Read
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Filter by Type:</label>
                            <select class="form-select" id="typeFilter">
                                <option value="">All Types</option>
                                <option value="announcement">Announcements</option>
                                <option value="event">Event Reminders</option>
                                <option value="donation">Donation Requests</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status:</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="unread">Unread</option>
                                <option value="read">Read</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">From Date:</label>
                            <input type="date" class="form-control" id="fromDate">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date:</label>
                            <input type="date" class="form-control" id="toDate">
                        </div>
                    </div>
                </div>

                <div class="list-group" id="notificationsList">
                </div>

                <nav class="mt-3">
                    <ul class="pagination justify-content-center" id="pagination">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="notificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Type:</strong>
                    <span id="modalType"></span>
                </div>
                <div class="mb-3">
                    <strong>Date:</strong>
                    <span id="modalDate"></span>
                </div>
                <div class="mb-3">
                    <strong>Message:</strong>
                    <p id="modalMessage"></p>
                </div>
                <div class="mb-3">
                    <strong>Recipients:</strong>
                    <div id="modalRecipients"></div>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="sendEmailCheck">
                        <label class="form-check-label" for="sendEmailCheck">
                            Send email notification to recipients
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" onclick="deleteNotification()">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentNotification = null;
    const notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));

    document.addEventListener('DOMContentLoaded', () => {
        loadNotifications();
        setupFilterListeners();

        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 30);
        
        document.getElementById('toDate').value = today.toISOString().split('T')[0];
        document.getElementById('fromDate').value = thirtyDaysAgo.toISOString().split('T')[0];
    });

    function setupFilterListeners() {
        const filters = ['typeFilter', 'statusFilter', 'fromDate', 'toDate'];
        filters.forEach(id => {
            document.getElementById(id).addEventListener('change', loadNotifications);
        });
    }

    function loadNotifications(page = 1) {
        const type = document.getElementById('typeFilter').value;
        const status = document.getElementById('statusFilter').value;
        const fromDate = document.getElementById('fromDate').value;
        const toDate = document.getElementById('toDate').value;

        fetch('../crud/notifications/read_notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ type, status, fromDate, toDate, page })
        })
        .then(response => response.json())
        .then(data => {
            const notificationsList = document.getElementById('notificationsList');
            notificationsList.innerHTML = '';
            
            data.notifications.forEach(notification => {
                notificationsList.innerHTML += `
                    <div class="list-group-item list-group-item-action ${notification.status === 'unread' ? 'active' : ''}"
                         onclick="showNotificationDetails(${JSON.stringify(notification).replace(/"/g, '&quot;')})">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">${notification.subject}</h5>
                            <small>${new Date(notification.created_at).toLocaleString()}</small>
                        </div>
                        <p class="mb-1">${notification.message.substring(0, 100)}...</p>
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <small>
                                <span class="badge bg-primary">${notification.notification_type}</span>
                                <span class="badge ${notification.status === 'sent' ? 'bg-success' : 'bg-warning'}">
                                    ${notification.status}
                                </span>
                            </small>
                            <small>${notification.recipient_count} recipients</small>
                        </div>
                    </div>
                `;
            });

            updatePagination(data.totalPages, page);
        })
        .catch(error => console.error('Error:', error));
    }

    function updatePagination(totalPages, currentPage) {
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = '';
        
        if (totalPages <= 1) return;

        pagination.innerHTML += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadNotifications(${currentPage - 1})">Previous</a>
            </li>
        `;

        for (let i = 1; i <= totalPages; i++) {
            pagination.innerHTML += `
                <li class="page-item ${currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadNotifications(${i})">${i}</a>
                </li>
            `;
        }

        pagination.innerHTML += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadNotifications(${currentPage + 1})">Next</a>
            </li>
        `;
    }

    function showNotificationDetails(notification) {
        currentNotification = notification;
        document.getElementById('modalTitle').textContent = notification.subject;
        document.getElementById('modalType').textContent = notification.notification_type;
        document.getElementById('modalDate').textContent = new Date(notification.created_at).toLocaleString();
        document.getElementById('modalMessage').textContent = notification.message;

        fetch('../crud/notifications/get_notification_recipients.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ notification_id: notification.id })
        })
        .then(response => response.json())
        .then(data => {
            const recipientsList = document.getElementById('modalRecipients');
            recipientsList.innerHTML = data.recipients.map(r => 
                `<span class="badge bg-secondary me-1">${r.username} (${r.email})</span>`
            ).join('');
        })
        .catch(error => console.error('Error:', error));

        notificationModal.show();

        if (notification.status === 'unread') {
            markAsRead(notification.id);
        }
    }

    function markAsRead(id) {
        fetch('../crud/notifications/mark_as_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
                updateNotificationBadge(); // Add this line
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function markAllRead() {
        fetch('../crud/notifications/mark_all_read.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
                updateNotificationBadge(); // Add this line
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function deleteNotification() {
        if (!currentNotification) return;
        
        if (confirm('Are you sure you want to delete this notification?')) {
            fetch('../crud/notifications/delete_notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: currentNotification.id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notificationModal.hide();
                    loadNotifications();
                } else {
                    alert('Error deleting notification: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }

    function updateNotificationBadge() {
        // Logic to refresh the notification badge
        console.log('Notification badge updated');
    }
</script>

<?php require_once '../templates/admin_footer.php'; ?>