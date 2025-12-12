<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\LineRequestController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Agent\WorkingHoursController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Get available networks with logos
Route::get('/networks', function () {
    // Use url() helper to automatically detect the current domain
    // This works correctly on both localhost and production servers
    return response()->json([
        [
            'id' => 'vodacom',
            'name' => 'Vodacom',
            'logo' => url('/images/networks/vodacom.png'),
            'color' => '#E60000',
        ],
        [
            'id' => 'airtel',
            'name' => 'Airtel',
            'logo' => url('/images/networks/airtel.png'),
            'color' => '#FF0000',
        ],
        [
            'id' => 'tigo',
            'name' => 'Tigo',
            'logo' => url('/images/networks/tigo.png'),
            'color' => '#00377B',
        ],
        [
            'id' => 'halotel',
            'name' => 'Halotel',
            'logo' => url('/images/networks/halotel.jpeg'),
            'color' => '#FF6600',
        ],
        [
            'id' => 'zantel',
            'name' => 'Zantel',
            'logo' => url('/images/networks/zantel.jpeg'),
            'color' => '#009639',
        ],
    ]);
});

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

    // Notifications (Old - SystemNotification)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // In-App Notifications (New - comprehensive)
    Route::prefix('in-app-notifications')->group(function () {
        // Static routes MUST come first
        Route::get('/', [\App\Http\Controllers\Api\InAppNotificationController::class, 'index']);
        Route::get('/unread-count', [\App\Http\Controllers\Api\InAppNotificationController::class, 'unreadCount']);
        Route::get('/summary', [\App\Http\Controllers\Api\InAppNotificationController::class, 'summary']);
        Route::post('/read-all', [\App\Http\Controllers\Api\InAppNotificationController::class, 'markAllAsRead']);
        Route::post('/clear-read', [\App\Http\Controllers\Api\InAppNotificationController::class, 'clearRead']);
        
        // Dynamic {id} routes come after static routes
        Route::get('/{id}', [\App\Http\Controllers\Api\InAppNotificationController::class, 'show'])->whereNumber('id');
        Route::post('/{id}/read', [\App\Http\Controllers\Api\InAppNotificationController::class, 'markAsRead'])->whereNumber('id');
        Route::delete('/{id}', [\App\Http\Controllers\Api\InAppNotificationController::class, 'destroy'])->whereNumber('id');
    });

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
        Route::post('/tickets/{ticket}/close', [SupportController::class, 'close']);
        
        // Get support contact info (WhatsApp, Phone, Email)
        Route::get('/contact', function () {
            return response()->json([
                'whatsapp' => [
                    'number' => config('services.support.whatsapp', '+255712345678'),
                    'url' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', config('services.support.whatsapp', '255712345678')),
                    'message' => 'Habari, nahitaji msaada kuhusu Sky Laini.',
                    'full_url' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', config('services.support.whatsapp', '255712345678')) . '?text=' . urlencode('Habari, nahitaji msaada kuhusu Sky Laini.'),
                ],
                'phone' => config('services.support.phone', '+255712345678'),
                'email' => config('services.support.email', 'support@skylaini.co.tz'),
                'working_hours' => [
                    'weekdays' => '08:00 - 18:00',
                    'weekends' => '09:00 - 15:00',
                ],
            ]);
        });
        
        // Admin chat (for direct messaging with support)
        Route::prefix('chat')->group(function () {
            Route::get('/messages', function (Request $request) {
                // Get or create admin support ticket for chat
                $ticket = \App\Models\SupportTicket::firstOrCreate(
                    [
                        'user_id' => $request->user()->id,
                        'category' => 'chat',
                        'status' => 'open',
                    ],
                    [
                        'subject' => 'Live Chat Support',
                        'message' => 'Started live chat session',
                    ]
                );
                
                $messages = $ticket->messages()
                    ->with('user:id,name,role')
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(function ($msg) use ($request) {
                        return [
                            'id' => $msg->id,
                            'message' => $msg->message,
                            'is_mine' => $msg->user_id === $request->user()->id,
                            'sender_name' => $msg->user?->name ?? 'Support',
                            'is_admin' => $msg->user?->role?->value === 'admin',
                            'created_at' => $msg->created_at->diffForHumans(),
                            'timestamp' => $msg->created_at->toIso8601String(),
                        ];
                    });
                
                return response()->json([
                    'ticket_id' => $ticket->id,
                    'messages' => $messages,
                ]);
            });
            
            Route::post('/send', function (Request $request) {
                $request->validate([
                    'message' => 'required|string|max:1000',
                ]);
                
                // Get or create admin support ticket for chat
                $ticket = \App\Models\SupportTicket::firstOrCreate(
                    [
                        'user_id' => $request->user()->id,
                        'category' => 'chat',
                        'status' => 'open',
                    ],
                    [
                        'subject' => 'Live Chat Support',
                        'message' => $request->message,
                    ]
                );
                
                $message = $ticket->messages()->create([
                    'user_id' => $request->user()->id,
                    'message' => $request->message,
                ]);
                
                // Update ticket to show activity
                $ticket->touch();
                
                return response()->json([
                    'id' => $message->id,
                    'message' => $message->message,
                    'is_mine' => true,
                    'sender_name' => $request->user()->name,
                    'created_at' => $message->created_at->diffForHumans(),
                    'timestamp' => $message->created_at->toIso8601String(),
                ], 201);
            });
        });
    });

    // Customer routes
    Route::prefix('customer')->group(function () {
        Route::get('/profile', [CustomerController::class, 'profile']);
        Route::put('/profile', [CustomerController::class, 'updateProfile']);
        Route::post('/location', [CustomerController::class, 'updateLocation']);
    });

    // Nearby Agents - Get all agents with online/offline status for map
    Route::get('/agents/nearby', function (Request $request) {
        $customer = $request->user()->customer;
        
        // Get customer location if available
        $customerLat = $customer?->current_latitude ?? null;
        $customerLng = $customer?->current_longitude ?? null;
        
        // Get all verified agents with their locations
        $agents = \App\Models\Agent::where('is_verified', true)
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->with('user:id,name')
            ->get(['id', 'user_id', 'current_latitude', 'current_longitude', 'is_online', 'phone', 'rating', 'specialization']);
        
        // Calculate distance if customer location is available
        $agentsWithDistance = $agents->map(function ($agent) use ($customerLat, $customerLng) {
            $agentData = [
                'id' => $agent->id,
                'name' => $agent->user?->name ?? 'Unknown',
                'phone' => $agent->phone,
                'latitude' => $agent->current_latitude,
                'longitude' => $agent->current_longitude,
                'is_online' => $agent->is_online,
                'rating' => $agent->rating,
                'specialization' => $agent->specialization,
            ];
            
            // Calculate distance if customer location is available
            if ($customerLat && $customerLng) {
                $distance = \App\Services\LocationService::calculateDistanceStatic(
                    $customerLat,
                    $customerLng,
                    $agent->current_latitude,
                    $agent->current_longitude
                );
                $agentData['distance_km'] = round($distance, 2);
            }
            
            return $agentData;
        });
        
        // Separate into online and offline
        $onlineAgents = $agentsWithDistance->where('is_online', true)->values();
        $offlineAgents = $agentsWithDistance->where('is_online', false)->values();
        
        return response()->json([
            'online' => $onlineAgents,
            'offline' => $offlineAgents,
            'total_online' => $onlineAgents->count(),
            'total_offline' => $offlineAgents->count(),
            'all' => $agentsWithDistance->values(),
        ]);
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
        Route::get('/requests/{lineRequest}/payment-status', [PaymentController::class, 'checkStatus']);
        
        // Full tracking data for agent (with payment check, directions, distance)
        Route::get('/requests/{lineRequest}/tracking', function (Request $request, \App\Models\LineRequest $lineRequest) {
            $agent = $request->user()->agent;
            if (!$agent || $lineRequest->agent_id !== $agent->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            $lineRequest->load(['customer.user']);
            
            // Check if paid
            $isPaid = $lineRequest->payment_status === 'paid';
            $canNavigate = $isPaid && in_array($lineRequest->status->value ?? $lineRequest->status, ['accepted', 'in_progress']);
            
            // Get locations
            $agentLat = $agent->current_latitude;
            $agentLng = $agent->current_longitude;
            $customerLat = $lineRequest->customer_latitude;
            $customerLng = $lineRequest->customer_longitude;
            
            // Calculate distance if both locations available
            $distance = null;
            $estimatedMinutes = null;
            if ($agentLat && $agentLng && $customerLat && $customerLng) {
                $distance = \App\Services\LocationService::calculateDistanceStatic(
                    $agentLat, $agentLng, $customerLat, $customerLng
                );
                $estimatedMinutes = round(($distance / 30) * 60); // 30 km/h average
            }
            
            // Build navigation URLs
            $origin = "{$agentLat},{$agentLng}";
            $destination = "{$customerLat},{$customerLng}";
            
            $response = [
                'request_id' => $lineRequest->id,
                'request_number' => $lineRequest->request_number,
                'status' => $lineRequest->status->value ?? $lineRequest->status,
                'line_type' => $lineRequest->line_type->value ?? $lineRequest->line_type,
                
                // Payment info
                'payment' => [
                    'status' => $lineRequest->payment_status ?? 'pending',
                    'is_paid' => $isPaid,
                    'confirmation_code' => $isPaid ? $lineRequest->confirmation_code : null,
                    'amount' => $lineRequest->service_fee ?? 1000,
                ],
                
                // Can navigate (only after payment)
                'can_navigate' => $canNavigate,
                
                // Customer info
                'customer' => [
                    'name' => $lineRequest->customer?->user?->name ?? 'Customer',
                    'phone' => $lineRequest->customer_phone,
                    'address' => $lineRequest->customer_address,
                    'latitude' => $customerLat,
                    'longitude' => $customerLng,
                ],
                
                // Agent current position
                'agent_location' => [
                    'latitude' => $agentLat,
                    'longitude' => $agentLng,
                ],
            ];
            
            // Add navigation data only if paid
            if ($canNavigate) {
                $response['tracking'] = [
                    'distance_km' => $distance ? round($distance, 2) : null,
                    'estimated_minutes' => $estimatedMinutes,
                    'estimated_arrival' => $estimatedMinutes ? now()->addMinutes($estimatedMinutes)->format('H:i') : null,
                ];
                
                $response['navigation'] = [
                    'google_maps_url' => "https://www.google.com/maps/dir/{$origin}/{$destination}",
                    'directions_api_url' => "https://www.google.com/maps/dir/?api=1&origin={$origin}&destination={$destination}&travelmode=driving",
                    'android_intent' => "google.navigation:q={$destination}",
                    'ios_intent' => "comgooglemaps://?daddr={$destination}&directionsmode=driving",
                    'waze_url' => "https://waze.com/ul?ll={$customerLat},{$customerLng}&navigate=yes",
                ];
                
                $response['route'] = [
                    'start' => ['latitude' => $agentLat, 'longitude' => $agentLng],
                    'end' => ['latitude' => $customerLat, 'longitude' => $customerLng],
                ];
            }
            
            return response()->json($response);
        });

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
        Route::get('/{lineRequest}/directions', [\App\Http\Controllers\Customer\TrackingController::class, 'getTrackingWithDirections']);
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

    // User profile management routes
    Route::prefix('profile')->group(function () {
        // Get current user profile
        Route::get('/', [ProfileController::class, 'show']);
        
        // Update profile (name, phone)
        Route::put('/', [ProfileController::class, 'update']);
        
        // Update name only
        Route::put('/name', [ProfileController::class, 'updateName']);
        
        // Update password
        Route::put('/password', [ProfileController::class, 'updatePassword']);
        
        // Upload profile picture
        Route::post('/picture', [ProfileController::class, 'uploadProfilePicture']);
        
        // Delete profile picture
        Route::delete('/picture', [ProfileController::class, 'deleteProfilePicture']);
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
// Password Reset (OTP)
Route::post('/password/email', [ForgotPasswordController::class, 'sendOtp']);
Route::post('/password/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/password/reset', [ForgotPasswordController::class, 'resetPassword']);

