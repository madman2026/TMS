# AI-PACK-TMS-TARGET-RUNTIME-OBSERVATION-0020 — Bounded target runtime input and semantic Browser observation

Status: accepted — implementation and validation passed; operator accepted Run 001 and authorized commit 2026-10-11

Generated: 2026-10-11

Owner: TMS project with explicit Core contract scope

Decision: `TMS-DECISION-0021`

Change Request: `CHANGE-REQUEST-TMS-TARGET-RUNTIME-OBSERVATION-0010`

Execution / Acceptance / Run / Commit: implementation, approved validation, Run 001 reporting, operator acceptance, and commit authorization are complete as of 2026-10-11. No target or external network was used; the complete suite used only the already accepted process-local headless Core Browser smoke boundary.

## 1. Task ID

`AI-PACK-TMS-TARGET-RUNTIME-OBSERVATION-0020`

## 2. Task Title

Pass validated prerequisite handles to target Browser runtime resolution and add bounded target-semantic observations.

## 3. Goal

Provide the minimum shared contracts required for a target owner to resolve one per-execution Browser runtime from the final safe target context plus validated prerequisite handles, and to expose objective target-specific UI facts to Scenario code without exposing raw DOM, text, selectors, URLs, sessions, credentials, or arbitrary target data.

## 4. Context

Accepted Pack 0012 gives target account and fixture adapters validated `PrerequisiteExecutionData`; accepted Pack 0019 resolves a `BrowserTargetRuntime` only from the final `TargetContext`. DK Pack 0004 needs both contexts at runtime resolution, but raw values cannot be added to safe references or lifecycle data.

Accepted Core Browser Observability Pack 0001 intentionally returns only generic browser-native facts. The selected Operations Health group additionally requires target-owned computed/effective/freshness/reason/source facts. Core must not learn DK selectors or schemas, and Scenario code must not read raw target content.

This Pack changes only the shared handoff and adds an optional typed semantic observation boundary. It does not implement DK, connect to a target, resolve a real secret, launch a Browser, or make any Scenario executable.

## 5. Related Release / Phase

Project-owned prerequisite before DK Decision-0015 stage 14 Pack 0004. No roadmap stage is added or reordered.

## 6. Related Epic / Feature / Story

First bounded Notification Delivery Operations Health Browser Acceptance group — shared runtime/observation prerequisite only.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0009-operator-input-approval-and-sensitive-values.md`
- `docs/project/ai/decisions/TMS-DECISION-0011-extensible-executors-and-capabilities.md`
- `docs/project/ai/decisions/TMS-DECISION-0012-automated-e2e-coverage-and-traceability.md`
- `docs/project/ai/decisions/TMS-DECISION-0015-clean-acceptance-hierarchy-cutover.md`
- `docs/project/ai/decisions/TMS-DECISION-0017-temporary-local-synthetic-data-protection-deferral.md`
- `docs/project/ai/decisions/TMS-DECISION-0021-bounded-target-runtime-input-and-observation.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-RUNTIME-OBSERVATION-0010.md`
- `docs/project/ai/packs/AI-PACK-TMS-TARGET-RESOURCE-LIFECYCLE-0012.md`
- `docs/project/ai/packs/AI-PACK-TMS-ACCEPTANCE-EXECUTION-CONTEXT-0019.md`
- `Modules/Core/docs/ai/packs/AI-PACK-CORE-BROWSER-OBSERVABILITY-0001.md`
- `Modules/DK/docs/ai/decisions/DK-DECISION-0002-first-bounded-nd-browser-acceptance-group.md`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004.md`

## 8. Files to Create

Production/Core contracts:

- `Modules/Core/app/Contracts/BrowserTargetObserver.php`
- `Modules/Core/app/Data/BrowserTargetObservation.php`

Focused tests:

- `Modules/Core/tests/Unit/BrowserTargetObservationTest.php`
- `Modules/Core/tests/Unit/TestContextTargetObservationTest.php`

Lifecycle record, only after approved execution and reporting gate:

- `docs/project/ai/runs/AI-PACK-TMS-TARGET-RUNTIME-OBSERVATION-0020-run-001.md`

## 9. Files to Edit

Production/shared source:

- `app/Acceptance/Targets/Contracts/TargetBrowserRuntimeResolver.php`
- `app/Acceptance/Execution/AcceptanceExecutionService.php`
- `Modules/Core/app/Contracts/TestContext.php`

