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

                        <div class="col-12">
                            <label class="form-label">Assigned Staff</label>
                            <div class="mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAllStaff">
                                    <label class="form-check-label" for="selectAllStaff">
                                        Select All Staff
                                    </label>
                                </div>
                            </div>
                            <div class="search-container position-relative">
                                <input type="text" 
                                    class="form-control" 
                                    id="staffSearch" 
                                    placeholder="Search staff members..." 
                                    autocomplete="off">
                                <div id="staffSearchSuggestions" 
                                    class="suggestions-dropdown d-none">
                                </div>
                            </div>
                            <div id="selectedStaff" class="selected-users mt-2"></div>
                            <input type="hidden" name="assigned_staff" id="assignedStaffIds">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Event Image (Optional)</label>
                            <input type="file" class="form-control" name="event_image" accept="image/*">
                        </div>

                        <div class="col-12">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="send_notifications" id="sendNotifications">
                                <label class="form-check-label" for="sendNotifications">
                                    Send notifications to registered users
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="send_all_emails" id="sendAllEmails">
                                <label class="form-check-label" for="sendAllEmails">
                                    Send email to all users and members
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
                <h5 class="card-title mb-4">Ongoing Events - Mark the Members' attendances</h5>
                <div class="table-responsive">
                    <table class="table table-hover" id="ongoingEventsTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Attendance</th>
                            </tr>
                        </thead>
                        <tbody id="ongoingEventsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Attendances</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 id="eventTitle" class="mb-3"></h6>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="text-center">Mark As Present</th>
                                <th class="text-center">Mark As Absent</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                                <th class="sortable" data-sort="title">Title <i class="bi bi-arrow-down-up"></i></th>
                                <th class="sortable" data-sort="start_datetime">Date & Time <i class="bi bi-arrow-down-up"></i></th>
                                <th class="sortable" data-sort="event_type">Type <i class="bi bi-arrow-down-up"></i></th>
                                <th class="sortable" data-sort="location">Location <i class="bi bi-arrow-down-up"></i></th>
                                <th class="sortable" data-sort="status">Status <i class="bi bi-arrow-down-up"></i></th>
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
                        <div class="col-12">
                            <label class="form-label">Assigned Staff</label>
                            <div class="mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editSelectAllStaff">
                                    <label class="form-check-label" for="editSelectAllStaff">
                                        Select All Staff
                                    </label>
                                </div>
                            </div>
                            <div class="search-container position-relative">
                                <input type="text" 
                                    class="form-control" 
                                    id="editStaffSearch" 
                                    placeholder="Search staff members..." 
                                    autocomplete="off">
                                <div id="editStaffSearchSuggestions" 
                                    class="suggestions-dropdown d-none">
                                </div>
                            </div>
                            <div id="editSelectedStaff" class="selected-users mt-2"></div>
                            <input type="hidden" name="assigned_staff" id="editAssignedStaffIds">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Event Image</label>
                            <input type="file" class="form-control" name="event_image" accept="image/*">
                            <div class="form-text">Leave empty to keep existing image</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="send_notifications" id="editSendNotifications">
                                <label class="form-check-label" for="editSendNotifications">
                                    Send update notifications to members
                                </label>
                            </div>
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

<div class="modal fade" id="viewAttendeesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Event Attendees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Attendees</th>
                                <th>Absentees</th>
                            </tr>
                        </thead>
                        <tbody id="attendeesTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="markAttendanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6 class="event-title"></h6>
                    <small class="text-muted event-datetime"></small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

function getStatusBadge(status) {
    switch(status) {
        case 'upcoming': return 'primary';
        case 'ongoing': return 'success';
        case 'completed': return 'secondary';
        case 'cancelled': return 'danger';
        case 'no_record': return 'secondary';
        case 'absent': return 'danger';
        case 'present': return 'success';
        default: return 'info';
    }
}


let selectedStaffMembers = new Set();
let editSelectedStaffMembers = new Set();

let currentSort = { column: 'created_at', direction: 'DESC' };
let currentStaffSuggestionIndex = -1;
let staffDebounceTimeout;

