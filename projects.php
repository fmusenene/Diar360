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
    global $projects;
    
    // Get current language
    $currentLang = getCurrentLanguage();
    
    // Check if projects array is loaded
    if (!isset($projects) || !is_array($projects)) {
        return '';
    }
    
    // Check if project exists
    if (!isset($projects[$projectSlug])) {
        return '';
    }
    
    $project = $projects[$projectSlug];
    
    // If Arabic language and Arabic field exists, use it
    if ($currentLang === 'ar' && isset($project[$field . '_ar'])) {
        return convertNumbersInText($project[$field . '_ar']);
    }
    
    // If Arabic language but no Arabic field, try to provide a basic translation
    if ($currentLang === 'ar' && !isset($project[$field . '_ar'])) {
        if ($field === 'title') {
            // Basic Arabic fallback for title
            return convertNumbersInText($project[$field] . ' (AR)');
        } elseif ($field === 'description') {
            // Basic Arabic fallback for description
            return convertNumbersInText($project[$field] . ' (AR)');
        }
    }
    
    // Otherwise use English field
    if (isset($project[$field])) {
        return $project[$field];
    }
    
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

          <!-- Dynamic Projects Section with pagination -->
          <?php
          // Collect all visible projects (so Admin edits always reflect here)
          $visibleProjects = [];
          foreach ($projects as $slug => $project) {
              $isVisible = isset($project['visible']) ? (int)$project['visible'] : 1;
              if ($isVisible === 1) {
                  $visibleProjects[$slug] = $project;
              }
          }
          // Show newest admin-added/updated projects first
          $visibleProjects = array_reverse($visibleProjects, true);

          $total = count($visibleProjects);
          $perPage = 10; // projects per page
          $pageParam = isset($_GET['p']) ? (int)$_GET['p'] : 1;
          $currentPage = max(1, $pageParam);
          $totalPages = max(1, (int)ceil($total / $perPage));
          if ($currentPage > $totalPages) {
              $currentPage = $totalPages;
          }

          $offset = ($currentPage - 1) * $perPage;
          $pagedSlugs = array_slice(array_keys($visibleProjects), $offset, $perPage, true);

          $dynamic_delay = 50;
          foreach ($pagedSlugs as $slug) {
              $project = $visibleProjects[$slug];
          ?>
          <div class="project-item" data-aos="zoom-in" data-aos-delay="<?php echo $dynamic_delay; ?>">
            <div class="project-content">
              <div class="project-header">
                <span class="project-category"><?php echo getCategoryTranslation($project['category']); ?></span>
                <span class="project-status <?php echo getStatusClass($project['status']); ?>"><?php echo getStatusLabel($project['status']); ?></span>
              </div>
              <h3 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h3>
              <div class="project-details">
                <div class="project-info">
                  <p><?php echo htmlspecialchars($project['description']); ?></p>
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
          ?>

        </div>

        <?php if ($total > $perPage): ?>
          <?php
          // Simple pagination controls
          $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
          $query = $_GET;
          ?>
          <div class="mt-4 d-flex justify-content-center">
            <nav class="projects-pagination" aria-label="Projects pagination">
              <ul class="pagination">
                <?php
                $query['p'] = max(1, $currentPage - 1);
                $prevUrl = htmlspecialchars($baseUrl . '?' . http_build_query($query));
                ?>
                <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                  <a class="page-link" href="<?php echo $currentPage <= 1 ? '#' : $prevUrl; ?>">&laquo;</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <?php
                  $query['p'] = $i;
                  $pageUrl = htmlspecialchars($baseUrl . '?' . http_build_query($query));
                  ?>
                  <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo $pageUrl; ?>"><?php echo $i; ?></a>
                  </li>
                <?php endfor; ?>
                <?php
                $query['p'] = min($totalPages, $currentPage + 1);
                $nextUrl = htmlspecialchars($baseUrl . '?' . http_build_query($query));
                ?>
                <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                  <a class="page-link" href="<?php echo $currentPage >= $totalPages ? '#' : $nextUrl; ?>">&raquo;</a>
                </li>
              </ul>
            </nav>
          </div>
        <?php endif; ?>

      </div>

    </section><!-- /Projects Section -->

  </main>

  <?php
// Include footer
require_once __DIR__ . '/include/footer.php';
?>