Exact resolver-signature and handoff regressions:

- `tests/Unit/TargetBrowserRuntimeRegistryTest.php`
- `tests/Feature/AcceptanceBrowserRuntimeHandoffTest.php`
- `tests/Feature/AcceptanceOperationCommandContractTest.php`
- `tests/Feature/AcceptancePrerequisiteWorkflowTest.php`
- `tests/Feature/AcceptanceRunCommandTest.php`

Lifecycle/documentation:

- `docs/project/ai/decisions/TMS-DECISION-0021-bounded-target-runtime-input-and-observation.md`
- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-RUNTIME-OBSERVATION-0010.md`
- `docs/project/ai/changes/CHANGES-INDEX.md`
- `docs/project/ai/packs/AI-PACK-TMS-TARGET-RUNTIME-OBSERVATION-0020.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md` only after an approved Run exists
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004.md` — dependency/blocker normalization only
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`

No DK implementation source is authorized by this Pack.

## 10. Files to Read / Reference

Required governance and owner routers:

- `AGENTS.md`
- `docs/ai/start/START-HERE.md`
- `docs/ai/start/CONTEXT-MAP.md`
- `docs/project/PROJECT-DOCS-INDEX.md`
- `docs/project/ai/TMS-AI-PROFILE.md`
- `Modules/Core/AGENTS.md`
- `Modules/Core/docs/ai/README.md`
- `Modules/Core/docs/ai/manifest.yaml`
- `Modules/Core/docs/ai/GOVERNANCE-PROFILE.md`
- project/Core/DK lifecycle indexes required by sections 7–9
- source references in section 7

Exact current source:

- `app/Acceptance/Prerequisites/Data/PrerequisiteExecutionData.php`
- `app/Acceptance/Targets/Data/TargetContext.php`
- `app/Acceptance/Targets/Data/ResourceReference.php`
- `app/Acceptance/Targets/Contracts/TargetBrowserRuntimeResolver.php`
- `app/Acceptance/Targets/TargetBrowserRuntimeRegistry.php`
- `app/Acceptance/Execution/AcceptanceExecutionService.php`
- `Modules/Core/app/Contracts/BrowserTargetRuntime.php`
- `Modules/Core/app/Contracts/BrowserProbe.php`
- `Modules/Core/app/Contracts/TestContext.php`
- `Modules/Core/app/Data/BrowserObservation.php`
- `Modules/Core/app/Exceptions/AcceptanceExecutionException.php`
- exact tests in sections 8–9 and nearby accepted test conventions

Required rules/templates:

- `docs/ai/rules/AI-PACK-GENERATION-RULES.md`
- `docs/ai/rules/SCOPE-CONTROL-RULES.md`
- `docs/ai/rules/AI-EXECUTION-RULES.md`
- `docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md`
- `docs/ai/rules/GIT-AND-COMMIT-RULES.md`
- `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`
- `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md`
- `docs/ai/rules/COMMENTING-RULES.md`
- `docs/ai/rules/REPORTING-RULES.md`
- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`
- `docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md`
- `docs/ai/templates/AI-PACK-TEMPLATE.md`

### Project / Framework Source Reference Rule

Use current PHP 8.4/Laravel 12 source, installed interfaces, accepted Core conventions, and nearby PHPUnit tests. Use Laravel Boost documentation search only if version-sensitive behavior cannot be resolved from current source. Do not load unrelated Packs/Runs/Reviews, raw external corpus, `.env`, target data, or broad vendor source.

## 11. Configuration / Settings Requirements

No configuration or settings changes required.

### Config File Requirements

No config file changes required.

### Environment Variable Requirements

No environment variables required. Do not read or edit `.env` or `.env.example`.

### Admin Settings Requirements

No Admin Settings changes required.

### External Integration / Secret Settings Requirements

No external-integration or secret settings are introduced. Existing `app-secret://<app>/<opaque-id>` handles may reach the target runtime resolver only inside `PrerequisiteExecutionData`. Pack 0020 must not resolve them or add a secret store/provider.

### Test / Development Setup Requirements

No target setup is required. New/focused Pack tests use inert fakes/mocks and no usable URL, credential, token, cookie, session, or external value. The final complete-suite regression may exercise already accepted local Core Browser smoke tests; that bounded local-system-Chrome execution requires explicit Pack-0020 pre-execution approval, a process-local `ACCEPTANCE_HEADLESS=true` override, synthetic local pages only, and no target/external network. Do not edit `phpunit.xml` for this Pack.

