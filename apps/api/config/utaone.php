<?php

return [
    'storage_path' => env('UTAONE_STORAGE_PATH', storage_path('app/media')),
    'admin_api_token' => env('UTAONE_ADMIN_API_TOKEN', 'change-me'),
    'require_subscription' => filter_var(env('UTAONE_REQUIRE_SUBSCRIPTION', false), FILTER_VALIDATE_BOOL),
    'revenuecat_entitlement_id' => env('REVENUECAT_ENTITLEMENT_ID', 'premium'),
    'revenuecat_authorization' => env('REVENUECAT_WEBHOOK_AUTHORIZATION', ''),
    'revenuecat_signing_secret' => env('REVENUECAT_WEBHOOK_SIGNING_SECRET', ''),
];
