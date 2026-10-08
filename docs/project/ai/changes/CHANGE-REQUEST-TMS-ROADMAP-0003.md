# CHANGE-REQUEST-TMS-ROADMAP-0003 — Replace the CLI-only roadmap with the layered TMS architecture

## 1. Title

Register the transport-neutral, Nwidart, multi-executor, async TMS architecture and replace the pending CLI-only roadmap.

## 2. Status

Implemented — documentation validated on 2026-10-07; no runtime implementation authorized.

## 3. Related Release / Phase

Not applicable; pre-Release platform and target-onboarding roadmap.

## 4. Decision Source

`docs/project/ai/decisions/TMS-DECISION-0007-nwidart-target-modules-and-component-hierarchy.md` through `docs/project/ai/decisions/TMS-DECISION-0013-fifteen-stage-layered-delivery-roadmap.md`.

## 5. Proposed Change

Replace the pending CLI-only/sequential future plan with a transport-neutral operation layer, complete Artisan client, Nwidart target modules and Components, declared input/approval workflow, App-owned target resources, capability-based executors, Laravel queue/batch orchestration, reports/traceability, and staged DK ownership. Supersede Draft project Pack 0008 and create the current-owner Draft Packs required by Decision 0013.

## 6. Reason for Change

The operator requires all TMS operations through Artisan now, later Web UI/MCP reuse, maximum automated E2E coverage, async/batch execution, and reusable onboarding for other systems. The earlier roadmap explicitly deferred or excluded these capabilities.

## 7. Scope of Impact

This Change Request authorizes documentation maintenance only: project Decisions 0007–0013, current-owner Draft Packs, Pack/Decision/Change indexes, and historical supersession notices. It does not authorize source, config, dependency, database, module, queue, test, target-I/O, environment, or commit changes.

## 8. Affected Files

- project Decision, Change, and Pack indexes and the new records named by Decision 0013;
- historical Decisions 0002–0005 and Draft Pack 0008 only for current-architecture notices/status;
- Core Pack index and Core executor Draft Pack 0003.

Stages 10–14 are excluded until their owners are active.

## 9. Backward Compatibility

Yes for accepted source and accepted Pack evidence. Existing accepted commands and runtime behavior remain unchanged. Pending future Pack instructions change.

## 10. Risk Level

Medium. The roadmap crosses project/Core/future target owners and introduces persistence and state contracts, but this Change Request changes documentation only.

## 11. Migration or Remediation Needed

No current implementation remediation. Outcome: future-pack-regeneration / no-remediation-required. Each Draft Pack requires separate normalization and approval.

## 12. Required Human Approval

Satisfied by the operator's explicit approval of the decisions and instruction to register them and create the Packs. Execution and commit approval remain separate.

## 13. Acceptance Criteria

- [x] Decisions 0007–0013 are indexed and mutually consistent.
- [x] Decision 0013 records all fifteen stages and the owner-activation gate for stages 10–14.
- [x] Draft Pack 0008 is visibly superseded and cannot be executed.
- [x] Draft Packs for stages 1–9 and 15 exist in the correct current owner indexes.
- [x] No DK/ND-specific implementation Pack is placed under project or Core ownership.
- [x] New and changed document references resolve and `git diff --check` passes.
- [x] No source, tests, config, dependencies, `.env`, database, or target system is changed.

## 14. Rollback Plan

Remove only the new Decision/Change/Pack records and reverse their index/notices before commit. Do not reset accepted source, Packs, Runs, Reviews, or unrelated work.

## 15. Final Decision

Implemented for documentation creation. Runtime implementation is not approved; every Draft Pack retains normalization and approval gates.

## 16. Required remediation

None.

## 17. Notes

The fresh resource/executor/orchestration Packs do not restore the deleted proposals from Decision 0006. They implement the newly approved boundaries and remain Draft until normalized.

Validation Result (2026-10-07): passed. Seven Decisions, ten current-owner Draft Packs, and this Change Request were created; nine existing governance documents were synchronized. All ten Pack files contain the required numbered sections 1–34. The roadmap contains stages 1–15 exactly once, Pack IDs are unique, and all Decision/Pack/Change index references resolve. A whitespace/final-newline scan covered all 27 changed files and `git diff --check` passed. Git scope inspection found no source, test, config, dependency, `.env`, database, generated artifact, or target-system change. No runtime tests were run because this change is documentation-only.
