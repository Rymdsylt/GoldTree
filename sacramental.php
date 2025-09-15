<?php
require_once 'auth/login_status.php';
require_once 'db/connection.php';


$stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['admin_status'] != 1) {
    header("Location: /GoldTree/events.php");
    exit();
}

?>

<?php require_once 'templates/header.php'; ?>

<div class="container-fluid px-3 px-md-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Sacramental Records</h2>
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
                                    echo "<button class='btn btn-sm btn-primary' onclick='viewRecord(" . $record['id'] . ")'><i class='bi bi-eye'></i></button>";
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




            </div>
        </div>
    </div>
</div>


<?php require_once 'templates/admin_footer.php'; ?>

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
