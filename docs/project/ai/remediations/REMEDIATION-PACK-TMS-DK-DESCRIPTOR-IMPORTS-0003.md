# REMEDIATION-PACK-TMS-DK-DESCRIPTOR-IMPORTS-0003

Status: `accepted — exact import correction validated and accepted by the operator on 2026-10-11; commit authorized`

Generated: `2026-10-11`

Owner: TMS project

## 1. Task ID

`REMEDIATION-PACK-TMS-DK-DESCRIPTOR-IMPORTS-0003`

## 2. Task Title

Resolve DK Notification Delivery descriptor classes at runtime.

## 3. Related Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007.md`

Decision source: `docs/project/ai/decisions/TMS-DECISION-0018-targeted-dk-descriptor-import-remediation.md`.

## 4. Goal

Make the retained DK Pack 0003 catalog runtime-resolvable without changing its generated hierarchy, mappings, managed-region content, or scenario behavior.

## 5. Current Problem

The retained Notification Delivery component contains managed-region calls to `SuiteDescriptor`, `ScenarioDescriptor`, and `VariantDescriptor`, but its namespace imports do not resolve those classes from `App\Data`. Structural validation accepts the marker layout while runtime catalog construction fails.

## 6. Required Change

Add exactly three namespace imports to the Notification Delivery component:

- `App\Data\ScenarioDescriptor`;
- `App\Data\SuiteDescriptor`;
- `App\Data\VariantDescriptor`.

Do not move managed markers, rewrite generated lines, or change the shared importer/validator in this remediation.

## 7. Files to Read

- repository and DK startup/profile files;
- Decision 0018 and Change Request 0007;
- DK Pack 0003 and its current component/tests;
- the shared descriptor classes, importer, and validator only as runtime references.

## 8. Files to Edit

