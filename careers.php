<?php
/**
 * Careers Page
 */

// Check for maintenance mode
$settings_file = __DIR__ . '/config/admin-settings.php';
$maintenance_mode = false;

if (file_exists($settings_file)) {
    include $settings_file;
    if (isset($site_settings['maintenance_mode']) && $site_settings['maintenance_mode'] === '1') {
        $is_admin = isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true;
        if (!$is_admin) {
            header('Location: maintenance.php');
            exit;
        }
        $maintenance_mode = true;
    }
}

require_once __DIR__ . '/include/header.php';
require_once __DIR__ . '/functions/language.php';
require_once __DIR__ . '/config/careers-data.php';

if (!isset($job_posts) || !is_array($job_posts)) {
    $job_posts = [];
}

// Localize job fields (EN/AR) with fallback to legacy keys
$lang = getCurrentLanguage();
$jobField = function(array $job, string $key) use ($lang) {
    $suffix = ($lang === 'ar') ? '_ar' : '_en';
    $preferred = $key . $suffix;
    if (array_key_exists($preferred, $job) && is_string($job[$preferred]) && trim($job[$preferred]) !== '') {
        return $job[$preferred];
    }
    if (array_key_exists($preferred, $job) && is_array($job[$preferred]) && !empty($job[$preferred])) {
        return $job[$preferred];
    }
    // fallback to legacy single-language key
    return $job[$key] ?? (is_array($job[$preferred] ?? null) ? [] : '');
};

// Only show visible jobs on public site
$visible_jobs = array_filter($job_posts, function($j) {
    return ($j['visible'] ?? '0') === '1' || ($j['visible'] ?? 0) === 1;
});

// Sort by posted_at desc, then title
uasort($visible_jobs, function($a, $b) {
    $da = strtotime($a['posted_at'] ?? '1970-01-01') ?: 0;
    $db = strtotime($b['posted_at'] ?? '1970-01-01') ?: 0;
    if ($da === $db) {
        return strcmp((string)($a['title'] ?? ''), (string)($b['title'] ?? ''));
    }
    return $db <=> $da;
});

$job_slug = isset($_GET['job']) ? preg_replace('/[^a-z0-9-]/', '', strtolower((string)$_GET['job'])) : '';
$selected_job = ($job_slug !== '' && isset($visible_jobs[$job_slug])) ? $visible_jobs[$job_slug] : null;

$apply_email = $site_settings['admin_email'] ?? (defined('SITE_EMAIL') ? SITE_EMAIL : 'info@diar360.com');
$job_title = $selected_job ? ($jobField($selected_job, 'title') ?: 'Job') : 'Job';
// Create proper slug from job title
$job_title_slug = preg_replace('/[^a-zA-Z0-9]/', '-', $job_title);
$job_title_slug = preg_replace('/-+/', '-', $job_title_slug);
$job_title_slug = trim($job_title_slug, '-');
$apply_subject = $selected_job ? ('Application: ' . $job_title . ' (' . $job_title_slug . ')') : 'Job Application';
$mailto = 'mailto:' . rawurlencode($apply_email) . '?subject=' . rawurlencode($apply_subject);
?>

