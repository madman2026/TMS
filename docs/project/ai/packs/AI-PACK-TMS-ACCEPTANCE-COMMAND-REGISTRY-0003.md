# AI-PACK-TMS-ACCEPTANCE-COMMAND-REGISTRY-0003 — Acceptance Command and App Registry

Status: `accepted — technical validation passed; operator accepted`

Generated: `2026-09-23`

Decision: `docs/project/ai/decisions/TMS-DECISION-0003-cli-first-acceptance-execution.md`

Run: `docs/project/ai/runs/AI-PACK-TMS-ACCEPTANCE-COMMAND-REGISTRY-0003-run-001.md`

Execution: started `2026-09-23` after operator approval

## 1. Task ID

`AI-PACK-TMS-ACCEPTANCE-COMMAND-REGISTRY-0003`

## 2. Task Title

Add the minimum explicit Acceptance App Registry and Artisan execution command.

## 3. Goal

Make the accepted Pack 0002 Acceptance foundation invokable through one internal Artisan command without adding a real target-system App or another execution channel.

This Pack must:

1. add an explicit code-owned project Registry for Acceptance Apps and their Scenarios;
2. add `acceptance:run` as the first operator execution entry point;
3. resolve an existing Profile and validated Run options before execution;
4. delegate all browser execution and result persistence to the existing `AcceptanceRunService`;
5. return deterministic exit codes and safe language-neutral JSON output;
6. prove command/registry behavior using fake Apps, Scenarios, and services only.

## 4. Context

Accepted Pack 0002 created the generic Core App/Scenario contracts, runner lifecycle, Playwright adapter, and project persistence service. It intentionally created no operator execution entry point and registered no real App.

The operator selected Command as the next execution channel. Laravel 12 already auto-discovers classes under `app/Console/Commands`; the existing `make:apiRequest` command proves that repository convention. No edit to `routes/console.php` or `bootstrap/app.php` is required.

The current `Profile` model/table is sufficient to identify the execution profile. This Pack passes the resolved Profile to the existing service but must not read, output, log, redesign, encrypt, or depend on Profile `extra`. Real credentials and target-specific Profile behavior remain deferred until a real App Pack.

## 5. Related Release / Phase

Not applicable. This is a pre-Release execution-entry foundation Pack.

## 6. Related Epic / Feature / Story

Generic multi-system Acceptance execution foundation — minimal operator entry point.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0002-generic-code-based-acceptance-apps.md`
- `docs/project/ai/decisions/TMS-DECISION-0003-cli-first-acceptance-execution.md`
- `docs/project/ai/runs/AI-PACK-TMS-ACCEPTANCE-RUNNER-STABILIZATION-0002-run-001.md`
- `docs/project/references/TMS-SOURCE-STRUCTURE-SUMMARY.md`
- current Laravel 12 command discovery source under `vendor/laravel/framework/src/Illuminate/Foundation/`

No external documentation or network fetch is required.

## 8. Files to Create

### Project execution boundary

- `app/Console/Commands/RunAcceptanceCommand.php`
- `app/Exceptions/AcceptanceRegistryException.php`
- `app/Services/AcceptanceAppRegistry.php`

### Tests

- `tests/Unit/AcceptanceAppRegistryTest.php`
- `tests/Feature/AcceptanceRunCommandTest.php`

### Execution record

- `docs/project/ai/runs/AI-PACK-TMS-ACCEPTANCE-COMMAND-REGISTRY-0003-run-001.md`, only after implementation, validation, scope review, and the pre-commit reporting gate are complete

## 9. Files to Edit

### Project source

- `app/Providers/AppServiceProvider.php`

### Lifecycle and index maintenance

- `docs/project/ai/packs/AI-PACK-TMS-ACCEPTANCE-COMMAND-REGISTRY-0003.md`, only for lifecycle status, acceptance state, and execution references
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md`

Required Decision, Review, or other lifecycle index maintenance is allowed only through the governance maintenance exception and must be reported.

## 10. Files to Read / Reference

### Governance and ownership

