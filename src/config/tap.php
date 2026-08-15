<?php

return [
    'secret_key' => env('TAP_SECRET_KEY'),
    'public_key' => env('TAP_PUBLIC_KEY'),
    'base_url' => env('TAP_BASE_URL', 'https://api.tap.company/v2'),
    'redirect_url' => env('TAP_REDIRECT_URL', env('APP_URL') . '/payment/tap/redirect'),
    'webhook_url' => env('TAP_WEBHOOK_URL', env('APP_URL') . '/api/payments/tap/webhook'),
    'source_id' => env('TAP_SOURCE_ID', 'src_all'),
];
