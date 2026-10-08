# AI-PACK-TMS-ARTISAN-OPERATOR-CLIENT-0015 — Complete Artisan operator client

Status: `draft — depends on stages 1–7; normalization and separate operator approval required`

Generated: `2026-10-07`

Decision: Decisions 0007–0014; Decision 0014 applied on 2026-10-08 (documentation only).

## 1. Task ID

`AI-PACK-TMS-ARTISAN-OPERATOR-CLIENT-0015`

## 2. Task Title

Expose all approved TMS operations through fast interactive and structured Artisan commands.

## 3. Goal

Give test authors/operators a complete CLI for onboarding, validation, input/approval, execution, recovery, and reporting while keeping commands thin.

## 4. Context

Stages 1–7 provide client-neutral typed operations. Current CLI covers list/plan/single-run only. Decision 0008 requires every approved operation through Artisan and future client reuse. Decision 0014 assigns JSON/human presentation to the client and selects Laravel Prompts for interactive CLI UX. The installed lockfile currently contains `laravel/prompts` v0.3.24; no installation or upgrade is needed for this decision.

## 5. Related Release / Phase

Decision 0013, stage 8.

## 6. Related Epic / Feature / Story

Primary operator and test-author experience.

## 7. Source References

Decisions 0007–0013; accepted stages 1–7; existing Acceptance commands/tests; Persian CLI guide binding in the project profile.

`docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md` — service DTOs, CLI rendering, interactive/script modes and terminal fallback.

## 8. Files to Create

- `app/Console/Commands/MakeAcceptanceAppCommand.php`
- `app/Console/Commands/ValidateAcceptanceAppCommand.php`
- `app/Console/Commands/MakeAcceptanceComponentCommand.php`
- `app/Console/Commands/ImportAcceptanceScenariosCommand.php`
- `app/Console/Commands/ValidateAcceptanceCatalogCommand.php`
- `app/Console/Commands/AcceptanceRequirementsCommand.php`
- `app/Console/Commands/SubmitAcceptanceInputCommand.php`
- `app/Console/Commands/ApproveAcceptanceOperationCommand.php`
- `app/Console/Commands/StartAcceptanceBatchCommand.php`
- `app/Console/Commands/AcceptanceStatusCommand.php`
- `app/Console/Commands/ResumeAcceptanceBatchCommand.php`
- `app/Console/Commands/RetryAcceptanceItemCommand.php`
- `app/Console/Commands/CancelAcceptanceBatchCommand.php`
- `app/Console/Commands/AcceptanceReportCommand.php`
- `app/Console/Commands/AcceptanceCoverageCommand.php`
- `app/Console/Acceptance/OperationResultRenderer.php`
- `tests/Feature/AcceptanceOperatorCommandsTest.php`
- `tests/Feature/AcceptanceOperatorJsonContractTest.php`

## 9. Files to Edit

- `app/Console/Commands/ListAcceptanceCommand.php`
- `app/Console/Commands/PlanAcceptanceCommand.php`
- `app/Console/Commands/RunAcceptanceCommand.php`
- `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`
- `docs/project/ai/guides/GUIDES-INDEX.md`
- existing command compatibility tests

## 10. Files to Read / Reference

Project profile; Decisions 0008–0012 and 0014; operator-doc, error, security, test rules; all accepted operation handlers/result DTOs; files in sections 8–9. Read-only installed-source evidence: `composer.lock`, `vendor/laravel/framework/src/Illuminate/Console/Concerns/ConfiguresPrompts.php` and only the `vendor/laravel/prompts/src/` helpers actually used. Consult the official Laravel 12 Prompts/Console documentation only as needed and verify availability against the installed version.

## 11. Configuration / Settings Requirements

Commands consume accepted `config/acceptance.php` settings and expose safe effective values. No new `.env`, Admin UI, secret setting, or external integration. Use the already installed Prompts dependency without modifying Composer/vendor. `--json`, `--no-interaction` and non-TTY execution disable all questions and animations. Respect Laravel's native-Windows/unit-test Symfony fallback; do not force raw terminal behavior. Missing input/confirmation must follow the normalized safe policy, never an inferred approval.

## 12. Do Not Change

Application/domain behavior, models/migrations, queue transitions, executor/resource contracts, Web/API/MCP, target modules, dependencies, `.env`, real targets.

## 13. Clarification Questions Before Implementation

