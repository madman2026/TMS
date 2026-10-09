# AI-PACK-TMS-ASYNC-BATCH-ORCHESTRATION-0013 — Async Queue and Bus Batch orchestration

Status: `accepted — technical validation passed and operator accepted 2026-10-10`

Generated: `2026-10-07`

Normalized: `2026-10-10`

Run: `docs/project/ai/runs/AI-PACK-TMS-ASYNC-BATCH-ORCHESTRATION-0013-run-001.md`

Decision: accepted Decisions 0005, 0008–0012, 0014–0016. Decision 0015 owns stage 7 and the single hierarchy contract. Decision 0013 is superseded history.

Execution Gate: Core Pack 0003 is accepted at commit `5a0c55e72cff95a1a84e7768d7ceba131a405cb2`. The operator separately approved execution, accepted the validated result, and authorized commit on 2026-10-10.

## 1. Task ID

`AI-PACK-TMS-ASYNC-BATCH-ORCHESTRATION-0013`

## 2. Task Title

Implement durable synchronous, queued, batched, resumable, retryable, cancellable, and bounded-parallel Acceptance execution.

## 3. Goal

Execute an immutable version-2 Acceptance plan without an open terminal while preserving exact operation, batch, item, attempt, Test, prerequisite, idempotency, cleanup, cancellation, and recovery history.

## 4. Context

Accepted Packs 0010–0012 provide the hierarchy, prerequisite, and target-resource boundaries. Accepted Core Pack 0003 provides the capability-based `ExecutorRegistry`. Decision 0010 approves Laravel Queue, Jobs, Events, locks, and Bus Batches, but requires this Pack to define the exact persistence and recovery contract.

`AcceptanceOperationRequest` remains the per-scenario prerequisite/input/approval record. It is not reused as the aggregate async execution operation: one batch may contain many complete hierarchy identities, while every prerequisite request owns exactly one identity. This Pack therefore adds `AcceptanceExecutionOperation` as the one-to-one lifecycle record for a batch.

## 5. Related Release / Phase

Decision 0015, stage 7. Pre-Release; no release-specific or phase-specific directory is activated.

## 6. Related Epic / Feature / Story

Scalable E2E orchestration over the accepted App → Component → Suite → Scenario → Variant → Step hierarchy.

## 7. Source References

- `AGENTS.md`, `docs/START-HERE.md`, `docs/ai/CONTEXT-MAP.md`, and the project profile/index;
- Decisions 0005, 0008–0012, and 0014–0016;
- accepted Pack/Run evidence for Packs 0009–0012 and Core Pack 0003;
- installed Laravel 12 Queue, Bus Batch, database transaction, atomic-lock, failed-job, and job-timeout behavior;
- current `app/Data/AcceptancePlan.php`, `app/Data/AcceptancePlanItem.php`, `app/Services/AcceptancePlanner.php`, operation/prerequisite/resource services, `config/queue.php`, and Core executor contracts.

## 8. Files to Create

- `config/acceptance.php`
- `app/Acceptance/Execution/Enums/OperationState.php`
- `app/Acceptance/Execution/Enums/BatchState.php`
- `app/Acceptance/Execution/Enums/BatchItemState.php`
- `app/Acceptance/Execution/Enums/AttemptState.php`
- `app/Acceptance/Execution/Enums/ExecutionMode.php`
- `app/Acceptance/Execution/Data/BatchPrerequisiteReference.php`
- `app/Acceptance/Execution/Data/BatchOperationData.php`
- `app/Acceptance/Execution/Data/BatchItemOperationData.php`
- `app/Acceptance/Execution/AcceptanceExecutionException.php`
- `app/Acceptance/Execution/AcceptanceExecutionStateMachine.php`
- `app/Acceptance/Execution/AcceptanceBatchService.php`
- `app/Acceptance/Execution/AcceptanceExecutionService.php`
- `app/Acceptance/Execution/AcceptanceRecoveryService.php`
- `app/Models/AcceptanceBatch.php`
- `app/Models/AcceptanceExecutionOperation.php`
- `app/Models/AcceptanceBatchItem.php`
- `app/Models/AcceptanceExecutionAttempt.php`
- `database/migrations/2026_10_10_000020_create_acceptance_batches_table.php`
- `database/migrations/2026_10_10_000021_create_acceptance_execution_operations_table.php`
- `database/migrations/2026_10_10_000022_create_acceptance_batch_items_table.php`
- `database/migrations/2026_10_10_000023_create_acceptance_execution_attempts_table.php`
- `database/factories/AcceptanceBatchFactory.php`
- `database/factories/AcceptanceExecutionOperationFactory.php`
- `database/factories/AcceptanceBatchItemFactory.php`
- `database/factories/AcceptanceExecutionAttemptFactory.php`
- `app/Jobs/RunAcceptanceBatch.php`
- `app/Jobs/RunAcceptanceBatchItem.php`
- `app/Events/AcceptanceExecutionStateChanged.php`
- `app/Acceptance/Operations/Handlers/StartAcceptanceBatch.php`
- `app/Acceptance/Operations/Handlers/ResumeAcceptanceBatch.php`
- `app/Acceptance/Operations/Handlers/RetryAcceptanceBatchItem.php`
- `app/Acceptance/Operations/Handlers/CancelAcceptanceBatch.php`
- `tests/Feature/AcceptanceAsyncBatchTest.php`
- `tests/Feature/AcceptanceBatchPersistenceTest.php`
- `tests/Unit/AcceptanceExecutionStateMachineTest.php`
- `tests/Unit/AcceptanceExecutionIdempotencyTest.php`
- `tests/Unit/AcceptanceRecoveryServiceTest.php`

