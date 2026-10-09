<?php

namespace App\Acceptance\Targets\Data;

use InvalidArgumentException;

/** Opaque non-secret target resource identity; raw IDs never enter logs. */
final readonly class ResourceReference
{
    public string $referenceHash;

    public function __construct(public string $type, public string $id, public int $version = 1)
    {
        if ($version !== 1 || strlen($type) > 64
            || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $type) !== 1
            || strlen($id) > 128 || preg_match('/^[A-Za-z0-9._~-]+$/D', $id) !== 1
            || preg_match('/(?:authorization|credential|password|passwd|secret|token|cookie|session|bearer|selector|payload|personal|email|phone|sensitive)/i', $id) === 1) {
            throw new InvalidArgumentException('resource_reference_invalid');
        }
        $this->referenceHash = hash('sha256', $type."\0".$id);
    }

    public function key(): string
    {
        return $this->type."\0".$this->id;
    }
}
