<?php include 'includes/student_header.php'; ?>

<?php
if (isMaintenanceMode($conn)) {
    // Show a friendly message and exit or disable actions
    echo '<div class="alert alert-warning text-center">';
    echo '<i class="bi bi-tools"></i> <strong>Maintenance Mode</strong><br>';
    echo 'The computer lab is currently under maintenance. New sessions and bookings are temporarily disabled. Please check back later.';
    echo '</div>';
    // Optionally, you can still show the page but disable forms/buttons
}

// Count active session for this student (if any)
$active = $conn->prepare("SELECT ls.*, c.computer_name 
                          FROM lab_sessions ls 
                          JOIN computers c ON ls.computer_id = c.id 
                          WHERE ls.user_id = ? AND ls.status = 'active'");
$active->execute([$user_id]);
$active_session = $active->fetch(PDO::FETCH_ASSOC);

// Upcoming bookings – initialize as empty array
$upcoming_bookings = [];
try {
    $stmt = $conn->prepare("SELECT b.*, c.computer_name 
                            FROM bookings b 
                            JOIN computers c ON b.computer_id = c.id 
                            WHERE b.user_id = ? AND b.booking_date >= CURDATE() AND b.status != 'cancelled' 
                            ORDER BY b.booking_date, b.start_time LIMIT 5");
    $stmt->execute([$user_id]);
    $upcoming_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Upcoming bookings query failed: " . $e->getMessage());
}

// Recent sessions – initialize as empty array
$recent_sessions = [];
try {
    $stmt = $conn->prepare("SELECT ls.*, c.computer_name 
                            FROM lab_sessions ls 
                            JOIN computers c ON ls.computer_id = c.id 
                            WHERE ls.user_id = ? 
                            ORDER BY ls.start_time DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Recent sessions query failed: " . $e->getMessage());
}

// Try to get student name from session (if available)
$student_name = $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Student';
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
    .modern-card {
        border: none;
        border-radius: var(--card-border-radius);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .modern-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 25px -12px rgba(0,0,0,0.1);
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
    .status-badge {
        background: #e9ecef;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.9rem;
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
    .booking-badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.7rem;
        border-radius: 2rem;
        font-weight: 500;
    }
    .booking-badge.confirmed {
        background: #d4edda;
        color: #155724;
    }
    .booking-badge.pending {
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
    }
</style>

<!-- Bootstrap Icons (optional, but adds nice visuals) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Hero Section with Welcoming Message -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title">
                    <i class="bi bi-emoji-smile"></i> Hello, <?= htmlspecialchars($student_name) ?>!
                </h1>
                <p class="mission-text mb-0">
                    <i class="bi bi-laptop"></i> Missions of Hope Namarei Computer Lab — Empowering students through technology.
                </p>
                <p class="mt-2 small opacity-75">
                    Your gateway to digital learning, research, and innovation.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="computer_status.php" class="btn btn-light btn-modern rounded-pill px-4">
                    <i class="bi bi-play-circle"></i> Start a Session
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <!-- Current Status Card -->
        <div class="col-md-6">
            <div class="modern-card">
                <div class="card-header-modern">
                    <i class="bi bi-pc-display"></i> Current Status
                </div>
                <div class="card-body p-4">
                    <?php if ($active_session): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <i class="bi bi-check-circle-fill text-success fs-2"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">Active Session</h5>
                                <p class="mb-0">
                                    Using <strong><?= htmlspecialchars($active_session['computer_name']) ?></strong><br>
                                    Since <?= date('g:i A', strtotime($active_session['start_time'])) ?>
                                </p>
                            </div>
                        </div>
                        <a href="end_session.php" class="btn btn-danger btn-modern w-100">
                            <i class="bi bi-box-arrow-right"></i> End Session
                        </a>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-power fs-1 text-muted"></i>
                            <p class="mt-2 mb-3">You are not logged into any computer.</p>
                            <a href="computer_status.php" class="btn btn-primary-modern btn-modern">
                                <i class="bi bi-laptop"></i> Use a Computer
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
    <div class="modern-card">
        <div class="card-header-modern">
            <i class="bi bi-megaphone"></i> Announcements
        </div>
        <div class="card-body p-0">
            <?php
            $stmt = $conn->prepare("SELECT * FROM announcements WHERE expires_at IS NULL OR expires_at > NOW() ORDER BY created_at DESC LIMIT 3");
            $stmt->execute();
            $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($announcements) > 0):
            ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($announcements as $a): ?>
                        <div class="list-group-item">
                            <h6 class="mb-1"><?= htmlspecialchars($a['title']) ?></h6>
                            <p class="mb-0 small"><?= nl2br(htmlspecialchars($a['content'])) ?></p>
                            <small class="text-muted"><?= date('M d, Y', strtotime($a['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state text-center py-3">
                    <i class="bi bi-megaphone text-muted"></i>
                    <p class="mb-0">No announcements at this time.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

        <!-- Upcoming Bookings Card -->
        <div class="col-md-6">
            <div class="modern-card">
                <div class="card-header-modern">
                    <i class="bi bi-calendar-check"></i> Upcoming Bookings
                </div>
                <div class="card-body p-0">
                    <?php if (count($upcoming_bookings) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($upcoming_bookings as $b): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <div>
                                        <i class="bi bi-display me-2 text-primary"></i>
                                        <strong><?= htmlspecialchars($b['computer_name']) ?></strong><br>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar-date"></i> <?= date('M d, Y', strtotime($b['booking_date'])) ?>
                                            &nbsp;|&nbsp;
                                            <i class="bi bi-clock"></i> <?= substr($b['start_time'], 0, 5) ?> – <?= substr($b['end_time'], 0, 5) ?>
                                        </small>
                                    </div>
                                    <span class="booking-badge <?= $b['status'] == 'confirmed' ? 'confirmed' : 'pending' ?>">
                                        <?= ucfirst($b['status']) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-calendar-x fs-1 text-muted"></i>
                            <p class="mt-2 mb-0">No upcoming bookings.</p>
                            <a href="book_computer.php" class="btn btn-sm btn-outline-primary mt-2">Book a Computer</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Sessions Card -->
    <div class="modern-card">
        <div class="card-header-modern">
            <i class="bi bi-clock-history"></i> Recent Sessions
        </div>
        <div class="card-body p-0">
            <?php if (count($recent_sessions) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th><i class="bi bi-laptop"></i> Computer</th>
                                <th><i class="bi bi-play-circle"></i> Start Time</th>
                                <th><i class="bi bi-stop-circle"></i> End Time</th>
                                <th><i class="bi bi-hourglass-split"></i> Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_sessions as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['computer_name']) ?></td>
                                    <td><?= date('M d, Y g:i A', strtotime($s['start_time'])) ?></td>
                                    <td><?= $s['end_time'] ? date('M d, Y g:i A', strtotime($s['end_time'])) : '-' ?></td>
                                    <td>
                                        <?php if ($s['duration_minutes']): ?>
                                            <?= floor($s['duration_minutes'] / 60) ?>h <?= $s['duration_minutes'] % 60 ?>m
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
                    <p class="mt-2 mb-0">No recent sessions found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>