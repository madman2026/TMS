# REMEDIATION-PACK-DK-OPERATIONS-HEALTH-PREREQUISITE-WIRING-0001

Status: Accepted — automated suites, exact 21-variant Browser execution, and cleanup passed; operator accepted the result and authorized the scoped commit on 2026-10-15. Push remains separately gated.

## 1. Task ID

`REMEDIATION-PACK-DK-OPERATIONS-HEALTH-PREREQUISITE-WIRING-0001`

## 2. Task Title

Wire the approved credential-reference prerequisite into the exact Pack 0004 variant set.

## 3. Related Change Request

`CHANGE-REQUEST-DK-OPERATIONS-HEALTH-PREREQUISITE-WIRING-0004`

## 4. Goal

Make the existing public prerequisite workflow capable of producing the exact `PrerequisiteExecutionData` already required by the Pack 0004 target adapter, fixture, and Browser runtime, without changing generated variant lines or expanding executable coverage.

## 5. Current Problem

Every imported variant line constructs `VariantDescriptor` without a schema, so it receives `PrerequisiteSchema::none()`. For the selected Operations Health group this conflicts with the already implemented requirement for `secretReferences['credential']`. A prepared request cannot accept that input and real execution deterministically fails with `resource_unavailable` before Browser start.

## 6. Required Change

- Add a non-managed early branch to `NotificationDeliveryAcceptanceComponent::variants()` for only `nd-component-operations-health`.
- Obtain the ordered keys from `OperationsHealthVariantCatalog`; do not duplicate the 21-key list.
- Attach one immutable `operations-health-v1` schema with one required `credential` secret-reference input to all 21 descriptors.
- Return after the bounded branch so the managed imported lines are not yielded a second time.
- Preserve the generated region byte-for-byte and prove replay preservation.
- Add regression coverage for exact schema shape, unaffected variants, and the persisted prerequisite workflow.

## 7. Files to Read

