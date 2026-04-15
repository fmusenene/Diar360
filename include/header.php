<?php
/**
 * Header Include File
 * Contains the site header and navigation
 */

// Load configuration and functions
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/functions.php';
require_once __DIR__ . '/../functions/language.php';

// Load admin settings for dynamic contact information
$admin_settings_file = __DIR__ . '/../config/admin-settings.php';
$site_settings = [];
$admin_password = 'diar360_admin_2024'; // Default fallback

if (file_exists($admin_settings_file)) {
    include $admin_settings_file;
}

// Use admin settings if available, otherwise fall back to config constants
$site_name = isset($site_settings['site_name']) ? $site_settings['site_name'] : SITE_NAME;
$admin_email = isset($site_settings['admin_email']) ? $site_settings['admin_email'] : SITE_EMAIL;
$company_phone = isset($site_settings['company_phone']) ? $site_settings['company_phone'] : SITE_PHONE;
$company_address = isset($site_settings['company_address']) ? $site_settings['company_address'] : SITE_ADDRESS;

// Initialize session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current language
$currentLang = isset($_SESSION['language']) ? $_SESSION['language'] : DEFAULT_LANGUAGE;
if (!in_array($currentLang, ['en', 'ar'])) {
    $currentLang = DEFAULT_LANGUAGE;
}

// Get current page for active state
$currentPage = getCurrentPage();
$pageTitle = getPageTitle($currentPage);
$bodyClass = getBodyClass();

