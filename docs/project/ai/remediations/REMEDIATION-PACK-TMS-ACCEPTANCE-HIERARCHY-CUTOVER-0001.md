# REMEDIATION-PACK-TMS-ACCEPTANCE-HIERARCHY-CUTOVER-0001

Status: `accepted — implementation validated and operator accepted on 2026-10-08`

Generated: `2026-10-08`

Owner: TMS project, with explicit cross-owner Core implementation scope.

Execution Gate: The operator approved this exact Remediation scope on 2026-10-08 with “تائید است . اجرا کن”, accepted the validated result with “تائید میشه”, and separately authorized the final commit with “کامیت کن”.

## 1. Task ID

`REMEDIATION-PACK-TMS-ACCEPTANCE-HIERARCHY-CUTOVER-0001`

## 2. Task Title

Atomically replace the legacy Acceptance provider/runtime contract with the complete hierarchy contract.

## 3. Related Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0004.md`

Decision source: `docs/project/ai/decisions/TMS-DECISION-0015-clean-acceptance-hierarchy-cutover.md`.

## 4. Goal

Leave one runtime and client contract for:

```text
App -> Component -> Suite -> Scenario -> Variant -> Step
```

Remove the old discovery/compatibility paths completely, propagate the full tuple through planning and single execution, and prepare clean source for Pack 0010's Nwidart module SDK.

## 5. Current Problem

Accepted source currently contains two App models:

- `Modules\Core\Contracts\AcceptanceApp::scenarios()` exposes runnable scenarios directly;
- optional `App\Contracts\AcceptanceCatalogProvider` exposes descriptors and variants;
- `AcceptanceAppRegistry` and `AcceptanceCatalog` branch between them and cache a `legacySnapshot`;
- `ScenarioMetadata::$suites` treats Suite as a classification list rather than hierarchy identity;
- `AutomationDisposition::MANUAL_ONLY` conflicts with the accepted automated-E2E policy;
- plan/run/CLI/persistence identity omits Component and Suite and the run path defaults Variant.

Pack 0010 proposed another temporary `legacy/legacy` hierarchy adapter. Decision 0015 rejects that design.

## 6. Required Change

- Delete the Core `AcceptanceApp` discovery contract and the project `AcceptanceCatalogProvider` compatibility contract.
- Add one project-owned `AcceptanceComponentProvider` contract for App identity, catalog version, Component/Suite/Scenario descriptors, variants, and exact tuple resolution.
- Remove `ScenarioMetadata::$suites` and `AutomationDisposition::MANUAL_ONLY`.
- Add explicit immutable App/Component/Suite/Scenario/Variant execution identity at the Core runner boundary and in `RunResult`.
- Make Registry, Catalog, Planner, Dispatcher, operation DTOs, commands, logs, and Test records use the full tuple.
- Replace old list/plan/run request and JSON projections with a declared version-2 contract. Do not add aliases, fallback fields, implicit default Component/Suite/Variant, or dual serializers.
- Replace compatibility tests with single-contract tests. Historical Pack/Run documents remain unchanged.

## 7. Files to Read

- repository startup, project profile, Core router/manifest/profile, Decision 0015, Change Request 0004, and applicable shared rules;
- accepted Packs/Runs 0004, 0007, and 0009 as historical evidence only;
- all files in sections 8–9;
- current migration/model relationships for `tests` and `steps`;
- `composer.lock` only for installed version confirmation.

Do not read `.env`, unrelated modules, target corpora, unrelated vendor packages, or unrelated Packs/Runs/Reviews.

## 8. Files to Edit

### Core source and tests

- `Modules/Core/app/Data/ScenarioMetadata.php`
- `Modules/Core/app/Data/RunResult.php`
- `Modules/Core/app/Enums/AutomationDisposition.php`
- `Modules/Core/app/Services/AcceptanceRunner.php`
- `Modules/Core/tests/Unit/ScenarioMetadataTest.php`
- `Modules/Core/tests/Unit/AcceptanceRunnerTest.php`
- `Modules/Core/tests/Feature/PlaywrightAcceptanceSmokeTest.php`

