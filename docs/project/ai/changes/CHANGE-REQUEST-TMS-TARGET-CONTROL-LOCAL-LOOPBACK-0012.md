# CHANGE-REQUEST-TMS-TARGET-CONTROL-LOCAL-LOOPBACK-0012 — Add explicit local-loopback HTTP support

## 1. Title

Add an opt-in direct-loopback HTTP exception to the shared target-control client.

## 2. Status

Implemented and accepted through Project Pack 0023 / Run 001 on 2026-10-13; implementation commit authorized.

## 3. Related Release / Phase

Pre-Release cross-owner Acceptance roadmap; prerequisite for DK stage 14 Pack 0004.

## 4. Decision Source

Accepted `TMS-DECISION-0023`; extends Decision 0022 only for the exact local-loopback case.

## 5. Proposed Change

Add a constructor option that defaults to strict HTTPS and, when explicitly enabled, accepts HTTP only for exact `127.0.0.1`, `localhost`, and `[::1]` origins. Preserve every other endpoint, request, response, secret, retry, and error constraint.

## 6. Reason for Change

The accepted DK target binding and TMS consumer run on one development workstation with no real data or workload. DK Pack 0004 cannot consume that binding while the reusable client rejects every HTTP origin.

## 7. Scope of Impact

- one shared client class and its unit tests;
- project governance lifecycle records;
- DK Pack 0004 prerequisite wording;
- no owner-local runtime binding, network call, target mutation, Browser execution, config, `.env`, dependency, or database change.

## 8. Affected Files

Exact source, test, documentation, and index allowlists are owned by `AI-PACK-TMS-TARGET-CONTROL-LOCAL-LOOPBACK-0023`.

## 9. Backward Compatibility

Existing callers retain HTTPS-only behavior because the new option defaults to `false`. HTTPS origins continue unchanged. Opted-in callers gain only the exact loopback HTTP origins.

## 10. Risk Level

Medium: a broad scheme or host exception could permit plaintext traffic beyond the workstation. Exact parsing and negative tests are required.

## 11. Migration or Remediation Needed

No remediation is required. The existing strict implementation is correct for Decision 0022; this is an approved additive contract change for a future consumer Pack.

## 12. Required Human Approval

The operator approved this Change Request, scope addition, implementation, and test execution on 2026-10-13. Run acceptance, implementation commit, target access, DK Pack 0004 execution, Browser execution, and push remain separate.

## 13. Acceptance Criteria

- default construction still rejects loopback and non-loopback HTTP;
- explicit opt-in accepts only `127.0.0.1`, `localhost`, and `[::1]` HTTP origins;
- opted-in construction rejects LAN/wildcard/suffixed/credentialed/path/query/fragment origins;
- HTTPS remains accepted regardless of the option;
- existing error, redirect, timeout, retry, response-bound, and secret rules remain unchanged;
- focused, affected, and full repository tests pass without reading dotenv.

## 14. Rollback Plan

Remove the constructor option and loopback branch plus their tests and governance records. No data or external state requires rollback.

## 15. Final Decision

Implemented and accepted through Project Pack 0023 / Run 001.

## 16. Required Remediation

None; this is an additive accepted-contract extension.

## 17. Notes

The Pack adds capability only. DK Pack 0004 remains responsible for explicit owner-local activation and later deployment gates.
