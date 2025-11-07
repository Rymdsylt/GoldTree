<?php
require_once 'templates/header.php';
require_once 'auth/login_status.php';
require_once 'auth/check_admin.php';
require_once 'db/connection.php';?>

<style>
.chart-container {
    height: 400px;
    position: relative;
}
</style>

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="card-title mb-0 text-white">Analytics Dashboard</h4>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2 justify-content-md-end">
                                <div class="input-group">
                                    <input type="date" class="form-control" id="startDate">
                                    <span class="input-group-text bg-light">to</span>
                                    <input type="date" class="form-control" id="endDate">
                                    <button class="btn btn-light" id="applyDateRange">
                                        <i class="bi bi-check-lg"></i> Apply
                                    </button>
                                </div>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Active Members</h6>
                    <h2 class="card-title mb-0" id="activeMembers">0</h2>
                    <small>Current count</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6">            
            <div class="card stat-card">
                <div class="card-body">                    
                    <h6 class="card-subtitle mb-2">Attendance Rate</h6>
                    <h2 class="card-title mb-0"><span id="avgAttendance">0</span>%</h2>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-x"></i>
                        <span id="avgAbsence">0</span>% Absence Rate
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <?php if (isset($_SESSION['admin_status']) && $_SESSION['admin_status'] == 1): ?>
            <?php
            $sacraments = [
                'Baptism' => 'baptismal_records',
                'Confirmation' => 'confirmation_records',
                'First Communion' => 'first_communion_records',
                'Marriage' => 'matrimony_records'
            ];
            foreach ($sacraments as $sacrament => $table) {
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM $table");
                $stmt->execute();
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                ?>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2"><?php echo $sacrament; ?></h6>
                            <h2 class="card-title mb-0"><?php echo $count; ?></h2>
                            <small>Total records</small>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        <?php endif; ?>
    </div>

  
    <div class="row g-4">
        <?php if (isset($_SESSION['admin_status']) && $_SESSION['admin_status'] == 1): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Member Demographics</h5>
                        <div class="chart-container">
                            <canvas id="demographicsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Event Participation</h5>
                        <div class="chart-container">
                            <canvas id="eventParticipationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Yearly Sacramental Records</h5>
                        <div class="chart-container">
                            <canvas id="sacramentalChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Member Demographics</h5>
                        <div class="chart-container">
                            <canvas id="demographicsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Event Participation</h5>
                        <div class="chart-container">
                            <canvas id="eventParticipationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php
    if (isset($_SESSION['admin_status']) && $_SESSION['admin_status'] == 1) {

        // Check if database is PostgreSQL
        $isPostgres = (getenv('DATABASE_URL') !== false);
        
        if ($isPostgres) {
            // PostgreSQL recursive CTE syntax
            $monthsQuery = "
                WITH RECURSIVE months AS (
                    SELECT CURRENT_DATE as full_date, 
                           TO_CHAR(CURRENT_DATE, 'YYYY-MM') as month
                    UNION ALL
                    SELECT (full_date - INTERVAL '1 month'),
                           TO_CHAR((full_date - INTERVAL '1 month'), 'YYYY-MM')
                    FROM months
                    WHERE full_date > (CURRENT_DATE - INTERVAL '1 year')
                )
                SELECT month 
                FROM months 
                ORDER BY full_date DESC";
        } else {
            // MySQL recursive CTE syntax
            $monthsQuery = "
                WITH RECURSIVE months AS (
                    SELECT CURRENT_DATE as full_date, 
                           DATE_FORMAT(CURRENT_DATE, '%Y-%m') as month
                    UNION ALL
                    SELECT DATE_SUB(full_date, INTERVAL 1 MONTH),
                           DATE_FORMAT(DATE_SUB(full_date, INTERVAL 1 MONTH), '%Y-%m')
                    FROM months
                    WHERE full_date > DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR)
                )
                SELECT month 
                FROM months 
                ORDER BY full_date DESC";
        }

        $monthsStmt = $conn->query($monthsQuery);
        $allMonths = $monthsStmt->fetchAll(PDO::FETCH_COLUMN);


        $query = "";
        foreach ($allMonths as $month) {
            if ($query !== "") {
                $query .= "\nUNION ALL\n";
            }
            
            $startOfMonth = $month . '-01';
            $endOfMonth = date('Y-m-t', strtotime($startOfMonth));
            
            $query .= "
            /* Baptisms for $month */
            SELECT 'Baptism' as sacrament_type, COUNT(*) as count, '$month' as month
            FROM baptismal_records 
            WHERE baptism_date BETWEEN '$startOfMonth' AND '$endOfMonth'
            
            UNION ALL
            
            /* Confirmations for $month */
            SELECT 'Confirmation' as sacrament_type, COUNT(*) as count, '$month' as month
            FROM confirmation_records 
            WHERE baptism_date BETWEEN '$startOfMonth' AND '$endOfMonth'
            
            UNION ALL
            
            /* First Communions for $month */
            SELECT 'First Communion' as sacrament_type, COUNT(*) as count, '$month' as month
            FROM first_communion_records 
            WHERE communion_date BETWEEN '$startOfMonth' AND '$endOfMonth'
            
            UNION ALL
            
            /* Matrimony for $month */
            SELECT 'Matrimony' as sacrament_type, COUNT(*) as count, '$month' as month
            FROM matrimony_records 
            WHERE matrimony_date BETWEEN '$startOfMonth' AND '$endOfMonth'";
        }
        
        $query .= "\nORDER BY month ASC, sacrament_type ASC";
            
        $stmt = $conn->query($query);
        $sacramentalData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $sacramentalData = [];
    }
    ?>


    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Export Reports</h5>
                    <div class="row g-3">
                        <?php 
                        $isAdmin = isset($_SESSION['admin_status']) && $_SESSION['admin_status'] == 1;
                        $buttonCol = $isAdmin ? 'col-md-3' : 'col-md-4';
                        ?>
                        <div class="<?php echo $buttonCol; ?>">
                            <button class="btn btn-outline-primary w-100" onclick="exportReport('members')">
                                <i class="bi bi-people me-2"></i> Members Report
                            </button>
                        </div>
                        <div class="<?php echo $buttonCol; ?>">
                            <button class="btn btn-outline-primary w-100" onclick="exportReport('events')">
                                <i class="bi bi-calendar-event me-2"></i> Events Report
                            </button>
                        </div>
                        <?php if ($isAdmin): ?>
                        <div class="<?php echo $buttonCol; ?>">
                            <button class="btn btn-outline-primary w-100" onclick="exportReport('sacramental')">
                                <i class="bi bi-book me-2"></i> Sacramental Records
                            </button>
                        </div>
                        <?php endif; ?>
                        <div class="<?php echo $buttonCol; ?>">
                            <button class="btn btn-outline-primary w-100" onclick="exportReport('complete')">
                                <i class="bi bi-file-earmark-text me-2"></i> Complete Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

