<?php
/**
 * Language Switcher Handler
 * Handles language switching via GET parameter
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the language from URL parameter
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';

// Validate language (only allow 'en' and 'ar' for now)
$allowed_languages = ['en', 'ar'];
if (!in_array($lang, $allowed_languages)) {
    $lang = 'en';
}

// Set language in session
$_SESSION['language'] = $lang;

// Get the referrer or default to index
$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

// Extract the page from referrer
$parsed_url = parse_url($referrer);
$path = isset($parsed_url['path']) ? $parsed_url['path'] : 'index.php';
$page = basename($path);

// Preserve query parameters if they exist (but remove lang if present)
$query_string = '';
if (isset($parsed_url['query'])) {
    parse_str($parsed_url['query'], $query_params);
    unset($query_params['lang']); // Remove lang parameter
    if (!empty($query_params)) {
        $query_string = '?' . http_build_query($query_params);
    }
}

// If no referrer or referrer is from different domain, go to index
if (empty($referrer) || strpos($referrer, $_SERVER['HTTP_HOST']) === false) {
    $page = 'index.php';
    $query_string = '';
}

// Redirect back to the same page with language set
header('Location: ' . $page . $query_string);
exit;
