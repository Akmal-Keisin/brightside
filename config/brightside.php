<?php

return [
    'currency' => env('BRIGHTSIDE_CURRENCY', 'IDR'),

    'terms_version' => env('BRIGHTSIDE_TERMS_VERSION', '1.0'),

    'video_completion_ratio' => (float) env('BRIGHTSIDE_VIDEO_COMPLETION_RATIO', 0.90),

    'order_expiry_hours' => (int) env('BRIGHTSIDE_ORDER_EXPIRY_HOURS', 24),

    'quiz_grace_seconds' => (int) env('BRIGHTSIDE_QUIZ_GRACE_SECONDS', 30),

    'receipt_recipient' => env('BRIGHTSIDE_RECEIPT_RECIPIENT'),

    'support_email' => env('BRIGHTSIDE_SUPPORT_EMAIL', 'support@brightside.test'),

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
        'snap_base_url' => env('MIDTRANS_SNAP_BASE_URL', 'https://app.sandbox.midtrans.com'),
        'api_base_url' => env('MIDTRANS_API_BASE_URL', 'https://api.sandbox.midtrans.com'),
    ],
];
