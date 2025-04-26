<?php
require_once '../auth/login_status.php';
require_once '../db/connection.php';
session_start();

// Verify admin status
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

require_once '../templates/admin_header.php';
?>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Send Notification</h5>
            </div>
            <div class="card-body">
                <form id="notificationForm">
                    <div class="mb-3">
                        <label class="form-label">Recipients</label>
                        <div class="d-flex gap-2 mb-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAll()">
                                Select All
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAll()">
                                Deselect All
                            </button>
                        </div>
                        <div class="card member-select-list">
                            <div class="list-group list-group-flush" id="membersList">
                                <!-- Members will be loaded here dynamically -->
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notification Type</label>
                        <select class="form-select" name="notification_type" required>
                            <option value="announcement">Announcement</option>
                            <option value="event">Event Reminder</option>
                            <option value="donation">Donation Request</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sendEmail" name="send_email">
                            <label class="form-check-label" for="sendEmail">
                                Also send as email
                            </label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Notification
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent Notifications -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Recent Notifications</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Subject</th>
                                <th>Recipients</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="notificationsTableBody">
                            <!-- Recent notifications will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadMembers();
        loadRecentNotifications();
    });

    function loadMembers() {
        fetch('../crud/members/read_members.php')
            .then(response => response.json())
            .then(data => {
                const membersList = document.getElementById('membersList');
                membersList.innerHTML = '';
                
                data.forEach(member => {
                    membersList.innerHTML += `
                        <label class="list-group-item">
                            <input class="form-check-input me-1" type="checkbox" name="recipients[]" 
                                value="${member.id}">
                            ${member.first_name} ${member.last_name}
                        </label>
                    `;
                });
            })
            .catch(error => console.error('Error:', error));
    }

    function loadRecentNotifications() {
        fetch('../crud/notifications/read_notifications.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('notificationsTableBody');
                tbody.innerHTML = '';
                
                data.forEach(notification => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${new Date(notification.created_at).toLocaleString()}</td>
                            <td>
                                <span class="badge bg-primary">
                                    ${notification.notification_type}
                                </span>
                            </td>
                            <td>${notification.subject}</td>
                            <td>${notification.recipient_count} members</td>
                            <td>
                                <span class="badge ${notification.status === 'sent' ? 'bg-success' : 'bg-warning'}">
                                    ${notification.status}
                                </span>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(error => console.error('Error:', error));
    }

    function selectAll() {
        document.querySelectorAll('input[name="recipients[]"]')
            .forEach(checkbox => checkbox.checked = true);
    }

    function deselectAll() {
        document.querySelectorAll('input[name="recipients[]"]')
            .forEach(checkbox => checkbox.checked = false);
    }

    document.getElementById('notificationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
 
        if (!formData.getAll('recipients[]').length) {
            alert('Please select at least one recipient');
            return;
        }

        fetch('../crud/notifications/create_notification.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Notification sent successfully!');
                this.reset();
                loadRecentNotifications();
            } else {
                alert('Error sending notification: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>

<?php require_once '../templates/admin_footer.php'; ?>