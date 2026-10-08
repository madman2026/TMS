<?php

namespace App\Acceptance\Prerequisites;

use App\Acceptance\Prerequisites\Contracts\OperatorContextProvider;
use App\Acceptance\Prerequisites\Data\ApprovalFact;
use App\Acceptance\Prerequisites\Data\ApprovalRequirement;
use App\Acceptance\Prerequisites\Data\InputRequirement;
use App\Acceptance\Prerequisites\Data\OperatorContext;
use App\Acceptance\Prerequisites\Data\PrerequisiteOperationData;
use App\Acceptance\Prerequisites\Data\PrerequisiteSchema;
use App\Acceptance\Prerequisites\Enums\InputSensitivity;
use App\Acceptance\Prerequisites\Enums\InputSource;
use App\Acceptance\Prerequisites\Enums\InputType;
use App\Acceptance\Prerequisites\Enums\PrerequisiteState;
use App\Exceptions\AcceptanceCatalogException;
use App\Models\AcceptanceOperationApproval;
use App\Models\AcceptanceOperationInput;
use App\Models\AcceptanceOperationRequest;
use App\Models\Profile;
use App\Services\AcceptanceCatalog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use JsonException;
use Throwable;

/** Owns prerequisite persistence, validation, expiry, locking and approval state. */
final class PrerequisiteService
{
    public function __construct(
        private readonly AcceptanceCatalog $catalog,
        private readonly OperatorContextProvider $operatorContext,
    ) {}

    /** @param array{app_key: string, component_key: string, suite_key: string, scenario_key: string, variant_key: string} $identity */
    public function prepare(
        array $identity,
        int $profileId,
        string $correlationId,
        string $requestId,
        string $operation,
    ): PrerequisiteOperationData {
        return $this->run(function () use ($identity, $profileId, $correlationId, $requestId, $operation): PrerequisiteOperationData {
            return DB::transaction(function () use ($identity, $profileId, $correlationId, $requestId, $operation): PrerequisiteOperationData {
                $schema = $this->schemaFor($identity);
                if (! Profile::query()->whereKey($profileId)->exists()) {
                    throw PrerequisiteException::because('input_invalid');
                }
                $now = now();
                $state = $schema->inputs === []
                    ? ($this->requiredApprovals($schema) === [] ? PrerequisiteState::READY : PrerequisiteState::AWAITING_APPROVAL)
                    : ($this->requiredInputs($schema) === []
                        ? ($this->requiredApprovals($schema) === [] ? PrerequisiteState::READY : PrerequisiteState::AWAITING_APPROVAL)
                        : PrerequisiteState::AWAITING_INPUT);
                $request = AcceptanceOperationRequest::query()->create([
                    'id' => $requestId,
                    'correlation_id' => $correlationId,
                    ...$identity,
                    'profile_id' => $profileId,
                    'schema_version' => $schema->version,
                    'schema_fingerprint' => $schema->fingerprint(),
                    'state' => $state,
                    'lock_version' => 0,
                    'expires_at' => $now->copy()->addSeconds($schema->requestTtlSeconds),
                    'cancelled_at' => null,
                ]);
                $this->logTransition(
                    $request,
                    PrerequisiteState::DRAFT,
                    $state,
                    $operation,
                    $correlationId,
                    $this->operatorContext->current(),
                );

                return $this->snapshot($request, $schema, $correlationId, $now);
            });
        });
    }

    public function discover(string $requestId, string $correlationId, string $operation): PrerequisiteOperationData
    {
        return $this->run(function () use ($requestId, $correlationId, $operation): PrerequisiteOperationData {
            return DB::transaction(function () use ($requestId, $correlationId, $operation): PrerequisiteOperationData {
                $request = $this->lockedRequest($requestId);
                $schema = $this->schemaForRequest($request);
                $now = now();
                $previous = $request->state;
                $changed = $this->applyExpiryOrRecompute($request, $schema, $now);
                if ($changed) {
                    $this->logTransition(
                        $request,
                        $previous,
                        $request->state,
                        $operation,
                        $correlationId,
                        $this->operatorContext->current(),
                    );
                }

                return $this->snapshot($request, $schema, $correlationId, $now);
            });
        });
    }

