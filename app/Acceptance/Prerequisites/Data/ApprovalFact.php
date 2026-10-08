<?php

namespace App\Acceptance\Prerequisites\Data;

use DateTimeImmutable;
use InvalidArgumentException;
use Throwable;

/** Safe persisted workflow evidence; never an authorization decision. */
final readonly class ApprovalFact
{
    public function __construct(
        public string $scope,
        public string $schemaVersion,
        public string $actorType,
        public string $actorReference,
        public string $inputFingerprint,
        public string $approvedAt,
        public string $expiresAt,
        public ?string $revokedAt,
    ) {
        InputRequirement::assertKey($scope);
        InputRequirement::assertKey($schemaVersion);
        if (strlen($actorType) > 32 || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $actorType) !== 1
            || strlen($actorReference) > 128 || preg_match('/^[A-Za-z0-9][A-Za-z0-9._~-]*$/D', $actorReference) !== 1
            || preg_match('/^[a-f0-9]{64}$/D', $inputFingerprint) !== 1) {
            throw new InvalidArgumentException('prerequisite_operation_data_invalid');
        }
        try {
            $approved = new DateTimeImmutable($approvedAt);
            $expires = new DateTimeImmutable($expiresAt);
            $revoked = $revokedAt === null ? null : new DateTimeImmutable($revokedAt);
        } catch (Throwable) {
            throw new InvalidArgumentException('prerequisite_operation_data_invalid');
        }
        if ($expires <= $approved || ($revoked !== null && $revoked < $approved)) {
            throw new InvalidArgumentException('prerequisite_operation_data_invalid');
        }
    }
}