- `AGENTS.md`
- `docs/ai/start/START-HERE.md`
- `docs/ai/start/CONTEXT-MAP.md`
- `docs/project/PROJECT-DOCS-INDEX.md`
- `docs/project/ai/TMS-AI-PROFILE.md`
- `docs/ai/rules/AI-EXECUTION-RULES.md`
- `docs/ai/rules/SCOPE-CONTROL-RULES.md`
- `docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md`
- `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`
- `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md`
- `docs/ai/rules/COMMENTING-RULES.md`
- `docs/ai/rules/GIT-AND-COMMIT-RULES.md`
- `docs/ai/rules/REPORTING-RULES.md`
- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`

### Current implementation

- `app/Console/Commands/ApiRequestMakeCommand.php`
- `app/Models/Profile.php`
- `app/Models/Test.php`
- `app/Providers/AppServiceProvider.php`
- `app/Services/AcceptanceRunService.php`
- `app/TestStatusEnum.php`
- `bootstrap/app.php`
- `bootstrap/providers.php`
- `routes/console.php`
- `database/factories/ProfileFactory.php`
- `database/factories/UserFactory.php`
- `database/migrations/2026_02_17_131201_create_profiles_table.php`
- `Modules/Core/app/Contracts/AcceptanceApp.php`
- `Modules/Core/app/Contracts/AcceptanceScenario.php`
- `Modules/Core/app/Data/RunOptions.php`
- `Modules/Core/app/Exceptions/AcceptanceExecutionException.php`
- `composer.json`
- `phpunit.xml`

Laravel vendor source is read-only evidence and must not be edited.

## 11. Configuration / Settings Requirements

Configuration / Settings Impact: No new configuration file, environment variable, Admin Setting, secret setting, queue/cache/log setting, or external integration is required.

- The command uses the existing `core.acceptance` browser, headless, timeout, and slow-motion defaults when an option is omitted.
- Allowed command overrides are `--browser`, `--headed`, `--timeout`, and `--slow-mo`.
- Browser/context option validation remains owned by `RunOptions`.
- The Registry is populated explicitly in source code through `AcceptanceAppRegistry::register`; it must not load App class names from environment data, the database, Profile data, arbitrary paths, or filesystem scanning.
- No `.env`, `.env.example`, Composer, npm, or module configuration change is allowed.
- No browser installation or dependency command is required.

Settings validation:

- invalid browser/timeout/slow-motion values return exit code `2` with `acceptance_configuration_invalid` and do not call the execution service;
- omitted values use the existing Core defaults;
- `--headed` only changes the per-Run `headless` option and does not persist configuration.

## 12. Do Not Change

- Do not create a real, example, demo, Martfury, DieselKhodro, Auth, Ecommerce, or other target-system App.
- Do not modify Core source, Core tests, Core configuration, the accepted runner/persistence implementation, Profile/User/Test/Step models, migrations, factories, routes, bootstrap files, or existing commands.
- Do not add API/web routes, controllers, UI/Admin UI, queue, scheduler, Job, retry, parallel execution, browser matrix, screenshots, trace/video artifacts, or reporting UI.
- Do not add database-defined scenarios, dynamic PHP evaluation, filesystem/reflection scanning, automatic module discovery, or configuration-driven arbitrary class instantiation.
- Do not read or expose Profile `extra`, credentials, tokens, cookies, URLs, selectors, external payloads, or production data.
- Do not edit `.env`, `.env.example`, `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, Scribe output, views, assets, or generated vendor files.
- Do not change accepted Pack 0002, its Decision, Run, Review, or commit history.
- Do not perform unrelated formatting, factory cleanup, command cleanup, model cleanup, or dependency updates.

## 13. Clarification Questions Before Implementation

The Pack requires the operator to confirm the three Pack-local execution choices in section 29 before implementation.

Approved architecture source already available:

1. Command is the first execution channel.
2. Registry/App definitions remain code-owned.
3. No real App or credential use belongs in this Pack.

## 14. Multilingual / Translation Requirements

Multilingual / RTL-LTR Impact: No localized human-readable message is introduced.

- `acceptance:run` output is a stable language-neutral JSON object intended for terminal and automation consumption.
- Output field names and stable error codes are technical protocol identifiers and are not translated.
- Raw exception messages and framework validation text must not be printed.
- The command signature contains no human-readable argument/option descriptions; command name and option names are technical identifiers.
- No translation file is created or modified.
- RTL/LTR behavior is not applicable because no UI or formatted natural-language terminal content is introduced.

## 15. UI / Admin UI Requirements

No UI or Admin UI change is required or allowed.

The Artisan command is a CLI execution boundary, not an Admin UI surface. No page, view, menu, form, table, filter, action, button, dashboard, permission, or navigation behavior may be introduced.

## 16. Operator Documentation Requirements

