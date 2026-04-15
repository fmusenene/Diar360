<?php
/**
 * Service-details Page
 */

// Include header
require_once __DIR__ . '/include/header.php';

// Load language functions
require_once __DIR__ . '/functions/language.php';

// Helper function to get translated service field
function getServiceField($service, $field) {
    // Get current language
    $currentLang = getCurrentLanguage();
    
    // If Arabic language and Arabic field exists, use it
    if ($currentLang === 'ar' && isset($service[$field . '_ar'])) {
        return $service[$field . '_ar'];
    }
    
    // Otherwise use English field
    if (isset($service[$field])) {
        return $service[$field];
    }
    
    return '';
}

// Service slug (e.g. service-details.php?service=civil-and-concrete-work)
$serviceSlug = isset($_GET['service']) ? strtolower(trim((string)$_GET['service'])) : '';
if ($serviceSlug === '') {
    $serviceSlug = 'civil-and-concrete-work';
}

// Service content map (based on Diar 360 services pages)
// Source reference: https://diar360.com/service and https://diar360.com/service/civil-and-concrete-work/
$services = [
    'civil-and-concrete-work' => [
        'title' => 'CIVIL AND CONCRETE WORK',
        'title_ar' => 'أعمال مدنية وإسمنتية',
        'subtitle' => 'CIVIL AND CONCRETE WORK',
        'subtitle_ar' => 'أعمال مدنية وإسمنتية',
        'icon' => 'bi-building',
        'intro' => [
            'We specialize in robust civil and concrete construction solutions for infrastructure and building projects.',
            'Our team of skilled engineers and project managers ensures precision and quality while adhering to safety regulations.',
            'We collaborate closely with clients to understand their project goals, timelines, and budget constraints, providing tailored solutions and transparent communication throughout the project lifecycle.',
            'At DYAR 360 we deliver superior civil and concrete construction solutions through innovation, expertise, and a commitment to excellence, contributing to the development of sustainable and resilient communities.',
        ],
        'intro_ar' => [
            'نحن متخصصون في تقديم حلول إنشائية مدنية وإسمنتية قوية للبنية التحتية ومشاريع البناء.',
            'يتأكد فريقنا من المهندسين والقادة المشروعين المهرة من الدقة والجودة مع الالتزام بقواعد السلامة.',
            'نعمل عن كثب مع العملاء لفهم أهداف المشروع وأجندها ومحددات الميزانية، وتقديم حلول مخصصة وتواصل شفاف على مدار دورة حياة المشروع.',
            'في ديار 360 نقدم حلولاً إنشائية مدنية وإسمنتية متفوقة من خلال الابتكار والخبرة والالتزام بالتميز، مما يساهم في تطوير مجتمعات مستدامة ومرنة.',
        ],
    ],
    'mep' => [
        'title' => 'MEP (Mechanical, Electrical, and Plumbing)',
        'subtitle' => 'MEP (Mechanical, Electrical, and Plumbing)',
        'icon' => 'bi-gear',
        'intro' => [
            'Integrated MEP solutions for mechanical, electrical, and plumbing systems supporting high-end construction projects.',
            'Our teams ensure efficient installation, performance, and compliance with safety and quality standards.',
        ],
    ],
    'fit-out-and-finishing' => [
        'title' => 'FIT-OUT & FINISHING WORKS',
        'subtitle' => 'FIT-OUT & FINISHING WORKS',
        'icon' => 'bi-paint-bucket',
        'intro' => [
            'Professional fit-out and finishing works delivering high-quality interior execution aligned with client requirements.',
            'We focus on detail, craftsmanship, and timely delivery for commercial and residential spaces.',
        ],
    ],
    'landscaping' => [
        'title' => 'LANDSCAPING',
        'subtitle' => 'LANDSCAPING',
        'icon' => 'bi-tree',
        'intro' => [
            'Landscaping services designed to enhance outdoor spaces with functional and aesthetic solutions.',
            'From planning to execution, we deliver durable, well-finished external works.',
        ],
    ],
    'facility-management' => [
        'title' => 'FACILITY MANAGEMENT',
        'subtitle' => 'FACILITY MANAGEMENT',
        'icon' => 'bi-shield-check',
        'intro' => [
            'Facility management services supporting operational continuity and quality maintenance.',
            'We deliver planned and responsive services to maintain assets and improve performance.',
        ],
    ],
    'risk-management' => [
        'title' => 'RISK MANAGEMENT (In Facility Management)',
        'subtitle' => 'RISK MANAGEMENT',
        'icon' => 'bi-exclamation-triangle',
        'intro' => [
            'Risk management services focused on identifying, assessing, and mitigating project and operational risks.',
            'We prioritize safety, compliance, and continuity across our facility management operations.',
        ],
    ],
];