Chart.defaults.color = '#6a1b9a';
Chart.defaults.borderColor = 'rgba(106, 27, 154, 0.1)';
    
document.addEventListener('DOMContentLoaded', function() {
    initializeDateRange();
    loadStatistics();
    initializeCharts();
    
    document.getElementById('applyDateRange').addEventListener('click', function() {
        if (!validateDateRange()) {
            return;
        }
        loadStatistics();
        updateCharts();
    });

   
    document.getElementById('startDate').addEventListener('change', function() {
        document.getElementById('endDate').min = this.value;
    });
    
    document.getElementById('endDate').addEventListener('change', function() {
        document.getElementById('startDate').max = this.value;
    });
});

function initializeDateRange() {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setFullYear(startDate.getFullYear() - 1); 
    
    document.getElementById('startDate').value = formatDateForInput(startDate);
    document.getElementById('endDate').value = formatDateForInput(endDate);


    document.getElementById('startDate').max = formatDateForInput(endDate);
    document.getElementById('endDate').max = formatDateForInput(endDate);
}

function formatDateForInput(date) {
    return date.toISOString().split('T')[0];
}

function validateDateRange() {
    const startDate = new Date(document.getElementById('startDate').value);
    const endDate = new Date(document.getElementById('endDate').value);
    
    if (startDate > endDate) {
        alert('Start date cannot be after end date');
        return false;
    }
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (endDate > today) {
        alert('End date cannot be in the future');
        return false;
    }
    
    return true;
}

