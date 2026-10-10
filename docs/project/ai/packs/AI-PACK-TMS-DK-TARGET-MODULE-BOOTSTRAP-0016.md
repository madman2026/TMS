# AI-PACK-TMS-DK-TARGET-MODULE-BOOTSTRAP-0016 — DK target module and owner bootstrap

Status: accepted — operator accepted 2026-10-10; technical validation passed

Generated: 2026-10-07

Normalized: 2026-10-10

Owner: TMS project

Decision: Decisions 0005, 0007, 0012, 0014, and 0015.

Execution / Acceptance / Commit: Packs 0010–0015, Core Pack 0003, and Remediations 0001–0002 are accepted and committed. Pack 0016 execution and validation completed, the operator accepted the result, and separately authorized its commit on 2026-10-10.

## 1. Task ID

AI-PACK-TMS-DK-TARGET-MODULE-BOOTSTRAP-0016

## 2. Task Title

Create and activate the empty DK Nwidart target module and its owner-local AI documentation root.

## 3. Goal

Establish DK as the first real target-system module and an independently governed documentation owner, while registering one empty dk Acceptance App through the accepted hierarchy.

This Pack creates no DK target behavior, Notification Delivery behavior, Component, Suite, Scenario, Variant, Step, source mapping, credential, selector, fixture, external request, or database workflow.

## 4. Context

Decision 0015 fixes the only executable identity:

~~~text
App -> Component -> Suite -> Scenario -> Variant -> Step
~~~

Accepted Pack 0010 owns the reusable module creator and validator. Accepted Pack 0015 exposes them through the Artisan client. The obsolete Draft instruction to call native module:make is removed. The approved creation boundary is acceptance:app:create, which uses the accepted lower-level Nwidart repository, stub, and file APIs without ModuleGenerator, native make commands, force, overwrite, Composer, or activation.

The creator leaves a new module disabled. This Pack adds owner documents and tests, refreshes local Composer autoload metadata without scripts or dependency resolution, validates the disabled module, and then activates DK through Nwidart's file activator as the last bootstrap mutation. A fresh boot must load DKServiceProvider, which registers DKAcceptanceApp with AcceptanceAppRegistry through the generated afterResolving hook.

DK is the target boundary. Notification Delivery (ND) is reserved as a future Component inside Modules/DK/app/Acceptance/Components/NotificationDelivery/. It is not a Nwidart module and is not created here.

## 5. Related Release / Phase

Decision 0015, stage 10.

## 6. Related Epic / Feature / Story

First real target-system onboarding and owner activation.

## 7. Source References

- docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md
- docs/project/ai/decisions/TMS-DECISION-0007-nwidart-target-modules-and-component-hierarchy.md
- docs/project/ai/decisions/TMS-DECISION-0012-automated-e2e-coverage-and-traceability.md
- docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md
- docs/project/ai/decisions/TMS-DECISION-0015-clean-acceptance-hierarchy-cutover.md
- accepted Pack 0010 and Run 001 for module creation/validation
- accepted Pack 0015 and Run 001 for the Artisan client and guarded command contract
- current source and installed Nwidart v12.0.5 files in section 10

## 8. Files to Create

Exact acceptance.app.create output:

- Modules/DK/module.json
- Modules/DK/composer.json
- Modules/DK/app/Providers/DKServiceProvider.php
- Modules/DK/app/Acceptance/DKAcceptanceApp.php
- Modules/DK/app/Acceptance/Shared/.gitkeep
- Modules/DK/app/Acceptance/Components/.gitkeep
- Modules/DK/app/Acceptance/Coverage/SourceCaseMappings.php
- Modules/DK/config/config.php
- Modules/DK/database/factories/.gitkeep
- Modules/DK/database/migrations/.gitkeep
- Modules/DK/database/seeders/DKDatabaseSeeder.php
- Modules/DK/tests/Feature/.gitkeep
- Modules/DK/tests/Unit/.gitkeep

DK owner router/documentation:

- Modules/DK/AGENTS.md
- Modules/DK/docs/ai/README.md
- Modules/DK/docs/ai/manifest.yaml
- Modules/DK/docs/ai/GOVERNANCE-PROFILE.md
- Modules/DK/docs/ai/canonical/CANONICAL-INDEX.md
- Modules/DK/docs/ai/decisions/DECISIONS-INDEX.md
- Modules/DK/docs/ai/changes/CHANGES-INDEX.md
- Modules/DK/docs/ai/remediations/REMEDIATIONS-INDEX.md
- Modules/DK/docs/ai/packs/PACKS-INDEX.md
- Modules/DK/docs/ai/runs/RUNS-INDEX.md
- Modules/DK/docs/ai/reviews/REVIEWS-INDEX.md
- Modules/DK/docs/ai/guides/GUIDES-INDEX.md
- Modules/DK/docs/ai/references/REFERENCES-INDEX.md

Tests and execution record:

- Modules/DK/tests/Unit/DKAcceptanceAppTest.php
- tests/Feature/DKModuleBootstrapTest.php
- docs/project/ai/runs/AI-PACK-TMS-DK-TARGET-MODULE-BOOTSTRAP-0016-run-001.md

## 9. Files to Edit

- modules_statuses.json
- phpunit.xml
- tests/Feature/AcceptanceOperationCommandContractTest.php
- docs/modules/MODULES-DOCS-INDEX.md
- docs/project/ai/packs/PACKS-INDEX.md
- docs/project/ai/runs/RUNS-INDEX.md
- this Pack for required lifecycle/status synchronization

docs/project/PROJECT-DOCS-INDEX.md already routes module discovery correctly and remains unchanged unless execution proves a conflict. A conflict is a stop condition, not implicit scope expansion.

## 10. Files to Read / Reference

- repository startup files, project entry/profile, and applicable shared rules
- Decisions and accepted Packs/Runs in section 7
- docs/modules/MODULES-DOCS-INDEX.md
- docs/project/references/REFERENCES-INDEX.md
- docs/project/references/SOURCE-DOCS-INDEX.md
- docs/project/references/TMS-SOURCE-STRUCTURE-SUMMARY.md
- docs/project/ai/guides/ACCEPTANCE-CLI.fa.md
- app/Acceptance/Modules/NwidartTargetModuleCreator.php
- app/Acceptance/Modules/TargetModuleValidator.php
- app/Acceptance/Operations/Handlers/CreateAcceptanceApp.php
- app/Acceptance/Operations/Handlers/ValidateAcceptanceApp.php
- app/Console/Commands/CreateAcceptanceAppCommand.php
- app/Console/Commands/ValidateAcceptanceAppCommand.php
- app/Services/AcceptanceAppRegistry.php
- app/Services/AcceptanceCatalog.php
- stubs/acceptance/TargetModuleServiceProvider.stub
- stubs/acceptance/TargetAcceptanceApp.stub
- composer.json, phpunit.xml, and modules_statuses.json
- Modules/Core/AGENTS.md
- Modules/Core/docs/ai/README.md
- Modules/Core/docs/ai/manifest.yaml
- Modules/Core/docs/ai/GOVERNANCE-PROFILE.md
- Modules/Core/docs/ai/canonical/CANONICAL-INDEX.md
- Modules/Core/docs/ai/decisions/DECISIONS-INDEX.md
- Modules/Core/docs/ai/changes/CHANGES-INDEX.md
- Modules/Core/docs/ai/remediations/REMEDIATIONS-INDEX.md
- Modules/Core/docs/ai/packs/PACKS-INDEX.md
- Modules/Core/docs/ai/runs/RUNS-INDEX.md
- Modules/Core/docs/ai/reviews/REVIEWS-INDEX.md
- Modules/Core/docs/ai/guides/GUIDES-INDEX.md
- Modules/Core/docs/ai/references/REFERENCES-INDEX.md
- vendor/nwidart/laravel-modules/config/config.php
- vendor/nwidart/laravel-modules/src/Activators/FileActivator.php
- vendor/nwidart/laravel-modules/src/ModuleManifest.php
- vendor/nwidart/laravel-modules/src/ModulesServiceProvider.php
- vendor/nwidart/laravel-modules/src/Laravel/Module.php
- tests/Feature/AcceptanceOperationCommandContractTest.php
- tests/Unit/TargetModuleCreatorTest.php
- tests/Unit/TargetModuleValidatorTest.php
- tests/Unit/AcceptanceHierarchyTest.php
- tests/Feature/TargetModuleOperationServiceTest.php
- tests/Feature/AcceptanceOperatorCommandsTest.php
- tests/Feature/AcceptanceOperatorJsonContractTest.php
- tests/Feature/AcceptanceCatalogCommandTest.php
- docs/ai/rules/COMMENTING-RULES.md
- docs/ai/rules/REPORTING-RULES.md

