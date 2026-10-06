<?php

declare(strict_types=1);

namespace Modules\Core\Enums;

/** Availability classifies evidence, never a target assertion's success. */
enum BrowserObservationStatus: string
{
    case AVAILABLE = 'available';
    case UNAVAILABLE = 'unavailable';
    case UNSUPPORTED = 'unsupported';
}
