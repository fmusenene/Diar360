<?php
/**
 * Modern Admin Dashboard - Project Oasis Design
 * Matches the React/TypeScript design exactly but with PHP backend
 */

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com; " .
    "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
    "font-src 'self' https://cdnjs.cloudflare.com data:; " .
    "img-src 'self' data: blob:; " .
    "connect-src 'self'; " .
    "object-src 'none'; " .
    "base-uri 'self'; " .
    "frame-ancestors 'none'; " .
    "form-action 'self'"
);

// HTTPS enforcement
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    if ($_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }
}

$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $is_https ? '1' : '0');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $is_https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
require_once __DIR__ . '/../config/projects-data.php';
require_once __DIR__ . '/../config/project-status.php';
require_once __DIR__ . '/../config/team-data.php';
require_once __DIR__ . '/../config/careers-data.php';
require_once __DIR__ . '/../config/testimonials-data.php';

// Security: Session timeout and activity tracking
$session_timeout = 10 * 60; // 20 minutes in seconds
$current_time = time(); 

// Preserve the last admin page we were on (so after re-login we can return there).
// Whitelist to avoid redirecting to unexpected pages.
$requested_page = $_GET['page'] ?? 'projects';
if (!is_string($requested_page)) {
    $requested_page = 'projects';
}
if ($requested_page === 'dashboard') {
    $requested_page = 'projects';
}
if (!in_array($requested_page, ['projects', 'team', 'settings', 'careers', 'certifications', 'testimonials'], true)) {
    $requested_page = 'projects';
}

// Initialize session variables if not exists
if (!isset($_SESSION['session_start'])) {
    $_SESSION['session_start'] = $current_time;
    $_SESSION['last_activity'] = $current_time;
}

// Check for session timeout
// IMPORTANT: allow the login POST to go through even if the session was already timed out.
// Otherwise the session gets destroyed before authentication is processed, causing the timeout modal to loop.
$is_login_attempt = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login']));
if (!$is_login_attempt && isset($_SESSION['last_activity']) && ($current_time - $_SESSION['last_activity']) > $session_timeout) {
    session_destroy();
    header(
        'Location: projects-new.php?timeout=1' .
        ($requested_page !== 'projects' ? '&page=' . urlencode($requested_page) : '')
    );
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = $current_time;

// Handle activity update from JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_activity'])) {
    $_SESSION['last_activity'] = time();
    echo json_encode(['success' => true]);
    exit;
}

// Handle force logout from JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['force_logout'])) {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// Generate unique session token for security
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load admin settings
$admin_password = 'diar360_admin_2024'; // Default fallback
$admin_password_hash = '';
$site_settings = [];

$settings_file = __DIR__ . '/../config/admin-settings.php';
if (file_exists($settings_file)) {
    include $settings_file;
}
if (isset($site_settings['admin_password_hash']) && is_string($site_settings['admin_password_hash'])) {
    $admin_password_hash = $site_settings['admin_password_hash'];
}

// Rate limiting and brute force protection
function checkRateLimit($ip) {
    $attempts_file = __DIR__ . '/../config/login_attempts.json';
    $attempts = [];
    
    if (file_exists($attempts_file)) {
        $attempts = json_decode(file_get_contents($attempts_file), true) ?: [];
    }
    
    $current_time = time();
    $ip_attempts = $attempts[$ip] ?? ['count' => 0, 'first_attempt' => $current_time];
    
    // Reset count after 30 minutes
    if ($current_time - $ip_attempts['first_attempt'] > 1800) {
        $ip_attempts = ['count' => 0, 'first_attempt' => $current_time];
    }
    
    // Block after 5 failed attempts
    if ($ip_attempts['count'] >= 5) {
        return false;
    }
    
    return true;
}

function recordFailedAttempt($ip) {
    $attempts_file = __DIR__ . '/../config/login_attempts.json';
    $attempts = [];
    
    if (file_exists($attempts_file)) {
        $attempts = json_decode(file_get_contents($attempts_file), true) ?: [];
    }
    
    $current_time = time();
    $ip_attempts = $attempts[$ip] ?? ['count' => 0, 'first_attempt' => $current_time];
    $ip_attempts['count']++;
    $ip_attempts['first_attempt'] = $ip_attempts['first_attempt'];
    
    $attempts[$ip] = $ip_attempts;
    file_put_contents($attempts_file, json_encode($attempts), LOCK_EX);
}

function clearFailedAttempts($ip) {
    $attempts_file = __DIR__ . '/../config/login_attempts.json';
    $attempts = [];
    
    if (file_exists($attempts_file)) {
        $attempts = json_decode(file_get_contents($attempts_file), true) ?: [];
    }
    
    unset($attempts[$ip]);
    file_put_contents($attempts_file, json_encode($attempts), LOCK_EX);
}

// Input validation and sanitization functions
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateInput($input, $type = 'string', $required = true) {
    if ($required && (empty($input) && $input !== '0')) {
        return false;
    }
    
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL);
        case 'url':
            return filter_var($input, FILTER_VALIDATE_URL);
        case 'int':
            return filter_var($input, FILTER_VALIDATE_INT);
        case 'float':
            return filter_var($input, FILTER_VALIDATE_FLOAT);
        case 'string':
        default:
            return is_string($input);
    }
}

function sanitizeFilename($filename) {
    // Remove dangerous characters
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    
    // Prevent directory traversal
    $filename = str_replace('..', '', $filename);
    
    // Limit length
    if (strlen($filename) > 255) {
        $filename = substr($filename, 0, 255);
    }
    
    return $filename;
}

function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf']) {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    
    // Check file size (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        return false;
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes)) {
        return false;
    }
    
    // Check MIME type
    $allowedMimes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf'
    ];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowedMimes)) {
        return false;
    }
    
    return true;
}

// Authentication with enhanced security
$is_authenticated = false;

// Pending testimonials count (used for admin notification badge)
$pending_testimonials_count = 0;
if (isset($testimonials) && is_array($testimonials)) {
    $pending_testimonials_count = count(array_filter($testimonials, function($t) {
        return ($t['status'] ?? 'pending') === 'pending';
    }));
}

// Get client IP for rate limiting
$client_ip = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? 'unknown';

if (isset($_POST['login'])) {
    // Check rate limit first
    if (!checkRateLimit($client_ip)) {
        header('Location: projects-new.php?error=rate_limit');
        exit;
    }
    
    $input_password = (string)($_POST['password'] ?? '');
    $login_ok = false;
    if ($admin_password_hash !== '') {
        $login_ok = password_verify($input_password, $admin_password_hash);
    } else {
        $login_ok = hash_equals((string)$admin_password, $input_password);
    }

    if ($login_ok) {
    // Validate CSRF token (constant-time compare + graceful recovery)
    $posted_token = $_POST['csrf_token'] ?? '';
    $session_token = $_SESSION['csrf_token'] ?? '';
    if ($posted_token === '' || $session_token === '' || !hash_equals($session_token, $posted_token)) {
        // Regenerate token and return to login with an error instead of hard-stopping.
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: projects-new.php?error=csrf');
        exit;
    }
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Clear failed attempts on successful login
    clearFailedAttempts($client_ip);
    
    $_SESSION['admin_authenticated'] = true;
    $_SESSION['login_time'] = $current_time;
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenerate token
    $is_authenticated = true;
    
    // Redirect to clear URL parameters after successful login
    header(
        'Location: projects-new.php' .
        ($requested_page !== 'projects' ? '?page=' . urlencode($requested_page) : '')
    );
    exit;
    } else {
        // Record failed attempt
        recordFailedAttempt($client_ip);
        error_log('Admin login failed for IP: ' . $client_ip);
        
        // Wrong password: redirect back with an error so user gets feedback.
        header('Location: projects-new.php?error=invalid_login');
        exit;
    }
} elseif (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true) {
    // Verify session integrity
    if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        session_destroy();
        header(
            'Location: projects-new.php?security=1' .
            ($requested_page !== 'projects' ? '&page=' . urlencode($requested_page) : '')
        );
        exit;
    }
    
    if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_destroy();
        header(
            'Location: projects-new.php?security=1' .
            ($requested_page !== 'projects' ? '&page=' . urlencode($requested_page) : '')
        );
        exit;
    }
    
    $is_authenticated = true;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: projects-new.php');
    exit;
}

// Handle AJAX requests for security
if (isset($_GET['heartbeat']) || isset($_GET['check_session'])) {
    header('Content-Type: application/json');
    
    if (isset($_GET['heartbeat'])) {
        // Update last activity time
        $_SESSION['last_activity'] = time();
        echo json_encode(['success' => true]);
    } elseif (isset($_GET['check_session'])) {
        // Check if session is still valid
        $is_valid = isset($_SESSION['admin_authenticated']) && 
                   $_SESSION['admin_authenticated'] === true &&
                   isset($_SESSION['ip_address']) && 
                   $_SESSION['ip_address'] === $_SERVER['REMOTE_ADDR'];
        
        echo json_encode(['valid' => $is_valid]);
    }
    exit;
}

if (
    $is_authenticated &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    !isset($_POST['update_activity']) &&
    !isset($_POST['force_logout'])
) {
    $same_origin_valid = true;
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host !== '') {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if ($origin !== '') {
            $origin_host = parse_url($origin, PHP_URL_HOST);
            $same_origin_valid = is_string($origin_host) && strcasecmp($origin_host, $host) === 0;
        } elseif ($referer !== '') {
            $referer_host = parse_url($referer, PHP_URL_HOST);
            $same_origin_valid = is_string($referer_host) && strcasecmp($referer_host, $host) === 0;
        }
    }

    if (!$same_origin_valid) {
        http_response_code(403);
        exit('Invalid request origin.');
    }
}

