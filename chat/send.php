<?php
require_once '../includes/session.php';
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    exit;
}
require_once '../includes/Database.php';
$db = new Database();
$conn = $db->getConnection();

$sender_id = $_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) && is_numeric($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : null;
$message = trim($_POST['message'] ?? '');

if (empty($message)) {
    echo json_encode(['error' => 'Message cannot be empty']);
    exit;
}

// Check if receiver exists (if provided)
if ($receiver_id !== null) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$receiver_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['error' => 'Invalid receiver']);
        exit;
    }
}

$stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
if ($stmt->execute([$sender_id, $receiver_id, $message])) {
    echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
} else {
    echo json_encode(['error' => 'Failed to save message']);
}