### Settings Validation

```text
Validation required:
- Config keys checked: Not applicable
- Env placeholders documented: Not applicable
- Admin settings created or documented: Not applicable
- Sensitive settings masked: Yes — inert handles/sentinels only; raw values omitted
- Tests/API test artifacts updated for setting-dependent behavior: API artifact not applicable; focused contract tests required
```

### Operator Setup Notes

No operator manual setup required for Pack 0020. DK Pack 0004 retains its own separate runtime-only target setup gate.

## 12. Do Not Change

- `BrowserTargetRuntime` prepare/open contract or `BrowserProbe` operations/schemas;
- target resource lifecycle order, safe references, cleanup, oracle, readiness, or prerequisite persistence;
- any DK implementation source, descriptor, scenario, selector, fixture, adapter, runtime, provider, or test;
- any API/HTTP/CLI response shape, database schema/model, queue/job/batch behavior, callback, external integration, configuration, environment file, `phpunit.xml`, dependency, lockfile, UI, translation, or target product source;
- existing error-code meaning, logging event, Test/Step persistence, evidence retention, screenshot/trace/HAR behavior;
- real/staging/production/remote/shared target or raw access value;
- accepted historical lifecycle documents except exact status/index maintenance authorized in section 9.

## 13. Clarification Questions Before Implementation

Before execution, the operator must approve the exact Pack file allowlist, clean resolver signature cutover, observation fact bounds, fake-only focused validation, reuse of existing error codes, and the bounded headless local Chrome regressions that may be reached by the complete suite. No target value or setup detail is required for this shared Pack.

## 14. Multilingual / Translation Requirements

```text
Multilingual / Translation Impact: No
RTL/LTR Impact: No
API Message Translation Impact: No
Admin UI Translation Impact: No
Owner-defined Default Content Translation Impact: No
```

No multilingual or translation changes required. Observation keys and string fact values are language-neutral technical tokens; translated/free text is forbidden.

## 15. UI / Admin UI Requirements

```text
UI Impact: No
Admin UI Impact: No
Public UI Impact: No
Settings UI Impact: No
Dashboard / Monitoring UI Impact: No
Table / Form Impact: No
Action / Button Impact: No
Navigation / Menu Impact: No
Permission-gated UI Impact: No
```

No UI changes required. This Pack defines an in-memory test-runtime contract only.

## 16. Operator Documentation Requirements

```text
Operator Documentation Impact: No
Operator Documentation Update Required: Not applicable
Guide File: docs/project/ai/guides/ACCEPTANCE-CLI.fa.md
```

No operator-facing behavior changed and no operator documentation update is required. If DK Pack 0004 later adds a setup/troubleshooting workflow, that owner Pack must update the guide.

## 17. Implementation Rules

1. Change `TargetBrowserRuntimeResolver::resolve(...)` to accept, in order, `TargetContext $context` and `PrerequisiteExecutionData $prerequisites`.
2. `AcceptanceExecutionService` passes the exact prerequisite instance already validated by `TargetResourceCoordinator`; do not clone, project, log, serialize, persist, or inspect its values in shared code.
3. Preserve App-keyed resolver lookup and the current `resource_unavailable` normalization for missing/throwing resolvers.
4. Create `BrowserTargetObserver` with one method accepting the current `PageInterface` plus a semantic observation key and returning `BrowserTargetObservation`.
5. Do not add `observe()` to `BrowserTargetRuntime`; observation is optional and capability-checked at the call site so existing runtimes remain valid.
6. `TestContext::observeTarget()` validates the requested key with the existing lowercase key grammar/64-byte bound, requires `targetRuntime instanceof BrowserTargetObserver`, invokes it for the same Page, and rejects a returned observation whose key differs from the request.
7. Missing capability, invalid key, invalid observation, or key mismatch must throw the existing fixed-safe `AcceptanceExecutionException::configurationInvalid()`; do not echo rejected values or retain previous raw target errors.
8. `BrowserTargetObservation` is readonly and defensively copies an associative fact map after validation.
9. Exact observation bounds: 1–16 facts; unique lowercase fact keys using `[a-z0-9][a-z0-9._-]*`, maximum 64 bytes; values only `bool|int|float|string|null`; strings are lowercase semantic tokens with the same grammar and maximum 64 bytes; integers/numbers are finite within `[-1_000_000_000, 1_000_000_000]`; nested arrays/objects/resources and invalid UTF-8 are rejected.
10. No `toArray()`, JSON, logging, persistence, capture, raw-detail, selector, URL, or debug export API is added by this Pack.
11. Update all exact first-party resolver implementations/test doubles in section 9 to the new signature; no optional argument, overload, legacy adapter, or fallback state.