### Project hierarchy, execution, persistence, and presentation

- `app/Data/AcceptancePlan.php`
- `app/Data/AcceptancePlanItem.php`
- `app/Data/AcceptanceSelector.php`
- `app/Data/ScenarioDescriptor.php`
- `app/Exceptions/AcceptanceCatalogException.php`
- `app/Exceptions/AcceptanceRegistryException.php`
- `app/Services/AcceptanceAppRegistry.php`
- `app/Services/AcceptanceCatalog.php`
- `app/Services/AcceptancePlanner.php`
- `app/Services/AcceptanceVariantDispatcher.php`
- `app/Services/AcceptanceRunService.php`
- `app/Acceptance/Operations/AcceptanceOperationService.php`
- `app/Acceptance/Operations/Data/CatalogOperationData.php`
- `app/Acceptance/Operations/Data/OperationRequest.php`
- `app/Acceptance/Operations/Data/OperationResult.php`
- `app/Acceptance/Operations/Data/RunOperationData.php`
- `app/Acceptance/Operations/Handlers/ListAcceptanceApps.php`
- `app/Acceptance/Operations/Handlers/RunAcceptanceScenario.php`
- `app/Console/Commands/ListAcceptanceCommand.php`
- `app/Console/Commands/PlanAcceptanceCommand.php`
- `app/Console/Commands/RunAcceptanceCommand.php`
- `app/Models/Test.php`

### Project tests and guide

- `tests/Unit/AcceptanceAppRegistryTest.php`
- `tests/Unit/AcceptanceCatalogTest.php`
- `tests/Unit/AcceptancePlannerTest.php`
- `tests/Unit/AcceptanceVariantDispatcherTest.php`
- `tests/Unit/AcceptanceOperationServiceTest.php`
- `tests/Feature/AcceptanceCatalogCommandTest.php`
- `tests/Feature/AcceptanceRunCommandTest.php`
- `tests/Feature/AcceptanceRunPersistenceTest.php`
- `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`

### Files to delete

- `Modules/Core/app/Contracts/AcceptanceApp.php`
- `app/Contracts/AcceptanceCatalogProvider.php`
- `tests/Feature/AcceptanceOperationCommandCompatibilityTest.php`

Deletion is limited to these three exact obsolete files. No broad cleanup command is permitted.

## 9. Files to Create

- `Modules/Core/app/Data/AcceptanceExecutionIdentity.php`
- `app/Contracts/AcceptanceComponentProvider.php`
- `app/Data/ComponentDescriptor.php`
- `app/Data/SuiteDescriptor.php`
- `database/migrations/2026_10_08_000001_cut_over_acceptance_execution_identity.php`
- `tests/Feature/AcceptanceOperationCommandContractTest.php`

Governance maintenance may create the remediation Run Report and update the project/Core remediation/run/Pack indexes at the later reporting gate.

## 10. Do Not Change

- target modules or `modules_statuses.json`;
- dependencies, lockfiles, vendor, package config, generated autoload files, `.env`, or any environment file;
- Profile/User/Step schema outside the exact Test identity columns;
- browser behavior, target resources, queue/batch behavior, API/Web/MCP, permissions, translations, or UI;
- accepted Pack/Run/Review history;
- any file not named in sections 8–9 except mandatory directly related governance maintenance.

## 11. Configuration / Settings Impact

```text
Configuration / Settings Impact: No
Config Files Changed: No
Environment Variables Required: No
Admin Settings Changed: Not applicable
External Integration / Secret Settings Changed: Not applicable
Queue / Cache / Logging Settings Changed: No
Test / Development Setup Required: Yes — absent-dotenv, in-memory SQLite only
Operator Manual Setup Required: No for source validation
```

No configuration or settings impact. Database migration execution against a persistent database is not authorized by this remediation execution.

