# CORS Issues - Fix Guide

This guide will help you fix CORS (Cross-Origin Resource Sharing) issues in development mode.

## 🔍 Common CORS Error Messages

- `Access to fetch at 'http://localhost:8000/api/...' from origin 'http://localhost:3000' has been blocked by CORS policy`
- `No 'Access-Control-Allow-Origin' header is present on the requested resource`
- `CORS policy: Response to preflight request doesn't pass access control check`

---

## ✅ Solution 1: Update .env File (Easiest)

### For Development (Allow All Origins)

Open your `.env` file and set:

```env
CORS_ALLOWED_ORIGINS=*
```

Then clear config cache:
```bash
php artisan config:clear
php artisan config:cache
```

### For Development (Specific Origins)

If you want to be more specific:

```env
# For React development server
CORS_ALLOWED_ORIGINS=http://localhost:3000

# For multiple origins (comma-separated)
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:3001,http://127.0.0.1:3000
```

Then clear config cache:
```bash
php artisan config:clear
```

---

## ✅ Solution 2: Clear All Caches

Sometimes Laravel caches the configuration. Clear everything:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

Then restart your server:
```bash
php artisan serve
```

---

## ✅ Solution 3: Verify Middleware is Active

Check that the CORS middleware is running. Open `bootstrap/app.php` and verify:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [
        \App\Http\Middleware\HandleCors::class,
    ]);
    // ... rest of middleware
})
```

---

## ✅ Solution 4: Test CORS Headers

Test if CORS headers are being sent:

```bash
# Test OPTIONS request (preflight)
curl -X OPTIONS http://localhost:8000/api/health \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: GET" \
  -H "Access-Control-Request-Headers: Content-Type" \
  -v

# Test GET request
curl -X GET http://localhost:8000/api/health \
  -H "Origin: http://localhost:3000" \
  -v
```

You should see these headers in the response:
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin
```

---

## ✅ Solution 5: Frontend Axios Configuration

Make sure your frontend is configured correctly:

### React with Axios

```javascript
// src/services/api.js
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: false, // Set to false for development
});

// Add token to requests
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('accessToken');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default api;
```

### React with Fetch

```javascript
// src/services/api.js
const API_BASE_URL = 'http://localhost:8000/api';

export const apiRequest = async (endpoint, options = {}) => {
  const token = localStorage.getItem('accessToken');
  
  const config = {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...(token && { 'Authorization': `Bearer ${token}` }),
      ...options.headers,
    },
  };

  const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
  return response.json();
};
```

---

## ✅ Solution 6: Alternative - Install Laravel CORS Package

If the custom middleware doesn't work, install Laravel's official CORS package:

```bash
composer require fruitcake/laravel-cors
```

Then update `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [
        \Fruitcake\Cors\HandleCors::class,  // Use official package
    ]);
})
```

And update `config/cors.php`:

```php
<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

---

## 🔧 Development vs Production Settings

### Development (.env)
```env
APP_ENV=local
APP_DEBUG=true
CORS_ALLOWED_ORIGINS=*
```

### Production (.env)
```env
APP_ENV=production
APP_DEBUG=false
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
```

---

## 🧪 Testing CORS

### Test 1: Health Check Without Auth

```bash
# From terminal
curl http://localhost:8000/api/health

# Expected: Should work without CORS headers
```

### Test 2: From Browser Console

Open your frontend in browser, then in console:

```javascript
fetch('http://localhost:8000/api/health')
  .then(response => response.json())
  .then(data => console.log('Success:', data))
  .catch(error => console.error('CORS Error:', error));
```

Expected output:
```json
{
  "status": "OK",
  "message": "Server is running",
  "database": "MySQL",
  "framework": "Laravel"
}
```

### Test 3: Login Request

```javascript
fetch('http://localhost:8000/api/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    username: 'admin',
    password: 'admin123'
  })
})
  .then(response => response.json())
  .then(data => console.log('Login Success:', data))
  .catch(error => console.error('CORS Error:', error));