## 18. Architecture Constraints

- Project owns validated prerequisite handoff and App-keyed resolver selection; Core owns the Scenario-facing Browser contracts; target owners own reference resolution and semantic mapping.
- Shared code sees secret-reference handles only, never the resolved value. Raw resolved access may exist only in a private per-execution target runtime supplied later by a target-owner Pack.
- `TargetContext`, `ResourceReference`, lifecycle DTOs, Test/Step records, logs, and reports remain safe and unchanged.
- Semantic observations are target-neutral transport values, not a shared domain schema. DK-specific fact names/allowed values belong in DK Pack 0004 tests/runtime.
- No service locator, static/global current execution, singleton-held access value, arbitrary option array, raw target context in Scenario code, or target-specific logic in Core.
- Preserve complete App/Component/Suite/Scenario/Variant/profile/prerequisite identity and fail closed on mismatch.

## 19. Validation Rules

- Prove resolver receives the same final context and same prerequisite object after account/fixture provisioning and before Browser execution.
- Prove missing/throwing resolvers still fail before `AcceptanceRunService::run()` and cleanup all acquired safe references.
- Prove `TestContext` passes its exact Page to the observer and never calls it for invalid/missing/mismatched capability.
- Prove every allowed fact type/bound, defensive copy, exact key match, and every rejected nested/raw/overbound value.
- Prove existing runtime prepare/open and BrowserProbe behavior are unchanged.
- Use inert secret-like sentinels and assert absence from results, errors, logs, model data, and printable lifecycle output.
- Run focused tests, affected resolver regressions, accepted Core runner/executor regressions, full PHPUnit suite, syntax, scoped formatting, safe searches, path allowlist, and diff checks.

## 20. Security Rules

- Follow Decisions 0009 and temporary 0017; no real or usable secret/access/target value may enter implementation, tests, docs, output, or logs.
- Do not dereference `app-secret://...` in shared code. Tests use non-resolving inert handles only.
- Do not return or persist raw DOM, text, labels, selectors, HTML, URL, session, cookie, storage, headers, credentials, personal identifiers, payloads, console/network data, screenshots, traces, or HAR.
- Observation string facts are language-neutral short tokens, never arbitrary display text or opaque target identifiers.
- No external target or network process is used by Pack validation. Only already accepted local Core Browser smoke may run, headless, against synthetic local pages after the explicit execution gate.

## 21. Error Handling / Logging / Traceability Requirements

