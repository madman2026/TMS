<?php

namespace Modules\Core\Enums;

enum AutomationDisposition: string
{
    case AUTOMATED = 'automated';
    case BLOCKED = 'blocked';
    case NOT_IMPLEMENTED = 'not-implemented';
}