Operator Documentation Impact: Limited.

- The Pack and Run Report must record the exact command signature, options, JSON result shape, exit codes, prerequisites, and current limitation that no real App is registered.
- A separate operator guide is not created because no real App or production execution workflow exists yet and the project profile declares no active project guide path.
- `php artisan help acceptance:run` and the Run Report provide the bounded technical reference for this internal entry point.
- The first real App Pack must revisit operator documentation, secret setup, target prerequisites, and safe usage examples.

## 17. Implementation Rules

- Create a final project service `AcceptanceAppRegistry` that accepts only `AcceptanceApp` objects through explicit `register` calls.
- Validate App and Scenario keys with the same capability-neutral key shape accepted by Core: lowercase alphanumeric start followed by lowercase alphanumeric, dot, underscore, or hyphen.
- Eagerly index an App's Scenarios at registration so invalid values and duplicate keys fail before command execution.
- Store only runtime objects in memory; do not persist registry definitions.
- Throw `AcceptanceRegistryException` with stable codes and safe fixed messages for invalid definitions or duplicate keys.
- Register the Registry as a singleton in `AppServiceProvider`. Leave it empty by default.
- Future Apps must be able to register through an approved service provider without modifying Core.
- Add an auto-discovered `RunAcceptanceCommand` with signature:

```text
acceptance:run {app} {scenario} {profile} {--browser=} {--headed} {--timeout=} {--slow-mo=}
```

- Resolve in this order: App, Scenario, Profile, RunOptions, then execute. Invalid preconditions must not create a Test record or call `AcceptanceRunService`.
- Delegate execution exactly once to `AcceptanceRunService::run(Profile, App, Scenario, RunOptions)`.
- Never call Playwright, `AcceptanceRunner`, or Eloquent Test/Step persistence directly from the Command.
- Emit one compact JSON object only. Do not output arbitrary result payloads, Profile fields, stack traces, exception messages, URLs, or command input values that were not resolved through the Registry.
- Return exit code `0` for a finished Run, `1` for an executed failed Run or unexpected safe failure, and `2` for invalid selection/profile/options before execution.
- Apply only targeted formatting to Pack-created/edited PHP files.

## 18. Architecture Constraints

- Follow Decisions 0002 and 0003.
- Dependency direction remains CLI Command → project Registry/persistence service → Core contracts/runtime.
- Core must not import or know about the Command, Registry, Profile, or project service.
- The Registry is an explicit in-process map, not a service locator for arbitrary classes and not an executable workflow store.
- The command is an internal adapter. It owns parsing and presentation only; execution and persistence remain in their existing services.
- One command invocation executes exactly one App/Scenario/Profile combination.
- No authorization model is added because the CLI already runs inside the trusted local/server operator boundary; public or remote execution requires a later security decision.

## 19. Validation Rules

- Run PHP syntax checks for every Pack-created/edited PHP file.
- Run Pint in check-only mode limited to Pack-scoped PHP files; formatting fixes may affect only those files.
- Confirm `php artisan list --raw` contains exactly one `acceptance:run` command and no other new command.
- Confirm `php artisan help acceptance:run` exposes only the Pack-defined arguments/options.
- Run targeted Registry and Command tests, then the complete PHPUnit suite with SQLite `:memory:` only.
- Confirm `php artisan route:list --except-vendor` contains no new route.
- Confirm `php artisan module:list` still contains Core only.
- Run a forbidden-reference scan across Pack-created source/tests for target identifiers, URLs, credentials, Profile `extra`, direct Playwright calls, and direct Test/Step persistence.
- Confirm Composer/npm/environment/migration files are unchanged.
- Run `git diff --check`, `git diff --name-only`, and an exact Pack allowlist comparison.
- No browser navigation, external URL, persistent database, external network, npm, Composer, migration, seed, queue, cache, Scribe, asset, server, or watcher command may run.

Allowed mutating commands during execution:

- targeted Pint formatting only for Pack-created/edited PHP files if check-only validation reports formatting failures.

## 20. Security Rules