```

---

## 🐛 Common Issues and Fixes

### Issue 1: CORS works for GET but not POST

**Problem:** OPTIONS preflight request failing

**Fix:** Make sure HandleCors middleware handles OPTIONS:
```php
if ($request->isMethod('OPTIONS')) {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
}
```

### Issue 2: CORS works sometimes but not always

**Problem:** Cached configuration

**Fix:**
```bash
php artisan config:clear
php artisan cache:clear
# Restart server
php artisan serve
```

### Issue 3: localhost vs 127.0.0.1

**Problem:** Frontend uses `localhost` but backend sees `127.0.0.1`

**Fix:** Use consistent URLs. Either both `localhost` or both `127.0.0.1`:
```javascript
// Frontend
const API_BASE_URL = 'http://localhost:8000/api';

// Or
const API_BASE_URL = 'http://127.0.0.1:8000/api';
```

### Issue 4: Port conflicts

**Problem:** Laravel running on different port

**Fix:** Check which port Laravel is using:
```bash
php artisan serve
# Output: Laravel development server started: http://127.0.0.1:8000
```

Update frontend to match that port.

### Issue 5: Multiple CORS headers

**Problem:** Both custom middleware and Laravel's default CORS adding headers

**Fix:** Choose ONE method:
- Either use custom `HandleCors` middleware
- OR use Laravel's built-in CORS (remove custom middleware)

---

## 📋 Quick Checklist

- [ ] Set `CORS_ALLOWED_ORIGINS=*` in `.env`
- [ ] Run `php artisan config:clear`
- [ ] Restart Laravel server (`php artisan serve`)
- [ ] Clear browser cache (Ctrl+Shift+Delete)
- [ ] Check browser console for specific error
- [ ] Verify API URL in frontend matches backend
- [ ] Test with curl to confirm API works
- [ ] Check HandleCors middleware is loaded

---

## 🎯 Recommended Setup for Development

### 1. Update .env
```env
CORS_ALLOWED_ORIGINS=*
```

### 2. Clear caches
```bash
php artisan config:clear
php artisan cache:clear
```

### 3. Restart server
```bash
php artisan serve
```

### 4. Test immediately
Open browser console and run:
```javascript
fetch('http://localhost:8000/api/health')
  .then(r => r.json())
  .then(d => console.log(d));
```

If this works, CORS is fixed! ✅

---

## 🆘 Still Not Working?

### Debug Steps:

1. **Check Laravel logs:**
```bash
tail -f storage/logs/laravel.log
```

2. **Check web server logs:**
```bash
# If using Apache
tail -f /var/log/apache2/error.log

# If using Nginx
tail -f /var/log/nginx/error.log
```

3. **Enable verbose errors in .env:**
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

4. **Test backend directly:**
```bash
# Should return JSON, no CORS errors
curl http://localhost:8000/api/health
```

5. **Check if middleware is loaded:**
Add this to `HandleCors` middleware for debugging:
```php
public function handle(Request $request, Closure $next): Response
{
    \Log::info('CORS Middleware triggered', [
        'method' => $request->method(),
        'origin' => $request->headers->get('Origin'),
        'path' => $request->path(),
    ]);
    
    // ... rest of code
}
```

Then check logs:
```bash
tail -f storage/logs/laravel.log
```

---

## ✅ Quick Fix Summary

**Most common fix:**

1. Edit `.env`:
   ```env
   CORS_ALLOWED_ORIGINS=*
   ```

2. Run:
   ```bash
   php artisan config:clear
   php artisan serve
   ```

3. Refresh your frontend

**Done!** 🎉

---

## 📞 Need More Help?

If CORS still doesn't work after trying all solutions above:

1. Check the exact error message in browser console
2. Test API with Postman or curl (if it works, it's a CORS issue)
3. Verify middleware is loaded in `bootstrap/app.php`
4. Check Laravel version compatibility
5. Try the official Laravel CORS package

Your CORS issue should be resolved! 🚀