## 9. Files to Edit

- `.env.example`
- `app/Acceptance/Operations/AcceptanceOperationService.php`
- `app/Acceptance/Operations/Data/OperationResult.php`
- `app/Acceptance/Operations/Handlers/RunAcceptanceScenario.php`
- `app/Models/AcceptanceOperationRequest.php`
- `app/Models/Profile.php`
- `app/Models/Test.php`
- `app/Providers/AppServiceProvider.php`
- `app/Services/AcceptanceRunService.php`
- `tests/Feature/AcceptanceRunCommandTest.php`
- `tests/Feature/AcceptanceTargetResourceLifecycleTest.php`
- `tests/Unit/AcceptanceOperationServiceTest.php`
- `docs/project/ai/packs/AI-PACK-TMS-ASYNC-BATCH-ORCHESTRATION-0013.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md`

The executed Run creates `docs/project/ai/runs/AI-PACK-TMS-ASYNC-BATCH-ORCHESTRATION-0013-run-001.md`. No other docs or guide are implementation scope; Pack 0015 owns operator-facing queue guidance.

## 10. Files to Read / Reference

Read every section 7–9 path that exists, the applicable repository rules, all predecessor Packs/Runs named in section 7, current migrations/factories/tests for `Profile`, `Test`, operation requests and queue tables, installed framework source for `Bus::batch`, `ShouldQueue`, atomic locks and timeout handling, and current Core executor DTOs/registry. Confirm package versions before relying on APIs. Do not read `.env`.

## 11. Configuration / Settings Requirements

Create `config/acceptance.php` with these exact non-sensitive defaults:

```text
queue.connection                 = env('ACCEPTANCE_QUEUE_CONNECTION', 'database')
queue.name                       = env('ACCEPTANCE_QUEUE_NAME', 'acceptance')
queue.dispatch_chunk             = env int, default 100, range 1..1000
queue.max_in_flight              = env int, default 4, range 1..32
queue.job_timeout_seconds        = env int, default 75, range 45..900
queue.infrastructure_attempts    = env int, default 3, range 1..5
queue.backoff_seconds            = [5, 15]
queue.lock_seconds               = env int, default 90, range 80..1200
queue.stale_after_seconds        = env int, default 180, range 90..3600
queue.max_failures               = env int, default 0, range 0..1000
execution.executor_timeout_ms    = env int, default 30000, range 1000..30000
execution.cleanup_timeout_ms     = 30000 (code-owned Pack 0012 contract)
```

`max_failures=0` disables fail-fast and preserves a complete result set. A positive value stops new dispatch after that many failed items, cooperatively cancels pending work, cleans up running work, and ends the batch as `failed`.

Add only the corresponding placeholder names to `.env.example`. Do not edit/read `.env`. At bootstrap/use time reject invalid bounds, a non-existent queue connection, `retry_after <= job_timeout_seconds`, `lock_seconds <= job_timeout_seconds`, or `stale_after_seconds <= lock_seconds` with `acceptance_configuration_invalid`. The current database queue default (`retry_after=90`) is compatible with the 75-second job timeout.

