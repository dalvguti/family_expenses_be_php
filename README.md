# Family Expenses API - Laravel

A complete Laravel REST API for managing family expenses and earnings. This is a Laravel migration of the original Node.js version, maintaining 100% API compatibility.

## Features

- 👤 **User Authentication & Authorization** - JWT-based auth with role management (Admin/Member)
- 💰 **Expense & Earning Tracking** - Track all financial transactions
- 📊 **Categories Management** - Organize transactions with custom categories (with emoji support)
- 📈 **Reports** - Monthly and yearly financial reports with detailed breakdowns
- 🔒 **Secure** - Password hashing, JWT tokens, input validation
- 🚀 **RESTful API** - Clean, consistent API design
- 📱 **CORS Enabled** - Ready for frontend integration

## Tech Stack

- **Framework:** Laravel 10
- **Database:** MySQL
- **Authentication:** JWT (Firebase PHP-JWT)
- **PHP:** 8.0+

## Quick Start

### Prerequisites

- PHP 8.0 or higher
- Composer
- MySQL 5.7+
- Web server (Apache/Nginx)

### Local Development Setup

1. **Clone the repository**
```bash
cd family_expenses_laravel
```

2. **Install dependencies**
```bash
composer install
```

3. **Configure environment**
```bash
cp .env.example .env
```

Edit `.env` and update:
- Database credentials
- JWT_SECRET (generate with: `php -r "echo bin2hex(random_bytes(32));"`)
- APP_URL

4. **Generate application key**
```bash
php artisan key:generate
```

5. **Run migrations**
```bash
php artisan migrate
```

6. **Seed database (optional)**
```bash
php artisan db:seed
```

This creates sample users and categories:
- Admin: username: `admin`, password: `admin123`
- Member: username: `john`, password: `john123`
- Member: username: `jane`, password: `jane123`

7. **Start development server**
```bash
php artisan serve
```

API will be available at: `http://localhost:8000/api`

### Testing the API

**Health Check:**
```bash
curl http://localhost:8000/api/health
```

**Login:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "admin123"}'
```

## API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication
Most endpoints require JWT authentication. Include the token in the Authorization header:
```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

### Endpoints

#### Authentication
- `POST /auth/register` - Register new user
- `POST /auth/login` - Login
- `POST /auth/logout` - Logout (auth required)
- `POST /auth/refresh` - Refresh access token
- `GET /auth/me` - Get current user (auth required)
- `PUT /auth/password` - Update password (auth required)

#### Expenses
- `GET /expenses` - List all expenses (auth required)
- `GET /expenses/stats` - Get statistics (auth required)
- `POST /expenses` - Create expense (auth required)
- `GET /expenses/{id}` - Get single expense (auth required)
- `PUT /expenses/{id}` - Update expense (auth required)
- `DELETE /expenses/{id}` - Delete expense (auth required)

#### Categories
- `GET /categories` - List all categories (auth required)
- `POST /categories` - Create category (auth required)
- `GET /categories/{id}` - Get single category (auth required)
- `PUT /categories/{id}` - Update category (auth required)
- `DELETE /categories/{id}` - Delete category (auth required)
- `PATCH /categories/{id}/toggle` - Toggle category status (auth required)

#### Users
- `GET /users` - List all users (auth required)
- `POST /users` - Create user (auth required)
- `GET /users/{id}` - Get single user (auth required)
- `PUT /users/{id}` - Update user (auth required)
- `DELETE /users/{id}` - Delete user (auth required)

#### Reports
- `GET /reports/monthly?year=2024&month=11` - Monthly report (auth required)
- `GET /reports/yearly?year=2024` - Yearly report (auth required)

## Deployment

For cPanel deployment, see the comprehensive guide: **[CPANEL_SETUP.md](CPANEL_SETUP.md)**

The guide includes:
- Step-by-step setup instructions
- Database configuration
- Environment setup
- Security best practices
- Troubleshooting tips

## Project Structure

```
family_expenses_laravel/
├── app/
│   ├── Helpers/
│   │   └── JwtHelper.php          # JWT token management
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ExpenseController.php
│   │   │   ├── ReportController.php
│   │   │   └── UserController.php
│   │   └── Middleware/
│   │       ├── HandleCors.php
│   │       ├── JwtAuthMiddleware.php
│   │       └── AdminAuthMiddleware.php
│   └── Models/
│       ├── User.php
│       ├── Category.php
│       └── Expense.php
├── bootstrap/
│   └── app.php
├── config/
│   ├── database.php
│   ├── jwt.php
│   └── cors.php
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_users_table.php
│   │   ├── 2024_01_01_000002_create_categories_table.php
│   │   └── 2024_01_01_000003_create_expenses_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       └── CategorySeeder.php
├── public/
│   ├── index.php
│   └── .htaccess
├── routes/
│   ├── api.php
│   ├── web.php
│   └── console.php
├── storage/
│   └── logs/
├── .env.example
├── composer.json
├── CPANEL_SETUP.md
└── README.md
```

## Database Schema

### Users Table
- id (Primary Key)
- name
- username (Unique)
- email (Unique)
- password (Hashed)
- role (enum: 'member', 'admin')
- isActive (boolean)
- lastLogin (timestamp)
- refreshToken (text)
- timestamps

### Categories Table
- id (Primary Key)
- name (Unique)
- description
- color (hex code)
- icon (emoji, utf8mb4 support)
- isActive (boolean)
- timestamps

### Expenses Table
- id (Primary Key)
- description
- amount (decimal)
- category
- date (timestamp)
- paidBy
- transactionType (enum: 'expense', 'earning')
- timestamps

## Environment Variables

Key environment variables in `.env`:

```env
APP_NAME="Family Expenses API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

JWT_SECRET=your-64-character-secret
JWT_ACCESS_TOKEN_EXPIRE=1440
JWT_REFRESH_TOKEN_EXPIRE=10080

CORS_ALLOWED_ORIGINS=*
```

## Security Features

- ✅ Password hashing with bcrypt
- ✅ JWT token authentication
- ✅ Refresh token mechanism
- ✅ Input validation on all endpoints
- ✅ SQL injection protection (Eloquent ORM)
- ✅ XSS protection
- ✅ CORS configuration
- ✅ Environment-based configuration

## Development Commands

```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# View routes
php artisan route:list

# Run tests
php artisan test
```

## Migration from Node.js

This Laravel version maintains 100% API compatibility with the Node.js version. The same endpoints, request/response formats, and authentication mechanism are used.

**What's the same:**
- All API endpoints and methods
- Request/response JSON structures
- JWT authentication flow
- Database schema

**What's different:**
- Backend framework (Node.js → Laravel)
- Better performance and scalability
- Easier to deploy on shared hosting (cPanel)
- More maintainable code structure

**Frontend compatibility:**
- Your existing frontend will work without changes
- Just update the API base URL

## Troubleshooting

### Database Connection Issues
```bash
# Test database connection
php artisan tinker
> DB::connection()->getPdo();
```

### Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Permission Issues
```bash
chmod -R 775 storage bootstrap/cache
```

### View Logs
```bash
tail -f storage/logs/laravel.log
```

## Support

- Check logs: `storage/logs/laravel.log`
- Review [CPANEL_SETUP.md](CPANEL_SETUP.md) for deployment help
- Verify environment configuration in `.env`

## License

MIT License

---

**Note:** This is a Laravel migration of the Family Expenses API, originally built with Node.js/Express. All functionality has been preserved while improving performance and deployability on shared hosting platforms.

