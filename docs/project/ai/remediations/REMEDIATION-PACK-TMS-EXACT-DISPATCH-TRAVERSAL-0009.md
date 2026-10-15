# REMEDIATION-PACK-TMS-EXACT-DISPATCH-TRAVERSAL-0009

Status: `accepted — validated and operator-accepted with scoped commit authorization on 2026-10-15`

Generated: `2026-10-14`

Owner: TMS project

## 1. Task ID

`REMEDIATION-PACK-TMS-EXACT-DISPATCH-TRAVERSAL-0009`

## 2. Task Title

Bound exact Acceptance dispatch to the selected scenario.

## 3. Related Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-EXACT-DISPATCH-TRAVERSAL-0015.md`

## 4. Goal

Restore the accepted bounded exact-dispatch behavior for large target catalogs.

## 5. Current Problem

`AcceptanceVariantDispatcher` uses generic planning, which scans unrelated scenario variants when a variant filter is present. DK's valid exact operation-health identity is rejected with `acceptance_catalog_limit_exceeded` before execution.

## 6. Required Change

Introduce exact-identity planning that validates the complete selected hierarchy, consumes only the selected scenario's variants, preserves catalog-version checks, then keep the existing dispatcher eligibility and runtime-resolution checks.

## 7. Files to Read

Startup/governance files, Pack 0007/Run 001, Change 0015, Planner/dispatcher, and focused tests.

## 8. Files to Edit

- `app/Services/AcceptancePlanner.php`
- `app/Services/AcceptanceVariantDispatcher.php`
- `tests/Unit/AcceptanceVariantDispatcherTest.php`
- this record and project Change/Remediation/Run indexes
- DK Pack 0004 lifecycle note only

## 9. Files to Create

- Change Request 0015
- `docs/project/ai/runs/REMEDIATION-PACK-TMS-EXACT-DISPATCH-TRAVERSAL-0009-run-001.md` after validation

## 10. Do Not Change

Do not change generic selector semantics, public limits, error codes, target modules, scenario behavior, database/configuration, dependencies, API/UI, browser adapters, or sensitive-data rules.

## 11. Configuration / Settings Impact

No configuration or settings impact.

## 12. Multilingual / Translation Impact

No multilingual or translation impact.

## 13. UI / Admin UI Impact

No UI or Admin UI impact.

## 14. Implementation Rules

Validate every exact hierarchy dimension and consume complete selected-dimension generators so duplicate and malformed catalog checks remain active. Do not resolve runtime scenario code until the exact plan is valid and automated.

## 15. Architecture Constraints

The Planner remains the catalog-validation owner; the dispatcher remains the sole targeted runtime materializer. Target-owner code is unchanged.

## 16. Data Model / Migration / Relationship Impact

No data model, migration, relationship, index, backfill, or stored-history impact.

## 17. Validation Rules

Prove an unrelated variant generator is not called, all existing Planner/dispatcher tests pass, the real DK exact canary proceeds beyond selection, and generic 10,000-visit/1,000-row limits remain intact.

## 18. Error Handling / Logging / Traceability Impact

No error code or logging contract changes. A valid exact identity no longer receives the unrelated generic traversal error; genuinely missing, ineligible, unstable, malformed, or oversized selected identities keep existing safe codes.

## 19. Tests to Add

One focused dispatcher regression with an unrelated scenario whose variant generator fails if traversed.

## 20. Tests to Run

Focused Planner/dispatcher units, DK operations-health feature coverage, full TMS suite, Pint on changed PHP, `git diff --check`, and the operator-approved live canary.

## 21. Acceptance Checklist

- [x] exact path implemented;
- [x] unrelated variants not traversed;
- [x] existing limits and tests preserved;
- [x] live DK canary and remaining variants complete;
- [x] Run/index records synchronized;
- [x] operator accepted the result and authorized the scoped commit on 2026-10-15.

## 22. Rollback / Safety Notes

Source-only rollback. No target or database cleanup beyond the enclosing Pack 0004 run lifecycle.

## 23. Operator Review Checklist

Review exact hierarchy validation, selected-scenario-only traversal, version checks, test evidence, and unchanged public planner behavior.

## 24. Operator Documentation Impact

No visible operator workflow changed; no guide or screenshot update required.

## 25. Agent Final Report

Report defect evidence, exact traversal change, tests, live-run outcome, files, deviations, and remaining gates.
