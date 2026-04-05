<?php
/**
 * Admin Project Management Dashboard
 * User-friendly interface for managing projects without coding
 */

// Start session
session_start();

// Simple authentication (you can enhance this later)
$admin_password = 'diar360_admin_2024'; // Change this to a secure password
$is_authenticated = false;

// Check authentication
if (isset($_POST['login']) && $_POST['password'] === $admin_password) {
    $_SESSION['admin_authenticated'] = true;
    $is_authenticated = true;
} elseif (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true) {
    $is_authenticated = true;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_start();
    session_destroy();
    unset($_SESSION['admin_authenticated']);
    $is_authenticated = false;
    
    // Redirect to login page
    header('Location: projects.php');
    exit;
}

// Include project data and status management
require_once __DIR__ . '/../config/projects-data.php';
require_once __DIR__ . '/../config/project-status.php';

// Handle form submissions
if ($is_authenticated && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle status updates
    if (isset($_POST['update_status'])) {
        $project_slug = $_POST['project_slug'];
        $new_status = $_POST['status'];
        
        // Update project status in data array
        if (isset($projects[$project_slug])) {
            $projects[$project_slug]['status'] = $new_status;
            
            // Save to file
            updateProjectsData($projects);
            
            // Force page refresh to show updated status
            header('Location: projects.php?success=1');
            exit;
        }
    }
    
    // Handle image-only editing
    if (isset($_POST['edit_images_only'])) {
        $project_slug = $_POST['project_slug'];
        
        if (isset($projects[$project_slug])) {
            // Handle image uploads only
            $image_fields = [
                'project_image' => $project_slug . '.webp',
                'construction_image' => $project_slug . '-construction.webp',
                'foundation_image' => $project_slug . '-foundation.webp',
                'interior_image' => $project_slug . '-interior.webp',
                'architecture_image' => $project_slug . '-architecture.webp'
            ];
            
            foreach ($image_fields as $field_name => $file_name) {
                if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === 0) {
                    $image_tmp = $_FILES[$field_name]['tmp_name'];
                    $image_path = __DIR__ . '/../assets/img/projects/' . $file_name;
                    move_uploaded_file($image_tmp, $image_path);
                }
            }
            
            // Force page refresh to show updated images
            header('Location: projects.php?images_updated=1');
            exit;
        }
    }
    
    // Handle project editing
    if (isset($_POST['edit_project'])) {
        $project_slug = $_POST['project_slug'];
        
        if (isset($projects[$project_slug])) {
            // Update project data
            $projects[$project_slug] = [
                'title' => $_POST['title'],
                'category' => $_POST['category'],
                'status' => $_POST['status'],
                'location' => $_POST['location'],
                'contract_value' => $_POST['contract_value'],
                'scope' => $_POST['scope'],
                'description' => $_POST['description'],
                'specs' => [
                    'Scope of Work' => $_POST['scope'],
                    'Contract Value' => $_POST['contract_value'],
                    'Location' => $_POST['location'],
                    'Status' => $_POST['status'],
                ],
            ];
            
            // Save to file
            updateProjectsData($projects);
            
            // Handle image uploads
            $image_fields = [
                'project_image' => $project_slug . '.webp',
                'construction_image' => $project_slug . '-construction.webp',
                'foundation_image' => $project_slug . '-foundation.webp',
                'interior_image' => $project_slug . '-interior.webp',
                'architecture_image' => $project_slug . '-architecture.webp'
            ];
            
            foreach ($image_fields as $field_name => $file_name) {
                if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === 0) {
                    $image_tmp = $_FILES[$field_name]['tmp_name'];
                    $image_path = __DIR__ . '/../assets/img/projects/' . $file_name;
                    move_uploaded_file($image_tmp, $image_path);
                }
            }
            
            // Force page refresh to show updated project
            header('Location: projects.php?edited=1');
            exit;
        }
    }
    
    // Handle project deletion
    if (isset($_POST['delete_project'])) {
        $project_slug = $_POST['project_slug'];
        
        // Delete project from array
        if (isset($projects[$project_slug])) {
            unset($projects[$project_slug]);
            
            // Save to file
            updateProjectsData($projects);
            
            // Delete project images if they exist
            $image_files = [
                __DIR__ . '/../assets/img/projects/' . $project_slug . '.webp',
                __DIR__ . '/../assets/img/projects/' . $project_slug . '-construction.webp',
                __DIR__ . '/../assets/img/projects/' . $project_slug . '-foundation.webp',
                __DIR__ . '/../assets/img/projects/' . $project_slug . '-interior.webp',
                __DIR__ . '/../assets/img/projects/' . $project_slug . '-architecture.webp'
            ];
            
            foreach ($image_files as $image_file) {
                if (file_exists($image_file)) {
                    unlink($image_file);
                }
            }
            
            // Force page refresh to show updated project list
            header('Location: projects.php?deleted=1');
            exit;
        }
    }
    
    // Handle new project addition
    if (isset($_POST['add_project'])) {
        $new_project = [
            'title' => $_POST['title'],
            'category' => $_POST['category'],
            'status' => $_POST['status'],
            'location' => $_POST['location'],
            'contract_value' => $_POST['contract_value'],
            'scope' => $_POST['scope'],
            'description' => $_POST['description'],
            'specs' => [
                'Scope of Work' => $_POST['scope'],
                'Contract Value' => $_POST['contract_value'],
                'Location' => $_POST['location'],
                'Status' => ucfirst(str_replace('-', ' ', $_POST['status']))
            ]
        ];
        
        // Generate slug from title
        $slug = strtolower(str_replace(' ', '-', $_POST['title']));
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        
        // Add to projects array
        $projects[$slug] = $new_project;
        
        // Save to file
        updateProjectsData($projects);
        
        $success_message = "New project added successfully!";
        
        // Handle image uploads
        $image_fields = [
            'project_image' => $slug . '.webp',
            'construction_image' => $slug . '-construction.webp',
            'foundation_image' => $slug . '-foundation.webp',
            'interior_image' => $slug . '-interior.webp',
            'architecture_image' => $slug . '-architecture.webp'
        ];
        
        foreach ($image_fields as $field_name => $file_name) {
            if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === 0) {
                $image_tmp = $_FILES[$field_name]['tmp_name'];
                $image_path = __DIR__ . '/../assets/img/projects/' . $file_name;
                move_uploaded_file($image_tmp, $image_path);
            }
        }
    }
}