// Handle form submissions
if ($is_authenticated) {
    // Get current page
    $current_page = isset($_GET['page']) ? $_GET['page'] : 'projects';
    
    // Handle dashboard alias
    if ($current_page === 'dashboard') {
        $current_page = 'projects';
    }
    
    // Settings + Certifications (share storage in admin-settings.php)
    if (in_array($current_page, ['settings', 'certifications'], true)) {
        $saveSettings = function($admin_password, $site_settings, $admin_password_hash = '') {
            $settings_data = "<?php\n/**\n * Site Settings\n */\n\n";
            $settings_data .= "\$admin_password = '" . addslashes($admin_password) . "';\n";
            if ($admin_password_hash !== '') {
                $settings_data .= "\$site_settings['admin_password_hash'] = '" . addslashes($admin_password_hash) . "';\n";
            }
            $settings_data .= "\$site_settings = " . var_export($site_settings, true) . ";\n";
            $settings_data .= "\n?>";
            file_put_contents(__DIR__ . '/../config/admin-settings.php', $settings_data);
        };

        $safeSlug = function($name) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string)$name));
            return trim($slug, '-');
        };

        $partners_upload_dir = __DIR__ . '/../assets/img/partners';
        if (!is_dir($partners_upload_dir)) {
            @mkdir($partners_upload_dir, 0775, true);
        }

        $savePartnerLogo = function($fileField) use ($partners_upload_dir) {
            if (!isset($_FILES[$fileField]) || !is_array($_FILES[$fileField])) return '';
            if (($_FILES[$fileField]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return '';

            $tmp = $_FILES[$fileField]['tmp_name'];
            $orig = $_FILES[$fileField]['name'] ?? 'logo';
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed, true)) return '';
            if (!validateFileUpload($_FILES[$fileField], $allowed)) return '';

            $base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($orig, PATHINFO_FILENAME));
            $base = trim($base, '-');
            if ($base === '') $base = 'partner';
            $filename = $base . '-' . date('Y-m-d_H-i-s') . '.' . $ext;
            $dest = $partners_upload_dir . '/' . $filename;

            if (!move_uploaded_file($tmp, $dest)) return '';
            return 'partners/' . $filename; // relative to assets/img/
        };

        // Partners CRUD (stored in admin settings)
        if (!isset($site_settings['global_partners_items']) || !is_array($site_settings['global_partners_items'])) {
            $site_settings['global_partners_items'] = [];
        }

        // Certification cards CRUD (stored in admin settings)
        if (!isset($site_settings['certification_cards']) || !is_array($site_settings['certification_cards'])) {
            $site_settings['certification_cards'] = [];
        }

        $saveCertIcon = function($fileField) use ($partners_upload_dir) {
            // reuse same upload dir base but store under /partners/ (or create /certifications/)
            // We'll store under assets/img/partners/ for now to avoid new dirs.
            if (!isset($_FILES[$fileField]) || !is_array($_FILES[$fileField])) return '';
            if (($_FILES[$fileField]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return '';

            $tmp = $_FILES[$fileField]['tmp_name'];
            $orig = $_FILES[$fileField]['name'] ?? 'icon';
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed, true)) return '';
            if (!validateFileUpload($_FILES[$fileField], $allowed)) return '';

            $base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($orig, PATHINFO_FILENAME));
            $base = trim($base, '-');
            if ($base === '') $base = 'cert';
            $filename = $base . '-' . date('Y-m-d_H-i-s') . '.' . $ext;
            $dest = $partners_upload_dir . '/' . $filename;

            if (!move_uploaded_file($tmp, $dest)) return '';
            return 'partners/' . $filename; // relative to assets/img/
        };

        if (isset($_POST['init_cert_cards'])) {
            // Seed defaults matching current homepage cards
            $site_settings['certification_cards'] = [
                'iso-9001' => [
                    'title_en' => 'ISO 9001:2015',
                    'title_ar' => 'ISO 9001:2015',
                    'category_en' => 'Quality Management',
                    'category_ar' => 'إدارة الجودة',
                    'desc_en' => trim((string)t('certifications_iso_desc')),
                    'desc_ar' => trim((string)t('certifications_iso_desc')),
                    'icon' => 'construction/badge-1.webp',
                    'visible' => '1',
                    'order' => 1,
                ],
                'osha' => [
                    'title_en' => 'OSHA 30-Hour',
                    'title_ar' => 'OSHA 30-Hour',
                    'category_en' => trim((string)t('safety_standards')),
                    'category_ar' => trim((string)t('safety_standards')),
                    'desc_en' => trim((string)t('cert_osha_desc')),
                    'desc_ar' => trim((string)t('cert_osha_desc')),
                    'icon' => 'construction/badge-2.webp',
                    'visible' => '1',
                    'order' => 2,
                ],
                'licensed' => [
                    'title_en' => trim((string)t('state_licensed')),
                    'title_ar' => trim((string)t('state_licensed')),
                    'category_en' => trim((string)t('legal_compliance')),
                    'category_ar' => trim((string)t('legal_compliance')),
                    'desc_en' => trim((string)t('cert_licensed_desc')),
                    'desc_ar' => trim((string)t('cert_licensed_desc')),
                    'icon' => 'construction/badge-3.webp',
                    'visible' => '1',
                    'order' => 3,
                ],
                'leed' => [
                    'title_en' => trim((string)t('leed_certified')),
                    'title_ar' => trim((string)t('leed_certified')),
                    'category_en' => trim((string)t('sustainable_building')),
                    'category_ar' => trim((string)t('sustainable_building')),
                    'desc_en' => trim((string)t('cert_leed_desc')),
                    'desc_ar' => trim((string)t('cert_leed_desc')),
                    'icon' => 'construction/badge-4.webp',
                    'visible' => '1',
                    'order' => 4,
                ],
                'insured' => [
                    'title_en' => trim((string)t('fully_insured')),
                    'title_ar' => trim((string)t('fully_insured')),
                    'category_en' => trim((string)t('risk_management')),
                    'category_ar' => trim((string)t('risk_management')),
                    'desc_en' => trim((string)t('cert_insured_desc')),
                    'desc_ar' => trim((string)t('cert_insured_desc')),
                    'icon' => 'construction/badge-6.webp',
                    'visible' => '1',
                    'order' => 5,
                ],
                'training' => [
                    'title_en' => trim((string)t('skills_certified')),
                    'title_ar' => trim((string)t('skills_certified')),
                    'category_en' => trim((string)t('professional_training')),
                    'category_ar' => trim((string)t('professional_training')),
                    'desc_en' => trim((string)t('cert_skills_desc')),
                    'desc_ar' => trim((string)t('cert_skills_desc')),
                    'icon' => 'construction/badge-7.webp',
                    'visible' => '1',
                    'order' => 6,
                ],
            ];

            $saveSettings($admin_password, $site_settings);
            header('Location: projects-new.php?page=settings&success=cert_cards_updated');
            exit;
        }

        if (isset($_POST['add_cert_card'])) {
            $title_en = trim($_POST['cert_title_en'] ?? '');
            $title_ar = trim($_POST['cert_title_ar'] ?? '');
            $id = $safeSlug($title_en !== '' ? $title_en : $title_ar);
            if ($id === '') $id = 'cert-' . date('YmdHis');
            if (isset($site_settings['certification_cards'][$id])) {
                $id .= '-' . substr((string)time(), -4);
            }

            $icon = $saveCertIcon('cert_icon');
            if ($icon === '') {
                $icon = trim($_POST['cert_icon_existing'] ?? '');
            }

            $site_settings['certification_cards'][$id] = [
                'title_en' => $title_en,
                'title_ar' => $title_ar,
                'category_en' => trim($_POST['cert_category_en'] ?? ''),
                'category_ar' => trim($_POST['cert_category_ar'] ?? ''),
                'desc_en' => trim($_POST['cert_desc_en'] ?? ''),
                'desc_ar' => trim($_POST['cert_desc_ar'] ?? ''),
                'icon' => $icon,
                'visible' => isset($_POST['cert_visible']) ? '1' : '0',
                'order' => (int)($_POST['cert_order'] ?? 999),
            ];

            $saveSettings($admin_password, $site_settings);
            header('Location: projects-new.php?page=settings&success=cert_cards_updated');
            exit;
        }

        if (isset($_POST['edit_cert_card'])) {
            $id = $_POST['cert_id'] ?? '';
            if ($id !== '' && isset($site_settings['certification_cards'][$id])) {
                $site_settings['certification_cards'][$id]['title_en'] = trim($_POST['cert_title_en'] ?? '');
                $site_settings['certification_cards'][$id]['title_ar'] = trim($_POST['cert_title_ar'] ?? '');
                $site_settings['certification_cards'][$id]['category_en'] = trim($_POST['cert_category_en'] ?? '');
                $site_settings['certification_cards'][$id]['category_ar'] = trim($_POST['cert_category_ar'] ?? '');
                $site_settings['certification_cards'][$id]['desc_en'] = trim($_POST['cert_desc_en'] ?? '');
                $site_settings['certification_cards'][$id]['desc_ar'] = trim($_POST['cert_desc_ar'] ?? '');
                $site_settings['certification_cards'][$id]['visible'] = isset($_POST['cert_visible']) ? '1' : '0';
                $site_settings['certification_cards'][$id]['order'] = (int)($_POST['cert_order'] ?? 999);

                $icon = $saveCertIcon('cert_icon');
                if ($icon !== '') {
                    $site_settings['certification_cards'][$id]['icon'] = $icon;
                } else {
                    $existing = trim($_POST['cert_icon_existing'] ?? '');
                    if ($existing !== '') {
                        $site_settings['certification_cards'][$id]['icon'] = $existing;
                    }
                }

                $saveSettings($admin_password, $site_settings);
                header('Location: projects-new.php?page=settings&success=cert_cards_updated');
                exit;
            }
        }

        if (isset($_POST['delete_cert_card'])) {
            $id = $_POST['cert_id'] ?? '';
            if ($id !== '' && isset($site_settings['certification_cards'][$id])) {
                unset($site_settings['certification_cards'][$id]);
                $saveSettings($admin_password, $site_settings);
                header('Location: projects-new.php?page=settings&success=cert_cards_updated');
                exit;
            }
        }

        if (isset($_POST['add_partner'])) {
            $name_en = trim($_POST['partner_name_en'] ?? '');
            $name_ar = trim($_POST['partner_name_ar'] ?? '');
            $url = trim($_POST['partner_url'] ?? '');
            $visible = isset($_POST['partner_visible']) ? '1' : '0';

            $id = $safeSlug($name_en !== '' ? $name_en : $name_ar);
            if ($id === '') $id = 'partner-' . date('YmdHis');
            if (isset($site_settings['global_partners_items'][$id])) {
                $id .= '-' . substr((string)time(), -4);
            }

            $logo = $savePartnerLogo('partner_logo');

            $site_settings['global_partners_items'][$id] = [
                'name_en' => $name_en,
                'name_ar' => $name_ar,
                'url' => $url,
                'logo' => $logo,
                'visible' => $visible,
            ];

            $saveSettings($admin_password, $site_settings);
            header('Location: projects-new.php?page=settings&success=partners_updated');
            exit;
        }

        if (isset($_POST['edit_partner'])) {
            $id = $_POST['partner_id'] ?? '';
            if ($id !== '' && isset($site_settings['global_partners_items'][$id])) {
                $site_settings['global_partners_items'][$id]['name_en'] = trim($_POST['partner_name_en'] ?? '');
                $site_settings['global_partners_items'][$id]['name_ar'] = trim($_POST['partner_name_ar'] ?? '');
                $site_settings['global_partners_items'][$id]['url'] = trim($_POST['partner_url'] ?? '');
                $site_settings['global_partners_items'][$id]['visible'] = isset($_POST['partner_visible']) ? '1' : '0';

                $logo = $savePartnerLogo('partner_logo');
                if ($logo !== '') {
                    $site_settings['global_partners_items'][$id]['logo'] = $logo;
                }

                $saveSettings($admin_password, $site_settings);
                header('Location: projects-new.php?page=settings&success=partners_updated');
                exit;
            }
        }

        if (isset($_POST['delete_partner'])) {
            $id = $_POST['partner_id'] ?? '';
            if ($id !== '' && isset($site_settings['global_partners_items'][$id])) {
                unset($site_settings['global_partners_items'][$id]);
                $saveSettings($admin_password, $site_settings);
                header('Location: projects-new.php?page=settings&success=partners_updated');
                exit;
            }
        }

        if ($current_page === 'settings' && isset($_POST['update_settings'])) {
            // Password validation
            if (!empty($_POST['new_password'])) {
                if ($_POST['new_password'] !== $_POST['confirm_password']) {
                    header('Location: projects-new.php?page=settings&error=password_mismatch');
                    exit;
                }
                
                // Validate password strength
                $password = $_POST['new_password'];
                $errors = [];
                
                if (strlen($password) < 8) {
                    $errors[] = 'Password must be at least 8 characters long';
                }
                if (!preg_match('/[A-Z]/', $password)) {
                    $errors[] = 'Password must contain at least one uppercase letter';
                }
                if (!preg_match('/[a-z]/', $password)) {
                    $errors[] = 'Password must contain at least one lowercase letter';
                }
                if (!preg_match('/[0-9]/', $password)) {
                    $errors[] = 'Password must contain at least one number';
                }
                if (!preg_match('/[!@#$%^&*]/', $password)) {
                    $errors[] = 'Password must contain at least one special character (!@#$%^&*)';
                }
                
                if (!empty($errors)) {
                    // Store errors in session to display
                    $_SESSION['password_errors'] = $errors;
                    header('Location: projects-new.php?page=settings&error=password_validation');
                    exit;
                }
                
                // Update password
                $admin_password = $_POST['new_password'];
                
                // Force logout to require new password login
                unset($_SESSION['admin_authenticated']);
                session_write_close();
                
                $new_password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
                $site_settings['admin_password_hash'] = $new_password_hash;

                // Save settings to file
                $settings_data = "<?php\n/**\n * Site Settings\n */\n\n";
                $settings_data .= "\$admin_password = '" . addslashes($admin_password) . "';\n";
                $settings_data .= "\$site_settings['admin_password_hash'] = '" . addslashes($new_password_hash) . "';\n";
                $settings_data .= "\$site_settings = " . var_export($site_settings, true) . ";\n";
                $settings_data .= "\n?>";
                
                file_put_contents(__DIR__ . '/../config/admin-settings.php', $settings_data);
                
                // Redirect to login with success message
                header('Location: projects-new.php?success=password_changed');
                exit;
            }
            
            // Update site settings (only if password wasn't changed)
            if (empty($_POST['new_password'])) {
                // Merge to avoid wiping unrelated settings (e.g., partners list)
                $site_settings = array_merge($site_settings, [
                    'site_name' => $_POST['site_name'] ?? 'Diar360',
                    'admin_email' => $_POST['admin_email'] ?? 'info@diar360.com',
                    'company_phone' => $_POST['company_phone'] ?? '+966 1 1 296 7735',
                    'company_address' => $_POST['company_address'] ?? 'Prince Mohammed Ibn Salman Ibn Abdulaziz Rd, Al Falah Dist, Riyadh - KSA',
                    'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0',

                    // Homepage: Global Partners block (EN/AR)
                    'global_partners_title_en' => trim($_POST['global_partners_title_en'] ?? ''),
                    'global_partners_title_ar' => trim($_POST['global_partners_title_ar'] ?? ''),
                    'global_partners_desc_en' => trim($_POST['global_partners_desc_en'] ?? ''),
                    'global_partners_desc_ar' => trim($_POST['global_partners_desc_ar'] ?? ''),
                ]);
                
                // Save settings to file
                $saveSettings($admin_password, $site_settings);
                header('Location: projects-new.php?page=settings&success=settings_updated');
                exit;
            }
        }
        
        // Backup functionality (settings only)
        if ($current_page === 'settings' && isset($_GET['action']) && $_GET['action'] === 'backup') {
            $backup_filename = 'projects-backup-' . date('Y-m-d-H-i-s') . '.php';
            $backup_content = "<?php\n/**\n * Projects Data Backup - " . date('Y-m-d H:i:s') . "\n */\n\n";
            $backup_content .= file_get_contents(__DIR__ . '/../config/projects-data.php');
            
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $backup_filename . '"');
            echo $backup_content;
            exit;
        }
        
        // Restore functionality (settings only)
        if ($current_page === 'settings' && isset($_POST['restore_backup']) && isset($_FILES['backup_file'])) {
            if ($_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
                $backup_tmp = $_FILES['backup_file']['tmp_name'];
                $backup_ext = strtolower(pathinfo($_FILES['backup_file']['name'] ?? '', PATHINFO_EXTENSION));
                $backup_size = (int)($_FILES['backup_file']['size'] ?? 0);
                if ($backup_ext !== 'php' || $backup_size <= 0 || $backup_size > 2 * 1024 * 1024) {
                    header('Location: projects-new.php?page=settings&error=invalid_backup');
                    exit;
                }

                $backup_content = file_get_contents($backup_tmp);
                if (!is_string($backup_content) || strpos($backup_content, '<?php') === false) {
                    header('Location: projects-new.php?page=settings&error=invalid_backup');
                    exit;
                }
                
                // Validate backup file
                if (strpos($backup_content, '$projects = [') !== false) {
                    // Restore the backup
                    file_put_contents(__DIR__ . '/../config/projects-data.php', $backup_content);
                    header('Location: projects-new.php?page=settings&success=restored');
                    exit;
                } else {
                    header('Location: projects-new.php?page=settings&error=invalid_backup');
                    exit;
                }
            }
        }
    }

    // Team CRUD
    if ($current_page === 'team') {
        // Normalize data in case file is missing/empty
        if (!isset($ceo_profile) || !is_array($ceo_profile)) {
            $ceo_profile = [];
        }
        if (!isset($team_members) || !is_array($team_members)) {
            $team_members = [];
        }

        $team_img_dir = realpath(__DIR__ . '/../assets/img');
        $team_upload_dir = __DIR__ . '/../assets/img/team';
        if (!is_dir($team_upload_dir)) {
            @mkdir($team_upload_dir, 0775, true);
        }

        $safeSlug = function($name) {
            // Case-insensitive so "Civil Engineer" => "civil-engineer" (do not drop uppercase letters).
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string)$name));
            return trim($slug, '-');
        };

        $saveUploadedImage = function($fileField) use ($team_upload_dir) {
            if (!isset($_FILES[$fileField]) || !is_array($_FILES[$fileField])) return '';
            if ($_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) return '';

            $tmp = $_FILES[$fileField]['tmp_name'];
            $orig = $_FILES[$fileField]['name'] ?? 'image';
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed, true)) return '';
            if (!validateFileUpload($_FILES[$fileField], $allowed)) return '';

            $base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($orig, PATHINFO_FILENAME));
            $base = trim($base, '-');
            if ($base === '') $base = 'team';
            $filename = $base . '-' . date('Y-m-d_H-i-s') . '.' . $ext;
            $dest = $team_upload_dir . '/' . $filename;

            if (!move_uploaded_file($tmp, $dest)) return '';
            return 'team/' . $filename; // relative to assets/img/
        };

        // Update CEO profile
        if (isset($_POST['update_ceo'])) {
            $ceo_profile['name'] = trim($_POST['ceo_name'] ?? '');
            $ceo_profile['title'] = trim($_POST['ceo_title'] ?? '');
            $ceo_profile['source_heading'] = trim($_POST['ceo_source_heading'] ?? '');

            $ceo_profile['socials'] = [
                'linkedin' => trim($_POST['ceo_social_linkedin'] ?? ''),
                'twitter' => trim($_POST['ceo_social_twitter'] ?? ''),
                'email' => trim($_POST['ceo_social_email'] ?? ''),
            ];

            $bioRaw = trim($_POST['ceo_bio'] ?? '');
            $paras = array_values(array_filter(array_map('trim', preg_split('/\R\R+/', $bioRaw))));
            $ceo_profile['bio_paragraphs'] = $paras;

            $newPhoto = $saveUploadedImage('ceo_photo');
            if ($newPhoto !== '') {
                $ceo_profile['photo'] = $newPhoto;
            } else {
                $ceo_profile['photo'] = $ceo_profile['photo'] ?? 'construction/CEO.webp';
            }

            updateTeamData($ceo_profile, $team_members);
            header('Location: projects-new.php?page=team&success=team_updated');
            exit;
        }

        // Add team member
        if (isset($_POST['add_member'])) {
            $name = trim($_POST['name'] ?? '');
            $slug = $safeSlug($name);
            if ($slug === '') $slug = 'member-' . date('YmdHis');

            $photo = $saveUploadedImage('photo');
            if ($photo === '') {
                $photo = 'construction/team-3.webp';
            }

            $layout = ($_POST['layout'] ?? 'compact') === 'featured' ? 'featured' : 'compact';
            $visible = isset($_POST['visible']) ? '1' : '0';

            $socials = [
                'linkedin' => trim($_POST['social_linkedin'] ?? ''),
                'twitter' => trim($_POST['social_twitter'] ?? ''),
                'facebook' => trim($_POST['social_facebook'] ?? ''),
                'instagram' => trim($_POST['social_instagram'] ?? ''),
            ];

            $credentials = [];
            $cred1 = trim($_POST['credential_1'] ?? '');
            $cred2 = trim($_POST['credential_2'] ?? '');
            if ($cred1 !== '') $credentials[] = ['icon' => trim($_POST['credential_1_icon'] ?? 'bi-award'), 'label' => $cred1];
            if ($cred2 !== '') $credentials[] = ['icon' => trim($_POST['credential_2_icon'] ?? 'bi-award'), 'label' => $cred2];

            $skills = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $_POST['skills'] ?? '')))));

            $team_members[$slug] = [
                'layout' => $layout,
                'name' => $name,
                'role' => trim($_POST['role'] ?? ''),
                'photo' => $photo,
                'experience' => trim($_POST['experience'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'credentials' => $credentials,
                'socials' => $socials,
                'quick_contact' => [
                    'email' => trim($_POST['qc_email'] ?? ''),
                    'phone' => trim($_POST['qc_phone'] ?? ''),
                    'linkedin' => trim($_POST['qc_linkedin'] ?? ''),
                ],
                'skills' => $skills,
                'visible' => $visible,
            ];

            updateTeamData($ceo_profile, $team_members);
            header('Location: projects-new.php?page=team&success=member_added');
            exit;
        }

        // Edit team member
        if (isset($_POST['edit_member'])) {
            $slug = $_POST['member_slug'] ?? '';
            if (isset($team_members[$slug])) {
                $team_members[$slug]['name'] = trim($_POST['name'] ?? '');
                $team_members[$slug]['role'] = trim($_POST['role'] ?? '');
                $team_members[$slug]['layout'] = ($_POST['layout'] ?? 'compact') === 'featured' ? 'featured' : 'compact';
                $team_members[$slug]['experience'] = trim($_POST['experience'] ?? '');
                $team_members[$slug]['email'] = trim($_POST['email'] ?? '');
                $team_members[$slug]['phone'] = trim($_POST['phone'] ?? '');
                $team_members[$slug]['description'] = trim($_POST['description'] ?? '');
                $team_members[$slug]['visible'] = isset($_POST['visible']) ? '1' : '0';

                $photo = $saveUploadedImage('photo');
                if ($photo !== '') {
                    $team_members[$slug]['photo'] = $photo;
                }

                $team_members[$slug]['socials'] = [
                    'linkedin' => trim($_POST['social_linkedin'] ?? ''),
                    'twitter' => trim($_POST['social_twitter'] ?? ''),
                    'facebook' => trim($_POST['social_facebook'] ?? ''),
                    'instagram' => trim($_POST['social_instagram'] ?? ''),
                ];

                $credentials = [];
                $cred1 = trim($_POST['credential_1'] ?? '');
                $cred2 = trim($_POST['credential_2'] ?? '');
                if ($cred1 !== '') $credentials[] = ['icon' => trim($_POST['credential_1_icon'] ?? 'bi-award'), 'label' => $cred1];
                if ($cred2 !== '') $credentials[] = ['icon' => trim($_POST['credential_2_icon'] ?? 'bi-award'), 'label' => $cred2];
                $team_members[$slug]['credentials'] = $credentials;

                $team_members[$slug]['quick_contact'] = [
                    'email' => trim($_POST['qc_email'] ?? ''),
                    'phone' => trim($_POST['qc_phone'] ?? ''),
                    'linkedin' => trim($_POST['qc_linkedin'] ?? ''),
                ];

                $skills = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $_POST['skills'] ?? '')))));
                $team_members[$slug]['skills'] = $skills;

                updateTeamData($ceo_profile, $team_members);
                header('Location: projects-new.php?page=team&success=member_updated');
                exit;
            }
        }

        // Delete team member
        if (isset($_POST['delete_member'])) {
            $slug = $_POST['member_slug'] ?? '';
            if (isset($team_members[$slug])) {
                unset($team_members[$slug]);
                updateTeamData($ceo_profile, $team_members);
                header('Location: projects-new.php?page=team&success=member_deleted');
                exit;
            }
        }
    }

    // Careers / Jobs CRUD
    if ($current_page === 'careers') {
        if (!isset($job_posts) || !is_array($job_posts)) {
            $job_posts = [];
        }

        $safeSlug = function($name) {
            // Case-insensitive so "Civil Engineer" => "civil-engineer" (do not drop uppercase letters).
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string)$name));
            return trim($slug, '-');
        };

        $normalizeLines = function($text) {
            $text = str_replace("\r", "", (string)$text);
            $lines = array_values(array_filter(array_map('trim', explode("\n", $text))));
            return $lines;
        };

        $updateCareersData = function($job_posts) {
            $data = "<?php\n/**\n * Careers / Jobs Data\n *\n * This file is written by the admin panel (admin/projects-new.php?page=careers).\n */\n\n";
            $data .= "\$job_posts = " . var_export($job_posts, true) . ";\n\n?>\n";
            file_put_contents(__DIR__ . '/../config/careers-data.php', $data);
        };

        if (isset($_POST['add_job'])) {
            $title_en = trim($_POST['job_title'] ?? '');
            $title_ar = trim($_POST['job_title_ar'] ?? '');
            $slug = $safeSlug($title_en);
            if ($slug === '') $slug = 'job-' . date('YmdHis');

            $posted_at = trim($_POST['posted_at'] ?? '');
            if ($posted_at === '') $posted_at = date('Y-m-d');

            $job_posts[$slug] = [
                // Back-compat: keep legacy keys as EN
                'title' => $title_en,
                'title_en' => $title_en,
                'title_ar' => $title_ar,
                'department' => trim($_POST['department'] ?? ''),
                'department_en' => trim($_POST['department'] ?? ''),
                'department_ar' => trim($_POST['department_ar'] ?? ''),
                'location' => trim($_POST['location'] ?? ''),
                'location_en' => trim($_POST['location'] ?? ''),
                'location_ar' => trim($_POST['location_ar'] ?? ''),
                'type' => trim($_POST['type'] ?? ''),
                'type_en' => trim($_POST['type'] ?? ''),
                'type_ar' => trim($_POST['type_ar'] ?? ''),
                // Back-compat: keep legacy keys as EN
                'summary' => trim($_POST['summary'] ?? ''),
                'summary_en' => trim($_POST['summary'] ?? ''),
                'summary_ar' => trim($_POST['summary_ar'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'description_en' => trim($_POST['description'] ?? ''),
                'description_ar' => trim($_POST['description_ar'] ?? ''),
                'responsibilities' => $normalizeLines($_POST['responsibilities'] ?? ''),
                'responsibilities_en' => $normalizeLines($_POST['responsibilities'] ?? ''),
                'responsibilities_ar' => $normalizeLines($_POST['responsibilities_ar'] ?? ''),
                'requirements' => $normalizeLines($_POST['requirements'] ?? ''),
                'requirements_en' => $normalizeLines($_POST['requirements'] ?? ''),
                'requirements_ar' => $normalizeLines($_POST['requirements_ar'] ?? ''),
                'visible' => isset($_POST['visible']) ? '1' : '0',
                'posted_at' => $posted_at,
                'updated_at' => date('Y-m-d'),
            ];

            $updateCareersData($job_posts);
            header('Location: projects-new.php?page=careers&success=job_added');
            exit;
        }

        if (isset($_POST['edit_job'])) {
            $slug = $_POST['job_slug'] ?? '';
            if ($slug !== '' && isset($job_posts[$slug])) {
                $title_en = trim($_POST['job_title'] ?? '');
                $title_ar = trim($_POST['job_title_ar'] ?? '');
                $job_posts[$slug]['title'] = $title_en;
                $job_posts[$slug]['title_en'] = $title_en;
                $job_posts[$slug]['title_ar'] = $title_ar;
                $job_posts[$slug]['department'] = trim($_POST['department'] ?? '');
                $job_posts[$slug]['department_en'] = trim($_POST['department'] ?? '');
                $job_posts[$slug]['department_ar'] = trim($_POST['department_ar'] ?? '');
                $job_posts[$slug]['location'] = trim($_POST['location'] ?? '');
                $job_posts[$slug]['location_en'] = trim($_POST['location'] ?? '');
                $job_posts[$slug]['location_ar'] = trim($_POST['location_ar'] ?? '');
                $job_posts[$slug]['type'] = trim($_POST['type'] ?? '');
                $job_posts[$slug]['type_en'] = trim($_POST['type'] ?? '');
                $job_posts[$slug]['type_ar'] = trim($_POST['type_ar'] ?? '');
                $job_posts[$slug]['summary'] = trim($_POST['summary'] ?? '');
                $job_posts[$slug]['summary_en'] = trim($_POST['summary'] ?? '');
                $job_posts[$slug]['summary_ar'] = trim($_POST['summary_ar'] ?? '');
                $job_posts[$slug]['description'] = trim($_POST['description'] ?? '');
                $job_posts[$slug]['description_en'] = trim($_POST['description'] ?? '');
                $job_posts[$slug]['description_ar'] = trim($_POST['description_ar'] ?? '');
                $job_posts[$slug]['responsibilities'] = $normalizeLines($_POST['responsibilities'] ?? '');
                $job_posts[$slug]['responsibilities_en'] = $normalizeLines($_POST['responsibilities'] ?? '');
                $job_posts[$slug]['responsibilities_ar'] = $normalizeLines($_POST['responsibilities_ar'] ?? '');
                $job_posts[$slug]['requirements'] = $normalizeLines($_POST['requirements'] ?? '');
                $job_posts[$slug]['requirements_en'] = $normalizeLines($_POST['requirements'] ?? '');
                $job_posts[$slug]['requirements_ar'] = $normalizeLines($_POST['requirements_ar'] ?? '');
                $job_posts[$slug]['visible'] = isset($_POST['visible']) ? '1' : '0';

                $posted_at = trim($_POST['posted_at'] ?? '');
                if ($posted_at !== '') {
                    $job_posts[$slug]['posted_at'] = $posted_at;
                } elseif (!isset($job_posts[$slug]['posted_at'])) {
                    $job_posts[$slug]['posted_at'] = date('Y-m-d');
                }

                $job_posts[$slug]['updated_at'] = date('Y-m-d');

                $updateCareersData($job_posts);
                header('Location: projects-new.php?page=careers&success=job_updated');
                exit;
            }
        }

        if (isset($_POST['delete_job'])) {
            $slug = $_POST['job_slug'] ?? '';
            if ($slug !== '' && isset($job_posts[$slug])) {
                unset($job_posts[$slug]);
                $updateCareersData($job_posts);
                header('Location: projects-new.php?page=careers&success=job_deleted');
                exit;
            }
        }
    }

    // Testimonials CRUD (moderation)
    if ($current_page === 'testimonials') {
        if (!isset($testimonials) || !is_array($testimonials)) {
            $testimonials = [];
        }

        $saveTestimonials = function($testimonials) {
            $data = "<?php\n/**\n * Testimonials Data\n *\n * - Public submissions append here as \"pending\"\n * - Admin moderates (approve/visible/edit/delete)\n */\n\n";
            $data .= "\$testimonials = " . var_export($testimonials, true) . ";\n\n?>\n";
            file_put_contents(__DIR__ . '/../config/testimonials-data.php', $data);
        };

        $normLines = function($t) {
            $t = trim((string)$t);
            return $t;
        };

        if (isset($_POST['approve_testimonial'])) {
            $id = $_POST['testimonial_id'] ?? '';
            if ($id !== '' && isset($testimonials[$id])) {
                $testimonials[$id]['status'] = 'approved';
                $testimonials[$id]['visible'] = '1';
                $testimonials[$id]['updated_at'] = date('Y-m-d H:i:s');
                $saveTestimonials($testimonials);
                header('Location: projects-new.php?page=testimonials&success=testimonials_updated');
                exit;
            }
        }

        if (isset($_POST['hide_testimonial'])) {
            $id = $_POST['testimonial_id'] ?? '';
            if ($id !== '' && isset($testimonials[$id])) {
                $testimonials[$id]['visible'] = '0';
                $testimonials[$id]['updated_at'] = date('Y-m-d H:i:s');
                $saveTestimonials($testimonials);
                header('Location: projects-new.php?page=testimonials&success=testimonials_updated');
                exit;
            }
        }

        if (isset($_POST['edit_testimonial'])) {
            $id = $_POST['testimonial_id'] ?? '';
            if ($id !== '' && isset($testimonials[$id])) {
                $testimonials[$id]['name'] = trim($_POST['t_name'] ?? '');
                $testimonials[$id]['rating'] = max(1, min(5, (int)($_POST['t_rating'] ?? 5)));
                $testimonials[$id]['message_en'] = $normLines($_POST['t_message_en'] ?? '');
                $testimonials[$id]['message_ar'] = $normLines($_POST['t_message_ar'] ?? '');
                $testimonials[$id]['role_en'] = trim($_POST['t_role_en'] ?? '');
                $testimonials[$id]['role_ar'] = trim($_POST['t_role_ar'] ?? '');
                $testimonials[$id]['company_en'] = trim($_POST['t_company_en'] ?? '');
                $testimonials[$id]['company_ar'] = trim($_POST['t_company_ar'] ?? '');
                $testimonials[$id]['status'] = in_array($_POST['t_status'] ?? 'pending', ['pending', 'approved', 'rejected'], true) ? ($_POST['t_status'] ?? 'pending') : 'pending';
                $testimonials[$id]['visible'] = isset($_POST['t_visible']) ? '1' : '0';
                $testimonials[$id]['updated_at'] = date('Y-m-d H:i:s');
                $saveTestimonials($testimonials);
                header('Location: projects-new.php?page=testimonials&success=testimonials_updated');
                exit;
            }
        }

        if (isset($_POST['delete_testimonial'])) {
            $id = $_POST['testimonial_id'] ?? '';
            if ($id !== '' && isset($testimonials[$id])) {
                unset($testimonials[$id]);
                $saveTestimonials($testimonials);
                header('Location: projects-new.php?page=testimonials&success=testimonials_updated');
                exit;
            }
        }
    }
    
    // Global backup functionality (accessible from any page)
    if (isset($_GET['action']) && $_GET['action'] === 'backup' && $is_authenticated) {
        $backup_filename = 'projects-backup-' . date('Y-m-d-H-i-s') . '.php';
        $backup_content = "<?php\n/**\n * Projects Data Backup - " . date('Y-m-d H:i:s') . "\n */\n\n";
        $backup_content .= file_get_contents(__DIR__ . '/../config/projects-data.php');
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup_filename . '"');
        echo $backup_content;
        exit;
    }
    
    // Add Project
    if (isset($_POST['add_project'])) {
        $title = $_POST['title'];
        $title_ar = $_POST['title_ar'];
        $category = $_POST['category'];
        $status = $_POST['status'];
        $location = $_POST['location'];
        $contract_value = $_POST['contract_value'];
        $scope = $_POST['scope'];
        $description = $_POST['description'];
        $description_ar = $_POST['description_ar'];
        $visible = isset($_POST['visible']) ? 1 : 0;
        
        // Create slug from title
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
        $slug = rtrim($slug, '-');
        
        // Handle image uploads
        $image_fields = [
            'project_image' => $slug . '.webp',
            'construction_image' => $slug . '-construction.webp',
            'foundation_image' => $slug . '-foundation.webp',
            'interior_image' => $slug . '-interior.webp',
            'architecture_image' => $slug . '-architecture.webp',
            'blueprint_image' => $slug . '-blueprint.webp',
            'quality_control_image' => $slug . '-quality-control.webp',
            'system_installation_image' => $slug . '-system-installation.webp'
        ];
        
        foreach ($image_fields as $field => $filename) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                // Validate file upload
                if (!validateFileUpload($_FILES[$field], ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    continue; // Skip this file if invalid
                }
                
                $tmp_name = $_FILES[$field]['tmp_name'];
                $upload_path = __DIR__ . '/../assets/img/projects/' . $filename;
                
                // Convert to webp
                $image_info = getimagesize($tmp_name);
                if ($image_info) {
                    // Check if GD library functions are available
                    if (!function_exists('imagecreatefromjpeg') || !function_exists('imagewebp')) {
                        // GD library not available, just move the file as-is
                        move_uploaded_file($tmp_name, $upload_path);
                    } else {
                        $image = null;
                        switch ($image_info[2]) {
                            case IMAGETYPE_JPEG:
                                if (function_exists('imagecreatefromjpeg')) {
                                    $image = imagecreatefromjpeg($tmp_name);
                                }
                                break;
                            case IMAGETYPE_PNG:
                                if (function_exists('imagecreatefrompng')) {
                                    $image = imagecreatefrompng($tmp_name);
                                }
                                break;
                            case IMAGETYPE_GIF:
                                if (function_exists('imagecreatefromgif')) {
                                    $image = imagecreatefromgif($tmp_name);
                                }
                                break;
                        }
                        
                        if ($image && function_exists('imagewebp')) {
                            imagewebp($image, $upload_path, 85);
                            imagedestroy($image);
                        } elseif ($image) {
                            // WebP conversion not available, save as original format
                            switch ($image_info[2]) {
                                case IMAGETYPE_JPEG:
                                    imagejpeg($image, str_replace('.webp', '.jpg', $upload_path), 85);
                                    break;
                                case IMAGETYPE_PNG:
                                    imagepng($image, str_replace('.webp', '.png', $upload_path));
                                    break;
                                case IMAGETYPE_GIF:
                                    imagegif($image, str_replace('.webp', '.gif', $upload_path));
                                    break;
                            }
                            imagedestroy($image);
                        } else {
                            // No image processing available, move file as-is
                            move_uploaded_file($tmp_name, $upload_path);
                        }
                    }
                }
            }
        }
        
        // Handle PDF upload
        $contract_pdf_path = '';
        if (isset($_FILES['contract_pdf']) && $_FILES['contract_pdf']['error'] === UPLOAD_ERR_OK) {
            $pdf_tmp_name = $_FILES['contract_pdf']['tmp_name'];
            $pdf_filename = $slug . '-contract.pdf';
            $pdf_upload_path = __DIR__ . '/../assets/contracts/' . $pdf_filename;
            
            // Ensure contracts directory exists
            if (!is_dir(__DIR__ . '/../assets/contracts/')) {
                mkdir(__DIR__ . '/../assets/contracts/', 0755, true);
            }
            
            // Validate and move PDF
            if (isset($_FILES['contract_pdf']) && validateFileUpload($_FILES['contract_pdf'], ['pdf'])) {
                $file_type = mime_content_type($pdf_tmp_name);
                if ($file_type === 'application/pdf') {
                    move_uploaded_file($pdf_tmp_name, $pdf_upload_path);
                    $contract_pdf_path = $pdf_filename;
                }
            }
        }
        
        // Add to projects array
        $projects[$slug] = [
            'title' => $title,
            'title_ar' => $title_ar,
            'category' => $category,
            'status' => $status,
            'location' => $location,
            'contract_value' => $contract_value,
            'scope' => $scope,
            'description' => $description,
            'description_ar' => $description_ar,
            'contract_pdf' => $contract_pdf_path,
            'visible' => $visible,
            'specs' => [
                'Client' => 'Diar360 Client',
                'Duration' => '12 months',
                'Budget' => $contract_value,
                'Location' => $location
            ]
        ];
        
        updateProjectsData($projects);
        header('Location: projects-new.php?success=added');
        exit;
    }
    
    // Edit Project
    if (isset($_POST['edit_project'])) {
        $slug = $_POST['project_slug'];
        if (isset($projects[$slug])) {
            $projects[$slug]['title'] = $_POST['title'];
            $projects[$slug]['title_ar'] = $_POST['title_ar'];
            $projects[$slug]['category'] = $_POST['category'];
            $projects[$slug]['status'] = $_POST['status'];
            $projects[$slug]['location'] = $_POST['location'];
            $projects[$slug]['contract_value'] = $_POST['contract_value'];
            $projects[$slug]['scope'] = $_POST['scope'];
            $projects[$slug]['description'] = $_POST['description'];
            $projects[$slug]['description_ar'] = $_POST['description_ar'];
            $projects[$slug]['visible'] = isset($_POST['visible']) ? 1 : 0;
            
            // Handle image uploads
            $image_fields = [
                'project_image' => $slug . '.webp',
                'construction_image' => $slug . '-construction.webp',
                'foundation_image' => $slug . '-foundation.webp',
                'interior_image' => $slug . '-interior.webp',
                'architecture_image' => $slug . '-architecture.webp',
                'blueprint_image' => $slug . '-blueprint.webp',
                'quality_control_image' => $slug . '-quality-control.webp',
                'system_installation_image' => $slug . '-system-installation.webp'
            ];
            
            foreach ($image_fields as $field => $filename) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES[$field]['tmp_name'];
                    $upload_path = __DIR__ . '/../assets/img/projects/' . $filename;
                    
                    $image_info = getimagesize($tmp_name);
                    if ($image_info) {
                        // Check if GD library functions are available
                        if (!function_exists('imagecreatefromjpeg') || !function_exists('imagewebp')) {
                            // GD library not available, just move the file as-is
                            move_uploaded_file($tmp_name, $upload_path);
                        } else {
                            $image = null;
                            switch ($image_info[2]) {
                                case IMAGETYPE_JPEG:
                                    if (function_exists('imagecreatefromjpeg')) {
                                        $image = imagecreatefromjpeg($tmp_name);
                                    }
                                    break;
                                case IMAGETYPE_PNG:
                                    if (function_exists('imagecreatefrompng')) {
                                        $image = imagecreatefrompng($tmp_name);
                                    }
                                    break;
                                case IMAGETYPE_GIF:
                                    if (function_exists('imagecreatefromgif')) {
                                        $image = imagecreatefromgif($tmp_name);
                                    }
                                    break;
                            }
                            
                            if ($image && function_exists('imagewebp')) {
                                imagewebp($image, $upload_path, 85);
                                imagedestroy($image);
                            } elseif ($image) {
                                // WebP conversion not available, save as original format
                                switch ($image_info[2]) {
                                    case IMAGETYPE_JPEG:
                                        imagejpeg($image, str_replace('.webp', '.jpg', $upload_path), 85);
                                        break;
                                    case IMAGETYPE_PNG:
                                        imagepng($image, str_replace('.webp', '.png', $upload_path));
                                        break;
                                    case IMAGETYPE_GIF:
                                        imagegif($image, str_replace('.webp', '.gif', $upload_path));
                                        break;
                                }
                                imagedestroy($image);
                            } else {
                                // No image processing available, move file as-is
                                move_uploaded_file($tmp_name, $upload_path);
                            }
                        }
                    }
                }
            }
            
            // Handle PDF upload - only if not deleting
            if (!isset($_POST['delete_contract_pdf']) && isset($_FILES['contract_pdf']) && $_FILES['contract_pdf']['error'] === UPLOAD_ERR_OK) {
                $pdf_tmp_name = $_FILES['contract_pdf']['tmp_name'];
                $timestamp = date('Y-m-d_H-i-s');
                $pdf_filename = $slug . '-' . $timestamp . '-contract.pdf';
                $pdf_upload_path = __DIR__ . '/../assets/contracts/' . $pdf_filename;
                
                // Debug: Log upload process
                error_log("PDF Upload Process:");
                error_log("Slug: " . $slug);
                error_log("New filename: " . $pdf_filename);
                error_log("Upload path: " . $pdf_upload_path);
                error_log("Current PDF in data: " . (isset($projects[$slug]['contract_pdf']) ? $projects[$slug]['contract_pdf'] : 'NONE'));
                
                // Ensure contracts directory exists
                if (!is_dir(__DIR__ . '/../assets/contracts/')) {
                    mkdir(__DIR__ . '/../assets/contracts/', 0755, true);
                }
                
                // Delete ALL existing PDFs for this project to prevent conflicts
                if (isset($projects[$slug]['contract_pdf']) && $projects[$slug]['contract_pdf'] !== '') {
                    $existing_pdf = __DIR__ . '/../assets/contracts/' . $projects[$slug]['contract_pdf'];
                    error_log("Existing PDF to delete: " . $existing_pdf);
                    error_log("Existing PDF exists: " . (file_exists($existing_pdf) ? 'YES' : 'NO'));
                    
                    if (file_exists($existing_pdf)) {
                        $delete_result = unlink($existing_pdf);
                        error_log("Delete existing PDF result: " . ($delete_result ? 'SUCCESS' : 'FAILED'));
                    }
                    
                    // Also clean up any old PDF files with this project slug
                    $contract_files = glob(__DIR__ . '/../assets/contracts/' . $slug . '*-contract.pdf');
                    if ($contract_files) {
                        foreach ($contract_files as $old_file) {
                            if (file_exists($old_file) && $old_file !== $pdf_upload_path) {
                                error_log("Cleaning up old file: " . $old_file);
                                unlink($old_file);
                            }
                        }
                    }
                }
                
                // Validate and move new PDF
                $file_type = mime_content_type($pdf_tmp_name);
                if ($file_type === 'application/pdf') {
                    $move_result = move_uploaded_file($pdf_tmp_name, $pdf_upload_path);
                    error_log("Move new PDF result: " . ($move_result ? 'SUCCESS' : 'FAILED'));
                    error_log("New file exists after upload: " . (file_exists($pdf_upload_path) ? 'YES' : 'NO'));
                    
                    if ($move_result) {
                        $projects[$slug]['contract_pdf'] = $pdf_filename;
                        error_log("Updated project data with new PDF: " . $pdf_filename);
                    }
                }
            }
            
            updateProjectsData($projects);
            header('Location: projects-new.php?success=updated');
            exit;
        }
    }
    
    // Delete Project
    if (isset($_POST['delete_project'])) {
        $slug = $_POST['project_slug'];
        if (isset($projects[$slug])) {
            unset($projects[$slug]);
            updateProjectsData($projects);
            header('Location: projects-new.php?success=deleted');
            exit;
        }
    }
    
    // Delete Contract PDF
    if (isset($_POST['delete_contract_pdf'])) {
        $slug = $_POST['project_slug'];
        if (isset($projects[$slug])) {
            // Delete existing PDF
            if (isset($projects[$slug]['contract_pdf']) && $projects[$slug]['contract_pdf'] !== '') {
                $existing_pdf = __DIR__ . '/../assets/contracts/' . $projects[$slug]['contract_pdf'];
                
                // Debug: Log the file path
                error_log("Attempting to delete PDF: " . $existing_pdf);
                error_log("File exists: " . (file_exists($existing_pdf) ? 'YES' : 'NO'));
                
                if (file_exists($existing_pdf)) {
                    $delete_result = unlink($existing_pdf);
                    error_log("Delete result: " . ($delete_result ? 'SUCCESS' : 'FAILED'));
                    
                    if ($delete_result) {
                        $projects[$slug]['contract_pdf'] = '';
                        updateProjectsData($projects);
                        header('Location: projects-new.php?success=pdf_deleted');
                        exit;
                    } else {
                        error_log("Failed to delete PDF file: " . $existing_pdf);
                        header('Location: projects-new.php?error=pdf_delete_failed');
                        exit;
                    }
                } else {
                    error_log("PDF file does not exist: " . $existing_pdf);
                    // File doesn't exist, just clear the data
                    $projects[$slug]['contract_pdf'] = '';
                    updateProjectsData($projects);
                    header('Location: projects-new.php?success=pdf_deleted');
                    exit;
                }
            } else {
                // No PDF to delete
                header('Location: projects-new.php?error=no_pdf_to_delete');
                exit;
            }
        }
    }
    
    // Status Update
    if (isset($_POST['update_status'])) {
        $slug = $_POST['project_slug'];
        $new_status = $_POST['status'];
        
        if (isset($projects[$slug])) {
            $projects[$slug]['status'] = $new_status;
            updateProjectsData($projects);
            header('Location: projects-new.php?success=status_updated');
            exit;
        }
    }
}

