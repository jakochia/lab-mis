<?php
require_once 'includes/session.php';
if (!$auth->isLoggedIn()) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

$stmt = $conn->prepare("SELECT id, username, message, created_at FROM chat_messages WHERE id > ? ORDER BY id ASC");
$stmt->execute([$last_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);