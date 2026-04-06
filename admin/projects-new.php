<?php
/**
 * Modern Admin Dashboard - Project Oasis Design
 * Matches the React/TypeScript design exactly but with PHP backend
 */

session_start();
require_once __DIR__ . '/../config/projects-data.php';
require_once __DIR__ . '/../config/project-status.php';

// Security: Session timeout and activity tracking
$session_timeout = 20 * 60; // 20 minutes in seconds
$current_time = time();

// Initialize session variables if not exists
if (!isset($_SESSION['session_start'])) {
    $_SESSION['session_start'] = $current_time;
    $_SESSION['last_activity'] = $current_time;
}

// Check for session timeout
if (isset($_SESSION['last_activity']) && ($current_time - $_SESSION['last_activity']) > $session_timeout) {
    session_destroy();
    header('Location: projects-new.php?timeout=1');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = $current_time;

// Generate unique session token for security
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load admin settings
$admin_password = 'diar360_admin_2024'; // Default fallback
$site_settings = [];

$settings_file = __DIR__ . '/../config/admin-settings.php';
if (file_exists($settings_file)) {
    include $settings_file;
}

// Authentication with enhanced security
$is_authenticated = false;

if (isset($_POST['login']) && $_POST['password'] === $admin_password) {
    // Validate CSRF token (constant-time compare + graceful recovery)
    $posted_token = $_POST['csrf_token'] ?? '';
    $session_token = $_SESSION['csrf_token'] ?? '';
    if ($posted_token === '' || $session_token === '' || !hash_equals($session_token, $posted_token)) {
        // Regenerate token and return to login with an error instead of hard-stopping.
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: projects-new.php?error=csrf');
        exit;
    }
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    $_SESSION['admin_authenticated'] = true;
    $_SESSION['admin_password'] = $admin_password;
    $_SESSION['login_time'] = $current_time;
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenerate token
    $is_authenticated = true;
} elseif (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true) {
    // Verify session integrity
    if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        session_destroy();
        header('Location: projects-new.php?security=1');
        exit;
    }
    
    if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_destroy();
        header('Location: projects-new.php?security=1');
        exit;
    }
    
    // Use password from session if available
    if (isset($_SESSION['admin_password'])) {
        $admin_password = $_SESSION['admin_password'];
    }
    $is_authenticated = true;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: projects-new.php');
    exit;
}

// Handle AJAX requests for security
if (isset($_GET['heartbeat']) || isset($_GET['check_session'])) {
    header('Content-Type: application/json');
    
    if (isset($_GET['heartbeat'])) {
        // Update last activity time
        $_SESSION['last_activity'] = time();
        echo json_encode(['success' => true]);
    } elseif (isset($_GET['check_session'])) {
        // Check if session is still valid
        $is_valid = isset($_SESSION['admin_authenticated']) && 
                   $_SESSION['admin_authenticated'] === true &&
                   isset($_SESSION['ip_address']) && 
                   $_SESSION['ip_address'] === $_SERVER['REMOTE_ADDR'];
        
        echo json_encode(['valid' => $is_valid]);
    }
    exit;
}

