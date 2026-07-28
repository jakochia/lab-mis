<?php include 'includes/admin_header.php'; ?>

<?php
$feedbacks = $conn->query("SELECT f.*, u.full_name FROM feedback f JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Student Feedback</h2>

<div class="modern-card">
    <div class="card-header-modern">
        <i class="bi bi-chat-dots"></i> All Feedback
    </div>
    <div class="card-body p-0">
        <?php if (count($feedbacks) > 0): ?>
            <div class="list-group list-group-flush">
                <?php foreach ($feedbacks as $f): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong><?= htmlspecialchars($f['full_name']) ?></strong>
                            <small class="text-muted"><?= date('M d, Y g:i A', strtotime($f['created_at'])) ?></small>
                        </div>
                        <div class="my-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= $f['rating'] ? '★' : '☆' ?>
                            <?php endfor; ?>
                        </div>
                        <p><?= nl2br(htmlspecialchars($f['comment'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state text-center py-4">
                <i class="bi bi-chat-dots fs-1 text-muted"></i>
                <p class="mt-2 mb-0">No feedback received yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>