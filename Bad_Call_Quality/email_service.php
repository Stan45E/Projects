<?php
// email_service.php

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Make sure the Composer autoloader and our config file are included
// These paths assume all files are in the same root directory.
require_once 'vendor/autoload.php';
require_once 'config.php';

function send_alert_email($subject, $body) {
    // Check if the recipient email address is defined and not empty
    if (!defined('SHARED_EMAIL_ADDRESS') || empty(SHARED_EMAIL_ADDRESS)) {
        // You can log this error or just return, to prevent crashes
        error_log("Email not sent: SHARED_EMAIL_ADDRESS is not defined in config.php");
        return false;
    }
    
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : PHPMailer::ENCRYPTION_STARTTLS; // More robust
        $mail->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587; // More robust

        //Recipients
        $mail->setFrom(SMTP_USERNAME, 'Call Quality Portal');
        $mail->addAddress(SHARED_EMAIL_ADDRESS); // Sends to the shared inbox

        //Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // A plain-text version for non-HTML email clients

        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        // Log the detailed error for debugging purposes instead of showing it to the user
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false; // Email failed to send
    }
}