# Hostinger Setup Guide

Complete step-by-step guide to deploy the Field Service Management System on Hostinger hosting.

## 📋 Prerequisites

Before starting, ensure you have:
- ✅ Hostinger hosting account (Shared/VPS/Cloud)
- ✅ PHP 8.2 or higher enabled
- ✅ MySQL database created
- ✅ FTP/SFTP access credentials
- ✅ SSH access (if available - recommended for easier setup)

## 🔧 Step 1: Prepare Your Local Environment

### 1.1 Install Dependencies Locally
```bash
# Navigate to your project directory
cd /path/to/your/project

# Install all dependencies
composer install --no-dev --optimize-autoloader
```

### 1.2 Optimize for Production
```bash
# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📤 Step 2: Upload Files to Hostinger

### 2.1 Files to Upload
Upload the entire project to your Hostinger `public_html` directory (or subdirectory if needed).

**Important**: Upload these files/folders:
- ✅ `app/`
- ✅ `bootstrap/`
- ✅ `config/`
- ✅ `database/`
- ✅ `public/`
- ✅ `resources/`
- ✅ `routes/`
- ✅ `storage/`
- ✅ `vendor/` (if uploaded, otherwise install via SSH)
- ✅ `.env` (create this - see Step 3)
- ✅ `artisan`
- ✅ `composer.json`
- ✅ `composer.lock`

**Do NOT upload**:
- ❌ `.git/`
- ❌ `node_modules/`
- ❌ `.env.example` (optional)
- ❌ `tests/`
- ❌ `storage/logs/*.log` (keep the folder, but not log files)

### 2.2 Recommended Upload Method
- **Option A (Recommended)**: Use SSH and `rsync` or `scp`
- **Option B**: Use FTP/SFTP client (FileZilla, WinSCP, etc.)
- **Option C**: Use Git (if SSH access available)

## 🗄️ Step 3: Database Setup

### 3.1 Create Database via Hostinger Control Panel
1. Log in to Hostinger Control Panel (hPanel)
2. Navigate to **Databases** → **MySQL Databases**
3. Create a new database (e.g., `u123456789_fsm`)
4. Create a new database user
5. Grant all privileges to the user
6. Note down:
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost` or `127.0.0.1`)

### 3.2 Database Configuration
The database host might be different on Hostinger. Common values:
- `localhost`
- `127.0.0.1`
- `mysql.hostinger.com`
- Check your Hostinger control panel for the exact hostname

## ⚙️ Step 4: Environment Configuration

### 4.1 Create `.env` File
Create a `.env` file in your project root with the following configuration:

```env
APP_NAME="Field Service Management"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_fsm
DB_USERNAME=u123456789_dbuser
DB_PASSWORD=your_secure_password

# Session & Cache
SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_DRIVER=file
QUEUE_CONNECTION=database

# Mail Configuration (Update with your SMTP settings)
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Payment Gateways (Configure as needed)
RAZORPAY_KEY_ID=
RAZORPAY_KEY_SECRET=
RAZORPAY_WEBHOOK_SECRET=

PHONEPE_MERCHANT_ID=
PHONEPE_SALT_KEY=
PHONEPE_SALT_INDEX=1
PHONEPE_SANDBOX=false

PAYTM_MERCHANT_ID=
PAYTM_MERCHANT_KEY=
PAYTM_WEBSITE=WEBSTAGING
PAYTM_INDUSTRY_TYPE=Retail
PAYTM_CHANNEL_ID=WEB
PAYTM_SANDBOX=false

# Google Maps API (if using technician map)
GOOGLE_MAPS_API_KEY=

# API Documentation
API_VERSION=1.0.0
```

### 4.2 Generate Application Key
Via SSH (if available):
```bash
cd /home/u123456789/domains/yourdomain.com/public_html
php artisan key:generate
```

Or manually add to `.env`:
```env
APP_KEY=base64:your_generated_key_here
```

## 🔐 Step 5: File Permissions

### 5.1 Set Correct Permissions
Via SSH:
```bash
cd /home/u123456789/domains/yourdomain.com/public_html

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Storage and cache directories need write permissions
chmod -R 775 storage bootstrap/cache
chown -R u123456789:u123456789 storage bootstrap/cache
```

**Note**: Replace `u123456789` with your actual Hostinger username.

### 5.2 Create Storage Symlink
```bash
php artisan storage:link
```

## 🗃️ Step 6: Database Migration

### 6.1 Run Migrations
Via SSH:
```bash
cd /home/u123456789/domains/yourdomain.com/public_html
php artisan migrate --force
```

### 6.2 Seed Database
```bash
php artisan db:seed --force
```

This creates:
- Admin user: `admin@repair.com` / `password`
- Sample services, checklists, and components

**⚠️ Important**: Change default passwords after first login!

## 🌐 Step 7: Configure Web Server

### 7.1 Update Document Root
If your Laravel app is in a subdirectory, update the document root in Hostinger control panel to point to the `public` folder.

**Example**:
- Current: `/public_html/`
- Should be: `/public_html/public/`

### 7.2 Create/Update `.htaccess` in Root
If your app is in root directory, create `.htaccess` in root:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### 7.3 Verify `public/.htaccess`
Ensure `public/.htaccess` exists and contains:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## ⏰ Step 8: Setup Cron Jobs

### 8.1 Add Cron Job via Hostinger Control Panel
1. Log in to Hostinger Control Panel
2. Navigate to **Advanced** → **Cron Jobs**
3. Add a new cron job:

**Cron Schedule**: `* * * * *` (every minute)

**Command**:
```bash
cd /home/u123456789/domains/yourdomain.com/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Note**: Replace paths with your actual paths. Check PHP path via SSH:
```bash
which php
```

### 8.2 Queue Worker (Alternative Methods)

**Option A: Via Cron (Recommended for Shared Hosting)**
Add another cron job:
```bash
*/5 * * * * cd /home/u123456789/domains/yourdomain.com/public_html && /usr/bin/php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

