# Environment Variables Template

Copy this template to create your `.env` file:

```env
# Application
APP_NAME="Family Expenses API"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=family_expenses
DB_USERNAME=root
DB_PASSWORD=

# JWT Configuration
# Generate a random 64-character string with: php -r "echo bin2hex(random_bytes(32));"
JWT_SECRET=your-64-character-secret-key-here
JWT_ACCESS_TOKEN_EXPIRE=1440
JWT_REFRESH_TOKEN_EXPIRE=10080

# CORS Configuration
# Use * for all origins (development only) or comma-separated list
CORS_ALLOWED_ORIGINS=*
# Example: CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://www.yourdomain.com
```

## Configuration Notes

### APP_ENV
- `local` - Development environment
- `production` - Production environment

### APP_DEBUG
- `true` - Show detailed error messages (development only)
- `false` - Hide error details (production)

### Database Settings
Update these with your MySQL database credentials:
- `DB_HOST` - Usually `localhost` on shared hosting
- `DB_DATABASE` - Your database name (may be prefixed by cPanel)
- `DB_USERNAME` - Your database user
- `DB_PASSWORD` - Your database password

### JWT Settings
- `JWT_SECRET` - Must be a random 64-character string
  - Generate with: `php -r "echo bin2hex(random_bytes(32));"`
- `JWT_ACCESS_TOKEN_EXPIRE` - Access token lifetime in minutes (default: 1440 = 24 hours)
- `JWT_REFRESH_TOKEN_EXPIRE` - Refresh token lifetime in minutes (default: 10080 = 7 days)

### CORS Settings
- `*` - Allow all origins (development only)
- Specific domains - Comma-separated list (production)
  - Example: `https://yourdomain.com,https://app.yourdomain.com`

## Quick Setup

1. **Create .env file:**
```bash
cp ENV_TEMPLATE.md .env
# Edit .env and update the values
```

2. **Generate APP_KEY:**
```bash
php artisan key:generate
```

3. **Generate JWT_SECRET:**
```bash
php -r "echo bin2hex(random_bytes(32));"
# Copy the output and paste into JWT_SECRET in .env
```

4. **Test configuration:**
```bash
php artisan config:clear
php artisan config:cache
```

## Production Security Checklist

- [ ] `APP_ENV` is set to `production`
- [ ] `APP_DEBUG` is set to `false`
- [ ] `APP_URL` uses `https://`
- [ ] `JWT_SECRET` is a random 64-character string
- [ ] Database credentials are correct
- [ ] `CORS_ALLOWED_ORIGINS` is restricted to specific domains (not `*`)
- [ ] `.env` file is NOT committed to version control
- [ ] File permissions are correct (644 for .env)

