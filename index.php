<?php
require_once 'includes/session.php';
if ($auth->isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') header('Location: admin/dashboard.php');
    else header('Location: student/dashboard.php');
    exit;
}
header('Location: login.php');
?>