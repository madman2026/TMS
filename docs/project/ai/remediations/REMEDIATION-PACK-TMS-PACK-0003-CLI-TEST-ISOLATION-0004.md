# REMEDIATION-PACK-TMS-PACK-0003-CLI-TEST-ISOLATION-0004

Status: `accepted — focused, nearby, and full-suite validation passed and the operator accepted the remediation on 2026-10-11; commit authorized`

Generated: `2026-10-11`

Owner: TMS project

## 1. Task ID

`REMEDIATION-PACK-TMS-PACK-0003-CLI-TEST-ISOLATION-0004`

## 2. Task Title

Isolate generic Acceptance CLI contract tests from the Pack 0003 production catalog.

## 3. Related Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007.md`

Decision source: `docs/project/ai/decisions/TMS-DECISION-0019-pack-0003-generic-cli-test-isolation.md`.

## 4. Goal

Restore deterministic full-suite behavior after Pack 0003 without changing the production catalog or its bounded listing contract.

## 5. Current Problem

Two generic feature tests invoke unfiltered `acceptance:list` through the real application registry. Pack 0003 makes that registry contain 24,668 variants, so the command correctly returns `acceptance_catalog_limit_exceeded` under its default 1,000-row limit. The tests assert presentation/JSON contracts, not production catalog scale.

## 6. Required Change

Bind an empty `AcceptanceAppRegistry` inside the two exact test methods before invoking `acceptance:list`. Preserve every production service, limit, error code, and command contract.

## 7. Files to Read

- repository/project startup, profiles, and execution/testing rules;
- Decision 0019 and Change Request 0007;
- the two affected tests;
- `AcceptanceCatalog`, `AcceptancePlanner`, `ListAcceptanceApps`, and nearby command contract tests.

## 8. Files to Edit

- `tests/Feature/AcceptanceOperatorCommandsTest.php`
- `tests/Feature/AcceptanceOperatorJsonContractTest.php`
- directly related Decision, Change, Remediation, Pack lifecycle, and index records

## 9. Files to Create

- `docs/project/ai/decisions/TMS-DECISION-0019-pack-0003-generic-cli-test-isolation.md`
- `docs/project/ai/remediations/REMEDIATION-PACK-TMS-PACK-0003-CLI-TEST-ISOLATION-0004.md`
- a Run Report only after operator acceptance

## 10. Do Not Change

Do not change production Acceptance source, catalog/selector limits, error codes, DK mappings/catalog/skeletons, configuration, database, dependencies, Core, target/provider behavior, UI, API, environment, network, queue, or security controls.

## 11. Configuration / Settings Impact

No configuration or settings impact. No environment, Admin Setting, secret, queue, cache, logging, test setup, or operator setup change is required.

## 12. Multilingual / Translation Impact

No multilingual, translation, visible-text, message, or RTL/LTR impact.

## 13. UI / Admin UI Impact

No UI or Admin UI impact.

## 14. Implementation Rules

- Use the real empty `AcceptanceAppRegistry`, not a mock.
- Bind it inside only the two affected test methods.
- Preserve the tests' existing observable assertions.
- Do not alter production limits merely to make a test pass.

## 15. Architecture Constraints

Generic CLI contract tests own deterministic input. The DK owner retains its real catalog; project production services retain bounded traversal and result limits.

## 16. Data Model / Migration / Relationship Impact

No database, model, migration, relationship, index, snapshot, backfill, retention, or data-loss impact.

## 17. Validation Rules

- Both previously failing tests pass together.
- Nearby catalog/operation command contract tests pass.
- The full suite passes.
- A bounded real DK `acceptance:list` query remains successful.
- Unfiltered real DK listing remains allowed to return `acceptance_catalog_limit_exceeded`; no production behavior changes.
- scoped Pint and `git diff --check` pass.

## 18. Error Handling / Logging / Traceability Impact

No production error handling, logging, or traceability impact. `acceptance_catalog_limit_exceeded`, exit code 2, its classification, and safe output remain unchanged. No error-specific production tests are added or removed.

## 19. Tests to Add

No new test file. Correct method-local setup in the two existing feature tests.

## 20. Tests to Run

```text
php artisan test --compact tests/Feature/AcceptanceOperatorCommandsTest.php tests/Feature/AcceptanceOperatorJsonContractTest.php
php artisan test --compact tests/Feature/AcceptanceCatalogCommandTest.php tests/Feature/AcceptanceOperationCommandContractTest.php
php artisan test --compact
vendor/bin/pint --test tests/Feature/AcceptanceOperatorCommandsTest.php tests/Feature/AcceptanceOperatorJsonContractTest.php
git diff --check
```

## 21. Acceptance Checklist

- [x] exact two tests use method-local deterministic registries;
- [x] production source and limits remain unchanged;
- [x] focused and nearby regression tests pass;
- [x] full suite passes;
- [x] scoped Pint and diff check pass;
- [x] no external I/O or runtime mutation introduced;
- [x] no commit occurs without separate authorization.

Execution evidence on 2026-10-11:

- the two affected files passed together: 13 tests, 237 assertions;
- nearby catalog/operation command contracts passed: 14 tests, 456 assertions;
- the complete suite passed: 689 tests, 6,157 assertions;
- scoped Pint passed;
- `git diff --check` passed with only the existing non-failing CRLF-to-LF notices for retained large generated files;
- a bounded real DK listing succeeded before the correction, confirming the catalog itself was valid;
- no production source, limit, error code, target, provider, network, queue, dependency, cache, or environment behavior changed.

## 22. Rollback / Safety Notes

Rollback removes only the two imports and two method-local container bindings, then synchronizes directly related lifecycle records. No production, database, target, external-system, cache, queue, or dependency cleanup exists.

## 23. Operator Review Checklist

Before:

- [x] full-suite failure reproduced;
- [x] bounded real DK listing verified successful;
- [x] operator authorized the exact test correction and re-run;
- [x] commit remains separately gated.

After:

- [x] inspect the two method-local registry bindings;
- [x] inspect focused, nearby, and full-suite results;
- [x] accept or reject Remediation 0004 and the Pack 0003 result;
- [x] authorize commit separately if accepted.

## 24. Operator Documentation Impact

No operator-facing behavior changed. No guide or screenshot update is required.

## 25. Agent Final Report

Report the isolated tests, preserved production limit/error contract, focused/nearby/full-suite results, formatting/diff checks, scope classification, open Change Request 0007 work, and commit status.
