<?php
require_once '../includes/session.php';
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    exit;
}
require_once '../includes/Database.php';
$db = new Database();
$conn = $db->getConnection();

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, full_name, avatar FROM users WHERE id != ? ORDER BY full_name");
$stmt->execute([$user_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($users);