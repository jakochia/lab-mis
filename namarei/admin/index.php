<?php
include '../includes/config.php';
include '../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Namarei</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <h1>Dashboard</h1>
        <?php
        // Simple stats
        $stmt = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status='pending'");
        $pending = $stmt->fetchColumn();
        $stmt = $pdo->query("SELECT COUNT(*) FROM menu_items");
        $menuCount = $stmt->fetchColumn();
        ?>
        <div class="stats">
            <div class="card bg-red">Pending Reservations: <?= $pending ?></div>
            <div class="card bg-green">Menu Items: <?= $menuCount ?></div>
        </div>
    </div>
</div>
</body>
</html>