    /** @param array<string, array{source: string, value: mixed}> $inputs */
    public function submit(
        string $requestId,
        int $expectedLockVersion,
        array $inputs,
        string $correlationId,
        string $operation,
    ): PrerequisiteOperationData {
        $result = $this->run(function () use ($requestId, $expectedLockVersion, $inputs, $correlationId, $operation): array {
            return DB::transaction(function () use ($requestId, $expectedLockVersion, $inputs, $correlationId, $operation): array {
                $request = $this->lockedRequest($requestId);
                $schema = $this->schemaForRequest($request);
                $now = now();
                $previous = $request->state;
                if ($this->expire($request, $now)) {
                    $this->logTransition($request, $previous, $request->state, $operation, $correlationId, $this->operatorContext->current());

                    return ['expired' => true, 'data' => null];
                }
                $this->assertMutable($request);

                $normalized = [];
                foreach ($inputs as $key => $payload) {
                    $requirement = is_string($key) ? $schema->input($key) : null;
                    if ($requirement === null || ! is_array($payload)) {
                        throw PrerequisiteException::because('input_invalid');
                    }
                    $normalized[$key] = $this->normalize($requirement, $payload, $request->app_key);
                }

                /** @var Collection<int, AcceptanceOperationInput> $stored */
                $stored = $request->inputs()->get()->keyBy('key');
                $changedKeys = [];
                foreach ($normalized as $key => $value) {
                    if ($stored->get($key)?->value_fingerprint !== $value['value_fingerprint']) {
                        $changedKeys[] = $key;
                    }
                }
                // Compare normalized intent before the lock so an identical retry remains safely idempotent.
                if ($request->lock_version !== $expectedLockVersion) {
                    if ($changedKeys === []) {
                        return ['expired' => false, 'data' => $this->snapshot($request, $schema, $correlationId, $now)];
                    }
                    throw PrerequisiteException::because('conflict');
                }
                if ($changedKeys === []) {
                    return ['expired' => false, 'data' => $this->snapshot($request, $schema, $correlationId, $now)];
                }

                $actor = $this->operatorContext->current();
                foreach ($changedKeys as $key) {
                    $value = $normalized[$key];
                    AcceptanceOperationInput::query()->updateOrCreate(
                        [
                            'acceptance_operation_request_id' => $request->getKey(),
                            'key' => $key,
                            'schema_version' => $schema->version,
                        ],
                        [
                            ...$value,
                            'submitted_by_type' => $actor->actorType,
                            'submitted_by_reference' => $actor->actorReference,
                            'submitted_at' => $now,
                        ],
                    );
                }
                $invalidatedScopes = $this->invalidateApprovals($request, $schema, $changedKeys, $now);
                $request->lock_version++;
                $request->state = $this->computedState($request, $schema, $now);
                $request->save();
                if ($previous !== $request->state) {
                    $this->logTransition(
                        $request,
                        $previous,
                        $request->state,
                        $operation,
                        $correlationId,
                        $actor,
                        $changedKeys,
                        $invalidatedScopes,
                    );
                }

                return ['expired' => false, 'data' => $this->snapshot($request, $schema, $correlationId, $now)];
            });
        });
        if ($result['expired']) {
            throw PrerequisiteException::because('request_expired');
        }

        return $result['data'];
    }

