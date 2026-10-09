# AI-PACK-TMS-TARGET-RESOURCE-LIFECYCLE-0012 — Target resource lifecycle contracts

Status: `accepted — technical validation passed; operator accepted 2026-10-09`

Generated: `2026-10-07`

Normalized: `2026-10-09`

Decision: Decisions 0005–0010 and 0014–0016. Decision 0015 owns stage 5 and the single version-2 hierarchy contract. Decision 0013 is superseded history.

## 1. Task ID

`AI-PACK-TMS-TARGET-RESOURCE-LIFECYCLE-0012`

## 2. Task Title

Define target-owned readiness, account, fixture, oracle, and cleanup boundaries.

## 3. Goal

Wrap one complete App/Component/Suite/Scenario/Variant execution in a fail-closed, target-neutral resource lifecycle without hardcoded target data, raw secrets, target behavior, or client presentation in shared TMS.

## 4. Context

Accepted Packs 0009–0011 provide the transport-neutral operation boundary, the complete version-2 hierarchy, and durable prerequisite requests. This Pack adds the missing stage-5 lifecycle boundary before the Core executor and async orchestration stages.

The cancelled generic fixture/secret proposals remain deleted. This Pack introduces narrow App-owned ports, an App-keyed registry, immutable safe DTOs, and one coordinator. It does not introduce a shared resource database, secret resolver/store, target implementation, queue, or client.

## 5. Related Release / Phase

Decision 0015, stage 5.

## 6. Related Epic / Feature / Story

