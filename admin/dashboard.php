<?php include 'includes/admin_header.php'; ?>

<?php
// Stats
$total_computers = 35;
$in_use = $conn->query("SELECT COUNT(*) FROM lab_sessions WHERE status = 'active'")->fetchColumn();
$available = $conn->query("SELECT COUNT(*) FROM computers WHERE status = 'available'")->fetchColumn();
$faulty = $conn->query("SELECT COUNT(*) FROM computers WHERE status = 'faulty'")->fetchColumn();
$total_students = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

// Total sessions today
$today = date('Y-m-d');
$today_sessions = $conn->query("SELECT COUNT(*) FROM lab_sessions WHERE DATE(start_time) = '$today'")->fetchColumn();

// Open support tickets
$open_tickets = $conn->query("SELECT COUNT(*) FROM support_tickets WHERE status != 'closed'")->fetchColumn();

// Usage trend (last 7 days)
$trend = $conn->query("
    SELECT DATE(start_time) as date, COUNT(*) as sessions 
    FROM lab_sessions 
    WHERE start_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(start_time)
    ORDER BY date
")->fetchAll(PDO::FETCH_ASSOC);
$trend_dates = array_column($trend, 'date');
$trend_counts = array_column($trend, 'sessions');

// Recent sessions (last 5)
$recent = $conn->query("
    SELECT ls.start_time, u.full_name, c.computer_name 
    FROM lab_sessions ls
    JOIN users u ON ls.user_id = u.id
    JOIN computers c ON ls.computer_id = c.id
    ORDER BY ls.start_time DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Faulty computers
$faulty_computers = $conn->query("SELECT computer_name FROM computers WHERE status = 'faulty'")->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- Modern Styles -->
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
    .list-group-item-modern {
        border: none;
        padding: 1rem 1.5rem;
        background: transparent;
        transition: background 0.2s;
    }
    .list-group-item-modern:hover {
        background: #f8f9fa;
    }
    .alert-modern {
        border-radius: 1rem;
        border-left: 4px solid #ffc107;
    }
    .btn-modern {
        border-radius: 2rem;
        padding: 0.5rem 1.2rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    @media (max-width: 768px) {
        .welcome-title { font-size: 1.5rem; }
        .mission-text { font-size: 0.9rem; }
        .stat-number { font-size: 1.5rem; }
    }
</style>

<!-- Bootstrap Icons + FontAwesome (for admin icons) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<!-- Hero Section -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title">
                    <i class="bi bi-speedometer2"></i> Admin Dashboard
                </h1>
                <p class="mission-text mb-0">
                    <i class="bi bi-building"></i> Missions of Hope Namarei Computer Lab — Monitor and manage lab operations.
                </p>
                <p class="mt-2 small opacity-75">
                    Welcome back, <?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?>! Here's today's overview.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="manage_computers.php" class="btn btn-light btn-modern rounded-pill px-4">
                    <i class="bi bi-pc-display"></i> Manage Computers
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Stats Row -->
    <div class="row g-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-desktop fa-2x text-primary"></i>
                <div class="stat-number"><?= $total_computers ?></div>
                <div class="stat-label">Total Computers</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-check-circle fa-2x text-success"></i>
                <div class="stat-number"><?= $available ?></div>
                <div class="stat-label">Available</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-users fa-2x text-warning"></i>
                <div class="stat-number"><?= $in_use ?></div>
                <div class="stat-label">In Use</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                <div class="stat-number"><?= $faulty ?></div>
                <div class="stat-label">Faulty</div>
            </div>
        </div>
    </div>

    <!-- Extra Stats Row -->
    <div class="row g-4 mt-2">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-user-graduate fa-2x text-info"></i>
                <div class="stat-number"><?= $total_students ?></div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-calendar-day fa-2x text-secondary"></i>
                <div class="stat-number"><?= $today_sessions ?></div>
                <div class="stat-label">Sessions Today</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-ticket-alt fa-2x text-warning"></i>
                <div class="stat-number"><?= $open_tickets ?></div>
                <div class="stat-label">Open Tickets</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-chart-line fa-2x text-primary"></i>
                <div class="stat-number"><?= array_sum($trend_counts) ?></div>
                <div class="stat-label">Sessions (7 days)</div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="card-header-modern">
                    <i class="fas fa-chart-line"></i> Lab Usage Trend (Last 7 Days)
                </div>
                <div class="card-body p-4">
                    <canvas id="usageChart" height="300" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="modern-card">
                <div class="card-header-modern">
                    <i class="fas fa-history"></i> Recent Sessions
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (count($recent) > 0): ?>
                            <?php foreach ($recent as $row): ?>
                                <div class="list-group-item list-group-item-modern">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
                                            <small class="text-muted"><i class="fas fa-laptop"></i> <?= $row['computer_name'] ?></small>
                                        </div>
                                        <small class="text-muted"><?= date('H:i', strtotime($row['start_time'])) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item list-group-item-modern text-muted">No recent sessions.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 text-center">
                    <a href="sessions_report.php" class="btn btn-sm btn-link">View all <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Faulty Computers Alert -->
    <?php if (count($faulty_computers) > 0): ?>
    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-warning alert-modern alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <strong>Faulty Computers Alert!</strong>
                The following computers need attention: <?= implode(', ', $faulty_computers) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('usageChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($trend_dates) ?>,
            datasets: [{
                label: 'Sessions',
                data: <?= json_encode($trend_counts) ?>,
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
                y: { beginAtZero: true, title: { display: true, text: 'Number of Sessions' } },
                x: { title: { display: true, text: 'Date' } }
            }
        }
    });
</script>

<!-- Ticket Notification Script (kept from original) -->
<script>
function checkNewTickets() {
    fetch('check_new_tickets.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('ticketBadge');
            if (data.new_tickets > 0) {
                if (badge) {
                    badge.innerText = data.new_tickets;
                    badge.style.display = 'inline-block';
                }
                // Show toast (optional)
                // ...
            } else {
                if (badge) badge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error checking tickets:', error));
}
// Check immediately and then every 15 seconds
checkNewTickets();
setInterval(checkNewTickets, 15000);
</script>

<?php include '../includes/footer.php'; ?>