### Error Handling Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: No
UI / Admin UI Error Impact: No
Internal Exception Impact: Yes
External Integration Error Impact: No
Callback / Inbound-event Error Impact: No
Logging Impact: No
Traceability Impact: Yes
Error Code Impact: No
Security / Sensitive Data Impact: Yes
```

### Error Scenarios

| Scenario | Existing code | Classification / behavior |
|---|---|---|
| missing/throwing target runtime resolver | `resource_unavailable` | existing permanent/admin-action lifecycle execution failure; cleanup preserved |
| invalid observation request/fact/key mismatch | `acceptance_configuration_invalid` | non-retryable configuration failure before unsafe data can reach Scenario assertions |
| target observer throws an already normalized Acceptance exception | existing exception code | existing Runner/Executor normalization; no raw message added |

No API/UI error contract, status transition, external-error mapping, or new code is introduced.

### Logging Requirements

No logging changes required. Existing operation/run logs must not add prerequisite inputs/references, observation keys/facts, target references, or raw exceptions.

### Traceability Requirements

The existing operation/correlation IDs and full tuple/profile remain authoritative. The exact prerequisite object is handed to the resolver within the already traced execution closure; no new identifier or persisted trace field is added.

### Sensitive Data and Masking Requirements

Secret references are internal opaque handles and are omitted from logs/results. Resolved values are outside this Pack and forbidden in shared state. Observation facts must be semantic and non-sensitive; raw values are omitted rather than masked.

### Error Handling Test Requirements

Test exact stable codes, no resolver/run call on invalid paths, cleanup after acquired references, fixed-safe exception messages, absent previous/raw value exposure, and no new log context.

### Required Follow-up Updates

- DK Pack 0004 must supply and test the target-owner resolution/observer implementation and exact fact allowlists after Pack 0020 acceptance.
- If a new shared secret provider or new error code is actually required, stop and open a separate decision/Change Request.

## 22. Data Model / Migration / Relationship Requirements

```text
Data / Migration Impact: No
New Tables Required: No
Existing Tables Modified: No
Foreign Keys Required: No
Model Relationships Required: No
Indexes / Unique Constraints Required: No
Data Backfill Required: No
Soft Delete / Retention Impact: No
Audit / History Impact: No
Rollback Impact: source-only
```

No data model, migration, relationship, snapshot, index, constraint, backfill, seed, retention, or persistence changes required. No snapshot fields required.

## 23. Commenting Requirements

Follow `docs/ai/rules/COMMENTING-RULES.md`. Comment only the non-obvious distinction between opaque handles and resolved values, optional observer capability, and semantic-fact safety bounds. Do not copy Pack prose.

## 24. Testing Requirements

```text
Test Quality Required: Yes
Behavioral Assertions Required: Yes
Contract Assertions Required: Yes
Database Assertions Required: only existing no-persistence/leakage regressions
Side-effect Assertions Required: Yes — absence of target/browser/network/log/persistence effects
Negative/Error Scenario Tests Required: Yes
Authorization/Permission Tests Required: No
Idempotency Tests Required: No
Queue/Event/Job Assertions Required: No new behavior; assert unchanged where existing regression covers it
External Integration Fake/Mock Required: No external fake; local interface mocks only
Security/Secret Masking Tests Required: Yes — omission
```

The tests must prove exact instance handoff, final-context ordering, optional capability behavior, all fact bounds, defensive copying, fixed-safe errors, cleanup preservation, and no sensitive/raw output. Use descriptive PHPUnit `test_...` names, Arrange/Act/Assert structure, keyed providers for validation matrices, exact assertions, and independent setup.

## 25. Acceptance Checklist

- [x] exact validated prerequisite instance reaches resolver with final context;
- [x] shared source never resolves or exposes a secret/access value;
- [x] optional observer works only through same-page `TestContext` access;
- [x] bounded observation rejects nested/raw/arbitrary/overbound content and mismatched keys;
- [x] existing runtime/probe/lifecycle/persistence/log contracts remain unchanged;
- [x] every first-party resolver implementation/test double uses the clean new signature;
- [x] focused and affected regressions plus full suite pass;
- [x] no target/Browser/network/config/database/dependency/UI/DK implementation scope is added;
- [x] lifecycle indexes/report are synchronized only after approved execution;
- [x] no commit occurs without separate authorization.

## 26. Tests to Add

### `BrowserTargetObservationTest`

Prove all allowed scalar/token facts, exact bounds, deterministic defensive copy, 1–16 fact count, and rejection of lists/nested values/objects/resources/arbitrary text/invalid tokens/NaN/Infinity/out-of-range values without echoing sentinels.

### `TestContextTargetObservationTest`

Prove the exact Page and requested key reach an implementing observer; non-observer runtime, invalid request key, thrown/malformed observation, and returned-key mismatch fail with fixed-safe `acceptance_configuration_invalid`; existing prepare/open/close behavior remains intact.

Edit `AcceptanceBrowserRuntimeHandoffTest` to prove resolver receives the identical prerequisite instance after final references and before run, and that missing/throwing resolution still cleans up. Edit the other listed resolver tests only for signature compatibility and directly affected assertions.

## 27. Tests to Run

Only after separate execution approval, through the accepted absent-dotenv test entrypoint with process-local testing/SQLite-memory settings and `ACCEPTANCE_HEADLESS=true`:

```text
test --compact Modules/Core/tests/Unit/BrowserTargetObservationTest.php Modules/Core/tests/Unit/TestContextTargetObservationTest.php
test --compact tests/Unit/TargetBrowserRuntimeRegistryTest.php tests/Feature/AcceptanceBrowserRuntimeHandoffTest.php
test --compact tests/Feature/AcceptanceOperationCommandContractTest.php tests/Feature/AcceptancePrerequisiteWorkflowTest.php tests/Feature/AcceptanceRunCommandTest.php
test --compact Modules/Core/tests/Unit/TestContextBrowserProbeTest.php Modules/Core/tests/Unit/AcceptanceRunnerTest.php Modules/Core/tests/Unit/BrowserAcceptanceExecutorTest.php
test --compact
```

Guard each group with the accepted no-dotenv entrypoint, substituting the exact `test --compact ...` arguments above:

```powershell
php -r 'putenv("ACCEPTANCE_HEADLESS=true"); define("LARAVEL_START", microtime(true)); require "vendor/autoload.php"; $pack20App = require "bootstrap/app.php"; $pack20App->loadEnvironmentFrom("__tms_pack_0020_no_dotenv__"); exit($pack20App->handleCommand(new \Symfony\Component\Console\Input\ArgvInput));' -- test --compact <exact paths>
```

Do not edit `.env` or `phpunit.xml`. Classify any existing Core smoke reached by the complete suite as local headless Browser evidence, not target validation.

Also run `php -l` on every created/edited PHP file, scoped `vendor/bin/pint --test` on those files, `git diff --check`, changed-path allowlist checks, and static scans for forbidden resolver dereferencing, raw output/persistence/logging, DOM/text/capture APIs, target/network calls, and sensitive sentinels. Do not launch a server, worker, target, migration, seed, cache/config command, Composer/npm install, or external network call. No new Browser test is added by this Pack.

## 28. Expected Output

- one clean resolver signature carrying final context plus validated prerequisite handles;
- one optional Core target-observer port and one bounded immutable semantic observation value;
- one `TestContext` observation accessor with fail-closed validation;
- focused fake-only contract/security tests and updated exact regressions;
- no target implementation, secret resolution, Browser run, persistence/log/API/UI/config/dependency change;
- synchronized Decision/Change/Pack/DK lifecycle records and a Run only after approved execution.

## 29. Operator Execution Checklist

### Before AI Execution

- Approve the exact fact bounds and optional-observer shape in sections 17–18.
- Approve the clean resolver signature migration and exact file allowlist in sections 8–9.
- Confirm focused validation remains fake-only and approve only the existing headless local Core Browser smoke that may run in the complete suite; no target/network/value access.

### After AI Execution

- Inspect one resolver handoff and confirm only opaque handles, not resolved values, cross the shared call.
- Inspect accepted/rejected observations and confirm no raw target content or new persisted/logged data exists.
- Confirm every existing runtime without the optional observer retains prepare/open behavior.

## 30. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md` and include the Documentation Maintenance section from `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`. Report exact signatures/bounds, files, commands/results, regressions/full-suite results, safe scans, preserved gates, deviations, and DK follow-up without any access-bearing value.