function updateProjectsData($projects) {
    $data = "<?php\n/**\n * Projects Data\n */\n\n";
    $data .= "// Include project status management\nrequire_once __DIR__ . '/project-status.php';\n\n";
    $data .= "\$projects = [\n";
    
    foreach ($projects as $slug => $project) {
        $data .= "    '$slug' => [\n";
        foreach ($project as $key => $value) {
            if ($key === 'specs') {
                $data .= "        '$key' => [\n";
                foreach ($value as $spec_key => $spec_value) {
                    $data .= "            '$spec_key' => '" . addslashes($spec_value) . "',\n";
                }
                $data .= "        ],\n";
            } else {
                $data .= "        '$key' => '" . addslashes($value) . "',\n";
            }
        }
        $data .= "    ],\n";
    }
    
    $data .= "];\n\n?>";
    
    $file_path = __DIR__ . '/../config/projects-data.php';
    file_put_contents($file_path, $data);
    return true;
}

function updateTeamData($ceo_profile, $team_members) {
    $data = "<?php\n/**\n * Team Data\n *\n * This file is written by the admin panel (projects-new.php?page=team).\n */\n\n";
    $data .= "\$ceo_profile = " . var_export($ceo_profile, true) . ";\n\n";
    $data .= "\$team_members = " . var_export($team_members, true) . ";\n\n";
    $data .= "?>\n";

    $file_path = __DIR__ . '/../config/team-data.php';
    file_put_contents($file_path, $data);
    return true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diar360 Admin - Projects</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --card: 0 0% 100%;
            --border: 214 32% 91%;
            --foreground: 215 28% 17%;
            --muted: 210 40% 96%;
            --muted-foreground: 215 16% 47%;
            --primary: 214 78% 35%;
            --primary-foreground: 0 0% 100%;
            --destructive: 0 73% 51%;
            --sidebar-background: 214 78% 35%;
            --sidebar-foreground: 0 0% 100%;
            --sidebar-accent: 214 70% 42%;
            --sidebar-accent-foreground: 0 0% 100%;
            --sidebar-border: 214 45% 50%;
            --status-completed: 142 65% 36%;
            --status-in-progress: 217 91% 60%;
            --status-planning: 43 96% 56%;
            --status-on-hold: 220 9% 46%;
            --font-heading: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #F8FAFC; color: #1E293B; line-height: 1.6; }
        
        .min-h-screen { min-height: 100vh; }
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .gap-4 { gap: 16px; }
        .w-full { width: 100%; }
        .h-full { height: 100%; }
        .max-w-md { max-width: 448px; }
        .p-4 { padding: 16px; }
        .p-6 { padding: 24px; }
        .p-8 { padding: 32px; }
        .px-3 { padding-left: 12px; padding-right: 12px; }
        .px-4 { padding-left: 16px; padding-right: 16px; }
        .px-6 { padding-left: 24px; padding-right: 24px; }
        .py-2 { padding-top: 8px; padding-bottom: 8px; }
        .py-2\.5 { padding-top: 10px; padding-bottom: 10px; }
        .py-4 { padding-top: 16px; padding-bottom: 16px; }
        .mb-4 { margin-bottom: 16px; }
        .mt-4 { margin-top: 16px; }
        .ml-auto { margin-left: auto; }
        .text-center { text-align: center; }
        .text-sm { font-size: 14px; }
        .text-lg { font-size: 18px; }
        .text-xl { font-size: 20px; }
        .text-2xl { font-size: 24px; }
        .font-bold { font-weight: 700; }
        .font-medium { font-weight: 500; }
        .font-heading { font-weight: 600; }
        .rounded-lg { border-radius: 8px; }
        .rounded-xl { border-radius: 12px; }
        .border { border: 1px solid #E2E8F0; }
        .border-b { border-bottom: 1px solid #E2E8F0; }
        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .transition-all { transition: all 0.3s ease; }
        .transition-colors { transition: color 0.2s ease, background-color 0.2s ease; }
        .hidden { display: none; }
        .block { display: block; }
        .relative { position: relative; }
        .absolute { position: absolute; }
        .fixed { position: fixed; }
        .inset-0 { top: 0; right: 0; bottom: 0; left: 0; }
        .top-1\/2 { top: 50%; }
        .right-3 { right: 12px; }
        .bottom-4 { bottom: 16px; }
        .z-10 { z-index: 10; }
        .z-40 { z-index: 40; }
        .z-50 { z-index: 50; }
        .transform { transform: translateY(-50%); }
        .-translate-y-1\/2 { transform: translateY(-50%); }
        .shrink-0 { flex-shrink: 0; }
        .whitespace-nowrap { white-space: nowrap; }
        .h-10 { height: 40px; }
        .h-16 { height: 64px; }
        .h-12 { height: 48px; }
        .w-10 { width: 40px; }
        .w-12 { width: 48px; }
        .w-5 { width: 20px; }
        
        .bg-background { background-color: #F8FAFC; }
        .bg-card { background-color: #FFFFFF; }
        .bg-primary { background-color: #13529D; }
        .bg-sidebar { background-color: #13529D; }
        .bg-muted { background-color: #F1F5F9; }
        .bg-red-50 { background-color: #FEF2F2; }
        .bg-yellow-100 { background-color: #FEF3C7; }
        .bg-gray-300 { background-color: #D1D5DB; }
        .bg-orange-500 { background-color: #F97316; }
        .bg-orange-600 { background-color: #EA580C; }
        .bg-blue-600 { background-color: #2563EB; }
        .bg-blue-700 { background-color: #1D4ED8; }
        .bg-gray-700 { background-color: #374151; }
        .bg-black { background-color: #000000; }
        .bg-black\/50 { background-color: rgba(0, 0, 0, 0.5); }
        
        .text-foreground { color: #1E293B; }
        .text-card-foreground { color: #1E293B; }
        .text-primary-foreground { color: #FFFFFF; }
        .text-sidebar-foreground { color: #FFFFFF; }
        .text-muted-foreground { color: #64748B; }
        .text-red-500 { color: #EF4444; }
        .text-red-800 { color: #991B1B; }
        .text-yellow-600 { color: #D97706; }
        .text-white { color: #FFFFFF; }
        .text-destructive { color: #DC2626; }
        
        .border-border { border-color: #E2E8F0; }
        .border-input { border-color: #D1D5DB; }
        .border-red-200 { border-color: #FECACA; }
        .ring-ring { --tw-ring-color: #13529D; }
        
        .hover\:bg-primary:hover { background-color: #0F4C81; }
        .hover\:bg-destructive\/20:hover { background-color: rgba(220, 38, 38, 0.2); }
        .hover\:bg-gray-400:hover { background-color: #9CA3AF; }
        .hover\:text-foreground:hover { color: #1E293B; }
        
        .focus\:outline-none:focus { outline: none; }
        .focus\:ring-2:focus { box-shadow: 0 0 0 2px var(--tw-ring-color); }
        .focus\:ring-ring:focus { box-shadow: 0 0 0 2px #13529D; }
        
        .sidebar { position: fixed; top: 0; left: 0; width: 280px; height: 100vh; background-color: #13529D; color: #FFFFFF; z-index: 40; transition: transform 0.3s ease; }
        .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 30; display: none; }
        .main-content-shifted { margin-left: 280px; transition: margin-left 0.3s ease; }
        
        button { cursor: pointer; border: none; border-radius: 6px; font-weight: 500; transition: all 0.2s ease; }
        .btn-primary { background-color: #13529D; color: #FFFFFF; padding: 8px 16px; }
        .btn-primary:hover { background-color: #0F4C81; }
        .btn-secondary { background-color: #6B7280; color: #FFFFFF; padding: 8px 16px; }
        .btn-secondary:hover { background-color: #4B5563; }
        
        input, textarea, select { width: 100%; padding: 8px 12px; border: 1px solid #D1D5DB; border-radius: 6px; background-color: #FFFFFF; color: #1E293B; font-size: 14px; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: #13529D; box-shadow: 0 0 0 3px rgba(19, 82, 157, 0.1); }
        
        .card { background-color: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; padding: 16px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }
        
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content-shifted { margin-left: 0; }
            .mobile-header-btn { display: flex !important; }
            .sidebar-text { display: none; }
        }
        
        .cursor-pointer { cursor: pointer; }
        .select-none { user-select: none; }
        .overflow-hidden { overflow: hidden; }
        .opacity-0 { opacity: 0; }
        .opacity-100 { opacity: 1; }
        .space-y-4 > * + * { margin-top: 16px; }
        .space-x-3 > * + * { margin-left: 12px; }
        .py-2\.5 { padding-top: 10px; padding-bottom: 10px; }
        .py-4 { padding-top: 16px; padding-bottom: 16px; }
        .mb-4 { margin-bottom: 16px; }
        .mt-4 { margin-top: 16px; }
        .ml-auto { margin-left: auto; }
        .text-center { text-align: center; }
        .text-sm { font-size: 14px; }
        .text-lg { font-size: 18px; }
        .text-xl { font-size: 20px; }
        .text-2xl { font-size: 24px; }
        .font-bold { font-weight: 700; }
        .font-medium { font-weight: 500; }
        .font-heading { font-weight: 600; }
        .rounded-lg { border-radius: 8px; }
        .rounded-xl { border-radius: 12px; }
        .border { border: 1px solid #E2E8F0; }
        .border-b { border-bottom: 1px solid #E2E8F0; }
        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .transition-all { transition: all 0.3s ease; }
        .transition-colors { transition: color 0.2s ease, background-color 0.2s ease; }
        .hidden { display: none; }
        .block { display: block; }
        .relative { position: relative; }
        .absolute { position: absolute; }
        .fixed { position: fixed; }
        .inset-0 { top: 0; right: 0; bottom: 0; left: 0; }
        .top-1\/2 { top: 50%; }
        .right-3 { right: 12px; }
        .bottom-4 { bottom: 16px; }
        .z-10 { z-index: 10; }
        .z-40 { z-index: 40; }
        .z-50 { z-index: 50; }
        .transform { transform: translateY(-50%); }
        .-translate-y-1\/2 { transform: translateY(-50%); }
        .shrink-0 { flex-shrink: 0; }
        .whitespace-nowrap { white-space: nowrap; }
        .h-10 { height: 40px; }
        .h-16 { height: 64px; }
        .h-12 { height: 48px; }
        .w-10 { width: 40px; }
        .w-12 { width: 48px; }
        .w-5 { width: 20px; }
        
        /* Background Colors */
        .bg-background { background-color: #F8FAFC; }
        .bg-card { background-color: #FFFFFF; }
        .bg-primary { background-color: #13529D; }
        .bg-sidebar { background-color: #13529D; }
        .bg-muted { background-color: #F1F5F9; }
        .bg-red-50 { background-color: #FEF2F2; }
        .bg-yellow-100 { background-color: #FEF3C7; }
        .bg-gray-300 { background-color: #D1D5DB; }
        .bg-orange-500 { background-color: #F97316; }
        .bg-orange-600 { background-color: #EA580C; }
        .bg-blue-600 { background-color: #2563EB; }
        .bg-blue-700 { background-color: #1D4ED8; }
        .bg-gray-700 { background-color: #374151; }
        .bg-black { background-color: #000000; }
        .bg-black\/50 { background-color: rgba(0, 0, 0, 0.5); }
        
        /* Text Colors */
        .text-foreground { color: #1E293B; }
        .text-card-foreground { color: #1E293B; }
        .text-primary-foreground { color: #FFFFFF; }
        .text-sidebar-foreground { color: #FFFFFF; }
        .text-muted-foreground { color: #64748B; }
        .text-red-500 { color: #EF4444; }
        .text-red-800 { color: #991B1B; }
        .text-yellow-600 { color: #D97706; }
        .text-white { color: #FFFFFF; }
        .text-destructive { color: #DC2626; }
        
        /* Border Colors */
        .border-border { border-color: #E2E8F0; }
        .border-input { border-color: #D1D5DB; }
        .border-red-200 { border-color: #FECACA; }
        .ring-ring { --tw-ring-color: #13529D; }
        
        /* Hover States */
        .hover\:bg-primary:hover { background-color: #0F4C81; }
        .hover\:bg-destructive\/20:hover { background-color: rgba(220, 38, 38, 0.2); }
        .hover\:bg-gray-400:hover { background-color: #9CA3AF; }
        .hover\:text-foreground:hover { color: #1E293B; }
        
        /* Focus States */
        .focus\:outline-none:focus { outline: none; }
        .justify-center { justify-content: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .gap-4 { gap: 16px; }
        .w-full { width: 100%; }
        .h-full { height: 100%; }
        .max-w-md { max-width: 448px; }
        .p-4 { padding: 16px; }
        .p-6 { padding: 24px; }
        .p-8 { padding: 32px; }
        .px-3 { padding-left: 12px; padding-right: 12px; }
        .px-4 { padding-left: 16px; padding-right: 16px; }
        .px-6 { padding-left: 24px; padding-right: 24px; }
        .py-2 { padding-top: 8px; padding-bottom: 8px; }
        .py-2\.5 { padding-top: 10px; padding-bottom: 10px; }
        .py-4 { padding-top: 16px; padding-bottom: 16px; }
        .mb-4 { margin-bottom: 16px; }
        .mt-4 { margin-top: 16px; }
        .ml-auto { margin-left: auto; }
        .text-center { text-align: center; }
        .text-sm { font-size: 14px; }
        .text-lg { font-size: 18px; }
        .text-xl { font-size: 20px; }
        .text-2xl { font-size: 24px; }
        .font-bold { font-weight: 700; }
        .font-medium { font-weight: 500; }
        .font-heading { font-weight: 600; }
        .rounded-lg { border-radius: 8px; }
        .rounded-xl { border-radius: 12px; }
        .border { border: 1px solid #E2E8F0; }
        .border-b { border-bottom: 1px solid #E2E8F0; }
        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .transition-all { transition: all 0.3s ease; }
        .transition-colors { transition: color 0.2s ease, background-color 0.2s ease; }
        .hidden { display: none; }
        .block { display: block; }
        .relative { position: relative; }
        .absolute { position: absolute; }
        .fixed { position: fixed; }
        .inset-0 { top: 0; right: 0; bottom: 0; left: 0; }
        .top-1\/2 { top: 50%; }
        .right-3 { right: 12px; }
        .bottom-4 { bottom: 16px; }
        .z-10 { z-index: 10; }
        .z-40 { z-index: 40; }
        .z-50 { z-index: 50; }
        .transform { transform: translateY(-50%); }
        .-translate-y-1\/2 { transform: translateY(-50%); }
        .shrink-0 { flex-shrink: 0; }
        .whitespace-nowrap { white-space: nowrap; }
        .h-10 { height: 40px; }
        .h-16 { height: 64px; }
        .h-12 { height: 48px; }
        .w-10 { width: 40px; }
        .w-12 { width: 48px; }
        .w-5 { width: 20px; }
        
        /* Background Colors */
        .bg-background { background-color: #F8FAFC; }
        .bg-card { background-color: #FFFFFF; }
        .bg-primary { background-color: #13529D; }
        .bg-sidebar { background-color: #13529D; }
        .bg-muted { background-color: #F1F5F9; }
        .bg-red-50 { background-color: #FEF2F2; }
        .bg-yellow-100 { background-color: #FEF3C7; }
        .bg-gray-300 { background-color: #D1D5DB; }
        .bg-orange-500 { background-color: #F97316; }
        .bg-orange-600 { background-color: #EA580C; }
        .bg-blue-600 { background-color: #2563EB; }
        .bg-blue-700 { background-color: #1D4ED8; }
        .bg-gray-700 { background-color: #374151; }
        .bg-black { background-color: #000000; }
        .bg-black\/50 { background-color: rgba(0, 0, 0, 0.5); }
        
        /* Text Colors */
        .text-foreground { color: #1E293B; }
        .text-card-foreground { color: #1E293B; }
        .text-primary-foreground { color: #FFFFFF; }
        .text-sidebar-foreground { color: #FFFFFF; }
        .text-muted-foreground { color: #64748B; }
        .text-red-500 { color: #EF4444; }
        .text-red-800 { color: #991B1B; }
        .text-yellow-600 { color: #D97706; }
        .text-white { color: #FFFFFF; }
        .text-destructive { color: #DC2626; }
        
        /* Border Colors */
        .border-border { border-color: #E2E8F0; }
        .border-input { border-color: #D1D5DB; }
        .border-red-200 { border-color: #FECACA; }
        .ring-ring { --tw-ring-color: #13529D; }
        
        /* Hover States */
        .hover\:bg-primary:hover { background-color: #0F4C81; }
        .hover\:bg-destructive\/20:hover { background-color: rgba(220, 38, 38, 0.2); }
        .hover\:bg-gray-400:hover { background-color: #9CA3AF; }
        .hover\:text-foreground:hover { color: #1E293B; }
        
        /* Focus States */
        .focus\:outline-none:focus { outline: none; }
        .focus\:ring-2:focus { box-shadow: 0 0 0 2px var(--tw-ring-color); }
        .focus\:ring-ring:focus { box-shadow: 0 0 0 2px #13529D; }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            background-color: #13529D;
            color: #FFFFFF;
            z-index: 40;
            transition: transform 0.3s ease;
        }
        
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 30;
            display: none;
        }
        
        .main-content-shifted {
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }
        
        /* Card Styles */
        .card {
            background: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
        }
        
        .bg-card {
            background: hsl(var(--card));
        }
        
        .bg-sidebar {
            background: hsl(var(--sidebar-background));
        }
        
        .text-sidebar-foreground {
            color: hsl(var(--sidebar-foreground));
        }
        
        .bg-sidebar-accent {
            background: hsl(var(--sidebar-accent));
        }
        
        .text-sidebar-accent-foreground {
            color: hsl(var(--sidebar-accent-foreground));
        }
        
        .border-sidebar-border {
            border-color: hsl(var(--sidebar-border));
        }
        
        .text-muted-foreground {
            color: hsl(var(--muted-foreground));
        }
        
        .bg-muted {
            background: hsl(var(--muted));
        }
        
        .bg-primary {
            background: hsl(var(--primary));
        }
        
        .text-primary-foreground {
            color: hsl(var(--primary-foreground));
        }
        
        .bg-destructive {
            background: hsl(var(--destructive));
        }
        
        .text-destructive {
            color: hsl(var(--destructive));
        }
        
        .status-completed {
            background: hsl(var(--status-completed));
            color: white;
        }
        
        .status-in-progress {
            background: hsl(var(--status-in-progress));
            color: white;
        }
        
        .status-planning {
            background: hsl(var(--status-planning));
            color: white;
        }
        
        .status-on-hold {
            background: hsl(var(--status-on-hold));
            color: white;
        }

        /* Dashboard statistic card styles */
        .stats-card {
            border: 1px solid hsl(var(--border));
            border-left-width: 4px;
            border-radius: 0.85rem;
            transition: transform 0.2s ease, box-shadow 0.25s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(15, 42, 73, 0.12);
        }

        .stats-card-total {
            border-left-color: #13529D;
            background: linear-gradient(135deg, rgba(19, 82, 157, 0.08), rgba(19, 82, 157, 0.02));
        }

        .stats-card-completed {
            border-left-color: #1f8f45;
            background: linear-gradient(135deg, rgba(31, 143, 69, 0.1), rgba(31, 143, 69, 0.02));
        }

        .stats-card-progress {
            border-left-color: #f59e0b;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.14), rgba(245, 158, 11, 0.03));
        }

        .stats-card-planning {
            border-left-color: #3b82f6;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(59, 130, 246, 0.03));
        }

        .stats-card-hold {
            border-left-color: #6b7280;
            background: linear-gradient(135deg, rgba(107, 114, 128, 0.16), rgba(107, 114, 128, 0.03));
        }

        .stats-value {
            transition: transform 0.2s ease;
        }

        .stats-card:hover .stats-value {
            transform: scale(1.04);
        }

        /* Pagination */
        .pagination-btn {
            min-width: 2.2rem;
            height: 2.2rem;
            padding: 0 0.55rem;
            border-radius: 0.6rem;
            border: 1px solid hsl(var(--border));
            background: hsl(var(--card));
            color: hsl(var(--foreground));
            font-size: 0.82rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .pagination-btn:hover:not(:disabled) {
            background: rgba(19, 82, 157, 0.08);
            border-color: rgba(19, 82, 157, 0.35);
        }

        .pagination-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .pagination-btn.active {
            background: #13529D;
            border-color: #13529D;
            color: #fff;
        }

        /* Session/security modal */
        .session-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 42, 73, 0.55);
            backdrop-filter: blur(2px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1200;
            padding: 1rem;
        }

        .session-modal-overlay.active {
            display: flex;
        }

        .session-modal-card {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            border: 1px solid rgba(19, 82, 157, 0.2);
            border-radius: 14px;
            box-shadow: 0 18px 35px rgba(15, 42, 73, 0.25);
            padding: 1.1rem 1.1rem 1rem;
            text-align: center;
        }

        .session-modal-title {
            font-family: var(--font-heading);
            color: #0F2A49;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.45rem;
        }

        .session-modal-message {
            color: #274668;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 0.9rem;
        }

        .session-modal-countdown {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 56px;
            height: 32px;
            border-radius: 999px;
            background: rgba(220, 38, 38, 0.12);
            color: #b91c1c;
            font-weight: 700;
            margin-bottom: 0.9rem;
            padding: 0 0.8rem;
        }

        .session-modal-actions {
            display: flex;
            gap: 0.55rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .session-btn {
            border: 1px solid transparent;
            border-radius: 0.6rem;
            font-size: 0.86rem;
            font-weight: 600;
            padding: 0.55rem 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .session-btn-primary {
            background: #13529D;
            color: #fff;
        }

        .session-btn-primary:hover {
            background: #0f4688;
        }

        .session-btn-muted {
            background: #fff;
            border-color: #d1d5db;
            color: #374151;
        }

        .session-btn-muted:hover {
            background: #f9fafb;
        }
        
        /* Custom status dropdown styling */
        .status-dropdown {
            background-color: white !important;
            border: 1px solid hsl(var(--border));
        }
        
        .status-dropdown option {
            background-color: white;
            color: hsl(var(--foreground));
            padding: 8px 12px;
        }
        
        /* Hover colors for options */
        .status-dropdown option:hover {
            background-color: #f3f4f6 !important;
        }
        
        .status-dropdown option[value="completed"]:hover {
            background-color: #28a745 !important;
            color: white !important;
        }
        
        .status-dropdown option[value="in-progress"]:hover {
            background-color: #007bff !important;
            color: white !important;
        }
        
        .status-dropdown option[value="planning"]:hover {
            background-color: #ffc107 !important;
            color: white !important;
        }
        
        .status-dropdown option[value="on-hold"]:hover {
            background-color: #6c757d !important;
            color: white !important;
        }
        
        /* Selected state colors for dropdown */
        .status-dropdown.completed-selected {
            background-color: #28a745 !important;
            color: white !important;
        }
        
        .status-dropdown.in-progress-selected {
            background-color: #007bff !important;
            color: white !important;
        }
        
        .status-dropdown.planning-selected {
            background-color: #ffc107 !important;
            color: white !important;
        }
        
        .status-dropdown.on-hold-selected {
            background-color: #6c757d !important;
            color: white !important;
        }
        
        /* Hover effects for selected dropdown */
        .status-dropdown.completed-selected:hover {
            background-color: #218838 !important;
            color: white !important;
        }
        
        .status-dropdown.in-progress-selected:hover {
            background-color: #0056b3 !important;
            color: white !important;
        }
        
        .status-dropdown.planning-selected:hover {
            background-color: #e0a800 !important;
            color: white !important;
        }
        
        .status-dropdown.on-hold-selected:hover {
            background-color: #545b62 !important;
            color: white !important;
        }
        
        .transition-all {
            transition: all 0.3s ease;
        }
        
        .sidebar-collapsed {
            width: 72px;
        }
        
        .sidebar-expanded {
            width: 260px;
        }
        
        .main-content-shifted {
            margin-left: 260px;
        }
        
        .main-content-collapsed {
            margin-left: 72px;
        }

        /* Project add/edit modal cleanup */
        #project-modal .modal-shell {
            width: min(1100px, calc(100vw - 2rem));
            max-height: min(92vh, 920px);
            overflow-y: auto;
            border-radius: 14px;
        }

        #project-modal .modal-header {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.25rem;
        }

        #project-modal .modal-body {
            padding: 1.25rem;
        }

        #project-modal .form-section {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            background: #ffffff;
            margin-bottom: 1rem;
        }

        #project-modal .form-section-title {
            font-size: 0.92rem;
            font-weight: 700;
            color: #0f2a49;
            margin-bottom: 0.85rem;
        }

        #project-modal input[type="text"],
        #project-modal textarea,
        #project-modal select {
            min-height: 42px;
        }

        #project-modal textarea {
            min-height: 120px;
            resize: vertical;
        }

        #project-modal input[type="file"] {
            font-size: 0.86rem;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #f8fafc;
            padding: 0.35rem;
        }

        #project-modal input[type="file"]::file-selector-button {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f2a49;
            border-radius: 8px;
            padding: 0.42rem 0.7rem;
            margin-right: 0.7rem;
            cursor: pointer;
            font-weight: 600;
        }

        #project-modal .visibility-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        #project-modal .modal-footer {
            position: sticky;
            bottom: 0;
            z-index: 5;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 0.95rem 1.25rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        
        /* Responsive layout for admin panel */
        #sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 40;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        @media (max-width: 1024px) {
            #sidebar {
                width: 260px !important;
                transform: translateX(-100%);
                transition: transform 0.28s ease;
            }

            #sidebar.mobile-open {
                transform: translateX(0);
            }

            #main-content {
                margin-left: 0 !important;
            }

            #sidebar-overlay.active {
                opacity: 1;
                pointer-events: auto;
            }

            .sidebar-text {
                display: block !important;
            }

            .mobile-header-btn {
                display: inline-flex !important;
            }
        }

        @media (min-width: 1025px) {
            .mobile-header-btn {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            header.h-16 {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            main.flex-1 {
                padding: 1rem !important;
            }

            .tab-button {
                font-size: 0.8rem;
                padding-left: 0.25rem !important;
                padding-right: 0.25rem !important;
            }

            #projects-container .project-card {
                padding: 1rem !important;
            }

            .project-card {
                overflow: hidden;
            }

            .project-card h3 {
                font-size: 1rem !important;
                line-height: 1.3 !important;
            }

            .project-card .status-badge,
            .project-card .visibility-badge {
                font-size: 0.68rem !important;
                padding: 0.2rem 0.45rem !important;
                white-space: nowrap;
            }

            /* Keep description visible but compact on small screens */
            .project-card p.line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                margin-bottom: 0.5rem !important;
                font-size: 0.82rem !important;
                line-height: 1.35 !important;
            }

            .project-card .project-meta {
                gap: 0.4rem 0.85rem !important;
                font-size: 0.78rem !important;
            }

            .project-card .project-meta-scope {
                display: none;
            }

            .project-card .project-status-actions,
            .project-card .project-file-actions,
            .project-card .project-main-actions {
                width: 100%;
                margin-top: 0.25rem !important;
                flex-wrap: wrap;
            }

            .project-card .project-status-actions select {
                width: 100% !important;
                max-width: 100% !important;
            }

            .project-card .project-file-actions a,
            .project-card .project-main-actions button {
                flex: 1 1 auto;
                justify-content: center;
                min-height: 38px;
            }

            .project-card,
            .project-card * {
                word-break: break-word;
                overflow-wrap: anywhere;
            }

            /* Fit more summary cards in first viewport */
            .mobile-stats-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: 0.5rem !important;
            }

            .mobile-stats-grid > div {
                padding: 0.6rem !important;
            }

            .mobile-stats-grid p.text-2xl {
                font-size: 1.05rem !important;
                line-height: 1.1 !important;
            }

            .mobile-stats-grid p.text-sm {
                font-size: 0.72rem !important;
            }

            #pagination-controls {
                gap: 0.35rem !important;
            }

            #pagination-controls .pagination-btn {
                min-width: 2rem;
                height: 2rem;
                font-size: 0.76rem;
                padding: 0 0.42rem;
            }

            #project-modal .bg-card,
            #delete-modal .bg-card,
            #delete-pdf-modal .bg-card {
                width: calc(100vw - 1rem) !important;
                max-width: calc(100vw - 1rem) !important;
                margin: 0.5rem;
            }

            .session-modal-card {
                max-width: 100%;
                padding: 0.95rem 0.9rem;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-background">
    <?php if (!$is_authenticated): ?>
    <!-- Login Page -->
    <div class="min-h-screen flex items-center justify-center bg-card">
        <div class="max-w-md w-full p-8 bg-card border border-border rounded-xl">
            <div class="text-center mb-8">
                <div class="h-12 w-12 bg-primary rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-building text-primary-foreground text-xl"></i>
                </div>
                <h2 class="text-2xl font-bold font-heading text-foreground">Diar360 Admin</h2>
                <p class="text-muted-foreground mt-2">Project Management Dashboard</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                        <div>
                            <?php
                            switch ($_GET['error']) {
                                case 'invalid_login':
                                    echo 'Incorrect password. Please try again.';
                                    break;
                                case 'csrf':
                                    echo 'Security token expired or invalid. Please try logging in again.';
                                    break;
                                default:
                                    echo 'Login failed. Please try again.';
                                    break;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="post" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div>
                    <label for="password" class="block text-sm font-medium text-foreground mb-2">Admin Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required 
                               class="w-full px-3 py-2 pr-10 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                        <button type="button" onclick="togglePassword('password', 'login_password_eye')" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-muted-foreground hover:text-foreground focus:outline-none">
                            <i id="login_password_eye" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" name="login" 
                        class="w-full bg-primary text-primary-foreground py-2 px-4 rounded-lg hover:bg-primary/90 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </button>
            </form>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Admin Dashboard -->
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed left-0 top-0 h-screen bg-sidebar text-sidebar-foreground flex flex-col z-50 overflow-hidden sidebar-expanded transition-all duration-300">
        <!-- Logo + Collapse Toggle -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-sidebar-border">
            <div class="flex items-center min-w-0">
                <div class="h-8 w-8 bg-secondary rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-secondary-foreground"></i>
                </div>
                <span class="sidebar-text ml-3 text-xl font-heading font-bold whitespace-nowrap">Diar360</span>
            </div>
            <button onclick="toggleSidebar()" class="h-8 w-8 shrink-0 flex items-center justify-center rounded-lg text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground transition-colors">
                <i id="sidebar-toggle-icon" class="fas fa-chevron-left text-sm"></i>
            </button>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 py-4 px-2 space-y-1">
            <a href="projects-new.php" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground">
                <i class="fas fa-chart-line h-5 w-5 shrink-0"></i>
                <span class="sidebar-text text-sm font-medium whitespace-nowrap">Dashboard</span>
            </a>
            <a href="projects-new.php?page=team" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground">
                <i class="fas fa-users h-5 w-5 shrink-0"></i>
                <span class="sidebar-text text-sm font-medium whitespace-nowrap">Team</span>
            </a>
            <a href="projects-new.php?page=careers" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground">
                <i class="fas fa-briefcase h-5 w-5 shrink-0"></i>
                <span class="sidebar-text text-sm font-medium whitespace-nowrap">Careers</span>
            </a>
            <a href="projects-new.php?page=certifications" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground">
                <i class="fas fa-certificate h-5 w-5 shrink-0"></i>
                <span class="sidebar-text text-sm font-medium whitespace-nowrap">Certifications</span>
            </a>
            <a href="projects-new.php?page=testimonials" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground">
                <i class="fas fa-comment-dots h-5 w-5 shrink-0"></i>
                <span class="sidebar-text text-sm font-medium whitespace-nowrap">Testimonials</span>
                <?php if (!empty($pending_testimonials_count)): ?>
                    <span class="ml-auto inline-flex items-center justify-center text-xs font-bold rounded-full bg-amber-500 text-white w-6 h-6">
                        <?php echo (int)$pending_testimonials_count; ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="projects-new.php?page=settings" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground">
                <i class="fas fa-cog h-5 w-5 shrink-0"></i>
                <span class="sidebar-text text-sm font-medium whitespace-nowrap">Settings</span>
            </a>
        </nav>
        
        <!-- Logout -->
        <div class="border-t border-sidebar-border p-2">
            <a href="?logout=true" class="sidebar-logout flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sidebar-foreground/70 hover:bg-destructive/20 hover:text-destructive transition-colors">
                <i class="fas fa-sign-out-alt h-5 w-5 shrink-0"></i>
                <span class="sidebar-text text-sm font-medium whitespace-nowrap">Logout</span>
            </a>
        </div>
    </aside>
    
    <div id="sidebar-overlay" onclick="closeMobileSidebar()"></div>

    <!-- Main Content -->
    <div id="main-content" class="main-content-shifted min-h-screen flex flex-col transition-all duration-300">
        <!-- Top Navbar -->
        <header class="h-16 border-b border-border bg-card flex items-center justify-between px-6 shrink-0">
            <button type="button" onclick="toggleSidebar()" class="mobile-header-btn hidden h-10 w-10 items-center justify-center rounded-lg border border-border bg-background text-foreground">
                <i class="fas fa-bars"></i>
            </button>
            <div class="flex items-center gap-3 ml-auto">
                <div class="flex items-center gap-2 rounded-lg px-2 py-1.5">
                    <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center">
                        <i class="fas fa-user text-primary-foreground"></i>
                    </div>
                    <span class="text-sm font-medium text-foreground">Admin</span>
                </div>
            </div>
        </header>
        
        <!-- Main Content Area -->
        <main class="flex-1 p-6 lg:p-8 max-w-7xl">
            <?php if (isset($_GET['success'])): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span class="text-green-800">
                            <?php
                            switch ($_GET['success']) {
                                case 'added': echo 'Project created successfully'; break;
                                case 'project_updated': echo 'Project updated successfully'; break;
                                case 'deleted': echo 'Project deleted successfully'; break;
                                case 'status_updated': echo 'Status updated successfully'; break;
                                case 'settings_updated': echo 'Settings updated successfully'; break;
                                case 'restored': echo 'Backup restored successfully'; break;
                                case 'backup_created': echo 'Backup created successfully'; break;
                                case 'password_changed': echo 'Password changed successfully! Please login with your new password.'; break;
                                case 'team_updated': echo 'Team settings updated successfully'; break;
                                case 'member_added': echo 'Team member added successfully'; break;
                                case 'member_updated': echo 'Team member updated successfully'; break;
                                case 'member_deleted': echo 'Team member deleted successfully'; break;
                                case 'job_added': echo 'Job post created successfully'; break;
                                case 'job_updated': echo 'Job post updated successfully'; break;
                                case 'job_deleted': echo 'Job post deleted successfully'; break;
                                case 'partners_updated': echo 'Global partners updated successfully'; break;
                                case 'cert_cards_updated': echo 'Certification cards updated successfully'; break;
                                case 'testimonials_updated': echo 'Testimonials updated successfully'; break;
                            }
                            ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <div class="text-red-800">
                            <?php
                            switch ($_GET['error']) {
                                case 'password_mismatch': 
                                    echo 'Passwords do not match'; 
                                    break;
                                case 'password_validation': 
                                    echo 'Password validation failed:';
                                    if (isset($_SESSION['password_errors']) && is_array($_SESSION['password_errors'])) {
                                        echo '<ul class="mt-2 list-disc list-inside">';
                                        foreach ($_SESSION['password_errors'] as $error) {
                                            echo '<li>' . htmlspecialchars($error) . '</li>';
                                        }
                                        echo '</ul>';
                                        unset($_SESSION['password_errors']);
                                    }
                                    break;
                                case 'invalid_backup': 
                                    echo 'Invalid backup file'; 
                                    break;
                                case 'csrf':
                                    echo 'Security token expired or invalid. Please try logging in again.';
                                    break;
                                default: 
                                    echo 'An error occurred'; 
                                    break;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($current_page === 'settings'): ?>
                <!-- Settings Page -->
                <div class="space-y-8">
                    <!-- Header -->
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-heading font-bold text-foreground">Settings</h1>
                        <p class="text-muted-foreground mt-1">Manage your admin panel and site configuration</p>
                    </div>
                    
                    <!-- Settings Tabs -->
                    <div class="border-b border-border">
                        <nav class="-mb-px flex space-x-8">
                            <button class="tab-button py-2 px-1 border-b-2 border-primary text-primary font-medium text-sm" onclick="showTab('general', this)">
                                General
                            </button>
                            <button class="tab-button py-2 px-1 border-b-2 border-transparent text-muted-foreground hover:text-foreground font-medium text-sm" onclick="showTab('security', this)">
                                Security
                            </button>
                            <button class="tab-button py-2 px-1 border-b-2 border-transparent text-muted-foreground hover:text-foreground font-medium text-sm" onclick="showTab('backup', this)">
                                Backup
                            </button>
                        </nav>
                    </div>
                    
                    <!-- General Settings -->
                    <div id="general-tab" class="tab-content" style="display: block !important;">
                        <div class="bg-card rounded-xl border border-border p-6">
                            <h3 class="text-lg font-heading font-bold text-foreground mb-6">General Settings</h3>
                            
                            <form method="post" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="site_name" class="block text-sm font-medium text-foreground mb-2">Site Name</label>
                                        <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars(isset($site_settings['site_name']) ? $site_settings['site_name'] : 'Diar360'); ?>" 
                                               class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label for="admin_email" class="block text-sm font-medium text-foreground mb-2">Admin Email</label>
                                        <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars(isset($site_settings['admin_email']) ? $site_settings['admin_email'] : 'info@diar360.com'); ?>" 
                                               class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label for="company_phone" class="block text-sm font-medium text-foreground mb-2">Company Phone</label>
                                        <input type="tel" id="company_phone" name="company_phone" value="<?php echo htmlspecialchars(isset($site_settings['company_phone']) ? $site_settings['company_phone'] : '+966 1 1 296 7735'); ?>" 
                                               class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label for="company_address" class="block text-sm font-medium text-foreground mb-2">Company Address</label>
                                        <input type="text" id="company_address" name="company_address" value="<?php echo htmlspecialchars(isset($site_settings['company_address']) ? $site_settings['company_address'] : 'Prince Mohammed Ibn Salman Ibn Abdulaziz Rd, Al Falah Dist, Riyadh - KSA'); ?>" 
                                               class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                </div>
                                
                                <div class="flex items-center">
                                    <input type="checkbox" id="maintenance_mode" name="maintenance_mode" class="h-4 w-4 text-primary bg-background border-input rounded focus:ring-ring" 
                                           <?php echo (isset($site_settings['maintenance_mode']) && $site_settings['maintenance_mode'] === '1') ? 'checked' : ''; ?>>
                                    <label for="maintenance_mode" class="ml-2 text-sm text-foreground">
                                        Enable Maintenance Mode
                                        <?php if (isset($site_settings['maintenance_mode']) && $site_settings['maintenance_mode'] === '1'): ?>
                                            <span class="ml-2 text-xs text-orange-600">(Currently Active)</span>
                                        <?php endif; ?>
                                    </label>
                                </div>

                                <div class="bg-muted/30 rounded-lg p-4 border border-border">
                                    <h4 class="font-medium text-foreground mb-3">Homepage: Global Partners Block</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Title (EN)</label>
                                            <input type="text" name="global_partners_title_en" value="<?php echo htmlspecialchars($site_settings['global_partners_title_en'] ?? ''); ?>" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                            <p class="text-xs text-muted-foreground mt-1">Leave empty to use the default translation.</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Title (AR)</label>
                                            <input type="text" name="global_partners_title_ar" dir="rtl" value="<?php echo htmlspecialchars($site_settings['global_partners_title_ar'] ?? ''); ?>" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Description (EN)</label>
                                            <textarea name="global_partners_desc_en" rows="3" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"><?php echo htmlspecialchars($site_settings['global_partners_desc_en'] ?? ''); ?></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Description (AR)</label>
                                            <textarea name="global_partners_desc_ar" rows="3" dir="rtl" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"><?php echo htmlspecialchars($site_settings['global_partners_desc_ar'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <?php
                                $partners = is_array($site_settings['global_partners_items'] ?? null) ? $site_settings['global_partners_items'] : [];
                                $edit_partner_id = $_GET['edit_partner'] ?? '';
                                $is_partner_edit = is_string($edit_partner_id) && $edit_partner_id !== '' && isset($partners[$edit_partner_id]);
                                $partner = $is_partner_edit ? $partners[$edit_partner_id] : ['name_en' => '', 'name_ar' => '', 'url' => '', 'logo' => '', 'visible' => '1'];
                                ?>

                                <div class="bg-muted/30 rounded-lg p-4 border border-border">
                                    <div class="flex items-center justify-between gap-3 mb-3">
                                        <h4 class="font-medium text-foreground mb-0">Homepage: Global Partners (CRUD)</h4>
                                        <a class="text-sm text-primary underline" href="projects-new.php?page=settings">Reset form</a>
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        <div class="bg-card rounded-lg border border-border p-4">
                                            <h5 class="font-medium text-foreground mb-3"><?php echo $is_partner_edit ? 'Edit Partner' : 'Add Partner'; ?></h5>

                                            <form method="post" enctype="multipart/form-data" class="space-y-3">
                                                <?php if ($is_partner_edit): ?>
                                                    <input type="hidden" name="partner_id" value="<?php echo htmlspecialchars($edit_partner_id); ?>">
                                                <?php endif; ?>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-sm font-medium text-foreground mb-2">Name (EN)</label>
                                                        <input type="text" name="partner_name_en" value="<?php echo htmlspecialchars($partner['name_en'] ?? ''); ?>" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-foreground mb-2">Name (AR)</label>
                                                        <input type="text" name="partner_name_ar" dir="rtl" value="<?php echo htmlspecialchars($partner['name_ar'] ?? ''); ?>" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-foreground mb-2">Website URL (optional)</label>
                                                    <input type="text" name="partner_url" placeholder="https://example.com" value="<?php echo htmlspecialchars($partner['url'] ?? ''); ?>" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-foreground mb-2">Logo (optional)</label>
                                                    <input type="file" name="partner_logo" accept=".jpg,.jpeg,.png,.webp,.svg" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                                    <?php if (!empty($partner['logo'])): ?>
                                                        <p class="text-xs text-muted-foreground mt-1">Current: <?php echo htmlspecialchars($partner['logo']); ?></p>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <input id="partner-visible" type="checkbox" name="partner_visible" class="h-4 w-4 text-primary bg-background border-input rounded focus:ring-ring" <?php echo (($partner['visible'] ?? '0') === '1' || ($partner['visible'] ?? 0) === 1) ? 'checked' : ''; ?>>
                                                    <label for="partner-visible" class="text-sm text-foreground">Visible on homepage</label>
                                                </div>

                                                <div class="flex items-center justify-end gap-3 pt-2">
                                                    <?php if ($is_partner_edit): ?>
                                                        <a href="projects-new.php?page=settings" class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">Cancel</a>
                                                        <button type="submit" name="edit_partner" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                                            <i class="fas fa-save mr-2"></i>Update Partner
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="submit" name="add_partner" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                                            <i class="fas fa-plus mr-2"></i>Add Partner
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="bg-card rounded-lg border border-border p-4">
                                            <h5 class="font-medium text-foreground mb-3">Existing Partners</h5>

                                            <?php if (empty($partners)): ?>
                                                <div class="text-sm text-muted-foreground">No partners yet.</div>
                                            <?php else: ?>
                                                <div class="grid gap-2">
                                                    <?php foreach ($partners as $pid => $p): ?>
                                                        <div class="bg-background border border-border rounded-lg p-3 flex items-center justify-between gap-3">
                                                            <div class="min-w-0">
                                                                <div class="flex items-center gap-2 flex-wrap">
                                                                    <span class="font-medium text-foreground"><?php echo htmlspecialchars(($p['name_en'] ?? '') ?: ($p['name_ar'] ?? '') ?: $pid); ?></span>
                                                                    <span class="text-xs px-2 py-1 rounded-full <?php echo (($p['visible'] ?? '0') === '1' || ($p['visible'] ?? 0) === 1) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'; ?>">
                                                                        <?php echo (($p['visible'] ?? '0') === '1' || ($p['visible'] ?? 0) === 1) ? 'Visible' : 'Hidden'; ?>
                                                                    </span>
                                                                    <span class="text-xs text-muted-foreground break-all">ID: <?php echo htmlspecialchars($pid); ?></span>
                                                                </div>
                                                                <?php if (!empty($p['url'])): ?>
                                                                    <div class="text-xs text-muted-foreground break-all"><?php echo htmlspecialchars($p['url']); ?></div>
                                                                <?php endif; ?>
                                                            </div>

                                                            <div class="flex items-center gap-2 shrink-0">
                                                                <a href="projects-new.php?page=settings&edit_partner=<?php echo urlencode($pid); ?>" class="px-3 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors text-sm">
                                                                    <i class="fas fa-edit mr-2"></i>Edit
                                                                </a>
                                                                <form method="post" onsubmit="return confirm('Delete this partner?');">
                                                                    <input type="hidden" name="partner_id" value="<?php echo htmlspecialchars($pid); ?>">
                                                                    <button type="submit" name="delete_partner" class="px-3 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors text-sm text-destructive">
                                                                        <i class="fas fa-trash mr-2"></i>Delete
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <?php
                                $certCards = is_array($site_settings['certification_cards'] ?? null) ? $site_settings['certification_cards'] : [];
                                $edit_cert_id = $_GET['edit_cert'] ?? '';
                                $is_cert_edit = is_string($edit_cert_id) && $edit_cert_id !== '' && isset($certCards[$edit_cert_id]);
                                $cert = $is_cert_edit ? $certCards[$edit_cert_id] : [
                                    'title_en' => '',
                                    'title_ar' => '',
                                    'category_en' => '',
                                    'category_ar' => '',
                                    'desc_en' => '',
                                    'desc_ar' => '',
                                    'icon' => '',
                                    'visible' => '1',
                                    'order' => 10,
                                ];
                                ?>

                                <div class="bg-muted/30 rounded-lg p-4 border border-border">
                                    <div class="flex items-center justify-between gap-3 mb-3">
                                        <h4 class="font-medium text-foreground mb-0">Homepage: Certification Cards (CRUD)</h4>
                                        <div class="flex items-center gap-3">
                                            <form method="post">
                                                <button type="submit" name="init_cert_cards" class="text-sm px-3 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">
                                                    <i class="fas fa-wand-magic-sparkles mr-2"></i>Load Defaults
                                                </button>
                                            </form>
                                            <a class="text-sm text-primary underline" href="projects-new.php?page=settings">Reset form</a>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        <div class="bg-card rounded-lg border border-border p-4">
                                            <h5 class="font-medium text-foreground mb-3"><?php echo $is_cert_edit ? 'Edit Card' : 'Add Card'; ?></h5>

                                            <form method="post" enctype="multipart/form-data" class="space-y-3">
                                                <?php if ($is_cert_edit): ?>
                                                    <input type="hidden" name="cert_id" value="<?php echo htmlspecialchars($edit_cert_id); ?>">
                                                <?php endif; ?>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-sm font-medium text-foreground mb-2">Title (EN)</label>
                                                        <input type="text" name="cert_title_en" value="<?php echo htmlspecialchars($cert['title_en'] ?? ''); ?>" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-foreground mb-2">Title (AR)</label>
                                                        <input type="text" name="cert_title_ar" dir="rtl" value="<?php echo htmlspecialchars($cert['title_ar'] ?? ''); ?>" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-sm font-medium text-foreground mb-2">Category (EN)</label>
                                                        <input type="text" name="cert_category_en" value="<?php echo htmlspecialchars($cert['category_en'] ?? ''); ?>" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-foreground mb-2">Category (AR)</label>
                                                        <input type="text" name="cert_category_ar" dir="rtl" value="<?php echo htmlspecialchars($cert['category_ar'] ?? ''); ?>" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-sm font-medium text-foreground mb-2">Description (EN)</label>
                                                        <textarea name="cert_desc_en" rows="3" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"><?php echo htmlspecialchars($cert['desc_en'] ?? ''); ?></textarea>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-foreground mb-2">Description (AR)</label>
                                                        <textarea name="cert_desc_ar" rows="3" dir="rtl" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"><?php echo htmlspecialchars($cert['desc_ar'] ?? ''); ?></textarea>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-sm font-medium text-foreground mb-2">Icon image (optional)</label>
                                                        <input type="file" name="cert_icon" accept=".jpg,.jpeg,.png,.webp,.svg" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                                        <input type="hidden" name="cert_icon_existing" value="<?php echo htmlspecialchars($cert['icon'] ?? ''); ?>">
                                                        <?php if (!empty($cert['icon'])): ?>
                                                            <p class="text-xs text-muted-foreground mt-1">Current: <?php echo htmlspecialchars($cert['icon']); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-foreground mb-2">Order</label>
                                                        <input type="number" name="cert_order" value="<?php echo htmlspecialchars((string)($cert['order'] ?? 10)); ?>" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                                        <div class="flex items-center gap-2 mt-3">
                                                            <input id="cert-visible" type="checkbox" name="cert_visible" class="h-4 w-4 text-primary bg-background border-input rounded focus:ring-ring" <?php echo (($cert['visible'] ?? '0') === '1' || ($cert['visible'] ?? 0) === 1) ? 'checked' : ''; ?>>
                                                            <label for="cert-visible" class="text-sm text-foreground">Visible on homepage</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center justify-end gap-3 pt-2">
                                                    <?php if ($is_cert_edit): ?>
                                                        <a href="projects-new.php?page=settings" class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">Cancel</a>
                                                        <button type="submit" name="edit_cert_card" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                                            <i class="fas fa-save mr-2"></i>Update Card
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="submit" name="add_cert_card" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                                            <i class="fas fa-plus mr-2"></i>Add Card
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="bg-card rounded-lg border border-border p-4">
                                            <h5 class="font-medium text-foreground mb-3">Existing Cards</h5>

                                            <?php if (empty($certCards)): ?>
                                                <div class="text-sm text-muted-foreground">
                                                    No cards yet. Click <strong>Load Defaults</strong> to import the existing homepage cards, then edit/disable/add new ones.
                                                </div>
                                            <?php else: ?>
                                                <?php
                                                uasort($certCards, function($a, $b) {
                                                    $oa = (int)($a['order'] ?? 999);
                                                    $ob = (int)($b['order'] ?? 999);
                                                    if ($oa === $ob) return 0;
                                                    return $oa <=> $ob;
                                                });
                                                ?>
                                                <div class="grid gap-2">
                                                    <?php foreach ($certCards as $cid => $c): ?>
                                                        <div class="bg-background border border-border rounded-lg p-3 flex items-center justify-between gap-3">
                                                            <div class="min-w-0">
                                                                <div class="flex items-center gap-2 flex-wrap">
                                                                    <span class="font-medium text-foreground"><?php echo htmlspecialchars(($c['title_en'] ?? '') ?: ($c['title_ar'] ?? '') ?: $cid); ?></span>
                                                                    <span class="text-xs px-2 py-1 rounded-full <?php echo (($c['visible'] ?? '0') === '1' || ($c['visible'] ?? 0) === 1) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'; ?>">
                                                                        <?php echo (($c['visible'] ?? '0') === '1' || ($c['visible'] ?? 0) === 1) ? 'Visible' : 'Hidden'; ?>
                                                                    </span>
                                                                    <span class="text-xs text-muted-foreground">Order: <?php echo htmlspecialchars((string)($c['order'] ?? '')); ?></span>
                                                                    <span class="text-xs text-muted-foreground break-all">ID: <?php echo htmlspecialchars($cid); ?></span>
                                                                </div>
                                                                <?php if (!empty($c['icon'])): ?>
                                                                    <div class="text-xs text-muted-foreground break-all">Icon: <?php echo htmlspecialchars($c['icon']); ?></div>
                                                                <?php endif; ?>
                                                            </div>

                                                            <div class="flex items-center gap-2 shrink-0">
                                                                <a href="projects-new.php?page=settings&edit_cert=<?php echo urlencode($cid); ?>" class="px-3 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors text-sm">
                                                                    <i class="fas fa-edit mr-2"></i>Edit
                                                                </a>
                                                                <form method="post" onsubmit="return confirm('Delete this card?');">
                                                                    <input type="hidden" name="cert_id" value="<?php echo htmlspecialchars($cid); ?>">
                                                                    <button type="submit" name="delete_cert_card" class="px-3 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors text-sm text-destructive">
                                                                        <i class="fas fa-trash mr-2"></i>Delete
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" name="update_settings" 
                                            class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                        <i class="fas fa-save mr-2"></i>
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Security Settings -->
                    <div id="security-tab" class="tab-content" style="display: none !important;">
                        <div class="bg-card rounded-xl border border-border p-6">
                            <h3 class="text-lg font-heading font-bold text-foreground mb-6">Security Settings</h3>
                            
                            <form method="post" class="space-y-6">
                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-foreground mb-2">New Admin Password</label>
                                    <div class="relative">
                                        <input type="password" id="new_password" name="new_password" 
                                               placeholder="Leave blank to keep current password"
                                               class="w-full px-3 py-2 pr-10 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"
                                               oninput="validatePassword()">
                                        <button type="button" onclick="togglePassword('new_password', 'new_password_eye')" 
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-muted-foreground hover:text-foreground focus:outline-none">
                                            <i id="new_password_eye" class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="password-strength" class="mt-2 text-sm hidden">
                                        <div class="flex items-center justify-between mb-1">
                                            <span>Password Strength:</span>
                                            <span id="strength-text" class="font-medium"></span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div id="strength-bar" class="h-2 rounded-full transition-all duration-300"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-foreground mb-2">Confirm New Password</label>
                                    <div class="relative">
                                        <input type="password" id="confirm_password" name="confirm_password" 
                                               placeholder="Confirm new password"
                                               class="w-full px-3 py-2 pr-10 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"
                                               oninput="validatePasswordMatch()">
                                        <button type="button" onclick="togglePassword('confirm_password', 'confirm_password_eye')" 
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-muted-foreground hover:text-foreground focus:outline-none">
                                            <i id="confirm_password_eye" class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="password-match" class="mt-2 text-sm hidden">
                                        <span id="match-text"></span>
                                    </div>
                                </div>
                                
                                <div class="bg-muted/50 rounded-lg p-4">
                                    <h4 class="font-medium text-foreground mb-2">Password Requirements:</h4>
                                    <ul class="text-sm text-muted-foreground space-y-1" id="password-requirements">
                                        <li id="req-length" class="flex items-center">
                                            <i class="fas fa-times-circle text-red-500 mr-2" id="req-length-icon"></i>
                                            At least 8 characters long
                                        </li>
                                        <li id="req-uppercase" class="flex items-center">
                                            <i class="fas fa-times-circle text-red-500 mr-2" id="req-uppercase-icon"></i>
                                            At least one uppercase letter (A-Z)
                                        </li>
                                        <li id="req-lowercase" class="flex items-center">
                                            <i class="fas fa-times-circle text-red-500 mr-2" id="req-lowercase-icon"></i>
                                            At least one lowercase letter (a-z)
                                        </li>
                                        <li id="req-number" class="flex items-center">
                                            <i class="fas fa-times-circle text-red-500 mr-2" id="req-number-icon"></i>
                                            At least one number (0-9)
                                        </li>
                                        <li id="req-special" class="flex items-center">
                                            <i class="fas fa-times-circle text-red-500 mr-2" id="req-special-icon"></i>
                                            At least one special character (!@#$%^&*)
                                        </li>
                                    </ul>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" name="update_settings" 
                                            class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                        <i class="fas fa-shield-alt mr-2"></i>
                                        Update Security
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Backup Settings -->
                    <div id="backup-tab" class="tab-content" style="display: none !important;">
                        <div class="bg-card rounded-xl border border-border p-6">
                            <h3 class="text-lg font-heading font-bold text-foreground mb-6">Backup & Restore</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-muted/50 rounded-lg p-4">
                                    <h4 class="font-medium text-foreground mb-2">Backup Projects Data</h4>
                                    <p class="text-sm text-muted-foreground mb-4">Create a backup of all projects data</p>
                                    <button onclick="createBackup()" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                        <i class="fas fa-download mr-2"></i>
                                        Download Backup
                                    </button>
                                </div>
                                
                                <div class="bg-muted/50 rounded-lg p-4">
                                    <h4 class="font-medium text-foreground mb-2">Restore Projects Data</h4>
                                    <p class="text-sm text-muted-foreground mb-4">Restore from a backup file</p>
                                    <input type="file" id="backup_file" accept=".php" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring mb-2">
                                    <button onclick="restoreBackup()" class="px-4 py-2 bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/90 transition-colors">
                                        <i class="fas fa-upload mr-2"></i>
                                        Restore Backup
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mt-6 bg-muted/50 rounded-lg p-4">
                                <h4 class="font-medium text-foreground mb-2">Backup Information</h4>
                                <div class="text-sm text-muted-foreground space-y-1">
                                    <p>• Backups include all projects data and configurations</p>
                                    <p>• Store backup files in a secure location</p>
                                    <p>• Regular backups are recommended for data safety</p>
                                    <p>• Last backup: <span id="last-backup-date">Never</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($current_page === 'team'): ?>
                <div class="space-y-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-heading font-bold text-foreground">Team</h1>
                            <p class="text-muted-foreground mt-1">Manage the public Team page content</p>
                        </div>
                        <button type="button" onclick="openMemberAddModal()" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors gap-2 inline-flex items-center">
                            <i class="fas fa-user-plus h-4 w-4"></i>
                            Add Member
                        </button>
                    </div>

                    <?php
                    $team_total = is_array($team_members ?? null) ? count($team_members) : 0;
                    $team_visible = is_array($team_members ?? null) ? count(array_filter($team_members, fn($m) => !isset($m['visible']) || $m['visible'] === '1' || $m['visible'] === 1)) : 0;
                    $team_featured = is_array($team_members ?? null) ? count(array_filter($team_members, fn($m) => ($m['layout'] ?? '') === 'featured')) : 0;
                    ?>
                    <div class="mobile-stats-grid grid grid-cols-2 lg:grid-cols-5 gap-3">
                        <div class="stats-card stats-card-total bg-card p-4 cursor-default">
                            <p class="text-sm text-muted-foreground">Total</p>
                            <p class="stats-value text-2xl font-heading font-bold mt-1 text-primary"><?php echo $team_total; ?></p>
                        </div>
                        <div class="stats-card stats-card-completed bg-card p-4 cursor-default">
                            <p class="text-sm text-muted-foreground">Visible</p>
                            <p class="stats-value text-2xl font-heading font-bold mt-1 text-green-700"><?php echo $team_visible; ?></p>
                        </div>
                        <div class="stats-card stats-card-planning bg-card p-4 cursor-default">
                            <p class="text-sm text-muted-foreground">Featured</p>
                            <p class="stats-value text-2xl font-heading font-bold mt-1 text-blue-600"><?php echo $team_featured; ?></p>
                        </div>
                    </div>

                    <!-- CEO Editor -->
                    <div class="bg-card rounded-xl border border-border p-6">
                        <h3 class="text-lg font-heading font-bold text-foreground mb-4">CEO Block</h3>
                        <form method="post" enctype="multipart/form-data" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">Name</label>
                                    <input type="text" name="ceo_name" value="<?php echo htmlspecialchars($ceo_profile['name'] ?? ''); ?>" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">Title</label>
                                    <input type="text" name="ceo_title" value="<?php echo htmlspecialchars($ceo_profile['title'] ?? ''); ?>" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">Source Heading</label>
                                    <input type="text" name="ceo_source_heading" value="<?php echo htmlspecialchars($ceo_profile['source_heading'] ?? ''); ?>" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">CEO Photo</label>
                                    <input type="file" name="ceo_photo" accept=".jpg,.jpeg,.png,.webp" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    <p class="text-xs text-muted-foreground mt-1">Current: <?php echo htmlspecialchars($ceo_profile['photo'] ?? ''); ?></p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">LinkedIn URL</label>
                                    <input type="text" name="ceo_social_linkedin" value="<?php echo htmlspecialchars($ceo_profile['socials']['linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/... or #" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">X/Twitter URL</label>
                                    <input type="text" name="ceo_social_twitter" value="<?php echo htmlspecialchars($ceo_profile['socials']['twitter'] ?? ''); ?>" placeholder="https://x.com/... or #" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">Email Link</label>
                                    <input type="text" name="ceo_social_email" value="<?php echo htmlspecialchars($ceo_profile['socials']['email'] ?? ''); ?>" placeholder="mailto:ceo@company.com" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">CEO Bio (separate paragraphs with a blank line)</label>
                                <textarea name="ceo_bio" rows="8" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"><?php echo htmlspecialchars(implode("\n\n", $ceo_profile['bio_paragraphs'] ?? [])); ?></textarea>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" name="update_ceo" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                    <i class="fas fa-save mr-2"></i>Save CEO Block
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Members list -->
                    <div class="bg-card rounded-xl border border-border p-6">
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <h3 class="text-lg font-heading font-bold text-foreground">Members</h3>
                            <a class="text-sm text-primary underline" href="../team.php" target="_blank">View public page</a>
                        </div>

                        <div class="grid gap-3">
                            <?php if (empty($team_members)): ?>
                                <div class="text-sm text-muted-foreground">No team members yet.</div>
                            <?php else: ?>
                                <?php foreach ($team_members as $slug => $m): ?>
                                    <div class="bg-background border border-border rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                        <div class="shrink-0">
                                            <img
                                                src="../assets/img/<?php echo htmlspecialchars($m['photo'] ?? 'construction/team-1.webp'); ?>"
                                                alt="<?php echo htmlspecialchars($m['name'] ?? 'Team member'); ?>"
                                                class="w-16 h-16 md:w-20 md:h-20 rounded-xl object-cover border border-border bg-background"
                                                onerror="this.onerror=null;this.src='../assets/img/construction/team-1.webp';"
                                            >
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="font-medium text-foreground truncate"><?php echo htmlspecialchars($m['name'] ?? ''); ?></p>
                                                <span class="text-xs px-2 py-1 rounded-full bg-muted text-muted-foreground"><?php echo htmlspecialchars($m['layout'] ?? ''); ?></span>
                                                <span class="text-xs px-2 py-1 rounded-full <?php echo (!isset($m['visible']) || $m['visible'] === '1' || $m['visible'] === 1) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'; ?>">
                                                    <?php echo (!isset($m['visible']) || $m['visible'] === '1' || $m['visible'] === 1) ? 'Visible' : 'Hidden'; ?>
                                                </span>
                                                <span class="text-xs text-muted-foreground break-all">Slug: <?php echo htmlspecialchars($slug); ?></span>
                                            </div>
                                            <p class="text-sm text-muted-foreground truncate"><?php echo htmlspecialchars(($m['role'] ?? '') . (empty($m['email']) ? '' : ' • ' . $m['email'])); ?></p>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" onclick="openMemberEditModal('<?php echo htmlspecialchars($slug); ?>')" class="px-3 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors text-sm">
                                                <i class="fas fa-edit mr-2"></i>Edit
                                            </button>
                                            <button type="button" onclick="openMemberDeleteModal('<?php echo htmlspecialchars($slug); ?>')" class="px-3 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors text-sm text-destructive">
                                                <i class="fas fa-trash mr-2"></i>Delete
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Add/Edit Team Member Modal -->
                <div id="member-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div class="bg-card rounded-xl border border-border max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 id="member-modal-title" class="text-xl font-heading font-bold text-foreground">Add Member</h2>
                                <button type="button" onclick="closeMemberModal()" class="text-muted-foreground hover:text-foreground">
                                    <i class="fas fa-times h-5 w-5"></i>
                                </button>
                            </div>

                            <form id="member-form" method="post" enctype="multipart/form-data" class="space-y-4">
                                <input type="hidden" id="member-slug" name="member_slug" value="">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Name</label>
                                        <input id="member-name" type="text" name="name" required class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Role/Title</label>
                                        <input id="member-role" type="text" name="role" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Layout</label>
                                        <select id="member-layout" name="layout" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                            <option value="compact">Compact</option>
                                            <option value="featured">Featured</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Experience (e.g. 12+)</label>
                                        <input id="member-experience" type="text" name="experience" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Email</label>
                                        <input id="member-email" type="email" name="email" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Phone</label>
                                        <input id="member-phone" type="text" name="phone" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Photo</label>
                                        <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        <p id="member-current-photo" class="text-xs text-muted-foreground mt-1"></p>
                                    </div>
                                    <div class="flex items-center gap-2 pt-7">
                                        <input id="member-visible" type="checkbox" name="visible" class="h-4 w-4 text-primary bg-background border-input rounded focus:ring-ring">
                                        <label for="member-visible" class="text-sm text-foreground">Visible on public page</label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">Description (featured cards)</label>
                                    <textarea id="member-description" name="description" rows="3" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-muted/30 rounded-lg p-4 border border-border">
                                        <h4 class="font-medium text-foreground mb-3">Social Media</h4>
                                        <div class="space-y-3">
                                            <input id="member-social-linkedin" type="text" name="social_linkedin" placeholder="LinkedIn URL (or #)" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                            <input id="member-social-twitter" type="text" name="social_twitter" placeholder="X/Twitter URL (or #)" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                            <input id="member-social-facebook" type="text" name="social_facebook" placeholder="Facebook URL (or #)" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                            <input id="member-social-instagram" type="text" name="social_instagram" placeholder="Instagram URL (or #)" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                    </div>
                                    <div class="bg-muted/30 rounded-lg p-4 border border-border">
                                        <h4 class="font-medium text-foreground mb-3">Compact Card Overlay</h4>
                                        <div class="space-y-3">
                                            <input id="member-qc-email" type="text" name="qc_email" placeholder="Email link (mailto:... or #)" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                            <input id="member-qc-phone" type="text" name="qc_phone" placeholder="Phone link (tel:... or #)" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                            <input id="member-qc-linkedin" type="text" name="qc_linkedin" placeholder="LinkedIn link (or #)" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Credentials (featured)</label>
                                        <div class="grid grid-cols-1 gap-3">
                                            <div class="grid grid-cols-3 gap-2">
                                                <input id="member-credential-1-icon" type="text" name="credential_1_icon" placeholder="Icon class e.g. bi-award" class="col-span-1 px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                                <input id="member-credential-1" type="text" name="credential_1" placeholder="Credential 1 label" class="col-span-2 px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                            </div>
                                            <div class="grid grid-cols-3 gap-2">
                                                <input id="member-credential-2-icon" type="text" name="credential_2_icon" placeholder="Icon class e.g. bi-shield-check" class="col-span-1 px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                                <input id="member-credential-2" type="text" name="credential_2" placeholder="Credential 2 label" class="col-span-2 px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Skills (compact) one per line</label>
                                        <textarea id="member-skills" name="skills" rows="5" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" onclick="closeMemberModal()" class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">Cancel</button>
                                    <button id="member-submit-btn" type="submit" name="add_member" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                        <i class="fas fa-save mr-2"></i>Save Member
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Member Modal -->
                <div id="member-delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div class="bg-card rounded-xl border border-border max-w-md w-full">
                        <div class="p-6">
                            <h3 class="text-lg font-heading font-bold text-foreground mb-2">Delete Team Member</h3>
                            <p class="text-muted-foreground mb-6">Are you sure you want to delete <span id="member-delete-name" class="font-medium text-foreground"></span>?</p>
                            <form method="post">
                                <input type="hidden" id="member-delete-slug" name="member_slug" value="">
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="closeMemberDeleteModal()" class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">Cancel</button>
                                    <button type="submit" name="delete_member" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                        <i class="fas fa-trash mr-2"></i>Delete
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php elseif ($current_page === 'certifications'): ?>
                <?php
                $certCards = is_array($site_settings['certification_cards'] ?? null) ? $site_settings['certification_cards'] : [];
                $certVisibleCount = count(array_filter($certCards, fn($c) => ($c['visible'] ?? '0') === '1' || ($c['visible'] ?? 0) === 1));
                uasort($certCards, function($a, $b) {
                    $oa = (int)($a['order'] ?? 999);
                    $ob = (int)($b['order'] ?? 999);
                    if ($oa === $ob) return 0;
                    return $oa <=> $ob;
                });
                ?>

                <div class="space-y-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-heading font-bold text-foreground">Certifications</h1>
                            <p class="text-muted-foreground mt-1">Manage the homepage certification cards (ISO/OSHA/Licensed/LEED/Insurance/Training)</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="post">
                                <button type="submit" name="init_cert_cards" class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors inline-flex items-center gap-2">
                                    <i class="fas fa-wand-magic-sparkles"></i>
                                    Load Defaults
                                </button>
                            </form>
                            <button type="button" onclick="openCertAddModal()" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors gap-2 inline-flex items-center">
                                <i class="fas fa-plus h-4 w-4"></i>
                                Add Card
                            </button>
                        </div>
                    </div>

                    <div class="mobile-stats-grid grid grid-cols-2 lg:grid-cols-5 gap-3">
                        <div class="stats-card stats-card-total bg-card p-4 cursor-default">
                            <p class="text-sm text-muted-foreground">Total</p>
                            <p class="stats-value text-2xl font-heading font-bold mt-1 text-primary"><?php echo count($certCards); ?></p>
                        </div>
                        <div class="stats-card stats-card-completed bg-card p-4 cursor-default">
                            <p class="text-sm text-muted-foreground">Visible</p>
                            <p class="stats-value text-2xl font-heading font-bold mt-1 text-green-700"><?php echo $certVisibleCount; ?></p>
                        </div>
                    </div>

                    <div class="bg-card rounded-xl border border-border p-6">
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <h3 class="text-lg font-heading font-bold text-foreground mb-0">Cards</h3>
                            <a class="text-sm text-primary underline" href="../index.php#certifications" target="_blank">View on homepage</a>
                        </div>

                        <?php if (empty($certCards)): ?>
                            <div class="text-sm text-muted-foreground">
                                No cards yet. Click <strong>Load Defaults</strong> to import the existing homepage cards, then edit/disable/add new ones.
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-muted-foreground">
                                            <th class="py-2 pr-4">Title (EN)</th>
                                            <th class="py-2 pr-4">Visible</th>
                                            <th class="py-2 pr-4">Order</th>
                                            <th class="py-2 pr-0 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-foreground">
                                        <?php foreach ($certCards as $cid => $c): ?>
                                            <tr class="border-t border-border">
                                                <td class="py-3 pr-4">
                                                    <div class="font-medium"><?php echo htmlspecialchars(($c['title_en'] ?? '') ?: $cid); ?></div>
                                                    <div class="text-xs text-muted-foreground break-all">ID: <?php echo htmlspecialchars($cid); ?></div>
                                                </td>
                                                <td class="py-3 pr-4">
                                                    <span class="text-xs px-2 py-1 rounded-full <?php echo (($c['visible'] ?? '0') === '1' || ($c['visible'] ?? 0) === 1) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'; ?>">
                                                        <?php echo (($c['visible'] ?? '0') === '1' || ($c['visible'] ?? 0) === 1) ? 'Yes' : 'No'; ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 pr-4"><?php echo htmlspecialchars((string)($c['order'] ?? '')); ?></td>
                                                <td class="py-3 pr-0">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <button type="button" onclick="openCertEditModal('<?php echo htmlspecialchars($cid); ?>')" class="px-3 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors text-sm">
                                                            <i class="fas fa-edit mr-2"></i>Edit
                                                        </button>
                                                        <button type="button" onclick="openCertDeleteModal('<?php echo htmlspecialchars($cid); ?>')" class="px-3 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors text-sm text-destructive">
                                                            <i class="fas fa-trash mr-2"></i>Delete
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Add/Edit Certification Modal -->
                <div id="cert-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div class="bg-card rounded-xl border border-border max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 id="cert-modal-title" class="text-xl font-heading font-bold text-foreground">Add Card</h2>
                                <button type="button" onclick="closeCertModal()" class="text-muted-foreground hover:text-foreground">
                                    <i class="fas fa-times h-5 w-5"></i>
                                </button>
                            </div>

                            <form id="cert-form" method="post" enctype="multipart/form-data" class="space-y-4">
                                <input type="hidden" id="cert-id" name="cert_id" value="">
                                <input type="hidden" id="cert-icon-existing" name="cert_icon_existing" value="">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Title (EN)</label>
                                        <input id="cert-title-en" type="text" name="cert_title_en" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Title (AR)</label>
                                        <input id="cert-title-ar" type="text" name="cert_title_ar" dir="rtl" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Category (EN)</label>
                                        <input id="cert-category-en" type="text" name="cert_category_en" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Category (AR)</label>
                                        <input id="cert-category-ar" type="text" name="cert_category_ar" dir="rtl" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Order</label>
                                        <input id="cert-order" type="number" name="cert_order" value="10" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div class="flex items-center gap-2 pt-7">
                                        <input id="cert-visible" type="checkbox" name="cert_visible" class="h-4 w-4 text-primary bg-background border-input rounded focus:ring-ring" checked>
                                        <label for="cert-visible" class="text-sm text-foreground">Visible on homepage</label>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Description (EN)</label>
                                        <textarea id="cert-desc-en" name="cert_desc_en" rows="4" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Description (AR)</label>
                                        <textarea id="cert-desc-ar" name="cert_desc_ar" dir="rtl" rows="4" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">Icon image (optional)</label>
                                    <input type="file" name="cert_icon" accept=".jpg,.jpeg,.png,.webp,.svg" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    <p id="cert-current-icon" class="text-xs text-muted-foreground mt-1"></p>
                                </div>

                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" onclick="closeCertModal()" class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">Cancel</button>
                                    <button id="cert-submit-btn" type="submit" name="add_cert_card" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                        <i class="fas fa-save mr-2"></i>Save Card
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Certification Modal -->
                <div id="cert-delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div class="bg-card rounded-xl border border-border max-w-md w-full">
                        <div class="p-6">
                            <h3 class="text-lg font-heading font-bold text-foreground mb-2">Delete Card</h3>
                            <p class="text-muted-foreground mb-6">Are you sure you want to delete <span id="cert-delete-name" class="font-medium text-foreground"></span>?</p>
                            <form method="post">
                                <input type="hidden" id="cert-delete-id" name="cert_id" value="">
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="closeCertDeleteModal()" class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">Cancel</button>
                                    <button type="submit" name="delete_cert_card" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                        <i class="fas fa-trash mr-2"></i>Delete
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    const certCards = <?php echo json_encode($certCards); ?>;

                    function openCertAddModal() {
                        const form = document.getElementById('cert-form');
                        if (!form) return;
                        form.reset();
                        document.getElementById('cert-modal-title').textContent = 'Add Card';
                        document.getElementById('cert-id').value = '';
                        document.getElementById('cert-icon-existing').value = '';
                        document.getElementById('cert-current-icon').textContent = '';
                        document.getElementById('cert-visible').checked = true;

                        const btn = document.getElementById('cert-submit-btn');
                        btn.name = 'add_cert_card';
                        btn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Card';
                        document.getElementById('cert-modal').classList.remove('hidden');
                    }

                    function openCertEditModal(id) {
                        const c = certCards?.[id];
                        if (!c) return;
                        const form = document.getElementById('cert-form');
                        if (!form) return;
                        form.reset();
                        document.getElementById('cert-modal-title').textContent = 'Edit Card';
                        document.getElementById('cert-id').value = id;
                        document.getElementById('cert-title-en').value = c.title_en || '';
                        document.getElementById('cert-title-ar').value = c.title_ar || '';
                        document.getElementById('cert-category-en').value = c.category_en || '';
                        document.getElementById('cert-category-ar').value = c.category_ar || '';
                        document.getElementById('cert-desc-en').value = c.desc_en || '';
                        document.getElementById('cert-desc-ar').value = c.desc_ar || '';
                        document.getElementById('cert-order').value = (c.order ?? 10);
                        document.getElementById('cert-visible').checked = (c.visible === '1' || c.visible === 1 || c.visible === true);
                        document.getElementById('cert-icon-existing').value = c.icon || '';
                        document.getElementById('cert-current-icon').textContent = c.icon ? ('Current: ' + c.icon) : '';

                        const btn = document.getElementById('cert-submit-btn');
                        btn.name = 'edit_cert_card';
                        btn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Card';
                        document.getElementById('cert-modal').classList.remove('hidden');
                    }

                    function closeCertModal() {
                        const modal = document.getElementById('cert-modal');
                        if (modal) modal.classList.add('hidden');
                    }

                    function openCertDeleteModal(id) {
                        const c = certCards?.[id];
                        if (!c) return;
                        document.getElementById('cert-delete-id').value = id;
                        document.getElementById('cert-delete-name').textContent = c.title_en || id;
                        document.getElementById('cert-delete-modal').classList.remove('hidden');
                    }

                    function closeCertDeleteModal() {
                        const modal = document.getElementById('cert-delete-modal');
                        if (modal) modal.classList.add('hidden');
                    }
                </script>

            <?php elseif ($current_page === 'testimonials'): ?>
                <?php
                $t_items = is_array($testimonials ?? null) ? $testimonials : [];
                // newest first
                uasort($t_items, function($a, $b) {
                    return strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? ''));
                });
                $pending_count = count(array_filter($t_items, fn($t) => ($t['status'] ?? 'pending') === 'pending'));
                $visible_count = count(array_filter($t_items, fn($t) => ($t['visible'] ?? '0') === '1' || ($t['visible'] ?? 0) === 1));
                ?>

                <div class="space-y-8">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-heading font-bold text-foreground">Testimonials</h1>
                        <p class="text-muted-foreground mt-1">User-submitted reviews from the website (approve and publish them here)</p>
                    </div>

                    <div class="mobile-stats-grid grid grid-cols-2 lg:grid-cols-5 gap-3">
                        <div class="stats-card stats-card-total bg-card p-4 cursor-default">
                            <p class="text-sm text-muted-foreground">Total</p>
                            <p class="stats-value text-2xl font-heading font-bold mt-1 text-primary"><?php echo count($t_items); ?></p>
                        </div>
                        <div class="stats-card stats-card-planning bg-card p-4 cursor-default">
                            <p class="text-sm text-muted-foreground">Pending</p>
                            <p class="stats-value text-2xl font-heading font-bold mt-1 text-amber-600"><?php echo $pending_count; ?></p>
                        </div>
                        <div class="stats-card stats-card-completed bg-card p-4 cursor-default">
                            <p class="text-sm text-muted-foreground">Visible</p>
                            <p class="stats-value text-2xl font-heading font-bold mt-1 text-green-700"><?php echo $visible_count; ?></p>
                        </div>
                    </div>

                    <div class="bg-card rounded-xl border border-border p-6">
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <h3 class="text-lg font-heading font-bold text-foreground mb-0">Submissions</h3>
                            <a class="text-sm text-primary underline" href="../index.php#testimonials" target="_blank">View on homepage</a>
                        </div>

                        <?php if (empty($t_items)): ?>
                            <div class="text-sm text-muted-foreground">No testimonials submitted yet.</div>
                        <?php else: ?>
                            <div class="grid gap-3">
                                <?php foreach ($t_items as $id => $t): ?>
                                    <?php
                                    $status = $t['status'] ?? 'pending';
                                    $isVisible = (($t['visible'] ?? '0') === '1' || ($t['visible'] ?? 0) === 1);
                                    $msgPreview = trim((string)($t['message_en'] ?? ''));
                                    if ($msgPreview === '') $msgPreview = trim((string)($t['message_ar'] ?? ''));
                                    $msgPreview = mb_substr($msgPreview, 0, 140);
                                    ?>
                                    <div class="bg-background border border-border rounded-lg p-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="font-medium text-foreground mb-0"><?php echo htmlspecialchars($t['name'] ?? ''); ?></p>
                                                <span class="text-xs px-2 py-1 rounded-full <?php echo $status === 'approved' ? 'bg-green-100 text-green-800' : ($status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'); ?>">
                                                    <?php echo htmlspecialchars(ucfirst($status)); ?>
                                                </span>
                                                <span class="text-xs px-2 py-1 rounded-full <?php echo $isVisible ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'; ?>">
                                                    <?php echo $isVisible ? 'Visible' : 'Hidden'; ?>
                                                </span>
                                                <div class="flex items-center gap-1 text-xs text-muted-foreground">
                                                    <?php 
                                                    $rating = (int)($t['rating'] ?? 5);
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $rating) {
                                                            echo '<i class="fas fa-star text-yellow-400 text-sm"></i>';
                                                        } else {
                                                            echo '<i class="far fa-star text-gray-300 text-sm"></i>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <span class="text-xs text-muted-foreground">Submitted: <?php echo htmlspecialchars($t['created_at'] ?? ''); ?></span>
                                            </div>
                                            <p class="text-sm text-muted-foreground mt-2 mb-0"><?php echo htmlspecialchars($msgPreview); ?><?php echo (strlen($msgPreview) >= 140 ? '…' : ''); ?></p>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <?php if ($status !== 'approved'): ?>
                                                <form method="post">
                                                    <input type="hidden" name="testimonial_id" value="<?php echo htmlspecialchars($id); ?>">
                                                    <button type="submit" name="approve_testimonial" class="px-3 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors text-sm">
                                                        <i class="fas fa-check mr-2"></i>Approve
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($isVisible): ?>
                                                <form method="post">
                                                    <input type="hidden" name="testimonial_id" value="<?php echo htmlspecialchars($id); ?>">
                                                    <button type="submit" name="hide_testimonial" class="px-3 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors text-sm">
                                                        <i class="fas fa-eye-slash mr-2"></i>Hide
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <button type="button" onclick="openTestimonialEditModal('<?php echo htmlspecialchars($id); ?>')" class="px-3 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors text-sm">
                                                <i class="fas fa-edit mr-2"></i>Edit
                                            </button>
                                            <button type="button" onclick="openTestimonialDeleteModal('<?php echo htmlspecialchars($id); ?>')" class="px-3 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors text-sm text-destructive">
                                                <i class="fas fa-trash mr-2"></i>Delete
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Edit Testimonial Modal -->
                <div id="testimonial-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div class="bg-card rounded-xl border border-border max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-heading font-bold text-foreground">Edit Testimonial</h2>
                                <button type="button" onclick="closeTestimonialModal()" class="text-muted-foreground hover:text-foreground">
                                    <i class="fas fa-times h-5 w-5"></i>
                                </button>
                            </div>

                            <form id="testimonial-form" method="post" class="space-y-4">
                                <input type="hidden" id="t-id" name="testimonial_id" value="">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Name</label>
                                        <input id="t-name" type="text" name="t_name" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">(1-5)</label>
                                        <input id="t-rating" type="number" min="1" max="5" name="t_rating" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Role (EN)</label>
                                        <input id="t-role-en" type="text" name="t_role_en" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Role (AR)</label>
                                        <input id="t-role-ar" type="text" dir="rtl" name="t_role_ar" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Company (EN)</label>
                                        <input id="t-company-en" type="text" name="t_company_en" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Company (AR)</label>
                                        <input id="t-company-ar" type="text" dir="rtl" name="t_company_ar" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Status</label>
                                        <select id="t-status" name="t_status" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-2 pt-7">
                                        <input id="t-visible" type="checkbox" name="t_visible" class="h-4 w-4 text-primary bg-background border-input rounded focus:ring-ring">
                                        <label for="t-visible" class="text-sm text-foreground">Visible on homepage</label>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Message (EN)</label>
                                        <textarea id="t-message-en" name="t_message_en" rows="4" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-foreground mb-2">Message (AR)</label>
                                        <textarea id="t-message-ar" name="t_message_ar" dir="rtl" rows="4" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" onclick="closeTestimonialModal()" class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">Cancel</button>
                                    <button type="submit" name="edit_testimonial" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                        <i class="fas fa-save mr-2"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Testimonial Modal -->
                <div id="testimonial-delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div class="bg-card rounded-xl border border-border max-w-md w-full">
                        <div class="p-6">
                            <h3 class="text-lg font-heading font-bold text-foreground mb-2">Delete Testimonial</h3>
                            <p class="text-muted-foreground mb-6">Are you sure you want to delete <span id="testimonial-delete-name" class="font-medium text-foreground"></span>?</p>
                            <form method="post">
                                <input type="hidden" id="testimonial-delete-id" name="testimonial_id" value="">
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="closeTestimonialDeleteModal()" class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">Cancel</button>
                                    <button type="submit" name="delete_testimonial" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                        <i class="fas fa-trash mr-2"></i>Delete
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    const testimonialsData = <?php echo json_encode($t_items); ?>;

                    function openTestimonialEditModal(id) {
                        const t = testimonialsData?.[id];
                        if (!t) return;
                        document.getElementById('t-id').value = id;
                        document.getElementById('t-name').value = t.name || '';
                        document.getElementById('t-rating').value = t.rating ?? 5;
                        document.getElementById('t-role-en').value = t.role_en || '';
                        document.getElementById('t-role-ar').value = t.role_ar || '';
                        document.getElementById('t-company-en').value = t.company_en || '';
                        document.getElementById('t-company-ar').value = t.company_ar || '';
                        document.getElementById('t-status').value = t.status || 'pending';
                        document.getElementById('t-visible').checked = (t.visible === '1' || t.visible === 1 || t.visible === true);
                        document.getElementById('t-message-en').value = t.message_en || '';
                        document.getElementById('t-message-ar').value = t.message_ar || '';
                        document.getElementById('testimonial-modal').classList.remove('hidden');
                    }

                    function closeTestimonialModal() {
                        const m = document.getElementById('testimonial-modal');
                        if (m) m.classList.add('hidden');
                    }

                    function openTestimonialDeleteModal(id) {
                        const t = testimonialsData?.[id];
                        if (!t) return;
                        document.getElementById('testimonial-delete-id').value = id;
                        document.getElementById('testimonial-delete-name').textContent = t.name || id;
                        document.getElementById('testimonial-delete-modal').classList.remove('hidden');
                    }

                    function closeTestimonialDeleteModal() {
                        const m = document.getElementById('testimonial-delete-modal');
                        if (m) m.classList.add('hidden');
                    }
                </script>

            <?php elseif ($current_page === 'careers'): ?>
                <?php
                $edit_slug = $_GET['edit'] ?? '';
                $is_edit = is_string($edit_slug) && $edit_slug !== '' && isset($job_posts[$edit_slug]);
                $job = $is_edit ? $job_posts[$edit_slug] : [
                    'title' => '',
                    'title_en' => '',
                    'title_ar' => '',
                    'department' => '',
                    'location' => '',
                    'type' => '',
                    'summary' => '',
                    'summary_en' => '',
                    'summary_ar' => '',
                    'description' => '',
                    'description_en' => '',
                    'description_ar' => '',
                    'responsibilities' => [],
                    'responsibilities_en' => [],
                    'responsibilities_ar' => [],
                    'requirements' => [],
                    'requirements_en' => [],
                    'requirements_ar' => [],
                    'visible' => '1',
                    'posted_at' => date('Y-m-d'),
                ];

                $jobs_total = is_array($job_posts ?? null) ? count($job_posts) : 0;
                $jobs_visible = is_array($job_posts ?? null) ? count(array_filter($job_posts, fn($j) => ($j['visible'] ?? '0') === '1' || ($j['visible'] ?? 0) === 1)) : 0;
                ?>

                <div class="space-y-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-heading font-bold text-foreground">Careers</h1>
                            <p class="text-muted-foreground mt-1">Create and manage job posts shown on the public Careers page</p>
                        </div>
                        <button id="add-job-btn" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors gap-2 inline-flex items-center">
                            <i class="fas fa-plus h-4 w-4"></i>
                            New Job
                        </button>
                    </div>

                    <!-- Add Job Modal -->
                    <div id="add-job-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
                        <div class="bg-card rounded-xl border border-border max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                            <div class="sticky top-0 bg-card border-b border-border p-6 flex items-center justify-between">
                                <h3 id="modal-title" class="text-lg font-heading font-bold text-foreground">Add New Job</h3>
                                <button id="close-modal-btn" class="text-muted-foreground hover:text-foreground transition-colors">
                                    <i class="fas fa-times h-5 w-5"></i>
                                </button>
                            </div>
                            
                            <div class="p-6">
                                <form id="add-job-form" method="post" class="space-y-4">
                                    <input type="hidden" id="edit-job-slug" name="job_slug" value="">
                                    <input type="hidden" id="is-edit-mode" name="is_edit_mode" value="0">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Title (EN)</label>
                                            <input type="text" name="job_title" required class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Title (AR)</label>
                                            <input type="text" name="job_title_ar" dir="rtl" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Department (EN)</label>
                                            <input type="text" name="department" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Department (AR)</label>
                                            <input type="text" name="department_ar" dir="rtl" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Location (EN)</label>
                                            <input type="text" name="location" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Location (AR)</label>
                                            <input type="text" name="location_ar" dir="rtl" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Type (EN)</label>
                                            <input type="text" name="type" placeholder="Full-time / Part-time / Contract" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Type (AR)</label>
                                            <input type="text" name="type_ar" dir="rtl" placeholder="Two hours full / Two hours part / Contract" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Posted date</label>
                                            <input type="date" name="posted_at" value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                        <div class="flex items-center gap-2 pt-7">
                                            <input id="job-visible" type="checkbox" name="visible" class="h-4 w-4 text-primary bg-background border-input rounded focus:ring-ring" checked>
                                            <label for="job-visible" class="text-sm text-foreground">Visible on public page</label>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Summary (EN) (shown in list)</label>
                                            <input type="text" name="summary" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Summary (AR) (shown in list)</label>
                                            <input type="text" name="summary_ar" dir="rtl" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Description (EN)</label>
                                            <textarea name="description" rows="5" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-foreground mb-2">Description (AR)</label>
                                            <textarea name="description_ar" rows="5" dir="rtl" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">Responsibilities (EN) (one per line)</label>
                                    <textarea name="responsibilities" rows="6" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">Responsibilities (AR) (one per line)</label>
                                    <textarea name="responsibilities_ar" rows="6" dir="rtl" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">Requirements (EN) (one per line)</label>
                                    <textarea name="requirements" rows="6" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-2">Requirements (AR) (one per line)</label>
                                    <textarea name="requirements_ar" rows="6" dir="rtl" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                                </div>
                            </div>

                                    <div class="flex justify-end gap-3">
                                        <button type="button" id="cancel-modal-btn" class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">Cancel</button>
                                        <button type="submit" id="submit-btn" name="add_job" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                            <i class="fas fa-save mr-2"></i>Create Job
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mobile-stats-grid grid grid-cols-2 lg:grid-cols-5 gap-3">
                        <div class="stats-card stats-card-total bg-card p-4 cursor-default">
                            <p class="text-sm text-muted-foreground">Total</p>
                            <p class="stats-value text-2xl font-heading font-bold mt-1 text-primary"><?php echo $jobs_total; ?></p>
                        </div>
                        <div class="stats-card stats-card-completed bg-card p-4 cursor-default">
                            <p class="text-sm text-muted-foreground">Visible</p>
                            <p class="stats-value text-2xl font-heading font-bold mt-1 text-green-700"><?php echo $jobs_visible; ?></p>
                        </div>
                    </div>

                    <div class="bg-card rounded-xl border border-border p-6">
                        <h3 class="text-lg font-heading font-bold text-foreground mb-4">Job Posts</h3>

                        <?php if (empty($job_posts)): ?>
                            <div class="text-sm text-muted-foreground">No job posts yet.</div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-muted-foreground">
                                            <th class="py-2 pr-4">Title</th>
                                            <th class="py-2 pr-4">Department</th>
                                            <th class="py-2 pr-4">Location</th>
                                            <th class="py-2 pr-4">Type</th>
                                            <th class="py-2 pr-4">Visible</th>
                                            <th class="py-2 pr-4">Posted</th>
                                            <th class="py-2 pr-0 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-foreground">
                                        <?php foreach ($job_posts as $slug => $j): ?>
                                            <tr class="border-t border-border">
                                                <td class="py-3 pr-4">
                                                    <div class="font-medium"><?php echo htmlspecialchars($j['title'] ?? $slug); ?></div>
                                                    <div class="text-xs text-muted-foreground break-all">Slug: <?php echo htmlspecialchars($slug); ?></div>
                                                </td>
                                                <td class="py-3 pr-4"><?php echo htmlspecialchars($j['department'] ?? ''); ?></td>
                                                <td class="py-3 pr-4"><?php echo htmlspecialchars($j['location'] ?? ''); ?></td>
                                                <td class="py-3 pr-4"><?php echo htmlspecialchars($j['type'] ?? ''); ?></td>
                                                <td class="py-3 pr-4">
                                                    <span class="text-xs px-2 py-1 rounded-full <?php echo (($j['visible'] ?? '0') === '1' || ($j['visible'] ?? 0) === 1) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'; ?>">
                                                        <?php echo (($j['visible'] ?? '0') === '1' || ($j['visible'] ?? 0) === 1) ? 'Yes' : 'No'; ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 pr-4"><?php echo htmlspecialchars($j['posted_at'] ?? ''); ?></td>
                                                <td class="py-3 pr-0">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <button type="button" class="edit-job-btn px-3 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors text-sm" data-slug="<?php echo urlencode($slug); ?>">
                                                            <i class="fas fa-edit mr-2"></i>Edit
                                                        </button>
                                                        <form method="post" onsubmit="return confirm('Delete this job post?');">
                                                            <input type="hidden" name="job_slug" value="<?php echo htmlspecialchars($slug); ?>">
                                                            <button type="submit" name="delete_job" class="px-3 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors text-sm text-destructive">
                                                                <i class="fas fa-trash mr-2"></i>Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($current_page === 'projects' || $current_page === 'dashboard'): ?>
                <!-- Projects Page (Dashboard) -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-heading font-bold text-foreground">Projects</h1>
                    <p class="text-muted-foreground mt-1">Manage and track all company projects</p>
                </div>
                <button onclick="openAddModal()" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors gap-2 inline-flex items-center">
                    <i class="fas fa-plus h-4 w-4"></i>
                    New Project
                </button>
            </div>
            
            <!-- Stats -->
            <div class="mobile-stats-grid grid grid-cols-2 lg:grid-cols-5 gap-3 mb-8">
                <?php
                $total_projects = count($projects);
                $completed = array_filter($projects, function($p) { return $p['status'] === 'completed'; });
                $in_progress = array_filter($projects, function($p) { return $p['status'] === 'in-progress'; });
                $planning = array_filter($projects, function($p) { return $p['status'] === 'planning'; });
                $on_hold = array_filter($projects, function($p) { return $p['status'] === 'on-hold'; });
                ?>
                
                <div class="stats-card stats-card-total bg-card rounded-xl p-4 hover:shadow-lg transition-all duration-300 cursor-pointer group">
                    <p class="text-sm text-muted-foreground">Total</p>
                    <p class="stats-value text-2xl font-heading font-bold mt-1 text-primary"><?php echo $total_projects; ?></p>
                </div>
                <div class="stats-card stats-card-completed bg-card rounded-xl p-4 hover:shadow-lg transition-all duration-300 cursor-pointer group">
                    <p class="text-sm text-muted-foreground">Completed</p>
                    <p class="stats-value text-2xl font-heading font-bold mt-1 text-green-700"><?php echo count($completed); ?></p>
                </div>
                <div class="stats-card stats-card-progress bg-card rounded-xl p-4 hover:shadow-lg transition-all duration-300 cursor-pointer group">
                    <p class="text-sm text-muted-foreground">In Progress</p>
                    <p class="stats-value text-2xl font-heading font-bold mt-1 text-amber-600"><?php echo count($in_progress); ?></p>
                </div>
                <div class="stats-card stats-card-planning bg-card rounded-xl p-4 hover:shadow-lg transition-all duration-300 cursor-pointer group">
                    <p class="text-sm text-muted-foreground">Planning</p>
                    <p class="stats-value text-2xl font-heading font-bold mt-1 text-blue-600"><?php echo count($planning); ?></p>
                </div>
                <div class="stats-card stats-card-hold bg-card rounded-xl p-4 hover:shadow-lg transition-all duration-300 cursor-pointer group">
                    <p class="text-sm text-muted-foreground">On Hold</p>
                    <p class="stats-value text-2xl font-heading font-bold mt-1 text-gray-600"><?php echo count($on_hold); ?></p>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-3 mb-6">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground"></i>
                    <input type="text" id="search" placeholder="Search projects or locations..." 
                           class="w-full pl-9 pr-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <select id="status-filter" class="w-full sm:w-44 px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                    <option value="all">All Statuses</option>
                    <option value="completed">Completed</option>
                    <option value="in-progress">In Progress</option>
                    <option value="planning">Planning</option>
                    <option value="on-hold">On Hold</option>
                </select>
            </div>
            
                    
        <!-- Project Cards -->
            <div id="projects-container" class="grid gap-4">
                <?php foreach ($projects as $slug => $project): ?>
                <div class="project-card bg-card border border-border rounded-xl p-5 hover:shadow-md transition-shadow" 
                     data-status="<?php echo $project['status']; ?>" 
                     data-search="<?php echo strtolower($project['title'] . ' ' . $project['location']); ?>">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        <div class="shrink-0">
                            <img
                                src="../assets/img/projects/<?php echo htmlspecialchars($slug); ?>.webp"
                                alt="<?php echo htmlspecialchars($project['title']); ?>"
                                class="w-20 h-20 md:w-24 md:h-24 rounded-xl object-cover border border-border bg-background"
                                onerror="this.onerror=null;this.src='../assets/img/construction/project-1.webp';"
                            >
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="font-heading font-semibold text-lg text-foreground truncate">
                                    <?php echo htmlspecialchars($project['title']); ?>
                                </h3>
                                <span class="status-badge px-2 py-1 rounded-full text-xs font-medium <?php echo getStatusClass($project['status']); ?>">
                                    <?php echo getStatusLabel($project['status']); ?>
                                </span>
                                <span class="visibility-badge px-2 py-1 rounded-full text-xs font-medium <?php echo ($project['visible'] ?? '0') === '1' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'; ?>">
                                    <i class="fas <?php echo ($project['visible'] ?? '0') === '1' ? 'fa-eye' : 'fa-eye-slash'; ?> mr-1"></i>
                                    <?php echo ($project['visible'] ?? '0') === '1' ? 'Visible' : 'Hidden'; ?>
                                </span>
                            </div>
                            <p class="text-sm text-muted-foreground mb-3 line-clamp-2"><?php echo htmlspecialchars($project['description']); ?></p>
                            <div class="project-meta flex flex-wrap gap-x-5 gap-y-2 text-sm text-muted-foreground">
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-map-marker-alt h-3.5 w-3.5"></i>
                                    <?php echo htmlspecialchars($project['location']); ?>
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-dollar-sign h-3.5 w-3.5"></i>
                                    <?php echo htmlspecialchars($project['contract_value']); ?>
                                </span>
                                <span class="project-meta-scope flex items-center gap-1.5">
                                    <i class="fas fa-briefcase h-3.5 w-3.5"></i>
                                    <?php echo htmlspecialchars($project['scope']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="project-file-actions flex items-center gap-2 shrink-0 mt-2">
                            <?php if (!empty($project['contract_pdf'])): ?>
                            <?php 
                            $pdf_path = __DIR__ . '/../assets/contracts/' . $project['contract_pdf'];
                            $file_exists = file_exists($pdf_path);
                            ?>
                            <a href="../assets/contracts/<?php echo htmlspecialchars($project['contract_pdf']); ?>" 
                               target="_blank"
                               class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors gap-1.5 inline-flex items-center text-sm">
                                <i class="fas fa-file-pdf h-3.5 w-3.5"></i>
                                Contract
                                <?php if (!$file_exists): ?>
                                <span class="ml-1 text-xs bg-white text-red-600 px-1 rounded">Missing</span>
                                <?php endif; ?>
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="project-main-actions flex items-center gap-2 shrink-0 mt-2">
                            <button onclick="openEditModal('<?php echo $slug; ?>')" class="px-3 py-1.5 bg-background border border-input rounded-lg hover:bg-muted transition-colors gap-1.5 inline-flex items-center text-sm">
                                <i class="fas fa-edit h-3.5 w-3.5"></i>
                                Edit
                            </button>
                            <button onclick="confirmDelete('<?php echo $slug; ?>')" class="px-3 py-1.5 bg-background border border-input rounded-lg hover:bg-muted transition-colors gap-1.5 inline-flex items-center text-sm text-destructive hover:text-destructive">
                                <i class="fas fa-trash h-3.5 w-3.5"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div id="client-no-results" class="hidden text-center py-10 text-muted-foreground">
                <i class="fas fa-search h-10 w-10 mx-auto mb-3 opacity-40"></i>
                <p class="text-base">No projects match your current filter</p>
            </div>

            <div id="pagination-wrapper" class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p id="pagination-summary" class="text-sm text-muted-foreground"></p>
                <div id="pagination-controls" class="flex items-center gap-2"></div>
            </div>
            
            <?php if (empty($projects)): ?>
            <div class="text-center py-16 text-muted-foreground">
                <i class="fas fa-folder-open h-12 w-12 mx-auto mb-3 opacity-40"></i>
                <p class="text-lg">No projects found</p>
                <p class="text-sm mt-1">Create your first project to get started</p>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Add/Edit Project Modal -->
    <div id="project-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-card border border-border modal-shell">
            <div class="modal-header">
                <div class="flex items-center justify-between">
                    <h2 id="modal-title" class="text-xl font-heading font-bold text-foreground">Add New Project</h2>
                    <button onclick="closeModal()" class="text-muted-foreground hover:text-foreground">
                        <i class="fas fa-times h-5 w-5"></i>
                    </button>
                </div>
            </div>
            <form id="project-form" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="project-slug" name="project_slug">

                    <div class="form-section">
                        <h3 class="form-section-title">Project Basics</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="title" class="block text-sm font-medium text-foreground mb-2">Project Title (EN)</label>
                                <input type="text" id="title" name="title" required class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                            <div>
                                <label for="title_ar" class="block text-sm font-medium text-foreground mb-2">Project Title (AR)</label>
                                <input type="text" id="title_ar" name="title_ar" dir="rtl" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="category" class="block text-sm font-medium text-foreground mb-2">Category</label>
                                <select id="category" name="category" required class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    <option value="Residential">Residential</option>
                                    <option value="Commercial">Commercial</option>
                                    <option value="Industrial">Industrial</option>
                                    <option value="Infrastructure">Infrastructure</option>
                                    <option value="MEP">MEP</option>
                                </select>
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-foreground mb-2">Status</label>
                                <select id="status" name="status" required class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    <option value="completed">Completed</option>
                                    <option value="in-progress">In Progress</option>
                                    <option value="planning">Planning</option>
                                    <option value="on-hold">On Hold</option>
                                </select>
                            </div>
                            <div>
                                <label for="location" class="block text-sm font-medium text-foreground mb-2">Location</label>
                                <input type="text" id="location" name="location" required class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="contract_value" class="block text-sm font-medium text-foreground mb-2">Contract Value</label>
                                <input type="text" id="contract_value" name="contract_value" required class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                            <div>
                                <label for="scope" class="block text-sm font-medium text-foreground mb-2">Scope of Work</label>
                                <input type="text" id="scope" name="scope" required class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Descriptions & Visibility</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="description" class="block text-sm font-medium text-foreground mb-2">Description (EN)</label>
                                <textarea id="description" name="description" rows="4" required class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                            </div>
                            <div>
                                <label for="description_ar" class="block text-sm font-medium text-foreground mb-2">Description (AR)</label>
                                <textarea id="description_ar" name="description_ar" rows="4" dir="rtl" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                            </div>
                        </div>
                        <div class="visibility-row">
                            <div class="flex items-center">
                                <input type="checkbox" id="visible" name="visible" value="1" checked class="h-4 w-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                <label for="visible" class="ml-2 text-sm text-foreground">
                                    <i class="fas fa-eye mr-1"></i> Publish to Website
                                </label>
                            </div>
                            <span class="text-xs text-muted-foreground">(Uncheck to hide from public website)</span>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Project Images</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="project_image" class="block text-sm font-medium text-foreground mb-2">Main Project Image</label>
                                <input type="file" id="project_image" name="project_image" accept="image/*" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                            <div>
                                <label for="construction_image" class="block text-sm font-medium text-foreground mb-2">Construction Image</label>
                                <input type="file" id="construction_image" name="construction_image" accept="image/*" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="foundation_image" class="block text-sm font-medium text-foreground mb-2">Foundation Image</label>
                                <input type="file" id="foundation_image" name="foundation_image" accept="image/*" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                            <div>
                                <label for="interior_image" class="block text-sm font-medium text-foreground mb-2">Interior Image</label>
                                <input type="file" id="interior_image" name="interior_image" accept="image/*" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                            <div>
                                <label for="architecture_image" class="block text-sm font-medium text-foreground mb-2">Architecture Image</label>
                                <input type="file" id="architecture_image" name="architecture_image" accept="image/*" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                            <div>
                                <label for="blueprint_image" class="block text-sm font-medium text-foreground mb-2">Blueprint Review Image</label>
                                <input type="file" id="blueprint_image" name="blueprint_image" accept="image/*" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                            <div>
                                <label for="quality_control_image" class="block text-sm font-medium text-foreground mb-2">Quality Control Image</label>
                                <input type="file" id="quality_control_image" name="quality_control_image" accept="image/*" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                            <div>
                                <label for="system_installation_image" class="block text-sm font-medium text-foreground mb-2">System Installation Image</label>
                                <input type="file" id="system_installation_image" name="system_installation_image" accept="image/*" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                            </div>
                        </div>
                    </div>

                    <div class="form-section mb-0">
                        <h3 class="form-section-title">Contract File</h3>
                        <label for="contract_pdf" class="block text-sm font-medium text-foreground mb-2">Project Contract PDF</label>
                        <input type="file" id="contract_pdf" name="contract_pdf" accept=".pdf,application/pdf" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                        <p class="text-xs text-muted-foreground mt-1">Upload project contract PDF (optional)</p>
                        <div id="current-pdf" class="mt-2 hidden">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm text-muted-foreground">Current PDF:</p>
                                <div class="flex items-center gap-2">
                                    <a href="#" id="current-pdf-link" class="text-sm text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                        <i class="fas fa-file-pdf"></i>
                                        <span id="current-pdf-name"></span>
                                    </a>
                                    <button type="button" id="delete-pdf-btn" onclick="showDeletePDFModal()" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700 transition-colors inline-flex items-center gap-1">
                                        <i class="fas fa-trash h-3 w-3"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">
                        Cancel
                    </button>
                    <button type="submit" name="<?php echo !empty($_POST['project_slug']) || !empty($_GET['edit']) ? 'edit_project' : 'add_project'; ?>" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        <?php echo !empty($_POST['project_slug']) || !empty($_GET['edit']) ? 'Update Project' : 'Create Project'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-card rounded-xl border border-border max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 bg-destructive/10 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-exclamation-triangle text-destructive"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-heading font-bold text-foreground">Delete Project</h3>
                        <p class="text-muted-foreground text-sm">This action cannot be undone</p>
                    </div>
                </div>
                
                <p class="text-muted-foreground mb-6">Are you sure you want to delete "<span id="delete-project-name"></span>"?</p>
                
                <form id="delete-form" method="post">
                    <input type="hidden" id="delete-project-slug" name="project_slug">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeleteModal()" 
                                class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button type="submit" name="delete_project" 
                                class="px-4 py-2 bg-destructive text-destructive-foreground rounded-lg hover:bg-destructive/90 transition-colors">
                            <i class="fas fa-trash mr-2"></i>
                            Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- PDF Delete Confirmation Modal -->
    <div id="delete-pdf-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-card rounded-xl border border-border max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-file-pdf text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-heading font-bold text-foreground">Delete Contract PDF</h3>
                        <p class="text-muted-foreground text-sm">This action cannot be undone</p>
                    </div>
                </div>
                
                <p class="text-muted-foreground mb-6">Are you sure you want to delete the contract PDF "<span id="delete-pdf-name"></span>"?</p>
                
                <form id="delete-pdf-form" method="post">
                    <input type="hidden" id="delete-pdf-project-slug" name="project_slug">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeletePDFModal()" 
                                class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button type="submit" name="delete_contract_pdf" 
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-trash mr-2"></i>
                            Delete PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <div id="session-message-modal" class="session-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="session-message-title">
        <div class="session-modal-card">
            <h3 id="session-message-title" class="session-modal-title">Session Message</h3>
            <p id="session-message-text" class="session-modal-message"></p>
            <div class="session-modal-actions">
                <button id="session-message-btn" type="button" class="session-btn session-btn-primary">OK</button>
            </div>
        </div>
    </div>

    <div id="session-warning-modal" class="session-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="session-warning-title">
        <div class="session-modal-card">
            <h3 id="session-warning-title" class="session-modal-title">Still there?</h3>
            <p class="session-modal-message">
                You have been inactive for almost 20 minutes. For your security, you will be logged out unless you continue.
            </p>
            <div id="session-warning-countdown" class="session-modal-countdown">60s</div>
            <div class="session-modal-actions">
                <button id="continue-session-btn" type="button" class="session-btn session-btn-primary">Yes, I am here</button>
                <button id="logout-now-btn" type="button" class="session-btn session-btn-muted">Logout now</button>
            </div>
        </div>
    </div>
    
    <script>
        // Global projects data
        const projects = <?php echo json_encode($projects); ?>;
        const teamMembers = <?php echo json_encode($team_members ?? []); ?>;
        
        function isMobileLayout() {
            return window.matchMedia('(max-width: 1024px)').matches;
        }

        function closeMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar) sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
        }

        function applyResponsiveSidebarState() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const toggleIcon = document.getElementById('sidebar-toggle-icon');

            if (!sidebar || !mainContent) return;

            if (isMobileLayout()) {
                sidebar.classList.remove('sidebar-collapsed', 'sidebar-expanded');
                mainContent.classList.remove('main-content-collapsed', 'main-content-shifted');
                mainContent.classList.add('main-content-mobile');
                if (toggleIcon) {
                    toggleIcon.classList.remove('fa-chevron-right');
                    toggleIcon.classList.add('fa-chevron-left');
                }
                sidebarTexts.forEach(text => text.style.display = 'block');
                closeMobileSidebar();
            } else {
                sidebar.classList.remove('mobile-open');
                mainContent.classList.remove('main-content-mobile');
                if (!sidebar.classList.contains('sidebar-collapsed')) {
                    sidebar.classList.add('sidebar-expanded');
                    mainContent.classList.remove('main-content-collapsed');
                    mainContent.classList.add('main-content-shifted');
                    if (toggleIcon) {
                        toggleIcon.classList.remove('fa-chevron-right');
                        toggleIcon.classList.add('fa-chevron-left');
                    }
                    sidebarTexts.forEach(text => text.style.display = 'block');
                }
            }
        }

        // Sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const toggleIcon = document.getElementById('sidebar-toggle-icon');
            const overlay = document.getElementById('sidebar-overlay');

            if (isMobileLayout()) {
                sidebar.classList.toggle('mobile-open');
                if (overlay) overlay.classList.toggle('active');
                return;
            }
            
            if (sidebar.classList.contains('sidebar-expanded')) {
                sidebar.classList.remove('sidebar-expanded');
                sidebar.classList.add('sidebar-collapsed');
                mainContent.classList.remove('main-content-shifted');
                mainContent.classList.add('main-content-collapsed');
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
                sidebarTexts.forEach(text => text.style.display = 'none');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');
                mainContent.classList.remove('main-content-collapsed');
                mainContent.classList.add('main-content-shifted');
                toggleIcon.classList.remove('fa-chevron-right');
                toggleIcon.classList.add('fa-chevron-left');
                setTimeout(() => {
                    sidebarTexts.forEach(text => text.style.display = 'block');
                }, 150);
            }
        }
        
        // Modal functions
        function openAddModal() {
            document.getElementById('modal-title').textContent = 'Add New Project';
            document.getElementById('project-form').reset();
            document.getElementById('project-slug').value = '';
            
            // Reset submit button text and name
            const submitButton = document.querySelector('button[type="submit"]');
            submitButton.textContent = 'Create Project';
            submitButton.name = 'add_project';
            submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Create Project';
            
            // Hide current PDF display
            document.getElementById('current-pdf').classList.add('hidden');
            
            document.getElementById('project-modal').classList.remove('hidden');
        }
        
        // Contract Management Functions
        let currentProjectSlug = null;
        
        function showDeletePDFModal() {
            const project = projects[currentProjectSlug];
            
            if (project && project.contract_pdf && project.contract_pdf !== '') {
                document.getElementById('delete-pdf-name').textContent = project.contract_pdf;
                document.getElementById('delete-pdf-project-slug').value = currentProjectSlug;
                document.getElementById('delete-pdf-modal').classList.remove('hidden');
            }
        }
        
        function closeDeletePDFModal() {
            document.getElementById('delete-pdf-modal').classList.add('hidden');
        }
        
        // Handle file input change to simply allow upload (no management options)
        document.addEventListener('DOMContentLoaded', function() {
            const contractPdfInput = document.getElementById('contract_pdf');
            if (contractPdfInput) {
                contractPdfInput.addEventListener('change', function() {
                    // When a new file is selected, it will automatically replace any existing PDF
                    // No need for management options - simple upload and replace
                });
            }
        });
        
        // Active navigation tab switching
        document.addEventListener('DOMContentLoaded', function() {
            applyResponsiveSidebarState();
            window.addEventListener('resize', applyResponsiveSidebarState);

            const navItems = document.querySelectorAll('.nav-item');
            const urlParams = new URLSearchParams(window.location.search);
            const page = urlParams.get('page');
            
            // Remove existing active classes
            navItems.forEach(item => {
                item.classList.remove('active');
            });
            
            // Set active based on current page
            navItems.forEach(item => {
                const href = item.getAttribute('href');
                
                // Check for specific pages
                if (page === 'settings' && href.includes('page=settings')) {
                    item.classList.add('active');
                }
                // Check for team page
                else if (page === 'team' && href.includes('page=team')) {
                    item.classList.add('active');
                }
                // Check for careers page
                else if (page === 'careers' && href.includes('page=careers')) {
                    item.classList.add('active');
                }
                // Check for certifications page
                else if (page === 'certifications' && href.includes('page=certifications')) {
                    item.classList.add('active');
                }
                // Check for testimonials page
                else if (page === 'testimonials' && href.includes('page=testimonials')) {
                    item.classList.add('active');
                }
                // Check for dashboard (no page parameter)
                else if (!page && href === 'projects-new.php') {
                    item.classList.add('active');
                }
            });
            
            // Add click handlers for smooth transitions
            navItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    if (isMobileLayout()) {
                        closeMobileSidebar();
                    }
                    // Remove active from all items
                    navItems.forEach(nav => nav.classList.remove('active'));
                    // Add active to clicked item
                    this.classList.add('active');
                });
            });
        });
        
        // Update openEditModal to handle contract management
        function openEditModal(slug) {
            const project = projects[slug];
            currentProjectSlug = slug; // Set current project for contract management
            
            if (project) {
                document.getElementById('modal-title').textContent = 'Edit Project';
                document.getElementById('project-slug').value = slug;
                document.getElementById('title').value = project.title;
                document.getElementById('title_ar').value = project.title_ar || '';
                document.getElementById('category').value = project.category;
                document.getElementById('status').value = project.status;
                document.getElementById('location').value = project.location;
                document.getElementById('contract_value').value = project.contract_value;
                document.getElementById('scope').value = project.scope;
                document.getElementById('description').value = project.description;
                document.getElementById('description_ar').value = project.description_ar || '';
                
                // Handle visibility checkbox
                const visibleCheckbox = document.getElementById('visible');
                visibleCheckbox.checked = (project.visible === '1' || project.visible === 1);
                
                // Update submit button text and name
                const submitButton = document.querySelector('button[type="submit"]');
                submitButton.textContent = 'Update Project';
                submitButton.name = 'edit_project';
                submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Update Project';
                
                // Handle current PDF display
                const currentPdfDiv = document.getElementById('current-pdf');
                const currentPdfLink = document.getElementById('current-pdf-link');
                const currentPdfName = document.getElementById('current-pdf-name');
                
                if (project.contract_pdf && project.contract_pdf !== '') {
                    currentPdfDiv.classList.remove('hidden');
                    currentPdfLink.href = '../assets/contracts/' + project.contract_pdf;
                    currentPdfLink.target = '_blank';
                    currentPdfName.textContent = project.contract_pdf;
                } else {
                    currentPdfDiv.classList.add('hidden');
                }
                
                document.getElementById('project-modal').classList.remove('hidden');
            }
        }
        
        // Update form submission to handle contract actions
        document.addEventListener('DOMContentLoaded', function() {
            const projectForm = document.getElementById('project-form');
            if (projectForm) {
                projectForm.addEventListener('submit', function(e) {
                    // No need for contract management actions anymore
                    // Simple logic: if new PDF uploaded, it replaces existing
                    // If delete action triggered, it's handled by separate form
                });
            }
        });
        
        // Update dropdown color based on selected status
        function updateDropdownColor(selectElement) {
            const selectedValue = selectElement.value;
            
            // Remove all selected color classes
            selectElement.classList.remove('completed-selected', 'in-progress-selected', 'planning-selected', 'on-hold-selected');
            
            // Add the appropriate selected color class
            switch(selectedValue) {
                case 'completed':
                    selectElement.classList.add('completed-selected');
                    break;
                case 'in-progress':
                    selectElement.classList.add('in-progress-selected');
                    break;
                case 'planning':
                    selectElement.classList.add('planning-selected');
                    break;
                case 'on-hold':
                    selectElement.classList.add('on-hold-selected');
                    break;
            }
        }
        
        // Initialize dropdown colors on page load
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelects = document.querySelectorAll('select[onchange*="updateStatus"]');
            statusSelects.forEach(select => {
                updateDropdownColor(select);
                
                // Add change event listener to update color when selection changes
                select.addEventListener('change', function() {
                    updateDropdownColor(this);
                });
            });
        });
        
        // Update openAddModal to reset contract management
        function openAddModal() {
            currentProjectSlug = null; // Reset for new projects
            document.getElementById('modal-title').textContent = 'Add New Project';
            document.getElementById('project-form').reset();
            document.getElementById('project-slug').value = '';
            document.getElementById('visible').checked = true;
            
            // Reset submit button text and name
            const submitButton = document.querySelector('button[type="submit"]');
            submitButton.textContent = 'Create Project';
            submitButton.name = 'add_project';
            submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Create Project';
            
            // Hide current PDF display
            document.getElementById('current-pdf').classList.add('hidden');
            
            document.getElementById('project-modal').classList.remove('hidden');
        }
        
        // Show delete confirmation modal
        function confirmDelete(projectSlug) {
            console.log('confirmDelete called with slug:', projectSlug);
            const project = projects[projectSlug];
            if (!project) {
                console.error('Project not found:', projectSlug);
                return;
            }
            
            console.log('Project found:', project.title);
            document.getElementById('delete-project-name').textContent = project.title;
            document.getElementById('delete-project-slug').value = projectSlug;
            document.getElementById('delete-modal').classList.remove('hidden');
        }
        
        // Close delete modal
        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.add('hidden');
        }
        
        // Close project modal
        function closeModal() {
            document.getElementById('project-modal').classList.add('hidden');
        }
        
        // Search, filter, and pagination
        const projectsPerPage = 10;
        let currentProjectsPage = 1;

        function getFilteredProjectCards() {
            const searchInput = document.getElementById('search');
            const statusFilterSelect = document.getElementById('status-filter');
            const projectCards = Array.from(document.querySelectorAll('.project-card'));

            if (!searchInput || !statusFilterSelect || projectCards.length === 0) {
                return [];
            }

            const searchTerm = searchInput.value.toLowerCase().trim();
            const statusFilter = statusFilterSelect.value;

            return projectCards.filter(card => {
                const searchMatch = card.dataset.search.includes(searchTerm);
                const statusMatch = statusFilter === 'all' || card.dataset.status === statusFilter;
                return searchMatch && statusMatch;
            });
        }

        function renderPagination(totalPages) {
            const controls = document.getElementById('pagination-controls');
            if (!controls) return;
            controls.innerHTML = '';

            if (totalPages <= 1) return;

            const createButton = (label, page, disabled = false, active = false) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'pagination-btn' + (active ? ' active' : '');
                btn.textContent = label;
                btn.disabled = disabled;
                if (!disabled) {
                    btn.addEventListener('click', () => {
                        currentProjectsPage = page;
                        filterProjects();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                }
                controls.appendChild(btn);
            };

            createButton('Prev', Math.max(1, currentProjectsPage - 1), currentProjectsPage === 1);

            const startPage = Math.max(1, currentProjectsPage - 2);
            const endPage = Math.min(totalPages, startPage + 4);

            for (let page = startPage; page <= endPage; page++) {
                createButton(String(page), page, false, page === currentProjectsPage);
            }

            createButton('Next', Math.min(totalPages, currentProjectsPage + 1), currentProjectsPage === totalPages);
        }

        function filterProjects(resetPage = false) {
            const allCards = Array.from(document.querySelectorAll('.project-card'));
            if (allCards.length === 0) return;

            if (resetPage) {
                currentProjectsPage = 1;
            }

            const filteredCards = getFilteredProjectCards();
            const totalFiltered = filteredCards.length;
            const totalPages = Math.max(1, Math.ceil(totalFiltered / projectsPerPage));

            if (currentProjectsPage > totalPages) {
                currentProjectsPage = totalPages;
            }

            const start = (currentProjectsPage - 1) * projectsPerPage;
            const end = start + projectsPerPage;
            const visibleCards = new Set(filteredCards.slice(start, end));

            allCards.forEach(card => {
                card.style.display = visibleCards.has(card) ? 'block' : 'none';
            });

            const noResults = document.getElementById('client-no-results');
            if (noResults) {
                noResults.classList.toggle('hidden', totalFiltered !== 0);
            }

            const paginationSummary = document.getElementById('pagination-summary');
            const paginationWrapper = document.getElementById('pagination-wrapper');
            const from = totalFiltered === 0 ? 0 : start + 1;
            const to = Math.min(end, totalFiltered);

            if (paginationSummary) {
                paginationSummary.textContent = totalFiltered === 0
                    ? 'Showing 0 projects'
                    : `Showing ${from}-${to} of ${totalFiltered} projects`;
            }

            if (paginationWrapper) {
                paginationWrapper.classList.toggle('hidden', totalFiltered === 0);
            }

            renderPagination(totalPages);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const statusFilterSelect = document.getElementById('status-filter');

            if (searchInput && statusFilterSelect) {
                searchInput.addEventListener('input', () => filterProjects(true));
                statusFilterSelect.addEventListener('change', () => filterProjects(true));
                filterProjects(true);
            }
        });
        
        // Close modals on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
                closeDeleteModal();
            }
        });
        
        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target === document.getElementById('project-modal')) {
                closeModal();
            }
            if (event.target === document.getElementById('delete-modal')) {
                closeDeleteModal();
            }
        });
        
        // Simple tab switching function
        function showTab(tabName, buttonElement) {
            console.log('Switching to tab:', tabName);
            
            // Hide all tabs
            document.getElementById('general-tab').style.display = 'none';
            document.getElementById('security-tab').style.display = 'none';
            document.getElementById('backup-tab').style.display = 'none';
            
            // Remove active state from all buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(btn => {
                btn.classList.remove('border-primary', 'text-primary');
                btn.classList.add('border-transparent', 'text-muted-foreground');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').style.display = 'block';
            
            // Add active state to clicked button
            buttonElement.classList.remove('border-transparent', 'text-muted-foreground');
            buttonElement.classList.add('border-primary', 'text-primary');
            
            console.log('Tab switched to:', tabName);
        }
        
        // Backup functionality
        function createBackup() {
            window.location.href = 'projects-new.php?action=backup';
        }
        
        function restoreBackup() {
            const fileInput = document.getElementById('backup_file');
            if (fileInput.files.length === 0) {
                alert('Please select a backup file to restore');
                return;
            }
            
            if (confirm('Are you sure you want to restore from backup? This will replace all current project data.')) {
                const formData = new FormData();
                formData.append('backup_file', fileInput.files[0]);
                formData.append('restore_backup', '1');
                
                fetch('projects-new.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.text())
                  .then(data => {
                      window.location.reload();
                  })
                  .catch(error => {
                      console.error('Error:', error);
                      alert('Error restoring backup');
                  });
            }
        }
        
        // Password validation functions
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                eyeIcon.className = 'fas fa-eye';
            }
        }
        
        function validatePassword() {
            const password = document.getElementById('new_password').value;
            const strengthDiv = document.getElementById('password-strength');
            const strengthText = document.getElementById('strength-text');
            const strengthBar = document.getElementById('strength-bar');
            
            // Requirements
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*]/.test(password)
            };
            
            // Update requirement indicators
            updateRequirement('length', requirements.length);
            updateRequirement('uppercase', requirements.uppercase);
            updateRequirement('lowercase', requirements.lowercase);
            updateRequirement('number', requirements.number);
            updateRequirement('special', requirements.special);
            
            // Calculate strength
            const passedRequirements = Object.values(requirements).filter(req => req).length;
            let strength = 0;
            let strengthLabel = '';
            let strengthColor = '';
            
            if (password.length === 0) {
                strengthDiv.classList.add('hidden');
                return;
            }
            
            strengthDiv.classList.remove('hidden');
            
            if (passedRequirements <= 2) {
                strength = 25;
                strengthLabel = 'Weak';
                strengthColor = 'bg-red-500';
            } else if (passedRequirements <= 3) {
                strength = 50;
                strengthLabel = 'Fair';
                strengthColor = 'bg-yellow-500';
            } else if (passedRequirements <= 4) {
                strength = 75;
                strengthLabel = 'Good';
                strengthColor = 'bg-blue-500';
            } else {
                strength = 100;
                strengthLabel = 'Strong';
                strengthColor = 'bg-green-500';
            }
            
            strengthText.textContent = strengthLabel;
            strengthText.className = 'font-medium ' + strengthColor.replace('bg-', 'text-');
            strengthBar.style.width = strength + '%';
            strengthBar.className = 'h-2 rounded-full transition-all duration-300 ' + strengthColor;
        }
        
        function updateRequirement(req, passed) {
            const icon = document.getElementById('req-' + req + '-icon');
            if (passed) {
                icon.className = 'fas fa-check-circle text-green-500 mr-2';
            } else {
                icon.className = 'fas fa-times-circle text-red-500 mr-2';
            }
        }
        
        function validatePasswordMatch() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('password-match');
            const matchText = document.getElementById('match-text');
            
            if (confirmPassword.length === 0) {
                matchDiv.classList.add('hidden');
                return;
            }
            
            matchDiv.classList.remove('hidden');
            
            if (password === confirmPassword) {
                matchText.textContent = '✓ Passwords match';
                matchText.className = 'text-green-600';
            } else {
                matchText.textContent = '✗ Passwords do not match';
                matchText.className = 'text-red-600';
            }
        }
        
        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (newPassword && confirmPassword) {
                confirmPassword.addEventListener('input', function() {
                    if (newPassword.value !== confirmPassword.value) {
                        confirmPassword.setCustomValidity('Passwords do not match');
                    } else {
                        confirmPassword.setCustomValidity('');
                    }
                });
            }
        });
        
        // Security: Activity tracking, inactivity warning, and auto-logout
        let warningTimer;
        let logoutTimer;
        let warningCountdownInterval;
        let warningCountdownSeconds = 60;
        let lastHeartbeatAt = 0;
        const sessionTimeout = 20 * 60 * 1000; // 20 minutes
        const warningDuration = 60 * 1000; // show warning 60 seconds before logout
        const warningAt = sessionTimeout - warningDuration;
        let isWarningOpen = false;

        function showSessionMessageModal(title, message, onConfirm) {
            const modal = document.getElementById('session-message-modal');
            const titleEl = document.getElementById('session-message-title');
            const textEl = document.getElementById('session-message-text');
            const btn = document.getElementById('session-message-btn');
            if (!modal || !titleEl || !textEl || !btn) return;

            titleEl.textContent = title;
            textEl.textContent = message;
            modal.classList.add('active');

            btn.onclick = function() {
                modal.classList.remove('active');
                if (typeof onConfirm === 'function') {
                    onConfirm();
                }
            };
        }

        function closeWarningModal() {
            const modal = document.getElementById('session-warning-modal');
            if (modal) modal.classList.remove('active');
            clearInterval(warningCountdownInterval);
            isWarningOpen = false;
        }

        function updateServerActivity(force = false) {
            const now = Date.now();
            if (!force && now - lastHeartbeatAt < 30000) {
                return;
            }
            lastHeartbeatAt = now;

            fetch('projects-new.php?heartbeat=1', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).catch(() => {});
        }

        function logoutDueToTimeout() {
            window.location.href = 'projects-new.php?timeout=1';
        }

        function showInactivityWarning() {
            const modal = document.getElementById('session-warning-modal');
            const countdownEl = document.getElementById('session-warning-countdown');
            if (!modal || !countdownEl || isWarningOpen) return;

            isWarningOpen = true;
            warningCountdownSeconds = Math.ceil(warningDuration / 1000);
            countdownEl.textContent = `${warningCountdownSeconds}s`;
            modal.classList.add('active');

            clearInterval(warningCountdownInterval);
            warningCountdownInterval = setInterval(() => {
                warningCountdownSeconds -= 1;
                countdownEl.textContent = `${Math.max(0, warningCountdownSeconds)}s`;
                if (warningCountdownSeconds <= 0) {
                    clearInterval(warningCountdownInterval);
                }
            }, 1000);
        }

        function resetInactivityTimers() {
            clearTimeout(warningTimer);
            clearTimeout(logoutTimer);

            warningTimer = setTimeout(showInactivityWarning, warningAt);
            logoutTimer = setTimeout(logoutDueToTimeout, sessionTimeout);
        }

        function handleUserActivity() {
            if (isWarningOpen) {
                closeWarningModal();
            }
            resetInactivityTimers();
            updateServerActivity();
        }

        // Track user activity and show session/security messages
        document.addEventListener('DOMContentLoaded', function() {
            const continueBtn = document.getElementById('continue-session-btn');
            const logoutNowBtn = document.getElementById('logout-now-btn');
            const isAuthenticatedView = !!document.getElementById('main-content');

            if (continueBtn) {
                continueBtn.addEventListener('click', function() {
                    handleUserActivity();
                    updateServerActivity(true);
                });
            }

            if (logoutNowBtn) {
                logoutNowBtn.addEventListener('click', function() {
                    window.location.href = 'projects-new.php?logout=true';
                });
            }

            if (isAuthenticatedView) {
                const activityEvents = [
                    'mousedown', 'mousemove', 'keypress', 'scroll',
                    'touchstart', 'click', 'keydown'
                ];

                activityEvents.forEach(event => {
                    document.addEventListener(event, handleUserActivity, { passive: true });
                });

                resetInactivityTimers();
                updateServerActivity(true);
            }

            // Check for timeout/security parameters and show centered modal
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('timeout') === '1') {
                showSessionMessageModal(
                    'Session Expired',
                    'Your session has expired due to inactivity. Please login again.'
                );
                // Clear URL parameters to prevent modal from showing again
                const params = new URLSearchParams(window.location.search);
                params.delete('timeout');
                window.history.replaceState({}, '', 'projects-new.php' + (params.toString() ? '?' + params.toString() : ''));
            }
            if (urlParams.get('security') === '1') {
                showSessionMessageModal(
                    'Security Alert',
                    'Security alert: Your session has been terminated for security reasons. Please login again.'
                );
                // Clear URL parameters to prevent modal from showing again
                const params = new URLSearchParams(window.location.search);
                params.delete('security');
                window.history.replaceState({}, '', 'projects-new.php' + (params.toString() ? '?' + params.toString() : ''));
            }
        });

        // Team modals
        function openMemberAddModal() {
            const form = document.getElementById('member-form');
            if (!form) return;
            form.reset();
            document.getElementById('member-modal-title').textContent = 'Add Member';
            document.getElementById('member-slug').value = '';
            document.getElementById('member-current-photo').textContent = '';
            document.getElementById('member-visible').checked = true;

            const submitBtn = document.getElementById('member-submit-btn');
            submitBtn.name = 'add_member';
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Create Member';
            document.getElementById('member-modal').classList.remove('hidden');
        }

        function openMemberEditModal(slug) {
            const member = teamMembers[slug];
            if (!member) return;

            const form = document.getElementById('member-form');
            if (!form) return;
            form.reset();

            document.getElementById('member-modal-title').textContent = 'Edit Member';
            document.getElementById('member-slug').value = slug;
            document.getElementById('member-name').value = member.name || '';
            document.getElementById('member-role').value = member.role || '';
            document.getElementById('member-layout').value = (member.layout === 'featured') ? 'featured' : 'compact';
            document.getElementById('member-experience').value = member.experience || '';
            document.getElementById('member-email').value = member.email || '';
            document.getElementById('member-phone').value = member.phone || '';
            document.getElementById('member-description').value = member.description || '';
            document.getElementById('member-visible').checked = (member.visible === '1' || member.visible === 1 || member.visible === true);

            document.getElementById('member-current-photo').textContent = member.photo ? ('Current: ' + member.photo) : '';

            const socials = member.socials || {};
            document.getElementById('member-social-linkedin').value = socials.linkedin || '';
            document.getElementById('member-social-twitter').value = socials.twitter || '';
            document.getElementById('member-social-facebook').value = socials.facebook || '';
            document.getElementById('member-social-instagram').value = socials.instagram || '';

            const qc = member.quick_contact || {};
            document.getElementById('member-qc-email').value = qc.email || '';
            document.getElementById('member-qc-phone').value = qc.phone || '';
            document.getElementById('member-qc-linkedin').value = qc.linkedin || '';

            const creds = Array.isArray(member.credentials) ? member.credentials : [];
            document.getElementById('member-credential-1-icon').value = creds[0]?.icon || 'bi-award';
            document.getElementById('member-credential-1').value = creds[0]?.label || '';
            document.getElementById('member-credential-2-icon').value = creds[1]?.icon || 'bi-award';
            document.getElementById('member-credential-2').value = creds[1]?.label || '';

            const skills = Array.isArray(member.skills) ? member.skills : [];
            document.getElementById('member-skills').value = skills.join("\n");

            const submitBtn = document.getElementById('member-submit-btn');
            submitBtn.name = 'edit_member';
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Member';

            document.getElementById('member-modal').classList.remove('hidden');
        }

        function closeMemberModal() {
            const modal = document.getElementById('member-modal');
            if (modal) modal.classList.add('hidden');
        }

        function openMemberDeleteModal(slug) {
            const member = teamMembers[slug];
            if (!member) return;
            document.getElementById('member-delete-name').textContent = member.name || slug;
            document.getElementById('member-delete-slug').value = slug;
            document.getElementById('member-delete-modal').classList.remove('hidden');
        }

        function closeMemberDeleteModal() {
            const modal = document.getElementById('member-delete-modal');
            if (modal) modal.classList.add('hidden');
        }

        // Close team modals on escape/outside click
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeMemberModal();
                closeMemberDeleteModal();
            }
        });

        document.addEventListener('click', function(event) {
            const memberModal = document.getElementById('member-modal');
            const deleteModal = document.getElementById('member-delete-modal');
            if (event.target === memberModal) closeMemberModal();
            if (event.target === deleteModal) closeMemberDeleteModal();
        });
        
        // Handle page visibility change
        document.addEventListener('visibilitychange', function() {
            const isAuthenticatedView = !!document.getElementById('main-content');
            if (document.visibilityState === 'visible' && isAuthenticatedView) {
                // When page becomes visible, check session validity
                fetch('projects-new.php?check_session=1', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        window.location.href = 'projects-new.php?security=1';
                    }
                })
                .catch(error => {})
                .catch(error => console.error('Session check error:', error));
            }
        });

        // Add Job Modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const addJobBtn = document.getElementById('add-job-btn');
            const addJobModal = document.getElementById('add-job-modal');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const cancelModalBtn = document.getElementById('cancel-modal-btn');
            const addJobForm = document.getElementById('add-job-form');
            const modalTitle = document.getElementById('modal-title');
            const submitBtn = document.getElementById('submit-btn');
            const editJobSlug = document.getElementById('edit-job-slug');
            const isEditMode = document.getElementById('is-edit-mode');

            // Job data for editing
            const jobData = <?php echo json_encode($job_posts ?? []); ?>;

            // Open modal for Add
            if (addJobBtn) {
                addJobBtn.addEventListener('click', function() {
                    resetModal();
                    modalTitle.textContent = 'Add New Job';
                    submitBtn.name = 'add_job';
                    submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Create Job';
                    isEditMode.value = '0';
                    openModal();
                });
            }

            // Open modal for Edit
            document.addEventListener('click', function(event) {
                if (event.target.closest('.edit-job-btn')) {
                    const editBtn = event.target.closest('.edit-job-btn');
                    const slug = editBtn.dataset.slug;
                    const job = jobData[slug];
                    
                    if (job) {
                        populateModal(job, slug);
                        modalTitle.textContent = 'Edit Job';
                        submitBtn.name = 'edit_job';
                        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Job';
                        isEditMode.value = '1';
                        openModal();
                    }
                }
            });

            function openModal() {
                addJobModal.classList.remove('hidden');
                addJobModal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }

            // Close modal functions
            function closeModal() {
                addJobModal.classList.add('hidden');
                addJobModal.classList.remove('flex');
                document.body.style.overflow = 'auto';
                resetModal();
            }

            function resetModal() {
                if (addJobForm) {
                    addJobForm.reset();
                }
                editJobSlug.value = '';
                isEditMode.value = '0';
            }

            function populateModal(job, slug) {
                editJobSlug.value = slug;
                
                // Populate form fields with job data
                const fields = {
                    'job_title': job.title_en || job.title || '',
                    'job_title_ar': job.title_ar || '',
                    'department': job.department_en || job.department || '',
                    'department_ar': job.department_ar || '',
                    'location': job.location_en || job.location || '',
                    'location_ar': job.location_ar || '',
                    'type': job.type_en || job.type || '',
                    'type_ar': job.type_ar || '',
                    'posted_at': job.posted_at || '',
                    'summary': job.summary_en || job.summary || '',
                    'summary_ar': job.summary_ar || '',
                    'description': job.description_en || job.description || '',
                    'description_ar': job.description_ar || '',
                    'responsibilities': Array.isArray(job.responsibilities_en || job.responsibilities) ? (job.responsibilities_en || job.responsibilities).join('\n') : '',
                    'responsibilities_ar': Array.isArray(job.responsibilities_ar || []) ? (job.responsibilities_ar || []).join('\n') : '',
                    'requirements': Array.isArray(job.requirements_en || job.requirements) ? (job.requirements_en || job.requirements).join('\n') : '',
                    'requirements_ar': Array.isArray(job.requirements_ar || []) ? (job.requirements_ar || []).join('\n') : ''
                };

                // Set field values
                Object.keys(fields).forEach(fieldName => {
                    const field = addJobForm.elements[fieldName];
                    if (field) {
                        field.value = fields[fieldName];
                    }
                });

                // Set checkbox
                const visibleCheckbox = addJobForm.elements['visible'];
                if (visibleCheckbox) {
                    visibleCheckbox.checked = (job.visible === '1' || job.visible === 1);
                }
            }

            // Close modal on X button click
            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', closeModal);
            }

            // Close modal on Cancel button click
            if (cancelModalBtn) {
                cancelModalBtn.addEventListener('click', closeModal);
            }

            // Close modal on outside click
            addJobModal.addEventListener('click', function(event) {
                if (event.target === addJobModal) {
                    closeModal();
                }
            });

            // Close modal on Escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && !addJobModal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        });

        // Inactivity Timeout Notification System
        let inactivityTimer;
        let countdownTimer;
        let countdownInterval;
        let warningShown = false;
        
        const INACTIVITY_LIMIT = 18 * 60 * 1000; // 18 minutes (show warning 2 minutes before timeout)
        const COUNTDOWN_DURATION = 60 * 1000; // 1 minute countdown
        const SESSION_TIMEOUT = 20 * 60 * 1000; // 20 minutes total session timeout
        
        // Create the inactivity warning dialog
        const warningDialog = document.createElement('div');
        warningDialog.id = 'inactivity-warning';
        warningDialog.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden';
        warningDialog.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md mx-4 transform transition-all">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Are you still there?</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Your session is about to expire due to inactivity. You will be automatically logged out in:
                    </p>
                    <div class="text-3xl font-bold text-red-600 mb-6" id="countdown-timer">1:00</div>
                    <div class="flex justify-center">
                        <button id="stay-logged-in" class="bg-blue-600 text-white px-8 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-check mr-2"></i>Yes, I'm here
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(warningDialog);
        
        // Prevent activity events when interacting with the dialog
        warningDialog.addEventListener('mousedown', (e) => {
            e.stopPropagation();
        });
        
        warningDialog.addEventListener('mousemove', (e) => {
            e.stopPropagation();
        });
        
        warningDialog.addEventListener('click', (e) => {
            e.stopPropagation();
        });
        
        warningDialog.addEventListener('keypress', (e) => {
            e.stopPropagation();
        });
        
        // Function to reset inactivity timer
        function resetInactivityTimer() {
            // Don't reset if warning dialog is visible (user is interacting with it)
            if (warningShown && !warningDialog.classList.contains('hidden')) {
                return;
            }
            
            clearTimeout(inactivityTimer);
            clearTimeout(countdownTimer);
            clearInterval(countdownInterval);
            warningShown = false;
            
            // Hide warning dialog if it's showing
            warningDialog.classList.add('hidden');
            
            // Start new timer
            inactivityTimer = setTimeout(showWarning, window.INACTIVITY_LIMIT || INACTIVITY_LIMIT);
        }
        
        // Function to show warning dialog
        function showWarning() {
            warningShown = true;
            warningDialog.classList.remove('hidden');
            
            let timeLeft = (window.COUNTDOWN_DURATION || COUNTDOWN_DURATION) / 1000; // Convert to seconds
            const countdownElement = document.getElementById('countdown-timer');
            
            // Update countdown display
            countdownInterval = setInterval(() => {
                timeLeft--;
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    logout();
                }
            }, 1000);
            
            // Auto logout after countdown
            countdownTimer = setTimeout(logout, window.COUNTDOWN_DURATION || COUNTDOWN_DURATION);
        }
        
        // Function to logout
        function logout() {
            // Destroy session on server side first
            fetch('projects-new.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'force_logout=1'
            }).then(() => {
                // Redirect to login page after session is destroyed
                window.location.href = 'projects-new.php?timeout=1';
            }).catch(() => {
                // Fallback redirect even if fetch fails
                window.location.href = 'projects-new.php?timeout=1';
            });
        }
        
        // Event listeners for dialog buttons
        document.getElementById('stay-logged-in').addEventListener('click', () => {
            // Clear all timers first
            clearTimeout(inactivityTimer);
            clearTimeout(countdownTimer);
            clearInterval(countdownInterval);
            
            // Hide dialog immediately
            warningDialog.classList.add('hidden');
            warningShown = false;
            
            // Start fresh timer
            inactivityTimer = setTimeout(showWarning, window.INACTIVITY_LIMIT || INACTIVITY_LIMIT);
            
            // Update server-side last activity
            fetch('projects-new.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'update_activity=1'
            }).catch(err => console.log('Activity update failed:', err));
        });
        
        // Track user activity
        const activityEvents = [
            'mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'
        ];
        
        activityEvents.forEach(event => {
            document.addEventListener(event, resetInactivityTimer, true);
        });
        
        // Initialize timer on page load
        resetInactivityTimer();
        
        // Handle page visibility change
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // Page is hidden, don't reset timer
            } else {
                // Page is visible, reset timer
                resetInactivityTimer();
            }
        });
        
        // Handle window focus/blur
        window.addEventListener('focus', resetInactivityTimer);
        window.addEventListener('blur', () => {
            // Don't reset timer when window loses focus
        });
    </script>
</body>
</html>
