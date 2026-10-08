# AI-PACK-TMS-OPERATION-SERVICE-LAYER-0009 — Transport-neutral Acceptance operation layer

Status: `accepted — technical validation passed; operator accepted on 2026-10-08`

Generated: `2026-10-07`
Normalized: `2026-10-08`
Owner: TMS project
Decision: accepted Decisions 0008, 0013 and 0014; retained constraints from Decisions 0002, 0003 and 0005.
Depends On: accepted project Packs 0003, 0004 and 0007.
Execution Approval: granted on 2026-10-08 after the operator reviewed the revised contract and its alignment with accepted decisions, then instructed execution. Approval includes the two typed payload DTOs, safe raw-code fallback, classification/shared logs, CLI-owned projections, the inspected dirty `main` baseline, and fake-backed dotenv-free in-memory validation.
Execution / Acceptance / Commit: implementation, scoped validation, post-execution review and final operator acceptance completed. Run 001 records the accepted execution. The operator authorized commit on 2026-10-08; the commit containing this Pack and Run follows this pre-commit update.

## 1. Task ID

`AI-PACK-TMS-OPERATION-SERVICE-LAYER-0009`

## 2. Task Title

Create the transport-neutral application boundary used by Artisan and future Web/MCP clients.

## 3. Goal

Move accepted list, plan and single-run orchestration behind immutable typed requests/results and an explicit operation registry. Preserve accepted command arguments, options, JSON projections, selection semantics, default configuration, rejection order and exits.

This stage adds infrastructure and focused tests, with no new operator execution capability.

## 4. Context

Current source at `6684d9c79a0518a2c0e975ff2330e8f558c15cb7` is the implementation baseline. The predecessor Pack/Run indexes mark Packs 0003, 0004 and 0007 accepted; their accepted source is present.

- `ListAcceptanceCommand` calls the planner and renders `AcceptancePlan::toArray(false)`. Despite the handler filename, listing means catalog tuples, not an App-key-only list.
- `PlanAcceptanceCommand` uses the same selection and fingerprint, projecting executable tuples.
- `RunAcceptanceCommand` resolves App, Scenario/default variant, Profile and run options, in that order, then calls `AcceptanceRunService`.
- No `app/Acceptance/Operations/` implementation exists.
- The execution startup tree has 28 pre-existing dirty documentation files on `main`, which is two commits ahead of `origin/main`. Decision 0014 added one file to the earlier 27-file normalization baseline. Source/test files in this Pack are clean. Preserve these changes; the operator approved execution on this inspected baseline on 2026-10-08.

### Normalization record

The operator requested normalization if needed on 2026-10-08. It was needed because the Draft left operation names, DTO fields, error mappings, exact validation commands and compatibility exits unresolved.

The initial normalization defined those contracts in sections 17 and 21, added the stateful operation ID required by Decision 0008, froze lazy resolution to protect inspection from execution, and specified isolated test boot without dotenv. Accepted CLI JSON is a client projection of the richer service DTO and receives no added trace/envelope fields.

Logging moves from CLI-specific failure events to shared operation events. The existing Persian guide explicitly documents the old events, so only its error/logging paragraph and index status require synchronization. These two exact governance paths replaced the Draft's conditional guide note; the initial normalization added no implementation file beyond the Draft list.

Safety normalization permits only approved fixed error codes in public results. An unrecognized raw Test/exception code must be replaced by the existing safe fallback `acceptance_command_failed`, with the same failure exit and available Test identifier. Supported accepted codes retain their meaning; this is an explicit safe-output proposal requiring approval with this normalized Pack.

This normalization does not approve execution or constitute an executed Run. Explain these differences in the eventual Agent Final Report and Run Report. Do not create a completed Run Report before the reporting gate.

Initial normalization validation (2026-10-08, before Decision 0014): all numbered sections 1–34 were present once and in order; 91 distinct local references were checked against existing files, declared future creates and explicit absent bootstrap guards, with no unexplained missing reference. Pack/index readiness agreed; whitespace/final-newline checks and `git diff --check` passed. SHA-256 comparison with that captured startup tree found changes only to this Pack and its index; the other 25 dirty documents were byte-for-byte preserved. No source, test, runtime, database, environment or dependency file was changed.

Inspection used scoped `Get-Content`, `rg`, `Test-Path`, `Get-FileHash`, `git status --short --branch`, `git status --porcelain`, `git branch --show-current`, `git rev-parse HEAD`, `git diff --check` and scoped `git diff`. Read-only PHP argv probes initially failed with stop-parsing syntax, then passed with the single-quoted program in section 27; the proposed bootstrap program passed `php -l` via stdin without execution. No Artisan, PHPUnit, Pint, real browser or target command has run in this normalization. Implementation/test validation remains pending execution approval.

### Decision 0014 revision

The operator approved recording and propagating the DTO/client-presentation decision on 2026-10-08. The previous proposed result contract unnecessarily fixed CLI JSON key names/order and empty-object representation inside service data. Sections 8, 17 and the validation/checklists now define typed catalog/run data and reserve JSON encoding/projection for the commands. Two exact payload DTO files are added to the proposed implementation allowlist; no source file is created by this documentation revision. The previous pre-execution gate must be read against this revised scope before implementation.

Laravel Prompts and complete human/structured rendering remain Pack 0015 work. Decision 0014 does not approve this Pack's implementation, error/logging proposal, test execution or commit. Initial normalization validation above is historical evidence for the earlier revision; this decision's documentation validation is recorded in Decision 0014 after the affected records are checked.

### Execution evidence (2026-10-08)

The operator approved the revised contract after its decision-alignment review and instructed execution. This is execution authorization, not an architecture amendment. The baseline remained `main` at `6684d9c79a0518a2c0e975ff2330e8f558c15cb7`, two commits ahead of `origin/main`, with 28 pre-existing documentation changes. SHA-256 comparison preserved all 26 baseline documents outside this Pack's two existing maintenance paths; the Pack and its index changed only for directly required lifecycle evidence. The guide and its index are the two additional scoped governance edits.

