<?php

return [
    'queue' => [
        'connection' => env('ACCEPTANCE_QUEUE_CONNECTION', 'database'),
        'name' => env('ACCEPTANCE_QUEUE_NAME', 'acceptance'),
        'dispatch_chunk' => (int) env('ACCEPTANCE_QUEUE_DISPATCH_CHUNK', 100),
        'max_in_flight' => (int) env('ACCEPTANCE_QUEUE_MAX_IN_FLIGHT', 4),
        'job_timeout_seconds' => (int) env('ACCEPTANCE_QUEUE_JOB_TIMEOUT', 75),
        'infrastructure_attempts' => (int) env('ACCEPTANCE_QUEUE_ATTEMPTS', 3),
        'backoff_seconds' => [5, 15],
        'lock_seconds' => (int) env('ACCEPTANCE_QUEUE_LOCK_SECONDS', 90),
        'stale_after_seconds' => (int) env('ACCEPTANCE_QUEUE_STALE_AFTER', 180),
        'max_failures' => (int) env('ACCEPTANCE_QUEUE_MAX_FAILURES', 0),
    ],
    'execution' => [
        'executor_timeout_ms' => (int) env('ACCEPTANCE_EXECUTOR_TIMEOUT_MS', 30_000),
        'cleanup_timeout_ms' => 30_000,
    ],
    'reporting' => [
        'default_page_size' => 50,
        'max_page_size' => 250,
        'max_export_rows' => 25_000,
        'max_source_cases' => 25_000,
    ],
];
