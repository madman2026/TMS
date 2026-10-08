<?php

namespace App\Acceptance\Coverage\Enums;

enum CoverageDisposition: string
{
    case AUTOMATED_FULL = 'automated_full';
    case AUTOMATED_PARTIAL = 'automated_partial';
    case MERGED_EQUIVALENT = 'merged_equivalent';
    case EXCLUDED_NO_RELIABLE_EXECUTOR = 'excluded_no_reliable_executor';
    case EXCLUDED_HUMAN_JUDGMENT = 'excluded_human_judgment';
}