// Handle form submissions
if ($is_authenticated) {
    // Get current page
    $current_page = isset($_GET['page']) ? $_GET['page'] : 'projects';
    
    // Handle dashboard alias
    if ($current_page === 'dashboard') {
        $current_page = 'projects';
    }
    
    // Settings form submissions
    if ($current_page === 'settings') {
        if (isset($_POST['update_settings'])) {
            // Password validation
            if (!empty($_POST['new_password'])) {
                if ($_POST['new_password'] !== $_POST['confirm_password']) {
                    header('Location: projects-new.php?page=settings&error=password_mismatch');
                    exit;
                }
                
                // Validate password strength
                $password = $_POST['new_password'];
                $errors = [];
                
                if (strlen($password) < 8) {
                    $errors[] = 'Password must be at least 8 characters long';
                }
                if (!preg_match('/[A-Z]/', $password)) {
                    $errors[] = 'Password must contain at least one uppercase letter';
                }
                if (!preg_match('/[a-z]/', $password)) {
                    $errors[] = 'Password must contain at least one lowercase letter';
                }
                if (!preg_match('/[0-9]/', $password)) {
                    $errors[] = 'Password must contain at least one number';
                }
                if (!preg_match('/[!@#$%^&*]/', $password)) {
                    $errors[] = 'Password must contain at least one special character (!@#$%^&*)';
                }
                
                if (!empty($errors)) {
                    // Store errors in session to display
                    $_SESSION['password_errors'] = $errors;
                    header('Location: projects-new.php?page=settings&error=password_validation');
                    exit;
                }
                
                // Update password
                $admin_password = $_POST['new_password'];
                
                // Update session with new password
                $_SESSION['admin_password'] = $admin_password;
                
                // Force logout to require new password login
                unset($_SESSION['admin_authenticated']);
                session_write_close();
                
                // Save settings to file
                $settings_data = "<?php\n/**\n * Site Settings\n */\n\n";
                $settings_data .= "\$admin_password = '" . addslashes($admin_password) . "';\n";
                $settings_data .= "\$site_settings = " . var_export($site_settings, true) . ";\n";
                $settings_data .= "\n?>";
                
                file_put_contents(__DIR__ . '/../config/admin-settings.php', $settings_data);
                
                // Redirect to login with success message
                header('Location: projects-new.php?success=password_changed');
                exit;
            }
            
            // Update site settings (only if password wasn't changed)
            if (empty($_POST['new_password'])) {
                $site_settings = [
                    'site_name' => $_POST['site_name'] ?? 'Diar360',
                    'admin_email' => $_POST['admin_email'] ?? 'info@diar360.com',
                    'company_phone' => $_POST['company_phone'] ?? '+966 1 1 296 7735',
                    'company_address' => $_POST['company_address'] ?? 'Prince Mohammed Ibn Salman Ibn Abdulaziz Rd, Al Falah Dist, Riyadh - KSA',
                    'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0'
                ];
                
                // Save settings to file
                $settings_data = "<?php\n/**\n * Site Settings\n */\n\n";
                $settings_data .= "\$admin_password = '" . addslashes($admin_password) . "';\n";
                $settings_data .= "\$site_settings = " . var_export($site_settings, true) . ";\n";
                $settings_data .= "\n?>";
                
                file_put_contents(__DIR__ . '/../config/admin-settings.php', $settings_data);
                header('Location: projects-new.php?page=settings&success=settings_updated');
                exit;
            }
        }
        
        // Backup functionality
        if (isset($_GET['action']) && $_GET['action'] === 'backup') {
            $backup_filename = 'projects-backup-' . date('Y-m-d-H-i-s') . '.php';
            $backup_content = "<?php\n/**\n * Projects Data Backup - " . date('Y-m-d H:i:s') . "\n */\n\n";
            $backup_content .= file_get_contents(__DIR__ . '/../config/projects-data.php');
            
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $backup_filename . '"');
            echo $backup_content;
            exit;
        }
        
        // Restore functionality
        if (isset($_POST['restore_backup']) && isset($_FILES['backup_file'])) {
            if ($_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
                $backup_content = file_get_contents($_FILES['backup_file']['tmp_name']);
                
                // Validate backup file
                if (strpos($backup_content, '$projects = [') !== false) {
                    // Restore the backup
                    file_put_contents(__DIR__ . '/../config/projects-data.php', $backup_content);
                    header('Location: projects-new.php?page=settings&success=restored');
                    exit;
                } else {
                    header('Location: projects-new.php?page=settings&error=invalid_backup');
                    exit;
                }
            }
        }
    }
    
    // Global backup functionality (accessible from any page)
    if (isset($_GET['action']) && $_GET['action'] === 'backup' && $is_authenticated) {
        $backup_filename = 'projects-backup-' . date('Y-m-d-H-i-s') . '.php';
        $backup_content = "<?php\n/**\n * Projects Data Backup - " . date('Y-m-d H:i:s') . "\n */\n\n";
        $backup_content .= file_get_contents(__DIR__ . '/../config/projects-data.php');
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup_filename . '"');
        echo $backup_content;
        exit;
    }
    
    // Add Project
    if (isset($_POST['add_project'])) {
        $title = $_POST['title'];
        $category = $_POST['category'];
        $status = $_POST['status'];
        $location = $_POST['location'];
        $contract_value = $_POST['contract_value'];
        $scope = $_POST['scope'];
        $description = $_POST['description'];
        $visible = isset($_POST['visible']) ? 1 : 0;
        
        // Create slug from title
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
        $slug = rtrim($slug, '-');
        
        // Handle image uploads
        $image_fields = [
            'project_image' => $slug . '.webp',
            'construction_image' => $slug . '-construction.webp',
            'foundation_image' => $slug . '-foundation.webp',
            'interior_image' => $slug . '-interior.webp',
            'architecture_image' => $slug . '-architecture.webp'
        ];
        
        foreach ($image_fields as $field => $filename) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES[$field]['tmp_name'];
                $upload_path = __DIR__ . '/../assets/img/projects/' . $filename;
                
                // Convert to webp
                $image_info = getimagesize($tmp_name);
                if ($image_info) {
                    // Check if GD library functions are available
                    if (!function_exists('imagecreatefromjpeg') || !function_exists('imagewebp')) {
                        // GD library not available, just move the file as-is
                        move_uploaded_file($tmp_name, $upload_path);
                    } else {
                        $image = null;
                        switch ($image_info[2]) {
                            case IMAGETYPE_JPEG:
                                if (function_exists('imagecreatefromjpeg')) {
                                    $image = imagecreatefromjpeg($tmp_name);
                                }
                                break;
                            case IMAGETYPE_PNG:
                                if (function_exists('imagecreatefrompng')) {
                                    $image = imagecreatefrompng($tmp_name);
                                }
                                break;
                            case IMAGETYPE_GIF:
                                if (function_exists('imagecreatefromgif')) {
                                    $image = imagecreatefromgif($tmp_name);
                                }
                                break;
                        }
                        
                        if ($image && function_exists('imagewebp')) {
                            imagewebp($image, $upload_path, 85);
                            imagedestroy($image);
                        } elseif ($image) {
                            // WebP conversion not available, save as original format
                            switch ($image_info[2]) {
                                case IMAGETYPE_JPEG:
                                    imagejpeg($image, str_replace('.webp', '.jpg', $upload_path), 85);
                                    break;
                                case IMAGETYPE_PNG:
                                    imagepng($image, str_replace('.webp', '.png', $upload_path));
                                    break;
                                case IMAGETYPE_GIF:
                                    imagegif($image, str_replace('.webp', '.gif', $upload_path));
                                    break;
                            }
                            imagedestroy($image);
                        } else {
                            // No image processing available, move file as-is
                            move_uploaded_file($tmp_name, $upload_path);
                        }
                    }
                }
            }
        }
        
        // Handle PDF upload
        $contract_pdf_path = '';
        if (isset($_FILES['contract_pdf']) && $_FILES['contract_pdf']['error'] === UPLOAD_ERR_OK) {
            $pdf_tmp_name = $_FILES['contract_pdf']['tmp_name'];
            $pdf_filename = $slug . '-contract.pdf';
            $pdf_upload_path = __DIR__ . '/../assets/contracts/' . $pdf_filename;
            
            // Ensure contracts directory exists
            if (!is_dir(__DIR__ . '/../assets/contracts/')) {
                mkdir(__DIR__ . '/../assets/contracts/', 0755, true);
            }
            
            // Validate and move PDF
            $file_type = mime_content_type($pdf_tmp_name);
            if ($file_type === 'application/pdf') {
                move_uploaded_file($pdf_tmp_name, $pdf_upload_path);
                $contract_pdf_path = $pdf_filename;
            }
        }
        
        // Add to projects array
        $projects[$slug] = [
            'title' => $title,
            'category' => $category,
            'status' => $status,
            'location' => $location,
            'contract_value' => $contract_value,
            'scope' => $scope,
            'description' => $description,
            'contract_pdf' => $contract_pdf_path,
            'visible' => $visible,
            'specs' => [
                'Client' => 'Diar360 Client',
                'Duration' => '12 months',
                'Budget' => $contract_value,
                'Location' => $location
            ]
        ];
        
        updateProjectsData($projects);
        header('Location: projects-new.php?success=added');
        exit;
    }
    
    // Edit Project
    if (isset($_POST['edit_project'])) {
        $slug = $_POST['project_slug'];
        if (isset($projects[$slug])) {
            $projects[$slug]['title'] = $_POST['title'];
            $projects[$slug]['category'] = $_POST['category'];
            $projects[$slug]['status'] = $_POST['status'];
            $projects[$slug]['location'] = $_POST['location'];
            $projects[$slug]['contract_value'] = $_POST['contract_value'];
            $projects[$slug]['scope'] = $_POST['scope'];
            $projects[$slug]['description'] = $_POST['description'];
            $projects[$slug]['visible'] = isset($_POST['visible']) ? 1 : 0;
            
            // Handle image uploads
            $image_fields = [
                'project_image' => $slug . '.webp',
                'construction_image' => $slug . '-construction.webp',
                'foundation_image' => $slug . '-foundation.webp',
                'interior_image' => $slug . '-interior.webp',
                'architecture_image' => $slug . '-architecture.webp'
            ];
            
            foreach ($image_fields as $field => $filename) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES[$field]['tmp_name'];
                    $upload_path = __DIR__ . '/../assets/img/projects/' . $filename;
                    
                    $image_info = getimagesize($tmp_name);
                    if ($image_info) {
                        // Check if GD library functions are available
                        if (!function_exists('imagecreatefromjpeg') || !function_exists('imagewebp')) {
                            // GD library not available, just move the file as-is
                            move_uploaded_file($tmp_name, $upload_path);
                        } else {
                            $image = null;
                            switch ($image_info[2]) {
                                case IMAGETYPE_JPEG:
                                    if (function_exists('imagecreatefromjpeg')) {
                                        $image = imagecreatefromjpeg($tmp_name);
                                    }
                                    break;
                                case IMAGETYPE_PNG:
                                    if (function_exists('imagecreatefrompng')) {
                                        $image = imagecreatefrompng($tmp_name);
                                    }
                                    break;
                                case IMAGETYPE_GIF:
                                    if (function_exists('imagecreatefromgif')) {
                                        $image = imagecreatefromgif($tmp_name);
                                    }
                                    break;
                            }
                            
                            if ($image && function_exists('imagewebp')) {
                                imagewebp($image, $upload_path, 85);
                                imagedestroy($image);
                            } elseif ($image) {
                                // WebP conversion not available, save as original format
                                switch ($image_info[2]) {
                                    case IMAGETYPE_JPEG:
                                        imagejpeg($image, str_replace('.webp', '.jpg', $upload_path), 85);
                                        break;
                                    case IMAGETYPE_PNG:
                                        imagepng($image, str_replace('.webp', '.png', $upload_path));
                                        break;
                                    case IMAGETYPE_GIF:
                                        imagegif($image, str_replace('.webp', '.gif', $upload_path));
                                        break;
                                }
                                imagedestroy($image);
                            } else {
                                // No image processing available, move file as-is
                                move_uploaded_file($tmp_name, $upload_path);
                            }
                        }
                    }
                }
            }
            
            // Handle PDF upload - only if not deleting
            if (!isset($_POST['delete_contract_pdf']) && isset($_FILES['contract_pdf']) && $_FILES['contract_pdf']['error'] === UPLOAD_ERR_OK) {
                $pdf_tmp_name = $_FILES['contract_pdf']['tmp_name'];
                $timestamp = date('Y-m-d_H-i-s');
                $pdf_filename = $slug . '-' . $timestamp . '-contract.pdf';
                $pdf_upload_path = __DIR__ . '/../assets/contracts/' . $pdf_filename;
                
                // Debug: Log upload process
                error_log("PDF Upload Process:");
                error_log("Slug: " . $slug);
                error_log("New filename: " . $pdf_filename);
                error_log("Upload path: " . $pdf_upload_path);
                error_log("Current PDF in data: " . (isset($projects[$slug]['contract_pdf']) ? $projects[$slug]['contract_pdf'] : 'NONE'));
                
                // Ensure contracts directory exists
                if (!is_dir(__DIR__ . '/../assets/contracts/')) {
                    mkdir(__DIR__ . '/../assets/contracts/', 0755, true);
                }
                
                // Delete ALL existing PDFs for this project to prevent conflicts
                if (isset($projects[$slug]['contract_pdf']) && $projects[$slug]['contract_pdf'] !== '') {
                    $existing_pdf = __DIR__ . '/../assets/contracts/' . $projects[$slug]['contract_pdf'];
                    error_log("Existing PDF to delete: " . $existing_pdf);
                    error_log("Existing PDF exists: " . (file_exists($existing_pdf) ? 'YES' : 'NO'));
                    
                    if (file_exists($existing_pdf)) {
                        $delete_result = unlink($existing_pdf);
                        error_log("Delete existing PDF result: " . ($delete_result ? 'SUCCESS' : 'FAILED'));
                    }
                    
                    // Also clean up any old PDF files with this project slug
                    $contract_files = glob(__DIR__ . '/../assets/contracts/' . $slug . '*-contract.pdf');
                    if ($contract_files) {
                        foreach ($contract_files as $old_file) {
                            if (file_exists($old_file) && $old_file !== $pdf_upload_path) {
                                error_log("Cleaning up old file: " . $old_file);
                                unlink($old_file);
                            }
                        }
                    }
                }
                
                // Validate and move new PDF
                $file_type = mime_content_type($pdf_tmp_name);
                if ($file_type === 'application/pdf') {
                    $move_result = move_uploaded_file($pdf_tmp_name, $pdf_upload_path);
                    error_log("Move new PDF result: " . ($move_result ? 'SUCCESS' : 'FAILED'));
                    error_log("New file exists after upload: " . (file_exists($pdf_upload_path) ? 'YES' : 'NO'));
                    
                    if ($move_result) {
                        $projects[$slug]['contract_pdf'] = $pdf_filename;
                        error_log("Updated project data with new PDF: " . $pdf_filename);
                    }
                }
            }
            
            updateProjectsData($projects);
            header('Location: projects-new.php?success=updated');
            exit;
        }
    }
    
    // Delete Project
    if (isset($_POST['delete_project'])) {
        $slug = $_POST['project_slug'];
        if (isset($projects[$slug])) {
            unset($projects[$slug]);
            updateProjectsData($projects);
            header('Location: projects-new.php?success=deleted');
            exit;
        }
    }
    
    // Delete Contract PDF
    if (isset($_POST['delete_contract_pdf'])) {
        $slug = $_POST['project_slug'];
        if (isset($projects[$slug])) {
            // Delete existing PDF
            if (isset($projects[$slug]['contract_pdf']) && $projects[$slug]['contract_pdf'] !== '') {
                $existing_pdf = __DIR__ . '/../assets/contracts/' . $projects[$slug]['contract_pdf'];
                
                // Debug: Log the file path
                error_log("Attempting to delete PDF: " . $existing_pdf);
                error_log("File exists: " . (file_exists($existing_pdf) ? 'YES' : 'NO'));
                
                if (file_exists($existing_pdf)) {
                    $delete_result = unlink($existing_pdf);
                    error_log("Delete result: " . ($delete_result ? 'SUCCESS' : 'FAILED'));
                    
                    if ($delete_result) {
                        $projects[$slug]['contract_pdf'] = '';
                        updateProjectsData($projects);
                        header('Location: projects-new.php?success=pdf_deleted');
                        exit;
                    } else {
                        error_log("Failed to delete PDF file: " . $existing_pdf);
                        header('Location: projects-new.php?error=pdf_delete_failed');
                        exit;
                    }
                } else {
                    error_log("PDF file does not exist: " . $existing_pdf);
                    // File doesn't exist, just clear the data
                    $projects[$slug]['contract_pdf'] = '';
                    updateProjectsData($projects);
                    header('Location: projects-new.php?success=pdf_deleted');
                    exit;
                }
            } else {
                // No PDF to delete
                header('Location: projects-new.php?error=no_pdf_to_delete');
                exit;
            }
        }
    }
    
    // Status Update
    if (isset($_POST['update_status'])) {
        $slug = $_POST['project_slug'];
        $new_status = $_POST['status'];
        
        if (isset($projects[$slug])) {
            $projects[$slug]['status'] = $new_status;
            updateProjectsData($projects);
            header('Location: projects-new.php?success=status_updated');
            exit;
        }
    }
}

