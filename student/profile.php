<?php include 'includes/student_header.php'; ?>

<?php
$user = $auth->getCurrentUser();
$error = '';
$success = '';

// Handle avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $error = "Only JPG, PNG, and GIF images are allowed.";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = "Image size must be less than 2MB.";
        } else {
            // Create uploads folder if it doesn't exist
            $upload_dir = 'uploads/avatars/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $new_filename = $user_id . '_' . time() . '.' . $ext;
            $destination = $upload_dir . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Attempt to resize if GD is available
                if (function_exists('imagecreatefromjpeg')) {
                    $img = null;
                    if ($ext == 'jpg' || $ext == 'jpeg') {
                        $img = imagecreatefromjpeg($destination);
                    } elseif ($ext == 'png') {
                        $img = imagecreatefrompng($destination);
                    } elseif ($ext == 'gif') {
                        $img = imagecreatefromgif($destination);
                    }
                    
                    if ($img) {
                        // Resize to 200x200 max (maintain aspect)
                        list($width, $height) = getimagesize($destination);
                        $max_size = 200;
                        $ratio = $width / $height;
                        if ($width > $max_size || $height > $max_size) {
                            if ($width > $height) {
                                $new_width = $max_size;
                                $new_height = $max_size / $ratio;
                            } else {
                                $new_height = $max_size;
                                $new_width = $max_size * $ratio;
                            }
                            $new_img = imagecreatetruecolor($new_width, $new_height);
                            imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                            
                            // Save resized image
                            if ($ext == 'jpg' || $ext == 'jpeg') {
                                imagejpeg($new_img, $destination, 90);
                            } elseif ($ext == 'png') {
                                imagepng($new_img, $destination);
                            } elseif ($ext == 'gif') {
                                imagegif($new_img, $destination);
                            }
                            imagedestroy($new_img);
                        }
                        imagedestroy($img);
                    }
                } // else: GD not installed, keep original image
                
                // Update database with new avatar filename
                $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                if ($stmt->execute([$new_filename, $user_id])) {
                    $success = "Avatar updated successfully.";
                    // Refresh user data
                    $user = $auth->getCurrentUser();
                } else {
                    $error = "Failed to update avatar in database.";
                }
            } else {
                $error = "Failed to upload file.";
            }
        }
    } else {
        $error = "Please select an image to upload.";
    }
}
// Remove avatar
if (isset($_GET['remove_avatar'])) {
    $old_avatar = $user['avatar'];
    if ($old_avatar && file_exists('uploads/avatars/' . $old_avatar)) {
        unlink('uploads/avatars/' . $old_avatar);
    }
    $stmt = $conn->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        $success = "Avatar removed.";
        $user = $auth->getCurrentUser();
    } else {
        $error = "Failed to remove avatar.";
    }
}

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $security_q = trim($_POST['security_question']);
    $security_a = trim($_POST['security_answer']);
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, security_question = ?, security_answer = ? WHERE id = ?");
    if ($stmt->execute([$full_name, $email, $security_q, $security_a, $user_id])) {
        $_SESSION['full_name'] = $full_name;
        $success = "Profile updated successfully.";
        $user = $auth->getCurrentUser(); // refresh
    } else {
        $error = "Update failed.";
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    if (password_verify($old, $user['password'])) {
        if ($new === $confirm) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user_id]);
            $success = "Password changed successfully.";
        } else {
            $error = "New passwords do not match.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}
?>

<!-- Modern Styles (consistent with other pages) -->
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        --card-border-radius: 1rem;
    }
    body {
        background: #f4f7fc;
    }
    .dashboard-header {
        background: var(--primary-gradient);
        border-radius: 0 0 2rem 2rem;
        padding: 2rem 0;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .welcome-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .mission-text {
        font-size: 1.1rem;
        opacity: 0.9;
        font-style: italic;
    }
    .modern-card {
        border: none;
        border-radius: var(--card-border-radius);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .modern-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 25px -12px rgba(0,0,0,0.1);
    }
    .card-header-modern {
        background: white;
        border-bottom: 2px solid #eef2f6;
        padding: 1rem 1.5rem;
        font-weight: 600;
        font-size: 1.1rem;
    }
    .card-header-modern i {
        margin-right: 0.5rem;
        color: #2a5298;
    }
    .btn-modern {
        border-radius: 2rem;
        padding: 0.5rem 1.2rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    .btn-primary-modern {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        border: none;
        color: white;
    }
    .btn-primary-modern:hover {
        background: linear-gradient(135deg, #162d54, #1f3e75);
        transform: translateY(-1px);
        box-shadow: 0 5px 10px rgba(0,0,0,0.1);
    }
    .btn-outline-modern {
        border: 1px solid #dee2e6;
        background: white;
        border-radius: 2rem;
    }
    .btn-outline-modern:hover {
        background: #f8f9fa;
        border-color: #adb5bd;
    }
    .form-control-modern {
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        padding: 0.6rem 1rem;
        transition: 0.2s;
    }
    .form-control-modern:focus {
        border-color: #2a5298;
        box-shadow: 0 0 0 0.2rem rgba(42,82,152,0.25);
    }
    .avatar-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 1rem;
    }
    .avatar-container {
        text-align: center;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 768px) {
        .welcome-title { font-size: 1.5rem; }
        .mission-text { font-size: 0.9rem; }
    }
</style>

<!-- Bootstrap Icons + FontAwesome (optional) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Hero Section -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title">
                    <i class="bi bi-person-circle"></i> My Profile
                </h1>
                <p class="mission-text mb-0">
                    <i class="bi bi-laptop"></i> Missions of Hope Namarei Computer Lab — Manage your account details.
                </p>
                <p class="mt-2 small opacity-75">
                    Update your personal information, change password, or upload a profile picture.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="dashboard.php" class="btn btn-light btn-modern rounded-pill px-4">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Alert Messages -->
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Profile Edit Card -->
        <div class="col-md-6">
            <div class="modern-card">
                <div class="card-header-modern">
                    <i class="bi bi-pencil-square"></i> Edit Profile
                </div>
                <div class="card-body p-4">
                    <!-- Avatar Section -->
                    <div class="avatar-container">
                        <?php if (!empty($user['avatar']) && file_exists('uploads/avatars/' . $user['avatar'])): ?>
                            <img src="uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="avatar-preview">
                        <?php else: ?>
                            <div class="avatar-preview" style="background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-person-fill" style="font-size: 3rem; color: #6c757d;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="mt-2">
                            <form method="post" enctype="multipart/form-data" class="d-inline">
                                <input type="file" name="avatar" accept="image/*" class="form-control form-control-modern d-inline-block w-auto" style="width: auto;">
                                <button type="submit" name="upload_avatar" class="btn btn-sm btn-primary-modern btn-modern">Upload</button>
                            </form>
                            <?php if (!empty($user['avatar'])): ?>
                                <a href="?remove_avatar=1" class="btn btn-sm btn-outline-danger btn-modern" onclick="return confirm('Remove your profile picture?')">Remove</a>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">Allowed: JPG, PNG, GIF (max 2MB)</small>
                    </div>

                    <form method="post">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="full_name" class="form-control form-control-modern" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control form-control-modern" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Security Question</label>
                            <input type="text" name="security_question" class="form-control form-control-modern" value="<?= htmlspecialchars($user['security_question']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Security Answer</label>
                            <input type="text" name="security_answer" class="form-control form-control-modern" value="<?= htmlspecialchars($user['security_answer']) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary-modern btn-modern">
                            <i class="bi bi-save"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="col-md-6">
            <div class="modern-card">
                <div class="card-header-modern">
                    <i class="bi bi-key"></i> Change Password
                </div>
                <div class="card-body p-4">
                    <form method="post">
                        <input type="hidden" name="change_password" value="1">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password</label>
                            <input type="password" name="old_password" class="form-control form-control-modern" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password</label>
                            <input type="password" name="new_password" class="form-control form-control-modern" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control form-control-modern" required>
                        </div>
                        <button type="submit" class="btn btn-warning btn-modern">
                            <i class="bi bi-shield-lock"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>