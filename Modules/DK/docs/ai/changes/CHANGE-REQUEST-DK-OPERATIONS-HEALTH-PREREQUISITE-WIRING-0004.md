# CHANGE-REQUEST-DK-OPERATIONS-HEALTH-PREREQUISITE-WIRING-0004 — Complete Pack 0004 prerequisite wiring

## 1. Title

Correct the missing Operations Health credential-reference prerequisite on the exact 21 executable variants.

## 2. Status

Implemented and validated — bounded remediation, automated suites, exact 21-variant Browser execution, and cleanup passed. Operator accepted the result and authorized the scoped commit on 2026-10-15; push remains separately gated.

## 3. Related Release / Phase

TMS Acceptance roadmap / DK stage 14 / Pack 0004.

## 4. Decision Source

Accepted `DK-DECISION-0004`, accepted `DK-DECISION-0005`, Pack 0004's requirement that the validated prerequisite handle reach the runtime, and the Human Operator's remediation instruction on 2026-10-14.

## 5. Proposed Change

Keep the generated variant region unchanged. Add one replay-safe, non-managed override in `NotificationDeliveryAcceptanceComponent::variants()` that obtains the exact 21 keys from `OperationsHealthVariantCatalog` and returns each with one code-owned prerequisite schema:

- schema version: `operations-health-v1`;
- one required input named `credential`;
- type and sensitivity: `secret_reference`;
- accepted source: `secret_reference` only;
- no prerequisite approval scope.

All other scenarios continue through the generated region with `none-v1`.

## 6. Reason for Change

The Pack 0004 adapter, fixture, and Browser runtime correctly require `PrerequisiteExecutionData::secretReferences['credential']`, but the selected `VariantDescriptor` instances currently use the default empty schema. The official prerequisite workflow therefore cannot persist or deliver the required opaque handle, and real execution fails before target access even when the local target is otherwise ready.

## 7. Scope of Impact

Affected areas:

- DK Acceptance catalog wiring;
- prerequisite metadata for exactly 21 variants;
- focused DK feature/regression tests;
- DK lifecycle documentation.

No shared project/Core source, product UI, target source, database schema, API contract, queue, provider behavior, or external-I/O capability changes.

## 8. Affected Files

- `Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php`
- `Modules/DK/tests/Feature/NotificationDeliveryOperationsHealthAcceptanceTest.php`
- `Modules/DK/tests/Feature/NotificationDeliveryCorpusImportTest.php`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004.md` — lifecycle note only
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`
- this Change Request and `Modules/DK/docs/ai/changes/CHANGES-INDEX.md`
- Remediation 0001 and `Modules/DK/docs/ai/remediations/REMEDIATIONS-INDEX.md`

## 9. Backward Compatibility

Yes. Imported identities, mappings, ordering, executable disposition, catalog version `v4`, all 138 skeleton scenarios, and all target-control contracts remain unchanged. Existing prepared requests for these variants used schema `none-v1` and cannot safely be reinterpreted; the shared prerequisite service will reject them with the existing `schema_changed` behavior, so a fresh request is required.

## 10. Risk Level

Low. The change is code-owned metadata with no target access or product mutation. Main risks are variant drift, duplicate yields, or import replay erasing the override; focused tests cover all three.

## 11. Migration or Remediation Needed

Yes: `REMEDIATION-PACK-DK-OPERATIONS-HEALTH-PREREQUISITE-WIRING-0001`.

No database migration or data repair is required. Any stale prepared prerequisite request must be replaced, not migrated.

## 12. Required Human Approval

Approved for the bounded remediation and automated source validation by the Human Operator on 2026-10-14. This approval does not include target access, Browser execution, acceptance, commit, or push.

## 13. Acceptance Criteria

- Exactly the selected 21 variants expose `operations-health-v1` with one required `credential` secret-reference input.
- The remaining 24,647 imported variant mappings continue to expose `none-v1`.
- The official prerequisite service can prepare the selected identity as awaiting `credential` input and can produce execution data containing the submitted opaque reference.
- No managed generated line is manually edited.
- Import replay preserves the non-managed override and remains idempotent.
- Existing identity, cardinality, side-effect, syntax, formatting, focused-regression, full-suite, and diff checks pass.
- No target or Browser command is run under this remediation approval.

## 14. Rollback Plan

Remove the non-managed prerequisite override/imports and its exact regression assertions, restore Pack 0004 to blocked-before-execution, and leave generated regions untouched. No target or database cleanup is needed because remediation validation is source-only and test-isolated.

## 15. Final Decision

Approved for implementation and automated validation on 2026-10-14.

## 16. Required remediation

`Modules/DK/docs/ai/remediations/REMEDIATION-PACK-DK-OPERATIONS-HEALTH-PREREQUISITE-WIRING-0001.md`

## 17. Notes

The opaque value remains the already approved `app-secret://dk/local-acceptance-control-v1` reference. This record neither authorizes nor contains a session, lease context, credential value, or non-loopback target.

Implementation evidence: all 21 selected descriptors expose `operations-health-v1`; the official prepare/submit/execution-data workflow delivers the approved opaque reference; an unrelated variant retains `none-v1`; guarded planning reports exactly 21 executable variants; the managed variants block remains byte-identical to `HEAD`; focused/regression tests passed with 153 tests and 1,894 assertions; the full suite passed with 812 tests and 7,168 assertions. No target or Browser was accessed.
