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
 * Get asset URL with filemtime cache-busting (fresh after uploads/edits, long-cache friendly).
 */
function asset($path) {
    static $cache = [];

    $path = ltrim(str_replace('\\', '/', (string) $path), '/');
    if ($path === '') {
        return ASSETS_PATH;
    }
    if (isset($cache[$path])) {
        return $cache[$path];
    }

    $url = ASSETS_PATH . '/' . $path;
    if (defined('ROOT_PATH')) {
        $fullPath = ROOT_PATH . '/assets/' . $path;
        if (is_file($fullPath)) {
            $url .= '?v=' . filemtime($fullPath);
        }
    }

    $cache[$path] = $url;
    return $url;
}

/**
 * Image under assets/img/ (e.g. "construction/about1.webp" or "team/photo.webp").
 */
function asset_img($pathWithinImg) {
    return asset('img/' . ltrim(str_replace('\\', '/', (string) $pathWithinImg), '/'));
}

/**
 * Whether a team contact field has a real value (not blank/placeholder).
 */
function team_contact_is_meaningful($value) {
    $v = trim((string) $value);
    if ($v === '' || $v === '#' || $v === '-' || strcasecmp($v, 'n/a') === 0) {
        return false;
    }
    return true;
}

/**
 * Resolve email / phone / linkedin for compact card overlay (single source: member fields, with legacy quick_contact fallback).
 */
function team_member_resolved_contacts(array $member) {
    $email = trim((string) ($member['email'] ?? ''));
    $phone = trim((string) ($member['phone'] ?? ''));
    $linkedin = trim((string) ($member['socials']['linkedin'] ?? ''));
    $qc = is_array($member['quick_contact'] ?? null) ? $member['quick_contact'] : [];

    $qcEmail = trim((string) ($qc['email'] ?? ''));
    $qcPhone = trim((string) ($qc['phone'] ?? ''));
    $qcLinkedin = trim((string) ($qc['linkedin'] ?? ''));

    // Primary fields win so clearing phone/email in admin removes icons immediately (legacy quick_contact ignored when primary is empty).
    $resolvedEmail = team_contact_is_meaningful($email)
        ? $email
        : (team_contact_is_meaningful($qcEmail) ? $qcEmail : '');
    $resolvedPhone = team_contact_is_meaningful($phone)
        ? $phone
        : (team_contact_is_meaningful($qcPhone) ? $qcPhone : '');
    $resolvedLinkedin = team_contact_is_meaningful($linkedin)
        ? $linkedin
        : (team_contact_is_meaningful($qcLinkedin) ? $qcLinkedin : '');

    return [
        'email' => $resolvedEmail,
        'phone' => $resolvedPhone,
        'linkedin' => $resolvedLinkedin,
    ];
}

/**
 * Build quick_contact storage from primary member fields (admin saves).
 */
function team_build_quick_contact($email, $phone, $linkedin) {
    return [
        'email' => trim((string) $email),
        'phone' => trim((string) $phone),
        'linkedin' => trim((string) $linkedin),
    ];
}

/**
 * Compact team card hover icons (icon-only; no visible phone text).
 */
function render_team_compact_quick_contact(array $member) {
    $c = team_member_resolved_contacts($member);

    if ($c['email'] !== '') {
        $mailHref = str_starts_with(strtolower($c['email']), 'mailto:')
            ? $c['email']
            : 'mailto:' . $c['email'];
        echo '<a href="' . e($mailHref) . '" title="' . e($c['email']) . '" aria-label="' . e($c['email']) . '"><i class="bi bi-envelope" aria-hidden="true"></i></a>';
    }
    if ($c['phone'] !== '') {
        $phoneClean = preg_replace('/[^0-9+]/', '', $c['phone']);
        if ($phoneClean !== '') {
            echo '<a href="tel:' . e($phoneClean) . '" title="' . e($c['phone']) . '" aria-label="' . e($c['phone']) . '"><i class="bi bi-telephone" aria-hidden="true"></i></a>';
        }
    }
    if ($c['linkedin'] !== '') {
        $linkHref = $c['linkedin'];
        if (!preg_match('/^https?:\/\//i', $linkHref)) {
            $linkHref = 'https://' . $linkHref;
        }
        echo '<a href="' . e($linkHref) . '" target="_blank" rel="noopener noreferrer" title="' . e(t('linkedin')) . '"><i class="bi bi-linkedin" aria-hidden="true"></i></a>';
    }
}

require_once __DIR__ . '/image-optimize.php';

// Include language functions
require_once __DIR__ . '/language.php';
?>
