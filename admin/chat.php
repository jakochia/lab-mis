<?php include 'includes/admin_header.php'; ?>
<!-- rest identical to student/chat.php -->

<h2>Group Chat</h2>

<div id="chat-container" style="border:1px solid #ccc; height:400px; overflow-y:auto; padding:10px; margin-bottom:10px; background:#f9f9f9;">
    <!-- messages will be loaded here -->
</div>

<div class="input-group">
    <input type="text" id="message-input" class="form-control" placeholder="Type your message...">
    <button class="btn btn-primary" id="send-btn">Send</button>
</div>

<script>
    let lastMessageId = 0;
    let pollingInterval = null;

    function fetchMessages() {
        fetch('../get_messages.php?last_id=' + lastMessageId)
            .then(response => response.json())
            .then(messages => {
                if (messages.length > 0) {
                    const container = document.getElementById('chat-container');
                    messages.forEach(msg => {
                        const msgDiv = document.createElement('div');
                        msgDiv.className = 'chat-message';
                        msgDiv.innerHTML = `<strong>${escapeHtml(msg.username)}</strong> <small>${msg.created_at}</small><br>${escapeHtml(msg.message)}`;
                        container.appendChild(msgDiv);
                        lastMessageId = msg.id;
                    });
                    container.scrollTop = container.scrollHeight;
                }
            });
    }

    function sendMessage() {
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        if (message === '') return;

        fetch('../send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(message)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                fetchMessages(); // Immediately fetch to see own message
            } else {
                alert(data.error);
            }
        });
    }

    function escapeHtml(str) {
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    document.getElementById('send-btn').addEventListener('click', sendMessage);
    document.getElementById('message-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });

    // Start polling
    fetchMessages();
    pollingInterval = setInterval(fetchMessages, 2000);
</script>

<style>
.chat-message {
    margin-bottom: 10px;
    padding: 5px;
    border-bottom: 1px solid #eee;
}
.chat-message strong {
    color: #007bff;
}
</style>

<?php include '../includes/footer.php'; ?>