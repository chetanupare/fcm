# API Documentation

## Access API Documentation

The API documentation is generated using **Scramble** and is available at:

**URL**: `/docs/api`

### Full URL Examples:
- **Local Development**: `http://localhost:8001/docs/api`
- **Production**: `https://yourdomain.com/docs/api`

### JSON Specification (OpenAPI):
- **URL**: `/docs/api.json`
- **Local Development**: `http://localhost:8001/docs/api.json`
- **Production**: `https://yourdomain.com/docs/api.json`

## Documentation Segments

The API documentation is organized into 4 segments:

1. **Admin** - Administrator endpoints for managing the system
2. **Customer** - Customer endpoints for bookings and tracking
3. **Technician** - Technician endpoints for job management
4. **Misc** - Miscellaneous endpoints (auth, webhooks, etc.)

## Configuration

The Scramble configuration is located in:
- **File**: `config/scramble.php`

## Setup

Scramble is already installed and configured. The documentation is automatically generated from your API controllers.

### View Documentation

Simply navigate to `/docs/api` in your browser after starting the Laravel server.

**Direct Links:**
- UI Documentation: `http://localhost:8001/docs/api`
- JSON Spec: `http://localhost:8001/docs/api.json`

### Generate OpenAPI Spec

To generate the OpenAPI JSON specification:

```bash
php artisan tinker
>>> \Dedoc\Scramble\Scramble::openApi()->toJson();
```

## Notes

- The documentation is automatically updated when you modify API controllers
- All controllers are tagged with `@group` annotations for proper segmentation
- Authentication endpoints are documented in the "Misc" segment
