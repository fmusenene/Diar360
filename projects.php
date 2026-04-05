<?php
/**
 * Projects Page
 */

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

// Load projects data for translations
require_once __DIR__ . '/config/projects-data.php';

// Helper function to get translated project field
function getProjectField($projectSlug, $field) {
    $translationKey = 'project_' . $projectSlug . '_' . $field;
    $translated = t($translationKey);
    // If translation exists and is different from key, use it
    if ($translated !== $translationKey) {
        // Convert numbers in the translated text
        return convertNumbersInText($translated);
    }
    // Otherwise return empty (will use fallback)
    return '';
}

// Helper function to convert numbers in text
function convertNumbersInText($text) {
    // Pattern to match numbers (including decimals, commas, ranges, and suffixes)
    // Matches: 123, 12.5, 60,000, 13-37, 13th, 37th, etc.
    $pattern = '/(\d+(?:[.,]\d+)*(?:-\d+)?(?:th|st|nd|rd)?)/i';
    return preg_replace_callback($pattern, function($matches) {
        // Remove suffixes (th, st, nd, rd) before conversion, then add them back
        $number = preg_replace('/(th|st|nd|rd)$/i', '', $matches[0]);
        $suffix = preg_match('/(th|st|nd|rd)$/i', $matches[0], $suffixMatch) ? $suffixMatch[0] : '';
        $converted = convertNumbers($number);
        return $converted . $suffix;
    }, $text);
}

// Helper function to get translated category
function getCategoryTranslation($category) {
    $key = 'project_category_' . strtolower($category);
    $translated = t($key);
    return ($translated !== $key) ? $translated : $category;
}

