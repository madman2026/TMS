# REMEDIATION-PACK-TMS-PACK-0003-CLI-TEST-ISOLATION-0004 — Run 001

## 1. Run Metadata

```text
Run Report ID: REMEDIATION-PACK-TMS-PACK-0003-CLI-TEST-ISOLATION-0004-run-001
Run Number: 001
Execution Date: 2026-10-11
Agent: Codex
Model / Tool: Codex desktop with local PHP/Laravel CLI
Created Before Commit: Yes
Run Status: accepted
```

## 2. Pack Reference

```text
Pack ID: REMEDIATION-PACK-TMS-PACK-0003-CLI-TEST-ISOLATION-0004
Pack Path: docs/project/ai/remediations/REMEDIATION-PACK-TMS-PACK-0003-CLI-TEST-ISOLATION-0004.md
Related Decision: TMS-DECISION-0019
Related Change Request: CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007
```

The operator accepted the validated remediation and separately authorized commit on 2026-10-11.

## 3. Pack Scope Summary

Isolate two generic Acceptance CLI presentation/JSON tests from the 24,668-item production catalog by binding a real empty registry inside the exact test methods. Preserve production limits and error behavior.

## 4. Execution Context

```text
Branch: main
Commit Before Execution: 356f612f9c66959bae2d8601cac14c42fc93a955
Commit After Execution: the authorized commit containing this report; resolve from Git history
Diff Checked: Yes
Configuration Impact: No
```

## 5. Path Resolution Review

The two exact test files and related service contracts were resolved without ambiguity.

## 6. Configuration / Settings Verification

Not applicable; no configuration changed.

## 7. UI / Admin UI Verification

Not applicable; only CLI test isolation changed.

## 8. Operator Documentation Verification

No operator documentation update was required.

## 9. Files Read / Referenced

Mandatory routers/rules, Decision 0019, Change Request 0007, Pack 0003, the two failing tests, and nearby catalog/operation command contracts.

## 10. Created Files

- Decision 0019;
- Remediation 0004;
- this Run Report.

## 11. Modified Files

- `tests/Feature/AcceptanceOperatorCommandsTest.php`;
- `tests/Feature/AcceptanceOperatorJsonContractTest.php`;
- directly related lifecycle/index records.

No production source was changed. No unauthorized file was modified or staged; the unrelated untracked `config/core.php` artifact was excluded.

## 12. Deleted Files

None.

## 13. Commenting Verification

No comments were required; the method-local binding is explicit.

## 14. API Test Artifact Verification

Not applicable.

## 15. Multilingual / RTL-LTR Verification

Not applicable.

## 16. Commands Run

The failing tests were reproduced, bounded real DK queries were verified, focused and nearby test groups were rerun, scoped Pint and diff checks passed, and the full suite passed.

## 17. Tests Added

No new test method was required; the two existing contract tests were isolated at method scope.

## 18. Tests Run

- affected files: 13 tests, 237 assertions passed;
- nearby catalog/operation contracts: 14 tests, 456 assertions passed;
- full suite: 689 tests, 6,157 assertions passed.

## 19. Test Results

Final result passed. Before correction, exactly two unfiltered tests returned exit code 2 because the real catalog correctly exceeded the default 1,000-item limit.

## 20. Error Handling / Logging / Traceability Verification

Production `acceptance_catalog_limit_exceeded`, its exit code, and all catalog/selector limits remain unchanged. No logging or external-error behavior changed.

## 21. Implementation Summary

Each affected test method now binds `new AcceptanceAppRegistry` into the container before calling `acceptance:list`. The real empty implementation is used rather than a mock.

## 22. Scope Compliance

Only the two exact tests and directly related governance records changed.

## 23. Owner-root / Protected Area Review

No owner implementation root or protected production source changed.

## 24. Canonical Decisions Applied

Decision 0019 preserves the production catalog limit and isolates generic presentation contracts from production data volume.

## 25. Operator Answers / Decisions Captured

The operator added the failure to the current Pack scope, authorized the exact correction, accepted the final result, and separately authorized commit.

## 26. Deviations from Original Pack

None from Remediation 0004. It is a separately recorded correction discovered by Pack 0003 full-suite validation.

## 27. Assumptions Made

None material.

## 28. Index Updates

Decision, Remediation, project Run, and DK Pack/Run indexes were synchronized.

## 29. Documentation Maintenance

Decision 0019, Remediation 0004, Pack 0003 lifecycle records, this report, and their indexes were aligned.

## 30. Change / Remediation Links

Change Request 0007 retains broader catalog-scale and compatibility follow-up. This Run closes only the two-test isolation defect.

## 31. Open Questions

None blocking.

## 32. Potential Risks

No production risk was introduced. Generic contract tests now intentionally use a deterministic empty catalog, while separate tests continue to cover real limits and DK scale.

## 33. Human Review Needed

Completed by the operator on 2026-10-11.

## 34. Ready for Review

Yes; focused, nearby, full-suite, formatting, and diff checks passed.

## 35. Acceptance Status

```text
Acceptance Status: accepted
Accepted By: operator
Review Date: 2026-10-11
Commit Authorization: granted separately
```

## 36. Required Follow-up Updates

Complete any broader catalog-scale hardening only through a future approved Pack under Change Request 0007.

## 37. Traceability Notes

```text
Related Decision: TMS-DECISION-0019
Related Pack: AI-PACK-DK-NOTIFICATION-DELIVERY-CORPUS-IMPORT-0003
Baseline Commit: 356f612f9c66959bae2d8601cac14c42fc93a955
Rollback: remove the two method-local bindings/imports and synchronize directly related lifecycle records
```
