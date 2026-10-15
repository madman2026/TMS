<?php

$targetProfile = env('DK_ACCEPTANCE_TARGET_PROFILE');
$localTesting = $targetProfile === 'local-testing';

return [
    'name' => 'DK',
    'target' => [
        'enabled' => $localTesting,
        'environment' => $localTesting ? 'testing' : 'unknown',
        'control' => [
            'profile' => 'local-loopback',
            'endpoint' => 'http://127.0.0.1:8000',
            'secret_handle' => 'app-secret://dk/local-acceptance-control-v1',
        ],
    ],
];
