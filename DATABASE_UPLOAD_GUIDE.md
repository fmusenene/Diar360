# Database Upload Guide for Diar360

## 🚨 URGENT: Upload These Files to Your Hosted Server

Your image edits aren't reflecting because the database files are missing on your hosted server.

### Files to Upload (Copy from localhost to hosted server):

**📁 Upload entire `/config/` folder:**
```
config/
├── projects-data.php (26KB) - MOST IMPORTANT - All project data
├── admin-settings.php (1.7KB) - Admin settings
├── team-data.php (12KB) - Team members
├── project-status.php (3.5KB) - Status definitions
├── careers-data.php (7KB) - Job listings
├── testimonials-data.php (5KB) - Customer testimonials
├── login_attempts.json (2B) - Security log
└── .htaccess (78B) - Security protection
```

### Upload Methods:

**Method 1: FTP/SFTP**
1. Connect to your hosted server
2. Navigate to your Diar360 root directory
3. Upload the entire `/config/` folder
4. Set permissions: 644 for PHP files, 755 for folders

**Method 2: File Manager (cPanel)**
1. Open cPanel → File Manager
2. Go to your Diar360 installation
3. Upload each file from `/config/` folder
4. Overwrite existing files if they exist

**Method 3: Git Push (if using Git)**
```bash
git add config/
git commit -m "Upload database files to production"
git push origin main
```

### After Upload:
1. **Test image editing** - Try adding/editing a project
2. **Verify images appear** - Check if uploaded images show on frontend
3. **Test admin panel** - Ensure all project data loads correctly

### ⚠️ Important Notes:
- **Backup first** - Save current hosted files before overwriting
- **Check permissions** - Ensure PHP can read/write config files
- **Sync changes** - After uploading, any localhost changes need to be re-uploaded

### Why This Fixes the Issue:
- Project images upload ✅ (files save to assets/img/projects/)
- Database updates ✅ (changes save to config/projects-data.php)
- Frontend displays ✅ (reads from database files)
- Admin panel works ✅ (can read/write project data)

## Result:
After uploading database files, your image edits will immediately start reflecting on the hosted website!
