<?php include 'includes/student_header.php'; ?>

<?php
$history = $conn->prepare("SELECT ls.*, c.computer_name 
                           FROM lab_sessions ls 
                           JOIN computers c ON ls.computer_id = c.id 
                           WHERE ls.user_id = ? 
                           ORDER BY ls.start_time DESC");
$history->execute([$user_id]);
$history = $history->fetchAll(PDO::FETCH_ASSOC);

// Calculate some statistics
$total_sessions = count($history);
$total_minutes = 0;
$active_count = 0;
foreach ($history as $h) {
    if (!empty($h['duration_minutes'])) {
        $total_minutes += $h['duration_minutes'];
    }
    if ($h['status'] === 'active') {
        $active_count++;
    }
}
$total_hours = floor($total_minutes / 60);
$remaining_minutes = $total_minutes % 60;
?>

<!-- Modern Styles (same as dashboard for consistency) -->
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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
    .stats-card {
        background: white;
        border-radius: 1rem;
        padding: 1.2rem;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-5px);
    }
    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        color: #2a5298;
        margin-bottom: 0.25rem;
    }
    .stats-label {
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
        color: #2a5298;
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
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.7rem;
        border-radius: 2rem;
        font-weight: 500;
        display: inline-block;
    }
    .status-badge.active {
        background: #cff4fc;
        color: #055160;
    }
    .status-badge.completed {
        background: #d4edda;
        color: #155724;
    }
    @media (max-width: 768px) {
        .welcome-title { font-size: 1.5rem; }
        .mission-text { font-size: 0.9rem; }
        .stats-number { font-size: 1.5rem; }
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Hero Section with Mission -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title">
                    <i class="bi bi-clock-history"></i> My Session History
                </h1>
                <p class="mission-text mb-0">
                    <i class="bi bi-laptop"></i> Missions of Hope Namarei Computer Lab — Track your lab usage and progress.
                </p>
                <p class="mt-2 small opacity-75">
                    Review all your past and current computer sessions.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="computer_status.php" class="btn btn-light btn-modern rounded-pill px-4">
                    <i class="bi bi-play-circle"></i> Start New Session
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Statistics Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-number"><?= $total_sessions ?></div>
                <div class="stats-label">Total Sessions</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-number"><?= $total_hours ?>h <?= $remaining_minutes ?>m</div>
                <div class="stats-label">Total Time Used</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-number"><?= $active_count ?></div>
                <div class="stats-label">Active Sessions</div>
            </div>
        </div>
    </div>

    <!-- History Table Card -->
    <div class="modern-card">
        <div class="card-header-modern">
            <i class="bi bi-journal-bookmark-fill"></i> Session Log
        </div>
        <div class="card-body p-0">
            <?php if (count($history) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th><i class="bi bi-display"></i> Computer</th>
                                <th><i class="bi bi-play-circle"></i> Start Time</th>
                                <th><i class="bi bi-stop-circle"></i> End Time</th>
                                <th><i class="bi bi-hourglass-split"></i> Duration</th>
                                <th><i class="bi bi-tag"></i> Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $h): ?>
                            <tr>
                                <td><?= htmlspecialchars($h['computer_name']) ?></td>
                                <td><?= date('M d, Y g:i A', strtotime($h['start_time'])) ?></td>
                                <td>
                                    <?php if ($h['end_time']): ?>
                                        <?= date('M d, Y g:i A', strtotime($h['end_time'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">In Progress</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($h['duration_minutes']): ?>
                                        <?php 
                                            $hours = floor($h['duration_minutes'] / 60);
                                            $mins = $h['duration_minutes'] % 60;
                                            echo ($hours ? $hours . 'h ' : '') . $mins . 'm';
                                        ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?= $h['status'] ?>">
                                        <?= ucfirst($h['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-journal-x fs-1 text-muted"></i>
                    <p class="mt-2 mb-0">No session history found. Start your first session now!</p>
                    <a href="computer_status.php" class="btn btn-primary-modern btn-modern mt-3">
                        <i class="bi bi-laptop"></i> Use a Computer
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>