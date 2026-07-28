<?php include 'includes/student_header.php'; ?>

<?php
$error = '';
$success = '';

if (isMaintenanceMode($conn)) {
    // Show a friendly message and exit or disable actions
    echo '<div class="alert alert-warning text-center">';
    echo '<i class="bi bi-tools"></i> <strong>Maintenance Mode</strong><br>';
    echo 'The computer lab is currently under maintenance. New sessions and bookings are temporarily disabled. Please check back later.';
    echo '</div>';
    // Optionally, you can still show the page but disable forms/buttons
}

// Handle booking submission (AJAX will handle this, but keep for fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book'])) {
    $computer_id = (int)$_POST['computer_id'];
    $booking_date = $_POST['booking_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Check for conflicts
    $conflict = $conn->prepare("SELECT id FROM bookings 
                                 WHERE computer_id = ? AND booking_date = ? 
                                 AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?) OR (? BETWEEN start_time AND end_time))
                                 AND status != 'cancelled'");
    $conflict->execute([$computer_id, $booking_date, $end_time, $start_time, $end_time, $start_time, $start_time]);
    if ($conflict->rowCount() > 0) {
        $error = "This time slot is already booked for the selected computer.";
    } else {
        $insert = $conn->prepare("INSERT INTO bookings (user_id, computer_id, booking_date, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, 'confirmed')");
        if ($insert->execute([$user_id, $computer_id, $booking_date, $start_time, $end_time])) {
            $success = "Booking confirmed!";
        } else {
            $error = "Failed to book. Please try again.";
        }
    }
}

// Cancel booking
if (isset($_GET['cancel'])) {
    $booking_id = (int)$_GET['cancel'];
    $update = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND booking_date >= CURDATE()");
    $update->execute([$booking_id, $user_id]);
    $success = "Booking cancelled.";
}

// Check-in from booking (start session)
if (isset($_GET['checkin'])) {
    $booking_id = (int)$_GET['checkin'];
    // Verify booking belongs to user, is confirmed, and not in the past
    $booking = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status = 'confirmed' AND booking_date >= CURDATE()");
    $booking->execute([$booking_id, $user_id]);
    $book = $booking->fetch(PDO::FETCH_ASSOC);
    if ($book) {
        // Check if student already has an active session
        $active = $conn->prepare("SELECT id FROM lab_sessions WHERE user_id = ? AND status = 'active'");
        $active->execute([$user_id]);
        if ($active->rowCount() > 0) {
            $error = "You already have an active session. End it first.";
        } else {
            // Check if computer is still available
            $comp = $conn->prepare("SELECT status FROM computers WHERE id = ?");
            $comp->execute([$book['computer_id']]);
            $computer = $comp->fetch(PDO::FETCH_ASSOC);
            if ($computer['status'] !== 'available') {
                $error = "Computer is no longer available.";
            } else {
                // Start session
                $start = date('Y-m-d H:i:s');
                $stmt = $conn->prepare("INSERT INTO lab_sessions (user_id, computer_id, start_time, status) VALUES (?, ?, ?, 'active')");
                $stmt->execute([$user_id, $book['computer_id'], $start]);
                $conn->prepare("UPDATE computers SET status = 'in_use' WHERE id = ?")->execute([$book['computer_id']]);
                $conn->prepare("UPDATE bookings SET status = 'completed' WHERE id = ?")->execute([$booking_id]);
                $success = "Checked in. Your session has started.";
            }
        }
    } else {
        $error = "Invalid booking.";
    }
}

