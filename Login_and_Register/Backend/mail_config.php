<?php
// Include PHPMailer classes
require_once '../../../phpmailer/src/Exception.php';
require_once '../../../phpmailer/src/PHPMailer.php';
require_once '../../../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function configureMailer() {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'learninglinuxmint@gmail.com'; // Replace with your SMTP username
        $mail->Password = 'pknh zplk fsbz phim'; // Replace with your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use encryption
        $mail->Port = 465; // Usually 465 for SSL

        // Sender details
        $mail->setFrom('learninglinuxmint@gmail.com', 'KBCSS Support');
    } catch (Exception $e) {
        die("Mailer configuration error: {$e->getMessage()}");
    }

    return $mail;
}
