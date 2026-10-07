# AI-PACK-TMS-SCENARIO-CATALOG-DISPATCHER-0007 — Lazy Scenario Catalog and Variant Dispatcher

Status: `accepted — technical validation passed; accepted as the bounded catalog/dispatcher foundation`
Generated: `2026-10-05`
Normalized: `2026-10-06`
Executed: `2026-10-06`
Owner: TMS project — root catalog, selection, dispatch resolution, and CLI orchestration.

Execution Gate: operator approved this normalized revision and execution on the reported main working tree on 2026-10-06 (answer: “تائید میشه”). Approval is Pack-local execution authorization, including selector/cap/compatibility contracts, fake/in-memory validation and guide binding; no Canonical update. Acceptance, post-execution report approval, and commit remain separate.

Acceptance Review: accepted on 2026-10-07 after the operator instructed the Agent to determine Pack 0007, the scoped implementation and current tests were re-reviewed, and all named validation passed again. Acceptance is limited to this Pack's catalog, selection, planning, safe-output and targeted-dispatch scope. First-class Components, Nwidart target modules, transport-neutral operation services, full-variant execution, persisted/async Batch execution, target resources and the revised E2E coverage policy remain successor work and are not implied by this acceptance.

Reporting State: persistent Run Report created at `docs/project/ai/runs/AI-PACK-TMS-SCENARIO-CATALOG-DISPATCHER-0007-run-001.md`. The operator authorized commit on 2026-10-07; the containing commit hash is unavailable until creation.

Depends On: accepted project Pack 0004 and accepted Core Browser Observability Pack 0001. Decision 0006 cancels/removes project 0006 and the superseded Core 0002/project 0005 proposals. No fixture lifecycle, environment guard, lease, or error contract from them is available or recreated here.

## 1. Task ID

`AI-PACK-TMS-SCENARIO-CATALOG-DISPATCHER-0007`

## 2. Task Title

Add a bounded lazy code-owned scenario catalog, deterministic selection plans, and targeted variant resolution.

## 3. Goal

Inspect and plan scenario/variant selections without constructing the whole runnable matrix, creating execution state, or executing a target contract as data. Preserve valid existing single-scenario CLI usage.

## 4. Context

At normalization, HEAD is `4650cf943ba33ae40a8c4bf61df67f312ede3731` on `main`. The working tree has pre-existing roadmap/cancellation/lifecycle documentation changes; no implementation files are modified or staged. Preserve that baseline and report this Pack's changes separately.

Current Registry registration eagerly traverses `AcceptanceApp::scenarios()`, retains runnable objects and their `ScenarioMetadata`, and validates the whole App before registration. The existing `acceptance:run` resolves App/scenario/Profile and delegates to `AcceptanceRunService`.

Pack 0004 provides readonly classification and backed disposition/evidence enums. Core 0001 is accepted; its browser capability is a prerequisite record, not something this Pack calls. The project routers activate no project runtime Canonical file; the Core Canonical index is empty. Accepted Decisions and actual source control this proposal.

The source-structure summary is a historical bootstrap observation. Current contract/Registry/command source supersedes its implementation observations.

## 5. Related Release / Phase

Not applicable — pre-Release project orchestration.

## 6. Related Epic / Feature / Story

Reusable human-acceptance automation — bounded code-owned catalog and selection.

## 7. Source References

- Accepted Decisions 0002–0006; Decision 0006 controls cancelled prerequisites, Decision 0005 retains synthetic testing/staging and safe-output constraints.
- Accepted project Pack 0004 Run 001, especially scope, metadata invariants, and acceptance.
- Accepted Core Pack 0001 Run/acceptance Review, only prerequisite status and current-source evidence.
- Current Core App/Scenario/metadata/enums, project Registry/command/provider, and affected tests.
- Installed Laravel command discovery source: ApplicationBuilder::withCommands and Console Kernel::discoverCommands. No external reference fetch or dependency update.

## 8. Files to Create

Exact implementation allowlist:

- `app/Contracts/AcceptanceCatalogProvider.php`
- `app/Data/ScenarioDescriptor.php`
- `app/Data/VariantDescriptor.php`
- `app/Data/AcceptanceSelector.php`
- `app/Data/AcceptancePlanItem.php`
- `app/Data/AcceptancePlan.php`
- `app/Exceptions/AcceptanceCatalogException.php`
- `app/Services/AcceptanceCatalog.php`
- `app/Services/AcceptancePlanner.php`
- `app/Services/AcceptanceVariantDispatcher.php`
- `app/Console/Commands/ListAcceptanceCommand.php`
- `app/Console/Commands/PlanAcceptanceCommand.php`
- `tests/Unit/AcceptanceCatalogTest.php`
- `tests/Unit/AcceptancePlannerTest.php`
- `tests/Unit/AcceptanceVariantDispatcherTest.php`
- `tests/Feature/AcceptanceCatalogCommandTest.php`

