<?php

declare(strict_types=1);

namespace Modules\Core\Enums;

enum ExecutorResultStatus: string
{
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Unsupported = 'unsupported';
}