function loadEvents() {
    fetch('../cron/update_event_status.php')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.text().then(text => {
                try {
                    const jsonStart = text.indexOf('{');
                    const jsonEnd = text.lastIndexOf('}');
                    if (jsonStart >= 0 && jsonEnd >= 0) {
                        return JSON.parse(text.substring(jsonStart, jsonEnd + 1));
                    }
                    throw new Error('No JSON found in response');
                } catch (e) {
                    console.error('Response text:', text);
                    throw new Error('Invalid response format');
                }
            });
        })
        .then(data => {
            if (!data.success) {
                console.error('Status update error:', data.message);
            }
    
            const url = new URL('../crud/events/read_events.php', window.location.href);
            url.searchParams.append('sort', currentSort.column);
            url.searchParams.append('direction', currentSort.direction);
            return fetch(url);
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                try {
               
                    const jsonStart = text.indexOf('{');
                    const jsonEnd = text.lastIndexOf('}');
                    if (jsonStart >= 0 && jsonEnd >= 0) {
                        return JSON.parse(text.substring(jsonStart, jsonEnd + 1));
                    }
                    throw new Error('No JSON found in response');
                } catch (e) {
                    console.error('Response text:', text);
                    throw new Error('Invalid response format');
                }
            });
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Server returned error');
            }
            
            const tbody = document.getElementById('eventsTableBody');
            tbody.innerHTML = '';
            
            if (!data.events || data.events.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No events found</td></tr>';
                return;
            }
            
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
                                <button class="btn btn-sm btn-primary" onclick="viewAttendees(${event.id})">
                                    <i class="bi bi-people-fill"></i>
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
        .catch(error => {
            console.error('Error:', error);
            const tbody = document.getElementById('eventsTableBody');
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Error loading events: ${error.message}</td></tr>`;
        });
}


document.querySelectorAll('.sortable').forEach(header => {
    header.addEventListener('click', function() {
        const column = this.dataset.sort;
        
   
        document.querySelectorAll('.sortable').forEach(h => {
            h.classList.remove('asc', 'desc');
        });
        
       
        if (currentSort.column === column) {
            currentSort.direction = currentSort.direction === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentSort.column = column;
            currentSort.direction = 'ASC';
        }
        
    
        this.classList.add(currentSort.direction.toLowerCase());
        

        loadEvents();
    });
});

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
    loadOngoingEvents();
    setInterval(loadOngoingEvents, 60000);

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
        
    
        if (start && end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            
            if (startDate > endDate) {
                const endInput = form.elements['end_datetime'];
                endInput.classList.add('is-invalid');
                let feedback = endInput.nextElementSibling;
                if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    endInput.parentNode.appendChild(feedback);
                }
                feedback.textContent = 'End date must be after start date';
                return;
            } else {
                const endInput = form.elements['end_datetime'];
                endInput.classList.remove('is-invalid');
                const feedback = endInput.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.remove();
                }
            }
        }
        
        let dateTimeText = '<i class="bi bi-calendar-event"></i> ';
        if (start && end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            if (startDate.toDateString() === endDate.toDateString()) {
                dateTimeText += formatDate(startDate) + ' ' + 
                    formatTime(startDate) + ' - ' + formatTime(endDate);
            } else {
                dateTimeText += formatDate(startDate) + ' ' + formatTime(startDate) + 
                    ' - ' + formatDate(endDate) + ' ' + formatTime(endDate);
            }
        } else if (start) {
            const startDate = new Date(start);
            dateTimeText += formatDate(startDate) + ' ' + formatTime(startDate);
        }
        preview.dateTime.innerHTML = dateTimeText;
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
        
      
        const startDate = new Date(form.elements['start_datetime'].value);
        const endDate = new Date(form.elements['end_datetime'].value);
        
        if (startDate > endDate) {
            const endInput = form.elements['end_datetime'];
            endInput.classList.add('is-invalid');
            let feedback = endInput.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                endInput.parentNode.appendChild(feedback);
            }
            feedback.textContent = 'End date must be after start date';
            return; 
        }
        
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

   
        const submitButton = document.querySelector('button[form="eventForm"][type="submit"]');
        const formData = new FormData(this);
        const sendNotifications = formData.get('send_notifications') === 'on';
        const sendAllEmails = formData.get('send_all_emails') === 'on';

        const eventData = {
            title: formData.get('title'),
            start_datetime: formData.get('start_datetime'),
            end_datetime: formData.get('end_datetime'),
            event_type: formData.get('event_type'),
            location: formData.get('location'),
            description: formData.get('description'),
            max_attendees: formData.get('max_attendees')
        };

   
        if (sendNotifications || sendAllEmails) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating Event & Sending Emails... Don\'t close or refresh the page!';
            showAlert('info', '<i class="bi bi-hourglass-split"></i> Creating event and sending email notifications... Don\'t close or refresh the page!', false);
        }

        fetch('../crud/events/create_event.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                // I FUCKEN HATE THIS
                const jsonStart = text.indexOf('{');
                const jsonEnd = text.lastIndexOf('}');
                if (jsonStart >= 0 && jsonEnd >= 0) {
                    const jsonString = text.substring(jsonStart, jsonEnd + 1);
                    try {
                        return JSON.parse(jsonString);
                    } catch (e) {
                        console.error('Response text:', text);
                        throw new Error('Invalid JSON in response');
                    }
                } else {
                    console.error('Response text:', text);
                    throw new Error('No JSON found in response');
                }
            });
        })
        .then(data => {
            if (data.success) {
                showAlert('success', 'Event created successfully!');
                preview.image.src = '../images/placeholder-event.jpg';
                preview.title.textContent = 'Event Title';
                preview.dateTime.innerHTML = '<i class="bi bi-calendar-event"></i> Date and Time';
                preview.location.innerHTML = '<i class="bi bi-geo-alt"></i> Location';
                preview.description.textContent = 'Event description will appear here...';
                form.reset();
                loadEvents();
            } else {
                throw new Error(data.message || 'Failed to create event');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', error.message || 'An error occurred while processing your request');
        })
        .finally(() => {

            if (sendNotifications || sendAllEmails) {
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="bi bi-plus-circle"></i> Create Event';
            }
        });
    });

    function loadOngoingEvents() {
        const tbody = document.getElementById('ongoingEventsTableBody');
        

        tbody.innerHTML = '<tr><td colspan="4" class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading events...</td></tr>';
        
        fetch('../crud/events/read_events.php?status=ongoing')
            .then(response => response.json())
            .then(data => {
                tbody.innerHTML = '';
                
                if (!data.events || data.events.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle"></i> No ongoing events at this time
                                </div>
                            </td>
                        </tr>`;
                    return;
                }
                
                data.events.forEach(event => {
                    const startDate = new Date(event.start_datetime);
                    const endDate = new Date(event.end_datetime);
                    
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${event.title}</td>
                        <td>
                            ${startDate.toLocaleDateString()} ${startDate.toLocaleTimeString()} - 
                            ${endDate.toLocaleDateString()} ${endDate.toLocaleTimeString()}
                        </td>
                        <td>${event.location}</td>
                        <td>
                            <button class="btn btn-primary" onclick="showAttendanceModal(${event.id})">
                                <i class="bi bi-person-check"></i> Mark Attendances
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);

         
                    row.style.animation = 'fadeIn 0.5s';
                });
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center">
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle"></i> Error loading ongoing events
                            </div>
                        </td>
                    </tr>`;
            });
    }


    document.addEventListener('DOMContentLoaded', function() {
        
        loadOngoingEvents();
        
     
        setInterval(loadOngoingEvents, 30000);
    });

    window.viewEvent = function(event) {
        document.getElementById('modalEventTitle').textContent = event.title;
        document.getElementById('modalEventDateTime').textContent = `${new Date(event.start_datetime).toLocaleString()} - ${new Date(event.end_datetime).toLocaleString()}`;
        document.getElementById('modalEventLocation').textContent = event.location;
        document.getElementById('modalEventType').textContent = event.event_type.charAt(0).toUpperCase() + event.event_type.slice(1);
        document.getElementById('modalEventAttendees').textContent = event.max_attendees ? `Maximum ${event.max_attendees} attendees` : 'Unlimited attendees';
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

        editSelectedStaffMembers.clear();
        document.getElementById('editSelectedStaff').innerHTML = '';
        document.getElementById('editAssignedStaffIds').value = '';
        

        fetch(`../crud/events/get_event_staff.php?event_id=${event.id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.staff.forEach(staff => {
                        editSelectedStaffMembers.add(staff.id.toString());
                        addEditStaffTag(staff);
                    });
                    document.getElementById('editAssignedStaffIds').value = Array.from(editSelectedStaffMembers).join(',');
                }
            })
            .catch(error => console.error('Error:', error));

        const modal = new bootstrap.Modal(document.getElementById('editEventModal'));
        modal.show();
    };

    function addEditStaffTag(user) {
        const tag = document.createElement('div');
        tag.className = 'user-tag';
        tag.innerHTML = `
            <span>${user.username}</span>
            <span class="remove" onclick="removeEditStaffMember('${user.id}')">&times;</span>
        `;
        document.getElementById('editSelectedStaff').appendChild(tag);
    }


    window.removeEditStaffMember = function(id) {
        const eventId = document.getElementById('editEventForm').elements['id'].value;
        const formData = new FormData();
        formData.append('event_id', eventId);
        formData.append('user_id', id);

        fetch('../crud/events/delete_attendance.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                editSelectedStaffMembers.delete(id.toString());
                const tag = document.querySelector(`#editSelectedStaff .user-tag span[onclick*="${id}"]`).parentNode;
                tag.remove();
                document.getElementById('editAssignedStaffIds').value = Array.from(editSelectedStaffMembers).join(',');
                showAlert('success', 'Staff member and attendance records removed successfully');
            } else {
                throw new Error(data.message || 'Failed to remove staff member');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', error.message || 'An error occurred while removing the staff member');
        });
    };

    document.getElementById('editEventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
     
        const startDate = new Date(this.elements['start_datetime'].value);
        const endDate = new Date(this.elements['end_datetime'].value);
        
        if (startDate > endDate) {
            const endInput = this.elements['end_datetime'];
            endInput.classList.add('is-invalid');
            let feedback = endInput.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                endInput.parentNode.appendChild(feedback);
            }
            feedback.textContent = 'End date must be after start date';
            return; 
        }

        const formData = new FormData(this);
        
        fetch('../crud/events/update_event.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Server response:', text);
                    throw new Error('Invalid server response');
                }
            });
        })
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

    document.getElementById('editStaffSearch').addEventListener('input', handleEditStaffSearch);
    document.getElementById('editStaffSearch').addEventListener('keydown', handleEditStaffKeyboardNavigation);

    document.getElementById('editSelectAllStaff').addEventListener('change', function(e) {
        if (e.target.checked) {
            fetch('../ajax/search_users.php?all=true')
                .then(response => response.json())
                .then(users => {
                    editSelectedStaffMembers.clear();
                    document.getElementById('editSelectedStaff').innerHTML = '';
                    users.forEach(user => {
                        editSelectedStaffMembers.add(user.id.toString());
                        addEditStaffTag(user);
                    });
                    document.getElementById('editAssignedStaffIds').value = Array.from(editSelectedStaffMembers).join(',');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'Error fetching staff members');
                });
        } else {
            editSelectedStaffMembers.clear();
            document.getElementById('editSelectedStaff').innerHTML = '';
            document.getElementById('editAssignedStaffIds').value = '';
        }
    });

    function handleEditStaffSearch(e) {
        const searchTerm = e.target.value.trim();
        const suggestionsBox = document.getElementById('editStaffSearchSuggestions');

        if (searchTerm.length === 0) {
            suggestionsBox.classList.add('d-none');
            return;
        }

        suggestionsBox.innerHTML = '<div class="suggestion-item text-muted">Searching...</div>';
        suggestionsBox.classList.remove('d-none');

        fetch(`../ajax/search_users.php?search=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(users => {
                suggestionsBox.innerHTML = '';
                
                if (users.length === 0) {
                    suggestionsBox.innerHTML = '<div class="suggestion-item text-muted">No staff members found</div>';
                    return;
                }

                users.forEach(user => {
                    if (editSelectedStaffMembers.has(user.id.toString())) return;

                    const div = document.createElement('div');
                    div.className = 'suggestion-item';
                    div.innerHTML = `
                        <div class="suggestion-info">
                            <div><strong>${user.username}</strong></div>
                            ${user.email ? `<small class="text-muted">${user.email}</small>` : ''}
                        </div>
                    `;
                    
                    div.addEventListener('click', () => {
                        if (!editSelectedStaffMembers.has(user.id.toString())) {
                            editSelectedStaffMembers.add(user.id.toString());
                            addEditStaffTag(user);
                            document.getElementById('editAssignedStaffIds').value = Array.from(editSelectedStaffMembers).join(',');
                        }
                        e.target.value = '';
                        suggestionsBox.classList.add('d-none');
                    });
                    
                    suggestionsBox.appendChild(div);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                suggestionsBox.innerHTML = '<div class="suggestion-item text-danger">Error loading suggestions</div>';
            });
    }

    function handleEditStaffKeyboardNavigation(e) {
        const suggestionsBox = document.getElementById('editStaffSearchSuggestions');
        const suggestions = suggestionsBox.querySelectorAll('.suggestion-item');
        
        if (suggestionsBox.classList.contains('d-none') || !suggestions.length) return;

        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                navigateEditStaffSuggestions(1, suggestions);
                break;
            case 'ArrowUp':
                e.preventDefault();
                navigateEditStaffSuggestions(-1, suggestions);
                break;
            case 'Enter':
                e.preventDefault();
                if (currentStaffSuggestionIndex >= 0) {
                    const selectedItem = suggestions[currentStaffSuggestionIndex];
                    const userId = selectedItem.dataset.userId;
                    selectEditStaffMemberById(userId);
                }
                break;
            case 'Escape':
                hideEditStaffSuggestions();
                break;
        }
    }

    function hideEditStaffSuggestions() {
        document.getElementById('editStaffSearchSuggestions').classList.add('d-none');
        currentStaffSuggestionIndex = -1;
    }

    function navigateEditStaffSuggestions(direction, suggestions) {
        suggestions[currentStaffSuggestionIndex]?.classList.remove('keyboard-selected');
        
        currentStaffSuggestionIndex += direction;
        if (currentStaffSuggestionIndex >= suggestions.length) {
            currentStaffSuggestionIndex = 0;
        } else if (currentStaffSuggestionIndex < 0) {
            currentStaffSuggestionIndex = suggestions.length - 1;
        }

        const selectedItem = suggestions[currentStaffSuggestionIndex];
        selectedItem.classList.add('keyboard-selected');
        selectedItem.scrollIntoView({ block: 'nearest' });
    }

    function selectEditStaffMemberById(userId) {
        fetch(`../ajax/search_users.php?search=${userId}`)
            .then(response => response.json())
            .then(results => {
                const user = results.find(u => u.id.toString() === userId);
                if (user) {
                    selectEditStaffMember(user);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function selectEditStaffMember(user) {
        if (editSelectedStaffMembers.has(user.id.toString())) return;
        
        editSelectedStaffMembers.add(user.id.toString());
        addEditStaffTag(user);
        
        document.getElementById('editStaffSearch').value = '';
        hideEditStaffSuggestions();
        document.getElementById('editAssignedStaffIds').value = Array.from(editSelectedStaffMembers).join(',');
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

    window.viewAttendees = function(eventId) {
        fetch(`../crud/events/view_attendees.php?event_id=${eventId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.getElementById('attendeesTableBody');
                    tbody.innerHTML = '';
                    
                    data.data.forEach(day => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${new Date(day.date).toLocaleDateString('en-US', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            })}</td>
                            <td>${day.attendees.length ? day.attendees.join(', ') : 'No attendees'}</td>
                            <td>${day.absentees.length ? day.absentees.join(', ') : 'No absentees'}</td>
                        `;
                        tbody.appendChild(row);
                    });
                    
                    const modal = new bootstrap.Modal(document.getElementById('viewAttendeesModal'));
                    modal.show();
                } else {
                    alert(data.message || 'Error loading attendee data');
                }
            })
            .catch(error => {
                console.error('Error loading attendee data:', error);
                alert('Error loading attendee data');
            });
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

function handleStaffSearch(e) {
    const searchTerm = e.target.value.trim();
    clearTimeout(staffDebounceTimeout);

    if (searchTerm.length === 0) {
        hideStaffSuggestions();
        return;
    }

    const suggestionsBox = document.getElementById('staffSearchSuggestions');
    suggestionsBox.innerHTML = '<div class="suggestion-item text-muted">Searching...</div>';
    suggestionsBox.classList.remove('d-none');

    staffDebounceTimeout = setTimeout(() => {
        fetch(`../ajax/search_users.php?search=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(handleStaffSearchResults)
            .catch(error => {
                console.error('Error:', error);
                showStaffErrorSuggestion();
            });
    }, 300);
}

