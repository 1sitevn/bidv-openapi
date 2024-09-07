<?php

return [
    'bidv' => [
        'open_api' => [
            'url' => env('BIDV_OPENAPI_URL'),
            'client_id' => env('BIDV_OPENAPI_CLIENT_ID'),
            'secret_id' => env('BIDV_OPENAPI_SECRET_ID'),

            'service_id' => env('BIDV_OPENAPI_SERVICE_ID'),
            'merchant_id' => env('BIDV_OPENAPI_MERCHANT_ID'),
            'merchant_name' => env('BIDV_OPENAPI_MERCHANT_NAME'),
            'channel_id' => env('BIDV_OPENAPI_CHANNEL_ID'),
            'certificate_key' => env('BIDV_OPENAPI_CERTIFICATE_KEY'),
            'private_key' => env('BIDV_OPENAPI_PRIVATE_KEY'),
            'symmatric_key' => env('BIDV_OPENAPI_SYMMATRIC_KEY'),
            'provider_id' => env('BIDV_OPENAPI_PROVIDER_ID'),

            'access_token' => env('BIDV_OPENAPI_ACCESS_TOKEN'),
        ]
    ]
];
