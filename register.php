<?php
require_once 'includes/session.php';
if ($auth->isLoggedIn()) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php'));
    exit;
}
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $security_q = trim($_POST['security_question']);
    $security_a = trim($_POST['security_answer']);

    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($security_q) || empty($security_a)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        $auth = new Auth();
        if ($auth->register($username, $email, $password, $full_name, $security_q, $security_a)) {
            $success = "Registration successful. You can now login.";
            // Optionally redirect after 2 seconds
            // header("Refresh: 2; url=login.php");
        } else {
            $error = "Registration failed. Username or email may already exist.";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Student Registration</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <p><a href="login.php">Click here to login</a></p>
                <?php else: ?>
                    <form method="post">
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Security Question</label>
                            <select name="security_question" class="form-control" required>
                                <option value="">-- Select a question --</option>
                                <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                                <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                                <option value="What is your favorite book?">What is your favorite book?</option>
                                <option value="What city were you born in?">What city were you born in?</option>
                                <option value="What was your first car?">What was your first car?</option>
                                <option value="What is your favorite teacher's name?">What is your favorite teacher's name?</option>
                                <option value="What is the name of your childhood friend?">What is the name of your childhood friend?</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Security Answer</label>
                            <input type="text" name="security_answer" class="form-control" required>
                            <small class="text-muted">This answer will be used to recover your password.</small>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Register</button>
                    </form>
                    <hr>
                    <p class="text-center"><a href="login.php">Already have an account? Login</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>