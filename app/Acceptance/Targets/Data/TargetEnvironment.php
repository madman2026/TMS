<?php

namespace App\Acceptance\Targets\Data;

enum TargetEnvironment: string
{
    case TESTING = 'testing';
    case STAGING = 'staging';
    case PRODUCTION = 'production';
    case UNKNOWN = 'unknown';

    public function safe(): bool
    {
        return $this === self::TESTING || $this === self::STAGING;
    }
}
