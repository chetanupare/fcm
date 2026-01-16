# How to Start the Laravel Server

## Quick Start

Run this command in your terminal:

```bash
cd /home/user/Music/fcm
php8.2 artisan serve --port=8001
```

**Important:** 
- Do NOT add a colon (`:`) at the end
- The server will run in the foreground (you'll see output)
- Press `Ctrl+C` to stop it

## Alternative: Use the Script

```bash
cd /home/user/Music/fcm
./start-server.sh
```

## Verify Server is Running

After starting, you should see:
```
INFO  Server running on [http://127.0.0.1:8001]
```

## Access the Admin Panel

Once the server is running:
- **URL**: http://localhost:8001/admin/login
- **Email**: admin@repair.com
- **Password**: password

## Troubleshooting

### Connection Refused Error
If you get "ERR_CONNECTION_REFUSED":
1. Make sure the server is actually running
2. Check if port 8001 is available: `lsof -i :8001`
3. Try a different port: `php8.2 artisan serve --port=8002`

### Port Already in Use
If port 8001 is busy:
```bash
# Kill any existing Laravel servers
pkill -f "php.*artisan serve"

# Then start fresh
php8.2 artisan serve --port=8001
```

### Check Server Status
```bash
# See if server is running
ps aux | grep "php.*artisan serve"

# Check what's on port 8001
lsof -i :8001
```
