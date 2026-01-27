<?php
/**
 * Contact Page
 */

// Include header
require_once __DIR__ . '/include/header.php';

// Load language functions
require_once __DIR__ . '/functions/language.php';
?>

  <main class="main">

    <!-- Page Title -->
    <div class="page-title light-background">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0"><?php echo t('contact_title'); ?></h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php"><?php echo t('breadcrumb_home'); ?></a></li>
            <li class="current"><?php echo t('contact_title'); ?></li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Contact Section -->
    <section id="contact" class="contact section">

      <div class="container">
        <div class="contact-wrapper">
          <div class="contact-info-panel">
            <div class="contact-info-header">
              <h3><?php echo t('contact_info_title'); ?></h3>
              <p><?php echo t('contact_info_desc'); ?></p>
            </div>

            <div class="contact-info-cards">
              <div class="info-card">
                <div class="icon-container">
                  <i class="bi bi-pin-map-fill"></i>
                </div>
                <div class="card-content">
                  <h4><?php echo t('contact_location'); ?></h4>
                  <p><?php echo e(SITE_ADDRESS); ?>, <?php echo e(SITE_CITY); ?> - KSA</p>
                </div>
              </div>

              <div class="info-card">
                <div class="icon-container">
                  <i class="bi bi-envelope-open"></i>
                </div>
                <div class="card-content">
                  <h4><?php echo t('contact_email_us'); ?></h4>
                  <p><?php echo e(SITE_EMAIL); ?></p>
                </div>
              </div>

              <div class="info-card">
                <div class="icon-container">
                  <i class="bi bi-telephone-fill"></i>
                </div>
                <div class="card-content">
                  <h4><?php echo t('contact_call_us'); ?></h4>
                  <p><?php echo formatPhoneNumber(SITE_PHONE); ?></p>
                </div>
              </div>

              <div class="info-card">
                <div class="icon-container">
                  <i class="bi bi-clock-history"></i>
                </div>
                <div class="card-content">
                  <h4><?php echo t('contact_working_hours'); ?></h4>
                  <p><?php echo t('contact_hours'); ?></p>
                </div>
              </div>
            </div>

            <div class="social-links-panel">
              <h5><?php echo t('contact_follow_us'); ?></h5>
              <div class="social-icons">
                <a href="#"><i class="bi bi-facebook"></i></a>
                <a href="#"><i class="bi bi-twitter-x"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-linkedin"></i></a>
                <a href="#"><i class="bi bi-youtube"></i></a>
              </div>
            </div>
          </div>

          <div class="contact-form-panel">
            <div class="map-container">
              <iframe src="https://www.google.com/maps?q=<?php echo SITE_LATITUDE; ?>,<?php echo SITE_LONGITUDE; ?>&hl=en&z=15&output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>

            <div class="form-container">
              <h3><?php echo t('contact_send_message'); ?></h3>
              <p><?php echo t('contact_form_desc'); ?></p>

              <form action="forms/contact.php" method="post" class="php-email-form">
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="nameInput" name="name" placeholder="<?php echo t('contact_full_name'); ?>" required="">
                  <label for="nameInput"><?php echo t('contact_full_name'); ?></label>
                </div>

                <div class="form-floating mb-3">
                  <input type="email" class="form-control" id="emailInput" name="email" placeholder="<?php echo t('contact_email_address'); ?>" required="">
                  <label for="emailInput"><?php echo t('contact_email_address'); ?></label>
                </div>

                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="subjectInput" name="subject" placeholder="<?php echo t('contact_subject'); ?>" required="">
                  <label for="subjectInput"><?php echo t('contact_subject'); ?></label>
                </div>

                <div class="form-floating mb-3">
                  <textarea class="form-control" id="messageInput" name="message" rows="5" placeholder="<?php echo t('contact_your_message'); ?>" style="height: 150px" required=""></textarea>
                  <label for="messageInput"><?php echo t('contact_your_message'); ?></label>
                </div>

                <div class="my-3">
                  <div class="loading"><?php echo t('contact_loading'); ?></div>
                  <div class="error-message"></div>
                  <div class="sent-message"><?php echo t('contact_message_sent'); ?></div>
                </div>

                <div class="d-grid">
                  <button type="submit" class="btn-submit"><?php echo t('contact_send'); ?> <i class="bi bi-send-fill ms-2"></i></button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section><!-- /Contact Section -->

  </main>

  <?php
// Include footer
require_once __DIR__ . '/include/footer.php';
?>