# MySQL Setup Instructions

## Quick Setup

I've created setup scripts to help you configure MySQL. Choose one:

### Option 1: Interactive Setup (Recommended)
```bash
./mysql-setup-interactive.sh
```
This will prompt you for MySQL username and password, then automatically:
- Create the database
- Update .env file
- Run migrations
- Seed the database

### Option 2: Automatic Setup
```bash
./setup-mysql.sh
```
This tries common MySQL configurations automatically.

### Option 3: Manual Setup

#### Step 1: Create Database
```bash
mysql -u root -p
```

Then in MySQL:
```sql
CREATE DATABASE fsm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

#### Step 2: Update .env
Edit `.env` and set:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fsm_db
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

#### Step 3: Run Migrations
```bash
php8.2 artisan migrate --force
php8.2 artisan db:seed --force
```

## Current Configuration

Your `.env` is currently set to:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fsm_db
DB_USERNAME=root
DB_PASSWORD=
```

**Note**: The password is empty. If your MySQL root user requires a password, you need to:
1. Update `DB_PASSWORD` in `.env` with your MySQL root password
2. Or create the database manually first
3. Then run migrations

## After Setup

Once migrations and seeding complete:

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

### "Access denied for user 'root'@'localhost'"
Your MySQL root user requires a password. Either:
1. Run the interactive setup script: `./mysql-setup-interactive.sh`
2. Or manually update `DB_PASSWORD` in `.env`

### "Unknown database 'fsm_db'"
The database doesn't exist. Create it:
```bash
mysql -u root -p -e "CREATE DATABASE fsm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### MySQL Service Not Running
Start MySQL service:
```bash
sudo systemctl start mysql
# or
sudo systemctl start mysqld
```
