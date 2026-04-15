<?php
/**
 * Public Testimonial Submission Handler
 *
 * Returns plain text "OK" for assets/vendor/php-email-form/validate.js.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/functions.php';
require_once __DIR__ . '/../functions/language.php';
require_once __DIR__ . '/../config/testimonials-data.php';

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

// Simple anti-spam: honeypot + basic rate limit
$hp = trim((string)($_POST['website'] ?? ''));
if ($hp !== '') {
  echo 'OK';
  exit;
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$now = time();
$last = (int)($_SESSION['last_testimonial_submit'] ?? 0);
if ($last && ($now - $last) < 30) {
  echo 'Please wait a moment and try again.';
  exit;
}

$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$role = trim((string)($_POST['role'] ?? ''));
$company = trim((string)($_POST['company'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));
$google_name = trim((string)($_POST['google_name'] ?? ''));
$google_email = trim((string)($_POST['google_email'] ?? ''));
$google_picture = trim((string)($_POST['google_picture'] ?? ''));
$rating = (int)($_POST['rating'] ?? 5);
$rating = max(1, min(5, $rating));

if (defined('TESTIMONIAL_REQUIRE_GOOGLE_SIGNIN') && TESTIMONIAL_REQUIRE_GOOGLE_SIGNIN) {
  if ($google_email === '' || !filter_var($google_email, FILTER_VALIDATE_EMAIL)) {
    echo 'Please continue with Google to submit your review.';
    exit;
  }
}

if ($google_email !== '' && filter_var($google_email, FILTER_VALIDATE_EMAIL)) {
  $email = $google_email;
}

if ($google_name !== '') {
  $name = $google_name;
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo 'Please enter a valid email address.';
  exit;
}

if ($name === '' || $message === '') {
  echo 'Please fill in all required fields.';
  exit;
}

if (strlen($name) > 120 || strlen($message) > 5000 || strlen($role) > 120 || strlen($company) > 160) {
  echo 'Input is too long.';
  exit;
}

// Store message in the current language field; leave the other empty for later admin translation.
$lang = getCurrentLanguage();
$message_en = ($lang === 'ar') ? '' : $message;
$message_ar = ($lang === 'ar') ? $message : '';

$role_en = ($lang === 'ar') ? '' : $role;
$role_ar = ($lang === 'ar') ? $role : '';

$company_en = ($lang === 'ar') ? '' : $company;
$company_ar = ($lang === 'ar') ? $company : '';

$id = 't-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(6)), 0, 12);
$dt = date('Y-m-d H:i:s');

if (!isset($testimonials) || !is_array($testimonials)) {
  $testimonials = [];
}

$testimonials[$id] = [
  'status' => 'pending',
  'visible' => '0',
  'rating' => $rating,
  'message_en' => $message_en,
  'message_ar' => $message_ar,
  'name' => $name,
  'email' => $email,
  'avatar_url' => $google_picture,
  'role_en' => $role_en,
  'role_ar' => $role_ar,
  'company_en' => $company_en,
  'company_ar' => $company_ar,
  'avatar' => '',
  'created_at' => $dt,
  'updated_at' => $dt,
];

$file = __DIR__ . '/../config/testimonials-data.php';
$fp = @fopen($file, 'c+');
if ($fp) {
  @flock($fp, LOCK_EX);
  // Reload latest content to avoid overwriting concurrent changes
  $fresh = [];
  rewind($fp);
  $raw = stream_get_contents($fp);
  if (is_string($raw) && $raw !== '') {
    if (preg_match('/\\$testimonials\\s*=\\s*(.+?);\\s*\\?>/s', $raw, $m)) {
      $expr = trim($m[1]);
      $loaded = @eval('return ' . $expr . ';');
      if (is_array($loaded)) {
        $fresh = $loaded;
      }
    }
  }
  $fresh[$id] = $testimonials[$id];

  $data = "<?php\n/**\n * Testimonials Data\n *\n * - Public submissions append here as \"pending\"\n * - Admin moderates (approve/visible/edit/delete)\n */\n\n";
  $data .= "\$testimonials = " . var_export($fresh, true) . ";\n\n?>\n";
  ftruncate($fp, 0);
  rewind($fp);
  fwrite($fp, $data);
  fflush($fp);
  @flock($fp, LOCK_UN);
  fclose($fp);
}

$_SESSION['last_testimonial_submit'] = $now;

echo 'OK';

?>

