# AI-PACK-DK-NOTIFICATION-DELIVERY-CONTRACT-FAKE-0002 — Notification Delivery contract and deterministic fake

Status: `accepted — operator accepted 2026-10-10 with the documented managed-App Pint/validator compatibility exception; behavioral validation passed; commit authorized`

Generated: `2026-10-10`

Owner: DK

Decision: accepted project Decisions 0005, 0007, 0009, 0011, 0012, and 0015; accepted `DK-DECISION-0001`.

Depends On: accepted DK Pack 0001 and Run 001; accepted project Pack 0010 Component SDK, Pack 0012 target-resource contracts, Core Pack 0003 capability runtime, and project Packs 0013–0016 with their accepted Runs.

Execution / Acceptance / Commit: the Pack was initially generated without execution authority. The operator subsequently accepted it, authorized execution, accepted the completed result with the documented compatibility exception, and separately authorized commit on 2026-10-10. No target access was authorized or used.

## 1. Task ID

`AI-PACK-DK-NOTIFICATION-DELIVERY-CONTRACT-FAKE-0002`

## 2. Task Title

Create the ND Component contract and deterministic no-real-SMS fake.

## 3. Goal

Create the first Notification Delivery (ND) Component inside DK and establish a deterministic, in-process simulation boundary that later DK scenario Packs can use without any real provider, SMS, notification, callback endpoint, queue worker, clock, database, browser, HTTP request, or external network operation.

The Pack introduces five narrow DK-local ports, immutable safe DTOs/enums, and one shared in-memory fake implementing all five roles:

```text
NotificationProvider
NotificationCallbackSimulator
NotificationWorkerOracle
NotificationTimeOracle
NotificationAuditOracle
```

It registers the empty `notification-delivery` Component and the fake composition boundary, but creates no Suite, Scenario, Variant, Step, source-case mapping, target-resource replacement, or executable notification behavior.

## 4. Context

Decision 0015 assigns stage 12 to the owner-local ND contract/fake after the stage-11 DK target foundation. DK Pack 0001 and Run 001 are accepted and explicitly leave this work as the next Pack. Project Pack `0017` remains reserved for stage 16 and is not renumbered or replaced.

`DK-DECISION-0001` requires a fake Provider, simulated Callback, worker/time/audit oracles, and an explicit no-real-SMS boundary. Decision 0007 keeps ND as a Component inside the DK Nwidart module. Decision 0012 requires every future executable scenario to be automated and traceable, but corpus import belongs to stage 13 and is intentionally excluded here.

Current source contains an empty DK App and no ND directory. The existing Component generator can create the Component shell and managed App registration. The accepted DK target adapter remains fail-closed and is not replaced by this Pack.

The dependency-injection design uses one concrete in-memory fake aliased to five role-specific contracts at `DKServiceProvider`, so all roles observe one deterministic state while domain/component code remains container-agnostic. No service locator is permitted.

## 5. Related Release / Phase

Decision 0015, stage 12 of the sixteen-stage roadmap.

## 6. Related Epic / Feature / Story

DK Notification Delivery simulation foundation: Component identity, provider boundary, simulated callback, deterministic worker/time/audit observation, and structural no-real-SMS proof.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`
- `docs/project/ai/decisions/TMS-DECISION-0007-nwidart-target-modules-and-component-hierarchy.md`
- `docs/project/ai/decisions/TMS-DECISION-0009-operator-input-approval-and-sensitive-values.md`
- `docs/project/ai/decisions/TMS-DECISION-0011-extensible-executors-and-capabilities.md`
- `docs/project/ai/decisions/TMS-DECISION-0012-automated-e2e-coverage-and-traceability.md`
- `docs/project/ai/decisions/TMS-DECISION-0015-clean-acceptance-hierarchy-cutover.md`
- `Modules/DK/docs/ai/decisions/DK-DECISION-0001-isolated-target-foundation-boundary.md`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-TARGET-FOUNDATION-0001.md`
- `Modules/DK/docs/ai/runs/AI-PACK-DK-TARGET-FOUNDATION-0001-run-001.md`
- current source paths listed in sections 8–10

## 8. Files to Create

