# AI-PACK-TMS-ARTISAN-OPERATOR-CLIENT-0015 — Complete Artisan operator client

Status: `accepted — operator accepted 2026-10-10; commit authorized`

Generated: `2026-10-07`

Regenerated: `2026-10-10`

Baseline: `c166b561c3ca860b7d543e9ab334cb9292c2e1d3`

Decision: accepted Decisions 0005, 0008–0012, and 0014–0016. Decision 0015 owns stage 9 and the single hierarchy contract. Decision 0013 and pre-cutover compatibility clauses are superseded history.

Execution / Acceptance / Commit: Packs 0010–0014, Core Pack 0003, and Remediations 0001–0002 are accepted and committed. The operator approved this regenerated Pack for execution, accepted the validated result, and authorized commit on 2026-10-10.

## 1. Task ID

`AI-PACK-TMS-ARTISAN-OPERATOR-CLIENT-0015`

## 2. Task Title

Implement the complete interactive and structured Artisan client for every accepted Acceptance operation.

## 3. Goal

Expose all 18 registered Acceptance operations through thin Artisan commands with one active hierarchy contract, deterministic version-2 machine output, explicit opt-in English-only interactive UX, safe confirmation and interruption behavior, bounded input/output, and no business logic or direct persistence in the console layer.

## 4. Context

Stages 1–8 now provide a complete transport-neutral operation layer: catalog inspection, exact-tuple execution, target-module scaffolding, prerequisite input/approval, durable sync/async batches, recovery, status, reports, and coverage. Only `acceptance:list`, `acceptance:plan`, and `acceptance:run` currently have Artisan clients.

Remediation 0001 already replaced their obsolete pre-cutover contracts with the active version-2 hierarchy contract. This Pack preserves that accepted version-2 behavior; it does not preserve or recreate any version-1 projection, partial tuple, implicit Variant, alias, fallback hierarchy, or dual serializer. Decision 0014 keeps typed service DTOs transport-neutral and assigns prompting, English guidance, JSON projection, stdout/stderr, and exits to the CLI adapter.

Decision 0015 assigns the complete shared Artisan operator client to stage 9. Packs 0016 and 0017 consume or document this client for target onboarding and scale hardening; they do not introduce a later shared Laravel Prompts implementation. Therefore this Pack completes the prompt UX for all 18 commands rather than deferring it.

The registered operation set contains exactly 18 names. The historical Draft incorrectly proposed a standalone catalog-validation command, although no such operation exists, and omitted `acceptance.prerequisite.request.cancel`. This regeneration removes the nonexistent command and adds the missing prerequisite-cancel client.

## 5. Related Release / Phase

Decision 0015, stage 9. Pre-Release; no release-specific or phase-specific directory is activated.

## 6. Related Epic / Feature / Story

Primary operator and test-author CLI for synthetic Acceptance onboarding, prerequisite completion, execution, recovery, status, reporting, and coverage.

## 7. Source References

- `docs/project/ai/TMS-AI-PROFILE.md`
- `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`
- `docs/project/ai/decisions/TMS-DECISION-0008-transport-neutral-operations-and-artisan-client.md`
- `docs/project/ai/decisions/TMS-DECISION-0009-operator-input-approval-and-sensitive-values.md`
- `docs/project/ai/decisions/TMS-DECISION-0010-async-batch-state-and-recovery.md`
- `docs/project/ai/decisions/TMS-DECISION-0011-extensible-executors-and-capabilities.md`
- `docs/project/ai/decisions/TMS-DECISION-0012-automated-e2e-coverage-and-traceability.md`
- `docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md`
- `docs/project/ai/decisions/TMS-DECISION-0015-clean-acceptance-hierarchy-cutover.md`
- `docs/project/ai/decisions/TMS-DECISION-0016-prerequisite-operation-naming.md`
- accepted Packs and Runs 0010–0014, accepted Core Pack/Run 0003, and accepted Remediations/Runs 0001–0002
- current registered handlers, typed requests/results, accepted CLI v2 commands/tests, Persian operator guide, and installed Laravel Prompts integration named below

## 8. Files to Create

