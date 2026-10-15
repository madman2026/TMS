# REMEDIATION-PACK-TMS-PREREQUISITE-MYSQL-IDENTIFIERS-0008

Status: `accepted — validated and operator-accepted with scoped commit authorization on 2026-10-15`

Generated: `2026-10-14`

Owner: TMS project

## 1. Task ID

`REMEDIATION-PACK-TMS-PREREQUISITE-MYSQL-IDENTIFIERS-0008`

## 2. Task Title

Correct overlength MySQL foreign-key identifiers in prerequisite migrations.

## 3. Related Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-PREREQUISITE-MYSQL-IDENTIFIERS-0014.md`

## 4. Goal

Allow the accepted prerequisite schema to migrate on MySQL without changing its logical contract.

## 5. Current Problem

Fresh MySQL execution applies the request migration, then leaves an empty partial input table when MySQL rejects the generated 67-character constraint identifier. The approval identifier would fail next at 70 characters.

## 6. Required Change

Provide explicit short identifiers for both constraints, add a regression that inspects the migration blueprints, remove only the verified empty partial input table from the authorized local test database, rerun migrations, and validate the prerequisite workflow.

## 7. Files to Read

Startup/governance files, Pack 0011/Run 001, Change 0014, the two migrations, and the focused prerequisite workflow tests.

## 8. Files to Edit

- `database/migrations/2026_10_08_000003_create_acceptance_operation_inputs_table.php`
- `database/migrations/2026_10_08_000004_create_acceptance_operation_approvals_table.php`
- project Change/Remediation/Run indexes and this record
- DK Pack 0004 lifecycle note only

## 9. Files to Create

- `tests/Unit/AcceptancePrerequisiteMigrationPortabilityTest.php`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-PREREQUISITE-MYSQL-IDENTIFIERS-0014.md`
- `docs/project/ai/runs/REMEDIATION-PACK-TMS-PREREQUISITE-MYSQL-IDENTIFIERS-0008-run-001.md` after validation

## 10. Do Not Change

Do not change columns, types, indexes, unique constraints, referential actions, models, runtime services, API/UI, configuration, dependencies, target source, or any non-test database.

## 11. Configuration / Settings Impact

No configuration or settings impact.

## 12. Multilingual / Translation Impact

No multilingual or translation impact.

## 13. UI / Admin UI Impact

No UI or Admin UI impact.

## 14. Implementation Rules

Names must be explicit, descriptive, unique within their tables, and no longer than 64 characters. The local partial table may be dropped only after verifying it has zero rows and its migration has no migration-table record.

## 15. Architecture Constraints

This is project-owned migration portability. DK Pack 0004 remains owner-local and consumes the corrected project persistence layer without owning it.

## 16. Data Model / Migration / Relationship Impact

Data / Migration Impact: Yes — identifier naming only. No table, column, key relationship, index, data, backfill, or rollback-semantic change. The two foreign keys still reference `acceptance_operation_requests.id`, restrict delete, and cascade update.

## 17. Validation Rules

Inspect both generated blueprint names, run focused tests, perform a fresh continuation on the authorized MySQL test database, verify all migrations ran, and run the full suite already required by DK Pack 0004.

## 18. Error Handling / Logging / Traceability Impact

No API error contract, stable error code, logging, traceability, or sensitive-data change. The correction prevents database DDL error 1059 during deployment.

## 19. Tests to Add

One migration-blueprint portability regression covering both explicit foreign-key identifiers and the MySQL 64-character ceiling.

## 20. Tests to Run

- focused portability test;
- prerequisite workflow feature test;
- `php artisan migrate --force` on the authorized local test database;
- full TMS suite, Pint on changed PHP files, and `git diff --check`.

## 21. Acceptance Checklist

- [x] explicit bounded identifiers implemented;
- [x] focused and workflow tests pass;
- [x] local MySQL migrations complete;
- [x] DK Pack 0004 Browser run completed;
- [x] Run report and indexes synchronized;
- [x] operator accepted the result and authorized the scoped commit on 2026-10-15.

## 22. Rollback / Safety Notes

Only the exact empty partial table created by the failed migration may be dropped. Preserve the three successfully applied migration records. No broad rollback, reset, or database recreation is authorized.

## 23. Operator Review Checklist

Review the two names, unchanged referential actions, regression evidence, exact database repair, suite result, and lifecycle records.

## 24. Operator Documentation Impact

No operator-facing behavior changed and no guide update or screenshot is required.

## 25. Agent Final Report

Report the original lengths, corrected names, exact test-database repair, migration/test results, Browser continuation, changed files, and remaining acceptance/commit gates.