## 12. Multilingual / Translation Impact

```text
Multilingual / Translation Impact: Yes
Visible Text Changed: Yes — Persian CLI guide only
Admin UI Labels Changed: Not applicable
API Messages Changed: Not applicable
Validation Messages Changed: No runtime translated messages
Translation Files Changed: No
RTL/LTR Impact: Not applicable
Human Review for Locale Wording Required: No
```

Machine identifiers and JSON remain English/language-neutral. The Persian guide documents only behavior that exists after the cutover.

## 13. UI / Admin UI Impact

No UI or Admin UI impact.

## 14. Implementation Rules

### Single provider contract

- `AcceptanceComponentProvider` is the only registrable App contract.
- It exposes `key()`, `catalogVersion()`, `components()`, `suites()`, `scenarios()`, `variants(scenarioKey)`, and exact `resolveScenario(componentKey, suiteKey, scenarioKey, variantKey)` behavior.
- Descriptor inspection never constructs runnable scenarios and remains bounded/repeatable.
- Component, Suite, and Scenario keys are App-global in this cutover. Suite descriptors reference an existing Component; Scenario descriptors reference an existing matching Component/Suite.
- No registry or catalog method accepts a provider that lacks the complete contract.

### Identity and selectors

- `AcceptanceExecutionIdentity` and `AcceptancePlanItem` carry exactly App/Component/Suite/Scenario/Variant keys.
- Plan version becomes 2 and its fingerprint includes the complete tuple and normalized selector.
- `--component` and hierarchy `--suite` are exact selectors. Classification suites are removed.
- `acceptance:run` requires explicit App, Component, Suite, Scenario, Variant, and Profile identity. No default Variant is inferred.

### Runtime disposition

- Runtime dispositions are `automated`, `blocked`, and `not-implemented` only.
- Human judgment and exclusions exist only in source coverage mappings owned by Decision 0012, not runtime scenario metadata.

### Persistence cutover

- New Test records require non-null `app_key`, `component_key`, `suite_key`, `scenario_key`, and `variant_key`.
- A fresh migration chain produces the complete tuple; the accepted historical migration is not rewritten.
- The forward migration must stop before mutation when existing Test rows are present. It must not backfill an invented Component/Suite/Variant, delete history, or assign `legacy` values.
- Persistent migration execution is outside this remediation run. Validation uses a fresh in-memory database only.

### Removal policy

- Remove obsolete branches, methods, comments, tests, fixtures, and imports; do not leave deprecated wrappers or aliases.
- Do not rename old symbols to `Legacy*`, retain dead code behind feature flags, or keep compatibility-only error handling.
- Accepted historical documentation remains intact and is not searched as proof that obsolete source still exists.

## 15. Architecture Constraints

The project owns cross-module identity, registry, catalog, operations, persistence, and clients. Core owns target-neutral execution primitives. This remediation may edit the exact Core files listed because Decision 0015 and Change Request 0004 explicitly authorize one cross-owner contract cutover.

No target-specific code enters Core or root application code. No filesystem discovery becomes registration. No console object, JSON serializer, HTTP object, or service locator enters domain/application services.

## 16. Data Model / Migration / Relationship Impact

```text
Data / Migration Impact: Yes
New Tables Required: No
Existing Tables Modified: Yes — tests identity columns
Foreign Keys Changed: No
Model Relationships Changed: No
Indexes / Unique Constraints Changed: Yes
Data Backfill Required: No — invented compatibility data is forbidden
Rollback Impact: Yes
Data-loss Risk: No during authorized tests; persistent migration stops on existing rows
```

Database changes:

- `tests`: add non-null `component_key`, `suite_key`, and `variant_key`; require the complete identity for new rows; replace the partial identity index with a complete tuple/time index.

The migration must remain reversible on an empty/non-production schema. It must not run automatically and must not delete or rewrite Test/Step history.

## 17. Validation Rules