Implementation created the ten declared PHP operation files and three declared test files, and edited the four declared application files and two declared regression tests. Requests/results and catalog/run data are immutable typed objects. The registry resolves only the selected lazy factory; commands retain the accepted JSON projection. Fixed error-code normalization, classification, UUID flow and one shared operation event are covered by the scoped tests. Successful inspection has no new log or execution/DB/provider side effect. Actual target/browser execution remains untested and outside scope.

Validation used section 27's exact two Artisan test groups and `list --format=json` through the declared absent-environment-file PHP entrypoint, with section 11's process-only values saved/restored without printing prior values. Final results: 11 Unit tests passed (324 assertions), 22 Feature tests passed (985 assertions), command discovery exited 0 and included all three accepted commands. Syntax validation passed for all 19 PHP files; the final scoped Pint `--test` check passed. Static inspection of the operation layer found no console/Prompts/client serializer, dotenv/environment read, executable discovery or network dependency.

Initial Feature validation had one failing newly added test and 21 warnings. The failure consumed Artisan's output buffer twice; the test now retains the first captured output for its empty-map check. Dotenv's deliberately absent-file probe caused the warnings. Each allowed Feature bootstrap handles only that guarded missing-file reader warning, forwards other errors to the prior handler and restores that handler in `finally`. It also verifies the process cache path before boot and the testing/SQLite/`:memory:` configuration before `RefreshDatabase`. The narrow fix check passed (21 assertions), and the final Feature group passed without warnings. No real environment file was read or created.

Post-validation remediation reproduced an operator-reported 22-test failure when the otherwise safe process omitted `APP_CONFIG_CACHE`: all Feature tests stopped before assertions because their bootstrap required one exact sentinel path even though PHPUnit's declared disposable database values were valid and Laravel's resolved default cache file was absent. The three allowed Feature classes now reject an actually existing path returned by `Application::getCachedConfigPath()` before kernel bootstrap instead of requiring that optional process variable to equal one Pack-specific filename. The absent-file and post-bootstrap testing/SQLite/`:memory:` guards remain intact. The same 22-test group passed with 985 assertions while `APP_CONFIG_CACHE` was unset. Final remediation validation with the approved sentinel path passed all 11 Unit tests (324 assertions) and 22 Feature tests (985 assertions); syntax for the three edited tests, the complete scoped Pint `--test`, `git diff --check` and all four absence guards also passed.

The narrow diagnostic test arguments were `test --compact tests/Feature/AcceptanceOperationCommandCompatibilityTest.php --filter=empty_catalog_versions --display-warnings --display-deprecations`, then `test --compact tests/Feature/AcceptanceOperationCommandCompatibilityTest.php --filter=empty_catalog_versions --display-warnings`; both used the same approved isolated entrypoint/process values. The latter passed without warnings before the complete Feature group was rerun.

The initial Pint check reported six changed files needing style corrections. The required targeted formatting command was:

```text
php vendor/bin/pint app/Acceptance/Operations/AcceptanceOperationService.php app/Console/Commands/RunAcceptanceCommand.php tests/Unit/AcceptanceOperationServiceTest.php tests/Feature/AcceptanceOperationCommandCompatibilityTest.php tests/Feature/AcceptanceCatalogCommandTest.php tests/Feature/AcceptanceRunCommandTest.php
```

It touched only approved modified files; both test groups and the scoped Pint check passed afterward. Read-only inspection used scoped `Get-Content`, `rg`, `Test-Path`, `Get-FileHash`, Git status/branch/HEAD/diff checks and a PHP version/SQLite-extension probe. Validation also used per-file `php -l`, document-section/reference/newline checks and `git diff --check`. The first full Artisan command-list output exceeded tool capture limits; it was repeated with an in-memory PowerShell JSON projection of only the three relevant registrations.

Scope review found 23 task-owned changed files (19 PHP, four governance), 26 preserved unrelated startup documents, and no unauthorized changed path. The 34 numbered Pack sections remain unique and ordered; all 99 distinct scoped local references resolve or are explicitly deferred Run/absent-bootstrap guards. No Core/schema/target/queue/API/UI/MCP/dependency/environment/production-bootstrap change occurred. The persistent Run and its index remain deferred to the reporting gate; acceptance and commit are not granted by passing validation.

After the remediation validation, the operator reviewed the TMS architecture diagram and Pack 0009's exact stage-1 placement, then approved the post-execution gate on 2026-10-08. After receiving the Agent Final Report, the operator granted final acceptance and instructed commit. The accepted persistent record is `docs/project/ai/runs/AI-PACK-TMS-OPERATION-SERVICE-LAYER-0009-run-001.md`.

## 5. Related Release / Phase

Decision 0013, stage 1. Pre-Release; no release-specific or phase-specific directory is activated.

## 6. Related Epic / Feature / Story

