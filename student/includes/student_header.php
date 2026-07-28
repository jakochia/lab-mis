<?php
require_once '../includes/session.php';
if ($_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/Database.php';
$db = new Database();
$conn = $db->getConnection();
require_once '../includes/functions.php'; // <-- Add this line

$user_id = $_SESSION['user_id'];

// Check if maintenance mode is active
$maintenance_mode = isMaintenanceMode($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student - Lab MIS</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Font Awesome 6 (for existing icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dark.css" id="dark-mode-style" disabled>
    <!-- jQuery (optional, but used in some scripts) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Student Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studentNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="studentNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="computer_status.php">Computer Status</a></li>
                    <li class="nav-item"><a class="nav-link" href="bookings.php">Bookings</a></li>
                    <li class="nav-item"><a class="nav-link" href="history.php">My History</a></li>
                    <li class="nav-item"><a class="nav-link" href="support.php">Support</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                    <li class="nav-item"><a class="nav-link" href="faq.php"><i class="bi bi-question-circle"></i> FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="feedback.php"><i class="bi bi-chat-right-text"></i> Feedback</a></li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><span class="nav-link"><?= htmlspecialchars($_SESSION['full_name']) ?></span></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
                    <li class="nav-item"><button id="darkModeToggle" class="btn btn-sm btn-outline-light">Dark Mode</button></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <?php if ($maintenance_mode): ?>
            <!-- Global maintenance alert (appears on all pages) -->
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-tools"></i> <strong>Maintenance Mode</strong>
                <br>The computer lab is currently under maintenance. New sessions and bookings are temporarily disabled.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>