function updateProjectsData($projects) {
    // Write updated projects data back to the actual file
    $data = "<?php\n/**\n * Projects Data\n * Contains all project details from Diar 360 Company Profile\n */\n\n";
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
    
    $data .= "];\n\n";
    $data .= "// Get project slug from URL\n\$project_slug = isset(\$_GET['project']) ? \$_GET['project'] : 'lamar-towers';\n\n";
    $data .= "// Get project data or default to first project\n\$project = isset(\$projects[\$project_slug]) ? \$projects[\$project_slug] : \$projects['lamar-towers'];\n\n";
    $data .= "// Helper function to get status class\nfunction getStatusClass(\$status) {\n    \$statusInfo = getProjectStatusInfo(\$status);\n    return \$statusInfo['class'];\n}\n\n";
    $data .= "// Helper function to get status label\nfunction getStatusLabel(\$status) {\n    \$statusInfo = getProjectStatusInfo(\$status);\n    return \$statusInfo['label'];\n}\n\n";
    $data .= "// Helper function to get status icon\nfunction getStatusIcon(\$status) {\n    \$statusInfo = getProjectStatusInfo(\$status);\n    return \$statusInfo['icon'];\n}\n\n?>\n";
    
    // Save to the actual projects data file
    $file_path = __DIR__ . '/../config/projects-data.php';
    file_put_contents($file_path, $data);
    
    return true;
}

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management - Diar360 Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }
        .admin-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        .sidebar {
            position: fixed;
            top: 80px;
            left: 0;
            width: 250px;
            height: calc(100vh - 80px);
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            transition: width 0.3s ease;
            z-index: 1040;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        .sidebar.collapsed {
            width: 70px;
        }
        .sidebar.collapsed .sidebar-title span,
        .sidebar.collapsed .sidebar-item span {
            display: none;
        }
        .sidebar.collapsed .sidebar-header {
            padding: 2rem 0.5rem;
            text-align: center;
        }
        .sidebar.collapsed .sidebar-item {
            padding: 0.75rem 0.5rem;
            text-align: center;
            border-left: none;
            border-bottom: 3px solid transparent;
        }
        .sidebar.collapsed .sidebar-item:hover {
            border-left: none;
            border-bottom-color: #667eea;
        }
        .sidebar.collapsed .sidebar-item.active {
            border-left: none;
            border-bottom-color: #667eea;
        }
        .sidebar.collapsed .sidebar-item i {
            margin-right: 0;
            font-size: 1.25rem;
        }
        .sidebar.collapsed hr {
            display: none;
        }
        .sidebar-header {
            padding: 2rem 1rem;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sidebar-menu {
            padding: 1rem 0;
        }
        .sidebar-item {
            display: block;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        .sidebar-item:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #667eea;
        }
        .sidebar-item.active {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            border-left-color: #667eea;
        }
        .sidebar-item i {
            margin-right: 0.75rem;
            width: 20px;
        }
        .menu-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .menu-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1035;
            display: none;
        }
        }
        .main-content {
            margin-top: 80px;
            margin-left: 250px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
            min-height: calc(100vh - 80px);
        }
        .main-content.sidebar-collapsed {
            margin-left: 70px;
        }
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
                width: 250px;
                height: 100vh;
                top: 0;
            }
            .sidebar.active {
                left: 0;
            }
            .sidebar.collapsed {
                left: -250px;
            }
            .main-content {
                margin-left: 0;
                margin-top: 0;
            }
            .main-content.sidebar-collapsed {
                margin-left: 0;
            }
            .admin-header {
                position: relative;
            }
        }
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .project-card {
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .project-card:hover {
            transform: translateY(-5px);
        }
        .project-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-inprogress {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-planning {
            background-color: #cfe2ff;
            color: #0d6efd;
        }
        .status-onhold {
            background-color: #f8d7da;
            color: #721c24;
        }
        .dashboard-stats {
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: #102A49;
            border: none;
        }
        .btn-primary:hover {
            background: #263F5B;
        }
        .project-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <?php if (!$is_authenticated): ?>
    <!-- Login Form -->
    <div class="container">
        <div class="login-form">
            <div class="text-center mb-4">
                <h2><i class="bi bi-building"></i> Diar 360 Admin</h2>
                <p class="text-muted">Project Management Dashboard</p>
            </div>
            <div class="form-section">
                <form method="post">
                    <div class="mb-3">
                        <label for="password" class="form-label">Admin Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100">
                        <i class="bi bi-lock-fill"></i> Login
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Admin Dashboard -->
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4 class="sidebar-title">
                <i class="bi bi-gear-fill"></i>
                <span>Admin Panel</span>
            </h4>
        </div>
        <nav class="sidebar-menu">
            <a href="projects.php" class="sidebar-item active">
                <i class="bi bi-folder-fill"></i>
                <span>Projects</span>
            </a>
            <a href="#" class="sidebar-item">
                <i class="bi bi-bar-chart-fill"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="sidebar-item">
                <i class="bi bi-people-fill"></i>
                <span>Team</span>
            </a>
            <a href="#" class="sidebar-item">
                <i class="bi bi-calendar-fill"></i>
                <span>Schedule</span>
            </a>
            <a href="#" class="sidebar-item">
                <i class="bi bi-file-earmark-text-fill"></i>
                <span>Reports</span>
            </a>
            <a href="#" class="sidebar-item">
                <i class="bi bi-gear-fill"></i>
                <span>Settings</span>
            </a>
            <hr style="border-color: rgba(255,255,255,0.1); margin: 1rem 0;">
            <a href="../index.php" class="sidebar-item">
                <i class="bi bi-house-fill"></i>
                <span>View Website</span>
            </a>
            <a href="?logout=true" class="sidebar-item">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Header -->
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <button class="menu-toggle" id="menuToggle">
                            <i class="bi bi-list"></i>
                        </button>
                        <div class="ms-3">
                            <h1 class="mb-0"><i class="bi bi-building"></i> Diar 360 Project Management</h1>
                            <p class="mb-0">Manage your projects and update statuses</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex align-items-center justify-content-end">
                        <span class="text-white me-3">Welcome, Admin</span>
                        <div class="dropdown">
                            <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="?logout=true"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> Project status updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-trash-fill"></i> Project deleted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['images_updated'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-image-fill"></i> Project images updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['edited'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-pencil-fill"></i> Project updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Edit Images Modal -->
        <div class="modal fade" id="editImagesModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-image-fill"></i> Edit Project Images</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="post" enctype="multipart/form-data" id="editImagesForm">
                        <div class="modal-body">
                            <input type="hidden" name="project_slug" id="edit_images_project_slug">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_images_main" class="form-label">Main Project Image</label>
                                    <div class="current-image mb-2">
                                        <small class="text-muted">Current:</small>
                                        <img id="current_main_image" src="" alt="Current main image" style="max-width: 100px; height: 60px; object-fit: cover;" class="img-thumbnail">
                                    </div>
                                    <input type="file" class="form-control" id="edit_images_main" name="project_image" accept="image/*">
                                    <small class="text-muted">Upload new image to replace current</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_images_construction" class="form-label">Construction Phase Image</label>
                                    <div class="current-image mb-2">
                                        <small class="text-muted">Current:</small>
                                        <img id="current_construction_image" src="" alt="Current construction image" style="max-width: 100px; height: 60px; object-fit: cover;" class="img-thumbnail">
                                    </div>
                                    <input type="file" class="form-control" id="edit_images_construction" name="construction_image" accept="image/*">
                                    <small class="text-muted">Upload new image to replace current</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_images_foundation" class="form-label">Foundation Image</label>
                                    <div class="current-image mb-2">
                                        <small class="text-muted">Current:</small>
                                        <img id="current_foundation_image" src="" alt="Current foundation image" style="max-width: 100px; height: 60px; object-fit: cover;" class="img-thumbnail">
                                    </div>
                                    <input type="file" class="form-control" id="edit_images_foundation" name="foundation_image" accept="image/*">
                                    <small class="text-muted">Upload new image to replace current</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_images_interior" class="form-label">Interior Design Image</label>
                                    <div class="current-image mb-2">
                                        <small class="text-muted">Current:</small>
                                        <img id="current_interior_image" src="" alt="Current interior image" style="max-width: 100px; height: 60px; object-fit: cover;" class="img-thumbnail">
                                    </div>
                                    <input type="file" class="form-control" id="edit_images_interior" name="interior_image" accept="image/*">
                                    <small class="text-muted">Upload new image to replace current</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_images_architecture" class="form-label">Architecture Image</label>
                                    <div class="current-image mb-2">
                                        <small class="text-muted">Current:</small>
                                        <img id="current_architecture_image" src="" alt="Current architecture image" style="max-width: 100px; height: 60px; object-fit: cover;" class="img-thumbnail">
                                    </div>
                                    <input type="file" class="form-control" id="edit_images_architecture" name="architecture_image" accept="image/*">
                                    <small class="text-muted">Upload new image to replace current</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="edit_images_only" class="btn btn-info">
                                <i class="bi bi-image-fill"></i> Update Images
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Edit Project Modal -->
        <div class="modal fade" id="editProjectModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil-fill"></i> Edit Project</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="post" enctype="multipart/form-data" id="editProjectForm">
                        <div class="modal-body">
                            <input type="hidden" name="project_slug" id="edit_project_slug">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_title" class="form-label">Project Title</label>
                                    <input type="text" class="form-control" id="edit_title" name="title" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_category" class="form-label">Category</label>
                                    <select class="form-select" id="edit_category" name="category" required>
                                        <option value="Residential">Residential</option>
                                        <option value="Commercial">Commercial</option>
                                        <option value="Industrial">Industrial</option>
                                        <option value="Infrastructure">Infrastructure</option>
                                        <option value="MEP">MEP</option>
                                        <option value="Government">Government</option>
                                        <option value="Towers">Towers</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_status" class="form-label">Status</label>
                                    <select class="form-select" id="edit_status" name="status" required>
                                        <option value="completed">Completed</option>
                                        <option value="in-progress">In Progress</option>
                                        <option value="planning">Planning</option>
                                        <option value="on-hold">On Hold</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="edit_location" name="location" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_contract_value" class="form-label">Contract Value</label>
                                    <input type="text" class="form-control" id="edit_contract_value" name="contract_value" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_scope" class="form-label">Scope of Work</label>
                                    <input type="text" class="form-control" id="edit_scope" name="scope" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="4" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_project_image" class="form-label">Main Project Image</label>
                                    <input type="file" class="form-control" id="edit_project_image" name="project_image" accept="image/*">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_construction_image" class="form-label">Construction Phase Image</label>
                                    <input type="file" class="form-control" id="edit_construction_image" name="construction_image" accept="image/*">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_foundation_image" class="form-label">Foundation Image</label>
                                    <input type="file" class="form-control" id="edit_foundation_image" name="foundation_image" accept="image/*">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_interior_image" class="form-label">Interior Design Image</label>
                                    <input type="file" class="form-control" id="edit_interior_image" name="interior_image" accept="image/*">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_architecture_image" class="form-label">Architecture Image</label>
                                    <input type="file" class="form-control" id="edit_architecture_image" name="architecture_image" accept="image/*">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="edit_project" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Dashboard Statistics -->
        <div class="dashboard-stats">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stat-card bg-primary">
                        <div class="stat-icon">
                            <i class="bi bi-folder-fill"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number"><?php echo count($projects); ?></h3>
                            <p class="stat-label">Total Projects</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-success">
                        <div class="stat-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">
                                <?php 
                                $completed = array_filter($projects, function($p) { 
                                    return $p['status'] === 'completed'; 
                                });
                                echo count($completed); 
                                ?>
                            </h3>
                            <p class="stat-label">Completed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-warning">
                        <div class="stat-icon">
                            <i class="bi bi-clock-fill"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">
                                <?php 
                                $in_progress = array_filter($projects, function($p) { 
                                    return $p['status'] === 'in-progress'; 
                                });
                                echo count($in_progress); 
                                ?>
                            </h3>
                            <p class="stat-label">In Progress</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-info">
                        <div class="stat-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">
                                <?php 
                                $locations = array_unique(array_column($projects, 'location'));
                                echo count($locations); 
                                ?>
                            </h3>
                            <p class="stat-label">Locations</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Add Project Button -->
        <div class="text-center mb-4">
            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                <i class="bi bi-plus-circle-fill"></i> Add New Project
            </button>
        </div>
        
        <!-- Add Project Modal -->
        <div class="modal fade" id="addProjectModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle-fill"></i> Add New Project</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="post" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_title" class="form-label">Project Title</label>
                                    <input type="text" class="form-control" id="add_title" name="title" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_category" class="form-label">Category</label>
                                    <select class="form-select" id="add_category" name="category" required>
                                        <option value="Residential">Residential</option>
                                        <option value="Commercial">Commercial</option>
                                        <option value="Industrial">Industrial</option>
                                        <option value="Infrastructure">Infrastructure</option>
                                        <option value="MEP">MEP</option>
                                        <option value="Government">Government</option>
                                        <option value="Towers">Towers</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_status" class="form-label">Status</label>
                                    <select class="form-select" id="add_status" name="status" required>
                                        <option value="completed">Completed</option>
                                        <option value="in-progress">In Progress</option>
                                        <option value="planning">Planning</option>
                                        <option value="on-hold">On Hold</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="add_location" name="location" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_contract_value" class="form-label">Contract Value</label>
                                    <input type="text" class="form-control" id="add_contract_value" name="contract_value" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_scope" class="form-label">Scope of Work</label>
                                    <input type="text" class="form-control" id="add_scope" name="scope" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="add_description" class="form-label">Description</label>
                                <textarea class="form-control" id="add_description" name="description" rows="4" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_project_image" class="form-label">Main Project Image</label>
                                    <input type="file" class="form-control" id="add_project_image" name="project_image" accept="image/*">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_construction_image" class="form-label">Construction Phase Image</label>
                                    <input type="file" class="form-control" id="add_construction_image" name="construction_image" accept="image/*">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_foundation_image" class="form-label">Foundation Image</label>
                                    <input type="file" class="form-control" id="add_foundation_image" name="foundation_image" accept="image/*">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="add_interior_image" class="form-label">Interior Design Image</label>
                                    <input type="file" class="form-control" id="add_interior_image" name="interior_image" accept="image/*">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="add_architecture_image" class="form-label">Architecture Image</label>
                                    <input type="file" class="form-control" id="add_architecture_image" name="architecture_image" accept="image/*">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="add_project" class="btn btn-primary">
                                <i class="bi bi-plus-circle-fill"></i> Add Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Projects List -->
        <div class="form-section">
            <h3><i class="bi bi-list-ul"></i> Current Projects</h3>
            <div class="row">
                <?php foreach ($projects as $slug => $project): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card project-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="../assets/img/construction/project-1.webp" alt="<?php echo htmlspecialchars($project['title']); ?>" class="project-image me-3">
                                <div>
                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($project['title']); ?></h5>
                                    <small class="text-muted"><?php echo htmlspecialchars($project['category']); ?></small>
                                </div>
                            </div>
                            <p class="card-text small"><?php echo htmlspecialchars(substr($project['description'], 0, 100)) . '...'; ?></p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="status-badge status-<?php echo str_replace('-', '', $project['status']); ?>">
                                    <?php echo ucfirst(str_replace('-', ' ', $project['status'])); ?>
                                </span>
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt-fill"></i> <?php echo htmlspecialchars($project['location']); ?>
                                </small>
                            </div>
                            <div class="project-actions mb-3">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-gear-fill"></i> Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="editProject('<?php echo $slug; ?>')">
                                                <i class="bi bi-pencil-fill text-primary"></i> Edit
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="confirmDelete('<?php echo $slug; ?>')">
                                                <i class="bi bi-trash-fill"></i> Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <form method="post" id="status-form-<?php echo $slug; ?>">
                                <input type="hidden" name="project_slug" value="<?php echo $slug; ?>">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="completed" <?php echo $project['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="in-progress" <?php echo $project['status'] === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="planning" <?php echo $project['status'] === 'planning' ? 'selected' : ''; ?>>Planning</option>
                                            <option value="on-hold" <?php echo $project['status'] === 'on-hold' ? 'selected' : ''; ?>>On Hold</option>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <button type="submit" name="update_status" class="btn btn-sm btn-primary w-100">
                                            <i class="bi bi-check"></i> Update
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <?php endif; ?>

    <script>
    // Sidebar toggle functionality
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const mainContent = document.getElementById('mainContent');

    menuToggle.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            // Mobile: Toggle sidebar visibility
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        } else {
            // Desktop: Toggle sidebar collapse
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
        }
    });

    overlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            // Desktop: Reset mobile states
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        } else {
            // Mobile: Reset desktop collapse state
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
    });

    // Initialize sidebar state
    if (window.innerWidth > 768) {
        // Sidebar is always open on desktop
        mainContent.style.marginLeft = '250px';
    }

    function viewProject(slug) {
        // Open project details page in new tab
        window.open('project-details.php?project=' + slug, '_blank');
    }

    function editProject(slug) {
        // Load project data from PHP array
        const projects = <?php echo json_encode($projects); ?>;
        const project = projects[slug];
        
        if (project) {
            // Populate modal with project data
            document.getElementById('edit_project_slug').value = slug;
            document.getElementById('edit_title').value = project.title;
            document.getElementById('edit_category').value = project.category;
            document.getElementById('edit_status').value = project.status;
            document.getElementById('edit_location').value = project.location;
            document.getElementById('edit_contract_value').value = project.contract_value;
            document.getElementById('edit_scope').value = project.scope;
            document.getElementById('edit_description').value = project.description;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editProjectModal'));
            modal.show();
        }
    }

    function editImages(slug) {
        // Set project slug
        document.getElementById('edit_images_project_slug').value = slug;
        
        // Load current images
        const baseUrl = '../assets/img/projects/';
        const imageTypes = [
            { id: 'current_main_image', filename: slug + '.webp' },
            { id: 'current_construction_image', filename: slug + '-construction.webp' },
            { id: 'current_foundation_image', filename: slug + '-foundation.webp' },
            { id: 'current_interior_image', filename: slug + '-interior.webp' },
            { id: 'current_architecture_image', filename: slug + '-architecture.webp' }
        ];
        
        imageTypes.forEach(imageType => {
            const imgElement = document.getElementById(imageType.id);
            imgElement.src = baseUrl + imageType.filename;
            imgElement.onerror = function() {
                this.src = '../assets/img/construction/project-1.webp';
            };
        });
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editImagesModal'));
        modal.show();
    }

    function confirmDelete(slug) {
        if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
            // Create and submit form for deletion
            const form = document.createElement('form');
            form.method = 'post';
            form.innerHTML = '<input type="hidden" name="project_slug" value="' + slug + '"><input type="hidden" name="delete_project" value="1">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.display = 'none';
            }, 5000);
        });
    });
    </script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
