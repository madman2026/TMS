# TMS-DECISION-0014 — Typed service results and client-owned presentation

Status: Accepted
Scope: Project architecture / service contracts and client presentation
Source: Operator discussion on service DTOs and Laravel Prompts, followed by the instruction to record the decision and apply it to affected Packs
Date: 2026-10-08
Review At: Pack 0009 execution review and Pack 0015 normalization
Blocking: Yes for service/client contracts in affected Packs
Closure Condition: superseded by a separately approved service/client contract decision
Next Review At: `AI-PACK-TMS-OPERATION-SERVICE-LAYER-0009`

Current Architecture Notice (2026-10-08): Decision 0015 retains immutable typed service DTOs and client-owned presentation, but supersedes the legacy list/plan/run projection-preservation clauses. The active client contract is one explicit version-2 hierarchy representation.

## Type

4. Project-level architecture decision; clarification and extension of Decision 0008.

## Decision

Application/operation services accept typed requests and return immutable, versioned, language-neutral DTOs with typed operation data, stable status/error codes and safe trace identifiers. DTOs express the operation's meaning and approved data allowlist. They contain no encoded JSON, HTTP response, console output, terminal dependency, exit code, translated display message or client-specific schema/order convention.

Each client maps the shared DTO to its own presentation contract. Artisan owns option/input collection, prompting, human tables/messages, CLI JSON serialization, stdout/stderr and exit codes. A future API adapter owns its response resource/JSON schema and HTTP mapping; a future Web app owns its view model and UI. MCP likewise adapts the same service result. Clients must not reproduce domain validation, authorization, state transitions, retry decisions or orchestration.

Safety is enforced before presentation: services return only approved safe fields and normalized codes, never models, raw exceptions, arbitrary objects or sensitive payloads. Client renderers must also respect that allowlist. Transport independence does not permit each client to expose a different set of unrestricted data.

Use the existing `laravel/prompts` package for the interactive Artisan experience in Pack 0015. Prompt selection/search, forms, confirmation, tables and progress belong to the CLI adapter. Prompt validation may give immediate feedback, but the operation service remains authoritative for validation and approval facts.

CLI execution has two presentation paths: interactive terminal UX and deterministic script/JSON output. `--json`, `--no-interaction` and non-TTY execution must not ask questions, show animations or contaminate machine stdout with human guidance. Missing required input follows the approved operation validation contract; a disabled prompt must not invent input or confirmation. UI confirmation is not a persisted domain approval. Cancelling a prompt must not implicitly request cancellation of an existing domain operation.

Preserve accepted `acceptance:list`, `acceptance:plan` and `acceptance:run` default JSON, options and exits. A change of default interaction or a new versioned output mode requires the explicit compatibility contract in Pack 0015 normalization. DTO property names and JSON property names need not be identical; JSON empty-map representation and property order are client concerns.

Native Windows must use Laravel's configured Symfony fallback. Rich terminal behavior is available where the installed Prompts/runtime supports it, including Windows under WSL. Do not force ANSI/TTY behavior on unsupported terminals. No package installation, dependency upgrade or vendor change is authorized by this decision.

## Reason

The operator required services to return DTOs so CLI, API and Web clients can process the same operation result independently. The earlier Pack 0009 normalization coupled internal result data to the existing CLI JSON shape. The operator also approved using Laravel Prompts to improve interactive CLI UX while retaining automation support.

## Impact

- Decision 0008 remains accepted; this decision makes its typed-result and client responsibilities explicit.
- Pack 0009 replaces its serialized internal data contract with typed catalog/run data. Its commands alone reconstruct the accepted JSON projection. Interactive UX remains outside Pack 0009.
- Packs 0010–0013 preserve typed results when adding scaffolding, prerequisites, resource and async operations. Their services/handlers do not depend on Prompts or client serializers.
- Pack 0014 validates semantic report DTOs, counts and safe fields. JSON presentation tests belong to the CLI in Pack 0015, rather than defining the report service contract.
- Pack 0015 implements Prompts, terminal fallback, client rendering and script/JSON behavior, with separate tests for domain equivalence and presentation compatibility.
- No implemented source contract, persisted schema, status model, accepted Run or roadmap order changes in this documentation task. Execution/acceptance/commit approval remain separate.

## Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes — error presentation belongs to clients
API Error Contract Impact: Future adapter mapping only; no endpoint/schema/HTTP status introduced
UI / Admin UI Error Impact: CLI guidance/fallback only; no Web/Admin UI implementation
Error Code Impact: No — no code is introduced, renamed or reinterpreted by this decision
Exception Handling Impact: Yes — clients render safe DTO codes, never raw exception text
External Error Normalization Impact: No
Retry / Failure Classification Impact: No — accepted service classification remains authoritative
Status Model Impact: No
Logging Impact: Yes — client rendering must not duplicate service failure logs
Traceability Impact: Yes — adapters consume the existing approved safe identifiers
Sensitive Data Impact: Yes — service and presentation allowlists both remain mandatory
Testing Impact: Yes
Documentation Impact: Yes
```

Affected scenarios: validation rejection, execution failure, missing script input, prompt interruption and unsupported terminal rendering. Previously Pack 0009 specified CLI JSON conventions inside service data, while Pack 0015 left terminal behavior broad. Approved behavior separates semantic DTOs from client encoding and keeps prompting out of services.

Stable codes, retryable/permanent/admin-action classification, failure state and shared boundary events remain those of the applicable approved operation Pack. In particular, the existing `acceptance_command_failed` fallback is retained for compatibility; its CLI-oriented name is not grounds to rename it here. Client-only input/confirmation/interruption exits and localized message mappings must be finalized in Pack 0015 before its execution; this decision does not invent domain error codes for them.

Human-readable guidance is CLI-owned Persian text mapped from stable codes; JSON codes are language-neutral. Technical exception messages/chains and sensitive input are omitted. No new permission model or secret persistence is introduced.

Pack 0009 keeps `tms.acceptance.operation.failed` (warning/error according to its mapping) and successful-run `tms.acceptance.operation.completed` (info), with its existing fixed context allowlist. Client rendering adds no second failure event. DTO correlation/operation IDs and returned Test linkage keep Pack 0009's in-memory/log-only limits. This decision adds no ID generation, persistence, target propagation or end-to-end trace claim. Pack 0015 may present safe IDs under its normalized client contract; existing default JSON receives no added fields in Pack 0009.

Required tests: direct typed results without console boot; service validation and safe-data omission; exact legacy CLI JSON/exits; interactive/script equivalence; prompt-free JSON/non-TTY/`--no-interaction`; native-Windows/test fallback; prompt interruption and confirmation without unintended mutation; sentinel absence in stdout/stderr/logs. Full TTY rendering requires an approved terminal check, since Laravel uses fallback during unit tests.

## Files to Update

- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/decisions/TMS-DECISION-0008-transport-neutral-operations-and-artisan-client.md` — reference to this clarification only
- `docs/project/ai/packs/AI-PACK-TMS-OPERATION-SERVICE-LAYER-0009.md`
- `docs/project/ai/packs/AI-PACK-TMS-TARGET-MODULE-COMPONENT-SDK-0010.md`
- `docs/project/ai/packs/AI-PACK-TMS-OPERATOR-INPUT-APPROVAL-0011.md`
- `docs/project/ai/packs/AI-PACK-TMS-TARGET-RESOURCE-LIFECYCLE-0012.md`
- `docs/project/ai/packs/AI-PACK-TMS-ASYNC-BATCH-ORCHESTRATION-0013.md`
- `docs/project/ai/packs/AI-PACK-TMS-QUERY-REPORT-EVIDENCE-0014.md`
- `docs/project/ai/packs/AI-PACK-TMS-ARTISAN-OPERATOR-CLIENT-0015.md`
- `docs/project/ai/packs/PACKS-INDEX.md`

## Required change request

None. This clarifies accepted Decision 0008 and revises unexecuted/proposed Pack instructions. It does not contradict an active Canonical contract or change implemented CLI behavior. No implementation remediation or accepted-history rewrite is required.

## future-work

Required Follow-up Update: Pack 0015 must update `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md` and `docs/project/ai/guides/GUIDES-INDEX.md` with interactive versus JSON usage, non-TTY/`--no-interaction`, Windows fallback, safe confirmations/input handling, error guidance and trace visibility. Keep the current guide accurate to accepted source until implementation exists.

Web/API/MCP clients require later approved Packs with their own serializers/presenters and transport-specific contracts. This decision adds none of those clients. Pack 0015 still requires normalization of exact signatures, JSON schemas, messages, confirmation/cancellation exits and limits before execution.

## Review / Resolution

```text
Reviewed In: operator discussion on DTO service output and Laravel Prompts on 2026-10-08
Review Result: operator instructed recording the decision and applying it to affected Packs
Resolution: accepted
Next Review At: Pack 0009 execution review, then Pack 0015 normalization
Resolution Notes: documentation approval only; no runtime implementation or commit approval
```

## Notes

Installed-source evidence: `composer.lock` pins `laravel/prompts` to v0.3.24. `vendor/laravel/framework/src/Illuminate/Console/Concerns/ConfiguresPrompts.php` configures interactivity from input/TTY and fallback for native Windows and unit tests. These files are read-only evidence, not edit scope.

Packs 0016 and 0017 retain their bootstrap/regeneration scopes and inherit accepted service contracts; no additional implementation change is required there by this decision. Core's executor Pack is unaffected because no runtime execution contract or Core-owned UI is changed. Future owner-local stages must consume the accepted DTO boundary when generated.

Documentation validation (2026-10-08): passed. One Decision was created; Decision 0008 gained a clarification reference; Packs 0009–0015 and the Decision/Pack indexes were synchronized (11 files total). All seven Packs retain sections 1–34 once and in order. All 221 distinct backticked local path references in the changed records resolve to existing paths (100), declared future creates/directories (119), or explicit absent configuration-cache guards (2); no unexplained missing reference remains. Whitespace/final-newline checks and `git diff --check` passed. SHA-256 comparison against this task's 27-file startup baseline confirmed that the 17 files outside its edit scope were byte-for-byte preserved.

Validation used read-only file/source/Git inspection and document-structure/reference checks. An initial `rg` call used shell brace syntax unsupported by PowerShell and failed before searching; it was replaced with `rg -g` filters and passed. No Artisan, PHPUnit, Pint, dependency, browser, database or target command ran; runtime tests are not applicable to this documentation-only change. No source/config/lockfile/environment file changed and no commit was made. Guide updates remain the explicit implementation follow-up above; no executed Run or acceptance evidence is fabricated.
