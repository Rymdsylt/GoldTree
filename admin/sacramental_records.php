<?php
require_once '../auth/login_status.php';
require_once '../db/connection.php';


$stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

require_once __DIR__ . '/../config.php';

if (!$user || $user['admin_status'] != 1) {
    header("Location: " . base_path('events.php'));
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
                            <select class="form-select" id="filterSacramentType" name="sacramentType" required>
                                <option value="Baptism">Baptism</option>
                                <option value="Confirmation">Confirmation</option>
                                <option value="First Communion">First Communion</option>
                                <option value="Marriage">Marriage</option>
                            </select>
                        </div>
        
                        <div class="col-md-3 marriage-filter" style="display: none;">
                            <label for="matrimonyDateFrom" class="form-label">Marriage Date From</label>
                            <input type="date" class="form-control" id="matrimonyDateFrom" name="matrimonyDateFrom">
                        </div>
                        <div class="col-md-3 marriage-filter" style="display: none;">
                            <label for="matrimonyDateTo" class="form-label">Marriage Date To</label>
                            <input type="date" class="form-control" id="matrimonyDateTo" name="matrimonyDateTo">
                        </div>
                        <div class="col-md-3 marriage-filter" style="display: none;">
                            <label for="brideName" class="form-label">Bride's Name</label>
                            <input type="text" class="form-control" id="brideName" name="brideName" placeholder="Search bride's name...">
                        </div>
                        <div class="col-md-3 marriage-filter" style="display: none;">
                            <label for="groomName" class="form-label">Groom's Name</label>
                            <input type="text" class="form-control" id="groomName" name="groomName" placeholder="Search groom's name...">
                        </div>
          
                        <div class="col-md-4 confirmation-filter" style="display: none;">
                            <label for="confirmationName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="confirmationName" name="confirmationName" placeholder="Search by name...">
                        </div>
                        <div class="col-md-4 confirmation-filter" style="display: none;">
                            <label for="confirmationParent" class="form-label">Parent's Name</label>
                            <input type="text" class="form-control" id="confirmationParent" name="confirmationParent" placeholder="Search by parent's name...">
                        </div>
                        <div class="col-md-4 confirmation-filter" style="display: none;">
                            <label for="confirmationMinister" class="form-label">Minister</label>
                            <input type="text" class="form-control" id="confirmationMinister" name="confirmationMinister" placeholder="Search by minister...">
                        </div>
                        <div class="col-md-3 confirmation-filter" style="display: none;">
                            <label for="confirmationDateFrom" class="form-label">Confirmation Date From</label>
                            <input type="date" class="form-control" id="confirmationDateFrom" name="confirmationDateFrom">
                        </div>
                        <div class="col-md-3 confirmation-filter" style="display: none;">
                            <label for="confirmationDateTo" class="form-label">Confirmation Date To</label>
                            <input type="date" class="form-control" id="confirmationDateTo" name="confirmationDateTo">
                        </div>

                  
                        <div class="col-md-3 communion-filter" style="display: none;">
                            <label for="communionDateFrom" class="form-label">Communion Date From</label>
                            <input type="date" class="form-control" id="communionDateFrom" name="communionDateFrom">
                        </div>
                        <div class="col-md-3 communion-filter" style="display: none;">
                            <label for="communionDateTo" class="form-label">Communion Date To</label>
                            <input type="date" class="form-control" id="communionDateTo" name="communionDateTo">
                        </div>
                        <div class="col-md-2 communion-filter" style="display: none;">
                            <label for="communionName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="communionName" name="communionName" placeholder="Search by name...">
                        </div>
                        <div class="col-md-2 communion-filter" style="display: none;">
                            <label for="communionParent" class="form-label">Parent's Name</label>
                            <input type="text" class="form-control" id="communionParent" name="communionParent" placeholder="Search by parent's name...">
                        </div>
                        <div class="col-md-2 communion-filter" style="display: none;">
                            <label for="communionMinister" class="form-label">Minister</label>
                            <input type="text" class="form-control" id="communionMinister" name="communionMinister" placeholder="Search by minister...">
                        </div>

                 
                        <div class="col-md-3 baptism-filter" style="display: none;">
                            <label for="baptismDateFrom" class="form-label">Baptism Date From</label>
                            <input type="date" class="form-control" id="baptismDateFrom" name="baptismDateFrom">
                        </div>
                        <div class="col-md-3 baptism-filter" style="display: none;">
                            <label for="baptismDateTo" class="form-label">Baptism Date To</label>
                            <input type="date" class="form-control" id="baptismDateTo" name="baptismDateTo">
                        </div>
                        <div class="col-md-2 baptism-filter" style="display: none;">
                            <label for="baptismName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="baptismName" name="baptismName" placeholder="Search by name...">
                        </div>
                        <div class="col-md-2 baptism-filter" style="display: none;">
                            <label for="baptismParent" class="form-label">Parent's Name</label>
                            <input type="text" class="form-control" id="baptismParent" name="baptismParent" placeholder="Search by parent's name...">
                        </div>
                        <div class="col-md-2 baptism-filter" style="display: none;">
                            <label for="baptismMinister" class="form-label">Minister</label>
                            <input type="text" class="form-control" id="baptismMinister" name="baptismMinister" placeholder="Search by minister...">
                        </div>
                    </form>
                </div>
            </div>


            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div id="addRecordButtonContainer">
                     
                        </div>
                    </div>
                    <div class="table-responsive">
                      
                        <div id="confirmationTable" style="display: none;">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Parents Information</th>
                                        <th>Birth Details</th>
                                        <th>Baptism Date</th>
                                        <th>Minister</th>
                                        <th>Sponsors</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="confirmationTableBody">
                                </tbody>
                            </table>
                            <div id="confirmationPagination" class="mt-3">
                            
                            </div>
                        </div>

                        <div id="baptismTable" style="display: none;">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Parents Information</th>
                                        <th>Birth Details</th>
                                        <th>Baptism Date</th>
                                        <th>Minister</th>
                                        <th>Sponsors</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="baptismTableBody">
                                </tbody>
                            </table>
                            <div id="baptismPagination" class="mt-3">
                             
                            </div>
                        </div>

                        <div id="firstCommunionTable" style="display: none;" class="card mb-4">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Parents Information</th>
                                                <th>Birth Details</th>
                                                <th>Baptism Details</th>
                                                <th>Confirmation Date</th>
                                                <th>Minister</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="firstCommunionTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div id="firstCommunionPagination" class="mt-2">
                                </div>
                            </div>
                        </div>

                        <div id="matrimonyTables" style="display: none;">
                        
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Marriage Records</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Marriage Date</th>
                                                <th>Bride's Name</th>
                                                <th>Groom's Name</th>
                                                <th>Church</th>
                                                <th>Minister</th>
                                                <th>Sponsors</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="matrimonyDetailsTableBody">
                                        </tbody>
                                    </table>
                                </div>
                                <div id="matrimonyPagination" class="mt-3">
                                </div>
                            </div>

                
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Bride Details</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Birth Details</th>
                                                <th>Parents</th>
                                                <th>Baptism Details</th>
                                                <th>Confirmation Details</th>
                                            </tr>
                                        </thead>
                                        <tbody id="matrimonyBrideTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Groom Details</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Birth Details</th>
                                                <th>Parents</th>
                                                <th>Baptism Details</th>
                                                <th>Confirmation Details</th>
                                            </tr>
                                        </thead>
                                        <tbody id="matrimonyGroomTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                                <div id="matrimonyPagination" class="mt-3">
                            
                   
                        </div>

                        <div id="noRecordsMessage" class="alert alert-info text-center">
                            <i class="bi bi-info-circle me-2"></i>
                            Please select a sacrament type to view records.
                        </div>
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
         
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label for="sacramentType" class="form-label">Sacrament Type</label>
                            <select class="form-select" id="sacramentType" name="sacramentType" required onchange="updateFormFields()">
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
                    </div>

            
                    <div id="dynamicFormFields">
                     
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
<script>
// Helper function to get the base path for API endpoints
function getApiEndpoint(path) {
    const basePath = document.body.getAttribute('data-base-path') || '';
    return `${basePath}${path}`;
}
</script>
<script src="/GoldTree/js/first_communion_filters.js"></script>

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
                <h5 class="modal-title">Edit First Communion Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editRecordForm" class="needs-validation" novalidate>
                    <input type="hidden" id="editId" name="id">

     
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Personal Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="editName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="editName" name="name" required>
                                    <div class="invalid-feedback">Please enter the full name</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="editGender" class="form-label">Gender</label>
                                    <select class="form-select" id="editGender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a gender</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="editAddress" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="editAddress" name="address" required>
                                    <div class="invalid-feedback">Please enter the address</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Birth Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="editBirthDate" class="form-label">Birth Date</label>
                                    <input type="date" class="form-control" id="editBirthDate" name="birth_date" required>
                                    <div class="invalid-feedback">Please select the birth date</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="editBirthPlace" class="form-label">Birth Place</label>
                                    <input type="text" class="form-control" id="editBirthPlace" name="birth_place" required>
                                    <div class="invalid-feedback">Please enter the birth place</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Parent Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="editParent1Name" class="form-label">Parent 1 Name</label>
                                    <input type="text" class="form-control" id="editParent1Name" name="parent1_name">
                                </div>
                                <div class="col-md-6">
                                    <label for="editParent1Origin" class="form-label">Parent 1 Origin</label>
                                    <input type="text" class="form-control" id="editParent1Origin" name="parent1_origin">
                                </div>
                                <div class="col-md-6">
                                    <label for="editParent2Name" class="form-label">Parent 2 Name</label>
                                    <input type="text" class="form-control" id="editParent2Name" name="parent2_name">
                                </div>
                                <div class="col-md-6">
                                    <label for="editParent2Origin" class="form-label">Parent 2 Origin</label>
                                    <input type="text" class="form-control" id="editParent2Origin" name="parent2_origin">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Baptism Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="editBaptismDate" class="form-label">Baptism Date</label>
                                    <input type="date" class="form-control" id="editBaptismDate" name="baptism_date" required>
                                    <div class="invalid-feedback">Please select the baptism date</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="editBaptismChurch" class="form-label">Baptism Church</label>
                                    <input type="text" class="form-control" id="editBaptismChurch" name="baptism_church" required>
                                    <div class="invalid-feedback">Please enter the baptism church</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">First Communion Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="editChurch" class="form-label">Church</label>
                                    <input type="text" class="form-control" id="editChurch" name="church" required>
                                    <div class="invalid-feedback">Please enter the church</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="editConfirmationDate" class="form-label">Confirmation Date</label>
                                    <input type="date" class="form-control" id="editConfirmationDate" name="confirmation_date" required>
                                    <div class="invalid-feedback">Please select the confirmation date</div>
                                </div>
                                <div class="col-12">
                                    <label for="editMinister" class="form-label">Minister</label>
                                    <input type="text" class="form-control" id="editMinister" name="minister" required>
                                    <div class="invalid-feedback">Please enter the minister's name</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="updateRecord('First Communion')">Save Changes</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="editMarriageModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Marriage Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editMarriageForm" class="needs-validation" novalidate>
                    <input type="hidden" id="editMarriageId" name="id">
                    

                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Marriage Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="editMarriageDate" class="form-label">Marriage Date</label>
                                    <input type="date" class="form-control" id="editMarriageDate" name="matrimony_date" required>
                                    <div class="invalid-feedback">Please select a date</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="editMarriageChurch" class="form-label">Church</label>
                                    <input type="text" class="form-control" id="editMarriageChurch" name="church" required>
                                    <div class="invalid-feedback">Please enter the church</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="editMarriageMinister" class="form-label">Minister</label>
                                    <input type="text" class="form-control" id="editMarriageMinister" name="minister" required>
                                    <div class="invalid-feedback">Please enter the minister's name</div>
                                </div>
                            </div>
                        </div>
                    </div>

    
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Bride Information</h6>
                        </div>
                        <div class="card-body">
                            <input type="hidden" id="editBrideId" name="bride_id">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="editBrideName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="editBrideName" name="bride_name" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="editBrideBirthDate" class="form-label">Birth Date</label>
                                    <input type="date" class="form-control" id="editBrideBirthDate" name="bride_birth_date" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="editBrideBirthPlace" class="form-label">Birth Place</label>
                                    <input type="text" class="form-control" id="editBrideBirthPlace" name="bride_birth_place" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editBrideParent1Name" class="form-label">Parent 1 Name</label>
                                    <input type="text" class="form-control" id="editBrideParent1Name" name="bride_parent1_name">
                                </div>
                                <div class="col-md-6">
                                    <label for="editBrideParent1Origin" class="form-label">Parent 1 Origin</label>
                                    <input type="text" class="form-control" id="editBrideParent1Origin" name="bride_parent1_origin">
                                </div>
                                <div class="col-md-6">
                                    <label for="editBrideParent2Name" class="form-label">Parent 2 Name</label>
                                    <input type="text" class="form-control" id="editBrideParent2Name" name="bride_parent2_name">
                                </div>
                                <div class="col-md-6">
                                    <label for="editBrideParent2Origin" class="form-label">Parent 2 Origin</label>
                                    <input type="text" class="form-control" id="editBrideParent2Origin" name="bride_parent2_origin">
                                </div>
                                <div class="col-md-6">
                                    <label for="editBrideBaptismDate" class="form-label">Baptism Date</label>
                                    <input type="date" class="form-control" id="editBrideBaptismDate" name="bride_baptism_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editBrideBaptismChurch" class="form-label">Baptism Church</label>
                                    <input type="text" class="form-control" id="editBrideBaptismChurch" name="bride_baptism_church" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editBrideConfirmationDate" class="form-label">Confirmation Date</label>
                                    <input type="date" class="form-control" id="editBrideConfirmationDate" name="bride_confirmation_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editBrideConfirmationChurch" class="form-label">Confirmation Church</label>
                                    <input type="text" class="form-control" id="editBrideConfirmationChurch" name="bride_confirmation_church" required>
                                </div>
                            </div>
                        </div>
                    </div>

           
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Groom Information</h6>
                        </div>
                        <div class="card-body">
                            <input type="hidden" id="editGroomId" name="groom_id">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="editGroomName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="editGroomName" name="groom_name" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="editGroomBirthDate" class="form-label">Birth Date</label>
                                    <input type="date" class="form-control" id="editGroomBirthDate" name="groom_birth_date" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="editGroomBirthPlace" class="form-label">Birth Place</label>
                                    <input type="text" class="form-control" id="editGroomBirthPlace" name="groom_birth_place" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editGroomParent1Name" class="form-label">Parent 1 Name</label>
                                    <input type="text" class="form-control" id="editGroomParent1Name" name="groom_parent1_name">
                                </div>
                                <div class="col-md-6">
                                    <label for="editGroomParent1Origin" class="form-label">Parent 1 Origin</label>
                                    <input type="text" class="form-control" id="editGroomParent1Origin" name="groom_parent1_origin">
                                </div>
                                <div class="col-md-6">
                                    <label for="editGroomParent2Name" class="form-label">Parent 2 Name</label>
                                    <input type="text" class="form-control" id="editGroomParent2Name" name="groom_parent2_name">
                                </div>
                                <div class="col-md-6">
                                    <label for="editGroomParent2Origin" class="form-label">Parent 2 Origin</label>
                                    <input type="text" class="form-control" id="editGroomParent2Origin" name="groom_parent2_origin">
                                </div>
                                <div class="col-md-6">
                                    <label for="editGroomBaptismDate" class="form-label">Baptism Date</label>
                                    <input type="date" class="form-control" id="editGroomBaptismDate" name="groom_baptism_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editGroomBaptismChurch" class="form-label">Baptism Church</label>
                                    <input type="text" class="form-control" id="editGroomBaptismChurch" name="groom_baptism_church" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editGroomConfirmationDate" class="form-label">Confirmation Date</label>
                                    <input type="date" class="form-control" id="editGroomConfirmationDate" name="groom_confirmation_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="editGroomConfirmationChurch" class="form-label">Confirmation Church</label>
                                    <input type="text" class="form-control" id="editGroomConfirmationChurch" name="groom_confirmation_church" required>
                                </div>
                            </div>
                        </div>
                    </div>

     
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Sponsors</h6>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addEditMatrimonySponsor()">
                                Add Sponsor
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="editMatrimonySponsorsContainer">
                              
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateMarriageRecord()">Save Changes</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteMarriageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Marriage Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this marriage record?</p>
                <input type="hidden" id="deleteMarriageId">
                <p><strong>Marriage Date:</strong> <span id="deleteMarriageDate"></span></p>
                <p><strong>Couple:</strong> <span id="deleteMarriageCouple"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteMarriage()">Delete</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewMarriageModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Marriage Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Marriage Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>Date:</strong> <span id="viewMarriageDate"></span></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Church:</strong> <span id="viewMarriageChurch"></span></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Minister:</strong> <span id="viewMarriageMinister"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

     
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Bride Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <span id="viewBrideName"></span></p>
                                <p><strong>Birth Date:</strong> <span id="viewBrideBirthDate"></span></p>
                                <p><strong>Birth Place:</strong> <span id="viewBrideBirthPlace"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Parents:</strong></p>
                                <p id="viewBrideParent1"></p>
                                <p id="viewBrideParent2"></p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>Baptism:</strong></p>
                                <p>Date: <span id="viewBrideBaptismDate"></span></p>
                                <p>Church: <span id="viewBrideBaptismChurch"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Confirmation:</strong></p>
                                <p>Date: <span id="viewBrideConfirmationDate"></span></p>
                                <p>Church: <span id="viewBrideConfirmationChurch"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

 
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Groom Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <span id="viewGroomName"></span></p>
                                <p><strong>Birth Date:</strong> <span id="viewGroomBirthDate"></span></p>
                                <p><strong>Birth Place:</strong> <span id="viewGroomBirthPlace"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Parents:</strong></p>
                                <p id="viewGroomParent1"></p>
                                <p id="viewGroomParent2"></p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>Baptism:</strong></p>
                                <p>Date: <span id="viewGroomBaptismDate"></span></p>
                                <p>Church: <span id="viewGroomBaptismChurch"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Confirmation:</strong></p>
                                <p>Date: <span id="viewGroomConfirmationDate"></span></p>
                                <p>Church: <span id="viewGroomConfirmationChurch"></span></p>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Sponsors</h6>
                    </div>
                    <div class="card-body">
                        <div id="viewMarriageSponsors"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addMarriageModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Marriage Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addMarriageForm" class="needs-validation" novalidate>
    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Marriage Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="matrimonyDate" class="form-label">Marriage Date</label>
                                    <input type="date" class="form-control" id="matrimonyDate" name="matrimonyDate" required>
                                    <div class="invalid-feedback">Please select the marriage date.</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="church" class="form-label">Church</label>
                                    <input type="text" class="form-control" id="church" name="church" required>
                                    <div class="invalid-feedback">Please enter the church name.</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="minister" class="form-label">Minister</label>
                                    <input type="text" class="form-control" id="minister" name="minister" required>
                                    <div class="invalid-feedback">Please enter the minister's name.</div>
                                </div>
                            </div>
                        </div>
                    </div>

           
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Bride Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="brideName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="brideName" name="brideName" required>
                                    <div class="invalid-feedback">Please enter the bride's name.</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="brideBirthDate" class="form-label">Birth Date</label>
                                    <input type="date" class="form-control" id="brideBirthDate" name="brideBirthDate" required>
                                    <div class="invalid-feedback">Please select the birth date.</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="brideBirthPlace" class="form-label">Birth Place</label>
                                    <input type="text" class="form-control" id="brideBirthPlace" name="brideBirthPlace" required>
                                    <div class="invalid-feedback">Please enter the birth place.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="brideParent1Name" class="form-label">Parent 1 Name</label>
                                    <input type="text" class="form-control" id="brideParent1Name" name="brideParent1Name">
                                </div>
                                <div class="col-md-6">
                                    <label for="brideParent1Origin" class="form-label">Parent 1 Origin</label>
                                    <input type="text" class="form-control" id="brideParent1Origin" name="brideParent1Origin">
                                </div>
                                <div class="col-md-6">
                                    <label for="brideParent2Name" class="form-label">Parent 2 Name</label>
                                    <input type="text" class="form-control" id="brideParent2Name" name="brideParent2Name">
                                </div>
                                <div class="col-md-6">
                                    <label for="brideParent2Origin" class="form-label">Parent 2 Origin</label>
                                    <input type="text" class="form-control" id="brideParent2Origin" name="brideParent2Origin">
                                </div>
                                <div class="col-md-6">
                                    <label for="brideBaptismDate" class="form-label">Baptism Date</label>
                                    <input type="date" class="form-control" id="brideBaptismDate" name="brideBaptismDate" required>
                                    <div class="invalid-feedback">Please select the baptism date.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="brideBaptismChurch" class="form-label">Baptism Church</label>
                                    <input type="text" class="form-control" id="brideBaptismChurch" name="brideBaptismChurch" required>
                                    <div class="invalid-feedback">Please enter the baptism church.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="brideConfirmationDate" class="form-label">Confirmation Date</label>
                                    <input type="date" class="form-control" id="brideConfirmationDate" name="brideConfirmationDate" required>
                                    <div class="invalid-feedback">Please select the confirmation date.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="brideConfirmationChurch" class="form-label">Confirmation Church</label>
                                    <input type="text" class="form-control" id="brideConfirmationChurch" name="brideConfirmationChurch" required>
                                    <div class="invalid-feedback">Please enter the confirmation church.</div>
                                </div>
                            </div>
                        </div>
                    </div>

        
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Groom Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="groomName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="groomName" name="groomName" required>
                                    <div class="invalid-feedback">Please enter the groom's name.</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="groomBirthDate" class="form-label">Birth Date</label>
                                    <input type="date" class="form-control" id="groomBirthDate" name="groomBirthDate" required>
                                    <div class="invalid-feedback">Please select the birth date.</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="groomBirthPlace" class="form-label">Birth Place</label>
                                    <input type="text" class="form-control" id="groomBirthPlace" name="groomBirthPlace" required>
                                    <div class="invalid-feedback">Please enter the birth place.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="groomParent1Name" class="form-label">Parent 1 Name</label>
                                    <input type="text" class="form-control" id="groomParent1Name" name="groomParent1Name">
                                </div>
                                <div class="col-md-6">
                                    <label for="groomParent1Origin" class="form-label">Parent 1 Origin</label>
                                    <input type="text" class="form-control" id="groomParent1Origin" name="groomParent1Origin">
                                </div>
                                <div class="col-md-6">
                                    <label for="groomParent2Name" class="form-label">Parent 2 Name</label>
                                    <input type="text" class="form-control" id="groomParent2Name" name="groomParent2Name">
                                </div>
                                <div class="col-md-6">
                                    <label for="groomParent2Origin" class="form-label">Parent 2 Origin</label>
                                    <input type="text" class="form-control" id="groomParent2Origin" name="groomParent2Origin">
                                </div>
                                <div class="col-md-6">
                                    <label for="groomBaptismDate" class="form-label">Baptism Date</label>
                                    <input type="date" class="form-control" id="groomBaptismDate" name="groomBaptismDate" required>
                                    <div class="invalid-feedback">Please select the baptism date.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="groomBaptismChurch" class="form-label">Baptism Church</label>
                                    <input type="text" class="form-control" id="groomBaptismChurch" name="groomBaptismChurch" required>
                                    <div class="invalid-feedback">Please enter the baptism church.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="groomConfirmationDate" class="form-label">Confirmation Date</label>
                                    <input type="date" class="form-control" id="groomConfirmationDate" name="groomConfirmationDate" required>
                                    <div class="invalid-feedback">Please select the confirmation date.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="groomConfirmationChurch" class="form-label">Confirmation Church</label>
                                    <input type="text" class="form-control" id="groomConfirmationChurch" name="groomConfirmationChurch" required>
                                    <div class="invalid-feedback">Please enter the confirmation church.</div>
                                </div>
                            </div>
                        </div>
                    </div>

           
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Sponsors</h6>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addMatrimonySponsor()">
                                <i class="bi bi-plus-circle me-1"></i>Add Sponsor
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="matrimonySponsorsContainer">
                                <div class="sponsor-entry mb-2 d-flex align-items-center">
                                    <div class="me-2 fw-bold" style="width: 25px;">1.</div>
                                    <input type="text" class="form-control" name="sponsors[]" placeholder="Sponsor's Name">
                                    <button type="button" class="btn btn-danger ms-2" onclick="removeMatrimonySponsor(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveMarriageRecord()">Save Record</button>
            </div>
        </div>
            <div class="modal-header">
                <h5 class="modal-title">Add Marriage Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addMarriageForm" class="needs-validation" novalidate>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Marriage Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="matrimonyDate" class="form-label">Marriage Date</label>
                                    <input type="date" class="form-control" id="matrimonyDate" name="matrimony_date" required>
                                    <div class="invalid-feedback">Please select the marriage date</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="matrimonyChurch" class="form-label">Church</label>
                                    <input type="text" class="form-control" id="matrimonyChurch" name="church" required>
                                    <div class="invalid-feedback">Please enter the church name</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="matrimonyMinister" class="form-label">Minister</label>
                                    <input type="text" class="form-control" id="matrimonyMinister" name="minister" required>
                                    <div class="invalid-feedback">Please enter the minister's name</div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Bride Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="brideName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="brideName" name="bride[name]" required>
                                    <div class="invalid-feedback">Please enter the bride's name</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="brideBirthDate" class="form-label">Birth Date</label>
                                    <input type="date" class="form-control" id="brideBirthDate" name="bride[birth_date]" required>
                                    <div class="invalid-feedback">Please select the birth date</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="brideBirthPlace" class="form-label">Birth Place</label>
                                    <input type="text" class="form-control" id="brideBirthPlace" name="bride[birth_place]" required>
                                    <div class="invalid-feedback">Please enter the birth place</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="brideParent1Name" class="form-label">Parent 1 Name</label>
                                    <input type="text" class="form-control" id="brideParent1Name" name="bride[parent1_name]">
                                </div>
                                <div class="col-md-6">
                                    <label for="brideParent1Origin" class="form-label">Parent 1 Origin</label>
                                    <input type="text" class="form-control" id="brideParent1Origin" name="bride[parent1_origin]">
                                </div>
                                <div class="col-md-6">
                                    <label for="brideParent2Name" class="form-label">Parent 2 Name</label>
                                    <input type="text" class="form-control" id="brideParent2Name" name="bride[parent2_name]">
                                </div>
                                <div class="col-md-6">
                                    <label for="brideParent2Origin" class="form-label">Parent 2 Origin</label>
                                    <input type="text" class="form-control" id="brideParent2Origin" name="bride[parent2_origin]">
                                </div>
                                <div class="col-md-6">
                                    <label for="brideBaptismDate" class="form-label">Baptism Date</label>
                                    <input type="date" class="form-control" id="brideBaptismDate" name="bride[baptism_date]" required>
                                    <div class="invalid-feedback">Please select the baptism date</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="brideBaptismChurch" class="form-label">Baptism Church</label>
                                    <input type="text" class="form-control" id="brideBaptismChurch" name="bride[baptism_church]" required>
                                    <div class="invalid-feedback">Please enter the baptism church</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="brideConfirmationDate" class="form-label">Confirmation Date</label>
                                    <input type="date" class="form-control" id="brideConfirmationDate" name="bride[confirmation_date]" required>
                                    <div class="invalid-feedback">Please select the confirmation date</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="brideConfirmationChurch" class="form-label">Confirmation Church</label>
                                    <input type="text" class="form-control" id="brideConfirmationChurch" name="bride[confirmation_church]" required>
                                    <div class="invalid-feedback">Please enter the confirmation church</div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Groom Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="groomName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="groomName" name="groom[name]" required>
                                    <div class="invalid-feedback">Please enter the groom's name</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="groomBirthDate" class="form-label">Birth Date</label>
                                    <input type="date" class="form-control" id="groomBirthDate" name="groom[birth_date]" required>
                                    <div class="invalid-feedback">Please select the birth date</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="groomBirthPlace" class="form-label">Birth Place</label>
                                    <input type="text" class="form-control" id="groomBirthPlace" name="groom[birth_place]" required>
                                    <div class="invalid-feedback">Please enter the birth place</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="groomParent1Name" class="form-label">Parent 1 Name</label>
                                    <input type="text" class="form-control" id="groomParent1Name" name="groom[parent1_name]">
                                </div>
                                <div class="col-md-6">
                                    <label for="groomParent1Origin" class="form-label">Parent 1 Origin</label>
                                    <input type="text" class="form-control" id="groomParent1Origin" name="groom[parent1_origin]">
                                </div>
                                <div class="col-md-6">
                                    <label for="groomParent2Name" class="form-label">Parent 2 Name</label>
                                    <input type="text" class="form-control" id="groomParent2Name" name="groom[parent2_name]">
                                </div>
                                <div class="col-md-6">
                                    <label for="groomParent2Origin" class="form-label">Parent 2 Origin</label>
                                    <input type="text" class="form-control" id="groomParent2Origin" name="groom[parent2_origin]">
                                </div>
                                <div class="col-md-6">
                                    <label for="groomBaptismDate" class="form-label">Baptism Date</label>
                                    <input type="date" class="form-control" id="groomBaptismDate" name="groom[baptism_date]" required>
                                    <div class="invalid-feedback">Please select the baptism date</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="groomBaptismChurch" class="form-label">Baptism Church</label>
                                    <input type="text" class="form-control" id="groomBaptismChurch" name="groom[baptism_church]" required>
                                    <div class="invalid-feedback">Please enter the baptism church</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="groomConfirmationDate" class="form-label">Confirmation Date</label>
                                    <input type="date" class="form-control" id="groomConfirmationDate" name="groom[confirmation_date]" required>
                                    <div class="invalid-feedback">Please select the confirmation date</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="groomConfirmationChurch" class="form-label">Confirmation Church</label>
                                    <input type="text" class="form-control" id="groomConfirmationChurch" name="groom[confirmation_church]" required>
                                    <div class="invalid-feedback">Please enter the confirmation church</div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Sponsors</h6>
                        </div>
                        <div class="card-body">
                            <div id="marriageSponsorsContainer">
                                <div class="sponsor-entry mb-2 d-flex align-items-center">
                                    <div class="me-2 fw-bold" style="width: 25px;">1.</div>
                                    <input type="text" class="form-control" name="sponsors[]" placeholder="Sponsor's Name">
                                    <button type="button" class="btn btn-danger ms-2" onclick="removeSponsor(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="addSponsor('marriage')">
                                    <i class="bi bi-plus-circle me-1"></i>Add Sponsor
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveMarriageRecord()">Save Record</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteFirstCommunionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete First Communion Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this First Communion record? This action cannot be undone.</p>
                <div class="alert alert-danger">
                    <strong>Warning:</strong> This will permanently delete the record and all associated information.
                </div>
                <div class="mt-3">
                    <strong>Record Details:</strong>
                    <p id="deleteRecordName" class="mb-1"></p>
                    <p id="deleteRecordDate" class="mb-1"></p>
                </div>
                <input type="hidden" id="deleteRecordId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteRecord()">Delete Record</button>
            </div>
        </div>
    </div>
</div>
                    </div>
                </form>
            </div>
          
        </div>
    </div>
</div>

<div class="modal fade" id="deleteBaptismModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Baptismal Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this baptismal record? This action cannot be undone.</p>
                <div class="alert alert-danger">
                    <strong>Warning:</strong> This will permanently delete the record and all associated sponsor information.
                </div>
                <div class="mt-3">
                    <strong>Record Details:</strong>
                    <p id="deleteBaptismName" class="mb-1"></p>
                    <p id="deleteBaptismDate" class="mb-1"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteBaptismalRecord()">Delete Record</button>
            </div>
            <input type="hidden" id="deleteBaptismId">
        </div>
    </div>
</div>


<div class="modal fade" id="editBaptismModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Baptismal Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editBaptismForm">
                    <input type="hidden" id="editBaptismId" name="id">
                    <div class="row g-3">
                      
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Personal Information</h6>
                        </div>
                        <div class="col-12">
                            <label for="editBaptismName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="editBaptismName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editBaptismGender" class="form-label">Gender</label>
                            <select class="form-select" id="editBaptismGender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="editBaptismAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="editBaptismAddress" name="address" rows="2" required></textarea>
                        </div>

                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Birth Details</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="editBaptismBirthDate" class="form-label">Birth Date</label>
                            <input type="date" class="form-control" id="editBaptismBirthDate" name="birth_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editBaptismBirthPlace" class="form-label">Birth Place</label>
                            <input type="text" class="form-control" id="editBaptismBirthPlace" name="birth_place" required>
                        </div>

                   
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Parents Information</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="editBaptismParent1Name" class="form-label">Parent 1 Name</label>
                            <input type="text" class="form-control" id="editBaptismParent1Name" name="parent1_name">
                        </div>
                        <div class="col-md-6">
                            <label for="editBaptismParent1Origin" class="form-label">Parent 1 Origin</label>
                            <input type="text" class="form-control" id="editBaptismParent1Origin" name="parent1_origin">
                        </div>
                        <div class="col-md-6">
                            <label for="editBaptismParent2Name" class="form-label">Parent 2 Name</label>
                            <input type="text" class="form-control" id="editBaptismParent2Name" name="parent2_name">
                        </div>
                        <div class="col-md-6">
                            <label for="editBaptismParent2Origin" class="form-label">Parent 2 Origin</label>
                            <input type="text" class="form-control" id="editBaptismParent2Origin" name="parent2_origin">
                        </div>

                
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Baptism Details</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="editBaptismDate" class="form-label">Baptism Date</label>
                            <input type="date" class="form-control" id="editBaptismDate" name="baptism_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editBaptismMinister" class="form-label">Minister</label>
                            <input type="text" class="form-control" id="editBaptismMinister" name="minister" required>
                        </div>

                     
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Sponsors (Optional)</h6>
                        </div>
                        <div class="col-12" id="editBaptismSponsorsContainer">
                            <div class="sponsor-entry mb-2 d-flex align-items-center">
                                <div class="me-2 fw-bold" style="width: 25px;">1.</div>
                                <input type="text" class="form-control" name="sponsors[]" placeholder="Sponsor's Name (Optional)">
                                <button type="button" class="btn btn-danger ms-2" onclick="removeSponsor(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addSponsor('editBaptism')">
                                <i class="bi bi-plus-circle me-1"></i>Add Another Sponsor
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="updateBaptismalRecord()">Update Record</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="viewBaptismModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Baptismal Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
            
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Personal Information</h6>
                    </div>
                    <div class="col-12">
                        <label class="fw-bold">Full Name</label>
                        <p id="viewBaptismName"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Gender</label>
                        <p id="viewBaptismGender"></p>
                    </div>
                    <div class="col-12">
                        <label class="fw-bold">Address</label>
                        <p id="viewBaptismAddress"></p>
                    </div>

              
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Birth Details</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Birth Date</label>
                        <p id="viewBaptismBirthDate"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Birth Place</label>
                        <p id="viewBaptismBirthPlace"></p>
                    </div>

              
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Parents Information</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Parent 1</label>
                        <p id="viewBaptismParent1"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Parent 2</label>
                        <p id="viewBaptismParent2"></p>
                    </div>

           
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Baptism Details</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Baptism Date</label>
                        <p id="viewBaptismDate"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Minister</label>
                        <p id="viewBaptismMinister"></p>
                    </div>

                   
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Sponsors</h6>
                    </div>
                    <div class="col-12">
                        <div id="viewBaptismSponsors"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="addConfirmationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Confirmation Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addConfirmationForm">
                    <div class="row g-3">
                   
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Personal Information</h6>
                        </div>
                        <div class="col-12">
                            <label for="confirmationName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="confirmationName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmationGender" class="form-label">Gender</label>
                            <select class="form-select" id="confirmationGender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="confirmationAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="confirmationAddress" name="address" rows="2" required></textarea>
                        </div>

               
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Birth Details</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmationBirthDate" class="form-label">Birth Date</label>
                            <input type="date" class="form-control" id="confirmationBirthDate" name="birth_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmationBirthPlace" class="form-label">Birth Place</label>
                            <input type="text" class="form-control" id="confirmationBirthPlace" name="birth_place" required>
                        </div>

                
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Parents Information</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmationParent1Name" class="form-label">Parent 1 Name</label>
                            <input type="text" class="form-control" id="confirmationParent1Name" name="parent1_name">
                        </div>
                        <div class="col-md-6">
                            <label for="confirmationParent1Origin" class="form-label">Parent 1 Origin</label>
                            <input type="text" class="form-control" id="confirmationParent1Origin" name="parent1_origin">
                        </div>
                        <div class="col-md-6">
                            <label for="confirmationParent2Name" class="form-label">Parent 2 Name</label>
                            <input type="text" class="form-control" id="confirmationParent2Name" name="parent2_name">
                        </div>
                        <div class="col-md-6">
                            <label for="confirmationParent2Origin" class="form-label">Parent 2 Origin</label>
                            <input type="text" class="form-control" id="confirmationParent2Origin" name="parent2_origin">
                        </div>

                
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Confirmation Details</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="baptismDate" class="form-label">Baptism Date</label>
                            <input type="date" class="form-control" id="baptismDate" name="baptism_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmationMinister" class="form-label">Minister</label>
                            <input type="text" class="form-control" id="confirmationMinister" name="minister" required>
                        </div>

                    
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Sponsors (Optional)</h6>
                        </div>
                        <div class="col-12" id="confirmationSponsorsContainer">
                            <div class="sponsor-entry mb-2 d-flex align-items-center">
                                <div class="me-2 fw-bold" style="width: 25px;">1.</div>
                                <input type="text" class="form-control" name="sponsors[]" placeholder="Sponsor's Name (Optional)">
                                <button type="button" class="btn btn-danger ms-2" onclick="removeSponsor(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addSponsor('confirmation')">
                                <i class="bi bi-plus-circle me-1"></i>Add Another Sponsor
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveConfirmationRecord()">Save Record</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="addBaptismModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Baptismal Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addBaptismForm">
                    <div class="row g-3">
                     
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Personal Information</h6>
                        </div>
                        <div class="col-12">
                            <label for="baptismName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="baptismName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="baptismGender" class="form-label">Gender</label>
                            <select class="form-select" id="baptismGender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="baptismAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="baptismAddress" name="address" rows="2" required></textarea>
                        </div>
  
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Birth Details</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="baptismBirthDate" class="form-label">Birth Date</label>
                            <input type="date" class="form-control" id="baptismBirthDate" name="birth_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="baptismBirthPlace" class="form-label">Birth Place</label>
                            <input type="text" class="form-control" id="baptismBirthPlace" name="birth_place" required>
                        </div>

                   
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Parents Information</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="baptismParent1Name" class="form-label">Parent 1 Name</label>
                            <input type="text" class="form-control" id="baptismParent1Name" name="parent1_name">
                        </div>
                        <div class="col-md-6">
                            <label for="baptismParent1Origin" class="form-label">Parent 1 Origin</label>
                            <input type="text" class="form-control" id="baptismParent1Origin" name="parent1_origin">
                        </div>
                        <div class="col-md-6">
                            <label for="baptismParent2Name" class="form-label">Parent 2 Name</label>
                            <input type="text" class="form-control" id="baptismParent2Name" name="parent2_name">
                        </div>
                        <div class="col-md-6">
                            <label for="baptismParent2Origin" class="form-label">Parent 2 Origin</label>
                            <input type="text" class="form-control" id="baptismParent2Origin" name="parent2_origin">
                        </div>

                     
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Baptism Details</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="baptismDate" class="form-label">Baptism Date</label>
                            <input type="date" class="form-control" id="baptismDate" name="baptism_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="baptismMinister" class="form-label">Minister</label>
                            <input type="text" class="form-control" id="baptismMinister" name="minister" required>
                        </div>

                    
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Sponsors (Optional)</h6>
                        </div>
                        <div class="col-12" id="baptismSponsorsContainer">
                            <div class="sponsor-entry mb-2 d-flex align-items-center">
                                <div class="me-2 fw-bold" style="width: 25px;">1.</div>
                                <input type="text" class="form-control" name="sponsors[]" placeholder="Sponsor's Name (Optional)">
                                <button type="button" class="btn btn-danger ms-2" onclick="removeSponsor(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addSponsor('baptism')">
                                <i class="bi bi-plus-circle me-1"></i>Add Sponsor (Optional)
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveBaptismalRecord()">Save Record</button>
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

async function loadMatrimonyRecords() {
    try {
        const response = await fetch(getApiEndpoint('/crud/matrimony_records/get_all.php'));
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const result = await response.json();
        const records = result.data;

        const coupleTableBody = document.getElementById('matrimonyCoupleTableBody');
        const brideTableBody = document.getElementById('matrimonyBrideTableBody');
        const groomTableBody = document.getElementById('matrimonyGroomTableBody');

        coupleTableBody.innerHTML = '';
        brideTableBody.innerHTML = '';
        groomTableBody.innerHTML = '';

        if (records.length === 0) {
            const noRecordsMessage = '<tr><td colspan="5" class="text-center">No marriage records found</td></tr>';
            coupleTableBody.innerHTML = noRecordsMessage;
            brideTableBody.innerHTML = noRecordsMessage;
            groomTableBody.innerHTML = noRecordsMessage;
            return;
        }

        records.forEach(record => {
          
            const coupleRow = document.createElement('tr');
            coupleRow.innerHTML = `
                <td>${new Date(record.matrimony_date).toLocaleDateString()}</td>
                <td>${htmlEscape(record.church)}</td>
                <td>${htmlEscape(record.minister)}</td>
                <td>${record.sponsors.map(s => htmlEscape(s.sponsor_name)).join('<br>') || 'No sponsors listed'}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-primary me-2" onclick="viewMatrimonyRecord(${record.id})">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning me-2" onclick="editMatrimonyRecord(${record.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteMatrimonyRecord(${record.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            coupleTableBody.appendChild(coupleRow);

         
            const bride = record.couples.find(c => c.type === 'bride');
            const groom = record.couples.find(c => c.type === 'groom');

            if (bride) {
                const brideRow = document.createElement('tr');
                brideRow.innerHTML = `
                    <td>${htmlEscape(bride.name)}</td>
                    <td>
                        Birth Date: ${new Date(bride.birth_date).toLocaleDateString()}<br>
                        Birth Place: ${htmlEscape(bride.birth_place)}
                    </td>
                    <td>
                        Parent 1: ${htmlEscape(bride.parent1_name || 'N/A')} (${htmlEscape(bride.parent1_origin || 'N/A')})<br>
                        Parent 2: ${htmlEscape(bride.parent2_name || 'N/A')} (${htmlEscape(bride.parent2_origin || 'N/A')})
                    </td>
                    <td>
                        Date: ${new Date(bride.baptism_date).toLocaleDateString()}<br>
                        Church: ${htmlEscape(bride.baptism_church)}
                    </td>
                    <td>
                        Date: ${new Date(bride.confirmation_date).toLocaleDateString()}<br>
                        Church: ${htmlEscape(bride.confirmation_church)}
                    </td>
                `;
                brideTableBody.appendChild(brideRow);
            } else {
                const brideRow = document.createElement('tr');
                brideRow.innerHTML = '<td colspan="5" class="text-center">No bride information available</td>';
                brideTableBody.appendChild(brideRow);
            }

            if (groom) {
                const groomRow = document.createElement('tr');
                groomRow.innerHTML = `
                    <td>${htmlEscape(groom.name)}</td>
                    <td>
                        Birth Date: ${new Date(groom.birth_date).toLocaleDateString()}<br>
                        Birth Place: ${htmlEscape(groom.birth_place)}
                    </td>
                    <td>
                        Parent 1: ${htmlEscape(groom.parent1_name || 'N/A')} (${htmlEscape(groom.parent1_origin || 'N/A')})<br>
                        Parent 2: ${htmlEscape(groom.parent2_name || 'N/A')} (${htmlEscape(groom.parent2_origin || 'N/A')})
                    </td>
                    <td>
                        Date: ${new Date(groom.baptism_date).toLocaleDateString()}<br>
                        Church: ${htmlEscape(groom.baptism_church)}
                    </td>
                    <td>
                        Date: ${new Date(groom.confirmation_date).toLocaleDateString()}<br>
                        Church: ${htmlEscape(groom.confirmation_church)}
                    </td>
                `;
                groomTableBody.appendChild(groomRow);
            } else {
                const groomRow = document.createElement('tr');
                groomRow.innerHTML = '<td colspan="5" class="text-center">No groom information available</td>';
                groomTableBody.appendChild(groomRow);
            }
        });

    } catch (error) {
        console.error('Error loading marriage records:', error);
        alert('Failed to load marriage records');
    }
}

async function saveMarriageRecord() {
    try {
        const form = document.getElementById('addMarriageForm');


        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

      
        const formData = {
            matrimony_date: document.getElementById('matrimonyDate').value,
            church: document.getElementById('matrimonyChurch').value,
            minister: document.getElementById('matrimonyMinister').value,
          
            bride: {
                name: document.getElementById('brideName').value,
                birth_date: document.getElementById('brideBirthDate').value,
                birth_place: document.getElementById('brideBirthPlace').value,
                parent1_name: document.getElementById('brideParent1Name').value,
                parent1_origin: document.getElementById('brideParent1Origin').value,
                parent2_name: document.getElementById('brideParent2Name').value,
                parent2_origin: document.getElementById('brideParent2Origin').value,
                baptism_date: document.getElementById('brideBaptismDate').value,
                baptism_church: document.getElementById('brideBaptismChurch').value,
                confirmation_date: document.getElementById('brideConfirmationDate').value,
                confirmation_church: document.getElementById('brideConfirmationChurch').value
            },

         
            groom: {
                name: document.getElementById('groomName').value,
                birth_date: document.getElementById('groomBirthDate').value,
                birth_place: document.getElementById('groomBirthPlace').value,
                parent1_name: document.getElementById('groomParent1Name').value,
                parent1_origin: document.getElementById('groomParent1Origin').value,
                parent2_name: document.getElementById('groomParent2Name').value,
                parent2_origin: document.getElementById('groomParent2Origin').value,
                baptism_date: document.getElementById('groomBaptismDate').value,
                baptism_church: document.getElementById('groomBaptismChurch').value,
                confirmation_date: document.getElementById('groomConfirmationDate').value,
                confirmation_church: document.getElementById('groomConfirmationChurch').value
            },

            sponsors: Array.from(document.querySelectorAll('#marriageSponsorsContainer input[name="sponsors[]"]'))
                .map(input => input.value)
                .filter(sponsor => sponsor.trim() !== '')
        };

        const response = await fetch('/GoldTree/crud/matrimony_records/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Failed to save marriage record');
        }

        alert('Marriage record saved successfully!');
        
   
        form.reset();
        form.classList.remove('was-validated');
        const modal = bootstrap.Modal.getInstance(document.getElementById('addMarriageModal'));
        modal.hide();


        if (document.getElementById('filterSacramentType').value === 'Marriage') {
            loadMatrimonyRecords();
        }

    } catch (error) {
        alert(error.message);
    }
}

async function editRecord(id, recordType) {
    try {
        let endpoint = '';
        switch(recordType) {
            case 'First Communion':
                endpoint = `/GoldTree/crud/first_communion_records/get.php?id=${id}`;
                break;
            default:
                endpoint = `/GoldTree/crud/sacramental_records/get.php?id=${id}`;
        }

        const response = await fetch(endpoint);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Failed to fetch record');
        }

        document.getElementById('editId').value = result.id;

        if (recordType === 'First Communion') {
        
            document.getElementById('editName').value = result.name;
            document.getElementById('editGender').value = result.gender;
            document.getElementById('editAddress').value = result.address;

            
            document.getElementById('editBirthDate').value = result.birth_date;
            document.getElementById('editBirthPlace').value = result.birth_place;

         
            document.getElementById('editParent1Name').value = result.parent1_name || '';
            document.getElementById('editParent1Origin').value = result.parent1_origin || '';
            document.getElementById('editParent2Name').value = result.parent2_name || '';
            document.getElementById('editParent2Origin').value = result.parent2_origin || '';

          
            document.getElementById('editBaptismDate').value = result.baptism_date;
            document.getElementById('editBaptismChurch').value = result.baptism_church;

       
            document.getElementById('editChurch').value = result.church;
            document.getElementById('editConfirmationDate').value = result.confirmation_date;
            document.getElementById('editMinister').value = result.minister;
        } else {
            document.getElementById('editName').value = result.name;
            document.getElementById('editAge').value = result.age;
            document.getElementById('editAddress').value = result.address;
            document.getElementById('editSacramentType').value = result.sacrament_type;
            document.getElementById('editDate').value = result.date;
            document.getElementById('editPriestPresiding').value = result.priest_presiding;
        }

        const modal = new bootstrap.Modal(document.getElementById('editRecordModal'));
        modal.show();
    } catch (error) {
        alert(error.message);
    }
}

async function updateRecord(recordType) {
    try {
        const form = document.getElementById('editRecordForm');
        let formData;

        if (recordType === 'First Communion') {
            formData = {
                id: document.getElementById('editId').value,
         
                name: document.getElementById('editName').value.trim(),
                gender: document.getElementById('editGender').value,
                address: document.getElementById('editAddress').value.trim(),

             
                birth_date: document.getElementById('editBirthDate').value,
                birth_place: document.getElementById('editBirthPlace').value.trim(),

              
                parent1_name: document.getElementById('editParent1Name').value.trim(),
                parent1_origin: document.getElementById('editParent1Origin').value.trim(),
                parent2_name: document.getElementById('editParent2Name').value.trim(),
                parent2_origin: document.getElementById('editParent2Origin').value.trim(),

                
                baptism_date: document.getElementById('editBaptismDate').value,
                baptism_church: document.getElementById('editBaptismChurch').value.trim(),

              
                church: document.getElementById('editChurch').value.trim(),
                confirmation_date: document.getElementById('editConfirmationDate').value,
                minister: document.getElementById('editMinister').value.trim()
            };
        } else {
            formData = {
                id: document.getElementById('editId').value,
                name: document.getElementById('editName').value.trim(),
                age: document.getElementById('editAge').value.trim(),
                address: document.getElementById('editAddress').value.trim(),
                sacrament_type: document.getElementById('editSacramentType').value,
                date: document.getElementById('editDate').value.trim(),
                priest_presiding: document.getElementById('editPriestPresiding').value.trim()
            };
        }

        
        const requiredFields = recordType === 'First Communion' ? [
            'name', 'gender', 'address', 'birth_date', 'birth_place',
            'baptism_date', 'baptism_church', 'church', 'confirmation_date', 'minister'
        ] : ['name', 'age', 'address', 'sacrament_type', 'date', 'priest_presiding'];

        for (const field of requiredFields) {
            if (!formData[field]) {
                throw new Error(`Please fill in all required fields (${field} is missing)`);
            }
        }

        const endpoint = recordType === 'First Communion' 
            ? '/GoldTree/crud/first_communion_records/update.php'
            : '/GoldTree/crud/sacramental_records/update.php';

        const response = await fetch(endpoint, {
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

async function deleteRecord(id, recordType) {
    if (recordType === 'First Communion') {
        try {
 
            const response = await fetch(`/GoldTree/crud/first_communion_records/get.php?id=${id}`);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to fetch record details');
            }

         
            document.getElementById('deleteRecordId').value = id;
            document.getElementById('deleteRecordName').textContent = `Name: ${result.name}`;
            document.getElementById('deleteRecordDate').textContent = `Confirmation Date: ${new Date(result.confirmation_date).toLocaleDateString()}`;

     
            const modal = new bootstrap.Modal(document.getElementById('deleteFirstCommunionModal'));
            modal.show();
        } catch (error) {
            alert(error.message);
        }
    } else {
        if (confirm('Are you sure you want to delete this record?')) {
            try {
                const response = await fetch('/GoldTree/crud/sacramental_records/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
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
}

async function confirmDeleteRecord() {
    try {
        const id = document.getElementById('deleteRecordId').value;
        const response = await fetch('/GoldTree/crud/first_communion_records/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Failed to delete record');
        }


        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteFirstCommunionModal'));
        modal.hide();


        alert('First Communion record deleted successfully!');
        location.reload();

    } catch (error) {
        alert(error.message);
    }
}

async function saveRecord() {
    try {
        const form = document.getElementById('addRecordForm');
        const sacramentSelect = form.querySelector('select[name="sacramentType"]');
        const selectedValue = sacramentSelect.value;

        if (!selectedValue) {
            sacramentSelect.classList.add('is-invalid');
            throw new Error('Please select a sacrament type');
        }
        sacramentSelect.classList.remove('is-invalid');

        let formData;
        let endpoint;

 
        const formInputs = {};
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.name && input.name !== 'sacramentType') {
                if (input.name.endsWith('[]')) {
          
                    const baseName = input.name.slice(0, -2);
                    if (!formInputs[baseName]) {
                        formInputs[baseName] = [];
                    }
                    if (input.value.trim()) {
                        formInputs[baseName].push(input.value.trim());
                    }
                } else {
                    formInputs[input.name] = input.value.trim();
                }
            }
        });

        switch(selectedValue) {
            case 'Baptism':
                formData = {
                    name: formInputs.name,
                    gender: formInputs.gender,
                    address: formInputs.address,
                    birth_date: formInputs.birth_date,
                    birth_place: formInputs.birth_place,
                    parent1_name: formInputs.parent1_name || null,
                    parent1_origin: formInputs.parent1_origin || null,
                    parent2_name: formInputs.parent2_name || null,
                    parent2_origin: formInputs.parent2_origin || null,
                    baptism_date: formInputs.baptism_date,
                    minister: formInputs.minister,
                    sponsors: formInputs.sponsors || []
                };
                endpoint = `${document.body.getAttribute('data-base-path')}/crud/baptismal_records/save.php`;
                break;

            case 'Confirmation':
                formData = {
                    name: formInputs.name,
                    gender: formInputs.gender,
                    address: formInputs.address,
                    birth_date: formInputs.birth_date,
                    birth_place: formInputs.birth_place,
                    parent1_name: formInputs.parent1_name || null,
                    parent1_origin: formInputs.parent1_origin || null,
                    parent2_name: formInputs.parent2_name || null,
                    parent2_origin: formInputs.parent2_origin || null,
                    baptism_date: formInputs.baptism_date,
                    minister: formInputs.minister,
                    sponsors: formInputs.sponsors || []
                };
                endpoint = getApiEndpoint('/crud/confirmation_records/save.php');
                break;

            case 'First Communion':
                formData = {
                    name: formInputs.name,
                    gender: formInputs.gender,
                    address: formInputs.address,
                    birth_date: formInputs.birth_date,
                    birth_place: formInputs.birth_place,
                    parent1_name: formInputs.parent1_name || null,
                    parent1_origin: formInputs.parent1_origin || null,
                    parent2_name: formInputs.parent2_name || null,
                    parent2_origin: formInputs.parent2_origin || null,
                    baptism_date: formInputs.baptism_date,
                    baptism_church: formInputs.baptism_church,
                    church: formInputs.church,
                    confirmation_date: formInputs.confirmation_date,
                    minister: formInputs.minister
                };
                endpoint = '/GoldTree/crud/first_communion_records/save.php';
                break;

            case 'Marriage':
            
                if (formInputs.person1_type === formInputs.person2_type) {
                    throw new Error('One person must be a bride and the other must be a groom');
                }

                formData = {
                    matrimony_date: formInputs.matrimony_date,
                    church: formInputs.church,
                    minister: formInputs.minister,
                    couples: [
                        {
                            type: formInputs.person1_type,
                            name: formInputs.person1_name,
                            gender: formInputs.person1_gender,
                            address: formInputs.person1_address,
                            birth_date: formInputs.person1_birth_date,
                            birth_place: formInputs.person1_birth_place,
                            parent1_name: formInputs.person1_parent1_name || null,
                            parent1_origin: formInputs.person1_parent1_origin || null,
                            parent2_name: formInputs.person1_parent2_name || null,
                            parent2_origin: formInputs.person1_parent2_origin || null,
                            baptism_date: formInputs.person1_baptism_date,
                            baptism_church: formInputs.person1_baptism_church,
                            confirmation_date: formInputs.person1_confirmation_date,
                            confirmation_church: formInputs.person1_confirmation_church
                        },
                        {
                            type: formInputs.person2_type,
                            name: formInputs.person2_name,
                            gender: formInputs.person2_gender,
                            address: formInputs.person2_address,
                            birth_date: formInputs.person2_birth_date,
                            birth_place: formInputs.person2_birth_place,
                            parent1_name: formInputs.person2_parent1_name || null,
                            parent1_origin: formInputs.person2_parent1_origin || null,
                            parent2_name: formInputs.person2_parent2_name || null,
                            parent2_origin: formInputs.person2_parent2_origin || null,
                            baptism_date: formInputs.person2_baptism_date,
                            baptism_church: formInputs.person2_baptism_church,
                            confirmation_date: formInputs.person2_confirmation_date,
                            confirmation_church: formInputs.person2_confirmation_church
                        }
                    ],
                    sponsors: formInputs.matrimony_sponsors || []
                };
                endpoint = '/GoldTree/crud/matrimony_records/save.php';
                break;

            default:
                throw new Error('Invalid sacrament type');
        }


        const validateObject = (obj) => {
            for (const [key, value] of Object.entries(obj)) {
                if (value === null || value === undefined || value === '') {
                    if (key.includes('parent') || key.includes('sponsor')) continue; // SKIP OPTIONAL FUCKIGN FIELDS
                    throw new Error(`Please fill in all required fields`);
                }
                if (typeof value === 'object' && !Array.isArray(value)) {
                    validateObject(value);
                }
            }
        };
        validateObject(formData);

        const response = await fetch(endpoint, {
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
        form.reset();
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('addRecordModal'));
        modal.hide();

        await filterRecords();

    } catch (error) {
        alert(error.message);
    }
}



async function filterRecords(page = 1) {
    try {
        const sacramentType = document.getElementById('filterSacramentType').value;
        if (!sacramentType) {
            throw new Error('Please select a sacrament type');
        }

       
        const marriageFilters = document.querySelectorAll('.marriage-filter');
        const communionFilters = document.querySelectorAll('.communion-filter');
        const confirmationFilters = document.querySelectorAll('.confirmation-filter');
        const baptismFilters = document.querySelectorAll('.baptism-filter');
        
        marriageFilters.forEach(filter => {
            filter.style.display = sacramentType === 'Marriage' ? 'block' : 'none';
        });
        
        communionFilters.forEach(filter => {
            filter.style.display = sacramentType === 'First Communion' ? 'block' : 'none';
        });

        confirmationFilters.forEach(filter => {
            filter.style.display = sacramentType === 'Confirmation' ? 'block' : 'none';
        });

        baptismFilters.forEach(filter => {
            filter.style.display = sacramentType === 'Baptism' ? 'block' : 'none';
        });

  
        document.getElementById('baptismTable').style.display = 'none';
        document.getElementById('confirmationTable').style.display = 'none';
        document.getElementById('firstCommunionTable').style.display = 'none';
        document.getElementById('matrimonyTables').style.display = 'none';
        document.getElementById('noRecordsMessage').style.display = 'none';

    
        const buttonContainer = document.getElementById('addRecordButtonContainer');
        if (sacramentType === 'Baptism') {
            buttonContainer.innerHTML = `
         
            `;
            document.getElementById('baptismTable').style.display = 'block';
            await loadBaptismalRecords();
        } else if (sacramentType === 'Confirmation') {
            document.getElementById('confirmationTable').style.display = 'block';
            await loadConfirmationRecords();
        } else if (sacramentType === 'First Communion') {
            buttonContainer.innerHTML = `
            
            `;
            document.getElementById('firstCommunionTable').style.display = 'block';
            await loadFirstCommunionRecords();
        } else if (sacramentType === 'Marriage') {
            buttonContainer.innerHTML = `
            
            `;
            document.getElementById('matrimonyTables').style.display = 'block';
            await loadMatrimonyRecords();
        } else {
            document.getElementById('noRecordsMessage').style.display = 'block';
            document.getElementById('noRecordsMessage').innerHTML = `
                <i class="bi bi-info-circle me-2"></i>
                The records system for ${sacramentType} is being updated.
            `;
        }

    } catch (error) {
        alert(error.message);
    }
}

async function loadBaptismalRecords() {
    try {
        const response = await fetch('/GoldTree/crud/baptismal_records/get_all.php');
        const records = await response.json();

        const tbody = document.getElementById('baptismTableBody');
        tbody.innerHTML = '';

        if (records.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No baptismal records found</td></tr>';
            return;
        }

        records.forEach(record => {
          
            const parentsInfo = [];
            if (record.parent1_name && record.parent1_name !== 'N/A') {
                const origin1 = record.parent1_origin ? (record.parent1_origin !== 'N/A' ? record.parent1_origin : 'N/A') : 'N/A';
                parentsInfo.push(`Parent 1: ${record.parent1_name} (${origin1})`);
            }
            if (record.parent2_name && record.parent2_name !== 'N/A') {
                const origin2 = record.parent2_origin ? (record.parent2_origin !== 'N/A' ? record.parent2_origin : 'N/A') : 'N/A';
                parentsInfo.push(`Parent 2: ${record.parent2_name} (${origin2})`);
            }

            const parentDisplay = parentsInfo.length > 0 ? parentsInfo.join('\n') : 'No parent information';
            const birthDetails = `Born: ${new Date(record.birth_date).toLocaleDateString()}\nPlace: ${record.birth_place}`;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${htmlEscape(record.name)}</td>
                <td style="white-space: pre-line">${htmlEscape(parentDisplay)}</td>
                <td style="white-space: pre-line">${birthDetails}</td>
                <td>${new Date(record.baptism_date).toLocaleDateString()}</td>
                <td>${htmlEscape(record.minister)}</td>
                <td id="sponsors-${record.id}">Loading...</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-primary me-2" onclick="viewBaptismalRecord(${record.id})">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning me-2" onclick="editBaptismalRecord(${record.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteBaptismalRecord(${record.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);

           
            loadBaptismalSponsors(record.id);
        });

    } catch (error) {
        console.error('Error loading baptismal records:', error);
        alert('Failed to load baptismal records');
    }
}

async function loadBaptismalSponsors(recordId) {
    try {
        const response = await fetch(`/GoldTree/crud/baptismal_records/get_sponsors.php?record_id=${recordId}`);
        const sponsors = await response.json();

        const sponsorCell = document.getElementById(`sponsors-${recordId}`);
        if (sponsors.length > 0) {
            sponsorCell.innerHTML = sponsors.map(s => htmlEscape(s.sponsor_name)).join('<br>');
        } else {
            sponsorCell.innerHTML = 'No sponsors listed';
        }
    } catch (error) {
        console.error('Error loading sponsors:', error);
        const sponsorCell = document.getElementById(`sponsors-${recordId}`);
        sponsorCell.innerHTML = 'Failed to load sponsors';
    }
}

async function loadConfirmationRecords(page = 1) {
    try {
    
        const dateFrom = document.getElementById('confirmationDateFrom').value;
        const dateTo = document.getElementById('confirmationDateTo').value;
        const name = document.getElementById('confirmationName').value;
        const parent = document.getElementById('confirmationParent').value;
        const minister = document.getElementById('confirmationMinister').value;

        
        const url = new URL(getApiEndpoint('/crud/confirmation_records/get_all.php'), window.location.origin);
        if (dateFrom) url.searchParams.append('dateFrom', dateFrom);
        if (dateTo) url.searchParams.append('dateTo', dateTo);
        if (name) url.searchParams.append('name', name);
        if (parent) url.searchParams.append('parent', parent);
        if (minister) url.searchParams.append('minister', minister);
        url.searchParams.append('page', page);
        url.searchParams.append('limit', 10); 

        const response = await fetch(url);
        const result = await response.json();
        const records = result.data || [];
        const total = result.total || 0;
        const limit = result.limit || 10;
        const currentPage = result.page || 1;

        const tbody = document.getElementById('confirmationTableBody');
        tbody.innerHTML = '';

        if (records.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No confirmation records found</td></tr>';
            document.getElementById('confirmationPagination')?.remove();
            return;
        }

        records.forEach(record => {
            const parentsInfo = [];
            if (record.parent1_name && record.parent1_name !== 'N/A') {
                const origin1 = record.parent1_origin ? (record.parent1_origin !== 'N/A' ? record.parent1_origin : 'N/A') : 'N/A';
                parentsInfo.push(`Parent 1: ${record.parent1_name} (${origin1})`);
            }
            if (record.parent2_name && record.parent2_name !== 'N/A') {
                const origin2 = record.parent2_origin ? (record.parent2_origin !== 'N/A' ? record.parent2_origin : 'N/A') : 'N/A';
                parentsInfo.push(`Parent 2: ${record.parent2_name} (${origin2})`);
            }

            const parentDisplay = parentsInfo.length > 0 ? parentsInfo.join('\n') : 'No parent information';
            const birthDetails = `Born: ${new Date(record.birth_date).toLocaleDateString()}\nPlace: ${record.birth_place}`;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${htmlEscape(record.name)}</td>
                <td style="white-space: pre-line">${htmlEscape(parentDisplay)}</td>
                <td style="white-space: pre-line">${birthDetails}</td>
                <td>${new Date(record.baptism_date).toLocaleDateString()}</td>
                <td>${htmlEscape(record.minister)}</td>
                <td id="confirmation-sponsors-${record.id}">Loading...</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-primary me-2" onclick="viewConfirmationRecord(${record.id})">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning me-2" onclick="editConfirmationRecord(${record.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteConfirmationRecord(${record.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);

            loadConfirmationSponsors(record.id);
        });

        
        let pagination = document.getElementById('confirmationPagination');
        if (!pagination) {
            pagination = document.createElement('div');
            pagination.id = 'confirmationPagination';
            pagination.className = 'mt-3';
            tbody.parentElement.parentElement.appendChild(pagination);
        }
        const totalPages = Math.ceil(total / limit);
        let html = '<nav><ul class="pagination justify-content-center">';
        
      
        html += `<li class="page-item${currentPage === 1 ? ' disabled' : ''}">
                    <a class="page-link" href="#" ${currentPage !== 1 ? `onclick="loadConfirmationRecords(${currentPage - 1});return false;"` : ''}>
                        Previous
                    </a>
                </li>`;

    
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item${i === currentPage ? ' active' : ''}">
                        <a class="page-link" href="#" onclick="loadConfirmationRecords(${i});return false;">${i}</a>
                    </li>`;
        }

    
        html += `<li class="page-item${currentPage >= totalPages ? ' disabled' : ''}">
                    <a class="page-link" href="#" ${currentPage < totalPages ? `onclick="loadConfirmationRecords(${currentPage + 1});return false;"` : ''}>
                        Next
                    </a>
                </li>`;

        html += '</ul></nav>';
        pagination.innerHTML = html;

    } catch (error) {
        console.error('Error loading confirmation records:', error);
        alert('Failed to load confirmation records');
    }
}

async function loadConfirmationSponsors(recordId) {
    try {
        const response = await fetch(`/GoldTree/crud/confirmation_records/get_sponsors.php?record_id=${recordId}`);
        const sponsors = await response.json();

        const sponsorCell = document.getElementById(`confirmation-sponsors-${recordId}`);
        if (sponsors.length > 0) {
            sponsorCell.innerHTML = sponsors.map(s => htmlEscape(s.sponsor_name)).join('<br>');
        } else {
            sponsorCell.innerHTML = 'No sponsors listed';
        }
    } catch (error) {
        console.error('Error loading sponsors:', error);
        const sponsorCell = document.getElementById(`confirmation-sponsors-${recordId}`);
        sponsorCell.innerHTML = 'Failed to load sponsors';
    }
}

async function editMatrimonyRecord(id) {
    try {
        const response = await fetch(`/GoldTree/crud/matrimony_records/get.php?id=${id}`);
        if (!response.ok) {
            throw new Error('Failed to fetch marriage record');
        }

        const result = await response.json();
        if (!result.data) {
            throw new Error('No data received from server');
        }

        const record = result.data;

        
        document.getElementById('editMarriageId').value = record.id;
        document.getElementById('editMarriageDate').value = record.matrimony_date;
        document.getElementById('editMarriageChurch').value = record.church;
        document.getElementById('editMarriageMinister').value = record.minister;

        const bride = record.couples.find(c => c.type === 'bride');
        if (bride) {
            document.getElementById('editBrideId').value = bride.id;
            document.getElementById('editBrideName').value = bride.name;
            document.getElementById('editBrideBirthDate').value = bride.birth_date;
            document.getElementById('editBrideBirthPlace').value = bride.birth_place;
            document.getElementById('editBrideParent1Name').value = bride.parent1_name || '';
            document.getElementById('editBrideParent1Origin').value = bride.parent1_origin || '';
            document.getElementById('editBrideParent2Name').value = bride.parent2_name || '';
            document.getElementById('editBrideParent2Origin').value = bride.parent2_origin || '';
            document.getElementById('editBrideBaptismDate').value = bride.baptism_date;
            document.getElementById('editBrideBaptismChurch').value = bride.baptism_church;
            document.getElementById('editBrideConfirmationDate').value = bride.confirmation_date;
            document.getElementById('editBrideConfirmationChurch').value = bride.confirmation_church;
        }

       
        const groom = record.couples.find(c => c.type === 'groom');
        if (groom) {
            document.getElementById('editGroomId').value = groom.id;
            document.getElementById('editGroomName').value = groom.name;
            document.getElementById('editGroomBirthDate').value = groom.birth_date;
            document.getElementById('editGroomBirthPlace').value = groom.birth_place;
            document.getElementById('editGroomParent1Name').value = groom.parent1_name || '';
            document.getElementById('editGroomParent1Origin').value = groom.parent1_origin || '';
            document.getElementById('editGroomParent2Name').value = groom.parent2_name || '';
            document.getElementById('editGroomParent2Origin').value = groom.parent2_origin || '';
            document.getElementById('editGroomBaptismDate').value = groom.baptism_date;
            document.getElementById('editGroomBaptismChurch').value = groom.baptism_church;
            document.getElementById('editGroomConfirmationDate').value = groom.confirmation_date;
            document.getElementById('editGroomConfirmationChurch').value = groom.confirmation_church;
        }

       
        const sponsorsContainer = document.getElementById('editMatrimonySponsorsContainer');
        sponsorsContainer.innerHTML = '';
        
        if (record.sponsors && record.sponsors.length > 0) {
            record.sponsors.forEach((sponsor, index) => {
                const sponsorEntry = document.createElement('div');
                sponsorEntry.className = 'sponsor-entry mb-2 d-flex align-items-center';
                sponsorEntry.innerHTML = `
                    <div class="me-2 fw-bold" style="width: 25px;">${index + 1}.</div>
                    <input type="text" class="form-control" name="sponsors[]" value="${htmlEscape(sponsor.sponsor_name)}">
                    <button type="button" class="btn btn-danger ms-2" onclick="removeEditMatrimonySponsor(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                `;
                sponsorsContainer.appendChild(sponsorEntry);
            });
        } else {
        
            addEditMatrimonySponsor();
        }

       
        const modal = new bootstrap.Modal(document.getElementById('editMarriageModal'));
        modal.show();
    } catch (error) {
        console.error('Error editing marriage record:', error);
        alert('Failed to load marriage record for editing');
    }
}

async function updateMarriageRecord() {
    try {
        const form = document.getElementById('editMarriageForm');
        
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        const marriageId = document.getElementById('editMarriageId').value;
        const brideId = document.getElementById('editBrideId').value;
        const groomId = document.getElementById('editGroomId').value;

        const formData = {
            id: marriageId,
            matrimony_date: document.getElementById('editMarriageDate').value,
            church: document.getElementById('editMarriageChurch').value,
            minister: document.getElementById('editMarriageMinister').value,
            
            bride: {
                id: brideId,
                name: document.getElementById('editBrideName').value,
                birth_date: document.getElementById('editBrideBirthDate').value,
                birth_place: document.getElementById('editBrideBirthPlace').value,
                parent1_name: document.getElementById('editBrideParent1Name').value,
                parent1_origin: document.getElementById('editBrideParent1Origin').value,
                parent2_name: document.getElementById('editBrideParent2Name').value,
                parent2_origin: document.getElementById('editBrideParent2Origin').value,
                baptism_date: document.getElementById('editBrideBaptismDate').value,
                baptism_church: document.getElementById('editBrideBaptismChurch').value,
                confirmation_date: document.getElementById('editBrideConfirmationDate').value,
                confirmation_church: document.getElementById('editBrideConfirmationChurch').value
            },
            
            groom: {
                id: groomId,
                name: document.getElementById('editGroomName').value,
                birth_date: document.getElementById('editGroomBirthDate').value,
                birth_place: document.getElementById('editGroomBirthPlace').value,
                parent1_name: document.getElementById('editGroomParent1Name').value,
                parent1_origin: document.getElementById('editGroomParent1Origin').value,
                parent2_name: document.getElementById('editGroomParent2Name').value,
                parent2_origin: document.getElementById('editGroomParent2Origin').value,
                baptism_date: document.getElementById('editGroomBaptismDate').value,
                baptism_church: document.getElementById('editGroomBaptismChurch').value,
                confirmation_date: document.getElementById('editGroomConfirmationDate').value,
                confirmation_church: document.getElementById('editGroomConfirmationChurch').value
            },
            
            sponsors: Array.from(document.getElementsByName('sponsors[]'))
                .map(input => input.value.trim())
                .filter(name => name !== '')
        };

        const response = await fetch('/GoldTree/crud/matrimony_records/update.php', {
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

        alert('Marriage record updated successfully!');
        const modal = bootstrap.Modal.getInstance(document.getElementById('editMarriageModal'));
        modal.hide();
        
    
        await loadMatrimonyRecords();

    } catch (error) {
        console.error('Error updating marriage record:', error);
        alert(error.message || 'Failed to update marriage record');
    }
}

function addEditMatrimonySponsor() {
    const container = document.getElementById('editMatrimonySponsorsContainer');
    const sponsorCount = container.getElementsByClassName('sponsor-entry').length + 1;
    
    const sponsorEntry = document.createElement('div');
    sponsorEntry.className = 'sponsor-entry mb-2 d-flex align-items-center';
    sponsorEntry.innerHTML = `
        <div class="me-2 fw-bold" style="width: 25px;">${sponsorCount}.</div>
        <input type="text" class="form-control" name="sponsors[]" placeholder="Sponsor's Name">
        <button type="button" class="btn btn-danger ms-2" onclick="removeEditMatrimonySponsor(this)">
            <i class="bi bi-trash"></i>
        </button>
    `;
    container.appendChild(sponsorEntry);
    updateSponsorNumbers('editMatrimonySponsorsContainer');
}

function removeEditMatrimonySponsor(button) {
    button.closest('.sponsor-entry').remove();
    updateSponsorNumbers('editMatrimonySponsorsContainer');
}

function updateSponsorNumbers(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const entries = container.getElementsByClassName('sponsor-entry');
    Array.from(entries).forEach((entry, index) => {
        const numberDiv = entry.querySelector('.fw-bold');
        if (numberDiv) {
            numberDiv.textContent = `${index + 1}.`;
        }
    });
}

function deleteMatrimonyRecord(id) {
    try {
        fetch(`/GoldTree/crud/matrimony_records/get.php?id=${id}`)
            .then(response => response.json())
            .then(result => {
                if (!result.data) {
                    throw new Error('No data received from server');
                }

                const record = result.data;
                const bride = record.couples.find(c => c.type === 'bride');
                const groom = record.couples.find(c => c.type === 'groom');
                const coupleName = `${bride ? bride.name : 'Unknown Bride'} & ${groom ? groom.name : 'Unknown Groom'}`;

                document.getElementById('deleteMarriageId').value = id;
                document.getElementById('deleteMarriageDate').textContent = new Date(record.matrimony_date).toLocaleDateString();
                document.getElementById('deleteMarriageCouple').textContent = coupleName;

                const modal = new bootstrap.Modal(document.getElementById('deleteMarriageModal'));
                modal.show();
            })
            .catch(error => {
                console.error('Error loading marriage record for deletion:', error);
                alert('Failed to load marriage record details');
            });
    } catch (error) {
        console.error('Error initiating delete:', error);
        alert('Failed to initiate delete operation');
    }
}

async function confirmDeleteMarriage() {
    try {
        const id = document.getElementById('deleteMarriageId').value;
        
        const response = await fetch('/GoldTree/crud/matrimony_records/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || 'Failed to delete record');
        }

        alert('Marriage record deleted successfully!');
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteMarriageModal'));
        modal.hide();
        
    
        await loadMatrimonyRecords();

    } catch (error) {
        console.error('Error deleting marriage record:', error);
        alert(error.message || 'Failed to delete marriage record');
    }
}

async function loadMatrimonyRecords(page = 1) {
    try {
      
        const dateFrom = document.getElementById('matrimonyDateFrom')?.value || '';
        const dateTo = document.getElementById('matrimonyDateTo')?.value || '';
        const minister = document.getElementById('minister')?.value || '';
        const brideName = document.getElementById('brideName')?.value || '';
        const groomName = document.getElementById('groomName')?.value || '';

        const url = new URL('/GoldTree/crud/matrimony_records/get_all.php', window.location.origin);
        url.searchParams.append('page', page);
        url.searchParams.append('per_page', 10);
        if (dateFrom) url.searchParams.append('dateFrom', dateFrom);
        if (dateTo) url.searchParams.append('dateTo', dateTo);
        if (minister) url.searchParams.append('minister', minister);
        if (brideName) url.searchParams.append('brideName', brideName);
        if (groomName) url.searchParams.append('groomName', groomName);

        const response = await fetch(url);
        if (!response.ok) {
            throw new Error('Failed to fetch matrimony records');
        }

        const result = await response.json();
        if (result.status !== 'success') {
            throw new Error(result.message || 'Failed to load records');
        }

        const records = result.data;
        const detailsBody = document.getElementById('matrimonyDetailsTableBody');
        const brideBody = document.getElementById('matrimonyBrideTableBody');
        const groomBody = document.getElementById('matrimonyGroomTableBody');

      
        detailsBody.innerHTML = '';
        brideBody.innerHTML = '';
        groomBody.innerHTML = '';

        if (!records || records.length === 0) {
            const noRecordsMessage = '<tr><td colspan="7" class="text-center">No matrimony records found</td></tr>';
            detailsBody.innerHTML = noRecordsMessage;
            brideBody.innerHTML = noRecordsMessage;
            groomBody.innerHTML = noRecordsMessage;
            return;
        }

        records.forEach(record => {
         
            if (!record || !record.matrimony_date || !record.church || !record.minister) {
                console.error('Invalid matrimony record:', record);
                return;
            }

        
            const bride = record.couples?.find(c => c.type === 'bride');
            const groom = record.couples?.find(c => c.type === 'groom');

            const detailsRow = document.createElement('tr');
            detailsRow.innerHTML = `
                <td>${new Date(record.matrimony_date).toLocaleDateString()}</td>
                <td>${htmlEscape(bride?.name || 'N/A')}</td>
                <td>${htmlEscape(groom?.name || 'N/A')}</td>
                <td>${htmlEscape(record.church)}</td>
                <td>${htmlEscape(record.minister)}</td>
                <td id="matrimony-sponsors-${record.id}">Loading...</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-primary me-2" onclick="viewMatrimonyRecord(${record.id})">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning me-2" onclick="editMatrimonyRecord(${record.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteMatrimonyRecord(${record.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            detailsBody.appendChild(detailsRow);


            if (record.couples && Array.isArray(record.couples)) {
                record.couples.forEach(couple => {
                    if (!couple || typeof couple !== 'object') {
                        console.error('Invalid couple data:', couple);
                        return;
                    }

                    const personRow = document.createElement('tr');
                    const personInfo = `
                        <td>${couple.name ? htmlEscape(couple.name) : 'N/A'}</td>
                        <td>
                            Born on ${couple.birth_date ? new Date(couple.birth_date).toLocaleDateString() : 'N/A'}<br>
                            at ${couple.birth_place ? htmlEscape(couple.birth_place) : 'N/A'}
                        </td>
                        <td>
                            ${couple.parent1_name ? `${htmlEscape(couple.parent1_name)} (${htmlEscape(couple.parent1_origin)})<br>` : ''}
                            ${couple.parent2_name ? `${htmlEscape(couple.parent2_name)} (${htmlEscape(couple.parent2_origin)})` : 'No parent information'}
                        </td>
                        <td>
                            ${couple.baptism_date ? new Date(couple.baptism_date).toLocaleDateString() : 'N/A'}<br>
                            at ${couple.baptism_church ? htmlEscape(couple.baptism_church) : 'N/A'}
                        </td>
                        <td>
                            ${couple.confirmation_date ? new Date(couple.confirmation_date).toLocaleDateString() : 'N/A'}<br>
                            at ${couple.confirmation_church ? htmlEscape(couple.confirmation_church) : 'N/A'}
                        </td>
                    `;
                    personRow.innerHTML = personInfo;

              
                    if (couple.type === 'bride') {
                        brideBody.appendChild(personRow);
                    } else {
                        groomBody.appendChild(personRow);
                    }
                });

              
                loadMatrimonySponsors(record.id);
            }
        });

    
        const paginationContainer = document.getElementById('matrimonyPagination');
        const totalPages = result.pagination.total_pages;
        const currentPage = result.pagination.current_page;

        let paginationHTML = `
            <nav aria-label="Matrimony records pagination">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="loadMatrimonyRecords(${currentPage - 1}); return false;">Previous</a>
                    </li>
        `;

        for (let i = 1; i <= totalPages; i++) {
            if (
                i === 1 || 
                i === totalPages || 
                (i >= currentPage - 2 && i <= currentPage + 2) 
            ) {
                paginationHTML += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="loadMatrimonyRecords(${i}); return false;">${i}</a>
                    </li>
                `;
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                paginationHTML += `
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                `;
            }
        }

        paginationHTML += `
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="loadMatrimonyRecords(${currentPage + 1}); return false;">Next</a>
                    </li>
                </ul>
            </nav>
        `;

        paginationContainer.innerHTML = paginationHTML;

    } catch (error) {
        console.error('Error loading matrimony records:', error);
        alert('Failed to load matrimony records');
    }
}

async function loadMatrimonySponsors(recordId) {
    try {
        const response = await fetch(`/GoldTree/crud/matrimony_records/get_sponsors.php?record_id=${recordId}`);
        const result = await response.json();

        if (result.status !== 'success') {
            throw new Error(result.message || 'Failed to load sponsors');
        }

        const sponsors = result.data;
        const sponsorCell = document.getElementById(`matrimony-sponsors-${recordId}`);
        if (sponsors && sponsors.length > 0) {
            sponsorCell.innerHTML = sponsors.map(s => htmlEscape(s.sponsor_name)).join('<br>');
        } else {
            sponsorCell.innerHTML = 'No sponsors listed';
        }
    } catch (error) {
        console.error('Error loading sponsors:', error);
        const sponsorCell = document.getElementById(`matrimony-sponsors-${recordId}`);
        sponsorCell.innerHTML = 'Failed to load sponsors';
    }
}

async function viewMatrimonyRecord(id) {
    try {
       
        const response = await fetch(`/GoldTree/crud/matrimony_records/get.php?id=${id}`);
        if (!response.ok) {
            throw new Error('Failed to fetch marriage record');
        }

        const result = await response.json();
        console.log('Received data:', result);

        if (!result.data) {
            throw new Error('No data received from server');
        }

        const record = result.data;


        document.getElementById('viewMarriageDate').textContent = record.matrimony_date ? 
            new Date(record.matrimony_date).toLocaleDateString() : 'N/A';
        document.getElementById('viewMarriageChurch').textContent = record.church || 'N/A';
        document.getElementById('viewMarriageMinister').textContent = record.minister || 'N/A';

        ['Bride', 'Groom'].forEach(prefix => {
            document.getElementById(`view${prefix}Name`).textContent = 'N/A';
            document.getElementById(`view${prefix}BirthDate`).textContent = 'N/A';
            document.getElementById(`view${prefix}BirthPlace`).textContent = 'N/A';
            document.getElementById(`view${prefix}Parent1`).textContent = 'N/A';
            document.getElementById(`view${prefix}Parent2`).textContent = 'N/A';
            document.getElementById(`view${prefix}BaptismDate`).textContent = 'N/A';
            document.getElementById(`view${prefix}BaptismChurch`).textContent = 'N/A';
            document.getElementById(`view${prefix}ConfirmationDate`).textContent = 'N/A';
            document.getElementById(`view${prefix}ConfirmationChurch`).textContent = 'N/A';
        });


        if (record.couples && Array.isArray(record.couples)) {
            record.couples.forEach(couple => {
                if (couple && typeof couple === 'object' && couple.type) {
                    const prefix = couple.type.toLowerCase() === 'bride' ? 'Bride' : 'Groom';
                    
                
                    document.getElementById(`view${prefix}Name`).textContent = couple.name || 'N/A';
                    document.getElementById(`view${prefix}BirthDate`).textContent = couple.birth_date ? 
                        new Date(couple.birth_date).toLocaleDateString() : 'N/A';
                    document.getElementById(`view${prefix}BirthPlace`).textContent = couple.birth_place || 'N/A';
                    
                   
                    document.getElementById(`view${prefix}Parent1`).textContent = couple.parent1_name ? 
                        `${couple.parent1_name} (${couple.parent1_origin || 'N/A'})` : 'N/A';
                    document.getElementById(`view${prefix}Parent2`).textContent = couple.parent2_name ? 
                        `${couple.parent2_name} (${couple.parent2_origin || 'N/A'})` : 'N/A';
                    
                  
                    document.getElementById(`view${prefix}BaptismDate`).textContent = couple.baptism_date ? 
                        new Date(couple.baptism_date).toLocaleDateString() : 'N/A';
                    document.getElementById(`view${prefix}BaptismChurch`).textContent = couple.baptism_church || 'N/A';
                    
                   
                    document.getElementById(`view${prefix}ConfirmationDate`).textContent = couple.confirmation_date ? 
                        new Date(couple.confirmation_date).toLocaleDateString() : 'N/A';
                    document.getElementById(`view${prefix}ConfirmationChurch`).textContent = couple.confirmation_church || 'N/A';
                }
            });
        }

   
        const sponsorsContainer = document.getElementById('viewMarriageSponsors');
        if (record.sponsors && Array.isArray(record.sponsors) && record.sponsors.length > 0) {
            const sponsorsList = record.sponsors
                .map((sponsor, index) => `${index + 1}. ${sponsor.sponsor_name}`)
                .join('<br>');
            sponsorsContainer.innerHTML = sponsorsList;
        } else {
            sponsorsContainer.innerHTML = 'No sponsors listed';
        }

   
        const modal = new bootstrap.Modal(document.getElementById('viewMarriageModal'));
        const editModal = new bootstrap.Modal(document.getElementById('editMarriageModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteMarriageModal'));
        modal.show();

    } catch (error) {
        console.error('Error viewing marriage record:', error);
        alert('Failed to load marriage record details');
    }
}

async function loadFirstCommunionRecords(page = 1) {
    try {
        const dateFrom = document.getElementById('communionDateFrom')?.value || '';
        const dateTo = document.getElementById('communionDateTo')?.value || '';
        const name = document.getElementById('communionName')?.value || '';
        const parent = document.getElementById('communionParent')?.value || '';
        const minister = document.getElementById('communionMinister')?.value || '';
 
        const url = new URL('/GoldTree/crud/first_communion_records/get_all.php', window.location.origin);
        url.searchParams.append('page', page);
        url.searchParams.append('per_page', 10);
        if (dateFrom) url.searchParams.append('dateFrom', dateFrom);
        if (dateTo) url.searchParams.append('dateTo', dateTo);
        if (name) url.searchParams.append('name', name);
        if (parent) url.searchParams.append('parent', parent);
        if (minister) url.searchParams.append('minister', minister);

        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const result = await response.json();

   
        if (result.error) {
            throw new Error(result.error);
        }

       
        const records = result.data || [];
        
   
        const tableBody = document.getElementById('firstCommunionTableBody');
        tableBody.innerHTML = '';

        if (records.length === 0) {
            document.getElementById('firstCommunionTable').style.display = 'none';
            document.getElementById('noRecordsMessage').style.display = 'block';
            return;
        }

        document.getElementById('firstCommunionTable').style.display = 'block';
        document.getElementById('noRecordsMessage').style.display = 'none';

        records.forEach(record => {
            const row = document.createElement('tr');
            
           
            const birthDate = new Date(record.birth_date).toLocaleDateString();
            const baptismDate = new Date(record.baptism_date).toLocaleDateString();
            const communionDate = new Date(record.communion_date).toLocaleDateString();
          
            const parentsInfo = [];
            if (record.parent1_name) {
                parentsInfo.push(`${record.parent1_name}${record.parent1_origin ? ` (${record.parent1_origin})` : ''}`);
            }
            if (record.parent2_name) {
                parentsInfo.push(`${record.parent2_name}${record.parent2_origin ? ` (${record.parent2_origin})` : ''}`);
            }

           
            const parentsCell = document.createElement('td');
            parentsCell.innerHTML = parentsInfo.length ? parentsInfo.join('<br>') : 'N/A';

          
            const birthDetails = `${birthDate}<br>${htmlEscape(record.birth_place)}`;
   
            const baptismDetails = `${baptismDate}<br>${htmlEscape(record.baptism_church)}`;

            row.innerHTML = `
                <td class="text-nowrap">${htmlEscape(record.name)}</td>
                <td class="text-wrap">${parentsInfo.length ? parentsInfo.join('<br>') : 'N/A'}</td>
                <td class="text-nowrap">${birthDetails}</td>
                <td class="text-nowrap">${baptismDetails}</td>
                <td class="text-nowrap">${new Date(record.communion_date).toLocaleDateString()}</td>
                <td class="text-wrap">${htmlEscape(record.minister)}</td>
                <td class="text-end">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary me-1" onclick="viewFirstCommunionRecord(${record.id})" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning me-1" onclick="editRecord(${record.id}, 'First Communion')" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteRecord(${record.id}, 'First Communion')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);
        });

   
        const paginationContainer = document.getElementById('firstCommunionPagination');
        const totalPages = result.pagination.total_pages;
        const currentPage = result.pagination.current_page;

        let paginationHTML = `
            <nav aria-label="First Communion records pagination">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="loadFirstCommunionRecords(${currentPage - 1}); return false;">Previous</a>
                    </li>
        `;

        for (let i = 1; i <= totalPages; i++) {
            if (
                i === 1 ||
                i === totalPages || 
                (i >= currentPage - 2 && i <= currentPage + 2)
            ) {
                paginationHTML += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="loadFirstCommunionRecords(${i}); return false;">${i}</a>
                    </li>
                `;
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                paginationHTML += `
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                `;
            }
        }

        paginationHTML += `
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="loadFirstCommunionRecords(${currentPage + 1}); return false;">Next</a>
                    </li>
                </ul>
            </nav>
        `;

        paginationContainer.innerHTML = paginationHTML;

    } catch (error) {
        console.error('Error loading First Communion records:', error);
        alert('Failed to load First Communion records');
    }
}

function addSponsor(prefix = '') {

    const containerId = prefix + 'SponsorsContainer';
    const container = document.getElementById(containerId);
    if (!container) {
        console.error('Sponsors container not found:', containerId);
        return;
    }

    const sponsorEntries = container.getElementsByClassName('sponsor-entry');
    const sponsorCount = sponsorEntries.length + 1;
    

    const sponsorEntry = document.createElement('div');
    sponsorEntry.className = 'sponsor-entry mb-2 d-flex align-items-center';
    sponsorEntry.innerHTML = `
        <div class="me-2 fw-bold" style="width: 25px;">${sponsorCount}.</div>
        <input type="text" class="form-control" name="sponsors[]" placeholder="Sponsor's Name (Optional)">
        <button type="button" class="btn btn-danger ms-2" onclick="removeSponsor(this)">
            <i class="bi bi-trash"></i>
        </button>
    `;
    container.appendChild(sponsorEntry);
}

function removeSponsor(button) {
  
    const entry = button.closest('.sponsor-entry');
    const container = entry.parentElement;
    if (!container) {
        console.error('Sponsor container not found');
        return;
    }
    

    entry.remove();
    

    const sponsorEntries = container.getElementsByClassName('sponsor-entry');
    Array.from(sponsorEntries).forEach((entry, index) => {
        const numberDiv = entry.querySelector('div');
        if (numberDiv) {
            numberDiv.textContent = `${index + 1}.`;
        }
    });
}

async function validateConfirmationForm(formData) {
    const requiredFields = {
        name: 'Full Name',
        gender: 'Gender',
        address: 'Address',
        birth_date: 'Birth Date',
        birth_place: 'Birth Place',
        baptism_date: 'Baptism Date',
        minister: 'Minister'
    };

    for (const [field, label] of Object.entries(requiredFields)) {
        if (!formData[field]) {
            throw new Error(`Please fill in ${label}`);
        }
    }


    const birthDate = new Date(formData.birth_date);
    if (birthDate > new Date()) {
        throw new Error('Birth date cannot be in the future');
    }

    const baptismDate = new Date(formData.baptism_date);
    if (baptismDate < birthDate) {
        throw new Error('Baptism date cannot be before birth date');
    }


    if (formData.parent1_name && formData.parent1_name !== 'N/A' && !formData.parent1_origin) {
        throw new Error('Please provide origin for Parent 1');
    }
    if (formData.parent2_name && formData.parent2_name !== 'N/A' && !formData.parent2_origin) {
        throw new Error('Please provide origin for Parent 2');
    }

    if (!formData.sponsors) {
        formData.sponsors = [];
    }
}

async function saveConfirmationRecord() {
    try {
        const form = document.getElementById('addConfirmationForm');
        
        const container = document.getElementById('confirmationSponsorsContainer');
        let sponsors = [];
        if (container) {
            const sponsorInputs = container.querySelectorAll('input[name="sponsors[]"]');
            sponsors = Array.from(sponsorInputs)
                .map(input => input.value.trim())
                .filter(value => value !== ''); 
        }

        const formData = {
            name: document.getElementById('confirmationName').value.trim(),
            gender: document.getElementById('confirmationGender').value,
            address: document.getElementById('confirmationAddress').value.trim(),
            birth_date: document.getElementById('confirmationBirthDate').value,
            birth_place: document.getElementById('confirmationBirthPlace').value.trim(),
            parent1_name: document.getElementById('confirmationParent1Name').value.trim() || 'N/A',
            parent1_origin: document.getElementById('confirmationParent1Origin').value.trim() || 'N/A',
            parent2_name: document.getElementById('confirmationParent2Name').value.trim() || 'N/A',
            parent2_origin: document.getElementById('confirmationParent2Origin').value.trim() || 'N/A',
            baptism_date: document.getElementById('baptismDate').value,
            minister: document.getElementById('confirmationMinister').value.trim(),
            sponsors: sponsors
        };

  
        await validateConfirmationForm(formData);

        const response = await fetch('/GoldTree/crud/confirmation_records/save.php', {
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

        alert('Confirmation record saved successfully!');
        form.reset();

        const sponsorsContainer = document.getElementById('confirmationSponsorsContainer');
        if (sponsorsContainer) {
            sponsorsContainer.innerHTML = `
                <div class="sponsor-entry mb-2 d-flex align-items-center">
                    <div class="me-2 fw-bold" style="width: 25px;">1.</div>
                    <input type="text" class="form-control" name="sponsors[]" placeholder="Sponsor's Name (Optional)">
                    <button type="button" class="btn btn-danger ms-2" onclick="removeSponsor(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
        }
        //
    
        const modal = bootstrap.Modal.getInstance(document.getElementById('addConfirmationModal'));
        modal.hide();

        
        await loadConfirmationRecords();

    } catch (error) {
        alert(error.message);
    }
}

async function loadBaptismalRecords(page = 1) {
    try {
     
        const dateFrom = document.getElementById('baptismDateFrom')?.value || '';
        const dateTo = document.getElementById('baptismDateTo')?.value || '';
        const name = document.getElementById('baptismName')?.value || '';
        const parent = document.getElementById('baptismParent')?.value || '';
        const minister = document.getElementById('baptismMinister')?.value || '';
        const limit = 10; 

        const basePath = document.body.getAttribute('data-base-path') || '/GoldTree';
        const url = new URL(`${basePath}/crud/baptismal_records/get_all.php`, window.location.origin);
        url.searchParams.append('page', page);
        url.searchParams.append('limit', limit);
        if (dateFrom) url.searchParams.append('dateFrom', dateFrom);
        if (dateTo) url.searchParams.append('dateTo', dateTo);
        if (name) url.searchParams.append('name', name);
        if (parent) url.searchParams.append('parent', parent);
        if (minister) url.searchParams.append('minister', minister);

        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        const { records, pagination } = data;

        const tbody = document.getElementById('baptismTableBody');
        tbody.innerHTML = '';

        if (records.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No Baptism records found</td></tr>';
            return;
        }

        records.forEach(record => {
            const parentsInfo = [];
            if (record.parent1_name) {
                parentsInfo.push(`${record.parent1_name} (${record.parent1_origin})`);
            }
            if (record.parent2_name) {
                parentsInfo.push(`${record.parent2_name} (${record.parent2_origin})`);
            }

            const birthDetails = `Born: ${new Date(record.birth_date).toLocaleDateString()}<br>Place: ${record.birth_place}`;

            const row = document.createElement('tr');
            const parentsCell = document.createElement('td');
            parentsInfo.forEach((info, index) => {
                parentsCell.appendChild(document.createTextNode(info));
                if (index < parentsInfo.length - 1) {
                    parentsCell.appendChild(document.createElement('br'));
                }
            });

            row.innerHTML = `
                <td>${htmlEscape(record.name)}</td>
                <td></td>
                <td>${birthDetails}</td>
                <td>${new Date(record.baptism_date).toLocaleDateString()}</td>
                <td>${htmlEscape(record.minister)}</td>
                <td>${record.sponsors ? record.sponsors.join(', ') : ''}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-primary me-2" onclick="viewBaptismalRecord(${record.id})">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning me-2" onclick="editBaptismalRecord(${record.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteBaptismalRecord(${record.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            row.cells[1].replaceWith(parentsCell);
            tbody.appendChild(row);
        });

      
        const paginationContainer = document.getElementById('baptismPagination');
        if (paginationContainer) {
            let paginationHtml = '<nav aria-label="Baptism records pagination"><ul class="pagination justify-content-center mb-0">';
           
            paginationHtml += `
                <li class="page-item ${pagination.page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); loadBaptismalRecords(${pagination.page - 1})" ${pagination.page === 1 ? 'tabindex="-1" aria-disabled="true"' : ''}>Previous</a>
                </li>
            `;

          
            for (let i = 1; i <= pagination.totalPages; i++) {
                paginationHtml += `
                    <li class="page-item ${pagination.page === i ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="event.preventDefault(); loadBaptismalRecords(${i})">${i}</a>
                    </li>
                `;
            }

      
            paginationHtml += `
                <li class="page-item ${pagination.page === pagination.totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); loadBaptismalRecords(${pagination.page + 1})" ${pagination.page === pagination.totalPages ? 'tabindex="-1" aria-disabled="true"' : ''}>Next</a>
                </li>
            `;

            paginationHtml += '</ul></nav>';
            paginationContainer.innerHTML = paginationHtml;
        }

    } catch (error) {
        console.error('Error loading Baptism records:', error);
        alert('Failed to load Baptism records');
    }
}

async function validateBaptismForm(formData) {
    const requiredFields = {
        name: 'Full Name',
        gender: 'Gender',
        address: 'Address',
        birth_date: 'Birth Date',
        birth_place: 'Birth Place',
        baptism_date: 'Baptism Date',
        minister: 'Minister'
    };

    for (const [field, label] of Object.entries(requiredFields)) {
        if (!formData[field]) {
            throw new Error(`Please fill in ${label}`);
        }
    }

  
    const birthDate = new Date(formData.birth_date);
    if (birthDate > new Date()) {
        throw new Error('Birth date cannot be in the future');
    }

  
    const baptismDate = new Date(formData.baptism_date);
    if (baptismDate < birthDate) {
        throw new Error('Baptism date cannot be before birth date');
    }

 
    if (formData.parent1_name && formData.parent1_name !== 'N/A' && !formData.parent1_origin) {
        throw new Error('Please provide origin for Parent 1');
    }
    if (formData.parent2_name && formData.parent2_name !== 'N/A' && !formData.parent2_origin) {
        throw new Error('Please provide origin for Parent 2');
    }

  
    if (!formData.sponsors) {
        formData.sponsors = [];
    }
}

async function saveBaptismalRecord() {
    try {
        const form = document.getElementById('addBaptismForm');
        
       
        const container = document.getElementById('baptismSponsorsContainer');
        let sponsors = [];
        if (container) {
            const sponsorInputs = container.querySelectorAll('input[name="sponsors[]"]');
            sponsors = Array.from(sponsorInputs)
                .map(input => input.value.trim())
                .filter(value => value !== '');
        }

        const formData = {
            name: document.getElementById('baptismName').value.trim(),
            gender: document.getElementById('baptismGender').value,
            address: document.getElementById('baptismAddress').value.trim(),
            birth_date: document.getElementById('baptismBirthDate').value,
            birth_place: document.getElementById('baptismBirthPlace').value.trim(),
            parent1_name: document.getElementById('baptismParent1Name').value.trim() || 'N/A',
            parent1_origin: document.getElementById('baptismParent1Origin').value.trim() || 'N/A',
            parent2_name: document.getElementById('baptismParent2Name').value.trim() || 'N/A',
            parent2_origin: document.getElementById('baptismParent2Origin').value.trim() || 'N/A',
            baptism_date: document.getElementById('baptismDate').value,
            minister: document.getElementById('baptismMinister').value.trim(),
            sponsors: sponsors
        };

    
        await validateBaptismForm(formData);

       
        const basePath = document.body.getAttribute('data-base-path');
        const response = await fetch(`${basePath}/crud/baptismal_records/save.php`, {
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

        if (result.status === 'error') {
            throw new Error(result.message);
        }

       
        alert('Baptismal record saved successfully!');
        form.reset();
        
        
        const sponsorsContainer = document.getElementById('baptismSponsorsContainer');
        if (sponsorsContainer) {
            sponsorsContainer.innerHTML = `
                <div class="sponsor-entry mb-2 d-flex align-items-center">
                    <div class="me-2 fw-bold" style="width: 25px;">1.</div>
                    <input type="text" class="form-control" name="sponsors[]" placeholder="Sponsor's Name (Optional)">
                    <button type="button" class="btn btn-danger ms-2" onclick="removeSponsor(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
        }
        
     
        const modal = bootstrap.Modal.getInstance(document.getElementById('addBaptismModal'));
        modal.hide();

    
        await loadBaptismalRecords();

    } catch (error) {
        alert(error.message);
    }
}

async function editBaptismalRecord(id) {
    try {
       
        const response = await fetch(getApiEndpoint(`/crud/baptismal_records/get.php?id=${id}`));
        const record = await response.json();

        if (!response.ok) {
            throw new Error(record.message || 'Failed to fetch record');
        }

        document.getElementById('editBaptismId').value = record.id;
        document.getElementById('editBaptismName').value = record.name;
        document.getElementById('editBaptismGender').value = record.gender;
        document.getElementById('editBaptismAddress').value = record.address;
        document.getElementById('editBaptismBirthDate').value = record.birth_date;
        document.getElementById('editBaptismBirthPlace').value = record.birth_place;
        document.getElementById('editBaptismParent1Name').value = record.parent1_name === 'N/A' ? '' : record.parent1_name;
        document.getElementById('editBaptismParent1Origin').value = record.parent1_origin === 'N/A' ? '' : record.parent1_origin;
        document.getElementById('editBaptismParent2Name').value = record.parent2_name === 'N/A' ? '' : record.parent2_name;
        document.getElementById('editBaptismParent2Origin').value = record.parent2_origin === 'N/A' ? '' : record.parent2_origin;
        document.getElementById('editBaptismDate').value = record.baptism_date;
        document.getElementById('editBaptismMinister').value = record.minister;


        const sponsorsContainer = document.getElementById('editBaptismSponsorsContainer');
        sponsorsContainer.innerHTML = ''; 

        const sponsorsResponse = await fetch(`/GoldTree/crud/baptismal_records/get_sponsors.php?record_id=${id}`);
        const sponsors = await sponsorsResponse.json();

        if (sponsors && sponsors.length > 0) {
            sponsors.forEach((sponsor, index) => {
                const sponsorEntry = document.createElement('div');
                sponsorEntry.className = 'sponsor-entry mb-2 d-flex align-items-center';
                sponsorEntry.innerHTML = `
                    <div class="me-2 fw-bold" style="width: 25px;">${index + 1}.</div>
                    <input type="text" class="form-control" name="sponsors[]" value="${sponsor.sponsor_name}" placeholder="Sponsor's Name (Optional)">
                    <button type="button" class="btn btn-danger ms-2" onclick="removeSponsor(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                `;
                sponsorsContainer.appendChild(sponsorEntry);
            });
        } else {
         
            const sponsorEntry = document.createElement('div');
            sponsorEntry.className = 'sponsor-entry mb-2 d-flex align-items-center';
            sponsorEntry.innerHTML = `
                <div class="me-2 fw-bold" style="width: 25px;">1.</div>
                <input type="text" class="form-control" name="sponsors[]" placeholder="Sponsor's Name (Optional)">
                <button type="button" class="btn btn-danger ms-2" onclick="removeSponsor(this)">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            sponsorsContainer.appendChild(sponsorEntry);
        }

      
        const modal = new bootstrap.Modal(document.getElementById('editBaptismModal'));
        modal.show();

    } catch (error) {
        console.error('Error editing baptismal record:', error);
        alert('Failed to load baptismal record details');
    }
}

async function deleteBaptismalRecord(id) {
    try {
       
        const response = await fetch(`/GoldTree/crud/baptismal_records/get.php?id=${id}`);
        const record = await response.json();

        if (!response.ok) {
            throw new Error(record.message || 'Failed to fetch record');
        }


        document.getElementById('deleteBaptismId').value = record.id;
        document.getElementById('deleteBaptismName').textContent = `Name: ${record.name}`;
        document.getElementById('deleteBaptismDate').textContent = 
            `Baptism Date: ${new Date(record.baptism_date).toLocaleDateString()}`;

        const modal = new bootstrap.Modal(document.getElementById('deleteBaptismModal'));
        modal.show();

    } catch (error) {
        console.error('Error preparing to delete baptismal record:', error);
        alert('Failed to load record details');
    }
}

async function confirmDeleteBaptismalRecord() {
    try {
        const id = document.getElementById('deleteBaptismId').value;
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetch(getApiEndpoint('/crud/baptismal_records/delete.php'), {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || 'Failed to delete record');
        }

        if (result.error) {
            throw new Error(result.error);
        }

        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteBaptismModal'));
        modal.hide();

   
        alert(result.message || 'Baptismal record deleted successfully');

    
        await loadBaptismalRecords();

    } catch (error) {
        alert(error.message);
    }
}

async function updateBaptismalRecord() {
    try {
        const form = document.getElementById('editBaptismForm');
        

        const container = document.getElementById('editBaptismSponsorsContainer');
        let sponsors = [];
        if (container) {
            const sponsorInputs = container.querySelectorAll('input[name="sponsors[]"]');
            sponsors = Array.from(sponsorInputs)
                .map(input => input.value.trim())
                .filter(value => value !== ''); 
        }

        const formData = {
            id: document.getElementById('editBaptismId').value,
            name: document.getElementById('editBaptismName').value.trim(),
            gender: document.getElementById('editBaptismGender').value,
            address: document.getElementById('editBaptismAddress').value.trim(),
            birth_date: document.getElementById('editBaptismBirthDate').value,
            birth_place: document.getElementById('editBaptismBirthPlace').value.trim(),
            parent1_name: document.getElementById('editBaptismParent1Name').value.trim() || 'N/A',
            parent1_origin: document.getElementById('editBaptismParent1Origin').value.trim() || 'N/A',
            parent2_name: document.getElementById('editBaptismParent2Name').value.trim() || 'N/A',
            parent2_origin: document.getElementById('editBaptismParent2Origin').value.trim() || 'N/A',
            baptism_date: document.getElementById('editBaptismDate').value,
            minister: document.getElementById('editBaptismMinister').value.trim(),
            sponsors: sponsors
        };

      
        await validateBaptismForm(formData);

       
        const response = await fetch('/GoldTree/crud/baptismal_records/update.php', {
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

       
        alert('Baptismal record updated successfully!');
        const modal = bootstrap.Modal.getInstance(document.getElementById('editBaptismModal'));
        modal.hide();


        await loadBaptismalRecords();

    } catch (error) {
        alert(error.message);
    }
}

async function viewBaptismalRecord(id) {
    try {

        const response = await fetch(`/GoldTree/crud/baptismal_records/get.php?id=${id}`);
        const record = await response.json();

        if (!response.ok) {
            throw new Error(record.message || 'Failed to fetch record');
        }

        document.getElementById('viewBaptismName').textContent = record.name;
        document.getElementById('viewBaptismGender').textContent = 
            record.gender.charAt(0).toUpperCase() + record.gender.slice(1);
        document.getElementById('viewBaptismAddress').textContent = record.address;
        document.getElementById('viewBaptismBirthDate').textContent = 
            new Date(record.birth_date).toLocaleDateString();
        document.getElementById('viewBaptismBirthPlace').textContent = record.birth_place;

        const parent1Info = record.parent1_name && record.parent1_name !== 'N/A' 
            ? `${record.parent1_name} (${record.parent1_origin || 'N/A'})`
            : 'Not provided';
        const parent2Info = record.parent2_name && record.parent2_name !== 'N/A'
            ? `${record.parent2_name} (${record.parent2_origin || 'N/A'})`
            : 'Not provided';
            
        document.getElementById('viewBaptismParent1').textContent = parent1Info;
        document.getElementById('viewBaptismParent2').textContent = parent2Info;
        
        document.getElementById('viewBaptismDate').textContent = 
            new Date(record.baptism_date).toLocaleDateString();
        document.getElementById('viewBaptismMinister').textContent = record.minister;

        const sponsorsResponse = await fetch(`/GoldTree/crud/baptismal_records/get_sponsors.php?record_id=${id}`);
        const sponsors = await sponsorsResponse.json();

        const sponsorsContainer = document.getElementById('viewBaptismSponsors');
        if (sponsors && sponsors.length > 0) {
            const sponsorsList = sponsors.map((s, index) => 
                `<div class="mb-1">${index + 1}. ${s.sponsor_name}</div>`
            ).join('');
            sponsorsContainer.innerHTML = sponsorsList;
        } else {
            sponsorsContainer.innerHTML = '<p>No sponsors listed</p>';
        }

        const modal = new bootstrap.Modal(document.getElementById('viewBaptismModal'));
        modal.show();

    } catch (error) {
        console.error('Error viewing baptismal record:', error);
        alert('Failed to load baptismal record details');
    }
}

function htmlEscape(str) {
    return str
        ? str.replace(/&/g, '&amp;')
             .replace(/</g, '&lt;')
             .replace(/>/g, '&gt;')
             .replace(/"/g, '&quot;')
             .replace(/'/g, '&#039;')
        : '';
}

function addMatrimonySponsor() {
    const container = document.getElementById('matrimonySponsorsContainer');
    const sponsorCount = container.getElementsByClassName('sponsor-entry').length + 1;
    
    const sponsorEntry = document.createElement('div');
    sponsorEntry.className = 'sponsor-entry mb-2 d-flex align-items-center';
    sponsorEntry.innerHTML = `
        <div class="me-2 fw-bold" style="width: 25px;">${sponsorCount}.</div>
        <input type="text" class="form-control" name="sponsors[]" placeholder="Sponsor's Name">
        <button type="button" class="btn btn-danger ms-2" onclick="removeMatrimonySponsor(this)">
            <i class="bi bi-trash"></i>
        </button>
    `;
    container.appendChild(sponsorEntry);
}

function removeMatrimonySponsor(button) {
    const entry = button.closest('.sponsor-entry');
    const container = entry.parentElement;
    
    entry.remove();
    
    const sponsorEntries = container.getElementsByClassName('sponsor-entry');
    Array.from(sponsorEntries).forEach((entry, index) => {
        const numberDiv = entry.querySelector('div');
        if (numberDiv) {
            numberDiv.textContent = `${index + 1}.`;
        }
    });
}

function validateMarriageForm() {
    const form = document.getElementById('addMarriageForm');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return false;
    }

    const marriageDate = new Date(form.matrimonyDate.value);
    const today = new Date();

    if (marriageDate > today) {
        alert('Marriage date cannot be in the future');
        return false;
    }

    const brideBirthDate = new Date(form.brideBirthDate.value);
    const brideBaptismDate = new Date(form.brideBaptismDate.value);
    const brideConfirmationDate = new Date(form.brideConfirmationDate.value);

    if (brideBirthDate > today) {
        alert("Bride's birth date cannot be in the future");
        return false;
    }
    if (brideBaptismDate < brideBirthDate) {
        alert("Bride's baptism date cannot be before birth date");
        return false;
    }
    if (brideConfirmationDate < brideBaptismDate) {
        alert("Bride's confirmation date cannot be before baptism date");
        return false;
    }

    const groomBirthDate = new Date(form.groomBirthDate.value);
    const groomBaptismDate = new Date(form.groomBaptismDate.value);
    const groomConfirmationDate = new Date(form.groomConfirmationDate.value);

    if (groomBirthDate > today) {
        alert("Groom's birth date cannot be in the future");
        return false;
    }
    if (groomBaptismDate < groomBirthDate) {
        alert("Groom's baptism date cannot be before birth date");
        return false;
    }
    if (groomConfirmationDate < groomBaptismDate) {
        alert("Groom's confirmation date cannot be before baptism date");
        return false;
    }

    return true;
}

async function saveMarriageRecord() {
    try {
        if (!validateMarriageForm()) {
            return;
        }

        const form = document.getElementById('addMarriageForm');
        
     
        const sponsorsInputs = Array.from(document.querySelectorAll('#matrimonySponsorsContainer input[name="sponsors[]"]'));
        const sponsors = sponsorsInputs.map(input => input.value).filter(sponsor => sponsor.trim() !== '');

    
        const formData = {
            matrimonyDate: form.matrimonyDate.value,
            church: form.church.value,
            minister: form.minister.value,
            bride: {
                name: form.brideName.value,
                birth_date: form.brideBirthDate.value,
                birth_place: form.brideBirthPlace.value,
                parent1_name: form.brideParent1Name.value || 'N/A',
                parent1_origin: form.brideParent1Origin.value || 'N/A',
                parent2_name: form.brideParent2Name.value || 'N/A',
                parent2_origin: form.brideParent2Origin.value || 'N/A',
                gender: 'female',
                baptism_date: form.brideBaptismDate.value,
                baptism_church: form.brideBaptismChurch.value,
                confirmation_date: form.brideConfirmationDate.value,
                confirmation_church: form.brideConfirmationChurch.value
            },
            groom: {
                name: form.groomName.value,
                birth_date: form.groomBirthDate.value,
                birth_place: form.groomBirthPlace.value,
                parent1_name: form.groomParent1Name.value || 'N/A',
                parent1_origin: form.groomParent1Origin.value || 'N/A',
                parent2_name: form.groomParent2Name.value || 'N/A',
                parent2_origin: form.groomParent2Origin.value || 'N/A',
                gender: 'male',
                baptism_date: form.groomBaptismDate.value,
                baptism_church: form.groomBaptismChurch.value,
                confirmation_date: form.groomConfirmationDate.value,
                confirmation_church: form.groomConfirmationChurch.value
            },
            sponsors: sponsors
        };

        const response = await fetch('/GoldTree/crud/matrimony_records/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        if (!response.ok) {
            throw new Error('Failed to save marriage record');
        }

        const result = await response.json();
        if (result.status === 'error') {
            throw new Error(result.message || 'Failed to save marriage record');
        }

        alert('Marriage record saved successfully!');
        form.reset();
        form.classList.remove('was-validated');
        

        const sponsorsContainer = document.getElementById('matrimonySponsorsContainer');
        sponsorsContainer.innerHTML = `
            <div class="sponsor-entry mb-2 d-flex align-items-center">
                <div class="me-2 fw-bold" style="width: 25px;">1.</div>
                <input type="text" class="form-control" name="sponsors[]" placeholder="Sponsor's Name">
                <button type="button" class="btn btn-danger ms-2" onclick="removeMatrimonySponsor(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

  
        const modal = bootstrap.Modal.getInstance(document.getElementById('addMarriageModal'));
        modal.hide();

        
        if (document.getElementById('filterSacramentType').value === 'Marriage') {
            await loadMatrimonyRecords();
        }

    } catch (error) {
        console.error('Error saving marriage record:', error);
        alert(error.message);
    }
}


function addInputListener(elementId, eventType, handler) {
    const element = document.getElementById(elementId);
    if (element) {
        element.addEventListener(eventType, handler);
    }
}

let filterTimeout;

function debounceFilter() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        filterRecords();
    }, 300);
}


document.addEventListener('DOMContentLoaded', () => {

    const sacramentType = document.getElementById('filterSacramentType')?.value;
    if (sacramentType) {
        filterRecords();
    } else {
        const noRecordsMessage = document.getElementById('noRecordsMessage');
        if (noRecordsMessage) {
            noRecordsMessage.style.display = 'block';
        }
    }


    addInputListener('filterSacramentType', 'change', filterRecords);
    addInputListener('search', 'input', debounceFilter);
    addInputListener('dateFrom', 'change', filterRecords);
    addInputListener('dateTo', 'change', filterRecords);


    ['minister', 'brideName', 'groomName', 'communionName', 'communionParent', 'communionMinister',
     'confirmationName', 'confirmationParent', 'confirmationMinister', 'baptismName', 'baptismParent', 'baptismMinister']
        .forEach(id => addInputListener(id, 'input', debounceFilter));
    

    ['matrimonyDateFrom', 'matrimonyDateTo', 'communionDateFrom', 'communionDateTo', 'baptismDateFrom', 'baptismDateTo'].forEach(id => 
        addInputListener(id, 'change', debounceFilter));


    document.addEventListener('change', function(e) {
        if (e.target && (e.target.id === 'person1Type' || e.target.id === 'person2Type')) {
            validateMatrimonyTypes();
        }
    });
});

function debounceFilter() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        filterRecords();
    }, 300);
}

