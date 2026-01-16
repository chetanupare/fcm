#!/bin/bash

echo "🚀 Quick Start Script for FSM Application"
echo "=========================================="
echo ""

PHP_CMD="php8.2"
if ! command -v $PHP_CMD &> /dev/null; then
    PHP_CMD="php"
    echo "⚠️  Using default php command. Ensure it's PHP 8.2+"
else
    echo "✅ Using PHP 8.2"
fi

echo ""

# Step 1: Check database
echo "Step 1: Checking database configuration..."
DB_CONNECTION=$(grep "^DB_CONNECTION=" .env | cut -d '=' -f2)

if [ "$DB_CONNECTION" = "sqlite" ]; then
    if [ -f "database/database.sqlite" ]; then
        echo "✅ SQLite database exists"
    else
        touch database/database.sqlite
        chmod 664 database/database.sqlite
        echo "✅ SQLite database created"
    fi
elif [ "$DB_CONNECTION" = "mysql" ]; then
    echo "⚠️  MySQL configured - ensure database 'fsm_db' exists"
fi

echo ""

# Step 2: Run migrations
echo "Step 2: Running migrations..."
$PHP_CMD artisan migrate --force 2>&1 | tail -5

if [ ${PIPESTATUS[0]} -eq 0 ]; then
    echo "✅ Migrations completed"
    
    # Step 3: Seed database
    echo ""
    echo "Step 3: Seeding database..."
    $PHP_CMD artisan db:seed --force 2>&1 | tail -5
    
    if [ ${PIPESTATUS[0]} -eq 0 ]; then
        echo "✅ Database seeded"
        echo ""
        echo "=========================================="
        echo "✅ Setup Complete!"
        echo ""
        echo "Default credentials:"
        echo "  Admin: admin@repair.com / password"
        echo "  Technician: tech@repair.com / password"
        echo "  Customer: customer@repair.com / password"
        echo ""
        echo "To start the server:"
        echo "  $PHP_CMD artisan serve"
        echo ""
        echo "To start queue worker (in another terminal):"
        echo "  $PHP_CMD artisan queue:work"
        echo ""
        echo "API Documentation:"
        echo "  http://localhost:8000/api/documentation"
        echo ""
    else
        echo "⚠️  Seeding failed - check database connection"
    fi
else
    echo "⚠️  Migrations failed - check database configuration"
    echo ""
    echo "For SQLite, install extension:"
    echo "  sudo apt-get install php8.2-sqlite3"
    echo ""
    echo "For MySQL, ensure database exists and credentials are correct in .env"
fi
