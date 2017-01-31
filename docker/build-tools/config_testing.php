<?php

$defaultConfig = require 'config.php';

return array_merge($defaultConfig, [
        'front' => [
            'showException' => true
        ]
    ]
);