## 31. Review Checklist

Review owner boundaries, exact instance/identity handoff, optional capability behavior, fact bounds, fixed-safe errors, no raw content/access, no persistence/log expansion, cleanup preservation, complete resolver migration, test strength, scope/path allowlist, and lifecycle synchronization.

## 32. Rollback / Safety Notes

Source-only targeted rollback. Remove the two new Core types/tests/accessor, restore the previous resolver signature at every exact implementation/call site, and synchronize related docs/indexes. No data, environment, secret, target, Browser, queue, cache, dependency, or external cleanup is expected. If any such side effect appears, stop and report a scope violation.

## 33. Stop Conditions

Stop for:

- missing/revoked acceptance of Packs 0012/0019 or conflict with current source;
- any need to resolve a handle in shared code or add secret/config/environment storage;
- any need to put raw values into safe references, contexts, DTOs, models, logs, reports, exceptions, or docs;
- any need to modify `BrowserTargetRuntime`, `BrowserProbe`, DK implementation, API/UI/database/queue/dependency/target source, or a file outside sections 8–9;
- an observation requirement that cannot fit bounded semantic scalar/token facts without raw text/DOM/data;
- a new stable error code/log event/persistence field/compatibility shim;
- unapproved Browser execution, any target/network execution, real value access, destructive command, unrelated working-tree overlap, or missing execution approval.

No commit is authorized by this Pack.

## 34. Open Questions

No shared design, implementation, or acceptance question remains for Pack 0020. The operator approved execution, accepted Run 001, and authorized commit on 2026-10-11. DK-local secret-reference resolution, concrete access source, selector/semantic mapping, fixture/oracle/cleanup behavior, actual target procedure, and target Browser validation remain deliberately unresolved here and blocking for DK Pack 0004 until that Pack is renormalized and separately approved for execution.