function getBasePersonalFields(prefix = '', required = true) {
    return `
        <!-- Personal Information -->
        <div class="col-12">
            <h6 class="border-bottom pb-2">Personal Information</h6>
        </div>
        <div class="col-12">
            <label for="${prefix}Name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="${prefix}Name" name="${prefix}name" ${required ? 'required' : ''}>
        </div>
        <div class="col-md-6">
            <label for="${prefix}Gender" class="form-label">Gender</label>
            <select class="form-select" id="${prefix}Gender" name="${prefix}gender" ${required ? 'required' : ''}>
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="col-12">
            <label for="${prefix}Address" class="form-label">Address</label>
            <textarea class="form-control" id="${prefix}Address" name="${prefix}address" rows="2" ${required ? 'required' : ''}></textarea>
        </div>`;
}

function getBirthDetailsFields(prefix = '', required = true) {
    return `
        <!-- Birth Details -->
        <div class="col-12">
            <h6 class="border-bottom pb-2">Birth Details</h6>
        </div>
        <div class="col-md-6">
            <label for="${prefix}BirthDate" class="form-label">Birth Date</label>
            <input type="date" class="form-control" id="${prefix}BirthDate" name="${prefix}birth_date" ${required ? 'required' : ''}>
        </div>
        <div class="col-md-6">
            <label for="${prefix}BirthPlace" class="form-label">Birth Place</label>
            <input type="text" class="form-control" id="${prefix}BirthPlace" name="${prefix}birth_place" ${required ? 'required' : ''}>
        </div>`;
}