function handleStaffSearchResults(results) {
    const suggestionsBox = document.getElementById('staffSearchSuggestions');
    
    if (!results.length) {
        suggestionsBox.innerHTML = '<div class="suggestion-item text-muted">No staff members found</div>';
        return;
    }

    suggestionsBox.innerHTML = '';
    currentStaffSuggestionIndex = -1;

    results.forEach((user, index) => {
        if (selectedStaffMembers.has(user.id.toString())) return;

        const div = document.createElement('div');
        div.className = 'suggestion-item';
        div.dataset.index = index;
        div.dataset.userId = user.id;
        
        div.innerHTML = `
            <div class="suggestion-info">
                <div><strong>${user.username}</strong></div>
                ${user.email ? `<small class="text-muted">${user.email}</small>` : ''}
            </div>
        `;
        
        div.addEventListener('click', () => selectStaffMember(user));
        suggestionsBox.appendChild(div);
    });
}

function handleStaffKeyboardNavigation(e) {
    const suggestionsBox = document.getElementById('staffSearchSuggestions');
    const suggestions = suggestionsBox.querySelectorAll('.suggestion-item');
    
    if (suggestionsBox.classList.contains('d-none') || !suggestions.length) return;

    switch(e.key) {
        case 'ArrowDown':
            e.preventDefault();
            navigateStaffSuggestions(1, suggestions);
            break;
        case 'ArrowUp':
            e.preventDefault();
            navigateStaffSuggestions(-1, suggestions);
            break;
        case 'Enter':
            e.preventDefault();
            if (currentStaffSuggestionIndex >= 0) {
                const selectedItem = suggestions[currentStaffSuggestionIndex];
                const userId = selectedItem.dataset.userId;
                selectStaffMemberById(userId);
            }
            break;
        case 'Escape':
            hideStaffSuggestions();
            break;
    }
}

