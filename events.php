<?php 
require_once 'templates/header.php';
require_once 'db/connection.php';
require_once 'auth/login_status.php';

$now = date('Y-m-d H:i:s');


$upcomingStmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE start_datetime > ? AND status = 'upcoming'");
$upcomingStmt->execute([$now]);
$upcomingCount = $upcomingStmt->fetch()['count'];


$ongoingStmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE start_datetime <= ? AND end_datetime >= ? AND status = 'ongoing'");
$ongoingStmt->execute([$now, $now]);
$ongoingCount = $ongoingStmt->fetch()['count'];

$totalStmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE status != 'cancelled'");
$totalStmt->execute();
$totalCount = $totalStmt->fetch()['count'];
?>

<div class="container-fluid py-4">

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <h2 class="display-4 mb-2"><?php echo $upcomingCount; ?></h2>
                    <h6 class="card-subtitle mb-1">Upcoming Events</h6>
                    <small>Events starting after today</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <h2 class="display-4 mb-2"><?php echo $ongoingCount; ?></h2>
                    <h6 class="card-subtitle mb-1">Ongoing Events</h6>
                    <small>Events in progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body text-center">
                    <h2 class="display-4 mb-2"><?php echo $totalCount; ?></h2>
                    <h6 class="card-subtitle mb-1">Total Events</h6>
                    <small>All non-cancelled events</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchEvents" placeholder="Search events...">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="date" class="form-control" id="dateFilter">
                                <button class="btn btn-outline-primary" id="clearDate">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row g-4" id="eventsGrid">
    </div>

    <div class="modal fade" id="eventDescriptionModal" tabindex="-1" aria-labelledby="eventDescriptionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventDescriptionModalLabel">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="eventDescriptionModalBody">
                    <div class="row">
                        <div class="col-12 mb-4 text-center event-image-container">
                        </div>
                        <div class="col-12 event-details">
                        <!--CONTENTS -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

   
    <nav aria-label="Events pagination" class="mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted">
                Showing <span id="showing">0</span> of <span id="total">0</span> events
            </div>
            <ul class="pagination mb-0" id="eventsPagination">
   
            </ul>
        </div>
    </nav>
</div>


<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addEventForm">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Event Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="event_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Time</label>
                            <input type="time" class="form-control" name="event_time" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Event Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addEventForm" class="btn btn-primary">Add Event</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    loadEvents();
    

    setTimeout(checkAttendanceStatus, 500);
    

    setInterval(checkAttendanceStatus, 15000);
    
    document.getElementById('searchEvents').addEventListener('input', debounce(() => {
        loadEvents();
    }, 300));
    
    document.getElementById('statusFilter').addEventListener('change', () => {
        loadEvents();
    });
    
    document.getElementById('dateFilter').addEventListener('change', () => {
        loadEvents();
    });
    
    document.getElementById('clearDate').addEventListener('click', function() {
        document.getElementById('dateFilter').value = '';
        loadEvents();
    });

    document.getElementById('addEventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('crud/events/create_event.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addEventModal')).hide();
                loadEvents();
                this.reset();
            } else {
                alert(data.message || 'Error adding event');
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

function toggleAttendance(eventId) {
    fetch('crud/events/toggle_attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ event_id: eventId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const button = document.querySelector(`.attend-btn[data-event-id="${eventId}"]`);
            if (button) {
                if (data.status === 'present') {
                    button.classList.remove('btn-outline-success');
                    button.classList.add('btn-success');
                    button.innerHTML = '<i class="bi bi-person-check-fill"></i> Present - Click Again to Mark Absence';
                } else {
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-success');
                    button.innerHTML = '<i class="bi bi-person-check"></i> Mark Attendance';
                }
            }
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update attendance');
    });
}

