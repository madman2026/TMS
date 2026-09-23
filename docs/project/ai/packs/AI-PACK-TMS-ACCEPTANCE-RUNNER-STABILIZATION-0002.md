# AI-PACK-TMS-ACCEPTANCE-RUNNER-STABILIZATION-0002 — Acceptance Runner minimal stabilization

Status: `accepted — technical validation passed; operator accepted`

Generated: `2026-09-23`

Decision: `docs/project/ai/decisions/TMS-DECISION-0002-generic-code-based-acceptance-apps.md`

Execution: completed `2026-09-23` after operator approval

Run: `docs/project/ai/runs/AI-PACK-TMS-ACCEPTANCE-RUNNER-STABILIZATION-0002-run-001.md`

Review: `docs/project/ai/reviews/REVIEW-AI-PACK-TMS-ACCEPTANCE-RUNNER-STABILIZATION-0002.md`

Accepted: `2026-09-23` by Human Operator

Commit: authorized by Human Operator; recorded by the commit containing this Pack

## 1. Task ID

`AI-PACK-TMS-ACCEPTANCE-RUNNER-STABILIZATION-0002`

## 2. Task Title

Stabilize the minimum generic Acceptance Runner foundation for code-based Apps.

## 3. Goal

Create the smallest reliable execution foundation that proves TMS can run code-defined Acceptance scenarios through Playwright PHP without depending on Martfury, DieselKhodro, an HTTP request, or a specific target App.

This Pack must:

1. establish capability-neutral App and Scenario contracts in Core;
2. correct browser launch, context, page, assertion, step, and cleanup behavior;
3. persist a minimal, safe execution history through the TMS project layer;
4. remove active target-specific and obsolete runner code;
5. prove the foundation with local, headless, non-networked tests.

This Pack is stabilization, not a complete TMS product implementation.

## 4. Context

TMS currently contains early runner code and persistence models, but no active target-system App and no runnable Acceptance entry point. The historical Auth and Ecommerce modules were removed in accepted cleanup commit `a6e1574` and must remain removed.

Read-only inspection on 2026-09-23 found:

- `TestAccessMiddleware` and request-option resolution are globally appended, so ordinary application requests depend on an Acceptance profile;
- `php artisan test --compact` reports one passing and one failing test because the global middleware queries a missing `profiles` table for the default Feature test;
- `TestContext` reads the nonexistent `Profile::data` attribute, resolves behavior through the current HTTP request, and combines persistence with browser construction;
- `HasStep` closes the browser context after every step and writes a `status` field that the Step model/table do not currently own;
- context/device options are not nested as required by the installed Playwright PHP `v1.5.0` API;
- custom Node/runtime traits are broken or tied to one Windows user even though the installed package already provides cross-platform Node resolution;
- Core contains the DieselKhodro-specific `DKAPI` client;
- the Playwright PHP package is declared with `*` under `require-dev`, although browser execution is a runtime responsibility of TMS;
- generated Composer autoload state still references removed Auth and Ecommerce paths;
- unused Codeception suite files remain although Codeception is not installed;
- no behavioral tests cover runner lifecycle, critical-step handling, persistence, Core isolation, or a real local Playwright smoke flow.

Operator intent: TMS manages Acceptance tests for multiple systems; tests are implemented in code as Apps using Laravel and Playwright PHP. The former Auth structure is reference input only, not an implementation to restore. Only debugging and minimum stabilization are authorized now.

## 5. Related Release / Phase

Not applicable. This is a pre-Release runtime-foundation Pack.

## 6. Related Epic / Feature / Story

