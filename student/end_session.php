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

// Find active session
$stmt = $conn->prepare("SELECT id, computer_id, start_time FROM lab_sessions WHERE user_id = ? AND status = 'active'");
$stmt->execute([$user_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);
if ($session) {
    $end = date('Y-m-d H:i:s');
    $start = new DateTime($session['start_time']);
    $end_dt = new DateTime($end);
    $duration = $end_dt->getTimestamp() - $start->getTimestamp();
    $duration_minutes = round($duration / 60);

    $update = $conn->prepare("UPDATE lab_sessions SET end_time = ?, duration_minutes = ?, status = 'completed' WHERE id = ?");
    $update->execute([$end, $duration_minutes, $session['id']]);

    // Update computer status
    $conn->prepare("UPDATE computers SET status = 'available' WHERE id = ?")->execute([$session['computer_id']]);

    $_SESSION['success'] = "Session ended. Duration: $duration_minutes minutes.";
} else {
    $_SESSION['error'] = "No active session found.";
}
header('Location: dashboard.php');
exit;
?>