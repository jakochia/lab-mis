<?php
require_once 'includes/session.php';
if (!$auth->isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$message = trim($_POST['message'] ?? '');

if (empty($message)) {
    echo json_encode(['error' => 'Message cannot be empty']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO chat_messages (user_id, username, message) VALUES (?, ?, ?)");
$stmt->execute([$user_id, $username, $message]);

echo json_encode(['success' => true]);