- `app/Console/Acceptance/AcceptanceCommand.php`
- `app/Console/Acceptance/OperationResultRenderer.php`
- `app/Console/Commands/CreateAcceptanceAppCommand.php`
- `app/Console/Commands/ValidateAcceptanceAppCommand.php`
- `app/Console/Commands/CreateAcceptanceComponentCommand.php`
- `app/Console/Commands/ImportAcceptanceScenariosCommand.php`
- `app/Console/Commands/PrepareAcceptancePrerequisiteCommand.php`
- `app/Console/Commands/SubmitAcceptanceInputCommand.php`
- `app/Console/Commands/GrantAcceptanceApprovalCommand.php`
- `app/Console/Commands/CancelAcceptancePrerequisiteCommand.php`
- `app/Console/Commands/StartAcceptanceBatchCommand.php`
- `app/Console/Commands/AcceptanceStatusCommand.php`
- `app/Console/Commands/ResumeAcceptanceBatchCommand.php`
- `app/Console/Commands/RetryAcceptanceItemCommand.php`
- `app/Console/Commands/CancelAcceptanceBatchCommand.php`
- `app/Console/Commands/AcceptanceReportCommand.php`
- `app/Console/Commands/AcceptanceCoverageCommand.php`
- `tests/Unit/AcceptanceOperationResultRendererTest.php`
- `tests/Feature/AcceptanceOperatorCommandsTest.php`
- `tests/Feature/AcceptanceOperatorJsonContractTest.php`

After accepted execution, create `docs/project/ai/runs/AI-PACK-TMS-ARTISAN-OPERATOR-CLIENT-0015-run-001.md`.

## 9. Files to Edit

- `app/Console/Commands/ListAcceptanceCommand.php`
- `app/Console/Commands/PlanAcceptanceCommand.php`
- `app/Console/Commands/RunAcceptanceCommand.php`
- `tests/Feature/AcceptanceOperationCommandContractTest.php`
- `tests/Feature/AcceptanceCatalogCommandTest.php`
- `tests/Feature/AcceptanceRunCommandTest.php`
- `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`
- `docs/project/ai/guides/GUIDES-INDEX.md`
- `docs/project/ai/packs/AI-PACK-TMS-ARTISAN-OPERATOR-CLIENT-0015.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md` after accepted execution

No other source, test, configuration, dependency, target, Core, migration, model, route, API, Web, MCP, decision, change, remediation, review, or accepted-history file is in scope.

## 10. Files to Read / Reference

Required files for this Pack:

- `docs/project/ai/TMS-AI-PROFILE.md`
- the decisions and accepted lifecycle records named in section 7
- `app/Providers/AppServiceProvider.php`
- `app/Acceptance/Operations/AcceptanceOperationService.php`
- `app/Acceptance/Operations/Data/OperationRequest.php`
- `app/Acceptance/Operations/Data/OperationResult.php`
- `app/Acceptance/Operations/Data/CatalogOperationData.php`
- `app/Acceptance/Operations/Data/RunOperationData.php`
- `app/Acceptance/Operations/Data/ModuleChangeData.php`
- `app/Acceptance/Operations/Data/TargetModuleValidationData.php`
- `app/Acceptance/Prerequisites/Data/PrerequisiteOperationData.php`
- `app/Acceptance/Execution/Data/BatchPrerequisiteReference.php`
- `app/Acceptance/Execution/Data/BatchOperationData.php`
- `app/Acceptance/Execution/Data/BatchItemOperationData.php`
- `app/Acceptance/Reporting/Data/AcceptanceStatusView.php`
- `app/Acceptance/Reporting/Data/AcceptanceReport.php`
- `app/Acceptance/Reporting/Data/AcceptanceCoverageView.php`
- `app/Acceptance/Coverage/Data/SourceCaseMapping.php`
- `app/Acceptance/Targets/Data/TargetResourceLifecycleData.php`
- every existing or created section 8–9 command/test/guide path
- `composer.lock`
- `vendor/laravel/framework/src/Illuminate/Console/Concerns/ConfiguresPrompts.php`
- only the installed `vendor/laravel/prompts/src/` functions/classes actually used
- applicable shared commenting, execution, error, Git, scope, testing, reporting, and documentation-maintenance rules

Do not read `.env`, target corpora, raw evidence, unrelated modules, vendor packages not used by the CLI, or unrelated lifecycle records.

## 11. Configuration / Settings Requirements

No config file, environment variable, `.env.example`, Admin setting, database-backed setting, external-integration setting, secret setting, queue/cache/log setting, or new test setup is required.

Use the already installed `laravel/prompts` v0.3.24 and Laravel 12 console integration. Do not change `composer.json`, `composer.lock`, vendor source, terminal configuration, queue configuration, or deployment configuration.

Existing bounded settings remain authoritative:

