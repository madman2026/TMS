# REMEDIATION-PACK-TMS-TARGET-IMPORT-HARDENING-0006 — Target import preflight, scale and generated-format hardening

Status: `accepted — scoped, scale, DK, and full-suite validation passed; operator accepted and authorized commit 2026-10-15`

## 1. Task ID

`REMEDIATION-PACK-TMS-TARGET-IMPORT-HARDENING-0006`

## 2. Task Title

Prevent runtime-invalid generated target imports and remove undeclared scale/format assumptions.

## 3. Related Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007.md`

Decision sources: accepted Decisions 0015 and 0018–0019. This Remediation closes only the still-open shared importer/validator prevention and scale/format scope.

## 4. Goal

Make dry-run/preflight reject generated component source that cannot resolve the shared descriptor contract, process a 24,668-mapping import under default process limits without a PCRE override, and establish a deterministic validation policy for large generated managed regions.

## 5. Current Problem

DK Pack 0003 proved that dry-run/apply/replay and structural validation could succeed while generated component source lacked required `SuiteDescriptor`, `ScenarioDescriptor` and `VariantDescriptor` imports. The same run required an undocumented process-local `pcre.backtrack_limit` increase and could not apply ordinary Pint formatting to the large generated managed representation. Targeted Remediations 0003–0004 repaired the current DK output and generic CLI tests but did not prevent recurrence.

## 6. Required Change

1. Validate the exact generated descriptor imports and symbols from candidate component content before any write.
2. Pass the complete in-memory candidate set through `TargetModuleValidator::validatePath(...)` during dry-run and apply preflight; never rely on loading target classes.
3. Replace regex operations whose work grows with a complete managed region by a bounded line/marker scanner with exact single start/end marker enforcement.
4. Preserve deterministic ordering, idempotent replay, collision checks and targeted staging rollback.
5. Adopt this generated-format policy: ordinary handwritten source in the allowlist must pass Pint; generated managed-region rows are canonical generator output and are not rewritten by Pint. They are instead protected by PHP syntax, exact emitter-shape, no-trailing-whitespace, deterministic hash/replay and validator tests.
6. Keep the active CLI guide's correct positional `acceptance:coverage <app>` form. Historical accepted Pack/Run command records are immutable and are not rewritten.

## 7. Files to Read

- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007.md`
- accepted Remediations 0003–0004 and their Runs;
- `app/Acceptance/Modules/NwidartTargetModuleCreator.php`
- `app/Acceptance/Modules/TargetModuleValidator.php`
- the exact tests in sections 8–9;
- `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md` for read-only command-shape verification.

## 8. Files to Edit

- `app/Acceptance/Modules/NwidartTargetModuleCreator.php`
- `app/Acceptance/Modules/TargetModuleValidator.php`
- `app/Acceptance/Operations/Data/ValidationIssue.php`
- `tests/Unit/TargetModuleCreatorTest.php`
- `tests/Unit/TargetModuleValidatorTest.php`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007.md`
- `docs/project/ai/remediations/REMEDIATIONS-INDEX.md`
- after an approved execution, `docs/project/ai/runs/RUNS-INDEX.md`

## 9. Files to Create

- `tests/Unit/TargetModuleImportScaleTest.php`
- after an approved execution, `docs/project/ai/runs/REMEDIATION-PACK-TMS-TARGET-IMPORT-HARDENING-0006-run-001.md`

## 10. Do Not Change

Generated DK source, DK/N-D runtime behavior, scenario Steps, catalog identities/mappings, CLI signatures, database/config/environment, Browser, API, queue, dependencies, `.env`, accepted historical Packs/Runs or production listing limits.

## 11. Configuration / Settings Impact

```text
Configuration / Settings Impact: No
Config Files Changed: No
Environment Variables Required: No
Admin Settings Changed: Not applicable
External Integration / Secret Settings Changed: No
Queue / Cache / Logging Settings Changed: No
Test / Development Setup Required: Yes — test-owned temporary module roots only
Operator Manual Setup Required: No
```

No `ini_set`, CLI `-d pcre.backtrack_limit`, `.env` access or committed runtime setting is allowed. Tests use default process limits and delete verified temporary roots in `finally`/teardown.

