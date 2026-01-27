# How to Access Your Website Locally

## Quick Start Guide

### Step 1: Start XAMPP

1. Open **XAMPP Control Panel**
2. Start **Apache** server (click "Start" button)
3. (Optional) Start **MySQL** if you need database functionality

### Step 2: Access Your Website

Open your web browser and navigate to:

```
http://localhost/diar360/
```

Or specifically:

```
http://localhost/diar360/index.php
```

### Alternative URLs

If you want to access without the `/diar360/` path, you can:

1. **Create a Virtual Host** (recommended for production-like setup)
2. **Move project to htdocs root** (not recommended)
3. **Use the full path** as shown above

## Available Pages

Once the server is running, you can access:

- **Homepage**: `http://localhost/diar360/` or `http://localhost/diar360/index.php`
- **About**: `http://localhost/diar360/about.php`
- **Contact**: `http://localhost/diar360/contact.php`
- **Services**: `http://localhost/diar360/services.php`
- **Projects**: `http://localhost/diar360/projects.php`
- **Team**: `http://localhost/diar360/team.php`
- **Quote**: `http://localhost/diar360/quote.php`
- **Terms**: `http://localhost/diar360/terms.php`
- **Privacy**: `http://localhost/diar360/privacy.php`

## Troubleshooting

### Issue: "404 Not Found" or "Page Not Found"

**Solution:**
- Make sure Apache is running in XAMPP Control Panel
- Check that your project is in `C:\xampp\htdocs\diar360\`
- Try: `http://localhost/diar360/index.php` (with full filename)

### Issue: "PHP Parse Error" or "Fatal Error"

**Solution:**
- Check XAMPP error logs: `C:\xampp\apache\logs\error.log`
- Make sure PHP is enabled in XAMPP
- Verify all include files exist in the correct locations

### Issue: "Database Connection Failed"

**Solution:**
- Start MySQL in XAMPP Control Panel
- Update `config/database.php` with correct credentials:
  - Default XAMPP: User: `root`, Password: (empty)
- Create the database if it doesn't exist

### Issue: CSS/Images Not Loading

**Solution:**
- Check that `ASSETS_PATH` in `config/config.php` is set to `/assets`
- Verify the `assets/` folder exists in your project root
- Clear browser cache (Ctrl+F5)

## Setting Up Database (Optional)

If you want to use the database features:

1. Start **MySQL** in XAMPP Control Panel
2. Open **phpMyAdmin**: `http://localhost/phpmyadmin`
3. Create a new database (e.g., `diar360_db`)
4. Update `config/database.php`:
   ```php
   define('DB_NAME', 'diar360_db');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Empty for default XAMPP
   ```

## Testing Your Setup

1. **Check Apache is running**: Visit `http://localhost/` - you should see XAMPP dashboard
2. **Check your site**: Visit `http://localhost/diar360/` - you should see your homepage
3. **Check PHP is working**: Create a test file `test.php` with `<?php phpinfo(); ?>` and visit it

## Quick Test

Create a file `test.php` in your project root:

```php
<?php
require_once __DIR__ . '/config/config.php';
echo "Site Name: " . SITE_NAME . "<br>";
echo "Site Email: " . SITE_EMAIL . "<br>";
echo "PHP Version: " . phpversion();
?>
```

Visit: `http://localhost/diar360/test.php`

If you see the site information, everything is working! ✅

## Port Conflicts

If Apache won't start (usually port 80 or 443 conflict):

1. Check what's using the port
2. Change Apache port in XAMPP Control Panel → Config → Apache → httpd.conf
3. Update URL to: `http://localhost:8080/diar360/` (if using port 8080)

## Next Steps

Once your site is accessible:

1. ✅ Test all pages
2. ✅ Check forms are working
3. ✅ Verify database connection (if using)
4. ✅ Test multi-language support
5. ✅ Customize content in config files

---

**Your website should now be accessible at: `http://localhost/diar360/`** 🚀
