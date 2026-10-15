# REMEDIATION-PACK-TMS-AUTOMATIC-PREREQUISITE-REFERENCES-0012

Status: `accepted — validated and operator-accepted with scoped commit authorization on 2026-10-15`

Owner: TMS project

## 1. Task ID

`REMEDIATION-PACK-TMS-AUTOMATIC-PREREQUISITE-REFERENCES-0012`

## 2. Task Title

Create and attach app-resolvable prerequisite references during Acceptance execution.

## 3. Related Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-AUTOMATIC-PREREQUISITE-REFERENCES-0018.md`

## 4. Goal

Make the safe path the short path: operators run an exact identity or a selector batch directly, while TMS resolves, validates, persists, and attaches app-owned opaque references automatically.

## 5. Current Problem

`PrerequisiteService::executionData()` rejects every required input when no request ID is supplied. Batch planning already knows each Variant, but the normal workflow still requires separate prepare/submit commands and request-ID transport for every selected identity.

## 6. Required Change

Add a fingerprinted `operator` / `app_reference` input-resolution policy; add a narrow resolver context, contract, and app-key registry; inject the registry into `PrerequisiteService`; automatically materialize a request only for fully automatic, approval-free schemas; retain explicit behavior otherwise; classify resolver failures with `prerequisite_reference_unavailable`; preserve safe rendering/logging; update the operator guide.

## 7. Files to Read

Decision 0009, Decisions 0021–0025, the current prerequisite/run/batch sources and tests, the Acceptance CLI guide, applicable project rules, and DK Remediation 0002.

## 8. Files to Edit

- `app/Acceptance/Prerequisites/Data/InputRequirement.php`
- `app/Acceptance/Prerequisites/PrerequisiteException.php`
- `app/Acceptance/Prerequisites/PrerequisiteService.php`
- `app/Acceptance/Operations/Data/OperationResult.php`
- `app/Acceptance/Operations/AcceptanceOperationService.php`
- `app/Console/Acceptance/OperationResultRenderer.php`
- `app/Providers/AppServiceProvider.php`
- `tests/Unit/PrerequisiteServiceTest.php`
- `tests/Unit/AcceptanceOperationServiceTest.php`
- `tests/Feature/AcceptancePrerequisiteWorkflowTest.php`
- `tests/Feature/AcceptanceRunCommandTest.php`
- `tests/Feature/AcceptanceAsyncBatchTest.php`
- `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`
- directly related lifecycle indexes and this Remediation status/result

If an exact named test file does not contain the relevant current boundary, use the nearest existing Acceptance run/batch feature test and record the substitution before editing.

## 9. Files to Create

- `app/Acceptance/Prerequisites/Enums/InputResolution.php`
- `app/Acceptance/Prerequisites/Data/PrerequisiteReferenceContext.php`
- `app/Acceptance/Prerequisites/Contracts/PrerequisiteReferenceResolver.php`
- `app/Acceptance/Prerequisites/PrerequisiteReferenceResolverRegistry.php`
- focused registry/context tests if coverage does not fit the listed prerequisite test
- this Decision, Change Request, and eventual Run Report

## 10. Do Not Change

Database migrations/schema, target-control credential resolution, target source, Core executor contracts, scenario identities, catalog traversal, Pack 0005, dependencies, `.env`, or unrelated formatting/refactoring. Do not introduce implicit latest-request lookup, automatic approvals, secret literals, raw credential persistence, or application-container lookup from domain code.

## 11. Configuration / Settings Impact

No configuration or environment change. Resolver registration is code-owned composition-root behavior.

## 12. Multilingual / Translation Impact

No product translation change. The Persian operator guide changes because the command workflow becomes shorter.

## 13. UI / Admin UI Impact

No graphical UI source change. CLI UX changes: happy-path runs omit prerequisite plumbing, while errors retain stable codes and correlation IDs.

## 14. Implementation Rules

1. Default every existing requirement to `operator` resolution.
2. Permit `app_reference` only for required `SECRET_REFERENCE` type/sensitivity/source requirements.
3. Include resolution policy in `semanticData()` and the schema fingerprint.
4. Use constructor injection and a duplicate-safe app-key registry; no service locator.
5. Auto-create only when all required inputs are app-resolvable and no required approval exists.
6. Create a distinct UUID request per exact execution identity and persist through existing models/transactions.
7. Validate resolver output through the existing normalization path before persistence.
8. Store only the opaque reference and safe system provenance; never store or render a resolved credential.
9. Preserve explicit `request_id`, manual input, approval, expiry, conflict, and schema-drift behavior.
10. Batch items must receive the created request ID from existing disposition materialization.

