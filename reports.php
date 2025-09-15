<?php require_once 'templates/header.php'
; 
require_once 'auth/login_status.php';?>

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
        <div class="col-sm-6">            <div class="card stat-card">
                <div class="card-body">                    <h6 class="card-subtitle mb-2">Attendance Rate</h6>
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
    </div>

  
    <div class="row g-4">
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
    </div>


    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Export Reports</h5>
                    <div class="row g-3">                        <div class="col-md-4">
                            <button class="btn btn-outline-primary w-100" onclick="exportReport('members')">
                                <i class="bi bi-people me-2"></i> Members Report
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-primary w-100" onclick="exportReport('events')">
                                <i class="bi bi-calendar-event me-2"></i> Events Report
                            </button>
                        </div>
                        <div class="col-md-4">
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
    startDate.setMonth(startDate.getMonth() - 1);
    

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
                    backgroundColor: 'rgba(255, 193, 7, 0.8)', // Gold
                    borderColor: '#ffc107',
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: 'Absent',
                    data: [],
                    backgroundColor: 'rgba(108, 117, 125, 0.8)', // Gray
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