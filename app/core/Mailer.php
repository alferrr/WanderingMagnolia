<?php
// app/core/Mailer.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once ROOT . '/config/mail.php';
require_once ROOT . '/vendor/autoload.php';

class Mailer {

    public static function sendOtp(string $toEmail, string $toName, string $otp): bool {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USER;
            $mail->Password   = MAIL_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            $mail->Timeout = 10;

            $mail->setFrom(MAIL_USER, MAIL_FROM_NAME);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = 'Your Wandering Magnolias OTP Code';
            $mail->Body    = self::buildOtpEmail($toName, $otp);
            $mail->AltBody = "Hi {$toName}, your OTP code is: {$otp}. It expires in 15 minutes.";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log('Mailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    private static function buildOtpEmail(string $name, string $otp): string {
        $year        = date('Y');
        $safeName    = htmlspecialchars($name);
        $safeOtp     = htmlspecialchars($otp);
        $fontUrl     = "https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700;800&display=swap";
        $fontStack   = "'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    @import url("{$fontUrl}");
    body        { font-family: {$fontStack}; background: #faf8f6; margin: 0; padding: 0; }
    .wrap       { max-width: 480px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.08); font-family: {$fontStack}; }
    .header     { background: #0f0f0f; padding: 32px; text-align: center; }
    .logo       { font-family: {$fontStack}; font-size: 1.4rem; font-weight: 800; color: #fff; letter-spacing: -.5px; }
    .logo span  { color: #e8547a; }
    .body       { padding: 40px 32px; }
    .greeting   { font-size: 1rem; color: #3d3d3d; margin-bottom: 16px; font-family: {$fontStack}; }
    .otp-box    { background: #fde8ee; border-radius: 12px; padding: 24px; text-align: center; margin: 28px 0; }
    .otp-label  { font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .8px; color: #c03560; margin-bottom: 10px; font-family: {$fontStack}; }
    .otp-code   { font-size: 2.8rem; font-weight: 800; letter-spacing: 12px; color: #0f0f0f; font-family: monospace; }
    .note       { font-size: .85rem; color: #7a7a7a; line-height: 1.7; margin: 0; font-family: {$fontStack}; }
    .footer     { padding: 20px 32px; border-top: 1px solid #f0eded; font-size: .78rem; color: #c4c4c4; text-align: center; font-family: {$fontStack}; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <div class="logo">Wandering <span>Magnolias</span></div>
    </div>
    <div class="body">
      <p class="greeting">Hi {$safeName},</p>
      <p class="note">We received a request to reset your password. Use the code below to continue. It expires in <strong>15 minutes</strong>.</p>
      <div class="otp-box">
        <div class="otp-label">Your OTP Code</div>
        <div class="otp-code">{$safeOtp}</div>
      </div>
      <p class="note">If you did not request a password reset, you can safely ignore this email. Your password will not change.</p>
    </div>
    <div class="footer">&copy; {$year} Wandering Magnolias. All rights reserved.</div>
  </div>
</body>
</html>
HTML;
    }
}