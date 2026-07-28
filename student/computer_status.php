<?php include 'includes/student_header.php'; ?>

<?php
// Alias for convenience
$maintenance = $maintenance_mode;

if (isMaintenanceMode($conn)) {
    // Show a friendly message
    echo '<div class="alert alert-warning text-center">';
    echo '<i class="bi bi-tools"></i> <strong>Maintenance Mode</strong><br>';
    echo 'The computer lab is currently under maintenance. New sessions and bookings are temporarily disabled. Please check back later.';
    echo '</div>';
}

// Handle reporting a computer as faulty (only if not in maintenance)
if (!$maintenance && isset($_GET['report_fault']) && is_numeric($_GET['report_fault'])) {
    $computer_id = (int)$_GET['report_fault'];

    // Check if computer exists and is not already faulty
    $check = $conn->prepare("SELECT status FROM computers WHERE id = ?");
    $check->execute([$computer_id]);
    $computer = $check->fetch(PDO::FETCH_ASSOC);

    if ($computer && $computer['status'] !== 'faulty') {
        // End any active session on this computer
        $end_session = $conn->prepare("UPDATE lab_sessions SET end_time = NOW(), status = 'ended', duration_minutes = TIMESTAMPDIFF(MINUTE, start_time, NOW()) WHERE computer_id = ? AND status = 'active'");
        $end_session->execute([$computer_id]);

        // Mark computer as faulty
        $update = $conn->prepare("UPDATE computers SET status = 'faulty' WHERE id = ?");
        if ($update->execute([$computer_id])) {
            $success = "Computer #$computer_id has been reported as faulty. Staff will check it soon.";
        } else {
            $error = "Failed to update computer status.";
        }
    } else {
        $error = "Computer not found or already faulty.";
    }
}

