# Port 8000 Issue - CUPS Conflict

## Problem
Port 8000 is being used by CUPS (Common Unix Printing System), not Laravel.

## Solution
Laravel is running on **port 8001** instead.

## Access Your Application

✅ **Correct URL**: `http://localhost:8001/admin/login`

❌ **Wrong URL**: `http://localhost:8000/admin/login` (This shows CUPS interface)

## Start Server

Run one of these commands:

```bash
./START_SERVER.sh
```

OR

```bash
php8.2 artisan serve --port=8001
```

## Default Credentials

- Email: `admin@repair.com`
- Password: `password`

## Why Port 8001?

Port 8000 is commonly used by:
- CUPS (Printing System)
- Other development servers

Port 8001 is a safe alternative that avoids conflicts.
