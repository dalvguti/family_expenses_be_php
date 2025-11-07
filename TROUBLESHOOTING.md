# Troubleshooting Guide - Server Errors

This guide will help you diagnose and fix server errors when connecting from frontend to Laravel API.

## 🔍 Step 1: Identify the Error

### Check Browser Console
1. Open your frontend in browser
2. Press F12 to open Developer Tools
3. Go to **Console** tab
4. Try making an API request
5. Note the exact error message

### Common Error Messages

**500 Internal Server Error**
- Server-side PHP error
- Missing configuration
- Database connection issue

**404 Not Found**
- Wrong API URL
- Routes not configured
- Server not running on expected port

**503 Service Unavailable**
- Laravel in maintenance mode
- Server not started

---

## 🔧 Step 2: Check Laravel Logs

### View Recent Errors
```bash
cd FamilyExpenses/family_expenses_laravel

# View last 50 lines of log
tail -n 50 storage/logs/laravel.log

# Or follow logs in real-time
tail -f storage/logs/laravel.log
```

### Common Log Errors and Fixes

#### Error: "No application encryption key has been specified"
```
RuntimeException: No application encryption key has been specified.
```

**Fix:**
```bash
php artisan key:generate
```

#### Error: "Class 'JWT' not found" or JWT errors
```
Error: Class 'Firebase\JWT\JWT' not found
```

**Fix:**
```bash
composer require firebase/php-jwt
composer dump-autoload
```

#### Error: "SQLSTATE[HY000] [1045] Access denied"
```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'
```

**Fix:** Check database credentials in `.env`

#### Error: "Base table or view not found"
```
SQLSTATE[42S02]: Base table or view not found
```

**Fix:** Run migrations
```bash
php artisan migrate
```

---

## ✅ Step 3: Verify Basic Setup

### 1. Check if Laravel Server is Running

```bash
# Should show process running on port 8000
lsof -i :8000

# Or try to access directly
curl http://localhost:8000/api/health
```

**Expected Response:**
```json
{
  "status": "OK",
  "message": "Server is running",
  "database": "MySQL",
  "framework": "Laravel"
}
```

If this fails, Laravel isn't running properly.

### 2. Check .env File Exists

```bash
cd FamilyExpenses/family_expenses_laravel

# Check if .env exists
ls -la .env

# If not found, create it
cp .env.example .env
```

### 3. Verify Required Environment Variables

```bash
# Check if APP_KEY is set
grep APP_KEY .env

# Check if JWT_SECRET is set
grep JWT_SECRET .env

# Check database settings
grep DB_ .env
```

**Required variables:**
```env
APP_KEY=base64:...                    # Must be set!
JWT_SECRET=64-character-hex-string    # Must be set!
DB_DATABASE=family_expenses
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Check PHP Version

```bash
php -v
# Should be PHP 8.0 or higher
```

### 5. Check Composer Dependencies

```bash
# Verify vendor directory exists
ls -la vendor/

# If not, install dependencies
composer install
```

---

## 🚀 Step 4: Complete Setup Check

Run this complete setup verification:

```bash
cd FamilyExpenses/family_expenses_laravel

echo "=== Checking Laravel Setup ==="
echo ""

echo "1. Checking .env file..."
if [ -f .env ]; then
    echo "   ✅ .env file exists"
else
    echo "   ❌ .env file missing - creating from example"
    cp .env.example .env
fi

echo ""
echo "2. Checking APP_KEY..."
APP_KEY=$(grep "APP_KEY=" .env | cut -d'=' -f2)
if [ -z "$APP_KEY" ]; then
    echo "   ❌ APP_KEY not set - generating..."
    php artisan key:generate
else
    echo "   ✅ APP_KEY is set"
fi

echo ""
echo "3. Checking JWT_SECRET..."
JWT_SECRET=$(grep "JWT_SECRET=" .env | cut -d'=' -f2)
if [ -z "$JWT_SECRET" ]; then
    echo "   ❌ JWT_SECRET not set"
    echo "   Generate with: php -r \"echo bin2hex(random_bytes(32));\""
else
    echo "   ✅ JWT_SECRET is set"
fi

echo ""
echo "4. Checking vendor directory..."
if [ -d vendor ]; then
    echo "   ✅ Dependencies installed"
else
    echo "   ❌ Dependencies missing - installing..."
    composer install
fi

echo ""
echo "5. Checking database connection..."
php artisan db:show 2>/dev/null && echo "   ✅ Database connected" || echo "   ❌ Database connection failed"