## 12. Multilingual / Translation Impact

No multilingual, translation or RTL/LTR impact. The Persian CLI guide is read-only evidence because its current positional coverage syntax is already correct.

## 13. UI / Admin UI Impact

No UI or Admin UI impact.

## 14. Implementation Rules

- Parse PHP source safely without executing or autoloading target code.
- Validate required `use App\Data\SuiteDescriptor`, `ScenarioDescriptor` and `VariantDescriptor` imports whenever corresponding generated constructors occur.
- Reject missing, aliased-to-wrong-symbol, duplicate or namespace-conflicting imports before write using stable validation issue `target_module_descriptor_import_invalid`.
- Managed-region scanning must be linear in input size, reject duplicate/reversed/nested markers and never consume text outside the exact region.
- Dry-run and apply must validate the same candidate bytes. Apply writes only after the full candidate validates.
- Do not weaken existing hierarchy, path containment, source-mapping, collision, replay or staging cleanup checks.

## 15. Architecture Constraints

The validator remains static and must not boot a target provider or scenario. The creator owns deterministic generation/staging; the validator owns candidate validity. Generated managed regions remain source-controlled, replayable artifacts but are validated by generator-specific contracts rather than general formatting rewrites.

## 16. Data Model / Migration / Relationship Impact

```text
Data / Migration Impact: No
New Tables Required: No
Existing Tables Modified: No
Foreign Keys Changed: Not applicable
Model Relationships Changed: Not applicable
Indexes / Unique Constraints Changed: Not applicable
Data Backfill Required: No
Rollback Impact: Source-only
Data-loss Risk: No
```

No database or snapshot change.

## 17. Validation Rules

- Missing descriptor imports fail on dry-run before any file write.
- Correct imports and all existing valid fixtures continue to pass.
- A synthetic 139-scenario/24,668-mapping import completes dry-run, apply and replay under default PCRE settings.
- Replay has zero creates/updates and byte-identical managed files.
- Managed-region duplicate/reversed/nested/missing markers fail safely.
- Candidate validation and apply use identical content hashes.
- Generated rows have deterministic order, canonical exact shape, no trailing whitespace and valid PHP syntax.
- Current DK generated source remains untouched.

