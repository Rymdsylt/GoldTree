<?php
require_once '../auth/login_status.php';
require_once '../db/connection.php';


$stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['admin_status'] != 1) {
    header("Location: /GoldTree/events.php");
    exit();
}

?>

<?php require_once '../templates/admin_header.php'; ?>

<div class="container-fluid px-3 px-md-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Sacramental Records</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                    <i class="bi bi-plus-circle"></i> Add New Record
                </button>
            </div>
            

            <div class="card mb-4">
                <div class="card-body">
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="sacramentType" class="form-label">Sacrament Type</label>
                            <select class="form-select" id="filterSacramentType" name="sacramentType">
                                <option value="">All Sacraments</option>
                                <option value="Baptism">Baptism</option>
                                <option value="Confirmation">Confirmation</option>
                                <option value="First Communion">First Communion</option>
                                <option value="Marriage">Marriage</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="dateFrom" class="form-label">Date From</label>
                            <input type="date" class="form-control" id="dateFrom" name="dateFrom">
                        </div>
                        <div class="col-md-3">
                            <label for="dateTo" class="form-label">Date To</label>
                            <input type="date" class="form-control" id="dateTo" name="dateTo">
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Search by name...">
                        </div>
                    </form>
                </div>
            </div>


            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Address</th>
                                    <th>Sacrament</th>
                                    <th>Date</th>
                                    <th>Priest Presiding</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $conn->query("SELECT * FROM sacramental_records ORDER BY date DESC LIMIT 10");
                                while ($record = $stmt->fetch()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($record['name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($record['age']) . "</td>";
                                    echo "<td>" . htmlspecialchars($record['address']) . "</td>";
                                    echo "<td>" . htmlspecialchars($record['sacrament_type']) . "</td>";
                                    echo "<td>" . date('M d, Y', strtotime($record['date'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($record['priest_presiding']) . "</td>";
                                    echo "<td class='text-end'>";
                                    echo "<button class='btn btn-sm btn-primary me-2' onclick='viewRecord(" . $record['id'] . ")'><i class='bi bi-eye'></i></button>";
                                    echo "<button class='btn btn-sm btn-warning me-2' onclick='editRecord(" . $record['id'] . ")'><i class='bi bi-pencil'></i></button>";
                                    echo "<button class='btn btn-sm btn-danger' onclick='deleteRecord(" . $record['id'] . ")'><i class='bi bi-trash'></i></button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="addRecordModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Sacramental Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addRecordForm">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="age" class="form-label">Age</label>
                            <input type="number" class="form-control" id="age" name="age" required>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="sacramentType" class="form-label">Sacrament Type</label>
                            <select class="form-select" id="sacramentType" name="sacramentType" required>
                                <option value="">Select Sacrament</option>
                                <option value="Baptism">Baptism</option>
                                <option value="Confirmation">Confirmation</option>
                                <option value="First Communion">First Communion</option>
                                <option value="Marriage">Marriage</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a sacrament type
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        <div class="col-12">
                            <label for="priestPresiding" class="form-label">Priest Presiding</label>
                            <input type="text" class="form-control" id="priestPresiding" name="priestPresiding" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveRecord()">Save Record</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../templates/admin_footer.php'; ?>

<div class="modal fade" id="viewRecordModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Sacramental Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="fw-bold">Full Name</label>
                        <p id="viewName"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Age</label>
                        <p id="viewAge"></p>
                    </div>
                    <div class="col-12">
                        <label class="fw-bold">Address</label>
                        <p id="viewAddress"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Sacrament Type</label>
                        <p id="viewSacramentType"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Date</label>
                        <p id="viewDate"></p>
                    </div>
                    <div class="col-12">
                        <label class="fw-bold">Priest Presiding</label>
                        <p id="viewPriestPresiding"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editRecordModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Sacramental Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editRecordForm">
                    <input type="hidden" id="editId" name="id">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="editName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editAge" class="form-label">Age</label>
                            <input type="number" class="form-control" id="editAge" name="age" required>
                        </div>
                        <div class="col-12">
                            <label for="editAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="editAddress" name="address" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="editSacramentType" class="form-label">Sacrament Type</label>
                            <select class="form-select" id="editSacramentType" name="sacrament_type" required>
                                <option value="">Select Sacrament</option>
                                <option value="Baptism">Baptism</option>
                                <option value="Confirmation">Confirmation</option>
                                <option value="First Communion">First Communion</option>
                                <option value="Marriage">Marriage</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="editDate" name="date" required>
                        </div>
                        <div class="col-12">
                            <label for="editPriestPresiding" class="form-label">Priest Presiding</label>
                            <input type="text" class="form-control" id="editPriestPresiding" name="priest_presiding" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="updateRecord()">Update Record</button>
            </div>
        </div>
    </div>
</div>

<script>
async function viewRecord(id) {
    try {
        const response = await fetch(`/GoldTree/crud/sacramental_records/get.php?id=${id}`);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Failed to fetch record');
        }

        document.getElementById('viewName').textContent = result.name;
        document.getElementById('viewAge').textContent = result.age;
        document.getElementById('viewAddress').textContent = result.address;
        document.getElementById('viewSacramentType').textContent = result.sacrament_type;
        document.getElementById('viewDate').textContent = new Date(result.date).toLocaleDateString();
        document.getElementById('viewPriestPresiding').textContent = result.priest_presiding;

        const modal = new bootstrap.Modal(document.getElementById('viewRecordModal'));
        modal.show();
    } catch (error) {
        alert(error.message);
    }
}

async function editRecord(id) {
    try {
        const response = await fetch(`/GoldTree/crud/sacramental_records/get.php?id=${id}`);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Failed to fetch record');
        }

        document.getElementById('editId').value = result.id;
        document.getElementById('editName').value = result.name;
        document.getElementById('editAge').value = result.age;
        document.getElementById('editAddress').value = result.address;
        document.getElementById('editSacramentType').value = result.sacrament_type;
        document.getElementById('editDate').value = result.date;
        document.getElementById('editPriestPresiding').value = result.priest_presiding;

        const modal = new bootstrap.Modal(document.getElementById('editRecordModal'));
        modal.show();
    } catch (error) {
        alert(error.message);
    }
}

async function updateRecord() {
    try {
        const form = document.getElementById('editRecordForm');
        const formData = {
            id: document.getElementById('editId').value,
            name: document.getElementById('editName').value.trim(),
            age: document.getElementById('editAge').value.trim(),
            address: document.getElementById('editAddress').value.trim(),
            sacrament_type: document.getElementById('editSacramentType').value,
            date: document.getElementById('editDate').value.trim(),
            priest_presiding: document.getElementById('editPriestPresiding').value.trim()
        };

        for (const [key, value] of Object.entries(formData)) {
            if (!value && key !== 'id') {
                throw new Error(`Please fill in all required fields`);
            }
        }

        const response = await fetch('/GoldTree/crud/sacramental_records/update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Failed to update record');
        }

        alert('Record updated successfully!');
        const modal = bootstrap.Modal.getInstance(document.getElementById('editRecordModal'));
        modal.hide();
        location.reload();

    } catch (error) {
        alert(error.message);
    }
}

async function deleteRecord(id) {
    if (confirm('Are you sure you want to delete this record?')) {
        try {
            const formData = new FormData();
            formData.append('id', id);

            const response = await fetch('/GoldTree/crud/sacramental_records/delete.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to delete record');
            }

            alert('Record deleted successfully!');
            location.reload();

        } catch (error) {
            alert(error.message);
        }
    }
}

async function saveRecord() {
    try {
  
        const form = document.getElementById('addRecordForm');
        const sacramentSelect = form.querySelector('select[name="sacramentType"]');
        const selectedValue = sacramentSelect.value;
        
        const formData = {
            name: document.getElementById('name').value.trim(),
            age: document.getElementById('age').value.trim(),
            address: document.getElementById('address').value.trim(),
            sacrament_type: selectedValue,
            date: document.getElementById('date').value.trim(),
            priest_presiding: document.getElementById('priestPresiding').value.trim()
        };

        console.log('Form Data:', formData); 


        if (!selectedValue) {
            sacramentSelect.classList.add('is-invalid');
            throw new Error('Please select a sacrament type');
        }
        sacramentSelect.classList.remove('is-invalid');

    
        const fieldLabels = {
            name: 'Full Name',
            age: 'Age',
            address: 'Address',
            date: 'Date',
            priest_presiding: 'Priest Presiding'
        };

        for (const [key, value] of Object.entries(formData)) {
            if (key !== 'sacrament_type' && !value) {
                throw new Error(`Please fill in the ${fieldLabels[key]}`);
            }
        }

     
        const response = await fetch('/GoldTree/crud/sacramental_records/save.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Failed to save record');
        }


        alert('Record saved successfully!');


        document.getElementById('addRecordForm').reset();

    
        const modal = bootstrap.Modal.getInstance(document.getElementById('addRecordModal'));
        modal.hide();

        location.reload();

    } catch (error) {
        alert(error.message);
    }
}



async function filterRecords() {
    try {
        const filterData = {
            sacramentType: document.getElementById('filterSacramentType').value,
            dateFrom: document.getElementById('dateFrom').value,
            dateTo: document.getElementById('dateTo').value,
            search: document.getElementById('search').value
        };

        const response = await fetch('/GoldTree/crud/sacramental_records/filter.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(filterData)
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Failed to filter records');
        }

       
        const tbody = document.querySelector('table tbody');
        tbody.innerHTML = result.html;

    } catch (error) {
        alert(error.message);
    }
}


let filterTimeout;
document.querySelectorAll('#filterForm select, #filterForm input').forEach(element => {
    element.addEventListener('change', () => {
        filterRecords();
    });

  
    if (element.id === 'search') {
        element.addEventListener('input', () => {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                filterRecords();
            }, 300); 
        });
    }
});
</script>
