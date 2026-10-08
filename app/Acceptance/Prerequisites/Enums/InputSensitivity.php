<?php

namespace App\Acceptance\Prerequisites\Enums;

enum InputSensitivity: string
{
    case NON_SENSITIVE = 'non_sensitive';
    case SECRET_REFERENCE = 'secret_reference';
}
