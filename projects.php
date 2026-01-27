<?php
/**
 * Projects Page
 */

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
          <div class="project-item" data-aos="zoom-in" data-aos-delay="50">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo t('project_category_mep') !== 'project_category_mep' ? t('project_category_mep') : 'MEP'; ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
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

          <div class="project-item" data-aos="zoom-in" data-aos-delay="75">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo t('project_category_mep') !== 'project_category_mep' ? t('project_category_mep') : 'MEP'; ?></span>
                <span class="project-status completed"><?php echo t('projects_completed'); ?></span>
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
              </div>
            </div>
          </div><!-- End Project Item -->

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
              <img src="<?php echo ASSETS_PATH; ?>/img/construction/project-6.webp" alt="SSEM-NWC-Salboukh Station" class="img-fluid">
              <div class="project-badge">
                <i class="bi bi-award"></i>
              </div>
            </div>
          </div><!-- End Project Item -->

          <div class="project-item" data-aos="zoom-in" data-aos-delay="300">
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

        </div>

      </div>

    </section><!-- /Projects Section -->

  </main>

  <?php
// Include footer
require_once __DIR__ . '/include/footer.php';
?>