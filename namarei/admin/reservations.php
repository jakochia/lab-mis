<?php
include '../includes/config.php';
include '../includes/auth.php';

// Update status
if (isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    header('Location: reservations.php');
    exit;
}

// Fetch reservations
$stmt = $pdo->query("SELECT * FROM reservations ORDER BY date DESC, time DESC");
$reservations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Reservations - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1>Reservations</h1>
        <table>
            <thead>
                <tr><th>ID</th><th>Name</th><th>Phone</th><th>Date</th><th>Time</th><th>Guests</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($reservations as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['customer_name']) ?></td>
                <td><?= htmlspecialchars($r['phone']) ?></td>
                <td><?= $r['date'] ?></td>
                <td><?= $r['time'] ?></td>
                <td><?= $r['guests'] ?></td>
                <td><?= $r['status'] ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <select name="status">
                            <option value="pending" <?= $r['status']=='pending'?'selected':'' ?>>Pending</option>
                            <option value="confirmed" <?= $r['status']=='confirmed'?'selected':'' ?>>Confirmed</option>
                            <option value="cancelled" <?= $r['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>