- `Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Contracts/NotificationProvider.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Contracts/NotificationCallbackSimulator.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Contracts/NotificationWorkerOracle.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Contracts/NotificationTimeOracle.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Contracts/NotificationAuditOracle.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Data/NotificationDeliveryRequest.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Data/NotificationSubmission.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Data/NotificationDeliveryEvent.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Enums/NotificationCallbackOutcome.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Enums/NotificationWorkerState.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Enums/NotificationDeliveryEventType.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Exceptions/NotificationDeliverySimulationException.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Fakes/InMemoryNotificationDeliveryFake.php`
- `Modules/DK/tests/Unit/Acceptance/Components/NotificationDelivery/Data/NotificationDeliveryRequestTest.php`
- `Modules/DK/tests/Unit/Acceptance/Components/NotificationDelivery/Fakes/InMemoryNotificationDeliveryFakeTest.php`
- `Modules/DK/tests/Feature/NotificationDeliveryComponentIntegrationTest.php`

After separately approved execution, reporting may create exactly:

- `Modules/DK/docs/ai/runs/AI-PACK-DK-NOTIFICATION-DELIVERY-CONTRACT-FAKE-0002-run-001.md`

No other source, test, Decision, Canonical, Guide, Run, or Review file may be created by Pack execution.

## 9. Files to Edit

- `Modules/DK/app/Acceptance/DKAcceptanceApp.php`
- `Modules/DK/app/Providers/DKServiceProvider.php`
- `Modules/DK/tests/Unit/DKAcceptanceAppTest.php`
- `Modules/DK/tests/Feature/DKTargetFoundationIntegrationTest.php`
- `tests/Feature/DKModuleBootstrapTest.php` — exact DK catalog assertions only; no root runtime behavior change
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-CONTRACT-FAKE-0002.md` — lifecycle/status synchronization only
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`
- `Modules/DK/docs/ai/runs/RUNS-INDEX.md` — only if the Run Report is created after approved execution

The root Feature test is an explicit, narrow cross-owner integration assertion already dedicated to DK bootstrap/catalog behavior. No root application source or shared contract may be edited. If any additional path outside `Modules/DK/` is required, stop and route it to the owning layer.

## 10. Files to Read / Reference

- `AGENTS.md`
- `docs/ai/start/START-HERE.md`
- `docs/ai/start/CONTEXT-MAP.md`
- `docs/project/PROJECT-DOCS-INDEX.md`
- `docs/project/ai/TMS-AI-PROFILE.md`
- `docs/modules/MODULES-DOCS-INDEX.md`
- `Modules/DK/AGENTS.md`
- `Modules/DK/docs/ai/README.md`
- `Modules/DK/docs/ai/manifest.yaml`
- `Modules/DK/docs/ai/GOVERNANCE-PROFILE.md`
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`
- exact source references in section 7
- `app/Contracts/AcceptanceComponentProvider.php`
- `app/Contracts/AcceptanceCoverageProvider.php`
- `app/Acceptance/Modules/NwidartTargetModuleCreator.php`
- `app/Acceptance/Modules/TargetModuleValidator.php`
- `app/Data/ComponentDescriptor.php`
- `Modules/Core/app/Contracts/AcceptanceScenario.php`
- `Modules/Core/app/Data/AcceptanceExecutionIdentity.php`
- `Modules/Core/app/Data/ExecutorTraceContext.php`
- `Modules/DK/app/Acceptance/DKAcceptanceApp.php`
- `Modules/DK/app/Acceptance/Coverage/SourceCaseMappings.php`
- `Modules/DK/app/Acceptance/Shared/Target/DKTargetProfile.php`
- `Modules/DK/app/Acceptance/Shared/Target/DKTargetResourceAdapter.php`
- `Modules/DK/app/Providers/DKServiceProvider.php`
- `Modules/DK/config/config.php`
- `stubs/acceptance/AcceptanceComponent.stub`
- `Modules/DK/tests/Unit/DKAcceptanceAppTest.php`
- `Modules/DK/tests/Feature/DKTargetFoundationIntegrationTest.php`
- `tests/Feature/DKModuleBootstrapTest.php`
- `composer.json`
- `phpunit.xml`
- `docs/ai/rules/AI-PACK-GENERATION-RULES.md`
- `docs/ai/rules/SCOPE-CONTROL-RULES.md`
- `docs/ai/rules/AI-EXECUTION-RULES.md`
- `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md`
- `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`
- `docs/ai/rules/COMMENTING-RULES.md`
- `docs/ai/rules/GIT-AND-COMMIT-RULES.md`
- `docs/ai/rules/REPORTING-RULES.md`
- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`
- `docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md`

