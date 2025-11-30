<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboard;
use App\Http\Controllers\Agent\DashboardController as AgentDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Customer Dashboard
Route::middleware(['auth'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboard::class, 'index'])->name('dashboard');
    Route::get('/dashboard/agents', [CustomerDashboard::class, 'getAgents'])->name('dashboard.agents');
    
    // Line Requests
    Route::get('/line-requests', [\App\Http\Controllers\Customer\LineRequestController::class, 'index'])->name('line-requests.index');
    Route::get('/line-requests/create', [\App\Http\Controllers\Customer\LineRequestController::class, 'create'])->name('line-requests.create');
    Route::post('/line-requests', [\App\Http\Controllers\Customer\LineRequestController::class, 'store'])->name('line-requests.store');
    Route::get('/line-requests/{lineRequest}', [\App\Http\Controllers\Customer\LineRequestController::class, 'show'])->name('line-requests.show');
    Route::post('/line-requests/{lineRequest}/cancel', [\App\Http\Controllers\Customer\LineRequestController::class, 'cancel'])->name('line-requests.cancel');
    Route::get('/tracking/{lineRequest}', [\App\Http\Controllers\Customer\TrackingController::class, 'getAgentLocation'])->name('tracking.agent');
    Route::post('/location', [\App\Http\Controllers\Customer\TrackingController::class, 'updateCustomerLocation'])->name('location.update');
    
    // Payment Routes
    Route::post('/requests/{lineRequest}/pay', [\App\Http\Controllers\PaymentController::class, 'initiate'])->name('requests.pay');
    Route::get('/requests/{lineRequest}/payment-status', [\App\Http\Controllers\PaymentController::class, 'checkStatus'])->name('requests.payment-status');
});

// Agent Dashboard
Route::middleware(['auth'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/dashboard', [AgentDashboard::class, 'index'])->name('dashboard');
    Route::get('/dashboard/updates', [AgentDashboard::class, 'updates'])->name('dashboard.updates');
    Route::post('/location', [\App\Http\Controllers\Agent\AgentLocationController::class, 'update'])->name('location.update');
    Route::post('/status/toggle', [\App\Http\Controllers\Agent\AgentLocationController::class, 'toggle'])->name('status.toggle');
    Route::post('/requests/{lineRequest}/accept', [\App\Http\Controllers\Agent\RequestActionController::class, 'accept'])->name('requests.accept');
    Route::post('/requests/{lineRequest}/reject', [\App\Http\Controllers\Agent\RequestActionController::class, 'reject'])->name('requests.reject');
    Route::post('/requests/{lineRequest}/release', [\App\Http\Controllers\Agent\RequestActionController::class, 'release'])->name('requests.release');
    Route::get('/requests/{lineRequest}', [\App\Http\Controllers\Agent\RequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{lineRequest}/complete', [\App\Http\Controllers\PaymentController::class, 'completeJob'])->name('requests.complete');
    
    // Documents
    Route::post('/documents/upload', [\App\Http\Controllers\Agent\DocumentController::class, 'upload'])->name('documents.upload');
});

// Admin Dashboard
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');

    // User Management
    Route::resource('users', \App\Http\Controllers\Admin\UsersController::class);

    // Agent Verification
    Route::get('/agents/verification', [\App\Http\Controllers\Admin\AgentVerificationController::class, 'index'])->name('agents.verification');
    Route::post('/agents/{agent}/verify', [\App\Http\Controllers\Admin\AgentVerificationController::class, 'verify'])->name('agents.verify');
    Route::post('/agents/{agent}/reject', [\App\Http\Controllers\Admin\AgentVerificationController::class, 'reject'])->name('agents.reject');

    // Agent Management (General)
    Route::get('/agents', [\App\Http\Controllers\Admin\AgentController::class, 'index'])->name('agents.index');
    Route::get('/agents/{agent}', [\App\Http\Controllers\Admin\AgentController::class, 'show'])->name('agents.show');
    Route::post('/agents/{agent}/toggle', [\App\Http\Controllers\Admin\AgentController::class, 'toggleStatus'])->name('agents.toggle');
});