Generic multi-system Acceptance execution foundation.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0002-generic-code-based-acceptance-apps.md`
- `docs/project/references/TMS-SOURCE-STRUCTURE-SUMMARY.md`
- Git history at `a6e1574^` for the removed Auth implementation, read-only and reference-only
- installed package metadata for `playwright-php/playwright` `v1.5.0`
- `vendor/playwright-php/playwright/README.md`
- `vendor/playwright-php/playwright/src/Playwright.php`
- `vendor/playwright-php/playwright/src/Node/NodeBinaryResolver.php`
- `vendor/playwright-php/playwright/src/Testing/functions.php`

No external documentation or network fetch is required for this Pack.

## 8. Files to Create

### Core runtime and contracts

- `Modules/Core/app/Contracts/AcceptanceApp.php`
- `Modules/Core/app/Contracts/AcceptanceScenario.php`
- `Modules/Core/app/Data/RunOptions.php`
- `Modules/Core/app/Data/RunResult.php`
- `Modules/Core/app/Exceptions/AcceptanceExecutionException.php`
- `Modules/Core/app/Services/AcceptanceRunner.php`
- `Modules/Core/app/Services/PlaywrightBrowserFactory.php`

### Project orchestration and persistence

- `app/Services/AcceptanceRunService.php`
- `database/migrations/2026_09_23_000001_add_acceptance_execution_fields_to_tests_and_steps_tables.php`

### Tests

- `Modules/Core/tests/Unit/AcceptanceRunnerTest.php`
- `Modules/Core/tests/Feature/PlaywrightAcceptanceSmokeTest.php`
- `tests/Feature/AcceptanceRunPersistenceTest.php`
- `tests/Feature/ApplicationHealthTest.php`
- `tests/Unit/CoreIsolationTest.php`

### Execution record

- `docs/project/ai/runs/AI-PACK-TMS-ACCEPTANCE-RUNNER-STABILIZATION-0002-run-001.md`, only after implementation, required fixes, tests, scope review, and the pre-commit reporting gate are complete

## 9. Files to Edit

### Root application

- `bootstrap/app.php`
- `app/Contracts/BaseAction.php`
- `app/Models/Test.php`
- `app/Models/Step.php`
- `composer.json`
- `composer.lock`
- `phpunit.xml`

### Core

- `Modules/Core/app/Contracts/BrowserFactory.php`
- `Modules/Core/app/Contracts/Device.php`
- `Modules/Core/app/Contracts/StepResult.php`
- `Modules/Core/app/Contracts/TestContext.php`
- `Modules/Core/app/Providers/CoreServiceProvider.php`
- `Modules/Core/app/Traits/Assertion.php`
- `Modules/Core/app/Traits/HasStep.php`
- `Modules/Core/config/config.php`

### Files explicitly authorized for deletion

- `app/Http/Middleware/ResolveRequestOptionsMiddleware.php`
- `app/Http/Middleware/TestAccessMiddleware.php`
- `config/dieselkhodro.php`
- `Modules/Core/app/Contracts/DKAPI.php`
- `Modules/Core/app/Contracts/RequestOptions.php`
- `Modules/Core/app/Traits/NodeResolver.php`
- `Modules/Core/app/Traits/RuntimeEnv.php`
- `Modules/Core/app/Traits/WebDriverFactory.php`
- `tests/Acceptance.suite.yml`
- `tests/Functional.suite.yml`
- `tests/Unit.suite.yml`
- `tests/_output/.gitignore`
- `tests/Feature/ExampleTest.php`
- `tests/Unit/ExampleTest.php`

### Lifecycle and index maintenance

- `docs/project/ai/packs/AI-PACK-TMS-ACCEPTANCE-RUNNER-STABILIZATION-0002.md`, only for lifecycle status and execution references
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md`

## 10. Files to Read / Reference

### Governance and ownership

