# TMS-DECISION-0026 — Actionable Acceptance errors, reports, and traceability

Status: accepted — operator approved recording the correction on 2026-10-16; implementation readiness: ready
Scope: Project / shared Acceptance CLI and diagnostics, with a bounded DK fixture integration
Source: Operator-requested UX review and explicit instruction to record Decision, Change, and Remediation as ready and commit the planning records
Date: 2026-10-16
Review At: Remediation 0014 implementation review
Blocking: Yes for accepting the corrected operator workflow
Closure Condition: Remediation 0014 passes its acceptance checklist and receives operator acceptance
Next Review At: Before Remediation 0014 execution and after its automated validation

## Type

6. Change Request / Remediation Trigger; project-level operator workflow and diagnostic contract decision.

## Decision

The operator must be able to distinguish command completion, execution outcome, preparation failure, and cleanup failure without reading application source or manually querying the database.

1. Preserve safe diagnostic causes at the boundary where an exception would otherwise be discarded. Keep the existing public error codes and classifications; diagnostic cause and stage supplement them in structured logs, not in the version-2 JSON envelope.
2. Human reports show batch outcome, total/passed/failed/pending/running counts, grouped failure codes, cleanup failures, and whether execution was entered when attempt evidence is available. An unknown historical cause remains explicitly unknown. A failed command must not suppress useful safe result data already returned by its handler.
3. Distinguish successful retrieval from successful test execution. Say that the report was loaded, the batch was queued, or the run failed, as appropriate. Successful status/report queries retain their successful exit status even when the inspected batch failed.
4. Prompt validation gives immediate, field-specific feedback using the same configured bounds as the authoritative service. An out-of-range page size is corrected in the prompt; a script gets the existing deterministic error contract.
5. Show the first report page immediately with existing defaults. Ask about another page only when one exists; retain explicit `--limit` and `--after-item`. Show the page boundary and a copyable continuation command. JSON/non-interactive execution never prompts or fetches extra pages implicitly.
6. Every covered human error includes the failed stage when known, an honest explanation, existing error code, relevant trace identifiers, and one concrete next action. If no operation was persisted, do not promise that `acceptance:status` can recover it. For database/schema failures, suggest read-only checks before any operator-controlled migration or configuration change.
7. `retryable` remains a failure classification, not a promise of immediate retry. Explain a known cleanup blocker. The existing retry service remains authoritative; the renderer must not implement a second eligibility algorithm or automatically retry, clean up, migrate, or repair records.

### Diagnostic contract

- Add one bounded event, `tms.acceptance.diagnostic.failed`, at error level when a covered boundary loses a diagnostic cause. Keep existing operation/state events intact. Avoid logging the same exception repeatedly at each layer.
- Allowed context: existing public `error_code`; `stage` from `operation`, `status_query`, `status_counts`, `status_projection`, `report_query`, `report_projection`, `fixture`, `cleanup`, or `batch_attempt`; `cause_category` from `database`, `target_control`, `validation`, or `unexpected`; safe exception class; available correlation, operation, batch, item, attempt, and lifecycle identifiers.
- For typed target-control errors, also preserve the existing protocol-owned code as `cause_code` and bounded `http_status`. Their underlying retry classification does not replace the public fixture/cleanup classification.
- For database errors, SQLSTATE and a bounded driver error code may be logged. A missing-table explanation requires verified schema evidence or a reliable driver-specific classification; do not infer it from a broad SQLSTATE alone. Never log SQL text, bindings, connection strings, raw driver messages, or environment values.
- Unknown exceptions use category `unexpected`; do not invent a root cause. Preserve useful internal exception chaining where safe, but never serialize raw Throwable objects into logs or output.
- The query command's correlation identifies that query; stored batch correlation identifies the earlier execution. Label both when relevant. Preserve per-attempt lifecycle identity and never substitute a batch operation UUID for it.
- No new external error codes, database fields, routes, diagnostic command, logging channel, dependency, or configuration surface are approved. Reuse current identifiers and logger. One small shared diagnostic helper is permitted by the Remediation's exact file list.

## Reason

The read-only review on 2026-10-16 found six operator-visible defects: cause loss behind generic errors; human reports omitting available failure/cleanup details; late rejection of page size 10000 despite a configured maximum of 250; generic success wording for failed executions being inspected; trace IDs without actionable navigation; and upfront pagination questions with no human continuation information.

Observed local evidence included an absent-table status failure and two later async batches with 21 fixture and cleanup failures each, with executor entry false for all attempts. These observations do not establish the root cause of the target failures. No target-side defect or lease conflict is asserted as fact. Runtime IDs and raw log contents are not needed in this decision.

## Impact

| Area | Decision |
|---|---|
| Error handling / exception handling / logging / traceability | Yes; bounded safe causes and actionable presentation |
| Stable public error codes / API contracts | No additions or meaning changes; preserve version-2 JSON and exit behavior |
| External error normalization | Preserve existing typed target-control causes before generic fixture/cleanup wrapping |
| Retry classification / state model / attempts | No policy or transition changes; explain existing gates |
| CLI text / validation / report navigation | Yes; English CLI, Persian operator guide |
| Web/Admin UI / permissions | No |
| Config / secrets / queue or logging settings | No changes |
| Database / migrations / snapshots / historical data | No changes or backfill; old missing causes stay unknown |
| Sensitive data | Omit raw messages, SQL, payloads, credentials, handles and tokens; negative tests required |
| Testing / operator documentation | Yes; owned by Remediation 0014 |

Ownership remains project-level for shared presentation/diagnostics. The exact DK fixture catch-boundary change and its unit test are explicitly included as a bounded cross-owner exception in this project correction; they do not authorize target application, scenario, selector, fixture recipe, or transport behavior changes. Read the DK router/profile before implementation. Core source is outside scope.

## Files to Update

The exact source, test, guide, and lifecycle allowlist is owned by `docs/project/ai/remediations/REMEDIATION-PACK-TMS-OPERATOR-DIAGNOSTICS-0014.md`. Update the three project lifecycle indexes when recording this decision chain.

## Required change request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-OPERATOR-DIAGNOSTICS-0020.md`.

## future-work

Execute Remediation 0014 only after its separate execution instruction. Preserve Decisions 0014/0015, the accepted Remediation 0013 behavior, target safety gates, and the unrelated ND delivery sequence. A dashboard, broad error framework, schema-backed diagnostic store, and historical batch repair are outside this correction.

## Review / Resolution

Reviewed In: Operator-facing source review in this task, 2026-10-16
Review Result: Current failure/recovery UX needs correction; existing typed service and safety boundaries remain useful
Resolution: accepted; requires-remediation
Next Review At: Remediation 0014 execution/acceptance
Resolution Notes: The operator authorized this planning record and its commit, not runtime implementation or a claim that failed batches are repaired.

## Notes

Lifecycle mapping of the requested ready state: Decision accepted, Change approved, executable Remediation ready. No executed or accepted Remediation Run exists yet. No Canonical record needs amendment: this decision applies the shared error rules and retains the accepted client/service architecture. The Persian CLI guide is updated during implementation, so it does not describe planned behavior as already available.
