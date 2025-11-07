# Family Expenses API - Laravel - cPanel Setup Guide

Complete guide for deploying the Laravel Family Expenses API on cPanel hosting.

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Initial Setup](#initial-setup)
3. [File Upload](#file-upload)
4. [Database Configuration](#database-configuration)
5. [Environment Configuration](#environment-configuration)
6. [Directory Structure Setup](#directory-structure-setup)
7. [Composer Installation](#composer-installation)
8. [Running Migrations and Seeds](#running-migrations-and-seeds)
9. [Public Directory Setup](#public-directory-setup)
10. [Testing the API](#testing-the-api)
11. [Troubleshooting](#troubleshooting)

---

## Prerequisites

Before starting, ensure you have:
- cPanel hosting account with PHP 8.0 or higher
- MySQL database access
- SSH access (recommended) or File Manager access
- Domain or subdomain configured

---

## Initial Setup

### Step 1: Check PHP Version
1. Log into cPanel
2. Go to **Select PHP Version** or **MultiPHP Manager**
3. Ensure PHP 8.0 or higher is selected
4. Enable the following PHP extensions:
   - `mbstring`
   - `pdo_mysql`
   - `openssl`
   - `tokenizer`
   - `xml`
   - `ctype`
   - `json`
   - `bcmath`

---

## File Upload

### Option A: Using SSH (Recommended)

1. Connect to your server via SSH:
```bash
ssh username@yourdomain.com
```

2. Navigate to your home directory:
```bash
cd ~
```

3. Upload your Laravel project (you can use Git, FTP, or SCP)
```bash
# If using Git
git clone your-repository-url family_expenses_api

# Or upload via SCP from your local machine
scp -r /path/to/family_expenses_laravel username@yourdomain.com:~/family_expenses_api
```

### Option B: Using cPanel File Manager

1. Compress your `family_expenses_laravel` folder to a ZIP file
2. Log into cPanel
3. Open **File Manager**
4. Navigate to your home directory (usually `/home/username/`)
5. Click **Upload** and upload the ZIP file
6. Once uploaded, right-click the ZIP file and select **Extract**
7. Rename the extracted folder to `family_expenses_api`

---

## Database Configuration

### Step 1: Create MySQL Database

1. In cPanel, go to **MySQL® Databases**
2. Create a new database:
   - Database name: `username_family_expenses` (cPanel usually prefixes with your username)
   - Click **Create Database**

### Step 2: Create Database User

1. In the same **MySQL® Databases** page, scroll to **MySQL Users**
2. Create a new user:
   - Username: `username_api_user`
   - Password: Generate a strong password
   - Click **Create User**

### Step 3: Assign User to Database

1. Scroll to **Add User To Database**
2. Select the user you created
3. Select the database you created
4. Click **Add**
5. On the privileges page, select **ALL PRIVILEGES**
6. Click **Make Changes**

### Step 4: Note Your Database Credentials

Save the following information (you'll need it for the `.env` file):
- Database Host: Usually `localhost` (check cPanel if different)
- Database Name: `username_family_expenses`
- Database Username: `username_api_user`
- Database Password: The password you created
- Database Port: Usually `3306`

---

## Environment Configuration

### Step 1: Create .env File

1. Navigate to your project directory:
```bash
cd ~/family_expenses_api
```

2. Copy the example environment file:
```bash
cp .env.example .env
```

3. Edit the `.env` file:
```bash
nano .env
# or use cPanel File Manager editor
```

4. Update the following values:

```env
APP_NAME="Family Expenses API"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=username_family_expenses
DB_USERNAME=username_api_user
DB_PASSWORD=your_database_password

JWT_SECRET=generate-a-random-64-character-string-here
JWT_ACCESS_TOKEN_EXPIRE=1440
JWT_REFRESH_TOKEN_EXPIRE=10080

CORS_ALLOWED_ORIGINS=*
```

**Important:** Generate a secure JWT_SECRET:
```bash
# Generate random string for JWT_SECRET
php -r "echo bin2hex(random_bytes(32));"
```

---

## Composer Installation

### Step 1: Install Composer Dependencies

If you have SSH access:

```bash
cd ~/family_expenses_api

# If composer is not globally available, download it
curl -sS https://getcomposer.org/installer | php

# Install dependencies
php composer.phar install --optimize-autoloader --no-dev

# Or if composer is global
composer install --optimize-autoloader --no-dev
```

If you don't have SSH access:
1. Install dependencies on your local machine
2. Upload the entire `vendor` folder along with your project

### Step 2: Generate Application Key

```bash
php artisan key:generate
```

This will automatically update the `APP_KEY` in your `.env` file.

---

## Running Migrations and Seeds

### Step 1: Run Database Migrations

```bash
cd ~/family_expenses_api
php artisan migrate
```

You should see:
```
Migration table created successfully.
Migrating: 2024_01_01_000001_create_users_table
Migrated:  2024_01_01_000001_create_users_table
Migrating: 2024_01_01_000002_create_categories_table
Migrated:  2024_01_01_000002_create_categories_table
Migrating: 2024_01_01_000003_create_expenses_table
Migrated:  2024_01_01_000003_create_expenses_table
```

### Step 2: Run Database Seeders (Optional)

```bash
php artisan db:seed
```

This will create:
- 3 sample users (admin, john, jane)
- 12 categories for expenses and earnings

**Default Users:**
- Username: `admin`, Password: `admin123` (Admin)
- Username: `john`, Password: `john123` (Member)
- Username: `jane`, Password: `jane123` (Member)

**Important:** Change these passwords after first login!

---

## Public Directory Setup

### Important: Laravel Security Structure

Laravel's `public` folder should be your web root. Here's how to set it up properly:

### Option 1: Subdomain Setup (Recommended)

1. In cPanel, go to **Subdomains**
2. Create a subdomain: `api.yourdomain.com`
3. Set Document Root to: `/home/username/family_expenses_api/public`
4. Click **Create**

### Option 2: Main Domain Setup

If you want to use your main domain:

1. In cPanel, go to **Addon Domains** or **Domains**
2. Find your domain and click **Manage**
3. Change Document Root to: `/home/username/family_expenses_api/public`

### Option 3: Subdirectory Setup

If you want the API accessible at `yourdomain.com/api`:

1. Keep public_html as your document root
2. Create a symbolic link:
```bash
cd ~/public_html
ln -s ~/family_expenses_api/public api
```

3. Or use `.htaccess` redirect in `public_html/api/.htaccess`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /api/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ /home/username/family_expenses_api/public/index.php [L]
</IfModule>
```

### Step 3: Set Proper Permissions

```bash
cd ~/family_expenses_api

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Set storage and cache permissions
chmod -R 775 storage bootstrap/cache
```

---

## Testing the API

### Step 1: Test Health Endpoint

Visit in your browser or use curl:
```bash
curl https://api.yourdomain.com/api/health
```

Expected response:
```json
{
    "status": "OK",
    "message": "Server is running",
    "database": "MySQL",
    "framework": "Laravel"
}
```

### Step 2: Test Login

```bash
curl -X POST https://api.yourdomain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "admin123"
  }'
```

Expected response:
```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "Admin User",
        "username": "admin",
        "email": "admin@familyexpenses.com",
        "role": "admin",
        "isActive": true
    },
    "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

### Step 3: Test Protected Endpoint

```bash
# Use the accessToken from login response
curl https://api.yourdomain.com/api/auth/me \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

---

## API Endpoints Reference

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout (requires auth)
- `POST /api/auth/refresh` - Refresh access token
- `GET /api/auth/me` - Get current user (requires auth)
- `PUT /api/auth/password` - Update password (requires auth)

### Expenses
- `GET /api/expenses` - Get all expenses (requires auth)
- `GET /api/expenses/stats` - Get expense statistics (requires auth)
- `POST /api/expenses` - Create expense (requires auth)
- `GET /api/expenses/{id}` - Get single expense (requires auth)
- `PUT /api/expenses/{id}` - Update expense (requires auth)
- `DELETE /api/expenses/{id}` - Delete expense (requires auth)

### Categories
- `GET /api/categories` - Get all categories (requires auth)
- `POST /api/categories` - Create category (requires auth)
- `GET /api/categories/{id}` - Get single category (requires auth)
- `PUT /api/categories/{id}` - Update category (requires auth)
- `DELETE /api/categories/{id}` - Delete category (requires auth)
- `PATCH /api/categories/{id}/toggle` - Toggle category status (requires auth)

### Users
- `GET /api/users` - Get all users (requires auth)
- `POST /api/users` - Create user (requires auth)
- `GET /api/users/{id}` - Get single user (requires auth)
- `PUT /api/users/{id}` - Update user (requires auth)
- `DELETE /api/users/{id}` - Delete user (requires auth)

### Reports
- `GET /api/reports/monthly?year=2024&month=11` - Get monthly report (requires auth)
- `GET /api/reports/yearly?year=2024` - Get yearly report (requires auth)

---

## Troubleshooting

### Issue: 500 Internal Server Error

**Solution:**
1. Check error logs:
```bash
tail -f storage/logs/laravel.log
```

2. Or check cPanel error logs in **Errors** section

3. Common causes:
   - Wrong file permissions
   - Missing `.env` file
   - Database connection issues
   - Missing `APP_KEY` in `.env`

### Issue: Database Connection Failed

**Solution:**
1. Verify database credentials in `.env`
2. Test database connection:
```bash
php artisan tinker
DB::connection()->getPdo();
```

### Issue: CORS Errors

**Solution:**
1. Update `.env`:
```env
CORS_ALLOWED_ORIGINS=https://yourfrontend.com,http://localhost:3000
```

2. Or allow all origins (development only):
```env
CORS_ALLOWED_ORIGINS=*
```

### Issue: JWT Token Invalid

**Solution:**
1. Ensure `JWT_SECRET` is set in `.env`
2. Generate a new secret:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

3. Update `.env` with the generated secret

### Issue: File Permissions Error

**Solution:**
```bash
cd ~/family_expenses_api
chmod -R 775 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

### Issue: Composer Not Found

**Solution:**
Download composer locally:
```bash
cd ~/family_expenses_api
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

### Issue: PHP Version Error

**Solution:**
1. In cPanel, go to **Select PHP Version**
2. Select PHP 8.0 or higher
3. Enable required extensions
4. Restart PHP by toggling a random extension on/off

---

## Performance Optimization

### 1. Cache Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Optimize Autoloader

```bash
composer dump-autoload --optimize
```

### 3. Enable OPcache

In cPanel **Select PHP Version**, enable:
- `opcache`

---

## Security Best Practices

1. **Change Default Passwords**
   - Update all seeded user passwords immediately

2. **Environment File**
   - Never commit `.env` to version control
   - Keep `APP_DEBUG=false` in production

3. **JWT Secret**
   - Use a strong, random 64-character secret
   - Never share or expose this secret

4. **Database User**
   - Use a dedicated database user with minimal privileges
   - Never use root MySQL user

5. **HTTPS**
   - Always use HTTPS in production
   - Update `APP_URL` to use `https://`

6. **File Permissions**
   - Storage and cache: 775
   - Other files: 644
   - Directories: 755

---

## Updating the Application

To update your application:

```bash
cd ~/family_expenses_api

# Backup database first!
# Put application in maintenance mode
php artisan down

# Pull latest changes (if using Git)
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and rebuild cache
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# Bring application back online
php artisan up
```

---

## Backup Strategy

### Database Backup

In cPanel:
1. Go to **phpMyAdmin**
2. Select your database
3. Click **Export**
4. Choose format: SQL
5. Click **Go**

Or via command line:
```bash
mysqldump -u username_api_user -p username_family_expenses > backup.sql
```

### Files Backup

```bash
cd ~
tar -czf family_expenses_backup_$(date +%Y%m%d).tar.gz family_expenses_api
```

---

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check cPanel error logs

---

## Migration from Node.js

This Laravel application maintains 100% API compatibility with the previous Node.js version. All endpoints, request formats, and response formats remain the same, so your frontend application should work without any changes.

**Changes needed in frontend (if any):**
- Update API base URL to point to the new Laravel backend
- No changes needed to request/response handling

---

## Quick Command Reference

```bash
# Navigate to project
cd ~/family_expenses_api

# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache

# View routes
php artisan route:list

# Check logs
tail -f storage/logs/laravel.log

# Maintenance mode
php artisan down
php artisan up
```

---

**Congratulations!** Your Laravel Family Expenses API is now deployed and running on cPanel! 🎉

