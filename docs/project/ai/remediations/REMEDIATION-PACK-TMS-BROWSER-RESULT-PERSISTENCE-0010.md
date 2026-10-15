# REMEDIATION-PACK-TMS-BROWSER-RESULT-PERSISTENCE-0010

Status: `accepted — validated and operator-accepted with scoped commit authorization on 2026-10-15`

Generated: `2026-10-14`

Owner: TMS project

## 1. Task ID

`REMEDIATION-PACK-TMS-BROWSER-RESULT-PERSISTENCE-0010`

## 2. Task Title

Correct failed Browser executor result persistence.

## 3. Related Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-BROWSER-RESULT-PERSISTENCE-0016.md`

## 4. Goal

Persist and report the real failed Browser Step without a secondary persistence exception.

## 5. Current Problem

The failed-Step logging path assumes every normalized result has an `identity`. `BrowserExecutorData` does not, so a failed Browser Step rolls back and is replaced by `acceptance_result_persistence_failed`.

## 6. Required Change

Use the canonical persisted Test identity for the safe structured log context and cover the Browser executor failure branch.

## 7. Files to Read

Startup/governance files, result persistence source/tests, executor DTO contracts, Change 0016, and current DK Pack 0004.

## 8. Files to Edit

- `app/Services/AcceptanceRunService.php`
- `tests/Feature/AcceptanceRunPersistenceTest.php`
- this record and project Change/Remediation/Run indexes
- DK Pack 0004 lifecycle note only

## 9. Files to Create

- Change Request 0016
- `docs/project/ai/runs/REMEDIATION-PACK-TMS-BROWSER-RESULT-PERSISTENCE-0010-run-001.md` after validation

## 10. Do Not Change

Do not add identity to executor DTOs, change stable codes/log fields, store raw errors, change schema, target source, scenarios, configuration, dependencies, API/UI, or evidence retention.

## 11. Configuration / Settings Impact

No configuration or settings impact.

## 12. Multilingual / Translation Impact

No multilingual or translation impact.

## 13. UI / Admin UI Impact

No UI or Admin UI impact.

## 14. Implementation Rules

Use only the Test's five validated identity columns. Preserve transactional Step/Test persistence, safe fixed error text, and null Browser exception class.

## 15. Architecture Constraints

The Test remains the persistence aggregate and canonical persisted identity. Executor DTOs stay transport-neutral and unchanged.

## 16. Data Model / Migration / Relationship Impact

No schema, model relationship, index, backfill, stored-history, or migration impact.

## 17. Validation Rules

Prove the Browser executor failed-Step branch persists the Step/Test and logs all five safe identity fields, then rerun the real canary to expose the actual Browser outcome.

## 18. Error Handling / Logging / Traceability Impact

Corrects masking only: `browser_execution_failed` and the Step error remain observable instead of becoming `acceptance_result_persistence_failed`. Event name and context allowlist are unchanged; sensitive values remain omitted.

## 19. Tests to Add

One feature regression using a failed `BrowserExecutorData` with one safe failed Step.

## 20. Tests to Run

Focused result-persistence tests, relevant Acceptance integration tests, full TMS suite, scoped Pint, `git diff --check`, and the operator-approved live canary.

## 21. Acceptance Checklist

- [x] canonical Test identity used;
- [x] failed Browser Step persists safely;
- [x] focused and full validation pass;
- [x] live canary exposed the underlying Browser mismatch and the final matrix passed;
- [x] Run/index records synchronized;
- [x] operator accepted the result and authorized the scoped commit on 2026-10-15.

## 22. Rollback / Safety Notes

Source-only rollback. Failed canaries must retain bounded target cleanup; do not delete unrelated Test history.

## 23. Operator Review Checklist

Review identity source, unchanged safe log shape, regression result, real canary result, and absence of sensitive output.

## 24. Operator Documentation Impact

No operator-facing workflow changed; no guide or screenshot update required.

## 25. Agent Final Report

Report the masking defect, source correction, test and live evidence, changed files, and remaining gates.
