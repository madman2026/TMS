<?php

namespace App\Acceptance\Prerequisites\Enums;

/** Declares who must supply one prerequisite input. */
enum InputResolution: string
{
    case OPERATOR = 'operator';
    case APP_REFERENCE = 'app_reference';
}
