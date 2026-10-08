<?php

namespace App\Acceptance\Prerequisites\Data;

use InvalidArgumentException;
use JsonException;

/** Versioned code-owned prerequisite contract and deterministic semantic identity. */
final readonly class PrerequisiteSchema
{
    public const DEFAULT_REQUEST_TTL_SECONDS = 86400;

    public const MIN_REQUEST_TTL_SECONDS = 300;

    public const MAX_REQUEST_TTL_SECONDS = 604800;

    public const DEFAULT_APPROVAL_TTL_SECONDS = 3600;

    public const MAX_SCALAR_BYTES = 4096;

    public const MAX_SECRET_REFERENCE_BYTES = 255;

    public const MAX_LIST_ITEMS = 64;

    public const MAX_LIST_BYTES = 4096;

    /** @var list<InputRequirement> */
    public array $inputs;

    /** @var list<ApprovalRequirement> */
    public array $approvals;

    /**
     * @param  list<InputRequirement>  $inputs
     * @param  list<ApprovalRequirement>  $approvals
     */
    public function __construct(
        public string $version,
        array $inputs = [],
        array $approvals = [],
        public int $requestTtlSeconds = self::DEFAULT_REQUEST_TTL_SECONDS,
    ) {
        InputRequirement::assertKey($version);
        if ($requestTtlSeconds < self::MIN_REQUEST_TTL_SECONDS || $requestTtlSeconds > self::MAX_REQUEST_TTL_SECONDS
            || ! array_is_list($inputs) || ! array_is_list($approvals)) {
            throw new InvalidArgumentException('prerequisite_schema_invalid');
        }

        $inputKeys = [];
        foreach ($inputs as $input) {
            if (! $input instanceof InputRequirement || isset($inputKeys[$input->key])) {
                throw new InvalidArgumentException('prerequisite_schema_invalid');
            }
            $inputKeys[$input->key] = $input;
        }
        $approvalScopes = [];
        foreach ($approvals as $approval) {
            if (! $approval instanceof ApprovalRequirement || isset($approvalScopes[$approval->scope])
                || $approval->ttlSeconds > $requestTtlSeconds) {
                throw new InvalidArgumentException('prerequisite_schema_invalid');
            }
            foreach ($approval->invalidatedByInputKeys as $key) {
                if (! isset($inputKeys[$key]) || ! $inputKeys[$key]->approvalRelevant || ! $inputKeys[$key]->required) {
                    throw new InvalidArgumentException('prerequisite_schema_invalid');
                }
            }
            $approvalScopes[$approval->scope] = true;
        }

        $this->inputs = array_values($inputs);
        $this->approvals = array_values($approvals);
    }

    public static function none(): self
    {
        return new self('none-v1');
    }

    public function fingerprint(): string
    {
        try {
            $json = json_encode([
                'version' => $this->version,
                'request_ttl_seconds' => $this->requestTtlSeconds,
                'inputs' => array_map(fn (InputRequirement $input): array => $input->semanticData(), $this->inputs),
                'approvals' => array_map(fn (ApprovalRequirement $approval): array => $approval->semanticData(), $this->approvals),
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        } catch (JsonException) {
            throw new InvalidArgumentException('prerequisite_schema_invalid');
        }

        return hash('sha256', $json);
    }

    public function input(string $key): ?InputRequirement
    {
        foreach ($this->inputs as $input) {
            if ($input->key === $key) {
                return $input;
            }
        }

        return null;
    }

    public function approval(string $scope): ?ApprovalRequirement
    {
        foreach ($this->approvals as $approval) {
            if ($approval->scope === $scope) {
                return $approval;
            }
        }

        return null;
    }
}
