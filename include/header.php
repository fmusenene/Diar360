<?php
/**
 * Header Include File
 * Contains the site header and navigation
 */

// Load configuration and functions
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/functions.php';
require_once __DIR__ . '/../functions/language.php';

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
    /* Diar 360 Logo Styles */
    .diar360-logo {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-decoration: none;
      color: inherit;
    }
    
    .logo-top {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 8px;
      width: 100px;
      height: 35px;
    }
    
    .logo-arrow {
      position: absolute;
      top: -5px;
      left: 50%;
      transform: translateX(-50%);
      width: 90px;
      height: 25px;
      z-index: 1;
    }
    
    .logo-d3 {
      font-size: 22px;
      font-weight: 700;
      font-family: var(--heading-font, "Ubuntu", sans-serif);
      color: var(--heading-color, #102a49);
      position: relative;
      z-index: 2;
      letter-spacing: -1px;
    }
    
    .logo-lines {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      display: flex;
      justify-content: space-between;
      width: 100%;
      pointer-events: none;
      padding: 0 5px;
    }
    
    .logo-lines .line-left,
    .logo-lines .line-right {
      width: 25px;
      height: 1px;
      background-color: #666;
    }
    
    .logo-main {
      display: flex;
      align-items: baseline;
      gap: 5px;
      margin-bottom: 3px;
    }
    
    .logo-diar {
      font-size: 32px;
      font-weight: 700;
      font-family: Georgia, "Times New Roman", serif;
      color: var(--heading-color, #102a49);
      line-height: 1;
    }
    
    .logo-diar::first-letter {
      font-size: 36px;
    }
    
    .logo-360 {
      font-size: 32px;
      font-weight: 700;
      font-family: var(--heading-font, "Ubuntu", sans-serif);
      color: var(--heading-color, #102a49);
      line-height: 1;
    }
    
    .logo-tagline {
      font-size: 9px;
      font-weight: 500;
      letter-spacing: 1.5px;
      color: #666;
      text-transform: uppercase;
      margin-top: 2px;
      font-family: var(--default-font, "Roboto", sans-serif);
    }
    
    /* Logo hover effect */
    .logo:hover .diar360-logo {
      opacity: 0.9;
    }
    
    .logo:hover .logo-diar,
    .logo:hover .logo-360,
    .logo:hover .logo-d3 {
      color: var(--accent-color, #14529d);
    }
    
    /* Responsive logo */
    @media (max-width: 768px) {
      .logo-d3 {
        font-size: 20px;
      }
      
      .logo-diar {
        font-size: 26px;
      }
      
      .logo-diar::first-letter {
        font-size: 30px;
      }
      
      .logo-360 {
        font-size: 26px;
      }
      
      .logo-tagline {
        font-size: 8px;
        letter-spacing: 1px;
      }
      
      .logo-arrow {
        width: 65px;
        height: 25px;
        top: -6px;
      }
    }
    
    .language-switcher {
      position: relative;
    }
    
    .lang-switch-container {
      display: flex;
      align-items: center;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50px;
      padding: 3px;
      gap: 2px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
      width: 110px;
      flex-shrink: 0;
    }
    
    .lang-switch-container:hover {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      border-color: rgba(255, 255, 255, 0.3);
    }
    
    .lang-option {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      padding: 6px 14px;
      min-width: 44px;
      border-radius: 50px;
      text-decoration: none;
      color: #ffffff;
      font-size: 12px;
      font-weight: 600;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      flex: 1;
      white-space: nowrap;
      overflow: visible;
      text-align: center;
    }
    
    .lang-option::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
      transition: left 0.5s ease;
    }
    
    .lang-option:hover::before {
      left: 100%;
    }
    
    .lang-option i {
      font-size: 12px;
      line-height: 1;
      width: 14px;
      height: 14px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      opacity: 0.95;
    }
    
    .lang-option:hover {
      color: #ffffff;
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-1px);
    }
    
    .lang-option:hover i {
      opacity: 1;
      transform: scale(1.08);
    }
    
    .lang-option.active {
      background: #ffffff;
      color: #14529d;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
      font-weight: 600;
      transform: scale(1.02);
    }
    
    .lang-option.active i {
      color: #14529d;
      opacity: 1;
      transform: scale(1.05);
    }
    
    .lang-option.active:hover {
      transform: scale(1.05) translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }
    
    .lang-option span {
      letter-spacing: 0.5px;
      position: relative;
      z-index: 1;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }
    
    .lang-option.active span {
      text-shadow: none;
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
    
    body[dir="rtl"] .social-links > a:last-child {
      margin-right: 0 !important;
    }
    
    /* Add gap between language switcher and first social icon in RTL to match English spacing */
    body[dir="rtl"] .social-links > .language-switcher {
      margin-left: 1rem !important;
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
          <i class="bi bi-envelope d-flex align-items-center"><a href="mailto:<?php echo e(CONTACT_EMAIL); ?>"><?php echo e(CONTACT_EMAIL); ?></a></i>
          <i class="bi bi-phone d-flex align-items-center ms-4"><?php echo formatPhoneNumber(SITE_PHONE); ?></i>
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
            <div class="logo-top">
              <svg class="logo-arrow" viewBox="0 0 100 40" xmlns="http://www.w3.org/2000/svg">
                <path d="M 10 30 Q 50 5, 90 30" stroke="#333" stroke-width="2" fill="none" stroke-linecap="round"/>
              </svg>
              <span class="logo-d3">D3</span>
              <div class="logo-lines">
                <span class="line-left"></span>
                <span class="line-right"></span>
              </div>
            </div>
            <div class="logo-main">
              <span class="logo-diar">Diar</span>
              <span class="logo-360">360</span>
            </div>
            <div class="logo-tagline">WE BUILD NEW VISION</div>
          </div>
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="index.php" class="<?php echo isActive('index'); ?>"><?php echo t('nav_home'); ?></a></li>
            <li><a href="about.php" class="<?php echo isActive('about'); ?>"><?php echo t('nav_about'); ?></a></li>
            <li><a href="services.php" class="<?php echo isActive('services'); ?>"><?php echo t('nav_services'); ?></a></li>
            <li><a href="projects.php" class="<?php echo isActive('projects'); ?>"><?php echo t('nav_projects'); ?></a></li>
            <li><a href="team.php" class="<?php echo isActive('team'); ?>"><?php echo t('nav_team'); ?></a></li>
            <li class="dropdown"><a href="#"><span><?php echo t('nav_more_pages'); ?></span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
              <ul>
                <li><a href="service-details.php"><?php echo t('nav_service_details'); ?></a></li>
                <li><a href="project-details.php"><?php echo t('nav_project_details'); ?></a></li>
                <li><a href="quote.php"><?php echo t('nav_quote'); ?></a></li>
                <li><a href="terms.php"><?php echo t('nav_terms'); ?></a></li>
                <li><a href="privacy.php"><?php echo t('nav_privacy'); ?></a></li>
                <li><a href="404.php">404</a></li>
              </ul>
            </li>
            <li><a href="contact.php" class="<?php echo isActive('contact'); ?>"><?php echo t('nav_contact'); ?></a></li>
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

      </div>

    </div>

  </header>
