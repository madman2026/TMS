<?php

namespace App\Acceptance\Prerequisites;

use App\Acceptance\Prerequisites\Contracts\OperatorContextProvider;
use App\Acceptance\Prerequisites\Data\OperatorContext;

/** Local trace identity only; future authenticated clients replace this at their boundary. */
final class LocalCliOperatorContextProvider implements OperatorContextProvider
{
    public function current(): OperatorContext
    {
        return new OperatorContext('local_cli', 'local-cli');
    }
}