// Fetch computers with all details
$computers = $conn->query("SELECT * FROM computers ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total = count($computers);
$available = 0;
$in_use = 0;
$faulty = 0;
foreach ($computers as $c) {
    if ($c['status'] == 'available') $available++;
    elseif ($c['status'] == 'in_use') $in_use++;
    elseif ($c['status'] == 'faulty') $faulty++;
}
?>

<!-- Modern Styles -->
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
    .computer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .computer-card {
        background: #fff;
        border-radius: 1rem;
        padding: 1.2rem;
        text-align: center;
        transition: transform 0.2s, box-shadow 0.2s;
        border: 2px solid transparent;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .computer-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.1);
    }
    .computer-card.available {
        border-color: #28a745;
        background: linear-gradient(135deg, #f0fff4, #e6ffe6);
    }
    .computer-card.in-use {
        border-color: #dc3545;
        background: linear-gradient(135deg, #fff5f5, #ffe6e6);
    }
    .computer-card.faulty {
        border-color: #ffc107;
        background: linear-gradient(135deg, #fff9e6, #fff3cc);
    }
    .computer-image {
        width: 100%;
        height: 120px;
        object-fit: contain;
        margin-bottom: 10px;
    }
    .computer-name {
        font-weight: bold;
        font-size: 1.2rem;
        margin: 8px 0;
    }
    .computer-status {
        margin-bottom: 12px;
    }
    .badge-status {
        font-size: 0.75rem;
        padding: 0.35rem 0.7rem;
        border-radius: 2rem;
        font-weight: 500;
    }
    .badge-available {
        background: #d4edda;
        color: #155724;
    }
    .badge-in_use {
        background: #f8d7da;
        color: #721c24;
    }
    .badge-faulty {
        background: #fff3cd;
        color: #856404;
    }
    .tip-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #eef2f6;
    }
    .tip-item:last-child {
        border-bottom: none;
    }
    .tip-icon {
        font-size: 1.5rem;
        color: #2a5298;
    }
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
    }
    @media (max-width: 768px) {
        .welcome-title { font-size: 1.5rem; }
        .mission-text { font-size: 0.9rem; }
        .stats-number { font-size: 1.5rem; }
    }
</style>

<!-- Bootstrap Icons + FontAwesome (for existing icons) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<!-- Hero Section -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title">
                    <i class="bi bi-laptop"></i> Computer Lab Status
                </h1>
                <p class="mission-text mb-0">
                    <i class="bi bi-building"></i> Missions of Hope Namarei Computer Lab — Check availability and report issues.
                </p>
                <p class="mt-2 small opacity-75">
                    Green: Available | Red: In Use | Yellow: Faulty / Under Maintenance
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="#tips" class="btn btn-light btn-modern rounded-pill px-4">
                    <i class="bi bi-lightbulb"></i> Session Tips
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Alert Messages -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= $total ?></div>
                <div class="stats-label">Total Computers</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= $available ?></div>
                <div class="stats-label">Available</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= $in_use ?></div>
                <div class="stats-label">In Use</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= $faulty ?></div>
                <div class="stats-label">Faulty</div>
            </div>
        </div>
    </div>

    <!-- Computer Grid -->
    <div class="modern-card">
        <div class="card-header-modern">
            <i class="bi bi-grid-3x3-gap-fill"></i> Computers
        </div>
        <div class="card-body">
            <?php if (count($computers) > 0): ?>
                <div class="computer-grid">
                    <?php foreach ($computers as $comp):
                        $status_class = $comp['status'];
                        $status_text = ucfirst(str_replace('_', ' ', $comp['status']));
                        $action_button = '';
                        $report_button = '';

                        // Action button for available (only if not in maintenance)
                        if (!$maintenance && $comp['status'] == 'available') {
                            $action_button = '<a href="start_session.php?computer_id=' . $comp['id'] . '" class="btn btn-sm btn-success btn-modern mt-2"><i class="bi bi-play-fill"></i> Use</a>';
                        } elseif ($comp['status'] == 'in_use') {
                            $action_button = '<button class="btn btn-sm btn-secondary btn-modern mt-2" disabled><i class="bi bi-lock-fill"></i> Occupied</button>';
                        } else {
                            $action_button = '<button class="btn btn-sm btn-warning btn-modern mt-2" disabled><i class="bi bi-tools"></i> Maintenance</button>';
                        }

                        // Report fault button (only if not in maintenance and not already faulty)
                        if (!$maintenance && $comp['status'] != 'faulty') {
                            $report_button = '<a href="?report_fault=' . $comp['id'] . '" class="btn btn-sm btn-outline-danger btn-modern mt-2" onclick="return confirm(\'Report this computer as faulty? This will end any active session on it.\')"><i class="bi bi-exclamation-triangle"></i> Report Fault</a>';
                        }
                    ?>
                        <div class="computer-card <?= $status_class ?>">
                            <!-- Computer Image (Placeholder) -->
                            <img src="https://placehold.co/150x120/1e3c72/white?text=PC+<?= $comp['id'] ?>" alt="Computer" class="computer-image">
                            <div class="computer-name"><?= htmlspecialchars($comp['computer_name']) ?></div>
                            <div class="computer-status">
                                <span class="badge-status badge-<?= $status_class ?>">
                                    <?= $status_text ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-center gap-2">
                                <?= $action_button ?>
                                <?= $report_button ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-pc-display fs-1 text-muted"></i>
                    <p class="mt-2 mb-0">No computers found. Please contact the lab administrator.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tips Card -->
    <div class="modern-card" id="tips">
        <div class="card-header-modern">
            <i class="bi bi-lightbulb"></i> Tips for Using the Lab
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="tip-item">
                        <div class="tip-icon"><i class="bi bi-play-circle-fill"></i></div>
                        <div><strong>Starting a Session</strong><br>Click "Use" on an available computer to begin. Your session will be tracked automatically.</div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-icon"><i class="bi bi-stop-circle-fill"></i></div>
                        <div><strong>Ending a Session</strong><br>Always end your session from the dashboard when you're done. This frees the computer for others.</div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-icon"><i class="bi bi-journal-check"></i></div>
                        <div><strong>Book in Advance</strong><br>For guaranteed time, use the booking feature to reserve a computer for later.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="tip-item">
                        <div class="tip-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                        <div><strong>Report Issues</strong><br>If a computer isn't working properly, use "Report Fault" so our team can fix it quickly.</div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-icon"><i class="bi bi-clock-history"></i></div>
                        <div><strong>Check Your History</strong><br>Review your past sessions and total lab usage in the "Session History" section.</div>
                    </div>
                    <div class="tip-item">
                        <div class="tip-icon"><i class="bi bi-headset"></i></div>
                        <div><strong>Need Help?</strong><br>Submit a support ticket if you encounter technical difficulties or have questions.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>