# CHANGE-REQUEST-TMS-BROWSER-BRIDGE-CORRECTIONS-0017

## 1. Title

Align the DK Browser bridge with enhanced navigation, stable focus, presentation headers, and bounded observation types.

## 2. Status

Implemented and technically/live validated; operator accepted the result and authorized the scoped commit on 2026-10-15.

## 3. Related Release / Phase

DK Pack 0004 stage-14 Operations Health Browser Acceptance.

## 4. Decision Source

Accepted Browser runtime/semantic-observation decisions and operator authorization for bounded current-Pack blocker corrections.

## 5. Proposed Change

Read the post-enhancement navigation contract in one bounded page snapshot, validate a same-origin destination before direct navigation, send exact locale/theme request headers, use the visible contextual-help trigger as the focus target, remove a redundant implicit-role fact assertion, and expect the integer JSON representation of zero failure rate.

## 6. Reason for Change

Live Browser execution exposed differences between pre-enhancement DOM assumptions and the real Admin page: the navigation group becomes a semantic section, narrow navigation hides the original focus target, the shared semantic probe does not infer an implicit `<main>` role, and JSON normalizes `0.0` to integer `0`.

## 7. Scope of Impact

Only the DK owner Browser runtime, scenario selectors/catalog, and focused tests. No target product UI, shared Core probe, API, schema, dependency, or external I/O change.

## 8. Affected Files

DK Operations Health selectors/catalog/scenario, DK Browser runtime, their focused tests, this Change, Remediation 0011, Run, and indexes.

## 9. Backward Compatibility

The exact 21 imported identities and five-step scenario contract remain unchanged. The bridge now consumes the actual accepted target DOM and transport values.

## 10. Risk Level

Low to medium; navigation is constrained to a single evaluated snapshot and strict same-origin URL validation.

## 11. Migration or Remediation Needed

Yes: `REMEDIATION-PACK-TMS-BROWSER-BRIDGE-CORRECTIONS-0011`. No schema/data migration.

## 12. Required Human Approval

Execution was authorized by the operator on 2026-10-14. Result acceptance and commit remain separate.

## 13. Acceptance Criteria

Focused runtime/scenario tests, exact plan, local Playwright smoke, full TMS suite, two live canaries, all 21 variants, 105 steps, and cleanup pass with no unsafe navigation or external I/O.

## 14. Rollback Plan

Revert only the listed DK bridge/source/tests and lifecycle records; no target data rollback is required after verified cleanup.

## 15. Final Decision

Implementation and validation complete; operator accepted the result and authorized the scoped commit on 2026-10-15.

## 16. Required Remediation

`docs/project/ai/remediations/REMEDIATION-PACK-TMS-BROWSER-BRIDGE-CORRECTIONS-0011.md`.
