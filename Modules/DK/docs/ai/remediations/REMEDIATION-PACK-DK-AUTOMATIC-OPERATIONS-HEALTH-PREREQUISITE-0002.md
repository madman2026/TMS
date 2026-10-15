# REMEDIATION-PACK-DK-AUTOMATIC-OPERATIONS-HEALTH-PREREQUISITE-0002

Status: `accepted — validated and operator-accepted with scoped commit authorization on 2026-10-15`

Owner: DK

## 1. Task ID

`REMEDIATION-PACK-DK-AUTOMATIC-OPERATIONS-HEALTH-PREREQUISITE-0002`

## 2. Task Title

Resolve the Operations Health credential reference automatically and exactly.

## 3. Related Change Request

`Modules/DK/docs/ai/changes/CHANGE-REQUEST-DK-AUTOMATIC-OPERATIONS-HEALTH-PREREQUISITE-0005.md`

## 4. Goal

Let operators execute the 21-variant Operations Health group through Catalog selectors without obtaining Variant keys for setup or carrying prerequisite request IDs/files.

## 5. Current Problem

Every selected variant declares the same safe app-owned reference requirement, but DK does not expose an app resolver to the project prerequisite service.

## 6. Required Change

Declare app-reference resolution in `operations-health-v2`, add an exact resolver for the accepted hierarchy/catalog and existing handle, register it in `DKServiceProvider`, and prove automatic and explicit paths.

## 7. Files to Read

Project Decision 0025, project Remediation 0012, DK Pack 0004 and Run 001, DK Remediation 0001, component/catalog/provider/target handle source, and focused tests.

## 8. Files to Edit

- `Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php` — non-managed schema factory/imports only
- `Modules/DK/app/Providers/DKServiceProvider.php`
- `Modules/DK/tests/Feature/NotificationDeliveryOperationsHealthAcceptanceTest.php`
- applicable DK lifecycle indexes and this Remediation result

## 9. Files to Create

- `Modules/DK/app/Acceptance/Shared/Prerequisites/DKPrerequisiteReferenceResolver.php`
- focused resolver test if it does not fit the existing Operations Health feature test
- this Change Request and Remediation Pack

## 10. Do Not Change

Managed generated regions, the 21 identities/order, target-control resolver credential behavior, target source/config, fixture recipes, Browser runtime/selectors/oracles, database schema, dependencies, `.env`, Pack 0005, or any unrelated scenario.

## 11. Configuration / Settings Impact

No. Reuse the existing code-owned `LocalLoopbackSecretHandleResolver::HANDLE`; do not read configuration or environment values in the prerequisite resolver.

## 12. Multilingual / Translation Impact

No product visible text or translation change.

## 13. UI / Admin UI Impact

No graphical UI change. CLI user experience improves through the project Remediation.

## 14. Implementation Rules

Return a reference only when app/component/suite/scenario/schema/input and one of the 21 catalog Variant keys all match. Return no match otherwise. Reuse the existing catalog and handle constants. Register through `afterResolving` with duplicate-safe behavior. Never resolve the handle into a credential here.

## 15. Architecture Constraints

DK owns only its mapping adapter. Project code owns policy/persistence; target-control owns just-in-time credential conversion. No service locator, duplicated Variant list, fallback mapping, static mutable state, environment read, or target access.

## 16. Data Model / Migration / Relationship Impact

None. The schema version changes to `operations-health-v2`; old requests intentionally fail closed.

## 17. Validation Rules

Assert exact match/no-match behavior, all 21 schema policies, unrelated `none-v1` preservation, provider registration, existing explicit workflow, direct no-request execution data, one-request-per-batch-item behavior, managed-region digest/replay preservation, and no credential/reference leakage.

## 18. Error Handling / Logging / Traceability Impact

DK introduces no additional error code. No-match is normalized by project code to `prerequisite_reference_unavailable`. Resolver values must never be logged. Existing target-control errors remain unchanged.

## 19. Tests to Add

Exact resolver matrix, negative identity/input/schema cases, schema-v2 contract, automatic service integration, and explicit request regression.

## 20. Tests to Run

Syntax, scoped Pint, DK resolver/Operations Health/corpus replay tests, project prerequisite/run/batch tests, Pack 0004 regressions, full PHPUnit, safe scans, managed-region digest, and `git diff --check`. Live target/Browser proof is separately gated.

## 21. Acceptance Checklist

- [x] all 21 variants publish `operations-health-v2` and `app_reference` for `credential`;
- [x] only the exact tuple resolves the existing opaque handle;
- [x] unrelated variants and apps do not resolve;
- [x] provider registration is duplicate-safe;
- [x] no Variant list is duplicated and managed content is untouched;
- [x] automatic and explicit paths both pass;
- [x] no credential/reference leaks to unsafe output;
- [x] focused/full/static checks pass;
- [x] no commit occurs without separate authorization.

## 22. Rollback / Safety Notes

Revert only the listed DK source/tests/docs. Do not regenerate the corpus or delete test data. No target/Browser/server/network activity is needed for source implementation and automated validation.

## 23. Operator Review Checklist

Confirm exact 21-key scope, reuse of the existing opaque handle, v2 fail-closed behavior, no automatic approval, no target credential resolution, and the direct selector-based UX.

## 24. Operator Documentation Impact

The project Remediation updates the Persian CLI guide; no DK-local runbook change is required unless live validation reveals a target-specific operational delta.

## 25. Agent Final Report

Report exact scope, schema/resolver behavior, automatic and explicit test evidence, managed-region evidence, full-suite result, safe scans, Git status, and remaining live/acceptance/commit gates.

## 26. Execution Result

Implemented `operations-health-v2` with `app_reference` resolution for `credential`, an exact DK resolver bounded to the accepted App/Component/Suite/Scenario/schema/input tuple and the existing 21-key Catalog, and duplicate-safe provider registration. The resolver returns only the existing opaque local handle and never resolves it into a target credential. Explicit preparation and direct execution paths both pass, and unrelated contexts fail closed.

Validation completed on 2026-10-15. The final repository suite passed 827 tests with 7284 assertions, followed by a post-format focused rerun of 53 tests with 944 assertions. PHP syntax, Pint on every changed regular PHP file, sensitive-value scans, managed-region replay/digest preservation, and `git diff --check` passed. The 8.9 MB generated component cannot complete Pint under the default memory limit; its small non-managed diff was style-checked separately and its managed-region digest remained unchanged. No managed generated content, target source/configuration, database schema, dependency, or environment file was changed by this remediation. Live target/Browser execution remains a separate gate.
