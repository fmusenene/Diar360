<?php
/**
 * Contact Form Handler
 * Custom implementation (no external vendor dependency).
 *
 * Returns plain text "OK" on success to work with assets/vendor/php-email-form/validate.js.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/functions.php';
require_once __DIR__ . '/../functions/language.php';

$receiving_email_address = defined('CONTACT_EMAIL') ? CONTACT_EMAIL : SITE_EMAIL;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo 'Invalid request method.';
  exit;
}

if (function_exists('diar_is_same_origin_request') && !diar_is_same_origin_request()) {
  http_response_code(403);
  echo 'Invalid request origin.';
  exit;
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Basic anti-spam throttling: one submit every 20 seconds per session.
$now = time();
$lastSubmit = (int)($_SESSION['last_contact_submit'] ?? 0);
if ($lastSubmit && ($now - $lastSubmit) < 20) {
  echo 'Please wait a moment before sending another message.';
  exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Defend against header injection in mail headers.
$stripHeaderBreaks = static function (string $value): string {
  return trim(str_replace(["\r", "\n"], ' ', $value));
};

if ($name === '' || $email === '' || $subject === '' || $message === '') {
  echo 'Please fill in all required fields.';
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo 'Please enter a valid email address.';
  exit;
}

$name = $stripHeaderBreaks($name);
$email = $stripHeaderBreaks($email);
$subject = $stripHeaderBreaks($subject);

// Admin notification email
$adminSubject = 'New Contact Message: ' . $subject;
$adminBody = implode("\r\n", [
  'You received a new message from the DIAR 360 contact form:',
  '',
  'Name: ' . $name,
  'Email: ' . $email,
  'Subject: ' . $subject,
  '',
  'Message:',
  $message,
  '',
  '---',
  'Sent from DIAR 360 - Contact form.',
]);

$adminHeaders = [];
$adminHeaders[] = 'From: ' . $name . ' <' . $email . '>';
$adminHeaders[] = 'Reply-To: ' . $email;
$adminHeaders[] = 'Content-Type: text/plain; charset=UTF-8';
$adminHeadersString = implode("\r\n", $adminHeaders);

// Sender auto-reply (bilingual EN/AR)
$userSubject = 'Diar 360 – Message Received / تم استلام رسالتك';
$userBody = implode("\r\n", [
  'Dear ' . $name . ',',
  '',
  'Thank you for contacting DIAR 360. We have received your message and our team will get back to you as soon as possible.',
  '',
  'Your message subject: ' . $subject,
  '',
  '---',
  'عزيزي/عزيزتي ' . $name . '،',
  '',
  'شكرًا لتواصلك مع ديار 360. لقد استلمنا رسالتك وسيقوم فريقنا بالتواصل معك في أقرب وقت ممكن.',
  '',
  'موضوع رسالتك: ' . $subject,
  '',
  'مع تحيات،',
  'فريق ديار 360',
]);

$userHeaders = [];
$userHeaders[] = 'From: DIAR 360 <' . $receiving_email_address . '>';
$userHeaders[] = 'Reply-To: ' . $receiving_email_address;
$userHeaders[] = 'Content-Type: text/plain; charset=UTF-8';
$userHeadersString = implode("\r\n", $userHeaders);

// Attempt to send emails.
// Note: On local environments (like XAMPP) mail() may require SMTP/sendmail configuration.
@mail($receiving_email_address, $adminSubject, $adminBody, $adminHeadersString);
@mail($email, $userSubject, $userBody, $userHeadersString);

$_SESSION['last_contact_submit'] = $now;

echo 'OK';
?>