    public function approve(
        string $requestId,
        int $expectedLockVersion,
        string $scope,
        string $correlationId,
        string $operation,
    ): PrerequisiteOperationData {
        $result = $this->run(function () use ($requestId, $expectedLockVersion, $scope, $correlationId, $operation): array {
            return DB::transaction(function () use ($requestId, $expectedLockVersion, $scope, $correlationId, $operation): array {
                $request = $this->lockedRequest($requestId);
                $schema = $this->schemaForRequest($request);
                $approval = $schema->approval($scope);
                if ($approval === null) {
                    throw PrerequisiteException::because('input_invalid');
                }
                $now = now();
                $previous = $request->state;
                if ($this->expire($request, $now)) {
                    $this->logTransition($request, $previous, $request->state, $operation, $correlationId, $this->operatorContext->current());

                    return ['expired' => true, 'data' => null];
                }
                $this->assertMutable($request);
                $fingerprint = $this->approvalFingerprint($request, $approval);
                $existing = $request->approvals()
                    ->where('scope', $scope)
                    ->where('schema_version', $schema->version)
                    ->where('input_fingerprint', $fingerprint)
                    ->first();
                if ($existing !== null && $existing->revoked_at === null && $existing->expires_at->isAfter($now)) {
                    return ['expired' => false, 'data' => $this->snapshot($request, $schema, $correlationId, $now)];
                }
                // A valid identical fact wins before lock comparison; different stale intent conflicts.
                if ($request->lock_version !== $expectedLockVersion) {
                    throw PrerequisiteException::because('conflict');
                }

                $actor = $this->operatorContext->current();
                $request->approvals()->where('scope', $scope)->whereNull('revoked_at')->update(['revoked_at' => $now]);
                AcceptanceOperationApproval::query()->updateOrCreate(
                    [
                        'acceptance_operation_request_id' => $request->getKey(),
                        'scope' => $scope,
                        'schema_version' => $schema->version,
                        'input_fingerprint' => $fingerprint,
                    ],
                    [
                        'actor_type' => $actor->actorType,
                        'actor_reference' => $actor->actorReference,
                        'approved_at' => $now,
                        'expires_at' => $now->copy()->addSeconds($approval->ttlSeconds)->min($request->expires_at),
                        'revoked_at' => null,
                    ],
                );
                $request->lock_version++;
                $request->state = $this->computedState($request, $schema, $now);
                $request->save();
                if ($previous !== $request->state) {
                    $this->logTransition($request, $previous, $request->state, $operation, $correlationId, $actor);
                }

                return ['expired' => false, 'data' => $this->snapshot($request, $schema, $correlationId, $now)];
            });
        });
        if ($result['expired']) {
            throw PrerequisiteException::because('request_expired');
        }

        return $result['data'];
    }

    public function cancel(
        string $requestId,
        int $expectedLockVersion,
        string $correlationId,
        string $operation,
    ): PrerequisiteOperationData {
        $result = $this->run(function () use ($requestId, $expectedLockVersion, $correlationId, $operation): array {
            return DB::transaction(function () use ($requestId, $expectedLockVersion, $correlationId, $operation): array {
                $request = $this->lockedRequest($requestId);
                $schema = $this->schemaForRequest($request);
                $now = now();
                if ($request->state === PrerequisiteState::CANCELLED) {
                    return ['expired' => false, 'data' => $this->snapshot($request, $schema, $correlationId, $now)];
                }
                $previous = $request->state;
                if ($this->expire($request, $now)) {
                    $this->logTransition($request, $previous, $request->state, $operation, $correlationId, $this->operatorContext->current());

                    return ['expired' => true, 'data' => null];
                }
                $this->assertMutable($request);
                if ($request->lock_version !== $expectedLockVersion) {
                    throw PrerequisiteException::because('conflict');
                }
                $request->state = PrerequisiteState::CANCELLED;
                $request->cancelled_at = $now;
                $request->lock_version++;
                $request->save();
                $this->logTransition(
                    $request,
                    $previous,
                    $request->state,
                    $operation,
                    $correlationId,
                    $this->operatorContext->current(),
                );

                return ['expired' => false, 'data' => $this->snapshot($request, $schema, $correlationId, $now)];
            });
        });
        if ($result['expired']) {
            throw PrerequisiteException::because('request_expired');
        }

        return $result['data'];
    }

    /** @template T @param callable(): T $callback @return T */
    private function run(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (PrerequisiteException|AcceptanceCatalogException $exception) {
            throw $exception;
        } catch (InvalidArgumentException) {
            throw PrerequisiteException::because('prerequisite_schema_invalid');
        } catch (Throwable) {
            throw PrerequisiteException::because('prerequisite_persistence_failed');
        }
    }

    private function lockedRequest(string $requestId): AcceptanceOperationRequest
    {
        return AcceptanceOperationRequest::query()->lockForUpdate()->find($requestId)
            ?? throw PrerequisiteException::because('prerequisite_request_not_found');
    }

