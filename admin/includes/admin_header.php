<?php
// Start session if not already started (handled by session.php)
require_once '../includes/session.php';
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/Database.php';
$db = new Database();
$conn = $db->getConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Lab MIS</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Toastify CSS (for notifications) -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
    <!-- Dark mode CSS (disabled initially) -->
    <link rel="stylesheet" href="../assets/css/dark.css" id="dark-mode-style" disabled>
    <!-- jQuery (required for some scripts) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h4>Lab MIS</h4>
                <span class="text-muted">Admin Panel</span>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="user_management.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="inventory.php">
                        <i class="fas fa-desktop"></i> Inventory
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="session_logs.php">
                        <i class="fas fa-history"></i> Session Logs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-file-alt"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="support_tickets.php">
                        <i class="fas fa-ticket-alt"></i> Support
                        <span id="ticketBadge" class="badge bg-danger" style="display:none;"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="lab_map.php">
                        <i class="fas fa-map"></i> Lab Map
                    </a>
                </li>
                <!-- New admin pages -->
                <li class="nav-item">
                    <a class="nav-link" href="announcements.php">
                        <i class="fas fa-bullhorn"></i> Announcements
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="maintenance.php">
                        <i class="fas fa-tools"></i> Maintenance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="feedback.php">
                        <i class="fas fa-comment-dots"></i> Feedback
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="chat.php">
                        <i class="fas fa-comments"></i> Chat
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="../logout.php" class="btn btn-danger btn-sm w-100">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <nav class="topbar">
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-link" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="d-flex align-items-center">
                        <!-- FIXED: Added fallback for full_name -->
                        <span class="me-3">Welcome, <?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin') ?></span>
                        <button id="darkModeToggle" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-moon"></i>
                        </button>
                    </div>
                </div>
            </nav>

            <div class="container-fluid mt-4">
                <!-- Page content will be inserted here -->