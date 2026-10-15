# REMEDIATION-PACK-DK-PROCESS-LOCAL-TARGET-ACTIVATION-0003

Status: `accepted — automated and exact 21-variant live validation passed; operator accepted the result and authorized the scoped commit on 2026-10-15`

Owner: DK

## 1. Task ID

`REMEDIATION-PACK-DK-PROCESS-LOCAL-TARGET-ACTIVATION-0003`

## 2. Task Title

Provide a one-value, process-local activation gate for the accepted DK testing target.

## 3. Related Change Request

`Modules/DK/docs/ai/changes/CHANGE-REQUEST-DK-PROCESS-LOCAL-TARGET-ACTIVATION-0006.md`

## 4. Goal

Allow the exact Pack 0004 Operations Health matrix to run without editing source or `.env`, while retaining fail-closed defaults.

## 5. Current Problem

Batch 3 created and attached all 21 automatic prerequisite requests, then every item stopped at readiness with `unsafe_target` because DK config is hard-coded to disabled/unknown.

## 6. Required Change

Map only `DK_ACCEPTANCE_TARGET_PROFILE=local-testing` to enabled/testing in DK config; reject all other profile values by mapping them to disabled/unknown; add focused configuration/integration coverage and operator instructions.

## 7. Files to Read

DK Decisions 0001, 0005, 0006; Pack 0004; current target config/profile/provider/tests; project CLI guide and validation rules.

## 8. Files to Edit

- `Modules/DK/config/config.php`
- `.env.example` — declare `DK_ACCEPTANCE_TARGET_PROFILE=` without an activating default
- `Modules/DK/tests/Feature/DKTargetFoundationIntegrationTest.php`
- `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`
- this Remediation and DK lifecycle indexes for status/evidence

## 9. Files to Create

This Decision, Change Request, Remediation, and an eventual Run Report after validation.

## 10. Do Not Change

`.env`, target repository source, endpoint, secret handle/resolution, Browser selectors/oracles, generated corpus, database schema/data model, dependencies, staging/production policy, Pack 0005, or unrelated source. The versioned `.env.example` manifest may receive only the empty declaration required by the configuration-surface guard.

## 11. Configuration / Settings Impact

Configuration / Settings Impact: Yes. Config and `.env.example` manifest changes: yes. Environment variable required: only for an activated live run. Admin/external secret/queue settings: no. `DK_ACCEPTANCE_TARGET_PROFILE` is non-secret and accepts only `local-testing`; the manifest default is empty and otherwise the target is disabled/unknown.

## 12. Multilingual / Translation Impact

Persian operator-guide wording changes; no product translation or UI text changes.

## 13. UI / Admin UI Impact

CLI workflow only; no graphical/Admin UI change.

## 14. Implementation Rules

Use `env()` only in the module config file. Compare strictly to `local-testing`. Keep endpoint and opaque handle fixed. Preserve the default target array shape and existing provider injection. Never persist or print a resolved credential.

## 15. Architecture Constraints

DK owns activation/profile composition; project orchestration remains generic. No service locator, runtime source mutation, database setting, or arbitrary environment-to-enum conversion.

## 16. Data Model / Migration / Relationship Impact

None.

## 17. Validation Rules

Prove absent, invalid, and exact values; prove target profile resolution; run focused DK/project tests, full suite, syntax/Pint/diff checks, then execute the exact live selector Batch with a process-local selector and verify all 21 outcomes and cleanup.

## 18. Error Handling / Logging / Traceability Impact

No new error code. Existing `unsafe_target` remains the default/invalid classification. Correlation, operation, Batch, item, attempt, Test, and cleanup evidence remain authoritative. No sensitive data may enter output/logs.

## 19. Tests to Add

Focused config/profile integration assertions for default, exact activation, and invalid fail-closed behavior.

## 20. Tests to Run

Focused DK target and Batch/CLI suites; affected Pack 0004 suite; full PHPUnit; PHP syntax; scoped Pint; `git diff --check`; exact 21-variant live Batch/report.

## 21. Acceptance Checklist

- [x] exact profile activates testing only;
- [x] absent/invalid profile is disabled/unknown;
- [x] no `.env` or target source changes;
- [x] tests/static checks pass;
- [x] 21/21 live items and cleanup pass;
- [x] operator acceptance and commit remain separate.

## 22. Rollback / Safety Notes

Remove only the listed mapping/tests/guide text. The live command uses a temporary process value and removes it after completion. Preserve audit/history rows.

## 23. Operator Review Checklist

Confirm the one allow-listed value, fixed loopback/opaque handle, test-only data boundary, cleanup result, and no persistent environment edit.

## 24. Operator Documentation Impact

Update `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md` with activation, command, cleanup, config-cache warning, and troubleshooting.

## 25. Agent Final Report

Report exact files, config behavior, Batch/error UX behavior, automated/live results, cleanup evidence, Git status, and remaining acceptance/commit gates.

Execution evidence is recorded in `Modules/DK/docs/ai/runs/REMEDIATION-PACK-DK-PROCESS-LOCAL-TARGET-ACTIVATION-0003-run-001.md`.