- catalog CLI limit: existing selector default/maximum of 1000;
- report and coverage page sizes: `config('acceptance.reporting.default_page_size')` and `max_page_size`;
- source/export ceilings: existing Pack 0014 configuration;
- batch concurrency, leases, and execution settings: existing accepted configuration.

JSON input files are read-only client input. Maximum accepted sizes are 1 MiB for source mappings or batch prerequisite references and 64 KiB for prerequisite input maps. A file must contain one UTF-8 JSON document with no trailing non-whitespace data. Paths and file contents are never echoed or logged.

No operator manual setup is required. A queue worker is operationally required only for an async batch after it is started; this Pack documents the existing requirement but does not start or configure a worker.

## 12. Do Not Change

Do not change operation names, handler registration, typed request/result DTOs, service validation, domain approval, state transitions, retry/cancel safety, queue dispatch, executor/resource behavior, models, migrations, configuration, dependencies, vendor source, target modules, Core, Test/Step persistence, routes, Web/API/MCP clients, real targets, or external integrations.

Do not add an `acceptance.catalog.validate` operation or command. Do not add aliases, deprecated signatures, partial hierarchy requests, implicit defaults, compatibility projections, arbitrary DTO serialization, generic reflection serialization, direct model queries, direct queue dispatch, direct filesystem mutation, or business/orchestration decisions to commands.

Do not accept raw secrets, credentials, tokens, Authorization values, target payloads, or persisted async input literals in command arguments/options. Do not implement export, raw evidence display, or artifact download.

## 13. Normalized Client Contract

### 13.1 Command and operation matrix

| Artisan command | Operation | Required script input |
|---|---|---|
| `acceptance:list` | `acceptance.list` | optional repeatable selectors and bounded `--limit` |
| `acceptance:plan` | `acceptance.plan` | optional repeatable selectors and bounded `--limit` |
| `acceptance:run` | `acceptance.run` | `app component suite scenario variant profile` |
| `acceptance:app:create` | `acceptance.app.create` | `module app`; dry-run unless `--apply --confirm` |
| `acceptance:app:validate` | `acceptance.app.validate` | `module` |
| `acceptance:component:create` | `acceptance.component.create` | `module component`; dry-run unless `--apply --confirm` |
| `acceptance:scenarios:import` | `acceptance.scenarios.import` | `module --mappings=<json-file>`; dry-run unless `--apply --confirm` |
| `acceptance:prerequisite:prepare` | `acceptance.prerequisite.request.prepare` | either `--request-id=<uuid>` or full tuple plus profile |
| `acceptance:prerequisite:input:submit` | `acceptance.prerequisite.input.submit` | `request lock-version --inputs=<json-file>` |
| `acceptance:prerequisite:approval:grant` | `acceptance.prerequisite.approval.grant` | `request lock-version scope --confirm` |
| `acceptance:prerequisite:request:cancel` | `acceptance.prerequisite.request.cancel` | `request lock-version --confirm` |
| `acceptance:batch:start` | `acceptance.batch.start` | `profile mode`, optional selectors and `--prerequisites=<json-file>` |
| `acceptance:batch:resume` | `acceptance.batch.resume` | `batch operation lock-version`, optional prerequisite file |
| `acceptance:batch:item:retry` | `acceptance.batch.item.retry` | `item lock-version --confirm` |
| `acceptance:batch:cancel` | `acceptance.batch.cancel` | `batch operation lock-version --confirm` |
| `acceptance:status` | `acceptance.status` | exactly one of `--batch` or `--operation` |
| `acceptance:report` | `acceptance.report` | `batch`, optional `--after-item` and bounded `--limit` |
| `acceptance:coverage` | `acceptance.coverage` | `app`, optional cursor, disposition filters, and bounded `--limit` |

All commands expose `--interactive` and `--json`. `--json` is explicit documentation of script mode; omitting both flags also uses JSON script mode so the accepted current automation default remains deterministic. `--interactive` is the only entry into human/prompt mode.

### 13.2 Mode precedence and terminal behavior

1. `--interactive` is rejected with client code `cli_mode_invalid` and exit 2 when combined with `--json`, native `--no-interaction`, or a non-interactive input/terminal.
2. `--json`, `--no-interaction`, non-TTY, CI, and default execution never call prompt, table animation, progress, spin, pause, or confirmation helpers.
3. Interactive mode uses Laravel Prompts and Laravel's configured Symfony fallback. Native Windows and PHPUnit/test execution must not force raw terminal behavior. Rich rendering is used only where the installed runtime reports it supported.
4. Interactive prompt cancellation/interrupt returns client code `cli_interrupted`, writes one concise English diagnostic to stderr, exits 130, and invokes no mutating operation.
5. Missing or malformed script input returns `cli_input_invalid`, one version-2 JSON document on stdout, exit 2, and no operation-service call.