### Project / Framework Source Reference Rule

Required framework guidance is limited to the project/DK profiles, current component-scaffolding source, current DK provider/catalog patterns, Laravel 12 container alias/singleton behavior, and nearby PHPUnit tests. Resolve version-sensitive Laravel behavior from installed source or Laravel Boost only when current source is insufficient.

Do not bulk-read unrelated Packs, Runs, Reviews, source corpora, archives, vendor packages, external repositories, target documentation, or environment files.

## 11. Configuration / Settings Requirements

Configuration / Settings Impact: No.

### Config File Requirements

No config file changes required. Preserve `Modules/DK/config/config.php` byte-for-byte. The accepted `dk.target.enabled` and `dk.target.environment` gate remains authoritative.

### Environment Variable Requirements

No environment variables required. Do not read or edit `.env` or `.env.example`.

### Admin Settings Requirements

No Admin Settings changes required.

### External Integration / Secret Settings Requirements

No external-integration or secret settings required. The fake accepts no provider URL, account, phone number, credential, token, Authorization header, callback secret, signing key, or arbitrary payload.

### Test / Development Setup Requirements

No external setup is required. Tests construct safe synthetic request DTOs, resolve the existing application container only for wiring checks, and keep fake state in process memory. No database, queue worker, browser, HTTP server, callback endpoint, or provider account is used.

### Settings Validation

```text
Validation required:
- Config keys checked: Not applicable; config file must remain unchanged
- Env placeholders documented: Not applicable
- Admin settings created or documented: Not applicable
- Sensitive settings masked: Not applicable; no sensitive setting exists
- Tests/API test artifacts updated for setting-dependent behavior: Feature/Unit tests required; API artifacts not applicable
```

### Operator Setup Notes

No operator manual setup required.

## 12. Do Not Change

- root application source, configuration, bootstrap, routes, commands, services, registries, DTOs, models, migrations, factories, seeders, or database schema;
- `Modules/Core/**`;
- shared Acceptance Component, executor, prerequisite, target-resource, batch, reporting, or coverage contracts;
- `Modules/DK/config/config.php`, `module.json`, `composer.json`, database, routes, resources, seeders, coverage mappings, or target profile/adapter behavior;
- any Suite, Scenario, Variant, Step, source-case mapping, corpus record, executor request, target action, selector, fixture, real worker, job, Notification class, Mailable, event/listener, webhook route/controller, browser action, HTTP adapter, or external-provider adapter;
- Laravel `Notification`, `Http`, `Queue`, `Bus`, database, filesystem, cache, clock, random source, sleep, process, or network side effects in the fake;
- `.env`, `.env.example`, dependencies, lockfiles, vendor, generated assets, application cache, queue/cache/database/runtime state, or external systems;
- accepted Packs, Runs, Decisions, Reviews, Guides, Change Requests, Remediations, or Canonical records except lifecycle/index updates explicitly listed in sections 8–9.

## 13. Clarification Questions Before Implementation

No unresolved clarification blocked Pack review. The operator subsequently accepted this contract and separately authorized execution on 2026-10-10.

The proposed Pack-local contract is:

