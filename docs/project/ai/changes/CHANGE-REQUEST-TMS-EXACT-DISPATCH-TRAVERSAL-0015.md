# CHANGE-REQUEST-TMS-EXACT-DISPATCH-TRAVERSAL-0015

## 1. Title

Keep exact Acceptance dispatch bounded to the selected scenario.

## 2. Status

Implemented and validated; exact plan and all 21 live variants passed; operator accepted the result and authorized the scoped commit on 2026-10-15.

## 3. Related Release / Phase

Accepted catalog/dispatcher Pack 0007; discovered during DK Pack 0004 live Browser execution.

## 4. Decision Source

Pack 0007's accepted bounded exact-selection contract.

## 5. Proposed Change

Add a Planner path for one complete execution identity and route the dispatcher through it. Validate the selected App/component/suite/scenario/variant and catalog version while traversing variants only for the selected scenario.

## 6. Reason for Change

The generic selector intentionally scans all scenarios to validate cross-scenario variant vocabulary. Reusing it for exact execution scans DK's unrelated 24,668 variants and rejects a valid identity at the 10,000-visit guard.

## 7. Scope of Impact

Project Planner/dispatcher and focused unit tests only. General list/plan semantics and visit/item limits remain unchanged.

## 8. Affected Files

`AcceptancePlanner`, `AcceptanceVariantDispatcher`, their focused dispatcher test, this Change Request, Remediation 0009, indexes, and later Run evidence.

## 9. Backward Compatibility

Yes. Exact dispatch retains the same identity, eligibility, version-drift, mismatch, and safe-error outcomes while no longer visiting unrelated variant sets.

## 10. Risk Level

Low.

## 11. Migration or Remediation Needed

Yes: project Remediation 0009. No database migration.

## 12. Required Human Approval

Yes — received for current execution on 2026-10-14. Acceptance and commit remain separate.

## 13. Acceptance Criteria

Exact dispatch resolves the DK identity under the existing visit ceiling, never calls unrelated variant generators, preserves all existing dispatcher tests, and leaves generic planning limits unchanged.

## 14. Rollback Plan

Revert the exact Planner method, dispatcher call, and focused test.

## 15. Final Decision

Implementation and validation complete; result awaits operator acceptance and commit authorization.

## 16. Required remediation

`REMEDIATION-PACK-TMS-EXACT-DISPATCH-TRAVERSAL-0009`.

## 17. Notes

Executed during the current DK Pack 0004 validation window as a small project-owned blocker correction, without widening DK-owned source scope.