### 13.3 Confirmation contract

- `--apply` is the only way to change scaffolding/import from the service default dry-run to mutation. Interactive `--apply` asks for confirmation; script `--apply` additionally requires `--confirm`.
- prerequisite approval, prerequisite cancellation, item retry, and batch cancellation require interactive confirmation or script `--confirm`.
- missing confirmation returns `cli_confirmation_required`, exit 2, and no operation invocation.
- no `--force`, overwrite, collision bypass, or unmanaged-file replacement option exists. Existing service collision/managed-region rules remain authoritative.
- UI confirmation is client-local and never substitutes for persisted prerequisite approval.
- cancelling a prompt never invokes either prerequisite-cancel or batch-cancel.

### 13.4 Input document contract

- `--mappings` contains a JSON list mapped explicitly to `SourceCaseMapping`; enum, tuple, assertion, reason, version, and maximum-file constraints are checked before operation invocation.
- `--inputs` contains a JSON object keyed by prerequisite input key. Each value has exactly `source` and typed `value`. Script mode accepts no inline `--input` values. Secret inputs must use `source: secret_reference`; raw secret literal sources are rejected by the authoritative service and are never echoed.
- `--prerequisites` contains a JSON list mapped explicitly to `BatchPrerequisiteReference` with the full tuple, request UUID, and version.
- interactive prerequisite submission first calls the read-only prepare/discover operation for the supplied request UUID to obtain typed requirement metadata, prompts with masking for secret-reference values, then invokes only the submit operation for mutation. This is the only deliberate two-operation interactive flow.
- every decoder uses explicit allowlisted keys and constructors. No arbitrary object hydration is allowed.

### 13.5 JSON schema and stdout/stderr

- Every script invocation emits exactly one compact JSON document followed by one newline on stdout. No progress, warning, prompt, ANSI decoration, or human prose may contaminate stdout.
- Existing accepted version-2 `list`, `plan`, and `run` success/failure field sets and ordering remain exact. This preserves the current active Remediation-0001 contract, not a superseded legacy projection.
- Every newly exposed operation uses one version-2 envelope in this exact top-level order: `schema_version`, `operation`, `status`, `error_code`, `correlation_id`, `operation_id`, `retryable`, `permanent`, `admin_action_required`, `errors`, `data`.
- `errors` is a JSON object, including `{}` when empty. `data` is `null` on failure and an explicitly mapped operation-specific object on success. Enums use their string values; timestamps use their existing immutable ISO-8601 value; semantic maps remain JSON objects even when empty.
- The renderer explicitly maps every approved property of `ModuleChangeData`, `TargetModuleValidationData`, `PrerequisiteOperationData`, `BatchOperationData`, `BatchItemOperationData`, `AcceptanceStatusView`, `AcceptanceReport`, and `AcceptanceCoverageView`, including their nested typed values. It must not call `json_encode` on an arbitrary DTO/model, use reflection, expose Eloquent data, or add fields absent from the safe DTO.
- Client-local failures use exactly `schema_version`, `status`, and `error_code`; their allowed codes are `cli_mode_invalid`, `cli_input_invalid`, `cli_confirmation_required`, and `cli_interrupted`.
- Human diagnostics and prompt-interruption text use stderr. Interactive successful tables/summaries use stdout. Raw exceptions and rejected input values are never printed.

### 13.6 Exit matrix and verbosity

| Result | Exit |
|---|---:|
| succeeded operation | 0 |
| rejected operation or client input/mode/confirmation error | 2 |
| failed operation or unexpected safe client read/render failure | 1 |
| prompt interruption/cancellation | 130 |

Interactive default output shows the result, stable code when present, classification, bounded identifiers, and one next action. Native `-v` additionally shows safe correlation/operation IDs. Verbosity never changes JSON fields. No open signature, schema, mode, confirmation, interruption, exit, or limit choice remains after operator approval of this regenerated Pack.

## 14. Language / Translation Requirements

Multilingual / Translation Impact: documentation only. RTL/LTR Impact: none in CLI. API/Admin UI impact: No.