function checkAttendanceStatus() {

    const attendanceButtons = document.querySelectorAll('.attend-btn[data-event-id]');
    attendanceButtons.forEach(button => {
        const eventId = button.dataset.eventId;
        fetch(`crud/events/get_attendance_status.php?event_id=${eventId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.status === 'present') {
                        button.classList.remove('btn-outline-success');
                        button.classList.add('btn-success');
                        button.innerHTML = '<i class="bi bi-person-check-fill"></i> Present - Click Again to Mark Absence';
                    } else {
                        button.classList.remove('btn-success');
                        button.classList.add('btn-outline-success');
                        button.innerHTML = '<i class="bi bi-person-check"></i> Mark Attendance';
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    });
}

function loadEvents(page = 1) {
    const search = document.getElementById('searchEvents').value;
    const status = document.getElementById('statusFilter').value;
    const date = document.getElementById('dateFilter').value;
    
    fetch(`crud/events/read_events.php?page=${page}&search=${search}&status=${status}&date=${date}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.log('Raw response:', text);
                    throw new Error('Failed to parse JSON response');
                }
            });
        })
        .then(data => {
            const grid = document.getElementById('eventsGrid');
            grid.innerHTML = '';
            
            if (!data.success) {
                throw new Error(data.message || 'Server returned error');
            }
            
            if (!data.events || data.events.length === 0) {
                grid.innerHTML = '<div class="col-12"><div class="alert alert-info">No events found.</div></div>';
                return;
            }
            
            data.events.forEach(event => {
                const attendanceBtn = event.status === 'ongoing' 
                    ? `<button class="btn ${event.attendance_status === 'present' ? 'btn-success' : 'btn-outline-success'} attend-btn" 
                            onclick="toggleAttendance(${event.id})" 
                            data-event-id="${event.id}"
                            title="${event.attendance_status === 'present' ? 'Click to mark as absent' : 'Mark your attendance'}">
                        <i class="bi bi-person-check${event.attendance_status === 'present' ? '-fill' : ''}"></i> 
                        ${event.attendance_status === 'present' ? 'Present - Click Again to Mark Absence' : 'Mark Attendance'}
                       </button>`
                    : `<button class="btn btn-outline-secondary" disabled>
                        <i class="bi bi-${event.status === 'completed' ? 'person-check' : 'clock'}"></i> 
                        ${event.status === 'completed' ? 'Event Completed' : 'Not Started'}
                       </button>`;

                const card = `
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            ${event.image ? 
                                `<img src="data:image/jpeg;base64,${event.image}" class="card-img-top" alt="${event.title}" style="height: 200px; object-fit: cover;">` :
                                `<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="bi bi-calendar-event display-4"></i>
                                </div>`
                            }
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0">${event.title}</h5>
                                    <span class="badge bg-${getStatusBadge(event.status)}">
                                        ${capitalizeFirst(event.status)}
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-info">${capitalizeFirst(event.event_type)}</span>
                                </div>
                                <p class="card-text text-truncate">${event.description || 'No description available.'}</p>
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3"></i> ${new Date(event.start_datetime).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                                        <br>
                                        <i class="bi bi-clock"></i> ${new Date(event.start_datetime).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })} - 
                                        ${new Date(event.end_datetime).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}
                                        <br>
                                        <i class="bi bi-geo-alt"></i> ${event.location || 'Location TBA'}
                                        ${event.max_attendees ? `<br><i class="bi bi-people"></i> Max Attendees: ${event.max_attendees}` : ''}
                                    </small>
                                </div>
                                <div class="btn-group w-100">
                                    ${attendanceBtn}
                                    <button class="btn btn-outline-primary" onclick="viewAttendance(${event.id})" title="View Attendance">
                                        <i class="bi bi-people"></i>
                                    </button>
                                    <button class="btn btn-outline-primary" onclick="viewDescription(${event.id})" title="View Details">
                                        <i class="bi bi-info-circle"></i>
                                    </button>
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
        .catch(error => {
            console.error('Error loading events:', error);
            const grid = document.getElementById('eventsGrid');
            grid.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error loading events. Please try again.</div></div>';
        });
}

function updatePagination(currentPage, totalPages) {
    const pagination = document.getElementById('eventsPagination');
    pagination.innerHTML = '';
    
    pagination.insertAdjacentHTML('beforeend', `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadEvents(${currentPage - 1})">Previous</a>
        </li>
    `);
    
    for (let i = 1; i <= totalPages; i++) {
        pagination.insertAdjacentHTML('beforeend', `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadEvents(${i})">${i}</a>
            </li>
        `);
    }
    
    pagination.insertAdjacentHTML('beforeend', `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadEvents(${currentPage + 1})">Next</a>
        </li>
    `);
}

function getStatusBadge(status) {
    const badges = {
        upcoming: 'primary',
        ongoing: 'success',
        completed: 'secondary',
        cancelled: 'danger'
    };
    return badges[status] || 'secondary';
}

function formatDate(dateString) {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function formatTime(timeString) {
    return new Date(`2000-01-01T${timeString}`).toLocaleTimeString(undefined, {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
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

function viewDescription(eventId) {
    fetch(`crud/events/read_event.php?id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const event = data;
                const modalBody = document.getElementById('eventDescriptionModalBody');
                modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-12 mb-4 text-center event-image-container">
                            ${event.image ? `<img src="data:image/jpeg;base64,${event.image}" class="img-fluid" alt="${event.title}">` : ''}
                        </div>
                        <div class="col-12 event-details">
                            <h5>${event.title}</h5>
                            <p>${event.description || 'No description available.'}</p>
                            <p><strong>Date & Time:</strong><br>
                            Start: ${new Date(event.start_datetime).toLocaleString()}<br>
                            End: ${new Date(event.end_datetime).toLocaleString()}</p>
                            <p><strong>Location:</strong> ${event.location || 'Location TBA'}</p>
                            <p><strong>Type:</strong> ${capitalizeFirst(event.event_type)}</p>
                            ${event.max_attendees ? `<p><strong>Maximum Attendees:</strong> ${event.max_attendees}</p>` : ''}
                        </div>
                    </div>
                `;
                const modal = new bootstrap.Modal(document.getElementById('eventDescriptionModal'));
                modal.show();
            } else {
                alert('Error loading event details: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function viewAttendance(eventId) {
    fetch(`crud/events/view_attendance.php?event_id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let content = `
                    <h6>Event: ${data.event.title}</h6>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Attendance Status</th>
                                </tr>
                            </thead>
                            <tbody>`;
                
                if (data.attendance_dates && data.attendance_dates.length > 0) {
                    data.attendance_dates.forEach(record => {
                        let statusBadge = '';
                        switch(record.status) {
                            case 'present':
                                statusBadge = '<span class="badge bg-success">Present</span>';
                                break;
                            case 'absent':
                                statusBadge = '<span class="badge bg-danger">Absent</span>';
                                break;
                            case 'upcoming':
                                statusBadge = '<span class="badge bg-info">Upcoming</span>';
                                break;
                            default:
                                statusBadge = `<span class="badge bg-secondary">${record.status}</span>`;
                        }
                        
                        content += `
                            <tr>
                                <td>${new Date(record.date).toLocaleDateString('en-US', { 
                                    weekday: 'long', 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric' 
                                })}</td>
                                <td>${statusBadge}</td>
                            </tr>`;
                    });
                } else {
                    content += '<tr><td colspan="2" class="text-center">No attendance records found</td></tr>';
                }
                
                content += `
                            </tbody>
                        </table>
                    </div>`;

                const modalDiv = document.createElement('div');
                modalDiv.className = 'modal fade';
                modalDiv.innerHTML = `
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Attendance Records</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">${content}</div>
                        </div>
                    </div>`;
                document.body.appendChild(modalDiv);
                
                const modal = new bootstrap.Modal(modalDiv);
                modalDiv.addEventListener('hidden.bs.modal', function() {
                    document.body.removeChild(modalDiv);
                });
                modal.show();
            } else {
                alert(data.message || 'Error loading attendance records');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading attendance records');
        });
}


document.addEventListener('DOMContentLoaded', function() {
    checkAttendanceStatus();
    setInterval(checkAttendanceStatus, 15000); 
});
</script>

<?php require_once 'templates/footer.php'; ?>