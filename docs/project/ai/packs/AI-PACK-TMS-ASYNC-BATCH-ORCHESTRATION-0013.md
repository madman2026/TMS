# AI-PACK-TMS-ASYNC-BATCH-ORCHESTRATION-0013 — Async Queue and Bus Batch orchestration

Status: `draft — depends on stages 1–5; normalization and separate operator approval required`

Generated: `2026-10-07`

Decision: Decisions 0009–0011, 0013, and 0014; Decision 0014 applied on 2026-10-08 (documentation only).

## 1. Task ID

`AI-PACK-TMS-ASYNC-BATCH-ORCHESTRATION-0013`

## 2. Task Title

Implement durable synchronous, queued, batched, resumable, retryable, cancellable, and bounded-parallel execution.

## 3. Goal

Execute large plans without an open terminal while preserving exact state, attempts, idempotency, cleanup, and recovery.

## 4. Context

Pack 0008 is superseded. Decision 0010 approves Laravel Queue/Jobs/Bus Batch and exact operation/batch/item/attempt states.

## 5. Related Release / Phase

Decision 0013, stage 6.

## 6. Related Epic / Feature / Story

Scalable E2E orchestration.

## 7. Source References

Decisions 0005, 0008–0011; accepted stages 1–5; Laravel 12 Queue/Bus Batch/lock documentation as framework authority.

`docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md` — typed execution/recovery data and client-owned progress.

## 8. Files to Create

- `config/acceptance.php`
- `app/Acceptance/Execution/Enums/BatchState.php`
- `app/Acceptance/Execution/Enums/BatchItemState.php`
- `app/Acceptance/Execution/Enums/AttemptState.php`
- `app/Models/AcceptanceBatch.php`
- `app/Models/AcceptanceBatchItem.php`
- `app/Models/AcceptanceExecutionAttempt.php`
- `database/migrations/2026_10_07_000020_create_acceptance_batches_table.php`
- `database/migrations/2026_10_07_000021_create_acceptance_batch_items_table.php`
- `database/migrations/2026_10_07_000022_create_acceptance_execution_attempts_table.php`
- `database/factories/AcceptanceBatchFactory.php`
- `database/factories/AcceptanceBatchItemFactory.php`
- `app/Acceptance/Execution/AcceptanceBatchService.php`
- `app/Acceptance/Execution/AcceptanceExecutionService.php`
- `app/Acceptance/Execution/AcceptanceRecoveryService.php`
- `app/Jobs/RunAcceptanceBatch.php`
- `app/Jobs/RunAcceptanceBatchItem.php`
- `app/Events/AcceptanceExecutionStateChanged.php`
- `app/Acceptance/Operations/Handlers/StartAcceptanceBatch.php`
- `app/Acceptance/Operations/Handlers/ResumeAcceptanceBatch.php`
- `app/Acceptance/Operations/Handlers/RetryAcceptanceBatchItem.php`
- `app/Acceptance/Operations/Handlers/CancelAcceptanceBatch.php`
- `tests/Feature/AcceptanceAsyncBatchTest.php`
- `tests/Unit/AcceptanceExecutionStateMachineTest.php`
- `tests/Unit/AcceptanceExecutionIdempotencyTest.php`

## 9. Files to Edit

- `.env.example`
- `app/Models/AcceptanceOperationRequest.php`
- `app/Models/Test.php`
- `app/Providers/AppServiceProvider.php`
- operation/resource/executor services from stages 1–5
- existing run persistence tests

## 10. Files to Read / Reference

Project profile; Decisions 0009–0011 and 0014; data/status/idempotency/error/testing rules; Laravel queue config and jobs migration; exact section 8–9 files and accepted predecessor source.

## 11. Configuration / Settings Requirements

Create non-sensitive keys for queue connection/name, dispatch chunk, max in-flight items, lock TTL, item timeout, stale threshold, infrastructure attempts, and backoff. Add placeholder env names to `.env.example`; do not read/edit `.env`. Defaults must be safe and bounded. Operator must start a Laravel queue worker before async use.

## 12. Do Not Change

Production queue infrastructure, scheduler, Web/API/MCP, target-specific retry, real targets, dependencies, `.env`, manual-only tests, or raw artifact capture.

## 13. Clarification Questions Before Implementation

Normalization must approve exact schema/FKs/retention, complete transition table, queue defaults, timeout/lock recovery, failure threshold, retry policy, catalog fingerprint, and cancellation cleanup timeout.

Define immutable dispatch/recovery result DTO fields/version and exact additional DTO paths before execution approval. Console progress and client JSON are Pack 0015 presentation work, not execution-service output.

