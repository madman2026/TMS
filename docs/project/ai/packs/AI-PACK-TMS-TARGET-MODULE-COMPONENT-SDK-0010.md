# AI-PACK-TMS-TARGET-MODULE-COMPONENT-SDK-0010 — Nwidart target module and Component SDK

Status: `ready — normalized on 2026-10-08; separate operator execution approval required`

Generated: `2026-10-07`

Normalized: `2026-10-08`

Decision: Decisions 0007, 0008, 0012, 0013, and 0014.

## 1. Task ID

`AI-PACK-TMS-TARGET-MODULE-COMPONENT-SDK-0010`

## 2. Task Title

Add the App/Component/Suite/Scenario/Variant hierarchy and reusable target-module creation services.

## 3. Goal

Provide client-neutral operations that can create, extend, validate, and safely populate a minimal Nwidart target module while adding explicit Component and Suite identities to the existing code-owned Acceptance catalog.

This Pack creates reusable project infrastructure only. It does not create DK, ND, another production target module, an executable target workflow, or an operator-facing command.

## 4. Context

Accepted Pack 0009 provides the typed operation boundary and accepted list/plan/run behavior. Decision 0007 changes the executable identity to:

```text
App -> Component -> Suite -> Scenario -> Variant -> Step
```

The installed package is `nwidart/laravel-modules` v12.0.5. Its `ModuleGenerator` is not a service-safe boundary: it requires `Illuminate\Console\Command`, writes progress through console components, calls other Artisan commands, changes activation state, and the native `module:make` command runs `composer dump-autoload`. This Pack therefore reuses the lower-level native repository/path contract, `Nwidart\Modules\Support\Stub`, and `Nwidart\Modules\Generators\FileGenerator`; it does not call Artisan, Symfony Process, `ModuleGenerator::generate()`, Composer, or the activator.

The initial Draft also mentioned Component creation and scenario import but listed only target-module create/validate handlers. Normalization makes those already-required capabilities explicit through separate handlers and DTOs. Pack 0015 will only add Artisan presentation over these operations.

## 5. Related Release / Phase

Decision 0013, stage 2.

## 6. Related Epic / Feature / Story