    private function schemaForRequest(AcceptanceOperationRequest $request): PrerequisiteSchema
    {
        $schema = $this->schemaFor([
            'app_key' => $request->app_key,
            'component_key' => $request->component_key,
            'suite_key' => $request->suite_key,
            'scenario_key' => $request->scenario_key,
            'variant_key' => $request->variant_key,
        ]);
        // Persisted input is never reinterpreted after a code-owned schema identity changes.
        if ($request->schema_version !== $schema->version || $request->schema_fingerprint !== $schema->fingerprint()) {
            throw PrerequisiteException::because('schema_changed');
        }

        return $schema;
    }

    /** @param array{app_key: string, component_key: string, suite_key: string, scenario_key: string, variant_key: string} $identity */
    private function schemaFor(array $identity): PrerequisiteSchema
    {
        $componentFound = false;
        foreach ($this->catalog->components($identity['app_key']) as $component) {
            $componentFound = $componentFound || $component->key === $identity['component_key'];
        }
        $suiteFound = false;
        foreach ($this->catalog->suites($identity['app_key']) as $suite) {
            $suiteFound = $suiteFound || ($suite->key === $identity['suite_key']
                && $suite->componentKey === $identity['component_key']);
        }
        $descriptor = $this->catalog->descriptor($identity['app_key'], $identity['scenario_key']);
        if (! $componentFound || ! $suiteFound || $descriptor === null
            || $descriptor->componentKey !== $identity['component_key'] || $descriptor->suiteKey !== $identity['suite_key']) {
            throw AcceptanceCatalogException::because('acceptance_selector_not_found');
        }
        foreach ($this->catalog->variants($identity['app_key'], $identity['scenario_key']) as $variant) {
            if ($variant->key === $identity['variant_key']) {
                return $variant->prerequisiteSchema;
            }
        }

        throw AcceptanceCatalogException::because('acceptance_selector_not_found');
    }

    /** @param array<string, mixed> $payload @return array<string, mixed> */
    private function normalize(InputRequirement $requirement, array $payload, string $appKey): array
    {
        if (count($payload) !== 2 || ! array_key_exists('source', $payload) || ! array_key_exists('value', $payload)
            || ! is_string($payload['source'])) {
            throw PrerequisiteException::because('input_invalid');
        }
        $source = InputSource::tryFrom($payload['source']);
        if ($requirement->sensitivity === InputSensitivity::SECRET_REFERENCE
            && $source !== InputSource::SECRET_REFERENCE) {
            // Literal secrets are rejected before a value can reach persistence or logging.
            throw PrerequisiteException::because('secret_literal_forbidden');
        }
        if ($source === null || ! in_array($source, $requirement->allowedSources, true)) {
            throw PrerequisiteException::because('input_invalid');
        }
        $value = $payload['value'];
        if ($value === null) {
            throw PrerequisiteException::because('input_invalid');
        }

        if ($requirement->type === InputType::SECRET_REFERENCE) {
            if (! is_string($value) || strlen($value) > PrerequisiteSchema::MAX_SECRET_REFERENCE_BYTES
                || ! mb_check_encoding($value, 'UTF-8')
                || preg_match('/^app-secret:\/\/'.preg_quote($appKey, '/').'\/[A-Za-z0-9._~-]{1,128}$/D', $value) !== 1) {
                throw PrerequisiteException::because('input_invalid');
            }

            return [
                'type' => $requirement->type,
                'sensitivity' => $requirement->sensitivity,
                'value_json' => null,
                'secret_reference' => $value,
                'value_fingerprint' => $this->valueFingerprint($requirement->type, $value),
            ];
        }

        $valid = match ($requirement->type) {
            InputType::STRING => is_string($value) && mb_check_encoding($value, 'UTF-8')
                && strlen($value) <= PrerequisiteSchema::MAX_SCALAR_BYTES
                && ($requirement->minimum === null || mb_strlen($value, 'UTF-8') >= $requirement->minimum)
                && ($requirement->maximum === null || mb_strlen($value, 'UTF-8') <= $requirement->maximum),
            InputType::INTEGER => is_int($value)
                && ($requirement->minimum === null || $value >= $requirement->minimum)
                && ($requirement->maximum === null || $value <= $requirement->maximum),
            InputType::BOOLEAN => is_bool($value),
            InputType::STRING_LIST => $this->validStringList($value, $requirement->listLimit ?? PrerequisiteSchema::MAX_LIST_ITEMS),
            InputType::SECRET_REFERENCE => false,
        };
        if (! $valid || ($requirement->allowedValues !== null && ! in_array($value, $requirement->allowedValues, true))) {
            throw PrerequisiteException::because('input_invalid');
        }

        return [
            'type' => $requirement->type,
            'sensitivity' => $requirement->sensitivity,
            'value_json' => $value,
            'secret_reference' => null,
            'value_fingerprint' => $this->valueFingerprint($requirement->type, $value),
        ];
    }

