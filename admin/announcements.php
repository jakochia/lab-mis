<?php include 'includes/admin_header.php'; ?>

<?php
// Add announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_announcement'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
    $stmt = $conn->prepare("INSERT INTO announcements (title, content, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$title, $content, $expires_at]);
    $msg = "Announcement added.";
}

// Delete announcement
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    $msg = "Announcement deleted.";
}

$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Announcements</h2>

<?php if (isset($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-5">
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-plus-circle"></i> New Announcement
            </div>
            <div class="card-body p-4">
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" name="title" class="form-control form-control-modern" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Content</label>
                        <textarea name="content" class="form-control form-control-modern" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Expires (optional)</label>
                        <input type="datetime-local" name="expires_at" class="form-control form-control-modern">
                        <small class="text-muted">Leave blank for no expiry.</small>
                    </div>
                    <button type="submit" name="add_announcement" class="btn btn-primary-modern btn-modern">
                        <i class="bi bi-send"></i> Post Announcement
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="modern-card">
            <div class="card-header-modern">
                <i class="bi bi-megaphone"></i> All Announcements
            </div>
            <div class="card-body p-0">
                <?php if (count($announcements) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($announcements as $a): ?>
                            <div class="list-group-item list-group-item-modern">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1"><?= htmlspecialchars($a['title']) ?></h5>
                                        <p class="mb-1"><?= nl2br(htmlspecialchars($a['content'])) ?></p>
                                        <small class="text-muted">Posted: <?= date('M d, Y g:i A', strtotime($a['created_at'])) ?></small>
                                        <?php if ($a['expires_at']): ?>
                                            <br><small class="text-muted">Expires: <?= date('M d, Y g:i A', strtotime($a['expires_at'])) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <a href="?delete=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Delete this announcement?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state text-center py-4">
                        <i class="bi bi-megaphone fs-1 text-muted"></i>
                        <p class="mt-2 mb-0">No announcements yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>