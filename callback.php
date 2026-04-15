<?php
/**
 * OAuth Callback Handler
 * 
 * This file handles OAuth callbacks from authentication providers
 * such as Google, Facebook, GitHub, etc.
 */

session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration (you'll need to create this)
require_once __DIR__ . '/config/oauth-config.php';

/**
 * Handle OAuth callback
 */
function handleOAuthCallback() {
    // Get the OAuth provider from URL parameter
    $provider = $_GET['provider'] ?? '';
    
    // Validate provider
    $allowedProviders = ['google', 'facebook', 'github', 'linkedin'];
    if (!in_array($provider, $allowedProviders)) {
        die('Invalid OAuth provider');
    }
    
    try {
        switch ($provider) {
            case 'google':
                return handleGoogleCallback();
            case 'facebook':
                return handleFacebookCallback();
            case 'github':
                return handleGitHubCallback();
            case 'linkedin':
                return handleLinkedInCallback();
            default:
                die('Unsupported OAuth provider');
        }
    } catch (Exception $e) {
        error_log("OAuth Error: " . $e->getMessage());
        die('OAuth authentication failed: ' . htmlspecialchars($e->getMessage()));
    }
}

/**
 * Handle Google OAuth callback
 */
function handleGoogleCallback() {
    // Verify state parameter for CSRF protection
    if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
        die('Invalid state parameter');
    }
    
    // Exchange authorization code for access token
    if (!isset($_GET['code'])) {
        die('Authorization code not found');
    }
    
    $code = $_GET['code'];
    
    // You'll need to implement Google OAuth client
    // This is a placeholder - replace with actual Google OAuth implementation
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
    
    $postData = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    // Make token request (you'll need cURL or Guzzle HTTP client)
    $tokenResponse = makeHttpPostRequest($tokenUrl, $postData);
    $tokenData = json_decode($tokenResponse, true);
    
    if (!isset($tokenData['access_token'])) {
        die('Failed to obtain access token');
    }
    
    // Get user info
    $userInfoResponse = file_get_contents($userInfoUrl . '?access_token=' . $tokenData['access_token']);
    $userInfo = json_decode($userInfoResponse, true);
    
    // Process user data and create session
    processOAuthUser($userInfo, 'google');
}

/**
 * Handle Facebook OAuth callback
 */
function handleFacebookCallback() {
    // Similar implementation for Facebook OAuth
    // You'll need to implement Facebook SDK or manual OAuth flow
    if (!isset($_GET['code'])) {
        die('Authorization code not found');
    }
    
    // Implement Facebook token exchange
    // This is a placeholder - replace with actual Facebook OAuth implementation
    die('Facebook OAuth not implemented yet');
}

/**
 * Handle GitHub OAuth callback
 */
function handleGitHubCallback() {
    // Similar implementation for GitHub OAuth
    if (!isset($_GET['code'])) {
        die('Authorization code not found');
    }
    
    // Implement GitHub token exchange
    // This is a placeholder - replace with actual GitHub OAuth implementation
    die('GitHub OAuth not implemented yet');
}

/**
 * Handle LinkedIn OAuth callback
 */
function handleLinkedInCallback() {
    // Similar implementation for LinkedIn OAuth
    if (!isset($_GET['code'])) {
        die('Authorization code not found');
    }
    
    // Implement LinkedIn token exchange
    // This is a placeholder - replace with actual LinkedIn OAuth implementation
    die('LinkedIn OAuth not implemented yet');
}

/**
 * Process OAuth user data and create session
 */
function processOAuthUser($userInfo, $provider) {
    // Extract user information
    $email = $userInfo['email'] ?? '';
    $name = $userInfo['name'] ?? $userInfo['login'] ?? '';
    $avatar = $userInfo['picture'] ?? $userInfo['avatar_url'] ?? '';
    
    if (empty($email)) {
        die('Email is required for OAuth authentication');
    }
    
    // Here you can:
    // 1. Check if user exists in your database
    // 2. Create new user if doesn't exist
    // 3. Create authentication session
    // 4. Redirect to dashboard or intended page
    
    $_SESSION['oauth_authenticated'] = true;
    $_SESSION['oauth_provider'] = $provider;
    $_SESSION['oauth_user'] = [
        'email' => $email,
        'name' => $name,
        'avatar' => $avatar,
        'provider' => $provider
    ];
    
    // Redirect to dashboard or intended page
    $redirectUrl = $_SESSION['oauth_redirect'] ?? 'admin/projects-new.php';
    unset($_SESSION['oauth_redirect']);
    unset($_SESSION['oauth_state']);
    
    header('Location: ' . $redirectUrl);
    exit;
}

/**
 * Make HTTP POST request (helper function)
 */
function makeHttpPostRequest($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception("HTTP request failed with code: $httpCode");
    }
    
    return $response;
}

// Handle the callback
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    handleOAuthCallback();
} else {
    die('Invalid request method');
}
?>