**Option B: Via Supervisor (VPS/Cloud Hosting Only)**
If you have VPS/Cloud hosting with Supervisor access, create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/u123456789/domains/yourdomain.com/public_html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=u123456789
numprocs=1
redirect_stderr=true
stdout_logfile=/home/u123456789/domains/yourdomain.com/public_html/storage/logs/worker.log
stopwaitsecs=3600
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## 🔧 Step 9: Install Composer Dependencies (If Not Uploaded)

If you didn't upload the `vendor/` folder:

### 9.1 Via SSH
```bash
cd /home/u123456789/domains/yourdomain.com/public_html

# Check if Composer is available
composer --version

# If not installed, install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install dependencies
composer install --no-dev --optimize-autoloader
```

## 🚀 Step 10: Final Optimizations

### 10.1 Clear and Cache Everything
Via SSH:
```bash
cd /home/u123456789/domains/yourdomain.com/public_html

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 10.2 Optimize Autoloader
```bash
composer install --optimize-autoloader --no-dev
```

## ✅ Step 11: Verify Installation

### 11.1 Test URLs
1. **Admin Panel**: `https://yourdomain.com/admin/login`
   - Email: `admin@repair.com`
   - Password: `password` (change immediately!)

2. **API Documentation**: `https://yourdomain.com/docs/api`

3. **API Endpoint**: `https://yourdomain.com/api/admin/dashboard`

### 11.2 Check Logs
```bash
tail -f storage/logs/laravel.log
```

## 🔒 Step 12: Security Checklist

- [ ] Change default admin password
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Verify `.env` file is not publicly accessible
- [ ] Enable HTTPS/SSL certificate
- [ ] Update `APP_URL` to your actual domain
- [ ] Configure proper file permissions (755 for dirs, 644 for files)
- [ ] Review and update payment gateway credentials
- [ ] Set up proper email configuration
- [ ] Enable firewall rules if available

## 🐛 Troubleshooting

### Issue: 500 Internal Server Error
**Solution**:
1. Check `storage/logs/laravel.log` for errors
2. Verify file permissions: `chmod -R 775 storage bootstrap/cache`
3. Check `.env` file exists and has correct values
4. Verify `APP_KEY` is set: `php artisan key:generate`

### Issue: Database Connection Error
**Solution**:
1. Verify database credentials in `.env`
2. Check database host (might not be `localhost` on Hostinger)
3. Ensure database user has proper permissions
4. Test connection via SSH: `php artisan tinker` then `DB::connection()->getPdo();`

### Issue: Storage Files Not Accessible
**Solution**:
1. Create symlink: `php artisan storage:link`
2. Check permissions: `chmod -R 775 storage`
3. Verify `public/storage` directory exists

### Issue: Queue Jobs Not Running
**Solution**:
1. Verify cron job is set up correctly
2. Check cron logs in Hostinger control panel
3. Test manually: `php artisan queue:work --once`
4. For shared hosting, use cron-based queue processing

### Issue: Composer Not Found
**Solution**:
1. Install Composer globally or use local `composer.phar`
2. Check PHP path: `which php`
3. Use full path in cron jobs: `/usr/bin/php` or `/opt/alt/php82/usr/bin/php`

## 📞 Support Resources

- **Hostinger Support**: https://www.hostinger.com/contact
- **Laravel Documentation**: https://laravel.com/docs
- **Application Logs**: `storage/logs/laravel.log`

## 📝 Post-Installation Tasks

1. **Change Default Passwords**
   - Admin: `admin@repair.com`
   - Update via admin panel or database

2. **Configure Payment Gateways**
   - Add Razorpay, PhonePe, Paytm credentials
   - Test payment flows

3. **Setup Email**
   - Configure SMTP settings
   - Test email notifications

4. **Configure White Labeling**
   - Upload logo
   - Set brand colors
   - Configure company information

5. **Add Google Maps API Key**
   - For technician map functionality
   - Add to settings in admin panel

6. **Review Settings**
   - Timezone configuration
   - Currency settings
   - Upload image quality
   - Payment timeout settings

## 🎉 Installation Complete!

Your Field Service Management System should now be live on Hostinger!

**Access Points**:
- Admin Panel: `https://yourdomain.com/admin/login`
- API Documentation: `https://yourdomain.com/docs/api`
- API Base URL: `https://yourdomain.com/api`

---

**Need Help?** Check the troubleshooting section or contact support.
