# Field Service Management System - Laravel Backend

A comprehensive Field Service Management (FSM) system built with Laravel 11, designed specifically for Electronics & Appliance Repair shops. This system automates the entire workflow from customer booking to job completion, eliminating dispatch bottlenecks.

## Features

### Core Workflow Automation
- **Automated Triage System**: Tickets automatically move to assignment after configurable timeout (default: 5 minutes)
- **Smart Auto-Assignment**: Automatically assigns jobs to available technicians based on availability
- **Job Offer System**: Technicians receive job offers with time limits (default: 5 minutes)
- **Workflow Enforcement**: System enforces proper state transitions throughout the job lifecycle

### User Roles
- **Admin/Owner**: Full system control, triage management, service catalog, technician management
- **Technician**: Job acceptance, status updates, quote generation, checklist completion
- **Customer**: Booking, device history, real-time tracking

### Key Features
- **Service Catalog**: Master pricing catalog prevents manual price entry (anti-fraud)
- **Digital Contracts**: PDF contract generation with customer signature
- **Quality Checklists**: Mandatory checklists per device type before job completion
- **Payment Integration**: Supports Cash, Stripe, PayPal, and COD
- **White-Labeling**: Customizable app name, logo, and colors from admin panel
- **Multi-Currency & Language**: Support for multiple currencies and RTL languages
- **Real-Time Tracking**: Customer can track job status in real-time
- **Technician Management**: Live map view, revenue tracking, commission calculation

## Technology Stack

- **Framework**: Laravel 11
- **Authentication**: Laravel Sanctum (SPA/Mobile)
- **Database**: MySQL/PostgreSQL
- **Queue**: Laravel Queue (Redis/Database)
- **PDF Generation**: DomPDF
- **API**: RESTful API

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL/PostgreSQL
- Redis (optional, for queues)

### Setup Steps

1. **Clone and Install Dependencies**
```bash
cd /home/user/Music/fcm
composer install
```

2. **Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Configure Database**
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fsm_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

4. **Run Migrations**
```bash
php artisan migrate
```

5. **Seed Database**
```bash
php artisan db:seed
```

6. **Publish Sanctum (if needed)**
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

7. **Start Queue Worker**
```bash
php artisan queue:work
```

8. **Start Scheduler** (add to crontab)
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login
- `POST /api/logout` - Logout
- `GET /api/me` - Get current user

### Customer Endpoints
- `POST /api/customer/bookings` - Create booking
- `GET /api/customer/devices` - Get device history
- `GET /api/customer/tickets/{id}/track` - Track ticket status

### Technician Endpoints
- `PUT /api/technician/status` - Update duty status
- `PUT /api/technician/location` - Update location
- `GET /api/technician/jobs/offered` - Get job offers
- `POST /api/technician/jobs/{id}/accept` - Accept job
- `POST /api/technician/jobs/{id}/reject` - Reject job
- `POST /api/technician/jobs/{id}/generate-quote` - Generate quote
- `POST /api/technician/jobs/{id}/sign-contract` - Sign contract
- `PUT /api/technician/jobs/{id}/status` - Update job status
- `POST /api/technician/jobs/{id}/payment` - Record payment

### Admin Endpoints
- `GET /api/admin/dashboard` - Dashboard stats
- `GET /api/admin/triage` - Triage queue
- `POST /api/admin/triage/{id}/assign` - Assign ticket
- `GET /api/admin/services` - Service catalog
- `GET /api/admin/technicians` - Technician list
- `GET /api/admin/map` - Live map view
- `GET /api/admin/settings/white-label` - White label settings

## Default Credentials

After seeding:
- **Admin**: admin@repair.com / password
- **Technician**: tech@repair.com / password
- **Customer**: customer@repair.com / password

## Workflow Overview

1. **Customer Booking**: Customer creates ticket with device info and photos
2. **Triage Buffer**: Ticket enters triage queue with countdown timer
3. **Auto-Assignment**: System assigns to available technician after timeout
4. **Job Offer**: Technician receives offer with deadline
5. **Acceptance**: Technician accepts, customer details revealed
6. **Quote Generation**: Technician selects services from catalog
7. **Contract Signing**: Customer signs digital contract
8. **Work Execution**: Technician updates status, completes checklists
9. **Payment**: Payment recorded, technician released
10. **Completion**: Job closed, technician available for new assignments

## Configuration

### Workflow Settings
- `triage_timeout_minutes`: Time before auto-assign (default: 5)
- `job_offer_timeout_minutes`: Time for technician to accept (default: 5)
- `require_photos`: Require photos on booking (default: false)
- `tax_rate`: Tax percentage (default: 0)

### White Label Settings
- `app_name`: Application name
- `logo_url`: Logo URL
- `primary_color`: Primary brand color
- `secondary_color`: Secondary brand color

## Scheduled Tasks

The system includes scheduled tasks that run automatically:
- **Every minute**: Check expired triage deadlines and job offers
- **Every 5 minutes**: Clean up stale technician locations
- **Daily**: Calculate technician revenue totals

## Queue Jobs

- `ProcessTriageTimeout`: Handles triage timeout and auto-assignment
- `ProcessJobOfferTimeout`: Handles job offer expiration
- `SendNotificationJob`: Sends async notifications
- `GenerateContractPDF`: Generates contract PDFs

## Testing

```bash
php artisan test
```

## License

This project is designed for commercial sale on Envato Market.

## Support

For issues and questions, please refer to the documentation or contact support.
