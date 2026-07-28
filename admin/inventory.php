<?php include 'includes/admin_header.php'; ?>

<?php
$msg = '';
$error = '';

// Process form submissions (same as before, but with improved error reporting)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_computer':
                $name = trim($_POST['computer_name']);
                $status = $_POST['status'];
                $last_maintenance = $_POST['last_maintenance_date'] ?: null;
                $notes = trim($_POST['notes']);
                $stmt = $conn->prepare("INSERT INTO computers (computer_name, status, last_maintenance_date, notes) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $status, $last_maintenance, $notes]) ? $msg = "Computer added." : $error = "Failed to add computer.";
                break;
            case 'edit_computer':
                $id = (int)$_POST['id'];
                $status = $_POST['status'];
                $last_maintenance = $_POST['last_maintenance_date'] ?: null;
                $notes = trim($_POST['notes']);
                $stmt = $conn->prepare("UPDATE computers SET status=?, last_maintenance_date=?, notes=? WHERE id=?");
                $stmt->execute([$status, $last_maintenance, $notes, $id]) ? $msg = "Computer updated." : $error = "Failed to update computer.";
                break;
            case 'delete_computer':
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("DELETE FROM computers WHERE id=?");
                $stmt->execute([$id]) ? $msg = "Computer deleted." : $error = "Failed to delete computer.";
                break;
            case 'add_equipment':
                $name = trim($_POST['name']);
                $computer_id = $_POST['computer_id'] ? (int)$_POST['computer_id'] : null;
                $status = $_POST['status'];
                $last_maintenance = $_POST['last_maintenance_date'] ?: null;
                $notes = trim($_POST['notes']);
                $stmt = $conn->prepare("INSERT INTO equipment (name, computer_id, status, last_maintenance, notes) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $computer_id, $status, $last_maintenance, $notes]) ? $msg = "Equipment added." : $error = "Failed to add equipment.";
                break;
            case 'edit_equipment':
                $id = (int)$_POST['id'];
                $status = $_POST['status'];
                $last_maintenance = $_POST['last_maintenance_date'] ?: null;
                $notes = trim($_POST['notes']);
                $stmt = $conn->prepare("UPDATE equipment SET status=?, last_maintenance=?, notes=? WHERE id=?");
                $stmt->execute([$status, $last_maintenance, $notes, $id]) ? $msg = "Equipment updated." : $error = "Failed to update equipment.";
                break;
            case 'delete_equipment':
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("DELETE FROM equipment WHERE id=?");
                $stmt->execute([$id]) ? $msg = "Equipment deleted." : $error = "Failed to delete equipment.";
                break;
        }
    }
}

