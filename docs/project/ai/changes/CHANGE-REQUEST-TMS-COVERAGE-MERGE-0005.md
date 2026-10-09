# CHANGE-REQUEST-TMS-COVERAGE-MERGE-0005

## 1. Title

Allow many source cases to share one merged-equivalent executable replacement.

## 2. Status

Implemented and accepted — Remediation Pack 0002 run 001 accepted and its commit authorized on 2026-10-10.

## 3. Related Release / Phase

Pre-Release; Decision 0015 stage 8 preparation for project Pack 0014.

## 4. Decision Source

- `docs/project/ai/decisions/TMS-DECISION-0012-automated-e2e-coverage-and-traceability.md`
- conflict found while regenerating `AI-PACK-TMS-QUERY-REPORT-EVIDENCE-0014`
- operator approval in chat on 2026-10-10: “تائید است . پیشنهاد رو اعمال کن و سپس پک رو اجرا کن”

## 5. Proposed Change

Correct the accepted Pack 0010 mapping importer and static validator so multiple distinct `merged_equivalent` source mappings may reference the same valid executable replacement tuple.

Preserve these existing constraints:

- source-case IDs are unique inside one target App;
- direct executable identities used by `automated_full` or `automated_partial` mappings are unique;
- every replacement tuple is complete and references an existing Component/Suite/Scenario/Variant identity;
- an existing source-case ID cannot be changed silently;
- no scenario is generated for merged or excluded mappings.

## 6. Reason for Change

Decision 0012 permits duplicate source cases to merge into one executable replacement and requires every merged entry to retain a stable replacement link. The accepted Pack 0010 implementation instead rejects a repeated replacement identity, making a many-to-one merge impossible and blocking correct Pack 0014 reconciliation.

## 7. Scope of Impact

Affected areas:

- target-module mapping import validation;
- static target-module validation;
- focused PHPUnit regression tests;
- project lifecycle documentation and indexes.

No configuration, route, API, permission, database, model, queue, UI, target I/O, dependency, or operator-guide behavior changes.

## 8. Affected Files

- `app/Acceptance/Modules/NwidartTargetModuleCreator.php`
- `app/Acceptance/Modules/TargetModuleValidator.php`
- `tests/Unit/TargetModuleCreatorTest.php`
- `tests/Unit/TargetModuleValidatorTest.php`
- directly related Change/Remediation/Run/Pack index and lifecycle records

## 9. Backward Compatibility

Yes for previously valid mappings. The change only accepts a Decision-0012-valid many-to-one merged mapping that was previously rejected. Duplicate source IDs and duplicate direct executable identities remain rejected with the existing stable code.

## 10. Risk Level

Low. The correction removes two over-restrictive duplicate checks without changing mapping DTO shape, generated scenario behavior, error codes, persistence, or runtime execution.

## 11. Migration or Remediation Needed

Yes: `docs/project/ai/remediations/REMEDIATION-PACK-TMS-COVERAGE-MERGE-0002.md`.

## 12. Required Human Approval

Yes — received from the operator on 2026-10-10 for this correction path. Execution, post-execution acceptance, and commit remain separate gates.

## 13. Acceptance Criteria

- two distinct merged source mappings may reference the same valid replacement tuple in one import and across later imports;
- the resulting generated mapping file passes the read-only target-module validator;
- duplicate source IDs remain rejected;
- duplicate automated full/partial identities remain rejected;
- invalid or missing replacement tuples remain rejected;
- focused creator/validator tests and relevant Pack 0010 regressions pass;
- no file outside the declared remediation and governance scope changes.

## 14. Rollback Plan

Revert the two source edits and their focused tests. No database, generated target module, persistent data, or external system rollback is required.

## 15. Final Decision

Approved by the operator on 2026-10-10.

## 16. Required remediation

Execute and validate `REMEDIATION-PACK-TMS-COVERAGE-MERGE-0002`, obtain operator acceptance, and commit it separately before Pack 0014 is regenerated and executed.

## 17. Notes

This Change Request does not authorize Pack 0014 implementation, real module generation, target access, database changes, dependency changes, or commit.
