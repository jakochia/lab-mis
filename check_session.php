<?php
session_start();
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    echo 'expired';
} else {
    echo 'ok';
}
?>