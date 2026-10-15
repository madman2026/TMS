# REMEDIATION-PACK-TMS-ACCEPTANCE-BATCH-FAILURE-UX-0013

Status: `accepted — automated and exact 21-variant live validation passed; operator accepted the result and authorized the scoped commit on 2026-10-15`

Owner: TMS project

## 1. Task ID

`REMEDIATION-PACK-TMS-ACCEPTANCE-BATCH-FAILURE-UX-0013`

## 2. Task Title

Remove obsolete optional-input prompting, isolate Batch attempt lifecycles, and preserve homogeneous failure meaning.

## 3. Related Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-ACCEPTANCE-BATCH-FAILURE-UX-0019.md`

## 4. Goal

Keep the automatic Batch path short and return the error an operator can act on.

## 5. Current Problem

Interactive start asks whether to load prerequisite references even when the selected schema is automatic. After execution, Batch 3 replaced 21 identical `unsafe_target` item failures with `acceptance_command_failed`, and `unsafe_target` lacked the correct admin-action classification. Live Batch 4 then reused one operation UUID as all 21 lifecycle IDs, producing cross-item target lease conflicts after the first acquired lease.

## 6. Required Change

Parse prerequisite references only when `--prerequisites` is supplied. Let `AcceptanceExecutionService` accept an optional lifecycle UUID; direct runs default to their operation UUID, while Batch execution supplies the per-attempt execution token. During terminal finalization, promote one distinct non-empty failed-item code to the operation and derive its classification; use the generic code only for mixed/missing codes. Correct `unsafe_target` classification.

## 7. Files to Read

Decision 0025, Remediation 0012, current CLI/batch renderer/service/tests, error rules, CLI guide, and DK Decision 0006/Remediation 0003.

## 8. Files to Edit

- `app/Console/Acceptance/AcceptanceCommand.php`
- `app/Acceptance/Execution/AcceptanceExecutionService.php`
- `app/Acceptance/Execution/AcceptanceBatchService.php`
- `tests/Feature/AcceptanceOperatorCommandsTest.php`
- `tests/Feature/AcceptanceAsyncBatchTest.php`
- `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`
- this Remediation and project lifecycle indexes for status/evidence

## 9. Files to Create

This Change/Remediation and an eventual Run Report after validation.

## 10. Do Not Change

Prerequisite persistence/schema, automatic resolver policy, target adapters/source, catalog identities, executor/browser contracts, database schema, dependencies, `.env`, Pack 0005, or unrelated formatting/refactoring.

## 11. Configuration / Settings Impact

None in project source. DK activation is separately owner-approved.

## 12. Multilingual / Translation Impact

Persian guide only; command source remains ASCII/English.

## 13. UI / Admin UI Impact

CLI interactive prompts and failure output only.

## 14. Implementation Rules

Preserve explicit file support and manual/approval blocking. Aggregate from terminal failed items only. Promote only exactly one distinct non-empty code. Derive all three flags from the central classification method. Do not leak item payloads or exceptions.

## 15. Architecture Constraints

CLI gathers optional transport input; prerequisite domain policy decides whether input is required. Execution service owns target lifecycle construction; operation and trace identifiers stay unchanged. Batch service owns attempt identity plus aggregate state/error meaning. Renderer remains unchanged.

## 16. Data Model / Migration / Relationship Impact

None.

## 17. Validation Rules

Assert interactive automatic start succeeds without the removed prompt; explicit JSON remains accepted; direct execution keeps operation-owned lifecycle identity; Batch variants receive distinct attempt-owned lifecycle IDs; homogeneous resource/unsafe errors are promoted with flags; mixed errors stay generic; existing state/events and sync/async paths regress cleanly.

## 18. Error Handling / Logging / Traceability Impact

`unsafe_target` is reused and corrected to retryable=false, permanent=true, admin-action-required=true. A homogeneous stable item code now becomes the operation error. Mixed/missing item codes remain `acceptance_command_failed` with retryable/permanent unknown (`null`) and admin-action-required=true, matching the central operation contract. No log shape or identifier changes.

## 19. Tests to Add

Interactive no-prompt regression, distinct Batch lifecycle identity, homogeneous aggregate promotion/classification, mixed aggregate fallback, and existing sync-path assertions.

## 20. Tests to Run

Focused operator-command/async-batch/DK target tests; affected Pack 0004 tests; full PHPUnit; syntax; scoped Pint; safe scans; `git diff --check`; live selector Batch/report.

## 21. Acceptance Checklist

- [x] no automatic-path prerequisite prompt;
- [x] explicit prerequisite file remains supported;
- [x] every Batch attempt owns a distinct lifecycle UUID;
- [x] homogeneous failures are actionable;
- [x] mixed failures remain generic;
- [x] `unsafe_target` classification is correct;
- [x] automated/live validation passes;
- [x] no commit without separate authorization.

## 22. Rollback / Safety Notes

Source-only rollback; do not rewrite prior Batch 3 history. New behavior applies to future finalizations.

## 23. Operator Review Checklist

Confirm short interactive path, explicit manual path preservation, exact error output, safe correlation guidance, and live result.

## 24. Operator Documentation Impact

Update `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md` with the no-prompt behavior, DK process-local activation, cleanup, and homogeneous error guidance.

## 25. Agent Final Report

Report before/after UX, exact classification, test/live counts, cleanup, Git state, and remaining acceptance/commit gates.

Execution evidence is recorded in `docs/project/ai/runs/REMEDIATION-PACK-TMS-ACCEPTANCE-BATCH-FAILURE-UX-0013-run-001.md`.