function navigateStaffSuggestions(direction, suggestions) {
    suggestions[currentStaffSuggestionIndex]?.classList.remove('keyboard-selected');
    
    currentStaffSuggestionIndex += direction;
    if (currentStaffSuggestionIndex >= suggestions.length) {
        currentStaffSuggestionIndex = 0;
    } else if (currentStaffSuggestionIndex < 0) {
        currentStaffSuggestionIndex = suggestions.length - 1;
    }

    const selectedItem = suggestions[currentStaffSuggestionIndex];
    selectedItem.classList.add('keyboard-selected');
    selectedItem.scrollIntoView({ block: 'nearest' });
}

function selectStaffMemberById(userId) {
    fetch(`../ajax/search_users.php?search=${userId}`)
        .then(response => response.json())
        .then(results => {
            const user = results.find(u => u.id.toString() === userId);
            if (user) {
                selectStaffMember(user);
            }
        })
        .catch(error => console.error('Error:', error));
}

function selectStaffMember(user) {
    if (selectedStaffMembers.has(user.id.toString())) return;
    
    selectedStaffMembers.add(user.id.toString());
    addStaffTag(user);
    
    document.getElementById('staffSearch').value = '';
    hideStaffSuggestions();
    updateAssignedStaffIds();
}

function addStaffTag(user) {
    const selectedContainer = document.getElementById('selectedStaff');
    
    const tag = document.createElement('div');
    tag.className = 'user-tag';
    tag.innerHTML = `
        <span>${user.username}</span>
        <span class="remove" onclick="removeStaffMember('${user.id}')">&times;</span>
    `;
    
    selectedContainer.appendChild(tag);
}

