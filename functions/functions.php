<?php
/**
 * Common Functions File
 * Reusable utility functions for the application
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number
 */
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9+()-]/', '', $phone);
    return preg_match('/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/', $phone);
}

/**
 * Get current page name
 */
function getCurrentPage() {
    $page = basename($_SERVER['PHP_SELF'], '.php');
    return $page;
}

/**
 * Check if current page is active
 */
function isActive($page) {
    return getCurrentPage() === $page ? 'active' : '';
}

/**
 * Generate page title
 */
function getPageTitle($page = '') {
    if (empty($page)) {
        $page = getCurrentPage();
    }
    
    $titles = [
        'index' => 'Home',
        'about' => 'About',
        'contact' => 'Contact',
        'services' => 'Services',
        'projects' => 'Projects',
        'team' => 'Team',
        'careers' => 'Careers',
        'quote' => 'Quote',
        'service-details' => 'Service Details',
        'project-details' => 'Project Details',
        'terms' => 'Terms',
        'privacy' => 'Privacy',
        '404' => '404 - Page Not Found'
    ];
    
    $title = isset($titles[$page]) ? $titles[$page] : ucfirst($page);
    return $title . ' - ' . SITE_NAME;
}

/**
 * Redirect to a page
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Format date
 */
function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Generate breadcrumbs
 */
function getBreadcrumbs($currentPage) {
    $breadcrumbs = [
        'index' => ['Home'],
        'about' => ['Home' => 'index.php', 'About'],
        'contact' => ['Home' => 'index.php', 'Contact'],
        'services' => ['Home' => 'index.php', 'Services'],
        'projects' => ['Home' => 'index.php', 'Projects'],
        'team' => ['Home' => 'index.php', 'Team'],
        'careers' => ['Home' => 'index.php', 'Careers'],
        'quote' => ['Home' => 'index.php', 'Quote'],
        'service-details' => ['Home' => 'index.php', 'Service Details'],
        'project-details' => ['Home' => 'index.php', 'Project Details'],
        'terms' => ['Home' => 'index.php', 'Terms'],
        'privacy' => ['Home' => 'index.php', 'Privacy'],
        '404' => ['Home' => 'index.php', '404']
    ];
    
    return isset($breadcrumbs[$currentPage]) ? $breadcrumbs[$currentPage] : [];
}

/**
 * Get body class for current page
 */
function getBodyClass() {
    $page = getCurrentPage();
    return $page . '-page';
}

/**
 * Escape output for HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if request is POST
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Get POST data
 */
function getPost($key, $default = '') {
    return isset($_POST[$key]) ? sanitize($_POST[$key]) : $default;
}

/**
 * Get GET data
 */
function getGet($key, $default = '') {
    return isset($_GET[$key]) ? sanitize($_GET[$key]) : $default;
}

/**
 * Get asset URL
 */
function asset($path) {
    // Remove leading slash if present
    $path = ltrim($path, '/');
    return ASSETS_PATH . '/' . $path;
}

// Include language functions
require_once __DIR__ . '/language.php';
?>
