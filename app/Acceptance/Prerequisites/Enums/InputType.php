<?php

namespace App\Acceptance\Prerequisites\Enums;

enum InputType: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case BOOLEAN = 'boolean';
    case STRING_LIST = 'string_list';
    case SECRET_REFERENCE = 'secret_reference';
}
