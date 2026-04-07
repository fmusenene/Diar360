<?php
/**
 * Team Page
 */

// Include header
require_once __DIR__ . '/include/header.php';

// Load language functions
require_once __DIR__ . '/functions/language.php';

// Load team data (editable from admin panel)
require_once __DIR__ . '/config/team-data.php';
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

        <!-- CEO Message Block - editable from admin -->
        <div class="ceo-block team-card featured" data-aos="fade-up" data-aos-delay="50">
          <div class="row g-0">
            <div class="col-lg-4">
              <div class="ceo-block-card team-card compact">
                <div class="ceo-block-image member-photo">
                  <img src="<?php echo ASSETS_PATH; ?>/img/<?php echo htmlspecialchars($ceo_profile['photo'] ?? 'construction/CEO.webp'); ?>" class="img-fluid" alt="<?php echo htmlspecialchars(($ceo_profile['name'] ?? 'CEO') . ' - ' . ($ceo_profile['title'] ?? '')); ?>">
                  <div class="hover-overlay">
                    <div class="overlay-content">
                      <h5><?php echo htmlspecialchars($ceo_profile['name'] ?? ''); ?></h5>
                      <span><?php echo htmlspecialchars($ceo_profile['title'] ?? t('ceo')); ?></span>
                      <div class="quick-contact">
                        <?php
                        $ceo_socials = $ceo_profile['socials'] ?? [];
                        if (!empty($ceo_socials['linkedin'])): ?><a href="<?php echo htmlspecialchars($ceo_socials['linkedin']); ?>"><i class="bi bi-linkedin"></i></a><?php endif; ?>
                        <?php if (!empty($ceo_socials['twitter'])): ?><a href="<?php echo htmlspecialchars($ceo_socials['twitter']); ?>"><i class="bi bi-twitter-x"></i></a><?php endif; ?>
                        <?php if (!empty($ceo_socials['email'])): ?><a href="<?php echo htmlspecialchars($ceo_socials['email']); ?>"><i class="bi bi-envelope"></i></a><?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-8">
              <div class="ceo-block-content">
                <h2 class="ceo-heading"><?php echo t('as_stated_by'); ?> <?php echo htmlspecialchars($ceo_profile['source_heading'] ?? ''); ?></h2>
                <div class="ceo-message">
                  <?php foreach (($ceo_profile['bio_paragraphs'] ?? []) as $para): ?>
                    <p><?php echo htmlspecialchars($para); ?></p>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </div><!-- End CEO Block -->

        <div class="row gy-4">

          <?php
          $members = is_array($team_members ?? null) ? $team_members : [];
          $visible_members = array_filter($members, function($m) {
            return !isset($m['visible']) || $m['visible'] === '1' || $m['visible'] === 1;
          });
          $featured = array_filter($visible_members, fn($m) => ($m['layout'] ?? '') === 'featured');
          $compact = array_filter($visible_members, fn($m) => ($m['layout'] ?? '') === 'compact');
          $delay = 100;
          ?>

          <?php foreach ($featured as $slug => $m): ?>
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
              <div class="team-card featured">
                <div class="team-header">
                  <div class="team-image">
                    <img src="<?php echo ASSETS_PATH; ?>/img/<?php echo htmlspecialchars($m['photo'] ?? 'construction/team-1.webp'); ?>" class="img-fluid" alt="">
                    <?php if (!empty($m['experience'])): ?>
                      <div class="experience-badge"><?php echo convertNumbers($m['experience']); ?> <?php echo t('years'); ?></div>
                    <?php endif; ?>
                  </div>
                  <div class="team-info">
                    <h4><?php echo htmlspecialchars($m['name'] ?? ''); ?></h4>
                    <span class="position"><?php echo htmlspecialchars($m['role'] ?? ''); ?></span>
                    <div class="contact-info">
                      <?php if (!empty($m['email'])): ?>
                        <a href="mailto:<?php echo htmlspecialchars($m['email']); ?>"><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($m['email']); ?></a>
                      <?php endif; ?>
                      <?php if (!empty($m['phone'])): ?>
                        <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $m['phone'])); ?>"><i class="bi bi-telephone"></i> <?php echo convertNumbers($m['phone']); ?></a>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <div class="team-details">
                  <p><?php echo htmlspecialchars($m['description'] ?? ''); ?></p>
                  <?php if (!empty($m['credentials']) && is_array($m['credentials'])): ?>
                    <div class="credentials">
                      <?php foreach ($m['credentials'] as $cred): ?>
                        <div class="cred-item">
                          <i class="bi <?php echo htmlspecialchars($cred['icon'] ?? 'bi-award'); ?>"></i>
                          <span><?php echo htmlspecialchars($cred['label'] ?? ''); ?></span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($m['socials']) && is_array($m['socials'])): ?>
                    <div class="social-links">
                      <?php if (!empty($m['socials']['linkedin'])): ?><a href="<?php echo htmlspecialchars($m['socials']['linkedin']); ?>"><i class="bi bi-linkedin"></i></a><?php endif; ?>
                      <?php if (!empty($m['socials']['twitter'])): ?><a href="<?php echo htmlspecialchars($m['socials']['twitter']); ?>"><i class="bi bi-twitter-x"></i></a><?php endif; ?>
                      <?php if (!empty($m['socials']['facebook'])): ?><a href="<?php echo htmlspecialchars($m['socials']['facebook']); ?>"><i class="bi bi-facebook"></i></a><?php endif; ?>
                      <?php if (!empty($m['socials']['instagram'])): ?><a href="<?php echo htmlspecialchars($m['socials']['instagram']); ?>"><i class="bi bi-instagram"></i></a><?php endif; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php $delay += 100; ?>
          <?php endforeach; ?>

          <?php foreach ($compact as $slug => $m): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
              <div class="team-card compact">
                <div class="member-photo">
                  <img src="<?php echo ASSETS_PATH; ?>/img/<?php echo htmlspecialchars($m['photo'] ?? 'construction/team-3.webp'); ?>" class="img-fluid" alt="">
                  <div class="hover-overlay">
                    <div class="overlay-content">
                      <h5><?php echo htmlspecialchars($m['name'] ?? ''); ?></h5>
                      <span><?php echo htmlspecialchars($m['role'] ?? ''); ?></span>
                      <div class="quick-contact">
                        <?php
                        $qc = $m['quick_contact'] ?? [];
                        if (!empty($qc['email'])): ?><a href="<?php echo htmlspecialchars($qc['email']); ?>"><i class="bi bi-envelope"></i></a><?php endif; ?>
                        <?php if (!empty($qc['phone'])): ?><a href="<?php echo htmlspecialchars($qc['phone']); ?>"><i class="bi bi-telephone"></i></a><?php endif; ?>
                        <?php if (!empty($qc['linkedin'])): ?><a href="<?php echo htmlspecialchars($qc['linkedin']); ?>"><i class="bi bi-linkedin"></i></a><?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="member-summary">
                  <h5><?php echo htmlspecialchars($m['name'] ?? ''); ?></h5>
                  <span><?php echo htmlspecialchars($m['role'] ?? ''); ?></span>
                  <?php if (!empty($m['skills']) && is_array($m['skills'])): ?>
                    <div class="skills">
                      <?php foreach ($m['skills'] as $skill): ?>
                        <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php $delay += 100; ?>
          <?php endforeach; ?>

        </div>

      </div>

    </section><!-- /Team Section -->

  </main>

  <?php
// Include footer
require_once __DIR__ . '/include/footer.php';
?>