Normalization must approve exact command names/signatures, client JSON schema/version, exit-code matrix, confirmation requirements for cancel/retry/overwrite, output verbosity, aliases/deprecation, and max list/report sizes. It must also fix the explicit entry into interactive mode, mode precedence, non-TTY/missing-input behavior, prompt-interruption exits and code-to-Persian-message mapping. Preserve the accepted list/plan/run default JSON/options/exits unless a separately approved versioned mode changes them; adding Prompts does not itself change those defaults.

## 14. Multilingual / Translation Requirements

Operator guide and interactive help/examples are Persian (`fa`). Machine JSON fields/error codes remain English/language-neutral. Human labels, validation guidance and next actions belong to CLI presentation; service DTOs contain no translated display messages. Console direction is terminal-managed; no Admin UI RTL. Avoid hardcoded duplicate messages by central renderer/help conventions. Any translation/message resource needed by the renderer must be named exactly in sections 8–9 at normalization before implementation.

## 15. UI / Admin UI Requirements

No Web/Admin UI. CLI UX uses Laravel Prompts for safe App/Scenario/Profile selection/search, declared input forms, confirmations and human tables where appropriate. Show spinner/progress only in supported interactive TTY mode. On native Windows, use Laravel's configured Symfony fallback; on Linux/macOS/WSL, use the supported rich terminal rendering. Both paths must invoke the same typed operation service and show a clear next action.

`--json`, `--no-interaction`, non-TTY and CI execution must never wait for a prompt or emit terminal animations. JSON stdout contains only the normalized machine document; human diagnostics belong to stderr. UI confirmation cannot replace the prerequisite service's domain approval. Interrupting a prompt must not execute a mutation or silently cancel an existing batch. Exact mode/signature/exit choices remain the normalization gate in section 13.

## 16. Operator Documentation Requirements

Update `ACCEPTANCE-CLI.fa.md` and its guide index with prerequisites; App/Component scaffold and validation; catalog import/list/plan; requirements/input/approval; sync/async/batch start; status/resume/retry/cancel; report/coverage; queue worker; interactive versus JSON/CI usage; `--no-interaction`/non-TTY and missing-input behavior; native-Windows fallback/WSL support; safe confirmation/interruption; safe secret references; common errors, trace IDs and next steps. Describe actual accepted CLI behavior, including retained legacy defaults.

## 17. Implementation Rules

- Every command calls one operation service and renders its typed result.
- Flow: CLI arguments/Prompts -> typed request -> operation service -> typed result/data -> `OperationResultRenderer` -> human display or CLI-owned JSON and exit code. The renderer explicitly maps properties; it must not encode an arbitrary DTO/model wholesale or require a JSON-returning service.
- No direct model query, queue dispatch, target I/O, filesystem mutation, or orchestration in commands.
- New structured output writes one normalized versioned JSON document to stdout; diagnostics go to stderr. Accepted legacy command JSON remains its exact projection unless an explicit approved mode versions it. JSON map representation/order is a client contract, not a DTO constraint.
- Sensitive literals are never accepted as options/arguments for persisted async work.
- Interactive answers are validated by the same operation contract as structured input. Prompt feedback may assist correction but cannot duplicate domain decisions or skip service validation/approval checks.
- Select the presentation mode before invoking Prompts; script mode must not call prompt helpers and rely on their defaults/fallbacks to hide interactivity. Missing required values or unconfirmed mutations follow the section 13 approved policy without side effects.
- Prompt cancellation is local UI behavior. Request a domain cancel only through the separately confirmed cancel operation. No renderer/client-only failure log duplicates a returned service failure event.

## 18. Architecture Constraints

Artisan is the complete first client. Decision 0014 places Prompts/terminal state, human presentation, JSON encoding and exit mapping inside the CLI adapter. Services/handlers/DTOs remain callable without console boot; future API/Web/MCP adapters map the same typed results and never parse Artisan output.

## 19. Validation Rules

Prove command discovery, help/signatures, semantic equivalence of interactive/noninteractive requests/results, separate CLI JSON snapshots, retained legacy defaults, exits, dry-run/no mutation, confirmation gates, pagination/limits, failure next steps, and thin-command architecture. Assert zero prompt/animation calls for JSON/non-TTY/`--no-interaction`, clean stdout/stderr, safe prompt interruption and native-Windows/test fallback. Feature/unit tests use Laravel fallback and do not prove rich TTY rendering; an approved manual terminal check must cover that path or be reported as unverified.

## 20. Security Rules

Hide/mask prompt input where allowed, reject sensitive argv, sanitize shell examples, never render raw exception/model/payload/secret/URL, and prove sentinel absence in stdout/stderr/logs/history records.

