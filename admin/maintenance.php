<?php include 'includes/admin_header.php'; ?>

<?php
// Toggle maintenance mode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle'])) {
    $new_status = ($_POST['mode'] === 'on') ? 'on' : 'off';
    $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'maintenance_mode'");
    $stmt->execute([$new_status]);
    $msg = "Maintenance mode has been turned " . ($new_status == 'on' ? 'ON' : 'OFF');
}

// Get current status
$stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'");
$stmt->execute();
$current = $stmt->fetchColumn();
$maintenance_on = ($current === 'on');
?>

<h2>Maintenance Mode</h2>

<?php if (isset($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="modern-card">
    <div class="card-header-modern">
        <i class="bi bi-tools"></i> System Maintenance
    </div>
    <div class="card-body p-4">
        <p>When maintenance mode is <strong class="text-danger">ON</strong>, students will see a friendly message and will be unable to start new sessions or make bookings. Administrators can still access all pages.</p>

        <form method="post" class="mt-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Current Status</label>
                    <div class="fw-bold <?= $maintenance_on ? 'text-danger' : 'text-success' ?>">
                        <?= $maintenance_on ? '🔴 MAINTENANCE MODE IS ON' : '🟢 NORMAL OPERATION' ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="mode" class="form-select form-control-modern">
                        <option value="off" <?= !$maintenance_on ? 'selected' : '' ?>>Turn OFF</option>
                        <option value="on" <?= $maintenance_on ? 'selected' : '' ?>>Turn ON</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" name="toggle" class="btn btn-primary-modern btn-modern w-100">
                        <i class="bi bi-power"></i> Apply Change
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>