## 12. Do Not Change

Do not change dependencies, vendor source, production queue/supervisor/Horizon infrastructure, scheduler, Web/API/MCP, target modules, real targets, target-specific retry policy, the accepted Core executor contract, plan limit `1..1000`, raw artifact capture, `.env`, or manual-only/production data. Do not add automatic scenario/business retries. Do not serialize Eloquent models, executable scenarios, prerequisite values, secret references, resource handles, raw executor payloads, exceptions, DOM, screenshots, or provider data into queue jobs.

## 13. Approved Normalization Decisions

1. Four new tables are required: batch, execution operation, item, and attempt. Existing operation-request rows remain prerequisite records.
2. One `AcceptanceExecutionOperation` belongs to exactly one batch. Start/resume/cancel mutate that lifecycle operation; a retry of a terminal failed item creates a one-item child batch and a new operation rather than reopening terminal history.
3. All aggregate and child rows are retained indefinitely by this Pack. No prune/delete command, cascade deletion, or TTL cleanup is added.
4. Sync and async modes materialize the same batch/item/attempt records and call the same execution service. Sync is sequential and never uses the queue. Async uses bounded Bus Batch waves.
5. A batch does not partially start while executable items still require input/approval. It stays `planned`; its operation is `awaiting_input` before `awaiting_approval`. Once every executable item is ready, resume may dispatch it.
6. A plan snapshot is safe and immutable: version, SHA-256 fingerprint, normalized selector, per-App catalog versions, and allowlisted item classification only. Dispatch/resume must rebuild and compare the exact fingerprint before new work.
7. Automatic queue retry is infrastructure-only. After executor entry, every exception/result is normalized and the job returns. Worker death/timeout becomes stale recovery, never an implicit scenario rerun.
8. Explicit retry is allowed only when the recorded failure is retryable, cleanup succeeded, and either executor entry never occurred or the executor reported a safe retryable transport/startup failure. Scenario/step/oracle failures and unknown post-entry crashes return `acceptance_retry_not_safe`.
9. Cancellation is cooperative. It prevents new waves, cancels pending/queued items, requests cleanup for running items, and records cleanup timeout/failure independently. A process kill cannot guarantee cleanup; stale recovery records that fact.
10. Laravel `job_batches` tracks each dispatch wave; TMS tables are authoritative. A Laravel batch ID is an infrastructure reference, never the lifecycle state source.
11. Current `AcceptanceScenario` step providers execute through Core's `browser` capability. Scenario metadata `capabilities` remain selection/coverage requirements and are never reinterpreted as executor keys. Core HTTP execution needs a target-owned typed HTTP request provider and is therefore not dispatched by this Pack; no browser-to-HTTP fallback is invented.

No schema, state, retry, retention, timeout, fingerprint, or DTO choice remains open.

## 14. Multilingual / Translation Requirements

No UI/RTL work. All state, mode, operation and error keys are language-neutral. Persian worker/recovery messages and display mapping remain Pack 0015 scope.

## 15. UI / Admin UI Requirements

No UI/Admin UI. Future clients call operation services and later query Pack 0014 read models.

## 16. Operator Documentation Requirements

The Run Report records, for Pack 0015 follow-up, the compatible queue worker contract: configured connection and queue, `--timeout=75`, job-owned attempts/backoff, safe shutdown, resume after interruption, explicit retry restrictions, cooperative cancellation, stale recovery, and the requirement that queue `retry_after` exceed worker timeout. Do not edit the current CLI guide in this Pack.

## 17. Implementation Rules

