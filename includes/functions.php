<?php
function exportToCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    fputcsv($output, array_keys($data[0]));
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

function sendEmail($to, $subject, $message) {
    // Use PHP mail() or PHPMailer
    mail($to, $subject, $message, "From: noreply@labmis.com");
}
function isMaintenanceMode($conn) {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'");
    $stmt->execute();
    return $stmt->fetchColumn() === 'on';
}
?>