## 18. Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: Not applicable
UI / Admin UI Error Impact: No
Stable Error Code Impact: Yes — one validator issue code
Exception Handling Impact: Yes
External Error Normalization Impact: Not applicable
Callback / Inbound-event Error Impact: Not applicable
Retry / Permanent Failure Impact: permanent invalid input
Message / Attempt Status Impact: Not applicable
Structured Logging Impact: No
Traceability Impact: Yes — safe relative path only
Sensitive Data Impact: No new data; omission retained
Error-specific Test Impact: Yes
Operator Documentation Impact: No
```

Existing defective behavior: an import could validate structurally and fail only when catalog code loaded; valid large input could surface misleading `target_module_path_collision` because PCRE exhausted its default budget.

Corrected behavior: candidate source fails before write with `target_module_descriptor_import_invalid` when descriptor imports are invalid; malformed markers retain `target_module_managed_region_invalid`; unexpected generation/infrastructure failures retain existing safe creator exception normalization. Do not expose source contents or raw exceptions—only stable code and safe relative path.

No API/UI/log contract changes. No retry is appropriate for deterministic invalid source. A resource/transient filesystem failure remains governed by existing creator behavior.

## 19. Tests to Add

### `TargetModuleValidatorTest` additions

- Purpose: prove descriptor imports are validated statically.
- Main assertions: missing/wrong/duplicate/conflicting imports produce exactly `target_module_descriptor_import_invalid` and safe relative path; valid imports pass; no class is loaded.
- Negative cases: constructor token in comments/string does not create a false requirement; fully qualified approved constructor is handled according to the exact accepted emitter contract.

### `TargetModuleCreatorTest` additions

- Purpose: prove dry-run/apply candidate parity and pre-write failure.
- Main assertions: invalid candidate changes no file; valid import applies once and replays byte-identically; duplicate/reversed/nested markers fail with the correct safe code; staging cleanup stays targeted.

### `TargetModuleImportScaleTest`

- Purpose: prove supported current-corpus scale without process overrides.
- Behavior proven: deterministic 139 scenarios and 24,668 unique mappings can dry-run, apply, validate and replay using default `pcre.backtrack_limit`.
- Main assertions: exact counts, valid catalog hierarchy/mappings, zero replay writes, equal hashes, canonical managed rows, no trailing whitespace and no modified global ini value.
- Required setup: a verified test-owned temporary module root and synthetic identifiers only.
- Expected side effects: temporary files only, removed after the test.
- Negative cases: one duplicate identity and one malformed marker fail before target write.

## 20. Tests to Run

After separate pre-execution approval:

```powershell
php -l app/Acceptance/Modules/NwidartTargetModuleCreator.php
php -l app/Acceptance/Modules/TargetModuleValidator.php
php -l app/Acceptance/Operations/Data/ValidationIssue.php
php -l tests/Unit/TargetModuleCreatorTest.php
php -l tests/Unit/TargetModuleValidatorTest.php
php -l tests/Unit/TargetModuleImportScaleTest.php
php vendor/bin/pint --test app/Acceptance/Modules/NwidartTargetModuleCreator.php app/Acceptance/Modules/TargetModuleValidator.php app/Acceptance/Operations/Data/ValidationIssue.php tests/Unit/TargetModuleCreatorTest.php tests/Unit/TargetModuleValidatorTest.php tests/Unit/TargetModuleImportScaleTest.php
php vendor/bin/phpunit --do-not-cache-result tests/Unit/TargetModuleCreatorTest.php tests/Unit/TargetModuleValidatorTest.php tests/Unit/TargetModuleImportScaleTest.php tests/Unit/AcceptanceHierarchyTest.php
php artisan acceptance:app:validate DK --json
php artisan acceptance:coverage dk --json
php artisan test --compact
git diff --check
```

Command classification: syntax/Pint-check/Git inspection are non-mutating validation; PHPUnit/Artisan validation may create test-only temporary state and require the execution gate. The DK validate/coverage commands are read-only. No import/apply against DK, Browser, database, worker, dependency or external-network command is authorized.

## 21. Acceptance Checklist

- [x] exact allowlist preserved;
- [x] descriptor imports fail before write when invalid;
- [x] no target class/provider execution occurs in validation;
- [x] managed-region parsing is bounded and marker-safe;
- [x] 139/24,668 scale passes without PCRE override;
- [x] dry-run/apply/replay candidate hashes and counts pass;
- [x] generated-format policy assertions pass;
- [x] current DK source remains unchanged and validates 139/24,668;
- [x] focused and full suites pass;
- [x] Run/index/change lifecycle synchronized;
- [x] no commit without separate authorization.

Execution evidence: the final focused set passed 30 tests and 74,303 assertions in 77.313 seconds using 166 MB; the synthetic scale slice passed 139 scenarios and 24,668 mappings under unchanged default PCRE settings; DK validation and exact coverage passed with an unchanged tracked-index hash. After the operator resolved the external full-suite blocker, the final repository-wide run passed 846 tests and 81,530 assertions in 115.92 seconds. Three verified stale synthetic test roots were removed and the post-suite residue count is zero. See Run 001 for the exact evidence.

## 22. Rollback / Safety Notes

Revert only the three shared source files, three test files and lifecycle records from this Pack. Temporary test roots must be verified beneath the test-owned prefix before deletion. Do not restore/remove current DK generated files, modules, mappings or user changes. No data rollback exists.

## 23. Operator Review Checklist

Execution approval was received for the exact six source/test paths, validator issue code, linear marker scanner, managed-region Pint exemption/canonical-generator policy, scale test and full-suite command. The operator reviewed and accepted the final scoped, scale, DK, full-suite, cleanup, diff, and lifecycle result on 2026-10-15 and separately authorized commit.

## 24. Operator Documentation Impact

No operator-facing runtime behavior changes. The active Persian CLI guide already uses positional `acceptance:coverage <app>` syntax and remains unchanged. Historical accepted Pack/Run command records are audit evidence and remain immutable.

## 25. Agent Final Report

Report exact files, parser/import policy, issue-code behavior, scale/time/memory evidence, default PCRE proof, deterministic hashes/counts, tests/results, DK no-change evidence, deviations, lifecycle updates and commit status.
