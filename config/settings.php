<?php

return [
    'excluded' => [
        'app',
        'database',
        'cache',
    ],

    'cache' => [
        'ttl' => 60 // minutes
    ],

    'database' => [
        'table' => 'settings',
    ],
];