Operator/governance outputs after implementation:

- `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`
- `docs/project/ai/guides/GUIDES-INDEX.md`
- `docs/project/ai/runs/AI-PACK-TMS-SCENARIO-CATALOG-DISPATCHER-0007-run-001.md`, only when the reporting lifecycle permits it.

Keep synthetic doubles inside the named test files; no new target App or helper tree.

## 9. Files to Edit

Implementation:

- `app/Services/AcceptanceAppRegistry.php`
- `app/Console/Commands/RunAcceptanceCommand.php`
- `tests/Unit/AcceptanceAppRegistryTest.php`
- `tests/Feature/AcceptanceRunCommandTest.php`

Directly related documentation:

- this Pack;
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md`, only if a Run is created;
- `docs/project/PROJECT-DOCS-INDEX.md`, only the new project operator-guide navigation;
- `docs/project/ai/TMS-AI-PROFILE.md`, only the project CLI operator-guide binding;
- `Modules/Core/docs/ai/packs/PACKS-INDEX.md`, only the current downstream navigation notice.

No AppServiceProvider change is needed: the Registry already has its explicit singleton binding; other concrete project services use existing container autowiring. Artisan auto-discovers commands in the existing command directory.

## 10. Files to Read / Reference

Read startup files, project entry/profile, selected Pack, and the shared execution/scope/question/Git/test/reporting/maintenance/generation/error/commenting rules. Pack and report formats remain owned by the shared templates.

Bounded concrete references:

- `docs/project/references/REFERENCES-INDEX.md`
- `docs/project/references/SOURCE-DOCS-INDEX.md`
- `docs/project/references/TMS-SOURCE-STRUCTURE-SUMMARY.md`
- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/decisions/TMS-DECISION-0002-generic-code-based-acceptance-apps.md`
- `docs/project/ai/decisions/TMS-DECISION-0003-cli-first-acceptance-execution.md`
- `docs/project/ai/decisions/TMS-DECISION-0004-cli-only-staged-generic-acceptance-roadmap.md`
- `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`
- `docs/project/ai/decisions/TMS-DECISION-0006-remove-cancelled-fixture-and-safeguard-packs.md`
- `docs/project/ai/runs/AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004-run-001.md`, relevant accepted scope/evidence only;
- `Modules/Core/AGENTS.md`, Core entry/manifest/profile, and `Modules/Core/docs/ai/canonical/CANONICAL-INDEX.md`;
- `Modules/Core/docs/ai/runs/AI-PACK-CORE-BROWSER-OBSERVABILITY-0001-run-001.md`, prerequisite status only;
- `Modules/Core/docs/ai/reviews/REVIEW-CORE-BROWSER-OBSERVABILITY-0001-acceptance-001.md`, prerequisite acceptance only;
- `Modules/Core/app/Contracts/AcceptanceApp.php`
- `Modules/Core/app/Contracts/AcceptanceScenario.php`
- `Modules/Core/app/Data/ScenarioMetadata.php`
- `Modules/Core/app/Enums/AutomationDisposition.php`
- `Modules/Core/app/Enums/EvidenceMode.php`
- `app/Exceptions/AcceptanceRegistryException.php`
- `app/Services/AcceptanceRunService.php`
- `app/Providers/AppServiceProvider.php`
- `bootstrap/app.php`
- `tests/TestCase.php`
- `tests/Feature/AcceptanceRunPersistenceTest.php`
- `Modules/Core/tests/Unit/ScenarioMetadataTest.php`
- `composer.json`, `phpunit.xml`;
- `vendor/laravel/framework/src/Illuminate/Foundation/Configuration/ApplicationBuilder.php`, command discovery only;
- `vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php`, command discovery only.

Read all four named implementation edit files. Do not open deleted proposal paths. Core source, vendor, runtime configuration, persistence source, and unrelated tests are read-only.

## 11. Configuration / Settings Requirements

No config file, environment variable, .env.example, Admin Setting, integration/secret setting, database-backed setting, queue/cache/log-channel, or browser setting changes.

Technical budgets belong in named service/data constants, not config: at most 1,000 matched variant rows; at most 10,000 descriptor/variant visits per catalog operation; legacy materialization at most 10,000 runnable scenarios for one App. An overflow check may consume the first over-budget element, then must stop without requesting another. App registration does not consume scenarios/descriptors/variants.