- Never read, print, log, persist, document, or return Profile `extra`, passwords, cookies, Authorization headers, tokens, OTPs, service secrets, URLs, selectors, or external payloads.
- Command rejection output must not echo unknown App/Scenario/Profile input because an operator could accidentally pass a sensitive value.
- Use only obvious local fake identifiers such as `local-app`, `local-scenario`, and `example-sensitive-value` in tests.
- The command may output only status, Test Run ID, resolved App key, resolved Scenario key, and stable error code.
- Registry exception messages must be fixed and safe; definition objects or raw values must not be serialized.
- Unexpected exceptions must be normalized to `acceptance_command_failed`, logged with exception class only, and never printed raw.
- Tests must prove no Test is created and the execution service is not called for rejected preconditions.

## 21. Error Handling / Logging / Traceability Requirements

Error handling, CLI error visibility, logging, and Run traceability are modified by this Pack.

### Error scenarios

1. App not found
   - Trigger: requested key is absent from the explicit Registry.
   - Layer: Command/Registry resolution.
   - `error_code`: `acceptance_app_not_found`.
   - Exit: `2`; permanent until source registration changes; operator/developer action required.
   - Output: `{"status":"rejected","error_code":"acceptance_app_not_found"}`.
   - Log: none; invalid operator selection is fully observable in the CLI.
   - Persistence: no Test created.

2. Scenario not found
   - Trigger: requested key is absent from the resolved App.
   - Layer: Command/Registry resolution.
   - `error_code`: `acceptance_scenario_not_found`.
   - Exit: `2`; permanent until App source changes or input is corrected.
   - Output: safe code only; no raw input.
   - Log: none; no Test created.

3. Profile not found
   - Trigger: no Profile matches the supplied ID.
   - Layer: Command project lookup.
   - `error_code`: `acceptance_profile_not_found`.
   - Exit: `2`; permanent until input/data is corrected.
   - Output: safe code only; no raw Profile input or data.
   - Log: none; no Test created.

4. Invalid Registry definition
   - Trigger: invalid key, invalid Scenario value, duplicate App, or duplicate Scenario key during registration.
   - Layer: `AcceptanceAppRegistry`.
   - `error_code`: `acceptance_registry_invalid` or `acceptance_registry_duplicate`.
   - Classification: permanent developer/configuration failure; no automatic retry.
   - Message: fixed safe exception message; no object dump or raw value.
   - Log: when observed through the Command, `tms.acceptance.command.failed` at error level with error code and exception class only.

5. Invalid Run options
   - Trigger: unsupported browser or invalid numeric option.
   - Layer: Command parsing / Core `RunOptions` validation.
   - `error_code`: `acceptance_configuration_invalid`.
   - Exit: `2`; no execution-service call or Test.
   - Output: safe code only; no raw input.

6. Executed Run fails
   - Trigger: `AcceptanceRunService` returns a failed Test.
   - Layer: existing execution/persistence service and Command presentation.
   - `error_code`: existing normalized Test error code.
   - Exit: `1`.
   - Output: status, Test ID, resolved App/Scenario keys, and stable error code.
   - Logging/persistence: remains owned by the existing service.

7. Unexpected Command failure
   - Trigger: an unclassified exception escapes lookup/options/service orchestration.
   - Layer: Command outer boundary.
   - `error_code`: `acceptance_command_failed`.
   - Exit: `1`; retryability unknown and no automatic retry.
   - Output: safe code only.
   - Log: `tms.acceptance.command.failed` at error level with `error_code` and `exception_class`; omit arguments, Profile data, raw message, and stack trace from structured context.

### Traceability

- An executed Run is traced by the persisted Test ID returned in command JSON.
- The resolved, registry-owned App and Scenario keys are returned for executed Runs only.
- Rejected pre-execution requests have no Test ID because no Run exists.
- No request ID, API trace, queue attempt, or external reference is introduced.

## 22. Data Model / Migration / Relationship Requirements

Data / Migration Impact: No.

- No table, column, index, foreign key, relationship, cast, factory, seed, or migration changes are allowed.
- The Command reads one existing Profile and delegates all Test/Step writes to `AcceptanceRunService`.
- Registry definitions remain in memory and source control; they are never stored in the database.
- All Feature tests use the existing SQLite `:memory:` configuration and `RefreshDatabase` only.
- No persistent migration command may run.

## 23. Commenting Requirements

Follow `docs/ai/rules/COMMENTING-RULES.md`.

- Add concise PHPDoc for Registry collection shapes and public registration/resolution contracts where types cannot express the map structure.
- Add an inline comment only if the safe output or explicit-registration boundary is otherwise unclear.
- Do not add tutorial comments, repeat command flow, or document future features inside source code.

## 24. Testing Requirements

