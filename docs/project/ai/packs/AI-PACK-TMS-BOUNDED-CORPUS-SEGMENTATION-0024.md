# AI-PACK-TMS-BOUNDED-CORPUS-SEGMENTATION-0024 — Deterministic bounded full-corpus orchestration

Status: `blocked draft — normalize after accepted DK Pack 0004, Remediation 0006, current N-D drift disposition and Remediation 0007`

## 1. Task ID

`AI-PACK-TMS-BOUNDED-CORPUS-SEGMENTATION-0024`

## 2. Task Title

Add deterministic bounded segmentation for complete ND corpus execution.

## 3. Goal

Allow TMS to plan, start, resume, cancel, report and reconcile the complete 24,668-case ND corpus as deterministic non-overlapping segments while preserving the existing 1,000-item per-segment safety ceiling.

## 4. Context

Current selection intentionally rejects more than 1,000 matched items and catalog traversal is bounded. DK stage 15 requires all 24,668 identities to execute through bounded child Packs. Raising limits or scripting untracked manual slices would weaken safety and traceability. Decision 0024 assigns reusable segmentation/orchestration to the project rather than Core or DK.

## 5. Related Release / Phase

Decision-0015 stage 15 prerequisite, ordered by Decision 0024.

## 6. Related Epic / Feature / Story

Change Request 0013; complete ND MVP execution without stage-16 second-target scope.

## 7. Source References

- Decisions 0010–0012, 0015 and 0024;
- accepted DK Pack 0004 Run;
- accepted Remediation 0006 Run;
- accepted Remediation 0007 Run;
- current N-D contract-drift result and DK Pack 0005 normalization input.

## 8. Files to Create

Deferred until normalization. Expected additions are project-owned segmentation DTO/service/operation tests and, only if required, migration and Run Report files.

## 9. Files to Edit

Deferred until normalization. Candidate ownership is limited to project catalog/planning/operation/batch/report/CLI/configuration/tests and the Persian Acceptance CLI guide. Core executor implementations, DK source and N-D source are not editable through this Pack.

## 10. Files to Read / Reference

At normalization, read only the accepted sources/Runs above and current project planner, selector, operation, batch, reporting and CLI files required to define the exact allowlist.

## 11. Configuration / Settings Requirements

No configuration change is approved by this draft. Normalization may define bounded non-sensitive segment size/concurrency defaults, never above 1,000 items per segment. No environment, Admin Setting or secret is authorized.

## 12. Do Not Change

Do not raise/remove selector or traversal safety bounds, place orchestration in Core/DK, add N-D identities to generic code, execute real targets/providers, retain raw evidence, change dependencies, read `.env`, rewrite history or include stage-16 second-target work.

## 13. Clarification Questions Before Implementation

After predecessor acceptance, resolve exact segment identity/cursor, parent-run semantics, persistence need, failure policy, operator command shape, cancellation behavior, prerequisite reuse/isolation, ordering and quantitative local time/memory budgets.

## 14. Multilingual / Translation Requirements

No product UI or translation change. The Persian operator guide must document the accepted workflow; machine keys and error codes remain English and language-neutral.

## 15. UI / Admin UI Requirements

No UI or Admin UI.

## 16. Operator Documentation Requirements

Update `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md` with segment creation, progress, resume/cancel, final reconciliation, safe limits and troubleshooting after the exact CLI contract is accepted.

## 17. Implementation Rules

- keep each segment at or below 1,000 items;
- derive stable ordering from full hierarchy identity and pinned catalog version;
- prove no gap/overlap across segments;
- preserve parent/segment/item idempotency and traceability;
- never materialize all executable scenarios or Steps eagerly;
- keep target prerequisites and cleanup scoped to the exact item/run contract;
- final completion requires every planned identity to reach an accepted terminal accounting state.

## 18. Architecture Constraints