- Use PHP 8.2-compatible syntax, explicit parameter/return types, constructor injection, readonly immutable DTOs, Eloquent relationships, transactions, row locks, and cache atomic locks. Follow sibling conventions.
- Operation names are exactly `acceptance.batch.start`, `acceptance.batch.resume`, `acceptance.batch.item.retry`, and `acceptance.batch.cancel`.
- Start parameters are the accepted selector lists plus `limit`, `profile_id`, `mode` (`sync|async`), and a list of typed `BatchPrerequisiteReference` values. Resume accepts `batch_id`, `operation_id`, `expected_lock_version`, and prerequisite references. Retry accepts `item_id` and `expected_lock_version`. Cancel accepts `batch_id`, `operation_id`, and `expected_lock_version`.
- `BatchPrerequisiteReference` version 1 contains the complete identity tuple and a prerequisite-request UUID; duplicate identities are invalid. Services match identity/profile/schema under lock and never return stored values/references.
- `BatchOperationData` version 1 contains operation/batch IDs, correlation ID, operation/batch states, mode, plan fingerprint, lock version, matched/executable/skipped/pending/blocked/queued/running/passed/failed/cancelled counts, and nullable stable error classification. `BatchItemOperationData` version 1 additionally contains item/parent-item/attempt/Test IDs and the full identity. Neither DTO contains models or presentation fields.
- `OperationResult` accepts those two DTO types, adds the four operations/fields and section 21 codes, and independently revalidates ID/state/count/identity consistency. Handlers cannot override service-owned correlation/classification.
- Start materializes rows in chunks of 100 inside bounded transactions. A stable item idempotency key is SHA-256 of plan fingerprint, profile ID, and NUL-delimited full identity. Unique constraints and locks make repeated dispatch a no-op.
- `RunAcceptanceBatch` receives only batch ID and operation UUID. Under batch row lock it validates state/fingerprint, calculates `max_in_flight - (queued + running)`, creates queued attempts, and dispatches at most that many ID-only `RunAcceptanceBatchItem` jobs in one `Bus::batch(...)->allowFailures()` wave. Wave completion dispatches one ID-only orchestrator continuation after commit.
- `RunAcceptanceBatchItem` receives only item ID, attempt ID, operation UUID and execution-token UUID. It sets `$timeout=75`, `$tries=3`, `$failOnTimeout=true`, uses `[5, 15]` backoff, and claims the exact queued attempt with row and atomic locks. Redelivery cannot create an attempt or enter the executor twice.
- `AcceptanceExecutionService` performs prerequisite validation, target-resource coordination, Core `ExecutorRegistry` execution, Test/Step persistence, classification and cleanup for both `acceptance.run` and batch items. `AcceptanceRunService` persists safe Core browser result DTOs. Existing single-run behavior remains synchronous and does not create batch records.
- Core trace context contains correlation, lifecycle operation, batch, item, attempt and Test IDs. Test may be created immediately before executor entry so its ID is available to Core. Failed pre-execution attempts may have no Test.
- Emit `AcceptanceExecutionStateChanged` only after commit with IDs, previous/current state, stable code and classification. It contains no model or payload snapshot.
- Use structured allowlisted logs. Never log parameter maps, prerequisite content, secret references, snapshots, exception messages/chains, executor data, resources, queue payloads or failed-job payloads.

## 18. Architecture Constraints

Clients request transitions only through `AcceptanceOperationService`. Services own validation/state; jobs reconstruct context from IDs and immutable safe records. The App owns orchestration, prerequisites, resources, Test persistence and recovery. Core executes exactly one normalized capability request. Queue/Bus infrastructure never becomes the domain truth.

Complete hierarchy identity is stored on every batch item and Test. Aggregate batch/operation rows represent a multi-identity plan and therefore store an exact plan fingerprint/selector rather than inventing a synthetic hierarchy tuple. This is the Decision 0015-compliant aggregate representation.

## 19. Validation Rules

The state machine permits only these transitions:

```text
Operation
draft -> awaiting_input | awaiting_approval | ready | failed
awaiting_input -> awaiting_approval | ready | cancelling | expired | failed
awaiting_approval -> awaiting_input | ready | cancelling | expired | failed
ready -> queued | running | cancelling | failed
queued -> running | cancelling | failed
running -> queued (only while Batch=interrupted) | succeeded | failed | cancelling
cancelling -> cancelled | failed
terminal: succeeded | failed | cancelled | expired

Batch
planned -> queued | running | cancelling | failed
queued -> running | cancelling | interrupted | failed
running -> cancelling | completed | completed_with_failures | interrupted | failed
interrupted -> queued | running | cancelling | failed
cancelling -> cancelled | failed
terminal: cancelled | completed | completed_with_failures | failed

Item
pending -> queued | blocked | skipped | cancelled
blocked -> queued | cancelled
queued -> running | blocked | cancelled
running -> passed | failed | blocked | cancelled
terminal: passed | failed | skipped | cancelled

Attempt
queued -> running | abandoned
running -> succeeded | failed | abandoned
terminal: succeeded | failed | abandoned
```