Reusable multi-client TMS application layer.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0002-generic-code-based-acceptance-apps.md`
- `docs/project/ai/decisions/TMS-DECISION-0003-cli-first-acceptance-execution.md`
- `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md` — retained synthetic-data, target-ownership and safe-output constraints only.
- `docs/project/ai/decisions/TMS-DECISION-0008-transport-neutral-operations-and-artisan-client.md`
- `docs/project/ai/decisions/TMS-DECISION-0013-fifteen-stage-layered-delivery-roadmap.md`
- `docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0003.md` — approved architecture/documentation context; no standing runtime authority.
- `docs/project/ai/packs/AI-PACK-TMS-ACCEPTANCE-COMMAND-REGISTRY-0003.md` and its `docs/project/ai/runs/AI-PACK-TMS-ACCEPTANCE-COMMAND-REGISTRY-0003-run-001.md`.
- `docs/project/ai/packs/AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004.md` and its `docs/project/ai/runs/AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004-run-001.md`.
- `docs/project/ai/packs/AI-PACK-TMS-SCENARIO-CATALOG-DISPATCHER-0007.md` and its `docs/project/ai/runs/AI-PACK-TMS-SCENARIO-CATALOG-DISPATCHER-0007-run-001.md` — accepted bounded foundation, not complete variant/batch/target functionality.

## 8. Files to Create

### Implementation

- `app/Acceptance/Operations/Contracts/AcceptanceOperationHandler.php`
- `app/Acceptance/Operations/Data/OperationRequest.php`
- `app/Acceptance/Operations/Data/OperationResult.php`
- `app/Acceptance/Operations/Data/CatalogOperationData.php`
- `app/Acceptance/Operations/Data/RunOperationData.php`
- `app/Acceptance/Operations/AcceptanceOperationRegistry.php`
- `app/Acceptance/Operations/AcceptanceOperationService.php`
- `app/Acceptance/Operations/Handlers/ListAcceptanceApps.php`
- `app/Acceptance/Operations/Handlers/PlanAcceptance.php`
- `app/Acceptance/Operations/Handlers/RunAcceptanceScenario.php`

### Tests

- `tests/Unit/AcceptanceOperationRegistryTest.php`
- `tests/Unit/AcceptanceOperationServiceTest.php`
- `tests/Feature/AcceptanceOperationCommandCompatibilityTest.php`

### Execution record, only when the reporting gate is satisfied

- `docs/project/ai/runs/AI-PACK-TMS-OPERATION-SERVICE-LAYER-0009-run-001.md`

## 9. Files to Edit

### Implementation and tests

- `app/Providers/AppServiceProvider.php`
- `app/Console/Commands/ListAcceptanceCommand.php`
- `app/Console/Commands/PlanAcceptanceCommand.php`
- `app/Console/Commands/RunAcceptanceCommand.php`
- `tests/Feature/AcceptanceCatalogCommandTest.php`
- `tests/Feature/AcceptanceRunCommandTest.php`

### Directly required governance maintenance

- `docs/project/ai/packs/AI-PACK-TMS-OPERATION-SERVICE-LAYER-0009.md` — normalization, gates and actual lifecycle evidence.
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md` — only when a real Run Report is created.
- `docs/project/ai/reviews/REVIEWS-INDEX.md` — only if an actual review is required/performed; no fabricated review row.
- `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md` — targeted logging/troubleshooting synchronization only, after implementation.
- `docs/project/ai/guides/GUIDES-INDEX.md` — matching guide lifecycle status only.

Existing unrelated index rows and roadmap content must be preserved.

## 10. Files to Read / Reference

Read sections 8–9 and section 7 only as needed, plus:

- `AGENTS.md`, `docs/ai/start/START-HERE.md`, `docs/ai/start/CONTEXT-MAP.md`
- `docs/project/PROJECT-DOCS-INDEX.md`, `docs/project/ai/TMS-AI-PROFILE.md`
- `docs/project/references/REFERENCES-INDEX.md`, `docs/project/references/SOURCE-DOCS-INDEX.md`, `docs/project/references/TMS-SOURCE-STRUCTURE-SUMMARY.md` — bootstrap observations, not current source authority.
- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/ai/rules/SCOPE-CONTROL-RULES.md`, `docs/ai/rules/AI-EXECUTION-RULES.md`
- `docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md`, `docs/ai/rules/GIT-AND-COMMIT-RULES.md`
- `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`, `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md`
- `docs/ai/rules/COMMENTING-RULES.md`, `docs/ai/rules/REPORTING-RULES.md`, `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`
- `docs/ai/rules/AI-PACK-GENERATION-RULES.md`, `docs/ai/templates/AI-PACK-TEMPLATE.md` — normalization only.
- `app/Data/AcceptanceSelector.php`, `app/Data/AcceptancePlan.php`, `app/Data/AcceptancePlanItem.php`
- `app/Services/AcceptanceAppRegistry.php`, `app/Services/AcceptanceCatalog.php`, `app/Services/AcceptancePlanner.php`, `app/Services/AcceptanceVariantDispatcher.php`, `app/Services/AcceptanceRunService.php`
- `app/Exceptions/AcceptanceCatalogException.php`, `app/Exceptions/AcceptanceRegistryException.php`, `app/TestStatusEnum.php`
- `app/Models/Profile.php`, `app/Models/Test.php`, `app/Contracts/BaseAction.php` — bounded lookup/projection/error-code evidence, no changes.
- `tests/Unit/AcceptanceAppRegistryTest.php`, `tests/Unit/AcceptancePlannerTest.php`, `tests/Unit/AcceptanceVariantDispatcherTest.php`
- `tests/Feature/AcceptanceRunPersistenceTest.php` — persistence contract reference only; no scope to change its bootstrap or run it through a dotenv-reading bootstrap.
- `tests/TestCase.php`, `phpunit.xml`, `bootstrap/app.php`, `artisan` — read-only test/bootstrap evidence.
- `docs/modules/MODULES-DOCS-INDEX.md`, `Modules/Core/AGENTS.md`, `Modules/Core/docs/ai/README.md`, `Modules/Core/docs/ai/manifest.yaml`, `Modules/Core/docs/ai/GOVERNANCE-PROFILE.md`
- `Modules/Core/app/Data/RunOptions.php`, `Modules/Core/app/Data/RunResult.php`, `Modules/Core/app/Exceptions/AcceptanceExecutionException.php`, `Modules/Core/app/Contracts/StepResult.php`, `Modules/Core/app/Services/AcceptanceRunner.php` — consumption of existing contracts only.
- `vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/LoadEnvironmentVariables.php`, `vendor/laravel/framework/src/Illuminate/Foundation/Testing/TestCase.php`, `vendor/nunomaduro/collision/src/Adapters/Laravel/Commands/TestCommand.php` — installed bootstrap/test-process behavior only; never edit vendor.

Resolve report templates at their reporting gate. Do not load unrelated Packs or later-stage decisions.

## 11. Configuration / Settings Requirements

No configuration or settings changes required. No environment file, Admin Setting, external integration, secret setting, cache/queue configuration, dependency or operator setup change.

Read the existing `core.acceptance.browser`, `headless`, `timeout_ms` and `slow_mo_ms` configuration only through existing runtime configuration lookup in the run handler. Preserve their defaults and existing meaning; do not call `env()` in the operation layer.

### Test / Development Setup Requirements

- Existing PHP, Composer autoload, PHPUnit, Collision/Artisan test command and Pint must already be available. No installation is authorized.
- Check that `bootstrap/cache/config.php`, `bootstrap/cache/pack-0009-config-disabled.php`, `__tms_pack_0009_no_dotenv__` and `__tms_pack_0009_no_dotenv__.testing` are absent. Existence checks only; never read secret-bearing cached config. Stop rather than deleting anything.
- Use process-only `APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `CACHE_STORE=array`, `SESSION_DRIVER=array`, `QUEUE_CONNECTION=sync`, `BROADCAST_CONNECTION=null`, `MAIL_MAILER=array`, `APP_MAINTENANCE_DRIVER=file`, `BCRYPT_ROUNDS=4` and `LOG_CHANNEL=null`. The Pack validation entrypoint additionally sets `APP_CONFIG_CACHE=bootstrap/cache/pack-0009-config-disabled.php`; save and restore prior process values without printing them.
- The three scoped Feature classes must set the application's environment filename to the absent `__tms_pack_0009_no_dotenv__` before kernel bootstrap, preserving Laravel's required test-trait initialization. Before bootstrap they must reject any existing file at Laravel's resolved configuration-cache path; they must not require one exact `APP_CONFIG_CACHE` value. This is test-only setup inside the allowed test files, not a production bootstrap change.
- Before `RefreshDatabase` or any factory runs, prove the booted configuration is testing/SQLite/`:memory:`. Tests may initialize the existing schema and synthetic records in that disposable in-memory connection only.
- No manual Artisan migrate/seed/reset, database server, browser, target or filesystem database is authorized.