// Get current page URL for language switcher
$currentUrl = $_SERVER['REQUEST_URI'];
$currentUrl = strtok($currentUrl, '?'); // Remove existing query parameters
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>" dir="<?php echo ($currentLang === 'ar') ? 'rtl' : 'ltr'; ?>">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo e($pageTitle); ?></title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons - Diar 360 logo -->
  <!-- Update assets/img/favicon.png with your Diar 360 logo (recommended 64x64 or 512x512 PNG) -->
  <link rel="icon" type="image/png" href="<?php echo ASSETS_PATH; ?>/img/favicon.png?v=2">
  <link rel="shortcut icon" type="image/png" href="<?php echo ASSETS_PATH; ?>/img/favicon.png?v=2">
  <link rel="apple-touch-icon" href="<?php echo ASSETS_PATH; ?>/img/favicon.png?v=2">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="<?php echo ASSETS_PATH; ?>/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?php echo ASSETS_PATH; ?>/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="<?php echo ASSETS_PATH; ?>/vendor/aos/aos.css" rel="stylesheet">
  <link href="<?php echo ASSETS_PATH; ?>/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="<?php echo ASSETS_PATH; ?>/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="<?php echo ASSETS_PATH; ?>/css/main.css" rel="stylesheet">
  
  <!-- Language Switcher Styles -->
  <style>
    /* Diar 360 Logo Styles - use real logo image */
    .diar360-logo {
      display: flex;
      align-items: center;
    }

    .diar360-logo img {
      display: block;
      max-height: 160px; /* extra large desktop logo */
      height: auto;
      width: auto;
    }

    /* Responsive logo */
    @media (max-width: 768px) {
      .diar360-logo img {
        max-height: 120px; /* extra large on mobile as well */
      }
    }
    
    /* Language switcher – clean, standard pill style */
    .language-switcher {
      position: relative;
    }
    
    .lang-switch-container {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(20, 82, 157, 0.95); /* brand blue */
      border-radius: 999px;
      padding: 2px;
      gap: 2px;
      border: 1px solid rgba(255, 255, 255, 0.25);
      min-width: 104px;
      flex-shrink: 0;
    }
    
    .lang-option {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      padding: 5px 10px;
      flex: 1;
      border-radius: 999px;
      text-decoration: none;
      color: rgba(255, 255, 255, 0.85);
      font-size: 11px;
      font-weight: 600;
      white-space: nowrap;
      transition: background 0.2s ease, color 0.2s ease;
    }
    
    .lang-option i {
      font-size: 12px;
      line-height: 1;
      width: 14px;
      height: 14px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    
    .lang-option:hover {
      color: #ffffff;
      background: rgba(255, 255, 255, 0.12);
    }
    
    .lang-option.active {
      background: #ffffff;
      color: #14529d;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
    }
    
    .lang-option.active i {
      color: #14529d;
    }
    
    /* Mobile Language Switcher */
    .language-switcher-mobile {
      position: absolute;
      top: 50%;
      right: 15px;
      transform: translateY(-50%);
      z-index: 1000;
    }
    
    .language-switcher-mobile .lang-switch-container {
      width: 110px;
    }
    
    /* RTL support for mobile language switcher */
    body[dir="rtl"] .language-switcher-mobile {
      right: auto;
      left: 15px;
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
      .lang-switch-container {
        padding: 3px;
        gap: 2px;
        width: 108px;
      }
      
      .lang-option {
        padding: 5px 12px;
        min-width: 42px;
        font-size: 12px;
        font-weight: 600;
      }
      
      .lang-option i {
        font-size: 11px;
        width: 12px;
        height: 12px;
      }
      
      .language-switcher-mobile .lang-switch-container {
        width: 98px;
      }
      
      /* Adjust contact info on mobile to make room for language switcher */
      .topbar .contact-info {
        padding-right: 95px;
      }
      
      body[dir="rtl"] .topbar .contact-info {
        padding-right: 0;
        padding-left: 95px;
      }
    }
    
    @media (max-width: 576px) {
      .language-switcher-mobile .lang-switch-container {
        width: 92px;
      }
      
      .language-switcher-mobile .lang-option {
        padding: 5px 10px;
        min-width: 38px;
        font-size: 11px;
        font-weight: 600;
      }
      
      .language-switcher-mobile .lang-option i {
        font-size: 10px;
        width: 11px;
        height: 11px;
      }
      
      .topbar .contact-info {
        padding-right: 100px;
        font-size: 12px;
      }
      
      body[dir="rtl"] .topbar .contact-info {
        padding-right: 0;
        padding-left: 100px;
      }
    }
    
    /* Ensure consistent spacing */
    .language-switcher {
      margin-right: 1rem !important;
    }
    
    /* In RTL, add proper spacing to match English layout - push language switcher away from social icons */
    body[dir="rtl"] .language-switcher {
      margin-left: 1rem !important;
      margin-right: 0 !important;
    }
    
    /* Override Bootstrap me-3 class in RTL */
    body[dir="rtl"] .language-switcher.me-3 {
      margin-left: 1rem !important;
      margin-right: 0 !important;
    }
    
    /* Ensure social links have proper spacing in RTL */
    body[dir="rtl"] .social-links {
      display: flex;
      align-items: center;
      gap: 0;
    }
    
    /* Fix social icon margins in RTL - they should use margin-right instead of margin-left */
    body[dir="rtl"] .social-links > a {
      margin-left: 0 !important;
      margin-right: 20px !important;
    }
    /* Extra gap between pill and X/Twitter icon in RTL */
    body[dir="rtl"] .social-links > a.twitter {
      margin-right: 36px !important;
    }
    
    body[dir="rtl"] .social-links > a:last-child {
      margin-right: 0 !important;
    }
    
    /* Add clear gap between Twitter (X) icon and language switcher in RTL */
    body[dir="rtl"] .social-links > .language-switcher {
      margin-left: 4.5rem !important;
    }
    
    /* RTL Support for Arabic */
    <?php if ($currentLang === 'ar'): ?>
    body[dir="rtl"] {
      direction: rtl;
      text-align: right;
    }
    body[dir="rtl"] .container,
    body[dir="rtl"] .row,
    body[dir="rtl"] .col-lg-6,
    body[dir="rtl"] .col-lg-4,
    body[dir="rtl"] .col-md-6 {
      direction: rtl;
    }
    body[dir="rtl"] .ms-4 {
      margin-left: 0 !important;
      margin-right: 1.5rem !important;
    }
    body[dir="rtl"] .me-3 {
      margin-right: 0 !important;
      margin-left: 1rem !important;
    }
    body[dir="rtl"] .text-start {
      text-align: right !important;
    }
    body[dir="rtl"] .text-end {
      text-align: left !important;
    }
    
    /* Phone number formatting - prevent RTL reversal and ensure proper display */
    .phone-number {
      direction: ltr !important;
      text-align: left !important;
      display: inline-block;
      unicode-bidi: embed;
    }
    
    body[dir="rtl"] .phone-number {
      direction: ltr !important;
      text-align: left !important;
    }
    <?php endif; ?>
  </style>

  <!-- =======================================================
  * Project: Diar 360 Corporate Website
  * Description: Bilingual (EN/AR) construction & facility management website
  * Updated: <?php echo date('M d Y'); ?> with Bootstrap v5.3.8
  * Author: Diar 360 Web Team
  ======================================================== -->
</head>

<body class="<?php echo $bodyClass; ?>">

  <header id="header" class="header sticky-top">

    <div class="topbar d-flex align-items-center dark-background">
      <div class="container d-flex justify-content-center justify-content-md-between position-relative">
        <div class="contact-info d-flex align-items-center">
          <i class="bi bi-envelope d-flex align-items-center"><a href="mailto:<?php echo e($admin_email); ?>"><?php echo e($admin_email); ?></a></i>
          <i class="bi bi-phone d-flex align-items-center ms-4"><?php echo formatPhoneNumber($company_phone); ?></i>
        </div>
        <div class="social-links d-none d-md-flex align-items-center">
          <!-- Language Switcher - Desktop -->
          <div class="language-switcher me-3">
            <div class="lang-switch-container">
              <a href="language-switch.php?lang=en" class="lang-option <?php echo ($currentLang === 'en') ? 'active' : ''; ?>" title="English">
                <i class="bi bi-globe"></i>
                <span>EN</span>
              </a>
              <a href="language-switch.php?lang=ar" class="lang-option <?php echo ($currentLang === 'ar') ? 'active' : ''; ?>" title="العربية">
                <i class="bi bi-translate"></i>
                <span>AR</span>
              </a>
            </div>
          </div>
          <a href="<?php echo e(SOCIAL_TWITTER); ?>" class="twitter"><i class="bi bi-twitter-x"></i></a>
          <a href="<?php echo e(SOCIAL_FACEBOOK); ?>" class="facebook"><i class="bi bi-facebook"></i></a>
          <a href="<?php echo e(SOCIAL_INSTAGRAM); ?>" class="instagram"><i class="bi bi-instagram"></i></a>
          <a href="<?php echo e(SOCIAL_LINKEDIN); ?>" class="linkedin"><i class="bi bi-linkedin"></i></a>
        </div>
        <!-- Language Switcher - Mobile -->
        <div class="language-switcher-mobile d-flex d-md-none align-items-center">
          <div class="lang-switch-container">
            <a href="language-switch.php?lang=en" class="lang-option <?php echo ($currentLang === 'en') ? 'active' : ''; ?>" title="English">
              <i class="bi bi-globe"></i>
              <span>EN</span>
            </a>
            <a href="language-switch.php?lang=ar" class="lang-option <?php echo ($currentLang === 'ar') ? 'active' : ''; ?>" title="العربية">
              <i class="bi bi-translate"></i>
              <span>AR</span>
            </a>
          </div>
        </div>
      </div>
    </div><!-- End Top Bar -->

    <div class="branding d-flex align-items-cente">

      <div class="container position-relative d-flex align-items-center justify-content-between">
        <a href="index.php" class="logo d-flex align-items-center">
          <div class="diar360-logo">
            <img src="<?php echo ASSETS_PATH; ?>/img/logo.png" alt="Diar 360" />
          </div>
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="index.php" class="<?php echo isActive('index'); ?>"><?php echo t('nav_home'); ?></a></li>
            <li><a href="about.php" class="<?php echo isActive('about'); ?>"><?php echo t('nav_about'); ?></a></li>
            <li><a href="services.php" class="<?php echo isActive('services'); ?>"><?php echo t('nav_services'); ?></a></li>
            <li><a href="projects.php" class="<?php echo isActive('projects'); ?>"><?php echo t('nav_projects'); ?></a></li>
            <li><a href="team.php" class="<?php echo isActive('team'); ?>"><?php echo t('nav_team'); ?></a></li>
            <li><a href="careers.php" class="<?php echo isActive('careers'); ?>"><?php echo t('nav_careers'); ?></a></li>
            <li><a href="contact.php" class="<?php echo isActive('contact'); ?>"><?php echo t('nav_contact'); ?></a></li>
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

      </div>

    </div>

  </header>