All CLI-visible text is English-only, including field names, enum values, operation names, stable codes, interactive labels, confirmations, validation guidance, error summaries, tables, and next actions. Keep the finite English stable-code/action map centralized in `OperationResultRenderer`; do not create translation files in this Pack and do not duplicate messages across commands.

The operator guide remains Persian, with LTR examples, option names, IDs, paths, code values, and shell fragments. No Persian or other localized text may be emitted by CLI source at runtime. No Web/Admin UI layout, translation catalog, or locale metadata is created.

## 15. UI / Admin UI Requirements

No Web, public, or Admin UI is created. The only UI is the Artisan terminal adapter.

Interactive mode uses bounded Laravel Prompts text/select/multiselect/confirm/table/spinner capabilities only where the installed version supports them. Profile, batch, item, request, operation, and cursor identifiers are prompted as validated text/numeric values; commands must not query models directly to populate choices. Catalog choices may come only from a read-only operation result. Empty, validation-error, success, failure, interruption, unsupported-terminal, and confirmation-required states must be clear and deterministic.

No route, permission, menu, form, Blade, CSS, asset, dashboard, screenshot, or browser validation is required.

## 16. Operator Documentation Requirements

Operator Documentation Impact: Yes. Update required: Yes.

Guide: `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`.

Rewrite the guide to document:

- the full 18-command matrix and exact signatures;
- default JSON, explicit `--json`, opt-in `--interactive`, native `--no-interaction`, non-TTY/CI behavior, Windows fallback, and WSL/rich-terminal limits;
- App/Component scaffolding dry-run/apply, validation, source-mapping import, and no-overwrite behavior;
- catalog list/plan, exact-tuple single run, prerequisite prepare/input/approval/cancel, sync/async batch start, status/resume/retry/cancel, report, and coverage;
- JSON input-file schemas and size limits with safe placeholders only;
- confirmation/interruption rules, exit codes, stdout/stderr separation, stable codes, safe trace IDs, and next actions;
- queue-worker prerequisite for async execution without starting or configuring one;
- report/coverage cursors, page limits, attempt/evidence metadata, and no raw artifact/export behavior;
- synthetic/testing-only scope and prohibition on real targets/secrets.

Update `GUIDES-INDEX.md` to mark the guide current for accepted Pack 0015 behavior after execution. No screenshots are required.

## 17. Implementation Rules

- Follow existing PHP 8.2/Laravel 12/PHPUnit conventions, strict parameter/return types, constructor injection, and the repository Laravel/testing skills.
- `AcceptanceCommand` owns mode selection, client-local failure output, bounded JSON-file decoding helpers, confirmation/interruption safety, and one final service invocation boundary. It contains no domain validation or persistence.
- `OperationResultRenderer` owns all DTO-to-client mapping, JSON encoding, English human rendering, safe code/action mapping, stdout/stderr routing, and exit mapping. It receives typed results; it never resolves handlers, queries models, dispatches jobs, changes state, or logs service failures again.
- Each command maps arguments/options/prompts to one `OperationRequest` and delegates to `AcceptanceOperationService`. The section 13.4 interactive prerequisite discovery call is the only approved preliminary operation call.
- Existing list/plan/run JSON construction moves into the shared renderer without changing their accepted version-2 output bytes for equivalent semantic values.
- Selector options remain repeatable arrays and retain OR-within/AND-between semantics. Script parsing must not add wildcard, regex, negation, comma splitting, implicit hierarchy values, or default Variant behavior.
- Optional interactive arguments exist only to permit prompting. Script-mode completeness is checked before calling the service; authoritative semantic validation remains in `AcceptanceOperationService` and downstream services.
- Prompt validation may provide immediate format feedback but must not duplicate registry, catalog, prerequisite, approval, transition, retry, resource, executor, reporting, or coverage decisions.
- JSON file readers perform bounded read-only access and explicit decoding. They never write, delete, normalize in place, or log the file/path/content.
- Use no new dependency, helper package, translation system, serializer, command alias, or hidden compatibility branch.

## 18. Architecture Constraints

- Preserve `CLI input/Prompts -> OperationRequest -> AcceptanceOperationService -> OperationResult/typed data -> OperationResultRenderer -> JSON or human terminal`.
- Commands are framework adapters. Application/domain behavior, state transitions, validation authority, retryability, permanence, admin-action classification, and orchestration remain in accepted services.
- Preserve the only executable identity: `App -> Component -> Suite -> Scenario -> Variant -> Step`.
- Future Web/API/MCP adapters consume typed operation results; they must never parse Artisan output. This Pack creates none of those adapters.
- Preserve command idempotency and accepted state-machine rules. A duplicate/retry/cancel effect is decided only by the operation layer.
- Do not introduce a client-specific source-system identifier or consumer-specific core behavior.

