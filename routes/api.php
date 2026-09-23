<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SSOController;

// API routes for SSO Integration & Verification
Route::prefix('sso')->group(function () {
    Route::post('/login', [SSOController::class, 'login']);
    Route::post('/validate-token', [SSOController::class, 'validateToken']);
    Route::get('/app-info', [SSOController::class, 'appInfo']);
});