`running -> blocked` is restricted to infrastructure loss before confirmed executor entry. Business/executor failure is `failed`. Terminal rows never transition. Retry creates a child batch/item whose `retry_of_item_id` points to the failed source item. Resume creates a new attempt only for non-terminal pending/blocked items; it never repeats passed/skipped/cancelled/failed items.

## 20. Security Rules

Queue payloads and `job_batches` options contain only numeric IDs and UUID tokens. Snapshots allow only normalized selector keys, catalog versions, classification keys, counts and fingerprints. Profiles, prerequisite input values, secret references, target resources and executor requests are reconstructed in-process and never persisted into orchestration JSON. Tests must use a unique sentinel in every forbidden channel and assert absence from job payloads, failed jobs, events, logs, database snapshot columns and result DTOs.

## 21. Error Handling / Logging / Traceability Requirements

Add these stable codes to the existing safe operation contract:

```text
acceptance_batch_not_found
acceptance_batch_empty
acceptance_batch_conflict
acceptance_batch_transition_invalid
acceptance_batch_plan_changed
acceptance_batch_dispatch_failed
acceptance_attempt_not_found
acceptance_attempt_stale
acceptance_attempt_timeout
acceptance_retry_not_safe
acceptance_batch_cancelled
acceptance_execution_persistence_failed
```

Reuse `input_required`, `approval_required`, `schema_changed`, `acceptance_configuration_invalid`, executor codes, target-resource codes and `cleanup_failed` without renaming. Classification is persisted on operation/item/attempt: `retryable`, `permanent`, `admin_action_required`; success stores all nullable/false as applicable. Preserve the primary error and separate `cleanup_error_code`.

Allowlisted logs/events carry correlation_id, operation_id, batch_id, item_id, attempt_id, Test ID, full identity where applicable, previous/current state, code and classification. Recovery additionally carries stale age in seconds and executor-entry boolean, never exception text.

## 22. Data Model / Migration / Relationship Requirements

All timestamps are framework timestamps plus the named lifecycle timestamps. All state/mode columns are strings cast to enums. All UUIDs are canonical lowercase.

`acceptance_batches`:

- `id` big integer primary key; nullable `parent_batch_id` self-FK with `restrictOnDelete/cascadeOnUpdate`; `profile_id` FK with `restrictOnDelete/cascadeOnUpdate`;
- UUID `correlation_id`; `mode` (16); plan `version` unsigned small integer; `plan_fingerprint` char(64); JSON `selector_snapshot`; JSON `catalog_versions`;
- `state` (32), unsigned `lock_version` default 0, unsigned counts (`matched_count`, `executable_count`, `skipped_count`, `max_failures`, `failure_count`) and `next_dispatch_ordinal` default 0;
- nullable UUID `laravel_batch_id`, `cancel_requested_at`, `started_at`, `finished_at`, `interrupted_at`, `reconciled_at`;
- indexes: correlation; state/updated_at; parent; profile/created_at. Deliberate repeat runs of the same plan are valid, so there is no plan-level unique constraint.

`acceptance_execution_operations`:

- UUID `id` primary key (the operation ID returned for start/retry); `batch_id` unique FK with `restrictOnDelete/cascadeOnUpdate`; UUID `correlation_id`;
- `origin` (16: `start|retry`), `state` (32), unsigned `lock_version` default 0;
- nullable `error_code` (64), nullable booleans `retryable`, `permanent`, boolean `admin_action_required` default false, nullable `queued_at`, `started_at`, `finished_at`, `cancel_requested_at`;
- indexes: correlation; state/updated_at. Resume/cancel must supply this persisted UUID and cannot substitute a transient request identity.

`acceptance_batch_items`:

- big integer primary key; `batch_id` FK restrict/cascade-update; nullable indexed `acceptance_operation_request_id` UUID FK restrict/cascade-update; nullable `retry_of_item_id` self-FK restrict/cascade-update;
- unsigned `ordinal`; full five 64-byte hierarchy keys; `capability` (64); `catalog_version` (64); JSON `classification_snapshot`; char(64) `idempotency_key`;
- `state` (32), unsigned `lock_version` and `attempt_count` default 0; nullable `error_code` (64), `cleanup_error_code` (64), nullable booleans `retryable`, `permanent`, boolean `admin_action_required` default false; nullable `queued_at`, `started_at`, `finished_at`;
- unique `(batch_id, ordinal)`, unique `(batch_id, idempotency_key)`, unique full identity per batch, and indexes `(batch_id,state,ordinal)`, `retry_of_item_id`, and the full hierarchy.

