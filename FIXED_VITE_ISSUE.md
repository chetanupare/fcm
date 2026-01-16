# Fixed: Vite Manifest Error

## Issue
The application was returning 404 errors because:
1. **Vite manifest not found** - Views were using `@vite()` directive but assets weren't built
2. **Setting::get() calls** - Some views were calling database methods that could fail

## Fixes Applied

### 1. Replaced Vite with CDN
- Changed `@vite(['resources/css/app.css', 'resources/js/app.js'])` to CDN links
- Now using Tailwind CSS and Alpine.js from CDN

### 2. Fixed Setting Calls
- Changed `\App\Models\Setting::get('app_name', 'FSM')` to `config('app.name', 'FSM')`
- This avoids database queries in views

## Files Updated
- `resources/views/auth/login.blade.php`
- `resources/views/layouts/app.blade.php`

## Access
- **URL**: `http://localhost:8001/admin/login`
- **Credentials**: 
  - Email: `admin@repair.com`
  - Password: `password`

## Note
If you still see 404, make sure:
1. Server is running: `php8.2 artisan serve --port=8001`
2. Routes are cleared: `php8.2 artisan route:clear`
3. Views are cleared: `php8.2 artisan view:clear`