- `AGENTS.md`
- `Modules/DK/AGENTS.md`
- `docs/ai/rules/SCOPE-CONTROL-RULES.md`
- `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`
- `docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md`
- `Modules/DK/docs/ai/decisions/DK-DECISION-0004-target-control-binding-and-operations-health-recipes.md`
- `Modules/DK/docs/ai/decisions/DK-DECISION-0005-local-loopback-target-profile.md`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004.md`
- the source and tests listed below

## 8. Files to Edit

- `Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php` — non-managed imports, early variant branch, and private schema factory only
- `Modules/DK/tests/Feature/NotificationDeliveryOperationsHealthAcceptanceTest.php`
- `Modules/DK/tests/Feature/NotificationDeliveryCorpusImportTest.php`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004.md` — append-only lifecycle/remediation note and status only
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`
- `Modules/DK/docs/ai/changes/CHANGES-INDEX.md`
- `Modules/DK/docs/ai/remediations/REMEDIATIONS-INDEX.md`

## 9. Files to Create

- `Modules/DK/docs/ai/changes/CHANGE-REQUEST-DK-OPERATIONS-HEALTH-PREREQUISITE-WIRING-0004.md`
- this Remediation Pack

No new PHP class is required.

## 10. Do Not Change

- any managed generated line between `<tms:...>` markers;
- any scenario or variant outside the exact 21-key Operations Health set;
- catalog version `v4`, imported identity, source mapping, or scenario disposition;
- shared project/Core source or tests;
- DK target-control, fixture, runtime, selectors, recipe, observation, or error contracts;
- `.env`, dependencies, database migrations, target source, Browser setup, or external network;
- Pack 0005 or its stage-15 scope.

## 11. Configuration / Settings Impact

```text
Configuration / Settings Impact: No
Config Files Changed: No
Environment Variables Required: No
Admin Settings Changed: Not applicable
External Integration / Secret Settings Changed: No
Queue / Cache / Logging Settings Changed: No
Test / Development Setup Required: No
Operator Manual Setup Required: No for remediation validation; existing Pack 0004 setup remains required for later real execution
```

No configuration or settings impact. No configuration follow-up is added by this remediation.

## 12. Multilingual / Translation Impact

```text
Multilingual / Translation Impact: No
Visible Text Changed: No
Admin UI Labels Changed: Not applicable
API Messages Changed: Not applicable
Validation Messages Changed: No
Translation Files Changed: No
RTL/LTR Impact: No
Human Review for Locale Wording Required: No
```

No multilingual or translation impact.

## 13. UI / Admin UI Impact

```text
UI / Admin UI Impact: No
Admin UI Changed: No
Settings UI Changed: Not applicable
Tables / Forms Changed: Not applicable
Actions / Buttons Changed: Not applicable
Menus / Navigation Changed: Not applicable
Dashboard / Monitoring UI Changed: No
Permission-gated UI Changed: No
```

No UI or Admin UI impact.

## 14. Implementation Rules

1. Follow the existing code-owned `PrerequisiteSchema` and `InputRequirement` types; introduce no parallel validation.
2. Reuse `OperationsHealthVariantCatalog` as the single 21-key source.
3. Keep the schema value-free; it declares only the required opaque reference contract.
4. Use `InputType::SECRET_REFERENCE` and `InputSensitivity::SECRET_REFERENCE`; rely on the existing default allowed-source constraint.
5. No approval requirement is added because target/Browser permission remains an external operator gate, not a persisted prerequisite approval contract.
6. Use a non-managed early return so importer replay cannot erase the correction and generated lines remain unchanged.
7. The test must fail against the defective implementation and assert known exact schema values.

## 15. Architecture Constraints

The component owns scenario-specific variant metadata; the shared prerequisite service remains unchanged. Generated corpus data stays generated, while the bounded executable override stays in non-managed source. No service locator, environment read, secret value, static mutable state, or duplicated key list is allowed.

## 16. Data Model / Migration / Relationship Impact

```text
Data / Migration Impact: No
New Tables Required: No
Existing Tables Modified: No
Foreign Keys Changed: Not applicable
Model Relationships Changed: Not applicable
Indexes / Unique Constraints Changed: Not applicable
Data Backfill Required: No
Rollback Impact: No data rollback
Data-loss Risk: No
```

No database, relationship, snapshot, migration, backfill, or historical-data change is required. Existing requests with `none-v1` are intentionally rejected by existing schema-fingerprint/version checks; operators create fresh requests.

## 17. Validation Rules

- Assert all 21 selected descriptors have the exact schema/version/input contract and unique ordered keys.
- Assert an unrelated imported variant retains `none-v1` and no inputs.
- Through `PrerequisiteService`, assert prepare reports `credential` missing, literal submission is rejected, secret-reference submission reaches READY, and `executionData()` returns the exact opaque reference under `credential` without exposing a value.
- Assert the managed variants block is unchanged by comparing its pre/post digest in the replay test and by running the import replay test.
- Run PHP syntax, scoped Pint check, focused tests, affected Pack 0004 regressions, full PHPUnit, safe scans, and `git diff --check`.
- Do not run any target, Browser, server, queue, cache, config, dependency, or external-network command.

## 18. Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: No
UI / Admin UI Error Impact: No
Stable Error Code Impact: No — existing codes are reused unchanged
Exception Handling Impact: No
External Error Normalization Impact: No
Callback / Inbound-event Error Impact: No
Retry / Permanent Failure Impact: No
Message / Attempt Status Impact: No
Structured Logging Impact: No
Traceability Impact: Yes — prerequisite request identity now reaches the already accepted runtime path
Sensitive Data Impact: Yes — existing opaque-reference handling is exercised, not changed
Error-specific Test Impact: Yes
Operator Documentation Impact: No
```

### Existing Defect and Required Correction

Existing defective behavior: the executable variants publish `none-v1`; `executionData()` cannot contain `credential`; the DK target boundary returns existing `resource_unavailable` before target access.

Evidence: production component source, `VariantDescriptor` default behavior, `PrerequisiteService::executionData()`, and the exact checks in `DKTargetResourceAdapter`, `OperationsHealthFixture`, and `DKBrowserTargetRuntimeResolver`.

Root cause: Pack 0004 implemented the consumer side of the accepted prerequisite contract but omitted its producer-side variant schema.

Required corrected behavior: selected variants require an opaque `credential` secret reference and the official workflow supplies it. Missing input remains `input_required`; literals remain `secret_literal_forbidden`; stale pre-remediation requests remain `schema_changed`; invalid/missing runtime references retain `resource_unavailable`.

