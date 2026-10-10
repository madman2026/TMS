<?php

declare(strict_types=1);

namespace Modules\DK\Acceptance\Shared\Target;

use App\Acceptance\Targets\Data\TargetEnvironment;

final readonly class DKTargetProfile
{
    public int $version;

    public function __construct(
        public bool $enabled,
        public TargetEnvironment $environment,
    ) {
        $this->version = 1;
    }

    /** @param array<string, mixed> $config */
    public static function fromConfig(array $config): self
    {
        $enabled = $config['enabled'] ?? null;
        $environment = $config['environment'] ?? null;

        if (! is_bool($enabled) || ! is_string($environment)) {
            return new self(false, TargetEnvironment::UNKNOWN);
        }

        $resolvedEnvironment = TargetEnvironment::tryFrom($environment);
        if ($resolvedEnvironment === null) {
            return new self(false, TargetEnvironment::UNKNOWN);
        }

        return new self($enabled, $resolvedEnvironment);
    }
}