Reusable target onboarding, explicit Component/Suite hierarchy, and source-owned scenario mapping.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0007-nwidart-target-modules-and-component-hierarchy.md`
- `docs/project/ai/decisions/TMS-DECISION-0008-transport-neutral-operations-and-artisan-client.md`
- `docs/project/ai/decisions/TMS-DECISION-0012-automated-e2e-coverage-and-traceability.md`
- `docs/project/ai/decisions/TMS-DECISION-0013-fifteen-stage-layered-delivery-roadmap.md`
- `docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md`
- accepted Pack 0007 and its Run
- accepted Pack 0009 and its Run
- installed Nwidart v12.0.5 source named in section 10

## 8. Files to Create

### Hierarchy and coverage contracts

- `app/Contracts/AcceptanceComponentProvider.php`
- `app/Data/ComponentDescriptor.php`
- `app/Data/SuiteDescriptor.php`
- `app/Acceptance/Coverage/Enums/CoverageDisposition.php`
- `app/Acceptance/Coverage/Data/SourceCaseMapping.php`

### Creation and validation services

- `app/Acceptance/Modules/Contracts/TargetModuleCreator.php`
- `app/Acceptance/Modules/NwidartTargetModuleCreator.php`
- `app/Acceptance/Modules/TargetModuleDefinition.php`
- `app/Acceptance/Modules/TargetModuleValidator.php`
- `app/Acceptance/Modules/TargetModuleException.php`

### Typed operation data and handlers

- `app/Acceptance/Operations/Data/FileChange.php`
- `app/Acceptance/Operations/Data/ModuleChangeData.php`
- `app/Acceptance/Operations/Data/ValidationIssue.php`
- `app/Acceptance/Operations/Data/TargetModuleValidationData.php`
- `app/Acceptance/Operations/Handlers/CreateAcceptanceApp.php`
- `app/Acceptance/Operations/Handlers/ValidateAcceptanceApp.php`
- `app/Acceptance/Operations/Handlers/CreateAcceptanceComponent.php`
- `app/Acceptance/Operations/Handlers/ImportAcceptanceScenarios.php`

### TMS-specific stubs

- `stubs/acceptance/TargetModuleServiceProvider.stub`
- `stubs/acceptance/TargetAcceptanceApp.stub`
- `stubs/acceptance/AcceptanceComponent.stub`
- `stubs/acceptance/AcceptanceScenario.stub`
- `stubs/acceptance/SourceCaseMappings.stub`

### Tests

- `tests/Unit/TargetModuleCreatorTest.php`
- `tests/Unit/TargetModuleValidatorTest.php`
- `tests/Unit/AcceptanceHierarchyTest.php`
- `tests/Unit/SourceCaseMappingTest.php`
- `tests/Feature/TargetModuleOperationServiceTest.php`

## 9. Files to Edit

### Hierarchy/catalog source

- `app/Data/AcceptancePlan.php`
- `app/Data/AcceptancePlanItem.php`
- `app/Data/AcceptanceSelector.php`
- `app/Data/ScenarioDescriptor.php`
- `app/Services/AcceptanceAppRegistry.php`
- `app/Services/AcceptanceCatalog.php`
- `app/Services/AcceptancePlanner.php`

### Operation boundary and registration

- `app/Acceptance/Operations/AcceptanceOperationService.php`
- `app/Acceptance/Operations/Data/OperationRequest.php`
- `app/Acceptance/Operations/Data/OperationResult.php`
- `app/Providers/AppServiceProvider.php`

### Exact regression tests

- `tests/Unit/AcceptanceAppRegistryTest.php`
- `tests/Unit/AcceptanceCatalogTest.php`
- `tests/Unit/AcceptancePlannerTest.php`
- `tests/Unit/AcceptanceVariantDispatcherTest.php`
- `tests/Unit/AcceptanceOperationRegistryTest.php`
- `tests/Unit/AcceptanceOperationServiceTest.php`
- `tests/Feature/AcceptanceCatalogCommandTest.php`
- `tests/Feature/AcceptanceOperationCommandCompatibilityTest.php`
- `tests/Feature/AcceptanceRunCommandTest.php`

Governance maintenance directly required by execution may create the eventual Run Report and edit the project Pack/Run indexes under the shared maintenance exception. Normalization itself edits only this Pack and its Pack index row.

## 10. Files to Read / Reference

- project entry/profile and applicable shared rules;
- Decisions and accepted Packs/Runs in section 7;
- source files in sections 8–9;
- `composer.lock` only to confirm installed package versions;
- `Modules/Core/module.json`, `Modules/Core/composer.json`, and `Modules/Core/app/Providers/CoreServiceProvider.php` as read-only current-layout evidence; Core remains out of scope;
- `vendor/nwidart/laravel-modules/config/config.php`;
- `vendor/nwidart/laravel-modules/src/FileRepository.php`;
- `vendor/nwidart/laravel-modules/src/Contracts/RepositoryInterface.php`;
- `vendor/nwidart/laravel-modules/src/Generators/ModuleGenerator.php`;
- `vendor/nwidart/laravel-modules/src/Generators/FileGenerator.php`;
- `vendor/nwidart/laravel-modules/src/Support/Stub.php`;
- `vendor/nwidart/laravel-modules/src/Commands/Make/ModuleMakeCommand.php`;
- only the native JSON, Composer, config, seeder, provider, and test stubs actually evaluated for the normalized layout.

Do not read unrelated modules, Packs, Runs, Reviews, vendor packages, source corpora, or environment files.

## 11. Configuration / Settings Requirements

No project config file, `.env` key, Admin Setting, database setting, external integration, secret, cache, queue, or dependency change is allowed.

The service receives `Nwidart\Modules\Contracts\RepositoryInterface`, `Illuminate\Filesystem\Filesystem`, and the already-loaded Laravel config repository through dependency injection. It may read only these existing Nwidart keys:

- `modules.namespace`;
- `modules.paths.modules` and `modules.paths.app_folder`;
- `modules.paths.generator.provider`, `config`, and `seeder` paths;
- `modules.composer.vendor` and author values for native Composer-stub parity.

The service must not call `env()`, load `.env`, publish config, write `config/modules.php`, update `modules_statuses.json`, enable/disable a module, or run Composer. Tests override these values in an isolated container/repository and use obvious synthetic author data.

## 12. Do Not Change

- `Modules/Core/**`;
- any production target module, including future `Modules/DK/**`;
- `modules_statuses.json`;
- `composer.json`, `composer.lock`, `vendor/**`, package configuration, or generated autoload files;
- database workflow definitions, models, migrations, queue/reporting, browser execution, target access, routes, Web/API/MCP, UI/resources, or operator commands;
- accepted list/plan/run command options, default JSON field order/shape, exit codes, or Prompts behavior;
- `.env` or any environment file.

## 13. Clarification Questions Before Implementation

Normalization resolved the Draft questions as follows.

### Laravel-first application boundary

- Use Laravel first-party application APIs for orchestration: dependency injection through the Container, `Illuminate\Filesystem\Filesystem` for filesystem work, the Config repository for already-loaded settings, `Illuminate\Support\Str` for validated derived names/identifiers, and Laravel logging through the accepted operation boundary.
- Reuse Nwidart `RepositoryInterface` only for the configured module root and module path convention.
- Reuse Nwidart `Stub::render()` for native/TMS stub rendering and `FileGenerator::generate()` with overwrite disabled for each new file.
- Do not invoke `ModuleGenerator`, native Make commands, `Artisan::call()`, console components, Process, Composer, or the activator from application services because those boundaries are command-coupled or mutate state outside this Pack.
- Laravel Prompts belongs to Pack 0015's interactive Artisan client. It gathers and presents input there, then calls these same typed operations. Prompts, terminal state, questions, progress rendering, and human messages do not enter Pack 0010 services.

### Stable operation names

| Operation | Purpose | Stateful operation ID |
|---|---|---|
| `acceptance.app.create` | dry-run or create one minimal Acceptance App module | Yes, including dry-run attempts |
| `acceptance.app.validate` | inspect one Acceptance App module structure/registration | No |
| `acceptance.component.create` | dry-run or add one Component shell to a generated App | Yes |
| `acceptance.scenarios.import` | dry-run or reconcile typed mappings and non-executable scenario skeletons | Yes |

The existing `acceptance.plan` operation remains the catalog-validation operation used by the later `ValidateAcceptanceCatalogCommand`; no duplicate catalog validator is added.

### Request fields

- App creation: required `module_name`, `app_key`; optional `dry_run` default `true`;
- App validation: required `module_name`;
- Component creation: required `module_name`, `component_key`; optional `dry_run` default `true`;
- scenario import: required `module_name`, `mappings` as `list<SourceCaseMapping>`; optional `dry_run` default `true`.

No caller-supplied root path, destination path, namespace, PHP class name, provider class, stub path, force flag, or overwrite flag is accepted. Class/namespace segments are derived from validated identifiers and existing Nwidart configuration.

### Typed result DTOs

All new DTOs are immutable and version 1.

- `FileChange`: relative slash-normalized path plus `create`, `update_managed_region`, or `unchanged` action; no content or absolute path.
- `ModuleChangeData`: operation subject, module name, app key when known, optional Component key, `dryRun`, deterministic changes, created/updated/unchanged counts, and version.
- `ValidationIssue`: stable issue code plus one safe relative path when applicable; no raw parser/exception text.
- `TargetModuleValidationData`: module name, discovered app key or null, valid flag, deterministic issues, checked relative paths, and version.

`OperationResult` remains the shared status/classification/trace envelope. It accepts the two new operation-data types without JSON or console presentation.

### Hierarchy identity and legacy compatibility

- New providers implement `AcceptanceComponentProvider`, which extends the existing catalog provider with `components(): iterable<ComponentDescriptor>` and `suites(): iterable<SuiteDescriptor>`.
- Component, Suite, and Scenario keys are unique within one App. Scenario keys remain App-global in this Pack so the accepted `variants(string $scenarioKey)` and `resolveScenario(string $scenarioKey, string $variantKey)` contracts remain unambiguous.
- `SuiteDescriptor` carries its `componentKey`; `ScenarioDescriptor` carries `componentKey` and `suiteKey`.
- Existing providers that do not implement the new contract are normalized temporarily to reserved `component_key=legacy` and `suite_key=legacy`.
- Existing `ScenarioMetadata::$suites` remains a legacy classification list. Existing `--suite` behavior continues to filter that list. New typed selector dimensions are `component` and `suite_key`; their Artisan options are deferred to Pack 0015.
- `AcceptancePlanItem` gains typed `componentKey` and `suiteKey`; its canonical fingerprint identity becomes App/Component/Suite/Scenario/Variant.
- Existing CLI `items` JSON, `plan_version`, option set, exits, and field order remain unchanged in Pack 0010. The new typed properties are not silently added to the accepted legacy projection. Pack 0015 owns a separately normalized versioned client representation.

### Source mapping interchange

The application boundary receives already-constructed immutable `SourceCaseMapping` objects. Pack 0015 will own JSON/file parsing into these DTOs. Version-1 semantic fields are:

```text
sourceCaseId
disposition
componentKey
suiteKey
scenarioKey
variantKey
replacementComponentKey
replacementSuiteKey
replacementScenarioKey
replacementVariantKey
reason
coveredAssertions
uncoveredAssertions
version
```

Rules:

- `automated_full` and `automated_partial` require their own complete Component/Suite/Scenario/Variant tuple;
- `automated_partial` requires a reason plus non-empty covered and uncovered assertion IDs;
- `merged_equivalent` requires a reason, covered/uncovered lists, and a complete replacement tuple;
- excluded dispositions require a reason and non-empty uncovered assertions and must not carry an executable tuple;
- only `automated_full` and `automated_partial` may produce scenario skeletons;
- generated scenario skeletons use the existing non-executable `not-implemented` metadata state and contain no workflow steps, target selector, request, account, URL, credential, or payload;
- there is no `manual-only` coverage disposition and import must reject any unrecognized disposition before filesystem mutation.

## 14. Multilingual / Translation Requirements

No runtime visible text, translation key, Persian message, Web UI, Admin UI, or RTL/LTR change is allowed. Identifiers, DTO properties, issue/error codes, and generated PHP symbols are English/language-neutral. Pack 0015 owns Persian operator presentation and guide updates.

## 15. UI / Admin UI Requirements

No UI or Admin UI. No permission/menu/form/table/action/dashboard change.

## 16. Operator Documentation Requirements

No operator guide is changed before commands exist. Pack 0015 must later document the accepted generated layout, disabled-by-default activation boundary, dry-run/no-overwrite policy, validation issue codes, Component/import workflow, and native Nwidart relationship.

## 17. Implementation Rules

### Generated target layout

App creation plans exactly this minimal layout beneath the injected Nwidart module root:

```text
<Module>/module.json
<Module>/composer.json
<Module>/app/Providers/<Module>ServiceProvider.php
<Module>/app/Acceptance/<Module>AcceptanceApp.php
<Module>/app/Acceptance/Shared/.gitkeep
<Module>/app/Acceptance/Components/.gitkeep
<Module>/app/Acceptance/Coverage/SourceCaseMappings.php
<Module>/config/config.php
<Module>/database/factories/.gitkeep
<Module>/database/migrations/.gitkeep
<Module>/database/seeders/<Module>DatabaseSeeder.php
<Module>/tests/Feature/.gitkeep
<Module>/tests/Unit/.gitkeep
```

No routes, resources, controller, model, migration, package asset, Event provider, Route provider, command, browser code, owner documentation, or target behavior is generated.

Native Nwidart JSON, Composer, config, and seeder stubs are rendered where their output matches this layout. TMS stubs provide only the missing target provider, Acceptance App, Component, scenario skeleton, and mapping registry conventions. `module.json` explicitly names the generated module service provider. The provider explicitly registers the generated Acceptance App with `AcceptanceAppRegistry`. Creation does not activate the module or edit the root Composer/autoload/status files.

### Component and import layout

- Component creation creates `app/Acceptance/Components/<ComponentClass>/<ComponentClass>AcceptanceComponent.php` and updates only the exact managed Component registry region in the generated App class.
- Scenario import creates `app/Acceptance/Components/<ComponentClass>/Scenarios/<ScenarioClass>.php` only for full/partial automated mappings and updates only exact managed regions in the generated Component and `SourceCaseMappings.php` files.
- Suite identity is declared through `SuiteDescriptor` entries in the Component's managed region; a Suite directory/class is not generated.
- Two distinct keys that derive to the same Studly class name are a collision and are rejected.

### Write and collision policy

- `dry_run=true` is the default and performs no mkdir, file write, rename, activation, cache, Composer, or repository reset.
- Target creation fails with `target_module_exists` if either the repository or filesystem already contains the module.
- New files always use Nwidart `FileGenerator` with overwrite disabled.
- Updates are limited to exact TMS-managed marker regions in files produced by these stubs. Exact existing entries are idempotent and reported `unchanged`; missing/altered markers or conflicting entries fail before mutation.
- All rendered content and collision checks complete before the first write.
- Actual target creation writes to a unique sibling staging directory under the validated module root and renames it to the final module directory only after validation. On failure, cleanup is limited to that verified staging directory.
- Component/import updates stage replacement files beside their verified target files and use same-volume atomic rename. They never replace content outside managed regions.
- No force/overwrite option exists in this Pack.

### Catalog behavior

- Validate all Component and Suite descriptors without materializing executable scenarios.
- Reject duplicate Component, Suite, Scenario, and Variant keys deterministically.
- Reject Suites referencing an unknown Component and Scenarios referencing an unknown or mismatched Component/Suite.
- Preserve bounded iteration and accepted lazy-resolution/side-effect protections.
- Preserve legacy providers through the temporary reserved hierarchy and keep their current execution resolution behavior.

## 18. Architecture Constraints

TMS project code owns generic hierarchy, mapping, module creation, validation, and operations. Target-specific Apps, Components, mappings, and scenarios remain inside their target module. Core remains target-neutral and unchanged.

Services and DTOs must be callable without Artisan, Symfony console, Prompts, terminal state, HTTP, MCP, process execution, or client serialization. Generated source is explicit and code-owned; no filesystem discovery becomes an executable registration mechanism.

## 19. Validation Rules

`TargetModuleValidator` performs inspection only and returns deterministic `ValidationIssue` DTOs. It checks:

- the derived module path is contained by the injected module root;
- every required path in section 17 exists with the expected file/directory kind;
- `module.json` is valid JSON with the exact module name, alias, and generated provider;
- `composer.json` is valid JSON with the expected Nwidart namespace/app/test mappings;
- the provider namespace/class is consistent and contains the explicit Acceptance App registration hook;
- the Acceptance App has the approved app key and implements the required catalog/component contracts;
- managed-region markers are present and balanced;
- Component/Suite/Scenario references and class-name derivations are unique;
- mapping entries satisfy Decision 0012 and never create an executable `manual-only` scenario;
- no route/resource/controller/model/migration or other prohibited generated surface appears in the created skeleton.

Validation does not boot the generated provider, execute a scenario, enable a module, modify a file, or claim target readiness.

## 20. Security Rules

- Module names must be canonical Studly identifiers matching `^[A-Z][A-Za-z0-9]{0,63}$`.
- App/Component/Suite/Scenario/Variant/assertion keys use the existing language-neutral key rule and maximum length; source-case IDs use a separately bounded `^[A-Za-z0-9][A-Za-z0-9._:-]{0,127}$` rule.
- Reject separators, dot segments, drive/UNC prefixes, NUL/control characters, reserved `legacy` use by new hierarchy input, namespace syntax, and ambiguous class-name collisions.
- Resolve and compare normalized absolute module-root/staging/final paths before every write or cleanup. On Windows, compare case-insensitively and require the final path to remain a direct child of the injected module root.
- Stubs receive only validated/derived replacements. Never place raw exceptions, paths outside the module root, credentials, accounts, URLs, selectors, payloads, environment values, or arbitrary PHP/class text into source or results.
- Tests use only obvious synthetic sentinels and a unique temporary module root. No real target, module activation, root Composer change, environment file, or network call is allowed.

## 21. Error Handling / Logging / Traceability Requirements

### Stable operation error codes

| Code | Status/classification | Meaning |
|---|---|---|
| `target_module_name_invalid` | rejected; non-retryable; permanent; no admin action | invalid/non-canonical module name |
| `acceptance_hierarchy_key_invalid` | rejected; non-retryable; permanent; no admin action | invalid/reserved App, Component, Suite, Scenario, Variant, or assertion key |
| `target_module_path_invalid` | rejected; non-retryable; permanent; admin action required only when injected root/config is invalid | derived path escape or unsafe repository root |
| `target_module_not_found` | rejected; non-retryable; permanent; no admin action | Component/import/validate target does not exist |
| `target_module_exists` | rejected; non-retryable; permanent; no admin action | App creation would replace an existing module |
| `target_module_path_collision` | rejected; non-retryable; permanent; no admin action | planned file/class/managed entry collides |
| `acceptance_source_mapping_invalid` | rejected; non-retryable; permanent; no admin action | mapping fields/disposition invariants fail |
| `acceptance_source_mapping_duplicate` | rejected; non-retryable; permanent; no admin action | repeated source ID or executable/replacement identity |
| `acceptance_hierarchy_duplicate` | failed; non-retryable; permanent; admin action required | provider/runtime hierarchy definition is ambiguous |
| `target_module_generation_failed` | failed; retryable/permanent unknown; admin action required | safe fallback for staging/render/write/rename failure |
| `target_module_validation_failed` | failed; retryable/permanent unknown; admin action required | safe fallback when validation cannot complete |

### Stable validation issue codes

`target_module_required_path_missing`, `target_module_manifest_invalid`, `target_module_composer_invalid`, `target_module_provider_invalid`, `target_module_registration_missing`, `target_module_acceptance_app_invalid`, `target_module_managed_region_invalid`, `target_module_surface_forbidden`, `acceptance_hierarchy_invalid`, and `acceptance_source_mapping_invalid`.

A completed validation operation returns `succeeded` with `TargetModuleValidationData`; `valid=false` and issue codes describe the inspected module. It is an operation failure only when safe inspection cannot complete.

### Logging and trace

- Reuse `tms.acceptance.operation.failed` for rejected/failed results and `tms.acceptance.operation.completed` for successful stateful App-create/Component-create/import operations, including dry-run attempts.
- Reuse `correlation_id`; generate `operation_id` for the three stateful operation names.
- The fixed additional context allowlist is `module_name`, `app_key`, optional `component_key`, `dry_run`, and `change_count`. Do not log absolute paths, source-case/assertion IDs, generated content, mappings, request arrays, raw exceptions, or staging names.
- Successful validation remains silent. Validation issues are returned as typed data and are not logged one-by-one.
- No audit store or durable operation record is added in this Pack.

## 22. Data Model / Migration / Relationship Requirements

No database model, migration, table, column, foreign key, relationship, backfill, persisted snapshot, or data rollback change.

Descriptor/request/result DTO shapes change in source only. Legacy provider normalization is temporary and explicit. Coverage mappings remain target-owned source code; this Pack adds their generic immutable contract and generation convention only.

## 23. Commenting Requirements

Follow `docs/ai/rules/COMMENTING-RULES.md`. Explain only:

- why the adapter uses lower-level Nwidart services instead of command-coupled `ModuleGenerator`;
- the generated versus TMS-managed-region ownership boundary;
- App-global hierarchy-key uniqueness and temporary `legacy` normalization;
- why imported scenario skeletons remain non-executable.

Do not add broad comments, copied Pack text, or unrelated annotation cleanup.

## 24. Testing Requirements

- Direct service tests use an injected Nwidart repository rooted at a unique temporary directory; no test writes under repository `Modules/`.
- Cleanup resolves and verifies the exact temporary root before recursive removal and runs in `finally`/teardown.
- Creation tests compare dry-run and write plans, exact relative trees/content, no-overwrite/idempotent managed reconciliation, native-stub output, rollback after injected failure, traversal/class collision rejection, and absence of console/process/activator calls.
- Validator tests cover every stable issue code, valid minimal layout, malformed JSON/provider/Composer/marker content, registration missing, forbidden surface, and read-only behavior.
- Hierarchy tests cover legacy normalization, Component/Suite references, App-global duplicates, deterministic filters/order/fingerprint identity, accepted legacy CLI projection, and no executable-provider resolution during inspection.
- Mapping tests cover all five Decision 0012 dispositions, field requirements, duplicates, replacement links, no `manual-only`, and no scenario generation for merged/excluded entries.
- Operation tests call the shared service directly and assert DTO properties, request validation, operation IDs, classification, exact logs, dry-run/no side effects, safe fallback, and sentinel omission without parsing console output.
- Regression tests preserve accepted list/plan/run JSON/options/exits and existing Pack 0009 isolation/side-effect guarantees.

## 25. Acceptance Checklist

- [ ] explicit Component and Suite keys exist in descriptors, selectors, plan items, canonical identity, and typed service data;
- [ ] legacy providers normalize to the reserved hierarchy without changing accepted CLI JSON/options/exits;
- [ ] all four new operations use immutable semantic DTOs and remain callable without Artisan;
- [ ] lower-level Nwidart repository/Stub/FileGenerator APIs are reused and command/process/activator boundaries are absent;
- [ ] dry-run is default, deterministic, and side-effect free;
- [ ] creation is staging-based, collision-safe, rollback-bounded, and never overwrites operator-owned content;
- [ ] generated module structure/provider registration validates but remains disabled until a later approved Pack activates it;
- [ ] typed source mappings implement the five Decision 0012 dispositions and import never creates executable workflow steps;
- [ ] no Core/target/database/queue/UI/API/dependency/environment scope is added;
- [ ] all scoped validation and regression tests pass.

## 26. Tests to Add

| Exact test file | Main behavior |
|---|---|
| `tests/Unit/TargetModuleCreatorTest.php` | Laravel/Nwidart boundary, exact layout, dry-run, staging/rollback, managed reconciliation, collision/path safety, no console/process/activation |
| `tests/Unit/TargetModuleValidatorTest.php` | valid structure and every stable validation issue without mutation |
| `tests/Unit/AcceptanceHierarchyTest.php` | typed hierarchy, legacy defaults, unique/referential integrity, deterministic selection and canonical identity |
| `tests/Unit/SourceCaseMappingTest.php` | five dispositions, required fields, links, duplicates and non-executable import policy |
| `tests/Feature/TargetModuleOperationServiceTest.php` | four registered operations, typed results, classifications/logs/traces, dry-run/write in temp root and sensitive sentinel omission |

Update only the exact regression files in section 9; do not introduce a shared test-framework refactor.

## 27. Tests to Run

Only after separate execution approval. File/source/Git inspection is `safe-read-only`; syntax, Pint and tests are `validation-or-test`; temporary creation writes are authorized only inside the test-created verified root.

Run these exact test groups through the accepted Pack 0009 absent-dotenv Laravel entrypoint with `APP_ENV=testing`, `DB_CONNECTION=sqlite`, and `DB_DATABASE=:memory:` process values:

```text
test --compact tests/Unit/TargetModuleCreatorTest.php tests/Unit/TargetModuleValidatorTest.php tests/Unit/AcceptanceHierarchyTest.php tests/Unit/SourceCaseMappingTest.php
test --compact tests/Feature/TargetModuleOperationServiceTest.php tests/Unit/AcceptanceAppRegistryTest.php tests/Unit/AcceptanceCatalogTest.php tests/Unit/AcceptancePlannerTest.php tests/Unit/AcceptanceVariantDispatcherTest.php tests/Unit/AcceptanceOperationRegistryTest.php tests/Unit/AcceptanceOperationServiceTest.php
test --compact tests/Feature/AcceptanceOperationCommandCompatibilityTest.php tests/Feature/AcceptanceCatalogCommandTest.php tests/Feature/AcceptanceRunCommandTest.php
list --format=json
```

For each group, use the same guarded form accepted in Pack 0009:

```powershell
php -r 'define("LARAVEL_START", microtime(true)); require "vendor/autoload.php"; $pack10App = require "bootstrap/app.php"; $pack10App->loadEnvironmentFrom("__tms_pack_0010_no_dotenv__"); exit($pack10App->handleCommand(new \Symfony\Component\Console\Input\ArgvInput));' -- <exact group above>
```

Also run:

- `php -l` once for every created or edited PHP file in sections 8–9;
- `php vendor/bin/pint --test` limited to the created/edited PHP paths in sections 8–9;
- `git diff --check`, `git status --short --branch`, `git diff --name-only`, untracked-file review, and scoped diff review;
- static scans of the new module/operation layer for `Artisan`, `Command`, `Console`, `Process`, `composer`, activator mutation, `env(`, dotenv reads, absolute-path output, JSON/client rendering, `force`, and overwrite enablement; inspect every match;
- exact generated-tree comparison and proof that repository `Modules/`, `modules_statuses.json`, Composer files, vendor, and environment files are byte-unchanged;
- Pack section/order/reference/index checks.

Do not run ordinary dotenv-reading Artisan, `module:make`, `module:enable`, Composer, dependency commands, migrations, browser/target/network/queue/server commands, the full unrelated suite, or a persistent module creation. Stop if the isolated bootstrap or temporary-root containment cannot be proven.

## 28. Expected Output

- explicit hierarchy and source-mapping contracts;
- four transport-neutral typed operations;
- Nwidart-aware dry-run/create/reconcile/validate services;
- minimal reusable TMS target-module stubs;
- focused tests and predecessor regressions;
- Agent Final Report and, after later gates, a persistent Run Report and index synchronization.

No target module, owner activation, complete Artisan client, executable target workflow, database workflow, or Web/API/MCP adapter is delivered.

## 29. Operator Execution Checklist

### Before AI Execution

- Approve the normalized App-global key uniqueness and temporary `legacy` Component/Suite defaults.
- Approve the exact generated layout, disabled-by-default activation boundary, four operation names, typed DTO/mapping fields, validation/error codes, and managed-region policy.
- Approve dry-run by default, no force/overwrite option, sibling staging plus bounded cleanup, and temp-root-only write tests.
- Approve adding the exact new operation-boundary files and regression-test edits in sections 8–9.
- Confirm continued execution on `main` while preserving the pre-existing dirty documentation baseline recorded in the pre-execution gate.
- Approve only fake/synthetic, absent-dotenv, disposable in-memory/temp-root validation; no real module/target/browser/database/network/Composer/activation operation.

### After AI Execution

- Compare one target dry-run plan with one temporary generated tree and validator result.
- Inspect one Component creation and one mixed mapping import containing full, partial, merged, and excluded dispositions.
- Confirm imported skeletons are non-executable and excluded/merged mappings create no scenario file.
- Confirm legacy list/plan/run JSON/options/exits remain accepted and no persistent module/status/autoload file changed.

## 30. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md`. Report:

- Nwidart APIs reused and command-coupled APIs intentionally excluded;
- exact generated paths and managed-region/collision behavior;
- hierarchy/legacy compatibility and CLI preservation;
- mapping/import behavior and non-executable skeleton evidence;
- new operations, DTOs, codes, classification, logs, trace IDs, and sensitive-output checks;
- commands/tests actually run, failures/fixes, temp cleanup, limitations, and unchanged protected paths;
- changed-file scope, documentation maintenance, index status, and deferred target/CLI work.

Do not create an accepted Run, claim implementation, or claim target readiness before the separate execution, post-execution, final-report, acceptance, and commit gates.

## 31. Review Checklist

Review Nwidart coupling, typed/client-neutral results, path containment, staging cleanup, no-overwrite/idempotency, managed markers, generated layout, disabled activation, hierarchy uniqueness/references, legacy compatibility, source mapping invariants, non-executable skeletons, operation classification/log safety, regression evidence, scope, and protected paths.

## 32. Rollback / Safety Notes

Implementation rollback removes only Pack 0010 project files/wiring/tests and restores the edited generic catalog/operation files. It must not delete operator-created or production modules.

Test cleanup may remove only a resolved directory created by the current test under its unique temporary root. Staging cleanup may remove only the verified staging directory created by the current operation beneath the injected module root. No rollback edits `modules_statuses.json`, Composer/autoload, vendor, `.env`, Core, or a target owner.

Normalization changed only this Pack and its project Pack-index row. Preserve all unrelated dirty documentation and accepted Pack 0009 history.

## 33. Stop Conditions

Stop for:

- missing/revoked Pack 0009 acceptance or changed accepted operation boundary;
- operator rejection or alteration of a normalized contract in section 13 or checklist 29;
- inability to implement without console/Process/Composer/activator or direct vendor changes;
- ambiguous/caller-controlled path, path escape, unsafe cleanup, overwrite requirement, missing managed marker, or class/key collision;
- required file outside sections 8–9 other than mandatory governance maintenance;
- required Core, production target, schema, queue, UI/API/MCP, dependency, config, status-file, environment, or operator-command change;
- incompatible accepted list/plan/run JSON/options/exits;
- executable workflow generation, target access, secret/sensitive output, or unsafe log context;
- unproven absent-dotenv/temp-root isolation;
- canonical/source conflict requiring Change Request or Remediation;
- missing separate execution approval.

No commit is authorized by this Pack.

## 34. Open Questions

No unresolved design question remains inside the normalized contract.

Execution is blocked only on the explicit operator approval in section 29. Pack 0015 still owns command names/signatures, Persian presentation, JSON/file parsing into `SourceCaseMapping`, versioned hierarchy output, help, exits, and operator documentation. Pack 0016 owns actual DK module creation, activation/status entry, owner documentation, and target-specific registration review. Target owners own real mappings and executable scenarios.