## 19. Validation Rules

- Prove all 18 commands are discovered with exact names, arguments, options, descriptions, and no aliases.
- Prove default, `--json`, `--no-interaction`, non-TTY, and CI paths make zero prompt/animation calls and emit exactly one parseable JSON document on stdout.
- Prove `--interactive` conflicts and non-TTY rejection occur before service invocation.
- Prove each command builds the exact accepted operation name and parameter keys/types; final interactive and script requests are semantically equal for the same values.
- Prove current list/plan/run version-2 snapshots and exits remain exact.
- Prove every new DTO family has an exact JSON snapshot/map-shape test and bounded English human rendering without arbitrary fields.
- Prove Acceptance CLI source contains no Persian or other non-ASCII user-facing text.
- Prove confirmation denial, missing `--confirm`, and prompt interruption invoke no mutation and never translate into a domain cancel.
- Prove JSON input size, syntax, shape, enum, and key allowlists; rejected files/values/path strings do not appear in stdout, stderr, or logs.
- Prove no direct model query, queue dispatch, target I/O, filesystem mutation, or operation-handler resolution exists in command/renderer source.
- Run a safe manual terminal smoke only after separate operator approval if it would execute a mutating synthetic workflow. Automated fallback tests do not claim rich-TTY rendering coverage.

## 20. Security Rules

- Never read `.env` or accept real credentials, tokens, Authorization values, personal identifiers, real targets, or production URLs.
- Script input values that may be sensitive are file-based, never argv-based. Interactive secret-reference prompts use masked input; the value must remain a reference, not a raw secret.
- Omit raw input maps, mapping contents, prerequisite references, file paths, exception messages/chains, model attributes, Test/Step payloads, selectors, target resource IDs, raw evidence, and URLs from client errors and logs.
- Render only safe identifiers and fields already allowlisted by typed DTOs. Do not render `ResourceReference::id`; use only approved hashes/semantic fields already present in result DTOs.
- Confirmations must occur before mutating operation invocation. Interrupt/denial must have zero side effects.
- Fixtures, docs, JSON examples, and tests use `example-sensitive-value`, UUIDs, and `https://example.test` only; no real secret or target data.

## 21. Error Handling / Logging / Traceability Requirements

Error Handling Impact: Yes. API Error Contract Impact: No. UI/Admin UI Error Impact: terminal only. Internal Exception Impact: client read/render boundary only. External Integration Impact: No. Logging Impact: no new log event. Traceability Impact: presentation only. Error Code Impact: four client-local codes. Security/Sensitive Data Impact: Yes.

### Client-local scenarios

| Scenario | Code | Exit | Retry/action | State/log impact |
|---|---|---:|---|---|
| conflicting/unsupported presentation mode | `cli_mode_invalid` | 2 | correct flags/terminal | no operation, state, or log |
| missing, oversized, unreadable, malformed, or shape-invalid client input | `cli_input_invalid` | 2 | correct safe input | no operation, state, or log |
| required confirmation absent/denied | `cli_confirmation_required` | 2 | review then confirm explicitly | no operation, state, or log |
| prompt cancelled/interrupted | `cli_interrupted` | 130 | rerun if desired | no operation, state, domain cancel, or log |

These codes are CLI presentation codes only and are not added to `OperationResult::ERROR_CODES`. Returned operation errors retain the accepted stable service code, status, retryable/permanent/admin-action values, state effect, and existing `tms.acceptance.operation.failed` or completion logging. The renderer must not reinterpret classification or emit a duplicate log.

Unexpected safe JSON-file read/decode or renderer failures must omit the raw exception and sensitive context, render `cli_input_invalid` when caused by operator input or the operation's accepted fallback failure when caused after invocation, and exit according to section 13.6. No new exception class or API error contract is introduced.

Traceability: JSON for newly exposed operations includes returned `correlation_id` and nullable `operation_id`. Human errors show the stable code and safe correlation/operation ID when available; normal human success shows them only under `-v`. Existing list/plan/run JSON remains exact. No identifier is newly generated, persisted, propagated to targets, or logged by the renderer.

