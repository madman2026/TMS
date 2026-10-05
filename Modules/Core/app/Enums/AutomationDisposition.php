<?php

namespace Modules\Core\Enums;

enum AutomationDisposition: string
{
    case AUTOMATED = 'automated';
    case MANUAL_ONLY = 'manual-only';
    case BLOCKED = 'blocked';
    case NOT_IMPLEMENTED = 'not-implemented';
}
