#!/bin/bash

echo "=== Database Setup Script ==="
echo ""

# Check PHP version
PHP_CMD="php8.2"
if ! command -v $PHP_CMD &> /dev/null; then
    PHP_CMD="php"
fi

echo "Using: $PHP_CMD"
echo ""

# Check available database drivers
echo "Checking available database drivers..."
DRIVERS=$($PHP_CMD -r "echo implode(', ', PDO::getAvailableDrivers());")
echo "Available PDO drivers: $DRIVERS"
echo ""

# Try SQLite first (easiest for development)
if echo "$DRIVERS" | grep -q "sqlite"; then
    echo "✅ SQLite driver found!"
    echo "Configuring SQLite..."
    
    # Update .env for SQLite
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
    sed -i '/^DB_HOST=/d; /^DB_PORT=/d; /^DB_DATABASE=/d; /^DB_USERNAME=/d; /^DB_PASSWORD=/d' .env
    
    # Create database file
    touch database/database.sqlite
    chmod 664 database/database.sqlite
    
    echo "✅ SQLite database created"
    echo ""
    echo "Running migrations..."
    $PHP_CMD artisan migrate --force
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "✅ Migrations completed!"
        echo ""
        echo "Seeding database..."
        $PHP_CMD artisan db:seed --force
        echo ""
        echo "✅ Database setup complete!"
        exit 0
    fi
fi

# Try MySQL if SQLite failed
if echo "$DRIVERS" | grep -q "mysql"; then
    echo "⚠️  SQLite not available, trying MySQL..."
    echo ""
    echo "Please configure MySQL in .env file:"
    echo "  DB_CONNECTION=mysql"
    echo "  DB_HOST=127.0.0.1"
    echo "  DB_PORT=3306"
    echo "  DB_DATABASE=fsm_db"
    echo "  DB_USERNAME=your_username"
    echo "  DB_PASSWORD=your_password"
    echo ""
    echo "Then create the database:"
    echo "  mysql -u your_username -p -e 'CREATE DATABASE fsm_db;'"
    echo ""
    echo "After that, run:"
    echo "  $PHP_CMD artisan migrate --force"
    echo "  $PHP_CMD artisan db:seed --force"
fi

echo ""
echo "Setup incomplete. Please configure database manually."