### Operator Setup Notes

No operator manual setup required. Approve the normalized contracts and isolated test boundary at the execution gate.

## 12. Do Not Change

Core source/tests/runtime/contracts/configuration, database schema/migrations, Profile/Test/Step schema, target modules/registrations, queue/jobs, API/UI/MCP/auth, dependencies/lockfiles, vendor, `.env` or other environment files, production/test target state, and unrelated code/documentation.

Do not change `tests/TestCase.php`, `phpunit.xml`, `bootstrap/app.php` or `artisan`. No guide rewrite or full new client. Preserve accepted command projections except the explicitly proposed safe fallback for unrecognized raw error codes.

## 13. Clarification Questions Before Implementation

Normalization choices are specified concretely in sections 17 and 21. The operator approved this revision and execution on the inspected `main` baseline with all 28 pre-existing documentation files preserved on 2026-10-08. This approval is an execution authorization, not a new architecture decision or Pack acceptance.

Stop if source changes before approval invalidate the baseline, or compatibility requires a change beyond this contract. A request to begin normalization is not retroactive approval of its proposed contracts.

## 14. Multilingual / Translation Requirements

No multilingual or translation changes required in application code. Requests/results contain machine fields/codes only, with no new human-readable message field. Existing CLI JSON wording/statuses remain. No translation or locale files change; no RTL/LTR layout impact.

The targeted guide update is Persian prose under the existing profile binding. Existing English exception messages are not copied into service/CLI results. Future localized client messages remain Pack 0015/later client work.

## 15. UI / Admin UI Requirements

No UI changes required. No UI surfaces, routes, menus, views, assets, forms, dashboards or permissions are created or edited. No API contract or owner-declared API test artifact impact.

## 16. Operator Documentation Requirements

Operator Documentation Impact: Yes, limited to error/log tracing.
Guide: `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`.

After implementation, replace only the existing explanation of `tms.acceptance.catalog.failed` / `tms.acceptance.command.failed` with the shared events and safe trace context in section 21. Explain that existing CLI JSON/exits remain unchanged and correlated operation IDs are in shared results/logs, not new CLI flags/JSON fields. Synchronize the guide index. No screenshot is required.

Pack 0015 remains responsible for the complete interactive/structured Artisan surface and wider guide update. No guide claim may imply persistence of operation IDs or end-to-end target traceability.

## 17. Implementation Rules

### Frozen operation registry

| Operation name | Handler | Existing behavior delegated to |
|---|---|---|
| `acceptance.list` | `ListAcceptanceApps` | Planner's typed plan; all matched catalog tuples |
| `acceptance.plan` | `PlanAcceptance` | Planner's typed plan; executable tuples with full matched counts |
| `acceptance.run` | `RunAcceptanceScenario` | Registry/Dispatcher, Profile lookup, RunOptions and RunService |

Names are version-1 machine identifiers, not console command invocations. Listing retains catalog tuples/counts/fingerprint.

- Handler contract: `name(): string` and `handle(OperationRequest $request, string $correlationId, ?string $operationId): OperationResult`.
- Registry public methods: `register(string $name, Closure $factory): void`, `has(string $name): bool` (no factory invocation), and `resolve(string $name): ?AcceptanceOperationHandler`.
- One explicit name maps to one lazy factory. Names must be source-owned, bounded ASCII identifiers. Duplicate registration throws a fixed `LogicException('operation_registry_duplicate')`; invalid registration throws a fixed `InvalidArgumentException('operation_registry_invalid')`. These configuration failures carry no raw value or previous exception.
- Unknown resolution returns null without invoking any factory. The selected factory only is invoked; wrong handler type/name or factory failure is normalized to fixed `UnexpectedValueException('operation_registry_invalid')` without retaining its unsafe exception chain.
- Bind the registry/service in `AppServiceProvider`. Register three lazy factories explicitly. Constructing the registry/service or resolving list/plan must not resolve RunService, Runner, BrowserFactory, Profile or DB.
- Service entry: `execute(OperationRequest $request): OperationResult`. It owns request validation, trace creation, dispatch, safe typed result construction and operation logs. It returns a DTO, never JSON or a response object. No console/process exit code appears in request/result DTOs.

### Immutable request contract

`OperationRequest` is a final readonly typed object, constructed with `operation: string`, `parameters: array = []`, `version: int = 1`, `correlationId: ?string = null`. Validation occurs through the service; unsupported version, unknown parameter keys/types or invalid correlation ID return `operation_request_invalid` before handler/domain side effects. Typed constructor arguments are not an HTTP/untyped decoding API.

A supplied correlation ID must be a canonical lowercase UUID string (36 characters, hexadecimal groups 8-4-4-4-12, version 1–8 and RFC variant); otherwise return rejection with a newly generated safe UUID and omit the rejected value. Missing correlation and all operation IDs are generated as UUIDv4. Upstream operation IDs are not accepted.

Service ordering: establish safe trace IDs and identify a registered operation through `has()` without constructing its handler; reject invalid version/correlation first; return `operation_not_found` for an unknown name; then reject unknown parameter fields/types for the recognized operation; only then resolve/invoke its factory. This also fixes combined-invalid-input behavior. Unknown names are never returned/logged.

