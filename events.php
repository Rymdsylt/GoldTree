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
function loadEvents(page = 1) {
    // First update event statuses
    fetch('cron/update_event_status.php')
        .then(response => response.json())
        .catch(error => console.error('Error updating event statuses:', error));

    // Then load events as normal
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
                                ${event.assigned_staff ? 
                                    `<p class="small text-muted mb-2">
                                        <i class="bi bi-people"></i> Assigned Staff: ${event.assigned_staff}
                                    </p>` : ''
                                }
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3"></i> ${new Date(event.start_datetime).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                                        <br>
                                        <i class="bi bi-clock"></i> ${new Date(event.start_datetime).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })} - 
                                        ${new Date(event.end_datetime).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}
                                        <br>
                                        <i class="bi bi-geo-alt"></i> ${event.location || 'Location TBA'}
                                    </small>
                                </div>
                                <div class="btn-group w-100">
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

document.addEventListener('DOMContentLoaded', function() {
    // Initial load
    loadEvents();
    
    // Update event statuses every minute
    setInterval(() => {
        loadEvents();
    }, 60000);
    
    loadEvents();

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
</script>

<?php require_once 'templates/footer.php'; ?>