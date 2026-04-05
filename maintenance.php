<?php
/**
 * Maintenance Mode Page
 */

// Load admin settings for dynamic contact information
$admin_settings_file = __DIR__ . '/config/admin-settings.php';
$site_settings = [];
$admin_password = 'diar360_admin_2024'; // Default fallback

if (file_exists($admin_settings_file)) {
    include $admin_settings_file;
}

// Use admin settings if available, otherwise fall back to defaults
$admin_email = isset($site_settings['admin_email']) ? $site_settings['admin_email'] : 'info@diar360.com';
$company_phone = isset($site_settings['company_phone']) ? $site_settings['company_phone'] : '+966 1 1 296 7735';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - Diar360</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Domine:wght@400;500;600;700&family=Arimo:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&family=El+Messiri:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Arimo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .maintenance-container {
            animation: fadeIn 1s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .gear {
            animation: spin 4s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="maintenance-container max-w-2xl w-full text-center text-white">
            <!-- Maintenance Icon -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white/20 rounded-full backdrop-blur-sm">
                    <i class="fas fa-cogs text-4xl gear"></i>
                </div>
            </div>
            
            <!-- Maintenance Message -->
            <h1 class="text-4xl md:text-5xl font-bold mb-4 font-heading">
                Under Maintenance
            </h1>
            
            <p class="text-xl md:text-2xl mb-8 opacity-90">
                i love you suzan
            </p>
            
            <!-- Details -->
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-8 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                    <div>
                        <i class="fas fa-tools text-3xl mb-3"></i>
                        <h3 class="font-semibold mb-2">What We're Doing</h3>
                        <p class="text-sm opacity-80">
                            System upgrades and performance improvements
                        </p>
                    </div>
                    <div>
                        <i class="fas fa-clock text-3xl mb-3"></i>
                        <h3 class="font-semibold mb-2">Expected Time</h3>
                        <p class="text-sm opacity-80">
                            A few hours only
                        </p>
                    </div>
                    <div>
                        <i class="fas fa-shield-alt text-3xl mb-3"></i>
                        <h3 class="font-semibold mb-2">Your Data</h3>
                        <p class="text-sm opacity-80">
                            All your data is safe and secure
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6">
                <h3 class="font-semibold mb-4">Need Assistance?</h3>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="tel:<?php echo str_replace(' ', '', $company_phone); ?>" class="flex items-center gap-2 hover:opacity-80 transition">
                        <i class="fas fa-phone"></i>
                        <?php echo htmlspecialchars($company_phone); ?>
                    </a>
                    <a href="mailto:<?php echo htmlspecialchars($admin_email); ?>" class="flex items-center gap-2 hover:opacity-80 transition">
                        <i class="fas fa-envelope"></i>
                        <?php echo htmlspecialchars($admin_email); ?>
                    </a>
                </div>
            </div>
            
            <!-- Admin Access -->
            <div class="mt-8 text-sm opacity-70">
                <p>Administrator? <a href="admin/projects-new.php" class="underline hover:opacity-80 transition">Access Admin Panel</a></p>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-refresh every 5 minutes
        setTimeout(function() {
            window.location.reload();
        }, 300000);
    </script>
</body>
</html>
