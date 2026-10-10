# AI-PACK-DK-TARGET-FOUNDATION-0001 — DK target foundation

Status: `accepted — operator accepted 2026-10-10; technical validation passed; commit authorized`

Generated: `2026-10-10`

Owner: DK

Decision: accepted project Decisions 0005, 0007, 0009, 0011, 0012, and 0015; accepted `DK-DECISION-0001`.

Depends On: accepted project Pack 0012 and Run 001 for the target-resource contracts; accepted Core Pack 0003 for the capability boundary; accepted project Packs 0013–0016 and their Runs; active DK source and owner manifest created by Pack 0016.

Execution / Acceptance / Commit: execution was separately authorized and completed on 2026-10-10. The operator accepted the result and separately authorized commit on 2026-10-10.

## 1. Task ID

`AI-PACK-DK-TARGET-FOUNDATION-0001`

## 2. Task Title

Create the fail-closed DK target profile and resource-adapter foundation.

## 3. Goal

Establish the first DK-owned implementation of the accepted target-resource extension point while guaranteeing that no real DK target, account, credential, fixture, browser, API, notification, callback, or external network behavior can run.

The Pack adds a validated DK target profile, one fail-closed adapter aggregate implementing the five shared target-resource ports, owner-local provider wiring, and focused Unit/Feature tests. It does not create Notification Delivery or any executable scenario.

## 4. Context

Project Pack 0016 created and activated the DK Nwidart module and its owner-local documentation root. Decision 0015 places the target-profile foundation at stage 11 and reserves project Pack 0017 for stage 16; therefore this work is DK-local Pack 0001 rather than project Pack 0017.

Accepted project Pack 0012 already owns the target-neutral contracts and registry:

```text
TargetReadinessProbe
-> TargetAccountResolver
-> TargetFixtureManager
-> execution callback
-> TargetOracle
-> TargetCleanup
```

DK currently registers only an empty `dk` Acceptance App. Its `Shared` area contains no target profile or resource adapters. The safest next increment is a no-I/O composition foundation that exercises the real registry and lifecycle contracts but stops before target execution.

Project Decision 0012 requires an owner decision covering the isolated environment and future fake ND/no-real-SMS boundary before target implementation. The operator accepted `DK-DECISION-0001` on 2026-10-10, satisfying that prerequisite.

The operator-approved project-governance correction on 2026-10-10 synchronized root `AGENTS.md` and `docs/project/ai/TMS-AI-PROFILE.md` with `docs/modules/MODULES-DOCS-INDEX.md`, the DK manifest, current source, accepted Pack 0016, and accepted Run 001. The former owner-activation drift is resolved without changing implementation behavior.

## 5. Related Release / Phase

Decision 0015, stage 11 of the sixteen-stage roadmap.

## 6. Related Epic / Feature / Story

