<?php

return [

    // Connections to different AFAS servers
    'connections' => [

        'default' => [
            // The environment of the profit service. Ex: T11111AA
            'environment' => env('AFAS_ENVIRONMENT', null),

            // The authorization token to make requests to the profit service
            'token' => env('AFAS_TOKEN', null),

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
     * Do not change this array!
     * Check out the documentation to see which filters are available
     */
    'filterOperators' => [
        '1' => ['=', 'isEqualTo'],
        '2' => ['>=', 'isGreaterThanOrEqualTo'],
        '3' => ['<=', 'isSmallerThanOrEqualTo'],
        '4' => ['>', 'isGreaterThan'],
        '5' => ['>', 'isSmallerThan'],
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