Error tests must assert exact code, exit, stdout/stderr, no service call/side effect for client-local failures, returned classification for operation failures, safe identifier visibility, no duplicate log, and sentinel omission.

No external-error normalization, callback, HTTP status, Admin UI permission, or follow-up error work is required.

## 22. Data Model / Migration / Relationship Requirements

No data model, migration, table, column, foreign key, model relationship, index, constraint, snapshot, backfill, seed, retention, or rollback change is allowed. Commands may cause only the existing operation-layer effects explicitly requested by the operator; tests use the existing test database/fakes and assert safe no-mutation paths.

## 23. Commenting Requirements

Follow `docs/ai/rules/COMMENTING-RULES.md`. Add concise comments only for the non-obvious mode-precedence, DTO/client projection, stdout/stderr, bounded-file, and prompt-interruption safety boundaries. Do not narrate command signatures, ordinary array mapping, or framework calls.

## 24. Testing Requirements

Test Quality Required: Yes. Behavioral, contract, side-effect, negative/error, confirmation, fallback, and secret-masking assertions required. Database assertions are required only where an existing operation would mutate state. Authorization/API/browser/external-integration tests are not applicable.

Use PHPUnit and existing Laravel console-test conventions. Use operation-service fakes/mocks and existing factories/providers; do not add Pest, Dusk, browser downloads, packages, real queue workers, real target adapters, external network, or production-like data.

Required behavior:

- all command signatures and operation mappings are exact;
- every JSON document has exact ordering, map/list representation, safe fields, and one-line stdout;
- human output is English-only, bounded, actionable, and never mixed into JSON stdout;
- interactive and script values produce the same final `OperationRequest`;
- prompt-free modes call no Prompt helper;
- cancellation/denial/interruption causes no service mutation;
- current list/plan/run tests remain meaningful and detect projection/signature drift;
- unsafe sentinels never appear in stdout, stderr, logs, command history fixtures, or persisted rows.

## 25. Acceptance Checklist

- [x] all 18 registered operations have exactly one Artisan command;
- [x] no nonexistent catalog-validation command or alias exists;
- [x] commands/renderer contain no business logic, direct model query, queue dispatch, target I/O, or filesystem mutation;
- [x] current list/plan/run version-2 JSON and hierarchy semantics remain exact;
- [x] all new JSON schemas are exact, versioned, deterministic, safe, and one document per invocation;
- [x] interactive mode is explicit, English-only, bounded, and uses installed Laravel Prompts/fallback correctly;
- [x] JSON/default/no-interaction/non-TTY/CI modes never prompt or animate;
- [x] confirmations and interruptions have zero unintended mutation;
- [x] prerequisite input, approval, and cancellation use Decision 0016 operation names only;
- [x] status/report/coverage output respects existing limits/cursors and exposes no raw artifacts;
- [x] Persian operator guide and guide index explain the English-only CLI behavior accurately;
- [x] focused, predecessor, full-suite, formatting, syntax, and diff validation pass;
- [x] no sensitive argv/output/log/history value is introduced.

## 26. Tests to Add

### `tests/Unit/AcceptanceOperationResultRendererTest.php`

- Purpose: prove explicit mapping for every result DTO family independently of command input.
- Behavior: exact JSON key order/map shapes, enum/timestamp conversion, English code/action selection, exit mapping, stderr separation, and unsupported-data rejection.
- Assertions: no reflection/arbitrary serializer path; empty maps encode as objects; unsafe DTO-adjacent sentinel fields are absent; no logging side effect.
- Negative cases: client-local codes, failed/rejected results, unknown safe-code guidance, and encoding failure without raw exception exposure.

### `tests/Feature/AcceptanceOperatorCommandsTest.php`

- Purpose: prove discovery, signatures, mode precedence, request construction, interactivity, confirmation, interruption, fallback, and side effects for all 18 commands.
- Setup: bound operation service/handlers, Prompt fakes or Laravel console fallback, existing database factories only where a real accepted service path is necessary.
- Assertions: exact operation/parameter mapping, semantic interactive/script equivalence, zero prompt calls in script modes, zero service/mutation on denial/interruption, and exact exits.
- Negative cases: missing/conflicting values, non-TTY interactive request, invalid JSON files, confirmation missing/denied, prompt interruption, not-found/conflict/waiting/failure result families.

### `tests/Feature/AcceptanceOperatorJsonContractTest.php`

