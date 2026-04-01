<?php
/**
 * Index Page
 * Homepage of the website
 */

// Include header
require_once __DIR__ . '/include/header.php';

// Load language functions
require_once __DIR__ . '/functions/language.php';
?>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section" data-scroll-fade data-fade-start="0.1" data-fade-end="0.7">

      <div class="container" data-aos="fade-up" data-aos-delay="100" data-scroll-fade data-fade-start="0.15" data-fade-end="0.75">

        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="hero-content" data-aos="fade-right" data-aos-delay="200" data-scroll-fade data-fade-start="0.2" data-fade-end="0.8">
              <span class="subtitle"><?php echo t('hero_subtitle'); ?></span>
              <h1><?php echo t('site_tagline'); ?></h1>
              <p><?php echo t('company_description'); ?></p>

              <div class="hero-buttons">
                <a href="quote.php" class="btn-primary"><?php echo t('hero_get_in_touch'); ?></a>
                <a href="projects.php" class="btn-secondary"><?php echo t('hero_our_projects'); ?></a>
              </div>

              <div class="trust-badges">
                <div class="badge-item">
                  <i class="bi bi-building-check"></i>
                  <div class="badge-text">
                    <span class="count"><?php echo convertNumbers(COMPANY_EXPERIENCE_YEARS . '+'); ?></span>
                    <span class="label"><?php echo t('hero_years_experience'); ?></span>
                  </div>
                </div>
                <div class="badge-item">
                  <i class="bi bi-trophy"></i>
                  <div class="badge-text">
                    <span class="count"><?php echo t('about_expert'); ?></span>
                    <span class="label"><?php echo t('about_engineers_label'); ?></span>
                  </div>
                </div>
                <div class="badge-item">
                  <i class="bi bi-people"></i>
                  <div class="badge-text">
                    <span class="count"><?php echo t('about_global'); ?></span>
                    <span class="label"><?php echo t('about_partners'); ?></span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300" data-scroll-fade data-fade-start="0.25" data-fade-end="0.85">
            <div class="hero-image">
              <img src="<?php echo asset('img/construction/showcase-3.webp'); ?>" alt="<?php echo t('construction_project'); ?>" class="img-fluid">
              <div class="image-badge">
                <span><?php echo t('hero_iso_certified'); ?></span>
                <p><?php echo t('hero_certified_construction'); ?></p>
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Hero Section -->

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
                    <img src="<?php echo asset('img/construction/badge-4.webp'); ?>" alt="<?php echo t('certification'); ?>" class="img-fluid">
                  </div>
                  <div class="col-4 col-md-3">
                    <img src="<?php echo asset('img/construction/badge-3.webp'); ?>" alt="<?php echo t('certification'); ?>" class="img-fluid">
                  </div>
                  <div class="col-4 col-md-3">
                    <img src="<?php echo asset('img/construction/badge-5.webp'); ?>" alt="<?php echo t('certification'); ?>" class="img-fluid">
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
              <img src="<?php echo asset('img/construction/project-3.webp'); ?>" alt="<?php echo t('construction_team'); ?>" class="img-fluid main-image rounded">
              <div class="image-overlay">
                <img src="<?php echo asset('img/construction/project-7.webp'); ?>" alt="<?php echo t('construction_project'); ?>" class="img-fluid rounded">
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

    <!-- Services Section -->
    <section id="services" class="services section">

      <!-- Section Title -->
      <div class="container section-title">
        <h2><?php echo t('services_title'); ?></h2>
        <p><?php echo t('services_subtitle'); ?></p>
      </div><!-- End Section Title -->

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
        </div>

        <div class="row mt-5">
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="service-image-block">
              <img src="<?php echo asset('img/construction/project-1.webp'); ?>" alt="Construction Services" class="img-fluid">
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
            <div class="service-list-block">
              <h3><?php echo t('services_know_how_title'); ?></h3>
              <p><?php echo t('services_know_how_desc'); ?></p>

              <div class="service-list">
                <div class="service-list-item" data-aos="fade-up" data-aos-delay="100">
                  <div class="service-list-icon">
                    <i class="bi bi-lightbulb"></i>
                  </div>
                  <div class="service-list-content">
                    <h4><?php echo t('services_innovative_design'); ?></h4>
                    <p><?php echo t('services_innovative_desc'); ?></p>
                  </div>
                </div><!-- End Service List Item -->

                <div class="service-list-item" data-aos="fade-up" data-aos-delay="200">
                  <div class="service-list-icon">
                    <i class="bi bi-speedometer2"></i>
                  </div>
                  <div class="service-list-content">
                    <h4><?php echo t('services_efficient'); ?></h4>
                    <p><?php echo t('services_efficient_desc'); ?></p>
                  </div>
                </div><!-- End Service List Item -->

                <div class="service-list-item" data-aos="fade-up" data-aos-delay="300">
                  <div class="service-list-icon">
                    <i class="bi bi-tools"></i>
                  </div>
                  <div class="service-list-content">
                    <h4><?php echo t('services_skilled'); ?></h4>
                    <p><?php echo t('services_skilled_desc'); ?></p>
                  </div>
                </div><!-- End Service List Item -->
              </div>
            </div>
          </div>
        </div>

        <div class="cta-container text-center mt-5" data-aos="fade-up" data-aos-delay="300">
          <h3><?php echo t('services_ready_title'); ?></h3>
          <p><?php echo t('services_ready_desc'); ?></p>
          <a href="quote.php" class="btn btn-cta"><?php echo t('services_get_in_touch'); ?></a>
        </div>

      </div>

    </section><!-- /Services Section -->

    <!-- Projects Section -->
    <section id="projects" class="projects section">

      <!-- Section Title -->
      <div class="container section-title">
        <h2><?php echo t('projects_title'); ?></h2>
        <p><?php echo t('projects_subtitle'); ?></p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="projects-grid">

          <!-- Makkah Project - Chilled Water -->
          <div class="project-item" data-aos="zoom-in" data-aos-delay="100">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo t('project_category_mep') !== 'project_category_mep' ? t('project_category_mep') : 'MEP'; ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo t('project_makkah-chilled-water_title'); ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo t('project_makkah-chilled-water_description'); ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-droplet"></i>
                      <?php echo t('chilled_water_system'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('12.5'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo t('makkah') !== 'makkah' ? t('makkah') : 'Makkah'; ?></span>
                </div>
              </div>
              <a href="project-details.php?project=makkah-chilled-water" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo asset('img/construction/project-2.webp'); ?>" alt="Makkah Project Chilled Water" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->

          <!-- Makkah Project - Duct Work -->
          <div class="project-item" data-aos="zoom-in" data-aos-delay="200">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo t('project_category_mep') !== 'project_category_mep' ? t('project_category_mep') : 'MEP'; ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo t('project_makkah-duct-work_title'); ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo t('project_makkah-duct-work_description'); ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-gear"></i>
                      <?php echo t('duct_work'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('6'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo t('makkah') !== 'makkah' ? t('makkah') : 'Makkah'; ?></span>
                </div>
              </div>
              <a href="project-details.php?project=makkah-duct-work" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo asset('img/construction/project-6.webp'); ?>" alt="Makkah Project Duct Work" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->

          <!-- Rimal Project -->
          <div class="project-item" data-aos="zoom-in" data-aos-delay="300">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo t('project_category_infrastructure') !== 'project_category_infrastructure' ? t('project_category_infrastructure') : 'Infrastructure'; ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo t('project_rimal-project_title'); ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo t('project_rimal-project_description'); ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-tools"></i>
                      <?php echo t('civil_mep_work'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('11'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo t('riyadh') !== 'riyadh' ? t('riyadh') : 'Riyadh'; ?></span>
                </div>
              </div>
              <a href="project-details.php?project=rimal-project" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo asset('img/construction/project-4.webp'); ?>" alt="Rimal Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->

          <!-- Lamar Towers Project -->
          <div class="project-item" data-aos="zoom-in" data-aos-delay="100">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo t('project_category_towers') !== 'project_category_towers' ? t('project_category_towers') : 'Towers'; ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo t('project_lamar-towers_title'); ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo t('project_lamar-towers_description'); ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo convertNumbers('2'); ?> <?php echo t('towers'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-rulers"></i>
                      <?php echo convertNumbers('70,000'); ?> <?php echo t('m2'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo t('corniche_road_jeddah') !== 'corniche_road_jeddah' ? t('corniche_road_jeddah') : 'Corniche Road, Jeddah'; ?></span>
                </div>
              </div>
              <a href="project-details.php?project=lamar-towers" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo asset('img/construction/project-8.webp'); ?>" alt="Lamar Towers Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->

          <!-- Elegance Tower Project -->
          <div class="project-item" data-aos="zoom-in" data-aos-delay="200">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo t('project_category_towers') !== 'project_category_towers' ? t('project_category_towers') : 'Towers'; ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo t('project_elegance-tower_title'); ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo t('project_elegance-tower_description'); ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo convertNumbers('28'); ?> <?php echo t('floors'); ?> + <?php echo convertNumbers('5'); ?> <?php echo t('basements'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-rulers"></i>
                      <?php echo convertNumbers('63,000'); ?> <?php echo t('m2'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo t('riyadh') !== 'riyadh' ? t('riyadh') : 'Riyadh'; ?></span>
                </div>
              </div>
              <a href="project-details.php?project=elegance-tower" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo asset('img/construction/project-10.webp'); ?>" alt="Elegance Tower Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->

          <!-- Riyadh Metro Project -->
          <div class="project-item" data-aos="zoom-in" data-aos-delay="300">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo t('project_category_infrastructure') !== 'project_category_infrastructure' ? t('project_category_infrastructure') : 'Infrastructure'; ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo t('riyadh_metro_project'); ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p>
                    <?php echo t('civil_mep_work'); ?>
                    <?php echo t('for'); ?>
                    <?php echo t('riyadh_metro_project'); ?>.
                    <?php echo t('handed_over_on'); ?>
                    <?php echo convertNumbers('10'); ?>
                    <?php echo t('january'); ?>
                    <?php echo convertNumbers('2017'); ?>
                    <?php echo t('with_contract_value'); ?>
                    <?php echo convertNumbers('8'); ?> <?php echo t('million_sar'); ?>.
                  </p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-train-front"></i>
                      <?php echo t('project_category_infrastructure'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('8'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo t('riyadh') !== 'riyadh' ? t('riyadh') : 'Riyadh'; ?></span>
                </div>
              </div>
              <a href="project-details.php?project=riyadh-metro" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo asset('img/construction/project-12.webp'); ?>" alt="Riyadh Metro Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->

        </div>

      </div>

    </section><!-- /Projects Section -->

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="testimonials-slider swiper init-swiper">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": 1,
              "spaceBetween": 30,
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              },
              "navigation": {
                "nextEl": ".swiper-button-next",
                "prevEl": ".swiper-button-prev"
              }
            }
          </script>
          <div class="swiper-wrapper">

            <div class="swiper-slide">
              <div class="testimonial-slide" data-aos="fade-up" data-aos-delay="200">
                <div class="testimonial-header">
                  <div class="stars-rating">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                  </div>
                  <div class="quote-icon">
                    <i class="bi bi-quote"></i>
                  </div>
                </div>
                <div class="testimonial-body">
                  <p>"<?php echo t('testimonial_1'); ?>"</p>
                </div>
                <div class="testimonial-footer">
                  <div class="author-info">
                    <img src="<?php echo asset('img/person/person-f-12.webp'); ?>" alt="<?php echo t('testimonial_author'); ?>" class="author-avatar">
                    <div class="author-details">
                      <h4>Sophia Martinez</h4>
                      <span class="role"><?php echo t('testimonial_operations_director'); ?></span>
                      <span class="company">TechVision Corp</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="swiper-slide">
              <div class="testimonial-slide" data-aos="fade-up" data-aos-delay="300">
                <div class="testimonial-header">
                  <div class="stars-rating">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                  </div>
                  <div class="quote-icon">
                    <i class="bi bi-quote"></i>
                  </div>
                </div>
                <div class="testimonial-body">
                  <p>"<?php echo t('testimonial_2'); ?>"</p>
                </div>
                <div class="testimonial-footer">
                  <div class="author-info">
                    <img src="<?php echo asset('img/person/person-m-14.webp'); ?>" alt="<?php echo t('testimonial_author'); ?>" class="author-avatar">
                    <div class="author-details">
                      <h4>Michael Anderson</h4>
                      <span class="role"><?php echo t('testimonial_project_manager'); ?></span>
                      <span class="company">InnovateTech Ltd</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="swiper-slide">
              <div class="testimonial-slide" data-aos="fade-up" data-aos-delay="400">
                <div class="testimonial-header">
                  <div class="stars-rating">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                  </div>
                  <div class="quote-icon">
                    <i class="bi bi-quote"></i>
                  </div>
                </div>
                <div class="testimonial-body">
                  <p>"<?php echo t('testimonial_3'); ?>"</p>
                </div>
                <div class="testimonial-footer">
                  <div class="author-info">
                    <img src="<?php echo asset('img/person/person-f-11.webp'); ?>" alt="<?php echo t('testimonial_author'); ?>" class="author-avatar">
                    <div class="author-details">
                      <h4>Jennifer Wilson</h4>
                      <span class="role"><?php echo t('testimonial_digital_strategy'); ?></span>
                      <span class="company">FutureScope Inc</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <div class="swiper-navigation-wrapper">
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
          </div>

        </div>

      </div>

    </section><!-- /Testimonials Section -->

    <!-- Certifications Section -->
    <section id="certifications" class="certifications section">

      <!-- Section Title -->
      <div class="container section-title">
        <h2><?php echo t('certifications_title'); ?></h2>
        <p><?php echo t('certifications_subtitle'); ?></p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row align-items-center mb-5 content">
          <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
            <h2><?php echo t('certifications_global_partners'); ?></h2>
            <p><?php echo t('certifications_global_desc'); ?></p>
          </div>
          <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
            <div class="badge-highlight">
              <img src="<?php echo asset('img/construction/badge-5.webp'); ?>" alt="<?php echo t('certifications_quality_badge'); ?>" class="img-fluid">
              <div class="badge-content">
                <h4><?php echo t('certifications_premier_status'); ?></h4>
                <p><?php echo t('certifications_premier_desc'); ?></p>
              </div>
            </div>
          </div>
        </div>

        <div class="certification-grid" data-aos="fade-up" data-aos-delay="400">

          <div class="cert-card" data-aos="flip-left" data-aos-delay="100">
            <div class="cert-icon">
              <img src="<?php echo asset('img/construction/badge-1.webp'); ?>" alt="ISO 9001" class="img-fluid">
            </div>
            <div class="cert-details">
              <h5><?php echo t('certifications_iso_9001'); ?></h5>
              <span class="cert-category"><?php echo t('certifications_quality_management'); ?></span>
              <p><?php echo t('certifications_iso_desc'); ?></p>
            </div>
          </div>

          <div class="cert-card" data-aos="flip-left" data-aos-delay="200">
            <div class="cert-icon">
              <img src="<?php echo asset('img/construction/badge-2.webp'); ?>" alt="OSHA" class="img-fluid">
            </div>
            <div class="cert-details">
              <h5>OSHA 30-Hour</h5>
              <span class="cert-category"><?php echo t('safety_standards'); ?></span>
              <p><?php echo t('cert_osha_desc'); ?></p>
            </div>
          </div>

          <div class="cert-card" data-aos="flip-left" data-aos-delay="300">
            <div class="cert-icon">
              <img src="<?php echo asset('img/construction/badge-3.webp'); ?>" alt="<?php echo t('licensed'); ?>" class="img-fluid">
            </div>
            <div class="cert-details">
              <h5><?php echo t('state_licensed'); ?></h5>
              <span class="cert-category"><?php echo t('legal_compliance'); ?></span>
              <p><?php echo t('cert_licensed_desc'); ?></p>
            </div>
          </div>

          <div class="cert-card" data-aos="flip-left" data-aos-delay="400">
            <div class="cert-icon">
              <img src="<?php echo asset('img/construction/badge-4.webp'); ?>" alt="<?php echo t('green_building'); ?>" class="img-fluid">
            </div>
            <div class="cert-details">
              <h5><?php echo t('leed_certified'); ?></h5>
              <span class="cert-category"><?php echo t('sustainable_building'); ?></span>
              <p><?php echo t('cert_leed_desc'); ?></p>
            </div>
          </div>

          <div class="cert-card" data-aos="flip-left" data-aos-delay="500">
            <div class="cert-icon">
              <img src="<?php echo asset('img/construction/badge-6.webp'); ?>" alt="<?php echo t('insurance'); ?>" class="img-fluid">
            </div>
            <div class="cert-details">
              <h5><?php echo t('fully_insured'); ?></h5>
              <span class="cert-category"><?php echo t('risk_management'); ?></span>
              <p><?php echo t('cert_insured_desc'); ?></p>
            </div>
          </div>

          <div class="cert-card" data-aos="flip-left" data-aos-delay="600">
            <div class="cert-icon">
              <img src="<?php echo asset('img/construction/badge-7.webp'); ?>" alt="<?php echo t('training'); ?>" class="img-fluid">
            </div>
            <div class="cert-details">
              <h5><?php echo t('skills_certified'); ?></h5>
              <span class="cert-category"><?php echo t('professional_training'); ?></span>
              <p><?php echo t('cert_skills_desc'); ?></p>
            </div>
          </div>

        </div>

        <div class="achievements-banner" data-aos="zoom-in" data-aos-delay="700">
          <div class="row text-center">
            <div class="col-lg-3 col-sm-6">
              <div class="achievement-item">
                <i class="bi bi-folder-check"></i>
                <h3><?php echo t('projects_title'); ?></h3>
                <p><?php echo t('complete'); ?></p>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="achievement-item">
                <i class="bi bi-percent"></i>
                <h3><?php echo t('about_success_rate'); ?></h3>
                <p><?php echo t('rate'); ?></p>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="achievement-item">
                <i class="bi bi-clock-history"></i>
                <h3><?php echo convertNumbers(COMPANY_EXPERIENCE_YEARS); ?></h3>
                <p><?php echo t('about_years_of_experience'); ?></p>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="achievement-item">
                <i class="bi bi-people"></i>
                <h3><?php echo t('about_expert'); ?></h3>
                <p><?php echo t('about_engineers_label'); ?></p>
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Certifications Section -->

    <!-- Team Section -->
    <section id="team" class="team section">

      <!-- Section Title -->
      <div class="container section-title">
        <h2><?php echo t('team_expert_team'); ?></h2>
        <p><?php echo t('team_engineers_desc'); ?></p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="team-card featured">
              <div class="team-header">
                <div class="team-image">
                  <img src="<?php echo asset('img/construction/team-1.webp'); ?>" class="img-fluid" alt="">
                  <div class="experience-badge"><?php echo convertNumbers('15+'); ?> <?php echo t('years'); ?></div>
                </div>
                <div class="team-info">
                  <h4>Marcus Thompson</h4>
                  <span class="position"><?php echo t('testimonial_project_manager'); ?></span>
                  <div class="contact-info">
                    <a href="mailto:marcus@example.com"><i class="bi bi-envelope"></i> marcus@example.com</a>
                    <a href="tel:+1555123456"><i class="bi bi-telephone"></i> +1 (555) 123-456</a>
                  </div>
                </div>
              </div>
              <div class="team-details">
                <p><?php echo t('team_member_desc'); ?></p>
                <div class="credentials">
                  <div class="cred-item">
                    <i class="bi bi-award"></i>
                    <span><?php echo t('pmp_certified'); ?></span>
                  </div>
                  <div class="cred-item">
                    <i class="bi bi-shield-check"></i>
                    <span>OSHA 30</span>
                  </div>
                </div>
                <div class="social-links">
                  <a href="#"><i class="bi bi-linkedin"></i></a>
                  <a href="#"><i class="bi bi-twitter-x"></i></a>
                  <a href="#"><i class="bi bi-facebook"></i></a>
                </div>
              </div>
            </div>
          </div><!-- End Featured Team Member -->

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
            <div class="team-card featured">
              <div class="team-header">
                <div class="team-image">
                  <img src="<?php echo asset('img/construction/team-2.webp'); ?>" class="img-fluid" alt="">
                  <div class="experience-badge"><?php echo convertNumbers('12+'); ?> <?php echo t('years'); ?></div>
                </div>
                <div class="team-info">
                  <h4>Sarah Rodriguez</h4>
                  <span class="position"><?php echo t('site_supervisor'); ?></span>
                  <div class="contact-info">
                    <a href="mailto:sarah@example.com"><i class="bi bi-envelope"></i> sarah@example.com</a>
                    <a href="tel:+1555123457"><i class="bi bi-telephone"></i> +1 (555) 123-457</a>
                  </div>
                </div>
              </div>
              <div class="team-details">
                <p><?php echo t('team_member_desc'); ?></p>
                <div class="credentials">
                  <div class="cred-item">
                    <i class="bi bi-person-badge"></i>
                    <span><?php echo t('licensed_contractor'); ?></span>
                  </div>
                  <div class="cred-item">
                    <i class="bi bi-tools"></i>
                    <span><?php echo t('site_management'); ?></span>
                  </div>
                </div>
                <div class="social-links">
                  <a href="#"><i class="bi bi-linkedin"></i></a>
                  <a href="#"><i class="bi bi-twitter-x"></i></a>
                  <a href="#"><i class="bi bi-instagram"></i></a>
                </div>
              </div>
            </div>
          </div><!-- End Featured Team Member -->

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="team-card compact">
              <div class="member-photo">
                <img src="<?php echo asset('img/construction/team-3.webp'); ?>" class="img-fluid" alt="">
                <div class="hover-overlay">
                  <div class="overlay-content">
                    <h5>David Chen</h5>
                    <span><?php echo t('lead_engineer'); ?></span>
                    <div class="quick-contact">
                      <a href="#"><i class="bi bi-envelope"></i></a>
                      <a href="#"><i class="bi bi-telephone"></i></a>
                      <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="member-summary">
                <h5>David Chen</h5>
                <span><?php echo t('lead_engineer'); ?></span>
                <div class="skills">
                  <span class="skill-tag"><?php echo t('pe_license'); ?></span>
                  <span class="skill-tag"><?php echo t('leed_ap'); ?></span>
                </div>
              </div>
            </div>
          </div><!-- End Compact Team Member -->

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
            <div class="team-card compact">
              <div class="member-photo">
                <img src="<?php echo asset('img/construction/team-4.webp'); ?>" class="img-fluid" alt="">
                <div class="hover-overlay">
                  <div class="overlay-content">
                    <h5>Lisa Johnson</h5>
                    <span><?php echo t('safety_coordinator'); ?></span>
                    <div class="quick-contact">
                      <a href="#"><i class="bi bi-envelope"></i></a>
                      <a href="#"><i class="bi bi-telephone"></i></a>
                      <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="member-summary">
                <h5>Lisa Johnson</h5>
                <span><?php echo t('safety_coordinator'); ?></span>
                <div class="skills">
                  <span class="skill-tag"><?php echo t('csp_certified'); ?></span>
                  <span class="skill-tag"><?php echo t('safety_expert'); ?></span>
                </div>
              </div>
            </div>
          </div><!-- End Compact Team Member -->

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="team-card compact">
              <div class="member-photo">
                <img src="<?php echo asset('img/construction/team-5.webp'); ?>" class="img-fluid" alt="">
                <div class="hover-overlay">
                  <div class="overlay-content">
                    <h5>Robert Martinez</h5>
                    <span><?php echo t('equipment_operator'); ?></span>
                    <div class="quick-contact">
                      <a href="#"><i class="bi bi-envelope"></i></a>
                      <a href="#"><i class="bi bi-telephone"></i></a>
                      <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="member-summary">
                <h5>Robert Martinez</h5>
                <span><?php echo t('equipment_operator'); ?></span>
                <div class="skills">
                  <span class="skill-tag"><?php echo t('heavy_equipment'); ?></span>
                  <span class="skill-tag"><?php echo convertNumbers('10+'); ?> <?php echo t('years'); ?></span>
                </div>
              </div>
            </div>
          </div><!-- End Compact Team Member -->

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="team-card compact">
              <div class="member-photo">
                <img src="<?php echo asset('img/construction/team-6.webp'); ?>" class="img-fluid" alt="">
                <div class="hover-overlay">
                  <div class="overlay-content">
                    <h5>Emily Davis</h5>
                    <span><?php echo t('quality_control_specialist'); ?></span>
                    <div class="quick-contact">
                      <a href="#"><i class="bi bi-envelope"></i></a>
                      <a href="#"><i class="bi bi-telephone"></i></a>
                      <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="member-summary">
                <h5>Emily Davis</h5>
                <span><?php echo t('quality_control_specialist'); ?></span>
                <div class="skills">
                  <span class="skill-tag"><?php echo t('quality_assurance'); ?></span>
                  <span class="skill-tag"><?php echo t('certified'); ?></span>
                </div>
              </div>
            </div>
          </div><!-- End Compact Team Member -->

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="team-card compact">
              <div class="member-photo">
                <img src="<?php echo asset('img/construction/team-7.webp'); ?>" class="img-fluid" alt="">
                <div class="hover-overlay">
                  <div class="overlay-content">
                    <h5>James Wilson</h5>
                    <span><?php echo t('foreman'); ?></span>
                    <div class="quick-contact">
                      <a href="#"><i class="bi bi-envelope"></i></a>
                      <a href="#"><i class="bi bi-telephone"></i></a>
                      <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="member-summary">
                <h5>James Wilson</h5>
                <span><?php echo t('foreman'); ?></span>
                <div class="skills">
                  <span class="skill-tag"><?php echo t('supervisor'); ?></span>
                  <span class="skill-tag"><?php echo t('leadership'); ?></span>
                </div>
              </div>
            </div>
          </div><!-- End Compact Team Member -->

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
            <div class="team-card compact">
              <div class="member-photo">
                <img src="<?php echo asset('img/construction/team-8.webp'); ?>" class="img-fluid" alt="">
                <div class="hover-overlay">
                  <div class="overlay-content">
                    <h5>Amanda Taylor</h5>
                    <span><?php echo t('estimator'); ?></span>
                    <div class="quick-contact">
                      <a href="#"><i class="bi bi-envelope"></i></a>
                      <a href="#"><i class="bi bi-telephone"></i></a>
                      <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="member-summary">
                <h5>Amanda Taylor</h5>
                <span><?php echo t('estimator'); ?></span>
                <div class="skills">
                  <span class="skill-tag"><?php echo t('cost_professional'); ?></span>
                  <span class="skill-tag"><?php echo t('analytics'); ?></span>
                </div>
              </div>
            </div>
          </div><!-- End Compact Team Member -->

        </div>

      </div>

    </section><!-- /Team Section -->

    <!-- Call To Action Section -->
    <section id="call-to-action" class="call-to-action section light-background">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-5 align-items-center">

          <div class="col-lg-6">
            <div class="cta-hero-content" data-aos="fade-right" data-aos-delay="200">
              <div class="badge-wrapper">
                <span class="cta-badge">
                  <i class="bi bi-shield-check"></i>
                  <?php echo t('licensed_bonded_since'); ?> <?php echo convertNumbers('2008'); ?>
                </span>
              </div>

              <h2><?php echo t('quote_get_in_touch'); ?></h2>
              <p><?php echo t('company_description'); ?> <?php echo t('cta_operates_desc'); ?></p>

              <div class="feature-highlights">
                <div class="highlight-item">
                  <i class="bi bi-check-circle-fill"></i>
                  <span><?php echo t('cta_expert_engineers'); ?></span>
                </div>
                <div class="highlight-item">
                  <i class="bi bi-check-circle-fill"></i>
                  <span><?php echo t('cta_state_of_art'); ?></span>
                </div>
                <div class="highlight-item">
                  <i class="bi bi-check-circle-fill"></i>
                  <span><?php echo t('cta_customer_satisfaction'); ?></span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="cta-form-section" data-aos="fade-left" data-aos-delay="300">
              <div class="form-container">
                <div class="form-header">
                  <h3><?php echo t('quote_request_title'); ?></h3>
                  <p><?php echo t('cta_get_started'); ?></p>
                </div>

                <form action="forms/get-a-quote.php" method="post" class="php-email-form">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <div class="form-group">
                        <input type="text" name="name" class="form-control" placeholder="<?php echo t('quote_your_name'); ?>" required="">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="<?php echo t('quote_your_email'); ?>" required="">
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="form-group">
                        <input type="tel" name="phone" class="form-control" placeholder="<?php echo t('quote_your_phone'); ?>" required="">
                      </div>
                    </div>
                    <div class="col-12">
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
                    <div class="col-12">
                      <div class="form-group">
                        <textarea name="message" class="form-control" rows="4" placeholder="<?php echo t('quote_project_details'); ?>" required=""></textarea>
                      </div>
                    </div>
                  </div>

                  <div class="loading"><?php echo t('contact_loading'); ?></div>
                  <div class="error-message"></div>
                  <div class="sent-message"><?php echo t('quote_sent_success'); ?></div>

                  <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                      <i class="bi bi-send"></i>
                      <?php echo t('send_quote_request'); ?>
                    </button>

                    <div class="contact-alternative">
                      <span><?php echo t('or_call_directly'); ?></span>
                      <a href="tel:<?php echo str_replace(' ', '', SITE_PHONE); ?>" class="phone-link">
                        <i class="bi bi-telephone-fill"></i>
                        <?php echo formatPhoneNumber(SITE_PHONE); ?>
                      </a>
                    </div>
                  </div>
                </form>
              </div>

              <div class="trust-indicators" data-aos="fade-up" data-aos-delay="400">
                <div class="row g-3">
                  <div class="col-4">
                    <div class="trust-item">
                      <div class="trust-icon">
                        <i class="bi bi-clock"></i>
                      </div>
                      <div class="trust-content">
                        <span class="trust-number"><?php echo convertNumbers('24h'); ?></span>
                        <span class="trust-label"><?php echo t('quote_response_time'); ?></span>
                      </div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="trust-item">
                      <div class="trust-icon">
                        <i class="bi bi-star-fill"></i>
                      </div>
                      <div class="trust-content">
                        <span class="trust-number"><?php echo convertNumbers('4.9'); ?></span>
                        <span class="trust-label"><?php echo t('customer_rating'); ?></span>
                      </div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="trust-item">
                      <div class="trust-icon">
                        <i class="bi bi-hammer"></i>
                      </div>
                      <div class="trust-content">
                        <span class="trust-number"><?php echo convertNumbers('350+'); ?></span>
                        <span class="trust-label"><?php echo t('projects_done'); ?></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>

    </section><!-- /Call To Action Section -->

  </main>

<?php
// Include footer
require_once __DIR__ . '/include/footer.php';
?>