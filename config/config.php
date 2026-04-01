<?php
/**
 * Site Configuration File
 * Contains all site-wide settings and constants
 */

// Site Information
define('SITE_NAME', 'Diar 360');
define('SITE_TAGLINE', 'Leading in construction and achievement');
define('SITE_URL', 'http://localhost/diar360');
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
define('SOCIAL_LINKEDIN', '#');
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

// Assets path - relative to base path
define('ASSETS_PATH', BASE_PATH . '/assets');

// Timezone
date_default_timezone_set('Asia/Riyadh');

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
