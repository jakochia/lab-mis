<?php include 'includes/admin_header.php'; ?>
<?php require_once '../includes/functions.php'; ?>

<?php
$report_type = $_GET['type'] ?? 'daily';
$period = $_GET['period'] ?? date('Y-m-d');
$export = isset($_GET['export']);

if ($report_type == 'daily') {
    $start = $period . ' 00:00:00';
    $end = $period . ' 23:59:59';
} elseif ($report_type == 'weekly') {
    $start = date('Y-m-d', strtotime('monday this week', strtotime($period)));
    $end = date('Y-m-d', strtotime('sunday this week', strtotime($period))) . ' 23:59:59';
} elseif ($report_type == 'monthly') {
    $start = date('Y-m-01', strtotime($period));
    $end = date('Y-m-t', strtotime($period)) . ' 23:59:59';
}

$sql = "SELECT ls.*, u.full_name, u.username, c.computer_name 
        FROM lab_sessions ls
        JOIN users u ON ls.user_id = u.id
        JOIN computers c ON ls.computer_id = c.id
        WHERE ls.start_time BETWEEN :start AND :end
        ORDER BY ls.start_time";
$stmt = $conn->prepare($sql);
$stmt->execute(['start' => $start, 'end' => $end]);
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hourly usage for peak hours
$hourly = $conn->prepare("SELECT HOUR(start_time) as hour, COUNT(*) as count 
                          FROM lab_sessions 
                          WHERE start_time BETWEEN :start AND :end
                          GROUP BY hour ORDER BY hour");
$hourly->execute(['start' => $start, 'end' => $end]);
$peak_hours = $hourly->fetchAll(PDO::FETCH_KEY_PAIR);

// Faulty equipment report
$faulty_equipment = $conn->query("SELECT * FROM equipment WHERE status = 'faulty'")->fetchAll(PDO::FETCH_ASSOC);
$faulty_computers = $conn->query("SELECT * FROM computers WHERE status = 'faulty'")->fetchAll(PDO::FETCH_ASSOC);

// Stats for cards
$total_sessions = count($sessions);
$total_duration = 0;
foreach ($sessions as $s) {
    if ($s['duration_minutes']) $total_duration += $s['duration_minutes'];
}
$total_hours = floor($total_duration / 60);
$total_mins = $total_duration % 60;

if ($export) {
    $data = [];
    foreach ($sessions as $s) {
        $data[] = [
            'Student' => $s['full_name'],
            'Computer' => $s['computer_name'],
            'Start' => $s['start_time'],
            'End' => $s['end_time'],
            'Duration' => $s['duration_minutes'],
        ];
    }
    exportToCSV($data, "lab_usage_$report_type.csv");
    exit;
}
?>

<!-- Modern Styles (consistent with other admin pages) -->
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        --admin-accent: #1abc9c;
        --card-border-radius: 1rem;
    }
    body {
        background: #f4f7fc;
    }
    .dashboard-header {
        background: var(--primary-gradient);
        border-radius: 0 0 2rem 2rem;
        padding: 2rem 0;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .welcome-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .mission-text {
        font-size: 1.1rem;
        opacity: 0.9;
        font-style: italic;
    }
    .stat-card {
        background: white;
        border-radius: 1rem;
        padding: 1.2rem;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        margin-bottom: 1.5rem;
        border: none;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.1);
    }
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #2a5298;
        margin: 0.5rem 0;
    }
    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .modern-card {
        border: none;
        border-radius: var(--card-border-radius);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .card-header-modern {
        background: white;
        border-bottom: 2px solid #eef2f6;
        padding: 1rem 1.5rem;
        font-weight: 600;
        font-size: 1.1rem;
    }
    .card-header-modern i {
        margin-right: 0.5rem;
        color: var(--admin-accent);
    }
    .filter-form {
        background: white;
        padding: 1.5rem;
        border-radius: 1rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .form-control-modern {
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        padding: 0.6rem 1rem;
        transition: 0.2s;
    }
    .form-control-modern:focus {
        border-color: #2a5298;
        box-shadow: 0 0 0 0.2rem rgba(42,82,152,0.25);
    }
    .btn-modern {
        border-radius: 2rem;
        padding: 0.5rem 1.2rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    .btn-primary-modern {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        border: none;
        color: white;
    }
    .btn-primary-modern:hover {
        background: linear-gradient(135deg, #162d54, #1f3e75);
        transform: translateY(-1px);
        box-shadow: 0 5px 10px rgba(0,0,0,0.1);
    }
    .btn-success-modern {
        background: #28a745;
        border: none;
        color: white;
    }
    .btn-success-modern:hover {
        background: #218838;
        transform: translateY(-1px);
    }
    .table-modern {
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }
    .table-modern thead th {
        border: none;
        background: #f8fafc;
        font-weight: 600;
        padding: 0.75rem 1rem;
    }
    .table-modern tbody tr {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        transition: background 0.2s;
    }
    .table-modern tbody tr:hover {
        background: #fef9e6;
    }
    .table-modern td {
        border: none;
        padding: 0.9rem 1rem;
        vertical-align: middle;
    }
    .badge-faulty {
        font-size: 0.75rem;
        padding: 0.35rem 0.7rem;
        border-radius: 2rem;
        background: #fff3cd;
        color: #856404;
    }
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
    }
    @media (max-width: 768px) {
        .welcome-title { font-size: 1.5rem; }
        .mission-text { font-size: 0.9rem; }
        .stat-number { font-size: 1.5rem; }
        .table-modern thead { display: none; }
        .table-modern tbody tr {
            display: block;
            margin-bottom: 1rem;
            padding: 1rem;
        }
        .table-modern td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eef2f6;
        }
        .table-modern td:before {
            content: attr(data-label);
            font-weight: 600;
            width: 40%;
            color: #2a5298;
        }
        .table-modern td:last-child {
            border-bottom: none;
        }
    }
</style>

<!-- Bootstrap Icons + FontAwesome -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<!-- Hero Section -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title">
                    <i class="bi bi-graph-up"></i> Reports
                </h1>
                <p class="mission-text mb-0">
                    <i class="bi bi-building"></i> Missions of Hope Namarei Computer Lab — Analyze usage and track equipment status.
                </p>
                <p class="mt-2 small opacity-75">
                    Generate daily, weekly, or monthly reports. Export data to CSV for deeper analysis.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="dashboard.php" class="btn btn-light btn-modern rounded-pill px-4">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Statistics Cards -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="stat-card">
                <i class="fas fa-chart-line fa-2x text-primary"></i>
                <div class="stat-number"><?= $total_sessions ?></div>
                <div class="stat-label">Total Sessions</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                <div class="stat-number"><?= $total_hours ?>h <?= $total_mins ?>m</div>
                <div class="stat-label">Total Usage Time</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <i class="fas fa-calendar-alt fa-2x text-info"></i>
                <div class="stat-number"><?= ucfirst($report_type) ?></div>
                <div class="stat-label">Report Period</div>
            </div>
        </div>
    </div>

    <!-- Filter and Export -->
    <div class="filter-form">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Report Type</label>
                <select name="type" class="form-select form-control-modern" onchange="this.form.submit()">
                    <option value="daily" <?= $report_type == 'daily' ? 'selected' : '' ?>>Daily</option>
                    <option value="weekly" <?= $report_type == 'weekly' ? 'selected' : '' ?>>Weekly</option>
                    <option value="monthly" <?= $report_type == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Period</label>
                <input type="date" name="period" class="form-control form-control-modern" value="<?= $period ?>" onchange="this.form.submit()">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <a href="?type=<?= $report_type ?>&period=<?= $period ?>&export=1" class="btn btn-success-modern btn-modern w-100">
                    <i class="bi bi-download"></i> Export CSV
                </a>
            </div>
        </form>
    </div>

    <div class="row">
        <!-- Left Column: Session Details -->
        <div class="col-lg-6">
            <div class="modern-card">
                <div class="card-header-modern">
                    <i class="bi bi-table"></i> Session Details
                </div>
                <div class="card-body p-0">
                    <?php if (count($sessions) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Computer</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Duration</th>
                                    </thead>
                                <tbody>
                                    <?php foreach ($sessions as $s): ?>
                                    汽
                                        <td data-label="Student"><?= htmlspecialchars($s['full_name']) ?></td>
                                        <td data-label="Computer"><?= $s['computer_name'] ?></td>
                                        <td data-label="Start"><?= date('M d, Y g:i A', strtotime($s['start_time'])) ?></td>
                                        <td data-label="End">
                                            <?php if ($s['end_time']): ?>
                                                <?= date('M d, Y g:i A', strtotime($s['end_time'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">In Progress</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Duration">
                                            <?php if ($s['duration_minutes']): ?>
                                                <?php 
                                                    $h = floor($s['duration_minutes'] / 60);
                                                    $m = $s['duration_minutes'] % 60;
                                                    echo ($h ? $h . 'h ' : '') . $m . 'm';
                                                ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-journal-x fs-1 text-muted"></i>
                            <p class="mt-2 mb-0">No sessions found for the selected period.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Peak Hours + Faulty Equipment -->
        <div class="col-lg-6">
            <!-- Peak Hours Chart -->
            <div class="modern-card">
                <div class="card-header-modern">
                    <i class="bi bi-bar-chart-line"></i> Peak Hours (by session count)
                </div>
                <div class="card-body p-4">
                    <?php if (count($peak_hours) > 0): ?>
                        <canvas id="peakHoursChart" height="200" style="max-height: 200px;"></canvas>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-graph-down fs-1 text-muted"></i>
                            <p class="mt-2 mb-0">No data available for the selected period.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Faulty Equipment Report -->
            <div class="modern-card">
                <div class="card-header-modern">
                    <i class="bi bi-exclamation-triangle"></i> Faulty Equipment Report
                </div>
                <div class="card-body p-4">
                    <h6 class="fw-semibold">Faulty Computers</h6>
                    <?php if (count($faulty_computers) > 0): ?>
                        <ul class="list-unstyled">
                            <?php foreach ($faulty_computers as $fc): ?>
                                <li class="mb-2">
                                    <i class="fas fa-desktop text-warning me-2"></i>
                                    <strong><?= htmlspecialchars($fc['computer_name']) ?></strong>
                                    <?php if ($fc['notes']): ?> - <?= htmlspecialchars($fc['notes']) ?><?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted"><i class="fas fa-check-circle text-success"></i> No faulty computers.</p>
                    <?php endif; ?>

                    <h6 class="fw-semibold mt-3">Other Faulty Equipment</h6>
                    <?php if (count($faulty_equipment) > 0): ?>
                        <ul class="list-unstyled">
                            <?php foreach ($faulty_equipment as $fe): ?>
                                <li class="mb-2">
                                    <i class="fas fa-keyboard text-warning me-2"></i>
                                    <strong><?= htmlspecialchars($fe['name']) ?></strong>
                                    <?php if ($fe['notes']): ?> - <?= htmlspecialchars($fe['notes']) ?><?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted"><i class="fas fa-check-circle text-success"></i> No faulty equipment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const peakData = <?= json_encode(array_values($peak_hours)) ?>;
    const peakLabels = <?= json_encode(array_keys($peak_hours)) ?>;
    if (peakData.length > 0) {
        new Chart(document.getElementById('peakHoursChart'), {
            type: 'line',
            data: {
                labels: peakLabels,
                datasets: [{
                    label: 'Number of Sessions',
                    data: peakData,
                    borderColor: '#1abc9c',
                    backgroundColor: 'rgba(26, 188, 156, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#1abc9c',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    tooltip: { mode: 'index', intersect: false },
                    legend: { position: 'top' }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        title: { display: true, text: 'Number of Sessions' }
                    },
                    x: {
                        title: { display: true, text: 'Hour of Day' }
                    }
                }
            }
        });
    }
</script>

<?php include '../includes/footer.php'; ?>