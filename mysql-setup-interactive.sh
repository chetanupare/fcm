#!/bin/bash

echo "=== Interactive MySQL Setup ==="
echo ""

PHP_CMD="php8.2"
if ! command -v $PHP_CMD &> /dev/null; then
    PHP_CMD="php"
fi

echo "This script will help you configure MySQL for the FSM application."
echo ""

# Check current .env
if [ -f .env ]; then
    CURRENT_USER=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2)
    CURRENT_PASS=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2)
    echo "Current MySQL configuration:"
    echo "  Username: ${CURRENT_USER:-not set}"
    echo "  Password: ${CURRENT_PASS:-not set}"
    echo ""
fi

echo "Please provide MySQL credentials:"
read -p "MySQL Username [root]: " MYSQL_USER
MYSQL_USER=${MYSQL_USER:-root}

read -sp "MySQL Password: " MYSQL_PASS
echo ""

# Test connection
echo "Testing MySQL connection..."
mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" -e "SELECT 1;" > /dev/null 2>&1

if [ $? -ne 0 ]; then
    echo "❌ Connection failed. Please check your credentials."
    exit 1
fi

echo "✅ Connection successful!"
echo ""

# Create database
echo "Creating database 'fsm_db'..."
mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" -e "CREATE DATABASE IF NOT EXISTS fsm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1

if [ $? -eq 0 ]; then
    echo "✅ Database created!"
else
    echo "❌ Failed to create database"
    exit 1
fi

# Update .env
echo ""
echo "Updating .env file..."
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
sed -i "s/^DB_USERNAME=.*/DB_USERNAME=$MYSQL_USER/" .env || echo "DB_USERNAME=$MYSQL_USER" >> .env
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$MYSQL_PASS/" .env || echo "DB_PASSWORD=$MYSQL_PASS" >> .env
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=fsm_db/' .env || echo "DB_DATABASE=fsm_db" >> .env
sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env || echo "DB_HOST=127.0.0.1" >> .env
sed -i 's/^DB_PORT=.*/DB_PORT=3306/' .env || echo "DB_PORT=3306" >> .env

echo "✅ .env updated"
echo ""

# Run migrations
echo "Running migrations..."
$PHP_CMD artisan migrate --force

if [ $? -eq 0 ]; then
    echo "✅ Migrations completed!"
    echo ""
    echo "Seeding database..."
    $PHP_CMD artisan db:seed --force
    
    if [ $? -eq 0 ]; then
        echo "✅ Database seeded!"
        echo ""
        echo "=========================================="
        echo "✅ Setup Complete!"
        echo ""
        echo "Start the server with:"
        echo "  $PHP_CMD artisan serve"
        echo ""
    fi
else
    echo "❌ Migrations failed"
    exit 1
fi