| Operation | Allowed parameter fields |
|---|---|
| list/plan | `app`, `scenario`, `variant`, `suite`, `capability`, `tag`, `disposition`, `evidence_mode`: lists of strings, default []; `limit`: int or string, default 1000 |
| run | required `app_key`: string, `scenario_key`: string, `profile_id`: int or string; optional `browser`: string/null, `headed`: bool default false, `timeout_ms`: int/string/null, `slow_mo_ms`: int/string/null |

Only primitive values of those shapes are allowed; no model, Closure, arbitrary object, credential payload, context options or launch arguments. No arbitrary extra fields.

Handlers adapt machine parameter names to existing value objects. List/plan domain key/enum/limit validation remains in `AcceptanceSelector` (using existing `fromOptions` coercion, including its four-character string limit rule), returning existing `acceptance_selector_invalid`; do not duplicate planner/catalog logic.

Run preserves resolution order: App -> Scenario/default variant -> Profile -> option/default validation -> RunService. Do not validate key existence, numeric Profile content or browser/value validity early and change the accepted first failure. Browser null/empty and numeric null/empty retain existing default behavior; direct integer numeric inputs are converted to the same decimal representation accepted by the old command parser.

No arbitrary variant parameter or Component field is added. Catalog providers still resolve only `default`; legacy Apps use existing legacy lookup. No real App is registered.

### Immutable result contract

Decision 0014 governs this contract. `OperationResult` is final readonly with these typed semantic properties; this list is not a JSON schema:

- `version: int`: 1.
- `operation: ?string`: registered code-owned name, or null for unknown/untrusted name.
- `status: string`: `succeeded`, `rejected` or `failed`.
- `errorCode: ?string`: approved code from section 21 or null.
- `correlationId: string`: safe UUID on every returned execution result.
- `operationId: ?string`: service-created UUID for every recognized run invocation (including rejection), null for inspection/unknown operations.
- `retryable: ?bool` and `permanent: ?bool`: section 21 classification; no retry is scheduled.
- `adminActionRequired: bool`.
- `errors: array`: map of fixed approved request-field names to lists containing fixed `operation_request_invalid` codes only; no input values. Unrecognized parameter names are summarized under `parameters`, never echoed.
- `data: CatalogOperationData|RunOperationData|null`: safe typed operation data, never an arbitrary array/model/handler object.

The same service-generated trace values reach the handler, returned DTO and required log. Results must not echo unknown operation names. Handlers cannot override validated request identity or trace IDs; the service verifies/reconstructs the safe typed result. Service/result/payload classes contain no client serializer, `JsonSerializable`, JSON key order, empty-object convention, HTTP response or console renderer. The existing planner's canonical JSON hashing remains an unchanged domain fingerprint algorithm.

`CatalogOperationData` is final readonly with `planVersion: int`, `catalogVersions: array<string,string>`, `matched: int`, `executable: int`, `excluded: int`, `byDisposition: array<string,int>`, `fingerprint: string`, and `items: list<AcceptancePlanItem>`. Copy/validate arrays without caller-owned references; reuse only the existing immutable, allowlisted `AcceptancePlanItem` and its immutable metadata. No mutable `stdClass`, model or arbitrary nested object is retained.

List/plan handlers build this typed snapshot from the existing planner result, preserving all counts, domain item ordering, metadata allowlists, traversal limits and fingerprint. The list contains all matched items; the plan contains executable items only, while counts still cover the full match set. No catalog/provider logic is duplicated. `listed`/`planned` display status, snake-case JSON keys and map encoding belong to CLI presentation, not this DTO.

`RunOperationData` is final readonly with `testStatus: TestStatusEnum`, `testId: int`, `appKey: string`, `scenarioKey: string`, and `errorCode: ?string`, copied from resolved identities and approved safe Test fields. Its code agrees with the normalized result code. Finished Test means succeeded; failed Test means failed with its approved code. Any other returned Test status remains non-success, preserving the old exit behavior. Pre-Test/exception failures have `data = null`. Never call model `toArray()` or expose Profile extra, Test/Step data, duration, raw exceptions or Step payloads.

If RunService throws after creating a Test but supplies no Test ID, return null data and correlated safe failure. Do not search for a latest Test or alter the service/exception/schema to recover an unavailable ID. Report this traceability limit.

### Thin compatible commands

Commands collect arguments/options, construct typed requests, call the operation service, project its typed data to the approved legacy JSON and map results to exits. All client projection/encoding stays in the three scoped command files in this stage; Pack 0015 later owns the shared renderer. Commands do not resolve App/Scenario/Profile or orchestrate execution. Preserve current signatures, automatic discovery and list/plan inheritance where useful.

For catalog data, the CLI alone creates `status=listed/planned`, `plan_version`, `catalog_versions` (JSON object even when empty), nested `counts` (`matched`, `executable`, `excluded`, `by_disposition`), `fingerprint` and `items` in the accepted order/shape. For run data, it maps the enum/scalars to `status`, `test_id`, `app_key`, `scenario_key`, `error_code` in accepted order. CLI output tests own these encoding details; direct-service tests assert typed properties and semantic values.

| CLI condition | JSON projection | Exit |
|---|---|---:|
| list/plan success | typed catalog data mapped to existing plan JSON | 0 |
| returned finished Test | five existing run fields | 0 |
| returned failed Test | five existing run fields | 1 |
| pre-execution rejected result | `status=rejected`, `error_code` only | 2 |
| service/registry/catalog/unexpected failure | `status=failed`, `error_code` only | 1 |

One JSON line, existing encoding conventions, no added envelope/UUID/classification fields. Service success alone must never turn a failed Test into exit 0. No `Artisan::call`, output parsing, terminal dependency or implicit second client in services/handlers.

## 18. Architecture Constraints

Decision 0008 owns the application boundary; Decision 0014 specifies semantic DTOs and client-owned presentation. The layer is project-owned and delegates accepted domain behavior; Core and target Apps remain independent/read-only. Use installed Laravel/PHP conventions and capability-neutral operation names.

No filesystem scanning, DB-defined handler registration, queued/persisted operation model, authorization framework, interactive prompts, new transports, consumer/source identifiers or additional capabilities. No idempotency/retry guarantee is introduced: separate run invocations retain existing independent-run semantics.

