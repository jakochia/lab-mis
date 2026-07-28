<?php include 'includes/admin_header.php'; ?>

<?php
// Optional filtering
$filter_user = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$filter_computer = isset($_GET['computer_id']) ? $_GET['computer_id'] : '';

$sql = "SELECT ls.*, u.full_name, u.username, c.computer_name 
        FROM lab_sessions ls
        JOIN users u ON ls.user_id = u.id
        JOIN computers c ON ls.computer_id = c.id
        WHERE 1=1";
$params = [];

if ($filter_user) {
    $sql .= " AND ls.user_id = ?";
    $params[] = $filter_user;
}
if ($filter_computer) {
    $sql .= " AND ls.computer_id = ?";
    $params[] = $filter_computer;
}
$sql .= " ORDER BY ls.start_time DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For filters dropdown
$users = $conn->query("SELECT id, full_name, username FROM users WHERE role = 'student' ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
$computers = $conn->query("SELECT id, computer_name FROM computers ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Statistics
$total_sessions = count($sessions);
$active_sessions = 0;
$total_duration = 0;
foreach ($sessions as $s) {
    if ($s['status'] == 'active') $active_sessions++;
    if ($s['duration_minutes']) $total_duration += $s['duration_minutes'];
}
$total_hours = floor($total_duration / 60);
$total_mins = $total_duration % 60;
?>

<!-- Modern Styles (consistent with admin dashboard) -->
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
    .badge-status {
        font-size: 0.75rem;
        padding: 0.35rem 0.7rem;
        border-radius: 2rem;
        font-weight: 500;
    }
    .badge-active {
        background: #cff4fc;
        color: #055160;
    }
    .badge-ended {
        background: #e2e3e5;
        color: #383d41;
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
                    <i class="bi bi-clock-history"></i> Session Logs
                </h1>
                <p class="mission-text mb-0">
                    <i class="bi bi-building"></i> Missions of Hope Namarei Computer Lab — Track all lab usage.
                </p>
                <p class="mt-2 small opacity-75">
                    Filter by student or computer to analyze usage patterns.
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
    <!-- Statistics Row -->
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
                <i class="fas fa-play-circle fa-2x text-success"></i>
                <div class="stat-number"><?= $active_sessions ?></div>
                <div class="stat-label">Active Sessions</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                <div class="stat-number"><?= $total_hours ?>h <?= $total_mins ?>m</div>
                <div class="stat-label">Total Duration</div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-form">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Filter by Student</label>
                <select name="user_id" class="form-select form-control-modern">
                    <option value="">All Students</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $filter_user == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['full_name']) ?> (<?= $u['username'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Filter by Computer</label>
                <select name="computer_id" class="form-select form-control-modern">
                    <option value="">All Computers</option>
                    <?php foreach ($computers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $filter_computer == $c['id'] ? 'selected' : '' ?>><?= $c['computer_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary-modern btn-modern flex-grow-1">
                    <i class="bi bi-funnel"></i> Apply Filter
                </button>
                <a href="session_logs.php" class="btn btn-outline-secondary btn-modern">Reset</a>
            </div>
        </form>
    </div>

    <!-- Sessions Table Card -->
    <div class="modern-card">
        <div class="card-header-modern">
            <i class="bi bi-table"></i> Session Records
        </div>
        <div class="card-body p-0">
            <?php if (count($sessions) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Computer</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Duration</th>
                                <th>Status</th>
                            </thead>
                        <tbody>
                            <?php foreach ($sessions as $s): ?>
                            <tr>
                                <td data-label="ID">#<?= $s['id'] ?></td>
                                <td data-label="Student"><?= htmlspecialchars($s['full_name']) ?></td>
                                <td data-label="Computer"><?= $s['computer_name'] ?></td>
                                <td data-label="Start Time"><?= date('M d, Y g:i A', strtotime($s['start_time'])) ?></td>
                                <td data-label="End Time">
                                    <?php if ($s['end_time']): ?>
                                        <?= date('M d, Y g:i A', strtotime($s['end_time'])) ?>
                                    <?php else: ?>
                                        <span class="text-success">In Progress</span>
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
                                <td data-label="Status">
                                    <span class="badge-status badge-<?= $s['status'] ?>">
                                        <?= ucfirst($s['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state text-center py-5">
                    <i class="bi bi-journal-x fs-1 text-muted"></i>
                    <p class="mt-2 mb-0">No sessions found matching the selected filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>