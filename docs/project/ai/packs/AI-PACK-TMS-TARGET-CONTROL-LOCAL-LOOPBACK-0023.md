# AI-PACK-TMS-TARGET-CONTROL-LOCAL-LOOPBACK-0023 — Opt-in local-loopback HTTP transport

Status: `accepted 2026-10-13 — technical validation passed; operator accepted Run 001 and authorized the implementation commit`

## 1. Task ID

`AI-PACK-TMS-TARGET-CONTROL-LOCAL-LOOPBACK-0023`

## 2. Task Title

Add a fail-closed, opt-in loopback HTTP exception to the reusable target-control client.

## 3. Goal

Permit the accepted same-workstation DK control origin without weakening the shared client's HTTPS default.

## 4. Context

Accepted Pack 0021 implemented the Decision-0022 client with HTTPS-only origin validation. Accepted target-project Binding Pack 0002 now provides an HTTP loopback Agent. Decision 0023 authorizes only the exact bridge required by DK Pack 0004.

## 5. Related Release / Phase

Pre-Release cross-owner prerequisite for DK stage 14.

## 6. Related Epic / Feature / Story

DK Notification Delivery Operations Health Browser Acceptance, owner Pack 0004.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0022-target-acceptance-control-protocol.md`
- `docs/project/ai/decisions/TMS-DECISION-0023-local-loopback-target-control-transport.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-CONTROL-LOCAL-LOOPBACK-0012.md`
- accepted `AI-PACK-TMS-TARGET-CONTROL-PROTOCOL-0021` and Run 001
- accepted target-project Pack `AI-PACK-MARTFURY-ACCEPTANCE-CONTROL-BINDING-0002` and Run 001
- `Modules/DK/docs/ai/decisions/DK-DECISION-0005-local-loopback-target-profile.md`

## 8. Files to Create

- `docs/project/ai/decisions/TMS-DECISION-0023-local-loopback-target-control-transport.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-CONTROL-LOCAL-LOOPBACK-0012.md`
- `docs/project/ai/packs/AI-PACK-TMS-TARGET-CONTROL-LOCAL-LOOPBACK-0023.md`
- `docs/project/ai/runs/AI-PACK-TMS-TARGET-CONTROL-LOCAL-LOOPBACK-0023-run-001.md`

## 9. Files to Edit

- `app/Acceptance/Targets/Control/Http/HttpTargetControlClient.php`
- `tests/Unit/Acceptance/Targets/Control/HttpTargetControlClientTest.php`
- `docs/project/ai/decisions/TMS-DECISION-0022-target-acceptance-control-protocol.md`
- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-CONTROL-PROTOCOL-0011.md`
- `docs/project/ai/changes/CHANGES-INDEX.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004.md`
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`
- `Modules/DK/docs/ai/changes/CHANGE-REQUEST-DK-TARGET-CONTROL-LOCAL-LOOPBACK-0003.md`
- `Modules/DK/docs/ai/changes/CHANGES-INDEX.md`
- `Modules/DK/docs/ai/decisions/DK-DECISION-0005-local-loopback-target-profile.md`
- `Modules/DK/docs/ai/decisions/DECISIONS-INDEX.md`

Governance lifecycle updates within this exact list are part of Pack scope. Existing unrelated dirty changes in overlapping indexes must be preserved.

## 10. Files to Read / Reference

TMS and DK startup routers/profiles, Pack 0021 source/Run, Decisions 0022–0023, Change Requests 0011–0012, current client/tests, and applicable execution/testing/security rules.

## 11. Configuration / Settings Requirements

No configuration or environment key. Add only a constructor boolean defaulted to `false`.

### External Integration / Secret Settings Requirements

No target call or secret-provider binding. Existing just-in-time resolver behavior is unchanged.

### Test / Development Setup Requirements

Use Laravel HTTP fakes with stray requests prevented. Full tests must use an intentionally absent dotenv filename and the PHPUnit in-memory environment.

### Settings Validation

Prove default false behavior and explicit true behavior without reading `.env`.

### Operator Setup Notes

None for this Pack. DK Pack 0004 owns later composition and listener verification.

## 12. Do Not Change

Do not change protocol DTOs, paths, errors, retries, timeouts, redirect handling, TLS verification for HTTPS, secret resolution, Core, module runtime source/config, dependencies, database, UI, Browser, target code, `.env`, or live systems.

## 13. Clarification Questions Before Implementation

Resolved: operator approved localhost/HTTP on the same workstation and explicitly authorized adding this change to scope, implementing it, and rerunning tests on 2026-10-13.

## 14. Multilingual / Translation Requirements

None.

## 15. UI / Admin UI Requirements

None.

## 16. Operator Documentation Requirements

Decision, Change, Pack, Run, indexes, and DK Pack 0004 prerequisite state only.

## 17. Implementation Rules

Add `allowLocalLoopbackHttp` as the final constructor argument. Preserve current callers. Validate structure before scheme/host acceptance and return a canonical origin with IPv6 brackets intact.

## 18. Architecture Constraints

One reusable client; strict default; explicit owner opt-in; exact direct-loopback host allowlist; no DNS/network inference and no environment inference.

## 19. Validation Rules

Accept HTTPS as before. Accept HTTP only when the option is true and host is exactly `127.0.0.1`, `localhost`, or `[::1]`. Reject credentials, query, fragment, non-root path, and every other HTTP host.

## 20. Security Rules

Fail closed. Do not expose endpoint/handle/credential values in errors or logs. Do not trust suffixes, wildcard addresses, forwarded data, or alternative numeric spellings.

## 21. Error Handling / Logging / Traceability Requirements

### Error Handling Impact

Reuse `target_control_protocol_invalid` for rejected origins. No new code or status.

### Error Scenarios

Default HTTP; opted-in non-loopback HTTP; credentials; path; query; fragment; malformed origin.

### Logging Requirements

No logging change.

### Traceability Requirements

No trace model change; lifecycle correlation remains unchanged.

### Sensitive Data and Masking Requirements

Existing secret/handle non-exposure remains mandatory.

### Error Handling Test Requirements

Assert rejection and zero sends for every invalid case.

### Required Follow-up Updates

Record the technical result in Run 001 and synchronize DK Pack 0004's prerequisite wording.

## 22. Data Model / Migration / Relationship Requirements

None.

## 23. Commenting Requirements

No explanatory comment unless the code is not self-evident.

## 24. Testing Requirements

### Test Quality Requirements

Use observable outbound URL assertions and zero-send assertions; do not test private methods.

### Behavior to Prove

Strict default, exact opt-in allowlist, HTTPS compatibility, and rejection of boundary-widening inputs.

### Required Assertions

Exact URL, send count, stable error code, and no request for rejected origins.

### Required Scenarios

`127.0.0.1`, `localhost`, `[::1]`; default false; LAN, wildcard, suffix, credentials, path, query, and fragment.

### Test Data Requirements

Synthetic values only.

## 25. Acceptance Checklist

- [x] exact Pack scope preserved;
- [x] default HTTPS-only behavior preserved;
- [x] opt-in exact loopback HTTP behavior proven;
- [x] negative security cases proven with no send;
- [x] focused, affected, and full tests pass;
- [x] lint/style/diff/sensitive checks pass;
- [x] Run and indexes synchronized;
- [x] no target, Browser, database, `.env`, or external I/O.

## 26. Tests to Add

Extend `HttpTargetControlClientTest` with default rejection, exact opt-in positive cases, and opted-in negative cases.

## 27. Tests to Run

- focused `HttpTargetControlClientTest`;
- target-control unit directory;
- affected target lifecycle/runtime tests from Pack 0021;
- complete repository suite through an absent-dotenv bootstrap;
- PHP lint, scoped Pint, `git diff --check`, scope and sensitive-surface scans.

## 28. Expected Output

One backward-compatible constructor option and exact origin-validation branch, with automated evidence and no external mutation.

## 29. Operator Execution Checklist

### Before AI Execution

- [x] operator approved scope addition and implementation;
- [x] target-project Binding Pack 0002 Run accepted;
- [x] current source and dirty worktree reviewed;
- [x] exact paths and unrelated overlapping changes identified.

### After AI Execution

- [ ] review Run 001 and diff;
- [ ] accept/reject result;
- [ ] separately authorize commit if desired;
- [ ] only then continue DK Pack 0004 gates.

## 30. Agent Final Report

Report files, exact behavior, tests/counts, dirty-overlap preservation, external actions not performed, and remaining acceptance/commit gates.

## 31. Review Checklist

Review constructor compatibility, host parsing (including IPv6 brackets), exact negative set, no transport weakening outside opt-in, and preserved unrelated changes.

## 32. Rollback / Safety Notes

Source/document-only rollback. No data, target resource, network session, migration, or Browser state is created.

## 33. Stop Conditions

Stop for required dependency/config/DTO/protocol/error change, need for DNS/network inspection, live target access, `.env`, database mutation, out-of-scope file, overlapping destructive edit, or failed full suite not attributable and fixable in scope.

## 34. Open Questions

None for source execution. Run acceptance and implementation commit are authorized; target access and DK Pack 0004 execution remain separate.
