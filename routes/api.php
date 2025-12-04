<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\LineRequestController;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Customer routes
    Route::prefix('customer')->group(function () {
        Route::get('/profile', [CustomerController::class, 'profile']);
        Route::put('/profile', [CustomerController::class, 'updateProfile']);
        Route::post('/location', [CustomerController::class, 'updateLocation']);
    });

    // Agent routes
    Route::prefix('agent')->group(function () {
        Route::get('/profile', [AgentController::class, 'profile']);
        Route::put('/profile', [AgentController::class, 'updateProfile']);
        Route::post('/location', [AgentController::class, 'updateLocation']);
        Route::get('/requests', [AgentController::class, 'requests']);
        Route::get('/requests/{lineRequest}', [AgentController::class, 'showRequest']);
        Route::post('/requests/{lineRequest}/respond', [AgentController::class, 'respondToRequest']);
        Route::post('/requests/{lineRequest}/complete', [PaymentController::class, 'completeJob']);
    });

    // Line request routes (Customer)
    Route::prefix('line-requests')->group(function () {
        Route::get('/', [LineRequestController::class, 'index']);
        Route::post('/', [LineRequestController::class, 'store']);
        Route::get('/{lineRequest}', [LineRequestController::class, 'show']);
        Route::post('/{lineRequest}/cancel', [LineRequestController::class, 'cancel']);
        
        // Payment routes for Customer
        Route::post('/{lineRequest}/pay', [PaymentController::class, 'initiate']);
        Route::post('/{lineRequest}/cancel-pay', [PaymentController::class, 'cancelJobPayment']);
        Route::get('/{lineRequest}/payment-status', [PaymentController::class, 'checkStatus']);
    });
});