## 15. Architecture Constraints

The project owns policy and orchestration; Apps own mappings. A resolver receives only bounded typed context and returns an opaque reference or no match. The registry is resolved through the composition root. No static mutable state, global container lookup, environment access, target access, or cross-App fallback.

## 16. Data Model / Migration / Relationship Impact

No schema, relationship, index, backfill, or migration change. Existing prerequisite request/input tables are sufficient. New requests are auditable and expire under the existing TTL.

## 17. Validation Rules

Prove policy validation/fingerprinting, registry validation/duplicates, automatic prepare/execution persistence, distinct batch-item requests, explicit request compatibility, manual and approval blocking, fail-closed resolver behavior, safe result/log output, correlation/operation trace, and no raw reference or credential leakage into Test/Step/report payloads.

## 18. Error Handling / Logging / Traceability Impact

Add `prerequisite_reference_unavailable` as a failed, non-retryable, permanent, admin-action-required code. Logs may contain only operation/correlation and safe hierarchy identity already allowed by the operation boundary. The resolver value must not be logged. Existing `input_required`, `approval_required`, `schema_changed`, and explicit request errors remain unchanged.

## 19. Tests to Add

Behavioral tests for schema invariants; exact resolver lookup; automatic single run; automatic batch materialization; one request per item; manual/mixed/approval fallback; missing/declining/invalid/throwing resolver; explicit request regression; and safe output/log assertions.

## 20. Tests to Run

PHP syntax, scoped Pint, focused prerequisite/run/batch/operation tests, DK Remediation 0002 tests, affected Pack 0004 regressions, full PHPUnit, safe-value scans, `git diff --check`, and an exact selector-based CLI proof after automated suites pass. Target/Browser execution requires a separately reported live gate.

## 21. Acceptance Checklist

- [x] schema policy is explicit, validated, and fingerprinted;
- [x] resolver port/registry is injected and fail-closed;
- [x] exact run needs no prerequisite arguments for an automatic schema;
- [x] selector batch needs no Variant keys, request IDs, or prerequisite file;
- [x] every batch item receives its own persisted READY request;
- [x] manual inputs and approvals remain explicit;
- [x] no secret literal, target credential, or unsafe value is persisted/rendered/logged;
- [x] focused, regression, full-suite, format, syntax, scan, and diff checks pass;
- [x] guide and lifecycle records reflect the final behavior;
- [x] no commit occurs without separate authorization.

## 22. Rollback / Safety Notes

Rollback is source-only and leaves previously created bounded prerequisite audit rows to expire normally. Never delete broad test data or reset the database. Stop if existing Pack 0004 changes overlap unexpectedly.

## 23. Operator Review Checklist

Confirm the short command is the primary documented workflow; automatic resolution is opt-in and app-bounded; approvals are never automated; explicit/manual workflows remain; no target credential is exposed; no migration or dependency was added.

## 24. Operator Documentation Impact

Update `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md` with direct exact-run and selector-batch examples, explain that Catalog chooses Variant keys, and relegate prepare/submit/request-ID steps to manual/diagnostic schemas.

## 25. Agent Final Report

Report exact files, UX before/after, schema/error contracts, request linkage evidence, test counts/results, safe scans, Git status, deviations, and remaining target/Browser/acceptance/commit gates.

## 26. Execution Result

Implemented the explicit `operator` / `app_reference` policy, typed resolver context and contract, app-key registry, execution-owned request materialization, stable failure classification, DK exact resolver binding, direct selector-based batch UX, and operator-guide update. Automatic requests are distinct per execution identity, persisted as READY with safe resolver provenance, and correlated through the existing operation lifecycle. Manual inputs, approvals, and explicit request IDs remain explicit and compatible.

Validation completed on 2026-10-15. Focused suites exposed and corrected a missing import and a lifecycle-correlation assumption before passing. A first full-suite run had three transient Playwright Chromium launch/time-out failures; the isolated six-test Browser probe suite passed immediately, and two subsequent full-suite runs passed, with the final run at 827 tests and 7284 assertions. PHP syntax, Pint on every changed regular PHP file, sensitive-value scans, managed-region digest comparison, and `git diff --check` passed. The 8.9 MB generated component cannot complete Pint under the default memory limit; its small non-managed diff was style-checked separately, while the managed-region digest remained unchanged. No target application, external server, network transport, migration, dependency, configuration, or environment file was changed or accessed. Live target/Browser execution was not required for this source remediation and remains separately gated.
