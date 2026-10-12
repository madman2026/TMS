<?php

namespace App\Acceptance\Targets\Control\Contracts;

use App\Acceptance\Targets\Control\Data\TargetControlCredential;

interface SecretHandleResolver
{
    public function resolve(string $appKey, string $secretHandle): TargetControlCredential;
}
