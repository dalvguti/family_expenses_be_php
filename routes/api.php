<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'Server is running',
        'database' => 'MySQL',
        'framework' => 'Laravel',
    ]);
});

// Public Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refreshToken']);
    
    // Protected auth routes
    Route::middleware('auth.jwt')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'getMe']);
        Route::put('/password', [AuthController::class, 'updatePassword']);
    });
});

// Protected Routes - Require Authentication
Route::middleware('auth.jwt')->group(function () {
    
    // Expenses Routes
    Route::prefix('expenses')->group(function () {
        Route::get('/stats', [ExpenseController::class, 'stats']); // Must be before /{id}
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store']);
        Route::get('/{id}', [ExpenseController::class, 'show']);
        Route::put('/{id}', [ExpenseController::class, 'update']);
        Route::delete('/{id}', [ExpenseController::class, 'destroy']);
    });
    
    // Categories Routes
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
        Route::patch('/{id}/toggle', [CategoryController::class, 'toggleStatus']);
    });
    
    // Users Routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });
    
    // Reports Routes
    Route::prefix('reports')->group(function () {
        Route::get('/monthly', [ReportController::class, 'monthly']);
        Route::get('/yearly', [ReportController::class, 'yearly']);
    });
});

