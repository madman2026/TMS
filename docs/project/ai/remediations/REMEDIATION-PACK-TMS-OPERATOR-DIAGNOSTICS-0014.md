# REMEDIATION-PACK-TMS-OPERATOR-DIAGNOSTICS-0014

Status: ready
Owner: TMS project — shared operator workflow and diagnostics; bounded DK fixture integration
Date: 2026-10-16
Execution: not started; planning and documentation commit authorized; implementation requires a separate operator instruction

## 1. Task ID

`REMEDIATION-PACK-TMS-OPERATOR-DIAGNOSTICS-0014`

## 2. Task Title

Correct Acceptance error explanations, human reports, pagination, and safe diagnostic traceability.

## 3. Related Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-OPERATOR-DIAGNOSTICS-0020.md`.
Behavioral authority: `docs/project/ai/decisions/TMS-DECISION-0026-acceptance-operator-diagnostics-and-reporting.md`.

## 4. Goal

Let an operator identify the failure stage, known cause, cleanup/retry constraint, and concrete next step from the CLI and its correlated safe diagnostic record.

## 5. Current Problem

The six reviewed defects are discarded exception causes, omitted human failure details, late numeric-bound validation, ambiguous completion wording, generic trace-only guidance, and unnecessary upfront pagination without a visible continuation path. The observed 21-item fixture/cleanup failures did not enter the executor; their target root cause is unproven. Do not present hypothetical lease/auth/network failures as diagnoses of those historical runs.

## 6. Required Change

Implement in two bounded increments within this pack:

1. Presentation: use existing result data to show outcome/counts, grouped failure and cleanup codes, executor-entry evidence, contextual guidance, truthful completion wording, immediate page-size feedback, and default-first pagination. On failed operations render useful safe handler data before returning. Keep script output deterministic.
2. Diagnostics: capture allowlisted causes at the operation/query/report/batch and DK fixture/cleanup catch boundaries that currently discard them. Use Decision 0026's event/context contract, preserve original classifications, and document a usable correlation-to-log workflow. New diagnostics apply prospectively; no reconstruction of old root causes.

Both increments and their tests are required for acceptance. A presentation-only result must not be marked complete.

## 7. Files to Read

- `AGENTS.md`, `docs/ai/start/START-HERE.md`, `docs/ai/start/CONTEXT-MAP.md`
- `docs/project/PROJECT-DOCS-INDEX.md`, `docs/project/ai/TMS-AI-PROFILE.md`
- `Modules/DK/AGENTS.md`, `Modules/DK/docs/ai/README.md`, `Modules/DK/docs/ai/manifest.yaml`, `Modules/DK/docs/ai/GOVERNANCE-PROFILE.md`
- The Decision and Change in section 3; `docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md`
- `docs/project/ai/remediations/REMEDIATION-PACK-TMS-ACCEPTANCE-BATCH-FAILURE-UX-0013.md` as accepted history
- `docs/project/references/SOURCE-DOCS-INDEX.md`, `docs/project/references/TMS-SOURCE-STRUCTURE-SUMMARY.md` as bounded bootstrap context; actual source remains current implementation truth
- `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md`, `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`, `docs/ai/rules/AI-EXECUTION-RULES.md`, `docs/ai/rules/SCOPE-CONTROL-RULES.md`
- `docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md`, `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`, `docs/ai/rules/GIT-AND-COMMIT-RULES.md`, `docs/ai/rules/REPORTING-RULES.md`
- `config/acceptance.php`, `app/Acceptance/Targets/Control/Exceptions/TargetControlException.php`, `app/Acceptance/Targets/Control/TargetControlProtocol.php`
- `app/Acceptance/Reporting/Data/AcceptanceStatusView.php`, `app/Acceptance/Reporting/Data/AcceptanceReport.php`, `app/Acceptance/Reporting/Data/AcceptanceReportItem.php`, `app/Acceptance/Reporting/Data/AcceptanceAttemptView.php`
- `app/Acceptance/Operations/Data/OperationResult.php`, `app/Acceptance/Targets/Data/TargetContext.php`, `Modules/DK/app/Acceptance/Shared/Target/DKTargetResourceAdapter.php`
- Existing files in section 8 and the repository Laravel/testing skills before changing PHP.