echo ""
echo "=== Setup check complete ==="
```

---

## 🔑 Step 5: Fix Missing Configuration

### Generate All Required Keys

```bash
cd FamilyExpenses/family_expenses_laravel

# 1. Generate APP_KEY
php artisan key:generate

# 2. Generate JWT_SECRET
JWT_SECRET=$(php -r "echo bin2hex(random_bytes(32));")
echo "Generated JWT_SECRET: $JWT_SECRET"

# On Mac/Linux:
sed -i '' "s/JWT_SECRET=.*/JWT_SECRET=$JWT_SECRET/" .env

# On Linux only:
sed -i "s/JWT_SECRET=.*/JWT_SECRET=$JWT_SECRET/" .env

# Or manually edit .env and add:
# JWT_SECRET=your-generated-secret-here
```

### Verify .env Configuration

```env
APP_NAME="Family Expenses API"
APP_ENV=local
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=family_expenses
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=your-64-character-hex-string-here
JWT_ACCESS_TOKEN_EXPIRE=1440
JWT_REFRESH_TOKEN_EXPIRE=10080

CORS_ALLOWED_ORIGINS=*
```

---

## 💾 Step 6: Check Database

### 1. Test Database Connection

```bash
# Try to connect to MySQL
mysql -u root -p

# In MySQL prompt:
SHOW DATABASES;

# Check if family_expenses database exists
USE family_expenses;
SHOW TABLES;
```

### 2. Create Database if Missing

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE family_expenses CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed
```

### 3. Verify Tables Exist

```bash
php artisan migrate:status
```

Should show:
```
Migration name ..................................... Ran?
2024_01_01_000001_create_users_table ............... Yes
2024_01_01_000002_create_categories_table .......... Yes
2024_01_01_000003_create_expenses_table ............ Yes
```

---

## 🧹 Step 7: Clear All Caches

```bash
cd FamilyExpenses/family_expenses_laravel

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild optimized files
php artisan config:cache
php artisan route:cache

# Or use this all-in-one command:
php artisan optimize:clear
```

---

## 🔄 Step 8: Restart Everything

```bash
# Stop any running Laravel server
# Press Ctrl+C in the terminal running php artisan serve

# Start fresh
cd FamilyExpenses/family_expenses_laravel
php artisan serve

# You should see:
# Laravel development server started: http://127.0.0.1:8000
```

---

## 🧪 Step 9: Test API Endpoints

### Test 1: Health Check (No Auth)
```bash
curl http://localhost:8000/api/health
```

**Expected:**
```json
{
  "status": "OK",
  "message": "Server is running",
  "database": "MySQL",
  "framework": "Laravel"
}
```

### Test 2: Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

**Expected:**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {...},
  "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "refreshToken": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

### Test 3: Protected Endpoint
```bash
# Use the accessToken from login response
curl http://localhost:8000/api/expenses \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

---

## 🌐 Step 10: Check Frontend Configuration

### Verify API URL in Frontend

Make sure your frontend is pointing to the correct URL:

```javascript
// React - Check your API configuration file
// Usually in: src/services/api.js or src/config.js

const API_BASE_URL = 'http://localhost:8000/api';  // Must match Laravel server

// Axios example
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',  // Check this URL!
  timeout: 10000,
});

export default api;
```

### Test from Browser Console

```javascript
// Open browser console (F12) and run:
fetch('http://localhost:8000/api/health')
  .then(r => r.json())
  .then(d => console.log('✅ API Works:', d))
  .catch(e => console.error('❌ Error:', e));
```

---

## 🐛 Common Issues and Solutions

### Issue 1: "Class 'App\Helpers\JwtHelper' not found"

**Fix:**
```bash
composer dump-autoload
```

### Issue 2: "Undefined array key 'auth_user'"

**Problem:** JWT middleware not working

**Fix:**
```bash
# Check if middleware is registered
php artisan route:list --path=api/expenses

# Should show: auth.jwt middleware
```

### Issue 3: Port 8000 already in use

**Fix:**
```bash
# Find what's using port 8000
lsof -i :8000

# Kill the process
kill -9 PID

# Or use different port
php artisan serve --port=8001
```

### Issue 4: "Target class [AuthController] does not exist"

**Fix:**
```bash
composer dump-autoload
php artisan route:clear
php artisan config:clear
```

### Issue 5: CORS Errors Still Happening

**Fix:**
```bash
# Update .env
echo "CORS_ALLOWED_ORIGINS=*" >> .env

# Clear config
php artisan config:clear

