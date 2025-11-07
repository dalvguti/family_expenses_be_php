<?php
/**
 * Laravel API Setup Checker
 * Run this to diagnose common setup issues
 * 
 * Usage: php check-setup.php
 */

echo "\n";
echo "🔍 Family Expenses Laravel API - Setup Checker\n";
echo "================================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// Check 1: PHP Version
echo "Checking PHP version... ";
$phpVersion = PHP_VERSION;
if (version_compare($phpVersion, '8.0.0', '>=')) {
    echo "✅ PHP $phpVersion\n";
    $success[] = "PHP version is compatible";
} else {
    echo "❌ PHP $phpVersion (requires 8.0+)\n";
    $errors[] = "PHP version too old. Upgrade to PHP 8.0 or higher";
}

// Check 2: Required Extensions
echo "Checking PHP extensions... ";
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'json', 'bcmath'];
$missingExtensions = [];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}
if (empty($missingExtensions)) {
    echo "✅ All required extensions loaded\n";
    $success[] = "All PHP extensions present";
} else {
    echo "❌ Missing: " . implode(', ', $missingExtensions) . "\n";
    $errors[] = "Missing PHP extensions: " . implode(', ', $missingExtensions);
}

// Check 3: .env file
echo "Checking .env file... ";
if (file_exists('.env')) {
    echo "✅ Exists\n";
    $success[] = ".env file found";
    
    // Load .env
    $envContent = file_get_contents('.env');
    
    // Check APP_KEY
    echo "Checking APP_KEY... ";
    if (preg_match('/^APP_KEY=base64:.+$/m', $envContent)) {
        echo "✅ Set\n";
        $success[] = "APP_KEY is configured";
    } else {
        echo "❌ Not set\n";
        $errors[] = "APP_KEY not set. Run: php artisan key:generate";
    }
    
    // Check JWT_SECRET
    echo "Checking JWT_SECRET... ";
    if (preg_match('/^JWT_SECRET=.{32,}$/m', $envContent)) {
        echo "✅ Set\n";
        $success[] = "JWT_SECRET is configured";
    } else {
        echo "❌ Not set or too short\n";
        $errors[] = "JWT_SECRET not set. Generate with: php -r \"echo bin2hex(random_bytes(32));\"";
    }
    
    // Check Database config
    echo "Checking database configuration... ";
    $hasDbName = preg_match('/^DB_DATABASE=.+$/m', $envContent);
    $hasDbUser = preg_match('/^DB_USERNAME=.+$/m', $envContent);
    if ($hasDbName && $hasDbUser) {
        echo "✅ Configured\n";
        $success[] = "Database credentials present";
    } else {
        echo "⚠️  Incomplete\n";
        $warnings[] = "Database credentials may be incomplete";
    }
    
} else {
    echo "❌ Missing\n";
    $errors[] = ".env file not found. Copy .env.example to .env";
}

// Check 4: Vendor directory
echo "Checking vendor directory... ";
if (is_dir('vendor')) {
    echo "✅ Exists\n";
    $success[] = "Composer dependencies installed";
} else {
    echo "❌ Missing\n";
    $errors[] = "Vendor directory missing. Run: composer install";
}

// Check 5: Storage permissions
echo "Checking storage permissions... ";
$storageWritable = is_writable('storage');
$cacheWritable = is_writable('bootstrap/cache');
if ($storageWritable && $cacheWritable) {
    echo "✅ Writable\n";
    $success[] = "Storage directories are writable";
} else {
    echo "❌ Permission issues\n";
    $errors[] = "Storage or cache not writable. Run: chmod -R 775 storage bootstrap/cache";
}

// Check 6: Artisan file
echo "Checking artisan file... ";
if (file_exists('artisan')) {
    echo "✅ Exists\n";
    $success[] = "Artisan CLI available";
} else {
    echo "❌ Missing\n";
    $errors[] = "Artisan file missing. Are you in the Laravel root directory?";
}

// Check 7: Routes file
echo "Checking routes... ";
if (file_exists('routes/api.php')) {
    echo "✅ Routes configured\n";
    $success[] = "API routes file exists";
} else {
    echo "❌ Missing\n";
    $errors[] = "API routes file missing";
}

// Check 8: Controllers
echo "Checking controllers... ";
$controllers = [
    'app/Http/Controllers/AuthController.php',
    'app/Http/Controllers/UserController.php',
    'app/Http/Controllers/CategoryController.php',
    'app/Http/Controllers/ExpenseController.php',
];
$missingControllers = [];
foreach ($controllers as $controller) {
    if (!file_exists($controller)) {
        $missingControllers[] = basename($controller);
    }
}
if (empty($missingControllers)) {
    echo "✅ All present\n";
    $success[] = "All controllers exist";
} else {
    echo "⚠️  Missing: " . implode(', ', $missingControllers) . "\n";
    $warnings[] = "Some controllers missing: " . implode(', ', $missingControllers);
}

// Check 9: Models
echo "Checking models... ";
$models = [
    'app/Models/User.php',
    'app/Models/Category.php',
    'app/Models/Expense.php',
];
$missingModels = [];
foreach ($models as $model) {
    if (!file_exists($model)) {
        $missingModels[] = basename($model);
    }
}
if (empty($missingModels)) {
    echo "✅ All present\n";
    $success[] = "All models exist";
} else {
    echo "⚠️  Missing: " . implode(', ', $missingModels) . "\n";
    $warnings[] = "Some models missing: " . implode(', ', $missingModels);
}

// Summary
echo "\n";
echo "================================================\n";
echo "Summary\n";
echo "================================================\n\n";

if (!empty($success)) {
    echo "✅ SUCCESS (" . count($success) . "):\n";
    foreach ($success as $item) {
        echo "   • $item\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $item) {
        echo "   • $item\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "❌ ERRORS (" . count($errors) . "):\n";
    foreach ($errors as $item) {
        echo "   • $item\n";
    }
    echo "\n";
    echo "Fix these errors before starting the server.\n\n";
    exit(1);
}

if (empty($errors) && empty($warnings)) {
    echo "🎉 All checks passed! Your setup looks good!\n\n";
    echo "Next steps:\n";
    echo "1. Run migrations: php artisan migrate\n";
    echo "2. Seed database: php artisan db:seed\n";
    echo "3. Start server: php artisan serve\n";
    echo "4. Test API: curl http://localhost:8000/api/health\n\n";
    exit(0);
}

if (empty($errors)) {
    echo "⚠️  Setup is mostly complete but has some warnings.\n";
    echo "You can proceed but may need to address the warnings.\n\n";
    echo "To start server: php artisan serve\n\n";
    exit(0);
}

echo "\n";

