<?php
/**
 * Quote Page
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
        <h1 class="mb-2 mb-lg-0"><?php echo t('quote_title'); ?></h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php"><?php echo t('breadcrumb_home'); ?></a></li>
            <li class="current"><?php echo t('quote_title'); ?></li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Quote Section -->
    <section id="quote" class="quote section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="quote-form-container">
              <div class="row g-0">
                <div class="col-lg-6">
                  <div class="quote-info">
                    <div class="quote-content">
                      <h3><?php echo t('quote_get_in_touch'); ?></h3>
                      <p><?php echo t('quote_desc'); ?></p>

                      <div class="contact-items">
                        <div class="contact-item" data-aos="fade-right" data-aos-delay="200">
                          <div class="contact-icon">
                            <i class="bi bi-telephone"></i>
                          </div>
                          <div class="contact-details">
                            <h4><?php echo t('quote_call_us'); ?></h4>
                            <p><?php echo formatPhoneNumber(SITE_PHONE); ?></p>
                          </div>
                        </div>

                        <div class="contact-item" data-aos="fade-right" data-aos-delay="250">
                          <div class="contact-icon">
                            <i class="bi bi-envelope"></i>
                          </div>
                          <div class="contact-details">
                            <h4><?php echo t('quote_email_us'); ?></h4>
                            <p><?php echo e(SITE_EMAIL); ?></p>
                          </div>
                        </div>

                        <div class="contact-item" data-aos="fade-right" data-aos-delay="300">
                          <div class="contact-icon">
                            <i class="bi bi-clock"></i>
                          </div>
                          <div class="contact-details">
                            <h4><?php echo t('quote_response_time'); ?></h4>
                            <p><?php echo t('quote_response_time_value'); ?></p>
                          </div>
                        </div>
                      </div>

                      <div class="trust-badges" data-aos="fade-right" data-aos-delay="350">
                        <div class="trust-badge">
                          <i class="bi bi-shield-check"></i>
                          <span><?php echo t('quote_licensed'); ?></span>
                        </div>
                        <div class="trust-badge">
                          <i class="bi bi-award"></i>
                          <span><?php echo convertNumbers(COMPANY_EXPERIENCE_YEARS . '+'); ?> <?php echo t('quote_years_experience'); ?></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="quote-form-wrapper">
                    <form action="forms/get-a-quote.php" method="post" class="php-email-form" data-aos="fade-left" data-aos-delay="200">
                      <div class="form-header">
                        <h4><?php echo t('quote_request_title'); ?></h4>
                        <p><?php echo t('quote_request_desc'); ?></p>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="text" name="name" class="form-control" id="name" placeholder="<?php echo t('quote_your_name'); ?>" required="">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="email" name="email" class="form-control" id="email" placeholder="<?php echo t('quote_your_email'); ?>" required="">
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="tel" name="phone" class="form-control" id="phone" placeholder="<?php echo t('quote_your_phone'); ?>" required="">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <select name="type" class="form-control" required="">
                              <option value=""><?php echo t('quote_select_project_type'); ?></option>
                              <option value="civil"><?php echo t('services_civil_concrete'); ?></option>
                              <option value="mep"><?php echo t('services_mep'); ?></option>
                              <option value="fitout"><?php echo t('services_fitout'); ?></option>
                              <option value="landscaping"><?php echo t('services_landscaping'); ?></option>
                              <option value="facility"><?php echo t('services_facility'); ?></option>
                              <option value="risk"><?php echo t('services_risk'); ?></option>
                              <option value="other"><?php echo t('other'); ?></option>
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <select name="timeline" class="form-control" required="">
                              <option value=""><?php echo t('quote_project_timeline'); ?></option>
                              <option value="asap"><?php echo t('quote_timeline_asap'); ?></option>
                              <option value="1-3months"><?php echo t('quote_timeline_1_3'); ?></option>
                              <option value="3-6months"><?php echo t('quote_timeline_3_6'); ?></option>
                              <option value="6-12months"><?php echo t('quote_timeline_6_12'); ?></option>
                              <option value="planning"><?php echo t('quote_timeline_planning'); ?></option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <input type="text" name="budget" class="form-control" id="budget" placeholder="<?php echo t('quote_budget'); ?>">
                          </div>
                        </div>
                      </div>

                      <div class="form-group">
                        <textarea class="form-control" name="message" rows="5" placeholder="<?php echo t('quote_project_details'); ?>" required=""></textarea>
                      </div>

                      <div class="loading"><?php echo t('contact_loading'); ?></div>
                      <div class="error-message"></div>
                      <div class="sent-message"><?php echo t('quote_sent_success'); ?></div>

                      <div class="text-center">
                        <button type="submit"><?php echo t('quote_get_free_quote'); ?></button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Quote Section -->

  </main>

  <?php
// Include footer
require_once __DIR__ . '/include/footer.php';
?>