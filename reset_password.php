<?php
require_once 'includes/session.php';
require_once 'includes/Database.php';
$db = new Database();
$conn = $db->getConnection();
$error = '';
$success = '';

if (!isset($_GET['token'])) {
    header('Location: login.php');
    exit;
}
$token = $_GET['token'];
$stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()");
$stmt->execute(['token' => $token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$reset) {
    $error = "Invalid or expired reset token.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = :password WHERE email = :email");
        $update->execute(['password' => $hashed, 'email' => $reset['email']]);
        // Delete token
        $conn->prepare("DELETE FROM password_resets WHERE token = :token")->execute(['token' => $token]);
        $success = "Password has been reset. You can now login.";
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">Reset Password</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                    <p><a href="login.php">Login</a></p>
                <?php else: ?>
                    <form method="post">
                        <div class="mb-3">
                            <label>New Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>