Test Quality Required: Yes
Behavioral Assertions Required: Yes
Contract Assertions Required: Yes
Database Assertions Required: Yes, only negative/Run-result effects through existing schema
Side-effect Assertions Required: Yes
Negative/Error Scenario Tests Required: Yes
Authorization/Permission Tests Required: No — trusted internal CLI only
Idempotency Tests Required: Yes — duplicate Registry definitions must fail deterministically
Queue/Event/Job Assertions Required: No
External Integration Fake/Mock Required: No external integration exists; execution service must be mocked in Command tests
Security/Secret Masking Tests Required: Yes

### Behavior to prove

- explicit registration resolves the exact App and Scenario objects;
- invalid/duplicate definitions fail with stable Registry codes;
- the command is auto-discovered with the exact signature;
- valid input resolves App, Scenario, and Profile, builds exact RunOptions, and calls the execution service once;
- a finished Test returns exit `0`; a failed Test returns exit `1` with its safe code;
- missing App, Scenario, Profile, or invalid options return exit `2`, create no Test, and never call the service;
- unexpected exceptions return `acceptance_command_failed`, log safe context, and do not expose sample sensitive text;
- command output contains only the allowlisted JSON fields;
- no real browser or external target is invoked by the new tests.

## 25. Acceptance Checklist

- [x] Decision 0003 remains the controlling execution-channel decision.
- [x] Registry registration is explicit source code and empty by default.
- [x] Registry rejects invalid/duplicate App and Scenario definitions safely.
- [x] `acceptance:run` is auto-discovered with only the declared arguments/options.
- [x] Command resolution order is App → Scenario → Profile → options → service.
- [x] Command delegates exactly once to `AcceptanceRunService` and never calls Core runner/Playwright/persistence directly.
- [x] success, executed failure, and rejected precondition exit codes are deterministic.
- [x] JSON output contains only safe allowlisted fields and stable error codes.
- [x] invalid input creates no Test and calls no execution service.
- [x] no Profile `extra`, credential, URL, selector, raw exception, arbitrary result, or untrusted command input is exposed.
- [x] no real App, API, UI, queue, scheduler, migration, dependency, or Core change is introduced.
- [x] targeted and full tests pass using SQLite `:memory:` only.
- [x] command/help, route, module, forbidden-reference, diff, and allowlist checks pass.
- [x] Run Report and lifecycle indexes are complete before commit.
- [x] Human Operator performed the separate post-execution acceptance gate.

## 26. Tests to Add

1. `AcceptanceAppRegistryTest`
   - Purpose: prove explicit code registration and definition integrity.
   - Main assertions: exact object resolution; ordered key listing; invalid App/Scenario values and keys return `acceptance_registry_invalid`; duplicate App/Scenario keys return `acceptance_registry_duplicate`.
   - Setup: anonymous fake App/Scenario classes only.
   - Side effects: in-memory Registry state only.
   - Negative cases: invalid keys, duplicate keys, non-Scenario yielded value.

2. `AcceptanceRunCommandTest`
   - Purpose: prove the operator boundary without launching a browser.
   - Main assertions: auto-discovery/signature; exact service inputs including `RunOptions`; exit `0/1/2`; exact JSON shape; Test ID trace; no Test/service call for rejected preconditions; safe unexpected-error log/output.
   - Setup: `RefreshDatabase`, fake registered App/Scenario, factory Profile, mocked `AcceptanceRunService`.
   - Side effects: SQLite `:memory:` records only; no browser/network.
   - Negative cases: missing App, Scenario, Profile, invalid options, failed Test, unexpected exception containing `example-sensitive-value`.

## 27. Tests to Run

- `git status --short`
- `git branch --show-current`
- PHP syntax checks for every Pack-created/edited PHP file
- `vendor/bin/pint --test` limited to Pack-scoped PHP files
- `php artisan test --compact tests/Unit/AcceptanceAppRegistryTest.php tests/Feature/AcceptanceRunCommandTest.php`
- `php artisan test --compact`
- `php artisan list --raw`
- `php artisan help acceptance:run`
- `php artisan route:list --except-vendor`
- `php artisan module:list`
- targeted forbidden-reference/security scan over Pack-created source/tests
- unchanged protected-file checks for Core, Composer/npm, environment, migration, route, model, factory, and bootstrap files
- `git diff --check`
- `git diff --name-only`
- exact Pack allowlist comparison