function loadStatistics() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    fetch(`crud/reports/get_statistics.php?start=${startDate}&end=${endDate}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.error || 'Failed to load statistics');
            }
            document.getElementById('activeMembers').textContent = data.activeMembers;
            document.getElementById('avgAttendance').textContent = data.avgAttendance;
            document.getElementById('avgAbsence').textContent = data.avgAbsence;
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
          
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.textContent = 'Failed to load statistics: ' + error.message;
            const statsContainer = document.getElementById('activeMembers').closest('.card');
            statsContainer.appendChild(alertDiv);
        });
}

function initializeCharts() {

    const demographicsCtx = document.getElementById('demographicsChart').getContext('2d');
    window.demographicsChart = new Chart(demographicsCtx, {
        type: 'bar',
        data: {
            labels: ['18-24', '25-34', '35-44', '45-54', '55+'],
            datasets: [{
                label: 'Age Distribution',
                data: [],
                backgroundColor: '#6a1b9a'
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
                    beginAtZero: true
                }
            }
        }
    });

    const participationCtx = document.getElementById('eventParticipationChart').getContext('2d');
    window.eventParticipationChart = new Chart(participationCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Present',
                    data: [],
                    backgroundColor: 'rgba(255, 193, 7, 0.8)', 
                    borderColor: '#ffc107',
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: 'Absent',
                    data: [],
                    backgroundColor: 'rgba(108, 117, 125, 0.8)', 
                    borderColor: '#6c757d',
                    borderWidth: 1,
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Count'
                    },
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Daily Attendance Count',
                    padding: {
                        top: 10,
                        bottom: 30
                    }
                },
                tooltip: {
                    callbacks: {
                        footer: (tooltipItems) => {
                            const total = tooltipItems.reduce((sum, item) => sum + item.parsed.y, 0);
                            return `Total Count: ${total}`;
                        }
                    }
                }
            }
        }
    });

    <?php if (isset($_SESSION['admin_status']) && $_SESSION['admin_status'] == 1): ?>
    const sacramentalData = <?php echo json_encode($sacramentalData); ?>;
  
    const months = [...new Set(sacramentalData.map(item => item.month))]
        .sort((a, b) => new Date(a + '-01') - new Date(b + '-01'));
    const sacramentTypes = ['Baptism', 'Confirmation', 'First Communion', 'Matrimony'];
    
    const colors = {
        'Baptism': {
            bg: 'rgba(52, 152, 219, 0.7)',
            border: 'rgba(52, 152, 219, 1)'
        },
        'Confirmation': {
            bg: 'rgba(155, 89, 182, 0.7)',
            border: 'rgba(155, 89, 182, 1)'
        },
        'First Communion': {
            bg: 'rgba(230, 126, 34, 0.7)',
            border: 'rgba(230, 126, 34, 1)'
        },
        'Matrimony': {
            bg: 'rgba(231, 76, 60, 0.7)',
            border: 'rgba(231, 76, 60, 1)'
        }
    };
    
    const datasets = sacramentTypes.map(type => {
        return {
            label: type,
            data: months.map(month => {
                const record = sacramentalData.find(item => 
                    item.month === month && item.sacrament_type === type
                );
                return record ? record.count : 0;
            }),
            backgroundColor: colors[type].bg,
            borderColor: colors[type].border,
            borderWidth: 1,
            borderRadius: 4
        };
    });

    new Chart(document.getElementById('sacramentalChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Records Per Month',
                    font: {
                        size: 16
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        title: (tooltipItems) => {
                            const date = new Date(tooltipItems[0].label + '-01');
                            return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        callback: function(value, index) {
                            const date = new Date(this.getLabelForValue(value) + '-01');
                            return date.toLocaleDateString('en-US', { 
                                month: 'short',
                                year: '2-digit'
                            });
                        }
                    },
                    reverse: true 
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        }
    });
    <?php endif; ?>

    updateCharts();
}

function updateCharts() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    fetch(`crud/reports/get_demographics.php`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.error || 'Failed to load demographics data');
            }
            window.demographicsChart.data.datasets[0].data = data.values;
            window.demographicsChart.update();
        })
        .catch(error => {
            console.error('Error loading demographics:', error);
            const chartContainer = document.getElementById('demographicsChart').parentElement;
            chartContainer.innerHTML = `
                <div class="alert alert-danger">
                    Failed to load demographics data: ${error.message}
                </div>
            `;
        });

    fetch(`crud/reports/get_event_participation.php?start=${startDate}&end=${endDate}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.error || 'Failed to load attendance data');
            }
            window.eventParticipationChart.data.labels = data.labels;
            window.eventParticipationChart.data.datasets[0].data = data.present;
            window.eventParticipationChart.data.datasets[1].data = data.absent;
            window.eventParticipationChart.update();
        })
        .catch(error => {
            console.error('Error loading attendance data:', error);
            const chartContainer = document.getElementById('eventParticipationChart').parentElement;
            chartContainer.innerHTML = `
                <div class="alert alert-danger">
                    Failed to load attendance data: ${error.message}
                </div>
            `;
        });
}

function exportReport(type) {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    window.location.href = `crud/reports/export_report.php?type=${type}&start=${startDate}&end=${endDate}`;
}

function formatCurrency(number) {
    return '₱' + parseFloat(number).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
</script>

<?php require_once 'templates/footer.php'; ?>