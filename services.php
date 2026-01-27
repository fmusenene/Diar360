<?php
/**
 * Services Page
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
        <h1 class="mb-2 mb-lg-0"><?php echo t('nav_services'); ?></h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php"><?php echo t('breadcrumb_home'); ?></a></li>
            <li class="current"><?php echo t('nav_services'); ?></li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Services Section -->
    <section id="services" class="services section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">
          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="service-card">
              <div class="service-icon">
                <i class="bi bi-building"></i>
              </div>
              <h3><?php echo t('services_civil_concrete'); ?></h3>
              <p><?php echo t('services_civil_desc'); ?></p>
              <div class="service-features">
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_foundation_work'); ?></span>
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_structural_concrete'); ?></span>
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_infrastructure'); ?></span>
              </div>
              <a href="service-details.php?service=civil-and-concrete-work" class="service-link"><?php echo t('services_learn_more'); ?> <i class="bi bi-arrow-right"></i></a>
            </div>
          </div><!-- End Service Item -->

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
            <div class="service-card featured">
              <div class="service-badge"><?php echo t('services_most_requested'); ?></div>
              <div class="service-icon">
                <i class="bi bi-gear"></i>
              </div>
              <h3><?php echo t('services_mep'); ?></h3>
              <p><?php echo t('services_mep_desc'); ?></p>
              <div class="service-features">
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_mechanical_systems'); ?></span>
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_electrical'); ?></span>
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_plumbing'); ?></span>
              </div>
              <a href="service-details.php?service=mep" class="service-link"><?php echo t('services_learn_more'); ?> <i class="bi bi-arrow-right"></i></a>
            </div>
          </div><!-- End Service Item -->

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="400">
            <div class="service-card">
              <div class="service-icon">
                <i class="bi bi-paint-bucket"></i>
              </div>
              <h3><?php echo t('services_fitout'); ?></h3>
              <p><?php echo t('services_fitout_desc'); ?></p>
              <div class="service-features">
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_interior_fitout'); ?></span>
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_finishing_works'); ?></span>
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_quality_craftsmanship'); ?></span>
              </div>
              <a href="service-details.php?service=fit-out-and-finishing" class="service-link"><?php echo t('services_learn_more'); ?> <i class="bi bi-arrow-right"></i></a>
            </div>
          </div><!-- End Service Item -->

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="500">
            <div class="service-card">
              <div class="service-icon">
                <i class="bi bi-tree"></i>
              </div>
              <h3><?php echo t('services_landscaping'); ?></h3>
              <p><?php echo t('services_landscaping_desc'); ?></p>
              <div class="service-features">
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_garden_design'); ?></span>
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_outdoor_spaces'); ?></span>
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_maintenance'); ?></span>
              </div>
              <a href="service-details.php?service=landscaping" class="service-link"><?php echo t('services_learn_more'); ?> <i class="bi bi-arrow-right"></i></a>
            </div>
          </div><!-- End Service Item -->

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="600">
            <div class="service-card">
              <div class="service-icon">
                <i class="bi bi-shield-check"></i>
              </div>
              <h3><?php echo t('services_facility'); ?></h3>
              <p><?php echo t('services_facility_desc'); ?></p>
              <div class="service-features">
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_maintenance'); ?></span>
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_operations'); ?></span>
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_quality_assurance'); ?></span>
              </div>
              <a href="service-details.php?service=facility-management" class="service-link"><?php echo t('services_learn_more'); ?> <i class="bi bi-arrow-right"></i></a>
            </div>
          </div><!-- End Service Item -->

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="700">
            <div class="service-card">
              <div class="service-icon">
                <i class="bi bi-exclamation-triangle"></i>
              </div>
              <h3><?php echo t('services_risk'); ?></h3>
              <p><?php echo t('services_risk_desc'); ?></p>
              <div class="service-features">
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_risk_assessment'); ?></span>
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_safety'); ?></span>
                <span><i class="bi bi-check-circle"></i> <?php echo t('services_compliance'); ?></span>
              </div>
              <a href="service-details.php?service=risk-management" class="service-link"><?php echo t('services_learn_more'); ?> <i class="bi bi-arrow-right"></i></a>
            </div>
          </div><!-- End Service Item -->
        </div>

        <div class="row mt-5">
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="service-image-block">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-1.webp" alt="Construction Services" class="img-fluid">
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
            <div class="service-list-block">
              <h3>DIAR 360 KNOW HOW FOR ALL YOUR PROJECTS</h3>
              <p>Our comprehensive approach ensures perfect solutions tailored to client needs with innovative design, efficient installation, and skilled contracting.</p>

              <div class="service-list">
                <div class="service-list-item" data-aos="fade-up" data-aos-delay="100">
                  <div class="service-list-icon">
                    <i class="bi bi-lightbulb"></i>
                  </div>
                  <div class="service-list-content">
                    <h4>Innovative Design</h4>
                    <p>Our in-house technical expertise ensures perfect solutions tailored to client needs.</p>
                  </div>
                </div><!-- End Service List Item -->

                <div class="service-list-item" data-aos="fade-up" data-aos-delay="200">
                  <div class="service-list-icon">
                    <i class="bi bi-speedometer2"></i>
                  </div>
                  <div class="service-list-content">
                    <h4>Efficient Installation</h4>
                    <p>Our teams ensure swift and precise project execution.</p>
                  </div>
                </div><!-- End Service List Item -->

                <div class="service-list-item" data-aos="fade-up" data-aos-delay="300">
                  <div class="service-list-icon">
                    <i class="bi bi-tools"></i>
                  </div>
                  <div class="service-list-content">
                    <h4>Skilled Contracting</h4>
                    <p>We utilize state-of-the-art equipment and technologies to meet performance standards.</p>
                  </div>
                </div><!-- End Service List Item -->
              </div>
            </div>
          </div>
        </div>

        <div class="cta-container text-center mt-5" data-aos="fade-up" data-aos-delay="300">
          <h3>Ready to Start Your Construction Project?</h3>
          <p>Contact DIAR 360 for customized services and expert solutions for all your construction needs.</p>
          <a href="quote.php" class="btn btn-cta">Get In Touch</a>
        </div>

      </div>

    </section><!-- /Services Section -->

  </main>

  <?php
// Include footer
require_once __DIR__ . '/include/footer.php';
?>