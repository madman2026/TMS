<?php

namespace App\Console\Acceptance;

use App\Acceptance\Execution\Data\BatchItemOperationData;
use App\Acceptance\Execution\Data\BatchOperationData;
use App\Acceptance\Operations\Data\CatalogOperationData;
use App\Acceptance\Operations\Data\ModuleChangeData;
use App\Acceptance\Operations\Data\OperationResult;
use App\Acceptance\Operations\Data\RunOperationData;
use App\Acceptance\Operations\Data\TargetModuleValidationData;
use App\Acceptance\Prerequisites\Data\ApprovalFact;
use App\Acceptance\Prerequisites\Data\PrerequisiteOperationData;
use App\Acceptance\Reporting\Data\AcceptanceAttemptView;
use App\Acceptance\Reporting\Data\AcceptanceCoverageView;
use App\Acceptance\Reporting\Data\AcceptanceEvidenceView;
use App\Acceptance\Reporting\Data\AcceptanceReport;
use App\Acceptance\Reporting\Data\AcceptanceReportItem;
use App\Acceptance\Reporting\Data\AcceptanceStatusView;
use App\Acceptance\Reporting\Data\CoverageSourceCaseView;
use App\Acceptance\Targets\Data\CleanupResult;
use App\Acceptance\Targets\Data\ResourceReference;
use App\Acceptance\Targets\Data\TargetExecutionOutcome;
use App\Acceptance\Targets\Data\TargetOracleResult;
use App\Acceptance\Targets\Data\TargetReadinessResult;
use App\Acceptance\Targets\Data\TargetResourceLifecycleData;
use App\Data\AcceptancePlanItem;
use App\TestStatusEnum;
use DateTimeImmutable;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\table;

final class OperationResultRenderer
{
    public function render(Command $command, OperationResult $result, bool $interactive): int
    {
        if ($interactive) {
            $this->renderHuman($command, $result);
        } else {
            $command->line(json_encode(
                $this->payload($result),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
            ));
        }

        if ($result->data instanceof RunOperationData) {
            return $result->data->testStatus === TestStatusEnum::FINISHED ? Command::SUCCESS : Command::FAILURE;
        }

        return match ($result->status) {
            'succeeded' => Command::SUCCESS,
            'rejected' => Command::INVALID,
            default => Command::FAILURE,
        };
    }