The registry may prepare lazy factories through Laravel's container; handlers/request/result code must not depend on `Illuminate\Console\Command`, Artisan, Symfony Console input/output, Laravel Prompts, terminal state or CLI process lifetime.

## 19. Validation Rules

Prove deterministic and lazy registration, semantic equivalence between direct DTOs and decoded Artisan projections, unchanged command exits/signatures/defaults/rejection order, exact safe typed DTO/data contracts, stable errors and correlated logs. Compare typed properties to client fields, not a JSON service return. Inspection must cross no DB, RunService, Runner or browser boundary.

Test unknown operations/versions/fields/types/UUIDs before side effects, failed handler factories, synthetic provider exceptions, known/unknown result codes and safe omission from logs/results. Source-check the new layer for console, `env()`, dotenv, filesystem discovery, network or raw model serialization dependencies.

Use only section 27's isolated commands after execution approval. Verify changed-file scope and preserve the 28-file execution startup baseline outside directly required in-scope maintenance. No claim of runtime/API/UI/target traceability beyond section 21.

## 20. Security Rules

Reject unknown input keys/types. Construct all result DTOs through exact safe allowlists; CLI projection cannot bypass them. Allow only fixed codes, safe source-owned catalog identities and UUIDs. Omit Profile extra, credentials, account/session/token inputs, URLs, selectors, payloads, raw exceptions/chains/stack traces and arbitrary Step data.

Do not read any environment file, expose prior process values, access a target, or modify protected configuration/vendor/dependencies. Use only inert synthetic sentinels and mocks; no usable credential or production data. Safe code normalization is explicit in section 4, not silently broadened during implementation.

## 21. Error Handling / Logging / Traceability Requirements

Impact: boundary validation, exceptions, stable codes, shared logs, tracing, sensitive-output protection and tests. Decision 0014 assigns error presentation to clients without changing section 21 codes/classification/events. No HTTP/UI/external-adapter/queue/state-machine contract changes. Requests/results contain no human message field; clients receive machine codes. Typed trace properties map explicitly to the existing snake-case structured log context below.

### Error mappings and classification

All errors are operator/client observable by code. No new audit store or automatic retry. `R` = retryable; `P` = permanent until correction; `A` = privileged/developer investigation required. A null classification means unknown, never permission to retry. Input corrections remain normal operator work.

| Trigger / stable code | Result | R | P | A | Log level |
|---|---|---|---|---|---|
| unknown operation: `operation_not_found` | rejected | false | true | false | warning |
| unsupported version/parameter shape/unknown field/invalid UUID: `operation_request_invalid` | rejected | false | true | false | warning |
| invalid operation registration/type/factory/name: `operation_registry_invalid` | failed / fixed configuration exception at registration | false | true | true | error when observed by service |
| duplicate operation registration: `operation_registry_duplicate` | fixed configuration exception at registration; no execution | false | true | true | no service execution event |
| `acceptance_app_not_found`, `acceptance_scenario_not_found`, `acceptance_profile_not_found`, `acceptance_selector_invalid`, `acceptance_selector_not_found`, `acceptance_catalog_limit_exceeded`, `acceptance_variant_not_executable` | rejected | false | true | false | warning |
| `acceptance_configuration_invalid` before RunService | rejected | false | true | false | warning |
| `acceptance_configuration_invalid` from started RunService / returned Test | failed | false | true | false | error |
| `acceptance_registry_invalid`, `acceptance_registry_duplicate`, `acceptance_catalog_invalid`, `acceptance_catalog_duplicate` | failed | false | true | true | error |
| `acceptance_catalog_changed` | failed | true | false | false | error |
| `acceptance_catalog_failed` (including unexpected inspection handler exception) | failed | null | null | true | error |
| `acceptance_browser_start_failed` | failed | true | false | true | error |
| `acceptance_result_persistence_failed` | failed | true | false | true | error |
| `acceptance_scenario_failed`, `acceptance_step_failed` | failed | false | true | false | error |
| `acceptance_command_failed` (unexpected run failure / unrecognized raw code) | failed | null | null | true | error |

For existing `AcceptanceCatalogException`, preserve its rejected/failed classification. For `AcceptanceRegistryException`, preserve only the two fixed listed codes; never expose its public constructor's arbitrary safeMessage/code. For `AcceptanceExecutionException`, preserve only the four listed codes, with started/not-started behavior above; omit previous exceptions and arbitrary safeMessage/code. A failed Test with null code retains null in legacy data and unknown classification; do not invent a stored code or change state.

The handler owns run-start tracking. Unknown errors from inspection map to `acceptance_catalog_failed`; unknown run errors map to `acceptance_command_failed`. No new exception file or domain error semantics.

### Structured logs

- `tms.acceptance.operation.failed`: one shared boundary event for each returned rejected/failed result, warning/error per table, including known failed Test results.
- `tms.acceptance.operation.completed`: info for a successful run with its Test ID. Successful list/plan produce no new log.
- Fixed context allowlist: `operation` (registered name/null), `error_code`, `correlation_id`, `operation_id`, `retryable`, `permanent`, `admin_action_required`, plus `test_id` only when returned by RunService. An exception class is optional and limited to the three named safe exception classes above.
- No raw operation/parameter/field name, command arguments, exception text/chains, arbitrary class name or object context.
- Commands emit no second failure event. Existing RunService/Core internal logs remain unchanged, without a claim that they acquire these new trace fields. Guide/tests must reflect replacing the two command-owned events.

### Trace flow and state impact

Service entry -> UUID creation/validated correlation reuse -> selected handler -> returned operation result -> operation boundary log.

Run additionally carries a service-generated operation UUID and, when supplied by RunService, existing persisted Test ID. A run rejection has an operation UUID but no Test ID or Test creation. The Test ID joins the new boundary log to unchanged internal logs/persistence.

No operation/correlation UUID is stored in Test/Step or a new database record, and neither UUID enters catalog fingerprints. No API/UI trace exposure, target trace propagation or end-to-end guarantee. If a service throws without returning its created Test, the new layer cannot link that Test ID.

Required tests assert code/classification/status/log event/context and identical IDs across request handling/result/log, including invalid upstream ID replacement and sentinel omission. The result alone is not evidence of logging or sensitive-data verification.

