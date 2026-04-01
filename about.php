<?php
/**
 * About Page
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
        <h1 class="mb-2 mb-lg-0"><?php echo t('nav_about'); ?></h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php"><?php echo t('breadcrumb_home'); ?></a></li>
            <li class="current"><?php echo t('nav_about'); ?></li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <div class="about-content" data-aos="fade-right" data-aos-delay="200">
              <h2><?php echo t('about_title'); ?></h2>
              <p class="lead"><?php echo t('company_description'); ?></p>
              <p><?php echo t('about_company_operates'); ?></p>
              <p><?php echo t('about_engineers'); ?></p>
              <p><?php echo t('about_customer_satisfaction'); ?></p>

              <div class="achievement-boxes row g-4 mt-4">
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="300">
                  <div class="achievement-box">
                    <h3><?php echo convertNumbers(COMPANY_EXPERIENCE_YEARS); ?></h3>
                    <p><?php echo t('about_years_of_experience'); ?></p>
                  </div>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="400">
                  <div class="achievement-box">
                    <h3><?php echo t('about_expert'); ?></h3>
                    <p><?php echo t('about_engineers_label'); ?></p>
                  </div>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="500">
                  <div class="achievement-box">
                    <h3><?php echo convertNumbers('100%'); ?></h3>
                    <p><?php echo t('about_success_rate'); ?></p>
                  </div>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="600">
                  <div class="achievement-box">
                    <h3><?php echo t('about_global'); ?></h3>
                    <p><?php echo t('about_partners'); ?></p>
                  </div>
                </div>
              </div>

              <div class="certifications mt-5" data-aos="fade-up" data-aos-delay="700">
                <h5><?php echo t('about_certifications'); ?></h5>
                <div class="row g-3 align-items-center">
                  <div class="col-4 col-md-3">
                    <img src="<?php echo ASSETS_PATH; ?>/img/construction/badge-4.webp" alt="<?php echo t('certification'); ?>" class="img-fluid">
                  </div>
                  <div class="col-4 col-md-3">
                    <img src="<?php echo ASSETS_PATH; ?>/img/construction/badge-3.webp" alt="<?php echo t('certification'); ?>" class="img-fluid">
                  </div>
                  <div class="col-4 col-md-3">
                    <img src="<?php echo ASSETS_PATH; ?>/img/construction/badge-5.webp" alt="<?php echo t('certification'); ?>" class="img-fluid">
                  </div>
                </div>
              </div>

              <div class="cta-container mt-5" data-aos="fade-up" data-aos-delay="800">
                <a href="about.php" class="btn btn-primary"><?php echo t('about_learn_more'); ?></a>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="about-image position-relative" data-aos="fade-left" data-aos-delay="200">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-3.webp" alt="<?php echo t('construction_team'); ?>" class="img-fluid main-image rounded">
              <div class="image-overlay">
                <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-7.webp" alt="<?php echo t('construction_project'); ?>" class="img-fluid rounded">
              </div>
              <div class="experience-badge" data-aos="zoom-in" data-aos-delay="500">
                <span><?php echo convertNumbers(COMPANY_EXPERIENCE_YEARS . '+'); ?></span>
                <p><?php echo t('about_years_of_experience_badge'); ?></p>
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /About Section -->

  </main>

  <?php
// Include footer
require_once __DIR__ . '/include/footer.php';
?>