# Restart server
```

### Issue 6: 404 on all API routes

**Problem:** Routes not loaded or wrong URL

**Fix:**
```bash
# Check routes exist
php artisan route:list

# Clear route cache
php artisan route:clear

# Verify URL - should include /api/
curl http://localhost:8000/api/health
```

---

## 📋 Quick Diagnostic Script

Save this as `diagnose.sh` and run it:

```bash
#!/bin/bash

echo "🔍 Laravel API Diagnostics"
echo "=========================="
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Not in Laravel directory"
    echo "   Run: cd FamilyExpenses/family_expenses_laravel"
    exit 1
fi

echo "✅ In Laravel directory"
echo ""

# Check PHP version
echo "PHP Version:"
php -v | head -n 1
echo ""

# Check .env
if [ -f .env ]; then
    echo "✅ .env file exists"
    
    # Check APP_KEY
    if grep -q "APP_KEY=base64:" .env; then
        echo "✅ APP_KEY is set"
    else
        echo "❌ APP_KEY not set - run: php artisan key:generate"
    fi
    
    # Check JWT_SECRET
    if grep -q "JWT_SECRET=.\{32,\}" .env; then
        echo "✅ JWT_SECRET is set"
    else
        echo "❌ JWT_SECRET not set or too short"
    fi
else
    echo "❌ .env file missing"
fi
echo ""

# Check vendor
if [ -d "vendor" ]; then
    echo "✅ Composer dependencies installed"
else
    echo "❌ Dependencies missing - run: composer install"
fi
echo ""

# Test database connection
echo "Testing database connection..."
php artisan db:show 2>/dev/null && echo "✅ Database connected" || echo "❌ Database connection failed"
echo ""

# Check if server is running
if lsof -i :8000 > /dev/null 2>&1; then
    echo "✅ Server running on port 8000"
else
    echo "⚠️  Server not running on port 8000"
    echo "   Start with: php artisan serve"
fi
echo ""

# Test API health endpoint
echo "Testing API endpoint..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/api/health 2>/dev/null)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ API responding (HTTP $HTTP_CODE)"
else
    echo "❌ API not responding (HTTP $HTTP_CODE)"
fi
echo ""

# Check routes
echo "Checking routes..."
ROUTE_COUNT=$(php artisan route:list --json 2>/dev/null | grep -c "api/")
echo "   Found $ROUTE_COUNT API routes"
echo ""

echo "=========================="
echo "Diagnostics complete!"
```

Run it:
```bash
chmod +x diagnose.sh
./diagnose.sh
```

---

## 🆘 Still Not Working?

### Enable Debug Mode

Edit `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Get Detailed Error

```bash
# Clear everything
php artisan optimize:clear

# Start server with verbose output
php artisan serve --verbose

# In another terminal, check logs
tail -f storage/logs/laravel.log
```

### Check Specific Error

Based on the HTTP status code:

**500 Internal Server Error:**
- Check `storage/logs/laravel.log`
- Check PHP error log
- Check database connection

**404 Not Found:**
- Verify URL includes `/api/`
- Check `php artisan route:list`
- Verify server is running

**401 Unauthorized:**
- Check JWT token
- Verify JWT_SECRET in .env
- Check Authorization header format

**403 Forbidden:**
- File permissions issue
- Check storage/ and bootstrap/cache/ permissions

---

## ✅ Complete Reset (Last Resort)

If nothing works, start fresh:

```bash
cd FamilyExpenses/family_expenses_laravel

# 1. Clear everything
php artisan optimize:clear
rm -rf bootstrap/cache/*.php

# 2. Reinstall dependencies
rm -rf vendor/
composer install

# 3. Reconfigure
cp .env.example .env
php artisan key:generate

# 4. Set JWT secret
JWT_SECRET=$(php -r "echo bin2hex(random_bytes(32));")
echo "JWT_SECRET=$JWT_SECRET" >> .env

# 5. Update database credentials in .env
nano .env

# 6. Migrate database
php artisan migrate:fresh --seed

# 7. Clear and cache
php artisan config:cache
php artisan route:cache

# 8. Fix permissions
chmod -R 775 storage bootstrap/cache

# 9. Start server
php artisan serve
```

---

## 📞 Getting Help

Include this information when asking for help:

```bash
# System info
php -v
composer --version

# Laravel info
php artisan --version

# Error from logs
tail -n 20 storage/logs/laravel.log

# Route list
php artisan route:list --path=api/

# Environment check
php artisan about
```

---

Your API should now be working! 🚀