    private function validStringList(mixed $value, int $limit): bool
    {
        if (! is_array($value) || ! array_is_list($value) || count($value) > $limit) {
            return false;
        }
        $bytes = 0;
        foreach ($value as $item) {
            if (! is_string($item) || ! mb_check_encoding($item, 'UTF-8')) {
                return false;
            }
            $bytes += strlen($item);
        }

        return $bytes <= PrerequisiteSchema::MAX_LIST_BYTES;
    }

    private function valueFingerprint(InputType $type, mixed $value): string
    {
        try {
            return hash('sha256', json_encode(
                ['type' => $type->value, 'value' => $value],
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
            ));
        } catch (JsonException) {
            throw PrerequisiteException::because('input_invalid');
        }
    }

    /** @param list<string> $changedKeys @return list<string> */
    private function invalidateApprovals(
        AcceptanceOperationRequest $request,
        PrerequisiteSchema $schema,
        array $changedKeys,
        Carbon $now,
    ): array {
        $scopes = [];
        foreach ($schema->approvals as $approval) {
            if (array_intersect($changedKeys, $approval->invalidatedByInputKeys) === []) {
                continue;
            }
            $updated = $request->approvals()->where('scope', $approval->scope)->whereNull('revoked_at')
                ->update(['revoked_at' => $now]);
            if ($updated > 0) {
                $scopes[] = $approval->scope;
            }
        }

        return $scopes;
    }

    private function approvalFingerprint(AcceptanceOperationRequest $request, ApprovalRequirement $approval): string
    {
        $fingerprints = $request->inputs()->whereIn('key', $approval->invalidatedByInputKeys)
            ->pluck('value_fingerprint', 'key');
        $ordered = [];
        foreach ($approval->invalidatedByInputKeys as $key) {
            if (! isset($fingerprints[$key])) {
                throw PrerequisiteException::because('input_required');
            }
            $ordered[$key] = $fingerprints[$key];
        }

        // Scope order binds approval to the exact declared values that can invalidate it.
        return hash('sha256', json_encode($ordered, JSON_UNESCAPED_SLASHES));
    }

    private function applyExpiryOrRecompute(
        AcceptanceOperationRequest $request,
        PrerequisiteSchema $schema,
        Carbon $now,
    ): bool {
        if ($this->expire($request, $now)) {
            return true;
        }
        if ($request->state->terminal()) {
            return false;
        }
        $state = $this->computedState($request, $schema, $now);
        if ($state === $request->state) {
            return false;
        }
        $request->state = $state;
        $request->lock_version++;
        $request->save();

        return true;
    }

    private function expire(AcceptanceOperationRequest $request, Carbon $now): bool
    {
        if ($request->state->terminal() || $now->lt($request->expires_at)) {
            return false;
        }
        $request->state = PrerequisiteState::EXPIRED;
        $request->lock_version++;
        $request->save();

        return true;
    }

    private function assertMutable(AcceptanceOperationRequest $request): void
    {
        if ($request->state === PrerequisiteState::EXPIRED) {
            throw PrerequisiteException::because('request_expired');
        }
        if ($request->state->terminal()) {
            throw PrerequisiteException::because('invalid_transition');
        }
    }

    private function computedState(
        AcceptanceOperationRequest $request,
        PrerequisiteSchema $schema,
        Carbon $now,
    ): PrerequisiteState {
        $storedKeys = $request->inputs()->pluck('key')->all();
        foreach ($this->requiredInputs($schema) as $key) {
            if (! in_array($key, $storedKeys, true)) {
                return PrerequisiteState::AWAITING_INPUT;
            }
        }
        foreach ($schema->approvals as $approval) {
            if (! $approval->required) {
                continue;
            }
            try {
                $fingerprint = $this->approvalFingerprint($request, $approval);
            } catch (PrerequisiteException) {
                return PrerequisiteState::AWAITING_INPUT;
            }
            $valid = $request->approvals()
                ->where('scope', $approval->scope)
                ->where('schema_version', $schema->version)
                ->where('input_fingerprint', $fingerprint)
                ->whereNull('revoked_at')
                ->where('expires_at', '>', $now)
                ->exists();
            if (! $valid) {
                return PrerequisiteState::AWAITING_APPROVAL;
            }
        }

        return PrerequisiteState::READY;
    }

