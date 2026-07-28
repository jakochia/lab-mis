$(document).ready(function() {
    // Dark mode toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    const darkModeStyle = document.getElementById('dark-mode-style');
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
        darkModeStyle.disabled = false;
    }
    darkModeToggle.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        if (document.body.classList.contains('dark-mode')) {
            darkModeStyle.disabled = false;
            localStorage.setItem('darkMode', 'enabled');
        } else {
            darkModeStyle.disabled = true;
            localStorage.setItem('darkMode', 'disabled');
        }
    });

    // Auto logout check (every minute)
    setInterval(() => {
        fetch('../check_session.php')
            .then(res => res.text())
            .then(data => {
                if (data === 'expired') {
                    window.location.href = '../login.php';
                }
            });
    }, 60000);
});

function showToast(message, type = 'success') {
    let bgColor;
    switch(type) {
        case 'success': bgColor = '#28a745'; break;
        case 'error': bgColor = '#dc3545'; break;
        case 'warning': bgColor = '#ffc107'; break;
        default: bgColor = '#17a2b8';
    }
    Toastify({
        text: message,
        duration: 5000,
        close: true,
        gravity: "top", // `top` or `bottom`
        position: "right", // `left`, `center` or `right`
        backgroundColor: bgColor,
    }).showToast();
}