if (!array_key_exists($serviceSlug, $services)) {
    $serviceSlug = 'civil-and-concrete-work';
}

$service = $services[$serviceSlug];
?>

  <main class="main">

    <!-- Page Title -->
    <div class="page-title light-background">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0"><?php echo e(getServiceField($service, 'title')); ?></h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php"><?php echo t('breadcrumb_home'); ?></a></li>
            <li><a href="services.php"><?php echo t('nav_services'); ?></a></li>
            <li class="current"><?php echo e(getServiceField($service, 'title')); ?></li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Service Details Section -->
    <section id="service-details" class="service-details section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row">
          <div class="col-lg-4 order-lg-2">
            <div class="service-sidebar" data-aos="fade-left" data-aos-delay="200">

              <div class="service-overview-card">
                <div class="service-icon">
                  <i class="bi <?php echo e($service['icon']); ?>"></i>
                </div>
                <h3><?php echo e(getServiceField($service, 'title')); ?></h3>
                <p><?php echo t('company_description'); ?></p>
                <div class="service-stats">
                  <div class="stat-item">
                    <span class="stat-number"><?php echo convertNumbers('45'); ?></span>
                    <span class="stat-label"><?php echo t('about_engineers_label'); ?></span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-number"><?php echo convertNumbers(COMPANY_EXPERIENCE_YEARS); ?></span>
                    <span class="stat-label"><?php echo t('hero_years_experience'); ?></span>
                  </div>
                </div>
              </div>

              <div class="quick-info-card">
                <h4><?php echo t('services_title'); ?></h4>
                <div class="info-grid">
                  <div class="info-row">
                    <a class="label" href="service-details.php?service=civil-and-concrete-work"><?php echo t('services_civil_concrete'); ?></a>
                    <span class="value"><?php echo $serviceSlug === 'civil-and-concrete-work' ? '<i class="bi bi-check-circle-fill text-success"></i>' : ''; ?></span>
                  </div>
                  <div class="info-row">
                    <a class="label" href="service-details.php?service=mep"><?php echo t('services_mep'); ?></a>
                    <span class="value"><?php echo $serviceSlug === 'mep' ? '<i class="bi bi-check-circle-fill text-success"></i>' : ''; ?></span>
                  </div>
                  <div class="info-row">
                    <a class="label" href="service-details.php?service=fit-out-and-finishing"><?php echo t('services_fitout'); ?></a>
                    <span class="value"><?php echo $serviceSlug === 'fit-out-and-finishing' ? '<i class="bi bi-check-circle-fill text-success"></i>' : ''; ?></span>
                  </div>
                  <div class="info-row">
                    <a class="label" href="service-details.php?service=landscaping"><?php echo t('services_landscaping'); ?></a>
                    <span class="value"><?php echo $serviceSlug === 'landscaping' ? '<i class="bi bi-check-circle-fill text-success"></i>' : ''; ?></span>
                  </div>
                  <div class="info-row">
                    <a class="label" href="service-details.php?service=facility-management"><?php echo t('services_facility'); ?></a>
                    <span class="value"><?php echo $serviceSlug === 'facility-management' ? '<i class="bi bi-check-circle-fill text-success"></i>' : ''; ?></span>
                  </div>
                  <div class="info-row">
                    <a class="label" href="service-details.php?service=risk-management"><?php echo t('services_risk'); ?></a>
                    <span class="value"><?php echo $serviceSlug === 'risk-management' ? '<i class="bi bi-check-circle-fill text-success"></i>' : ''; ?></span>
                  </div>
                </div>
              </div>

              <div class="contact-action-card">
                <h4><?php echo t('services_get_in_touch'); ?></h4>
                <p class="contact-text"><?php echo e(COMPANY_DESCRIPTION); ?></p>
                <div class="contact-methods">
                  <a href="tel:<?php echo e(str_replace(' ', '', SITE_PHONE)); ?>" class="contact-btn">
                    <i class="bi bi-telephone-fill"></i>
                    <span><?php echo t('quote_call_us'); ?></span>
                  </a>
                  <a href="mailto:<?php echo e(SITE_EMAIL); ?>" class="contact-btn">
                    <i class="bi bi-envelope-fill"></i>
                    <span><?php echo t('quote_email_us'); ?></span>
                  </a>
                </div>
                <a href="quote.php" class="btn btn-primary w-100 mt-3"><?php echo t('quote_get_free_quote'); ?></a>
              </div>

            </div><!-- End Service Sidebar -->
          </div>

          <div class="col-lg-8 order-lg-1">
            <div class="service-main-content">

              <div class="hero-section" data-aos="zoom-in" data-aos-delay="150">
                <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-5.webp" alt="<?php echo e($service['title']); ?>" class="img-fluid">
                <div class="hero-overlay">
                  <div class="hero-badge">
                    <i class="bi bi-award"></i>
                    <span><?php echo t('service_details_awards'); ?></span>
                  </div>
                </div>
              </div>

              <div class="content-section" data-aos="fade-up" data-aos-delay="200">
                <h1><?php echo e(getServiceField($service, 'title')); ?></h1>
                <div class="content-intro">
                  <?php 
                  $introField = getServiceField($service, 'intro');
                  if (is_array($introField)) {
                      foreach ($introField as $p): ?>
                        <p><?php echo e($p); ?></p>
                      <?php endforeach;
                  } else {
                      // Fallback to English intro
                      foreach ($service['intro'] as $p): ?>
                        <p><?php echo e($p); ?></p>
                      <?php endforeach;
                  }
                  ?>
                </div>
              </div>

              <div class="capabilities-grid" data-aos="fade-up" data-aos-delay="250">
                <h2><?php echo t('service_details_know_how'); ?></h2>
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="capability-card">
                      <div class="capability-icon">
                        <i class="bi bi-lightbulb"></i>
                      </div>
                      <h4><?php echo t('service_details_innovative_design'); ?></h4>
                      <p><?php echo t('services_innovative_desc'); ?></p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="capability-card">
                      <div class="capability-icon">
                        <i class="bi bi-tools"></i>
                      </div>
                      <h4><?php echo t('service_details_skilled_contracting'); ?></h4>
                      <p><?php echo t('services_skilled_desc'); ?></p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="capability-card">
                      <div class="capability-icon">
                        <i class="bi bi-speedometer2"></i>
                      </div>
                      <h4><?php echo t('service_details_efficient_installation'); ?></h4>
                      <p><?php echo t('services_efficient_desc'); ?></p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="capability-card">
                      <div class="capability-icon">
                        <i class="bi bi-shield-check"></i>
                      </div>
                      <h4><?php echo t('about_customer_satisfaction'); ?></h4>
                      <p><?php echo t('about_customer_satisfaction'); ?> <?php echo t('about_customer_satisfaction'); ?></p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="methodology-section" data-aos="fade-up" data-aos-delay="300">
                <h2><?php echo t('service_details_for_all_projects'); ?></h2>
                <div class="methodology-timeline">
                  <div class="timeline-item">
                    <div class="timeline-marker">
                      <span class="phase-number">1</span>
                    </div>
                    <div class="timeline-content">
                      <h4>Innovative Design</h4>
                      <p>Our in-house technical expertise ensures perfect solutions tailored to client needs.</p>
                      <ul class="phase-features">
                        <li>In-house technical expertise</li>
                        <li>Tailored solutions</li>
                        <li>Best-practice delivery</li>
                      </ul>
                    </div>
                  </div>

                  <div class="timeline-item">
                    <div class="timeline-marker">
                      <span class="phase-number">2</span>
                    </div>
                    <div class="timeline-content">
                      <h4>Skilled contracting</h4>
                      <p>We utilize state-of-the-art equipment and technologies to meet performance standards.</p>
                      <ul class="phase-features">
                        <li>Advanced equipment</li>
                        <li>Quality control</li>
                        <li>Performance standards</li>
                      </ul>
                    </div>
                  </div>

                  <div class="timeline-item">
                    <div class="timeline-marker">
                      <span class="phase-number">3</span>
                    </div>
                    <div class="timeline-content">
                      <h4>Efficient installation</h4>
                      <p>Our teams ensure swift and precise project execution.</p>
                      <ul class="phase-features">
                        <li>Swift execution</li>
                        <li>Precise delivery</li>
                        <li>On-time completion</li>
                      </ul>
                    </div>
                  </div>

                  <div class="timeline-item">
                    <div class="timeline-marker">
                      <span class="phase-number">4</span>
                    </div>
                    <div class="timeline-content">
                      <h4>Customer satisfaction</h4>
                      <p>By consistently exceeding expectations, we foster long-lasting relationships and drive continued success.</p>
                      <ul class="phase-features">
                        <li>Exceed expectations</li>
                        <li>Long-lasting relationships</li>
                        <li>Continued success</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

            </div><!-- End Service Main Content -->
          </div>
        </div>

        <div class="portfolio-showcase mt-5" data-aos="fade-up" data-aos-delay="350">
          <div class="showcase-header text-center">
            <h2>Recent Commercial Projects</h2>
            <p>Explore our portfolio of successfully completed commercial construction projects</p>
          </div>
          <div class="row g-4 mt-3">
            <div class="col-lg-6">
              <div class="project-showcase-item">
                <div class="project-image">
                  <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-6.webp" alt="Office Building Construction" class="img-fluid">
                  <div class="project-overlay">
                    <div class="project-info">
                      <h4>Downtown Office Complex</h4>
                      <p>12-story commercial building with modern amenities</p>
                      <a href="<?php echo ASSETS_PATH; ?>/img/construction/project-6.webp" class="view-btn glightbox">
                        <i class="bi bi-eye"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="row g-4">
                <div class="col-12">
                  <div class="project-showcase-item">
                    <div class="project-image">
                      <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-7.webp" alt="Retail Space Construction" class="img-fluid">
                      <div class="project-overlay">
                        <div class="project-info">
                          <h4>Shopping Center Renovation</h4>
                          <p>Complete modernization of existing retail space</p>
                          <a href="<?php echo ASSETS_PATH; ?>/img/construction/project-7.webp" class="view-btn glightbox">
                            <i class="bi bi-eye"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="project-showcase-item">
                    <div class="project-image">
                      <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-8.webp" alt="Warehouse Construction" class="img-fluid">
                      <div class="project-overlay">
                        <div class="project-info">
                          <h4>Industrial Warehouse</h4>
                          <p>50,000 sq ft distribution facility</p>
                          <a href="<?php echo ASSETS_PATH; ?>/img/construction/project-8.webp" class="view-btn glightbox">
                            <i class="bi bi-eye"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div><!-- End Portfolio Showcase -->

      </div>

    </section><!-- /Service Details Section -->

  </main>

  <?php
// Include footer
require_once __DIR__ . '/include/footer.php';
?>