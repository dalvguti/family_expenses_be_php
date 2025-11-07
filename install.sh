#!/bin/bash

# Family Expenses Laravel - Installation Script
# This script helps automate the initial setup

echo "======================================"
echo "Family Expenses Laravel - Setup"
echo "======================================"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "⚠️  .env file not found!"
    echo "Creating .env file from .env.example..."
    
    # Create basic .env file
    cat > .env << 'EOF'
APP_NAME="Family Expenses API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=family_expenses
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=
JWT_ACCESS_TOKEN_EXPIRE=1440
JWT_REFRESH_TOKEN_EXPIRE=10080

CORS_ALLOWED_ORIGINS=*
EOF
    
    echo "✅ .env file created!"
    echo ""
    echo "⚠️  IMPORTANT: Edit .env file with your database credentials!"
    echo ""
else
    echo "✅ .env file already exists"
fi

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "⚠️  Composer not found!"
    echo "Please install Composer from https://getcomposer.org/"
    echo "Or download it locally:"
    echo "  curl -sS https://getcomposer.org/installer | php"
    echo "  php composer.phar install"
    exit 1
fi

echo "Installing Composer dependencies..."
composer install --optimize-autoloader

echo ""
echo "Generating application key..."
php artisan key:generate

echo ""
echo "Generating JWT secret..."
JWT_SECRET=$(php -r "echo bin2hex(random_bytes(32));")
if [[ "$OSTYPE" == "darwin"* ]]; then
    sed -i '' "s/JWT_SECRET=.*/JWT_SECRET=$JWT_SECRET/" .env
else
    sed -i "s/JWT_SECRET=.*/JWT_SECRET=$JWT_SECRET/" .env
fi
echo "✅ JWT secret generated and saved to .env"

echo ""
echo "======================================"
echo "Setup Complete!"
echo "======================================"
echo ""
echo "Next steps:"
echo "1. Edit .env with your database credentials"
echo "2. Run migrations: php artisan migrate"
echo "3. (Optional) Seed database: php artisan db:seed"
echo "4. Start server: php artisan serve"
echo ""
echo "API will be available at: http://localhost:8000/api"
echo ""

