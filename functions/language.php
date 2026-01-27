<?php
/**
 * Language Functions
 * Handles multi-language support
 */

/**
 * Get current language
 */
function getCurrentLanguage() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['language'])) {
        return $_SESSION['language'];
    }
    return defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en';
}

/**
 * Set language
 */
function setLanguage($lang) {
    $_SESSION['language'] = $lang;
}

/**
 * Get language file path
 */
function getLanguageFile($lang = null) {
    if ($lang === null) {
        $lang = getCurrentLanguage();
    }
    $langFile = __DIR__ . '/../language/' . $lang . '.php';
    if (file_exists($langFile)) {
        return require $langFile;
    }
    // Fallback to English
    return require __DIR__ . '/../language/en.php';
}

/**
 * Get translated string
 */
function t($key, $lang = null) {
    static $translations = null;
    
    if ($translations === null) {
        $translations = getLanguageFile($lang);
    }
    
    return isset($translations[$key]) ? $translations[$key] : $key;
}

/**
 * Check if RTL language
 */
function isRTL() {
    $rtlLanguages = ['ar', 'he', 'fa', 'ur'];
    return in_array(getCurrentLanguage(), $rtlLanguages);
}

/**
 * Convert numbers to Arabic numerals if Arabic is selected
 */
function convertNumbers($text) {
    if (getCurrentLanguage() === 'ar') {
        $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        return str_replace($western, $arabic, $text);
    }
    return $text;
}

/**
 * Get translated string with number conversion
 */
function tn($key, $lang = null) {
    return convertNumbers(t($key, $lang));
}

/**
 * Format phone number for display (converts numbers and prevents RTL reversal)
 */
function formatPhoneNumber($phone) {
    // Convert numbers to Arabic numerals if Arabic is selected
    $formatted = convertNumbers($phone);
    
    // Return with direction override to prevent RTL from reversing the number
    return '<span dir="ltr" class="phone-number">' . e($formatted) . '</span>';
}
?>