1. Component key: `notification-delivery`; class: `NotificationDeliveryAcceptanceComponent`; DK catalog version changes from `v1` to `v2` because the visible Component set changes.
2. The existing `acceptance:component:create` generator creates the Component shell and managed App registration. The generated Component remains empty: no Suite, Scenario, Variant, or resolution.
3. `NotificationDeliveryRequest` version 1 contains only `notificationKey`, `recipientReference`, `templateKey`, and `correlationId`. All three keys use the accepted safe hierarchy-key grammar and the correlation identifier is a UUID. No address, phone number, message body, template tokens, arbitrary metadata, or payload is accepted.
4. `NotificationProvider::submit()` returns a `NotificationSubmission` with the same safe identity, deterministic provider reference, current simulated millisecond time, and version 1.
5. Resubmitting an identical request is idempotent and returns the original submission without a second audit event. Reusing a notification key with different safe input fails with `notification_submission_conflict`.
6. `NotificationCallbackSimulator::simulate()` accepts a notification key, a safe callback key, and `NotificationCallbackOutcome` (`delivered` or `failed`). Unknown notification keys fail with `notification_not_found`.
7. Replaying the same callback key and outcome is idempotent and returns the original event. Reusing a callback key with a different outcome, or applying a different terminal callback after terminal completion, fails with `notification_callback_conflict`.
8. `NotificationWorkerOracle` exposes only `queued` before a terminal simulated callback and `processed` afterward. It performs no queue dispatch or worker execution.
9. `NotificationTimeOracle` exposes the current simulated millisecond value. `InMemoryNotificationDeliveryFake::advanceMilliseconds()` is the only time-control method; it accepts a positive bounded delta and rejects invalid input with `notification_time_invalid`.
10. `NotificationAuditOracle` returns an immutable ordered list of safe `NotificationDeliveryEvent` values for one notification. Event types are `provider_submitted`, `callback_delivered`, and `callback_failed`.
11. The fake starts at millisecond `0`, derives provider references deterministically from the notification key, preserves insertion order, and uses no system time or randomness.
12. `InMemoryNotificationDeliveryFake` implements all five ports. `DKServiceProvider` registers it once as a singleton and aliases every port to that same instance. Contracts/data/enums/fake contain no container lookup.
13. Fake audit events are in-memory test observations, not Laravel logs, persisted audit records, or evidence artifacts.
14. All new error codes are DK-local simulation contract codes. They do not modify shared operation/API/CLI error registries.

Changing any item above requires Pack normalization and operator acceptance before implementation.

## 14. Multilingual / Translation Requirements

```text
Multilingual / Translation Impact: No
RTL/LTR Impact: No
API Message Translation Impact: No
Admin UI Translation Impact: No
Owner-defined Default Content Translation Impact: No
```

No multilingual or translation changes required. No visible text, API message, validation message, UI label, operator message, or locale metadata is introduced.

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

No UI changes required. No UI surface, route, form, table, action, menu, permission, asset, translation, or RTL/LTR validation applies.

## 16. Operator Documentation Requirements

```text
Operator Documentation Impact: No
Operator Documentation Update Required: No
Guide File: none; the DK guide index declares no approved guide
Screenshots required: No
```

No operator-facing behavior changed. The Component is empty and the fake is an internal test/simulation boundary. No operator workflow or setup is introduced.

## 17. Implementation Rules

1. Run the existing Component scaffold in the separately approved execution with exact logical inputs `DK` and `notification-delivery`. First run `php artisan acceptance:component:create DK notification-delivery --json` as the dry-run; after reviewing its exact two-file plan, run `php artisan acceptance:component:create DK notification-delivery --apply --confirm --no-interaction --json`. The Pack authorizes only the files listed in sections 8–9.
2. Preserve the scaffold-managed marker regions in `DKAcceptanceApp` and `NotificationDeliveryAcceptanceComponent`.
3. Bump only the DK catalog version from `v1` to `v2`; do not change App key `dk` or any shared schema version.
4. Place every contract, DTO, enum, exception, and fake below `Modules/DK/app/Acceptance/Components/NotificationDelivery/`.
5. Use `declare(strict_types=1)`, final readonly DTOs, backed string enums, exact scalar/object types, defensive copying for returned lists, and constructor validation.
6. Use constructor injection. Container aliases exist only in `DKServiceProvider`; do not use `app()`, `resolve()`, facades, static mutable state, or global state inside Component code.
7. Keep ports narrow and role-specific. Do not add a generic repository, manager, gateway, service locator, event bus, storage abstraction, or speculative production adapter.
8. The in-memory fake is the only implementation in this Pack and must be `final`. It may own private arrays/scalars only and must expose state solely through the five contracts plus the explicit time-advance test control.
9. Enforce deterministic idempotency and terminal-state rules from section 13. Never silently overwrite a submission, callback, or terminal result.
10. Do not use Laravel Notification, Queue, Bus, Event, HTTP client, database, filesystem, cache, sleep, system clock, random/UUID generation, subprocess, socket, or external library calls.
11. Do not replace any method of `DKTargetResourceAdapter`. Stage 12 remains behind the accepted target readiness/account gate and does not make DK operational.
12. Do not add scenario metadata or source mappings. Stages 13–15 own corpus import, scenario groups, and reconciliation.