`acceptance_execution_attempts`:

- big integer primary key; `item_id` FK restrict/cascade-update; `operation_id` UUID FK restrict/cascade-update; nullable unique `test_id` FK with `nullOnDelete/cascadeOnUpdate`;
- unsigned `attempt_number`; UUID unique `execution_token`; `state` (32); `executor_capability` (64); nullable `executor_key` (64);
- unsigned `infrastructure_attempts` default 0; boolean `executor_entered` default false; nullable `error_code` and `cleanup_error_code` (64), nullable classification booleans, boolean `admin_action_required` default false, nullable `queued_at`, `started_at`, `heartbeat_at`, `lease_expires_at`, `finished_at`;
- unique `(item_id, attempt_number)` and indexes `(state,lease_expires_at)`, `(operation_id,state)`, and `created_at`.

Relationships: Profile has many batches; batch belongs to Profile/parent and has one operation/many items/child batches; operation belongs to batch and has many attempts; item belongs to batch/prerequisite request/retry source and has many attempts/retry children; attempt belongs to item/operation and optionally Test; Test has one attempt; prerequisite request has many items so a safe explicit child retry can reuse the same still-valid prerequisite fact. `classification_snapshot` is exactly capabilities, tags, disposition and evidence_mode. No arbitrary metadata column is permitted.

Migration `down()` drops attempts, items, operations, then batches through the four reverse migrations. It is intentionally destructive and may run only on the verified disposable test database during execution. No automatic deletion exists in runtime source.

## 23. Commenting Requirements

Use PHPDoc for array shapes/generics and concise invariant comments only at transition, idempotency, fingerprint, lease, retry-safety, and cancel/cleanup boundaries. Do not narrate ordinary code.

## 24. Testing Requirements

- State-machine tests cover every listed legal edge, representative illegal edges, and every terminal-state rejection.
- Persistence tests cover columns, casts, relationships, every FK action, uniqueness, retention, rollback on the disposable test DB, and no cascade deletion of history.
- Feature tests cover sync success/failure, async dispatch waves, prerequisite blocking/resume, duplicate/concurrent start-resume-cancel, catalog drift, fail-fast threshold, cancellation before/during execution, cleanup failure, stale before/after executor entry, safe/unsafe retry, child lineage, and final aggregation.
- Idempotency tests redeliver each job, race two dispatchers, and prove one executor entry/Test per attempt and no duplicate logical item.
- Queue/Bus/Event fakes prove payload allowlists and bounds; one controlled database-queue integration path uses fake executor/resources and a bounded worker timeout. No real target/network/browser is used.
- Existing single-run, prerequisite and target-resource suites prove regression and that `acceptance.run` uses the shared execution service/Core registry.
- Sentinel scans cover database JSON, queue/failed-job payloads, events, DTOs and logs.

## 25. Acceptance Checklist

- [ ] exact section 19 states/transitions and terminal protection implemented;
- [ ] sync/async/batch/single-run paths share the execution service and Core registry;
- [ ] immutable plan fingerprint and complete item identity persist and are revalidated;
- [ ] bounded wave dispatch never exceeds `max_in_flight`;
- [ ] resume, child retry, cancellation and stale recovery preserve all history;
- [ ] queue timeouts/locks/stale windows/config bounds are enforced;
- [ ] every job/event/result/log uses the safe allowlist and sentinel scans pass;
- [ ] migrations, focused tests, full safe regression, Pint and diff checks pass;
- [ ] Run Report and indexes record only observed evidence.

## 26. Tests to Add

Create the five exact files in section 8. Name tests by behavior, including: complete transition matrix; terminal protection; schema/FK retention; sync/async equivalence; four-item in-flight ceiling; duplicate delivery; two-dispatcher race; prerequisite gate; fingerprint drift; pre-entry stale resume; post-entry stale no-rerun; safe child retry; unsafe retry rejection; cancel race; cleanup failure; failure threshold; Test/attempt linkage; typed DTO validation; and sensitive sentinel omission.

Update section 9 tests only where the shared execution service and new operation contracts change their direct boundary. Do not weaken existing assertions or delete tests.

## 27. Tests to Run

After implementation and before any acceptance request:

```text
php artisan test --compact tests/Unit/AcceptanceExecutionStateMachineTest.php
php artisan test --compact tests/Unit/AcceptanceExecutionIdempotencyTest.php
php artisan test --compact tests/Unit/AcceptanceRecoveryServiceTest.php
php artisan test --compact tests/Feature/AcceptanceBatchPersistenceTest.php
php artisan test --compact tests/Feature/AcceptanceAsyncBatchTest.php
php artisan test --compact tests/Unit/AcceptanceOperationServiceTest.php
php artisan test --compact tests/Feature/AcceptanceRunCommandTest.php tests/Feature/AcceptancePrerequisiteWorkflowTest.php tests/Feature/AcceptanceTargetResourceLifecycleTest.php
php artisan migrate:fresh --env=testing --no-interaction
php artisan migrate:rollback --step=4 --env=testing --no-interaction
php artisan migrate --env=testing --no-interaction
vendor/bin/pint --dirty --format agent
php artisan test --compact
git diff --check
```

Before migration commands, prove the resolved testing connection/database is disposable and not production-like. Stop instead of running any migration if that proof fails. The controlled queue integration test is PHPUnit-owned and bounded; do not launch an unbounded external worker.

## 28. Expected Output

Validated config, four-table durable schema/models, state machine, typed operation DTOs/handlers, shared executor/resource/persistence service, bounded jobs/Bus waves, resume/retry/cancel/recovery behavior, comprehensive tests, one Run Report, synchronized Pack/Run indexes, and no target I/O or commit.

## 29. Operator Execution Checklist

Before:

- [x] Pack 0012 is accepted and its Run evidence is present.
- [x] Core Pack 0003 is accepted at `5a0c55e72cff95a1a84e7768d7ceba131a405cb2`.
- [x] exact schemas, FKs, retention and rollback risk are fixed in section 22.
- [x] exact transitions and terminal/retry behavior are fixed in sections 13 and 19.
- [x] queue bounds, timeout/lock/stale/failure defaults are fixed in section 11.
- [x] DTOs, operations, errors, fingerprint and cancellation rules are fixed.
- [x] repository branch is `main`, synchronized with `origin/main`, and clean before normalization.
- [x] operator grants separate execution approval after reading this normalized Pack and pre-execution report.

After:

- [x] inspect migration/model/state-machine diff and retention/FK behavior;
- [x] inspect queue payload/event/log/DTO sentinel evidence;
- [x] inspect sync, bounded async, interruption/resume, child retry and cancel evidence;
- [x] confirm exact focused/full test, migration, Pint and diff-check results;
- [x] approve or reject Pack acceptance;
- [x] authorize commit separately if acceptance is approved.

## 30. Agent Final Report

Report the implemented state table, schema/FKs/retention, queue configuration, exact operation signatures/DTOs, concurrency/lease/recovery behavior, catalog/idempotency evidence, retry/cancel/cleanup outcomes, sensitive scans, migration safety proof, focused/full tests, formatting, diff, changed files, deviations, residual risks and whether a commit was made. Never report unrun worker/target evidence.

## 31. Review Checklist

Review transaction/lock ordering, state integrity, terminal protection, exact identity, plan drift, idempotency constraints, bounded waves, worker redelivery/timeout, safe retry lineage, cancellation/cleanup race, Test linkage, payload/log safety, database portability, relationship types, config validation, typed result/client separation, regression coverage and scope discipline.

## 32. Rollback / Safety Notes

Before rollback, stop queue dispatch/workers and record any active batch/attempt. Application rollback is a code/config revert. Database rollback drops durable execution history and is authorized only on the verified disposable test database during this Pack; production-like rollback requires a separately approved backup/export and maintenance procedure. Existing Tests are not deleted when an attempt FK is removed; deleting a Test nulls only the attempt link. No production database, queue, browser or target command is authorized.

## 33. Stop Conditions

Stop for an unaccepted predecessor; dirty/unrelated overlapping changes; invalid/non-disposable test DB; unresolved migration portability; queue `retry_after` not greater than timeout; unbounded dispatch; non-atomic duplicate execution; catalog fingerprint mismatch during execution; unsafe retry; secret/raw payload persistence; real/production-like target; required dependency change; Core contract change; failing focused regression not caused and fixed within scope; or any need to alter the fixed normalized choices.

## 34. Open Questions

None. Deployment-specific worker count, supervisor/Horizon topology, scheduling and production retention/pruning remain operator infrastructure or later separately approved work. Pack 0014 owns query/report read models; Pack 0015 owns CLI presentation and Persian operator guidance.