- Purpose: prove exact one-line machine contracts for every operation family.
- Assertions: current list/plan/run snapshots remain exact; new envelope/data snapshots, property order, empty-object representation, stdout/stderr purity, trace/classification fields, cursor/limit output, and sentinel omission.
- Negative cases: every client-local code, rejected/failed operation, raw exception/file path/input omission, and no ANSI/progress/prompt bytes.

## 27. Tests to Run

- `php -l` for every created/edited PHP file.
- focused new renderer/operator/JSON test files.
- `tests/Feature/AcceptanceOperationCommandContractTest.php`.
- `tests/Feature/AcceptanceCatalogCommandTest.php`.
- `tests/Feature/AcceptanceRunCommandTest.php`.
- accepted predecessor operation, prerequisite, batch, reporting, and coverage feature/unit suites affected by command mapping.
- `php artisan list --format=json` and selected `php artisan help <command>` invocations; these are discovery/help validation only.
- `php artisan test --compact` full regression.
- `vendor/bin/pint --dirty --format agent` and a final no-change verification.
- `git diff --check` plus exact changed-path audit.

No migrate, rollback, queue worker, browser, target, network, Composer, cache, environment, or real interactive mutation command may run. Any rich-TTY manual smoke is separately approved, synthetic, non-secret, and reported as verified or unverified.

## 28. Expected Output

- 15 new Artisan command classes plus the 3 updated existing commands cover exactly 18 registered operations;
- one shared command boundary and one explicit typed-result renderer;
- exact version-2 JSON and safe English-only interactive presentation;
- three focused test files plus updated current v2 compatibility tests;
- rewritten Persian CLI guide and synchronized guide/Pack indexes;
- after accepted execution, one Run Report and synchronized Run index.

## 29. Operator Execution Checklist

### Before AI Execution

- [x] Confirm the section 13 command names/signatures, default-JSON and explicit-interactive mode, four client-local codes/exits, input-file schemas, and confirmation matrix.
- [x] Confirm preserving the current Remediation-0001 version-2 `list/plan/run` projection is required active-contract preservation, not forbidden legacy compatibility.
- [x] Confirm no rich-TTY manual mutating workflow is required for technical acceptance; automated Symfony fallback coverage is sufficient unless separately approved.

### After AI Execution

- Review the English CLI wording and next-action guidance against the Persian operator guide.
- Verify the 18-command discovery/help matrix and exact JSON snapshots.
- Verify confirmation denial/interruption and non-TTY/`--no-interaction` have no side effects or prompts.
- Decide whether to perform a separately approved safe rich-TTY synthetic smoke; otherwise accept the explicit unverified limitation.

## 30. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md` and include the documentation-maintenance section required by `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`.

Report the command/operation/signature matrix, mode and confirmation decisions, JSON/human mapping, exit/stdout/stderr behavior, Prompts/Windows fallback evidence, safe-input handling, trace/error guidance, exact tests/commands/results, changed-path audit, guide coverage, rich-TTY limitation, and deferred Web/API/MCP clients.

## 31. Review Checklist

Review command thinness; exact operation names; absence of aliases/legacy branches; active v2 list/plan/run preservation; explicit DTO mapping; mode precedence; prompt-free script paths; native Windows/test fallback; confirmations/interruption; English-only CLI quality; bounded file/page input; stdout/stderr purity; stable exits/codes/classifications; trace visibility; secret/sentinel omission; test value; guide accuracy; and scope/index/Run maintenance.

## 32. Rollback / Safety Notes

Rollback removes the 15 new commands and shared console adapter, restores the three existing commands/tests/guide to the accepted baseline, and leaves all services, durable data, migrations, queues, targets, and operation contracts untouched. No data rollback exists or is required.

Do not use destructive Git or filesystem commands. Do not run a mutating Artisan workflow during validation. A code rollback must not restore any pre-cutover client, alias, partial tuple, implicit default, or legacy serializer.

## 33. Stop Conditions

Stop for any missing registered operation; need for a new/renamed service operation or DTO; command-only business/domain validation; direct model/queue/target access; raw DTO/model/reflection serialization; incompatible change to the accepted current v2 list/plan/run contract; prompt/animation in script mode; unsafe argv/file/output/log value; confirmation/interruption side effect; unsupported Prompts API; dependency/vendor/config/migration change; real target/browser/network/worker use; failing tests whose smallest safe fix is outside sections 8–9; or any canonical/source conflict.

## 34. Open Questions

No open question remains. Execution, technical validation, post-execution acceptance, and commit authorization are complete.
