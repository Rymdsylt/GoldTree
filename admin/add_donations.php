<?php
session_start();
require_once '../templates/admin_header.php';
?>

<style>
.chart-container {
    height: 400px;
    position: relative;
}
</style>

<div class="container-fluid py-4">
     <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex flex-column">
                        <h6 class="card-subtitle mb-2">All Time Donations</h6>
                        <h2 class="card-title mb-0" id="allTimeDonations">₱0.00</h2>
                        <small class="text-muted">Total donations received</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex flex-column">
                        <h6 class="card-subtitle mb-2">Today's Donations</h6>
                        <h2 class="card-title mb-0" id="todayDonations">₱0.00</h2>
                        <small class="text-muted">Today's total</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex flex-column">
                        <h6 class="card-subtitle mb-2">This Week</h6>
                        <h2 class="card-title mb-0" id="weekDonations">₱0.00</h2>
                        <small class="text-muted">Week to date</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex flex-column">
                        <h6 class="card-subtitle mb-2">This Month</h6>
                        <h2 class="card-title mb-0" id="monthDonations">₱0.00</h2>
                        <small class="text-muted">Month to date</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex flex-column">
                        <h6 class="card-subtitle mb-2">This Year</h6>
                        <h2 class="card-title mb-0" id="yearDonations">₱0.00</h2>
                        <small class="text-muted">Year to date</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <h6 class="card-subtitle mb-2">Custom Range</h6>
                            <h2 class="card-title mb-0" id="totalDonations">₱0.00</h2>
                        </div>
                        <div class="input-group" style="width: auto;">
                            <input type="date" class="form-control" id="startDate">
                            <span class="input-group-text bg-light">to</span>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                    </div>
                    <button class="btn btn-light btn-sm" id="applyDateRange">
                        <i class="bi bi-check-lg"></i> Apply Filter
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Export Options</h5>
                        <button class="btn btn-primary" onclick="exportDonations()">
                            <i class="bi bi-download me-2"></i> Export Donations Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

   
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Donations Overview</h5>
                    <div class="chart-container">
                        <canvas id="donationsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Donation Types Distribution</h5>
                    <div class="chart-container">
                        <canvas id="donationTypesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Donation Ledger</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDonationModal">
                    <i class="bi bi-plus-lg"></i> Add Donation
                </button>
            </div>

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
                    </tbody>
                </table>
            </div>
            <nav aria-label="Donations pagination" class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Showing <span id="showing">0</span> of <span id="total">0</span> donations
                </div>
                <ul class="pagination mb-0">
                </ul>
            </nav>
        </div>
    </div>

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
                            <label class="form-label">Donor Type</label>
                            <select class="form-select" name="donor_type" id="donorType" required>
                                <option value="member">Member</option>
                                <option value="non-member">Non-Member</option>
                            </select>
                        </div>
                        <div class="mb-3" id="memberSelect">
                            <label class="form-label">Member</label>
                            <select class="form-select" name="member_id">
                                <option value="">Select Member</option>
                            </select>
                        </div>
                        <div class="mb-3" id="nonMemberName" style="display:none;">
                            <label class="form-label">Donor Name</label>
                            <input type="text" class="form-control" name="donor_name" placeholder="Name of the donor (optional)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Donation Type</label>
                            <select class="form-select" name="donation_type" required>
                                <option value="tithe">Tithe</option>
                                <option value="offering">Offering</option>
                                <option value="project">Project</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="amount" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="donation_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">                                <input type="checkbox" class="form-check-input" id="sendNotification" name="send_notification">
                                <label class="form-check-label" for="sendNotification">Notify administrators</label>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    Chart.defaults.color = '#6a1b9a';
    Chart.defaults.borderColor = 'rgba(106, 27, 154, 0.1)';    document.addEventListener('DOMContentLoaded', function() {
        initializeDateRange();
        loadStatistics();
        initializeCharts();
        loadDonations();
        loadMembers();
        
       
        document.getElementById('applyDateRange').addEventListener('click', function() {
            loadStatistics();
            loadDonations();
            updateCharts();
        });
        
  
        document.getElementById('donorType').addEventListener('change', function() {
            const memberSelect = document.getElementById('memberSelect');
            const nonMemberName = document.getElementById('nonMemberName');
            const memberIdSelect = document.querySelector('[name="member_id"]');
            
            if (this.value === 'non-member') {
                memberSelect.style.display = 'none';
                nonMemberName.style.display = 'block';
                memberIdSelect.removeAttribute('required');
            } else {
                memberSelect.style.display = 'block';
                nonMemberName.style.display = 'none';
                memberIdSelect.setAttribute('required', 'required');
            }
        });

        document.getElementById('addDonationModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('addDonationForm').reset();
            document.querySelector('#addDonationModal .modal-title').textContent = 'Add New Donation';
            document.querySelector('#addDonationModal button[type="submit"]').textContent = 'Add Donation';
            const idInput = document.querySelector('[name="donation_id"]');
            if (idInput) idInput.remove();
        });

        document.getElementById('addDonationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const isEdit = formData.has('donation_id');
            
            fetch(`../crud/donations/${isEdit ? 'update_donation.php' : 'create_donation.php'}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addDonationModal')).hide();
                    loadDonations();
                    loadStatistics();
                    updateCharts();
                    this.reset();
                } else {
                    alert(data.message || 'Error saving donation');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    function initializeDateRange() {
        const endDate = new Date();
        const startDate = new Date();
        startDate.setMonth(startDate.getMonth() - 1);
        
        document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
        document.getElementById('endDate').value = endDate.toISOString().split('T')[0];
    }    function loadStatistics() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

    
        fetch('../crud/donations/statistics/get_total_statistics.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('allTimeDonations').textContent = formatCurrency(data.totalDonations);
                }
            })
            .catch(error => console.error('Error loading total statistics:', error));

        fetch(`../crud/donations/statistics/get_donation_statistics.php?start=${startDate}&end=${endDate}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('totalDonations').textContent = formatCurrency(data.totalDonations);
            })
            .catch(error => console.error('Error loading custom range statistics:', error));
        
    
        fetch('../crud/donations/statistics/get_donation_periods.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('todayDonations').textContent = formatCurrency(data.today);
                    document.getElementById('weekDonations').textContent = formatCurrency(data.week);
                    document.getElementById('monthDonations').textContent = formatCurrency(data.month);
                    document.getElementById('yearDonations').textContent = formatCurrency(data.year);
                }
            })
            .catch(error => console.error('Error loading period statistics:', error));
    }

    function initializeCharts() {
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
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
          fetch(`../crud/donations/statistics/get_donations_chart.php?start=${startDate}&end=${endDate}`)
            .then(response => response.json())
            .then(data => {
                window.donationsChart.data.labels = data.labels;
                window.donationsChart.data.datasets[0].data = data.values;
                window.donationsChart.update();
            });
          fetch(`../crud/donations/statistics/get_donation_types.php?start=${startDate}&end=${endDate}`)
            .then(response => response.json())
            .then(data => {
                window.donationTypesChart.data.datasets[0].data = data.values;
                window.donationTypesChart.update();
            });
    }

    function loadDonations() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        
        fetch(`../crud/donations/read_donations.php?start=${startDate}&end=${endDate}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load donations');
                }

                const tbody = document.getElementById('donationsTableBody');
                tbody.innerHTML = '';
                
                if (data.donations.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center">No donations found</td>
                        </tr>
                    `;
                    return;
                }
                
                data.donations.forEach(donation => {
                    const row = `
                        <tr>
                            <td>${donation.donor_name || 'Anonymous'}</td>
                            <td>
                                <span class="badge bg-primary">
                                    ${capitalizeFirst(donation.donation_type)}
                                </span>
                            </td>
                            <td>${formatCurrency(donation.amount)}</td>
                            <td>${formatDate(donation.donation_date)}</td>
                            <td>${donation.notes || '-'}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="editDonation(${donation.id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteDonation(${donation.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
                
                document.getElementById('showing').textContent = data.donations.length;
                document.getElementById('total').textContent = data.total || 0;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('donationsTableBody').innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            ${error.message || 'Failed to load donations'}
                        </td>
                    </tr>
                `;
            });
    }

    function loadMembers() {
        fetch('../crud/members/read_members.php?all=true')
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load members');
                }
                
                const select = document.querySelector('select[name="member_id"]');
                select.innerHTML = '<option value="">Select Member</option>';
                
                data.members.forEach(member => {
                    select.insertAdjacentHTML('beforeend', `
                        <option value="${member.id}">
                            ${member.first_name} ${member.last_name}
                        </option>
                    `);
                });
            })
            .catch(error => {
                console.error('Error loading members:', error);
                document.querySelector('select[name="member_id"]').innerHTML = 
                    '<option value="">Error loading members</option>';
            });
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

    function deleteDonation(id) {
        if (confirm('Are you sure you want to delete this donation?')) {
            fetch('../crud/donations/delete_donations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadDonations();
                } else {
                    alert(data.message || 'Error deleting donation');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }

    function editDonation(id) {
        fetch(`../crud/donations/read_donations.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.donations && data.donations[0]) {
                    const donation = data.donations[0];
                    const form = document.getElementById('addDonationForm');
                    
                    const donorType = donation.member_id ? 'member' : 'non-member';
                    form.querySelector('[name="donor_type"]').value = donorType;
                    
                    const memberSelect = document.getElementById('memberSelect');
                    const nonMemberName = document.getElementById('nonMemberName');
                    const memberIdSelect = form.querySelector('[name="member_id"]');
                    
                    if (donorType === 'non-member') {
                        memberSelect.style.display = 'none';
                        nonMemberName.style.display = 'block';
                        memberIdSelect.removeAttribute('required');
                        form.querySelector('[name="donor_name"]').value = donation.donor_name || '';
                    } else {
                        memberSelect.style.display = 'block';
                        nonMemberName.style.display = 'none';
                        memberIdSelect.setAttribute('required', 'required');
                        memberIdSelect.value = donation.member_id;
                    }
                    
                    form.querySelector('[name="donation_type"]').value = donation.donation_type;
                    form.querySelector('[name="amount"]').value = donation.amount;
                    form.querySelector('[name="donation_date"]').value = donation.donation_date;
                    form.querySelector('[name="notes"]').value = donation.notes || '';
                    
                    let idInput = form.querySelector('[name="donation_id"]');
                    if (!idInput) {
                        idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'donation_id';
                        form.appendChild(idInput);
                    }
                    idInput.value = donation.id;
                    
                    document.querySelector('#addDonationModal .modal-title').textContent = 'Edit Donation';
                    document.querySelector('#addDonationModal button[type="submit"]').textContent = 'Save Changes';
                    
                    new bootstrap.Modal(document.getElementById('addDonationModal')).show();
                } else {
                    alert('Error loading donation details');
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function exportDonations() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        
        window.location.href = `../crud/donations/export_donations.php?start=${startDate}&end=${endDate}`;
    }
    </script>

<?php require_once '../templates/admin_footer.php'; ?>