- Static source scan finds no runtime `legacy`, `legacySnapshot`, `AcceptanceCatalogProvider`, `AcceptanceApp::scenarios`, `MANUAL_ONLY`, `manual-only`, compatibility adapter, or default-variant branch in current Core/project source and tests.
- Historical docs are excluded from that source scan.
- Every descriptor/reference/duplicate/collision rule fails deterministically before scenario resolution.
- Registry/catalog/planner inspection never invokes `resolveScenario()`.
- Exact tuple resolution materializes one scenario only.
- CLI JSON declares version 2 and contains the complete identity; old field-only projections and old run signatures fail command validation.
- In-memory migration creates exact non-null identity columns and index; a separate migration unit test proves non-empty cutover refusal without deleting data.
- Logs include only approved tuple keys and safe trace/error fields.
- No real target, persistent database, browser, network, queue, Composer, cache, or environment-file access occurs.

## 18. Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: Not applicable
UI / Admin UI Error Impact: CLI contract only
Stable Error Code Impact: Yes
Exception Handling Impact: Yes
External Error Normalization Impact: Not applicable
Retry / Permanent Failure Impact: No
Message / Attempt Status Impact: No
Structured Logging Impact: Yes
Traceability Impact: Yes
Sensitive Data Impact: No new sensitive data
Error-specific Test Impact: Yes
Operator Documentation Impact: Yes
```

Existing defective behavior permits two provider contracts and partial identity. Corrected behavior rejects incomplete hierarchy definitions and never falls back. Reuse `acceptance_catalog_invalid`, `acceptance_selector_invalid`, and `acceptance_selector_not_found` where their meaning remains exact. Add `acceptance_hierarchy_invalid` for incomplete or mismatched Component/Suite/Scenario references and `acceptance_hierarchy_duplicate` for duplicate Component, Suite, Scenario, or Variant identity. Remove `acceptance_catalog_duplicate` from the active result allowlist because hierarchy duplication now has one explicit code. App registration duplication continues to use `acceptance_registry_duplicate`.

Structured logs propagate `app_key`, `component_key`, `suite_key`, `scenario_key`, and `variant_key` only for validated identities. Raw exceptions, metadata arrays, source mappings, absolute paths, request arrays, credentials, URLs, and payloads remain excluded.

Backward compatibility impact: intentionally breaking. No API/Web/Admin UI exists. Existing persistent Test records are not modified by this Pack; migration refuses to invent missing identities.

## 19. Tests to Add

- `tests/Feature/AcceptanceOperationCommandContractTest.php` for version-2 list/plan/run requests, JSON/exits, and rejection of old signatures/projections.
- migration cases inside `tests/Feature/AcceptanceRunPersistenceTest.php` for full identity and non-empty-table refusal.
- focused hierarchy cases in existing unit files; do not add a second parallel compatibility suite.

## 20. Tests to Run

After separate execution approval:

- `php -l` for every created/edited PHP file in sections 8–9;
- scoped Core unit tests: `ScenarioMetadataTest`, `AcceptanceRunnerTest`, and the existing Playwright smoke discovery/mock-safe group without launching a browser;
- project unit tests: Registry, Catalog, Planner, Dispatcher, Operation Registry, and Operation Service;
- project feature tests: catalog commands, version-2 operation command contract, run command, and run persistence;
- scoped `pint --test` over changed PHP files;
- `git diff --check`, status/name-only/untracked review, and exact scoped diff review;
- absent-dotenv Laravel entrypoint with `APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` as process values;
- protected-file hashes for dependencies, `modules_statuses.json`, environment files, and target modules.

Do not run a persistent migration, ordinary dotenv-reading Artisan, a real browser/target/network call, Composer, queue/worker/server commands, or the unrelated full suite.

## 21. Acceptance Checklist

- [x] one hierarchy provider contract remains;
- [x] old provider/App discovery contracts and compatibility test are deleted;
- [x] no reserved `legacy` identities or fallback branches remain;
- [x] runtime `manual-only` and classification suites are removed;
- [x] full tuple reaches plan, dispatch, runner, result, operation, CLI, log, and new Test records;
- [x] plan/client schema is version 2 and old signatures/projections are rejected;
- [x] no invented data backfill or destructive persistent migration occurs;
- [x] all scoped tests and static scans pass;
- [x] protected paths are unchanged;
- [x] Pack 0010 was re-normalized after remediation acceptance and is ready for its separate execution-approval gate.

Execution Evidence (2026-10-08):

- PHP lint passed for all 40 changed/created PHP files.
- Final scoped unit validation passed: 100 tests, 564 assertions.
- Final absent-dotenv, in-memory SQLite feature validation passed: 31 tests, 608 assertions.
- The operator-provided full-suite checkpoint passed: 478 tests, 3323 assertions, 16.39 seconds. The exact command and environment were not provided, and this run predates the second coverage audit, so it is not the final full-suite result for the current tree.
- The modified test set contains 95 test methods and 363 explicit assertion calls. Two evidence-based reviews restored valid version-2 edge coverage; removed compatibility-only assertions were not reinstated.
- Restored disjoint Scenario/Variant coverage exposed and fixed a Planner defect that could misclassify a known Variant as unknown instead of returning an empty plan.
- The Playwright acceptance smoke file was loaded and both tests were discovered with `--list-tests`.
- Scoped Pint, `git diff --check`, active-source compatibility scans, protected-file object hashes, and scope review passed.
- The first feature run had four presentation-test assertion failures. The command output assertions were changed from chained console substring expectations to decoded JSON assertions, the Symfony argument expectation was corrected to its actual API shape, and the complete feature group then passed.
- No persistent migration, target, network, Composer, queue, cache, server, environment-file read, commit, or Pack 0010 execution occurred.
- One validation deviation occurred: the two local Playwright smoke tests were accidentally executed once in headless Chromium before the intended discovery-only command. Both passed; they used local in-memory content and no target or network, and no browser validation was repeated.
- The accepted persistent Run Report is `docs/project/ai/runs/REMEDIATION-PACK-TMS-ACCEPTANCE-HIERARCHY-CUTOVER-0001-run-001.md`.

## 22. Rollback / Safety Notes

Rollback is a targeted patch limited to sections 8–9. Never use broad reset/restore/clean commands. No persistent database rollback is part of this execution. Temporary in-memory databases are disposable. Historical Pack/Run records stay unchanged.

## 23. Operator Review Checklist

### Before execution

- approve intentional removal of the old provider/runtime/CLI contracts;
- approve the exact cross-owner Core/project file scope and three deletions;
- approve version-2 list/plan/run identity and no default Variant;
- approve full Test identity schema source plus refusal to migrate non-empty persistent data;
- approve only absent-dotenv, in-memory/synthetic validation;
- confirm execution on `main` and no commit.

### After execution

- inspect static proof that no compatibility path remains;
- compare one version-2 plan tuple with one exact dispatched scenario and persisted in-memory Test;
- verify old command signatures/projections fail;
- verify no persistent database, target module, status file, dependency, environment, or network state changed.

## 24. Operator Documentation Impact

```text
Operator Documentation Impact: Yes
Operator Documentation Update Required: Yes
Guide File: docs/project/ai/guides/ACCEPTANCE-CLI.fa.md
```

Replace old selector/signature/JSON examples with the version-2 hierarchy contract. Do not document aliases or a transition mode. No screenshots are required.

## 25. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md`. Report exact deleted/created/edited files, hierarchy propagation, migration refusal behavior, CLI version-2 contract, error/log safety, test commands/results, static scan results, protected-path evidence, documentation maintenance, and Pack 0010 normalization status.

The remediation acceptance, Pack 0010 normalization, and accepted Run gates are complete. Do not execute Pack 0010 or commit without their separate approvals.
