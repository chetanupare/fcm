#!/bin/bash

# Start Laravel Development Server
# Port 8000 is often used by CUPS, so we use 8001

echo "Starting Laravel development server..."
echo "Access: http://localhost:8001/admin/login"
echo ""
echo "Default credentials:"
echo "  Email: admin@repair.com"
echo "  Password: password"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

cd "$(dirname "$0")"
php8.2 artisan serve --port=8001 --host=127.0.0.1