- `AGENTS.md`
- `docs/ai/start/START-HERE.md`
- `docs/ai/start/CONTEXT-MAP.md`
- `docs/project/PROJECT-DOCS-INDEX.md`
- `docs/project/ai/TMS-AI-PROFILE.md`
- `Modules/Core/AGENTS.md`
- `Modules/Core/docs/ai/manifest.yaml`
- `Modules/Core/docs/ai/GOVERNANCE-PROFILE.md`
- `docs/ai/rules/AI-EXECUTION-RULES.md`
- `docs/ai/rules/SCOPE-CONTROL-RULES.md`
- `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`
- `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md`
- `docs/ai/rules/GIT-AND-COMMIT-RULES.md`
- `docs/ai/rules/REPORTING-RULES.md`
- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`

### Current implementation

- `package.json`
- `package-lock.json`
- `modules_statuses.json`
- `routes/api.php`
- `routes/web.php`
- `app/DeviceTypeEnum.php`
- `app/DriverTypeEnum.php`
- `app/InternetSpeedEnum.php`
- `app/TestStatusEnum.php`
- `app/Models/Profile.php`
- `app/Contracts/BaseService.php`
- `database/migrations/2026_02_17_131201_create_profiles_table.php`
- `database/migrations/2026_02_17_132111_create_tests_table.php`
- `database/migrations/2026_02_19_101058_create_steps_table.php`
- `Modules/Core/composer.json`
- `Modules/Core/module.json`

Files under `vendor/playwright-php/playwright/` listed in section 7 are read-only package evidence and must not be edited.

## 11. Configuration / Settings Requirements

Configuration / Settings Impact: Yes, limited to versioned runtime defaults and Composer dependency classification.

- Move `playwright-php/playwright` from `require-dev` to `require` and constrain it to the compatible `^1.5` line.
- Update only the Composer lock metadata required by that root dependency classification. Package versions must not change during this Pack.
- Keep `@playwright/test` and all npm files unchanged; removal or adoption of the JavaScript package is outside this Pack.
- `Modules/Core/config/config.php` may define non-sensitive defaults for headless execution and timeouts. It must not contain target URLs, credentials, local absolute paths, or browser-cache paths.
- No new environment variable is required.
- No `.env` or `.env.example` change is allowed.
- No Admin Settings, database-backed settings UI, queue, cache, or log-channel configuration is required.
- Node.js 20 or later and an installed Chromium browser are test/development prerequisites. The Pack may verify them read-only; it must not download or install a browser automatically.
- If browser installation is missing, stop and request operator approval for a separate exact installation command rather than broadening this Pack.

Settings validation:

- Composer validation succeeds.
- Locked package versions are unchanged.
- Core defaults are type/range validated before browser launch.
- Invalid browser engine, timeout, viewport, or device options fail with `acceptance_configuration_invalid`.

## 12. Do Not Change

- Do not recreate Auth or Ecommerce modules or their routes, controllers, services, actions, documentation owners, or generated API documentation.
- Do not add a Martfury, DieselKhodro, example, demo, or other real target-system App.
- Do not add API routes, web routes, Artisan execution commands, Scribe output, UI, Admin UI, dashboard, scheduler, queue, parallel runner, retry engine, trace/video/screenshot artifacts, or browser matrix execution.
- Do not edit `package.json`, `package-lock.json`, `.env`, `.env.example`, user/profile migrations, User/Profile models, application views/assets, or generated Scribe files.
- Do not change Laravel, PHPUnit, nWidart Modules, Scribe, Vite, or npm package versions.
- Do not read or use real credentials, tokens, profile secrets, external URLs, or production data.
- Do not run migrations against a persistent, shared, unknown, or production database.
- Do not modify accepted Pack 0001, its accepted Run, Review, or Decision 0001.
- Do not perform unrelated formatting, naming cleanup, contract consolidation, model renaming, enum relocation, or response-contract refactoring.

## 13. Clarification Questions Before Implementation

No blocking clarification remains.

Approved Pack-local interpretations:

1. An Acceptance App is a code-level boundary represented by a Core contract; this Pack does not create a production App registry or a real App implementation.
2. A Scenario yields ordered `StepResult` objects. `critical` controls whether later steps execute; any failed step makes the final Run failed.
3. Browser context lifetime is one Scenario Run. It is created once and closed exactly once in `finally` on success, failure, or exception.
4. The database persists execution history, not executable scenario definitions.
5. `Test` remains the current persistence model name for this Pack; renaming it to `TestRun` is deferred to avoid a broad schema/domain refactor.

## 14. Multilingual / Translation Requirements

No multilingual or translation changes required.

This Pack introduces no user-facing UI or API messages. Stable error codes are language-neutral. Internal exception fallback text and logs must remain technical and must not be presented as translated operator content.

## 15. UI / Admin UI Requirements

No UI or Admin UI changes required.

No page, view, menu, form, table, filter, action, button, dashboard, permission, or RTL/LTR behavior may be introduced.

## 16. Operator Documentation Requirements

No operator documentation update required.

The Run Report must record the local prerequisites and exact validation results. A user guide is deferred until a real App and operator entry point exist.

## 17. Implementation Rules

- Follow actual Laravel 12, nWidart Modules, PHPUnit 11, and installed Playwright PHP 1.5 APIs.
- Keep Core execution code independent of root Eloquent models, Laravel HTTP Request, request attributes, global container request state, and target-specific configuration.
- `AcceptanceApp` exposes stable App identity and its code-defined scenarios.
- `AcceptanceScenario` exposes stable scenario identity/name and yields ordered `StepResult` values for a supplied `TestContext`.
- `RunOptions` contains validated capability-neutral launch/context values only. It must separate browser launch options from browser-context/device options.
- Change `BrowserFactory` into an injectable contract and bind it to `PlaywrightBrowserFactory` in `CoreServiceProvider`.
- `TestContext` owns the browser context and page for one Run and provides idempotent close behavior. It must not create itself from a request or Profile model.
- `AcceptanceRunner` must create one context, consume Scenario steps, stop after a failed critical step, continue after a failed non-critical step, compute final status/duration, and close resources in `finally`.
- `HasStep` must create/return `StepResult` only. It must not close browser resources, persist Eloquent records, resolve requests, or directly mutate Run status.
- `Assertion` must delegate to the installed Playwright assertion/auto-waiting API; remove custom polling loops.
- `AcceptanceRunService` owns project persistence. It must create the pending Test record before execution, call Core without holding an open database transaction across browser work, and use a short final transaction for Test/Step results.
- Persist only structural, explicitly safe result fields. Do not persist arbitrary action return payloads, cookies, headers, credentials, tokens, raw external responses, stack traces, or full Profile `extra` data.
- Use Composer-generated autoload. Do not edit files under `vendor/`.
- Apply only targeted formatting to files changed by this Pack.

## 18. Architecture Constraints

- Follow `TMS-DECISION-0002`.
- Dependency direction is project orchestration/App code → Core contracts/runtime. Core must not depend on root project models, middleware, requests, or a target App.
- Shared names must remain capability-neutral. `DK`, `DieselKhodro`, `Martfury`, target Auth selectors, and target URLs are forbidden in active Core/runtime code.
- Target-specific APIs and raw external errors belong inside a future target App adapter, never Core.
- Tests are source code. The database may identify and record Apps, Scenarios, Runs, and Steps but must not become a dynamic workflow language in this Pack.
- The historical Auth code is evidence only. Do not copy its hardcoded selectors, URLs, credential access, controller, route, or service implementation.
- Do not add more extension layers than required by the contracts and tests listed in this Pack.

## 19. Validation Rules

- Run syntax checks for every changed PHP file.
- Run targeted Pint in check-only mode; formatting fixes may affect only Pack-scoped PHP files.
- Validate Composer metadata without changing packages.
- Confirm Composer package versions before and after the allowed lock metadata update are identical.
- Regenerate Composer autoload once after deletions/new classes, then confirm generated autoload has no Auth/Ecommerce paths.
- Run the complete PHPUnit suite with SQLite `:memory:` only.
- The Playwright smoke test must use headless Chromium and local in-memory page content. It must not navigate to an external URL or start a web server.
- Run a forbidden-reference scan across active source/config/tests for target-specific identifiers.
- Confirm `php artisan route:list --except-vendor` contains no new Acceptance endpoint.
- Confirm `php artisan module:list` contains Core only.
- Confirm no npm command, browser installation, external network request, persistent migration, queue, cache, Scribe, asset build, or server command ran.

Allowed mutating commands during execution:

- `composer update --lock --no-install --no-interaction` with Composer network access disabled, only after the scoped `composer.json` change and only if it preserves every locked package version;
- `composer dump-autoload --no-interaction`, only to regenerate autoload after scoped source deletions/additions;
- targeted Pint formatting only for Pack-scoped PHP files if check-only validation reports formatting failures.

If either Composer command proposes or produces a package-version change, stop and restore only the Pack-produced Composer changes using a targeted patch; do not use broad Git restore/reset commands.

## 20. Security Rules

- Never read, store, log, return, document, or commit real passwords, cookies, Authorization headers, tokens, OTPs, service secrets, profile secrets, or external payloads.
- Core and test fixtures must use only `https://example.test`, `example-token`, `example-sensitive-value`, or similarly obvious placeholders.
- The local browser smoke test must use page content created inside the test and no production/external URL.
- Raw exception messages and stack traces must not be persisted in Test/Step data or exposed through a response.
- Structured logs may include Run ID, App key, Scenario key, Step name, stable error code, retryability, critical flag, and exception class. They must omit Profile `extra`, result payloads, URLs with query strings, headers, cookies, and credentials.
- Step result data persisted by this Pack is allowlisted structural metadata only; arbitrary scenario/action results remain in memory.
- No secret migration, seed, fixture, screenshot, trace, video, or HTML dump may be created.

