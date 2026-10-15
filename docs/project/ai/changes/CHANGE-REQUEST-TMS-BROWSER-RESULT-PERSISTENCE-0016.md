# CHANGE-REQUEST-TMS-BROWSER-RESULT-PERSISTENCE-0016

## 1. Title

Persist failed Browser executor Steps without reading an absent DTO identity.

## 2. Status

Implemented and validated; failed-Step persistence regression and complete live matrix passed; operator accepted the result and authorized the scoped commit on 2026-10-15.

## 3. Related Release / Phase

Shared Acceptance execution persistence; discovered during DK Pack 0004 live Browser canary.

## 4. Decision Source

The accepted safe persisted Test identity and executor-neutral Browser result contracts remain authoritative.

## 5. Proposed Change

Build failed-Step log identity from the already persisted `Test` record for both legacy `RunResult` and executor `BrowserExecutorData`. Add focused coverage for a failed Browser executor result.

## 6. Reason for Change

`BrowserExecutorData` intentionally contains scenario/result scalars but no identity. The persistence logger accessed `$result->identity` only when a Step failed, causing `ErrorException`, rolling back all Steps, and masking `browser_execution_failed` as `acceptance_result_persistence_failed`.

## 7. Scope of Impact

Project Acceptance result persistence and its existing feature test only. No DTO, executor, target, database schema, API/UI, or logging-field change.

## 8. Affected Files

- `app/Services/AcceptanceRunService.php`
- `tests/Feature/AcceptanceRunPersistenceTest.php`
- this Change Request, Remediation 0010, indexes, and later Run evidence

## 9. Backward Compatibility

Yes. The same allowlisted identity fields are logged; their safe source changes from result DTO to the canonical persisted Test.

## 10. Risk Level

Low.

## 11. Migration or Remediation Needed

Yes: project Remediation 0010. No database migration or data repair.

## 12. Required Human Approval

Yes — received for current execution on 2026-10-14. Acceptance and commit remain separate gates.

## 13. Acceptance Criteria

A failed Browser Step persists, the Test retains `browser_execution_failed`, the Step retains its safe error code, the structured event contains the complete safe identity, and no raw error content is stored or logged.

## 14. Rollback Plan

Revert the one logging-context source line and focused test.

## 15. Final Decision

Implementation and validation complete; result awaits operator acceptance and commit authorization.

## 16. Required remediation

`REMEDIATION-PACK-TMS-BROWSER-RESULT-PERSISTENCE-0010`.

## 17. Notes

Executed as a small project-owned blocker correction within the operator-approved DK Pack 0004 live-run window.
