<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Shared\Target;

use App\Acceptance\Targets\Control\Contracts\SecretHandleResolver;
use App\Acceptance\Targets\Control\Data\TargetControlCredential;
use App\Acceptance\Targets\Control\Exceptions\TargetControlException;

final readonly class LocalLoopbackSecretHandleResolver implements SecretHandleResolver
{
    public const HANDLE = 'app-secret://dk/local-acceptance-control-v1';

    public const PUBLIC_MARKER = 'local-acceptance-control-v1';

    public function resolve(string $appKey, string $secretHandle): TargetControlCredential
    {
        if ($appKey !== 'dk' || $secretHandle !== self::HANDLE) {
            throw TargetControlException::because('target_control_unavailable');
        }

        return new TargetControlCredential(self::PUBLIC_MARKER);
    }
}
