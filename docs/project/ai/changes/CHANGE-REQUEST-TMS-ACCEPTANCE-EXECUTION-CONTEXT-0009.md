# CHANGE-REQUEST-TMS-ACCEPTANCE-EXECUTION-CONTEXT-0009 — Add bounded Browser Acceptance execution context

## 1. Title

Add per-scenario metadata, exact execution identity, and safe target Browser runtime handoff to the shared Acceptance contract.

## 2. Status

Implemented and accepted — Pack 0019 validation passed; operator accepted Run 001 and authorized commit on 2026-10-11.

## 3. Related Release / Phase

Project-owned enabling work between accepted DK stage 13 and DK stage 14 of the Decision-0015 roadmap. It does not add or reorder a roadmap stage.

## 4. Decision Source

Operator instruction on 2026-10-11, accepted project Decision 0015, and accepted `DK-DECISION-0002`.

## 5. Proposed Change

Establish one clean shared contract that:

1. generates each `ScenarioDescriptor` with metadata selected by its exact scenario key;
2. migrates existing generated component descriptors from the shared `metadata()` call to replay-safe `metadataFor('<scenario-key>')` calls;
3. propagates the exact immutable `AcceptanceExecutionIdentity` into `TestContext`;
4. resolves a target-owned, per-execution Browser runtime only after account and fixture provisioning has produced the final `TargetContext`;
5. passes that runtime explicitly through the run service, executor request, Browser executor, runner, and test context;
6. exposes only bounded `prepare()` and semantic surface-opening behavior to Browser execution, without exposing target URLs, sessions, credentials, raw resource references, or arbitrary option arrays;
7. fails closed when the exact App has no registered runtime resolver;
8. keeps API/HTTP execution, target-specific behavior, Fake behavior, configuration, queues, persistence schemas, and product UI outside this change.

The target Browser runtime is a Core-owned port because `TestContext` consumes it. The project layer owns the resolver and keyed registry because final `TargetContext` exists there. Target owners register adapters later at their composition root.

## 6. Reason for Change

The first bounded DK Browser Acceptance group cannot execute safely with the current shared contract:

- generated descriptors share one metadata result, so one scenario cannot become executable without affecting its siblings;
- `AcceptanceExecutionIdentity`, including the exact variant key, is not available to Scenario Steps;
- final target lifecycle references are available to the execution closure, but are currently discarded before Browser execution;
- passing raw target context, URLs, sessions, or an arbitrary data array into Scenario code would weaken the existing safety boundary;
- inserting a new generated descriptor line without replacing the legacy line would break replay idempotency.

## 7. Scope of Impact

Affected areas:

- shared target-module generator, component stub, and structural validator;
- one mechanical migration of the existing DK generated component;
- project target-runtime resolver registry and Laravel composition-root registration;
- project Acceptance execution orchestration and run service;
- Core Browser executor request, runner, and test context;
- focused project/Core tests and directly affected regression fixtures;
- project and DK lifecycle documentation.

Not affected:

- API/HTTP executor behavior or request/response contracts;
- ND Fake contracts or implementations;
- target product source, routes, views, translations, or permissions;
- database schema, migrations, models, queues, workers, callbacks, schedulers, or external integrations;
- configuration or environment variables;
- CLI JSON shape, persisted Test/Step schema, or evidence retention policy;
- real, staging, production, remote, or shared targets.

## 8. Affected Files

The exact implementation boundary is owned by:

- `docs/project/ai/packs/AI-PACK-TMS-ACCEPTANCE-EXECUTION-CONTEXT-0019.md`.

The Pack includes the shared project/Core contracts, the deterministic DK generated-source migration, affected tests, and required lifecycle indexes. DK-specific Scenario/runtime implementation remains exclusively in DK Pack 0004.

This Change Request consumes only the per-scenario metadata/replay-prevention slice of `CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007`; descriptor-resolution, large-file formatting policy, bounded PCRE execution, and scale hardening remain open there.

## 9. Backward Compatibility

External operator and persisted contracts remain compatible. Internal PHP method/constructor signatures are intentionally changed through one clean cutover: every in-repository caller and generated component is migrated in Pack 0019. No optional legacy runtime, fallback resolver, dual descriptor shape, or global compatibility state is retained.

## 10. Risk Level

High. The change crosses project/Core boundaries, modifies the Browser execution path, and migrates a large generated component. Risk is bounded by explicit DI, fail-closed resolution, no external target access, deterministic replay tests, focused regressions, and the complete PHPUnit suite.

## 11. Migration or Remediation Needed

No remediation Pack is required. The planned clean migration is part of standard project Pack 0019. If execution finds an accepted-contract defect outside that exact scope, stop and open a separate remediation.

## 12. Required Human Approval

Yes. The operator approved this Change Request, proposed design, Pack 0019 execution, post-execution review, final result, accepted Run 001, and separately authorized commit on 2026-10-11.

## 13. Acceptance Criteria

- [x] generated descriptor metadata is selected by the exact scenario key and defaults fail closed to `NOT_IMPLEMENTED`/`METADATA_ONLY`;
- [x] existing DK descriptors are migrated once, and selected/full import replay produces no duplicate or legacy metadata call;
- [x] validator/preflight rejects a descriptor whose `metadataFor()` key does not match its descriptor scenario key;
- [x] exact immutable App/Component/Suite/Scenario/Variant identity reaches `TestContext` without reparsing or global state;
- [x] final target context is resolved after account and fixture provisioning and before Browser startup;
- [x] an exact App-keyed runtime resolver is required; missing/unsafe resolution fails as `resource_unavailable` before Browser creation;
- [x] runtime preparation occurs before Page creation and Scenario code opens only a semantic surface key;
- [x] runtime objects and access-bearing values cannot be serialized, persisted, logged, or returned;
- [x] cleanup still runs after resolver, prepare, Browser, Scenario, or oracle failure whenever resources were acquired;
- [x] existing full-tuple logs/persistence remain unchanged and contain no raw target values;
- [x] API/Fake/config/database/queue/UI/external-network scope remains unchanged;
- [x] PHP syntax, scoped formatting, focused tests, affected browser smoke, full PHPUnit suite, safe scans, and diff checks pass.

## 14. Rollback Plan

Revert only Pack-0019-owned source/test edits, restore the generated DK descriptor/method shape through the inverse exact migration, and synchronize the directly related Change/Pack/DK lifecycle records. Do not use broad reset/restore/clean commands. No data, schema, config, environment, target, or dependency rollback is required.

## 15. Final Decision

Implemented and accepted through project Pack 0019 and accepted Run 001. The operator authorized commit on 2026-10-11.

## 16. Required remediation

None currently. A separate remediation is required only if Pack execution reveals a conflict with accepted source/contracts or cannot complete the clean migration safely.

## 17. Notes

- Existing stable result codes are reused: `acceptance_configuration_invalid`, `resource_unavailable`, `browser_execution_failed`, `acceptance_step_failed`, and `cleanup_failed`.
- Registry programming errors remain internal and reuse the existing target-resource registry validation/duplicate diagnostics; they are not new operator result codes.
- No new Decision is required because this change implements Decision 0015's explicit full-tuple contract and `DK-DECISION-0002`'s already accepted prerequisite.
