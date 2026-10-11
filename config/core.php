<?php

return [
    'name' => 'Core',
    'acceptance' => [
        'browser' => env('ACCEPTANCE_BROWSER', 'chromium'),
        'headless' => (bool) env('ACCEPTANCE_HEADLESS', true),
        'timeout_ms' => (int) env('ACCEPTANCE_BROWSER_TIMEOUT_MS', 30_000),
        'slow_mo_ms' => (int) env('ACCEPTANCE_BROWSER_SLOW_MO_MS', 0),
    ],
];