## 21. Error Handling / Logging / Traceability Requirements

### Error Handling Impact

Error handling, logging, execution status, and traceability are modified by this Pack.

### Error Scenarios

1. Invalid run configuration
   - Trigger: unsupported browser engine or invalid timeout/context option.
   - Layer: Core validation before launch.
   - `error_code`: `acceptance_configuration_invalid`.
   - Classification: permanent, non-retryable, operator/developer action required.
   - Result: no browser starts; project Test becomes `failed` if it was already created.
   - Log level: warning.

2. Browser startup failure
   - Trigger: Node/browser executable unavailable or Playwright launch failure.
   - Layer: `PlaywrightBrowserFactory` / `AcceptanceRunner`.
   - `error_code`: `acceptance_browser_start_failed`.
   - Classification: potentially temporary, retryable after environment correction.
   - Result: Test becomes `failed`; no Step is fabricated; close is attempted for any partially created resource.
   - Log level: error.

3. Step failure
   - Trigger: action or assertion throws during a Step callback.
   - Layer: `BaseAction` / `HasStep`.
   - `error_code`: `acceptance_step_failed`.
   - Classification: permanent for the current Run, not automatically retried.
   - Result: Step becomes `failed`; Run becomes `failed`; later steps execute only when the failed Step is non-critical.
   - Log level: warning for normalized Step failure.

4. Unexpected Scenario failure
   - Trigger: Scenario throws outside normalized Step execution or yields an invalid value.
   - Layer: `AcceptanceRunner`.
   - `error_code`: `acceptance_scenario_failed`.
   - Classification: permanent for the current Run, developer action usually required.
   - Result: Run becomes `failed`; browser closes in `finally`.
   - Log level: error.

