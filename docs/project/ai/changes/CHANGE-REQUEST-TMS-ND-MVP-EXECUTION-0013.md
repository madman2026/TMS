# CHANGE-REQUEST-TMS-ND-MVP-EXECUTION-0013 — Complete executor-neutral dispatch and bounded ND corpus execution

## 1. Title

Complete the project-owned execution handoff and bounded segmentation needed by the local synthetic ND MVP.

## 2. Status

Approved planning change — implementation remains split between blocked draft Remediation 0007 and blocked draft Pack 0024 with separate execution gates.

## 3. Related Release / Phase

Decision-0015 stages 14–15, refined by Decision 0024.

## 4. Decision Source

`docs/project/ai/decisions/TMS-DECISION-0024-nd-mvp-sequence-and-owner-boundaries.md`.

## 5. Proposed Change

1. Correct the current project application handoff so scenario capability selects the already accepted Core executor contract instead of persisting and constructing Browser unconditionally.
2. Add deterministic bounded corpus segmentation that keeps each plan/batch within existing safety ceilings while allowing the complete 24,668-case ND corpus to be scheduled and reconciled without manual identity loss or duplication.
3. Keep orchestration and segmentation project-owned, executor implementation Core-owned, scenario behavior DK-owned and target fixtures/drivers N-D-owned.

## 6. Reason for Change

Decision 0011 already requires capability routing, but current `AcceptanceBatchService`, `AcceptanceExecutionService` and `AcceptanceRunService` follow a Browser-specific path. The selector also rejects more than 1,000 matched items and the planner has a 10,000-visit ceiling. Those limits are valid safety controls, but the ND MVP needs a first-class bounded workflow over all 24,668 identities.

## 7. Scope of Impact

Affected areas:

- project Acceptance planning, batching and execution handoff;
- safe persistence/reporting of executor and segment identity;
- project tests and the Persian Acceptance CLI guide;
- no N-D product behavior, Core target knowledge, real target access, Provider I/O, UI or production behavior.

## 8. Affected Files

Exact implementation files are deferred to normalization of Remediation 0007 and Pack 0024. Candidate source is limited to current project-owned planning, execution, operation, persistence/reporting and CLI surfaces plus focused tests and lifecycle documentation. Core contracts may be consumed but must not be redesigned through this Change Request.

## 9. Backward Compatibility

Yes for existing bounded Browser runs. Existing selector safety limits, accepted identities, batch history and error meanings must remain valid. The new full-corpus workflow is additive and segmented.

## 10. Risk Level

High because incorrect routing or segmentation could execute the wrong channel, duplicate variants, omit identities or corrupt reconciliation evidence.

## 11. Migration or Remediation Needed

Yes:

- project Remediation 0007 for the existing Browser-only application handoff;
- project Pack 0024 for new bounded corpus segmentation;
- a data migration only if Pack 0024 proves that existing persistence cannot represent segment lineage safely.

## 12. Required Human Approval

Planning is approved. Exact source allowlists, error behavior, any migration, execution, tests, acceptance and commits require their own gates.

## 13. Acceptance Criteria

- existing Browser execution remains compatible;
- HTTP scenarios route through the accepted HTTP executor without target logic in Core;
- unsupported capabilities fail safely through the accepted executor contract;
- each segment contains at most 1,000 items and has a deterministic non-overlapping identity range/fingerprint;
- all 24,668 current identities are covered exactly once across a complete segmented plan;
- retry/resume/cancel remains segment- and item-safe;
- final reporting distinguishes segment progress from full-corpus reconciliation;
- no target selector, recipe, fixture or N-D rule enters project/Core generic code;
- synthetic/no-retention Decision-0017 constraints are enforced by scope and tests.

## 14. Rollback Plan

Rollback each implementation Pack separately. Preserve existing accepted batch/test history, remove only Pack-owned additive source/schema/configuration, and never delete DK mappings or target data broadly.

## 15. Final Decision

Approved for planning and later separately gated implementation.

## 16. Required remediation

`docs/project/ai/remediations/REMEDIATION-PACK-TMS-EXECUTOR-ROUTING-0007.md`.

## 17. Notes

This Change Request does not authorize Pack execution, raising safety limits, staging/production access, retained evidence, a second user, real data, real credentials or real Provider traffic.