    public function renderClientFailure(Command $command, string $code, bool $interactive): int
    {
        if ($interactive) {
            $messages = [
                'cli_mode_invalid' => 'The requested CLI mode is not available.',
                'cli_input_invalid' => 'The command input is invalid.',
                'cli_confirmation_required' => 'This operation requires explicit confirmation.',
                'cli_interrupted' => 'The operation was interrupted by the user.',
            ];
            $this->error($command, $messages[$code] ?? 'A local CLI error occurred.');
        } else {
            $command->line(json_encode([
                'schema_version' => 2,
                'status' => 'rejected',
                'error_code' => $code,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        }

        return $code === 'cli_interrupted' ? 130 : Command::INVALID;
    }

    /** @return array<string, mixed> */
    private function payload(OperationResult $result): array
    {
        if (in_array($result->operation, ['acceptance.list', 'acceptance.plan'], true)) {
            return $this->catalogPayload($result);
        }
        if ($result->operation === 'acceptance.run') {
            return $this->runPayload($result);
        }

        return [
            'schema_version' => 2,
            'operation' => $result->operation,
            'status' => $result->status,
            'error_code' => $result->errorCode,
            'correlation_id' => $result->correlationId,
            'operation_id' => $result->operationId,
            'retryable' => $result->retryable,
            'permanent' => $result->permanent,
            'admin_action_required' => $result->adminActionRequired,
            'errors' => (object) $result->errors,
            'data' => $this->data($result->data),
        ];
    }

    /** @return array<string, mixed> */
    private function catalogPayload(OperationResult $result): array
    {
        if (! $result->data instanceof CatalogOperationData) {
            return ['schema_version' => 2, 'status' => $result->status, 'error_code' => $result->errorCode];
        }
        $data = $result->data;

        return [
            'schema_version' => 2,
            'status' => $result->operation === 'acceptance.plan' ? 'planned' : 'listed',
            'plan_version' => $data->planVersion,
            'catalog_versions' => (object) $data->catalogVersions,
            'counts' => [
                'matched' => $data->matched,
                'executable' => $data->executable,
                'excluded' => $data->excluded,
                'by_disposition' => $data->byDisposition,
            ],
            'fingerprint' => $data->fingerprint,
            'items' => array_map(fn (AcceptancePlanItem $item): array => $item->toArray(), $data->items),
        ];
    }

    /** @return array<string, mixed> */
    private function runPayload(OperationResult $result): array
    {
        if (! $result->data instanceof RunOperationData) {
            return ['schema_version' => 2, 'status' => $result->status, 'error_code' => $result->errorCode];
        }
        $data = $result->data;

        return [
            'schema_version' => 2,
            'status' => $data->testStatus->value,
            'test_id' => $data->testId,
            'app_key' => $data->appKey,
            'component_key' => $data->componentKey,
            'suite_key' => $data->suiteKey,
            'scenario_key' => $data->scenarioKey,
            'variant_key' => $data->variantKey,
            'error_code' => $data->errorCode,
        ];
    }

    /** @return array<string, mixed>|null */
    private function data(?object $data): ?array
    {
        return match (true) {
            $data instanceof ModuleChangeData => [
                'version' => $data->version, 'subject' => $data->subject, 'module_name' => $data->moduleName,
                'app_key' => $data->appKey, 'component_key' => $data->componentKey, 'dry_run' => $data->dryRun,
                'created' => $data->created, 'updated' => $data->updated, 'unchanged' => $data->unchanged,
                'changes' => array_map(fn ($change): array => ['path' => $change->path, 'action' => $change->action], $data->changes),
            ],
            $data instanceof TargetModuleValidationData => [
                'version' => $data->version, 'module_name' => $data->moduleName, 'app_key' => $data->appKey,
                'valid' => $data->valid,
                'issues' => array_map(fn ($issue): array => ['code' => $issue->code, 'path' => $issue->path], $data->issues),
                'checked_paths' => $data->checkedPaths,
            ],
            $data instanceof PrerequisiteOperationData => $this->prerequisite($data),
            $data instanceof BatchOperationData => $this->batch($data),
            $data instanceof BatchItemOperationData => $this->batchItem($data),
            $data instanceof AcceptanceStatusView => $this->status($data),
            $data instanceof AcceptanceReport => $this->report($data),
            $data instanceof AcceptanceCoverageView => $this->coverage($data),
            $data instanceof TargetResourceLifecycleData => $this->resources($data),
            default => null,
        };
    }

    /** @return array<string, mixed> */
    private function prerequisite(PrerequisiteOperationData $data): array
    {
        return [
            'version' => $data->version, 'request_id' => $data->requestId, 'app_key' => $data->appKey,
            'component_key' => $data->componentKey, 'suite_key' => $data->suiteKey,
            'scenario_key' => $data->scenarioKey, 'variant_key' => $data->variantKey,
            'profile_id' => $data->profileId, 'schema_version' => $data->schemaVersion,
            'schema_fingerprint' => $data->schemaFingerprint, 'state' => $data->state->value,
            'lock_version' => $data->lockVersion, 'expires_at' => $data->expiresAt,
            'requirements' => array_map(fn ($item): array => $item->semanticData(), $data->requirements),
            'approval_requirements' => array_map(fn ($item): array => $item->semanticData(), $data->approvalRequirements),
            'missing_input_keys' => $data->missingInputKeys,
            'approval_facts' => array_map(fn (ApprovalFact $fact): array => [
                'scope' => $fact->scope, 'schema_version' => $fact->schemaVersion,
                'actor_type' => $fact->actorType, 'actor_reference' => $fact->actorReference,
                'input_fingerprint' => $fact->inputFingerprint, 'approved_at' => $fact->approvedAt,
                'expires_at' => $fact->expiresAt, 'revoked_at' => $fact->revokedAt,
            ], $data->approvalFacts),
            'missing_approval_scopes' => $data->missingApprovalScopes,
            'correlation_id' => $data->correlationId,
        ];
    }

    /** @return array<string, mixed> */
    private function batch(BatchOperationData $data): array
    {
        return [
            'version' => $data->version, 'operation_id' => $data->operationId, 'batch_id' => $data->batchId,
            'correlation_id' => $data->correlationId, 'operation_state' => $data->operationState->value,
            'batch_state' => $data->batchState->value, 'mode' => $data->mode->value,
            'plan_fingerprint' => $data->planFingerprint, 'lock_version' => $data->lockVersion,
            'matched_count' => $data->matchedCount, 'executable_count' => $data->executableCount,
            'skipped_count' => $data->skippedCount, 'pending_count' => $data->pendingCount,
            'blocked_count' => $data->blockedCount, 'queued_count' => $data->queuedCount,
            'running_count' => $data->runningCount, 'passed_count' => $data->passedCount,
            'failed_count' => $data->failedCount, 'cancelled_count' => $data->cancelledCount,
            'error_code' => $data->errorCode, 'retryable' => $data->retryable,
            'permanent' => $data->permanent, 'admin_action_required' => $data->adminActionRequired,
        ];
    }

    /** @return array<string, mixed> */
    private function batchItem(BatchItemOperationData $data): array
    {
        return [
            'version' => $data->version, 'operation_id' => $data->operationId, 'batch_id' => $data->batchId,
            'item_id' => $data->itemId, 'parent_item_id' => $data->parentItemId, 'attempt_id' => $data->attemptId,
            'test_id' => $data->testId, 'state' => $data->state->value, 'app_key' => $data->appKey,
            'component_key' => $data->componentKey, 'suite_key' => $data->suiteKey,
            'scenario_key' => $data->scenarioKey, 'variant_key' => $data->variantKey,
            'error_code' => $data->errorCode, 'retryable' => $data->retryable,
            'permanent' => $data->permanent, 'admin_action_required' => $data->adminActionRequired,
        ];
    }

    /** @return array<string, mixed> */
    private function status(AcceptanceStatusView $data): array
    {
        return [
            'version' => $data->version, 'operation_id' => $data->operationId, 'batch_id' => $data->batchId,
            'correlation_id' => $data->correlationId, 'operation_state' => $data->operationState->value,
            'batch_state' => $data->batchState->value, 'mode' => $data->mode->value,
            'operation_lock_version' => $data->operationLockVersion, 'batch_lock_version' => $data->batchLockVersion,
            'matched_count' => $data->matchedCount, 'executable_count' => $data->executableCount,
            'skipped_count' => $data->skippedCount, 'pending_count' => $data->pendingCount,
            'blocked_count' => $data->blockedCount, 'queued_count' => $data->queuedCount,
            'running_count' => $data->runningCount, 'passed_count' => $data->passedCount,
            'failed_count' => $data->failedCount, 'cancelled_count' => $data->cancelledCount,
            'failure_count' => $data->failureCount, 'error_code' => $data->errorCode,
            'retryable' => $data->retryable, 'permanent' => $data->permanent,
            'admin_action_required' => $data->adminActionRequired,
            'started_at' => $this->date($data->startedAt), 'finished_at' => $this->date($data->finishedAt),
        ];
    }

    /** @return array<string, mixed> */
    private function report(AcceptanceReport $data): array
    {
        return [
            'version' => $data->version, 'status' => $this->status($data->status),
            'items' => array_map(fn (AcceptanceReportItem $item): array => $this->reportItem($item), $data->items),
            'limit' => $data->limit, 'after_item_id' => $data->afterItemId,
            'next_item_id' => $data->nextItemId, 'has_more' => $data->hasMore,
        ];
    }

    /** @return array<string, mixed> */
    private function reportItem(AcceptanceReportItem $item): array
    {
        return [
            'version' => $item->version, 'id' => $item->id, 'ordinal' => $item->ordinal,
            'app_key' => $item->appKey, 'component_key' => $item->componentKey, 'suite_key' => $item->suiteKey,
            'scenario_key' => $item->scenarioKey, 'variant_key' => $item->variantKey,
            'capability' => $item->capability, 'catalog_version' => $item->catalogVersion,
            'state' => $item->state->value, 'attempt_count' => $item->attemptCount,
            'error_code' => $item->errorCode, 'cleanup_error_code' => $item->cleanupErrorCode,
            'retryable' => $item->retryable, 'permanent' => $item->permanent,
            'admin_action_required' => $item->adminActionRequired, 'queued_at' => $this->date($item->queuedAt),
            'started_at' => $this->date($item->startedAt), 'finished_at' => $this->date($item->finishedAt),
            'attempts' => array_map(fn (AcceptanceAttemptView $attempt): array => $this->attempt($attempt), $item->attempts),
        ];
    }

    /** @return array<string, mixed> */
    private function attempt(AcceptanceAttemptView $attempt): array
    {
        return [
            'version' => $attempt->version, 'id' => $attempt->id, 'attempt_number' => $attempt->attemptNumber,
            'state' => $attempt->state->value, 'test_id' => $attempt->testId,
            'executor_capability' => $attempt->executorCapability, 'executor_key' => $attempt->executorKey,
            'infrastructure_attempts' => $attempt->infrastructureAttempts,
            'executor_entered' => $attempt->executorEntered, 'error_code' => $attempt->errorCode,
            'cleanup_error_code' => $attempt->cleanupErrorCode, 'retryable' => $attempt->retryable,
            'permanent' => $attempt->permanent, 'admin_action_required' => $attempt->adminActionRequired,
            'queued_at' => $this->date($attempt->queuedAt), 'started_at' => $this->date($attempt->startedAt),
            'finished_at' => $this->date($attempt->finishedAt),
            'evidence' => array_map(fn (AcceptanceEvidenceView $evidence): array => $this->evidence($evidence), $attempt->evidence),
        ];
    }

    /** @return array<string, mixed> */
    private function evidence(AcceptanceEvidenceView $evidence): array
    {
        return [
            'version' => $evidence->version, 'id' => $evidence->id, 'type' => $evidence->type->value,
            'reference_key' => $evidence->referenceKey, 'checksum_sha256' => $evidence->checksumSha256,
            'size_bytes' => $evidence->sizeBytes, 'media_type' => $evidence->mediaType,
            'width' => $evidence->width, 'height' => $evidence->height, 'duration_ms' => $evidence->durationMs,
            'captured_at' => $this->date($evidence->capturedAt),
            'available_until' => $this->date($evidence->availableUntil), 'available' => $evidence->available,
        ];
    }

    /** @return array<string, mixed> */
    private function coverage(AcceptanceCoverageView $data): array
    {
        return [
            'version' => $data->version, 'app_key' => $data->appKey, 'catalog_version' => $data->catalogVersion,
            'counts' => [
                'total' => $data->counts->total, 'automated_full' => $data->counts->automatedFull,
                'automated_partial' => $data->counts->automatedPartial,
                'merged_equivalent' => $data->counts->mergedEquivalent,
                'excluded_no_reliable_executor' => $data->counts->excludedNoReliableExecutor,
                'excluded_human_judgment' => $data->counts->excludedHumanJudgment,
            ],
            'cases' => array_map(fn (CoverageSourceCaseView $case): array => $this->coverageCase($case), $data->cases),
            'limit' => $data->limit, 'after_source_case_id' => $data->afterSourceCaseId,
            'next_source_case_id' => $data->nextSourceCaseId, 'has_more' => $data->hasMore,
        ];
    }

    /** @return array<string, mixed> */
    private function coverageCase(CoverageSourceCaseView $case): array
    {
        return [
            'version' => $case->version, 'source_case_id' => $case->sourceCaseId,
            'disposition' => $case->disposition->value, 'component_key' => $case->componentKey,
            'suite_key' => $case->suiteKey, 'scenario_key' => $case->scenarioKey,
            'variant_key' => $case->variantKey, 'replacement_component_key' => $case->replacementComponentKey,
            'replacement_suite_key' => $case->replacementSuiteKey,
            'replacement_scenario_key' => $case->replacementScenarioKey,
            'replacement_variant_key' => $case->replacementVariantKey, 'reason' => $case->reason,
            'covered_assertions' => $case->coveredAssertions, 'uncovered_assertions' => $case->uncoveredAssertions,
        ];
    }

    /** @return array<string, mixed> */
    private function resources(TargetResourceLifecycleData $data): array
    {
        return [
            'version' => $data->version, 'lifecycle_id' => $data->lifecycleId,
            'correlation_id' => $data->correlationId, 'prerequisite_request_id' => $data->prerequisiteRequestId,
            'app_key' => $data->appKey, 'component_key' => $data->componentKey, 'suite_key' => $data->suiteKey,
            'scenario_key' => $data->scenarioKey, 'variant_key' => $data->variantKey,
            'profile_id' => $data->profileId, 'status' => $data->status, 'stage' => $data->stage,
            'readiness' => $data->readiness === null ? null : $this->readiness($data->readiness),
            'references' => array_map(fn (ResourceReference $reference): array => [
                'type' => $reference->type, 'reference_hash' => $reference->referenceHash,
            ], $data->references),
            'execution' => $data->execution === null ? null : $this->execution($data->execution),
            'oracle' => $data->oracle === null ? null : $this->oracle($data->oracle),
            'cleanup' => $data->cleanup === null ? null : $this->cleanup($data->cleanup),
            'primary_error_code' => $data->primaryErrorCode, 'cleanup_error_code' => $data->cleanupErrorCode,
        ];
    }

    /** @return array<string, mixed> */
    private function readiness(TargetReadinessResult $data): array
    {
        return ['version' => $data->version, 'environment' => $data->environment->value, 'ready' => $data->ready,
            'error_code' => $data->errorCode, 'retryable' => $data->retryable, 'permanent' => $data->permanent,
            'admin_action_required' => $data->adminActionRequired];
    }

    /** @return array<string, mixed> */
    private function execution(TargetExecutionOutcome $data): array
    {
        return ['version' => $data->version, 'status' => $data->status, 'error_code' => $data->errorCode,
            'completed' => $data->completed, 'retryable' => $data->retryable, 'permanent' => $data->permanent,
            'admin_action_required' => $data->adminActionRequired];
    }

    /** @return array<string, mixed> */
    private function oracle(TargetOracleResult $data): array
    {
        return ['version' => $data->version, 'passed' => $data->passed, 'error_code' => $data->errorCode,
            'retryable' => $data->retryable, 'permanent' => $data->permanent,
            'admin_action_required' => $data->adminActionRequired];
    }

    /** @return array<string, mixed> */
    private function cleanup(CleanupResult $data): array
    {
        $reference = fn (ResourceReference $item): array => [
            'type' => $item->type, 'reference_hash' => $item->referenceHash,
        ];

        return ['version' => $data->version, 'succeeded' => $data->succeeded,
            'cleaned_references' => array_map($reference, $data->cleanedReferences),
            'remaining_references' => array_map($reference, $data->remainingReferences),
            'timed_out' => $data->timedOut, 'error_code' => $data->errorCode,
            'retryable' => $data->retryable, 'permanent' => $data->permanent,
            'admin_action_required' => $data->adminActionRequired];
    }

    private function renderHuman(Command $command, OperationResult $result): void
    {
        if ($result->status !== 'succeeded') {
            $code = $result->errorCode ?? 'unknown_error';
            $this->error($command, sprintf('Operation failed (%s). %s', $code, $this->guidance($result->errorCode)));
            $this->error($command, 'Correlation ID: '.$result->correlationId);
            if ($result->operationId !== null) {
                $this->error($command, 'Operation ID: '.$result->operationId);
            }

            return;
        }

        if ($result->data instanceof CatalogOperationData) {
            table(
                ['app', 'component', 'suite', 'scenario', 'variant', 'disposition'],
                array_map(fn (AcceptancePlanItem $item): array => [
                    $item->appKey, $item->componentKey, $item->suiteKey,
                    $item->scenarioKey, $item->variantKey, $item->metadata->disposition->value,
                ], $result->data->items),
            );
        } elseif ($result->data instanceof RunOperationData) {
            table(['test', 'scenario', 'variant', 'status'], [[
                $result->data->testId,
                $result->data->scenarioKey,
                $result->data->variantKey,
                $result->data->testStatus->value,
            ]]);
        } elseif ($result->data instanceof ModuleChangeData) {
            note(sprintf(
                '%s: %s (dry-run: %s)',
                $result->data->subject,
                $result->data->moduleName,
                $result->data->dryRun ? 'yes' : 'no',
            ));
            table(['path', 'action'], array_map(
                fn ($change): array => [$change->path, $change->action],
                $result->data->changes,
            ));
        } elseif ($result->data instanceof TargetModuleValidationData) {
            note('Validation status: '.($result->data->valid ? 'valid' : 'invalid'));
            if ($result->data->issues !== []) {
                table(['code', 'path'], array_map(
                    fn ($issue): array => [$issue->code, $issue->path ?? '-'],
                    $result->data->issues,
                ));
            }
        } elseif ($result->data instanceof PrerequisiteOperationData) {
            note(sprintf(
                'Request %s | state %s | lock %d',
                $result->data->requestId,
                $result->data->state->value,
                $result->data->lockVersion,
            ));
            table(['input', 'type', 'required'], array_map(
                fn ($requirement): array => [
                    $requirement->key,
                    $requirement->type->value,
                    $requirement->required ? 'yes' : 'no',
                ],
                $result->data->requirements,
            ));
        } elseif ($result->data instanceof BatchOperationData) {
            table(['batch', 'operation state', 'batch state', 'passed', 'failed'], [[
                $result->data->batchId,
                $result->data->operationState->value,
                $result->data->batchState->value,
                $result->data->passedCount,
                $result->data->failedCount,
            ]]);
        } elseif ($result->data instanceof BatchItemOperationData) {
            table(['batch', 'item', 'attempt', 'state'], [[
                $result->data->batchId,
                $result->data->itemId,
                $result->data->attemptId ?? '-',
                $result->data->state->value,
            ]]);
        } elseif ($result->data instanceof AcceptanceStatusView) {
            table(['batch', 'operation state', 'batch state', 'passed', 'failed'], [[
                $result->data->batchId,
                $result->data->operationState->value,
                $result->data->batchState->value,
                $result->data->passedCount,
                $result->data->failedCount,
            ]]);
        } elseif ($result->data instanceof AcceptanceReport) {
            table(['id', 'scenario', 'variant', 'state'], array_map(
                fn (AcceptanceReportItem $item): array => [$item->id, $item->scenarioKey, $item->variantKey, $item->state->value],
                $result->data->items,
            ));
        } elseif ($result->data instanceof AcceptanceCoverageView) {
            table(['source case', 'disposition'], array_map(
                fn (CoverageSourceCaseView $case): array => [$case->sourceCaseId, $case->disposition->value],
                $result->data->cases,
            ));
        }

        if ($command->getOutput()->isVerbose()) {
            note('Correlation ID: '.$result->correlationId);
            if ($result->operationId !== null) {
                note('Operation ID: '.$result->operationId);
            }
        }

        outro('Operation completed successfully.');
    }

    private function guidance(?string $code): string
    {
        return match ($code) {
            'operation_request_invalid' => 'Check the command inputs and request version.',
            'acceptance_app_not_found', 'acceptance_selector_not_found' => 'Check the hierarchy keys and app registration.',
            'acceptance_profile_not_found' => 'The selected profile does not exist.',
            'input_required', 'input_invalid', 'secret_literal_forbidden' => 'Correct the prerequisite inputs and secret references.',
            'approval_required', 'approval_stale' => 'Review or renew the prerequisite approval.',
            'conflict', 'acceptance_batch_conflict', 'acceptance_attempt_stale' => 'Fetch the current state and lock version, then retry.',
            'target_not_ready' => 'Check target readiness, then run the operation again.',
            'unsafe_target', 'resource_unavailable' => 'Do not continue; an administrator must review the target configuration.',
            'acceptance_report_not_found' => 'Check the batch ID.',
            null => 'Use the correlation ID to investigate the operation.',
            default => 'Use the error code and correlation ID to investigate the operation.',
        };
    }

    private function error(Command $command, string $message): void
    {
        $output = $command->getOutput()->getOutput();
        if ($output instanceof ConsoleOutputInterface) {
            $output->getErrorOutput()->writeln($message);

            return;
        }
        $command->getOutput()->writeln($message);
    }

    private function date(?DateTimeImmutable $date): ?string
    {
        return $date?->format(DATE_ATOM);
    }
}
