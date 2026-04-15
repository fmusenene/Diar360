<?php
/**
 * Site Configuration File
 * Contains all site-wide settings and constants
 */

// Site Information
define('SITE_NAME', 'Diar 360');
define('SITE_TAGLINE', 'Leading in construction and achievement');
define('SITE_EMAIL', 'info@diar360.com');
define('SITE_PHONE', '+966 1 1 296 7735');
define('CONTACT_EMAIL', 'info@diar360.com');

// Site Address
define('SITE_ADDRESS', 'Prince Mohammed Ibn Salman Ibn Abdulaziz Rd, Al Falah Dist');
define('SITE_CITY', 'Riyadh');
define('SITE_COUNTRY', 'Saudi Arabia');
define('SITE_LATITUDE', '24.816789');
define('SITE_LONGITUDE', '46.753947');

// Company Information
define('COMPANY_DESCRIPTION', 'DIAR 360 is a contracting company with extensive experience in Saudi market, specializing in civil construction, Fit-out, MEP, Landscaping & Facility Management industries.');
// Company start year - update this if the company started in a different year
define('COMPANY_START_YEAR', 2009);
// Automatically calculate years of experience based on current year
define('COMPANY_EXPERIENCE_YEARS', (int)date('Y') - COMPANY_START_YEAR);

// Language Settings
define('DEFAULT_LANGUAGE', 'en');

// Social Media Links
define('SOCIAL_TWITTER', '#');
define('SOCIAL_FACEBOOK', '#');
define('SOCIAL_INSTAGRAM', '#');
define('SOCIAL_LINKEDIN', 'https://www.linkedin.com/company/113092400/admin/dashboard/');
define('SOCIAL_YOUTUBE', '#');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDE_PATH', ROOT_PATH . '/include');
define('FUNCTIONS_PATH', ROOT_PATH . '/functions');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Get the base URL path dynamically
// This works for subdirectories like /diar360/
if (!defined('BASE_PATH')) {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    // Remove leading/trailing slashes and add one at the start
    $basePath = '/' . trim($scriptDir, '/');
    // If we're in root, base path is empty
    if ($basePath === '/') {
        $basePath = '';
    }
    define('BASE_PATH', $basePath);
}

// Site URL (auto-detected for localhost vs hosted)
// Falls back to the previous localhost URL if detection isn't possible.
if (!defined('SITE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host !== '') {
        define('SITE_URL', $scheme . '://' . $host . BASE_PATH);
    } else {
        define('SITE_URL', 'http://localhost/diar360');
    }
}

// Assets path - relative to base path
define('ASSETS_PATH', BASE_PATH . '/assets');

// Google Sign-In (for testimonials avatar/name/email)
// Create a Client ID in Google Cloud Console (OAuth consent screen + Web application).
// Add Authorized JavaScript origins (e.g. https://yourdomain.com) and (for local) http://localhost.
define('GOOGLE_SIGNIN_CLIENT_ID', ''); // e.g. '1234567890-abcdefg.apps.googleusercontent.com'
define('TESTIMONIAL_REQUIRE_GOOGLE_SIGNIN', false);

// Timezone
date_default_timezone_set('Asia/Riyadh');

// Environment-aware security and error behavior
$isLocalHost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'], true);
if ($isLocalHost) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

/**
 * Apply baseline HTTP security headers.
 * Keep CSP compatible with the existing frontend/admin assets.
 */
if (!function_exists('diar_apply_security_headers')) {
    function diar_apply_security_headers(): void {
        if (headers_sent()) {
            return;
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header('X-Permitted-Cross-Domain-Policies: none');

        if ($isHttps) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        header(
            "Content-Security-Policy: " .
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://www.gstatic.com https://accounts.google.com; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; " .
            "img-src 'self' data: blob: https:; " .
            "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
            "connect-src 'self' https://www.googleapis.com https://accounts.google.com; " .
            "frame-src 'self' https://accounts.google.com https://www.google.com; " .
            "object-src 'none'; " .
            "base-uri 'self'; " .
            "frame-ancestors 'self'; " .
            "form-action 'self'"
        );
    }
}

diar_apply_security_headers();

if (!function_exists('diar_is_same_origin_request')) {
    function diar_is_same_origin_request(): bool {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host === '') {
            return false;
        }

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin !== '') {
            $originHost = parse_url($origin, PHP_URL_HOST);
            return is_string($originHost) && strcasecmp($originHost, $host) === 0;
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if ($referer !== '') {
            $refererHost = parse_url($referer, PHP_URL_HOST);
            return is_string($refererHost) && strcasecmp($refererHost, $host) === 0;
        }

        // Some clients may omit both; allow to avoid breaking legacy behavior.
        return true;
    }
}

// Session hardening
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $isHttps ? '1' : '0');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
?>
