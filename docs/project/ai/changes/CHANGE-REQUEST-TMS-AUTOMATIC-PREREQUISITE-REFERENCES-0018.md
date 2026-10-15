# CHANGE-REQUEST-TMS-AUTOMATIC-PREREQUISITE-REFERENCES-0018

## 1. Title

Remove manual prerequisite plumbing for app-resolvable Acceptance references.

## 2. Status

Implemented and validated; operator accepted the result and authorized the scoped commit on 2026-10-15.

## 3. Related Release / Phase

Current Acceptance prerequisite UX and DK Pack 0004 follow-up.

## 4. Decision Source

`TMS-DECISION-0025` and the operator instruction to optimize the workflow around user experience.

## 5. Proposed Change

Add an opt-in schema fulfillment policy, a narrow app-owned reference resolver port and registry, and execution-owned automatic request creation. Make both exact runs and selector-based batches persist and consume safe references without requiring manual Variant-key discovery, prerequisite preparation, request-ID copying, or reference files.

## 6. Reason for Change

The current safe prerequisite model works, but its command sequence leaks internal orchestration identifiers into the normal operator workflow. Catalog planning already knows the exact variants; requiring the operator to repeat those identities and then carry request IDs forward is unnecessary for app-owned static handles.

## 7. Scope of Impact

Project prerequisite schema/service, DI composition, safe result/error classification, focused run/batch tests, and the Persian Acceptance CLI guide. DK supplies a separately owned resolver binding.

## 8. Affected Files

Only files listed by project Remediation 0012 and DK Remediation 0002, plus their lifecycle records and indexes.

## 9. Backward Compatibility

Manual schemas and approvals retain their current behavior. Explicit request IDs remain valid. The new schema policy defaults to manual. Existing DK v1 requests fail closed after the deliberate v2 schema change.

## 10. Risk Level

Medium: the change creates persisted prerequisite state during execution and crosses a project/App ownership boundary, but stores only already accepted opaque references.

## 11. Migration or Remediation Needed

Yes: project Remediation 0012 and DK Remediation 0002. No database migration or data backfill.

## 12. Required Human Approval

Architecture direction and Remediation execution were approved. Operator acceptance and commit remain separately gated.

## 13. Acceptance Criteria

Direct single-run and selector batch paths work without prerequisite arguments for DK Operations Health; each execution item receives a distinct persisted READY request; explicit/manual/approval paths remain unchanged; resolver failures are safe and actionable; no secret literal or resolved credential is exposed.

## 14. Rollback Plan

Revert only the new policy/port/registry/service integration, DK binding/schema update, tests, guide, and lifecycle records. No data rollback is required; auto-created requests are bounded audit records.

## 15. Final Decision

Implemented through the separately approved Project Remediation 0012 and DK Remediation 0002; automated and live validation passed, and the operator accepted the result and authorized scoped commits on 2026-10-15.

## 16. Required Remediation

`docs/project/ai/remediations/REMEDIATION-PACK-TMS-AUTOMATIC-PREREQUISITE-REFERENCES-0012.md` and `Modules/DK/docs/ai/remediations/REMEDIATION-PACK-DK-AUTOMATIC-OPERATIONS-HEALTH-PREREQUISITE-0002.md`.
