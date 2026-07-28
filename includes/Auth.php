<?php
require_once __DIR__ . '/Database.php';
// No session_start() here – session is managed elsewhere

class Auth {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE (username = :username OR email = :username) AND status = 'active'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['last_activity'] = time();

            $update = $this->conn->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
            $update->execute(['id' => $user['id']]);

            return true;
        }
        return false;
    }

    public function register($username, $email, $password, $full_name, $security_q, $security_a) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password, role, full_name, security_question, security_answer) 
                VALUES (:username, :email, :password, 'student', :full_name, :security_q, :security_a)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashed,
            'full_name' => $full_name,
            'security_q' => $security_q,
            'security_a' => $security_a
        ]);
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function checkSessionTimeout() {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
            $this->logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) return null;
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>