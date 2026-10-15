# CHANGE-REQUEST-TMS-PREREQUISITE-MYSQL-IDENTIFIERS-0014

## 1. Title

Make prerequisite migration foreign-key identifiers portable to MySQL.

## 2. Status

Implemented and validated; operator accepted the result and authorized the scoped commit on 2026-10-15.

## 3. Related Release / Phase

Pack 0011 prerequisite persistence, discovered during DK Pack 0004 live Browser execution.

## 4. Decision Source

The accepted Pack 0011 prerequisite schema and referential actions remain authoritative.

## 5. Proposed Change

Assign explicit, bounded names to the two prerequisite child-table foreign keys. Preserve columns, referenced table, restrictive delete, cascading update, indexes, data contract, and rollback order.

## 6. Reason for Change

MySQL rejects Laravel's generated identifiers of 67 and 70 characters. SQLite validation did not expose the 64-character MySQL limit, so a clean local MySQL migration stopped before prerequisite persistence could be used.

## 7. Scope of Impact

Database migration portability and one focused regression test only. No runtime, API, UI, configuration, secret, target, or owner-local DK behavior changes.

## 8. Affected Files

- the two prerequisite input/approval migration files;
- one focused migration-portability test;
- this Change Request, Remediation 0008, their indexes, and later Run evidence.

## 9. Backward Compatibility

Yes. The logical schema and referential actions do not change. Databases where the migrations already ran retain their existing constraint names.

## 10. Risk Level

Low.

## 11. Migration or Remediation Needed

Yes: `REMEDIATION-PACK-TMS-PREREQUISITE-MYSQL-IDENTIFIERS-0008`.

## 12. Required Human Approval

Yes — received for execution on 2026-10-14. Acceptance and commit remain separate gates.

## 13. Acceptance Criteria

- both identifiers are at most 64 characters;
- fresh MySQL migration succeeds;
- prerequisite workflow and full automated suite pass;
- the failed empty partial table is removed only from the authorized local test database before retry;
- no schema semantics change.

## 14. Rollback Plan

Revert the explicit names and regression test. Do not rename constraints in already-migrated databases.

## 15. Final Decision

Implementation and validation complete; result awaits operator acceptance and commit authorization.

## 16. Required remediation

Project Remediation 0008.

## 17. Notes

This correction is executed in the current DK Pack 0004 validation window because it is small and directly blocks the operator-approved live run; it remains project-owned rather than widening the DK source boundary.