## 18. Architecture Constraints

- Preserve `App -> Component -> Suite -> Scenario -> Variant -> Step`; this Pack creates only the `notification-delivery` Component node.
- ND remains DK-owned and is not a Nwidart module, independent documentation owner, root service, or Core capability.
- Shared project/Core layers must not depend on DK or its simulation contracts.
- The provider contract is a DK-local boundary port; the in-memory fake is its adapter. Wiring occurs only at the DK composition root.
- All five contract aliases must resolve to one fake instance so provider, callback, and oracles share deterministic state.
- Metadata inspection remains side-effect free. Resolving/listing the Component must not mutate fake state.
- No real-SMS safety is structural: there is no production provider class, HTTP client, Laravel notification channel, target URL, credential field, recipient address, arbitrary payload, callback route, or external execution path.
- Do not introduce a new executor capability. Later executable scenarios must use accepted Core capabilities through a separately approved Pack.

## 19. Validation Rules

- Verify every created/edited path exactly matches sections 8–9 and no generated extra file appears.
- Verify the Component descriptor is exactly `notification-delivery`, DK catalog version is exactly `v2`, and suites/scenarios/variants/mappings remain empty.
- Verify all five contracts resolve to the exact same fake singleton.
- Verify valid submit/callback/time/audit flows and every invalid/conflicting branch from sections 13 and 21.
- Verify identical submit and callback replay are idempotent with no duplicate audit event.
- Verify events are ordered by simulated time/insertion and contain only the approved safe fields.
- Verify no side effect reaches Laravel Notification, Queue/Bus/Event, HTTP, database, filesystem, cache, real clock, randomness, process, or network boundaries.
- Scan created/edited implementation and tests for URL schemes, phone-like recipient values, Authorization headers, token/password/cookie/session/secret literals, arbitrary payload/body fields, `env(`, service-locator calls, facades in component source, and external I/O APIs.
- Verify PHP syntax, scoped formatting, focused tests, affected DK/catalog regressions, documentation structure/paths, and `git diff --check`.
- Do not claim full-suite, real-provider, queue-worker, callback-endpoint, SMS, browser, network, production, or staging validation.

## 20. Security Rules

- Use synthetic safe keys only, such as `notification-a`, `recipient-a`, `template-a`, and obvious UUID examples.
- Never read, accept, store, log, display, return, document, or commit a real phone number, recipient, customer identifier, account, URL, selector, message body, template token, provider payload, credential, token, cookie, session, Authorization value, callback secret, signing key, or production value.
- `recipientReference` is a non-sensitive synthetic key, never an address or personal identifier. Tests must reject malformed/free-form values and prove the DTO has no address/payload field.
- Exception messages are stable codes only and must not interpolate request data.
- In-memory events contain only allowlisted safe identity, event type, simulated time, and correlation/provider/callback references.
- No raw inbound payload exists; the callback simulator accepts typed safe values only.
- Real target access, external network, usable credentials, and production/unknown execution remain forbidden.

## 21. Error Handling / Logging / Traceability Requirements

