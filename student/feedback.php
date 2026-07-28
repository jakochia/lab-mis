<?php include 'includes/student_header.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    $stmt = $conn->prepare("INSERT INTO feedback (user_id, rating, comment) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $rating, $comment]);
    $success = "Thank you for your feedback!";
}
?>

<h2>Lab Feedback</h2>
<p class="text-muted">Help us improve the lab experience. Your feedback is anonymous to other students but helps us make better decisions.</p>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="modern-card">
    <div class="card-header-modern">
        <i class="bi bi-chat-right-text"></i> Share Your Experience
    </div>
    <div class="card-body p-4">
        <form method="post">
            <div class="mb-4">
                <label class="form-label fw-semibold">Rating</label>
                <div class="rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" required>
                        <label for="star<?= $i ?>" class="star">★</label>
                    <?php endfor; ?>
                </div>
                <style>
                    .rating-stars {
                        display: flex;
                        flex-direction: row-reverse;
                        justify-content: flex-end;
                    }
                    .rating-stars input {
                        display: none;
                    }
                    .rating-stars label {
                        font-size: 2rem;
                        color: #ddd;
                        cursor: pointer;
                        transition: color 0.2s;
                    }
                    .rating-stars label:hover,
                    .rating-stars label:hover ~ label,
                    .rating-stars input:checked ~ label {
                        color: #ffc107;
                    }
                </style>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Your Comment (optional)</label>
                <textarea name="comment" class="form-control form-control-modern" rows="4" placeholder="What did you like? What could be improved?"></textarea>
            </div>
            <button type="submit" name="submit_feedback" class="btn btn-primary-modern btn-modern">
                <i class="bi bi-send"></i> Submit Feedback
            </button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>