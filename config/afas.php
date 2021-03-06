<?php

return [

    // Connections to different AFAS servers
    'connections' => [

        'default' => [
            // The location URL of the profit service
            'url' => env('AFAS_URL'),

            // The environment of the profit service. Ex: T11111AA
            'environment' => env('AFAS_ENVIRONMENT', null),

            // The authorization token to make requests to the profit service
            'token' => env('AFAS_TOKEN', null),

            // List of all the getConnectors for the profit service
            'getConnectors' => [
                'contacts' => env('AFAS_CONTACTS_GETCONNECTOR')
            ],

            // List of all the updateConnectors for the profit service
            'updateConnectors' => [
                'persons' => env('AFAS_PERSONS_UPDATECONNECTOR')
            ]
        ]

    ]
];