### Error Handling Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: No
UI / Admin UI Error Impact: No
Internal Exception Impact: Yes
External Integration Error Impact: No; no external integration exists
Callback / Inbound-event Error Impact: Yes; in-process simulator only
Logging Impact: No
Traceability Impact: Yes; safe correlation identity is preserved in-memory
Error Code Impact: Yes; DK-local simulation codes only
Security / Sensitive Data Impact: Yes
```

### Error Scenarios and Codes

| Scenario | Trigger | Stable code | Retryable | Permanent | Admin action | State/log impact |
|---|---|---|---:|---:|---:|---|
| invalid safe request | invalid key/UUID/version or unsupported field shape | `notification_request_invalid` | No | Yes | No | no state; no log |
| conflicting submit | same notification key with different accepted request | `notification_submission_conflict` | No | Yes | No | original state unchanged; no log |
| unknown notification | callback/oracle lookup before submit | `notification_not_found` | No | Yes | No | no state; no log |
| conflicting callback | callback key/outcome conflict or second terminal outcome | `notification_callback_conflict` | No | Yes | No | original terminal state unchanged; no log |
| invalid simulated time | non-positive/out-of-bound advance or overflow | `notification_time_invalid` | No | Yes | No | time/state unchanged; no log |

`NotificationDeliverySimulationException::because(string $errorCode)` is the only new exception family. Its message and public `errorCode` are exactly the stable code; `retryable` is always `false`, `permanent` is always `true`, and `adminActionRequired` is always `false` for the five codes above. It exposes no raw input and is handled directly by tests/future owner code. No HTTP status, API shape, CLI mapping, shared operation result, or translation is introduced.

### External Error Normalization

No external-error normalization changes required. The word Provider names a port and fake; no external provider is called or modeled.

### Logging Requirements

No Laravel logging changes required. The fake must not log successes, failures, requests, callbacks, or state. In-memory audit observations are typed fake output, not logs.

### Traceability Requirements

- `correlationId` is supplied in `NotificationDeliveryRequest`, copied unchanged to submission and audit events, and never generated by the fake.
- `notificationKey`, deterministic provider reference, and safe callback key connect submit, callback, worker state, and audit observations within one process.
- No identifier is persisted, returned by API/CLI, written to Laravel logs, or claimed as end-to-end runtime traceability.

### Error Tests Required

Tests must assert each code exactly, verify classifications documented above at the Pack contract level, prove state/time/event collections stay unchanged on failure, and scan exception/event serialization for sentinel absence. Identical duplicate operations must prove idempotency rather than exception behavior.

### Required Follow-up Updates

Later scenario/corpus Packs must decide how these DK-local failures map into executable scenario results only when such execution exists. No mapping is authorized here.

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
Audit / History Impact: No persisted audit; in-memory fake observations only
Rollback Impact: source-only targeted rollback
```

No data model, migration, or relationship changes required. No tables, columns, foreign keys, model relationships, snapshots, indexes, constraints, backfill, seeder, factory, or persistent audit/history record is created.

## 23. Commenting Requirements

Follow `docs/ai/rules/COMMENTING-RULES.md`.

Add comments only for the no-real-SMS invariant, duplicate/terminal idempotency rule, or why all contract aliases must share one fake instance when names/types alone cannot express the reason. Do not narrate obvious state assignments or test setup.

## 24. Testing Requirements

```text
Test Quality Required: Yes
Behavioral Assertions Required: Yes
Contract Assertions Required: Yes
Database Assertions Required: No
Side-effect Assertions Required: Yes; assert absence
Negative/Error Scenario Tests Required: Yes
Authorization/Permission Tests Required: No
Idempotency Tests Required: Yes
Queue/Event/Job Assertions Required: Yes; assert none dispatched
External Integration Fake/Mock Required: No external mock; the Pack creates the domain fake
Security/Secret Masking Tests Required: Yes; omission rather than masking
```

Tests must use nearby PHPUnit conventions, Arrange/Act/Assert structure, exact `assertSame()` checks, and independent setup. Unit tests instantiate classes directly; only the Feature test boots Laravel to prove composition.

The tests must prove:

- safe immutable DTO validation and strict field allowlist;
- deterministic submit, provider reference, simulated time, worker state, and audit order;
- idempotent identical submit/callback replay;
- conflict and unknown-reference protection without partial mutation;
- terminal-state protection;
- all five contracts resolve to one singleton fake;
- ND is a visible empty Component while no executable catalog item exists;
- no HTTP, Laravel Notification, queue job, event, database, log, real time, random, or external side effect occurs;
- sentinel values never appear in exceptions outside the stable code or in an unintended output field.

## 25. Acceptance Checklist

- [x] exact `notification-delivery` Component is registered under DK and catalog version is `v2`;
- [x] Component has zero Suites, Scenarios, Variants, Steps, and source mappings;
- [x] five narrow contracts and one in-memory fake exist only inside the ND Component boundary;
- [x] all contracts alias the same singleton fake;
- [x] request/submission/event contracts contain only approved safe fields;
- [x] submit/callback replay is deterministic and duplicate-safe;
- [x] callback terminal conflicts and invalid/unknown input fail with exact DK-local codes and no mutation;
- [x] worker/time/audit oracles expose deterministic in-memory observations only;
- [x] no real SMS, Notification, HTTP, callback route, queue worker, database, filesystem, clock, randomness, or network behavior exists;
- [x] DK target resource adapter remains fail-closed and unchanged;
- [x] focused and regression tests pass with no sensitive sentinel exposure;
- [x] Run/index lifecycle records are complete after separately approved execution;
- [x] no commit occurs without separate operator authorization.