Both new commands accept `--limit=1000`, an ASCII positive integer in 1..1000. It lowers the matched-row budget only; it is not a truncation or pagination option. Scan budgets remain fixed. Expanded operator-configurable execution settings remain deferred.

### Test / Development Setup Requirements

Use installed PHP/vendor only, inert synthetic providers, and the existing test harness. Feature tests use explicit per-process APP_ENV=testing and in-memory SQLite. Existing RefreshDatabase test migrations may operate only on that in-memory connection. New list/plan no-state tests should also bind DB/execution/browser/account boundaries to fail on use; do not rely only on empty table assertions.

Check whether `bootstrap/cache/config.php` exists without reading its values. If cached configuration prevents confirming in-memory isolation, stop; do not clear caches or run the Feature suite against an uncertain database.

### Operator Setup Notes

No operator manual setup required for fake-backed validation. No real App is registered by this Pack. Real inspection requires a separately approved, explicitly registered code-owned App with safe descriptors; no target I/O is authorized here.

## 12. Do Not Change

Core implementation/tests/configuration; target contracts/importers/Apps/selectors/actions/fixtures/auth; Runner/Browser/TestContext behavior; expanded browser/Profile/CLI execution options; secret managers/providers/stores, leases, environment guards, capture/recording/export frameworks; AcceptanceRunService or Test/Step/Profile persistence; database/schema/models/migrations/factories/seeders; batch persistence/execution; API/UI; queues/scheduling/parallel/retry; dependencies/lockfiles; .env; generated/public assets; vendor; other repositories.

Existing source from cancelled Pack 0006 must not be recreated.

## 13. Clarification Questions Before Implementation

The five former normalization questions are resolved in sections 17, 19–21 and 27:

1. Optional project-owned catalog provider plus readonly descriptors; existing Core interfaces remain intact.
2. Repeated exact selectors: OR within a dimension, AND across dimensions; stable tuple ordering and scoped duplicate rejection.
3. Versioned SHA256 fingerprint of canonical safe metadata/selection; bounded scan and matched-row budgets.
4. Two JSON-only inspection commands; fixed output allowlists and fail-closed errors.
5. Lazy legacy adaptation, unchanged valid single-run signature/results, explicit default-variant resolution for catalog providers, and deferred definition validation.

These Pack-local contracts were approved for implementation on 2026-10-06. Final runtime acceptance remains pending validation and operator review.

## 14. Multilingual / Translation Requirements

Machine-readable JSON, command names/options, enum values, error codes, and fingerprints are language-neutral. No new human-readable CLI payload message/help description is introduced; preserve existing framework-generated help behavior. Internal exceptions use fixed safe technical messages and are never printed or logged.

No translation tree or required locale is declared by the project profile/current source. Do not invent one or add a message requiring a new translation convention. Operator usage/troubleshooting is Persian in the named guide. No API/Admin UI/default content/locale or RTL/LTR layout impact.

## 15. UI / Admin UI Requirements

No UI changes required. No route, view, menu, form, table, permission, asset, dashboard, or Admin Setting is created or edited. No UI review or screenshot required.

## 16. Operator Documentation Requirements

Operator Documentation Impact: Yes. Create the Persian project CLI guide in section 8; explicitly bind it in the project profile and route it through the project entry and new Guide index. This proposed binding is part of execution approval; no uncreated guide is authoritative before implementation.

Guide contents: explicit App/provider registration, legacy migration and validation timing, selectors and precedence, tuple identity/default variant, output/count/fingerprint scope, caps and failure instead of truncation, plan-only behavior, error-code troubleshooting, and synthetic testing/staging/safe-metadata responsibilities.

Explain that inspection does not require a Profile and never acquires a test account or creates a browser/fixture/Test/Step. Evidence mode is descriptive metadata; it does not prove runtime no-capture enforcement. Variant resolution creates one runnable object only when separately requested; it does not execute steps. No secret/capture/fixture setup workflow or real target example. No screenshots.

## 17. Implementation Rules

### Ownership and registration

- All new catalog/provider/data/selector/planner/dispatcher/CLI implementation is project-owned; reuse Core contracts/metadata/enums read-only.
- App registration remains explicit source code. No filesystem/module/database discovery.
- Registry register() validates App identity and duplicate App keys only, retaining the App without enumerating anything.
- Existing App/Scenario interfaces remain unchanged. The optional `App\Contracts\AcceptanceCatalogProvider` extends `AcceptanceApp` and adds:
  - `catalogVersion(): string` — safe code-owned revision key;
  - `descriptors(): iterable<ScenarioDescriptor>`;
  - `variants(string $scenarioKey): iterable<VariantDescriptor>`;
  - `resolveScenario(string $scenarioKey, string $variantKey): ?AcceptanceScenario`.
