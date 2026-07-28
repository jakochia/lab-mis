<?php
require_once '../includes/session.php';
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit;
}
require_once '../includes/Database.php';
$db = new Database();
$conn = $db->getConnection();

if (!isset($_SESSION['last_ticket_id'])) {
    // First time: store current max ID
    $stmt = $conn->query("SELECT MAX(id) as max_id FROM support_tickets");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['last_ticket_id'] = $row['max_id'] ?? 0;
    echo json_encode(['new_tickets' => 0]);
    exit;
}

$last_id = (int)$_SESSION['last_ticket_id'];
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM support_tickets WHERE id > ?");
$stmt->execute([$last_id]);
$count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

if ($count > 0) {
    // Update stored max ID
    $stmt = $conn->query("SELECT MAX(id) as max_id FROM support_tickets");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['last_ticket_id'] = $row['max_id'];
}

echo json_encode(['new_tickets' => $count]);
?>