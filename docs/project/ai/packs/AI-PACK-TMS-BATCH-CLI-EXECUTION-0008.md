# AI-PACK-TMS-BATCH-CLI-EXECUTION-0008 — Sequential Resumable Batch CLI Execution

Status: `superseded — do not normalize or execute`

Generated: `2026-10-05`

Decision: `docs/project/ai/decisions/TMS-DECISION-0006-remove-cancelled-fixture-and-safeguard-packs.md` controls deleted prerequisites and normalization. Decision 0005 retains synthetic testing/staging/safe-output constraints; unchanged CLI/ownership constraints from Decision 0004 still apply.

Depends On: accepted project Packs 0004 and 0007, and accepted Core Browser Observability Pack 0001; cancelled/deleted Pack 0006 is not a prerequisite.

Planning Revision: 2026-10-06; superseded Core Evidence Safety 0002 and project Ephemeral Execution Context 0005 are not prerequisites.

Cancellation Revision (2026-10-06): Decision 0006 deletes those two proposals and cancels/removes fixture Pack 0006 after rollback. No fixture lifecycle, target-environment guard, lease types or fixture error codes from it exist in accepted source. Resolve any required environment/adapter/cleanup integration explicitly during normalization; do not silently recreate the cancelled Pack.

Execution Gate: Closed. Decision 0013 replaces this Pack; retain this file as historical planning only.

Supersession Notice (2026-10-07): Decision 0013 replaces this sequential CLI-only proposal with transport-neutral operations, explicit input/approval, extensible executors, Laravel queue/batch orchestration, complete Artisan client operations, and cross-client reporting. This file must not be normalized or executed.

## 1. Task ID

`AI-PACK-TMS-BATCH-CLI-EXECUTION-0008`

## 2. Task Title

Add persisted sequential, bounded, resumable Acceptance batch execution through Artisan commands.

## 3. Goal

Allow an operator or CI process to create, run, inspect, interrupt, resume, cancel, and report a large automated scenario plan through CLI without API/UI/queue/parallel execution and without loading or launching all variants at once.

## 4. Context

Pack 0007 supplies a safe deterministic lazy plan. Current `acceptance:run` executes one App/scenario/Profile synchronously and persists one Test. Large target contracts require a parent batch identity, durable progress, bounded sequential execution, safe restart behavior, and aggregate reporting. Decision 0005 restricts targets to testing/staging with synthetic data and moves minimum output/history safeguards here instead of standalone secret/evidence frameworks.

## 5. Related Release / Phase

Not applicable; final Pack in the initial generic CLI roadmap.

## 6. Related Epic / Feature / Story

Reusable human-acceptance automation — scalable operational execution and reporting.

## 7. Source References

Decisions 0002–0006; accepted active predecessor Pack/Run records; current Profile/Test/Step schema/models, Registry/catalog, command, RunService, enums, migrations, factories, and tests; applicable data/status/idempotency/error/test/reporting rules. Decision 0006 controls deleted dependencies and required normalization; Decision 0005 retains the unchanged safeguard scope.

## 8. Files to Create

Anticipated, subject to normalization:

- batch/progress models and migrations;
- batch planning/execution/state services;
- Artisan commands for run/start, status, resume, cancel, and report, with exact names normalized;
- factories and focused unit/feature/database tests;
- project Run Report after execution.

## 9. Files to Edit

Anticipated Profile/Test relationships, AcceptanceRunService trace linkage, service-provider registration if required, command tests, indexes, and this Pack lifecycle fields. Exact paths are not authorized until normalized.

## 10. Files to Read / Reference

Repository/project/Core routing/profiles; shared scope/execution/data/status/idempotency/error/test/reporting/Git rules; accepted active predecessors; Decision 0005; current schema/models/factories/services/commands/tests; actual database driver behavior used by tests. Superseded proposals are not implementation prerequisites.

## 11. Configuration / Settings Requirements

Expected non-sensitive versioned defaults: per-invocation item limit/chunk size and stale-run threshold only if required. No API, UI, queue, scheduler, parallel worker, real secret, or target config. Any long-running process/CI timeout requirement must be documented in the normalized operator guide. Expanded operator-configurable browser/Profile/CLI options, resolution precedence, and effective-browser-settings snapshots remain deferred. Exact testing/staging target validation and adapter boundaries require normalization against actual source; no cancelled fixture/guard contract may be assumed. Do not introduce a generic secret provider/store/lease, comprehensive capture guard, or recording feature.

## 12. Do Not Change

API/UI, queue/jobs/workers, scheduler, parallel browsers, automatic retry engine, target Apps/contracts, target auth/fixtures/selectors, expanded browser/Profile/CLI settings or effective-browser-settings snapshots, generic secret infrastructure or comprehensive capture guards, capture/recording/export and artifact storage, external Provider I/O, unrelated Profile CRUD, dependencies, `.env`, vendor, or target repositories.