First DK-owned target profile, environment gate, resource lifecycle composition, and safety foundation.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`
- `docs/project/ai/decisions/TMS-DECISION-0007-nwidart-target-modules-and-component-hierarchy.md`
- `docs/project/ai/decisions/TMS-DECISION-0009-operator-input-approval-and-sensitive-values.md`
- `docs/project/ai/decisions/TMS-DECISION-0011-extensible-executors-and-capabilities.md`
- `docs/project/ai/decisions/TMS-DECISION-0012-automated-e2e-coverage-and-traceability.md`
- `docs/project/ai/decisions/TMS-DECISION-0015-clean-acceptance-hierarchy-cutover.md`
- `Modules/DK/docs/ai/decisions/DK-DECISION-0001-isolated-target-foundation-boundary.md` — accepted 2026-10-10
- `docs/project/ai/packs/AI-PACK-TMS-TARGET-RESOURCE-LIFECYCLE-0012.md`
- `docs/project/ai/runs/AI-PACK-TMS-TARGET-RESOURCE-LIFECYCLE-0012-run-001.md`
- `docs/project/ai/packs/AI-PACK-TMS-DK-TARGET-MODULE-BOOTSTRAP-0016.md`
- `docs/project/ai/runs/AI-PACK-TMS-DK-TARGET-MODULE-BOOTSTRAP-0016-run-001.md`
- current source paths listed in sections 8–10

## 8. Files to Create

- `Modules/DK/app/Acceptance/Shared/Target/DKTargetProfile.php`
- `Modules/DK/app/Acceptance/Shared/Target/DKTargetResourceAdapter.php`
- `Modules/DK/tests/Unit/DKTargetProfileTest.php`
- `Modules/DK/tests/Unit/DKTargetResourceAdapterTest.php`
- `Modules/DK/tests/Feature/DKTargetFoundationIntegrationTest.php`

After separately approved execution, reporting may create exactly:

- `Modules/DK/docs/ai/runs/AI-PACK-DK-TARGET-FOUNDATION-0001-run-001.md`

No other source, test, Decision, Canonical, Guide, Run, or Review file may be created by Pack execution.

## 9. Files to Edit

- `Modules/DK/config/config.php`
- `Modules/DK/app/Providers/DKServiceProvider.php`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-TARGET-FOUNDATION-0001.md` — lifecycle/status synchronization only
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`
- `Modules/DK/docs/ai/runs/RUNS-INDEX.md` — only if the Run Report is created after approved execution

No equivalent-path substitution may cross outside `Modules/DK/`. Any required shared-project or Core edit is a stop condition and must be routed to the owning documentation layer.

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
- exact source references in section 7
- `app/Acceptance/Prerequisites/Data/PrerequisiteExecutionData.php`
- `app/Acceptance/Targets/Contracts/TargetReadinessProbe.php`
- `app/Acceptance/Targets/Contracts/TargetAccountResolver.php`
- `app/Acceptance/Targets/Contracts/TargetFixtureManager.php`
- `app/Acceptance/Targets/Contracts/TargetOracle.php`
- `app/Acceptance/Targets/Contracts/TargetCleanup.php`
- `app/Acceptance/Targets/Data/TargetEnvironment.php`
- `app/Acceptance/Targets/Data/TargetReadinessResult.php`
- `app/Acceptance/Targets/Data/ResourceProvisionResult.php`
- `app/Acceptance/Targets/Data/TargetOracleResult.php`
- `app/Acceptance/Targets/Data/CleanupResult.php`
- `app/Acceptance/Targets/Data/TargetContext.php`
- `app/Acceptance/Targets/TargetResourceAdapters.php`
- `app/Acceptance/Targets/TargetResourceRegistry.php`
- `app/Acceptance/Targets/TargetResourceCoordinator.php`
- `Modules/DK/app/Acceptance/DKAcceptanceApp.php`
- `Modules/DK/app/Providers/DKServiceProvider.php`
- `Modules/DK/config/config.php`
- `Modules/DK/tests/Unit/DKAcceptanceAppTest.php`
- `tests/Feature/DKModuleBootstrapTest.php`
- `tests/Unit/TargetResourceCoordinatorTest.php`
- `tests/Unit/TargetResourceContractSafetyTest.php`
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

Required project/framework guidance is limited to `docs/project/ai/TMS-AI-PROFILE.md`, the DK profile/manifest, the exact accepted target-resource source above, Laravel 12 service-provider/container documentation resolved through Laravel Boost, and installed Nwidart module loading behavior only if provider registration cannot be verified from current source.

Do not bulk-read unrelated Packs, Runs, Reviews, Core implementation, vendor packages, source corpora, archives, environment files, external repositories, or target documentation.

## 11. Configuration / Settings Requirements

Configuration / Settings Impact: Yes, limited to non-sensitive, versioned, fail-closed DK defaults.

### Config File Requirements

Config files to edit:

- `Modules/DK/config/config.php`

Config keys required:

- Key: `dk.target.enabled`
  Purpose: explicit code-owned activation gate for the DK target foundation
  Default value: `false`
  Environment-specific: No in this Pack
  Sensitive: No
- Key: `dk.target.environment`
  Purpose: select the accepted `TargetEnvironment` classification
  Default value: `unknown`
  Allowed values: `testing`, `staging`, `production`, `unknown`
  Environment-specific: No in this Pack
  Sensitive: No

`DKServiceProvider` must merge the module config under key `dk` before binding the target profile. The Pack must not publish config, create a second config namespace, or read `env()` from application code.

### Environment Variable Requirements

No environment variables required. Do not edit or read `.env` or `.env.example`.

### Admin Settings Requirements

No Admin Settings changes required.

### External Integration / Secret Settings Requirements

No external-integration or secret settings required. No credential, secret reference, URL, selector, token, account identifier, callback secret, or provider setting is created or resolved.

### Test / Development Setup Requirements

Tests override `dk.target.enabled` and `dk.target.environment` in process. They use only constructed DTOs and the existing application container; no database migration, target setup, browser, HTTP fake, queue worker, or external service is required.

### Settings Validation

```text
Validation required:
- Config keys checked: Yes
- Env placeholders documented: Not applicable
- Admin settings created or documented: Not applicable
- Sensitive settings masked: Not applicable; no sensitive setting exists
- Tests/API test artifacts updated for setting-dependent behavior: Unit and Feature tests required; API artifacts not applicable
```

### Operator Setup Notes

No operator manual setup required. The default remains disabled and unknown. Enabling a real target through environment or deployment configuration is outside this Pack and forbidden.

## 12. Do Not Change

- root application source, configuration, bootstrap, routes, models, migrations, factories, seeders, database schema, tests, services, commands, DTOs, registries, or shared target-resource contracts;
- `Modules/Core/**`;
- DK App identity, catalog version, Component markers, coverage mappings, module metadata, Composer metadata, PHPUnit configuration, routes, resources, database, or seeders;
- any DK Component, including Notification Delivery;
- any Suite, Scenario, Variant, Step, source-case mapping, executor request, action, selector, URL, payload, fixture behavior, provider adapter, callback receiver, worker, time oracle, audit oracle, or notification sender;
- `.env`, `.env.example`, dependencies, lockfiles, vendor, generated assets, browser installation, queue/cache/database/runtime state, or external systems;
- accepted project Packs, Runs, Decisions, Reviews, Guides, Change Requests, Remediations, or Canonical records;
- root or project governance files during Pack execution unless a separately approved project-owned change explicitly lists them.

## 13. Clarification Questions Before Implementation

No unresolved clarification question remains. Pack execution still requires a separate operator authorization.

The accepted and normalized Pack choices are:

1. DK-local Pack numbering starts at `0001`; project Pack `0017` remains reserved for stage 16.
2. `DKTargetProfile` is an immutable, target-owned value object containing only `enabled`, `TargetEnvironment`, and version `1`.
3. Config parsing accepts only exact boolean `enabled` and an exact accepted environment string. Invalid/missing values normalize to disabled/unknown and cannot activate the target.
4. `DKTargetResourceAdapter` implements the five existing shared ports directly. It creates no additional local interface, facade, service locator, mutable global state, or persistence.
5. Readiness returns `unsafe_target` for production/unknown, `target_not_ready` for testing/staging while disabled, and ready only for testing/staging when explicitly enabled in process.
6. Account resolution always returns `resource_unavailable`; fixture provisioning always returns `fixture_setup_failed`; oracle evaluation always returns `oracle_failed`.
7. Cleanup succeeds only for an empty reference list. Any non-empty reference list is returned intact as remaining references with `cleanup_failed`.
8. These rejecting methods are safety sentinels, not placeholders to be bypassed. A later accepted Pack must replace the applicable adapter behavior before any scenario can execute.
9. `DKServiceProvider` owns all wiring: merge config, bind one immutable profile and one adapter object, keep existing `DKAcceptanceApp` registration, and register the adapter aggregate once for App key `dk` through `TargetResourceRegistry`.
10. Registration is idempotent for repeated provider callbacks and must not replace another pre-registered `dk` aggregate silently.
11. No log event, translation, UI, API, database record, Run/Test/Step field, or external request is added.
12. Pack validation is source-only and fake-only. The default application must prove that DK is registered yet rejects at readiness before execution.

## 14. Multilingual / Translation Requirements

### Multilingual Impact

```text
Multilingual / Translation Impact: No
RTL/LTR Impact: No
API Message Translation Impact: No
Admin UI Translation Impact: No
Owner-defined Default Content Translation Impact: No
```

No multilingual or translation changes required.

### Locales Required

No locale-specific content required.

### Translation Files to Create or Edit

No translation files changed.

### Texts / Labels to Translate

No visible text introduced or changed.

### Admin UI Translation Requirements

No Admin UI translation changes required.

### API Message Translation Requirements

No API message translation changes required.

### Owner / Integration Test Content Translation Requirements

No owner-visible or upstream content is introduced.

### RTL/LTR Requirements

No RTL/LTR layout impact.

### Locale / Direction Metadata

No locale or direction metadata changes required.

### Translation Validation

```text
Validation required:
- profile-required translations added/updated: Not applicable
- No hardcoded visible text remains: Yes
- API messages use translation keys: Not applicable
- Admin UI labels use translation keys: Not applicable
- RTL/LTR impact reviewed: Not applicable
- Human review for locale wording required: No
```

### Operator Translation Review Notes

No operator translation review required.

## 15. UI / Admin UI Requirements

### UI Impact

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

No UI changes required.

### UI Surfaces to Create or Edit

No UI surfaces created or edited.

### UI Files to Create or Edit

No UI files created or edited.

### Admin Menu / Navigation Requirements

No Admin menu or navigation changes required.

### Forms / Fields Requirements

No form or field changes required.

### Tables / Filters / Columns Requirements

No table, filter, or column changes required.

### UI Actions / Buttons Requirements

No UI actions or buttons changed.

### Dashboard / Monitoring UI Requirements

No dashboard or monitoring UI changes required.

### UI State Requirements

No special UI states required.

### UI Permission / Visibility Requirements

No UI permission or visibility changes required.

### UI Translation Requirements

No UI translation changes required.

### UI Validation Requirements

All UI validation items are not applicable.

### Operator UI Review Notes

No operator UI review required.

## 16. Operator Documentation Requirements

```text
Operator Documentation Impact: No
Operator Documentation Update Required: Not applicable
Guide File: none; the DK guide index declares no approved guide
```

### Operator-facing Changes

No operator-facing behavior changed. The foundation is disabled and cannot be used for target execution.

### Guide Sections to Create or Update

No operator documentation update required.

### Screenshot Requirements

Screenshots required: No.

### Operator Workflow Notes

No runtime operator workflow is introduced. The future Pack that enables an isolated target or ND fake must create/update the DK operator guide if operator setup or troubleshooting becomes applicable.

## 17. Implementation Rules

### Target profile

- Add `declare(strict_types=1)` to each new PHP file.
- `DKTargetProfile` must be `final readonly`, versioned at `1`, and contain only `enabled: bool`, `environment: TargetEnvironment`, and `version: int`.
- Provide one config-construction boundary that accepts the resolved `dk.target` array, copies it defensively, and fails closed to disabled/unknown for missing, wrong-type, or unsupported values.
- Do not include profile ID, URL, selector, credential, secret reference, account name, payload, executor choice, component identity, or display text.

### Foundation adapters

- `DKTargetResourceAdapter` implements exactly `TargetReadinessProbe`, `TargetAccountResolver`, `TargetFixtureManager`, `TargetOracle`, and `TargetCleanup` using constructor-injected `DKTargetProfile`.
- Readiness behavior must follow section 13 exactly and return only accepted shared DTO factories.
- Account, fixture, oracle, and cleanup methods must use the exact fail-closed sentinel behaviors in section 13. They must not call the container, config facade, database, filesystem, browser, HTTP client, queue, clock, random source, logger, or external system.
- Do not throw raw target errors or create a DK-specific public exception. Shared coordinator normalization remains unchanged.

### Composition root

- `DKServiceProvider` remains the only DK composition root.
- Merge `Modules/DK/config/config.php` under config key `dk` before resolving `DKTargetProfile`.
- Bind the profile and adapter object as module-owned singletons. Register the same adapter object into each of the five slots of one `TargetResourceAdapters` aggregate.
- Attach registration through `afterResolving(TargetResourceRegistry::class, ...)` in the established provider pattern. Check `for('dk')` first so repeated callbacks do not duplicate registration; do not overwrite a pre-existing aggregate.
- Preserve the existing `AcceptanceAppRegistry` registration unchanged in behavior and cardinality.
- Use constructor injection and explicit registry extension. No `app()`/`resolve()` call may appear inside the profile or adapter classes.

### Scope boundary

- Keep all implementation under `Modules/DK/`.
- Preserve the empty `DKAcceptanceApp` catalog and current version `v1`.
- Do not create ND directories, component registrations, scenario metadata, prerequisites, mappings, or execution behavior.

## 18. Architecture Constraints

- DK depends on the accepted shared ports and DTOs; shared project/Core layers must not depend on DK.
- The registry is the approved plugin boundary. DK extends it from its service provider and must not modify registry/coordinator internals.
- Config is non-sensitive and fail-closed. It is not a secret store, target connection definition, or operator settings system.
- The foundation must not claim operational readiness: enabled testing/staging reaches only the accepted `resource_unavailable` account failure.
- No generic abstraction is added around the five existing ports. The first DK implementation is concrete and owner-local.
- Capability-specific ND behavior remains inside the future ND Component boundary and outside this Pack.

## 19. Validation Rules

- Prove exact profile construction for default, testing, staging, production, unknown, missing, and wrong-type config values.
- Prove readiness classification and retry/permanent/admin-action flags through the returned shared DTOs.
- Prove account, fixture, oracle, and cleanup sentinels return the exact accepted codes/classifications and preserve non-empty cleanup references as remaining.
- Boot the application with no dotenv file and prove `TargetResourceRegistry::for('dk')` returns exactly one aggregate whose five ports reference the intended DK adapter singleton.
- Execute the coordinator with default DK configuration and a closure that fails the test if invoked; assert rejection occurs at readiness with `unsafe_target`, no reference, no cleanup, and no external side effect.
- Override config in process to safe/disabled and safe/enabled profiles before resolving the singletons; prove `target_not_ready` and then `resource_unavailable` respectively, and prove fixture/execution/oracle are not called.
- Prove the existing empty DK App remains registered once and unchanged.
- Scan created/edited source and tests for URL schemes, Authorization headers, credential/token/password/cookie/session literals, browser/HTTP/queue/database/filesystem calls, `env(` outside config, service locator calls, and ND/notification/callback behavior.
- Validate PHP syntax, scoped Pint, exact changed-path allowlist, Markdown headings/references, and `git diff --check`.

## 20. Security Rules

- Never read, store, log, display, return, document, or commit real or usable DK credentials, account identifiers, URLs, selectors, tokens, cookies, sessions, Authorization values, callback secrets, provider payloads, personal data, or production values.
- Tests use only obvious inert sentinels and synthetic UUID/key data. No fake value may resemble a real account or target endpoint.
- No raw `PrerequisiteExecutionData` content may enter output, logs, exceptions, config, reports, Run records, or snapshot data.
- No network-capable dependency may be injected into the foundation adapter.
- The default disabled/unknown state must remain fail-closed after config cache/normal application boot.
- A discovered need for a real secret resolver, environment value, target URL, account, fixture payload, or external request stops this Pack.

## 21. Error Handling / Logging / Traceability Requirements

### Error Handling Impact

```text
Error Handling Impact: Yes — exact existing failures are selected by the DK adapters
API Error Contract Impact: No
UI / Admin UI Error Impact: No
Internal Exception Impact: No new public exception contract
External Integration Error Impact: No external integration exists
Callback / Inbound-event Error Impact: No
Logging Impact: No new event or context
Traceability Impact: No new identifier or propagation
Error Code Impact: No new, changed, or deprecated code
Security / Sensitive Data Impact: Yes — absence and omission must be proven
```

### Error Scenarios

| Scenario | Trigger / layer | Stable `error_code` | Classification | State / log / trace behavior | Required test |
|---|---|---|---|---|---|
| Unsafe or invalid target profile | production, unknown, missing, or invalid environment at DK readiness adapter | `unsafe_target` | rejected; non-retryable; permanent; admin action required | coordinator stops at readiness; existing operation log/IDs only; no target details | profile and integration tests |
| Safe target disabled | testing/staging with `enabled=false` at DK readiness adapter | `target_not_ready` | rejected; retryable; temporary; no admin action | coordinator stops at readiness; no downstream adapter or execution call | readiness and integration tests |
| Foundation account unavailable | testing/staging with `enabled=true` reaches account adapter | `resource_unavailable` | rejected; non-retryable; permanent; admin action required | coordinator stops at account; no reference and no cleanup | adapter and integration tests |
| Direct fixture sentinel | direct unit invocation before a later replacement | `fixture_setup_failed` | failed; retryable; temporary; admin action required | no reference; no target state | adapter unit test |
| Direct oracle sentinel | direct unit invocation before a later replacement | `oracle_failed` | failed; non-retryable; permanent for the execution; no admin action | no external observation or state mutation | adapter unit test |
| Non-empty cleanup rejected | direct cleanup invocation with any safe reference | `cleanup_failed` | failed; retryable; temporary; admin action required | all references remain; existing lifecycle ID only | adapter unit test |

Human-readable messages, HTTP status, UI state, audit records, and owner-managed persistence are not applicable. Existing CLI rendering may display the stable shared code; this Pack does not change that presentation.

### API Error Contract Requirements

No API error contract changes required.

### UI / Admin UI Error Requirements

No UI or Admin UI error changes required.

### Error Code Requirements

No error codes introduced or changed. Reuse only the codes and classifications in the table above exactly as defined by accepted project Pack 0012.

### Exception Requirements

No exception changes required. Config parsing and adapter methods return fail-closed shared DTOs and must not expose raw exceptions.

### External Integration Error Normalization Requirements

No external-error normalization changes required because no external integration or call exists.

### Logging Requirements

No logging changes required. Do not add DK-local logs. Existing `tms.acceptance.operation.completed` and `tms.acceptance.operation.failed` events remain the only operation-boundary logs and must retain their accepted allowlist.

### Traceability Requirements

No traceability changes required. Existing `lifecycle_id`, `operation_id`, `correlation_id`, complete hierarchy tuple, and optional prerequisite request ID remain authoritative; the foundation creates, persists, or exposes none of them independently.

### Sensitive Data and Masking Requirements

Sensitive data is omitted, not masked or persisted. Required tests must prove obvious password/token/URL/account/payload sentinels are absent from config, DTOs, registry state, exception text, captured logs, and serialized test output.

### Error Handling Test Requirements

For every row in the error-scenario table, assert the exact stable code, retryable/permanent/admin-action flags, lifecycle stage/status where applicable, downstream non-invocation, reference/cleanup state, existing trace identity preservation, and absence of sensitive sentinels.

### Operator Review Notes

Operator review is required before execution to confirm that the fail-closed behaviors are intentionally non-operational and that no real target readiness is implied.

### Required Follow-up Updates

- `DK-DECISION-0001` was accepted and the project owner-activation drift was resolved on 2026-10-10.
- The next DK/ND Pack must replace only the adapter behavior it explicitly owns and record fake Provider, simulated Callback, worker/time/audit oracle, and no-real-SMS behavior before implementation.

## 22. Data Model / Migration / Relationship Requirements

### Data / Migration Impact

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
Rollback Impact: No database rollback
```

No data model, migration, or relationship changes required.

### Tables to Create or Modify

No tables created or modified.

### Columns

No columns created or modified.

### Foreign Keys and Referential Actions

No foreign keys required.

### Model Relationships

No model relationships required.

### Snapshot Requirements

No snapshot fields required.

### Indexes and Constraints

No indexes or constraints required.

### Delete / Update Policy

Not applicable; no persisted entity is introduced or changed.

### Data Backfill / Migration Data Changes

No data backfill required.

### Rollback / Down Migration Requirements

No migration or database rollback exists.

### Validation Requirements

All migration, schema, relationship, index, backfill, and data-loss checks are not applicable. Validation must still prove that no database query or write occurs in the new profile/adapter tests.

## 23. Commenting Requirements

Follow `docs/ai/rules/COMMENTING-RULES.md`.

Add concise documentation comments only for:

- the fail-closed config normalization invariant;
- why account/fixture/oracle/cleanup intentionally reject until a later accepted Pack;
- why non-empty cleanup references cannot be reported as cleaned by the foundation.

Do not copy Pack prose into code or comment obvious interface methods.

## 24. Testing Requirements

### Test Quality Requirements

```text
Test Quality Required: Yes
Behavioral Assertions Required: Yes
Contract Assertions Required: Yes
Database Assertions Required: No
Side-effect Assertions Required: Yes — prove absence
Negative/Error Scenario Tests Required: Yes
Authorization/Permission Tests Required: No
Idempotency Tests Required: Yes — provider registration cardinality
Queue/Event/Job Assertions Required: No
External Integration Fake/Mock Required: No; no external integration may exist
Security/Secret Masking Tests Required: Yes — omission, not masking
```

### Behavior to Prove

The tests must prove:

- deterministic and fail-closed profile construction;
- exact shared DTO behavior for every foundation port;
- default registry wiring through a real Laravel application boot;
- single DK App and single DK target-resource aggregate registration;
- no downstream call after readiness/account rejection;
- unchanged empty DK catalog;
- no target, network, browser, queue, database, filesystem, or secret side effect;
- no sensitive sentinel in results, logs, exceptions, or config.

### Required Assertions

- Response contract: not applicable; assert typed DTOs and lifecycle data directly.
- Database state: no database interaction; no record assertion required.
- Status/state transition: exact lifecycle `failed` stage `readiness` or `account` and accepted classification flags.
- Queue/job/event dispatch: assert none is introduced; no queue/event fake needed unless implementation unexpectedly dispatches.
- Permission/auth behavior: not applicable; no endpoint or real authentication exists.
- Validation error behavior: invalid config fails to disabled/unknown and `unsafe_target`.
- External adapter/fake behavior: not applicable; no HTTP/browser/external adapter exists.
- Idempotency behavior: repeated provider resolution does not duplicate or replace DK registration.
- Security/masking behavior: raw sentinels are absent; no secret field exists to mask.

### Required Scenarios

- Happy path: safe/enabled readiness returns ready as a DTO, then the full coordinator stops at account with `resource_unavailable`; this is the intended foundation outcome, not target success.
- Invalid input: missing/wrong-type/unsupported config normalizes to disabled/unknown and rejects safely.
- Unauthorized: not applicable because no endpoint or user action exists.
- Forbidden: production and unknown environments return `unsafe_target` before downstream calls.
- Duplicate/idempotency: repeated registry resolution/provider callback preserves one DK aggregate.
- External-integration failure: not applicable because no integration exists.
- Callback/inbound-event duplicate: not applicable because no callback exists.
- Terminal state protection: not applicable because no owner-managed state is created.
- Missing config/settings: same fail-closed default as invalid config.
- Other: non-empty cleanup reports every reference as remaining and never claims deletion.

### Test Data Requirements

Use only constructed `TargetContext`, `PrerequisiteExecutionData`, `ResourceReference`, in-process config overrides, deterministic UUIDs, and obvious inert sentinels. No seed, migration, database, manual data, external fixture, or cleanup is required.

## 25. Acceptance Checklist

- [x] `DK-DECISION-0001` is accepted and project governance drift is resolved before implementation starts.
- [x] all changed implementation paths remain under `Modules/DK/`;
- [x] config defaults are exactly disabled/unknown and contain no environment-variable or secret lookup;
- [x] DK registers one five-port aggregate through the accepted registry extension point;
- [x] default and invalid config stop at readiness with `unsafe_target`;
- [x] safe/disabled config stops at readiness with `target_not_ready`;
- [x] safe/enabled config stops at account with `resource_unavailable` before fixture/execution/oracle;
- [x] direct fixture/oracle/cleanup sentinel behavior matches accepted codes and classifications;
- [x] existing DK App registration and empty catalog remain unchanged;
- [x] no ND Component, Provider, Callback, Scenario, mapping, or notification behavior exists;
- [x] no real target, browser, HTTP, queue, database, filesystem, secret, URL, account, or credential access occurred;
- [x] focused DK and shared regression tests, lint, Pint, path allowlist, documentation checks, and `git diff --check` pass.

## 26. Tests to Add

### `Modules/DK/tests/Unit/DKTargetProfileTest.php`

- Test name: `test_profile_normalizes_default_and_invalid_config_to_disabled_unknown`
  Purpose: prove invalid configuration cannot activate DK.
  Behavior proven: missing, wrong-type, and unsupported values produce one disabled/unknown profile.
  Main assertions: exact booleans, enum, version, deterministic repeated construction, and no raw input retained.
  Required setup: direct arrays only.
  Expected side effects: none.
  Negative/error cases: production, unknown string, unexpected keys, non-boolean enabled value.
  Related acceptance criteria: configuration and unsafe-target items.
- Test name: `test_profile_accepts_only_explicit_safe_and_unsafe_environment_values`
  Purpose: prove exact environment classification.
  Behavior proven: testing/staging/production/unknown map to the shared enum without aliases or case folding.
  Main assertions: exact enum and enabled state.
  Required setup: direct arrays.
  Expected side effects: none.
  Negative/error cases: mixed case and arbitrary environment names normalize closed.
  Related acceptance criteria: environment gate items.

### `Modules/DK/tests/Unit/DKTargetResourceAdapterTest.php`

- Test name: `test_readiness_uses_exact_profile_safety_and_enabled_rules`
  Purpose: prove readiness branching and classifications.
  Behavior proven: unsafe, not-ready, and ready outcomes are exact.
  Main assertions: environment, ready flag, stable code, retryable, permanent, and admin-action fields.
  Required setup: direct profiles and safe context.
  Expected side effects: none.
  Negative/error cases: production/unknown and disabled safe environment.
  Related acceptance criteria: first three lifecycle outcomes.
- Test name: `test_non_readiness_ports_remain_fail_closed_without_external_side_effects`
  Purpose: prevent accidental operational behavior.
  Behavior proven: account/fixture/oracle reject with exact shared DTOs; empty cleanup succeeds; non-empty cleanup retains every reference and fails.
  Main assertions: exact codes/classifications, reference lists, version, and no mutation of inputs.
  Required setup: constructed context, prerequisite data, outcome, and safe references.
  Expected side effects: none.
  Negative/error cases: non-empty cleanup and failed execution outcome.
  Related acceptance criteria: sentinel and cleanup items.

### `Modules/DK/tests/Feature/DKTargetFoundationIntegrationTest.php`

- Test name: `test_default_application_registers_one_fail_closed_dk_target_aggregate`
  Purpose: prove real container/module composition.
  Behavior proven: registry resolves DK once; all five ports are the same intended singleton; the DK App remains registered once.
  Main assertions: class identity, object identity, registry cardinality observable through duplicate-rejection behavior, App key, and empty catalog.
  Required setup: normal test application with no dotenv and default DK config.
  Expected side effects: none.
  Negative/error cases: repeated resolution cannot replace or duplicate the aggregate.
  Related acceptance criteria: provider wiring and App-preservation items.
- Test name: `test_dk_lifecycle_stops_before_execution_for_default_disabled_and_enabled_foundation_profiles`
  Purpose: prove end-to-end fail-closed coordinator behavior.
  Behavior proven: default invalid/unknown stops at readiness, safe/disabled stops at readiness, safe/enabled stops at account, and the execution closure is never called.
  Main assertions: lifecycle status/stage/code/classification, unchanged references, null execution/oracle/cleanup as applicable, and preserved lifecycle/correlation/tuple identity.
  Required setup: in-process config set before resolving profile/registry, constructed prerequisite data, closure that records invocation.
  Expected side effects: no Test/Step record, log leak, queue event, browser, HTTP request, or target call.
  Negative/error cases: every non-operational profile state.
  Related acceptance criteria: all fail-closed and security items.

## 27. Tests to Run

Only after separate operator execution approval.

Run Laravel tests through the accepted absent-dotenv bootstrap with process-local `APP_ENV=testing`, `DB_CONNECTION=sqlite`, and `DB_DATABASE=:memory:`:

```text
test --compact Modules/DK/tests/Unit/DKTargetProfileTest.php Modules/DK/tests/Unit/DKTargetResourceAdapterTest.php
test --compact Modules/DK/tests/Feature/DKTargetFoundationIntegrationTest.php Modules/DK/tests/Unit/DKAcceptanceAppTest.php tests/Feature/DKModuleBootstrapTest.php
test --compact tests/Unit/TargetResourceCoordinatorTest.php tests/Unit/TargetResourceContractSafetyTest.php
acceptance:list --app=dk --json
acceptance:plan --app=dk --json
```

Guarded form for each Laravel command:

```powershell
php -r 'define("LARAVEL_START", microtime(true)); require "vendor/autoload.php"; $dkPack1App = require "bootstrap/app.php"; $dkPack1App->loadEnvironmentFrom("__tms_dk_pack_0001_no_dotenv__"); exit($dkPack1App->handleCommand(new \Symfony\Component\Console\Input\ArgvInput));' -- <arguments>
```

Also run:

- `php -l` on every created/edited PHP file;
- scoped `vendor/bin/pint --test` on those PHP files;
- exact changed-path allowlist validation against sections 8–9 plus permitted execution-reporting files;
- static forbidden-reference scans defined in sections 19–20;
- Pack/Decision heading, status, path, index, and reference validation;
- `git diff --check` and post-execution Git status/diff inspection.

Do not run migrations, seeders, database writes outside isolated tests, queue workers, browsers, HTTP requests, target commands, Composer/npm commands, config/cache commands, external network commands, or destructive commands.

## 28. Expected Output

- one accepted-ready `DKTargetProfile` contract with disabled/unknown defaults;
- one fail-closed five-port DK adapter implementation;
- one owner-local service-provider registration path for `TargetResourceRegistry` that preserves existing App registration;
- two DK Unit test files and one DK Feature integration test;
- unchanged empty DK App catalog and unchanged shared/Core source;
- after approved execution only, a DK-local Run Report and synchronized Pack/Run indexes;
- explicit remaining follow-up for the separate ND fake Provider/Callback stage.

Documentation maintenance for this generated Draft includes the accepted DK Decision, DK Decision index, this Pack, DK Pack index, and the operator-approved correction of the two stale project-governance statements. No Run, Review, Canonical, Guide, Change Request, or Remediation record is fabricated.

## 29. Operator Execution Checklist

### Before AI Execution

- Verify `DK-DECISION-0001` remains accepted and the project owner-activation correction remains in place.
- Provide separate authorization to execute this Pack.
- Confirm that safe/enabled still stops at `resource_unavailable` and that this Pack is not expected to connect to a target.
- Confirm exact files in sections 8–9 and the absence of ND/Provider/Callback scope.

### After AI Execution

- Inspect the default, safe/disabled, and safe/enabled lifecycle results and confirm the execution closure was never called.
- Verify the five registry slots use the intended DK adapter singleton without duplicate registration.
- Inspect config, DTOs, captured logs, and diff for credentials, URLs, selectors, payloads, account identifiers, or target/network code and confirm none exists.
- Confirm the DK catalog remains empty and stage 12 behavior was not pulled forward.

## 30. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md` and include the documentation-maintenance block required by `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`.

Report exact profile parsing, five-port behavior, provider wiring, lifecycle stages/codes/classifications, non-invocation evidence, sensitive-data scans, commands/results, changed paths, governance-drift resolution reference, Decision acceptance evidence, and remaining stage-12 follow-up. Do not claim target readiness, target access, or external validation.

## 31. Review Checklist

Review:

- owner and local Pack numbering;
- accepted Decision prerequisite and resolved project governance drift;
- fail-closed config normalization;
- exact reuse of shared ports, DTOs, codes, and classifications;
- provider-only composition and duplicate protection;
- no shared/Core/source-boundary change;
- no service locator or speculative local interface;
- no ND/Provider/Callback/scenario scope;
- no external I/O or sensitive value;
- behavioral test strength and anti-false-positive closures;
- exact file allowlist and documentation index synchronization.

## 32. Rollback / Safety Notes

Implementation rollback is source-only and limited to the exact created/edited files in sections 8–9. No database, target, queue, browser, network, or external cleanup should exist.

Before commit, remove only Pack-created source/tests and restore the two edited DK implementation files through a reviewed targeted patch. Do not use broad reset/restore/clean commands. Preserve the Pack/Run history according to lifecycle rules if execution has already been recorded.

If any real target call or resource is observed, stop immediately and report a safety violation. Do not attempt external cleanup under this Pack.

## 33. Stop Conditions

Stop before implementation if:

- `DK-DECISION-0001` is superseded, revised without Pack normalization, or rejected;
- a new owner-activation conflict appears between root/project routing and the active DK owner evidence;
- Pack 0016 or any required accepted shared/Core predecessor is missing, revoked, superseded, or contradicted by current source;
- implementation requires a shared project or Core edit, a new shared error code, changed classification, changed registry/coordinator contract, or changed App hierarchy contract;
- implementation requires Profile `extra`, a database change, environment variable, `.env` read, Admin Setting, secret resolver, target URL, credential, account, selector, payload, browser, HTTP request, queue, filesystem mutation, or external network;
- a real account/fixture/oracle/cleanup behavior is needed rather than the approved sentinel behavior;
- any ND Component, fake Provider, Callback, worker/time/audit oracle, corpus, mapping, scenario, notification, or SMS behavior is required;
- any required file falls outside sections 8–9 other than mandatory directly related governance maintenance;
- tests cannot prove non-invocation and sensitive-data absence with fake/constructed inputs only;
- unrelated working-tree changes overlap the exact Pack files;
- separate operator execution approval is absent.

No commit is authorized by this Pack.

## 34. Open Questions

No open design or governance question remains. Separate operator authorization is still required before Pack execution.
