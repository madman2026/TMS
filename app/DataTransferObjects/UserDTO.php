<?php

namespace App\DataTransferObjects;

use App\Contracts\DataTransferObject;

class UserDTO extends DataTransferObject
{
    public string $number;
    public string $password;
}