No new exception, stable code, HTTP/API response, retry classification, status transition, or structured log is introduced. No raw reference value may appear in logs, reports, persisted JSON values, or test output.

Compatibility: no API client, product record, message/attempt history, or log history changes. No migration, repair, reprocessing, historical cleanup, or credential rotation is required.

No error-handling, logging, traceability, or sensitive-data follow-up is required after automated validation.

## 19. Tests to Add

Extend `NotificationDeliveryOperationsHealthAcceptanceTest` to prove the exact schema contract for all 21 descriptors and `none-v1` preservation outside the group.

Extend `NotificationDeliveryCorpusImportTest` to prove the non-managed variant override survives an isolated import replay and that managed generated content is not manually replaced.

Use an existing prerequisite workflow feature test pattern, within the Pack-owned feature file, to prove the prepare/submit/execution-data path if it can be added without broad setup or unrelated shared-source edits.

## 20. Tests to Run

- `php -l` for the edited PHP files.
- scoped `vendor/bin/pint --test` for the edited PHP files.
- `php artisan test Modules/DK/tests/Feature/NotificationDeliveryOperationsHealthAcceptanceTest.php`
- `php artisan test Modules/DK/tests/Feature/NotificationDeliveryCorpusImportTest.php`
- Pack 0004 focused DK test set.
- applicable shared prerequisite and catalog regression tests.
- full `php artisan test`.
- safe source scans and `git diff --check`.

## 21. Acceptance Checklist

- [x] exact 21 variants publish `operations-health-v1`;
- [x] exact `credential` secret-reference requirement is present and required;
- [x] unrelated variants remain `none-v1`;
- [x] official prerequisite execution data contains the submitted opaque reference;
- [x] managed generated lines are unchanged and replay remains idempotent;
- [x] approved isolated target/Browser validation completed with no Provider or external-network I/O;
- [x] focused, regression, full-suite, formatting, syntax, scan, and diff checks pass;
- [x] lifecycle indexes and Pack 0004 status accurately report remediation outcome;
- [x] operator accepted the result and authorized the scoped commit on 2026-10-15.

## 22. Rollback / Safety Notes

Rollback removes only the non-managed branch/imports and matching test assertions. Never edit or regenerate the managed corpus region as rollback. Automated tests must prevent stray HTTP and must not boot a target or Browser.

## 23. Operator Review Checklist

- Confirm only the exact 21 variants gained a prerequisite.
- Confirm the schema contains a reference, not a secret value.
- Confirm no persisted approval requirement was invented.
- Confirm generated regions and Pack 0005 were untouched.
- Confirm real target access remains gated after remediation.

## 24. Operator Documentation Impact

```text
Operator Documentation Impact: No
Operator Documentation Update Required: Not applicable
Guide File:
- `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`
```

No operator-facing behavior is changed beyond making the already documented prerequisite workflow usable for Pack 0004. No screenshot or guide update is required.

## 25. Agent Final Report

Report exact files, schema version and shape, tests and results, managed-region/replay evidence, safety scans, lifecycle updates, remaining target/Browser gates, and any deviation. Do not include sensitive or access-bearing values beyond the already approved opaque local handle.

## 26. Execution Result

Completed on 2026-10-14. The non-managed branch uses `OperationsHealthVariantCatalog` as the sole ordered key source and attaches `operations-health-v1` with one required `credential` secret-reference input. Tests prove literal secret rejection, safe reference persistence/delivery, unaffected `none-v1` variants, import replay preservation, and unchanged managed variant content.

Validation results:

- syntax and scoped Pint checks passed;
- focused remediation tests: 5 tests, 688 assertions;
- Pack 0004 and shared prerequisite/runtime regressions: 153 tests, 1,894 assertions;
- full PHPUnit suite: 812 tests, 7,168 assertions;
- guarded `acceptance:plan` for the exact hierarchy matched 21 variants, all 21 executable and none excluded;
- managed variants block equals `HEAD`, SHA-256 `a7a5786701f9eb50af62f2ba9c8424543830850ae798b51c94d3b3e2bd27eef9`;
- `git diff --check` passed;
- no target, Browser, server, queue worker, cache/config mutation, dependency command, or external network was used.

No Run Report was created because this remediation is source-only and the Pack 0004 real target/Browser Run remains separately gated.
