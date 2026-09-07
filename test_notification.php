<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<h2>HRM Notification Test</h2>";

// Check PHPMailer
echo "<h3>1. PHPMailer Check</h3>";
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "<p style='color:green'>PHPMailer loaded</p>";
} else {
    // Try CI vendor
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
    }
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "<p style='color:green'>PHPMailer loaded from vendor</p>";
    } else {
        echo "<p style='color:red'>PHPMailer NOT found</p>";
        exit;
    }
}

// Test SMTP
echo "<h3>2. SMTP Send Test</h3>";
$mail = new PHPMailer\PHPMailer\PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'mail.techriseglow.co.ke';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'kkariuki@techriseglow.co.ke';
    $mail->Password   = 'Stalis@2026';
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom('kkariuki@techriseglow.co.ke', 'Stalis HRM');
    $mail->addAddress('softwareadmin@stalis.co.ke');
    $mail->Subject = 'HRM Test - ' . date('Y-m-d H:i:s');
    $mail->Body    = '<h3>Test Notification</h3><p>This is a test from Stalis HRM system.</p>';
    $mail->isHTML(true);
    $mail->send();
    echo "<p style='color:green'>Email sent successfully!</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>SMTP Error: " . htmlspecialchars($mail->ErrorInfo) . "</p>";
}
echo "<p><a href='https://hrm-2026.xo.je/admin/dashboard?module=dashboard'>Back to Dashboard</a></p>";