## 13. Clarification Questions Before Implementation

Normalization must resolve and record before execution:

1. exact batch/item/progress schema and relationship to existing Test rows;
2. status enum and legal transition state machine, including interrupted/stale/resumable/cancelled behavior;
3. plan fingerprint snapshot, catalog-drift behavior, and resume cursor/idempotency rules;
4. history retention and Profile deletion behavior;
5. command names/signatures, JSON contracts, exit codes, caps, and confirmation behavior;
6. failure policy: continue, stop, or configurable threshold for scenario failures;
7. exact target-environment validation, App-owned resource cleanup and process-termination recovery boundaries after cancellation of Pack 0006; no existing shared lease/lifecycle/error seam may be assumed.

## 14. Multilingual / Translation Requirements

CLI JSON fields, statuses, and error codes are language-neutral. Any human-readable CLI help/message must use the actual project convention resolved during normalization. No RTL/UI impact.

## 15. UI / Admin UI Requirements

No UI changes required. CLI output and persisted records are the only initial control/reporting surfaces.

## 16. Operator Documentation Requirements

Operator Documentation Impact: Yes. The normalized Pack must provide the generic CLI workflow: plan/start/status/resume/cancel/report, safe selectors, caps, interruption recovery, exit codes, testing/staging and synthetic-data prerequisites, minimum credential omission from history/reports, and what is intentionally skipped. No secret-manager/capture-policy setup or expanded browser-settings workflow is required.

## 17. Implementation Rules

- Execute only `automated` and capability-satisfied variants; count/report manual, blocked, and not-implemented without running them.
- Iterate lazily and sequentially with a bounded per-invocation limit; never materialize or launch the whole matrix concurrently.
- Persist batch identity, immutable selector/plan fingerprint, safe counts, cursor/progress, state, timestamps, and links to created Test runs.
- Resume only when state and plan/catalog compatibility allow it; duplicate command execution must not repeat a completed logical item.
- Check cancellation between items and before target-environment validation or fixture/target-App/browser setup.
- Normalize the smallest required target-environment/adapter boundary against accepted source before new/resumed execution; production/unknown-target rejection must have an explicitly approved contract. The cancelled fixture lifecycle is not available. Target authentication stays App-owned without a generic secret lease/provider prerequisite.
- Store only allowlisted operational metadata. Usable account passwords, cookies, tokens, authenticated storage, and raw adapter error values must not enter Test/Step/Batch records, plan/progress snapshots, CLI output, reports, or logs.
- No automatic retry. A failed item is recorded once; explicit resume behavior must follow the normalized state contract.
- Reports contain allowlisted metadata only.

## 18. Architecture Constraints

CLI is the sole operator channel. Batch orchestration is project-owned; Core continues one-scenario runtime. Target Apps remain source-owned adapters and execute only testing/staging targets with synthetic data. No API/UI/queue/scheduler/parallel implementation may be introduced opportunistically. Decision 0005 permits minimum lifecycle/output safeguards only; no expanded settings or standalone secret/capture framework.

## 19. Validation Rules

The normalized Pack must prove transitions, durable progress, crash/stale simulation, resume/idempotency, catalog-drift rejection, cancellation, failure policy, bounded lazy iteration, Test linkage, aggregate counts, retention relationships, safe JSON/logs, zero execution of ineligible variants, production/unknown-target rejection on start/resume through the contract approved at normalization, and fake authentication-sentinel absence from persistence/output/logs on success and failure.

## 20. Security Rules

Persist only safe keys, classifications, counts, fingerprints, statuses, timestamps, error codes, and relationships. Do not persist/log/output selectors, URLs, usable credentials/references, session cookies/tokens, authenticated storage, fixture payloads, raw DOM/network/console/trace, personal identifiers, arbitrary Step results, or raw exceptions. Use synthetic testing/staging fakes in shared tests; preserve minimum output omission without adding a generic secret store/provider/lease or capture guard. Do not add recording/export channels or claim universal no-capture enforcement.

## 21. Error Handling / Logging / Traceability Requirements

High impact: command validation, status transitions, persistence failures, stale/interrupted runs, catalog drift, idempotency conflict, cancellation, active prerequisite or unsafe/unknown-target failure, scenario failure, App-owned cleanup failure when applicable, and report failure. Normalization must define each stable error code, exit code, retry/permanent/admin-action classification, state effect, log event/context, Batch/Test trace flow, and minimum omission test. No accepted environment/fixture error contract from cancelled Pack 0006 exists; exact required mappings are deferred and must be approved before implementation. Raw fake adapter errors containing authentication sentinels must not leak through history/log/report/CLI paths. No current code or API/UI contract is changed by this Draft.