- Provider inspection methods are repeatable and side-effect free; they never resolve credentials or runnable scenarios. Their version changes when their code-owned catalog definitions change.
- Catalog-aware inspection never calls the legacy scenarios() method or resolveScenario(). No App/provider object serialization.
- Legacy Apps expose one `default` variant per Scenario. Their runnable objects may be obtained only on explicit inspection/lookup of that App, not registration/boot. Cache a validated per-App snapshot atomically; no partial snapshot survives failure. Legacy adaptation cannot provide descriptor-only construction guarantees that its old interface does not support.

### Data and identity

- ScenarioDescriptor is final readonly: `key` and typed ScenarioMetadata only.
- VariantDescriptor is final readonly: `key` only. All variants inherit the Scenario's classifications; no overrides, payloads, labels, actions, locators, callables, auth, URLs, or secret references.
- AcceptancePlanItem is final readonly: App/scenario/variant tuple and typed safe classification only. AcceptanceSelector and AcceptancePlan are immutable data with explicit serializers; copy arrays out of caller-owned references.
- New catalog identity/version/selector keys use `[a-z0-9][a-z0-9._-]*`, fully anchored, max 64 ASCII characters. Metadata collections allow at most 64 unique keys per dimension. Revalidate reused metadata at the catalog boundary without mutating Core objects.
- Identity is the full tuple `(app_key, scenario_key, variant_key)`; the same scenario/variant keys in different Apps are valid. Duplicate descriptor keys per visited App and duplicate variant keys per visited scenario fail, including visited excluded rows.
- Do not infer semantic safety from syntax: provider authors must use reviewed non-secret identifiers/classification/version values. There is no general detector for credentials disguised as legal identifiers.

### Selector contract

Commands: `acceptance:list` and `acceptance:plan`. Neither takes App/scenario/Profile positional arguments.

Both accept repeated options `--app=*`, `--scenario=*`, `--variant=*`, `--suite=*`, `--capability=*`, `--tag=*`, `--disposition=*`, `--evidence-mode=*`, and `--limit=1000`.

Values are exact keys/backed enum values. No comma expressions, wildcard, negation, regex, free-form query, or precedence language. Dedupe and sort repeated values; OR within each dimension, AND across dimensions. Empty dimensions impose no restriction.

Validate requested Apps before traversal. Scenario/suite/capability/tag vocabulary is scoped to all descriptors of selected Apps and checked independently of other filters. Requested variants are checked across scenario-selected descriptors before classification filtering; if --variant is absent, classification-filtered descriptors need no variant expansion. Any requested unknown term is a selector-not-found error, even if another dimension would yield an empty intersection. Known but disjoint selectors and unfiltered empty catalogs succeed with empty results. Enum values are checked against the existing enums.

Iterate only selected Apps; never resolve an unselected provider. Record only matched rows within the cap; do not accumulate the whole matrix. Validate identities seen in the traversed scope, not an unvisited global catalog. Sort final rows lexicographically by App/scenario/variant tuple; canonicalize metadata collection order on copies.

### Plan and dispatch

- List returns all matched rows, including non-automated dispositions, with `executable` true only for automated rows.
- Plan returns automated matched rows only. Both report matched/executable/excluded and all four disposition counts over the selected intersection, not claimed global catalog totals.
- AcceptancePlan retains the bounded matched rows and safe per-App catalog versions; it exposes both list and executable projections.
- Fingerprint schema/version is integer 1. SHA256 input is canonical JSON of version 1, normalized accepted selector dimensions and effective limit, sorted selected-App version map, and all sorted matched tuple/classification rows. Exclude command name/status, runtime identifiers/timestamps, object/class names, raw provider state, credentials, and executable data. Both commands share the fingerprint for the same selection. Metadata/provider traversal order and repeated selector order do not change it.
- Read provider catalogVersion() before and after planning; changed versions fail without returning a partial plan/fingerprint. Legacy version is the fixed safe adapter marker `legacy-v1`; the fingerprint covers its observed metadata, not a universal source-revision guarantee.
- AcceptanceVariantDispatcher resolves one explicit App/scenario/variant through a fresh bounded exact selection, rejects non-automated rows, then calls only that provider's resolveScenario() (or the legacy exact object lookup). Verify returned Scenario key and metadata match the selected safe descriptor; null/mismatch fails safely. Never call steps(), Runner, RunService, browser/auth/fixtures, or persist state.
- Registry scenario()/metadata()/scenarioKeys() remain available, with deferred per-App discovery. Catalog-aware direct scenario lookup resolves only `default`; descriptor lookup does not materialize a runnable. No universal full-matrix snapshot.
- Existing acceptance:run signature, browser/Profile options, valid success/failure JSON and exit codes remain intact. Legacy valid Apps keep the direct single-scenario path; catalog-aware Apps use the dispatcher for `default`. Add no variant execution CLI flag.
- Definition failures now occur on first inspection/lookup, not registration. Duplicate/invalid App keys still fail registration. Update affected tests to prove deferred, atomic validation and absence of execution/persistence; do not merely remove old assertions.
- New error normalization follows section 21. An invalid lazy catalog never causes Profile lookup, Test/Step creation, or RunService invocation.

