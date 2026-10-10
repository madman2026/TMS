<?php

namespace App\Acceptance\Reporting\Enums;

enum EvidenceType: string
{
    case SCREENSHOT = 'screenshot';
    case VIDEO = 'video';
    case TRACE = 'trace';
    case HAR = 'har';
    case DOM_SNAPSHOT = 'dom_snapshot';
    case NETWORK_TRACE = 'network_trace';
    case LOG_REFERENCE = 'log_reference';

    /** @return list<string> */
    public function mediaTypes(): array
    {
        return match ($this) {
            self::SCREENSHOT => ['image/png', 'image/webp'],
            self::VIDEO => ['video/webm', 'video/mp4'],
            self::TRACE, self::NETWORK_TRACE => ['application/json', 'application/zip'],
            self::HAR => ['application/json'],
            self::DOM_SNAPSHOT => ['text/html', 'application/zip'],
            self::LOG_REFERENCE => ['text/plain', 'application/zip'],
        };
    }
}
