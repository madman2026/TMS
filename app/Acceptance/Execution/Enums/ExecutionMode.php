<?php

namespace App\Acceptance\Execution\Enums;

enum ExecutionMode: string
{
    case SYNC = 'sync';
    case ASYNC = 'async';
}
