<?php
require_once 'includes/Database.php';

$db = new Database();
$conn = $db->getConnection();

$new_password = 'admin123'; // change to your desired password
$hashed = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
$stmt->execute(['password' => $hashed]);

echo "Admin password reset to: $new_password";
?>