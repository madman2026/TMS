# REMEDIATION-PACK-TMS-BROWSER-BRIDGE-CORRECTIONS-0011

Status: `accepted — technically/live validated and operator-accepted with scoped commit authorization on 2026-10-15`

Owner: TMS project with DK owner-local implementation files

## 1. Task ID

`REMEDIATION-PACK-TMS-BROWSER-BRIDGE-CORRECTIONS-0011`

## 2. Task Title

Correct the live DK Operations Health Browser bridge.

## 3. Related Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-BROWSER-BRIDGE-CORRECTIONS-0017.md`

## 4. Goal

Make the exact accepted 21-variant Browser group consume the real enhanced Admin DOM and request presentation context without expanding scope.

## 5. Current Problem

Live canaries exposed bounded navigation, focus, implicit-role, and numeric transport mismatches despite correct product behavior.

## 6. Required Change

Use one bounded navigation snapshot and strict same-origin direct navigation; send locale/theme headers; focus the visible contextual-help trigger; retain semantic `<main>` selection without asserting an unsupported inferred role fact; use the decoded integer zero metric.

## 7. Files to Read

Pack 0004, accepted runtime/observation contracts, current DK runtime/scenario/catalog/selectors, target navigation/health sources, and applicable rules.

## 8. Files to Edit

The DK Operations Health selectors, catalog, scenario, Browser runtime, and focused tests; directly related lifecycle records/indexes.

## 9. Files to Create

Change 0017, this Remediation, and `docs/project/ai/runs/REMEDIATION-PACK-TMS-BROWSER-BRIDGE-CORRECTIONS-0011-run-001.md`.

## 10. Do Not Change

Shared Core probe semantics, target product UI, imported identities/assertions, schema, API, dependency, Provider/queue/network behavior, or unrelated catalog groups.

## 11. Configuration / Settings Impact

No persisted configuration change; the runtime adds one bounded non-secret request header.

## 12. Multilingual / Translation Impact

No translation source change; existing EN/FA and LTR/RTL states are truthfully selected and validated.

## 13. UI / Admin UI Impact

No UI source change; existing navigation, Health view, and help trigger are consumed.

## 14. Implementation Rules

One bounded DOM evaluation, exact keys/counts, strict origin equality, no query/credentials, source-owned selector, no raw DOM/text persistence, and no fallback selectors.

## 15. Architecture Constraints

DK owns target-specific bridge semantics; Core remains generic; target project owns UI and identity bridge.

## 16. Data Model / Migration / Relationship Impact

None.

## 17. Validation Rules

Focused DK/runtime tests, Pack-0019 regressions, local Playwright smoke, exact 21-item plan, full TMS suite, two canaries, all 21 live variants, and zero residual resources.

## 18. Error Handling / Logging / Traceability Impact

No new stable code or log field. Unsafe/missing navigation still fails closed; raw URLs/content are not persisted.

## 19. Tests to Add

Focused runtime assertions for the enhanced navigation snapshot, presentation header, stable focus path, strict href rejection, and exact observation facts.

## 20. Tests to Run

The Pack 0004 focused/cross-owner/full/static/live matrix gates.

## 21. Acceptance Checklist

- [x] bounded navigation correction implemented;
- [x] presentation/focus/observation corrections implemented;
- [x] focused, smoke, plan, and full suites pass;
- [x] all 21 live variants and 105 steps pass;
- [x] cleanup and zero residual resources proven;
- [x] Run/index records synchronized;
- [x] operator acceptance recorded on 2026-10-15;
- [x] operator accepted the result and authorized the scoped commit on 2026-10-15.

## 22. Rollback / Safety Notes

Revert only listed bridge/test/docs files. No broad target cleanup or database reset.

## 23. Operator Review Checklist

Review single-snapshot navigation, same-origin guard, exact headers, source-owned focus selector, unchanged identities, full evidence, and cleanup.

## 24. Operator Documentation Impact

No operator-guide change required; target runbook owns the local header contract.

## 25. Agent Final Report

Report files, initial live mismatches, corrections, exact automated/live counts, cleanup, residual environment risk, Git state, and remaining gates.