5. Result persistence failure
   - Trigger: final Test/Step transaction fails.
   - Layer: `AcceptanceRunService`.
   - `error_code`: `acceptance_result_persistence_failed`.
   - Classification: potentially temporary, operator/developer action required; no automatic retry in this Pack.
   - Result: rollback the short final transaction; retain the pre-created Test record where possible and mark failure through a safe best-effort update without hiding the original persistence exception.
   - Log level: critical if final status cannot be recorded, otherwise error.

### API Error Contract Requirements

No API error contract changes required.

### UI / Admin UI Error Requirements

No UI or Admin UI error changes required.

### Error Code Requirements

Error codes introduced:

- `acceptance_configuration_invalid`
- `acceptance_browser_start_failed`
- `acceptance_step_failed`
- `acceptance_scenario_failed`
- `acceptance_result_persistence_failed`

These codes are stable, language-neutral, persisted only where applicable, and used consistently in results and structured logs.

### Exception Requirements

- `AcceptanceExecutionException`
  - Purpose: normalize Core setup/execution failures with a stable error code and retryability flag.
  - Thrown by: `PlaywrightBrowserFactory` and `AcceptanceRunner`.
  - Handled by: `AcceptanceRunService` or the calling test.
  - Raw message safe for UI/API: No.

Step callback failures are normalized into `StepResult` and are not rethrown solely to control normal critical-step flow.

### External Integration Error Normalization Requirements

No external integration is implemented by this Pack. `DKAPI` and its configuration are removed. Future App adapters must define their own normalization in their own Pack.

### Logging Requirements

- Event `tms.acceptance.run.failed`
  - Level: error, or critical when final persistence cannot be recorded.
  - Required context: `test_id`, `app_key`, `scenario_key`, `error_code`, `retryable`, `exception_class`.
- Event `tms.acceptance.step.failed`
  - Level: warning.
  - Required context: `test_id`, `app_key`, `scenario_key`, `step_name`, `error_code`, `critical`, `exception_class` when available.

Sensitive fields listed in section 20 must be excluded.

### Traceability Requirements

- Existing Test primary key is the Run identifier for this Pack.
- The Run identifier is created before browser execution.
- It is propagated to project persistence and log context but not required inside capability-neutral Core objects.
- `app_key` and `scenario_key` are stored on the Test record as execution-time identity snapshots.
- No request ID, correlation ID, external reference, API response, or UI display is introduced.

### Sensitive Data and Masking Requirements

- Profile `extra`: omit from logs and result snapshots.
- Cookies, headers, tokens, OTPs, passwords, and Authorization values: omit entirely.
- Raw exception text: do not persist; logs may include exception class and a safe normalized summary only.
- Arbitrary Step return data: do not persist in this Pack.

### Error Handling Test Requirements

Tests must prove stable error codes, correct Run/Step status, critical/non-critical control flow, browser cleanup, safe persistence, structured log context, and absence of sample secrets from persisted data/log context.

### Operator Review Notes

No separate operator error-handling review is required if all specified tests pass and the Run Report contains no raw sensitive values.

### Required Follow-up Updates

- Define target-specific external error normalization only when the first real App adapter is introduced.
- Define operator-facing translated messages only when an API, command output contract, or UI is added.

## 22. Data Model / Migration / Relationship Requirements

Data / Migration Impact: Yes
New Tables Required: No
Existing Tables Modified: Yes — `tests`, `steps`
Foreign Keys Required: No new foreign keys
Model Relationships Required: Existing relationships retained
Indexes / Unique Constraints Required: Yes
Data Backfill Required: No
Soft Delete / Retention Impact: Existing behavior retained; follow-up required
Audit / History Impact: Yes — minimum Run/Step identity and failure history
Rollback Impact: Yes

### Tables to Create or Modify

- `tests`: add execution identity and final normalized failure metadata.
- `steps`: add normalized Step outcome metadata.

### Columns

`tests`:

- `app_key`: string length 100, nullable for legacy rows, required by application service for new Runs, indexed.
- `scenario_key`: string length 150, nullable for legacy rows, required by application service for new Runs, indexed with `app_key` and `created_at`.
- `error_code`: string length 100, nullable, not unique.

`steps`:

- `status`: string length 30, nullable for legacy rows, required for new Step records.
- `critical`: boolean, non-null, default `true`.
- `error_code`: string length 100, nullable.
- `error_message`: text, nullable; only safe normalized text may be stored.

Do not change existing duration column types in this Pack.

### Foreign Keys and Referential Actions

No new foreign keys are added.

