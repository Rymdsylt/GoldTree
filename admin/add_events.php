<?php
require_once '../db/connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
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

<div class="row mb-4">
    <div class="col">
        <h2 class="mb-4">Create New Event</h2>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form id="eventForm">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Event Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Start Date & Time</label>
                            <input type="datetime-local" class="form-control" name="start_datetime" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">End Date & Time</label>
                            <input type="datetime-local" class="form-control" name="end_datetime" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Event Type</label>
                            <select class="form-select" name="event_type" required>
                                <option value="">Select Type</option>
                                <option value="worship">Worship Service</option>
                                <option value="prayer">Prayer Meeting</option>
                                <option value="youth">Youth Event</option>
                                <option value="outreach">Outreach</option>
                                <option value="special">Special Event</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Maximum Attendees</label>
                            <input type="number" class="form-control" name="max_attendees" min="1">
                            <div class="form-text">Leave empty for unlimited</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Registration Deadline</label>
                            <input type="datetime-local" class="form-control" name="registration_deadline">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Event Image (Optional)</label>
                            <input type="file" class="form-control" name="event_image" accept="image/*">
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="send_notifications" id="sendNotifications">
                                <label class="form-check-label" for="sendNotifications">
                                    Send notifications to members
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">Event Preview</h5>
                <div id="eventPreview" class="event-preview">
                    <div class="preview-image mb-3">
                        <img src="../images/placeholder-event.jpg" class="img-fluid rounded" id="previewImage">
                    </div>
                    <h4 id="previewTitle">Event Title</h4>
                    <div class="text-muted mb-2" id="previewDateTime">
                        <i class="bi bi-calendar-event"></i> Date and Time
                    </div>
                    <div class="text-muted mb-2" id="previewLocation">
                        <i class="bi bi-geo-alt"></i> Location
                    </div>
                    <div class="mb-3" id="previewDescription">
                        Event description will appear here...
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" form="eventForm" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Create Event
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='events.php'">
                            <i class="bi bi-arrow-left"></i> Back to Events
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('eventForm');
    const preview = {
        image: document.getElementById('previewImage'),
        title: document.getElementById('previewTitle'),
        dateTime: document.getElementById('previewDateTime'),
        location: document.getElementById('previewLocation'),
        description: document.getElementById('previewDescription')
    };

    form.addEventListener('input', function(e) {
        const input = e.target;
        
        switch(input.name) {
            case 'title':
                preview.title.textContent = input.value || 'Event Title';
                break;
            case 'start_datetime':
            case 'end_datetime':
                updateDateTimePreview();
                break;
            case 'location':
                preview.location.innerHTML = input.value ? 
                    `<i class="bi bi-geo-alt"></i> ${input.value}` : 
                    '<i class="bi bi-geo-alt"></i> Location';
                break;
            case 'description':
                preview.description.textContent = input.value || 'Event description will appear here...';
                break;
            case 'event_image':
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.image.src = e.target.result;
                    };
                    reader.readAsDataURL(input.files[0]);
                }
                break;
        }
    });

    function updateDateTimePreview() {
        const start = form.elements['start_datetime'].value;
        const end = form.elements['end_datetime'].value;
        
        if (start || end) {
            const startDate = start ? new Date(start) : null;
            const endDate = end ? new Date(end) : null;
            
            let dateTimeText = '<i class="bi bi-calendar-event"></i> ';
            if (startDate && endDate) {
                if (startDate.toDateString() === endDate.toDateString()) {
                    dateTimeText += formatDate(startDate) + ' ' + 
                        formatTime(startDate) + ' - ' + formatTime(endDate);
                } else {
                    dateTimeText += formatDate(startDate) + ' ' + formatTime(startDate) + 
                        ' - ' + formatDate(endDate) + ' ' + formatTime(endDate);
                }
            } else if (startDate) {
                dateTimeText += formatDate(startDate) + ' ' + formatTime(startDate);
            }
            preview.dateTime.innerHTML = dateTimeText;
        } else {
            preview.dateTime.innerHTML = '<i class="bi bi-calendar-event"></i> Date and Time';
        }
    }

    function formatDate(date) {
        return date.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function formatTime(date) {
        return date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit'
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
                let feedback = field.nextElementSibling;
                if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    field.parentNode.appendChild(feedback);
                }
                feedback.textContent = `Please enter ${field.previousElementSibling.textContent.toLowerCase()}`;
            } else {
                field.classList.remove('is-invalid');
                const feedback = field.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.remove();
                }
            }
        });

        if (!isValid) {
            return;
        }
        
        const formData = new FormData(this);
        
        fetch('../crud/events/create_event.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (formData.get('send_notifications') === 'on') {
                    showAlert('info', '<i class="bi bi-hourglass-split"></i> Sending email notifications...', false);
                    
                    fetch('../mailer/notify_event.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            event_id: data.event_id
                        })
                    })
                    .then(response => response.json())
                    .then(notifyData => {
                        if (notifyData.success) {
                            showAlert('success', 'Event created and notifications sent successfully!');
                        } else {
                            throw new Error(notifyData.message || 'Failed to send notifications');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('warning', 'Event created but failed to send notifications: ' + error.message);
                    });
                } else {
                    showAlert('success', 'Event created successfully!');
                }
                preview.image.src = '../images/placeholder-event.jpg';
                preview.title.textContent = 'Event Title';
                preview.dateTime.innerHTML = '<i class="bi bi-calendar-event"></i> Date and Time';
                preview.location.innerHTML = '<i class="bi bi-geo-alt"></i> Location';
                preview.description.textContent = 'Event description will appear here...';
            } else {
                throw new Error(data.message || 'Failed to create event');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', error.message || 'An error occurred while creating the event');
        });
    });
});

function showAlert(type, message, autoDismiss = true) {
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    const form = document.getElementById('eventForm');
    form.parentNode.insertBefore(alertDiv, form);

    if (autoDismiss) {
        setTimeout(() => {
            if (alertDiv && alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
}
</script>

<?php require_once '../templates/admin_footer.php'; ?>