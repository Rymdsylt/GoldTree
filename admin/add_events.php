<?php
session_start();
require_once '../db/connection.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: /GoldTree/login.php");
    exit();
}

$stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['admin_status'] != 1) {
    header("Location: /GoldTree/events.php");
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
                        <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='../events.php?page=events'">
                            <i class="bi bi-arrow-left"></i> Back to Events
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">Existing Events</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date & Time</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="eventsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventDetailsModalLabel">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <img id="modalEventImage" src="../images/placeholder-event.jpg" class="img-fluid rounded" alt="Event image" style="max-height: 300px;">
                        </div>
                        <h3 id="modalEventTitle" class="card-title"></h3>
                        <div class="mb-3">
                            <i class="bi bi-calendar-event text-primary"></i>
                            <span id="modalEventDateTime"></span>
                        </div>
                        <div class="mb-3">
                            <i class="bi bi-geo-alt text-primary"></i>
                            <span id="modalEventLocation"></span>
                        </div>
                        <div class="mb-3">
                            <i class="bi bi-tag text-primary"></i>
                            <span id="modalEventType"></span>
                        </div>
                        <div class="mb-3">
                            <i class="bi bi-people text-primary"></i>
                            <span id="modalEventAttendees"></span>
                        </div>
                        <div class="mb-3">
                            <i class="bi bi-clock text-primary"></i>
                            <span id="modalEventDeadline"></span>
                        </div>
                        <div class="mb-3">
                            <h5>Description</h5>
                            <p id="modalEventDescription" class="text-muted"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editEventForm">
                    <input type="hidden" name="id">
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
                            <label class="form-label">Event Image</label>
                            <input type="file" class="form-control" name="event_image" accept="image/*">
                            <div class="form-text">Leave empty to keep existing image</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editEventForm" class="btn btn-primary">Save Changes</button>
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

    loadEvents(); 

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

    function loadEvents() {
        fetch('../crud/events/read_events.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('eventsTableBody');
                tbody.innerHTML = '';
                
                data.events.forEach(event => {
                    const startDate = new Date(event.start_datetime);
                    const endDate = new Date(event.end_datetime);
                    
                    const row = `
                        <tr>
                            <td>${event.title}</td>
                            <td>
                                ${startDate.toLocaleDateString()} ${startDate.toLocaleTimeString()} - 
                                ${endDate.toLocaleDateString()} ${endDate.toLocaleTimeString()}
                            </td>
                            <td><span class="badge bg-info">${event.event_type}</span></td>
                            <td>${event.location}</td>
                            <td><span class="badge bg-${getStatusBadge(event.status)}">${event.status}</span></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-info" onclick="viewEvent(${JSON.stringify(event).replace(/"/g, '&quot;')})">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="editEvent(${JSON.stringify(event).replace(/"/g, '&quot;')})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteEvent(${event.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            })
            .catch(error => console.error('Error:', error));
    }

    window.viewEvent = function(event) {
 
        document.getElementById('modalEventTitle').textContent = event.title;
        document.getElementById('modalEventDateTime').textContent = `${new Date(event.start_datetime).toLocaleString()} - ${new Date(event.end_datetime).toLocaleString()}`;
        document.getElementById('modalEventLocation').textContent = event.location;
        document.getElementById('modalEventType').textContent = event.event_type.charAt(0).toUpperCase() + event.event_type.slice(1);
        document.getElementById('modalEventAttendees').textContent = event.max_attendees ? `Maximum ${event.max_attendees} attendees` : 'Unlimited attendees';
        document.getElementById('modalEventDeadline').textContent = event.registration_deadline ? 
            `Registration deadline: ${new Date(event.registration_deadline).toLocaleString()}` : 
            'No registration deadline';
        document.getElementById('modalEventDescription').textContent = event.description || 'No description available';
 
        const modalImage = document.getElementById('modalEventImage');
        if (event.image) {
            modalImage.src = `data:image/jpeg;base64,${event.image}`;
        } else {
            modalImage.src = '../images/placeholder-event.jpg';
        }

  
        const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
        modal.show();
    }

    window.editEvent = function(event) {
        const form = document.getElementById('editEventForm');
        form.elements['id'].value = event.id;
        form.elements['title'].value = event.title;
        form.elements['start_datetime'].value = event.start_datetime.slice(0, 16);
        form.elements['end_datetime'].value = event.end_datetime.slice(0, 16);
        form.elements['event_type'].value = event.event_type;
        form.elements['location'].value = event.location;
        form.elements['description'].value = event.description || '';
        form.elements['max_attendees'].value = event.max_attendees || '';
        if (event.registration_deadline) {
            form.elements['registration_deadline'].value = event.registration_deadline.slice(0, 16);
        }

        const modal = new bootstrap.Modal(document.getElementById('editEventModal'));
        modal.show();
    };

    document.getElementById('editEventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('../crud/events/update_event.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Event updated successfully');
                loadEvents();
                const modal = bootstrap.Modal.getInstance(document.getElementById('editEventModal'));
                modal.hide();
            } else {
                throw new Error(data.message || 'Failed to update event');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', error.message || 'An error occurred while updating the event');
        });
    });

    function getStatusBadge(status) {
        switch(status) {
            case 'upcoming': return 'primary';
            case 'ongoing': return 'success';
            case 'completed': return 'secondary';
            case 'cancelled': return 'danger';
            default: return 'info';
        }
    }

    window.deleteEvent = function(id) {
        if (confirm('Are you sure you want to delete this event?')) {
            const formData = new FormData();
            formData.append('id', id);
            
            fetch('../crud/events/delete_events.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Event deleted successfully');
                    loadEvents(); 
                } else {
                    throw new Error(data.message || 'Failed to delete event');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', error.message || 'An error occurred while deleting the event');
            });
        }
    }
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