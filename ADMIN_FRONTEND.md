# Admin Frontend Documentation

## Overview

A complete Laravel Blade-based admin panel has been created for managing the Field Service Management system. The admin panel provides a web interface for all administrative tasks.

## Features

### ✅ Authentication
- Login page with session-based authentication
- Logout functionality
- Role-based access control (admin only)

### ✅ Dashboard
- Real-time statistics (pending triage, active jobs, on-duty technicians, revenue)
- Revenue chart (last 7 days)
- Recent tickets and jobs list
- Modern, responsive design

### ✅ Triage Management
- View all pending tickets in queue
- Real-time countdown timers
- Assign tickets to technicians
- Reject tickets
- Auto-refresh every 30 seconds

### ✅ Service Catalog
- List all services
- Create new services
- Edit existing services
- Delete services
- Filter by device type and category

### ✅ Technician Management
- View all technicians with status
- See active jobs count
- View total revenue per technician
- Live map view (requires Google Maps API key)
- Individual revenue reports

### ✅ Settings
- White-label configuration (app name, logo, colors)
- Workflow settings (timeouts, photo requirements, tax rate)

## Access

### Login URL
```
http://localhost:8000/admin/login
```

### Default Credentials
- **Email**: `admin@repair.com`
- **Password**: `password`

## Routes

All admin routes are prefixed with `/admin`:

- `GET /admin/login` - Login page
- `POST /admin/login` - Login action
- `POST /admin/logout` - Logout
- `GET /admin/dashboard` - Dashboard
- `GET /admin/triage` - Triage queue
- `POST /admin/triage/{id}/assign` - Assign ticket
- `POST /admin/triage/{id}/reject` - Reject ticket
- `GET /admin/services` - Service list
- `GET /admin/services/create` - Create service
- `POST /admin/services` - Store service
- `GET /admin/services/{id}/edit` - Edit service
- `PUT /admin/services/{id}` - Update service
- `DELETE /admin/services/{id}` - Delete service
- `GET /admin/technicians` - Technician list
- `GET /admin/technicians/map` - Live map
- `GET /admin/technicians/{id}/revenue` - Revenue report
- `GET /admin/settings` - Settings page
- `POST /admin/settings/white-label` - Update white-label
- `POST /admin/settings/workflow` - Update workflow

## Design

- **Framework**: Laravel Blade with Tailwind CSS
- **Layout**: Sidebar navigation with main content area
- **Responsive**: Mobile-friendly design
- **Charts**: Chart.js for revenue visualization
- **Maps**: Google Maps API (requires API key configuration)

## Google Maps Setup

To enable the live map feature:

1. Get a Google Maps API key from [Google Cloud Console](https://console.cloud.google.com/)
2. Edit `resources/views/admin/technicians/map.blade.php`
3. Replace `YOUR_API_KEY` with your actual API key

## File Structure

```
resources/views/
├── layouts/
│   └── app.blade.php          # Main layout with sidebar
├── auth/
│   └── login.blade.php        # Login page
└── admin/
    ├── dashboard.blade.php    # Dashboard with stats
    ├── triage/
    │   └── index.blade.php     # Triage queue
    ├── services/
    │   ├── index.blade.php     # Service list
    │   ├── create.blade.php   # Create service
    │   └── edit.blade.php      # Edit service
    ├── technicians/
    │   ├── index.blade.php     # Technician list
    │   ├── map.blade.php       # Live map
    │   └── revenue.blade.php   # Revenue report
    └── settings/
        └── index.blade.php     # Settings page

app/Http/Controllers/Admin/
├── AuthController.php          # Authentication
├── DashboardController.php     # Dashboard
├── TriageController.php        # Triage management
├── ServiceController.php       # Service CRUD
├── TechnicianController.php    # Technician management
└── SettingsController.php      # Settings
```

## Security

- All admin routes are protected by `auth` middleware
- Additional `role:admin` middleware ensures only admins can access
- CSRF protection on all forms
- Session-based authentication

## Next Steps

1. **Configure Google Maps API** for live map feature
2. **Customize colors** via white-label settings
3. **Add more charts** to dashboard if needed
4. **Export functionality** for reports
5. **Email notifications** integration

## Notes

- The admin panel uses the same database as the API
- All changes made in admin panel are reflected in API
- API endpoints remain available for mobile/Next.js frontend
- Both admin panel and API can coexist