### Required Follow-up Updates

Pack 0014 owns durable operation/Run/report correlation and exception-path Test linkage; Pack 0015 owns broader client trace presentation. These limitations do not block this in-memory boundary when explicitly approved.

## 22. Data Model / Migration / Relationship Requirements

No data model, migration or relationship changes required. No table, column, foreign key, snapshot, backfill, retention or data rollback changes.

The existing RunService still creates Test/Step history for executed runs. Only fake-backed, disposable SQLite in-memory Feature tests may exercise that existing schema. Trace UUIDs remain in memory/logs; no raw request/result snapshot is persisted.

## 23. Commenting Requirements

Follow `docs/ai/rules/COMMENTING-RULES.md`. Explain the typed transport boundary, lazy registration invariant, safe DTO construction, client-owned legacy JSON projection and test-only dotenv isolation where code alone is insufficient. No redundant comments or broad annotation cleanup.

## 24. Testing Requirements

Contract/behavior/side-effect/error/security assertions are required. No HTTP authorization, queue, external integration, idempotency or UI scenarios apply in this Pack.

- Inspection: typed counts/items/fingerprint/version map, metadata-only selection, empty scope, limits and rejection behavior; CLI separately proves the accepted JSON version-object representation. No RunService/Runner/BrowserFactory resolution, DB lookup, provider execution or Test/Step creation.
- Run: prove resolved App/Scenario/Profile/RunOptions reach existing RunService, only default provider variant is resolved, and option/default/error ordering matches the accepted command.
- Use fake providers and mocked RunService/Runner/BrowserFactory only. Synthetic Profile/Test/Step records belong solely to in-memory SQLite. Assert returned Test fields and prohibited payload absence, not just record existence.
- Registry/boundary: duplicate/invalid/unknown registrations, lazy factory counts, wrong factory type/name, unsupported version, unknown keys/types, invalid/reused/generated UUIDs, deterministic dispatch and no side effects after validation failure.
- Error/log safety: safe unknown names/field names/raw exception/public-code sentinels, exact stable codes/exits/classification, success/failure logs and correlation/Test identity. Preserve existing assertions when updating log expectations; do not weaken CLI shape/negative-boundary coverage.
- Presentation separation: direct calls return `OperationResult` and the correct immutable catalog/run DTO (or null data), without console/Prompts/JSON serialization. Commands produce the exact accepted JSON from those properties. Assert count/item/status/code/Test identity equivalence without requiring DTOs to serialize themselves.

## 25. Acceptance Checklist

- [x] immutable versioned request/result boundary exists with the approved contracts;
- [x] catalog/run data are typed DTOs and legacy JSON encoding belongs only to the commands;
- [x] three explicit lazy handlers delegate existing domain logic;
- [x] existing commands are thin clients and preserve accepted projections/options/exits/defaults/rejection order;
- [x] direct and Artisan list/plan/run behavior is equivalent in approved synthetic tests;
- [x] safe error-code normalization and trace/classification/log contracts are proven by scoped tests;
- [x] inspection has no execution/DB/browser side effects in scoped tests;
- [x] synthetic tests boot without environment files and use only disposable in-memory SQLite;
- [x] targeted Persian guide/index synchronization is complete;
- [x] no Core/schema/target/queue/API/UI/MCP/dependency/secret scope was added.

## 26. Tests to Add

| Exact test file | Behavior and main assertions |
|---|---|
| `tests/Unit/AcceptanceOperationRegistryTest.php` | source registration, duplicate/invalid fixed exceptions, unknown lookup without factory calls, selected lazy resolution, invalid factory output/name/failure without raw exception leakage |
| `tests/Unit/AcceptanceOperationServiceTest.php` | immutable typed request/result/catalog/run data; validation/UUID rules; deterministic handler input; data allowlists and copied arrays; known/unknown exception/result codes; status/classification; exact shared logs and sentinel omission; no console/Prompts/client serializer dependency |
| `tests/Feature/AcceptanceOperationCommandCompatibilityTest.php` | typed direct-service properties match decoded Artisan catalog/run JSON and fingerprints; same safe synthetic successful/failed run semantics and Test linkage; exact legacy JSON/exits/options, including empty catalog-version object and field order; selection/default-variant/rejection ordering; in-memory safety and no real execution |

Focused updates to `AcceptanceCatalogCommandTest.php` and `AcceptanceRunCommandTest.php`: inject the test-only dotenv-free bootstrap, update logging assertions to the section 21 contract, retain and extend accepted JSON/exits/defaults/negative side-effect checks. No new shared test framework or source bootstrap changes.

## 27. Tests to Run

Only after execution approval. Classify existence/source/Git inspection as safe-read-only; syntax/check-only formatting/tests as validation-or-test. Tests are authorized only for section 11's disposable environment. Capture exact commands and results; do not call real list/plan/run against live registered targets.

The exact Artisan test argument groups are:

```text
test --compact tests/Unit/AcceptanceOperationRegistryTest.php tests/Unit/AcceptanceOperationServiceTest.php
test --compact tests/Feature/AcceptanceOperationCommandCompatibilityTest.php tests/Feature/AcceptanceCatalogCommandTest.php tests/Feature/AcceptanceRunCommandTest.php
list --format=json
```

Ordinary `php artisan test ...` / `php artisan list --format=json` can load `.env`; do not run that entrypoint. With the section 11 process values and absence guards, use the following PowerShell entrypoint, passing each exact group above separately:

```powershell
php -r 'define("LARAVEL_START", microtime(true)); require "vendor/autoload.php"; $pack09App = require "bootstrap/app.php"; $pack09App->loadEnvironmentFrom("__tms_pack_0009_no_dotenv__"); exit($pack09App->handleCommand(new \Symfony\Component\Console\Input\ArgvInput));' -- test --compact tests/Unit/AcceptanceOperationRegistryTest.php tests/Unit/AcceptanceOperationServiceTest.php
php -r 'define("LARAVEL_START", microtime(true)); require "vendor/autoload.php"; $pack09App = require "bootstrap/app.php"; $pack09App->loadEnvironmentFrom("__tms_pack_0009_no_dotenv__"); exit($pack09App->handleCommand(new \Symfony\Component\Console\Input\ArgvInput));' -- test --compact tests/Feature/AcceptanceOperationCommandCompatibilityTest.php tests/Feature/AcceptanceCatalogCommandTest.php tests/Feature/AcceptanceRunCommandTest.php
php -r 'define("LARAVEL_START", microtime(true)); require "vendor/autoload.php"; $pack09App = require "bootstrap/app.php"; $pack09App->loadEnvironmentFrom("__tms_pack_0009_no_dotenv__"); exit($pack09App->handleCommand(new \Symfony\Component\Console\Input\ArgvInput));' -- list --format=json
```

