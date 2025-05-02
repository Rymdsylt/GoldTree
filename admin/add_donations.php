<?php
session_start();
 require_once '../templates/admin_header.php';
 ?>

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
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="sendNotification" name="send_notification">
                            <label class="form-check-label" for="sendNotification">Send notification to Users</label>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDonations();
    loadMembers();

    document.getElementById('addDonationModal').addEventListener('hidden.bs.modal', function () {
        const form = document.getElementById('addDonationForm');
        form.reset();
        form.querySelector('[name="donation_id"]')?.remove();
        document.querySelector('#addDonationModal .modal-title').textContent = 'Add New Donation';
        document.querySelector('#addDonationModal button[type="submit"]').textContent = 'Add Donation';
    });

    document.getElementById('donorType').addEventListener('change', function() {
        const memberSelect = document.getElementById('memberSelect');
        const nonMemberName = document.getElementById('nonMemberName');
        const memberIdSelect = document.querySelector('[name="member_id"]');
        
        if (this.value === 'non-member') {
            memberSelect.style.display = 'none';
            nonMemberName.style.display = 'block';
            memberIdSelect.removeAttribute('required');
            memberIdSelect.value = ''; 
        } else {
            memberSelect.style.display = 'block';
            nonMemberName.style.display = 'none';
            memberIdSelect.setAttribute('required', 'required');
        }
    });

    document.getElementById('addDonationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const donorType = formData.get('donor_type');
        const donationId = formData.get('donation_id');
        
   
        if (donorType === 'non-member') {
            formData.delete('member_id');
            const memberIdSelect = this.querySelector('[name="member_id"]');
            if (memberIdSelect) {
                memberIdSelect.removeAttribute('required');
            }
        }
        

        if (!formData.get('donation_date')) {
            const today = new Date().toISOString().split('T')[0];
            formData.set('donation_date', today);
        }

        const url = donationId ? 
            '../crud/donations/update_donation.php' : 
            '../crud/donations/create_donation.php';
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addDonationModal')).hide();
                loadDonations();
                this.reset();
            } else {
                alert(data.message || 'Error ' + (donationId ? 'updating' : 'adding') + ' donation');
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

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
    fetch(`../crud/donations/read_donations.php?page=${page}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('donationsTableBody');
            tbody.innerHTML = '';
            
            if (data.success && data.donations) {
                data.donations.forEach(donation => {
                    const row = `
                        <tr>
                            <td>${donation.donor_name || 'Anonymous'}</td>
                            <td>
                                <span class="badge bg-${getTypeBadge(donation.donation_type)}">
                                    ${capitalizeFirst(donation.donation_type)}
                                </span>
                            </td>
                            <td>${formatCurrency(donation.amount)}</td>
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
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No donations found</td></tr>';
                document.getElementById('showing').textContent = '0';
                document.getElementById('total').textContent = '0';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('donationsTableBody').innerHTML = 
                '<tr><td colspan="6" class="text-center text-danger">Error loading donations</td></tr>';
        });
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
</script>

<?php require_once '../templates/admin_footer.php'; ?>