# CHANGE-REQUEST-TMS-OPERATOR-DIAGNOSTICS-0020

## 1. Title

Make Acceptance failures explainable, reports useful, and operator recovery discoverable.

## 2. Status

approved — operator approved planning and scoped documentation commit on 2026-10-16; implementation readiness: ready; not implemented.

## 3. Related Release / Phase

Project Acceptance operator workflow; follow-up to accepted project Packs 0014/0015 and Remediation 0013. No new release phase or change to the ND stage-14/15 delivery order.

## 4. Decision Source

`docs/project/ai/decisions/TMS-DECISION-0026-acceptance-operator-diagnostics-and-reporting.md` owns the approved behavior, diagnostic allowlist, and bounded cross-owner exception.

## 5. Proposed Change

Retain safe failure causes in structured logs, present existing report evidence and truthful outcome wording, validate interactive bounds at input, provide concrete next commands, and show the first report page before asking about pagination. Explain cleanup-blocked retry without changing retry policy.

## 6. Reason for Change

The current workflow requires operators to read raw JSON, search logs, and sometimes inspect source to distinguish input mistakes, missing schema, fixture failures, and failed cleanup. The review's observed cases and evidence limits are recorded in Decision 0026.

## 7. Scope of Impact

- Shared CLI prompts/rendering and project operation/reporting/target diagnostic boundaries.
- Bounded DK Operations Health fixture error capture and its unit test only.
- Behavioral tests, Persian CLI operator guide, and lifecycle records.
- No route, HTTP API, permission, database, migration, model relationship, queue policy, target configuration, or external target implementation changes.

## 8. Affected Files

The implementation allowlist is sections 8–9 of `docs/project/ai/remediations/REMEDIATION-PACK-TMS-OPERATOR-DIAGNOSTICS-0014.md`. It includes the existing renderer/command, query/report services, operation and batch error boundaries, DK fixture, and their tests. This Change does not broaden that allowlist.

## 9. Backward Compatibility

Yes for script consumers: existing version-2 JSON fields, error codes, classifications, command names/options, default script mode, and exit semantics remain unchanged. Human wording, prompt order, and report presentation intentionally change. Structured diagnostic logs gain the allowlisted event; old logs and records are not rewritten.

## 10. Risk Level

Medium: error capture could leak sensitive information or change failure propagation; human reporting could misstate completion or retry safety. Bound changes with negative disclosure tests and existing service/JSON/state regressions.

## 11. Migration or Remediation Needed

No migration. Implementation correction: `REMEDIATION-PACK-TMS-OPERATOR-DIAGNOSTICS-0014`.

## 12. Required Human Approval

Planning and this documentation commit: granted by the operator on 2026-10-16. Runtime execution, post-implementation acceptance, implementation commit, and any live target access remain separate gates.

## 13. Acceptance Criteria

- Input 10000 is rejected and can be corrected in the page-size prompt using configured bounds.
- A report of failed fixtures identifies the stage, grouped count, cleanup failure, and executor-entry evidence without asserting an unknown cause.
- Reports distinguish retrieval success from execution success; queued async work is not described as passed tests.
- Multi-page reports expose continuation; a small report does not ask about pagination first.
- Known typed causes and query failures produce a safe diagnostic event with usable correlation; scripts retain their contract.
- Cleanup failures explain why direct retry is blocked; existing retry guards remain effective.
- Relevant automated tests and guide updates pass; no target activity or historical data rewrite is needed.

## 14. Rollback Plan

Revert only the future source/test/guide implementation changes. Retain historical records and logs. A planning-only rollback changes the readiness/decision chain explicitly; it does not alter accepted earlier remediation history.

## 15. Final Decision

Approved for a ready implementation pack; not a record of completed implementation.

## 16. Required remediation

`docs/project/ai/remediations/REMEDIATION-PACK-TMS-OPERATOR-DIAGNOSTICS-0014.md`.

## 17. Notes

This corrects gaps remaining after accepted Remediation 0013; it does not reopen its validated lifecycle-identity or homogeneous-failure fixes. No source update to the separately blocked executor-routing Remediation 0007 is authorized. Guide changes and execution evidence are implementation follow-ups, not claims made by this planning commit.