- `Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007.md`
- `docs/project/ai/changes/CHANGES-INDEX.md`
- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/remediations/REMEDIATION-PACK-TMS-DK-DESCRIPTOR-IMPORTS-0003.md`
- `docs/project/ai/remediations/REMEDIATIONS-INDEX.md`
- DK Pack 0003 lifecycle/index/reference records only after validation

## 9. Files to Create

- `docs/project/ai/decisions/TMS-DECISION-0018-targeted-dk-descriptor-import-remediation.md`
- a remediation Run Report only after post-execution operator approval

## 10. Do Not Change

Do not change managed-region content, source mappings, scenario skeletons, shared importer/validator/runtime code, error codes, configuration, dependencies, database, environment files, Core, target/provider behavior, executable Steps, UI, API, MCP, network, queue, or security controls.

## 11. Configuration / Settings Impact

No configuration or settings impact. No configuration, environment variable, Admin Setting, secret, queue, cache, logging, development setup, or operator setup change is required.

## 12. Multilingual / Translation Impact

No multilingual, translation, visible-text, validation-message, or RTL/LTR impact.

## 13. UI / Admin UI Impact

No UI or Admin UI impact.

## 14. Implementation Rules

- Add only the three approved `App\Data` imports in project import order.
- Preserve every managed marker and its contents byte-for-byte except for line-ending normalization already present in the retained Pack output.
- Preserve all Pack 0003 mappings, hierarchy keys, counts, and non-executable scenario skeletons.
- Do not claim the shared importer or validator is corrected.

## 15. Architecture Constraints

Shared descriptor classes remain project-owned under `App\Data`; the DK component may reference them through normal PHP namespace imports. No owner boundary or hierarchy contract changes.

## 16. Data Model / Migration / Relationship Impact

No database, model, migration, relationship, index, snapshot, backfill, retention, rollback-data, or data-loss impact.

## 17. Validation Rules

- PHP lint passes for the corrected component.
- The component can construct its suites, scenarios, and variants at runtime.
- Target-module validation reports DK valid.
- DK catalog reports version `v3`, 10 suites, 139 scenarios, and 24,668 variants.
- Coverage reports 24,668 `automated_full` mappings and zero other dispositions.
- Pack 0003 focused and affected regression tests pass.
- `git diff --check` passes apart from non-failing line-ending notices.
- No external I/O or executable scenario behavior is introduced.

## 18. Error Handling / Logging / Traceability Impact

No error handling, logging, or traceability impact. No API/UI error, stable error code, exception mapping, retry classification, log event, trace identifier, sensitive-data behavior, or error-specific test contract changes.

Existing defective behavior: runtime catalog construction throws a class-not-found error for the first unqualified descriptor reference.

Required corrected behavior: the existing descriptor references resolve to the accepted `App\Data` classes. Previously stored data, logs, API consumers, UI workflows, and history are unaffected.

## 19. Tests to Add

No new test file. Existing DK catalog/integration tests already exercise descriptor construction and detect the defect.

## 20. Tests to Run

```text
php -l Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php
php -d memory_limit=2G vendor/bin/phpunit Modules/DK/tests/Unit/DKAcceptanceAppTest.php Modules/DK/tests/Feature/NotificationDeliveryComponentIntegrationTest.php tests/Feature/DKModuleBootstrapTest.php Modules/DK/tests/Unit/Acceptance/Coverage/SourceCaseMappingsTest.php Modules/DK/tests/Feature/NotificationDeliveryCorpusImportTest.php
php -d memory_limit=2G artisan acceptance:app:validate DK --json
php -d memory_limit=2G artisan acceptance:coverage dk --json
php artisan test --compact tests/Unit/TargetModuleCreatorTest.php tests/Unit/TargetModuleValidatorTest.php tests/Unit/CoverageTraceabilityServiceTest.php tests/Feature/TargetModuleOperationServiceTest.php
vendor/bin/pint --test Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php Modules/DK/tests/Unit/DKAcceptanceAppTest.php Modules/DK/tests/Feature/NotificationDeliveryComponentIntegrationTest.php tests/Feature/DKModuleBootstrapTest.php
git diff --check
```

The large generated mapping file remains outside this remediation's formatter scope. Shared formatting/scalability policy remains open in Change Request 0007.

## 21. Acceptance Checklist

- [x] exact three imports added;
- [x] managed regions unchanged by remediation;
- [x] runtime catalog resolves and exact counts match;
- [x] coverage reconciles all mappings;
- [x] focused and affected regression tests pass;
- [x] lint, scoped Pint, and diff check pass;
- [x] no external I/O or scenario execution introduced;
- [x] shared prevention remains explicitly open;
- [x] no commit occurs without separate authorization.

Execution evidence on 2026-10-11:

- component lint passed;
- five DK/root catalog and corpus test files passed: 8 tests, 498 assertions;
- shared importer/validator/coverage regressions passed: 22 tests, 97 assertions;
- target-module validation returned `valid=true` with zero issues;
- coverage returned catalog `v3` and `24668/0/0/0/0`;
- scoped Pint passed;
- `git diff --check` passed with non-failing CRLF-to-LF notices for the two retained large generated files;
- no target, provider, browser, network, queue, database, dependency, cache, environment, or executable scenario operation ran.

## 22. Rollback / Safety Notes

Rollback removes only the three added import lines and synchronizes the directly related lifecycle records. Do not reset or remove the retained Pack 0003 corpus output. No database, target, external system, cache, queue, or dependency cleanup exists.

## 23. Operator Review Checklist

Before:

- [x] operator approved retaining the safe partial Pack 0003 output;
- [x] operator approved the exact descriptor import correction;
- [x] Decision 0018 records why the executed Pack scope is not rewritten;
- [x] commit remains separately gated.

After:

- [x] inspect the exact three imports and unchanged managed markers;
- [x] inspect runtime catalog and coverage totals;
- [x] inspect focused/regression, lint, Pint, and diff evidence;
- [x] accept or reject the remediation and Pack 0003 result;
- [x] authorize commit separately if accepted.

## 24. Operator Documentation Impact

No operator-facing behavior changed. No operator guide or screenshot update is required.

## 25. Agent Final Report

Report the exact imports, runtime/catalog/coverage results, tests and checks, unchanged managed regions, open shared prevention work, lifecycle/index synchronization, scope classification, and commit status.
