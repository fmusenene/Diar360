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
$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

// Extract the page from referrer
$redirect_url = '';

if (!empty($referrer) && strpos($referrer, $_SERVER['HTTP_HOST']) !== false) {
    // Parse the referrer URL
    $parsed_url = parse_url($referrer);
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '/index.php';
    
    // Get the filename from the path
    $path_parts = pathinfo($path);
    $filename = isset($path_parts['basename']) ? $path_parts['basename'] : 'index.php';
    
    // If it's a directory or empty, default to index.php
    if (empty($filename) || strpos($filename, '.') === false) {
        $filename = 'index.php';
    }
    
    // Preserve query parameters if they exist (but remove lang if present)
    $query_string = '';
    if (isset($parsed_url['query'])) {
        parse_str($parsed_url['query'], $query_params);
        unset($query_params['lang']); // Remove lang parameter
        if (!empty($query_params)) {
            $query_string = '?' . http_build_query($query_params);
        }
    }
    
    $redirect_url = $filename . $query_string;
} else {
    // No valid referrer, go to index
    $redirect_url = 'index.php';
}

// Ensure the redirect URL is not empty and file exists
if (empty($redirect_url)) {
    $redirect_url = 'index.php';
}

// Verify the target file exists, if not default to index.php
if (!file_exists(__DIR__ . '/' . $redirect_url)) {
    $redirect_url = 'index.php';
}

// Debug: Log the redirect for troubleshooting (remove in production)
// error_log("Language switch: lang=$lang, referrer=$referrer, redirect_url=$redirect_url");

// Redirect back to the same page with language set
header('Location: ' . $redirect_url);
exit;

?>