<main class="main <?php echo $maintenance_mode ? 'mt-12' : ''; ?>">
  <style>
    .job-card {
      border: 1px solid rgba(0, 0, 0, 0.08);
      border-radius: 16px;
      background: #fff;
      overflow: hidden;
      transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .job-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 18px 42px rgba(0, 0, 0, 0.10);
      border-color: rgba(20, 82, 157, 0.25);
    }

    .job-card .job-meta {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px 14px;
      margin-top: 12px;
    }

    .job-card .job-meta-item {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
      padding: 10px 12px;
      border: 1px solid rgba(0, 0, 0, 0.06);
      border-radius: 12px;
      background: rgba(0, 0, 0, 0.02);
    }

    .job-card .job-meta-item i {
      width: 18px;
      height: 18px;
      font-size: 18px;
      color: rgba(20, 82, 157, 0.95);
      flex: 0 0 auto;
    }

    .job-card .job-meta-item .label {
      font-size: 12px;
      color: rgba(0, 0, 0, 0.55);
      line-height: 1.2;
      margin-bottom: 2px;
    }

    .job-card .job-meta-item .value {
      font-size: 13px;
      font-weight: 600;
      color: rgba(0, 0, 0, 0.78);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .job-card .job-summary {
      color: rgba(0, 0, 0, 0.62);
      margin-top: 10px;
    }

    .job-card .job-actions {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-top: 16px;
      padding-top: 14px;
      border-top: 1px dashed rgba(0, 0, 0, 0.12);
    }

    .job-card .job-actions .posted {
      font-size: 12px;
      color: rgba(0, 0, 0, 0.55);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      min-width: 0;
    }

    .job-card .job-actions .posted i {
      color: rgba(0, 0, 0, 0.45);
      font-size: 14px;
    }

    .job-detail-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 10px 12px;
      margin: 14px 0 18px;
    }

    .job-detail-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      border-radius: 999px;
      border: 1px solid rgba(0, 0, 0, 0.08);
      background: rgba(0, 0, 0, 0.02);
      font-size: 13px;
      color: rgba(0, 0, 0, 0.70);
      line-height: 1;
    }

    .job-detail-pill i {
      color: rgba(20, 82, 157, 0.95);
      font-size: 14px;
    }

    .job-detail-card {
      border: 1px solid rgba(0, 0, 0, 0.08);
      border-radius: 18px;
      background: #fff;
      overflow: hidden;
      box-shadow: 0 14px 34px rgba(0, 0, 0, 0.06);
    }

    .job-detail-hero {
      padding: 22px 22px 16px;
      background:
        radial-gradient(900px 420px at 10% 0%, rgba(20, 82, 157, 0.14) 0%, rgba(20, 82, 157, 0.00) 55%),
        linear-gradient(180deg, rgba(0, 0, 0, 0.02), rgba(0, 0, 0, 0.00));
      border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }

    .job-detail-title-row {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 14px;
    }

    .job-detail-title {
      font-size: 28px;
      line-height: 1.15;
      margin: 0;
      letter-spacing: -0.2px;
    }

    .job-detail-title-badge {
      flex: 0 0 auto;
      width: 42px;
      height: 42px;
      border-radius: 14px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border: 1px solid rgba(20, 82, 157, 0.18);
      background: rgba(20, 82, 157, 0.08);
      color: rgba(20, 82, 157, 0.95);
    }

    .job-detail-body {
      padding: 18px 22px 22px;
    }

    .job-section-title {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 16px;
      font-weight: 700;
      margin: 18px 0 10px;
    }

    .job-section-title i {
      color: rgba(20, 82, 157, 0.95);
      font-size: 16px;
    }

    .job-list {
      list-style: none;
      padding-left: 0;
      margin: 0;
      display: grid;
      gap: 10px;
    }

    .job-list li {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 10px 12px;
      border: 1px solid rgba(0, 0, 0, 0.06);
      border-radius: 12px;
      background: rgba(0, 0, 0, 0.02);
      color: rgba(0, 0, 0, 0.70);
    }

    .job-list li i {
      margin-top: 2px;
      color: rgba(20, 82, 157, 0.95);
      font-size: 14px;
      flex: 0 0 auto;
    }

    .sidebar-card {
      border: 1px solid rgba(0, 0, 0, 0.08);
      border-radius: 18px;
      background: #fff;
      overflow: hidden;
      box-shadow: 0 14px 34px rgba(0, 0, 0, 0.06);
    }

    .sidebar-card .sidebar-card-header {
      padding: 14px 16px;
      border-bottom: 1px solid rgba(0, 0, 0, 0.06);
      background: rgba(0, 0, 0, 0.015);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
    }

    .sidebar-card .sidebar-card-header .title {
      font-weight: 800;
      margin: 0;
      font-size: 14px;
      letter-spacing: 0.2px;
      text-transform: uppercase;
      color: rgba(0, 0, 0, 0.65);
    }

    .sidebar-card .sidebar-card-body {
      padding: 16px;
    }

    .apply-cta {
      display: grid;
      gap: 10px;
    }

    .apply-cta .small-muted {
      color: rgba(0, 0, 0, 0.58);
      font-size: 13px;
    }

    .other-jobs {
      display: grid;
      gap: 10px;
    }

    .other-job-item {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      padding: 12px 12px;
      border-radius: 14px;
      border: 1px solid rgba(0, 0, 0, 0.06);
      background: rgba(0, 0, 0, 0.02);
      text-decoration: none;
      color: inherit;
      transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease;
    }

    .other-job-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
      border-color: rgba(20, 82, 157, 0.22);
      background: rgba(20, 82, 157, 0.04);
    }

    .other-job-item.active {
      border-color: rgba(20, 82, 157, 0.35);
      background: rgba(20, 82, 157, 0.07);
    }

    .other-job-item .left {
      min-width: 0;
    }

    .other-job-item .job-name {
      font-weight: 700;
      margin: 0 0 4px;
      color: rgba(0, 0, 0, 0.80);
      line-height: 1.25;
    }

    .other-job-item .job-sub {
      font-size: 12px;
      color: rgba(0, 0, 0, 0.55);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .other-job-item .job-sub i {
      color: rgba(20, 82, 157, 0.95);
    }

    .other-job-item .arrow {
      flex: 0 0 auto;
      width: 34px;
      height: 34px;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border: 1px solid rgba(0, 0, 0, 0.08);
      background: rgba(255, 255, 255, 0.7);
      color: rgba(0, 0, 0, 0.55);
    }

    @media (min-width: 992px) {
      .sticky-sidebar {
        position: sticky;
        top: 110px;
      }
    }

    @media (max-width: 576px) {
      .job-card .job-meta {
        grid-template-columns: 1fr;
      }
    }

    /* Custom button colors to match #13529D */
    .btn-primary {
      background-color: #13529D !important;
      border-color: #13529D !important;
    }

    .btn-primary:hover,
    .btn-primary:focus,
    .btn-primary:active {
      background-color: #0f4688 !important;
      border-color: #0f4688 !important;
      box-shadow: 0 4px 12px rgba(19, 82, 157, 0.3) !important;
    }

    .btn-outline-primary {
      color: #13529D !important;
      border-color: #13529D !important;
    }

    .btn-outline-primary:hover,
    .btn-outline-primary:focus,
    .btn-outline-primary:active {
      background-color: #13529D !important;
      border-color: #13529D !important;
      color: #ffffff !important;
    }

    .btn-outline-secondary:hover,
    .btn-outline-secondary:focus,
    .btn-outline-secondary:active {
      background-color: #13529D !important;
      border-color: #13529D !important;
      color: #ffffff !important;
    }

    /* Custom badge colors for email */
    .badge.text-bg-primary {
      background-color: #13529D !important;
      color: #ffffff !important;
    }
  </style>

  <div class="page-title light-background" style="background:#EFF1F3 !important;" data-aos="fade">
    <div class="container d-lg-flex justify-content-between align-items-center">
      <h1 class="mb-2 mb-lg-0"><?php echo t('careers_title'); ?></h1>
      <nav class="breadcrumbs">
        <ol>
          <li><a href="index.php"><?php echo t('breadcrumb_home'); ?></a></li>
          <li class="current"><?php echo t('careers_title'); ?></li>
        </ol>
      </nav>
    </div>
  </div>

  <?php if (!empty($visible_jobs)): ?>
    <section class="section pt-0 mt-4">
      <div class="container" data-aos="fade-up" data-aos-delay="80">
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="job-detail-card" style="box-shadow:none;">
              <div class="job-detail-body" style="padding-top:22px;">
                <h2 class="mb-2" style="font-size:22px; letter-spacing:-0.2px;"><?php echo t('careers_subtitle'); ?></h2>
                <p class="mb-2" style="color: rgba(0,0,0,0.68); line-height:1.75;">
                  <?php echo t('careers_intro_desc'); ?>
                </p>
                <p class="mb-0" style="color: rgba(0,0,0,0.58);">
                  <i class="bi bi-lightning-charge me-1"></i><?php echo t('careers_intro_note'); ?>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="section">
    <div class="container" data-aos="fade-up" data-aos-delay="100">
      <?php if ($selected_job): ?>
        <div class="row g-4">
          <div class="col-lg-8">
            <div class="job-detail-card">
              <div class="job-detail-hero">
                <div class="job-detail-title-row">
                  <div class="min-w-0">
                <h2 class="job-detail-title"><?php echo e($jobField($selected_job, 'title') ?? ''); ?></h2>
                    <div class="job-detail-meta">
                <?php $dep = $jobField($selected_job, 'department'); ?>
                <?php if (!empty($dep)): ?>
                        <span class="job-detail-pill"><i class="bi bi-building"></i><?php echo e(t('careers_department')); ?>: <?php echo e($dep); ?></span>
                      <?php endif; ?>
                      <?php $loc = $jobField($selected_job, 'location'); ?>
                      <?php if (!empty($loc)): ?>
                        <span class="job-detail-pill"><i class="bi bi-geo-alt"></i><?php echo e(t('careers_location')); ?>: <?php echo e($loc); ?></span>
                      <?php endif; ?>
                      <?php $typ = $jobField($selected_job, 'type'); ?>
                      <?php if (!empty($typ)): ?>
                        <span class="job-detail-pill"><i class="bi bi-briefcase"></i><?php echo e(t('careers_type')); ?>: <?php echo e($typ); ?></span>
                      <?php endif; ?>
                      <?php if (!empty($selected_job['posted_at'])): ?>
                        <span class="job-detail-pill"><i class="bi bi-calendar-event"></i><?php echo e(t('careers_posted')); ?>: <?php echo e($selected_job['posted_at']); ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="job-detail-title-badge" aria-hidden="true">
                    <i class="bi bi-briefcase" style="font-size: 18px;"></i>
                  </div>
                </div>
              </div>

              <div class="job-detail-body">
                <?php $desc = $jobField($selected_job, 'description'); ?>
                <?php if (!empty($desc)): ?>
                  <div class="mb-2" style="color: rgba(0,0,0,0.68); font-size: 15px; line-height: 1.75;">
                    <?php echo nl2br(e($desc)); ?>
                  </div>
                <?php endif; ?>

                <?php $resp = $jobField($selected_job, 'responsibilities'); ?>
                <?php if (!empty($resp) && is_array($resp)): ?>
                  <div class="job-section-title"><i class="bi bi-list-check"></i><?php echo t('careers_responsibilities'); ?></div>
                  <ul class="job-list">
                    <?php foreach ($resp as $item): ?>
                      <li><i class="bi bi-check2-circle"></i><span><?php echo e($item); ?></span></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>

                <?php $req = $jobField($selected_job, 'requirements'); ?>
                <?php if (!empty($req) && is_array($req)): ?>
                  <div class="job-section-title"><i class="bi bi-patch-check"></i><?php echo t('careers_requirements'); ?></div>
                  <ul class="job-list">
                    <?php foreach ($req as $item): ?>
                      <li><i class="bi bi-dot"></i><span><?php echo e($item); ?></span></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-2 mt-4">
                  <a class="btn btn-primary" href="<?php echo e($mailto); ?>">
                    <i class="bi bi-send me-1"></i><?php echo t('careers_apply_now'); ?>
                  </a>
                  <a class="btn btn-outline-secondary" href="careers.php">
                    <i class="bi bi-arrow-left me-1"></i><?php echo t('careers_back_to_list'); ?>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="sticky-sidebar d-grid gap-3">
              <div class="sidebar-card">
                <div class="sidebar-card-header">
                  <p class="title mb-0">Apply</p>
                  <span class="badge text-bg-primary"><i class="bi bi-envelope-paper me-1"></i><?php echo e($apply_email); ?></span>
                </div>
                <div class="sidebar-card-body">
                  <div class="apply-cta">
                    <div class="small-muted">
                      <i class="bi bi-info-circle me-1"></i>
                      Click below to email your CV and mention the job title in the subject.
                    </div>
                    <a class="btn btn-primary w-100" href="<?php echo e($mailto); ?>">
                      <i class="bi bi-send me-1"></i><?php echo t('careers_apply_now'); ?>
                    </a>
                    <a class="btn btn-outline-secondary w-100" href="careers.php">
                      <i class="bi bi-grid me-1"></i><?php echo t('careers_open_positions'); ?>
                    </a>
                  </div>
                </div>
              </div>

              <div class="sidebar-card">
                <div class="sidebar-card-header">
                  <p class="title mb-0"><?php echo t('careers_open_positions'); ?></p>
                  <span class="badge text-bg-light"><?php echo count($visible_jobs); ?></span>
                </div>
                <div class="sidebar-card-body">
                  <div class="other-jobs">
                    <?php foreach ($visible_jobs as $slug => $j): ?>
                      <a class="other-job-item <?php echo ($slug === $job_slug) ? 'active' : ''; ?>" href="careers.php?job=<?php echo urlencode($slug); ?>">
                        <div class="left">
                          <p class="job-name"><?php echo e($jobField($j, 'title') ?? ($j['title'] ?? $slug)); ?></p>
                          <div class="job-sub">
                            <?php $jloc = $jobField($j, 'location'); ?>
                            <?php if (!empty($jloc)): ?>
                              <span><i class="bi bi-geo-alt me-1"></i><?php echo e($jloc); ?></span>
                            <?php endif; ?>
                            <?php $jtyp = $jobField($j, 'type'); ?>
                            <?php if (!empty($jtyp)): ?>
                              <span><i class="bi bi-briefcase me-1"></i><?php echo e($jtyp); ?></span>
                            <?php endif; ?>
                          </div>
                        </div>
                        <span class="arrow" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                      </a>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
              <h2 class="mb-0"><?php echo t('careers_open_positions'); ?></h2>
              <a class="btn btn-outline-primary" href="<?php echo e($mailto); ?>"><?php echo t('careers_apply_now'); ?></a>
            </div>

            <?php if (empty($visible_jobs)): ?>
              <div class="alert alert-info mb-0"><?php echo t('careers_no_openings'); ?></div>
            <?php else: ?>
              <div class="row g-4">
                <?php foreach ($visible_jobs as $slug => $j): ?>
                  <div class="col-md-6">
                    <div class="job-card h-100">
                      <div class="p-4">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                          <div class="min-w-0">
                        <h5 class="mb-1 text-truncate"><?php echo e($jobField($j, 'title') ?? ($j['title'] ?? $slug)); ?></h5>
                            <?php $sum = $jobField($j, 'summary'); ?>
                            <?php if (!empty($sum)): ?>
                              <div class="job-summary"><?php echo e($sum); ?></div>
                            <?php endif; ?>
                          </div>
                          <div class="flex-shrink-0">
                            <i class="bi bi-stars" style="font-size: 20px; color: rgba(20, 82, 157, 0.9);"></i>
                          </div>
                        </div>

                        <div class="job-meta">
                          <?php $jdep = $jobField($j, 'department'); ?>
                          <?php if (!empty($jdep)): ?>
                            <div class="job-meta-item" title="<?php echo e($j['department']); ?>">
                              <i class="bi bi-building"></i>
                              <div class="min-w-0">
                                <div class="label"><?php echo e(t('careers_department')); ?></div>
                                <div class="value"><?php echo e($jdep); ?></div>
                              </div>
                            </div>
                          <?php endif; ?>
                          <?php $jloc = $jobField($j, 'location'); ?>
                          <?php if (!empty($jloc)): ?>
                            <div class="job-meta-item" title="<?php echo e($j['location']); ?>">
                              <i class="bi bi-geo-alt"></i>
                              <div class="min-w-0">
                                <div class="label"><?php echo e(t('careers_location')); ?></div>
                                <div class="value"><?php echo e($jloc); ?></div>
                              </div>
                            </div>
                          <?php endif; ?>
                          <?php $jtyp = $jobField($j, 'type'); ?>
                          <?php if (!empty($jtyp)): ?>
                            <div class="job-meta-item" title="<?php echo e($j['type']); ?>">
                              <i class="bi bi-briefcase"></i>
                              <div class="min-w-0">
                                <div class="label"><?php echo e(t('careers_type')); ?></div>
                                <div class="value"><?php echo e($jtyp); ?></div>
                              </div>
                            </div>
                          <?php endif; ?>
                          <?php if (!empty($j['posted_at'])): ?>
                            <div class="job-meta-item" title="<?php echo e($j['posted_at']); ?>">
                              <i class="bi bi-calendar-event"></i>
                              <div class="min-w-0">
                                <div class="label"><?php echo e(t('careers_posted')); ?></div>
                                <div class="value"><?php echo e($j['posted_at']); ?></div>
                              </div>
                            </div>
                          <?php endif; ?>
                        </div>

                        <div class="job-actions">
                          <div class="posted">
                            <i class="bi bi-info-circle"></i>
                            <span class="text-truncate">
                              <?php echo e(($j['location'] ?? '') . (empty($j['type']) ? '' : ' • ' . $j['type'])); ?>
                            </span>
                          </div>
                          <div class="d-flex gap-2">
                            <a class="btn btn-primary btn-sm" href="careers.php?job=<?php echo urlencode($slug); ?>">
                              <?php echo t('careers_view_details'); ?>
                            </a>
                            <a class="btn btn-outline-secondary btn-sm" href="<?php echo e($mailto); ?>">
                              <?php echo t('careers_apply_now'); ?>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/include/footer.php'; ?>

