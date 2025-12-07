<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\LineRequestController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Agent\WorkingHoursController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public leaderboard
Route::get('/leaderboard', [LeaderboardController::class, 'data']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // User settings (language, profile)
    Route::prefix('settings')->group(function () {
        Route::put('/language', function (Request $request) {
            $request->validate(['locale' => 'required|in:sw,en']);
            $request->user()->update(['locale' => $request->locale]);
            return response()->json(['message' => 'Language updated', 'locale' => $request->locale]);
        });
        Route::get('/language', function (Request $request) {
            return response()->json(['locale' => $request->user()->locale ?? 'sw']);
        });
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Chat routes
    Route::prefix('chat')->group(function () {
        Route::get('/conversations', [ChatController::class, 'conversations']);
        Route::get('/unread-count', [ChatController::class, 'unreadCount']);
        Route::get('/{lineRequest}', [ChatController::class, 'index']);
        Route::post('/{lineRequest}', [ChatController::class, 'store']);
        Route::post('/{lineRequest}/read', [ChatController::class, 'markAsRead']);
    });

    // Invoices
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::get('/{invoice}', [InvoiceController::class, 'show']);
        Route::get('/{invoice}/download', [InvoiceController::class, 'download']);
        Route::post('/generate/{lineRequest}', [InvoiceController::class, 'generate']);
    });

    // Support
    Route::prefix('support')->group(function () {
        Route::get('/tickets', [SupportController::class, 'index']);
        Route::post('/tickets', [SupportController::class, 'store']);
        Route::get('/tickets/{ticket}', [SupportController::class, 'show']);
        Route::post('/tickets/{ticket}/reply', [SupportController::class, 'reply']);
    });

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
        
        Route::get('/gigs', [AgentController::class, 'gigs']);
        
        Route::get('/requests', [AgentController::class, 'requests']);
        Route::get('/requests/{lineRequest}', [AgentController::class, 'showRequest']);
        Route::post('/requests/{lineRequest}/respond', [AgentController::class, 'respondToRequest']);
        Route::post('/requests/{lineRequest}/complete', [PaymentController::class, 'completeJob']);

        // Wallet & Earnings
        Route::get('/wallet', [WalletController::class, 'index']);
        Route::post('/withdraw', [WalletController::class, 'withdraw']);

        // Documents
        Route::get('/documents', [DocumentController::class, 'index']);
        Route::post('/documents', [DocumentController::class, 'store']);

        // Working Hours
        Route::prefix('working-hours')->group(function () {
            Route::get('/', [WorkingHoursController::class, 'index']);
            Route::put('/', [WorkingHoursController::class, 'update']);
            Route::get('/status', [WorkingHoursController::class, 'checkStatus']);
        });

        // Get directions to customer
        Route::get('/requests/{lineRequest}/directions', function (Request $request, \App\Models\LineRequest $lineRequest) {
            $agent = $request->user()->agent;
            if (!$agent || $lineRequest->agent_id !== $agent->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $origin = "{$agent->current_latitude},{$agent->current_longitude}";
            $destination = "{$lineRequest->customer_latitude},{$lineRequest->customer_longitude}";

            return response()->json([
                'origin' => [
                    'latitude' => $agent->current_latitude,
                    'longitude' => $agent->current_longitude,
                ],
                'destination' => [
                    'latitude' => $lineRequest->customer_latitude,
                    'longitude' => $lineRequest->customer_longitude,
                    'address' => $lineRequest->customer_address,
                ],
                'google_maps_url' => "https://www.google.com/maps/dir/{$origin}/{$destination}",
                'navigation_intent' => [
                    'android' => "google.navigation:q={$destination}",
                    'ios' => "comgooglemaps://?daddr={$destination}&directionsmode=driving",
                    'web' => "https://www.google.com/maps/dir/?api=1&origin={$origin}&destination={$destination}&travelmode=driving",
                ],
            ]);
        });

        // Toggle online/offline status
        Route::post('/toggle-status', [\App\Http\Controllers\Agent\AgentLocationController::class, 'toggle']);

        // Request Actions (accept, reject, release, retry payment)
        Route::post('/requests/{lineRequest}/accept', [\App\Http\Controllers\Agent\RequestActionController::class, 'accept']);
        Route::post('/requests/{lineRequest}/reject', [\App\Http\Controllers\Agent\RequestActionController::class, 'reject']);
        Route::post('/requests/{lineRequest}/release', [\App\Http\Controllers\Agent\RequestActionController::class, 'release']);
        Route::post('/requests/{lineRequest}/retry-payment', [\App\Http\Controllers\Agent\RequestActionController::class, 'retryPayment']);

        // Dashboard stats
        Route::get('/dashboard', function (Request $request) {
            $agent = $request->user()->agent;
            if (!$agent) {
                return response()->json(['message' => 'Agent profile not found'], 404);
            }

            $pendingRequests = \App\Models\LineRequest::where('agent_id', $agent->id)
                ->where('status', \App\RequestStatus::Pending)
                ->count();

            $activeRequests = \App\Models\LineRequest::where('agent_id', $agent->id)
                ->whereIn('status', [\App\RequestStatus::Accepted, \App\RequestStatus::InProgress])
                ->count();

            $completedToday = \App\Models\LineRequest::where('agent_id', $agent->id)
                ->where('status', \App\RequestStatus::Completed)
                ->whereDate('completed_at', today())
                ->count();

            $earningsToday = \App\Models\LineRequest::where('agent_id', $agent->id)
                ->where('status', \App\RequestStatus::Completed)
                ->whereDate('completed_at', today())
                ->sum('commission');

            return response()->json([
                'agent' => $agent->load('user', 'wallet'),
                'stats' => [
                    'pending_requests' => $pendingRequests,
                    'active_requests' => $activeRequests,
                    'completed_today' => $completedToday,
                    'earnings_today' => $earningsToday,
                    'total_earnings' => $agent->total_earnings ?? 0,
                    'total_completed' => $agent->total_completed_requests ?? 0,
                    'rating' => $agent->rating ?? 0,
                    'is_online' => $agent->is_online,
                    'is_verified' => $agent->is_verified,
                ],
            ]);
        });

        // Get available gigs count
        Route::get('/gigs/count', function (Request $request) {
            $count = \App\Models\LineRequest::where('status', \App\RequestStatus::Pending)
                ->whereNull('agent_id')
                ->count();

            return response()->json(['count' => $count]);
        });
    });

    // Line request routes (Customer)
    Route::prefix('line-requests')->group(function () {
        Route::get('/', [LineRequestController::class, 'index']);
        Route::post('/', [LineRequestController::class, 'store']);
        Route::get('/{lineRequest}', [LineRequestController::class, 'show']);
        Route::post('/{lineRequest}/cancel', [LineRequestController::class, 'cancel']);
        Route::post('/{lineRequest}/rate', [LineRequestController::class, 'rate']);
        
        // Payment routes for Customer
        Route::post('/{lineRequest}/pay', [PaymentController::class, 'initiate']);
        Route::post('/{lineRequest}/cancel-pay', [PaymentController::class, 'cancelJobPayment']);
        Route::get('/{lineRequest}/payment-status', [PaymentController::class, 'checkStatus']);

        // Directions for customer (to track agent)
        Route::get('/{lineRequest}/agent-location', function (Request $request, \App\Models\LineRequest $lineRequest) {
            if ($lineRequest->customer?->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $agent = $lineRequest->agent;
            if (!$agent) {
                return response()->json(['message' => 'No agent assigned'], 404);
            }

            return response()->json([
                'agent' => [
                    'name' => $agent->user?->name,
                    'phone' => $agent->phone,
                    'latitude' => $agent->current_latitude,
                    'longitude' => $agent->current_longitude,
                    'is_online' => $agent->is_online,
                    'rating' => $agent->rating,
                ],
                'customer_location' => [
                    'latitude' => $lineRequest->customer_latitude,
                    'longitude' => $lineRequest->customer_longitude,
                ],
            ]);
        });
    });

    // Customer tracking routes
    Route::prefix('tracking')->group(function () {
        Route::get('/{lineRequest}', [\App\Http\Controllers\Customer\TrackingController::class, 'getAgentLocation']);
        Route::post('/location', [\App\Http\Controllers\Customer\TrackingController::class, 'updateCustomerLocation']);
    });

    // Customer dashboard
    Route::get('/customer/dashboard', function (Request $request) {
        $customer = $request->user()->customer;
        if (!$customer) {
            return response()->json(['message' => 'Customer profile not found'], 404);
        }

        $activeRequests = $customer->lineRequests()
            ->whereIn('status', [\App\RequestStatus::Pending, \App\RequestStatus::Accepted, \App\RequestStatus::InProgress])
            ->with(['agent.user'])
            ->get();

        $completedRequests = $customer->lineRequests()
            ->where('status', \App\RequestStatus::Completed)
            ->count();

        $totalSpent = $customer->lineRequests()
            ->where('payment_status', 'paid')
            ->sum('service_fee');

        return response()->json([
            'customer' => $customer->load('user'),
            'active_requests' => $activeRequests,
            'stats' => [
                'total_requests' => $customer->lineRequests()->count(),
                'active_count' => $activeRequests->count(),
                'completed_count' => $completedRequests,
                'total_spent' => $totalSpent,
            ],
        ]);
    });

    // Password update
    Route::put('/password', function (Request $request) {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    });

    // User profile
    Route::get('/profile', function (Request $request) {
        $user = $request->user();
        $user->load(['customer', 'agent.wallet']);

        return response()->json([
            'user' => $user,
            'role' => $user->role->value ?? 'customer',
        ]);
    });

    Route::put('/profile', function (Request $request) {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'user' => $request->user()->fresh(),
            'message' => 'Profile updated successfully',
        ]);
    });

    // Invoice print
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print']);
});

// System settings (public)
Route::get('/settings/price', function () {
    $price = \App\Models\SystemSetting::where('key', 'price_per_laini')->value('value') ?? 1000;
    return response()->json(['price' => (int) $price]);
});

// App info/version
Route::get('/app/info', function () {
    return response()->json([
        'app_name' => config('app.name', 'Sky Laini'),
        'version' => '1.0.0',
        'min_version' => '1.0.0',
        'update_required' => false,
        'maintenance_mode' => false,
    ]);
});

// Forgot password / Reset password (public)
Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'If this email exists, a reset link has been sent.']);
    }

    // In a real app, you'd send an email with a reset token
    // For now, we just return a success message
    return response()->json(['message' => 'If this email exists, a reset link has been sent.']);
});