function updateProjectsData($projects) {
    $data = "<?php\n/**\n * Projects Data\n */\n\n";
    $data .= "// Include project status management\nrequire_once __DIR__ . '/project-status.php';\n\n";
    $data .= "\$projects = [\n";
    
    foreach ($projects as $slug => $project) {
        $data .= "    '$slug' => [\n";
        foreach ($project as $key => $value) {
            if ($key === 'specs') {
                $data .= "        '$key' => [\n";
                foreach ($value as $spec_key => $spec_value) {
                    $data .= "            '$spec_key' => '" . addslashes($spec_value) . "',\n";
                }
                $data .= "        ],\n";
            } else {
                $data .= "        '$key' => '" . addslashes($value) . "',\n";
            }
        }
        $data .= "    ],\n";
    }
    
    $data .= "];\n\n?>";
    
    $file_path = __DIR__ . '/../config/projects-data.php';
    file_put_contents($file_path, $data);
    return true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diar360 Admin - Projects</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Force color override for immediate effect */
        .bg-primary {
            background-color: #13529D !important;
        }
        
        .bg-sidebar {
            background-color: #13529D !important;
        }
        
        .text-sidebar-foreground, .sidebar * {
            color: #FFFFFF !important;
        }
        
        body, .text-foreground, h1, h2, h3, h4, h5, h6, p, span, div, td, th, label, input, textarea, select, option, .project-title, .project-location, .project-value, .project-scope, .project-description, .project-category, .project-status, .text-muted-foreground, .text-card-foreground {
            color: #0F2A49 !important;
        }
        
        /* Ensure sidebar text stays white */
        .sidebar, .sidebar *, .bg-sidebar, .bg-sidebar * {
            color: #FFFFFF !important;
        }
        
        /* Active navigation highlighting */
        .nav-item.active {
            background-color: #265AA2 !important;
            color: #FFFFFF !important;
        }
        
        .nav-item:not(.active) {
            background-color: transparent !important;
            color: #FFFFFF !important;
        }
        
        .nav-item {
            transition: all 0.3s ease !important;
        }
        
        .nav-item:hover:not(.active) {
            background-color: rgba(38, 90, 162, 0.3) !important;
            color: #FFFFFF !important;
            transform: translateX(5px) !important;
        }
        
        /* Project Status Badge Colors */
        .status-badge.completed {
            background-color: hsl(120, 60%, 40%) !important;
            color: white !important;
        }
        
        .status-badge.in-progress {
            background-color: hsl(38, 92%, 50%) !important;
            color: white !important;
        }
        
        .status-badge.planning {
            background-color: hsl(45, 100%, 50%) !important;
            color: white !important;
        }
        
        .status-badge.on-hold {
            background-color: hsl(0, 0%, 55%) !important;
            color: white !important;
        }
        
        @import url('https://fonts.googleapis.com/css2?family=Domine:wght@400;500;600;700&family=Arimo:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&family=El+Messiri:wght@400;500;600;700&display=swap');
        
        :root {
            --background: 34 100% 97%;
            --foreground: 0 100% 29%; /* #0F2A49 */
            --card: 34 60% 95%;
            --card-foreground: 345 48% 25%;
            --primary: 345 48% 25%; /* #13529D */
            --primary-foreground: 34 100% 97%;
            --secondary: 9 30% 62%;
            --secondary-foreground: 34 100% 97%;
            --muted: 34 30% 92%;
            --muted-foreground: 345 20% 40%;
            --accent: 9 30% 62%;
            --accent-foreground: 34 100% 97%;
            --destructive: 0 72% 51%;
            --destructive-foreground: 0 0% 100%;
            --border: 345 15% 85%;
            --input: 345 15% 85%;
            --ring: 345 48% 25%;
            --sidebar-background: 345 48% 25%; /* #13529D */
            --sidebar-foreground: 34 100% 97%; /* #FFFFFF */
            --sidebar-primary: 34 100% 97%;
            --sidebar-primary-foreground: 345 48% 25%;
            --sidebar-accent: 345 40% 30%; /* #265AA2 */
            --sidebar-accent-foreground: 34 100% 97%;
            --sidebar-border: 345 35% 32%;
            --status-completed: 120 60% 40%; /* Green for completed */
            --status-in-progress: 38 92% 50%; /* Blue for in progress */
            --status-planning: 45 100% 50%; /* Orange for planning */
            --status-on-hold: 0 0% 55%; /* Gray for on hold */
            --font-heading: 'Domine', serif;
            --font-body: 'Arimo', sans-serif;
            --font-arabic: 'El Messiri', sans-serif;
        }
        
        * {
            border-color: hsl(var(--border));
        }
        
        body {
            background: hsl(var(--background));
            color: hsl(var(--foreground));
            font-family: var(--font-body);
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
        }
        
        .bg-card {
            background: hsl(var(--card));
        }
        
        .bg-sidebar {
            background: hsl(var(--sidebar-background));
        }
        
        .text-sidebar-foreground {
            color: hsl(var(--sidebar-foreground));
        }
        
        .bg-sidebar-accent {
            background: hsl(var(--sidebar-accent));
        }
        
        .text-sidebar-accent-foreground {
            color: hsl(var(--sidebar-accent-foreground));
        }
        
        .border-sidebar-border {
            border-color: hsl(var(--sidebar-border));
        }
        
        .text-muted-foreground {
            color: hsl(var(--muted-foreground));
        }
        
        .bg-muted {
            background: hsl(var(--muted));
        }
        
        .bg-primary {
            background: hsl(var(--primary));
        }
        
        .text-primary-foreground {
            color: hsl(var(--primary-foreground));
        }
        
        .bg-destructive {
            background: hsl(var(--destructive));
        }
        
        .text-destructive {
            color: hsl(var(--destructive));
        }
        
        .status-completed {
            background: hsl(var(--status-completed));
            color: white;
        }
        
        .status-in-progress {
            background: hsl(var(--status-in-progress));
            color: white;
        }
        
        .status-planning {
            background: hsl(var(--status-planning));
            color: white;
        }
        
        .status-on-hold {
            background: hsl(var(--status-on-hold));
            color: white;
        }

        /* Dashboard statistic card styles */
        .stats-card {
            border: 1px solid hsl(var(--border));
            border-left-width: 4px;
            border-radius: 0.85rem;
            transition: transform 0.2s ease, box-shadow 0.25s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(15, 42, 73, 0.12);
        }

        .stats-card-total {
            border-left-color: #13529D;
            background: linear-gradient(135deg, rgba(19, 82, 157, 0.08), rgba(19, 82, 157, 0.02));
        }

        .stats-card-completed {
            border-left-color: #1f8f45;
            background: linear-gradient(135deg, rgba(31, 143, 69, 0.1), rgba(31, 143, 69, 0.02));
        }

        .stats-card-progress {
            border-left-color: #f59e0b;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.14), rgba(245, 158, 11, 0.03));
        }

        .stats-card-planning {
            border-left-color: #3b82f6;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(59, 130, 246, 0.03));
        }

        .stats-card-hold {
            border-left-color: #6b7280;
            background: linear-gradient(135deg, rgba(107, 114, 128, 0.16), rgba(107, 114, 128, 0.03));
        }

        .stats-value {
            transition: transform 0.2s ease;
        }

        .stats-card:hover .stats-value {
            transform: scale(1.04);
        }

        /* Pagination */
        .pagination-btn {
            min-width: 2.2rem;
            height: 2.2rem;
            padding: 0 0.55rem;
            border-radius: 0.6rem;
            border: 1px solid hsl(var(--border));
            background: hsl(var(--card));
            color: hsl(var(--foreground));
            font-size: 0.82rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .pagination-btn:hover:not(:disabled) {
            background: rgba(19, 82, 157, 0.08);
            border-color: rgba(19, 82, 157, 0.35);
        }

        .pagination-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .pagination-btn.active {
            background: #13529D;
            border-color: #13529D;
            color: #fff;
        }
        
        /* Custom status dropdown styling */
        .status-dropdown {
            background-color: white !important;
            border: 1px solid hsl(var(--border));
        }
        
        .status-dropdown option {
            background-color: white;
            color: hsl(var(--foreground));
            padding: 8px 12px;
        }
        
        /* Hover colors for options */
        .status-dropdown option:hover {
            background-color: #f3f4f6 !important;
        }
        
        .status-dropdown option[value="completed"]:hover {
            background-color: #28a745 !important;
            color: white !important;
        }
        
        .status-dropdown option[value="in-progress"]:hover {
            background-color: #007bff !important;
            color: white !important;
        }
        
        .status-dropdown option[value="planning"]:hover {
            background-color: #ffc107 !important;
            color: white !important;
        }
        
        .status-dropdown option[value="on-hold"]:hover {
            background-color: #6c757d !important;
            color: white !important;
        }
        
        /* Selected state colors for dropdown */
        .status-dropdown.completed-selected {
            background-color: #28a745 !important;
            color: white !important;
        }
        
        .status-dropdown.in-progress-selected {
            background-color: #007bff !important;
            color: white !important;
        }
        
        .status-dropdown.planning-selected {
            background-color: #ffc107 !important;
            color: white !important;
        }
        
        .status-dropdown.on-hold-selected {
            background-color: #6c757d !important;
            color: white !important;
        }
        
        /* Hover effects for selected dropdown */
        .status-dropdown.completed-selected:hover {
            background-color: #218838 !important;
            color: white !important;
        }
        
        .status-dropdown.in-progress-selected:hover {
            background-color: #0056b3 !important;
            color: white !important;
        }
        
        .status-dropdown.planning-selected:hover {
            background-color: #e0a800 !important;
            color: white !important;
        }
        
        .status-dropdown.on-hold-selected:hover {
            background-color: #545b62 !important;
            color: white !important;
        }
        
        .transition-all {
            transition: all 0.3s ease;
        }
        
        .sidebar-collapsed {
            width: 72px;
        }
        
        .sidebar-expanded {
            width: 260px;
        }
        
        .main-content-shifted {
            margin-left: 260px;
        }
        
        .main-content-collapsed {
            margin-left: 72px;
        }
        
        /* Responsive layout for admin panel */
        #sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 40;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        @media (max-width: 1024px) {
            #sidebar {
                width: 260px !important;
                transform: translateX(-100%);
                transition: transform 0.28s ease;
            }

            #sidebar.mobile-open {
                transform: translateX(0);
            }

            #main-content {
                margin-left: 0 !important;
            }

            #sidebar-overlay.active {
                opacity: 1;
                pointer-events: auto;
            }

            .sidebar-text {
                display: block !important;
            }

            .mobile-header-btn {
                display: inline-flex !important;
            }
        }

        @media (min-width: 1025px) {
            .mobile-header-btn {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            header.h-16 {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            main.flex-1 {
                padding: 1rem !important;
            }

            .tab-button {
                font-size: 0.8rem;
                padding-left: 0.25rem !important;
                padding-right: 0.25rem !important;
            }

            #projects-container .project-card {
                padding: 1rem !important;
            }

            .project-card {
                overflow: hidden;
            }

            .project-card h3 {
                font-size: 1rem !important;
                line-height: 1.3 !important;
            }

            .project-card .status-badge,
            .project-card .visibility-badge {
                font-size: 0.68rem !important;
                padding: 0.2rem 0.45rem !important;
                white-space: nowrap;
            }

            /* Keep description visible but compact on small screens */
            .project-card p.line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                margin-bottom: 0.5rem !important;
                font-size: 0.82rem !important;
                line-height: 1.35 !important;
            }

            .project-card .project-meta {
                gap: 0.4rem 0.85rem !important;
                font-size: 0.78rem !important;
            }

            .project-card .project-meta-scope {
                display: none;
            }

            .project-card .project-status-actions,
            .project-card .project-file-actions,
            .project-card .project-main-actions {
                width: 100%;
                margin-top: 0.25rem !important;
                flex-wrap: wrap;
            }

            .project-card .project-status-actions select {
                width: 100% !important;
                max-width: 100% !important;
            }

            .project-card .project-file-actions a,
            .project-card .project-main-actions button {
                flex: 1 1 auto;
                justify-content: center;
                min-height: 38px;
            }

            .project-card,
            .project-card * {
                word-break: break-word;
                overflow-wrap: anywhere;
            }

            /* Fit more summary cards in first viewport */
            .mobile-stats-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                gap: 0.5rem !important;
            }

            .mobile-stats-grid > div {
                padding: 0.6rem !important;
            }

            .mobile-stats-grid p.text-2xl {
                font-size: 1.05rem !important;
                line-height: 1.1 !important;
            }

            .mobile-stats-grid p.text-sm {
                font-size: 0.72rem !important;
            }

            #pagination-controls {
                gap: 0.35rem !important;
            }

            #pagination-controls .pagination-btn {
                min-width: 2rem;
                height: 2rem;
                font-size: 0.76rem;
                padding: 0 0.42rem;
            }

            #project-modal .bg-card,
            #delete-modal .bg-card,
            #delete-pdf-modal .bg-card {
                width: calc(100vw - 1rem) !important;
                max-width: calc(100vw - 1rem) !important;
                margin: 0.5rem;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-background">
    <?php if (!$is_authenticated): ?>
    <!-- Login Page -->
    <div class="min-h-screen flex items-center justify-center bg-card">
        <div class="max-w-md w-full p-8 bg-card border border-border rounded-xl">
            <div class="text-center mb-8">
                <div class="h-12 w-12 bg-primary rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-building text-primary-foreground text-xl"></i>
                </div>
                <h2 class="text-2xl font-bold font-heading text-foreground">Diar360 Admin</h2>
                <p class="text-muted-foreground mt-2">Project Management Dashboard</p>
            </div>
            
            <form method="post" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div>
                    <label for="password" class="block text-sm font-medium text-foreground mb-2">Admin Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required 
                               class="w-full px-3 py-2 pr-10 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                        <button type="button" onclick="togglePassword('password', 'login_password_eye')" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-muted-foreground hover:text-foreground focus:outline-none">
                            <i id="login_password_eye" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" name="login" 
                        class="w-full bg-primary text-primary-foreground py-2 px-4 rounded-lg hover:bg-primary/90 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </button>
            </form>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Admin Dashboard -->
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed left-0 top-0 h-screen bg-sidebar text-sidebar-foreground flex flex-col z-50 overflow-hidden sidebar-expanded transition-all duration-300">
        <!-- Logo + Collapse Toggle -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-sidebar-border">
            <div class="flex items-center min-w-0">
                <div class="h-8 w-8 bg-secondary rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-secondary-foreground"></i>
                </div>
                <span class="sidebar-text ml-3 text-xl font-heading font-bold whitespace-nowrap">Diar360</span>
            </div>
            <button onclick="toggleSidebar()" class="h-8 w-8 shrink-0 flex items-center justify-center rounded-lg text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground transition-colors">
                <i id="sidebar-toggle-icon" class="fas fa-chevron-left text-sm"></i>
            </button>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 py-4 px-2 space-y-1">
            <a href="projects-new.php" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground">
                <i class="fas fa-chart-line h-5 w-5 shrink-0"></i>
                <span class="sidebar-text text-sm font-medium whitespace-nowrap">Dashboard</span>
            </a>
            <a href="projects-new.php?page=settings" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-foreground">
                <i class="fas fa-cog h-5 w-5 shrink-0"></i>
                <span class="sidebar-text text-sm font-medium whitespace-nowrap">Settings</span>
            </a>
        </nav>
        
        <!-- Logout -->
        <div class="border-t border-sidebar-border p-2">
            <a href="?logout=true" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sidebar-foreground/70 hover:bg-destructive/20 hover:text-destructive transition-colors">
                <i class="fas fa-sign-out-alt h-5 w-5 shrink-0"></i>
                <span class="sidebar-text text-sm font-medium whitespace-nowrap">Logout</span>
            </a>
        </div>
    </aside>
    
    <div id="sidebar-overlay" onclick="closeMobileSidebar()"></div>

    <!-- Main Content -->
    <div id="main-content" class="main-content-shifted min-h-screen flex flex-col transition-all duration-300">
        <!-- Top Navbar -->
        <header class="h-16 border-b border-border bg-card flex items-center justify-between px-6 shrink-0">
            <button type="button" onclick="toggleSidebar()" class="mobile-header-btn hidden h-10 w-10 items-center justify-center rounded-lg border border-border bg-background text-foreground">
                <i class="fas fa-bars"></i>
            </button>
            <div class="flex items-center gap-3">
                <!-- Admin Dropdown -->
                <div class="relative">
                    <button onclick="toggleDropdown()" class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-muted transition-colors">
                        <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center">
                            <i class="fas fa-user text-primary-foreground"></i>
                        </div>
                        <i class="fas fa-chevron-down h-4 w-4 text-muted-foreground"></i>
                    </button>
                    <div id="admin-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-card border border-border rounded-lg shadow-lg z-50">
                        <a href="projects-new.php?page=settings" class="block px-4 py-2 text-sm text-foreground hover:bg-muted">Settings</a>
                        <hr class="border-border">
                        <a href="?logout=true" class="block px-4 py-2 text-sm text-destructive hover:bg-muted">Logout</a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Main Content Area -->
        <main class="flex-1 p-6 lg:p-8 max-w-7xl">
            <?php if (isset($_GET['success'])): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <span class="text-green-800">
                            <?php
                            switch ($_GET['success']) {
                                case 'added': echo 'Project created successfully'; break;
                                case 'project_updated': echo 'Project updated successfully'; break;
                                case 'deleted': echo 'Project deleted successfully'; break;
                                case 'status_updated': echo 'Status updated successfully'; break;
                                case 'settings_updated': echo 'Settings updated successfully'; break;
                                case 'restored': echo 'Backup restored successfully'; break;
                                case 'backup_created': echo 'Backup created successfully'; break;
                                case 'password_changed': echo 'Password changed successfully! Please login with your new password.'; break;
                            }
                            ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <div class="text-red-800">
                            <?php
                            switch ($_GET['error']) {
                                case 'password_mismatch': 
                                    echo 'Passwords do not match'; 
                                    break;
                                case 'password_validation': 
                                    echo 'Password validation failed:';
                                    if (isset($_SESSION['password_errors']) && is_array($_SESSION['password_errors'])) {
                                        echo '<ul class="mt-2 list-disc list-inside">';
                                        foreach ($_SESSION['password_errors'] as $error) {
                                            echo '<li>' . htmlspecialchars($error) . '</li>';
                                        }
                                        echo '</ul>';
                                        unset($_SESSION['password_errors']);
                                    }
                                    break;
                                case 'invalid_backup': 
                                    echo 'Invalid backup file'; 
                                    break;
                                case 'csrf':
                                    echo 'Security token expired or invalid. Please try logging in again.';
                                    break;
                                default: 
                                    echo 'An error occurred'; 
                                    break;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($current_page === 'settings'): ?>
                <!-- Settings Page -->
                <div class="space-y-8">
                    <!-- Header -->
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-heading font-bold text-foreground">Settings</h1>
                        <p class="text-muted-foreground mt-1">Manage your admin panel and site configuration</p>
                    </div>
                    
                    <!-- Settings Tabs -->
                    <div class="border-b border-border">
                        <nav class="-mb-px flex space-x-8">
                            <button class="tab-button py-2 px-1 border-b-2 border-primary text-primary font-medium text-sm" onclick="showTab('general', this)">
                                General
                            </button>
                            <button class="tab-button py-2 px-1 border-b-2 border-transparent text-muted-foreground hover:text-foreground font-medium text-sm" onclick="showTab('security', this)">
                                Security
                            </button>
                            <button class="tab-button py-2 px-1 border-b-2 border-transparent text-muted-foreground hover:text-foreground font-medium text-sm" onclick="showTab('backup', this)">
                                Backup
                            </button>
                        </nav>
                    </div>
                    
                    <!-- General Settings -->
                    <div id="general-tab" class="tab-content" style="display: block !important;">
                        <div class="bg-card rounded-xl border border-border p-6">
                            <h3 class="text-lg font-heading font-bold text-foreground mb-6">General Settings</h3>
                            
                            <form method="post" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="site_name" class="block text-sm font-medium text-foreground mb-2">Site Name</label>
                                        <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars(isset($site_settings['site_name']) ? $site_settings['site_name'] : 'Diar360'); ?>" 
                                               class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label for="admin_email" class="block text-sm font-medium text-foreground mb-2">Admin Email</label>
                                        <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars(isset($site_settings['admin_email']) ? $site_settings['admin_email'] : 'info@diar360.com'); ?>" 
                                               class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label for="company_phone" class="block text-sm font-medium text-foreground mb-2">Company Phone</label>
                                        <input type="tel" id="company_phone" name="company_phone" value="<?php echo htmlspecialchars(isset($site_settings['company_phone']) ? $site_settings['company_phone'] : '+966 1 1 296 7735'); ?>" 
                                               class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div>
                                        <label for="company_address" class="block text-sm font-medium text-foreground mb-2">Company Address</label>
                                        <input type="text" id="company_address" name="company_address" value="<?php echo htmlspecialchars(isset($site_settings['company_address']) ? $site_settings['company_address'] : 'Prince Mohammed Ibn Salman Ibn Abdulaziz Rd, Al Falah Dist, Riyadh - KSA'); ?>" 
                                               class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                </div>
                                
                                <div class="flex items-center">
                                    <input type="checkbox" id="maintenance_mode" name="maintenance_mode" class="h-4 w-4 text-primary bg-background border-input rounded focus:ring-ring" 
                                           <?php echo (isset($site_settings['maintenance_mode']) && $site_settings['maintenance_mode'] === '1') ? 'checked' : ''; ?>>
                                    <label for="maintenance_mode" class="ml-2 text-sm text-foreground">
                                        Enable Maintenance Mode
                                        <?php if (isset($site_settings['maintenance_mode']) && $site_settings['maintenance_mode'] === '1'): ?>
                                            <span class="ml-2 text-xs text-orange-600">(Currently Active)</span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" name="update_settings" 
                                            class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                        <i class="fas fa-save mr-2"></i>
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Security Settings -->
                    <div id="security-tab" class="tab-content" style="display: none !important;">
                        <div class="bg-card rounded-xl border border-border p-6">
                            <h3 class="text-lg font-heading font-bold text-foreground mb-6">Security Settings</h3>
                            
                            <form method="post" class="space-y-6">
                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-foreground mb-2">New Admin Password</label>
                                    <div class="relative">
                                        <input type="password" id="new_password" name="new_password" 
                                               placeholder="Leave blank to keep current password"
                                               class="w-full px-3 py-2 pr-10 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"
                                               oninput="validatePassword()">
                                        <button type="button" onclick="togglePassword('new_password', 'new_password_eye')" 
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-muted-foreground hover:text-foreground focus:outline-none">
                                            <i id="new_password_eye" class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="password-strength" class="mt-2 text-sm hidden">
                                        <div class="flex items-center justify-between mb-1">
                                            <span>Password Strength:</span>
                                            <span id="strength-text" class="font-medium"></span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div id="strength-bar" class="h-2 rounded-full transition-all duration-300"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-foreground mb-2">Confirm New Password</label>
                                    <div class="relative">
                                        <input type="password" id="confirm_password" name="confirm_password" 
                                               placeholder="Confirm new password"
                                               class="w-full px-3 py-2 pr-10 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"
                                               oninput="validatePasswordMatch()">
                                        <button type="button" onclick="togglePassword('confirm_password', 'confirm_password_eye')" 
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-muted-foreground hover:text-foreground focus:outline-none">
                                            <i id="confirm_password_eye" class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="password-match" class="mt-2 text-sm hidden">
                                        <span id="match-text"></span>
                                    </div>
                                </div>
                                
                                <div class="bg-muted/50 rounded-lg p-4">
                                    <h4 class="font-medium text-foreground mb-2">Password Requirements:</h4>
                                    <ul class="text-sm text-muted-foreground space-y-1" id="password-requirements">
                                        <li id="req-length" class="flex items-center">
                                            <i class="fas fa-times-circle text-red-500 mr-2" id="req-length-icon"></i>
                                            At least 8 characters long
                                        </li>
                                        <li id="req-uppercase" class="flex items-center">
                                            <i class="fas fa-times-circle text-red-500 mr-2" id="req-uppercase-icon"></i>
                                            At least one uppercase letter (A-Z)
                                        </li>
                                        <li id="req-lowercase" class="flex items-center">
                                            <i class="fas fa-times-circle text-red-500 mr-2" id="req-lowercase-icon"></i>
                                            At least one lowercase letter (a-z)
                                        </li>
                                        <li id="req-number" class="flex items-center">
                                            <i class="fas fa-times-circle text-red-500 mr-2" id="req-number-icon"></i>
                                            At least one number (0-9)
                                        </li>
                                        <li id="req-special" class="flex items-center">
                                            <i class="fas fa-times-circle text-red-500 mr-2" id="req-special-icon"></i>
                                            At least one special character (!@#$%^&*)
                                        </li>
                                    </ul>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" name="update_settings" 
                                            class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                        <i class="fas fa-shield-alt mr-2"></i>
                                        Update Security
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Backup Settings -->
                    <div id="backup-tab" class="tab-content" style="display: none !important;">
                        <div class="bg-card rounded-xl border border-border p-6">
                            <h3 class="text-lg font-heading font-bold text-foreground mb-6">Backup & Restore</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-muted/50 rounded-lg p-4">
                                    <h4 class="font-medium text-foreground mb-2">Backup Projects Data</h4>
                                    <p class="text-sm text-muted-foreground mb-4">Create a backup of all projects data</p>
                                    <button onclick="createBackup()" class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                                        <i class="fas fa-download mr-2"></i>
                                        Download Backup
                                    </button>
                                </div>
                                
                                <div class="bg-muted/50 rounded-lg p-4">
                                    <h4 class="font-medium text-foreground mb-2">Restore Projects Data</h4>
                                    <p class="text-sm text-muted-foreground mb-4">Restore from a backup file</p>
                                    <input type="file" id="backup_file" accept=".php" class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring mb-2">
                                    <button onclick="restoreBackup()" class="px-4 py-2 bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/90 transition-colors">
                                        <i class="fas fa-upload mr-2"></i>
                                        Restore Backup
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mt-6 bg-muted/50 rounded-lg p-4">
                                <h4 class="font-medium text-foreground mb-2">Backup Information</h4>
                                <div class="text-sm text-muted-foreground space-y-1">
                                    <p>• Backups include all projects data and configurations</p>
                                    <p>• Store backup files in a secure location</p>
                                    <p>• Regular backups are recommended for data safety</p>
                                    <p>• Last backup: <span id="last-backup-date">Never</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($current_page === 'projects' || $current_page === 'dashboard'): ?>
                <!-- Projects Page (Dashboard) -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-heading font-bold text-foreground">Projects</h1>
                    <p class="text-muted-foreground mt-1">Manage and track all company projects</p>
                </div>
                <button onclick="openAddModal()" class="bg-primary text-primary-foreground px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors gap-2 inline-flex items-center">
                    <i class="fas fa-plus h-4 w-4"></i>
                    New Project
                </button>
            </div>
            
            <!-- Stats -->
            <div class="mobile-stats-grid grid grid-cols-2 lg:grid-cols-5 gap-3 mb-8">
                <?php
                $total_projects = count($projects);
                $completed = array_filter($projects, function($p) { return $p['status'] === 'completed'; });
                $in_progress = array_filter($projects, function($p) { return $p['status'] === 'in-progress'; });
                $planning = array_filter($projects, function($p) { return $p['status'] === 'planning'; });
                $on_hold = array_filter($projects, function($p) { return $p['status'] === 'on-hold'; });
                ?>
                
                <div class="stats-card stats-card-total bg-card rounded-xl p-4 hover:shadow-lg transition-all duration-300 cursor-pointer group">
                    <p class="text-sm text-muted-foreground">Total</p>
                    <p class="stats-value text-2xl font-heading font-bold mt-1 text-primary"><?php echo $total_projects; ?></p>
                </div>
                <div class="stats-card stats-card-completed bg-card rounded-xl p-4 hover:shadow-lg transition-all duration-300 cursor-pointer group">
                    <p class="text-sm text-muted-foreground">Completed</p>
                    <p class="stats-value text-2xl font-heading font-bold mt-1 text-green-700"><?php echo count($completed); ?></p>
                </div>
                <div class="stats-card stats-card-progress bg-card rounded-xl p-4 hover:shadow-lg transition-all duration-300 cursor-pointer group">
                    <p class="text-sm text-muted-foreground">In Progress</p>
                    <p class="stats-value text-2xl font-heading font-bold mt-1 text-amber-600"><?php echo count($in_progress); ?></p>
                </div>
                <div class="stats-card stats-card-planning bg-card rounded-xl p-4 hover:shadow-lg transition-all duration-300 cursor-pointer group">
                    <p class="text-sm text-muted-foreground">Planning</p>
                    <p class="stats-value text-2xl font-heading font-bold mt-1 text-blue-600"><?php echo count($planning); ?></p>
                </div>
                <div class="stats-card stats-card-hold bg-card rounded-xl p-4 hover:shadow-lg transition-all duration-300 cursor-pointer group">
                    <p class="text-sm text-muted-foreground">On Hold</p>
                    <p class="stats-value text-2xl font-heading font-bold mt-1 text-gray-600"><?php echo count($on_hold); ?></p>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-3 mb-6">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground"></i>
                    <input type="text" id="search" placeholder="Search projects or locations..." 
                           class="w-full pl-9 pr-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <select id="status-filter" class="w-full sm:w-44 px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                    <option value="all">All Statuses</option>
                    <option value="completed">Completed</option>
                    <option value="in-progress">In Progress</option>
                    <option value="planning">Planning</option>
                    <option value="on-hold">On Hold</option>
                </select>
            </div>
            
                    
        <!-- Project Cards -->
            <div id="projects-container" class="grid gap-4">
                <?php foreach ($projects as $slug => $project): ?>
                <div class="project-card bg-card border border-border rounded-xl p-5 hover:shadow-md transition-shadow" 
                     data-status="<?php echo $project['status']; ?>" 
                     data-search="<?php echo strtolower($project['title'] . ' ' . $project['location']); ?>">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="font-heading font-semibold text-lg text-foreground truncate">
                                    <?php echo htmlspecialchars($project['title']); ?>
                                </h3>
                                <span class="status-badge px-2 py-1 rounded-full text-xs font-medium <?php echo getStatusClass($project['status']); ?>">
                                    <?php echo getStatusLabel($project['status']); ?>
                                </span>
                                <span class="visibility-badge px-2 py-1 rounded-full text-xs font-medium <?php echo ($project['visible'] ?? '0') === '1' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'; ?>">
                                    <i class="fas <?php echo ($project['visible'] ?? '0') === '1' ? 'fa-eye' : 'fa-eye-slash'; ?> mr-1"></i>
                                    <?php echo ($project['visible'] ?? '0') === '1' ? 'Visible' : 'Hidden'; ?>
                                </span>
                            </div>
                            <p class="text-sm text-muted-foreground mb-3 line-clamp-2"><?php echo htmlspecialchars($project['description']); ?></p>
                            <div class="project-meta flex flex-wrap gap-x-5 gap-y-2 text-sm text-muted-foreground">
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-map-marker-alt h-3.5 w-3.5"></i>
                                    <?php echo htmlspecialchars($project['location']); ?>
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-dollar-sign h-3.5 w-3.5"></i>
                                    <?php echo htmlspecialchars($project['contract_value']); ?>
                                </span>
                                <span class="project-meta-scope flex items-center gap-1.5">
                                    <i class="fas fa-briefcase h-3.5 w-3.5"></i>
                                    <?php echo htmlspecialchars($project['scope']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="project-status-actions flex items-center gap-2 shrink-0">
                            <!-- Status Update -->
                            <select onchange="updateStatus('<?php echo $slug; ?>', this.value)" class="w-36 h-9 text-xs px-2 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring status-dropdown">
                                <option value="completed" <?php echo $project['status'] === 'completed' ? 'selected' : ''; ?>>✓ Completed</option>
                                <option value="in-progress" <?php echo $project['status'] === 'in-progress' ? 'selected' : ''; ?>>⏳ In Progress</option>
                                <option value="planning" <?php echo $project['status'] === 'planning' ? 'selected' : ''; ?>>📅 Planning</option>
                                <option value="on-hold" <?php echo $project['status'] === 'on-hold' ? 'selected' : ''; ?>>⏸ On Hold</option>
                            </select>
                        </div>
                        <div class="project-file-actions flex items-center gap-2 shrink-0 mt-2">
                            <?php if (!empty($project['contract_pdf'])): ?>
                            <?php 
                            $pdf_path = __DIR__ . '/../assets/contracts/' . $project['contract_pdf'];
                            $file_exists = file_exists($pdf_path);
                            ?>
                            <a href="../assets/contracts/<?php echo htmlspecialchars($project['contract_pdf']); ?>" 
                               target="_blank"
                               class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors gap-1.5 inline-flex items-center text-sm">
                                <i class="fas fa-file-pdf h-3.5 w-3.5"></i>
                                Contract
                                <?php if (!$file_exists): ?>
                                <span class="ml-1 text-xs bg-white text-red-600 px-1 rounded">Missing</span>
                                <?php endif; ?>
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="project-main-actions flex items-center gap-2 shrink-0 mt-2">
                            <button onclick="openEditModal('<?php echo $slug; ?>')" class="px-3 py-1.5 bg-background border border-input rounded-lg hover:bg-muted transition-colors gap-1.5 inline-flex items-center text-sm">
                                <i class="fas fa-edit h-3.5 w-3.5"></i>
                                Edit
                            </button>
                            <button onclick="confirmDelete('<?php echo $slug; ?>')" class="px-3 py-1.5 bg-background border border-input rounded-lg hover:bg-muted transition-colors gap-1.5 inline-flex items-center text-sm text-destructive hover:text-destructive">
                                <i class="fas fa-trash h-3.5 w-3.5"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div id="client-no-results" class="hidden text-center py-10 text-muted-foreground">
                <i class="fas fa-search h-10 w-10 mx-auto mb-3 opacity-40"></i>
                <p class="text-base">No projects match your current filter</p>
            </div>

            <div id="pagination-wrapper" class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p id="pagination-summary" class="text-sm text-muted-foreground"></p>
                <div id="pagination-controls" class="flex items-center gap-2"></div>
            </div>
            
            <?php if (empty($projects)): ?>
            <div class="text-center py-16 text-muted-foreground">
                <i class="fas fa-folder-open h-12 w-12 mx-auto mb-3 opacity-40"></i>
                <p class="text-lg">No projects found</p>
                <p class="text-sm mt-1">Create your first project to get started</p>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Add/Edit Project Modal -->
    <div id="project-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-card rounded-xl border border-border max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 id="modal-title" class="text-xl font-heading font-bold text-foreground">Add New Project</h2>
                    <button onclick="closeModal()" class="text-muted-foreground hover:text-foreground">
                        <i class="fas fa-times h-5 w-5"></i>
                    </button>
                </div>
                
                <form id="project-form" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="project-slug" name="project_slug">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-foreground mb-2">Project Title</label>
                            <input type="text" id="title" name="title" required 
                                   class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div>
                            <label for="category" class="block text-sm font-medium text-foreground mb-2">Category</label>
                            <select id="category" name="category" required 
                                    class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                <option value="Residential">Residential</option>
                                <option value="Commercial">Commercial</option>
                                <option value="Industrial">Industrial</option>
                                <option value="Infrastructure">Infrastructure</option>
                                <option value="MEP">MEP</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-foreground mb-2">Status</label>
                            <select id="status" name="status" required 
                                    class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                                <option value="completed">Completed</option>
                                <option value="in-progress">In Progress</option>
                                <option value="planning">Planning</option>
                                <option value="on-hold">On Hold</option>
                            </select>
                        </div>
                        <div>
                            <label for="location" class="block text-sm font-medium text-foreground mb-2">Location</label>
                            <input type="text" id="location" name="location" required 
                                   class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="contract_value" class="block text-sm font-medium text-foreground mb-2">Contract Value</label>
                            <input type="text" id="contract_value" name="contract_value" required 
                                   class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div>
                            <label for="scope" class="block text-sm font-medium text-foreground mb-2">Scope of Work</label>
                            <input type="text" id="scope" name="scope" required 
                                   class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-foreground mb-2">Description</label>
                        <textarea id="description" name="description" rows="4" required 
                                  class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-foreground mb-2">Project Visibility</label>
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center">
                                <input type="checkbox" id="visible" name="visible" value="1" 
                                       class="h-4 w-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                <label for="visible" class="ml-2 text-sm text-foreground">
                                    <i class="fas fa-eye mr-1"></i>
                                    Publish to Website
                                </label>
                            </div>
                            <span class="text-xs text-muted-foreground">
                                (Uncheck to hide from public website)
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="project_image" class="block text-sm font-medium text-foreground mb-2">Main Project Image</label>
                            <input type="file" id="project_image" name="project_image" accept="image/*" 
                                   class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div>
                            <label for="construction_image" class="block text-sm font-medium text-foreground mb-2">Construction Image</label>
                            <input type="file" id="construction_image" name="construction_image" accept="image/*" 
                                   class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="foundation_image" class="block text-sm font-medium text-foreground mb-2">Foundation Image</label>
                            <input type="file" id="foundation_image" name="foundation_image" accept="image/*" 
                                   class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div>
                            <label for="interior_image" class="block text-sm font-medium text-foreground mb-2">Interior Image</label>
                            <input type="file" id="interior_image" name="interior_image" accept="image/*" 
                                   class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                        <div>
                            <label for="architecture_image" class="block text-sm font-medium text-foreground mb-2">Architecture Image</label>
                            <input type="file" id="architecture_image" name="architecture_image" accept="image/*" 
                                   class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="contract_pdf" class="block text-sm font-medium text-foreground mb-2">Project Contract PDF</label>
                        <input type="file" id="contract_pdf" name="contract_pdf" accept=".pdf,application/pdf" 
                               class="w-full px-3 py-2 bg-background border border-input rounded-lg focus:outline-none focus:ring-2 focus:ring-ring">
                        <p class="text-xs text-muted-foreground mt-1">Upload project contract PDF (optional)</p>
                        <div id="current-pdf" class="mt-2 hidden">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm text-muted-foreground">Current PDF:</p>
                                <div class="flex items-center gap-2">
                                    <a href="#" id="current-pdf-link" class="text-sm text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                        <i class="fas fa-file-pdf"></i>
                                        <span id="current-pdf-name"></span>
                                    </a>
                                    <button type="button" id="delete-pdf-btn" onclick="showDeletePDFModal()" 
                                            class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700 transition-colors inline-flex items-center gap-1">
                                        <i class="fas fa-trash h-3 w-3"></i>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeModal()" 
                                class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button type="submit" name="<?php echo !empty($_POST['project_slug']) || !empty($_GET['edit']) ? 'edit_project' : 'add_project'; ?>" 
                                class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            <?php echo !empty($_POST['project_slug']) || !empty($_GET['edit']) ? 'Update Project' : 'Create Project'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-card rounded-xl border border-border max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 bg-destructive/10 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-exclamation-triangle text-destructive"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-heading font-bold text-foreground">Delete Project</h3>
                        <p class="text-muted-foreground text-sm">This action cannot be undone</p>
                    </div>
                </div>
                
                <p class="text-muted-foreground mb-6">Are you sure you want to delete "<span id="delete-project-name"></span>"?</p>
                
                <form id="delete-form" method="post">
                    <input type="hidden" id="delete-project-slug" name="project_slug">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeleteModal()" 
                                class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button type="submit" name="delete_project" 
                                class="px-4 py-2 bg-destructive text-destructive-foreground rounded-lg hover:bg-destructive/90 transition-colors">
                            <i class="fas fa-trash mr-2"></i>
                            Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- PDF Delete Confirmation Modal -->
    <div id="delete-pdf-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-card rounded-xl border border-border max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="h-12 w-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-file-pdf text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-heading font-bold text-foreground">Delete Contract PDF</h3>
                        <p class="text-muted-foreground text-sm">This action cannot be undone</p>
                    </div>
                </div>
                
                <p class="text-muted-foreground mb-6">Are you sure you want to delete the contract PDF "<span id="delete-pdf-name"></span>"?</p>
                
                <form id="delete-pdf-form" method="post">
                    <input type="hidden" id="delete-pdf-project-slug" name="project_slug">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeletePDFModal()" 
                                class="px-4 py-2 bg-background border border-input rounded-lg hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button type="submit" name="delete_contract_pdf" 
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-trash mr-2"></i>
                            Delete PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    
    <script>
        // Global projects data
        const projects = <?php echo json_encode($projects); ?>;
        
        function isMobileLayout() {
            return window.matchMedia('(max-width: 1024px)').matches;
        }

        function closeMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar) sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
        }

        function applyResponsiveSidebarState() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const toggleIcon = document.getElementById('sidebar-toggle-icon');

            if (!sidebar || !mainContent) return;

            if (isMobileLayout()) {
                sidebar.classList.remove('sidebar-collapsed', 'sidebar-expanded');
                mainContent.classList.remove('main-content-collapsed', 'main-content-shifted');
                mainContent.classList.add('main-content-mobile');
                if (toggleIcon) {
                    toggleIcon.classList.remove('fa-chevron-right');
                    toggleIcon.classList.add('fa-chevron-left');
                }
                sidebarTexts.forEach(text => text.style.display = 'block');
                closeMobileSidebar();
            } else {
                sidebar.classList.remove('mobile-open');
                mainContent.classList.remove('main-content-mobile');
                if (!sidebar.classList.contains('sidebar-collapsed')) {
                    sidebar.classList.add('sidebar-expanded');
                    mainContent.classList.remove('main-content-collapsed');
                    mainContent.classList.add('main-content-shifted');
                    if (toggleIcon) {
                        toggleIcon.classList.remove('fa-chevron-right');
                        toggleIcon.classList.add('fa-chevron-left');
                    }
                    sidebarTexts.forEach(text => text.style.display = 'block');
                }
            }
        }

        // Sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const toggleIcon = document.getElementById('sidebar-toggle-icon');
            const overlay = document.getElementById('sidebar-overlay');

            if (isMobileLayout()) {
                sidebar.classList.toggle('mobile-open');
                if (overlay) overlay.classList.toggle('active');
                return;
            }
            
            if (sidebar.classList.contains('sidebar-expanded')) {
                sidebar.classList.remove('sidebar-expanded');
                sidebar.classList.add('sidebar-collapsed');
                mainContent.classList.remove('main-content-shifted');
                mainContent.classList.add('main-content-collapsed');
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
                sidebarTexts.forEach(text => text.style.display = 'none');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');
                mainContent.classList.remove('main-content-collapsed');
                mainContent.classList.add('main-content-shifted');
                toggleIcon.classList.remove('fa-chevron-right');
                toggleIcon.classList.add('fa-chevron-left');
                setTimeout(() => {
                    sidebarTexts.forEach(text => text.style.display = 'block');
                }, 150);
            }
        }
        
        // Dropdown toggle
        function toggleDropdown() {
            const dropdown = document.getElementById('admin-dropdown');
            dropdown.classList.toggle('hidden');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('admin-dropdown');
            if (!event.target.closest('.relative') && !dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        });
        
        // Modal functions
        function openAddModal() {
            document.getElementById('modal-title').textContent = 'Add New Project';
            document.getElementById('project-form').reset();
            document.getElementById('project-slug').value = '';
            
            // Reset submit button text and name
            const submitButton = document.querySelector('button[type="submit"]');
            submitButton.textContent = 'Create Project';
            submitButton.name = 'add_project';
            submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Create Project';
            
            // Hide current PDF display
            document.getElementById('current-pdf').classList.add('hidden');
            
            document.getElementById('project-modal').classList.remove('hidden');
        }
        
        // Contract Management Functions
        let currentProjectSlug = null;
        
        function showDeletePDFModal() {
            const project = projects[currentProjectSlug];
            
            if (project && project.contract_pdf && project.contract_pdf !== '') {
                document.getElementById('delete-pdf-name').textContent = project.contract_pdf;
                document.getElementById('delete-pdf-project-slug').value = currentProjectSlug;
                document.getElementById('delete-pdf-modal').classList.remove('hidden');
            }
        }
        
        function closeDeletePDFModal() {
            document.getElementById('delete-pdf-modal').classList.add('hidden');
        }
        
        // Handle file input change to simply allow upload (no management options)
        document.addEventListener('DOMContentLoaded', function() {
            const contractPdfInput = document.getElementById('contract_pdf');
            if (contractPdfInput) {
                contractPdfInput.addEventListener('change', function() {
                    // When a new file is selected, it will automatically replace any existing PDF
                    // No need for management options - simple upload and replace
                });
            }
        });
        
        // Active navigation tab switching
        document.addEventListener('DOMContentLoaded', function() {
            applyResponsiveSidebarState();
            window.addEventListener('resize', applyResponsiveSidebarState);

            const navItems = document.querySelectorAll('.nav-item');
            const urlParams = new URLSearchParams(window.location.search);
            const page = urlParams.get('page');
            
            // Remove existing active classes
            navItems.forEach(item => {
                item.classList.remove('active');
            });
            
            // Set active based on current page
            navItems.forEach(item => {
                const href = item.getAttribute('href');
                
                // Check for specific pages
                if (page === 'settings' && href.includes('page=settings')) {
                    item.classList.add('active');
                } 
                // Check for dashboard (no page parameter)
                else if (!page && href === 'projects-new.php') {
                    item.classList.add('active');
                }
            });
            
            // Add click handlers for smooth transitions
            navItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    if (isMobileLayout()) {
                        closeMobileSidebar();
                    }
                    // Remove active from all items
                    navItems.forEach(nav => nav.classList.remove('active'));
                    // Add active to clicked item
                    this.classList.add('active');
                });
            });
        });
        
        // Update openEditModal to handle contract management
        function openEditModal(slug) {
            const project = projects[slug];
            currentProjectSlug = slug; // Set current project for contract management
            
            if (project) {
                document.getElementById('modal-title').textContent = 'Edit Project';
                document.getElementById('project-slug').value = slug;
                document.getElementById('title').value = project.title;
                document.getElementById('category').value = project.category;
                document.getElementById('status').value = project.status;
                document.getElementById('location').value = project.location;
                document.getElementById('contract_value').value = project.contract_value;
                document.getElementById('scope').value = project.scope;
                document.getElementById('description').value = project.description;
                
                // Handle visibility checkbox
                const visibleCheckbox = document.getElementById('visible');
                visibleCheckbox.checked = (project.visible === '1' || project.visible === 1);
                
                // Update submit button text and name
                const submitButton = document.querySelector('button[type="submit"]');
                submitButton.textContent = 'Update Project';
                submitButton.name = 'edit_project';
                submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Update Project';
                
                // Handle current PDF display
                const currentPdfDiv = document.getElementById('current-pdf');
                const currentPdfLink = document.getElementById('current-pdf-link');
                const currentPdfName = document.getElementById('current-pdf-name');
                
                if (project.contract_pdf && project.contract_pdf !== '') {
                    currentPdfDiv.classList.remove('hidden');
                    currentPdfLink.href = '../assets/contracts/' + project.contract_pdf;
                    currentPdfLink.target = '_blank';
                    currentPdfName.textContent = project.contract_pdf;
                } else {
                    currentPdfDiv.classList.add('hidden');
                }
                
                document.getElementById('project-modal').classList.remove('hidden');
            }
        }
        
        // Update form submission to handle contract actions
        document.addEventListener('DOMContentLoaded', function() {
            const projectForm = document.getElementById('project-form');
            if (projectForm) {
                projectForm.addEventListener('submit', function(e) {
                    // No need for contract management actions anymore
                    // Simple logic: if new PDF uploaded, it replaces existing
                    // If delete action triggered, it's handled by separate form
                });
            }
        });
        
        // Update dropdown color based on selected status
        function updateDropdownColor(selectElement) {
            const selectedValue = selectElement.value;
            
            // Remove all selected color classes
            selectElement.classList.remove('completed-selected', 'in-progress-selected', 'planning-selected', 'on-hold-selected');
            
            // Add the appropriate selected color class
            switch(selectedValue) {
                case 'completed':
                    selectElement.classList.add('completed-selected');
                    break;
                case 'in-progress':
                    selectElement.classList.add('in-progress-selected');
                    break;
                case 'planning':
                    selectElement.classList.add('planning-selected');
                    break;
                case 'on-hold':
                    selectElement.classList.add('on-hold-selected');
                    break;
            }
        }
        
        // Initialize dropdown colors on page load
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelects = document.querySelectorAll('select[onchange*="updateStatus"]');
            statusSelects.forEach(select => {
                updateDropdownColor(select);
                
                // Add change event listener to update color when selection changes
                select.addEventListener('change', function() {
                    updateDropdownColor(this);
                });
            });
        });
        
        // Update openAddModal to reset contract management
        function openAddModal() {
            currentProjectSlug = null; // Reset for new projects
            document.getElementById('modal-title').textContent = 'Add New Project';
            document.getElementById('project-form').reset();
            document.getElementById('project-slug').value = '';
            
            // Reset submit button text and name
            const submitButton = document.querySelector('button[type="submit"]');
            submitButton.textContent = 'Create Project';
            submitButton.name = 'add_project';
            submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Create Project';
            
            // Hide current PDF display
            document.getElementById('current-pdf').classList.add('hidden');
            
            document.getElementById('project-modal').classList.remove('hidden');
        }
        
        // Show delete confirmation modal
        function confirmDelete(projectSlug) {
            console.log('confirmDelete called with slug:', projectSlug);
            const project = projects[projectSlug];
            if (!project) {
                console.error('Project not found:', projectSlug);
                return;
            }
            
            console.log('Project found:', project.title);
            document.getElementById('delete-project-name').textContent = project.title;
            document.getElementById('delete-project-slug').value = projectSlug;
            document.getElementById('delete-modal').classList.remove('hidden');
        }
        
        // Close delete modal
        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.add('hidden');
        }
        
        // Close project modal
        function closeModal() {
            document.getElementById('project-modal').classList.add('hidden');
        }
        
        // Search, filter, and pagination
        const projectsPerPage = 10;
        let currentProjectsPage = 1;

        function getFilteredProjectCards() {
            const searchInput = document.getElementById('search');
            const statusFilterSelect = document.getElementById('status-filter');
            const projectCards = Array.from(document.querySelectorAll('.project-card'));

            if (!searchInput || !statusFilterSelect || projectCards.length === 0) {
                return [];
            }

            const searchTerm = searchInput.value.toLowerCase().trim();
            const statusFilter = statusFilterSelect.value;

            return projectCards.filter(card => {
                const searchMatch = card.dataset.search.includes(searchTerm);
                const statusMatch = statusFilter === 'all' || card.dataset.status === statusFilter;
                return searchMatch && statusMatch;
            });
        }

        function renderPagination(totalPages) {
            const controls = document.getElementById('pagination-controls');
            if (!controls) return;
            controls.innerHTML = '';

            if (totalPages <= 1) return;

            const createButton = (label, page, disabled = false, active = false) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'pagination-btn' + (active ? ' active' : '');
                btn.textContent = label;
                btn.disabled = disabled;
                if (!disabled) {
                    btn.addEventListener('click', () => {
                        currentProjectsPage = page;
                        filterProjects();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                }
                controls.appendChild(btn);
            };

            createButton('Prev', Math.max(1, currentProjectsPage - 1), currentProjectsPage === 1);

            const startPage = Math.max(1, currentProjectsPage - 2);
            const endPage = Math.min(totalPages, startPage + 4);

            for (let page = startPage; page <= endPage; page++) {
                createButton(String(page), page, false, page === currentProjectsPage);
            }

            createButton('Next', Math.min(totalPages, currentProjectsPage + 1), currentProjectsPage === totalPages);
        }

        function filterProjects(resetPage = false) {
            const allCards = Array.from(document.querySelectorAll('.project-card'));
            if (allCards.length === 0) return;

            if (resetPage) {
                currentProjectsPage = 1;
            }

            const filteredCards = getFilteredProjectCards();
            const totalFiltered = filteredCards.length;
            const totalPages = Math.max(1, Math.ceil(totalFiltered / projectsPerPage));

            if (currentProjectsPage > totalPages) {
                currentProjectsPage = totalPages;
            }

            const start = (currentProjectsPage - 1) * projectsPerPage;
            const end = start + projectsPerPage;
            const visibleCards = new Set(filteredCards.slice(start, end));

            allCards.forEach(card => {
                card.style.display = visibleCards.has(card) ? 'block' : 'none';
            });

            const noResults = document.getElementById('client-no-results');
            if (noResults) {
                noResults.classList.toggle('hidden', totalFiltered !== 0);
            }

            const paginationSummary = document.getElementById('pagination-summary');
            const paginationWrapper = document.getElementById('pagination-wrapper');
            const from = totalFiltered === 0 ? 0 : start + 1;
            const to = Math.min(end, totalFiltered);

            if (paginationSummary) {
                paginationSummary.textContent = totalFiltered === 0
                    ? 'Showing 0 projects'
                    : `Showing ${from}-${to} of ${totalFiltered} projects`;
            }

            if (paginationWrapper) {
                paginationWrapper.classList.toggle('hidden', totalFiltered === 0);
            }

            renderPagination(totalPages);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const statusFilterSelect = document.getElementById('status-filter');

            if (searchInput && statusFilterSelect) {
                searchInput.addEventListener('input', () => filterProjects(true));
                statusFilterSelect.addEventListener('change', () => filterProjects(true));
                filterProjects(true);
            }
        });
        
        // Close modals on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
                closeDeleteModal();
            }
        });
        
        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target === document.getElementById('project-modal')) {
                closeModal();
            }
            if (event.target === document.getElementById('delete-modal')) {
                closeDeleteModal();
            }
        });
        
        // Simple tab switching function
        function showTab(tabName, buttonElement) {
            console.log('Switching to tab:', tabName);
            
            // Hide all tabs
            document.getElementById('general-tab').style.display = 'none';
            document.getElementById('security-tab').style.display = 'none';
            document.getElementById('backup-tab').style.display = 'none';
            
            // Remove active state from all buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(btn => {
                btn.classList.remove('border-primary', 'text-primary');
                btn.classList.add('border-transparent', 'text-muted-foreground');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').style.display = 'block';
            
            // Add active state to clicked button
            buttonElement.classList.remove('border-transparent', 'text-muted-foreground');
            buttonElement.classList.add('border-primary', 'text-primary');
            
            console.log('Tab switched to:', tabName);
        }
        
        // Backup functionality
        function createBackup() {
            window.location.href = 'projects-new.php?action=backup';
        }
        
        function restoreBackup() {
            const fileInput = document.getElementById('backup_file');
            if (fileInput.files.length === 0) {
                alert('Please select a backup file to restore');
                return;
            }
            
            if (confirm('Are you sure you want to restore from backup? This will replace all current project data.')) {
                const formData = new FormData();
                formData.append('backup_file', fileInput.files[0]);
                formData.append('restore_backup', '1');
                
                fetch('projects-new.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.text())
                  .then(data => {
                      window.location.reload();
                  })
                  .catch(error => {
                      console.error('Error:', error);
                      alert('Error restoring backup');
                  });
            }
        }
        
        // Password validation functions
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                eyeIcon.className = 'fas fa-eye';
            }
        }
        
        function validatePassword() {
            const password = document.getElementById('new_password').value;
            const strengthDiv = document.getElementById('password-strength');
            const strengthText = document.getElementById('strength-text');
            const strengthBar = document.getElementById('strength-bar');
            
            // Requirements
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*]/.test(password)
            };
            
            // Update requirement indicators
            updateRequirement('length', requirements.length);
            updateRequirement('uppercase', requirements.uppercase);
            updateRequirement('lowercase', requirements.lowercase);
            updateRequirement('number', requirements.number);
            updateRequirement('special', requirements.special);
            
            // Calculate strength
            const passedRequirements = Object.values(requirements).filter(req => req).length;
            let strength = 0;
            let strengthLabel = '';
            let strengthColor = '';
            
            if (password.length === 0) {
                strengthDiv.classList.add('hidden');
                return;
            }
            
            strengthDiv.classList.remove('hidden');
            
            if (passedRequirements <= 2) {
                strength = 25;
                strengthLabel = 'Weak';
                strengthColor = 'bg-red-500';
            } else if (passedRequirements <= 3) {
                strength = 50;
                strengthLabel = 'Fair';
                strengthColor = 'bg-yellow-500';
            } else if (passedRequirements <= 4) {
                strength = 75;
                strengthLabel = 'Good';
                strengthColor = 'bg-blue-500';
            } else {
                strength = 100;
                strengthLabel = 'Strong';
                strengthColor = 'bg-green-500';
            }
            
            strengthText.textContent = strengthLabel;
            strengthText.className = 'font-medium ' + strengthColor.replace('bg-', 'text-');
            strengthBar.style.width = strength + '%';
            strengthBar.className = 'h-2 rounded-full transition-all duration-300 ' + strengthColor;
        }
        
        function updateRequirement(req, passed) {
            const icon = document.getElementById('req-' + req + '-icon');
            if (passed) {
                icon.className = 'fas fa-check-circle text-green-500 mr-2';
            } else {
                icon.className = 'fas fa-times-circle text-red-500 mr-2';
            }
        }
        
        function validatePasswordMatch() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('password-match');
            const matchText = document.getElementById('match-text');
            
            if (confirmPassword.length === 0) {
                matchDiv.classList.add('hidden');
                return;
            }
            
            matchDiv.classList.remove('hidden');
            
            if (password === confirmPassword) {
                matchText.textContent = '✓ Passwords match';
                matchText.className = 'text-green-600';
            } else {
                matchText.textContent = '✗ Passwords do not match';
                matchText.className = 'text-red-600';
            }
        }
        
        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (newPassword && confirmPassword) {
                confirmPassword.addEventListener('input', function() {
                    if (newPassword.value !== confirmPassword.value) {
                        confirmPassword.setCustomValidity('Passwords do not match');
                    } else {
                        confirmPassword.setCustomValidity('');
                    }
                });
            }
        });
        
        // Security: Activity tracking and auto-logout
        let activityTimer;
        const sessionTimeout = 20 * 60 * 1000; // 20 minutes in milliseconds
        
        function resetActivityTimer() {
            clearTimeout(activityTimer);
            activityTimer = setTimeout(function() {
                // Redirect to login after timeout
                window.location.href = 'projects-new.php?timeout=1';
            }, sessionTimeout);
        }
        
        function updateServerActivity() {
            // Send heartbeat to server to update session activity
            fetch('projects-new.php?heartbeat=1', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
        }
        
        // Track user activity
        document.addEventListener('DOMContentLoaded', function() {
            // Events that reset the timer
            const activityEvents = [
                'mousedown', 'mousemove', 'keypress', 'scroll', 
                'touchstart', 'click', 'keydown'
            ];
            
            activityEvents.forEach(event => {
                document.addEventListener(event, function() {
                    resetActivityTimer();
                    updateServerActivity();
                });
            });
            
            // Initial timer setup
            resetActivityTimer();
            
            // Check for timeout/security parameters
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('timeout') === '1') {
                alert('Your session has expired due to inactivity. Please login again.');
            }
            if (urlParams.get('security') === '1') {
                alert('Security alert: Your session has been terminated for security reasons. Please login again.');
            }
        });
        
        // Handle page visibility change
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                // When page becomes visible, check session validity
                fetch('projects-new.php?check_session=1', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        window.location.href = 'projects-new.php?security=1';
                    }
                })
                .catch(error => {
                    console.error('Session check failed:', error);
                });
            }
        });
    </script>
</body>
</html>
