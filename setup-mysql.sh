#!/bin/bash

echo "=== MySQL Database Setup ==="
echo ""

# Check if MySQL is running
if ! systemctl is-active --quiet mysql 2>/dev/null && ! systemctl is-active --quiet mysqld 2>/dev/null; then
    echo "⚠️  MySQL service status unknown. Continuing anyway..."
fi

echo "This script will help you set up the MySQL database."
echo ""

# Try to detect MySQL users
echo "Attempting to create database..."
echo ""

# Method 1: Try with common passwords
COMMON_PASSWORDS=("root" "" "password" "123456")
DB_CREATED=false

for pass in "${COMMON_PASSWORDS[@]}"; do
    if [ -z "$pass" ]; then
        mysql -u root -e "CREATE DATABASE IF NOT EXISTS fsm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
    else
        mysql -u root -p"$pass" -e "CREATE DATABASE IF NOT EXISTS fsm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
    fi
    
    if [ $? -eq 0 ]; then
        echo "✅ Database 'fsm_db' created successfully!"
        DB_CREATED=true
        break
    fi
done

if [ "$DB_CREATED" = false ]; then
    echo "❌ Could not create database automatically."
    echo ""
    echo "Please create the database manually:"
    echo ""
    echo "Option 1: Using MySQL command line"
    echo "  mysql -u root -p"
    echo "  Then run: CREATE DATABASE fsm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    echo ""
    echo "Option 2: Update .env with correct MySQL credentials"
    echo "  Edit .env and set:"
    echo "    DB_USERNAME=your_mysql_user"
    echo "    DB_PASSWORD=your_mysql_password"
    echo ""
    echo "Then run this script again or manually run:"
    echo "  php8.2 artisan migrate --force"
    echo "  php8.2 artisan db:seed --force"
    exit 1
fi

# Update .env with MySQL password if we found it
if [ ! -z "$MYSQL_PASS" ]; then
    sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$MYSQL_PASS/" .env
fi

echo ""
echo "Running migrations..."
php8.2 artisan migrate --force

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Migrations completed!"
    echo ""
    echo "Seeding database..."
    php8.2 artisan db:seed --force
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "✅ Database seeded successfully!"
        echo ""
        echo "=========================================="
        echo "✅ MySQL Setup Complete!"
        echo ""
        echo "Default users created:"
        echo "  Admin: admin@repair.com / password"
        echo "  Technician: tech@repair.com / password"
        echo "  Customer: customer@repair.com / password"
        echo ""
        echo "To start the server:"
        echo "  php8.2 artisan serve"
        echo ""
        echo "To start queue worker:"
        echo "  php8.2 artisan queue:work"
    else
        echo "⚠️  Seeding failed, but migrations completed"
    fi
else
    echo "⚠️  Migrations failed. Please check MySQL credentials in .env"
fi
