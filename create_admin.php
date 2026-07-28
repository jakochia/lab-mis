<?php
require_once 'includes/Database.php';

$db = new Database();
$conn = $db->getConnection();

$username = 'admin';
$email = 'admin@lab.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$full_name = 'Lab Administrator';
$security_q = 'What is your pet name?';
$security_a = 'admin';

$sql = "INSERT INTO users (username, email, password, role, full_name, security_question, security_answer) 
        VALUES (:username, :email, :password, 'admin', :full_name, :security_q, :security_a)";

$stmt = $conn->prepare($sql);
$stmt->execute([
    'username' => $username,
    'email' => $email,
    'password' => $password,
    'full_name' => $full_name,
    'security_q' => $security_q,
    'security_a' => $security_a
]);

echo "Admin user created successfully!<br>";
echo "Username: admin<br>";
echo "Password: admin123<br>";
echo "You can now login at <a href='login.php'>login.php</a>";
?>