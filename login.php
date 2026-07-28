<?php
require_once 'includes/session.php';
if ($auth->isLoggedIn()) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php'));
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if ($auth->login($username, $password)) {
        if ($_SESSION['role'] === 'admin') header('Location: admin/dashboard.php');
        else header('Location: student/dashboard.php');
        exit;
    } else {
        $error = 'Invalid username/email or password.';
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Login to Lab MIS</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label>Username or Email</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <hr>
                <p class="text-center">
                    <a href="register.php">Register as Student</a> |
                    <a href="password_recovery.php">Forgot Password?</a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>