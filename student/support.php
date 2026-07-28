<?php include 'includes/student_header.php'; ?>

<?php
// Submit new ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ticket'])) {
    $computer_id = $_POST['computer_id'] ?: null;
    $subject = trim($_POST['subject']);
    $description = trim($_POST['description']);
    $stmt = $conn->prepare("INSERT INTO support_tickets (user_id, computer_id, subject, description) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $computer_id, $subject, $description])) {
        $success = "Ticket submitted successfully.";
    } else {
        $error = "Failed to submit ticket.";
    }
}

// List user's tickets
$tickets = $conn->prepare("SELECT t.*, c.computer_name 
                           FROM support_tickets t 
                           LEFT JOIN computers c ON t.computer_id = c.id 
                           WHERE t.user_id = ? 
                           ORDER BY t.created_at DESC");
$tickets->execute([$user_id]);
$tickets = $tickets->fetchAll(PDO::FETCH_ASSOC);

// Computers list for dropdown
$computers = $conn->query("SELECT id, computer_name FROM computers ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_tickets = count($tickets);
$open_tickets = 0;
$in_progress = 0;
$closed_tickets = 0;
foreach ($tickets as $t) {
    if ($t['status'] == 'open') $open_tickets++;
    elseif ($t['status'] == 'in_progress') $in_progress++;
    elseif ($t['status'] == 'closed') $closed_tickets++;
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
    .badge-open {
        background: #f8d7da;
        color: #721c24;
    }
    .badge-in_progress {
        background: #fff3cd;
        color: #856404;
    }
    .badge-closed {
        background: #d4edda;
        color: #155724;
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

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Hero Section -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title">
                    <i class="bi bi-headset"></i> Support Tickets
                </h1>
                <p class="mission-text mb-0">
                    <i class="bi bi-laptop"></i> Missions of Hope Namarei Computer Lab — Get help with computer issues or lab concerns.
                </p>
                <p class="mt-2 small opacity-75">
                    Submit a ticket for technical support, report a problem, or ask a question.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="#new-ticket" class="btn btn-light btn-modern rounded-pill px-4">
                    <i class="bi bi-plus-circle"></i> New Ticket
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

    <!-- Statistics Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= $total_tickets ?></div>
                <div class="stats-label">Total Tickets</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= $open_tickets ?></div>
                <div class="stats-label">Open</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= $in_progress ?></div>
                <div class="stats-label">In Progress</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= $closed_tickets ?></div>
                <div class="stats-label">Closed</div>
            </div>
        </div>
    </div>

    <!-- New Ticket Card -->
    <div class="modern-card" id="new-ticket">
        <div class="card-header-modern">
            <i class="bi bi-envelope-paper"></i> Submit New Ticket
        </div>
        <div class="card-body p-4">
            <form method="post">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Computer (optional)</label>
                        <select name="computer_id" class="form-select form-control-modern">
                            <option value="">-- Not Specific --</option>
                            <?php foreach ($computers as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['computer_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Subject</label>
                        <input type="text" name="subject" class="form-control form-control-modern" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control form-control-modern" rows="4" required></textarea>
                        <small class="text-muted">Please provide as much detail as possible.</small>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="submit_ticket" class="btn btn-primary-modern btn-modern px-4">
                            <i class="bi bi-send"></i> Submit Ticket
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- My Tickets Card -->
    <div class="modern-card">
        <div class="card-header-modern">
            <i class="bi bi-journal-text"></i> My Tickets
        </div>
        <div class="card-body p-0">
            <?php if (count($tickets) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Computer</th>
                                <th>Subject</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Admin Response</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td data-label="ID">#<?= $t['id'] ?></td>
                                <td data-label="Computer"><?= $t['computer_name'] ?? 'General' ?></td>
                                <td data-label="Subject"><?= htmlspecialchars($t['subject']) ?></td>
                                <td data-label="Description"><?= nl2br(htmlspecialchars($t['description'])) ?></td>
                                <td data-label="Status">
                                    <span class="badge-status badge-<?= $t['status'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $t['status'])) ?>
                                    </span>
                                </td>
                                <td data-label="Admin Response">
                                    <?= $t['admin_response'] ? nl2br(htmlspecialchars($t['admin_response'])) : '<span class="text-muted">No response yet</span>' ?>
                                </td>
                                <td data-label="Date"><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-chat-dots fs-1 text-muted"></i>
                    <p class="mt-2 mb-0">No tickets submitted yet. Use the form above to create your first support ticket.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>