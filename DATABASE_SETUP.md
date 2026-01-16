# Database Setup Guide

## Current Status

The application needs a database to run. You have two options:

## Option 1: SQLite (Easiest - Recommended for Development)

### Install SQLite Extension
```bash
sudo apt-get install php8.2-sqlite3
```

### Configure
The `.env` file is already configured for SQLite. Just run:
```bash
php8.2 artisan migrate --force
php8.2 artisan db:seed --force
```

## Option 2: MySQL (Recommended for Production)

### 1. Create Database
```bash
mysql -u root -p
```

Then in MySQL:
```sql
CREATE DATABASE fsm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 2. Update .env
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fsm_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Run Migrations
```bash
php8.2 artisan migrate --force
php8.2 artisan db:seed --force
```

## Quick Setup Script

I've created a setup script for you:

```bash
# Make it executable (already done)
chmod +x quick-start.sh

# Run it
./quick-start.sh
```

This script will:
1. Check database configuration
2. Run migrations
3. Seed the database
4. Show you how to start the server

## After Database Setup

Once migrations and seeding are complete:

```bash
# Start development server
php8.2 artisan serve

# In another terminal, start queue worker
php8.2 artisan queue:work
```

## Default Users (after seeding)

- **Admin**: `admin@repair.com` / `password`
- **Technician**: `tech@repair.com` / `password`
- **Customer**: `customer@repair.com` / `password`

## Troubleshooting

### SQLite: "could not find driver"
Install SQLite extension:
```bash
sudo apt-get install php8.2-sqlite3
```

### MySQL: "Access denied"
1. Check MySQL is running: `sudo systemctl status mysql`
2. Verify credentials in `.env`
3. Ensure database exists: `mysql -u root -p -e "SHOW DATABASES;"`

### Migration Errors
Clear cache and try again:
```bash
php8.2 artisan config:clear
php8.2 artisan cache:clear
php8.2 artisan migrate --force
```
