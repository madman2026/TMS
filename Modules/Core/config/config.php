<?php

return [
    'name' => 'Core',
    'acceptance' => [
        'browser' => 'chromium',
        'headless' => (bool) env('ACCEPTANCE_HEADLESS', true),
        'timeout_ms' => 30_000,
        'slow_mo_ms' => 0,
    ],
];
