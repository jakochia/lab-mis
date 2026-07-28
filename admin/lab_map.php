<?php include 'includes/admin_header.php'; ?>

<?php
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
    .computer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .computer-card {
        background: #fff;
        border-radius: 1rem;
        padding: 1.5rem 1rem;
        text-align: center;
        transition: all 0.2s;
        border: 2px solid transparent;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        cursor: pointer;
    }
    .computer-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.1);
    }
    .computer-card.available {
        border-color: #28a745;
        background: linear-gradient(135deg, #f0fff4 0%, #e6ffe6 100%);
    }
    .computer-card.in-use {
        border-color: #dc3545;
        background: linear-gradient(135deg, #fff5f5 0%, #ffe6e6 100%);
    }
    .computer-card.faulty {
        border-color: #ffc107;
        background: linear-gradient(135deg, #fff9e6 0%, #fff3cc 100%);
    }
    .computer-icon {
        font-size: 48px;
        margin-bottom: 10px;
    }
    .computer-card.available .computer-icon { color: #28a745; }
    .computer-card.in-use .computer-icon { color: #dc3545; }
    .computer-card.faulty .computer-icon { color: #ffc107; }
    .computer-name {
        font-weight: bold;
        font-size: 1.2rem;
        margin-bottom: 8px;
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
    .btn-modern {
        border-radius: 2rem;
        padding: 0.5rem 1.2rem;
        font-weight: 500;
        transition: all 0.2s;
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
    @media (max-width: 768px) {
        .welcome-title { font-size: 1.5rem; }
        .mission-text { font-size: 0.9rem; }
        .stat-number { font-size: 1.5rem; }
        .computer-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
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
                    <i class="bi bi-map"></i> Computer Lab Map
                </h1>
                <p class="mission-text mb-0">
                    <i class="bi bi-building"></i> Missions of Hope Namarei Computer Lab — Visual layout of all computers.
                </p>
                <p class="mt-2 small opacity-75">
                    Click on a computer to edit its details. Colors indicate availability.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="inventory.php" class="btn btn-light btn-modern rounded-pill px-4">
                    <i class="bi bi-boxes"></i> Manage Inventory
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
                <i class="fas fa-desktop fa-2x text-primary"></i>
                <div class="stat-number"><?= $total ?></div>
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
                <i class="fas fa-users fa-2x text-danger"></i>
                <div class="stat-number"><?= $in_use ?></div>
                <div class="stat-label">In Use</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                <div class="stat-number"><?= $faulty ?></div>
                <div class="stat-label">Faulty</div>
            </div>
        </div>
    </div>

    <!-- Computer Grid -->
    <div class="computer-grid">
        <?php foreach ($computers as $comp):
            $status_class = '';
            $status_icon = '';
            $status_text = '';
            switch ($comp['status']) {
                case 'available':
                    $status_class = 'available';
                    $status_icon = 'fas fa-check-circle';
                    $status_text = 'Available';
                    break;
                case 'in_use':
                    $status_class = 'in-use';
                    $status_icon = 'fas fa-desktop';
                    $status_text = 'In Use';
                    break;
                default:
                    $status_class = 'faulty';
                    $status_icon = 'fas fa-exclamation-triangle';
                    $status_text = 'Faulty';
                    break;
            }
        ?>
            <div class="computer-card <?= $status_class ?>" onclick="window.location.href='inventory.php?edit=<?= $comp['id'] ?>'">
                <div class="computer-icon">
                    <i class="<?= $status_icon ?>"></i>
                </div>
                <div class="computer-name"><?= htmlspecialchars($comp['computer_name']) ?></div>
                <div class="computer-status">
                    <span class="badge-status badge-<?= $comp['status'] ?>">
                        <?= $status_text ?>
                    </span>
                </div>
                <a href="inventory.php?edit=<?= $comp['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill mt-2">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>