## 18. Architecture Constraints

Tests remain executable source code. Catalog rows and plans contain classification/identity only; no target contract parser/importer or database-defined workflow. Target behavior remains owned by a separately approved App.

Keep commands thin; catalog validation, selection, fingerprinting, and resolution belong in the named project services/data. Preserve existing repository/PHP conventions and container/Artisan integration. No duplicated decision engine, N+1 database access, unrelated refactor, or generic secret/settings framework.

This Pack performs metadata discovery and targeted materialization only. It neither proves target environment safety nor executes batches; those boundaries must be explicitly resolved in a later approved target/batch scope.

## 19. Validation Rules

Prove zero enumeration at registration/boot; generator consumption is bounded and stops after the permitted overflow observation; selected-App isolation; optional provider inspection never constructs scenarios; legacy per-App atomic cache behavior.

Prove exact selector intersections/vocabulary scope, empty selections, stable ordering/fingerprint, version changes, defensive copies, tuple uniqueness, budgets, disposition counts/exclusion, safe serializer fields, and targeted/default resolution.

Fingerprint must change for selected identity/metadata/version/selector/limit changes and remain stable for irrelevant raw provider state and reordering. Fake sensitive state must never become a hash input.

Inspect all new command discovery through the existing Laravel test harness. No standalone artisan invocation, browser smoke, real target network, or persistent database validation is required.

## 20. Security Rules

Only approved App/scenario/variant/version keys, classifications, counts, version 1 and fingerprints may leave the catalog boundary. Exclude Scenario names, Profile/extra, URLs, locators, step data, raw providers/descriptors, secret refs/values, cookies/tokens/authenticated storage, payloads, personal identifiers, stack traces, raw input, and exception messages/chains.

Use inert synthetic providers and explicit serializers. An invalid/unknown selector is never echoed. Successful JSON includes only validated known selector keys if a selector projection is exposed; rejected input is never hashed. The prescribed success projection in section 21 omits selectors.

Success/failure fakes must hold inert credential/session/token sentinels privately and throw them in raw failures; verify their absence from JSON, normalized exceptions, logs, and captured canonical fingerprint input. Account acquisition/runnable factories fail if inspection calls them. This verifies omission in the approved code-owned boundary, not a general runtime capture/secret framework.

No target connection, production/real customer data, environment-file access, capture, artifact store, credential acquisition, or fixture operation.

## 21. Error Handling / Logging / Traceability Requirements

Impact: new internal exceptions, CLI error codes, structured safe failure events and selection fingerprints. No API/UI/HTTP contract, external integration, retry engine, database/audit/status transition or new execution identifier.

New `AcceptanceCatalogException` carries an allowlisted code and fixed safe technical message, with no raw rejected value or chained exception. Normalize raw provider/iterator/metadata/resolve failures at the catalog/dispatcher boundary; never serialize arbitrary exception properties.

### Codes and classification

| Code | Trigger | CLI status / exit | Category / action |
|---|---|---|---|
| `acceptance_selector_invalid` | malformed option/key/enum/limit | rejected / 2 | permanent input error; correct options |
| `acceptance_app_not_found` | unknown requested App | rejected / 2 | reuse existing meaning; correct registration/selection |
| `acceptance_selector_not_found` | unknown scenario/suite/capability/tag/variant term in defined scope | rejected / 2 | input error; inspect catalog/correct selector |
| `acceptance_catalog_invalid` | invalid descriptor/variant/version/metadata or resolved Scenario mismatch/null | failed / 1 | code-definition error; correct provider |
| `acceptance_catalog_duplicate` | visited duplicate descriptor or tuple identity | failed / 1 | code-definition error; correct provider |
| `acceptance_catalog_limit_exceeded` | matched-row/scan/legacy materialization budget exceeded | rejected / 2 | bounded selection rejected; narrow selection |
| `acceptance_catalog_changed` | provider revision changes during planning/resolution | failed / 1 | unstable catalog; fix/re-run explicitly |
| `acceptance_catalog_failed` | unexpected provider/iterator/resolver failure | failed / 1 | code-provider failure; inspect source safely |
| `acceptance_variant_not_executable` | dispatcher selects a non-automated row | rejected / 2 | eligibility error; select automated definition |

