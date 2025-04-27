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
                          
                            </tbody>
                        </table>
                    </div>
                    <nav aria-label="Members pagination" class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Showing <span id="showing">0</span> of <span id="total">0</span> members
                        </div>
                        <ul class="pagination mb-0">
                           
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
    
    document.getElementById('searchMembers').addEventListener('input', debounce(loadMembers, 300));
    document.getElementById('statusFilter').addEventListener('change', loadMembers);
    document.getElementById('sortBy').addEventListener('change', loadMembers);
});

function loadMembers(page = 1) {
    const search = document.getElementById('searchMembers').value;
    const status = document.getElementById('statusFilter').value;
    const sort = document.getElementById('sortBy').value;
    
    fetch(`crud/members/read_members.php?page=${page}&search=${search}&status=${status}&sort=${sort}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Error loading members');
            }

            const tbody = document.getElementById('membersTableBody');
            tbody.innerHTML = '';
            
            data.members.forEach(member => {
                const profileImage = member.profile_image 
                    ? `<img src="data:image/jpeg;base64,${member.profile_image}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">` 
                    : `<div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-person"></i>
                       </div>`;

                const row = `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                ${profileImage}
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
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading members: ' + error.message);
        });
}

function updatePagination(currentPage, totalPages) {
    const pagination = document.querySelector('.pagination');
    pagination.innerHTML = '';
    
    if (totalPages <= 1) return;


    pagination.insertAdjacentHTML('beforeend', `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="loadMembers(${currentPage - 1})">Previous</a>
        </li>
    `);
    

    for (let i = 1; i <= totalPages; i++) {
        if (
            i === 1 || 
            i === totalPages || 
            (i >= currentPage - 1 && i <= currentPage + 1) 
        ) {
            pagination.insertAdjacentHTML('beforeend', `
                <li class="page-item ${currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="loadMembers(${i})">${i}</a>
                </li>
            `);
        } else if (
            i === 2 || 
            i === totalPages - 1
        ) {
            pagination.insertAdjacentHTML('beforeend', `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            `);
        }
    }
    
    pagination.insertAdjacentHTML('beforeend', `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="loadMembers(${currentPage + 1})">Next</a>
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
    fetch(`ajax/get_pastoral_care.php?member_id=${memberId}`)
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
            
            if (Array.isArray(data) && data.length > 0) {
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

            const modalDiv = document.createElement('div');
            modalDiv.className = 'modal fade';
            modalDiv.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Pastoral Care History</h5>
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
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading pastoral care records');
        });
}
</script>

<?php require_once 'templates/footer.php'; ?>