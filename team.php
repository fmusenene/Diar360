<?php
/**
 * Team Page
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
        <h1 class="mb-2 mb-lg-0"><?php echo t('nav_team'); ?></h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php"><?php echo t('breadcrumb_home'); ?></a></li>
            <li class="current"><?php echo t('nav_team'); ?></li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Team Section -->
    <section id="team" class="team section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <!-- CEO Message Block - left column behaves like David Chen card (image + hover overlay + summary below) -->
        <div class="ceo-block team-card featured" data-aos="fade-up" data-aos-delay="50">
          <div class="row g-0">
            <div class="col-lg-4">
              <div class="ceo-block-card team-card compact">
                <div class="ceo-block-image member-photo">
                  <img src="<?php echo ASSETS_PATH; ?>/img/construction/CEO.webp" class="img-fluid" alt="Khalil Awada - CEO">
                  <div class="hover-overlay">
                    <div class="overlay-content">
                      <h5>Khalil Awada</h5>
                      <span><?php echo t('ceo'); ?></span>
                      <div class="quick-contact">
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                        <a href="#"><i class="bi bi-envelope"></i></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-8">
              <div class="ceo-block-content">
                <h2 class="ceo-heading"><?php echo t('as_stated_by'); ?> Construction Week Saudi</h2>
                <div class="ceo-message">
                  <p>Khalil Awada is a seasoned leader with a dynamic career spanning multiple industries. A graduate in Business Administration and Marketing from the Lebanese American University (LAU), he began his professional journey in advertising and marketing in 2006. His success led to a role as Marketing Manager at British American Tobacco from 2008 to 2010.</p>
                  <p>In Riyadh, Awada transitioned into the construction and contracting sector with Spectrom Engineering Solutions, rising from Commercial Director to Managing Director. During his tenure, he strengthened client relationships and ensured the successful execution of complex projects across Saudi Arabia.</p>
                  <p>In 2022, Awada co-founded Abnia Innovative Limited, aligning with Saudi Arabia's Vision 2030. There, he applied his expertise to drive innovation in the evolving construction landscape. By 2024, Awada took the helm at Diar 360, leading its transformation journey.</p>
                  <p>At Diar 360, Awada emphasizes client success through cutting-edge civil construction, industrial automation, and turnkey electromechanical projects. Under his leadership, the company integrates the latest technologies with a steadfast commitment to safety, quality, and sustainability, aligning with the Saudi Green Initiative.</p>
                  <p>Diar's vision reflects Awada's dedication to shaping a prosperous, environmentally responsible future for Saudi Arabia, ensuring every project contributes meaningfully to the kingdom's transformation. With a focus on innovation, excellence, and sustainable growth, Diar 360 is a trusted partner in building the nation's future.</p>
                </div>
              </div>
            </div>
          </div>
        </div><!-- End CEO Block -->

        <div class="row gy-4">

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="team-card featured">
              <div class="team-header">
                <div class="team-image">
                  <img src="<?php echo ASSETS_PATH; ?>/img/construction/team-1.webp" class="img-fluid" alt="">
                  <div class="experience-badge"><?php echo convertNumbers('15+'); ?> <?php echo t('years'); ?></div>
                </div>
                <div class="team-info">
                  <h4>Marcus Thompson</h4>
                  <span class="position"><?php echo t('testimonial_project_manager'); ?></span>
                  <div class="contact-info">
                    <a href="mailto:marcus@example.com"><i class="bi bi-envelope"></i> marcus@example.com</a>
                    <a href="tel:+1555123456"><i class="bi bi-telephone"></i> <?php echo convertNumbers('+1 (555) 123-456'); ?></a>
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
                  <img src="<?php echo ASSETS_PATH; ?>/img/construction/team-2.webp" class="img-fluid" alt="">
                  <div class="experience-badge"><?php echo convertNumbers('12+'); ?> <?php echo t('years'); ?></div>
                </div>
                <div class="team-info">
                  <h4>Sarah Rodriguez</h4>
                  <span class="position"><?php echo t('site_supervisor'); ?></span>
                  <div class="contact-info">
                    <a href="mailto:sarah@example.com"><i class="bi bi-envelope"></i> sarah@example.com</a>
                    <a href="tel:+1555123457"><i class="bi bi-telephone"></i> <?php echo convertNumbers('+1 (555) 123-457'); ?></a>
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
                <img src="<?php echo ASSETS_PATH; ?>/img/construction/team-3.webp" class="img-fluid" alt="">
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
                <img src="<?php echo ASSETS_PATH; ?>/img/construction/team-4.webp" class="img-fluid" alt="">
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
                <img src="<?php echo ASSETS_PATH; ?>/img/construction/team-5.webp" class="img-fluid" alt="">
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
                <img src="<?php echo ASSETS_PATH; ?>/img/construction/team-6.webp" class="img-fluid" alt="">
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
                <img src="<?php echo ASSETS_PATH; ?>/img/construction/team-7.webp" class="img-fluid" alt="">
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
                <img src="<?php echo ASSETS_PATH; ?>/img/construction/team-8.webp" class="img-fluid" alt="">
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

  </main>

  <?php
// Include footer
require_once __DIR__ . '/include/footer.php';
?>