## 21. Error Handling / Logging / Traceability Requirements

One CLI renderer maps every accepted typed operation code/status/data to deterministic exits, concise Persian human guidance and language-neutral JSON. Display safe correlation/operation/batch/item IDs only under the normalized client contract; do not add them to retained legacy default JSON implicitly. Raw exceptions are forbidden. Service classification determines retry/permanent/admin-action guidance; clients do not reinterpret it or automatically retry.

Missing script input follows the approved validation/confirmation contract without mutation. Prompt interruption is client-local; normalize its exact exit/message in section 13 without inventing a domain failure or cancel transition. Commands/renderer emit no duplicate service failure event and never log prompt values or raw DTOs. Any needed client-only diagnostic event must be specified at normalization before implementation.

## 22. Data Model / Migration / Relationship Requirements

No model/migration/relationship change. Commands use accepted services only.

## 23. Commenting Requirements

Document typed DTO-to-client mapping, JSON/exit compatibility, prompt/mode/fallback boundary and stdout/stderr separation only where needed.

## 24. Testing Requirements

Feature tests cover every command's happy/invalid/not-found/conflict/waiting/error path as applicable, typed direct-operation equivalence, interactive versus JSON input semantics, confirmations/interruption, dry-run, limits, exits and sensitive sentinel absence. Renderer tests assert exact client schemas without redefining DTO shape. Force/fake fallback and non-TTY input where needed; prove no questions/animations in script modes and no service mutation when a prompt/confirmation is cancelled. Use fakes/test DB only; any full TTY smoke check needs a safe approved synthetic workflow.

## 25. Acceptance Checklist

- [ ] every approved operation has an Artisan client;
- [ ] commands contain no business/orchestration logic;
- [ ] services return DTOs; CLI renderer owns human/JSON presentation and exits;
- [ ] interactive UX is fast and gives next actions;
- [ ] Prompts respects native-Windows fallback and supported TTY rendering;
- [ ] structured/non-TTY/no-interaction modes never prompt or animate and preserve accepted compatibility;
- [ ] Persian guide is complete and validated;
- [ ] no sensitive argv/output.

## 26. Tests to Add

`AcceptanceOperatorCommandsTest.php`: fake-backed typed-service mapping, interactive inputs, same authoritative validation/approval, confirmation/interruption without unintended mutation, Windows/test fallback, help/signatures and human guidance. `AcceptanceOperatorJsonContractTest.php`: exact client JSON/version/maps, retained list/plan/run projections/exits, zero prompts/animations for JSON/non-TTY/`--no-interaction`, stderr separation, safe IDs and sentinel omission. Keep focused per-command cases in these files and existing compatibility suites; name any additional exact test path at normalization before execution.

## 27. Tests to Run

Exact command feature suite, `php artisan list --format=json`, selected help invocations, predecessor operation/queue/report regressions, guide/reference checks, and `git diff --check`. No target I/O.

## 28. Expected Output

Complete command surface, shared renderer, Persian guide, tests, Run Report, and index updates.

## 29. Operator Execution Checklist

Before: approve signatures/client JSON/exits, interactive entry/mode precedence, missing-input/confirmation/interruption rules and the safe TTY/fallback check. After: execute the documented synthetic onboarding-to-report workflow in interactive and JSON modes, verify native-Windows fallback where applicable, and confirm no prompt/animation appears in non-TTY or no-interaction output.

## 30. Agent Final Report

Report command matrix, typed operation-to-presentation mapping, UX/JSON/exits and legacy compatibility, Windows fallback/TTY verification limits, guide sections, security evidence, tests, and deferred API/Web/MCP clients.

## 31. Review Checklist

Review thinness, typed-result versus client schema separation, coverage, Prompts/fallback, prompt-free script modes, interruption/confirmation safety, translation/guide, secret handling, error guidance, and legacy compatibility.

## 32. Rollback / Safety Notes

Command rollback must preserve service behavior and accepted legacy commands until separately deprecated. No data rollback.

## 33. Stop Conditions

Stop for command-only business logic, JSON-returning service dependency, Prompts/terminal dependency in services, missing operation, questions/animations in script mode, unsafe confirmation/interruption, sensitive argv/output, incompatible accepted command without approval, target access, or unaccepted predecessors.

## 34. Open Questions

Exact command/signature, client schema/exit matrix, interactive entry/mode precedence and missing-input/confirmation/interruption behavior from section 13 block approval. The DTO/client boundary and use of Laravel Prompts are accepted by Decision 0014; API/Web/MCP adapters remain separate future Packs.
