# REMEDIATION-PACK-TMS-EXECUTOR-ROUTING-0007 — Executor-neutral application handoff

Status: `blocked draft — ND human-v1.1 amendment implemented/offline validated on 2026-10-16; amendment accepted 2026-10-17; exact DK successor mapping migration required before normalization`

Preflight outcome (2026-10-16): the operator authorized proceeding, but Decision-0024 prerequisite inspection found material navigation/source drift and the upstream offline validator failed with `HAC-V05 snapshot/changed-paths contract_snapshot_source_drift`. No implementation started. See `docs/project/ai/reviews/source-updates/SOURCE-UPDATE-REVIEW-ND-CONTRACT-2026-10-16.md` for evidence and the required owner-successor sequence. This authorization does not mark the draft ready or waive unresolved prerequisites.

Resumed disposition (2026-10-16): the operator explicitly authorized the cross-repository source recheck and contract update. ND Remediation 0067 now supplies human-v1.1; DK reference `ND-CONTRACT-AMENDMENT-0002.md` records the precise consumer facts and migration boundary. Core Browser/HTTP support exists, while application handoff still forces Browser and only 21 DK variants are executable. Amendment acceptance completed on 2026-10-17; exact DK obligation/context mapping remains a prerequisite; no runtime implementation or broad import is performed by the contract refresh.

## 1. Task ID

`REMEDIATION-PACK-TMS-EXECUTOR-ROUTING-0007`

## 2. Task Title

Remove Browser-only assumptions from the project execution handoff while preserving Core executor ownership.

## 3. Related Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-ND-MVP-EXECUTION-0013.md`; Decision 0024 and accepted Decision 0011.

## 4. Goal

Make the project application layer route each executable scenario through its declared accepted capability without moving scenario or target behavior into Core.

## 5. Current Problem

Current source stores `browser` on every batch item, resolves a `TargetBrowserRuntime` for every execution and creates `ExecutorRequest::browser(...)` unconditionally. Core already exposes an explicit Browser/HTTP executor registry, so the project handoff does not yet satisfy the accepted capability-neutral architecture for HTTP scenarios.

## 6. Required Change

- derive one supported executor capability from validated scenario metadata/plan data;
- persist that capability on the batch item and attempt;
- construct the matching typed executor request through a project-owned handoff;
- keep Browser runtime resolution only on the Browser path;
- obtain HTTP request data only through a target/DK-owned adapter contract;
- preserve accepted lifecycle, cleanup, correlation and safe error behavior;
- reject missing, ambiguous or unsupported capability before unsafe executor entry.

## 7. Files to Read

- Decision 0011, Decision 0024 and Change Request 0013;
- accepted Core executor contracts and current project execution/batch/run services;
- accepted DK Pack 0004 Run and the current stage-15 capability inventory.

## 8. Files to Edit

Deferred until normalization. Expected ownership is limited to project application execution/batch/run handoff and focused project tests. Core executor implementations and DK/N-D behavior are read-only unless a separately approved owner Pack is created.

## 9. Files to Create

Deferred until normalization. A project-owned typed target execution-input resolver may be added only if current contracts cannot safely supply Browser/HTTP inputs without branching on DK identity.

## 10. Do Not Change

Core executor semantics, DK scenario assertions, N-D fixtures/product behavior, selector limits, real target/provider access, evidence retention, `.env`, dependencies, accepted historical Runs or existing batch/test history.

## 11. Configuration / Settings Impact

No configuration or settings impact is approved by this draft. Any required non-sensitive executor setting must be declared during normalization. No `.env` access is allowed.

## 12. Multilingual / Translation Impact

No multilingual or translation impact.

## 13. UI / Admin UI Impact

No UI or Admin UI impact.

## 14. Implementation Rules

Use the accepted explicit executor registry. Do not branch on `dk`, `notification-delivery`, scenario keys or target recipes in shared project/Core code. Preserve typed results and fail closed before executor entry when input/capability is invalid.

## 15. Architecture Constraints

Project owns dispatch and persistence; Core owns executor contracts/implementations; DK owns scenario and target adapters; N-D owns target recipes/fixtures. No service locator, static target state or target-specific Core code.

## 16. Data Model / Migration / Relationship Impact

Unknown until normalization. Reuse existing capability fields where sufficient. Any schema change requires an exact migration/rollback section and separate approval. Historical execution records must not be rewritten.

## 17. Validation Rules

Prove Browser compatibility, HTTP routing, unsupported/ambiguous capability rejection, no Browser runtime resolution on HTTP execution, exact executor identity persistence, cleanup behavior and complete trace propagation using synthetic fakes only.

## 18. Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: No
UI / Admin UI Error Impact: No
Stable Error Code Impact: Deferred until normalization
Exception Handling Impact: Yes
External Error Normalization Impact: No
Retry / Permanent Failure Impact: Yes
Message / Attempt Status Impact: Yes
Structured Logging Impact: Yes
Traceability Impact: Yes
Sensitive Data Impact: Yes — omission must be preserved
Error-specific Test Impact: Yes
Operator Documentation Impact: No
```

The existing defect is observable: batch persistence and run construction force Browser even when an accepted HTTP executor exists. The corrected behavior must use the declared capability and preserve existing stable codes where semantically correct. Exact error-code additions or classification changes are blocked until normalization; implementation is not allowed before that resolution.

## 19. Tests to Add

Deferred until the exact allowlist is normalized. Required coverage includes Browser and HTTP success, unsupported/ambiguous capability, missing target input, no wrong-executor entry, lifecycle cleanup, trace identity and sensitive-output absence.

## 20. Tests to Run

Deferred. Focused Core contract regressions, project execution/batch tests, full PHPUnit and safe static checks will be required. No Browser, target, database migration, worker or network command is authorized by this draft.

## 21. Acceptance Checklist

- [ ] exact source/test allowlist normalized;
- [ ] capability and input contracts approved;
- [ ] Browser behavior preserved;
- [ ] HTTP path uses the accepted executor;
- [ ] no target rule enters project/Core generic code;
- [ ] failure classifications and trace fields verified;
- [ ] focused and full suites pass;
- [ ] Run/index lifecycle synchronized.

## 22. Rollback / Safety Notes

Rollback only Pack-owned project source/tests and any separately approved migration. Preserve historical batches/tests and owner source. Never use broad reset, cleanup or database deletion.

## 23. Operator Review Checklist

Approve the exact capability mapping, target-input port, files, stable errors, migration impact and tests before execution. After execution, review Browser compatibility, HTTP isolation, traceability and no target leakage.

## 24. Operator Documentation Impact

No operator-facing behavior is approved by this draft. If normalized CLI behavior changes, update `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md` in the final allowlist.

## 25. Agent Final Report

Follow the shared reporting rules and report exact routing, owner boundaries, error classifications, trace behavior, tests, migrations, deviations and commit state.
