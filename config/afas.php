<?php

return [

    // Connections to different AFAS servers
    'connections' => [

        'default' => [
            // The location URL of the profit service
            'url' => env('AFAS_URL'),

            // The environment of the profit service. Ex: T11111AA
            'environment' => env('AFAS_ENVIRONMENT'),

            // The authorization token to make requests to the profit service
            'token' => env('AFAS_TOKEN'),

            // List of all the getConnectors for the profit service
            'getConnectors' => [
                'articles' => env('AFAS_ARTICLES_GETCONNECTOR')
            ]
        ]
        
    ]
];
