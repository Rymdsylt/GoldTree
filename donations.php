<?php require_once 'templates/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Donations</h6>
                    <h2 class="card-title mb-0" id="totalDonations">₱0.00</h2>
                    <small>All time</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Monthly Donations</h6>
                    <h2 class="card-title mb-0" id="monthlyDonations">₱0.00</h2>
                    <small>This month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Donors</h6>
                    <h2 class="card-title mb-0" id="totalDonors">0</h2>
                    <small>Unique contributors</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Average Donation</h6>
                    <h2 class="card-title mb-0" id="averageDonation">₱0.00</h2>
                    <small>Per transaction</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchDonor" placeholder="Search donor...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="typeFilter">
                                <option value="">All Types</option>
                                <option value="tithe">Tithe</option>
                                <option value="offering">Offering</option>
                                <option value="project">Project</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="date" class="form-control" id="startDate">
                                <span class="input-group-text bg-light">to</span>
                                <input type="date" class="form-control" id="endDate">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Donations List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Donor</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="donationsTableBody">
                                <!-- Table content will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                    <nav aria-label="Donations pagination" class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Showing <span id="showing">0</span> of <span id="total">0</span> donations
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

<!-- Add Donation Modal -->
<div class="modal fade" id="addDonationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Donation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addDonationForm">
                    <div class="mb-3">
                        <label class="form-label">Member</label>
                        <select class="form-select" name="member_id" required>
                            <option value="">Select Member</option>
                            <!-- Members will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="amount" step="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="donation_type" required>
                            <option value="tithe">Tithe</option>
                            <option value="offering">Offering</option>
                            <option value="project">Project</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="donation_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancel</button>
            
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDonations();
    loadMembers();
    loadStats();
    
    // Add event listeners
    const filters = ['searchDonor', 'typeFilter', 'startDate', 'endDate'];
    filters.forEach(id => {
        document.getElementById(id).addEventListener('change', loadDonations);
    });
    document.getElementById('searchDonor').addEventListener('input', debounce(loadDonations, 300));
    
    // Handle form submission
    document.getElementById('addDonationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('crud/donations/create_donation.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addDonationModal')).hide();
                loadDonations();
                loadStats();
                this.reset();
            } else {
                alert(data.message || 'Error adding donation');
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

function loadStats() {
    fetch('crud/donations/get_filtered_donations.php?stats=true')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalDonations').textContent = formatCurrency(data.totalDonations);
            document.getElementById('monthlyDonations').textContent = formatCurrency(data.monthlyDonations);
            document.getElementById('totalDonors').textContent = data.totalDonors;
            document.getElementById('averageDonation').textContent = formatCurrency(data.averageDonation);
        })
        .catch(error => console.error('Error:', error));
}

function loadDonations(page = 1) {
    const search = document.getElementById('searchDonor').value;
    const type = document.getElementById('typeFilter').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    fetch(`crud/donations/read_donations.php?page=${page}&search=${search}&type=${type}&start=${startDate}&end=${endDate}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('donationsTableBody');
            tbody.innerHTML = '';
            
            data.donations.forEach(donation => {
                const row = `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="fw-bold">${donation.first_name} ${donation.last_name}</div>
                                    <small class="text-muted">#${donation.member_id}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-${getDonationTypeBadge(donation.donation_type)}">
                                ${capitalizeFirst(donation.donation_type)}
                            </span>
                        </td>
                        <td>₱${formatNumber(donation.amount)}</td>
                        <td>${formatDate(donation.donation_date)}</td>
                        <td>${donation.notes || '-'}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" onclick="editDonation(${donation.id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteDonation(${donation.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
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

function loadMembers() {
    fetch('crud/members/read_members.php?all=true')
        .then(response => response.json())
        .then(data => {
            const select = document.querySelector('select[name="member_id"]');
            select.innerHTML = '<option value="">Select Member</option>';
            
            data.members.forEach(member => {
                const option = `
                    <option value="${member.id}">
                        ${member.first_name} ${member.last_name}
                    </option>
                `;
                select.insertAdjacentHTML('beforeend', option);
            });
        })
        .catch(error => console.error('Error:', error));
}

function updatePagination(currentPage, totalPages) {
    const pagination = document.querySelector('.pagination');
    pagination.innerHTML = '';
    
    pagination.insertAdjacentHTML('beforeend', `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadDonations(${currentPage - 1})">Previous</a>
        </li>
    `);
    
    for (let i = 1; i <= totalPages; i++) {
        pagination.insertAdjacentHTML('beforeend', `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadDonations(${i})">${i}</a>
            </li>
        `);
    }
    
    pagination.insertAdjacentHTML('beforeend', `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadDonations(${currentPage + 1})">Next</a>
        </li>
    `);
}

function getDonationTypeBadge(type) {
    const badges = {
        tithe: 'success',
        offering: 'info',
        project: 'warning',
        other: 'secondary'
    };
    return badges[type] || 'secondary';
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function formatNumber(number) {
    return parseFloat(number).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatCurrency(number) {
    return '₱' + formatNumber(number);
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
</script>

<?php require_once 'templates/footer.php'; ?>