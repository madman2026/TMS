# CHANGE-REQUEST-DK-PROCESS-LOCAL-TARGET-ACTIVATION-0006

## 1. Title

Add one fail-closed process-local activation profile for DK Acceptance.

## 2. Status

Implemented and validated — exact 21-variant live Batch and cleanup passed; operator accepted the result and authorized the scoped commit on 2026-10-15.

## 3. Related Release / Phase

DK stage 14 / Pack 0004 live Operations Health execution.

## 4. Decision Source

Accepted `DK-DECISION-0006`; accepted local-loopback Decision 0005; observed Batch 3 `unsafe_target` result.

## 5. Proposed Change

Read `DK_ACCEPTANCE_TARGET_PROFILE` only in `Modules/DK/config/config.php`. Exact value `local-testing` selects enabled/testing; every other value selects disabled/unknown. Document process-local PowerShell usage and cleanup.

## 6. Reason for Change

The current hard-coded disabled/unknown configuration prevents the already accepted local synthetic execution and forces operators toward source/config mutation. The safe path should be one explicit, reversible profile selector.

## 7. Scope of Impact

DK config, the root environment manifest, focused integration tests, owner lifecycle records, and the shared Persian CLI guide. No target source, schema, dependency, `.env`, Admin Setting, URL, or secret change.

## 8. Affected Files

- `Modules/DK/config/config.php`
- `.env.example` — empty declaration only; no activation value
- `Modules/DK/tests/Feature/DKTargetFoundationIntegrationTest.php`
- `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`
- related DK Decision/Change/Remediation indexes and records

## 9. Backward Compatibility

Yes. With no selector, behavior remains disabled/unknown. Invalid values also fail closed.

## 10. Risk Level

Medium: it permits real local target I/O, bounded to the accepted loopback synthetic profile.

## 11. Migration or Remediation Needed

Yes: `REMEDIATION-PACK-DK-PROCESS-LOCAL-TARGET-ACTIVATION-0003`.

## 12. Required Human Approval

Yes — granted for creation and execution on 2026-10-15. Acceptance and commit remain separate gates.

## 13. Acceptance Criteria

Default/invalid fail closed; exact selector activates testing only; no `.env` mutation; focused/full validation passes; exact 21-variant live Batch passes with cleanup evidence.

## 14. Rollback Plan

Revert the two DK source/test edits and guide text. Do not delete historical Batch or prerequisite audit rows.

## 15. Final Decision

Approved.

## 16. Required remediation

DK Remediation 0003 and project Remediation 0013.