Do not read unrelated Packs, Runs, Reviews, modules, source corpora, environment files, target configuration, or external repositories.

## 11. Configuration / Settings Requirements

Configuration / Settings Impact: Yes, limited to:

- modules_statuses.json gains exactly DK: true through Nwidart after all pre-activation work succeeds; Core remains enabled.
- phpunit.xml gains DK Unit and DK Feature suites plus Modules/DK/app source coverage. Existing values remain unchanged.
- One composer dump-autoload --no-scripts --no-interaction is allowed with COMPOSER_DISABLE_NETWORK=1 only to regenerate ignored local autoload metadata from the existing Modules/*/composer.json merge pattern.
- composer.json, composer.lock, dependencies, root/provider config, .env files, Admin Settings, database, queue, cache, log, and external settings do not change.
- Laravel commands/tests use the accepted absent-dotenv bootstrap and process-local APP_ENV=testing, DB_CONNECTION=sqlite, DB_DATABASE=:memory:.
- No operator secret, URL, account, token, target configuration, or manual setup is required.

Any download, script execution, dependency resolution, or tracked Composer change is a stop condition.

## 12. Do Not Change

- Modules/Core/**
- root application source, accepted services, commands, DTOs, registries, providers, stubs, models, migrations, routes, resources, assets, views, or dependencies
- composer.json, composer.lock, package manifests/locks, or tracked generated artifacts
- .env or any environment file
- target settings, credentials, accounts, URLs, selectors, fixtures, payloads, browser/queue/cache/database state
- any DK Component, Suite, Scenario, Variant, Step, mapping entry, target action, adapter, resource, route, controller, model, migration, view, or asset
- any stage 11–15 owner-local lifecycle record
- any external DK/ND repository or system

## 13. Clarification Questions Before Implementation

Normalization resolved all Draft questions.

Creation:

- Module name is DK; App key is dk.
- Run acceptance:app:create DK dk first as dry-run, then with --apply --confirm.
- Dry-run must return the exact 13-file generator plan with no filesystem mutation.
- Native module:make, direct stub copying, force/overwrite, manual module construction, and root provider registration are forbidden.

Order:

1. Verify preconditions and dry-run.
2. Apply creation.
3. Add owner docs/tests and declared integration edits.
4. Run static checks not requiring activation.
5. Refresh Composer autoload offline without scripts; verify no tracked Composer diff.
6. Run acceptance:app:validate DK while disabled.
7. Activate with Nwidart module:enable DK through the absent-dotenv bootstrap.
8. In a fresh process prove discovery, provider loading, registration, empty-catalog validity, and regressions.

Activation is last. Any earlier failure leaves DK absent from or disabled in modules_statuses.json.

Provider/registry:

- Keep generated DKServiceProvider and DKAcceptanceApp consistent with accepted Pack 0010 stubs.
- Registration stays in the module composition root via afterResolving.
- Do not edit AppServiceProvider, add a second registration hook, or introduce a new interface.
- The App returns key dk and catalog version v1, yields no hierarchy/mappings, and resolves no tuple.

Owner documentation:

- Modules/DK/AGENTS.md routes owner-local work through DK entry/manifest/profile and cross-owner work back to the project owner.
- Manifest schema version is 1; owner id/name/type/layer are dk, DK, target-module, and 3.
- Manifest declares exact router, project profile, DK profile, source/documentation roots, and all nine indexes.
- Execution status was active — bootstrap executed; acceptance pending, and is now active — bootstrap accepted after operator acceptance.
- DK profile reserves ND as an internal future Component, preserves testing/staging-only synthetic-data rules, forbids real target access/secrets, and requires accepted DK-local Packs for later work.
- Owner indexes are empty navigation records. Project Pack 0016 is not duplicated locally.
- No stage 11–15 record is created before Pack 0016 acceptance.

Tests:

- phpunit.xml adds DK suites/source without changing existing configuration.
- The existing empty-map projection test explicitly binds an empty AcceptanceAppRegistry so it remains isolated from installed target Apps.
- A project feature test proves default application integration; a DK unit test proves the direct empty App contract.

## 14. Multilingual / Translation Requirements

No runtime text, translation, locale, API message, UI label, or RTL/LTR change. Runtime identifiers remain English/language-neutral. Owner governance documents use the repository's existing English structure. The Persian CLI guide already documents generic create/validate usage and requires no DK-specific edit.

## 15. UI / Admin UI Requirements

No UI/Admin UI, route, menu, form, table, action, dashboard, permission, view, or asset change.

## 16. Operator Documentation Requirements

Only the DK owner routing README/profile is added, explaining navigation and safety boundaries. It must not document target setup or behavior. No shared CLI guide update is required.

## 17. Implementation Rules

- Use the existing CLI/service operation; do not reproduce generator logic.
- Compare dry-run output to section 8 before apply.
- Preserve generator output except declared tests; keep empty structural directories.
- Do not create NotificationDelivery; Components/.gitkeep is the only placeholder.
- Create router, entry, manifest, profile, and all indexes atomically before module discovery is updated.
- Every owner path must resolve; empty indexes state that no accepted local record exists.
- Module discovery gains one DK row and no longer says Core is the only owner.
- Autoload refresh occurs before activation and changes only ignored metadata.
- Activate through Nwidart; status must contain exactly enabled Core and DK entries.
- The generated module provider is the only runtime hook.
- Use PHPUnit 11 and repository naming/style.
- Keep tests focused; only isolate the existing empty-registry regression without changing its assertions.

## 18. Architecture Constraints

DK is one Nwidart target module and independent owner. ND is a future internal Component. Generic Acceptance contracts, operations, orchestration, clients, and runtime remain project/Core-owned.

The provider is a composition root plugging DK into the existing registry contract. It must not bypass hierarchy validation, reach into Core internals, write to the database, or add a second discovery mechanism. Executable scenario registration remains explicit code.

## 19. Validation Rules

Prove:

- dry-run is exact, deterministic, and side-effect free
- applied inventory matches section 8 with no forbidden surface
- validator returns succeeded, valid=true, app_key=dk, no issues, and safe relative paths
- autoload refresh changes no tracked Composer/dependency file
- Nwidart discovers and enables DK, and a fresh process loads its provider
- default registry contains dk once; catalog reports dk => v1 and empty hierarchy/mappings
- App resolves no tuple and performs no target/browser/database/network/queue work
- every owner manifest/index/router path resolves and module discovery matches
- phpunit.xml includes DK suites/source without disturbing existing configuration
- no Component implementation, scenario, mapping entry, target value, route/resource/UI/database surface, or environment file appears
- scoped/full tests, references, whitespace, and Git checks pass

## 20. Security Rules

- Never read or edit environment files.
- Never connect to DK/ND, browser, network, queue worker, or non-test database.
- Store no URL, credential, token, cookie, session, selector, payload, personal identifier, production value, or real target detail.
- Use only fixed identifiers DK and dk.
- Do not expose raw exceptions, sensitive absolute paths, environment values, or generated autoload contents.
- Disable Composer network access; attempted network/dependency mutation stops execution.

## 21. Error Handling / Logging / Traceability Requirements

No new runtime error code, exception, status, retry policy, log event, or trace identifier.

- Creation/validation reuse Pack 0010 stable contracts and Pack 0015 safe CLI projection.
- Creation retains correlation_id, operation_id, and existing operation completed/failed logging.
- Activation/autoload/bootstrap failures are execution stop conditions recorded in the Run, not invented domain errors.
- Failure before activation leaves DK disabled. Failure after activation follows section 32 rollback before any boot is considered valid.
- Reports use safe summaries and relative paths only.

## 22. Data Model / Migration / Relationship Requirements

No model, table, migration PHP file, column, index, key, relationship, seed data, backfill, database command, or snapshot.

Generated empty migration/factory directories and seeder are structural only. Do not invoke the seeder or any migration.

## 23. Commenting Requirements

Follow docs/ai/rules/COMMENTING-RULES.md. Preserve accurate generated comments. Add comments only for a non-obvious registration/test-isolation boundary.

## 24. Testing Requirements

- No test calls a target, browser, network, queue worker, Composer, migration, seeder, or non-test database.
- DKAcceptanceAppTest constructs the App directly and proves key/version, complete interfaces, empty iterables/mappings, and null resolution.
- DKModuleBootstrapTest boots guarded Laravel and proves module discovery/status, provider registration, validator success, one dk entry, dk => v1, and empty valid catalog.
- The edited command-contract test explicitly binds an empty registry before its empty-map projection assertions.
- Existing Pack 0010 creation/validator tests and Pack 0015 operator client regressions remain green.
- Static documentation validation checks manifest shape, paths, index discovery, forbidden later-stage records, and local references.

## 25. Acceptance Checklist

- [x] separate execution approval received
- [x] creator dry-run and apply used; native generator not used
- [x] exact minimal module exists and validates
- [x] offline no-script autoload refresh caused no tracked Composer/dependency change
- [x] DK enabled through Nwidart and provider loads in a fresh boot
- [x] default registry exposes one empty dk App
- [x] owner router/manifest/profile/indexes are consistent and discoverable
- [x] ND exists only as a documented future internal Component
- [x] DK/focused/full PHPUnit validation passes
- [x] no target/config/database/queue/UI/API/dependency/environment/Core scope added
- [x] Run and project indexes show accepted execution
- [x] no commit without separate authorization

## 26. Tests to Add

Modules/DK/tests/Unit/DKAcceptanceAppTest.php:

- Direct construction; exact key/version; provider interfaces; empty components/suites/scenarios/variants/mappings; null tuple resolution; no side effects.

tests/Feature/DKModuleBootstrapTest.php:

- Guarded boot; repository has/enables DK; exact provider registration; catalog dk => v1; empty hierarchy/mappings; validator valid; no target behavior.

Existing regression:

- tests/Feature/AcceptanceOperationCommandContractTest.php explicitly substitutes an empty registry in its empty-map case; semantic assertions stay unchanged.

## 27. Tests to Run

Only after separate execution approval.

Run through the accepted absent-dotenv Laravel entrypoint:

~~~text
acceptance:app:create DK dk --json
acceptance:app:create DK dk --apply --confirm --json
acceptance:app:validate DK --json
module:enable DK
module:list --only=enabled
acceptance:list --app=dk --json
acceptance:plan --app=dk --json
~~~

Guarded form:

~~~powershell
php -r 'define("LARAVEL_START", microtime(true)); require "vendor/autoload.php"; $pack16App = require "bootstrap/app.php"; $pack16App->loadEnvironmentFrom("__tms_pack_0016_no_dotenv__"); exit($pack16App->handleCommand(new \Symfony\Component\Console\Input\ArgvInput));' -- <exact command>
~~~

Plan for dk may succeed with zero items; that is expected.

After files/docs/tests exist and before activation, run one composer dump-autoload --no-scripts --no-interaction with COMPOSER_DISABLE_NETWORK=1. Immediately verify no tracked Composer diff.

Focused guarded test groups:

~~~text
test --compact Modules/DK/tests/Unit/DKAcceptanceAppTest.php tests/Feature/DKModuleBootstrapTest.php tests/Feature/AcceptanceOperationCommandContractTest.php
test --compact tests/Unit/TargetModuleCreatorTest.php tests/Unit/TargetModuleValidatorTest.php tests/Unit/AcceptanceHierarchyTest.php tests/Feature/TargetModuleOperationServiceTest.php
test --compact tests/Feature/AcceptanceOperatorCommandsTest.php tests/Feature/AcceptanceOperatorJsonContractTest.php tests/Feature/AcceptanceCatalogCommandTest.php
~~~

Then run every non-browser PHPUnit suite through the guarded entrypoint because enabling a default App changes application bootstrap globally: Unit, Feature, Core Unit, DK Unit, and DK Feature. Run each by its exact testsuite name. Do not run Core Feature (it contains real Playwright smoke), coverage, parallel tests, migrations, or browser smoke.

Also run php -l for changed PHP, scoped Pint, exact tree/JSON/YAML/path/index/reference/final-newline/whitespace checks, forbidden surface/value scans limited to Modules/DK, Git checks, and protected-path no-change checks.

Do not run ordinary dotenv-reading Artisan, module:make, Composer install/update/require/remove, Composer scripts, migration/seed/database, cache, queue/server/browser/target/network, or parallel tests.

## 28. Expected Output

- one minimal enabled DK module generated by the accepted operation
- one empty dk App registered by its module provider
- one active DK owner router/manifest/profile/index tree
- module/project tests plus PHPUnit registration/isolation
- module discovery and lifecycle indexes
- accepted Run Report with technical validation recorded

No target behavior, Component, corpus, external integration, database workflow, UI/API, or later-stage Pack.

## 29. Operator Execution Checklist

### Before AI Execution

- Approve identity DK/dk, exact inventory, and accepted generator Composer metadata defaults.
- Approve dry-run → apply → docs/tests → offline no-script autoload → disabled validation → final Nwidart activation.
- Approve DK owner contract, ND as future internal Component, and empty local indexes.
- Approve declared status/phpunit/regression/discovery/lifecycle edits.
- Approve exact absent-dotenv focused/full tests and one offline autoload refresh; no target/browser/network/database/queue work.
- Confirm main branch and a working tree containing only this Pack/index normalization baseline, with no unrelated changes.

### After AI Execution

- Inspect inventory and absence of routes/resources/target behavior.
- Confirm fresh-process enabled status and empty dk registry/catalog.
- Inspect owner routing and confirm stages 11–15 remain absent.
- Review Composer/lock, Core, environment, database, target, and dependency no-change evidence.
- Review tests, any failure/fix history, and Run before acceptance.

## 30. Agent Final Report

Follow docs/ai/rules/REPORTING-RULES.md. Report exact commands and safety classes, inventory/dry-run comparison, discovery/activation/provider/registry/catalog evidence, owner docs/index lifecycle, tests and totals, failures/fixes, no-target/no-secret proof, protected-path no-change evidence, changed-file classification, limitations, assumptions, deviations, and follow-ups.

Do not claim target readiness, later-stage authorization, acceptance, or commit before its gate.

## 31. Review Checklist

Review generator reuse, exact minimal inventory, activation-last ordering, offline autoload boundary, official activator, composition-root wiring, single registration, empty catalog, owner manifest/profile/indexes, test isolation/full-suite impact, absence of target data/behavior, protected paths, Run accuracy, and separate acceptance/commit gates.

## 32. Rollback / Safety Notes

Before acceptance, rollback is allowed only if no later DK work exists:

1. Verify Modules/DK is the exact Pack-created absolute path under the repository Modules root and contains no later/unclassified work.
2. Disable DK or restore the verified pre-Pack modules_statuses.json.
3. Remove only Pack-created DK files and declared integration/index edits through targeted reviewable changes.
4. Run the same offline no-script autoload refresh to remove DK generated metadata.
5. Verify a fresh boot no longer discovers/registers DK and protected files are unchanged.

Never recursively delete an unverified/computed path. After acceptance or later DK work, rollback requires approved remediation.

## 33. Stop Conditions

Stop for missing predecessor acceptance, existing/unclassified DK work, dry-run mismatch, native generator/force/root wiring requests, Composer network/scripts/dependency/tracked changes, autoload requiring root/dependency changes, premature/broken activation, duplicate/missing dk registration, non-empty catalog, invalid validator result, out-of-scope files, Core/root/schema/target/browser/queue/API/UI/dependency/environment/network changes, sensitive values, broken ownership, later-stage creation before acceptance, unresolved tests, canonical conflict, or missing execution approval.

The operator separately authorized the accepted Pack 0016 commit on 2026-10-10. That authorization applies only to this scoped result.

## 34. Open Questions

No unresolved design question remains. Execution, technical validation, post-execution review, operator acceptance, and separate commit authorization are complete. Operator acceptance activates DK for later owner-local Pack generation.
