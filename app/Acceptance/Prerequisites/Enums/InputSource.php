<?php

namespace App\Acceptance\Prerequisites\Enums;

enum InputSource: string
{
    case LITERAL = 'literal';
    case SECRET_REFERENCE = 'secret_reference';
}