// Helper function to get translated location
function getLocationTranslation($location) {
    // Try exact match first
    $exactKey = strtolower(str_replace([' ', '-', ',', '#'], '_', $location));
    $translated = t($exactKey);
    if ($translated !== $exactKey) {
        return convertNumbersInText($translated);
    }
    // Try common location patterns
    $locationMap = [
        'King Fahd Road, Riyadh' => 'king_fahd_road_riyadh',
        'Riyadh - Exit 7' => 'riyadh_exit_7',
        'Riyadh Exit #2' => 'riyadh_exit_2',
        'Olaya Road, Riyadh' => 'olaya_road_riyadh',
        'Corniche Road, Jeddah' => 'corniche_road_jeddah',
        'Al-Fatiha, Jazan' => 'al_fatiha_jazan',
    ];
    if (isset($locationMap[$location])) {
        $translated = t($locationMap[$location]);
        if ($translated !== $locationMap[$location]) {
            return convertNumbersInText($translated);
        }
    }
    // Fallback: try individual words
    $words = preg_split('/[\s,\-#]+/', $location);
    $translatedParts = [];
    foreach ($words as $word) {
        $wordKey = strtolower(trim($word));
        if (!empty($wordKey)) {
            $wordTranslated = t($wordKey);
            $translatedParts[] = ($wordTranslated !== $wordKey) ? $wordTranslated : $word;
        }
    }
    if (count($translatedParts) > 0) {
        $result = implode(' ', $translatedParts);
        return convertNumbersInText($result);
    }
    return convertNumbersInText($location);
}
?>

  <main class="main">

    <!-- Page Title -->
    <div class="page-title light-background">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0"><?php echo t('projects_title'); ?></h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php"><?php echo t('breadcrumb_home'); ?></a></li>
            <li class="current"><?php echo t('projects_title'); ?></li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Projects Section -->
    <section id="projects" class="projects section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="projects-grid">
          <!-- Makkah Projects -->
          <?php
          // Check if makkah-chilled-water is visible
          $makkahChilledWaterVisible = isset($projects['makkah-chilled-water']['visible']) ? $projects['makkah-chilled-water']['visible'] : '0';
          if ($makkahChilledWaterVisible === '1' || $makkahChilledWaterVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="50">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo t('project_category_mep') !== 'project_category_mep' ? t('project_category_mep') : 'MEP'; ?></span>
                <span class="project-status completed"><?php echo getStatusLabel('completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('makkah-chilled-water', 'title') ?: 'Makkah Project - Chilled Water'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('makkah-chilled-water', 'description') ?: 'Chilled water system installation and MEP work for Makkah project.'; ?></p>
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
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-2.webp" alt="Makkah Project Chilled Water" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if makkah-duct-work project is visible
          $makkahDuctWorkVisible = isset($projects['makkah-duct-work']['visible']) ? $projects['makkah-duct-work']['visible'] : '0';
          if ($makkahDuctWorkVisible === '1' || $makkahDuctWorkVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="75">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo t('project_category_mep') !== 'project_category_mep' ? t('project_category_mep') : 'MEP'; ?></span>
                <span class="project-status completed"><?php echo getStatusLabel('completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('makkah-duct-work', 'title') ?: 'Makkah Project - Duct Work'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('makkah-duct-work', 'description') ?: 'Duct work installation and MEP services for Makkah project.'; ?></p>
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
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-6.webp" alt="Makkah Project Duct Work" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if makkah-electrical project is visible
          $makkahElectricalVisible = isset($projects['makkah-electrical']['visible']) ? $projects['makkah-electrical']['visible'] : '0';
          if ($makkahElectricalVisible === '1' || $makkahElectricalVisible === 1) {
          ?>
          <div class="project-item" data-aos="zoom-in" data-aos-delay="100">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo t('project_category_electrical') !== 'project_category_electrical' ? t('project_category_electrical') : 'Electrical'; ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('makkah-electrical', 'title') ?: 'Makkah Project - Electrical Work'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('makkah-electrical', 'description') ?: 'Electrical work installation and services for Makkah project.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-lightning"></i>
                      <?php echo t('electrical_work'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('3.2'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo t('makkah') !== 'makkah' ? t('makkah') : 'Makkah'; ?></span>
                </div>
              </div>
              <a href="project-details.php?project=makkah-electrical" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-10.webp" alt="Makkah Project Electrical" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if rimal-project is visible
          $rimalProjectVisible = isset($projects['rimal-project']['visible']) ? $projects['rimal-project']['visible'] : '0';
          if ($rimalProjectVisible === '1' || $rimalProjectVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="125">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo t('project_category_infrastructure') !== 'project_category_infrastructure' ? t('project_category_infrastructure') : 'Infrastructure'; ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('rimal-project', 'title') ?: 'Rimal Project'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('rimal-project', 'description') ?: 'Civil, Mechanical, and Electrical work for Rimal project.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-tools"></i>
                      <?php echo t('civil_mep'); ?>
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
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-4.webp" alt="Rimal Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if exit-9-project is visible
          $exit9ProjectVisible = isset($projects['exit-9-project']['visible']) ? $projects['exit-9-project']['visible'] : '0';
          if ($exit9ProjectVisible === '1' || $exit9ProjectVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="150">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo t('project_category_infrastructure') !== 'project_category_infrastructure' ? t('project_category_infrastructure') : 'Infrastructure'; ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('exit-9-project', 'title') ?: 'Exit 9 Project'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('exit-9-project', 'description') ?: 'Civil, Mechanical, and Electrical work for Exit 9 infrastructure project.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-tools"></i>
                      <?php echo t('civil_mep'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo t('project_category_infrastructure') !== 'project_category_infrastructure' ? t('project_category_infrastructure') : 'Infrastructure'; ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo t('riyadh') !== 'riyadh' ? t('riyadh') : 'Riyadh'; ?></span>
                </div>
              </div>
              <a href="project-details.php?project=exit-9-project" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-8.webp" alt="Exit 9 Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if lamar-towers is visible
          $lamarTowersVisible = isset($projects['lamar-towers']['visible']) ? $projects['lamar-towers']['visible'] : '0';
          if ($lamarTowersVisible === '1' || $lamarTowersVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="100">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo t('project_category_towers') !== 'project_category_towers' ? t('project_category_towers') : 'Towers'; ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('lamar-towers', 'title') ?: 'Lamar Towers Project'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('lamar-towers', 'description') ?: convertNumbersInText('RCC & MEP project with TAROUK CONTRACTING COMPANY. Two towers from 13th floor till 37th floor covering 60,000m² plus podium 10,000 m².'); ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo convertNumbers('2'); ?> <?php echo t('towers'); ?> (<?php echo convertNumbers('13-37'); ?> <?php echo t('floors'); ?>)
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
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-2.webp" alt="Lamar Towers Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if elegance-tower is visible
          $eleganceTowerVisible = isset($projects['elegance-tower']['visible']) ? $projects['elegance-tower']['visible'] : '0';
          if ($eleganceTowerVisible === '1' || $eleganceTowerVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="200">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Towers'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('elegance-tower', 'title') ?: 'Elegance Tower Project'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('elegance-tower', 'description') ?: convertNumbersInText('RCC & MEP project with TAROUK CONTRACTING COMPANY. 28 floors covering 28,000 m² and 5 basements totaling 35,000 m².'); ?></p>
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
                  <span><?php echo getLocationTranslation('Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=elegance-tower" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-6.webp" alt="Elegance Tower Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if 309-310-tower-kafd is visible
          $tower309310KafdVisible = isset($projects['309-310-tower-kafd']['visible']) ? $projects['309-310-tower-kafd']['visible'] : '0';
          if ($tower309310KafdVisible === '1' || $tower309310KafdVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="300">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Towers'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('309-310-tower-kafd', 'title') ?: '309,310 Tower Project (KAFD)'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('309-310-tower-kafd', 'description') ?: convertNumbersInText('RCC & MEP project with SAUDI BUILD COMPANY. Two towers totaling 140,000 m² with podium and basements.'); ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo convertNumbers('2'); ?> <?php echo t('towers'); ?> + <?php echo t('podium'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-rulers"></i>
                      <?php echo convertNumbers('140,000'); ?> <?php echo t('m2'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=309-310-tower-kafd" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-10.webp" alt="309,310 Tower Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if riyadh-metro is visible
          $riyadhMetroVisible = isset($projects['riyadh-metro']['visible']) ? $projects['riyadh-metro']['visible'] : '0';
          if ($riyadhMetroVisible === '1' || $riyadhMetroVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="100">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Infrastructure'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('riyadh-metro', 'title') ?: 'Riyadh Metro Project'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('riyadh-metro', 'description') ?: t('civil_mep_work') . ' ' . t('for') . ' ' . t('riyadh_metro_project') . '. ' . t('handed_over_on') . ' ' . convertNumbers('10') . ' ' . t('january') . ' ' . convertNumbers('2017') . '.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-train-front"></i>
                      <?php echo t('project_category_infrastructure') !== 'project_category_infrastructure' ? t('project_category_infrastructure') : 'Metro Infrastructure'; ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('8'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=riyadh-metro" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-4.webp" alt="Riyadh Metro Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if shaqra-roman-theater is visible
          $shaqraRomanTheaterVisible = isset($projects['shaqra-roman-theater']['visible']) ? $projects['shaqra-roman-theater']['visible'] : '0';
          if ($shaqraRomanTheaterVisible === '1' || $shaqraRomanTheaterVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="200">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Commercial'); ?></span>
                <span class="project-status in-progress"><?php echo t('projects_in_progress'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('shaqra-roman-theater', 'title') ?: 'Shaqra Roman Theater'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('shaqra-roman-theater', 'description') ?: 'Civil & MEP work for the Shaqra Roman Theater project.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo t('civil_mep_work'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('8'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Shaqra'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=shaqra-roman-theater" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-8.webp" alt="Shaqra Roman Theater" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-tools"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if saudi-press-agency is visible
          $saudiPressAgencyVisible = isset($projects['saudi-press-agency']['visible']) ? $projects['saudi-press-agency']['visible'] : '0';
          if ($saudiPressAgencyVisible === '1' || $saudiPressAgencyVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="300">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Commercial'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('saudi-press-agency', 'title') ?: 'Saudi Press Agency HQ'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('saudi-press-agency', 'description') ?: convertNumbersInText('Civil & MEP project for Saudi Press Agency headquarters. 12,000 sq/m with 10 floors & dome on 4,000 sq/m plot.'); ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo convertNumbers('10'); ?> <?php echo t('floors'); ?> + <?php echo t('dome'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('12'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=saudi-press-agency" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-12.webp" alt="Saudi Press Agency HQ" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if ramla-tower is visible
          $ramlaTowerVisible = isset($projects['ramla-tower']['visible']) ? $projects['ramla-tower']['visible'] : '0';
          if ($ramlaTowerVisible === '1' || $ramlaTowerVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="100">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Towers'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('ramla-tower', 'title') ?: 'Ramla Tower Project'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('ramla-tower', 'description') ?: convertNumbersInText('RCC & MEP project with FIRST COMPANY. 40 floors and 2 basements covering 63,000 m² plus 10,000 m³ foundation.'); ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo convertNumbers('40'); ?> <?php echo t('floors'); ?> + <?php echo convertNumbers('2'); ?> <?php echo t('basements'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-rulers"></i>
                      <?php echo convertNumbers('63,000'); ?> <?php echo t('m2'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=ramla-tower" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-2.webp" alt="Ramla Tower Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if al-rimal-showrooms is visible
          $alRimalShowroomsVisible = isset($projects['al-rimal-showrooms']['visible']) ? $projects['al-rimal-showrooms']['visible'] : '0';
          if ($alRimalShowroomsVisible === '1' || $alRimalShowroomsVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="200">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Commercial'); ?></span>
                <span class="project-status in-progress"><?php echo t('projects_in_progress'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('al-rimal-showrooms', 'title') ?: 'Al Rimal Commercial Showrooms'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('al-rimal-showrooms', 'description') ?: 'Earthwork, Civil, MEP, and Finishing Work for commercial showrooms.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-shop"></i>
                      <?php echo t('project_category_commercial') !== 'project_category_commercial' ? t('project_category_commercial') : 'Commercial Showrooms'; ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('8'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=al-rimal-showrooms" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-6.webp" alt="Al Rimal Commercial Showrooms" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-tools"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>


          <?php
          // Check if water-pump-station is visible
          $waterPumpStationVisible = isset($projects['water-pump-station']['visible']) ? $projects['water-pump-station']['visible'] : '0';
          if ($waterPumpStationVisible === '1' || $waterPumpStationVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="300">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Infrastructure'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('water-pump-station', 'title') ?: 'Water Pump House Station Al-Fatiha'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('water-pump-station', 'description') ?: 'Earthwork & Mechanical Work for water pump house station.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-droplet"></i>
                      <?php echo t('project_category_infrastructure') !== 'project_category_infrastructure' ? t('project_category_infrastructure') : 'Water Infrastructure'; ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('12'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Al-Fatiha, Jazan'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=water-pump-station" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-10.webp" alt="Water Pump House Station" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="100">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Residential'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('california-compound', 'title') ?: 'California Compound'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('california-compound', 'description') ?: 'Civil & MEP work for California Compound residential project.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-house"></i>
                      <?php echo t('project_category_residential') !== 'project_category_residential' ? t('project_category_residential') : 'Residential Compound'; ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('33'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh - Exit 7'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=california-compound" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-4.webp" alt="California Compound" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if al-wassil-tower is visible
          $alWassilTowerVisible = isset($projects['al-wassil-tower']['visible']) ? $projects['al-wassil-tower']['visible'] : '0';
          if ($alWassilTowerVisible === '1' || $alWassilTowerVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="200">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Towers'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('al-wassil-tower', 'title') ?: 'Al-Wassil Tower Project'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('al-wassil-tower', 'description') ?: convertNumbersInText('RCC & MEP project with TAROUK CONTRACTING COMPANY. 20 floors covering 20,000 m² plus 5 basements totaling 20,000 m².'); ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo convertNumbers('20'); ?> <?php echo t('floors'); ?> + <?php echo convertNumbers('5'); ?> <?php echo t('basements'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-rulers"></i>
                      <?php echo convertNumbers('40,000'); ?> <?php echo t('m2'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('King Fahd Road, Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=al-wassil-tower" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-8.webp" alt="Al-Wassil Tower Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if al-swailim-tower is visible
          $alSwailimTowerVisible = isset($projects['al-swailim-tower']['visible']) ? $projects['al-swailim-tower']['visible'] : '0';
          if ($alSwailimTowerVisible === '1' || $alSwailimTowerVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="300">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Towers'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('al-swailim-tower', 'title') ?: 'Al-Swailim Tower Project'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('al-swailim-tower', 'description') ?: convertNumbersInText('RCC & MEP project with TAROUK CONTRACTING COMPANY. 30,000 m² project with 20 floors plus 2 basements and 2 parking floors.'); ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo convertNumbers('20'); ?> <?php echo t('floors'); ?> + <?php echo convertNumbers('2'); ?> <?php echo t('basements'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-rulers"></i>
                      <?php echo convertNumbers('30,000'); ?> <?php echo t('m2'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Olaya Road, Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=al-swailim-tower" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-12.webp" alt="Al-Swailim Tower Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if mr-atif-project is visible
          $mrAtifProjectVisible = isset($projects['mr-atif-project']['visible']) ? $projects['mr-atif-project']['visible'] : '0';
          if ($mrAtifProjectVisible === '1' || $mrAtifProjectVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="100">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Residential'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('mr-atif-project', 'title') ?: 'Mr. Atif Bin Ayez Bin Awaz Almezani Almutari'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('mr-atif-project', 'description') ?: 'Earthwork, Civil & MEP work for residential project.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-house"></i>
                      <?php echo t('project_category_residential') !== 'project_category_residential' ? t('project_category_residential') : 'Residential'; ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('3'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=mr-atif-project" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-2.webp" alt="Mr. Atif Bin Ayez Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if mr-saleh-project is visible
          $mrSalehProjectVisible = isset($projects['mr-saleh-project']['visible']) ? $projects['mr-saleh-project']['visible'] : '0';
          if ($mrSalehProjectVisible === '1' || $mrSalehProjectVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="125">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Residential'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('mr-saleh-project', 'title') ?: 'Mr. Saleh Bin Al-Rasheed Allya'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('mr-saleh-project', 'description') ?: 'Civil, Finishes & MEP works for residential project.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-house"></i>
                      <?php echo t('project_category_residential') !== 'project_category_residential' ? t('project_category_residential') : 'Residential'; ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('15'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=mr-saleh-project" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-6.webp" alt="Mr. Saleh Bin Al-Rasheed Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if princess-jawaher is visible
          $princessJawaherVisible = isset($projects['princess-jawaher']['visible']) ? $projects['princess-jawaher']['visible'] : '0';
          if ($princessJawaherVisible === '1' || $princessJawaherVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="150">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Residential'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('princess-jawaher', 'title') ?: 'Princess Jawaher Bint Muqrin Bin Abdulaziz Al-Saud'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('princess-jawaher', 'description') ?: 'Civil, Finishes & MEP works for royal residential project.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-house"></i>
                      <?php echo t('project_category_residential') !== 'project_category_residential' ? t('project_category_residential') : 'Residential'; ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('6'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=princess-jawaher" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-10.webp" alt="Princess Jawaher Project" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if nwc-mep is visible
          $nwcMepVisible = isset($projects['nwc-mep']['visible']) ? $projects['nwc-mep']['visible'] : '0';
          if ($nwcMepVisible === '1' || $nwcMepVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="175">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Infrastructure'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('nwc-mep', 'title') ?: 'National Water Company - MEP Works'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('nwc-mep', 'description') ?: 'MEP works for National Water Company infrastructure project.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-droplet"></i>
                      <?php echo t('mep_works'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('6'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=nwc-mep" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-4.webp" alt="National Water Company MEP" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if nwc-civil-mep is visible
          $nwcCivilMepVisible = isset($projects['nwc-civil-mep']['visible']) ? $projects['nwc-civil-mep']['visible'] : '0';
          if ($nwcCivilMepVisible === '1' || $nwcCivilMepVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="200">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Infrastructure'); ?></span>
                <span class="project-status in-progress"><?php echo t('projects_in_progress'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('nwc-civil-mep', 'title') ?: 'National Water Company - Civil & MEP'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('nwc-civil-mep', 'description') ?: 'Civil & MEP work for National Water Company ongoing project.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-tools"></i>
                      <?php echo t('civil_mep'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('15'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=nwc-civil-mep" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-8.webp" alt="National Water Company Civil" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-tools"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if riyadh-development is visible
          $riyadhDevelopmentVisible = isset($projects['riyadh-development']['visible']) ? $projects['riyadh-development']['visible'] : '0';
          if ($riyadhDevelopmentVisible === '1' || $riyadhDevelopmentVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="225">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Government'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('riyadh-development', 'title') ?: 'Arriyadh Development Authority'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('riyadh-development', 'description') ?: 'MEP & Finishing work for Arriyadh Development Authority project.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo t('mep_finishing_work'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('5.5'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=riyadh-development" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-12.webp" alt="Arriyadh Development Authority" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if yammam-cement is visible
          $yammamCementVisible = isset($projects['yammam-cement']['visible']) ? $projects['yammam-cement']['visible'] : '0';
          if ($yammamCementVisible === '1' || $yammamCementVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="250">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Industrial'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('yammam-cement', 'title') ?: 'Yammam Cement Factory Project'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('yammam-cement', 'description') ?: t('mep_work') . ' ' . t('for') . ' ' . t('yammam_cement_factory') . '. ' . t('handed_over_on') . ' ' . convertNumbers('10') . ' ' . t('january') . ' ' . convertNumbers('2017') . '.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-gear"></i>
                      <?php echo t('mep_works'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('8'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Yammam'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=yammam-cement" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-2.webp" alt="Yammam Cement Factory" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if salboukh-station is visible
          $salboukhStationVisible = isset($projects['salboukh-station']['visible']) ? $projects['salboukh-station']['visible'] : '0';
          if ($salboukhStationVisible === '1' || $salboukhStationVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="275">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Infrastructure'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('salboukh-station', 'title') ?: 'SSEM-NWC-Salboukh Station'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('salboukh-station', 'description') ?: 'Structural, Mechanical and Finishing work for Salboukh station.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-tools"></i>
                      <?php echo t('structural_mechanical_finishing'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('6.5'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Salboukh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=salboukh-station" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-10.webp" alt="SSEM-NWC-Salboukh Station" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-tools"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if california-compound is visible
          $californiaCompoundVisible = isset($projects['california-compound']['visible']) ? $projects['california-compound']['visible'] : '0';
          if ($californiaCompoundVisible === '1' || $californiaCompoundVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="300">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Residential'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('california-compound', 'title') ?: 'California Compound'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('california-compound', 'description') ?: 'Civil & MEP work for California Compound residential project.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-house"></i>
                      <?php echo t('project_category_residential') !== 'project_category_residential' ? t('project_category_residential') : 'Residential'; ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('15'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh Exit 7'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=california-compound" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-4.webp" alt="California Compound" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if al-rashed-palace is visible
          $alRashedPalaceVisible = isset($projects['al-rashed-palace']['visible']) ? $projects['al-rashed-palace']['visible'] : '0';
          if ($alRashedPalaceVisible === '1' || $alRashedPalaceVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="325">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Residential'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('al-rashed-palace', 'title') ?: 'Al Rashed Palace'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('al-rashed-palace', 'description') ?: 'MEP work for Al Rashed Palace project.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo t('mep_works'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('10'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh Exit #2'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=al-rashed-palace" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-10.webp" alt="Al Rashed Palace" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if ballan-tower is visible
          $ballanTowerVisible = isset($projects['ballan-tower']['visible']) ? $projects['ballan-tower']['visible'] : '0';
          if ($ballanTowerVisible === '1' || $ballanTowerVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="325">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Commercial'); ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('ballan-tower', 'title') ?: 'Ballan Commercial Tower'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('ballan-tower', 'description') ?: convertNumbersInText('MEP work for Ballan Commercial Tower. Structural work 8,000 sq/m.'); ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo convertNumbers('8,000'); ?> <?php echo t('sq_m'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('6'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=ballan-tower" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-4.webp" alt="Ballan Commercial Tower" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <?php
          // Check if king-fahd-stadium is visible
          $kingFahdStadiumVisible = isset($projects['king-fahd-stadium']['visible']) ? $projects['king-fahd-stadium']['visible'] : '0';
          if ($kingFahdStadiumVisible === '1' || $kingFahdStadiumVisible === 1) {
          ?>

          <div class="project-item" data-aos="zoom-in" data-aos-delay="350">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation('Infrastructure'); ?></span>
                <span class="project-status completed"><?php echo getStatusLabel('completed'); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField('king-fahd-stadium', 'title') ?: 'King Fahd International Sports City Stadium'; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField('king-fahd-stadium', 'description') ?: 'Comprehensive construction project for King Fahd International Sports City Stadium, including civil works, MEP systems, and high-quality finishing works.'; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo t('civil_mep_finishing'); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers('25'); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation('Riyadh'); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=king-fahd-stadium" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/projects/king-fahd-stadium.webp" alt="King Fahd International Sports City Stadium" class="img-fluid" onerror="this.src='<?php echo ASSETS_PATH; ?>/img/construction/project-1.webp'">
              <div class="project-badge">
                <i class="<?php echo getStatusIcon('completed'); ?>"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php } ?>

          <!-- Dynamic Projects Section -->
          <?php
          // Get all projects that are not already hardcoded above
          $hardcoded_projects = [
              'makkah-chilled-water', 'makkah-duct-work', 'makkah-electrical', 'rimal-project',
              'exit-9-project', 'lamar-towers', 'elegance-tower', 'ramla-tower',
              '309-310-tower-kafd', 'al-wassil-tower', 'al-swailim-tower', 'saudi-press-agency',
              'shaqra-roman-theater', 'al-rimal-showrooms', 'water-pump-station', 'mr-atif-project',
              'mr-saleh-project', 'princess-jawaher', 'nwc-mep', 'nwc-civil-mep',
              'riyadh-development', 'riyadh-metro', 'yammam-cement', 'salboukh-station',
              'california-compound', 'al-rashed-palace', 'ballan-tower', 'king-fahd-stadium'
          ];
          $dynamic_delay = 400;
          foreach ($projects as $slug => $project) {
              if (!in_array($slug, $hardcoded_projects)) {
                  // Only show visible projects
                  $isVisible = isset($project['visible']) ? $project['visible'] : 0;
                  // Convert to integer for comparison (handles both '1' and 1)
                  $isVisible = (int)$isVisible;
                  if ($isVisible !== 1) {
                      continue; // Skip hidden projects
                  }
          ?>
          <div class="project-item" data-aos="zoom-in" data-aos-delay="<?php echo $dynamic_delay; ?>">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation($project['category']); ?></span>
                <span class="project-status <?php echo getStatusClass($project['status']); ?>"><?php echo getStatusLabel($project['status']); ?></span>
              </div>
              <h3 class="project-title"><?php echo getProjectField($slug, 'title') ?: $project['title']; ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo getProjectField($slug, 'description') ?: $project['description']; ?></p>
                  <div class="project-specs">
                    <span class="spec-item">
                      <i class="bi bi-building"></i>
                      <?php echo htmlspecialchars($project['scope']); ?>
                    </span>
                    <span class="spec-item">
                      <i class="bi bi-currency-dollar"></i>
                      <?php echo convertNumbers(str_replace([' MM SAR', ' Million SAR'], '', $project['contract_value'])); ?> <?php echo t('mm_sar'); ?>
                    </span>
                  </div>
                </div>
                <div class="project-location">
                  <i class="bi bi-geo-alt-fill"></i>
                  <span><?php echo getLocationTranslation($project['location']); ?></span>
                </div>
              </div>
              <a href="project-details.php?project=<?php echo $slug; ?>" class="project-link">
                <span><?php echo t('projects_view_project'); ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
            <div class="project-visual">
              <img src="<?php echo ASSETS_PATH; ?>/img/projects/<?php echo $slug; ?>.webp" alt="<?php echo htmlspecialchars($project['title']); ?>" class="img-fluid" onerror="this.src='<?php echo ASSETS_PATH; ?>/img/construction/project-1.webp'">
              <div class="project-badge">
                <i class="<?php echo getStatusIcon($project['status']); ?>"></i>
              </div>
            </div>
          </div><!-- End Project Item -->
          <?php
              $dynamic_delay += 25;
              }
          }
          ?>

        </div>

      </div>

    </section><!-- /Projects Section -->

  </main>

  <?php
// Include footer
require_once __DIR__ . '/include/footer.php';
?>




