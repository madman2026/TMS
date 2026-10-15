# CHANGE-REQUEST-TMS-ACCEPTANCE-BATCH-FAILURE-UX-0019

## 1. Title

Make Acceptance Batch prerequisite prompting, lifecycle identity, and aggregate failures actionable.

## 2. Status

Implemented and validated; operator accepted the result and authorized the scoped commit on 2026-10-15.

## 3. Related Release / Phase

Project Acceptance CLI/batch runtime; DK Pack 0004 live execution follow-up.

## 4. Decision Source

Observed interactive Batch 3 failure; accepted automatic prerequisite Decision 0025; operator UX instruction.

## 5. Proposed Change

Do not prompt for an optional prerequisite JSON file when it was not explicitly supplied. Automatic/manual domain gating remains authoritative. Give each Batch execution attempt its own lifecycle UUID while retaining the operation UUID for operation/trace correlation. When all failed items share one stable error code, promote that code and its classification to the operation result; retain `acceptance_command_failed` for mixed/unknown failures. Classify `unsafe_target` as permanent and admin-action-required.

## 6. Reason for Change

The CLI asked for obsolete plumbing on the automatic path and then hid 21 identical `unsafe_target` failures behind a generic aggregate code. After activation, live Batch 4 proved that reusing the shared operation UUID as every item's lifecycle UUID causes target lease conflicts across different variants/recipes.

## 7. Scope of Impact

Acceptance command parsing, per-attempt execution lifecycle identity, Batch finalization/classification, focused tests, Persian CLI guide, and project lifecycle records.

## 8. Affected Files

- `app/Console/Acceptance/AcceptanceCommand.php`
- `app/Acceptance/Execution/AcceptanceExecutionService.php`
- `app/Acceptance/Execution/AcceptanceBatchService.php`
- `tests/Feature/AcceptanceOperatorCommandsTest.php`
- `tests/Feature/AcceptanceAsyncBatchTest.php`
- `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`
- related project Change/Remediation indexes and records

## 9. Backward Compatibility

Yes. `--prerequisites=<file>` remains supported. Mixed failures retain the generic aggregate code.

## 10. Risk Level

Low.

## 11. Migration or Remediation Needed

Yes: `REMEDIATION-PACK-TMS-ACCEPTANCE-BATCH-FAILURE-UX-0013`.

## 12. Required Human Approval

Yes — granted 2026-10-15. Acceptance and commit remain separate.

## 13. Acceptance Criteria

No unsolicited prerequisite-file prompt; explicit file still parses; each Batch attempt has a distinct lifecycle UUID while direct runs retain operation-owned lifecycle identity; homogeneous failures surface their stable code/classification; mixed failures remain generic; `unsafe_target` requests admin action; regression/full tests and live proof pass.

## 14. Rollback Plan

Revert only the listed project source/test/guide changes; no data rewrite.

## 15. Final Decision

Approved.

## 16. Required remediation

Project Remediation 0013.
