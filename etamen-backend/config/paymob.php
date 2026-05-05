<?php

return [
    'secret_key' => env('PAYMOB_SECRET_KEY'),
    'public_key' => env('PAYMOB_PUBLIC_KEY'),
    'hmac_secret' => env('PAYMOB_HMAC_SECRET'),
    'card_integration_id' => env('PAYMOB_CARD_INTEGRATION_ID'),
    'wallet_integration_id' => env('PAYMOB_WALLET_INTEGRATION_ID'),
    'unified_checkout_url' => env('PAYMOB_UNIFIED_CHECKOUT_URL', 'https://accept.paymob.com/unifiedcheckout/'),
    'intention_url' => env('PAYMOB_INTENTION_URL', 'https://accept.paymob.com/v1/intention/'),
    'callback_url' => env('PAYMOB_CALLBACK_URL'),
    'webhook_url' => env('PAYMOB_WEBHOOK_URL'),
    'success_url' => env('PAYMOB_SUCCESS_URL'),
    'failure_url' => env('PAYMOB_FAILURE_URL'),
];