function getParentsFields(prefix = '') {
    return `
        <!-- Parents Information -->
        <div class="col-12">
            <h6 class="border-bottom pb-2">Parents Information</h6>
        </div>
        <div class="col-md-6">
            <label for="${prefix}Parent1Name" class="form-label">Parent 1 Name</label>
            <input type="text" class="form-control" id="${prefix}Parent1Name" name="${prefix}parent1_name">
        </div>
        <div class="col-md-6">
            <label for="${prefix}Parent1Origin" class="form-label">Parent 1 Origin</label>
            <input type="text" class="form-control" id="${prefix}Parent1Origin" name="${prefix}parent1_origin">
        </div>
        <div class="col-md-6">
            <label for="${prefix}Parent2Name" class="form-label">Parent 2 Name</label>
            <input type="text" class="form-control" id="${prefix}Parent2Name" name="${prefix}parent2_name">
        </div>
        <div class="col-md-6">
            <label for="${prefix}Parent2Origin" class="form-label">Parent 2 Origin</label>
            <input type="text" class="form-control" id="${prefix}Parent2Origin" name="${prefix}parent2_origin">
        </div>`;
}

function getSponsorsField(prefix = '') {
    return `
        <!-- Sponsors -->
        <div class="col-12">
            <h6 class="border-bottom pb-2">Sponsors (Optional)</h6>
        </div>
        <div class="col-12" id="${prefix}SponsorsContainer">
            <div class="sponsor-entry mb-2 d-flex align-items-center">
                <div class="me-2 fw-bold" style="width: 25px;">1.</div>
                <input type="text" class="form-control" name="${prefix}sponsors[]" placeholder="Sponsor's Name (Optional)">
                <button type="button" class="btn btn-danger ms-2" onclick="removeSponsor(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
        <div class="col-12">
            <button type="button" class="btn btn-secondary btn-sm" onclick="addSponsor('${prefix}')">
                <i class="bi bi-plus-circle me-1"></i>Add Another Sponsor
            </button>
        </div>`;
}