function removeStaffMember(id) {
    selectedStaffMembers.delete(id.toString());
    const tag = document.querySelector(`#selectedStaff .user-tag span[onclick*="${id}"]`).parentNode;
    tag.remove();
    updateAssignedStaffIds();
}

function updateAssignedStaffIds() {
    document.getElementById('assignedStaffIds').value = Array.from(selectedStaffMembers).join(',');
}

function hideStaffSuggestions() {
    document.getElementById('staffSearchSuggestions').classList.add('d-none');
    currentStaffSuggestionIndex = -1;
}

function showStaffErrorSuggestion() {
    const suggestionsBox = document.getElementById('staffSearchSuggestions');
    suggestionsBox.innerHTML = '<div class="suggestion-item text-danger">Error loading suggestions</div>';
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('staffSearch');
    const suggestionsBox = document.getElementById('staffSearchSuggestions');

    searchInput.addEventListener('input', handleStaffSearch);
    searchInput.addEventListener('keydown', handleStaffKeyboardNavigation);

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            hideStaffSuggestions();
        }
    });

    document.getElementById('selectAllStaff').addEventListener('change', function(e) {
        if (e.target.checked) {
            fetch('../ajax/search_users.php?all=true')
                .then(response => response.json())
                .then(users => {
                    selectedStaffMembers.clear();
                    document.getElementById('selectedStaff').innerHTML = '';
                    users.forEach(user => {
                        selectedStaffMembers.add(user.id.toString());
                        addStaffTag(user);
                    });
                    updateAssignedStaffIds();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching staff members');
                });
        } else {
            selectedStaffMembers.clear();
            document.getElementById('selectedStaff').innerHTML = '';
            updateAssignedStaffIds();
        }
    });
});