## 8. Files to Edit

- `app/Console/Acceptance/AcceptanceCommand.php`
- `app/Console/Acceptance/OperationResultRenderer.php`
- `app/Console/Commands/AcceptanceReportCommand.php`
- `app/Acceptance/Operations/AcceptanceOperationService.php`
- `app/Acceptance/Operations/Handlers/GetAcceptanceStatus.php` — propagate the query correlation into diagnostics
- `app/Acceptance/Operations/Handlers/GetAcceptanceReport.php` — propagate the query correlation into diagnostics
- `app/Acceptance/Reporting/AcceptanceQueryService.php`
- `app/Acceptance/Reporting/AcceptanceReportService.php`
- `app/Acceptance/Targets/TargetResourceCoordinator.php`
- `app/Acceptance/Execution/AcceptanceBatchService.php` — diagnostic capture only; no routing/state/retry redesign
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Fixtures/OperationsHealthFixture.php` — safe cause capture only
- `tests/Feature/AcceptanceOperatorCommandsTest.php`
- `tests/Feature/AcceptanceOperatorJsonContractTest.php`
- `tests/Feature/AcceptanceQueryServiceTest.php`
- `tests/Feature/AcceptanceReportServiceTest.php`
- `tests/Feature/AcceptanceReportingOperationTest.php`
- `tests/Feature/AcceptanceAsyncBatchTest.php`
- `tests/Unit/AcceptanceOperationResultRendererTest.php`
- `tests/Unit/AcceptanceOperationServiceTest.php`
- `tests/Unit/TargetResourceCoordinatorTest.php`
- `Modules/DK/tests/Unit/Acceptance/Components/NotificationDelivery/Fixtures/OperationsHealthFixtureTest.php`
- `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`, `docs/project/ai/guides/GUIDES-INDEX.md`
- This Remediation, its Decision and Change, and their three project indexes for lifecycle status only
- `docs/project/ai/runs/RUNS-INDEX.md` when an execution Run is created

This project's bounded cross-owner scope is expressly recorded in Decision 0026; it does not authorize other DK files. Reconcile any intervening edits to these paths before implementation.

## 9. Files to Create

- `app/Acceptance/Diagnostics/AcceptanceFailureReporter.php` — only if required for shared allowlisted capture; one helper, no reporter registry or framework
- `tests/Unit/AcceptanceFailureReporterTest.php` — if the helper is introduced
- `tests/Feature/AcceptanceOperatorDiagnosticsTest.php` — focused human diagnostics/navigation regressions
- `docs/project/ai/runs/REMEDIATION-PACK-TMS-OPERATOR-DIAGNOSTICS-0014-run-001.md` — only after actual execution and its reporting gate

## 10. Do Not Change

`.env`, environment manifests, dependencies, config values, models, migrations, database contents, stored attempts, target application files, routes, protocol DTOs, public JSON/result schemas, Core source, scenario behavior/catalogs/selectors, fixture recipes, secret resolution, worker configuration, state transitions, retry policy, or unrelated documentation. Preserve accepted previous records and all unrelated working-tree changes.

## 11. Configuration / Settings Impact

No configuration or settings changes. Read the existing `acceptance.reporting.default_page_size` and `max_page_size`; do not hardcode a second maximum. No new environment variable, channel, Admin Setting, secret, queue setting, or target activation. Automated tests use isolated fixtures and fake external clients. Operator setup: none for this correction. No configuration follow-up required.

## 12. Multilingual / Translation Impact

Visible CLI and validation text changes: Yes, English only under the existing contract. Persian operator-guide changes: Yes. No translation-file, API-message, web/Admin UI, or RTL layout change. Review English wording and Persian guide examples during acceptance; do not introduce a translation framework.

## 13. UI / Admin UI Impact

CLI prompts, tables, explanations, completion summaries, and page navigation change. No web/Admin UI, permissions, dashboard, routes, or menus. Show the report before optional navigation; retain explicit flags. Historical unknowns must remain visible as unknown. No screenshots required.

## 14. Implementation Rules

Reuse existing DTOs and Laravel Prompts. Keep authoritative validation in services; prompt checks provide immediate feedback from the same constraints. Do not derive retry eligibility independently in the renderer. Explain observed cleanup blockers without claiming all other retry conditions are satisfied. Read-only navigation never mutates the inspected batch. Continue pages with the last returned item ID and the same batch/filter context; cancel/end navigation cleanly without cancelling the batch. Preserve supplied options, no-interaction behavior, and safe stdout/stderr separation.

## 15. Architecture Constraints

Decision 0014's typed service/client separation remains binding. Generic project code must not import concrete DK fixture classes. DK may call the shared safe diagnostic boundary with its existing TargetContext. Existing correlation and per-attempt lifecycle identities remain distinct. Preserve async dispatch and executor routing; unrelated Remediation 0007 is not a dependency or execution authorization. No new diagnostic service layer beyond the optional small helper.

Pass query correlation explicitly from the two existing handlers into query/report calls where capture needs it; optional internal parameters must retain existing callers. Do not introduce process-global mutable trace state that can leak between queue jobs or successive operations.

## 16. Data Model / Migration / Relationship Impact

Data / Migration Impact: No. No tables, columns, keys, indexes, relationships, backfill, snapshots, retention policy, or history rewrite. Source rollback has no database rollback. Logging adds prospective safe events only; earlier missing causes cannot be repaired by this pack. No data-model follow-up required.

## 17. Validation Rules

Prove corrected output and diagnostics through synthetic failures and actual renderer/command paths. Test missing-table reporting in an isolated in-memory test database, never the operator's database. Fake target-control interactions, including typed conflicts and cleanup failures. Assert trace continuity and non-disclosure, not only success/error status. No live Browser, target, migration/reset on operator databases, network probe, queue worker, or automatic recovery is authorized by this pack.

## 18. Error Handling / Logging / Traceability Impact

### 18.1 Impact Summary

Error handling, human error UX, exception capture, structured logging, traceability, safe-data handling, tests and documentation: Yes. Existing typed external cause preservation: Yes. Public API/JSON/error-code meaning, retry/permanent classification, callback handling, state/attempt transitions: No changes.

### 18.2 Existing Defect and Required Correction

Generic catch boundaries discard cause information and human projections omit available error/cleanup evidence. Capture safe cause metadata once before it is lost; show the evidence already available in typed results. Existing records/logs are unchanged. Do not claim that adding capture fixes the operational target failure itself.

### 18.3 Error Scenarios to Correct

| Trigger | Required behavior | Existing public code / classification |
|---|---|---|
| Page size outside configured bounds | Inline re-prompt; scripts remain deterministic; no report read with invalid size | `acceptance_report_query_invalid`; existing classification retained |
| Status/report query throws | Safe database or unexpected cause event, query trace, read-only next check | `acceptance_reporting_failed`; retryable/permanent unknown, admin action required |
| Unexpected batch boundary exception | Safe diagnostic context; no inferred database diagnosis solely from fallback name | `acceptance_execution_persistence_failed`; existing classification retained |
| Fixture provisioning fails | Preserve typed control cause when available; identify preparation stage | `fixture_setup_failed`; retryable true, permanent false, admin action required |
| Cleanup fails | Preserve separate cleanup cause; report direct-retry blocker | `cleanup_failed`; retryable true, permanent false, admin action required |
| Report of failed or queued execution | Separate query/dispatch success from test outcome | No error-code or exit-policy change |

All scenarios preserve state transitions and sensitive-data omission. HTTP endpoints/status mappings are not changed.

### 18.4 Error Code and Contract Correction

Reuse existing public codes unchanged. New diagnostic `stage`, `cause_category`, and typed `cause_code` log fields are defined by Decision 0026; they are not new public error codes or JSON fields. No migration/API contract update.

### 18.5 UI / Admin UI Error Correction

Display a concise explanation, available batch/item context, public code, correctly labeled trace IDs and one specific next action. Invalid page size identifies the field and actual bounds. Query failure suggests safe inspection rather than blind migration. Report failure shows that evidence is unavailable instead of claiming the batch does not exist. CLI remains English; no raw message/stack/SQL display, including verbose mode.

### 18.6 Exception and External Error Correction

At reviewed catch sites capture typed TargetControlException metadata before converting to fixture/cleanup results. QueryException capture may include SQLSTATE/driver code but no raw message, SQL or bindings. Other Throwable instances get safe class/category only. Preserve existing results, classifications, thrown public exceptions, and failure behavior if diagnostic logging itself is unavailable; logging must not mask the primary failure or initiate another operation.

### 18.7 Logging Correction

Add `tms.acceptance.diagnostic.failed` at error level, following Decision 0026's exact context allowlist. Keep existing `tms.acceptance.operation.failed`, completion and transition events. Distinguish primary fixture and cleanup events. Log a cause at the boundary that has it; avoid duplicate reporting of one failure at wrappers. Existing logs may show only wrapper errors and must not be described as containing recoverable root causes.

### 18.8 Traceability Correction

The operation entry creates/accepts its existing correlation; batch/item/attempt records retain existing identities; queued execution keeps the originating trace. Diagnostic events use available trace context or lifecycle identity for joining, never a fabricated batch/attempt ID. Query correlation and stored execution correlation must remain distinguishable. The guide gives exact status/report and PowerShell log-search examples using placeholders. The diagnostic path must still work via logs if operation persistence fails; status lookup is not the sole investigation path.

### 18.9 Sensitive Data Correction

No confirmed exposure is being repaired. Added diagnostics must omit credentials, tokens, secret handles, endpoint values, environment contents, request/response bodies, SQL/bindings, raw exception messages and stacks. Allowlist scalar metadata only; technical class names must not expose anonymous-class file paths. No historical log cleanup or credential rotation is requested. Test non-exposure with synthetic sentinel values.

### 18.10 Error-specific Validation Requirements

Assertions must prove cause/category and identity linkage for synthetic query/fixture/cleanup failures, unchanged public envelopes and classifications, field-specific correction, visible known retry blockers, accurate completion wording, and working continuation with no duplicates. No missing historical detail may be replaced by a guessed cause or a claim that execution entered when evidence is unavailable.

### 18.11 Compatibility and Historical Impact

Version-2 JSON field names/order, error codes, exit semantics, command options, defaults for scripts, database records, attempts and existing logs remain compatible. English interactive wording and pagination flow intentionally change. No reprocessing, live retry, migration, history repair, or automatic cleanup. Source-only rollback.

### 18.12 Required Follow-up Updates

Update the Persian CLI guide in this pack. A separate target investigation is required to diagnose old fixture failures if their underlying causes were not recorded; it is not a blocker to validating this UX/diagnostic correction with fakes and is not authorized target access.

## 19. Tests to Add

| Test behavior | Setup and main assertions | Negative/side-effect checks |
|---|---|---|
| Bounded page-size correction | Configured max 250 and a different test max; enter too-large value, then valid value; prompt explains actual bounds and accepts correction | Invalid entry never reaches report execution; script code stays stable |
| Useful failed-batch report | Synthetic 21 failed items with fixture/cleanup errors and executor entry false; assert grouped counts/stage/cleanup/blocker and truthful completion text | No claim tests ran or root cause is known; records unchanged |
| Default-first report navigation | Multiple pages plus a small single-page batch; follow cursor and stop | No upfront pagination question, duplicates, skipped rows, hidden remaining-page flag, implicit batch cancellation or script prompt |
| Persistence/query diagnosis | In-memory schema/query failure and synthetic unexpected exception; assert cause category, query trace and useful safe guidance | No database alteration outside the test fixture; no raw SQL/message/sentinel leakage |
| Fixture and cleanup cause retention | Fake typed control exceptions; verify separate diagnostic events with the same originating trace/lifecycle and preserved cause codes | Public fixture/cleanup flags unchanged; no network; no repeated execution |
| JSON and retry regression | Existing envelope tests and cleanup-blocked item retry | No new JSON fields or changed exit semantics; retry guard still rejects |
| Failure-path resilience | Logger failure plus primary failure; failed operation containing safe typed data | Primary failure remains authoritative; safe data remains available in human output |

Use the files in sections 8–9. Keep tests focused on observable behavior and sensitive-data absence; do not add schema-mirroring or implementation-text tests.

## 20. Tests to Run

After separate execution approval, run the focused tests first:

```powershell
php artisan test --filter='AcceptanceOperatorDiagnosticsTest|AcceptanceOperatorCommandsTest|AcceptanceOperationResultRendererTest|AcceptanceOperationServiceTest|AcceptanceQueryServiceTest|AcceptanceReportServiceTest|AcceptanceReportingOperationTest|AcceptanceOperatorJsonContractTest|TargetResourceCoordinatorTest|OperationsHealthFixtureTest|AcceptanceFailureReporterTest'
php artisan test --filter='AcceptanceAsyncBatchTest|AcceptanceExecutionIdempotencyTest|AcceptanceRecoveryServiceTest|AcceptanceOperationCommandContractTest'
git diff --check
```

Run `php -l` for each edited/created PHP file and `vendor/bin/pint --test` restricted to those PHP paths. Verify the test process uses the repository's isolated SQLite in-memory test configuration before running DB tests; do not read `.env`. Run broader tests only if these changes expose unresolved shared-contract risk. Record actual commands/results, not assumed pass status. No live validation required.

## 21. Acceptance Checklist

- [ ] All six Decision 0026 UX defects have behavioral proof.
- [ ] Safe causes survive the covered error boundaries with usable trace linkage.
- [ ] Human failures retain relevant result evidence and a concrete next action.
- [ ] Pagination and validation use existing bounds; scripts do not prompt.
- [ ] Retrieval/dispatch success is distinguished from execution outcome.
- [ ] Cleanup retry guards, state rules and public JSON contracts remain unchanged.
- [ ] No secrets/raw exceptions or unsafe values appear in diagnostic output/logs.
- [ ] Focused/regression checks pass; guide and indexes are current.
- [ ] Historical unknowns are not fabricated; no target or record repair occurred.
- [ ] Operator reviews implementation, then separately accepts and authorizes its commit.

## 22. Rollback / Safety Notes

Revert only this implementation's source/tests/guide changes. Retain prior state, history and logging evidence. Do not delete failed batches, reset a database, force cleanup, clear caches, or retry items as a UX fix. Unrelated changes and previously accepted records remain outside the commit.

## 23. Operator Review Checklist

Review a bounded invalid-input prompt, a failed-fixture report, an async queued summary, multi-page navigation, a failed query with no persisted operation, and safe correlated diagnostic events. Confirm the report distinguishes classification from actual retry permission and does not require source inspection for known causes. Execution, acceptance and commit gates remain separate; this pack is ready, not executed.

## 24. Operator Documentation Impact

Operator Documentation Impact: Yes. Update `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md` and its index when implemented. Cover default-first report flow and configured limits; primary/cleanup and executor-entry interpretation; query versus execution success; operation/correlation/lifecycle distinctions; Windows log searches and explicit investigation commands; missing-history limitations and cleanup-blocked retry. Screenshots: not required. No guide wording should claim this prospective behavior is available before implementation.

## 25. Agent Final Report

After actual execution report files changed, before/after operator flow, exact validation evidence, safe diagnostic contract, remaining limits, documentation/index synchronization and Git scope. Create the named Run only under the reporting rules before an implementation commit. This planning commit creates no execution Run and does not close Change 0020.
