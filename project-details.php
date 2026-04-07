<?php
/**
 * Project-details Page
 */

// Include header
require_once __DIR__ . '/include/header.php';

// Load language functions
require_once __DIR__ . '/functions/language.php';

// Load projects data (this will set $project and $project_slug)
require_once __DIR__ . '/config/projects-data.php';

// Get project from URL parameter
$project_slug = isset($_GET['project']) ? $_GET['project'] : '';
$project = isset($projects[$project_slug]) ? $projects[$project_slug] : null;

// Helper function to get translated project field
function getProjectField($project, $field, $slug) {
    $translationKey = 'project_' . $slug . '_' . $field;
    $translated = t($translationKey);
    
    // If translation exists and is different from key, use it
    if ($translated !== $translationKey) {
        return $translated;
    }
    
    // Otherwise return original value
    return isset($project[$field]) ? $project[$field] : '';
}

// Helper function to get translated spec value
function getProjectSpecValue($specName, $specValue, $projectSlug) {
    // Clean the spec value for translation key
    $cleanValue = strtolower(trim($specValue));
    $cleanValue = str_replace(['.', ',', ':', ';', '(', ')', '[', ']', '/', '\\'], '', $cleanValue);
    $cleanValue = preg_replace('/\s+/', '_', $cleanValue);
    
    // Try to get translation for the spec value with project slug
    $translationKey = 'project_' . $projectSlug . '_spec_' . strtolower(str_replace(' ', '_', $specName)) . '_' . $cleanValue;
    $translated = t($translationKey);
    
    // If translation exists and is different from key, use it
    if ($translated !== $translationKey) {
        return $translated;
    }
    
    // Try simpler key with project slug
    $simpleKey = 'project_' . $projectSlug . '_spec_' . $cleanValue;
    $translated = t($simpleKey);
    if ($translated !== $simpleKey) {
        return $translated;
    }
    
    // Try common translations
    $commonKey = strtolower(str_replace([' ', '-', '–'], '_', $specValue));
    $translated = t($commonKey);
    if ($translated !== $commonKey) {
        // Replace the translated part in the original value
        $result = $specValue;
        if (stripos($specValue, 'Completed') !== false) {
            $result = str_ireplace('Completed', t('completed'), $result);
        }
        if (stripos($specValue, 'Million SAR') !== false) {
            $result = str_ireplace('Million SAR', t('million_sar'), $result);
        }
        if (stripos($specValue, 'MM SAR') !== false) {
            $result = str_ireplace('MM SAR', t('mm_sar'), $result);
        }
        if (stripos($specValue, 'Chilled Water System') !== false) {
            $result = str_ireplace('Chilled Water System', t('chilled_water_system'), $result);
        }
        if (stripos($specValue, 'Duct Work') !== false) {
            $result = str_ireplace('Duct Work', t('duct_work'), $result);
        }
        if (stripos($specValue, 'Electrical Work') !== false) {
            $result = str_ireplace('Electrical Work', t('electrical_work'), $result);
        }
        if (stripos($specValue, 'Civil – Mechanical- Electrical') !== false || stripos($specValue, 'Civil - Mechanical- Electrical') !== false) {
            $result = str_ireplace('Civil – Mechanical- Electrical', t('civil_mechanical_electrical'), $result);
            $result = str_ireplace('Civil - Mechanical- Electrical', t('civil_mechanical_electrical'), $result);
        }
        return convertNumbers($result);
    }
    
    // Otherwise return original value with number conversion
    $result = $specValue;
    
    // Handle dates - convert to Arabic format if needed
    if (preg_match('/(\d+)\s+(January|February|March|April|May|June|July|August|September|October|November|December)\s+(\d+)/i', $result, $matches)) {
        $day = $matches[1];
        $month = $matches[2];
        $year = $matches[3];
        $monthTranslated = t(strtolower($month));
        if ($monthTranslated !== strtolower($month)) {
            $result = convertNumbers($day) . ' ' . $monthTranslated . ' ' . convertNumbers($year);
        } else {
            $result = convertNumbers($day) . ' ' . $month . ' ' . convertNumbers($year);
        }
    }
    
    // Replace common English terms
    $replacements = [
        'Completed' => t('completed'),
        'Million SAR' => t('million_sar'),
        'MM SAR' => t('mm_sar'),
        'Chilled Water System' => t('chilled_water_system'),
        'Duct Work' => t('duct_work'),
        'Electrical Work' => t('electrical_work'),
        'Civil – Mechanical- Electrical' => t('civil_mechanical_electrical'),
        'Civil - Mechanical- Electrical' => t('civil_mechanical_electrical'),
        'RCC & MEP' => t('rcc_mep'),
        'With' => t('with'),
        'Towers' => t('towers'),
        'Tower' => t('tower'),
        'Floors' => t('floors'),
        'Floor' => t('floor'),
        'Basements' => t('basements'),
        'Basement' => t('basement'),
        'Podium' => t('podium'),
        'Foundation' => t('foundation'),
        'Total Area' => t('total_area'),
        'Project Area' => t('project_area'),
        'Partner' => t('partner'),
        'ON GOING' => t('on_going'),
        'Finished' => t('finished'),
        'Handed Over' => t('handed_over'),
        'Earthwork' => t('earthwork'),
        'Mechanical Work' => t('mechanical_work'),
        'Finishing Work' => t('finishing_work'),
        'CIVIL, FINISHES & MEP WORKS' => t('civil_finishes_mep_works'),
        'Earthwork, Civil & MEP Work' => t('earthwork_civil_mep_work'),
        'Earthwork, Civil, MEP, and Finishing Work' => t('earthwork_civil_mep_finishing_work'),
        'Earthwork & Mechanical Work' => t('earthwork_mechanical_work'),
        'MEP WORKS' => t('mep_works'),
        'MEP & Finishing Work' => t('mep_finishing_work'),
        'Civil & MEP Work' => t('civil_mep_work'),
        'Civil & MEP' => t('civil_mep'),
        'MEP' => t('mep'),
        'Structural, Mechanical and Finishing' => t('structural_mechanical_finishing'),
        'parking floors' => t('parking_floors'),
        'Dome' => t('dome'),
        'sq/m' => t('sq_m'),
        'm²' => t('m2'),
        'm³' => t('m3'),
        'January' => t('january'),
        'Handing Over Date' => t('project_spec_handing_over_date'),
        'Budget' => t('budget'),
        'Duration' => t('duration'),
        'Client' => t('client'),
        'Consultant' => t('consultant'),
        'Structural Work' => t('structural_work'),
        'Plot Area' => t('plot_area'),
        'months' => t('months'),
        'month' => t('month'),
        'TAROUK CONTRACTING COMPANY' => t('tarouk_contracting_company'),
        'SAUDI BUILD COMPANY' => t('saudi_build_company'),
        'FIRST COMPANY' => t('first_company'),
        'SPA - Tarouk as main contractor' => t('spa') . ' - ' . t('tarouk_contracting_company') . ' ' . t('main_contractor'),
        'Al Zaid' => t('al_zaid'),
        'King Abdullah Financial District' => t('king_abdullah_financial_district'),
        'King Fahd Road' => t('king_fahd_road'),
        'Olaya Road' => t('olaya_road'),
        'Corniche Road, Jeddah' => 'كورنيش جدة',
        'Riyadh (KAFD)' => 'الرياض (KAFD)',
        'Riyadh - Exit 7' => 'الرياض - المخرج 7',
        'Riyadh Exit #2' => 'الرياض المخرج #2',
        'Al-Fatiha, Jazan' => 'الفاتحة، جازان',
        'Shaqra' => 'شقراء',
        'Yammam' => 'يمام',
        'Salboukh' => 'سلبوخ',
        'Jeddah' => 'جدة',
        'Makkah' => 'مكة',
        'Riyadh' => 'الرياض',
        'Jeddah' => 'جدة',
        'Jazan' => 'جازان',
    ];
    
    // Apply replacements
    foreach ($replacements as $english => $translated) {
        // Use case-insensitive replacement but preserve original case structure
        $result = preg_replace('/\b' . preg_quote($english, '/') . '\b/i', $translated, $result);
    }
    
    return convertNumbers($result);
}
?>

  <main class="main">

    <!-- Page Title -->
    <div class="page-title light-background">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0"><?php echo e(getProjectField($project, 'title', $project_slug)); ?></h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php"><?php echo t('breadcrumb_home'); ?></a></li>
            <li><a href="projects.php"><?php echo t('nav_projects'); ?></a></li>
            <li class="current"><?php echo e(getProjectField($project, 'title', $project_slug)); ?></li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Project Details Section -->
    <section id="project-details" class="project-details section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="project-header" data-aos="zoom-in" data-aos-delay="200">
          <div class="row align-items-center">
            <div class="col-lg-6">
              <div class="project-banner">
                <img src="<?php echo ASSETS_PATH; ?>/img/projects/<?php echo $project_slug; ?>.webp" alt="<?php echo e(getProjectField($project, 'title', $project_slug)); ?>" class="img-fluid" onerror="this.src='<?php echo ASSETS_PATH; ?>/img/construction/project-4.webp'">
                <div class="banner-badge">
                  <span class="status-indicator <?php echo $project ? getStatusClass($project['status']) : ''; ?>"><?php echo $project ? getStatusLabel($project['status']) : ''; ?></span>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="project-summary">
                <div class="project-tags">
                  <span class="tag"><?php echo $project ? (t('project_category_' . strtolower($project['category'])) !== 'project_category_' . strtolower($project['category']) ? t('project_category_' . strtolower($project['category'])) : e($project['category'])) : ''; ?></span>
                  <span class="tag"><?php echo $project ? e($project['location']) : ''; ?></span>
                </div>
                <h1 class="main-title"><?php echo e(getProjectField($project, 'title', $project_slug)); ?></h1>
                <p class="summary-text"><?php echo e(getProjectField($project, 'description', $project_slug)); ?></p>

                <div class="key-metrics">
                  <div class="metric-row">
                    <div class="metric">
                      <span class="metric-title"><?php echo t('project_details_location'); ?></span>
                      <span class="metric-data"><?php echo $project ? e($project['location']) : ''; ?></span>
                      <span class="metric-data"><?php echo $project ? e($project['location']) : ''; ?></span>
                    </div>
                    <?php if (!empty($project['contract_value']) && $project['contract_value'] !== 'N/A'): ?>
                    <div class="metric">
                      <span class="metric-title"><?php echo t('project_details_contract_value'); ?></span>
                      <span class="metric-data"><?php echo convertNumbers($project['contract_value']); ?></span>
                    </div>
                    <?php endif; ?>
                  </div>
                  <div class="metric-row">
                    <div class="metric">
                      <span class="metric-title"><?php echo t('project_details_scope'); ?></span>
                      <span class="metric-data"><?php echo e(getProjectField($project, 'scope', $project_slug)); ?></span>
                    </div>
                    <div class="metric">
                      <span class="metric-title"><?php echo t('project_details_status'); ?></span>
                      <span class="metric-data"><?php echo $project ? getStatusLabel($project['status']) : ''; ?></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="visual-showcase" data-aos="fade-up" data-aos-delay="300">
          <div class="showcase-grid">
            <div class="showcase-item large">
              <img src="<?php echo ASSETS_PATH; ?>/img/projects/<?php echo $project_slug; ?>-construction.webp" alt="<?php echo t('building_progress'); ?>" class="img-fluid" loading="lazy" onerror="this.src='<?php echo ASSETS_PATH; ?>/img/construction/project-10.webp'">
              <div class="item-overlay">
                <span class="overlay-label"><?php echo t('construction_phase'); ?></span>
              </div>
            </div>
            <div class="showcase-item">
              <img src="<?php echo ASSETS_PATH; ?>/img/projects/<?php echo $project_slug; ?>-foundation.webp" alt="<?php echo t('foundation_work'); ?>" class="img-fluid" loading="lazy" onerror="this.src='<?php echo ASSETS_PATH; ?>/img/construction/project-2.webp'">
              <div class="item-overlay">
                <span class="overlay-label"><?php echo t('foundation'); ?></span>
              </div>
            </div>
            <div class="showcase-item">
              <img src="<?php echo ASSETS_PATH; ?>/img/projects/<?php echo $project_slug; ?>-interior.webp" alt="<?php echo t('interior_planning'); ?>" class="img-fluid" loading="lazy" onerror="this.src='<?php echo ASSETS_PATH; ?>/img/construction/project-6.webp'">
              <div class="item-overlay">
                <span class="overlay-label"><?php echo t('interior_design'); ?></span>
              </div>
            </div>
            <div class="showcase-item tall">
              <img src="<?php echo ASSETS_PATH; ?>/img/projects/<?php echo $project_slug; ?>-architecture.webp" alt="<?php echo t('architectural_detail'); ?>" class="img-fluid" loading="lazy" onerror="this.src='<?php echo ASSETS_PATH; ?>/img/construction/project-1.webp'">
              <div class="item-overlay">
                <span class="overlay-label"><?php echo t('architecture'); ?></span>
              </div>
            </div>
          </div>
        </div>

        <div class="detailed-breakdown" data-aos="fade-up" data-aos-delay="400">
          <div class="row">
            <div class="col-lg-7">
              <div class="breakdown-content">
                <h3><?php echo t('project_details_overview'); ?></h3>
                <p><?php echo e(getProjectField($project, 'description', $project_slug)); ?></p>
                <p><?php echo t('project_details_commitment_text'); ?></p>

                <h3><?php echo t('project_details_know_how'); ?></h3>
                <div class="achievement-list">
                  <div class="achievement-point">
                    <div class="achievement-marker">
                      <i class="bi bi-lightbulb"></i>
                    </div>
                    <div class="achievement-details">
                      <h5><?php echo t('services_innovative_design'); ?></h5>
                      <p><?php echo t('services_innovative_desc'); ?></p>
                    </div>
                  </div>
                  <div class="achievement-point">
                    <div class="achievement-marker">
                      <i class="bi bi-speedometer2"></i>
                    </div>
                    <div class="achievement-details">
                      <h5><?php echo t('services_efficient'); ?></h5>
                      <p><?php echo t('services_efficient_desc'); ?></p>
                    </div>
                  </div>
                  <div class="achievement-point">
                    <div class="achievement-marker">
                      <i class="bi bi-tools"></i>
                    </div>
                    <div class="achievement-details">
                      <h5><?php echo t('services_skilled'); ?></h5>
                      <p><?php echo t('services_skilled_desc'); ?></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="specifications-panel">
                <h4><?php echo t('project_details_specifications'); ?></h4>
                <div class="spec-table">
                  <?php if ($project && isset($project['specs'])): ?>
                  <?php foreach ($project['specs'] as $spec_name => $spec_value): ?>
                  <div class="spec-row">
                    <span class="spec-name"><?php echo t('project_spec_' . strtolower(str_replace(' ', '_', $spec_name))) !== 'project_spec_' . strtolower(str_replace(' ', '_', $spec_name)) ? t('project_spec_' . strtolower(str_replace(' ', '_', $spec_name))) : e($spec_name); ?></span>
                    <span class="spec-detail"><?php echo getProjectSpecValue($spec_name, $spec_value, $project_slug); ?></span>
                  </div>
                  <?php endforeach; ?>
                  <?php endif; ?>
                </div>

                <?php if ($project && ($project['status'] === 'in-progress' || strpos(strtolower($project['status']), 'going') !== false)): ?>
                <div class="progress-indicator">
                  <div class="progress-header">
                    <span class="progress-label"><?php echo t('project_details_progress'); ?></span>
                    <span class="progress-percentage"><?php echo t('projects_in_progress'); ?></span>
                  </div>
                  <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?php echo convertNumbers('75'); ?>%"></div>
                  </div>
                </div>
                <?php else: ?>
                <div class="progress-indicator">
                  <div class="progress-header">
                    <span class="progress-label"><?php echo t('project_details_status'); ?></span>
                    <span class="progress-percentage"><?php echo $project ? getStatusLabel($project['status']) : ''; ?></span>
                  </div>
                  <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?php echo convertNumbers('100'); ?>%"></div>
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <div class="technical-gallery" data-aos="fade-up" data-aos-delay="500">
          <div class="gallery-header">
            <h3><?php echo t('project_details_technical_doc'); ?></h3>
            <p><?php echo t('project_details_technical_desc'); ?></p>
          </div>
          <?php
            $blueprint_image_web = ASSETS_PATH . '/img/projects/' . $project_slug . '-blueprint.webp';
            $quality_image_web = ASSETS_PATH . '/img/projects/' . $project_slug . '-quality-control.webp';
            $system_image_web = ASSETS_PATH . '/img/projects/' . $project_slug . '-system-installation.webp';

            $blueprint_image_file = __DIR__ . '/assets/img/projects/' . $project_slug . '-blueprint.webp';
            $quality_image_file = __DIR__ . '/assets/img/projects/' . $project_slug . '-quality-control.webp';
            $system_image_file = __DIR__ . '/assets/img/projects/' . $project_slug . '-system-installation.webp';

            $blueprint_image_src = file_exists($blueprint_image_file) ? $blueprint_image_web : ASSETS_PATH . '/img/construction/project-12.webp';
            $quality_image_src = file_exists($quality_image_file) ? $quality_image_web : ASSETS_PATH . '/img/construction/project-3.webp';
            $system_image_src = file_exists($system_image_file) ? $system_image_web : ASSETS_PATH . '/img/construction/project-7.webp';
          ?>
          <div class="row g-3">
            <div class="col-md-4">
              <div class="tech-item">
                <img src="<?php echo $blueprint_image_src; ?>" alt="<?php echo t('blueprint_review'); ?>" class="img-fluid" loading="lazy">
                <div class="tech-caption"><?php echo t('blueprint_analysis'); ?></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="tech-item">
                <img src="<?php echo $quality_image_src; ?>" alt="<?php echo t('quality_control'); ?>" class="img-fluid" loading="lazy">
                <div class="tech-caption"><?php echo t('quality_inspection'); ?></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="tech-item">
                <img src="<?php echo $system_image_src; ?>" alt="<?php echo t('final_installation'); ?>" class="img-fluid" loading="lazy">
                <div class="tech-caption"><?php echo t('system_installation'); ?></div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Project Details Section -->

  </main>

  <?php
// Include footer
require_once __DIR__ . '/include/footer.php';
?>