function validateMatrimonyTypes() {
    const person1Type = document.getElementById('person1Type');
    const person2Type = document.getElementById('person2Type');
    
    if (person1Type && person2Type) {
        if (person1Type.value === person2Type.value && person1Type.value !== '') {
            alert('One person must be a bride and the other must be a groom');
            person2Type.value = '';
        }
    }
}

async function editConfirmationRecord(id) {
    try {
    
        const response = await fetch(`/GoldTree/crud/confirmation_records/get.php?id=${id}`);
        const record = await response.json();

        if (!response.ok) {
            throw new Error(record.message || 'Failed to fetch record');
        }

   
        document.getElementById('editConfirmationId').value = record.id;
        document.getElementById('editConfirmationName').value = record.name;
        document.getElementById('editConfirmationGender').value = record.gender;
        document.getElementById('editConfirmationAddress').value = record.address;
        document.getElementById('editConfirmationBirthDate').value = record.birth_date;
        document.getElementById('editConfirmationBirthPlace').value = record.birth_place;
        document.getElementById('editConfirmationParent1Name').value = record.parent1_name === 'N/A' ? '' : record.parent1_name;
        document.getElementById('editConfirmationParent1Origin').value = record.parent1_origin === 'N/A' ? '' : record.parent1_origin;
        document.getElementById('editConfirmationParent2Name').value = record.parent2_name === 'N/A' ? '' : record.parent2_name;
        document.getElementById('editConfirmationParent2Origin').value = record.parent2_origin === 'N/A' ? '' : record.parent2_origin;
        document.getElementById('editConfirmationBaptismDate').value = record.baptism_date;
        document.getElementById('editConfirmationMinister').value = record.minister;

       
        const sponsorsContainer = document.getElementById('editConfirmationSponsorsContainer');
        sponsorsContainer.innerHTML = '';

        const sponsorsResponse = await fetch(`/GoldTree/crud/confirmation_records/get_sponsors.php?record_id=${id}`);
        const sponsors = await sponsorsResponse.json();

        if (sponsors && sponsors.length > 0) {
            sponsors.forEach((sponsor, index) => {
                const sponsorEntry = document.createElement('div');
                sponsorEntry.className = 'sponsor-entry mb-2 d-flex align-items-center';
                sponsorEntry.innerHTML = `
                    <div class="me-2 fw-bold" style="width: 25px;">${index + 1}.</div>
                    <input type="text" class="form-control" name="sponsors[]" value="${sponsor.sponsor_name}" placeholder="Sponsor's Name (Optional)">
                    <button type="button" class="btn btn-danger ms-2" onclick="removeSponsor(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                `;
                sponsorsContainer.appendChild(sponsorEntry);
            });
        } else {
      
            const sponsorEntry = document.createElement('div');
            sponsorEntry.className = 'sponsor-entry mb-2 d-flex align-items-center';
            sponsorEntry.innerHTML = `
                <div class="me-2 fw-bold" style="width: 25px;">1.</div>
                <input type="text" class="form-control" name="sponsors[]" placeholder="Sponsor's Name (Optional)">
                <button type="button" class="btn btn-danger ms-2" onclick="removeSponsor(this)">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            sponsorsContainer.appendChild(sponsorEntry);
        }

     
        const modal = new bootstrap.Modal(document.getElementById('editConfirmationModal'));
        modal.show();

    } catch (error) {
        console.error('Error editing confirmation record:', error);
        alert('Failed to load confirmation record details');
    }
}

async function updateConfirmationRecord() {
    try {
        const form = document.getElementById('editConfirmationForm');
        
     
        const container = document.getElementById('editConfirmationSponsorsContainer');
        let sponsors = [];
        if (container) {
            const sponsorInputs = container.querySelectorAll('input[name="sponsors[]"]');
            sponsors = Array.from(sponsorInputs)
                .map(input => input.value.trim())
                .filter(value => value !== ''); 
        }

        const formData = {
            id: document.getElementById('editConfirmationId').value,
            name: document.getElementById('editConfirmationName').value.trim(),
            gender: document.getElementById('editConfirmationGender').value,
            address: document.getElementById('editConfirmationAddress').value.trim(),
            birth_date: document.getElementById('editConfirmationBirthDate').value,
            birth_place: document.getElementById('editConfirmationBirthPlace').value.trim(),
            parent1_name: document.getElementById('editConfirmationParent1Name').value.trim() || 'N/A',
            parent1_origin: document.getElementById('editConfirmationParent1Origin').value.trim() || 'N/A',
            parent2_name: document.getElementById('editConfirmationParent2Name').value.trim() || 'N/A',
            parent2_origin: document.getElementById('editConfirmationParent2Origin').value.trim() || 'N/A',
            baptism_date: document.getElementById('editConfirmationBaptismDate').value,
            minister: document.getElementById('editConfirmationMinister').value.trim(),
            sponsors: sponsors
        };

     
        await validateConfirmationForm(formData);

       
        const response = await fetch('/GoldTree/crud/confirmation_records/update.php', {
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

    
        alert('Confirmation record updated successfully!');
        const modal = bootstrap.Modal.getInstance(document.getElementById('editConfirmationModal'));
        modal.hide();

        await loadConfirmationRecords();

    } catch (error) {
        alert(error.message);
    }
}

async function deleteConfirmationRecord(id) {
    try {
    
        const response = await fetch(`/GoldTree/crud/confirmation_records/get.php?id=${id}`);
        const record = await response.json();

        if (!response.ok) {
            throw new Error(record.message || 'Failed to fetch record');
        }

        document.getElementById('deleteConfirmationId').value = record.id;
        document.getElementById('deleteConfirmationName').textContent = `Name: ${record.name}`;
        document.getElementById('deleteConfirmationDate').textContent = 
            `Confirmation Date: ${new Date(record.baptism_date).toLocaleDateString()}`;


        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
        modal.show();

    } catch (error) {
        console.error('Error preparing to delete confirmation record:', error);
        alert('Failed to load record details');
    }
}

async function confirmDeleteConfirmationRecord() {
    try {
        const id = document.getElementById('deleteConfirmationId').value;
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetch('/GoldTree/crud/confirmation_records/delete.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || 'Failed to delete record');
        }

        if (result.error) {
            throw new Error(result.error);
        }


        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmationModal'));
        modal.hide();

  
        alert(result.message || 'Confirmation record deleted successfully');


        await loadConfirmationRecords();

    } catch (error) {
        alert(error.message);
    }
}

async function validateFirstCommunionForm(formData) {
    const requiredFields = {
        name: 'Full Name',
        gender: 'Gender',
        address: 'Address',
        birth_date: 'Birth Date',
        birth_place: 'Birth Place',
        baptism_date: 'Baptism Date',
        baptism_church: 'Baptism Church',
        church: 'Church',
        confirmation_date: 'First Communion Date',
        minister: 'Minister'
    };

    for (const [field, label] of Object.entries(requiredFields)) {
        if (!formData[field]) {
            throw new Error(`Please fill in ${label}`);
        }
    }


    const birthDate = new Date(formData.birth_date);
    const baptismDate = new Date(formData.baptism_date);
    const communionDate = new Date(formData.confirmation_date);
    const today = new Date();

    if (birthDate > today) {
        throw new Error('Birth date cannot be in the future');
    }

    if (baptismDate < birthDate) {
        throw new Error('Baptism date cannot be before birth date');
    }

    if (communionDate < baptismDate) {
        throw new Error('First Communion date cannot be before Baptism date');
    }


    if (formData.parent1_name && !formData.parent1_origin) {
        throw new Error('Please provide origin for Parent 1');
    }
    if (formData.parent2_name && !formData.parent2_origin) {
        throw new Error('Please provide origin for Parent 2');
    }
}

async function viewFirstCommunionRecord(id) {
    try {
        if (!id) {
            throw new Error('Invalid record ID');
        }

    
        const response = await fetch(`/GoldTree/crud/first_communion_records/get.php?id=${id}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const text = await response.text();
        let record;
        try {
            record = JSON.parse(text);
        } catch (e) {
            console.error('Response text:', text);
            throw new Error('Invalid JSON response from server');
        }

     
        document.getElementById('viewFirstCommunionName').textContent = record.name;
        document.getElementById('viewFirstCommunionGender').textContent = 
            record.gender.charAt(0).toUpperCase() + record.gender.slice(1);
        document.getElementById('viewFirstCommunionAddress').textContent = record.address;
        
        document.getElementById('viewFirstCommunionBirthDate').textContent = 
            new Date(record.birth_date).toLocaleDateString();
        document.getElementById('viewFirstCommunionBirthPlace').textContent = record.birth_place;

 
        const parent1Info = record.parent1_name && record.parent1_name !== 'N/A' 
            ? `${record.parent1_name} (${record.parent1_origin || 'N/A'})`
            : 'Not provided';
        const parent2Info = record.parent2_name && record.parent2_name !== 'N/A'
            ? `${record.parent2_name} (${record.parent2_origin || 'N/A'})`
            : 'Not provided';
            
        document.getElementById('viewFirstCommunionParent1').textContent = parent1Info;
        document.getElementById('viewFirstCommunionParent2').textContent = parent2Info;

   
        document.getElementById('viewFirstCommunionBaptismDate').textContent = 
            new Date(record.baptism_date).toLocaleDateString();
        document.getElementById('viewFirstCommunionBaptismChurch').textContent = record.baptism_church;
        document.getElementById('viewFirstCommunionChurch').textContent = record.church;
        document.getElementById('viewFirstCommunionDate').textContent = 
            new Date(record.confirmation_date).toLocaleDateString();
        document.getElementById('viewFirstCommunionMinister').textContent = record.minister;


        const modal = new bootstrap.Modal(document.getElementById('viewFirstCommunionModal'));
        modal.show();

    } catch (error) {
        console.error('Error viewing First Communion record:', error);
        alert('Failed to load First Communion record details');
    }
}

async function saveFirstCommunionRecord() {
    try {
        const form = document.getElementById('addFirstCommunionForm');
        
        const formData = {
            name: document.getElementById('firstCommunionName').value.trim(),
            gender: document.getElementById('firstCommunionGender').value,
            address: document.getElementById('firstCommunionAddress').value.trim(),
            birth_date: document.getElementById('firstCommunionBirthDate').value,
            birth_place: document.getElementById('firstCommunionBirthPlace').value.trim(),
            parent1_name: document.getElementById('firstCommunionParent1Name').value.trim() || 'N/A',
            parent1_origin: document.getElementById('firstCommunionParent1Origin').value.trim() || 'N/A',
            parent2_name: document.getElementById('firstCommunionParent2Name').value.trim() || 'N/A',
            parent2_origin: document.getElementById('firstCommunionParent2Origin').value.trim() || 'N/A',
            baptism_date: document.getElementById('firstCommunionBaptismDate').value,
            baptism_church: document.getElementById('firstCommunionBaptismChurch').value.trim(),
            church: document.getElementById('firstCommunionChurch').value.trim(),
            confirmation_date: document.getElementById('firstCommunionDate').value,
            minister: document.getElementById('firstCommunionMinister').value.trim()
        };


        await validateFirstCommunionForm(formData);


        const response = await fetch('/GoldTree/crud/first_communion_records/save.php', {
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


        alert('First Communion record saved successfully!');
        form.reset();
        

        const modal = bootstrap.Modal.getInstance(document.getElementById('addFirstCommunionModal'));
        modal.hide();

        await loadFirstCommunionRecords();

    } catch (error) {
        alert(error.message);
    }
}

async function viewConfirmationRecord(id) {
    try {
     
        const response = await fetch(`/GoldTree/crud/confirmation_records/get.php?id=${id}`);
        const record = await response.json();

        if (!response.ok) {
            throw new Error(record.message || 'Failed to fetch record');
        }


        document.getElementById('viewConfirmationName').textContent = record.name;
        document.getElementById('viewConfirmationGender').textContent = 
            record.gender.charAt(0).toUpperCase() + record.gender.slice(1);
        document.getElementById('viewConfirmationAddress').textContent = record.address;
        document.getElementById('viewConfirmationBirthDate').textContent = 
            new Date(record.birth_date).toLocaleDateString();
        document.getElementById('viewConfirmationBirthPlace').textContent = record.birth_place;

        const parent1Info = record.parent1_name && record.parent1_name !== 'N/A' 
            ? `${record.parent1_name} (${record.parent1_origin || 'N/A'})`
            : 'Not provided';
        const parent2Info = record.parent2_name && record.parent2_name !== 'N/A'
            ? `${record.parent2_name} (${record.parent2_origin || 'N/A'})`
            : 'Not provided';
            
        document.getElementById('viewConfirmationParent1').textContent = parent1Info;
        document.getElementById('viewConfirmationParent2').textContent = parent2Info;
        
        document.getElementById('viewConfirmationBaptismDate').textContent = 
            new Date(record.baptism_date).toLocaleDateString();
        document.getElementById('viewConfirmationMinister').textContent = record.minister;


        const sponsorsResponse = await fetch(`/GoldTree/crud/confirmation_records/get_sponsors.php?record_id=${id}`);
        const sponsors = await sponsorsResponse.json();

        const sponsorsContainer = document.getElementById('viewConfirmationSponsors');
        if (sponsors && sponsors.length > 0) {
            const sponsorsList = sponsors.map((s, index) => 
                `<div class="mb-1">${index + 1}. ${s.sponsor_name}</div>`
            ).join('');
            sponsorsContainer.innerHTML = sponsorsList;
        } else {
            sponsorsContainer.innerHTML = '<p>No sponsors listed</p>';
        }


        const modal = new bootstrap.Modal(document.getElementById('viewConfirmationModal'));
        modal.show();

    } catch (error) {
        console.error('Error viewing confirmation record:', error);
        alert('Failed to load confirmation record details');
    }
}

function updateFormFields() {
    const sacramentType = document.getElementById('sacramentType').value;
    const container = document.getElementById('dynamicFormFields');
    
    if (!sacramentType) {
        container.innerHTML = '';
        return;
    }

    let formContent = '<div class="row g-3">';

    switch(sacramentType) {
        case 'Baptism':
            formContent += `
                ${getBasePersonalFields()}
                ${getBirthDetailsFields()}
                ${getParentsFields()}
                <!-- Baptism Details -->
                <div class="col-12">
                    <h6 class="border-bottom pb-2">Baptism Details</h6>
                </div>
                <div class="col-md-6">
                    <label for="baptismDate" class="form-label">Baptism Date</label>
                    <input type="date" class="form-control" id="baptismDate" name="baptism_date" required>
                </div>
                <div class="col-md-6">
                    <label for="minister" class="form-label">Minister</label>
                    <input type="text" class="form-control" id="minister" name="minister" required>
                </div>
                ${getSponsorsField()}`;
            break;

        case 'Confirmation':
            formContent += `
                ${getBasePersonalFields()}
                ${getBirthDetailsFields()}
                ${getParentsFields()}
                <!-- Confirmation Details -->
                <div class="col-12">
                    <h6 class="border-bottom pb-2">Confirmation Details</h6>
                </div>
                <div class="col-md-6">
                    <label for="baptismDate" class="form-label">Baptism Date</label>
                    <input type="date" class="form-control" id="baptismDate" name="baptism_date" required>
                </div>
                <div class="col-md-6">
                    <label for="minister" class="form-label">Minister</label>
                    <input type="text" class="form-control" id="minister" name="minister" required>
                </div>
                ${getSponsorsField()}`;
            break;

        case 'First Communion':
            formContent += `
                ${getBasePersonalFields()}
                ${getBirthDetailsFields()}
                ${getParentsFields()}
                <!-- First Communion Details -->
                <div class="col-12">
                    <h6 class="border-bottom pb-2">Sacrament Details</h6>
                </div>
                <div class="col-md-6">
                    <label for="baptismDate" class="form-label">Baptism Date</label>
                    <input type="date" class="form-control" id="baptismDate" name="baptism_date" required>
                </div>
                <div class="col-md-6">
                    <label for="baptismChurch" class="form-label">Baptism Church</label>
                    <input type="text" class="form-control" id="baptismChurch" name="baptism_church" required>
                </div>
                <div class="col-md-6">
                    <label for="church" class="form-label">Church</label>
                    <input type="text" class="form-control" id="church" name="church" required>
                </div>
                <div class="col-md-6">
                    <label for="confirmationDate" class="form-label">Confirmation Date</label>
                    <input type="date" class="form-control" id="confirmationDate" name="confirmation_date" required>
                </div>
                <div class="col-md-6">
                    <label for="minister" class="form-label">Minister</label>
                    <input type="text" class="form-control" id="minister" name="minister" required>
                </div>`;
            break;

        case 'Marriage':
            formContent += `
                <!-- Matrimony Details -->
                <div class="col-12">
                    <h6 class="border-bottom pb-2">Matrimony Details</h6>
                </div>
                <div class="col-md-6">
                    <label for="matrimonyDate" class="form-label">Matrimony Date</label>
                    <input type="date" class="form-control" id="matrimonyDate" name="matrimony_date" required>
                </div>
                <div class="col-md-6">
                    <label for="church" class="form-label">Church</label>
                    <input type="text" class="form-control" id="church" name="church" required>
                </div>
                <div class="col-md-6">
                    <label for="minister" class="form-label">Minister</label>
                    <input type="text" class="form-control" id="minister" name="minister" required>
                </div>

                <!-- First Person Details -->
                <div class="col-12">
                    <h6 class="border-bottom pb-2 mt-4">First Person Information</h6>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="person1Type" class="form-label">Type</label>
                    <select class="form-select" id="person1Type" name="person1_type" required>
                        <option value="">Select Type</option>
                        <option value="bride">Bride</option>
                        <option value="groom">Groom</option>
                    </select>
                </div>
                ${getBasePersonalFields('person1_')}
                ${getBirthDetailsFields('person1_')}
                ${getParentsFields('person1_')}
                <div class="col-md-6">
                    <label for="person1BaptismDate" class="form-label">Baptism Date</label>
                    <input type="date" class="form-control" id="person1BaptismDate" name="person1_baptism_date" required>
                </div>
                <div class="col-md-6">
                    <label for="person1BaptismChurch" class="form-label">Baptism Church</label>
                    <input type="text" class="form-control" id="person1BaptismChurch" name="person1_baptism_church" required>
                </div>
                <div class="col-md-6">
                    <label for="person1ConfirmationDate" class="form-label">Confirmation Date</label>
                    <input type="date" class="form-control" id="person1ConfirmationDate" name="person1_confirmation_date" required>
                </div>
                <div class="col-md-6">
                    <label for="person1ConfirmationChurch" class="form-label">Confirmation Church</label>
                    <input type="text" class="form-control" id="person1ConfirmationChurch" name="person1_confirmation_church" required>
                </div>

                <!-- Second Person Details -->
                <div class="col-12">
                    <h6 class="border-bottom pb-2 mt-4">Second Person Information</h6>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="person2Type" class="form-label">Type</label>
                    <select class="form-select" id="person2Type" name="person2_type" required>
                        <option value="">Select Type</option>
                        <option value="bride">Bride</option>
                        <option value="groom">Groom</option>
                    </select>
                </div>
                ${getBasePersonalFields('person2_')}
                ${getBirthDetailsFields('person2_')}
                ${getParentsFields('person2_')}
                <div class="col-md-6">
                    <label for="person2BaptismDate" class="form-label">Baptism Date</label>
                    <input type="date" class="form-control" id="person2BaptismDate" name="person2_baptism_date" required>
                </div>
                <div class="col-md-6">
                    <label for="person2BaptismChurch" class="form-label">Baptism Church</label>
                    <input type="text" class="form-control" id="person2BaptismChurch" name="person2_baptism_church" required>
                </div>
                <div class="col-md-6">
                    <label for="person2ConfirmationDate" class="form-label">Confirmation Date</label>
                    <input type="date" class="form-control" id="person2ConfirmationDate" name="person2_confirmation_date" required>
                </div>
                <div class="col-md-6">
                    <label for="person2ConfirmationChurch" class="form-label">Confirmation Church</label>
                    <input type="text" class="form-control" id="person2ConfirmationChurch" name="person2_confirmation_church" required>
                </div>

                ${getSponsorsField('matrimony_')}`;
            break;
    }

    formContent += '</div>';
    container.innerHTML = formContent;
}

</script>

<div class="modal fade" id="viewFirstCommunionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View First Communion Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Personal Information</h6>
                    </div>
                    <div class="col-12">
                        <label class="fw-bold">Full Name</label>
                        <p id="viewFirstCommunionName"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Gender</label>
                        <p id="viewFirstCommunionGender"></p>
                    </div>
                    <div class="col-12">
                        <label class="fw-bold">Address</label>
                        <p id="viewFirstCommunionAddress"></p>
                    </div>

                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Birth Details</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Birth Date</label>
                        <p id="viewFirstCommunionBirthDate"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Birth Place</label>
                        <p id="viewFirstCommunionBirthPlace"></p>
                    </div>

                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Parents Information</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Parent 1</label>
                        <p id="viewFirstCommunionParent1"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Parent 2</label>
                        <p id="viewFirstCommunionParent2"></p>
                    </div>

                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Sacrament Details</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Baptism Date</label>
                        <p id="viewFirstCommunionBaptismDate"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Baptism Church</label>
                        <p id="viewFirstCommunionBaptismChurch"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Church</label>
                        <p id="viewFirstCommunionChurch"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">First Communion Date</label>
                        <p id="viewFirstCommunionDate"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Minister</label>
                        <p id="viewFirstCommunionMinister"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addFirstCommunionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add First Communion Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addFirstCommunionForm">
                    <div class="row g-3">

                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Personal Information</h6>
                        </div>
                        <div class="col-12">
                            <label for="firstCommunionName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="firstCommunionName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="firstCommunionGender" class="form-label">Gender</label>
                            <select class="form-select" id="firstCommunionGender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="firstCommunionAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="firstCommunionAddress" name="address" rows="2" required></textarea>
                        </div>

                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Birth Details</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="firstCommunionBirthDate" class="form-label">Birth Date</label>
                            <input type="date" class="form-control" id="firstCommunionBirthDate" name="birth_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="firstCommunionBirthPlace" class="form-label">Birth Place</label>
                            <input type="text" class="form-control" id="firstCommunionBirthPlace" name="birth_place" required>
                        </div>

                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Parents Information</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="firstCommunionParent1Name" class="form-label">Parent 1 Name</label>
                            <input type="text" class="form-control" id="firstCommunionParent1Name" name="parent1_name">
                        </div>
                        <div class="col-md-6">
                            <label for="firstCommunionParent1Origin" class="form-label">Parent 1 Origin</label>
                            <input type="text" class="form-control" id="firstCommunionParent1Origin" name="parent1_origin">
                        </div>
                        <div class="col-md-6">
                            <label for="firstCommunionParent2Name" class="form-label">Parent 2 Name</label>
                            <input type="text" class="form-control" id="firstCommunionParent2Name" name="parent2_name">
                        </div>
                        <div class="col-md-6">
                            <label for="firstCommunionParent2Origin" class="form-label">Parent 2 Origin</label>
                            <input type="text" class="form-control" id="firstCommunionParent2Origin" name="parent2_origin">
                        </div>

                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Sacrament Details</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="firstCommunionBaptismDate" class="form-label">Baptism Date</label>
                            <input type="date" class="form-control" id="firstCommunionBaptismDate" name="baptism_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="firstCommunionBaptismChurch" class="form-label">Baptism Church</label>
                            <input type="text" class="form-control" id="firstCommunionBaptismChurch" name="baptism_church" required>
                        </div>
                        <div class="col-md-6">
                            <label for="firstCommunionChurch" class="form-label">Church</label>
                            <input type="text" class="form-control" id="firstCommunionChurch" name="church" required>
                        </div>
                        <div class="col-md-6">
                            <label for="firstCommunionDate" class="form-label">First Communion Date</label>
                            <input type="date" class="form-control" id="firstCommunionDate" name="confirmation_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="firstCommunionMinister" class="form-label">Minister</label>
                            <input type="text" class="form-control" id="firstCommunionMinister" name="minister" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveFirstCommunionRecord()">Save Record</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="editConfirmationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Confirmation Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editConfirmationForm">
                    <input type="hidden" id="editConfirmationId" name="id">
                    <div class="row g-3">
                   
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Personal Information</h6>
                        </div>
                        <div class="col-12">
                            <label for="editConfirmationName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="editConfirmationName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editConfirmationGender" class="form-label">Gender</label>
                            <select class="form-select" id="editConfirmationGender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="editConfirmationAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="editConfirmationAddress" name="address" rows="2" required></textarea>
                        </div>

                 
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Birth Details</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="editConfirmationBirthDate" class="form-label">Birth Date</label>
                            <input type="date" class="form-control" id="editConfirmationBirthDate" name="birth_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editConfirmationBirthPlace" class="form-label">Birth Place</label>
                            <input type="text" class="form-control" id="editConfirmationBirthPlace" name="birth_place" required>
                        </div>

                
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Parents Information</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="editConfirmationParent1Name" class="form-label">Parent 1 Name</label>
                            <input type="text" class="form-control" id="editConfirmationParent1Name" name="parent1_name">
                        </div>
                        <div class="col-md-6">
                            <label for="editConfirmationParent1Origin" class="form-label">Parent 1 Origin</label>
                            <input type="text" class="form-control" id="editConfirmationParent1Origin" name="parent1_origin">
                        </div>
                        <div class="col-md-6">
                            <label for="editConfirmationParent2Name" class="form-label">Parent 2 Name</label>
                            <input type="text" class="form-control" id="editConfirmationParent2Name" name="parent2_name">
                        </div>
                        <div class="col-md-6">
                            <label for="editConfirmationParent2Origin" class="form-label">Parent 2 Origin</label>
                            <input type="text" class="form-control" id="editConfirmationParent2Origin" name="parent2_origin">
                        </div>

                      
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Confirmation Details</h6>
                        </div>
                        <div class="col-md-6">
                            <label for="editConfirmationBaptismDate" class="form-label">Baptism Date</label>
                            <input type="date" class="form-control" id="editConfirmationBaptismDate" name="baptism_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editConfirmationMinister" class="form-label">Minister</label>
                            <input type="text" class="form-control" id="editConfirmationMinister" name="minister" required>
                        </div>

                    
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">Sponsors (Optional)</h6>
                        </div>
                        <div class="col-12" id="editConfirmationSponsorsContainer">
                    
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addSponsor('editConfirmation')">
                                <i class="bi bi-plus-circle me-1"></i>Add Another Sponsor
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="updateConfirmationRecord()">Update Record</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteConfirmationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Confirmation Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this confirmation record? This action cannot be undone.</p>
                <div class="alert alert-danger">
                    <strong>Warning:</strong> This will permanently delete the record and all associated sponsor information.
                </div>
                <div class="mt-3">
                    <strong>Record Details:</strong>
                    <p id="deleteConfirmationName" class="mb-1"></p>
                    <p id="deleteConfirmationDate" class="mb-1"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteConfirmationRecord()">Delete Record</button>
            </div>
            <input type="hidden" id="deleteConfirmationId">
        </div>
    </div>
</div>

<div class="modal fade" id="viewConfirmationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Confirmation Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">

                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Personal Information</h6>
                    </div>
                    <div class="col-12">
                        <label class="fw-bold">Full Name</label>
                        <p id="viewConfirmationName"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Gender</label>
                        <p id="viewConfirmationGender"></p>
                    </div>
                    <div class="col-12">
                        <label class="fw-bold">Address</label>
                        <p id="viewConfirmationAddress"></p>
                    </div>

                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Birth Details</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Birth Date</label>
                        <p id="viewConfirmationBirthDate"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Birth Place</label>
                        <p id="viewConfirmationBirthPlace"></p>
                    </div>

                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Parents Information</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Parent 1</label>
                        <p id="viewConfirmationParent1"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Parent 2</label>
                        <p id="viewConfirmationParent2"></p>
                    </div>

                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Confirmation Details</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Baptism Date</label>
                        <p id="viewConfirmationBaptismDate"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Minister</label>
                        <p id="viewConfirmationMinister"></p>
                    </div>

                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Sponsors</h6>
                    </div>
                    <div class="col-12">
                        <div id="viewConfirmationSponsors"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