Project owns segmentation/orchestration. Core remains executor-only. DK supplies catalog/scenarios/adapters through public contracts. N-D supplies target recipes/fixtures. Segmentation must be target-neutral and must not inspect N-D business payloads.

## 19. Validation Rules

Prove exact 24,668 accounting, maximum segment size, deterministic replay, no duplicate/orphan identity, bounded memory/traversal, catalog drift rejection, retry/resume/cancel safety, parent/segment/item trace, safe reporting and compatibility with existing single-batch behavior.

## 20. Security Rules

Decision 0017 applies. Use only inert synthetic identities and fake Provider behavior; retain metadata-only evidence; omit target references, payloads, secrets and raw Browser/HTTP content.

## 21. Error Handling / Logging / Traceability Requirements

Normalization must define stable errors for stale catalog/segment, gap/overlap, invalid cursor, parent conflict and partial failure; classify retry/permanence/admin action; and propagate correlation, parent run, segment, batch, item, attempt, test and full hierarchy identities. No exact new error code is approved by this draft.

## 22. Data Model / Migration / Relationship Requirements

Unknown until normalization. Prefer existing batch/operation records when they can preserve parent-segment lineage. Any new table/column/index requires exact migration, referential actions, history behavior and rollback approval. No historical rewrite.

## 23. Commenting Requirements

Comment only ordering, cursor, idempotency and gap/overlap invariants that are not obvious from types.

## 24. Testing Requirements

Use bounded synthetic generators and existing public contracts. Required tests cover 24,668 identities, one scenario above 1,000 variants, deterministic segment replay, gap/overlap negatives, catalog drift, partial failure, retry/resume/cancel, reporting, query/memory bounds and sensitive-sentinel absence.

## 25. Acceptance Checklist

- [ ] predecessor Runs accepted in Decision-0024 order;
- [ ] exact source/test/migration/config allowlist normalized;
- [ ] each segment is at most 1,000 items;
- [ ] 24,668 identities reconcile exactly once;
- [ ] ordering/replay/drift/recovery tests pass;
- [ ] Core and DK owner boundaries remain clean;
- [ ] CLI guide is updated;
- [ ] no real/external I/O or retained raw evidence;
- [ ] focused and full suites pass;
- [ ] Run/index lifecycle synchronized.

## 26. Tests to Add

Deferred until normalization; include focused segmentation, operation/batch integration and full-corpus synthetic scale tests.

## 27. Tests to Run

Deferred. No source, database, queue worker, Browser, target, corpus regeneration or network command is authorized by this draft.

## 28. Expected Output

One accepted target-neutral segmented-run contract that can account for all 24,668 ND identities without raising per-segment limits, followed by a safe normalization path for DK Pack 0005 child Packs.

## 29. Operator Execution Checklist

Before execution, approve exact segment semantics, files, persistence, errors, budgets, commands and rollback. After execution, inspect exact counts, no overlap/gap, replay, recovery, traceability, safe output and the updated guide.

## 30. Agent Final Report

Report exact segment contract, counts, budgets, persistence/migration, trace flow, errors, tests, guide updates, limitations, deviations and commit state.

## 31. Review Checklist

Review owner boundaries, boundedness, ordering, idempotency, drift, recovery, full accounting, no eager materialization, safe output, migration/rollback and operator workflow.

## 32. Rollback / Safety Notes

Rollback only Pack-owned additive source/config/schema/docs. Preserve accepted historical batches/tests and DK mappings. Remove test-owned temporary artifacts only after verifying their exact owned path; never broadly clean data.

## 33. Stop Conditions

Stop for an unaccepted predecessor, unresolved drift, a required limit increase, eager full-corpus materialization, target-specific project/Core logic, undefined segment identity, migration without rollback, real data/I/O, retained unsafe evidence, missing cleanup or scope conflict.

## 34. Open Questions

Exact persistence and CLI shape are intentionally deferred until predecessor evidence. This Pack does not replace stage-16 Pack 0017, which remains responsible for second-target proof and later generalized scale hardening.
