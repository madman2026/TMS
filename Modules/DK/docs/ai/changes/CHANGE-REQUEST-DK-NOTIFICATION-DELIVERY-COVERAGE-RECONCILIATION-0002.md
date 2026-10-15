# CHANGE-REQUEST-DK-NOTIFICATION-DELIVERY-COVERAGE-RECONCILIATION-0002 — Complete executable N-D coverage reconciliation

## 1. Title

Plan and complete the remaining Notification Delivery Acceptance groups and final source-coverage reconciliation.

## 2. Status

Open planning change — assigned to blocked draft DK Pack 0005; Decision 0024 requires accepted DK Pack 0004, project Remediations 0006–0007, current N-D contract-drift disposition and project Pack 0024 before child execution/final reconciliation.

## 3. Related Release / Phase

Decision-0015 roadmap stage 15.

## 4. Decision Source

Accepted project Decisions 0012, 0015 and 0024 and DK Decision 0002's explicit deferral of all work outside the first Operations Health group.

## 5. Proposed Change

Reconcile the complete 139-definition/24,668-variant source corpus against current N-D and TMS source, preserve every source identity, partition remaining executable work into bounded owner Packs and close every mapping with executed evidence or an approved partial/merged/excluded disposition. Follow Decision 0024's owner split: project orchestration/segmentation, Core executors, DK scenarios/adapters/oracles and N-D target fixtures/drivers.

## 6. Reason for Change

DK Pack 0003 created mappings and non-executable skeletons. DK Pack 0004 covers only 21 variants. Mapping status alone is not executed coverage and cannot close stage 15. Current application execution is Browser-specific and one selector is limited to 1,000 matched items; Decision 0024 routes those cross-owner prerequisites to project Remediation 0007 and Pack 0024 rather than duplicating them in DK.

## 7. Scope of Impact

DK owner scenarios/catalog/mappings/tests and later bounded Browser/API/fake/callback/queue/worker Packs. Shared TMS changes require project Packs; N-D fixture/driver changes require N-D Packs. A target-side Pack must be accepted before the DK child Pack that consumes its new recipe/fixture behavior.

## 8. Affected Files

Exact source files are intentionally deferred until Pack 0004 acceptance and contract drift reconciliation. The planning Pack, this Change Request and owner indexes are the only current changes.

## 9. Backward Compatibility

Preserve all accepted identities and mappings unless a reviewed source-drift migration records replacements. Do not silently rename/delete source cases or make all skeletons executable.

## 10. Risk Level

High due to corpus size, generated source, sensitive flows and cross-owner effects. Work must be split into bounded Packs with exact safety/evidence policies.

## 11. Migration or Remediation Needed

No TMS data migration is approved. Contract regeneration or deterministic mapping migration may be required after drift review.

## 12. Required Human Approval

Planning record creation is approved. Drift disposition, grouping, every child Pack, source execution, target/Browser/worker access, acceptance and commits remain separately gated.

## 13. Acceptance Criteria

- DK Pack 0004 is accepted with actual 21-variant evidence;
- project Remediations 0006–0007 and project Pack 0024 are accepted in Decision-0024 order;
- current source/contract drift is resolved, with an N-D contract-refresh and TMS/DK successor-import Pack executed first only when drift requires them;
- all 24,668 mappings are accounted for exactly once;
- every executable group has actual accepted evidence;
- partial/merged/excluded mappings have approved reasons and replacement links;
- sensitive groups wait for applicable information protection;
- no real Provider/SMS/customer data is used;
- final stage-15 coverage report distinguishes mapping from execution.

## 14. Rollback Plan

Each child Pack owns targeted rollback. Generated mapping changes use accepted replay/migration mechanisms. Never broadly regenerate or delete the corpus without preserved hashes and replacement traceability.

## 15. Final Decision

Stage-15 work is required; exact executable grouping and source scope remain pending the complete Decision-0024 predecessor chain. Project Pack 0022 remains deferred only inside Decision 0017 and project Pack 0017 remains post-stage-15 work.

## 16. Required Remediation

Project Remediation 0006 must close shared importer/validator hardening before broad generated-corpus writes. Project Remediation 0007 must close executor-neutral application handoff before API/HTTP child execution. Project Pack 0024 must provide bounded segmentation before full-corpus child execution and reconciliation. Any detected contract drift must be resolved through the owning N-D and TMS/DK Packs before normalization.

## 17. Notes

Production enablement, staging, shared/remote execution, retained raw evidence and real Provider behavior are not stage-15 MVP goals. Child order is Browser read-only, Admin mutation/permission/audit, API/HTTP, fake Provider/callback/queue/worker and synthetic-sensitive groups; exact child count/files remain deferred to the accepted drift/capability inventory.
