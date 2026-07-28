<?php include 'includes/admin_header.php'; ?>

<?php
$msg = '';
$error = '';

// Handle add/edit/delete as before
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $full_name = trim($_POST['full_name']);
        $role = $_POST['role'];
        $security_q = trim($_POST['security_question']);
        $security_a = trim($_POST['security_answer']);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, full_name, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $role, $full_name, $security_q, $security_a]);
        $msg = "User added successfully.";
    } elseif ($_POST['action'] === 'edit') {
        $id = $_POST['id'];
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, role=?, status=? WHERE id=?");
        $stmt->execute([$full_name, $email, $role, $status, $id]);
        $msg = "User updated successfully.";
    } elseif ($_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='student'");
        $stmt->execute([$id]);
        $msg = "User deleted successfully.";
    }
}

// Handle bulk upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_upload'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        if ($handle !== false) {
            // Read first line (headers)
            $headers = fgetcsv($handle);
            $expected_headers = ['username', 'email', 'full_name', 'role', 'password', 'security_question', 'security_answer'];
            if ($headers !== $expected_headers) {
                $error = "Invalid CSV format. Please use the sample template.";
            } else {
                $success_count = 0;
                $fail_count = 0;
                $errors = [];
                $duplicates = [];

                while (($data = fgetcsv($handle)) !== false) {
                    // Ensure we have 7 columns
                    if (count($data) < 7) {
                        $fail_count++;
                        continue;
                    }
                    list($username, $email, $full_name, $role, $password, $sec_q, $sec_a) = $data;
                    $username = trim($username);
                    $email = trim($email);
                    $full_name = trim($full_name);
                    $role = trim($role);
                    $password = trim($password);
                    $sec_q = trim($sec_q);
                    $sec_a = trim($sec_a);

                    // Basic validation
                    if (empty($username) || empty($email) || empty($full_name) || empty($password) || empty($sec_q) || empty($sec_a)) {
                        $fail_count++;
                        $errors[] = "Missing required fields for user: $username";
                        continue;
                    }
                    if (!in_array($role, ['student', 'admin'])) {
                        $fail_count++;
                        $errors[] = "Invalid role '$role' for user: $username";
                        continue;
                    }
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $fail_count++;
                        $errors[] = "Invalid email '$email' for user: $username";
                        continue;
                    }

                    // Check if username or email already exists
                    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                    $stmt->execute([$username, $email]);
                    if ($stmt->rowCount() > 0) {
                        $fail_count++;
                        $duplicates[] = "$username (username or email already exists)";
                        continue;
                    }

                    // Insert user
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, full_name, security_question, security_answer, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
                    if ($stmt->execute([$username, $email, $hashed, $role, $full_name, $sec_q, $sec_a])) {
                        $success_count++;
                    } else {
                        $fail_count++;
                        $errors[] = "Database error for user: $username";
                    }
                }
                fclose($handle);

                $msg = "Bulk upload complete. $success_count users added, $fail_count failed.";
                if (!empty($errors)) {
                    $msg .= "<br>Errors: " . implode(', ', array_slice($errors, 0, 5)) . (count($errors) > 5 ? '...' : '');
                }
                if (!empty($duplicates)) {
                    $msg .= "<br>Duplicates: " . implode(', ', array_slice($duplicates, 0, 5)) . (count($duplicates) > 5 ? '...' : '');
                }
            }
        } else {
            $error = "Failed to open uploaded file.";
        }
    } else {
        $error = "Please select a CSV file to upload.";
    }
}

// Fetch users
$users = $conn->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Statistics
$total_users = count($users);
$total_admins = 0;
$total_students = 0;
$active_users = 0;
foreach ($users as $u) {
    if ($u['role'] == 'admin') $total_admins++;
    else $total_students++;
    if ($u['status'] == 'active') $active_users++;
}
?>

