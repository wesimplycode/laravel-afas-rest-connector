<?php

return [

    // Connections to different AFAS servers
    'connections' => [

        'default' => [
            // The environment of the profit service. Ex: T11111AA
            'environment' => env('AFAS_ENVIRONMENT', null),

            /*
             * The authentication method used for this connection: "token" or "oauth".
             *
             * When left empty the package uses OAuth if a client_id is configured
             * and falls back to the classic AfasToken otherwise.
             *
             * Note: AFAS discontinues the classic token on 31 August 2027.
             */
            'auth' => env('AFAS_AUTH', null),

            // Classic token: the authorization token to make requests to the profit service
            'token' => env('AFAS_TOKEN', null),

            // OAuth client credentials flow: created on the App Connector in AFAS Profit
            'client_id' => env('AFAS_CLIENT_ID', null),
            'client_secret' => env('AFAS_CLIENT_SECRET', null),

            /*
             * These are placeholders. Replace them with your connectors!
             *
             * You can name the key whatever you want. The value has to be the connector ID in AFAS Profit.
             * You can add as many connectors as needed, just don't forget to add them to your .env file
             */

            // List of all the getConnectors for the profit service
            'getConnectors' => [
                'contacts' => ''
            ],

            // List of all the updateConnectors for the profit service
            'updateConnectors' => [
                'persons' => ''
            ]
        ]

    ],

    /*
     * OAuth access tokens are valid for a limited time (AFAS currently issues them for 1 hour).
     * The package caches the access token so a new one is only requested when the old one expires.
     */
    'oauth' => [
        // The cache store to use for access tokens. null uses the default cache store.
        'cache_store' => env('AFAS_OAUTH_CACHE_STORE', null),

        // Prefix for the cache keys of the access tokens
        'cache_prefix' => 'afas_oauth_access_token',

        // Number of seconds subtracted from expires_in so a token is refreshed before it actually expires
        'expiry_margin' => 60,

        // When multiple processes need a new token at the same time only one requests it.
        // How long the lock is held and how long other processes wait for it (seconds).
        'lock_seconds' => 10,
        'lock_wait_seconds' => 10,
    ],

    /*
     * Do not change this array!
     * Check out the documentation to see which filters are available
     */
    'filterOperators' => [
        '1' => ['=', 'isEqualTo'],
        '2' => ['>=', 'isGreaterThanOrEqualTo'],
        '3' => ['<=', 'isSmallerThanOrEqualTo'],
        '4' => ['>', 'isGreaterThan'],
        '5' => ['<', 'isSmallerThan'],
        '6' => ['*', 'contains'],
        '7' => ['!=', 'doesNotEqual'],
        '8' => ['[]', 'isEmpty'],
        '9' => ['![]', 'isNotEmpty'],
        '10' => ['@', 'startWith'],
        '11' => ['!*', 'doesNotContain'],
        '12' => ['!@', 'doNotStartWith'],
        '13' => ['&', 'endsIn'],
        '14' => ['!&', 'doesNotEndIn'],
        '15' => ['Sf', 'quickFilter'],
    ]
];