## 14. Multilingual / Translation Requirements

No UI/RTL. State/error/config keys are language-neutral. Persian worker/recovery guidance belongs in Pack 0015.

## 15. UI / Admin UI Requirements

No UI/Admin UI. Future clients query operation services.

## 16. Operator Documentation Requirements

Record queue worker setup, start/resume/retry/cancel semantics, timeout/stale recovery, parallel limits, and safe shutdown for Pack 0015 guide update.

## 17. Implementation Rules

- Plan snapshot/fingerprint is immutable; items are created in bounded chunks.
- Start/resume/retry/cancel return typed safe identifiers, state, counts and outcome/classification data, never console strings, encoded JSON, models or progress callbacks coupled to a terminal. Queue ID/reference payloads remain infrastructure contracts; they do not define client presentation.
- Use DB transactions, unique idempotency keys, atomic state transitions, and Laravel locks.
- Bus Batch tracks dispatch; TMS tables remain execution truth.
- Retry creates a new Attempt; resume never repeats terminal items.
- Cancel prevents new dispatch and invokes resource cleanup.
- Parallelize independent items only within configured bounds.

## 18. Architecture Constraints

Clients request transitions through operation services. Jobs reconstruct safe context by IDs/references. No serialized model containing secrets/credentials. Core executes one normalized request; project owns orchestration.

## 19. Validation Rules

Prove every legal/illegal transition, duplicate dispatch, concurrent start/resume/retry, crash/stale recovery, cancel race, catalog drift, failure thresholds, bounded concurrency, cleanup, and terminal protection. Assert typed service results/trace identities independently of CLI lifetime; client progress/JSON tests belong to Pack 0015.

## 20. Security Rules

Queue payloads contain IDs and safe references only. No secret/raw input/Profile extra/DOM/provider payload. Logs and failed-job payload review must prove sentinel omission.

## 21. Error Handling / Logging / Traceability Requirements

Define stable transition, lock, drift, timeout, stale, queue, idempotency, retry-not-safe, cancel, cleanup, and executor errors. Preserve primary and cleanup outcomes. Structured logs/events carry operation_id, batch_id, item_id, attempt_id, Test ID, correlation_id, state, code, retryable, and operator-action flag.

## 22. Data Model / Migration / Relationship Requirements

Three tables plus links to operation request/Test. Normalization must specify every column, index, composite unique idempotency constraint, explicit FK delete/update action, snapshot allowlist, retention, rollback, and data-loss risk. Operational history does not cascade-delete by default.

## 23. Commenting Requirements

Document transition/idempotency/lock/fingerprint/retry/cancel invariants.

## 24. Testing Requirements

Unit state-machine/idempotency tests; feature/database/job/batch tests using Queue/Bus/Event fakes plus controlled synchronous queue integration; concurrency simulation; migration/rollback review; sensitive sentinel scans; existing sync-run regression.

## 25. Acceptance Checklist

- [ ] exact Decision 0010 states/transitions implemented;
- [ ] sync/async/batch modes share execution service;
- [ ] resume/retry/cancel preserve history and idempotency;
- [ ] bounded parallelism and crash recovery proven;
- [ ] queue payload/history/logs contain no secret.

## 26. Tests to Add

Exact tests in section 8 plus database relationship, queue payload, cancel-cleanup, retry-history, and stale recovery cases named at normalization.

## 27. Tests to Run

Exact safe PHPUnit suite, migrations and rollback on test DB, queue fake/integration commands with bounded timeout, existing acceptance regressions, and `git diff --check`. No target I/O.

## 28. Expected Output

Config, schema/models, orchestration services/jobs/events/operations, tests, Run Report, indexes, and guide follow-up.

## 29. Operator Execution Checklist

Before: approve schema/state/retention/queue config and safe DB. After: run bounded async success, interruption/resume, retry, cancel, and parallel demonstrations.

## 30. Agent Final Report

Report state table, schema/retention, queue config, concurrency/recovery evidence, sensitive scans, tests, and operator setup.

## 31. Review Checklist

Review state/DB integrity, idempotency, concurrency, retries, cancellation, cleanup, payload safety, worker docs, and scope.

## 32. Rollback / Safety Notes

Stop workers and dispatch first. Preserve/report active/history rows before down migrations. No production DB/queue operation.

## 33. Stop Conditions

Stop for unresolved state/schema/retention, unsafe DB, secret payload, unbounded dispatch, duplicate logical execution, production-like target, or unaccepted predecessors.

## 34. Open Questions

Section 13 choices block approval; deployment-specific worker scaling remains operator infrastructure, not source automation.