Existing `steps.test_id → tests.id` cascade behavior is retained because Step records are constituent parts of one Test Run and have no independent lifecycle after an explicitly deleted Run.

Existing `tests.profile_id → profiles.id` cascade behavior is not changed by this Pack. Because Test/Step history may later require retention independent of Profile deletion, its policy must be decided before any profile-deletion feature is introduced.

### Model Relationships

- `Test::steps`: existing `hasMany(Step::class)` using `steps.test_id`; inverse `Step::test` retained.
- `Profile::tests`: existing `hasMany(Test::class)` using `tests.profile_id`; inverse `Test::profile` retained.

No relationship method is added or renamed.

### Snapshot Requirements

Snapshot Required: Yes

- `tests.app_key`: captures the code App identity at Run creation; non-sensitive.
- `tests.scenario_key`: captures the code Scenario identity at Run creation; non-sensitive.

Full Run options, Profile data, credentials, arbitrary results, page content, and external payloads must not be snapshotted in this Pack.

### Indexes and Constraints

- Composite index on `tests(app_key, scenario_key, created_at)` for run-history lookup.
- Composite index on `steps(test_id, status)` for per-Run outcome lookup.
- No new unique or idempotency constraint because no public execution command/API exists in this Pack.

### Delete / Update Policy

- No user/admin deletion behavior is introduced.
- Existing referential actions remain as documented above.
- Profile-to-Test history retention is a required future decision before deletion management exists.

### Data Backfill / Migration Data Changes

No data backfill required. New identity/status columns remain nullable for pre-existing rows. Tests must use a fresh in-memory database.

### Rollback / Down Migration Requirements

The down migration must remove the two new indexes and only the columns introduced by this Pack. It must not delete Test or Step rows. Rollback on a database containing new Run metadata loses only those added metadata fields and therefore requires explicit operator approval outside the test environment.

### Validation Requirements

- Migration up/down compiles and runs against SQLite `:memory:` in tests.
- New columns, nullability, defaults, indexes, model fillable fields, and casts match this section.
- Existing relationships still resolve.
- No persistent database migration command is run.

## 23. Commenting Requirements

Follow `docs/ai/rules/COMMENTING-RULES.md`.

Add comments only where lifecycle ownership, safe persistence, or Playwright launch-versus-context option separation is not clear from code. Do not add tutorial comments or comments restating method names.

## 24. Testing Requirements

Test Quality Required: Yes
Behavioral Assertions Required: Yes
Contract Assertions Required: Yes
Database Assertions Required: Yes
Side-effect Assertions Required: Yes
Negative/Error Scenario Tests Required: Yes
Authorization/Permission Tests Required: No — no protected entry point is introduced
Idempotency Tests Required: Yes — resource close must be idempotent
Queue/Event/Job Assertions Required: No
External Integration Fake/Mock Required: No external integration exists
Security/Secret Masking Tests Required: Yes

### Behavior to Prove

- a two-step Scenario uses one browser context/page and both Steps execute in order;
- a failed critical Step stops later Steps, while a failed non-critical Step permits continuation but still fails the Run;
- browser context closes exactly once on success, normalized failure, and unexpected exception;
- launch and context/device options are sent to the correct Playwright option layer;
- package-native assertions provide auto-waiting without custom polling loops;
- persistence creates a pending Test before execution and stores final Test/Step state afterward without wrapping browser work in a database transaction;
- normalized failures do not persist raw exception text or sample secrets;
- ordinary application health requests no longer require a Profile;
- Core contains no root model/request or target-system dependency;
- an actual local headless Chromium smoke flow passes without network access.

### Required Assertions

- Result contract: App/scenario identity, final status, duration, ordered Step results, critical flag, stable error code.
- Database state: Test and Step fields, relationships, identity snapshots, final statuses, and safe error metadata.
- Status transition: pending → finished or failed; no finished Run after a failed Step.
- Side effects: later critical-path Steps skipped correctly and context closed once.
- Security: sample password/token/cookie/raw exception string absent from persisted data and log context.
- Boundary: no forbidden Core imports/config keys/target names.

### Required Scenarios

- Happy path: two passing Steps.
- Non-critical failure: subsequent Step runs and Run fails.
- Critical failure: subsequent Step does not run and Run fails.
- Unexpected Scenario exception: normalized failure and cleanup.
- Invalid RunOptions: failure before browser launch.
- Browser startup failure: stable normalized error through a fake BrowserFactory.
- Persistence failure: final transaction rollback and structured failure log.
- Actual Chromium smoke: local content, interaction/assertion, headless cleanup.
- Authorization/forbidden/duplicate external request: not applicable because this Pack adds no execution endpoint.

### Test Data Requirements

