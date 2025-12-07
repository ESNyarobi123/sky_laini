<?php

return [
    // General
    'welcome' => 'Welcome',
    'home' => 'Home',
    'dashboard' => 'Dashboard',
    'logout' => 'Logout',
    'login' => 'Login',
    'register' => 'Register',
    'profile' => 'Profile',
    'settings' => 'Settings',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'edit' => 'Edit',
    'view' => 'View',
    'search' => 'Search',
    'loading' => 'Loading...',
    'success' => 'Success',
    'error' => 'Error',
    'warning' => 'Warning',
    'info' => 'Info',
    'confirm' => 'Confirm',
    'yes' => 'Yes',
    'no' => 'No',
    'back' => 'Back',
    'next' => 'Next',
    'previous' => 'Previous',
    'submit' => 'Submit',
    'close' => 'Close',
    'open' => 'Open',
    'all' => 'All',
    'none' => 'None',
    'select' => 'Select',
    'upload' => 'Upload',
    'download' => 'Download',
    
    // Navigation
    'nav' => [
        'dashboard' => 'Dashboard',
        'requests' => 'Requests',
        'payments' => 'Payments',
        'support' => 'Support',
        'chat' => 'Chat',
        'leaderboard' => 'Leaderboard',
        'analytics' => 'Analytics',
        'settings' => 'Settings',
        'agents' => 'Agents',
        'users' => 'Users',
    ],
    
    // Authentication
    'auth' => [
        'login' => 'Login',
        'register' => 'Register',
        'logout' => 'Logout',
        'forgot_password' => 'Forgot password?',
        'reset_password' => 'Reset password',
        'email' => 'Email',
        'password' => 'Password',
        'confirm_password' => 'Confirm password',
        'remember_me' => 'Remember me',
        'name' => 'Name',
        'phone' => 'Phone',
        'role' => 'Role',
        'customer' => 'Customer',
        'agent' => 'Agent',
        'admin' => 'Admin',
    ],
    
    // Dashboard
    'dashboard_page' => [
        'greeting' => 'Welcome, :name 👋',
        'subtitle_customer' => 'Manage your line requests here.',
        'subtitle_agent' => 'Manage your jobs and earnings here.',
        'total_requests' => 'Total Requests',
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'earnings' => 'Earnings',
        'since_start' => 'Since you started',
        'agent_on_way' => 'Agent on the way',
        'got_line' => 'Line received',
        'processing' => 'Processing',
        'new_request' => 'New Request',
        'available_gigs' => 'Available Gigs',
        'my_earnings' => 'My Earnings',
    ],
    
    // Line Requests
    'requests' => [
        'title' => 'Line Requests',
        'new' => 'New Request',
        'my_requests' => 'My Requests',
        'request_number' => 'Request Number',
        'line_type' => 'Line Type',
        'status' => 'Status',
        'agent' => 'Agent',
        'customer' => 'Customer',
        'created_at' => 'Date',
        'actions' => 'Actions',
        'view' => 'View',
        'cancel' => 'Cancel',
        'accept' => 'Accept',
        'reject' => 'Reject',
        'complete' => 'Complete',
        'rate' => 'Rate',
        'no_requests' => 'You have no requests yet.',
        'searching_agent' => 'Searching for agent...',
        'confirmation_code' => 'Confirmation Code',
        'enter_code' => 'Enter code',
        'invalid_code' => 'Invalid code',
        'job_completed' => 'Job completed!',
    ],
    
    // Statuses
    'status' => [
        'pending' => 'Pending',
        'accepted' => 'Accepted',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'paid' => 'Paid',
        'unpaid' => 'Unpaid',
    ],
    
    // Chat
    'chat' => [
        'title' => 'Chat',
        'send' => 'Send',
        'type_message' => 'Type a message...',
        'no_messages' => 'No messages yet',
        'new_message' => 'New message',
        'online' => 'Online',
        'offline' => 'Offline',
        'typing' => 'Typing...',
        'delivered' => 'Delivered',
        'read' => 'Read',
        'attachment' => 'Attachment',
        'send_image' => 'Send image',
    ],
    
    // Payments
    'payments' => [
        'title' => 'Payments',
        'pay_now' => 'Pay Now',
        'payment_pending' => 'Payment pending',
        'payment_complete' => 'Payment complete',
        'amount' => 'Amount',
        'method' => 'Method',
        'date' => 'Date',
        'status' => 'Status',
        'invoice' => 'Invoice',
        'download_invoice' => 'Download Invoice',
        'transaction_id' => 'Transaction ID',
    ],
    
    // Invoice
    'invoice' => [
        'title' => 'Invoice',
        'invoice_number' => 'Invoice Number',
        'date' => 'Date',
        'customer' => 'Customer',
        'description' => 'Description',
        'quantity' => 'Quantity',
        'price' => 'Price',
        'subtotal' => 'Subtotal',
        'tax' => 'Tax',
        'total' => 'Total',
        'thank_you' => 'Thank you for your business!',
        'contact' => 'Contact',
    ],
    
    // Agent
    'agent' => [
        'profile' => 'Agent Profile',
        'rating' => 'Rating',
        'completed_jobs' => 'Completed Jobs',
        'total_earnings' => 'Total Earnings',
        'available' => 'Available',
        'unavailable' => 'Unavailable',
        'online' => 'Online',
        'offline' => 'Offline',
        'verified' => 'Verified',
        'unverified' => 'Unverified',
        'working_hours' => 'Working Hours',
        'set_hours' => 'Set working hours',
    ],
    
    // Leaderboard
    'leaderboard' => [
        'title' => 'Top Agents Leaderboard',
        'rank' => 'Rank',
        'agent' => 'Agent',
        'rating' => 'Rating',
        'jobs' => 'Jobs',
        'earnings' => 'Earnings',
        'top_agents' => 'Top Agents',
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'all_time' => 'All Time',
    ],
    
    // Analytics
    'analytics' => [
        'title' => 'Analytics',
        'overview' => 'Overview',
        'requests_trend' => 'Requests Trend',
        'popular_lines' => 'Popular Line Types',
        'agent_performance' => 'Agent Performance',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
    ],
    
    // Support
    'support' => [
        'title' => 'Support',
        'new_ticket' => 'New Ticket',
        'my_tickets' => 'My Tickets',
        'subject' => 'Subject',
        'message' => 'Message',
        'priority' => 'Priority',
        'high' => 'High',
        'medium' => 'Medium',
        'low' => 'Low',
        'reply' => 'Reply',
        'close_ticket' => 'Close ticket',
    ],
    
    // Map & Navigation
    'map' => [
        'your_location' => 'Your location',
        'agent_location' => 'Agent location',
        'customer_location' => 'Customer location',
        'get_directions' => 'Get directions',
        'live_tracking' => 'Live tracking',
        'distance' => 'Distance',
        'eta' => 'ETA',
    ],
    
    // Settings
    'settings_page' => [
        'title' => 'Settings',
        'language' => 'Language',
        'notifications' => 'Notifications',
        'privacy' => 'Privacy',
        'account' => 'Account',
        'change_password' => 'Change password',
        'delete_account' => 'Delete account',
    ],
    
    // Time
    'time' => [
        'just_now' => 'Just now',
        'minutes_ago' => ':count minutes ago',
        'hours_ago' => ':count hours ago',
        'days_ago' => ':count days ago',
        'today' => 'Today',
        'yesterday' => 'Yesterday',
    ],
    
    // Days
    'days' => [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    ],
    
    // Errors
    'errors' => [
        'general' => 'An error occurred. Please try again.',
        'not_found' => 'Not found',
        'unauthorized' => 'Unauthorized',
        'forbidden' => 'Forbidden',
        'validation' => 'Invalid data',
    ],
];
