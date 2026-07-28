<?php
require_once 'includes/session.php';
require_once 'includes/Database.php';
$db = new Database();
$conn = $db->getConnection();
$error = '';
$success = '';
$step = 'ask_user'; // ask_user, ask_question, reset_password
$user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Identify user
    if (isset($_POST['identify'])) {
        $identifier = trim($_POST['identifier']);
        $stmt = $conn->prepare("SELECT id, username, email, security_question, security_answer FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['recovery_user_id'] = $user['id'];
            $_SESSION['recovery_username'] = $user['username'];
            $_SESSION['recovery_email'] = $user['email'];
            $_SESSION['recovery_question'] = $user['security_question'];
            $_SESSION['recovery_answer'] = $user['security_answer'];
            $step = 'choose_method';
        } else {
            $error = "No account found with that username or email.";
        }
    }
    // Step 2: Choose method
    elseif (isset($_POST['method'])) {
        $method = $_POST['method'];
        if ($method === 'email') {
            // Generate token and send email
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['recovery_email'], $token, $expires]);
            $resetLink = BASE_URL . "reset_password.php?token=" . $token;
            // Send email (use mail() or PHPMailer)
            $subject = "Password Reset Request";
            $message = "Click the link to reset your password: $resetLink\n\nThis link expires in 1 hour.";
            mail($_SESSION['recovery_email'], $subject, $message, "From: noreply@labmis.com");
            $success = "A password reset link has been sent to your email address.";
            unset($_SESSION['recovery_user_id'], $_SESSION['recovery_username'], $_SESSION['recovery_email'], $_SESSION['recovery_question'], $_SESSION['recovery_answer']);
            $step = 'done';
        } elseif ($method === 'security') {
            $step = 'ask_question';
        } else {
            $error = "Invalid choice.";
        }
    }
    // Step 3: Verify security answer
    elseif (isset($_POST['verify_answer'])) {
        $answer = trim($_POST['security_answer']);
        if (strtolower($answer) === strtolower($_SESSION['recovery_answer'])) {
            $step = 'reset_password';
        } else {
            $error = "Incorrect answer. Please try again.";
            $step = 'ask_question'; // allow retry
        }
    }
    // Step 4: Set new password
    elseif (isset($_POST['reset_password'])) {
        $new_password = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        if ($new_password !== $confirm) {
            $error = "Passwords do not match.";
            $step = 'reset_password';
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters.";
            $step = 'reset_password';
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $_SESSION['recovery_user_id']]);
            $success = "Password has been reset. You can now login.";
            unset($_SESSION['recovery_user_id'], $_SESSION['recovery_username'], $_SESSION['recovery_email'], $_SESSION['recovery_question'], $_SESSION['recovery_answer']);
            $step = 'done';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">Password Recovery</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                    <p><a href="login.php">Back to Login</a></p>
                <?php elseif ($step == 'ask_user'): ?>
                    <form method="post">
                        <div class="mb-3">
                            <label>Username or Email</label>
                            <input type="text" name="identifier" class="form-control" required>
                        </div>
                        <button type="submit" name="identify" class="btn btn-primary w-100">Continue</button>
                    </form>
                    <hr>
                    <p class="text-center"><a href="login.php">Back to Login</a></p>
                <?php elseif ($step == 'choose_method'): ?>
                    <p>We found account: <strong><?= htmlspecialchars($_SESSION['recovery_username']) ?></strong></p>
                    <form method="post">
                        <div class="mb-3">
                            <label>Choose a recovery method:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="method" value="email" id="emailMethod" checked>
                                <label class="form-check-label" for="emailMethod">Send reset link to <?= htmlspecialchars($_SESSION['recovery_email']) ?></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="method" value="security" id="securityMethod">
                                <label class="form-check-label" for="securityMethod">Answer security question</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Continue</button>
                    </form>
                <?php elseif ($step == 'ask_question'): ?>
                    <p>Security Question:</p>
                    <div class="alert alert-info"><?= htmlspecialchars($_SESSION['recovery_question']) ?></div>
                    <form method="post">
                        <div class="mb-3">
                            <label>Your Answer</label>
                            <input type="text" name="security_answer" class="form-control" required>
                        </div>
                        <button type="submit" name="verify_answer" class="btn btn-primary w-100">Verify</button>
                    </form>
                <?php elseif ($step == 'reset_password'): ?>
                    <form method="post">
                        <div class="mb-3">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="reset_password" class="btn btn-primary w-100">Set New Password</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>