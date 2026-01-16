# Setup Instructions

## Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL/PostgreSQL or SQLite (with PDO extension)

## Installation Steps

### 1. Install Dependencies
```bash
composer install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Configuration

#### Option A: MySQL/PostgreSQL (Recommended)
Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fsm_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Option B: SQLite
Edit `.env`:
```env
DB_CONNECTION=sqlite
# Remove DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
```

Then create the database file:
```bash
touch database/database.sqlite
```

**Note**: Ensure PHP SQLite extension is installed:
```bash
sudo apt-get install php8.2-sqlite3  # Ubuntu/Debian
```

### 4. Run Migrations
```bash
php artisan migrate
```

### 5. Seed Database
```bash
php artisan db:seed
```

This creates:
- Admin user: `admin@repair.com` / `password`
- Technician user: `tech@repair.com` / `password`
- Customer user: `customer@repair.com` / `password`
- Sample services and checklists

### 6. Start Development Server
```bash
php artisan serve
```

The API will be available at: `http://localhost:8000`

### 7. Start Queue Worker (Required for background jobs)
```bash
php artisan queue:work
```

### 8. Setup Scheduler (Cron)
Add to crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## API Documentation

After setup, access API documentation at:
```
http://localhost:8000/api/documentation
```

## Payment Gateway Configuration

Add your payment gateway credentials to `.env`:

```env
# Razorpay
RAZORPAY_KEY_ID=your_key_id
RAZORPAY_KEY_SECRET=your_key_secret
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret

# PhonePe
PHONEPE_MERCHANT_ID=your_merchant_id
PHONEPE_SALT_KEY=your_salt_key
PHONEPE_SALT_INDEX=1
PHONEPE_SANDBOX=true

# Paytm
PAYTM_MERCHANT_ID=your_merchant_id
PAYTM_MERCHANT_KEY=your_merchant_key
PAYTM_WEBSITE=WEBSTAGING
PAYTM_INDUSTRY_TYPE=Retail
PAYTM_CHANNEL_ID=WEB
PAYTM_SANDBOX=true
```

## Troubleshooting

### PHP Version Issue
If you see "PHP version >= 8.2.0 required", use PHP 8.2 explicitly:
```bash
php8.2 artisan migrate
php8.2 artisan serve
```

### SQLite Driver Not Found
Install SQLite extension:
```bash
sudo apt-get install php8.2-sqlite3
```

Or switch to MySQL/PostgreSQL in `.env`.

### Migration Errors
If you encounter enum errors with MySQL, you may need to modify the migration to use string columns instead of enums for better compatibility.

## Testing the API

### Register a Customer
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password",
    "password_confirmation": "password",
    "role": "customer"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@repair.com",
    "password": "password"
  }'
```

## Next Steps

1. Configure payment gateway webhooks in their dashboards
2. Set up file storage (local or S3)
3. Configure email settings for notifications
4. Set up production environment variables
5. Configure queue driver (Redis recommended for production)
