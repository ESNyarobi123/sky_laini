<?php

return [
    // General
    'welcome' => 'Karibu',
    'home' => 'Nyumbani',
    'dashboard' => 'Dashibodi',
    'logout' => 'Toka',
    'login' => 'Ingia',
    'register' => 'Jisajili',
    'profile' => 'Wasifu',
    'settings' => 'Mipangilio',
    'save' => 'Hifadhi',
    'cancel' => 'Ghairi',
    'delete' => 'Futa',
    'edit' => 'Hariri',
    'view' => 'Angalia',
    'search' => 'Tafuta',
    'loading' => 'Inapakia...',
    'success' => 'Imefanikiwa',
    'error' => 'Hitilafu',
    'warning' => 'Onyo',
    'info' => 'Taarifa',
    'confirm' => 'Thibitisha',
    'yes' => 'Ndiyo',
    'no' => 'Hapana',
    'back' => 'Rudi',
    'next' => 'Endelea',
    'previous' => 'Iliyopita',
    'submit' => 'Wasilisha',
    'close' => 'Funga',
    'open' => 'Fungua',
    'all' => 'Zote',
    'none' => 'Hakuna',
    'select' => 'Chagua',
    'upload' => 'Pakia',
    'download' => 'Pakua',
    
    // Navigation
    'nav' => [
        'dashboard' => 'Dashibodi',
        'requests' => 'Maombi',
        'payments' => 'Malipo',
        'support' => 'Msaada',
        'chat' => 'Mazungumzo',
        'leaderboard' => 'Chati ya Mawakala',
        'analytics' => 'Takwimu',
        'settings' => 'Mipangilio',
        'agents' => 'Mawakala',
        'users' => 'Watumiaji',
    ],
    
    // Authentication
    'auth' => [
        'login' => 'Ingia',
        'register' => 'Jisajili',
        'logout' => 'Toka',
        'forgot_password' => 'Umesahau nywila?',
        'reset_password' => 'Weka upya nywila',
        'email' => 'Barua pepe',
        'password' => 'Nywila',
        'confirm_password' => 'Thibitisha nywila',
        'remember_me' => 'Nikumbuke',
        'name' => 'Jina',
        'phone' => 'Simu',
        'role' => 'Aina',
        'customer' => 'Mteja',
        'agent' => 'Wakala',
        'admin' => 'Msimamizi',
    ],
    
    // Dashboard
    'dashboard_page' => [
        'greeting' => 'Karibu, :name 👋',
        'subtitle_customer' => 'Dhibiti maombi yako ya laini za simu hapa.',
        'subtitle_agent' => 'Simamia kazi zako na mapato hapa.',
        'total_requests' => 'Jumla ya Maombi',
        'pending' => 'Zinasubiri',
        'in_progress' => 'Zinaendelea',
        'completed' => 'Zimekamilika',
        'earnings' => 'Mapato',
        'since_start' => 'Tangu uanze',
        'agent_on_way' => 'Wakala yuko njiani',
        'got_line' => 'Umepata laini',
        'processing' => 'Inashughulikiwa',
        'new_request' => 'Omba Laini Mpya',
        'available_gigs' => 'Kazi Zinazopatikana',
        'my_earnings' => 'Mapato Yangu',
    ],
    
    // Line Requests
    'requests' => [
        'title' => 'Maombi ya Laini',
        'new' => 'Omba Laini Mpya',
        'my_requests' => 'Maombi Yangu',
        'request_number' => 'Nambari ya Ombi',
        'line_type' => 'Aina ya Laini',
        'status' => 'Hali',
        'agent' => 'Wakala',
        'customer' => 'Mteja',
        'created_at' => 'Tarehe',
        'actions' => 'Kitendo',
        'view' => 'Angalia',
        'cancel' => 'Ghairi',
        'accept' => 'Kubali',
        'reject' => 'Kataa',
        'complete' => 'Kamilisha',
        'rate' => 'Pima',
        'no_requests' => 'Hujafanya maombi yoyote bado.',
        'searching_agent' => 'Inatafuta wakala...',
        'confirmation_code' => 'Nambari ya Uthibitisho',
        'enter_code' => 'Ingiza nambari',
        'invalid_code' => 'Nambari si sahihi',
        'job_completed' => 'Kazi imekamilika!',
    ],
    
    // Statuses
    'status' => [
        'pending' => 'Inasubiri',
        'accepted' => 'Imekubaliwa',
        'in_progress' => 'Inaendelea',
        'completed' => 'Imekamilika',
        'cancelled' => 'Imeghairiwa',
        'paid' => 'Imelipwa',
        'unpaid' => 'Haijalipwa',
    ],
    
    // Chat
    'chat' => [
        'title' => 'Mazungumzo',
        'send' => 'Tuma',
        'type_message' => 'Andika ujumbe...',
        'no_messages' => 'Hakuna ujumbe bado',
        'new_message' => 'Ujumbe mpya',
        'online' => 'Yupo mtandaoni',
        'offline' => 'Hayupo mtandaoni',
        'typing' => 'Anaandika...',
        'delivered' => 'Imewasilishwa',
        'read' => 'Imesomwa',
        'attachment' => 'Kiambatisho',
        'send_image' => 'Tuma picha',
    ],
    
    // Payments
    'payments' => [
        'title' => 'Malipo',
        'pay_now' => 'Lipa Sasa',
        'payment_pending' => 'Malipo yanasubiri',
        'payment_complete' => 'Malipo yamekamilika',
        'amount' => 'Kiasi',
        'method' => 'Njia',
        'date' => 'Tarehe',
        'status' => 'Hali',
        'invoice' => 'Risiti',
        'download_invoice' => 'Pakua Risiti',
        'transaction_id' => 'Nambari ya Muamala',
    ],
    
    // Invoice
    'invoice' => [
        'title' => 'Risiti',
        'invoice_number' => 'Nambari ya Risiti',
        'date' => 'Tarehe',
        'customer' => 'Mteja',
        'description' => 'Maelezo',
        'quantity' => 'Idadi',
        'price' => 'Bei',
        'subtotal' => 'Jumla ndogo',
        'tax' => 'Kodi',
        'total' => 'Jumla',
        'thank_you' => 'Asante kwa biashara yako!',
        'contact' => 'Mawasiliano',
    ],
    
    // Agent
    'agent' => [
        'profile' => 'Wasifu wa Wakala',
        'rating' => 'Kiwango',
        'completed_jobs' => 'Kazi zilizokamilika',
        'total_earnings' => 'Mapato yote',
        'available' => 'Yupo',
        'unavailable' => 'Hayupo',
        'online' => 'Mtandaoni',
        'offline' => 'Nje ya mtandao',
        'verified' => 'Amethibitishwa',
        'unverified' => 'Hajathibitishwa',
        'working_hours' => 'Saa za Kazi',
        'set_hours' => 'Weka saa za kazi',
    ],
    
    // Leaderboard
    'leaderboard' => [
        'title' => 'Chati ya Mawakala Bora',
        'rank' => 'Nafasi',
        'agent' => 'Wakala',
        'rating' => 'Kiwango',
        'jobs' => 'Kazi',
        'earnings' => 'Mapato',
        'top_agents' => 'Mawakala Bora',
        'this_week' => 'Wiki Hii',
        'this_month' => 'Mwezi Huu',
        'all_time' => 'Wakati Wote',
    ],
    
    // Analytics
    'analytics' => [
        'title' => 'Takwimu',
        'overview' => 'Muhtasari',
        'requests_trend' => 'Trend ya Maombi',
        'popular_lines' => 'Laini Zinazopendwa',
        'agent_performance' => 'Utendaji wa Mawakala',
        'daily' => 'Kila siku',
        'weekly' => 'Kila wiki',
        'monthly' => 'Kila mwezi',
        'yearly' => 'Kila mwaka',
    ],
    
    // Support
    'support' => [
        'title' => 'Msaada',
        'new_ticket' => 'Tiketi Mpya',
        'my_tickets' => 'Tiketi Zangu',
        'subject' => 'Mada',
        'message' => 'Ujumbe',
        'priority' => 'Kipaumbele',
        'high' => 'Juu',
        'medium' => 'Kati',
        'low' => 'Chini',
        'reply' => 'Jibu',
        'close_ticket' => 'Funga tiketi',
    ],
    
    // Map & Navigation
    'map' => [
        'your_location' => 'Mahali ulipo',
        'agent_location' => 'Mahali pa wakala',
        'customer_location' => 'Mahali pa mteja',
        'get_directions' => 'Pata njia',
        'live_tracking' => 'Ufuatiliaji wa moja kwa moja',
        'distance' => 'Umbali',
        'eta' => 'Muda wa kufika',
    ],
    
    // Settings
    'settings_page' => [
        'title' => 'Mipangilio',
        'language' => 'Lugha',
        'notifications' => 'Arifa',
        'privacy' => 'Faragha',
        'account' => 'Akaunti',
        'change_password' => 'Badilisha nywila',
        'delete_account' => 'Futa akaunti',
    ],
    
    // Time
    'time' => [
        'just_now' => 'Sasa hivi',
        'minutes_ago' => 'dakika :count zilizopita',
        'hours_ago' => 'saa :count zilizopita',
        'days_ago' => 'siku :count zilizopita',
        'today' => 'Leo',
        'yesterday' => 'Jana',
    ],
    
    // Days
    'days' => [
        'monday' => 'Jumatatu',
        'tuesday' => 'Jumanne',
        'wednesday' => 'Jumatano',
        'thursday' => 'Alhamisi',
        'friday' => 'Ijumaa',
        'saturday' => 'Jumamosi',
        'sunday' => 'Jumapili',
    ],
    
    // Errors
    'errors' => [
        'general' => 'Hitilafu imetokea. Tafadhali jaribu tena.',
        'not_found' => 'Haukupatikana',
        'unauthorized' => 'Huna ruhusa',
        'forbidden' => 'Umeruzuiwa',
        'validation' => 'Data si sahihi',
    ],
];
