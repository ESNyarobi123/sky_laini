<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboard;
use App\Http\Controllers\Agent\DashboardController as AgentDashboard;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Leaderboard (Public)
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');

// Language Switch
Route::post('/language', function (\Illuminate\Http\Request $request) {
    $locale = $request->input('locale', 'sw');
    if (in_array($locale, ['sw', 'en'])) {
        session(['locale' => $locale]);
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }
    }
    return back();
})->name('language.switch');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Password Reset Routes
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.forgot');

Route::post('/forgot-password', [\App\Http\Controllers\Api\Auth\ForgotPasswordController::class, 'sendOtp'])->name('password.email');
Route::get('/reset-password', function () {
    return view('auth.reset-password');
})->name('password.reset.form');
Route::post('/reset-password', [\App\Http\Controllers\Api\Auth\ForgotPasswordController::class, 'resetPassword'])->name('password.update');

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
    Route::get('/payments', [\App\Http\Controllers\Customer\PaymentController::class, 'index'])->name('payments.index');
    Route::post('/requests/{lineRequest}/pay', [\App\Http\Controllers\PaymentController::class, 'initiate'])->name('requests.pay');
    Route::get('/requests/{lineRequest}/payment-status', [\App\Http\Controllers\PaymentController::class, 'checkStatus'])->name('requests.payment-status');

    // Invoice Routes
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');

    // Chat Routes
    Route::get('/chat', [\App\Http\Controllers\Customer\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{lineRequest}', [\App\Http\Controllers\Customer\ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{lineRequest}', [\App\Http\Controllers\Customer\ChatController::class, 'store'])->name('chat.store');

    // Support Routes
    Route::get('/support', [\App\Http\Controllers\Customer\SupportController::class, 'index'])->name('support.index');
    Route::post('/support', [\App\Http\Controllers\Customer\SupportController::class, 'store'])->name('support.store');

    // Profile Routes
    Route::get('/profile', [\App\Http\Controllers\Web\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/name', [\App\Http\Controllers\Web\ProfileController::class, 'updateName'])->name('profile.name.update');
    Route::put('/profile/password', [\App\Http\Controllers\Web\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/picture', [\App\Http\Controllers\Web\ProfileController::class, 'uploadPicture'])->name('profile.picture.upload');
    Route::delete('/profile/picture', [\App\Http\Controllers\Web\ProfileController::class, 'deletePicture'])->name('profile.picture.delete');
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
    Route::post('/requests/{lineRequest}/retry-payment', [\App\Http\Controllers\Agent\RequestActionController::class, 'retryPayment'])->name('requests.retry-payment');
    Route::get('/requests/{lineRequest}/payment-status', [\App\Http\Controllers\PaymentController::class, 'checkStatus'])->name('requests.payment-status');
    
    // Available Gigs
    Route::get('/gigs', [\App\Http\Controllers\Agent\AvailableGigsController::class, 'index'])->name('gigs.index');

    // Earnings
    Route::get('/earnings', [\App\Http\Controllers\Agent\EarningsController::class, 'index'])->name('earnings.index');

    // Documents
    Route::post('/documents/upload', [\App\Http\Controllers\Agent\DocumentController::class, 'upload'])->name('documents.upload');

    // Chat Routes
    Route::get('/chat', [\App\Http\Controllers\Agent\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{lineRequest}', [\App\Http\Controllers\Agent\ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{lineRequest}', [\App\Http\Controllers\Agent\ChatController::class, 'store'])->name('chat.store');

    // Working Hours
    Route::get('/working-hours', [\App\Http\Controllers\Agent\WorkingHoursController::class, 'index'])->name('working-hours.index');
    Route::post('/working-hours', [\App\Http\Controllers\Agent\WorkingHoursController::class, 'update'])->name('working-hours.update');

    // Support
    Route::get('/support', [\App\Http\Controllers\Agent\SupportController::class, 'index'])->name('support.index');
    Route::post('/support', [\App\Http\Controllers\Agent\SupportController::class, 'store'])->name('support.store');

    // Profile Routes
    Route::get('/profile', [\App\Http\Controllers\Web\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/name', [\App\Http\Controllers\Web\ProfileController::class, 'updateName'])->name('profile.name.update');
    Route::put('/profile/password', [\App\Http\Controllers\Web\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/picture', [\App\Http\Controllers\Web\ProfileController::class, 'uploadPicture'])->name('profile.picture.upload');
    Route::delete('/profile/picture', [\App\Http\Controllers\Web\ProfileController::class, 'deletePicture'])->name('profile.picture.delete');
    Route::post('/profile/withdrawal', [\App\Http\Controllers\Web\ProfileController::class, 'requestWithdrawal'])->name('profile.withdrawal.request');
});

// Admin Dashboard
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Analytics
    Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/data', [\App\Http\Controllers\Admin\AnalyticsController::class, 'data'])->name('analytics.data');

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
    
    // Agent Documents (bypasses storage link 403 issues)
    Route::get('/documents/{document}/view', [\App\Http\Controllers\Admin\AgentController::class, 'viewDocument'])->name('documents.view');
    Route::get('/documents/{document}/download', [\App\Http\Controllers\Admin\AgentController::class, 'downloadDocument'])->name('documents.download');

    // Support
    Route::get('/support', [\App\Http\Controllers\Admin\SupportController::class, 'index'])->name('support.index');
    Route::get('/support/{user}', [\App\Http\Controllers\Admin\SupportController::class, 'show'])->name('support.show');
    Route::post('/support/reply', [\App\Http\Controllers\Admin\SupportController::class, 'reply'])->name('support.reply');

    // Orders
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');

    // Payments
    Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');

    // Withdrawals
    Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals/{withdrawal}/approve', [\App\Http\Controllers\Admin\WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/withdrawals/{withdrawal}/reject', [\App\Http\Controllers\Admin\WithdrawalController::class, 'reject'])->name('withdrawals.reject');

    // Activity
    Route::get('/activity', [\App\Http\Controllers\Admin\ActivityController::class, 'index'])->name('activity.index');

    // Tickets (Detailed View)
    Route::resource('tickets', \App\Http\Controllers\Admin\TicketController::class)->only(['index', 'show']);
    Route::post('/tickets/{ticket}/reply', [\App\Http\Controllers\Admin\TicketController::class, 'reply'])->name('tickets.reply');
});