## 26. Tests to Add

### `NotificationDeliveryRequestTest`

- Purpose: protect the immutable request allowlist and safe identity grammar.
- Behavior proven: accepted safe keys/UUID/version are preserved; malformed, overlong, free-form, phone-like, wrong-version, or control-character values are rejected with `notification_request_invalid`.
- Main assertions: exact properties/version; reflection property allowlist; exact exception code; no arbitrary payload/address/body/token field.
- Required setup: constructed values only; no Laravel boot.
- Expected side effects: none.
- Negative/error cases: every invalid field independently; unexpected sensitive sentinel absent from exception.
- Related acceptance criteria: safe DTO and no-real-SMS boundary.

### `InMemoryNotificationDeliveryFakeTest`

- Purpose: prove the complete provider/callback/worker/time/audit simulation contract.
- Behavior proven: deterministic submission and time, state progression, ordered audit, identical replay idempotency, conflict protection, unknown lookup, and bounded time advance.
- Main assertions: exact DTOs/enums/correlation/provider/callback references, object/value equality where appropriate, exact event count/order/time, exact error codes, unchanged state after rejection.
- Required setup: instantiate one fake directly per test with safe synthetic values.
- Expected side effects: private in-memory state only.
- Negative/error cases: conflicting submit, unknown callback/oracle lookup, callback replay conflict, terminal conflict, invalid time delta.
- Related acceptance criteria: all simulation and idempotency/error criteria.

### `NotificationDeliveryComponentIntegrationTest`

- Purpose: prove DK App/catalog and Laravel composition without external behavior.
- Behavior proven: `notification-delivery` is the single Component, catalog version is `v2`, no executable items/mappings exist, and all five contracts resolve to the same fake singleton.
- Main assertions: exact descriptors/counts/null resolution; exact instance identity; framework fake assertions for zero Notification/Queue/Event work and blocked stray HTTP; no log or database expectation introduced.
- Required setup: existing application test bootstrap only; no migration or external setup.
- Expected side effects: container resolution only.
- Negative/error cases: resolving/listing repeatedly creates no duplicate Component and no fake state.
- Related acceptance criteria: component registration, composition, empty executable surface, no external side effects.

Existing `DKAcceptanceAppTest`, `DKTargetFoundationIntegrationTest`, and `DKModuleBootstrapTest` must be updated only for `v2` and the one empty Component. Preserve every target-foundation assertion.

## 27. Tests to Run

After separate execution approval, run only the following safe, targeted validation:

1. `php -l` for every created/edited PHP file in sections 8–9.
2. Scoped `vendor/bin/pint --test` for those PHP files; if formatting correction is required, run scoped Pint only on the same paths and rerun `--test`.
3. Focused PHPUnit files:
   - `Modules/DK/tests/Unit/Acceptance/Components/NotificationDelivery/Data/NotificationDeliveryRequestTest.php`
   - `Modules/DK/tests/Unit/Acceptance/Components/NotificationDelivery/Fakes/InMemoryNotificationDeliveryFakeTest.php`
   - `Modules/DK/tests/Feature/NotificationDeliveryComponentIntegrationTest.php`
   - `Modules/DK/tests/Unit/DKAcceptanceAppTest.php`
   - `Modules/DK/tests/Feature/DKTargetFoundationIntegrationTest.php`
   - `tests/Feature/DKModuleBootstrapTest.php`
4. Affected catalog/registry regressions only if the focused tests expose a shared interaction: `tests/Unit/AcceptanceCatalogTest.php` and `tests/Unit/AcceptanceAppRegistryTest.php`.
5. Guarded `acceptance:list --app=dk --json` expecting schema v2 output with Component `notification-delivery` and zero executable items; guarded `acceptance:plan --app=dk --json` expecting zero plan items.
6. Targeted source/sensitive-value/service-locator/external-I/O scans, document heading/path checks, and `git diff --check`.

Tests must run with the repository's approved isolated testing bootstrap. Do not run migrations, seeders, full suite, queue workers, browsers, HTTP servers, target commands, Composer/npm, cache/config commands, or external network operations without separate approval.

## 28. Expected Output