// List current and upcoming bookings
$bookings = $conn->prepare("SELECT b.*, c.computer_name 
                            FROM bookings b 
                            JOIN computers c ON b.computer_id = c.id 
                            WHERE b.user_id = ? AND b.booking_date >= CURDATE()
                            ORDER BY b.booking_date, b.start_time");
$bookings->execute([$user_id]);
$bookings = $bookings->fetchAll(PDO::FETCH_ASSOC);

// Get computers for dropdown (only available ones)
$computers = $conn->query("SELECT id, computer_name FROM computers WHERE status = 'available' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
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
    .btn-outline-modern {
        border: 1px solid #dee2e6;
        background: white;
        border-radius: 2rem;
    }
    .btn-outline-modern:hover {
        background: #f8f9fa;
        border-color: #adb5bd;
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
        display: inline-block;
    }
    .booking-badge.confirmed {
        background: #d4edda;
        color: #155724;
    }
    .booking-badge.pending {
        background: #fff3cd;
        color: #856404;
    }
    .booking-badge.cancelled {
        background: #f8d7da;
        color: #721c24;
    }
    .booking-badge.completed {
        background: #d1ecf1;
        color: #0c5460;
    }
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
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
    @media (max-width: 768px) {
        .welcome-title { font-size: 1.5rem; }
        .mission-text { font-size: 0.9rem; }
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
                    <i class="bi bi-calendar-plus"></i> Book a Computer
                </h1>
                <p class="mission-text mb-0">
                    <i class="bi bi-laptop"></i> Missions of Hope Namarei Computer Lab — Reserve your time for learning and innovation.
                </p>
                <p class="mt-2 small opacity-75">
                    Choose a computer, pick a date and time, and confirm your booking.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="computer_status.php" class="btn btn-light btn-modern rounded-pill px-4">
                    <i class="bi bi-play-circle"></i> Quick Start
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Alert messages -->
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- New Booking Card -->
    <div class="modern-card">
        <div class="card-header-modern">
            <i class="bi bi-plus-circle"></i> New Booking
        </div>
        <div class="card-body p-4">
            <form method="post">
                <div class="row g-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Computer</label>
                        <select name="computer_id" class="form-select form-control-modern" required>
                            <?php if (empty($computers)): ?>
                                <option value="" disabled>No computers available</option>
                            <?php else: ?>
                                <?php foreach ($computers as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= (isset($_GET['computer']) && $_GET['computer'] == $c['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['computer_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" name="booking_date" class="form-control form-control-modern" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Start Time</label>
                        <input type="time" name="start_time" class="form-control form-control-modern" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">End Time</label>
                        <input type="time" name="end_time" class="form-control form-control-modern" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="book" class="btn btn-primary-modern btn-modern px-4" <?= empty($computers) ? 'disabled' : '' ?>>
                            <i class="bi bi-calendar-check"></i> Book Now
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- My Bookings Card -->
    <div class="modern-card">
        <div class="card-header-modern">
            <i class="bi bi-journal-bookmark-fill"></i> My Bookings
        </div>
        <div class="card-body p-0">
            <?php if (count($bookings) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th><i class="bi bi-display"></i> Computer</th>
                                <th><i class="bi bi-calendar-date"></i> Date</th>
                                <th><i class="bi bi-clock"></i> Time Slot</th>
                                <th><i class="bi bi-tag"></i> Status</th>
                                <th><i class="bi bi-gear"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= htmlspecialchars($booking['computer_name']) ?></td>
                                <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                                <td><?= substr($booking['start_time'], 0, 5) ?> – <?= substr($booking['end_time'], 0, 5) ?></td>
                                <td>
                                    <span class="booking-badge <?= $booking['status'] ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($booking['status'] == 'confirmed' && $booking['booking_date'] >= date('Y-m-d')): ?>
                                        <a href="?checkin=<?= $booking['id'] ?>" class="btn btn-sm btn-success btn-modern me-1" onclick="return confirm('Start your session now?')">
                                            <i class="bi bi-play-fill"></i> Check In
                                        </a>
                                        <a href="?cancel=<?= $booking['id'] ?>" class="btn btn-sm btn-outline-danger btn-modern" onclick="return confirm('Cancel this booking?')">
                                            <i class="bi bi-x-lg"></i> Cancel
                                        </a>
                                    <?php elseif ($booking['status'] == 'cancelled'): ?>
                                        <span class="text-muted"><i class="bi bi-ban"></i> Cancelled</span>
                                    <?php elseif ($booking['status'] == 'completed'): ?>
                                        <span class="text-success"><i class="bi bi-check-lg"></i> Used</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                    <p class="mt-2 mb-0">No bookings found. Use the form above to reserve a computer.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>