- Use model factories and SQLite `:memory:` only for project persistence tests.
- Use anonymous/fake App, Scenario, and BrowserFactory implementations inside tests; do not create a production sample App.
- Use local HTML/page content and placeholder values only.
- No cleanup of external systems is required; browser resources must close within each test.

## 25. Acceptance Checklist

- [x] Decision 0002 remains the controlling architecture decision.
- [x] Core contains generic App/Scenario/Runner contracts and no target-system behavior.
- [x] Auth and Ecommerce remain absent.
- [x] `DKAPI`, DieselKhodro config, request-coupled middleware, and broken custom runtime traits are removed.
- [x] Playwright PHP is a pinned runtime dependency and no package version changed unexpectedly.
- [x] stale generated Composer autoload references to Auth/Ecommerce are gone.
- [x] browser launch options and context/device options are separated correctly.
- [x] one Scenario Run uses one browser context and closes it exactly once in `finally`.
- [x] Step handling no longer closes the browser or writes undeclared fields.
- [x] package-native Playwright assertions replace manual polling.
- [x] Test/Step persistence records safe identity, status, duration, criticality, and normalized errors.
- [x] no transaction remains open during browser execution.
- [x] global application requests no longer require an Acceptance Profile.
- [x] unused Codeception suite files and placeholder tests are removed.
- [x] the full PHPUnit suite and local headless Chromium smoke pass.
- [x] no external URL, real credential, token, cookie, OTP, or raw payload was used or persisted.
- [x] no API, command, UI, queue, scheduler, real App, or maximal feature was added.
- [x] Run Report and lifecycle indexes are completed before commit.
- [x] Human Operator performed the separate post-execution acceptance gate.

## 26. Tests to Add

1. `AcceptanceRunnerTest`
   - Purpose: prove lifecycle and critical-step control without launching a browser.
   - Behavior: ordered Steps, critical/non-critical handling, stable errors, one cleanup.
   - Setup: fake BrowserFactory, fake context/page, anonymous Scenario.
   - Negative cases: invalid options, startup failure, unexpected Scenario exception.
   - Acceptance criteria: lifecycle, result contract, and cleanup items in section 25.

2. `PlaywrightAcceptanceSmokeTest`
   - Purpose: prove compatibility with installed Playwright PHP and Chromium.
   - Behavior: launch headless, create local page content, perform an interaction, assert text with package-native assertions, execute a second Step in the same context, close cleanly.
   - Setup: no server and no external URL.
   - Negative case: one failing assertion verifies normalized failure and cleanup.
   - Acceptance criteria: actual runner compatibility and no-network execution.

3. `AcceptanceRunPersistenceTest`
   - Purpose: prove project-layer execution history.
   - Behavior: pending Test creation, finished/failed transition, ordered Step persistence, identity snapshot, safe error storage.
   - Setup: `RefreshDatabase`, factories, fake Core runner result.
   - Negative cases: failed Step and final persistence exception.
   - Acceptance criteria: data model, transaction boundary, logging, and masking items.

4. `ApplicationHealthTest`
   - Purpose: prove global HTTP behavior is no longer coupled to Acceptance profiles.
   - Behavior: Laravel health endpoint succeeds without Profile data or Acceptance headers.
   - Setup: normal testing application, no migrated Profile fixture required.
   - Negative cases: not applicable.

5. `CoreIsolationTest`
   - Purpose: prevent target/project coupling from returning to active Core code.
   - Behavior: scans Core PHP source for forbidden root model/request imports and target identifiers/config keys.
   - Setup: repository source only.
   - Negative cases: representative forbidden dependency fixture or explicit assertion list.

## 27. Tests to Run

- `git status --short`
- `git branch --show-current`
- `composer validate --no-check-publish --no-interaction --no-ansi`
- package-version snapshot before and after the allowed Composer lock metadata update
- `vendor/bin/playwright-install --dry-run chromium`
- PHP syntax checks for every Pack-created/edited PHP file
- `vendor/bin/pint --test` limited to Pack-scoped PHP files
- `php artisan test --compact`
- `php artisan module:list`
- `php artisan route:list --except-vendor`
- targeted active-source scan for `dieselkhodro`, `martfury`, `DKAPI`, removed middleware/traits, `Modules\\Auth`, and `Modules\\Ecommerce`
- generated Composer autoload scan for removed Auth/Ecommerce paths
- `git diff --check`
- `git diff --name-only`
- exact Pack allowlist comparison

Do not run npm, external network, persistent migration, database seeding, cache clearing, queue, Scribe generation, asset build, server, watcher, or browser installation commands.

## 28. Expected Output