Use the inspected PowerShell 7.6.5 native argument passing with the PHP program single-quoted as shown; a read-only argv probe confirmed that the command name/file arguments occupy the positions used by Symfony/Collision. The initial stop-parsing (`--%`) proposal failed a read-only probe on this host and was replaced; no application/test was booted in that probe.

This entrypoint is intended to run the real Artisan kernel/commands while selecting an absent environment filename; Feature applications must independently use the same guard. Unit tests remain isolated from application boot. Do not create the absent filenames or edit production bootstrap files. It has not been run as an application/test command during normalization.

Also run:

- `php -l <file>` once for each of the ten created PHP implementation files, four edited implementation PHP files, three created test PHP files and two edited test PHP files listed in sections 8–9 (19 files).
- `php vendor/bin/pint --test app/Acceptance/Operations app/Providers/AppServiceProvider.php app/Console/Commands/ListAcceptanceCommand.php app/Console/Commands/PlanAcceptanceCommand.php app/Console/Commands/RunAcceptanceCommand.php tests/Unit/AcceptanceOperationRegistryTest.php tests/Unit/AcceptanceOperationServiceTest.php tests/Feature/AcceptanceOperationCommandCompatibilityTest.php tests/Feature/AcceptanceCatalogCommandTest.php tests/Feature/AcceptanceRunCommandTest.php`.
- `git diff --check`, `git status --short --branch`, `git diff --name-only` and scoped diff review.
- Static new-layer scan for `Artisan`, `Command`, `Console`, `Laravel\Prompts`, `JsonSerializable`, JSON/client rendering, `env(`, dotenv/environment-file reads, raw model serialization and executable discovery. Inspect every match; the unchanged planner fingerprint algorithm is outside the new layer and not client serialization. Do not equate no matches with runtime proof.
- Verify all 34 Pack sections, local documentation references, applicable index status and byte preservation of unrelated startup documentation; include untracked files because Git diff omits them.

No browser, target, queue/worker/server, full unrelated suite, migration CLI, dependency operation, network fetch or environment-file read. If the isolated entrypoint/bootstrap cannot be proven safe, stop and report the exact blocker instead of falling back to plain Artisan.

## 28. Expected Output

Reusable list/plan/run operations with typed catalog/run data, thin compatible command presentation, focused tests, targeted guide maintenance and actual lifecycle/index evidence. Produce the Agent Final Report and eventual Run Report at their separate gates, including this normalization's reasons, Decision 0014's DTO/client correction and two added DTO paths, safe-code proposal, log changes and validation isolation.

No second client or claim that all TMS capabilities are now implemented.

## 29. Operator Execution Checklist

### Before AI Execution

- Approve execution of this revised version-1 contract, including two typed payload DTO files, safe raw-code fallback, classification, shared log changes and CLI-owned compatibility projection. Decision 0014's architecture/documentation acceptance does not authorize implementation.
- Confirm continued execution on the inspected `main` tree while preserving the 28 pre-existing documentation changes outside directly required in-scope maintenance.
- Approve only fake-backed, dotenv-free, disposable in-memory validation; no target/browser execution.

### After AI Execution

- Compare typed direct-service properties with decoded Artisan JSON for one synthetic list/plan and one successful/failed synthetic run; inspect the legacy client field/map conventions separately.
- Check the shared operation result/log correlation and returned Test linkage without added legacy JSON fields.
- Review the targeted Persian logging paragraph and stated non-durable/exception-path trace limits.

## 30. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md`. Report the normalization record and Decision 0014 propagation, exact typed DTO/client projection contracts, code fallback/log behavior, compatibility and side-effect evidence, commands actually run, tests added/run/results/limitations, changed-file classification, documentation maintenance and deferred clients.

Do not report normalization-only work as executed implementation or create an accepted Run without its separate operator gate. Eventual persistent Run must preserve normalization decisions and initial/final validation history.

## 31. Review Checklist

Review typed immutability/version validation and payload classes, lazy registry/handler identity, domain delegation, transport independence and absence of client serialization in services, safe data allowlists, command JSON compatibility, error-code allowlist, classification/trace/log safety, test isolation, source/protected-area scope and targeted guide synchronization.

No accepted-history rewrite, weakened regression checks or unsupported E2E claim.

## 32. Rollback / Safety Notes

Through separately authorized scoped changes only, remove this Pack's operation wiring/files/tests and restore direct command delegation plus its exact guide/lifecycle entries. Preserve startup documentation changes. No schema/data rollback exists; test data is disposable in-memory state.

Initial normalization edited only this Pack and its index row. Decision 0014 propagation is a separate authorized documentation change to its exact affected Decision/Pack/index records. Preserve both sets of documentation work; do not reset the working tree or revert unrelated roadmap records.

## 33. Stop Conditions

Stop for missing predecessor acceptance; changed/unapproved contract; required Core/schema/queue/target change; incompatible supported CLI JSON/exits/options/defaults/rejection order; unsafe result/log output; executable discovery; unrelated refactor/dependency/config changes; unknown/shared/production database; dotenv-reading bootstrap; insufficient test isolation; required file outside sections 8–9; or missing execution approval.

Use the conflict/change/remediation rules if source and accepted architecture conflict. No commit is authorized.

## 34. Open Questions

No unresolved design choice remains. Execution of the revised contract, the inspected dirty `main` baseline, isolated validation, post-execution review and final acceptance were approved on 2026-10-08. Commit was separately authorized and is the commit containing this accepted Pack and Run.

Web/MCP transports/authentication, full-variant/Component execution, input/approval workflows, durable operations, async/batch/retry and complete Artisan tooling remain later-stage scopes under Decision 0013.
