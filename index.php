<?php
/**
 * Index Page
 * Homepage of the website
 */

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// HTTPS enforcement
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    if ($_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// Check for maintenance mode
$settings_file = __DIR__ . '/config/admin-settings.php';
$maintenance_mode = false;

if (file_exists($settings_file)) {
    include $settings_file;
    if (isset($site_settings['maintenance_mode']) && $site_settings['maintenance_mode'] === '1') {
        // Allow admin access during maintenance
        $is_admin = isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true;
        if (!$is_admin) {
            // Redirect to maintenance page
            header('Location: maintenance.php');
            exit;
        }
        $maintenance_mode = true;
    }
}

// Include header
require_once __DIR__ . '/include/header.php';

// Load language functions
require_once __DIR__ . '/functions/language.php';

// Load projects data (managed by admin)
require_once __DIR__ . '/config/projects-data.php';
require_once __DIR__ . '/config/project-status.php';
require_once __DIR__ . '/config/team-data.php';
require_once __DIR__ . '/config/testimonials-data.php';

// Helpers for homepage projects (translated + visibility aware)
function homeProjectField($projectSlug, $field, $fallback = '') {
    $translationKey = 'project_' . $projectSlug . '_' . $field;
    $translated = t($translationKey);
    if ($translated !== $translationKey) {
        return convertNumbers($translated);
    }
    return $fallback;
}

function homeProjectStatusLabel($status) {
    $map = [
        PROJECT_STATUS_COMPLETED => 'projects_completed',
        PROJECT_STATUS_IN_PROGRESS => 'projects_in_progress',
        PROJECT_STATUS_PLANNING => 'projects_planning',
        PROJECT_STATUS_ON_HOLD => 'projects_on_hold',
    ];
    $key = $map[$status] ?? null;
    return $key ? t($key) : getStatusLabel($status);
}
?>

<?php if ($maintenance_mode): ?>
<!-- Maintenance Mode Notification -->
<div class="maintenance-notification fixed top-0 left-0 right-0 bg-orange-600 text-white text-center py-3 z-50">
    <div class="container">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Maintenance Mode Active:</strong> The website is currently under maintenance. 
        <span class="hidden sm:inline">Regular users see a maintenance page, but you can access the site as an administrator.</span>
        <a href="admin/projects-new.php?page=settings" class="ml-2 underline hover:no-underline">Disable in Settings</a>
    </div>
</div>
<?php endif; ?>

  <main class="main <?php echo $maintenance_mode ? 'mt-12' : ''; ?>">

    <!-- Hero Section -->
    <section id="hero" class="hero section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="hero-content" data-aos="fade-right" data-aos-delay="200">
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

          <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
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

    <?php
    $home_visible_projects = array_filter($projects ?? [], function($p) {
        return isset($p['visible']) && ($p['visible'] === '1' || $p['visible'] === 1);
    });
    ?>

    <?php if (!empty($home_visible_projects)): ?>
      <!-- Projects Section -->
      <section id="projects" class="projects section">

        <!-- Section Title -->
        <div class="container section-title">
          <h2><?php echo t('projects_title'); ?></h2>
          <p><?php echo t('projects_subtitle'); ?></p>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">
          <div class="projects-grid">
            <?php
            // Keep a consistent order (existing array order), show up to 6
            $shown = 0;
            foreach ($projects as $slug => $p) {
                if (!isset($p['visible']) || !($p['visible'] === '1' || $p['visible'] === 1)) continue;

                $status = $p['status'] ?? PROJECT_STATUS_COMPLETED;
                $statusClass = getStatusClass($status);
                $statusLabel = homeProjectStatusLabel($status);

                $category = $p['category'] ?? '';
                $catKey = 'project_category_' . strtolower(preg_replace('/[^a-z0-9]+/i', '_', $category));
                $categoryLabel = ($category !== '' && t($catKey) !== $catKey) ? t($catKey) : ($category !== '' ? $category : t('other'));

                $title = homeProjectField($slug, 'title', $p['title'] ?? $slug);
                $desc = homeProjectField($slug, 'description', $p['description'] ?? '');
                $location = $p['location'] ?? '';

                $imageRel = 'img/projects/' . $slug . '.webp';
                $imageAbs = __DIR__ . '/assets/img/projects/' . $slug . '.webp';
                $imageUrl = file_exists($imageAbs) ? asset($imageRel) : asset('img/construction/project-6.webp');
            ?>
              <div class="project-item" data-aos="zoom-in" data-aos-delay="<?php echo 100 + (($shown % 3) * 100); ?>">
                <div class="project-content">
                  <div class="project-header">
                    <span class="project-category"><?php echo e($categoryLabel); ?></span>
                    <span class="project-status <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span>
                  </div>
                  <h3 class="project-title"><?php echo e($title); ?></h3>
                  <div class="project-details">
                    <div class="project-info">
                      <?php if ($desc !== ''): ?>
                        <p><?php echo e($desc); ?></p>
                      <?php endif; ?>
                      <div class="project-specs">
                        <?php if (!empty($p['scope'])): ?>
                          <span class="spec-item">
                            <i class="bi bi-tools"></i>
                            <?php echo e($p['scope']); ?>
                          </span>
                        <?php endif; ?>
                        <?php if (!empty($p['contract_value'])): ?>
                          <span class="spec-item">
                            <i class="bi bi-currency-dollar"></i>
                            <?php echo e(convertNumbers($p['contract_value'])); ?>
                          </span>
                        <?php endif; ?>
                      </div>
                    </div>
                    <?php if ($location !== ''): ?>
                      <div class="project-location">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span><?php echo e($location); ?></span>
                      </div>
                    <?php endif; ?>
                  </div>
                  <a href="project-details.php?project=<?php echo urlencode($slug); ?>" class="project-link">
                    <span><?php echo t('projects_view_project'); ?></span>
                    <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
                <div class="project-visual">
                  <img src="<?php echo e($imageUrl); ?>" alt="<?php echo e($title); ?>" class="img-fluid">
                  <div class="project-badge">
                    <i class="bi bi-award"></i>
                  </div>
                </div>
              </div><!-- End Project Item -->
            <?php
                $shown++;
                if ($shown >= 6) break;
            }
            ?>
          </div>
        </div>
      </section><!-- /Projects Section -->
    <?php endif; ?>

    <?php
    $t_items = is_array($testimonials ?? null) ? $testimonials : [];
    $currentLang = isset($currentLang) ? $currentLang : (function_exists('getCurrentLanguage') ? getCurrentLanguage() : 'en');
    $t_visible = array_filter($t_items, function($t) {
      return ($t['status'] ?? 'pending') === 'approved' && (isset($t['visible']) && ($t['visible'] === '1' || $t['visible'] === 1));
    });
    // newest first
    uasort($t_visible, function($a, $b) {
      return strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? ''));
    });
    // Keep the slider tidy
    $t_visible = array_slice($t_visible, 0, 12, true);
    ?>

    <?php if (!empty($t_visible)): ?>
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
            <?php $delay = 200; ?>
            <?php foreach ($t_visible as $id => $t): ?>
              <?php
              $msg = ($currentLang === 'ar') ? trim((string)($t['message_ar'] ?? '')) : trim((string)($t['message_en'] ?? ''));
              if ($msg === '') $msg = trim((string)($t['message_en'] ?? '')) ?: trim((string)($t['message_ar'] ?? ''));

              $role = ($currentLang === 'ar') ? trim((string)($t['role_ar'] ?? '')) : trim((string)($t['role_en'] ?? ''));
              if ($role === '') $role = trim((string)($t['role_en'] ?? '')) ?: trim((string)($t['role_ar'] ?? ''));

              $company = ($currentLang === 'ar') ? trim((string)($t['company_ar'] ?? '')) : trim((string)($t['company_en'] ?? ''));
              if ($company === '') $company = trim((string)($t['company_en'] ?? '')) ?: trim((string)($t['company_ar'] ?? ''));

              $name = trim((string)($t['name'] ?? ''));
              $rating = (int)($t['rating'] ?? 5);
              $rating = max(1, min(5, $rating));
              $avatar = trim((string)($t['avatar'] ?? ''));
              $email = strtolower(trim((string)($t['email'] ?? '')));
              $avatarUrl = trim((string)($t['avatar_url'] ?? ''));

              $avatarSrc = '';
              if ($avatarUrl !== '') {
                $avatarSrc = $avatarUrl;
              } elseif ($avatar !== '') {
                $avatarSrc = (ASSETS_PATH . '/img/' . ltrim($avatar, '/'));
              } elseif ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $hash = md5($email);
                // Use d=404 so we can fall back to icon when no Gravatar exists.
                $avatarSrc = 'https://www.gravatar.com/avatar/' . $hash . '?s=96&d=404';
              }
              ?>
              <div class="swiper-slide">
                <div class="testimonial-slide" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                  <div class="testimonial-header">
                    <div class="stars-rating">
                      <?php for ($i = 0; $i < $rating; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                      <?php for ($i = $rating; $i < 5; $i++): ?><i class="bi bi-star"></i><?php endfor; ?>
                    </div>
                    <div class="quote-icon">
                      <i class="bi bi-quote"></i>
                    </div>
                  </div>
                  <div class="testimonial-body">
                    <p>"<?php echo e($msg); ?>"</p>
                  </div>
                  <div class="testimonial-footer">
                    <div class="author-info">
                      <div class="author-avatar d-flex align-items-center justify-content-center overflow-hidden" style="background:#EFF1F3; color:#14529D;">
                        <i class="bi bi-person-fill"></i>
                        <?php if ($avatarSrc !== ''): ?>
                          <img
                            src="<?php echo e($avatarSrc); ?>"
                            alt="<?php echo t('testimonial_author'); ?>"
                            style="width:100%;height:100%;object-fit:cover;"
                            onerror="this.onerror=null;this.style.display='none';"
                          >
                        <?php endif; ?>
                      </div>
                      <div class="author-details">
                        <h4><?php echo e($name); ?></h4>
                        <?php if ($role !== ''): ?><span class="role"><?php echo e($role); ?></span><?php endif; ?>
                        <?php if ($company !== ''): ?><span class="company"><?php echo e($company); ?></span><?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <?php $delay += 100; if ($delay > 400) $delay = 200; ?>
            <?php endforeach; ?>
          </div>

          <div class="swiper-navigation-wrapper">
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
          </div>

        </div>

      </div>

      <!-- Public submission form (user submitted -> pending moderation) -->
      <div class="container mt-5" data-aos="fade-up" data-aos-delay="150">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="contact-form-panel">
              <div class="form-container">
                <h3><?php echo ($currentLang === 'ar') ? 'أضف تقييمك' : 'Submit Your Review'; ?></h3>
                <p><?php echo ($currentLang === 'ar') ? 'سيتم نشر تقييمك بعد المراجعة والموافقة من الإدارة.' : 'Your review will appear after admin approval.'; ?></p>

                <form action="forms/submit-testimonial.php" method="post" class="php-email-form">
                  <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
                  <input type="hidden" name="google_name" id="tGoogleName" value="">
                  <input type="hidden" name="google_email" id="tGoogleEmail" value="">
                  <input type="hidden" name="google_picture" id="tGooglePicture" value="">

                  <div class="row g-3">
                    <?php if (defined('GOOGLE_SIGNIN_CLIENT_ID') && GOOGLE_SIGNIN_CLIENT_ID !== ''): ?>
                      <div class="col-12">
                        <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                          <div id="g_id_onload"
                               data-client_id="<?php echo e(GOOGLE_SIGNIN_CLIENT_ID); ?>"
                               data-callback="onTestimonialGoogleCredential"
                               data-auto_prompt="false">
                          </div>
                          <div class="g_id_signin"
                               data-type="standard"
                               data-size="large"
                               data-theme="outline"
                               data-text="continue_with"
                               data-shape="pill"
                               data-logo_alignment="left">
                          </div>
                          <small class="text-muted">
                            <?php echo ($currentLang === 'ar') ? 'سيساعد تسجيل الدخول عبر Google على عرض صورة ملفك الشخصي.' : 'Google sign-in helps show your profile photo.'; ?>
                          </small>
                        </div>
                      </div>
                    <?php endif; ?>
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" class="form-control" id="tName" name="name" placeholder="Name" required>
                        <label for="tName"><?php echo ($currentLang === 'ar') ? 'الاسم' : 'Name'; ?></label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" class="form-control" id="tRole" name="role" placeholder="Role">
                        <label for="tRole"><?php echo ($currentLang === 'ar') ? 'المسمى الوظيفي (اختياري)' : 'Role (optional)'; ?></label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="email" class="form-control" id="tEmail" name="email" placeholder="Email">
                        <label for="tEmail"><?php echo ($currentLang === 'ar') ? 'البريد الإلكتروني (اختياري)' : 'Email (optional)'; ?></label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input type="text" class="form-control" id="tCompany" name="company" placeholder="Company">
                        <label for="tCompany"><?php echo ($currentLang === 'ar') ? 'الشركة (اختياري)' : 'Company (optional)'; ?></label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-floating">
                        <div class="testimonial-rating-control" aria-label="<?php echo ($currentLang === 'ar') ? 'اختر التقييم' : 'Choose rating'; ?>">
                          <input type="radio" name="rating" id="tRating5" value="5" checked>
                          <label for="tRating5" title="5"><i class="bi bi-star-fill"></i></label>

                          <input type="radio" name="rating" id="tRating4" value="4">
                          <label for="tRating4" title="4"><i class="bi bi-star-fill"></i></label>

                          <input type="radio" name="rating" id="tRating3" value="3">
                          <label for="tRating3" title="3"><i class="bi bi-star-fill"></i></label>

                          <input type="radio" name="rating" id="tRating2" value="2">
                          <label for="tRating2" title="2"><i class="bi bi-star-fill"></i></label>

                          <input type="radio" name="rating" id="tRating1" value="1">
                          <label for="tRating1" title="1"><i class="bi bi-star-fill"></i></label>
                        </div>
                        <label><?php echo ($currentLang === 'ar') ? 'التقييم' : 'Rating'; ?></label>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="form-floating">
                        <textarea class="form-control" id="tMessage" name="message" placeholder="Message" style="height: 140px" required></textarea>
                        <label for="tMessage"><?php echo ($currentLang === 'ar') ? 'رسالتك' : 'Your Review'; ?></label>
                      </div>
                    </div>
                  </div>

                  <div class="my-3">
                    <div class="loading"><?php echo t('contact_loading'); ?></div>
                    <div class="error-message"></div>
                    <div class="sent-message"><?php echo ($currentLang === 'ar') ? 'تم إرسال تقييمك بنجاح. شكرًا لك!' : 'Your review has been submitted. Thank you!'; ?></div>
                  </div>

                  <div class="d-grid">
                    <button type="submit" class="btn-submit">
                      <?php echo ($currentLang === 'ar') ? 'إرسال التقييم' : 'Send Message'; ?>
                      <i class="bi bi-send-fill ms-2"></i>
                    </button>
                  </div>
                </form>
                <?php if (defined('GOOGLE_SIGNIN_CLIENT_ID') && GOOGLE_SIGNIN_CLIENT_ID !== ''): ?>
                  <script src="https://accounts.google.com/gsi/client" async defer></script>
                  <script>
                    function onTestimonialGoogleCredential(response) {
                      try {
                        if (!response || !response.credential) return;
                        const parts = response.credential.split('.');
                        if (parts.length < 2) return;
                        const payload = JSON.parse(atob(parts[1].replace(/-/g, '+').replace(/_/g, '/')));
                        if (!payload) return;

                        const name = payload.name || '';
                        const email = payload.email || '';
                        const picture = payload.picture || '';

                        if (name) document.getElementById('tName').value = name;
                        if (email) document.getElementById('tEmail').value = email;

                        document.getElementById('tGoogleName').value = name;
                        document.getElementById('tGoogleEmail').value = email;
                        document.getElementById('tGooglePicture').value = picture;
                      } catch (e) {}
                    }
                  </script>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

    </section><!-- /Testimonials Section -->
    <?php endif; ?>

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
            <?php
            $gp_title = ($currentLang ?? 'en') === 'ar'
              ? (trim($site_settings['global_partners_title_ar'] ?? '') ?: t('certifications_global_partners'))
              : (trim($site_settings['global_partners_title_en'] ?? '') ?: t('certifications_global_partners'));

            $gp_desc = ($currentLang ?? 'en') === 'ar'
              ? (trim($site_settings['global_partners_desc_ar'] ?? '') ?: t('certifications_global_desc'))
              : (trim($site_settings['global_partners_desc_en'] ?? '') ?: t('certifications_global_desc'));
            ?>
            <h2><?php echo e($gp_title); ?></h2>
            <p><?php echo e($gp_desc); ?></p>

            <?php
            $gp_items = is_array($site_settings['global_partners_items'] ?? null) ? $site_settings['global_partners_items'] : [];
            $gp_visible = array_filter($gp_items, function($p) {
                return isset($p['visible']) && ($p['visible'] === '1' || $p['visible'] === 1);
            });
            $gp_name_key = ($currentLang ?? 'en') === 'ar' ? 'name_ar' : 'name_en';
            ?>

            <?php if (!empty($gp_visible)): ?>
              <div class="d-flex flex-wrap gap-3 mt-4 align-items-center">
                <?php foreach ($gp_visible as $pid => $p): ?>
                  <?php
                  $name = trim((string)($p[$gp_name_key] ?? ''));
                  if ($name === '') {
                      $name = trim((string)($p['name_en'] ?? '')) ?: trim((string)($p['name_ar'] ?? '')) ?: $pid;
                  }
                  $url = trim((string)($p['url'] ?? ''));
                  $logo = trim((string)($p['logo'] ?? ''));
                  $logoSrc = $logo !== '' ? (ASSETS_PATH . '/img/' . ltrim($logo, '/')) : '';
                  ?>

                  <?php if ($url !== ''): ?><a href="<?php echo e($url); ?>" target="_blank" rel="noopener noreferrer" class="d-inline-flex align-items-center text-decoration-none"><?php endif; ?>
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3" style="border:1px solid rgba(0,0,0,0.08); background: rgba(255,255,255,0.6);">
                      <?php if ($logoSrc !== ''): ?>
                        <img src="<?php echo e($logoSrc); ?>" alt="<?php echo e($name); ?>" style="height:22px; width:auto; max-width:120px;" loading="lazy">
                      <?php else: ?>
                        <i class="bi bi-building" style="color: rgba(20, 82, 157, 0.9);"></i>
                      <?php endif; ?>
                      <span style="font-weight:600; color: rgba(0,0,0,0.72);"><?php echo e($name); ?></span>
                    </div>
                  <?php if ($url !== ''): ?></a><?php endif; ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
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

        <?php
        $certCards = is_array($site_settings['certification_cards'] ?? null) ? $site_settings['certification_cards'] : [];
        $certVisible = array_filter($certCards, function($c) {
            return isset($c['visible']) && ($c['visible'] === '1' || $c['visible'] === 1);
        });
        uasort($certVisible, function($a, $b) {
            $oa = (int)($a['order'] ?? 999);
            $ob = (int)($b['order'] ?? 999);
            if ($oa === $ob) return 0;
            return $oa <=> $ob;
        });
        ?>

        <?php if (!empty($certVisible)): ?>
          <div class="certification-grid" data-aos="fade-up" data-aos-delay="400">
            <?php
            $delay = 100;
            foreach ($certVisible as $cid => $c):
              $title = ($currentLang ?? 'en') === 'ar' ? trim((string)($c['title_ar'] ?? '')) : trim((string)($c['title_en'] ?? ''));
              if ($title === '') $title = trim((string)($c['title_en'] ?? '')) ?: trim((string)($c['title_ar'] ?? '')) ?: $cid;

              $cat = ($currentLang ?? 'en') === 'ar' ? trim((string)($c['category_ar'] ?? '')) : trim((string)($c['category_en'] ?? ''));
              if ($cat === '') $cat = trim((string)($c['category_en'] ?? '')) ?: trim((string)($c['category_ar'] ?? ''));

              $desc = ($currentLang ?? 'en') === 'ar' ? trim((string)($c['desc_ar'] ?? '')) : trim((string)($c['desc_en'] ?? ''));
              if ($desc === '') $desc = trim((string)($c['desc_en'] ?? '')) ?: trim((string)($c['desc_ar'] ?? ''));

              $icon = trim((string)($c['icon'] ?? ''));
              $iconSrc = $icon !== '' ? ASSETS_PATH . '/img/' . ltrim($icon, '/') : asset('img/construction/badge-1.webp');
            ?>
              <div class="cert-card" data-aos="flip-left" data-aos-delay="<?php echo $delay; ?>">
                <div class="cert-icon">
                  <img src="<?php echo e($iconSrc); ?>" alt="<?php echo e($title); ?>" class="img-fluid">
                </div>
                <div class="cert-details">
                  <h5><?php echo e($title); ?></h5>
                  <?php if ($cat !== ''): ?>
                    <span class="cert-category"><?php echo e($cat); ?></span>
                  <?php endif; ?>
                  <?php if ($desc !== ''): ?>
                    <p><?php echo e($desc); ?></p>
                  <?php endif; ?>
                </div>
              </div>
            <?php
              $delay += 100;
              if ($delay > 600) $delay = 100;
            endforeach;
            ?>
          </div>
        <?php endif; ?>

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
    <?php
    $home_members = is_array($team_members ?? null) ? $team_members : [];
    $home_visible_members = array_filter($home_members, function($m) {
      return !isset($m['visible']) || $m['visible'] === '1' || $m['visible'] === 1;
    });
    ?>

    <?php if (!empty($home_visible_members)): ?>
      <section id="team" class="team section">

        <!-- Section Title -->
        <div class="container section-title">
          <h2><?php echo t('team_expert_team'); ?></h2>
          <p><?php echo t('team_engineers_desc'); ?></p>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">
          <div class="row gy-4">
            <?php
            $featured = array_filter($home_visible_members, fn($m) => ($m['layout'] ?? '') === 'featured');
            $compact = array_filter($home_visible_members, fn($m) => ($m['layout'] ?? '') !== 'featured');
            $ordered = array_merge($featured, $compact);

            $shown = 0;
            $delay = 100;
            foreach ($ordered as $m) {
              if ($shown >= 8) break;

              $layout = (($m['layout'] ?? '') === 'featured') ? 'featured' : 'compact';
              $photo = $m['photo'] ?? 'construction/team-1.webp';
              $name = $m['name'] ?? '';
              $role = $m['role'] ?? '';
              $experience = $m['experience'] ?? '';
              $email = $m['email'] ?? '';
              $phone = $m['phone'] ?? '';
              $desc = $m['description'] ?? '';
              $socials = is_array($m['socials'] ?? null) ? $m['socials'] : [];
              $qc = is_array($m['quick_contact'] ?? null) ? $m['quick_contact'] : [];
              $skills = is_array($m['skills'] ?? null) ? $m['skills'] : [];
              $creds = is_array($m['credentials'] ?? null) ? $m['credentials'] : [];
            ?>

              <?php if ($layout === 'featured'): ?>
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                  <div class="team-card featured">
                    <div class="team-header">
                      <div class="team-image">
                        <img src="<?php echo ASSETS_PATH; ?>/img/<?php echo htmlspecialchars($photo); ?>" class="img-fluid" alt="">
                        <?php if (!empty($experience)): ?>
                          <div class="experience-badge"><?php echo convertNumbers($experience); ?> <?php echo t('years'); ?></div>
                        <?php endif; ?>
                      </div>
                      <div class="team-info">
                        <h4><?php echo htmlspecialchars($name); ?></h4>
                        <span class="position"><?php echo htmlspecialchars($role); ?></span>
                        <div class="contact-info">
                          <?php if (!empty($email)): ?>
                            <a href="mailto:<?php echo htmlspecialchars($email); ?>"><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($email); ?></a>
                          <?php endif; ?>
                          <?php if (!empty($phone)): ?>
                            <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $phone)); ?>"><i class="bi bi-telephone"></i> <?php echo convertNumbers($phone); ?></a>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                    <div class="team-details">
                      <?php if (!empty($desc)): ?>
                        <p><?php echo htmlspecialchars($desc); ?></p>
                      <?php endif; ?>
                      <?php if (!empty($creds)): ?>
                        <div class="credentials">
                          <?php foreach ($creds as $cred): ?>
                            <div class="cred-item">
                              <i class="bi <?php echo htmlspecialchars($cred['icon'] ?? 'bi-award'); ?>"></i>
                              <span><?php echo htmlspecialchars($cred['label'] ?? ''); ?></span>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                      <div class="social-links">
                        <?php if (!empty($socials['linkedin'])): ?><a href="<?php echo htmlspecialchars($socials['linkedin']); ?>"><i class="bi bi-linkedin"></i></a><?php endif; ?>
                        <?php if (!empty($socials['twitter'])): ?><a href="<?php echo htmlspecialchars($socials['twitter']); ?>"><i class="bi bi-twitter-x"></i></a><?php endif; ?>
                        <?php if (!empty($socials['instagram'])): ?><a href="<?php echo htmlspecialchars($socials['instagram']); ?>"><i class="bi bi-instagram"></i></a><?php endif; ?>
                        <?php if (!empty($socials['facebook'])): ?><a href="<?php echo htmlspecialchars($socials['facebook']); ?>"><i class="bi bi-facebook"></i></a><?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              <?php else: ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                  <div class="team-card compact">
                    <div class="member-photo">
                      <img src="<?php echo ASSETS_PATH; ?>/img/<?php echo htmlspecialchars($photo); ?>" class="img-fluid" alt="">
                      <div class="hover-overlay">
                        <div class="overlay-content">
                          <h5><?php echo htmlspecialchars($name); ?></h5>
                          <span><?php echo htmlspecialchars($role); ?></span>
                          <div class="quick-contact">
                            <?php if (!empty($qc['email'])): ?><a href="<?php echo htmlspecialchars($qc['email']); ?>"><i class="bi bi-envelope"></i></a><?php endif; ?>
                            <?php if (!empty($qc['phone'])): ?><a href="<?php echo htmlspecialchars($qc['phone']); ?>"><i class="bi bi-telephone"></i></a><?php endif; ?>
                            <?php if (!empty($qc['linkedin'])): ?><a href="<?php echo htmlspecialchars($qc['linkedin']); ?>"><i class="bi bi-linkedin"></i></a><?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="member-summary">
                      <h5><?php echo htmlspecialchars($name); ?></h5>
                      <span><?php echo htmlspecialchars($role); ?></span>
                      <?php if (!empty($skills)): ?>
                        <div class="skills">
                          <?php foreach (array_slice($skills, 0, 3) as $skill): ?>
                            <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endif; ?>

            <?php
              $shown++;
              $delay += 100;
              if ($delay > 400) $delay = 100;
            }
            ?>
          </div>
        </div>
      </section><!-- /Team Section -->
    <?php endif; ?>

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