Target resource preparation, observable post-execution assertions, and deterministic cleanup.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`
- `docs/project/ai/decisions/TMS-DECISION-0006-remove-cancelled-fixture-and-safeguard-packs.md`
- `docs/project/ai/decisions/TMS-DECISION-0007-nwidart-target-modules-and-component-hierarchy.md`
- `docs/project/ai/decisions/TMS-DECISION-0008-transport-neutral-operations-and-artisan-client.md`
- `docs/project/ai/decisions/TMS-DECISION-0009-operator-input-approval-and-sensitive-values.md`
- `docs/project/ai/decisions/TMS-DECISION-0010-async-batch-state-and-recovery.md`
- `docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md`
- `docs/project/ai/decisions/TMS-DECISION-0015-clean-acceptance-hierarchy-cutover.md`
- `docs/project/ai/decisions/TMS-DECISION-0016-prerequisite-operation-naming.md`
- accepted Pack 0009–0011 source and Run evidence
- project profile and the scope, execution, error/logging/traceability, security, testing, reporting, commenting, Git, and documentation-maintenance rules

Decision 0013 is historical and does not control stage numbering or compatibility.

## 8. Files to Create

- `app/Acceptance/Prerequisites/Data/PrerequisiteExecutionData.php`
- `app/Acceptance/Targets/Contracts/TargetReadinessProbe.php`
- `app/Acceptance/Targets/Contracts/TargetAccountResolver.php`
- `app/Acceptance/Targets/Contracts/TargetFixtureManager.php`
- `app/Acceptance/Targets/Contracts/TargetOracle.php`
- `app/Acceptance/Targets/Contracts/TargetCleanup.php`
- `app/Acceptance/Targets/Data/TargetEnvironment.php`
- `app/Acceptance/Targets/Data/TargetReadinessResult.php`
- `app/Acceptance/Targets/Data/TargetContext.php`
- `app/Acceptance/Targets/Data/ResourceReference.php`
- `app/Acceptance/Targets/Data/ResourceProvisionResult.php`
- `app/Acceptance/Targets/Data/TargetOracleResult.php`
- `app/Acceptance/Targets/Data/CleanupResult.php`
- `app/Acceptance/Targets/Data/TargetExecutionOutcome.php`
- `app/Acceptance/Targets/Data/TargetResourceLifecycleData.php`
- `app/Acceptance/Targets/TargetResourceAdapters.php`
- `app/Acceptance/Targets/TargetResourceRegistry.php`
- `app/Acceptance/Targets/TargetResourceCoordinator.php`
- `tests/Unit/TargetResourceCoordinatorTest.php`
- `tests/Unit/TargetResourceContractSafetyTest.php`

Execution reporting may create the exact Pack Run Report required by the reporting rules.

## 9. Files to Edit

- `app/Acceptance/Prerequisites/PrerequisiteService.php`
- `app/Acceptance/Prerequisites/PrerequisiteException.php`
- `app/Acceptance/Operations/AcceptanceOperationService.php`
- `app/Acceptance/Operations/Data/OperationResult.php`
- `app/Acceptance/Operations/Data/RunOperationData.php`
- `app/Acceptance/Operations/Handlers/RunAcceptanceScenario.php`
- `app/Providers/AppServiceProvider.php`
- `tests/Unit/PrerequisiteServiceTest.php`
- `tests/Unit/AcceptanceOperationServiceTest.php`
- `tests/Feature/AcceptancePrerequisiteWorkflowTest.php`
- `tests/Feature/AcceptanceOperationCommandContractTest.php`
- `tests/Feature/AcceptanceRunCommandTest.php`
- `tests/Feature/AcceptanceRunPersistenceTest.php`
- `docs/project/ai/packs/AI-PACK-TMS-TARGET-RESOURCE-LIFECYCLE-0012.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md`

No equivalent-path substitution may cross into Core or a target module. Any newly required implementation path outside sections 8–9 is a stop condition.

## 10. Files to Read / Reference

Only section 7 references, section 8–9 files, directly used Laravel source, and directly affected predecessor tests may be read. Do not bulk-load unrelated Packs, Runs, Reviews, Core source, target source, archives, or vendor source.

## 11. Configuration / Settings Requirements

No config, `.env`, Admin Setting, external-integration setting, secret manager, queue/cache/log-channel setting, or operator manual setup is introduced.

`TargetResourceCoordinator::CLEANUP_TIMEOUT_MS` is a code-owned cooperative budget of 30,000 milliseconds. The coordinator passes it to the target cleanup port and treats a returned timeout/unfinished result as `cleanup_failed`; it does not sleep, spawn a process, kill work, or claim hard timeout enforcement. Durable cancellation and worker hard-timeout behavior remain Pack 0013 scope.

Shared tests use constructor-injected fakes, synthetic values, explicit testing/staging readiness results, and the existing isolated SQLite test setup only where prerequisite persistence is exercised.

## 12. Do Not Change

- `Modules/Core/**` or the later Core executor contract;
- `app/Contracts/AcceptanceComponentProvider.php`, hierarchy descriptors, Pack 0010 generator/validator/stubs, or target-module layout;
- actual DK/ND resources, target modules, Profile credential storage, or Profile `extra`;
- a generic secret provider/store/lease, shared resource table/model, target payload store, or external-error snapshot store;
- queue, jobs, scheduler, batch state, Web/API/MCP, prompts, command presentation/serialization, translations, or UI;
- dependencies, lockfiles, vendor, environment files, production data, or real target/browser/network systems.

## 13. Clarification Questions Before Implementation

No unresolved clarification remains. The normalized choices are:

1. Resource adapters are registered per `app_key` in a dedicated `TargetResourceRegistry`. `AcceptanceComponentProvider` and Pack 0010 scaffolding remain unchanged; hierarchy discovery is not a resource-service locator.
2. `TargetResourceAdapters` is an immutable aggregate of the five narrow ports. Target modules register it at their composition root. Shared services use constructor injection and never resolve the Laravel container internally.
3. Missing App resource registration fails closed with `resource_unavailable`; there is no default, fallback, legacy, or synthetic runtime adapter.
4. `acceptance.run` retains one version-2 request shape: the complete hierarchy tuple and `profile_id` stay mandatory. Optional `request_id` is an additional durable prerequisite link, not an alternate selector.
5. If the selected Variant has no prerequisites, omitted `request_id` produces an in-memory empty `PrerequisiteExecutionData`. Otherwise a matching ready request is required. A supplied request must match the complete tuple and profile or return `prerequisite_request_mismatch`.
6. `PrerequisiteExecutionData` is an internal, in-memory-only DTO containing validated non-sensitive inputs and App-owned secret references. It is passed only to account/fixture adapters and is never attached to `OperationResult`, logs, reports, Tests, Steps, or queue payloads.
7. Readiness proof is an explicit App-owned `TargetEnvironment` value. Only `testing` and `staging` may be ready. `production` and `unknown` always normalize to `unsafe_target` before account, fixture, execution, oracle, or cleanup calls.
8. Safe-environment unavailability returns `target_not_ready`. Missing/unusable account resources return `resource_unavailable`; fixture, oracle, and cleanup failures use `fixture_setup_failed`, `oracle_failed`, and `cleanup_failed` respectively.
9. `ResourceReference` contains only `type`, an opaque non-secret `id`, and a deterministic SHA-256 reference hash. IDs are limited to 128 characters from `[A-Za-z0-9._~-]`; URLs, schemes, separators, whitespace, personal identifiers, credentials, tokens, cookies, selectors, and payloads are forbidden.
10. Oracle evaluation runs after any completed execution outcome, including a normal failed outcome, but not after an exception-normalized or cancelled outcome. Oracle failure becomes primary only when execution had no primary failure.
11. Cleanup runs once in `finally` whenever account or fixture stages returned at least one safe reference, including partial setup failure, execution pass/fail/exception, oracle failure, and cancellation. Readiness rejection and reference-free account failure perform no cleanup.
12. A failed provisioning result must return every safe reference requiring cleanup. Creating an unreferenced partial resource violates the adapter contract and is a stop condition for a target implementation.
13. Cleanup is App-owned and idempotent by `lifecycle_id`. Repeating cleanup for the same context must return a stable safe result and must not recreate or double-delete resources.
14. Primary outcome wins over cleanup failure. If no primary failure exists, cleanup failure is the operation error. If both exist, the primary code remains authoritative and `cleanupErrorCode=cleanup_failed` remains visible in lifecycle data and structured log context.
15. Cooperative cancellation is checked before readiness and between every stage. Cancellation after resource acquisition skips remaining setup/execution/oracle and still performs cleanup. Pack 0013 will provide durable cancellation state and hard worker limits.

## 14. Multilingual / Translation Requirements

No visible text, translation, API message, or RTL/LTR change. Enum values, stages, and error codes are language-neutral. Persian CLI guidance remains Pack 0015 scope.

## 15. UI / Admin UI Requirements

No UI or Admin UI. Services return immutable semantic DTOs for later client adapters.

## 16. Operator Documentation Requirements

No guide changes in this Pack. Pack 0015 must document testing/staging readiness failures, prerequisite request linkage, App-owned account/resource setup, cleanup failure precedence, and safe operator action without exposing target-specific secrets or payloads.

## 17. Implementation Rules

### Prerequisite execution boundary

- `PrerequisiteService::executionData(...)` validates the current code-owned schema, expiry, ready state, complete hierarchy identity, and profile under a transaction/lock before reconstructing inputs.
- It returns `PrerequisiteExecutionData` with ordered shallow-copied `nonSensitiveInputs` and `secretReferences`. Raw secret literals remain impossible.
- No-requirement Variants may construct an empty in-memory execution DTO without a database request. A supplied durable request is always validated; it is never silently ignored.
- Expired, cancelled, awaiting-input, awaiting-approval, stale-schema, or mismatched requests do not reach readiness or target adapters.

### Registry and coordinator

- `TargetResourceRegistry` validates App keys, rejects duplicate registration, and returns one immutable adapter aggregate per App. It contains no Laravel container calls.
- `AppServiceProvider` binds the registry as a singleton and the coordinator as an autowired concrete service. No target adapter is bound at project root.
- The coordinator order is: cancellation check -> readiness -> account resolution -> fixture provisioning -> execution callback -> oracle -> cleanup in `finally`.
- Account and fixture references are merged deterministically by `(type,id)` without reordering or duplication. Each new context is immutable.
- Adapter-thrown exceptions are caught at the coordinator boundary and normalized to the stage code. Raw messages, chains, target/provider codes, and payloads are discarded.

### Operation integration

- `RunAcceptanceScenario` obtains prerequisite execution data, creates a context from the shared operation/correlation IDs and complete tuple, and delegates the lifecycle to the coordinator around the existing accepted run callback.
- `TargetExecutionOutcome` carries only `succeeded|failed|cancelled`, a nullable stable primary code, and retry/operator-action classification. It contains no Test model or raw exception.
- `TargetResourceLifecycleData` contains lifecycle ID, final stage/status, readiness result, ordered safe references, nullable oracle/cleanup results, primary resource/execution code, and separate cleanup code.
- Before a Test exists, `acceptance.run` may return `TargetResourceLifecycleData` as safe failure data. After execution creates a Test, `RunOperationData` contains the same lifecycle data alongside existing safe Test/identity fields.
- `OperationResult` and `AcceptanceOperationService` accept only these exact typed combinations, validate request/result identity, preserve client-neutral data, and derive overall success from both Test status and lifecycle status.

## 18. Architecture Constraints

- Shared TMS owns orchestration, safe DTO validation, App-keyed adapter selection, and primary-versus-cleanup precedence.
- Target Apps own readiness proof, secret-reference resolution, account acquisition, fixture behavior, oracle business rules, cleanup, and all target I/O.
- The registry is an explicit plugin boundary, not a backdoor: adapters implement narrow ports and may not bypass prerequisite validation, return raw target state, write shared operation models, or resolve the Laravel container.
- The complete version-2 tuple remains explicit end to end. No default/legacy Component, Suite, Scenario, Variant, provider, or request projection is introduced.
- No client JSON, HTTP response, console output, Prompts call, translated message, target-specific rule, or Core executor behavior belongs in lifecycle DTOs/services.

## 19. Validation Rules

Using constructor-injected fakes, prove exact call order, no adapter construction/call before request validation, no setup on production/unknown/not-ready targets, partial-reference cleanup, execution pass/fail/exception, oracle pass/fail, cancellation at every boundary, cleanup timeout/failure, idempotent cleanup identity, and primary-versus-cleanup precedence.

Prove ready prerequisite reconstruction, no-requirement in-memory data, request/tuple/profile mismatch, not-ready prerequisite states, expiry/schema drift, and absence of prerequisite values/references from semantic operation output and logs.

All DTO constructors reject invalid versions, status/code combinations, duplicate references, over-limit fields, invalid UTF-8, unsafe reference syntax, and inconsistent success/failure classifications.

## 20. Security Rules

- Raw credentials, passwords, cookies, sessions, tokens, Authorization values, secret references, target URLs, selectors, personal identifiers, fixture payloads, target/provider responses, raw exceptions, and external error text never cross lifecycle result/log/report/Test/Step boundaries.
- The existing prerequisite input table remains the only allowed persistence for validated App-owned secret references. This Pack creates no second persistence or snapshot.
- Account/fixture adapters receive prerequisite values in memory only. Readiness, oracle, and cleanup ports receive only safe context and references.
- Resource hashes may be logged only after `ResourceReference` validates the underlying ID as non-secret. Raw IDs are never logged.
- Tests use obvious inert sentinels and assert their absence from lifecycle/operation DTOs, exception messages, captured logs, Test/Step records, and generated documentation.

## 21. Error Handling / Logging / Traceability Requirements

Add these stable codes to the shared result allowlist and classification map:

| Code | Result/classification | Meaning |
|---|---|---|
| `prerequisite_request_mismatch` | rejected; non-retryable; permanent; no admin action | supplied request tuple/profile differs from the run request |
| `unsafe_target` | rejected; non-retryable; permanent; admin action required | target declares production or unknown environment |
| `target_not_ready` | rejected; retryable; non-permanent; no admin action | explicit testing/staging target is temporarily not ready |
| `resource_unavailable` | rejected; non-retryable; permanent; admin action required | App resource adapters or required account resource are unavailable |
| `fixture_setup_failed` | failed; retryable; non-permanent; admin action required | fixture provisioning did not complete safely |
| `oracle_failed` | failed; non-retryable; permanent for this execution; no admin action | target-owned post-execution oracle failed |
| `cleanup_failed` | failed when sole failure; retryable; non-permanent; admin action required | cleanup failed, timed out cooperatively, or left references |

DTO constructor/registry programming errors use stable internal messages ending in `_invalid` or `_duplicate`; they are not client error contracts unless normalized through an operation.

The existing single `tms.acceptance.operation.failed` or `tms.acceptance.operation.completed` event remains the operation boundary log. Add only this bounded lifecycle context when available: `lifecycle_id`, `prerequisite_request_id`, `resource_stage`, `resource_status`, `primary_error_code`, `cleanup_error_code`, and an ordered list of `{type, reference_hash}`. Existing correlation/operation/Test/hierarchy context remains. Nulls are omitted. Raw IDs, input keys/values, secret references, target/provider fields, and raw exception class/message are excluded.

`operation_id` is the lifecycle ID for the current synchronous path. `correlation_id` and the complete tuple propagate through request, coordinator, result, and operation log. Batch/item/attempt identifiers do not yet exist and are explicitly deferred to Pack 0013.

## 22. Data Model / Migration / Relationship Requirements

No table, migration, model, relationship, index, snapshot, backfill, retention, delete policy, or rollback command is introduced. Existing prerequisite rows are read under their accepted Pack 0011 contract. Resource state remains target-owned and in memory through safe references.

## 23. Commenting Requirements

Follow `docs/ai/rules/COMMENTING-RULES.md`. Explain only the non-obvious opaque-reference boundary, internal prerequisite-data restriction, cleanup-in-finally invariant, cooperative timeout limitation, cancellation checkpoints, and primary-versus-cleanup precedence. Do not copy Pack prose or add unrelated comments.

## 24. Testing Requirements

- `TargetResourceCoordinatorTest` directly constructs the coordinator/registry with fakes and covers success, every stage failure, adapter exception normalization, partial resources, execution failure/exception, oracle precedence, cancellation checkpoints, timeout/failure cleanup, call order, and duplicate cleanup identity.
- `TargetResourceContractSafetyTest` covers DTO/enum/registry invariants, defensive array copying, reference syntax/hash/deduplication, invalid status/code combinations, and secret/personal/URL sentinel rejection.
- `PrerequisiteServiceTest` and `AcceptancePrerequisiteWorkflowTest` cover internal execution-data reconstruction, empty-schema behavior, full identity/profile matching, expiry/schema/approval state, and no output/log leakage.
- Operation and command regressions register explicit fake target adapters and prove early lifecycle failure data, successful Run lifecycle data, primary/cleanup classification, Test persistence behavior, and unchanged version-2 client rendering outside the additional semantic field consumed only by later Pack 0015 work.
- No real target, browser, network, queue, worker, external database, secret resolver, command prompt, or target module is used.

## 25. Acceptance Checklist

- [ ] target owners can register five narrow adapters per App without changing shared coordinator code;
- [ ] complete tuple/profile and any durable prerequisite request are validated before target calls;
- [ ] production/unknown targets stop before account/fixture/execution/oracle/cleanup;
- [ ] only safe references cross shared lifecycle/result/log boundaries;
- [ ] cleanup runs deterministically after every reference-bearing path and is idempotent by lifecycle ID;
- [ ] primary failure is never hidden by cleanup failure;
- [ ] lifecycle DTOs remain immutable, typed, client-neutral, and safe;
- [ ] no deleted secret/fixture framework, target resource database, or fallback provider is recreated;
- [ ] existing hierarchy, prerequisite, operation, and command regressions remain green.

## 26. Tests to Add

| Exact test file | Main behavior |
|---|---|
| `tests/Unit/TargetResourceCoordinatorTest.php` | lifecycle ordering, stage outcomes, cancellation, partial cleanup, timeout, exceptions, precedence, App-keyed adapter selection |
| `tests/Unit/TargetResourceContractSafetyTest.php` | immutable DTO/enum/registry/reference invariants, defensive copying, hash/deduplication, forbidden sentinel rejection |

Edit only the exact regression files in section 9. Do not refactor the test framework or move existing tests.

## 27. Tests to Run

Only after separate execution approval. Run through the accepted absent-dotenv Laravel entrypoint with process-local `APP_ENV=testing`, `DB_CONNECTION=sqlite`, and `DB_DATABASE=:memory:`:

```text
test --compact tests/Unit/TargetResourceCoordinatorTest.php tests/Unit/TargetResourceContractSafetyTest.php
test --compact tests/Unit/PrerequisiteServiceTest.php tests/Feature/AcceptancePrerequisiteWorkflowTest.php
test --compact tests/Unit/AcceptanceOperationServiceTest.php tests/Unit/AcceptanceOperationRegistryTest.php
test --compact tests/Feature/AcceptanceOperationCommandContractTest.php tests/Feature/AcceptanceRunCommandTest.php tests/Feature/AcceptanceRunPersistenceTest.php
test --compact tests/Unit/AcceptanceHierarchyTest.php tests/Unit/AcceptanceCatalogTest.php tests/Unit/AcceptancePlannerTest.php tests/Unit/AcceptanceVariantDispatcherTest.php
list --format=json
```

Guarded form for each group:

```powershell
php -r 'define("LARAVEL_START", microtime(true)); require "vendor/autoload.php"; $pack12App = require "bootstrap/app.php"; $pack12App->loadEnvironmentFrom("__tms_pack_0012_no_dotenv__"); exit($pack12App->handleCommand(new \Symfony\Component\Console\Input\ArgvInput));' -- <exact group above>
```

Also run PHP lint on every created/edited PHP file, scoped `vendor/bin/pint --test` on those files, `git diff --check`, changed-path allowlist/reference checks, all-34-section document validation, and static searches for forbidden Core/container lookup/Prompts/HTTP/queue/raw-sentinel behavior. Do not run migrations, rollback, seed, wipe, queue, browser, target, or external-network commands.

## 28. Expected Output

- five target-owned ports, typed lifecycle DTOs, adapter aggregate, registry, and coordinator;
- internal prerequisite execution-data boundary and validated `request_id` linkage;
- resource-aware `acceptance.run` semantic results without client presentation work;
- fake-only unit/integration/regression evidence;
- executed Run Report and Pack/Run index updates after implementation;
- explicit Pack 0013 cancellation/hard-timeout and Pack 0015 operator-guide follow-ups.

## 29. Operator Execution Checklist

### Before AI Execution

- Approve the independent App-keyed resource registry and no changes to the accepted hierarchy provider/scaffolding.
- Approve the five port signatures, internal prerequisite execution-data boundary, optional matching `request_id`, and safe `ResourceReference` syntax.
- Approve lifecycle order, cancellation checkpoints, oracle policy, partial-resource rule, and primary-versus-cleanup precedence in sections 13 and 17.
- Approve the fixed cooperative 30,000 ms cleanup budget and deferral of durable cancellation/hard timeout to Pack 0013.
- Approve stable codes/classifications, operation log allowlist, fake-only validation, and exact file scope in sections 8–9.

### After AI Execution

- Inspect one success trace and each readiness/account/fixture/execution/oracle/cleanup failure trace for exact ordering.
- Demonstrate cancellation after fixture setup and confirm execution/oracle are skipped while cleanup runs once.
- Demonstrate primary execution failure plus cleanup failure and confirm both remain visible without precedence inversion.
- Inspect lifecycle/operation DTOs, logs, and Test/Step records for secret/reference/URL/personal sentinels and confirm absence.
- Confirm existing version-2 hierarchy/prerequisite/command behavior and Core source remain unchanged.

## 30. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md` and the documentation-maintenance section required by `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`. Report exact ports/DTOs, prerequisite linkage, lifecycle/precedence behavior, cooperative timeout limitation, errors/logs/traces, security scans, commands/results, changed paths, and deferred target/Core/async/client work.

## 31. Review Checklist

Review target isolation, App-keyed DI, complete tuple/request matching, readiness proof, opaque-reference safety, partial cleanup, idempotency identity, cancellation checkpoints, cooperative timeout limitation, oracle policy, primary/cleanup precedence, typed result safety, log allowlist, fake strength, client neutrality, and absence of recreated cancelled infrastructure.

## 32. Rollback / Safety Notes

Source-only rollback. No migration, target resource, queue, or external cleanup should be required because implementation validation uses fakes and in-memory prerequisite persistence. If any real target call or resource is observed, stop immediately and report a scope/safety violation rather than attempting cleanup through this Pack.

## 33. Stop Conditions

Stop for:

- missing/revoked acceptance of Packs 0009–0011 or a changed accepted operation/hierarchy/prerequisite contract;
- operator alteration or rejection of a normalized choice in sections 13, 17, 21, or checklist 29;
- any need to modify Core, target modules, hierarchy/provider scaffolding, commands/presentation, API/UI/MCP, queue/jobs, dependencies, config, environment, migrations, or production-like data;
- any need to persist resource state or secret references outside the accepted prerequisite rows;
- any raw secret, target payload, personal identifier, unsafe reference, or raw adapter error crossing a shared boundary;
- an adapter that may create a partial resource without returning a safe cleanup reference;
- a required implementation path outside sections 8–9 other than mandatory governance maintenance;
- real target/browser/network/worker access, destructive command, canonical/source conflict, or missing separate execution approval.

No commit is authorized by this Pack.

## 34. Open Questions

No unresolved design question remains inside the normalized contract. Concrete DK/ND adapter behavior, hard cleanup timeout/process termination, durable cancel/recovery, batch/item/attempt traces, Core executor integration, and CLI/API/Web/MCP presentation remain explicitly deferred to their later approved owner Packs.
