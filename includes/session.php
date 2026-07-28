<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/Auth.php';
$auth = new Auth();

// Auto logout check
if ($auth->isLoggedIn()) {
    if (!$auth->checkSessionTimeout()) {
        $auth->logout();
    }
}
?>