<?php

namespace App\Acceptance\Prerequisites\Data;

use App\Acceptance\Prerequisites\Enums\PrerequisiteState;
use DateTimeImmutable;
use InvalidArgumentException;
use Throwable;

/** Safe semantic prerequisite snapshot for every transport client. */
final readonly class PrerequisiteOperationData
{
    /** @var list<InputRequirement> */
    public array $requirements;

    /** @var list<string> */
    public array $missingInputKeys;

    /** @var list<ApprovalFact> */
    public array $approvalFacts;

    /** @var list<string> */
    public array $missingApprovalScopes;

    /**
     * @param  list<InputRequirement>  $requirements
     * @param  list<string>  $missingInputKeys
     * @param  list<ApprovalFact>  $approvalFacts
     * @param  list<string>  $missingApprovalScopes
     */
    public function __construct(
        public string $requestId,
        public string $appKey,
        public string $componentKey,
        public string $suiteKey,
        public string $scenarioKey,
        public string $variantKey,
        public ?int $profileId,
        public string $schemaVersion,
        public string $schemaFingerprint,
        public PrerequisiteState $state,
        public int $lockVersion,
        public string $expiresAt,
        array $requirements,
        array $missingInputKeys,
        array $approvalFacts,
        array $missingApprovalScopes,
        public string $correlationId,
        public int $version = 1,
    ) {
        if ($version !== 1 || ! self::isUuid($requestId) || ! self::isUuid($correlationId)
            || ($profileId !== null && $profileId < 1) || $lockVersion < 0
            || preg_match('/^[a-f0-9]{64}$/D', $schemaFingerprint) !== 1
            || ! array_is_list($requirements) || ! array_is_list($missingInputKeys)
            || ! array_is_list($approvalFacts) || ! array_is_list($missingApprovalScopes)) {
            throw new InvalidArgumentException('prerequisite_operation_data_invalid');
        }
        foreach ([$appKey, $componentKey, $suiteKey, $scenarioKey, $variantKey, $schemaVersion] as $key) {
            InputRequirement::assertKey($key);
        }
        try {
            new DateTimeImmutable($expiresAt);
        } catch (Throwable) {
            throw new InvalidArgumentException('prerequisite_operation_data_invalid');
        }
        foreach ($requirements as $requirement) {
            if (! $requirement instanceof InputRequirement) {
                throw new InvalidArgumentException('prerequisite_operation_data_invalid');
            }
        }
        foreach ($approvalFacts as $fact) {
            if (! $fact instanceof ApprovalFact) {
                throw new InvalidArgumentException('prerequisite_operation_data_invalid');
            }
        }
        foreach ([...$missingInputKeys, ...$missingApprovalScopes] as $key) {
            if (! is_string($key)) {
                throw new InvalidArgumentException('prerequisite_operation_data_invalid');
            }
            InputRequirement::assertKey($key);
        }
        if (count(array_unique($missingInputKeys, SORT_STRING)) !== count($missingInputKeys)
            || count(array_unique($missingApprovalScopes, SORT_STRING)) !== count($missingApprovalScopes)) {
            throw new InvalidArgumentException('prerequisite_operation_data_invalid');
        }
        $this->requirements = array_values($requirements);
        $this->missingInputKeys = array_values($missingInputKeys);
        $this->approvalFacts = array_values($approvalFacts);
        $this->missingApprovalScopes = array_values($missingApprovalScopes);
    }

    private static function isUuid(string $value): bool
    {
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/D', $value) === 1;
    }
}
