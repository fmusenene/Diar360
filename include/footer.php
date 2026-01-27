<?php
/**
 * Footer Include File
 * Contains the site footer
 */

// Load configuration if not already loaded
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../functions/functions.php';
    require_once __DIR__ . '/../functions/language.php';
}
?>
  <footer id="footer" class="footer dark-background">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-5 col-md-12 footer-about">
          <a href="index.php" class="logo d-flex align-items-center">
            <span class="sitename"><?php echo e(SITE_NAME); ?></span>
          </a>
          <p><?php echo t('company_description'); ?></p>
          <div class="social-links d-flex mt-4">
            <a href="<?php echo e(SOCIAL_TWITTER); ?>"><i class="bi bi-twitter-x"></i></a>
            <a href="<?php echo e(SOCIAL_FACEBOOK); ?>"><i class="bi bi-facebook"></i></a>
            <a href="<?php echo e(SOCIAL_INSTAGRAM); ?>"><i class="bi bi-instagram"></i></a>
            <a href="<?php echo e(SOCIAL_LINKEDIN); ?>"><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-6 footer-links">
          <h4><?php echo t('footer_useful_links'); ?></h4>
          <ul>
            <li><a href="index.php"><?php echo t('footer_home'); ?></a></li>
            <li><a href="about.php"><?php echo t('footer_about'); ?></a></li>
            <li><a href="services.php"><?php echo t('footer_services'); ?></a></li>
            <li><a href="terms.php"><?php echo t('footer_terms'); ?></a></li>
            <li><a href="privacy.php"><?php echo t('footer_privacy'); ?></a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-6 footer-links">
          <h4><?php echo t('footer_our_services'); ?></h4>
          <ul>
            <li><a href="services.php"><?php echo t('footer_civil_work'); ?></a></li>
            <li><a href="services.php"><?php echo t('footer_mep'); ?></a></li>
            <li><a href="services.php"><?php echo t('footer_fitout'); ?></a></li>
            <li><a href="services.php"><?php echo t('footer_landscaping'); ?></a></li>
            <li><a href="services.php"><?php echo t('footer_facility'); ?></a></li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-12 footer-contact text-center text-md-start">
          <h4><?php echo t('footer_contact_us'); ?></h4>
          <p><?php echo e(SITE_ADDRESS); ?></p>
          <p><?php echo e(SITE_CITY); ?> - KSA</p>
          <p class="mt-4"><strong><?php echo t('footer_phone'); ?>:</strong> <?php echo formatPhoneNumber(SITE_PHONE); ?></p>
          <p><strong><?php echo t('footer_email'); ?>:</strong> <span><?php echo e(SITE_EMAIL); ?></span></p>
        </div>

      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>© <span><?php echo t('footer_copyright'); ?></span> <strong class="px-1 sitename"><?php echo e(SITE_NAME); ?></strong> <span><?php echo t('footer_rights'); ?></span></p>
      <div class="credits">
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you've purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
        Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="<?php echo ASSETS_PATH; ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?php echo ASSETS_PATH; ?>/vendor/php-email-form/validate.js"></script>
  <script src="<?php echo ASSETS_PATH; ?>/vendor/aos/aos.js"></script>
  <script src="<?php echo ASSETS_PATH; ?>/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="<?php echo ASSETS_PATH; ?>/vendor/glightbox/js/glightbox.min.js"></script>

  <!-- Main JS File -->
  <script src="<?php echo ASSETS_PATH; ?>/js/main.js"></script>

</body>

</html>
