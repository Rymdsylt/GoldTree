<?php 
require_once 'templates/header.php';
require_once 'auth/login_status.php';
?>

<style>
.notification-card {
    transition: transform 0.2s ease;
}
.notification-card:hover {
    transform: translateY(-5px);
}
.notification-unread {
    border-left: 4px solid #6a1b9a;
}
.notification-read {
    border-left: 4px solid #dee2e6;
    opacity: 0.8;
}
</style>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="mb-0"><i class="bi bi-bell"></i> My Notifications</h2>
        </div>
    </div>
    <div class="row g-4" id="notificationsContainer">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadNotifications);

function loadNotifications() {
    fetch('crud/announcements/read_announcements.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('notificationsContainer');
            container.innerHTML = '';
            
            if (!data.announcements || data.announcements.length === 0) {
                container.innerHTML = '<div class="col-12"><div class="alert alert-info">No notifications found.</div></div>';
                return;
            }
            
            data.announcements.forEach(notification => {
                const isRead = notification.read_count > 0;
                const card = `
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 notification-card ${isRead ? 'notification-read' : 'notification-unread'}">
                            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                <span class="badge bg-${getBadgeClass(notification.notification_type)}">
                                    ${capitalizeFirst(notification.notification_type)}
                                </span>
                                <small class="text-muted">${formatDate(notification.created_at)}</small>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">
                                    ${!isRead ? '<i class="bi bi-circle-fill text-primary me-2" style="font-size: 8px;"></i>' : ''}
                                    ${notification.subject}
                                </h5>
                                <p class="card-text">${notification.message}</p>
                            </div>
                            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                                ${!isRead ? `
                                    <button class="btn btn-sm btn-outline-primary" onclick="markAsRead(${notification.id})">
                                        <i class="bi bi-check-circle"></i> Mark as Read
                                    </button>
                                ` : `
                                    <button class="btn btn-sm btn-outline-secondary" disabled>
                                        <i class="bi bi-check-circle-fill"></i> Read
                                    </button>
                                `}
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification(${notification.id})">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', card);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            const container = document.getElementById('notificationsContainer');
            container.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error loading notifications.</div></div>';
        });
}

function getBadgeClass(type) {
    const badges = {
        announcement: 'info',
        event: 'primary',
        donation: 'success',
        other: 'secondary'
    };
    return badges[type] || 'secondary';
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleString();
}

function capitalizeFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function markAsRead(id) {
    fetch('crud/notifications/mark_as_read.php', {
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
        } else {
            alert('Error marking notification as read: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification(id) {
    if (confirm('Are you sure you want to delete this notification?')) {
        fetch('crud/notifications/delete_notification.php', {
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
            } else {
                alert('Error deleting notification: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>

<?php require_once 'templates/footer.php'; ?>