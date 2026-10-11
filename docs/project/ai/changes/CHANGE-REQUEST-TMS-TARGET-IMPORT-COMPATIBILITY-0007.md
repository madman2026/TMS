# CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007 — Make target scenario imports runtime-resolvable and scale-validatable

## 1. Title

Make target scenario imports runtime-resolvable and scale-validatable before owner source is written.

## 2. Status

Partially implemented — Decisions 0018–0019 and Remediations 0003–0004 corrected and validated the retained DK descriptor imports and generic CLI test isolation; the complete suite now passes. The per-scenario metadata/replay-prevention slice is approved through Change Request 0009 and assigned to Pack 0019. Descriptor-resolution prevention, generated-source formatting policy, bounded PCRE execution, and remaining scale hardening stay proposed and separately gated here.

## 3. Related Release / Phase

Pre-Release TMS stage 13 target corpus import.

## 4. Decision Source

Accepted project Decisions 0012, 0015, and 0018, plus the failed runtime validation of `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-CORPUS-IMPORT-0003.md` on 2026-10-10.

## 5. Proposed Change

Correct the target-module scenario-import boundary so an accepted import cannot pass dry-run, apply, replay, and structural validation while producing catalog code that cannot resolve the shared descriptor classes at runtime.

The operator-approved targeted remediation must:

1. make the existing DK Notification Delivery component resolve `App\Data\SuiteDescriptor`, `App\Data\ScenarioDescriptor`, and `App\Data\VariantDescriptor` without hand-editing generated managed lines;
2. preserve the already applied and replay-idempotent Pack 0003 output;
3. re-run the affected DK runtime catalog and coverage validation;
4. isolate generic CLI presentation/JSON tests from the real 24,668-variant catalog without changing production listing limits, then re-run the complete suite.

The remaining proposed shared remediation must separately:

1. make target-module validation or import preflight reject an owner component whose generated descriptor references cannot resolve;
2. define and validate the supported formatting policy for large generated managed regions, including whether they are exempt from ordinary Pint formatting or must be emitted in a formatter-compatible representation;
3. remove the need for an undocumented process-local `pcre.backtrack_limit` override during accepted chunk import, or explicitly establish and validate a bounded supported execution requirement;
4. correct reusable documentation/examples that still show the nonexistent `--app=dk` coverage option.

Change Request 0009 and Pack 0019 additionally own only the replay-safe migration from a shared generated `metadata()` call to exact keyed `metadataFor('<scenario-key>')` calls. That bounded dependency slice does not close the remaining items above.

## 6. Reason for Change

DK Pack 0003 applied and idempotently replayed all 25 accepted chunks, producing 24,668 mappings and 139 non-executable skeletons. Runtime tests then failed because the pre-existing DK component did not import the three descriptor classes assumed by the shared importer. `acceptance:app:validate DK --json` still reported `valid=true`, so the structural validator did not detect the runtime-invalid result. `acceptance:coverage dk --json` failed with `acceptance_coverage_mapping_invalid`.

The same run also showed that the default PCRE backtrack limit rejects a valid full chunk with `target_module_path_collision`, while a process-local higher limit succeeds, and that the generated mapping/component representation cannot satisfy the Pack's ordinary Pint command at this scale. A later complete-suite run exposed two generic CLI contract tests that implicitly used the real registry and therefore hit the intentional 1,000-row listing limit; Remediation 0004 isolated those tests and preserved the production limit/error contract.

## 7. Scope of Impact

Affected areas:

- shared target-module import validation and its focused tests;
- DK Notification Delivery component descriptor resolution;
- generic Acceptance CLI contract-test isolation;
- generated-source formatting and bounded import execution policy;
- DK Pack 0003 validation instructions and lifecycle records;
- no target behavior, executable scenario Steps, provider, browser, network, queue, database, config, environment, API, or UI behavior.

## 8. Affected Files

The approved targeted remediation scope is defined by Remediations 0003–0004 and includes the DK component, two generic CLI contract tests, and directly related lifecycle records. Expected candidates for the still-proposed shared follow-up are:

- `app/Acceptance/Modules/NwidartTargetModuleCreator.php`;
- `app/Acceptance/Modules/TargetModuleValidator.php`;
- `tests/Unit/TargetModuleCreatorTest.php`;
- `tests/Unit/TargetModuleValidatorTest.php`;
- focused shared importer/validator tests;
- directly related future Change, Remediation, Pack, Run, and index records.

The exact per-scenario metadata migration files are listed in project Pack 0019 and are not an expansion of this Change Request's unresolved scale/format scope.

Decisions 0018–0019 and Remediations 0003–0004 authorize only the exact DK import correction, two-test isolation, and their validation. They do not authorize shared production-source changes.

## 9. Backward Compatibility

Yes for intended valid generated target modules. The correction must reject only component/import states that cannot resolve the accepted shared descriptor contract or cannot be processed under the declared bounded execution policy.

## 10. Risk Level

Medium. The runtime correction is small, but the prevention belongs to a shared importer/validator boundary and the existing DK import contains large managed regions.

## 11. Migration or Remediation Needed

Yes. Project Remediation 0003 owns the approved target-specific correction. A separate remediation remains required for the proposed shared prevention and scale/format hardening.

## 12. Required Human Approval

The operator approved retention of the safe applied DK Pack 0003 output, the exact Remediation 0003 import correction, and its validation on 2026-10-11. Shared hardening, final result acceptance, Run creation, and commit remain separate gates.

## 13. Acceptance Criteria

- [x] the retained DK generated/imported component resolves all shared descriptor classes at runtime;
- [ ] validator or preflight fails safely before write when descriptor resolution is missing — shared follow-up;
- [x] the corrected DK catalog reports version `v3`, 10 suites, 139 scenarios, and 24,668 variants;
- [x] coverage reports exactly 24,668 `automated_full` mappings and zero other dispositions;
- [ ] the accepted 25 chunks apply/replay idempotently without an undeclared PCRE failure mode — shared follow-up;
- [ ] the generated-source formatting policy is explicit and its scoped validation passes — shared follow-up;
- [x] all Pack 0003 focused and affected regression tests pass;
- [x] the complete suite passes after generic CLI contract tests receive deterministic registry input;
- [x] no executable scenario behavior or external I/O is introduced;
- [ ] Pack 0003 is normalized as `re-run-after-remediation` and then re-executed only after remediation acceptance.

## 14. Rollback Plan

The operator chose to retain the safe partial import. Remediation 0003 rollback removes only the three imports and synchronizes its lifecycle records; it must not remove the retained Pack 0003 output. Any later Pack rollback remains targeted to the recorded pre-apply hashes. Broad reset/clean commands are prohibited.

## 15. Final Decision

Partially approved by the operator on 2026-10-11: exact DK descriptor imports and retained Pack output approved; shared prevention and scale/format hardening remain pending.

## 16. Required remediation

`docs/project/ai/remediations/REMEDIATION-PACK-TMS-DK-DESCRIPTOR-IMPORTS-0003.md` owns the approved target-specific correction. A future project remediation is still required for the remaining shared items.

## 17. Notes

The Decision-0017 synthetic/local exception is unrelated to this defect and remains unchanged. No sensitive-data protection capability is introduced by this Change Request.