No automated retry; changed-catalog failure is temporary until source stabilizes, other definition failures remain permanent until corrected. All are operator-visible; no Admin workflow, owner-state change or durable audit. Existing Registry codes/command codes remain unchanged. Legacy lazy discovery uses safe existing registry-invalid/duplicate errors where appropriate; no raw InvalidArgumentException leaks.

### CLI contracts

Success: `status` is `listed` or `planned`; fields are exactly `status, plan_version, catalog_versions, counts, fingerprint, items`. counts contains `matched, executable, excluded, by_disposition`. Each item contains only `app_key, scenario_key, variant_key, suites, capabilities, tags, disposition, evidence_mode, executable`.

Failure: exactly `{"status":"rejected|failed","error_code":"allowlisted-code"}`; one JSON line, no partial success/items/fingerprint, raw option/exception text or human message. Empty selection is success with zero counts and items.

New commands use only allowlisted error codes. Existing run command maps catalog exceptions into its existing safe rejected/failed shape before any Profile lookup/execution; retain existing unknown-App/scenario/Profile behavior for legacy Apps.

### Logging and traceability

New list/plan boundary event: `tms.acceptance.catalog.failed`; run-command catalog failures retain `tms.acceptance.command.failed`. warning for rejected selection/eligibility/budget; error for definition/provider/change failure. Required context: fixed command identifier and error_code only for the new event. Existing run event keeps its safe existing context contract; do not add raw catalog input. No success logging, stack trace, exception message/object/class from target providers, selector dump, Profile, or sensitive key.

Normalized exceptions may safely use the catalog exception class for existing run-event exception_class. No real log inspection; spies/fakes verify exact context and forbidden data absence. Fingerprint is the selection trace only on successful output; no new request/Run/Batch identifier or end-to-end execution trace claim.

Test every code/exit/output/classification path; verify no state change and safe structured events. Unit exceptions have no logging side effect; commands own event emission. No unresolved error contract or follow-up before implementation approval.

## 22. Data Model / Migration / Relationship Requirements

No data model, migration, relationship, table/column/index/FK, backfill, retention/audit, or snapshot fields required. Plans are bounded in-memory data; no disk/DB plan persistence. Persisted batch work belongs to a separately normalized Pack 0008.

## 23. Commenting Requirements

Follow shared commenting rules. Explain deferred legacy validation/cache atomicity, generator budgets/overflow observation, tuple scope, canonical fingerprint inputs, selector intersection/vocabulary scope, approved data allowlist, and separation between discovery/materialization/execution. Avoid large generic frameworks or comments repeating obvious code.

## 24. Testing Requirements

Meaningful positive/negative contract and side-effect assertions required. No status-only checks. Use inert doubles and pure unit tests; Feature tests verify actual Artisan discovery/JSON/log boundaries and legacy command regression.

Explicit fake spies must detect account acquisition, resolveScenario(), legacy scenarios() for catalog-aware inspection, steps(), RunService, Runner/browser creation and database use during list/plan. RefreshDatabase regression tests may operate only in memory, as stated in section 11.

No Core test edits, real browser, target authentication/network, fixture/capture/secret framework, or full-suite expansion.

## 25. Acceptance Checklist

- [x] exact optional provider/data contracts implemented without changing Core interfaces;
- [x] App registration performs zero enumeration;
- [x] bounded legacy adaptation, atomic cache and targeted catalog materialization proven;
- [x] selectors, scoped vocabulary/duplicates, empty results and caps match this proposal;
- [x] stable canonical ordering, immutable data and version 1 fingerprint proven;
- [x] non-automated rows counted/listed but absent from executable plan/dispatcher;
- [x] list/plan output, error codes, exits, structured events and sentinel omission verified;
- [x] no Profile/account/browser/fixture/Test/Step/target side effect during inspection;
- [x] valid legacy single-run behavior preserved; deferred invalid-definition timing tested;
- [x] no cancelled fixture/guard, expanded settings, secret/capture/batch capability added;
- [x] Persian CLI guide/profile/navigation bindings completed;
- [x] operator-directed post-execution review completed, persistent Run created, and the bounded Pack result accepted on 2026-10-07.