- one registered empty ND Component with DK catalog version `v2`;
- five DK-local ports and one deterministic in-memory fake;
- typed request/submission/event values and fixed callback/worker/audit enums;
- stable DK-local simulation errors with no raw input exposure;
- provider/callback/worker/time/audit behavior covered by focused Unit/Feature tests;
- existing DK foundation behavior preserved;
- no corpus, executable scenario, real provider, callback endpoint, queue, database, configuration, operator guide, UI, API, or external I/O;
- after approved execution, one Run Report and synchronized DK Pack/Run indexes.

## 29. Operator Execution Checklist

### Before AI Execution

- Confirm acceptance of the Pack-local contract choices in section 13, especially Component key `notification-delivery`, catalog version `v2`, the five ports, the request field allowlist, and the exact DK-local error codes.
- Confirm the narrow cross-owner edit to `tests/Feature/DKModuleBootstrapTest.php` is authorized solely to update DK catalog assertions.
- Confirm execution remains in-process/fake-only and no real provider, phone number, callback URL, credential, worker, database, browser, or network is available or needed.

### After AI Execution

- Verify catalog/list output shows the empty ND Component but no Suite/Scenario/Variant.
- Verify all five contracts resolve to one fake and the original target foundation still blocks execution before any real target behavior.
- Inspect the diff and test evidence for absence of real SMS/Notification/HTTP/queue/database/config/secret behavior.

## 30. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md` and include the Documentation Maintenance section required by `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`.

Report exact files, scaffold command/result, contract/fake design, catalog version, DI aliases, idempotency/error behavior, tests/commands and results, no-real-SMS evidence, sensitive scans, scope, deviations, limitations, lifecycle/index updates, and required follow-ups. Do not claim any real target/provider/worker/callback/network validation.

## 31. Review Checklist

- Review owner/path boundaries and the one explicit root integration-test edit.
- Review contract necessity and interface segregation; reject speculative abstractions.
- Review that one singleton backs every role and no service locator exists.
- Review DTO field allowlists, validation, defensive copies, deterministic time/reference rules, duplicate behavior, terminal-state protection, and exact codes.
- Review no-real-SMS structural proof and absence of sensitive/free-form values.
- Review Component/catalog behavior and preservation of empty scenarios/mappings.
- Review behavioral test value, distinct failure detection, framework-fake assertions, and regression scope.
- Review Run/index synchronization and separate execution/acceptance/commit gates.

## 32. Rollback / Safety Notes

Rollback is source-only and targeted: remove only the files created by this Pack, remove only the scaffolded `notification-delivery` managed App registration, restore DK catalog version/assertions to the pre-Pack value, remove only the fake bindings/aliases, and synchronize lifecycle indexes. Do not use broad Git restore/reset/clean commands.

No data rollback, queue cleanup, cache clear, provider cleanup, callback cleanup, or external-system action exists. Preserve the accepted DK Pack 0001 target foundation unchanged.

## 33. Stop Conditions

Stop and report before implementation or continuation if:

- the operator has not accepted section 13 or separately authorized execution;
- Component generation would create or edit a path outside sections 8–9;
- a shared project/Core source contract or any additional root file must change;
- any real provider, SMS/notification channel, phone number, target URL, callback endpoint, credential, token, payload, browser, HTTP request, queue worker, database, filesystem, real clock, randomness, process, or external network becomes necessary;
- the fake cannot be implemented deterministically in memory with the exact allowlist;
- catalog version `v2`, key `notification-delivery`, DTO fields, idempotency, terminal behavior, or error codes require a different decision;
- corpus, mapping, Suite, Scenario, Variant, Step, executor capability, target-resource replacement, or operational workflow is required;
- tests reveal a shared architecture defect or cross-owner conflict outside scope;
- unrelated working-tree changes overlap an allowed file;
- a destructive, dependency-changing, cache-changing, database-changing, long-running, or external-network command appears necessary.

## 34. Open Questions

No open question blocks operator review. All section-13 choices are Pack-local proposals and become executable instructions only after the operator accepts this Pack and separately authorizes execution.

Future stages remain separate:

- stage 13: DK/ND source-corpus import and deterministic mappings;
- stage 14: bounded executable scenario groups;
- stage 15: full coverage reconciliation;
- stage 16: regenerate project Pack 0017 from accepted stages 1–15.
