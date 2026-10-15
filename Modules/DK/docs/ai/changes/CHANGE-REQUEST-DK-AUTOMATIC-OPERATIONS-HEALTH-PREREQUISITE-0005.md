# CHANGE-REQUEST-DK-AUTOMATIC-OPERATIONS-HEALTH-PREREQUISITE-0005

## 1. Title

Bind DK Operations Health to automatic opaque-reference resolution.

## 2. Status

Implemented and validated; operator accepted the result and authorized the scoped commit on 2026-10-15.

## 3. Related Release / Phase

DK stage 14 / Pack 0004 UX follow-up.

## 4. Decision Source

Project Decision 0025 and operator approval of the UX-centered workflow.

## 5. Proposed Change

Mark the existing `credential` prerequisite as `app_reference`, bump its schema identity to `operations-health-v2`, implement an exact DK resolver returning the existing local opaque handle only for the accepted Operations Health tuple and 21-key catalog, and register it through the project resolver registry.

## 6. Reason for Change

DK already owns a constant safe handle and its target-boundary resolver. Requiring operators to copy that same reference through 21 manually prepared requests adds no safety and obscures the Catalog-driven batch UX.

## 7. Scope of Impact

DK prerequisite metadata, one owner-local resolver adapter, provider registration, focused tests, and lifecycle records. No target product/source/configuration change.

## 8. Affected Files

Only files listed in DK Remediation 0002.

## 9. Backward Compatibility

Exact identities and runtime target behavior remain unchanged. Existing v1 requests fail closed with `schema_changed`. Explicit v2 requests remain supported.

## 10. Risk Level

Low to medium; mapping is exact and value-free but participates in automatic persisted execution setup.

## 11. Migration or Remediation Needed

Yes: DK Remediation 0002. No migration/backfill.

## 12. Required Human Approval

Architecture direction and DK Remediation execution were approved. Operator acceptance and commit remain separately gated.

## 13. Acceptance Criteria

Only the exact 21 Operations Health variants resolve `credential`; every unrelated identity returns no match; returned value is the existing opaque DK handle; provider registration is duplicate-safe; automatic run/batch tests pass without manual prerequisite arguments.

## 14. Rollback Plan

Revert the v2 metadata, resolver, provider registration, tests, and lifecycle records. No target/data rollback.

## 15. Final Decision

Implemented through DK Remediation 0002; automated and live validation passed, and the operator accepted the result and authorized the scoped commit on 2026-10-15.

## 16. Required Remediation

`Modules/DK/docs/ai/remediations/REMEDIATION-PACK-DK-AUTOMATIC-OPERATIONS-HEALTH-PREREQUISITE-0002.md`.
