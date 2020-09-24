<?php

$defaultConfig = require 'config.php';

return [
    'db' => $defaultConfig['db'],
    'store' => [
        'apiEndpoint' => 'http://172.16.0.61:8000',
    ],
    'front' => [
        'throwException' => true,
        'showException' => true,
    ],
    'phpsettings' => [
        'display_errors' => 1,
    ],
    'mail' => [
        'type' => 'file',

    ]
];