<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Firebase Cloud Messaging settings here.
    | You need to get these from Firebase Console > Project Settings > Cloud Messaging
    |
    */

    // Firebase Project ID
    'project_id' => env('FIREBASE_PROJECT_ID', 'skyline-c84aa'),

    // Server Key (Legacy) - from Firebase Console > Project Settings > Cloud Messaging > Server key
    'server_key' => env('FIREBASE_SERVER_KEY', ''),

    // FCM Legacy API URL
    'fcm_url' => 'https://fcm.googleapis.com/fcm/send',

    // FCM v1 API URL (newer, recommended)
    'fcm_v1_url' => 'https://fcm.googleapis.com/v1/projects/skyline-c84aa/messages:send',

    // Service Account JSON file path (for v1 API with OAuth2)
    'service_account_path' => env('FIREBASE_SERVICE_ACCOUNT_PATH', storage_path('app/firebase/service-account.json')),

    // Default notification settings
    'defaults' => [
        'sound' => 'default',
        'badge' => 1,
        'android' => [
            'channel_id' => 'sky_laini_channel',
            'priority' => 'high',
            'notification_priority' => 'PRIORITY_HIGH',
        ],
        'ios' => [
            'sound' => 'default',
            'badge' => 1,
        ],
    ],

    // Notification Icons
    'icons' => [
        'default' => '@drawable/ic_notification',
        'large' => '@drawable/ic_launcher',
    ],
];

