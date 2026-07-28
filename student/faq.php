<?php include 'includes/student_header.php'; ?>

<style>
    .faq-item {
        margin-bottom: 1.5rem;
    }
    .faq-question {
        font-weight: 600;
        font-size: 1.1rem;
        color: #2a5298;
        margin-bottom: 0.5rem;
    }
    .faq-answer {
        color: #4a5568;
        line-height: 1.6;
    }
</style>

<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title">
                    <i class="bi bi-question-circle"></i> Frequently Asked Questions
                </h1>
                <p class="mission-text mb-0">
                    <i class="bi bi-building"></i> Missions of Hope Namarei Computer Lab
                </p>
                <p class="mt-2 small opacity-75">
                    Find answers to common questions about using the lab.
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
    <div class="modern-card">
        <div class="card-header-modern">
            <i class="bi bi-chat-dots"></i> General Questions
        </div>
        <div class="card-body p-4">
            <div class="faq-item">
                <div class="faq-question">❓ How do I start a session?</div>
                <div class="faq-answer">Go to the <a href="computer_status.php">Computer Status</a> page, find an available computer, and click "Use". You'll be logged in automatically.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">❓ How do I book a computer for later?</div>
                <div class="faq-answer">Use the <a href="book_computer.php">Booking</a> page. Select a computer, date, and time slot. Confirm the booking and you'll receive a confirmation.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">❓ What should I do if a computer isn't working?</div>
                <div class="faq-answer">Report it immediately using the "Report Fault" button on the computer status page. Our technicians will check it soon.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">❓ Can I see my session history?</div>
                <div class="faq-answer">Yes, visit the <a href="session_history.php">Session History</a> page. You'll see all your past sessions, including duration.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">❓ How do I change my password?</div>
                <div class="faq-answer">Go to your <a href="profile.php">Profile</a> page. You can update your password there.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">❓ I need help. How do I contact support?</div>
                <div class="faq-answer">Submit a <a href="support.php">Support Ticket</a>. Our team will respond as soon as possible.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">❓ Are there any lab rules?</div>
                <div class="faq-answer">Yes, please be respectful of equipment and other users. No food or drinks near computers. Always log out when finished.</div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>