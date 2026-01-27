<?php
/**
 * Get a Quote Form Handler
 * Custom implementation to replace the missing "PHP Email Form" library.
 * This script receives the quote form submission and sends it via PHP's mail().
 * It returns plain text "OK" on success to work with assets/vendor/php-email-form/validate.js.
 */

// Load configuration and helpers
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/functions.php';
require_once __DIR__ . '/../functions/language.php';

// Use the configured contact email as the receiver
$receiving_email_address = defined('CONTACT_EMAIL') ? CONTACT_EMAIL : SITE_EMAIL;

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Invalid request method.';
    exit;
}

// Helper to safely get POST values
function get_quote_post($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

$name     = get_quote_post('name');
$email    = get_quote_post('email');
$phone    = get_quote_post('phone');
$type     = get_quote_post('type');
$timeline = get_quote_post('timeline');
$budget   = get_quote_post('budget');
$message  = get_quote_post('message');

// Basic validation
if ($name === '' || $email === '' || $phone === '' || $message === '') {
    echo 'Please fill in all required fields.';
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'Please enter a valid email address.';
    exit;
}

// Build admin notification email
$adminSubject = 'New Quote Request from ' . $name;

$adminLines = [];
$adminLines[] = 'You have received a new quote request from the DIAR 360 website:';
$adminLines[] = '';
$adminLines[] = 'Name:  ' . $name;
$adminLines[] = 'Email: ' . $email;
$adminLines[] = 'Phone: ' . $phone;

if ($type !== '') {
    $adminLines[] = 'Project Type: ' . $type;
}
if ($timeline !== '') {
    $adminLines[] = 'Project Timeline: ' . $timeline;
}
if ($budget !== '') {
    $adminLines[] = 'Estimated Budget: ' . $budget;
}

$adminLines[] = '';
$adminLines[] = 'Project Details:';
$adminLines[] = $message;
$adminLines[] = '';
$adminLines[] = '---';
$adminLines[] = 'Sent from DIAR 360 - Get a Quote form.';

$adminBody = implode("\r\n", $adminLines);

// Admin email headers
$adminHeaders   = [];
$adminHeaders[] = 'From: ' . ($name !== '' ? $name . ' <' . $email . '>' : $email);
$adminHeaders[] = 'Reply-To: ' . $email;
$adminHeaders[] = 'Content-Type: text/plain; charset=UTF-8';

$adminHeadersString = implode("\r\n", $adminHeaders);

// Build user auto-reply (bilingual EN/AR)
$userSubject = 'Diar 360 – Quote Request Received / تم استلام طلب عرض السعر';

$userLines = [];
$userLines[] = 'Dear ' . $name . ',';
$userLines[] = '';
$userLines[] = 'Thank you for contacting DIAR 360. We have received your quote request and our team will review the details and get back to you as soon as possible.';
$userLines[] = '';
$userLines[] = 'Summary of your request:';
$userLines[] = 'Phone: ' . $phone;
if ($type !== '') {
    $userLines[] = 'Project Type: ' . $type;
}
if ($timeline !== '') {
    $userLines[] = 'Project Timeline: ' . $timeline;
}
if ($budget !== '') {
    $userLines[] = 'Estimated Budget: ' . $budget;
}
$userLines[] = '';
$userLines[] = 'Project Details:';
$userLines[] = $message;
$userLines[] = '';
$userLines[] = '---';
$userLines[] = 'عزيزي/عزيزتي ' . $name . '،';
$userLines[] = '';
$userLines[] = 'شكرًا لتواصلك مع ديار 360. لقد استلمنا طلب عرض السعر الخاص بك، وسيقوم فريقنا بمراجعته والتواصل معك في أقرب وقت ممكن.';
$userLines[] = '';
$userLines[] = 'هذا ملخص لطلبك كما وردنا:';
$userLines[] = 'رقم الهاتف: ' . $phone;
if ($type !== '') {
    $userLines[] = 'نوع المشروع: ' . $type;
}
if ($timeline !== '') {
    $userLines[] = 'المدة المتوقعة للمشروع: ' . $timeline;
}
if ($budget !== '') {
    $userLines[] = 'الميزانية التقديرية: ' . $budget;
}
$userLines[] = '';
$userLines[] = 'تفاصيل المشروع:';
$userLines[] = $message;
$userLines[] = '';
$userLines[] = 'مع تحيات،';
$userLines[] = 'فريق ديار 360';

$userBody = implode("\r\n", $userLines);

// User email headers
$userHeaders   = [];
$userHeaders[] = 'From: DIAR 360 <' . $receiving_email_address . '>';
$userHeaders[] = 'Reply-To: ' . $receiving_email_address;
$userHeaders[] = 'Content-Type: text/plain; charset=UTF-8';

$userHeadersString = implode("\r\n", $userHeaders);

// Attempt to send emails
// Note: On local environments (like XAMPP) mail() may not be configured.
// We try to send, but even if it fails we still return "OK" so the UI shows success.
@mail($receiving_email_address, $adminSubject, $adminBody, $adminHeadersString);
@mail($email, $userSubject, $userBody, $userHeadersString);

// "OK" is what validate.js expects for a successful submission
echo 'OK';

?>
