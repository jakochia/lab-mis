<?php include 'includes/admin_header.php'; ?>

<?php
// Update ticket status or add response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'])) {
    $ticket_id = $_POST['ticket_id'];
    $status = $_POST['status'];
    $response = $_POST['admin_response'];
    $stmt = $conn->prepare("UPDATE support_tickets SET status = ?, admin_response = ? WHERE id = ?");
    $stmt->execute([$status, $response, $ticket_id]);
    $msg = "Ticket updated.";
}

$tickets = $conn->query("SELECT t.*, u.full_name, u.username, c.computer_name 
                         FROM support_tickets t 
                         LEFT JOIN users u ON t.user_id = u.id 
                         LEFT JOIN computers c ON t.computer_id = c.id
                         ORDER BY t.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_tickets = count($tickets);
$open_tickets = 0;
$in_progress = 0;
$resolved = 0;
$closed = 0;
foreach ($tickets as $t) {
    switch ($t['status']) {
        case 'open': $open_tickets++; break;
        case 'in_progress': $in_progress++; break;
        case 'resolved': $resolved++; break;
        case 'closed': $closed++; break;
    }
}
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
    .badge-resolved {
        background: #d4edda;
        color: #155724;
    }
    .badge-closed {
        background: #d1ecf1;
        color: #0c5460;
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
    .modal-content-modern {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
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
                    <i class="bi bi-ticket-perforated"></i> Support Tickets
                </h1>
                <p class="mission-text mb-0">
                    <i class="bi bi-building"></i> Missions of Hope Namarei Computer Lab — Manage student support requests.
                </p>
                <p class="mt-2 small opacity-75">
                    View, respond to, and update ticket statuses to keep students informed.
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
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-ticket-alt fa-2x text-primary"></i>
                <div class="stat-number"><?= $total_tickets ?></div>
                <div class="stat-label">Total Tickets</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                <div class="stat-number"><?= $open_tickets ?></div>
                <div class="stat-label">Open</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-spinner fa-2x text-warning"></i>
                <div class="stat-number"><?= $in_progress ?></div>
                <div class="stat-label">In Progress</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-check-circle fa-2x text-success"></i>
                <div class="stat-number"><?= $resolved + $closed ?></div>
                <div class="stat-label">Resolved/Closed</div>
            </div>
        </div>
    </div>

    <!-- Tickets Table Card -->
    <div class="modern-card">
        <div class="card-header-modern">
            <i class="bi bi-list-ul"></i> All Tickets
        </div>
        <div class="card-body p-0">
            <?php if (isset($msg)): ?>
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= $msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (count($tickets) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Computer</th>
                                <th>Subject</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Admin Response</th>
                                <th>Action</th>
                            </thead>
                        <tbody>
                            <?php foreach ($tickets as $t): ?>
                            汽
                                <td data-label="ID">#<?= $t['id'] ?></td>
                                <td data-label="Student"><?= htmlspecialchars($t['full_name']) ?></td>
                                <td data-label="Computer"><?= $t['computer_name'] ?? 'General' ?></td>
                                <td data-label="Subject"><?= htmlspecialchars($t['subject']) ?></td>
                                <td data-label="Description"><?= nl2br(htmlspecialchars($t['description'])) ?></td>
                                <td data-label="Status">
                                    <span class="badge-status badge-<?= $t['status'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $t['status'])) ?>
                                    </span>
                                </td>
                                <td data-label="Created"><?= date('M d, Y g:i A', strtotime($t['created_at'])) ?></td>
                                <td data-label="Admin Response">
                                    <?= $t['admin_response'] ? nl2br(htmlspecialchars($t['admin_response'])) : '<span class="text-muted">No response yet</span>' ?>
                                </td>
                                <td data-label="Action">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#replyModal<?= $t['id'] ?>">
                                        <i class="bi bi-reply"></i> Reply
                                    </button>
                                </td>
                            </tr>

                            <!-- Reply Modal (Modernized) -->
                            <div class="modal fade" id="replyModal<?= $t['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content modal-content-modern">
                                        <form method="post">
                                            <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                            <div class="modal-header border-0">
                                                <h5 class="modal-title"><i class="bi bi-chat-text"></i> Reply to Ticket #<?= $t['id'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Status</label>
                                                    <select name="status" class="form-select form-control-modern">
                                                        <option value="open" <?= $t['status'] == 'open' ? 'selected' : '' ?>>Open</option>
                                                        <option value="in_progress" <?= $t['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                                        <option value="resolved" <?= $t['status'] == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                                        <option value="closed" <?= $t['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Admin Response</label>
                                                    <textarea name="admin_response" class="form-control form-control-modern" rows="4" placeholder="Type your response here..."><?= htmlspecialchars($t['admin_response']) ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary-modern btn-modern">Update Ticket</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state text-center py-5">
                    <i class="bi bi-ticket-perforated fs-1 text-muted"></i>
                    <p class="mt-2 mb-0">No support tickets found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>