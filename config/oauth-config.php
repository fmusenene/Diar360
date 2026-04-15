<?php
/**
 * OAuth Configuration
 * 
 * Configuration for OAuth providers
 * Replace these values with your actual OAuth app credentials
 */

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'your-google-client-id-here');
define('GOOGLE_CLIENT_SECRET', 'your-google-client-secret-here');
define('GOOGLE_REDIRECT_URI', 'http://localhost/diar360/callback.php?provider=google');

// Facebook OAuth Configuration
define('FACEBOOK_APP_ID', 'your-facebook-app-id-here');
define('FACEBOOK_APP_SECRET', 'your-facebook-app-secret-here');
define('FACEBOOK_REDIRECT_URI', 'http://localhost/diar360/callback.php?provider=facebook');

// GitHub OAuth Configuration
define('GITHUB_CLIENT_ID', 'your-github-client-id-here');
define('GITHUB_CLIENT_SECRET', 'your-github-client-secret-here');
define('GITHUB_REDIRECT_URI', 'http://localhost/diar360/callback.php?provider=github');

// LinkedIn OAuth Configuration
define('LINKEDIN_CLIENT_ID', 'your-linkedin-client-id-here');
define('LINKEDIN_CLIENT_SECRET', 'your-linkedin-client-secret-here');
define('LINKEDIN_REDIRECT_URI', 'http://localhost/diar360/callback.php?provider=linkedin');

// OAuth Scopes
define('GOOGLE_SCOPES', 'email profile');
define('FACEBOOK_SCOPES', 'email public_profile');
define('GITHUB_SCOPES', 'user:email');
define('LINKEDIN_SCOPES', 'r_liteprofile r_emailaddress');

// Session Security
define('OAUTH_STATE_LENGTH', 32);

/**
 * Generate OAuth state for CSRF protection
 */
function generateOAuthState() {
    return bin2hex(random_bytes(OAUTH_STATE_LENGTH));
}

/**
 * Validate OAuth state
 */
function validateOAuthState($state) {
    return isset($_SESSION['oauth_state']) && hash_equals($_SESSION['oauth_state'], $state);
}
?>