## 26. Tests to Add

Named files in section 8 must prove:

| File / test group | Behavior and main assertions | Setup / effects / negative cases |
|---|---|---|
| AcceptanceCatalogTest — lazy registration/provider isolation | zero generator/factory calls at registration; only selected App visited; catalog-aware legacy method never called | failing/counted synthetic generators; no execution effects |
| AcceptanceCatalogTest — legacy atomic discovery | default variant; same cached object/metadata; unknown key null; failure stores no partial cache | valid plus invalid/duplicate late Scenario; unrelated App remains usable |
| AcceptanceCatalogTest — descriptor validation | grammar/length/list/type/version validation, duplicate tuple scope and defensive copies | inert unsafe inputs; fixed code/message, no previous/raw exception |
| AcceptancePlannerTest — selector matrix | OR/AND precedence, exact vocabulary scopes, valid disjoint/empty success, invalid/unknown failures | multiple Apps/scenarios/variants and all enum cases; no runnable resolution |
| AcceptancePlannerTest — deterministic plans | stable tuple/label/selector order, exact counts/projections, fingerprint changes only for prescribed inputs | reordered providers; private raw state changes; captured canonical input |
| AcceptancePlannerTest — budgets/version drift | default/lowered row cap and visit cap fail without partial plan; stop after first overflow; revision change rejected | large lazy generators failing beyond allowed observation; no full materialization |
| AcceptanceVariantDispatcherTest — targeted resolution | exactly one factory called; key/metadata verified; default selection works; steps never called | missing/invalid/mismatched/non-automated/raw failure cases; safe exceptions |
| AcceptanceCatalogCommandTest — real CLI boundary | both commands discovered, exact safe success/empty/failure JSON, all exits and structured log context | synthetic registrations; spies deny DB/Profile/account/browser/RunService calls |
| AcceptanceCatalogCommandTest — safe private inputs | sentinel-free JSON/normalized exceptions/logs/hash input on success and raw provider failure | private inert credentials/session/token fakes; zero account acquisition |
| Existing Registry/RunCommand tests | preserved success/results/unknown lookups/options; deferred definition failure before Profile/execution; atomic validation | replace eager timing assertions with meaningful lazy/negative assertions; in-memory DB only |

Tests should fail if the protected behavior is removed; do not weaken accepted valid execution assertions or rewrite persistence tests.

## 27. Tests to Run

Only after normalized execution approval:

1. Safe read-only precheck: `Test-Path -LiteralPath 'bootstrap/cache/config.php'`; stop on unclear cached database isolation.
2. `php -l <each created/edited PHP file>` — syntax only.
3. `php vendor/bin/phpunit --do-not-cache-result tests/Unit/AcceptanceCatalogTest.php tests/Unit/AcceptancePlannerTest.php tests/Unit/AcceptanceVariantDispatcherTest.php tests/Unit/AcceptanceAppRegistryTest.php Modules/Core/tests/Unit/ScenarioMetadataTest.php`.
4. Per-process `$env:APP_ENV='testing'; $env:DB_CONNECTION='sqlite'; $env:DB_DATABASE=':memory:'`, then `php vendor/bin/phpunit --do-not-cache-result tests/Feature/AcceptanceCatalogCommandTest.php tests/Feature/AcceptanceRunCommandTest.php tests/Feature/AcceptanceRunPersistenceTest.php`. Restore each prior process variable in finally. Only named in-memory test migrations are allowed; no standalone migration.
5. `php vendor/bin/pint --test <each created/edited PHP file>` — check only; no whole-repository formatting.
6. `git diff --check` scoped to this Pack's touched files and targeted diff/path checks.

No test command has run during normalization. Do not use composer test (it clears config), artisan cache/migrate/list/help, dependency/build scripts, full PHPUnit suite, browser smoke, target network, queue or batch commands. New command behavior is exercised through the installed Laravel test harness only.

### Execution evidence — 2026-10-06

After the recorded normalized execution approval, the named validation scope was run with installed PHP 8.4.25 / PHPUnit 11.5.56:

- Cached-config existence precheck returned False; Feature validation used testing / sqlite / :memory: with prior process variables restored in finally.
- Syntax checks passed for all 20 created/edited PHP files.
- Final named Unit command passed: 63 tests, 633 assertions, including the read-only Core metadata regression.
- Final named Feature command passed: 21 tests, 537 assertions, including the unchanged persistence regression. Total: 84 tests, 1,170 assertions.
- Scoped Pint --test passed for all 20 PHP files. Initial formatting checks failed; only allowed files were corrected before the final pass.
- The initial Feature run exposed a test-harness Log spy accumulating calls between loop cases. The spy was isolated per case, without weakening code/context assertions; the failing case and subsequent complete named Feature runs passed.
- The large synthetic provider returned a complete 1,000-row plan with identical repeated fingerprints and zero runnable factory calls. Overflow tests observed exactly limit + 1 rows before rejecting, including 1,001 at the default limit, without returning a partial plan.
- The disposition example matched 6 rows: automated 3, manual-only 1, blocked 1, not-implemented 1. List retained all 6; plan retained only 3 automated rows with the same counts/fingerprint.
- New Feature tests denied database, execution service, Runner and browser boundaries; synthetic providers counted account/legacy/factory calls. No real browser, target network, account or persistent database validation was run.
- Final post-execution checks on main / HEAD 4650cf943ba33ae40a8c4bf61df67f312ede3731 classified 20 PHP files as allowed implementation and 7 documents as allowed governance maintenance. The other 14 dirty documentation paths were pre-existing; no new unauthorized or staged path was found. Scoped git diff --check and checks covering all new-file whitespace, the ordered 34 Pack sections and guide navigation passed.

The original execution deferred its formal Agent Final Report, persistent Run and acceptance. On 2026-10-07 the operator instructed the Agent to determine the Pack; the scoped validation was rerun successfully, the persistent Run was created, and the bounded Pack result was accepted. No staging or commit was authorized.

## 28. Expected Output

The exact project implementation/tests, safe JSON list/plan commands, targeted variant materialization and legacy compatibility described here, plus the Persian CLI guide and directly required lifecycle/navigation updates.

Run Report: `docs/project/ai/runs/AI-PACK-TMS-SCENARIO-CATALOG-DISPATCHER-0007-run-001.md`. Passing tests support the accepted bounded result; no commit is inferred or authorized.

## 29. Operator Execution Checklist

### Before AI Execution

- Approve repeated exact selector grammar, scoped unknown-term behavior and deterministic tuple ordering.
- Approve 1,000 matched-row / 10,000 visit budgets and failure instead of truncation.
- Approve deferred legacy definition validation, opt-in descriptor-only providers and default-only existing run command compatibility.
- Approve the Persian project guide binding and fake/in-memory-only validation scope.

### After AI Execution

- Inspect a large synthetic provider example, bounded rejection and stable repeated fingerprints.
- Inspect all four disposition counts and absence of excluded rows from executable plans.
- Verify the guide distinguishes inspection, targeted materialization and actual execution, with no runtime capture/environment-safety claim.

## 30. Agent Final Report

Follow shared reporting rules after the required post-execution gate. Report exact selector/provider/data contracts, legacy migration timing, caps/laziness evidence, selected counts/fingerprint guarantees, output/error/log fields, sentinel/hash-input omission tests, no-state checks, command compatibility and test limitations.

Report implementation separately from required guide/lifecycle maintenance and pre-existing roadmap edits. Do not claim a secret manager, target guard, fixture cleanup, runtime no-capture guarantee, or batch execution exists.

## 31. Review Checklist

Review tests-as-code ownership, root/Core boundaries, lazy provider/legacy behavior, bounded traversal versus stored matched rows, deterministic fingerprint, selector scopes, disposition exclusion, safe typed serialization/error/log boundaries, default dispatch and valid single-run regression.

## 32. Rollback / Safety Notes

Reviewable rollback is limited to this Pack's named source/tests/guide bindings and new files; preserve all pre-existing changes. No database or target state should exist. Do not execute Git restore/reset/clean or delete files without exact operator authorization.

## 33. Stop Conditions

Stop if source inspection requires changing the named allowlist/ownership, Core contracts/runtime, target parser/auth/fixtures/guard/capture, expanded settings, DB/batch execution/persistence, dependencies or another execution surface; if private sensitive values enter metadata/output/hash/logs; if required bounded/side-effect-free behavior cannot be met; if predecessors are not accepted; or if safe in-memory validation cannot be confirmed.

Provider-discovered invalid input/budget/change errors are specified failures to test, not permission to widen scope.

## 34. Open Questions

No unresolved question remains inside this Pack. Operator execution approval was received on 2026-10-06; implementation and named technical validation completed; operator-directed determination and bounded acceptance completed on 2026-10-07. First-class Components, Nwidart target modules, operation-service/client separation, target environment/resource cleanup, full-variant execution and persisted async Batch contracts remain outside this Pack and require successor decisions/Packs.
