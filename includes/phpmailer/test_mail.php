<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

// Enable verbose debug output (set to 0 in production)
$mail->SMTPDebug = 2; // 0 = off, 1 = client, 2 = client and server
$mail->Debugoutput = 'html';

try {
    // SMTP config
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'gamingxac@gmail.com';        // your Gmail
    $mail->Password   = 'aejgnazeeiebilxn';          // Gmail app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Email
    $mail->setFrom('gamingxac@gmail.com', 'Local Test');
    $mail->addAddress('xavieraceclark.azcona@edu.ph'); // where you receive

    $mail->isHTML(true);
    $mail->Subject = 'PHPMailer Localhost Test';
    $mail->Body    = 'It works ✅ — PHPMailer via XAMPP';

    $mail->send();
    echo "Message sent successfully";
} catch (Exception $e) {
    // Show PHPMailer error info and exception message for diagnosis
    echo "Mailer Error: {$mail->ErrorInfo} - Exception: {$e->getMessage()}";
}