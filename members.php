<?php require_once 'templates/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Church Members</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchMembers" placeholder="Search members...">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body d-flex gap-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <select class="form-select" id="sortBy">
                        <option value="name">Sort by Name</option>
                        <option value="date">Sort by Join Date</option>
                        <option value="status">Sort by Status</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Members List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Membership Date</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody id="membersTableBody">
                                <!-- Table content will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                    <nav aria-label="Members pagination" class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Showing <span id="showing">0</span> of <span id="total">0</span> members
                        </div>
                        <ul class="pagination mb-0">
                            <!-- Pagination will be added dynamically -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    loadMembers();
    
    // Add event listeners for search and filters
    document.getElementById('searchMembers').addEventListener('input', debounce(loadMembers, 300));
    document.getElementById('statusFilter').addEventListener('change', loadMembers);
    document.getElementById('sortBy').addEventListener('change', loadMembers);
    
    // Handle form submission
    document.getElementById('addMemberForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('crud/members/create_member.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addMemberModal')).hide();
                loadMembers();
                this.reset();
            } else {
                alert(data.message || 'Error adding member');
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

function loadMembers(page = 1) {
    const search = document.getElementById('searchMembers').value;
    const status = document.getElementById('statusFilter').value;
    const sort = document.getElementById('sortBy').value;
    
    fetch(`crud/members/read_members.php?page=${page}&search=${search}&status=${status}&sort=${sort}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('membersTableBody');
            tbody.innerHTML = '';
            
            data.members.forEach(member => {
                const row = `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="fw-bold">${member.first_name} ${member.last_name}</div>
                                    <small class="text-muted">#${member.id}</small>
                                </div>
                            </div>
                        </td>
                        <td>${member.email || '-'}</td>
                        <td>${member.phone || '-'}</td>
                        <td>${formatDate(member.membership_date)}</td>
                        <td>
                            <span class="badge bg-${member.status === 'active' ? 'success' : 'danger'}">
                                ${member.status}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="viewPastoralCare(${member.id})">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
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
    
    // Previous button
    pagination.insertAdjacentHTML('beforeend', `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadMembers(${currentPage - 1})">Previous</a>
        </li>
    `);
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        pagination.insertAdjacentHTML('beforeend', `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadMembers(${i})">${i}</a>
            </li>
        `);
    }
    
    // Next button
    pagination.insertAdjacentHTML('beforeend', `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadMembers(${currentPage + 1})">Next</a>
        </li>
    `);
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
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

function viewPastoralCare(memberId) {
    fetch(`crud/pastoral_care/get_pastoral_care.php?member_id=${memberId}`)
        .then(response => response.json())
        .then(data => {
            let content = `<div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>`;
            
            if (data.length > 0) {
                data.forEach(care => {
                    content += `
                        <tr>
                            <td>${formatDate(care.care_date)}</td>
                            <td>${care.care_type}</td>
                            <td>${care.notes}</td>
                        </tr>`;
                });
            } else {
                content += `<tr><td colspan="3" class="text-center">No pastoral care records found</td></tr>`;
            }
            
            content += `</tbody></table></div>`;

            // Create and show modal
            const modal = new bootstrap.Modal(document.createElement('div'));
            modal.element.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Pastoral Care History</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">${content}</div>
                    </div>
                </div>`;
            modal.element.classList.add('modal', 'fade');
            document.body.appendChild(modal.element);
            modal.show();
        })
        .catch(error => console.error('Error:', error));
}
</script>

<?php require_once 'templates/footer.php'; ?>