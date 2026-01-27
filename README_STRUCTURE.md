# Project Structure

This document explains the professional folder structure of the Diar360 project.

## Folder Structure

```
diar360/
├── assets/              # Static assets (CSS, JS, images, vendor files)
├── config/              # Configuration files
│   ├── config.php       # Site-wide configuration and constants
│   └── database.php     # Database configuration
├── database/            # Database related files
│   └── connection.php   # Database connection class (PDO)
├── forms/               # Form handlers
│   ├── contact.php      # Contact form handler
│   └── get-a-quote.php  # Quote form handler
├── functions/           # Reusable PHP functions
│   ├── functions.php     # Common utility functions
│   └── language.php     # Language/translation functions
├── fonts/               # Custom font files (if any)
├── include/             # Reusable PHP includes
│   ├── header.php       # Site header and navigation
│   └── footer.php       # Site footer and scripts
├── language/            # Language files for internationalization
│   ├── en.php           # English translations
│   └── ar.php           # Arabic translations
└── [PHP files]          # Main page files (index.php, about.php, etc.)
```

## Configuration Files

### config/config.php
Contains all site-wide settings:
- Site name, email, phone
- Social media links
- Path constants
- Timezone settings
- Error reporting settings

### config/database.php
Database connection credentials:
- Database host, name, user, password
- Character set
- Table prefix (if needed)

## Database

### database/connection.php
PDO-based database connection class using Singleton pattern:
- Secure connection handling
- Error handling
- Reusable connection instance

## Functions

### functions/functions.php
Common utility functions:
- Input sanitization
- Email/phone validation
- Page navigation helpers
- Breadcrumb generation
- Form data helpers

### functions/language.php
Multi-language support:
- Language switching
- Translation functions
- RTL language detection

## Includes

### include/header.php
- HTML head section
- Site header
- Navigation menu
- Uses config for dynamic content

### include/footer.php
- Footer content
- JavaScript files
- Uses config for dynamic content

## Language Files

### language/en.php & language/ar.php
Translation arrays for:
- Navigation items
- Form labels
- Common text
- Error messages

## Usage

### In PHP Files

```php
<?php
// Include header (loads config and functions automatically)
require_once __DIR__ . '/include/header.php';
?>

<!-- Your page content here -->

<?php
// Include footer
require_once __DIR__ . '/include/footer.php';
?>
```

### Using Configuration

```php
echo SITE_NAME;        // Outputs: Constructo
echo SITE_EMAIL;       // Outputs: info@example.com
echo ASSETS_PATH;      // Outputs: /assets
```

### Using Functions

```php
$clean = sanitize($_POST['name']);
$isValid = validateEmail($email);
$currentPage = getCurrentPage();
$pageTitle = getPageTitle();
```

### Using Database

```php
require_once __DIR__ . '/database/connection.php';
$db = getDB();
$stmt = $db->prepare("SELECT * FROM table WHERE id = ?");
$stmt->execute([$id]);
$result = $stmt->fetch();
```

### Using Translations

```php
echo t('nav_home');        // Outputs: Home (or الرئيسية in Arabic)
echo t('contact_title');   // Outputs: Contact (or اتصل بنا in Arabic)
```

## Best Practices

1. **Always use includes** for header and footer
2. **Use config constants** instead of hardcoding values
3. **Sanitize all user input** using the sanitize() function
4. **Use prepared statements** for database queries
5. **Use translation functions** for all user-facing text
6. **Keep functions organized** in the functions/ directory

## Notes

- All paths use `__DIR__` for reliability
- Configuration is loaded automatically in header.php
- Database connection uses Singleton pattern for efficiency
- Language files support easy addition of new languages