<!-- Modern Styles (consistent with admin dashboard) -->
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        --admin-accent: #1abc9c;
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
    .stat-card {
        background: white;
        border-radius: 1rem;
        padding: 1.2rem;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        margin-bottom: 1.5rem;
        border: none;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.1);
    }
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #2a5298;
        margin: 0.5rem 0;
    }
    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .modern-card {
        border: none;
        border-radius: var(--card-border-radius);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        margin-bottom: 1.5rem;
        overflow: hidden;
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
        color: var(--admin-accent);
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
    .table-modern {
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }
    .table-modern thead th {
        border: none;
        background: #f8fafc;
        font-weight: 600;
        padding: 0.75rem 1rem;
    }
    .table-modern tbody tr {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        transition: background 0.2s;
    }
    .table-modern tbody tr:hover {
        background: #fef9e6;
    }
    .table-modern td {
        border: none;
        padding: 0.9rem 1rem;
        vertical-align: middle;
    }
    .badge-status {
        font-size: 0.75rem;
        padding: 0.35rem 0.7rem;
        border-radius: 2rem;
        font-weight: 500;
    }
    .badge-active {
        background: #d4edda;
        color: #155724;
    }
    .badge-inactive {
        background: #f8d7da;
        color: #721c24;
    }
    .badge-role {
        font-size: 0.75rem;
        padding: 0.35rem 0.7rem;
        border-radius: 2rem;
        font-weight: 500;
    }
    .badge-admin {
        background: #cff4fc;
        color: #055160;
    }
    .badge-student {
        background: #e2e3e5;
        color: #383d41;
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
    .modal-content-modern {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    @media (max-width: 768px) {
        .welcome-title { font-size: 1.5rem; }
        .mission-text { font-size: 0.9rem; }
        .stat-number { font-size: 1.5rem; }
        .table-modern thead { display: none; }
        .table-modern tbody tr {
            display: block;
            margin-bottom: 1rem;
            padding: 1rem;
        }
        .table-modern td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eef2f6;
        }
        .table-modern td:before {
            content: attr(data-label);
            font-weight: 600;
            width: 40%;
            color: #2a5298;
        }
        .table-modern td:last-child {
            border-bottom: none;
        }
    }
</style>

<!-- Bootstrap Icons + FontAwesome -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<!-- Hero Section -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title">
                    <i class="bi bi-people"></i> User Management
                </h1>
                <p class="mission-text mb-0">
                    <i class="bi bi-building"></i> Missions of Hope Namarei Computer Lab — Manage student and admin accounts.
                </p>
                <p class="mt-2 small opacity-75">
                    Add, edit, or remove users. Bulk import via CSV.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button class="btn btn-light btn-modern rounded-pill px-4" data-bs-toggle="collapse" data-bs-target="#addUserForm">
                    <i class="bi bi-person-plus"></i> Add New User
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Statistics Row -->
    <div class="row g-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-users fa-2x text-primary"></i>
                <div class="stat-number"><?= $total_users ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-user-graduate fa-2x text-success"></i>
                <div class="stat-number"><?= $total_students ?></div>
                <div class="stat-label">Students</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-user-tie fa-2x text-warning"></i>
                <div class="stat-number"><?= $total_admins ?></div>
                <div class="stat-label">Admins</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <i class="fas fa-check-circle fa-2x text-info"></i>
                <div class="stat-number"><?= $active_users ?></div>
                <div class="stat-label">Active</div>
            </div>
        </div>
    </div>

    <!-- Add User Collapsible Card -->
    <div class="modern-card collapse" id="addUserForm">
        <div class="card-header-modern">
            <i class="bi bi-person-plus"></i> Add New User
        </div>
        <div class="card-body p-4">
            <form method="post">
                <input type="hidden" name="action" value="add">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control form-control-modern" placeholder="Username" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control form-control-modern" placeholder="Email" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control form-control-modern" placeholder="Password" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="full_name" class="form-control form-control-modern" placeholder="Full Name" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Role</label>
                        <select name="role" class="form-select form-control-modern">
                            <option value="student">Student</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Security Question</label>
                        <input type="text" name="security_question" class="form-control form-control-modern" placeholder="e.g., What is your pet's name?" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Security Answer</label>
                        <input type="text" name="security_answer" class="form-control form-control-modern" placeholder="Answer" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary-modern btn-modern">
                            <i class="bi bi-save"></i> Add User
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Upload Card -->
    <div class="modern-card">
        <div class="card-header-modern">
            <i class="bi bi-upload"></i> Bulk Upload Users (CSV)
        </div>
        <div class="card-body p-4">
            <form method="post" enctype="multipart/form-data">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">CSV File</label>
                        <input type="file" name="csv_file" class="form-control form-control-modern" accept=".csv" required>
                        <small class="text-muted">Headers: username, email, full_name, role, password, security_question, security_answer</small>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" name="bulk_upload" class="btn btn-primary-modern btn-modern w-100">
                            <i class="bi bi-cloud-upload"></i> Upload
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="sample_users.csv" class="btn btn-outline-secondary btn-modern w-100">
                            <i class="bi bi-download"></i> Download Sample CSV
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="modern-card">
        <div class="card-header-modern">
            <i class="bi bi-table"></i> All Users
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        汽
                            <td data-label="ID">#<?= $user['id'] ?></td>
                            <td data-label="Username"><?= htmlspecialchars($user['username']) ?></td>
                            <td data-label="Full Name"><?= htmlspecialchars($user['full_name']) ?></td>
                            <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
                            <td data-label="Role">
                                <span class="badge-role badge-<?= $user['role'] ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td data-label="Status">
                                <span class="badge-status badge-<?= $user['status'] ?>">
                                    <?= ucfirst($user['status']) ?>
                                </span>
                            </td>
                            <td data-label="Actions">
                                <button class="btn btn-sm btn-outline-warning rounded-pill" data-bs-toggle="modal" data-bs-target="#editModal<?= $user['id'] ?>">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <?php if ($user['role'] === 'student'): ?>
                                    <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="deleteUser(<?= $user['id'] ?>)">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?= $user['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content modal-content-modern">
                                    <form method="post">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                        <div class="modal-header border-0">
                                            <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit User</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Full Name</label>
                                                <input type="text" name="full_name" class="form-control form-control-modern" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Email</label>
                                                <input type="email" name="email" class="form-control form-control-modern" value="<?= htmlspecialchars($user['email']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Role</label>
                                                <select name="role" class="form-select form-control-modern">
                                                    <option value="student" <?= $user['role'] == 'student' ? 'selected' : '' ?>>Student</option>
                                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Status</label>
                                                <select name="status" class="form-select form-control-modern">
                                                    <option value="active" <?= $user['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                                    <option value="inactive" <?= $user['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary-modern btn-modern">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function deleteUser(id) {
    if (confirm("Are you sure you want to delete this student? This action cannot be undone.")) {
        var form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>