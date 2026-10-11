# TMS-DECISION-0019 — Isolate generic Acceptance CLI contract tests from the production catalog

Status: Accepted
Scope: Project / DK Pack 0003 full-suite recovery
Source: Operator decision
Date: 2026-10-11
Review At: DK Pack 0003 post-execution review
Blocking: Yes until the focused tests and full suite pass
Closure Condition: the two generic CLI contract tests use a deterministic registry and the full suite passes without changing production catalog limits
Next Review At: DK Pack 0003 post-execution review

## Type

3. Pack-Local Decision and 6. Change Request / Remediation Trigger

## Decision

Retain the Pack 0003 catalog and its existing bounded `acceptance:list` behavior. Correct the two generic CLI contract tests that accidentally depend on the real registered catalog by binding an empty deterministic `AcceptanceAppRegistry` inside the exact test methods that verify prompt/output shape.

Implement this correction through project Remediation 0004 connected to Pack 0003 and Change Request 0007. Do not rewrite the already executed Pack's historical scope, raise the production limit, filter the real DK catalog inside generic tests, or weaken the existing limit-exceeded contract.

## Reason

After Pack 0003 registered 24,668 variants, unfiltered `acceptance:list` correctly rejected the real catalog because its default matched-item limit is 1,000. Two tests intended to verify CLI presentation and JSON projection used the production registry implicitly and therefore failed for a data-volume reason unrelated to their asserted contracts. A bounded DK query succeeds, proving that the catalog is valid and the failure is test isolation.

## Impact

- Two project feature tests gain method-local deterministic registry setup.
- No production source, limit, selector, catalog, mapping, runtime, error code, configuration, database, API, UI, external I/O, or security behavior changes.
- The original full-suite evidence changes from 687 passed / 2 failed to the final re-run result recorded by Remediation 0004.
- No Canonical or operator-guide update is required.

Error Handling Impact: No production behavior change. The existing `acceptance_catalog_limit_exceeded` contract remains unchanged.

## Files to Update

- `tests/Feature/AcceptanceOperatorCommandsTest.php`
- `tests/Feature/AcceptanceOperatorJsonContractTest.php`
- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007.md`
- `docs/project/ai/changes/CHANGES-INDEX.md`
- `docs/project/ai/remediations/REMEDIATION-PACK-TMS-PACK-0003-CLI-TEST-ISOLATION-0004.md`
- `docs/project/ai/remediations/REMEDIATIONS-INDEX.md`
- DK Pack 0003 lifecycle records after validation

## Required change request

Change Request 0007 records the Pack 0003 scale and compatibility findings. Remediation 0004 owns only this approved test-isolation correction.

## future-work

Shared importer/validator prevention, generated-source formatting policy, and bounded PCRE execution remain open in Change Request 0007.

## Review / Resolution

```text
Reviewed In: operator instruction on 2026-10-11
Review Result: add the affected tests to the current Pack recovery and correct them
Resolution: requires-remediation
Next Review At: DK Pack 0003 post-execution review
Resolution Notes: exact two-test correction and full-suite result validated and accepted; operator separately authorized commit on 2026-10-11
```

## Notes

This decision does not make unfiltered production catalog listing succeed above its declared limit.