## 22. Data Model / Migration / Relationship Requirements

Data/Migration Impact: Yes, details deferred and blocking. Normalization must fully define tables, columns/types/defaults/nullability, indexes/unique constraints, foreign keys with explicit delete/update actions, Eloquent relationships, plan/progress snapshots, history retention, Profile/Test behavior, rollback/data-loss risk, and database tests. Operational history must not cascade-delete by default. Snapshots contain operational plan/progress metadata only; no credentials, authenticated storage, secret infrastructure schema, or new effective-browser-settings snapshot.

## 23. Commenting Requirements

Document state-transition, idempotency, resume cursor/fingerprint, cancellation boundary, and history-retention invariants.

## 24. Testing Requirements

Mandatory unit/state-machine, feature/CLI, database/relationship, idempotency, bounded-lazy, interruption/resume, cancellation, drift, failure precedence, logging/traceability, minimum credential omission, explicitly normalized testing/staging validation, and existing single-run regression tests. Shared implementation uses synthetic fake/local execution only; actual target testing/staging I/O remains outside this Pack. Do not require standalone secret/capture infrastructure suites.

## 25. Acceptance Checklist

- [ ] schema/retention/status/idempotency Decisions approved;
- [ ] only eligible variants execute sequentially and boundedly;
- [ ] progress survives interruption and resumes without duplication;
- [ ] cancellation/drift/failure transitions are deterministic;
- [ ] Batch-to-Test traceability and safe report counts are proven;
- [ ] exact environment/adapter/error boundaries are normalized against accepted source; start/resume cannot bypass the separately approved production/unknown-target rejection;
- [ ] fake authentication sentinels are absent from persisted history, snapshots, CLI/report JSON, errors, and logs on success/failure/resume;
- [ ] no expanded browser settings or generic secret/capture subsystem introduced;
- [ ] operational history relationships and rollback are explicit;
- [ ] API/UI/queue/parallel/retry/target scope remains absent.

## 26. Tests to Add

Normalize into named state-transition, schema/relationship, plan snapshot, bounded execution, resume/idempotency, stale/catalog drift, cancel, continue/stop failure policy, prerequisite/cleanup failure, JSON/exit-code, log/traceability, retention, rollback-review, and regression tests. Add fake start/resume environment cases proving no setup/Runner call for production/unknown targets. Add fake success/failure/resume cases proving credential/session/token sentinels are absent from Test/Step/Batch records, plan/progress snapshots, CLI/report JSON, normalized errors, and structured logs. These are local batch-boundary tests, not a secret-management framework.

## 27. Tests to Run

Deferred until normalization. Exact PHPUnit/Artisan/migration commands, safe database environment, browser-fake boundary, and forbidden commands must be stated before approval. No real target or production data.

## 28. Expected Output

Persisted sequential resumable batch CLI execution with safe status/report commands, exact tests and operator documentation, and no second execution channel.

## 29. Operator Execution Checklist

### Before AI Execution

- Approve normalized schema, retention, state machine, failure policy, command contracts, and safe database environment.

### After AI Execution

- Execute a bounded synthetic interrupted/resume/cancel demonstration and verify no duplicate Test runs or sensitive output.

## 30. Agent Final Report

Report schema/transitions, exact commands and outputs, tests, Batch/Test traceability, history/rollback assessment, bounded execution evidence, minimum environment/omission checks, and deferred queue/API/UI work. Do not claim a universal runtime no-capture or generic secret-management guarantee.

## 31. Review Checklist

Review database integrity/retention, state machine, idempotency, resume/cancel/drift, bounded laziness, failure precedence, trace/log/output safety, documentation, and scope exclusions.

## 32. Rollback / Safety Notes

Rollback must stop active local execution, preserve/report operational history risk, follow approved down migrations, and never use destructive broad database or Git commands. Target-App cleanup and process-termination recovery limits must be explicitly resolved during normalization; cancelled Pack 0006 is not authority for them.

## 33. Stop Conditions

Stop for unresolved schema/status/retention/error contracts, production-like database or target, destructive migration risk without approval, required queue/parallel/API/UI, expanded settings or secret/capture infrastructure outside this scope, catalog drift without safe resolution, duplicate execution risk, credential/sensitive persistence/output, target-specific shared code, or unaccepted active predecessors. Superseded Core 0002/project 0005 must not be reinstated as prerequisites.

## 34. Open Questions

The seven normalization decisions in section 13 block execution. After execution and acceptance, perform a cross-Pack Review before creating a target-system App Pack; create Remediation only for concrete findings.