$computers = $conn->query("SELECT * FROM computers ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$equipments = $conn->query("SELECT e.*, c.computer_name FROM equipment e LEFT JOIN computers c ON e.computer_id = c.id ORDER BY e.id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Statistics
$total_computers = count($computers);
$total_equipment = count($equipments);
$faulty_computers = 0;
$faulty_equipment = 0;
foreach ($computers as $c) {
    if ($c['status'] == 'faulty') $faulty_computers++;
}
foreach ($equipments as $e) {
    if ($e['status'] == 'faulty') $faulty_equipment++;
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
    .badge-available { background: #d4edda; color: #155724; }
    .badge-in_use { background: #f8d7da; color: #721c24; }
    .badge-faulty { background: #fff3cd; color: #856404; }
    .badge-maintenance { background: #d1ecf1; color: #0c5460; }
    .badge-working { background: #d4edda; color: #155724; }
    .modal-content-modern {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
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
                    <i class="bi bi-boxes"></i> Inventory Management
                </h1>
                <p class="mission-text mb-0">
                    <i class="bi bi-building"></i> Missions of Hope Namarei Computer Lab — Manage computers and equipment.
                </p>
                <p class="mt-2 small opacity-75">
                    Track status, maintenance records, and notes for all hardware.
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
    <!-- Alert Messages -->
    <?php if ($msg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Row -->
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
                <i class="fas fa-keyboard fa-2x text-success"></i>
                <div class="stat-number"><?= $total_equipment ?></div>
                <div class="stat-label">Total Equipment</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                <div class="stat-number"><?= $faulty_computers ?></div>
                <div class="stat-label">Faulty Computers</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-tools fa-2x text-danger"></i>
                <div class="stat-number"><?= $faulty_equipment ?></div>
                <div class="stat-label">Faulty Equipment</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Computers Card -->
        <div class="col-lg-6">
            <div class="modern-card">
                <div class="card-header-modern d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-desktop"></i> Computers
                    </div>
                    <button class="btn btn-sm btn-primary-modern btn-modern" data-bs-toggle="collapse" data-bs-target="#addComputerForm">
                        <i class="bi bi-plus-lg"></i> Add
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="collapse" id="addComputerForm">
                        <div class="p-4 border-bottom">
                            <form method="post" class="row g-3">
                                <input type="hidden" name="action" value="add_computer">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Computer Name</label>
                                    <input type="text" name="computer_name" class="form-control form-control-modern" placeholder="e.g., PC-01, Dell Optiplex" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select name="status" class="form-select form-control-modern">
                                        <option value="available">Available</option>
                                        <option value="in_use">In Use</option>
                                        <option value="faulty">Faulty</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Last Maintenance</label>
                                    <input type="date" name="last_maintenance_date" class="form-control form-control-modern">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Notes</label>
                                    <textarea name="notes" class="form-control form-control-modern" rows="2" placeholder="Any additional information"></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary-modern btn-modern w-100">Add Computer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Last Maint.</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($computers as $c): ?>
                                <tr>
                                    <td data-label="Name"><?= htmlspecialchars($c['computer_name']) ?></td>
                                    <td data-label="Status">
                                        <span class="badge-status badge-<?= $c['status'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $c['status'])) ?>
                                        </span>
                                    </td>
                                    <td data-label="Last Maint."><?= $c['last_maintenance_date'] ?: '-' ?></td>
                                    <td data-label="Actions">
                                        <button class="btn btn-sm btn-outline-warning rounded-pill" data-bs-toggle="modal" data-bs-target="#editComputerModal<?= $c['id'] ?>">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="deleteComputer(<?= $c['id'] ?>)">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>

                                <!-- Edit Modal for Computer -->
                                <div class="modal fade" id="editComputerModal<?= $c['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <form method="post" class="modal-content modal-content-modern">
                                            <input type="hidden" name="action" value="edit_computer">
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <div class="modal-header border-0">
                                                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Computer</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Computer Name</label>
                                                    <input type="text" class="form-control form-control-modern" value="<?= htmlspecialchars($c['computer_name']) ?>" disabled>
                                                    <small class="text-muted">Name cannot be changed.</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Status</label>
                                                    <select name="status" class="form-select form-control-modern">
                                                        <option value="available" <?= $c['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                                                        <option value="in_use" <?= $c['status'] == 'in_use' ? 'selected' : '' ?>>In Use</option>
                                                        <option value="faulty" <?= $c['status'] == 'faulty' ? 'selected' : '' ?>>Faulty</option>
                                                        <option value="maintenance" <?= $c['status'] == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Last Maintenance</label>
                                                    <input type="date" name="last_maintenance_date" class="form-control form-control-modern" value="<?= $c['last_maintenance_date'] ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Notes</label>
                                                    <textarea name="notes" class="form-control form-control-modern" rows="2"><?= htmlspecialchars($c['notes']) ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary-modern btn-modern">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipment Card -->
        <div class="col-lg-6">
            <div class="modern-card">
                <div class="card-header-modern d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-keyboard"></i> Equipment
                    </div>
                    <button class="btn btn-sm btn-primary-modern btn-modern" data-bs-toggle="collapse" data-bs-target="#addEquipmentForm">
                        <i class="bi bi-plus-lg"></i> Add
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="collapse" id="addEquipmentForm">
                        <div class="p-4 border-bottom">
                            <form method="post" class="row g-3">
                                <input type="hidden" name="action" value="add_equipment">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Equipment Name</label>
                                    <input type="text" name="name" class="form-control form-control-modern" placeholder="e.g., Mouse, Keyboard, Monitor" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Associated Computer</label>
                                    <select name="computer_id" class="form-select form-control-modern">
                                        <option value="">-- None --</option>
                                        <?php foreach ($computers as $c): ?>
                                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['computer_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select name="status" class="form-select form-control-modern">
                                        <option value="working">Working</option>
                                        <option value="faulty">Faulty</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Last Maintenance</label>
                                    <input type="date" name="last_maintenance_date" class="form-control form-control-modern">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Notes</label>
                                    <textarea name="notes" class="form-control form-control-modern" rows="2" placeholder="Any additional information"></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary-modern btn-modern w-100">Add Equipment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Associated</th>
                                    <th>Status</th>
                                    <th>Last Maint.</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipments as $e): ?>
                                <tr>
                                    <td data-label="Name"><?= htmlspecialchars($e['name']) ?></td>
                                    <td data-label="Associated"><?= $e['computer_name'] ?? 'None' ?></td>
                                    <td data-label="Status">
                                        <span class="badge-status badge-<?= $e['status'] ?>">
                                            <?= ucfirst($e['status']) ?>
                                        </span>
                                    </td>
                                    <td data-label="Last Maint."><?= $e['last_maintenance'] ?: '-' ?></td>
                                    <td data-label="Actions">
                                        <button class="btn btn-sm btn-outline-warning rounded-pill" data-bs-toggle="modal" data-bs-target="#editEquipmentModal<?= $e['id'] ?>">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="deleteEquipment(<?= $e['id'] ?>)">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>

                                <!-- Edit Modal for Equipment -->
                                <div class="modal fade" id="editEquipmentModal<?= $e['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <form method="post" class="modal-content modal-content-modern">
                                            <input type="hidden" name="action" value="edit_equipment">
                                            <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                            <div class="modal-header border-0">
                                                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Equipment</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Equipment Name</label>
                                                    <input type="text" class="form-control form-control-modern" value="<?= htmlspecialchars($e['name']) ?>" disabled>
                                                    <small class="text-muted">Name cannot be changed.</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Status</label>
                                                    <select name="status" class="form-select form-control-modern">
                                                        <option value="working" <?= $e['status'] == 'working' ? 'selected' : '' ?>>Working</option>
                                                        <option value="faulty" <?= $e['status'] == 'faulty' ? 'selected' : '' ?>>Faulty</option>
                                                        <option value="maintenance" <?= $e['status'] == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Last Maintenance</label>
                                                    <input type="date" name="last_maintenance_date" class="form-control form-control-modern" value="<?= $e['last_maintenance'] ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Notes</label>
                                                    <textarea name="notes" class="form-control form-control-modern" rows="2"><?= htmlspecialchars($e['notes']) ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary-modern btn-modern">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteComputer(id) {
    if (confirm("Are you sure you want to delete this computer? This action cannot be undone.")) {
        var form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = '<input type="hidden" name="action" value="delete_computer"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
function deleteEquipment(id) {
    if (confirm("Are you sure you want to delete this equipment? This action cannot be undone.")) {
        var form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = '<input type="hidden" name="action" value="delete_equipment"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>