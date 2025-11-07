<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Family Expenses API',
        'version' => '1.0.0',
        'framework' => 'Laravel',
        'api_prefix' => '/api',
    ]);
});

