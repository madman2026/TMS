<?php

namespace App\Acceptance\Targets\Control;

use DateTimeImmutable;
use DateTimeZone;

final class TargetControlProtocol
{
    public const IDENTITY = 'acceptance-control.v1';

    public const CAPABILITIES_PATH = '/_acceptance/v1/capabilities';

    public const LEASES_PATH = '/_acceptance/v1/leases';

    public const HEADER_PROTOCOL = 'X-Acceptance-Protocol';

    public const HEADER_CORRELATION_ID = 'X-Acceptance-Correlation-Id';

    public const HEADER_REQUEST_DIGEST = 'X-Acceptance-Request-Digest';

    public const HEADER_IDEMPOTENCY_KEY = 'Idempotency-Key';

    public const MAX_TOKEN_BYTES = 64;

    public const MAX_REFERENCE_ID_BYTES = 128;

    public const MAX_COMPONENTS = 64;

    public const MAX_RECIPES_PER_COMPONENT = 64;

    public const MAX_OBSERVATIONS_PER_COMPONENT = 64;

    public const MAX_REFERENCES = 32;

    public const MAX_FACTS = 16;

    public const MAX_RESPONSE_BYTES = 32_768;

    public const MAX_LEASE_TTL_SECONDS = 900;

    public const MAX_ATTEMPTS = 3;

    /** @var list<string> */
    public const ERROR_CODES = [
        'target_control_unavailable',
        'target_control_unauthorized',
        'target_control_capability_mismatch',
        'target_control_lease_conflict',
        'target_control_recipe_rejected',
        'target_control_cleanup_failed',
        'target_control_protocol_invalid',
    ];

    /** @var list<string> */
    public const LEASE_STATES = ['active', 'cleaned', 'expired'];

    private function __construct() {}

    public static function leasePath(string $lifecycleId): string
    {
        return self::LEASES_PATH.'/'.$lifecycleId;
    }

    public static function validToken(string $value): bool
    {
        return strlen($value) <= self::MAX_TOKEN_BYTES
            && preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $value) === 1;
    }

    public static function validUuid(string $value): bool
    {
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/D', $value) === 1;
    }

    public static function validDigest(string $value): bool
    {
        return preg_match('/^[a-f0-9]{64}$/D', $value) === 1;
    }

    public static function validFixedInstant(string $value): bool
    {
        $instant = DateTimeImmutable::createFromFormat(
            '!Y-m-d\TH:i:s\Z',
            $value,
            new DateTimeZone('UTC'),
        );

        return $instant !== false && $instant->format('Y-m-d\TH:i:s\Z') === $value;
    }
}