Do not run Composer, npm, migration, seed, browser installation, external network, persistent database, cache, queue, Scribe, asset, server, or watcher commands.

## 28. Expected Output

- one explicit in-memory `AcceptanceAppRegistry` project service;
- one auto-discovered `acceptance:run` Artisan command;
- deterministic safe JSON and exit-code contracts;
- project provider singleton registration;
- meaningful unit/Feature coverage with no real App or external execution;
- one project-owned Run Report after execution completes;
- no Core, schema, dependency, route, UI, API, queue, or target-system change.

## 29. Operator Execution Checklist

### Before AI Execution

- Confirm Pack 0003 must register no real App and may test the Command only with fake App/Scenario/service objects.
- Confirm Feature tests may create only SQLite `:memory:` Profile/Test records through `RefreshDatabase`; no persistent migration or seed command may run.
- Confirm CLI output should be compact language-neutral JSON with stable codes and no separate translation files in this Pack.

### After AI Execution

- Verify `acceptance:run` is the only new operator entry point and the Registry is empty by default.
- Verify rejected input produces no Run and executed output exposes only safe fields.
- Verify no real App, target URL, credential access, Core edit, API/UI/queue/scheduler, dependency, or migration change occurred.
- Accept or reject the Run after reviewing command help, tests, diff, output/exit contracts, and recorded follow-ups.

## 30. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md` and `docs/ai/templates/AGENT-FINAL-REPORT-TEMPLATE.md`.

The report must include:

- exact created and edited files;
- command signature, options, JSON shapes, and exit codes;
- Registry behavior and future registration extension point;
- error codes, logging, Test ID traceability, and masking checks;
- confirmation that only SQLite `:memory:` was used and no real browser/target ran in new tests;
- command/help, full PHPUnit, route/module, protected-file, forbidden-reference, and scope results;
- documentation/index maintenance and follow-up status;
- whether subagents were used; if none, state that explicitly.

## 31. Review Checklist

- [ ] Diff contains only Pack-scoped implementation or required governance maintenance files.
- [ ] Decision 0003 is applied without expanding Decision 0002.
- [ ] Registry is explicit, deterministic, and target-neutral.
- [ ] Command is a thin adapter and does not duplicate runner/persistence behavior.
- [ ] output and logs omit raw inputs, Profile data, exception messages, and arbitrary results.
- [ ] exit codes and stable error codes match section 21.
- [ ] tests verify meaningful calls, negative side effects, masking, and exact output.
- [ ] no protected Core/dependency/schema/route/bootstrap/model/factory file changed.
- [ ] no external browser, target, network, queue, cache, generated output, or persistent database side effect occurred.
- [ ] follow-up work is not silently implemented.

A separate formal Review file is optional unless tests fail, scope deviates, a sensitive-data concern remains, or the operator requests one.

## 32. Rollback / Safety Notes

- Source/documentation changes are rollback-friendly through a targeted reverse patch or the eventual Pack commit; never use broad reset/restore commands.
- Removing the Command, Registry, exception, tests, and provider binding restores the pre-Pack execution surface; no schema/data rollback is required.
- Do not unregister or remove future real Apps through this Pack because none may be added here.
- Stop before commit if the final Run cannot be recorded or protected files changed.

## 33. Stop Conditions

Stop and request operator direction if:

- the working tree contains unrelated changes before execution;
- implementation requires a real App, URL, selector, Profile `extra`, credential, token, external request, or browser launch;
- command discovery requires editing bootstrap/routes/framework source rather than the approved auto-discovered command path;
- Registry implementation requires config/database/filesystem/reflection discovery or arbitrary class instantiation;
- Core, models, factories, migrations, routes, dependencies, npm, environment, UI, API, queue, scheduler, or generated files must change;
- command tests cannot isolate execution with fake/mocked services and SQLite `:memory:`;
- raw sensitive input or exception text appears in output, logs, persistence, fixtures, or reports;
- required tests cannot pass within scoped files;
- execution conflicts with accepted Decisions 0002/0003 or Pack 0002 behavior.

## 34. Open Questions

No blocking implementation question remains after the section 29 execution confirmations.

Deferred decisions for later Packs:

1. Which real system and Scenario become the first registered App?
2. How are App-specific credentials encrypted, authorized, and supplied to Scenarios?
3. Should the first real App register through its module provider or a project-level App provider?
4. When should list/inspect commands, API/UI execution, queues, scheduling, retries, artifacts, or parallel execution be introduced?
