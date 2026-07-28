<?php
require_once '../includes/session.php';
if ($_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/Database.php';
$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

$computer_id = isset($_GET['computer_id']) ? (int)$_GET['computer_id'] : 0;

if ($computer_id) {
    // Check if student already has an active session
    $check_active = $conn->prepare("SELECT id FROM lab_sessions WHERE user_id = ? AND status = 'active'");
    $check_active->execute([$user_id]);
    if ($check_active->rowCount() > 0) {
        $_SESSION['error'] = "You already have an active session. End it first.";
        header('Location: dashboard.php');
        exit;
    }

    // Check if computer is available
    $comp = $conn->prepare("SELECT status FROM computers WHERE id = ?");
    $comp->execute([$computer_id]);
    $computer = $comp->fetch();
    if ($computer['status'] !== 'available') {
        $_SESSION['error'] = "Computer is not available.";
        header('Location: computer_status.php');
        exit;
    }

    // Start session
    $start = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO lab_sessions (user_id, computer_id, start_time, status) VALUES (?, ?, ?, 'active')");
    if ($stmt->execute([$user_id, $computer_id, $start])) {
        // Update computer status
        $conn->prepare("UPDATE computers SET status = 'in_use' WHERE id = ?")->execute([$computer_id]);
        $_SESSION['success'] = "Session started on computer #$computer_id.";
    } else {
        $_SESSION['error'] = "Failed to start session.";
    }
} else {
    $_SESSION['error'] = "Invalid computer.";
}
header('Location: dashboard.php');
exit;
?>