- a generic Core contract/runtime for code-defined Acceptance Apps and Scenarios;
- correct Playwright PHP browser/context/assertion lifecycle;
- a minimal project persistence service and compatible Test/Step schema metadata;
- removal of active Martfury/DieselKhodro and obsolete runner coupling;
- meaningful unit, persistence, health, boundary, and real local Playwright smoke coverage;
- updated Composer runtime classification without package-version drift;
- one project-owned Run Report after execution is complete;
- no real target App or operator-facing execution surface.

## 29. Operator Execution Checklist

### Before AI Execution

- Confirm this Pack may run one headless Chromium smoke test using only local in-memory page content.
- Confirm this Pack may update Composer lock metadata and regenerate local Composer autoload while preserving every dependency version.
- Confirm all migration validation must use SQLite `:memory:` and no persistent database.

### After AI Execution

- Verify active Core/source no longer contains Martfury or DieselKhodro behavior.
- Verify the local two-step Chromium smoke demonstrates one shared context and deterministic cleanup.
- Verify no real App, API/command entry point, UI, queue, scheduler, or external test execution was added.
- Accept or reject the Run after reviewing tests, dependency diff, schema diff, and remaining follow-ups.

## 30. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md` and `docs/ai/templates/AGENT-FINAL-REPORT-TEMPLATE.md`.

The report must include:

- exact created, edited, and deleted files;
- dependency classification and before/after package versions;
- migration fields/indexes and confirmation that only SQLite `:memory:` was used;
- initial failing baseline and final test results;
- browser smoke environment and confirmation of no external navigation;
- error codes, status transitions, cleanup behavior, logs, and sensitive-data checks;
- target-dependency and stale-autoload scan results;
- all commands run and their classification;
- documentation/index maintenance and required follow-up updates;
- whether subagents were used and their responsibilities; if none, state that no subagents were used.

## 31. Review Checklist

- [ ] Diff contains only Pack-scoped implementation or required governance maintenance files.
- [ ] Implementation matches Decision 0002 and does not restore historical Auth behavior.
- [ ] Core dependency direction is clean and names are capability-neutral.
- [ ] Playwright 1.5 APIs are used from local installed source, not assumed from another language binding.
- [ ] Resource cleanup is deterministic and exception-safe.
- [ ] persistence and browser execution transaction boundaries are separated.
- [ ] errors use stable codes and safe logs/persistence.
- [ ] schema/index/rollback behavior matches section 22.
- [ ] tests prove behavior rather than only status/no-exception checks.
- [ ] dependency/lock changes contain no package-version drift.
- [ ] no forbidden environment, network, database, UI, API, queue, or generated-output side effect occurred.
- [ ] follow-up work is not silently implemented.

A separate formal Review file is optional unless tests fail, scope deviates, a sensitive-data concern remains, or the operator requests one.

## 32. Rollback / Safety Notes

- Source and documentation changes are rollback-friendly through a targeted reverse patch or the eventual Pack commit; never use broad reset/restore commands.
- The migration down path removes only Pack-added indexes/columns. Do not execute it outside a disposable test database without explicit operator approval.
- Composer rollback must restore only the scoped root dependency classification and lock metadata; do not update/downgrade packages.
- Deleted files are recoverable from Git history, but Auth/Ecommerce source trees are not part of this Pack and must not be restored.
- If Playwright creates temporary/cache data during tests, report its location; do not delete broad cache directories.
- Stop before commit if the final Run cannot be recorded or if package versions changed.

## 33. Stop Conditions

Stop and request operator direction if:

- the working tree contains unrelated changes before execution;
- implementation requires a real target App, target URL, credential, OTP/API adapter, selector, or external request;
- Playwright/Chromium is missing and testing would require download or installation;
- compatibility requires changing away from the approved Playwright PHP `^1.5` line;
- Composer proposes any dependency version change;
- a persistent/shared/unknown database would be touched;
- schema changes beyond the exact columns/indexes in section 22 are required;
- a secure Profile/credential redesign becomes necessary to pass this Pack;
- an API, command, UI, queue, scheduler, retry engine, parallel runner, artifact system, or real App becomes necessary;
- Core must import a root model/request or target-specific class to proceed;
- raw secrets or sensitive target data are discovered in tracked source, fixtures, logs, or Run output;
- required tests cannot pass within the scoped files;
- execution reveals a conflict with an accepted decision or Pack.

## 34. Open Questions

No blocking question for this Pack.

Deferred decisions for later Packs:

1. Which real system becomes the first Acceptance App?
2. What operator entry point should run Apps: Artisan command, API, UI, queue, or a staged combination?
3. How should App-specific credentials and secret Profile data be encrypted and permission-gated?
4. What retention/deletion policy should preserve Test/Step history when a Profile is deleted?
5. When should screenshots, traces, videos, retries, scheduling, parallel browsers, and reporting UI be introduced?
