<?php require_once '../templates/admin_header.php'; ?>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Total Donations</h6>
                <h2 class="card-title mb-0" id="totalAmount">₱0.00</h2>
                <small>All time</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Monthly Average</h6>
                <h2 class="card-title mb-0" id="monthlyAverage">₱0.00</h2>
                <small>Per month</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Donation Count</h6>
                <h2 class="card-title mb-0" id="donationCount">0</h2>
                <small>Total transactions</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Unique Donors</h6>
                <h2 class="card-title mb-0" id="uniqueDonors">0</h2>
                <small>Individual contributors</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Donations Overview</h5>
                <canvas id="donationsChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Donation Types</h5>
                <canvas id="donationTypesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchDonations" placeholder="Search donations...">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="tithe">Tithe</option>
                            <option value="offering">Offering</option>
                            <option value="project">Project</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="month" class="form-control" id="monthFilter">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#addDonationModal">
                    <i class="bi bi-plus-lg"></i> Add Donation
                </button>
                <button class="btn btn-outline-primary" id="exportBtn">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Donations Table -->
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
                        <th>Payment Method</th>
                        <th>Status</th>
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

<!-- Add Donation Modal -->
<div class="modal fade" id="addDonationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Donation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addDonationForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Member</label>
                            <select class="form-select" name="member_id" required>
                                <option value="">Select Member</option>
                                <!-- Members will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Donation Type</label>
                            <select class="form-select" name="type" required>
                                <option value="tithe">Tithe</option>
                                <option value="offering">Offering</option>
                                <option value="project">Project</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="amount" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="donation_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="check">Check</option>
                                <option value="online">Online Payment</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Receipt Image</label>
                            <input type="file" class="form-control" name="receipt_image" accept="image/*">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addDonationForm" class="btn btn-primary">Add Donation</button>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDonations();
    loadStats();
    loadMembers();
    initializeCharts();
    initializeMonthFilter();
    
    // Add event listeners
    document.getElementById('searchDonations').addEventListener('input', debounce(loadDonations, 300));
    document.getElementById('typeFilter').addEventListener('change', loadDonations);
    document.getElementById('monthFilter').addEventListener('change', loadDonations);
    
    // Handle form submission
    document.getElementById('addDonationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('../crud/donations/create_donation.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addDonationModal')).hide();
                loadDonations();
                loadStats();
                updateCharts();
                this.reset();
            } else {
                alert(data.message || 'Error adding donation');
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

function initializeMonthFilter() {
    const now = new Date();
    document.getElementById('monthFilter').value = 
        now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
}

function loadStats() {
    fetch('../crud/donations/get_stats.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalAmount').textContent = formatCurrency(data.total);
            document.getElementById('monthlyAverage').textContent = formatCurrency(data.monthlyAverage);
            document.getElementById('donationCount').textContent = data.count;
            document.getElementById('uniqueDonors').textContent = data.uniqueDonors;
        })
        .catch(error => console.error('Error:', error));
}

function loadMembers() {
    fetch('../crud/members/read_members.php?active=true')
        .then(response => response.json())
        .then(data => {
            const select = document.querySelector('[name="member_id"]');
            data.members.forEach(member => {
                const option = document.createElement('option');
                option.value = member.id;
                option.textContent = `${member.first_name} ${member.last_name}`;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error:', error));
}

function loadDonations(page = 1) {
    const search = document.getElementById('searchDonations').value;
    const type = document.getElementById('typeFilter').value;
    const month = document.getElementById('monthFilter').value;
    
    fetch(`../crud/donations/read_donations.php?page=${page}&search=${search}&type=${type}&month=${month}`)
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
                                    <div class="fw-bold">${donation.donor_name}</div>
                                    <small class="text-muted">#${donation.member_id}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-${getTypeBadge(donation.type)}">
                                ${capitalizeFirst(donation.type)}
                            </span>
                        </td>
                        <td>${formatCurrency(donation.amount)}</td>
                        <td>${formatDate(donation.donation_date)}</td>
                        <td>${capitalizeFirst(donation.payment_method)}</td>
                        <td>
                            <span class="badge bg-${getStatusBadge(donation.status)}">
                                ${capitalizeFirst(donation.status)}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" onclick="viewReceipt(${donation.id})">
                                    <i class="bi bi-receipt"></i>
                                </button>
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

function initializeCharts() {
    // Donations Overview Chart
    const donationsCtx = document.getElementById('donationsChart').getContext('2d');
    window.donationsChart = new Chart(donationsCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Donations',
                data: [],
                borderColor: '#6a1b9a',
                backgroundColor: 'rgba(106, 27, 154, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => '₱' + value.toLocaleString()
                    }
                }
            }
        }
    });

    // Donation Types Chart
    const typesCtx = document.getElementById('donationTypesChart').getContext('2d');
    window.donationTypesChart = new Chart(typesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Tithe', 'Offering', 'Project', 'Other'],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#6a1b9a',
                    '#9c27b0',
                    '#ba68c8',
                    '#e1bee7'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    updateCharts();
}

function updateCharts() {
    const month = document.getElementById('monthFilter').value;
    
    fetch(`../crud/donations/get_charts_data.php?month=${month}`)
        .then(response => response.json())
        .then(data => {
            // Update Overview Chart
            window.donationsChart.data.labels = data.overview.labels;
            window.donationsChart.data.datasets[0].data = data.overview.values;
            window.donationsChart.update();

            // Update Types Chart
            window.donationTypesChart.data.datasets[0].data = data.types.values;
            window.donationTypesChart.update();
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

function getTypeBadge(type) {
    const badges = {
        tithe: 'primary',
        offering: 'success',
        project: 'info',
        other: 'secondary'
    };
    return badges[type] || 'secondary';
}

function getStatusBadge(status) {
    const badges = {
        completed: 'success',
        pending: 'warning',
        failed: 'danger'
    };
    return badges[status] || 'secondary';
}

function formatCurrency(number) {
    return '₱' + parseFloat(number).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
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

<?php require_once '../templates/admin_footer.php'; ?>