<?php
/**
 * Notification helper for Buea BloodLink
 * This includes email and SMS notification logic.
 * Real SMS sending requires a provider (Twilio/Nexmo/SMS API). Use the placeholder section.
 */

function sendEmailNotification($to, $subject, $messageBody) {
    $headers = "From: no-reply@bueabloodlink.local\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    // Using PHP mail() for sample. Replace with SMTP library in production.
    return mail($to, $subject, $messageBody, $headers);
}

function sendSmsNotification($phone, $messageText) {
    // Placeholder SMS logic. Integrate Twilio or SMS gateway here.
    // Example local log for testing:
    $logLine = date('Y-m-d H:i:s') . " | SMS to $phone | $messageText\n";
    file_put_contents(__DIR__ . '/sms.log', $logLine, FILE_APPEND);
    return true;
}

function notifyRequest($userEmail, $userPhone, $subject, $message) {
    $sentEmail = sendEmailNotification($userEmail, $subject, $message);
    $sentSms = false;
    if ($userPhone) {
        $sentSms = sendSmsNotification($userPhone, strip_tags($message));
    }
    return $sentEmail || $sentSms;
}