function showAttendanceModal(eventId) {
    fetch(`../crud/events/get_event_attendance.php?event_id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.getElementById('attendanceModal');
                document.getElementById('eventTitle').textContent = data.event.title;
                
                const tbody = document.getElementById('attendanceTableBody');
                tbody.innerHTML = '';

                if (!data.members || data.members.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center">No assigned members found for this event</td></tr>';
                    return;
                }

                data.members.forEach(member => {
                    const row = document.createElement('tr');
                    row.dataset.memberId = member.id; 
                    row.innerHTML = `
                        <td>${member.first_name} ${member.last_name}</td>
                        <td class="text-center">
                            <button class="btn btn-sm attendance-btn present-btn ${member.attendance_status === 'present' ? 'btn-success' : 'btn-outline-success'}"
                                onclick="markAttendance(${eventId}, ${member.id}, 'present', this)">
                                <i class="bi bi-check-circle"></i>
                            </button>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm attendance-btn absent-btn ${member.attendance_status === 'absent' ? 'btn-danger' : 'btn-outline-danger'}"
                                onclick="markAttendance(${eventId}, ${member.id}, 'absent', this)">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            } else {
                showAlert('danger', data.message || 'Failed to load attendance data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred while loading attendance data');
        });
}

function markAttendance(eventId, memberId, status, buttonElement) {
    const formData = new FormData();
    formData.append('event_id', eventId);
    formData.append('member_id', memberId);
    formData.append('status', status);

    fetch('../crud/events/mark_attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
         
            const row = buttonElement.closest('tr');
            const presentBtn = row.querySelector('.present-btn');
            const absentBtn = row.querySelector('.absent-btn');
            
            if (status === 'present') {
                presentBtn.classList.remove('btn-outline-success');
                presentBtn.classList.add('btn-success');
                absentBtn.classList.remove('btn-danger');
                absentBtn.classList.add('btn-outline-danger');
            } else {
                presentBtn.classList.remove('btn-success');
                presentBtn.classList.add('btn-outline-success');
                absentBtn.classList.remove('btn-outline-danger');
                absentBtn.classList.add('btn-danger');
            }
            
            showAlert('success', 'Attendance marked successfully');
        } else {
            showAlert('danger', data.message || 'Failed to mark attendance');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while marking attendance');
    });
}
</script>

<style>
.search-container {
    position: relative;
}
.suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
}
.suggestion-item {
    padding: 8px 12px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f0f0f0;
}
.suggestion-item:last-child {
    border-bottom: none;
}
.suggestion-item:hover {
    background-color: #f8f9fa;
}
.suggestion-item.keyboard-selected {
    background-color: #e9ecef;
}
.selected-users {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.user-tag {
    background: #e9ecef;
    border-radius: 16px;
    padding: 4px 12px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.875rem;
}
.user-tag .remove {
    cursor: pointer;
    color: #dc3545;
    font-weight: bold;
    padding: 0 4px;
}
.sortable {
    cursor: pointer;
    user-select: none;
}
.sortable:hover {
    background-color: #f8f9fa;
}
.sortable i {
    opacity: 0.5;
}
.sortable.asc i, .sortable.desc i {
    opacity: 1;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

#ongoingEventsTableBody tr {
    transition: all 0.3s ease;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,.1);
}
</style>

<?php require_once '../templates/admin_footer.php'; ?>