<?php

namespace App\Acceptance\Prerequisites\Contracts;

use App\Acceptance\Prerequisites\Data\OperatorContext;

interface OperatorContextProvider
{
    public function current(): OperatorContext;
}