    private function snapshot(
        AcceptanceOperationRequest $request,
        PrerequisiteSchema $schema,
        string $correlationId,
        Carbon $now,
    ): PrerequisiteOperationData {
        $storedKeys = $request->inputs()->orderBy('key')->pluck('key')->all();
        $missingInputs = array_values(array_filter(
            $this->requiredInputs($schema),
            fn (string $key): bool => ! in_array($key, $storedKeys, true),
        ));
        $missingApprovals = [];
        foreach ($schema->approvals as $approval) {
            if (! $approval->required) {
                continue;
            }
            try {
                $fingerprint = $this->approvalFingerprint($request, $approval);
                $valid = $request->approvals()
                    ->where('scope', $approval->scope)
                    ->where('schema_version', $schema->version)
                    ->where('input_fingerprint', $fingerprint)
                    ->whereNull('revoked_at')
                    ->where('expires_at', '>', $now)
                    ->exists();
            } catch (PrerequisiteException) {
                $valid = false;
            }
            if (! $valid) {
                $missingApprovals[] = $approval->scope;
            }
        }
        $facts = $request->approvals()->orderBy('id')->get()->map(
            fn (AcceptanceOperationApproval $approval): ApprovalFact => new ApprovalFact(
                $approval->scope,
                $approval->schema_version,
                $approval->actor_type,
                $approval->actor_reference,
                $approval->input_fingerprint,
                $approval->approved_at->utc()->toISOString(),
                $approval->expires_at->utc()->toISOString(),
                $approval->revoked_at?->utc()->toISOString(),
            ),
        )->all();

        return new PrerequisiteOperationData(
            (string) $request->getKey(),
            $request->app_key,
            $request->component_key,
            $request->suite_key,
            $request->scenario_key,
            $request->variant_key,
            $request->profile_id,
            $request->schema_version,
            $request->schema_fingerprint,
            $request->state,
            $request->lock_version,
            $request->expires_at->utc()->toISOString(),
            $schema->inputs,
            $missingInputs,
            $facts,
            $missingApprovals,
            $correlationId,
        );
    }

    /** @return list<string> */
    private function requiredInputs(PrerequisiteSchema $schema): array
    {
        return array_values(array_map(
            fn (InputRequirement $input): string => $input->key,
            array_filter($schema->inputs, fn (InputRequirement $input): bool => $input->required),
        ));
    }

    /** @return list<string> */
    private function requiredApprovals(PrerequisiteSchema $schema): array
    {
        return array_values(array_map(
            fn (ApprovalRequirement $approval): string => $approval->scope,
            array_filter($schema->approvals, fn (ApprovalRequirement $approval): bool => $approval->required),
        ));
    }

    /** @param list<string> $changedKeys @param list<string> $invalidatedScopes */
    private function logTransition(
        AcceptanceOperationRequest $request,
        PrerequisiteState $previous,
        PrerequisiteState $current,
        string $operation,
        string $correlationId,
        OperatorContext $actor,
        array $changedKeys = [],
        array $invalidatedScopes = [],
    ): void {
        if ($previous === $current) {
            return;
        }
        Log::info('tms.acceptance.prerequisite.state_changed', [
            'operation_request_id' => (string) $request->getKey(),
            'operation' => $operation,
            'previous_state' => $previous->value,
            'current_state' => $current->value,
            'changed_requirement_keys' => array_values($changedKeys),
            'invalidated_approval_scopes' => array_values($invalidatedScopes),
            'actor_type' => $actor->actorType,
            'actor_reference' => $actor->actorReference,
            'correlation_id' => $correlationId,
            'app_key' => $request->app_key,
            'component_key' => $request->component_key,
            'suite_key' => $request->suite_key,
            'scenario_key' => $request->scenario_key,
            'variant_key' => $request->variant_key,
        ]);
    }
}
