# CHANGE-REQUEST-TMS-ROADMAP-0002 — Remove cancelled fixture and safeguard proposals

## 1. Title

Delete Core 0002 and project 0005–0006 proposal documents and synchronize downstream planning.

## 2. Status

Implemented — explicit operator deletion request applied and documentation validated on 2026-10-06.

## 3. Related Release / Phase

Not applicable; pre-Release project roadmap.

## 4. Decision Source

`docs/project/ai/decisions/TMS-DECISION-0006-remove-cancelled-fixture-and-safeguard-packs.md`.

## 5. Proposed Change

Delete exactly the three proposals named by Decision 0006. Remove active Pack rows, annotate affected historical directions, and remove project 0006 as a dependency of Drafts 0007–0008. Mark those Drafts as needing normalization; do not implement or replace any cancelled capability.

## 6. Reason for Change

The operator cancelled the fixture Pack after complete rollback and requested deletion of it and the two preceding unexecuted proposals. See Decision 0006 for the approved meaning and execution history.

## 7. Scope of Impact

Documentation only. No config, routes, API, permissions, database, models, services, jobs, UI, executable tests, dependencies, environment files, target I/O or commit.

## 8. Affected Files

The three deletion targets and directly required synchronization documents listed in Decision 0006. No implementation path is allowed by this change.

## 9. Backward Compatibility

Yes for accepted runtime/source. Only future proposal navigation/dependencies change. Historical validation evidence is preserved.

## 10. Risk Level

Low for this documentation change. Drafts must not assume the reverted fixture/guard/error contracts exist; they remain non-executable until normalized and approved.

## 11. Migration or Remediation Needed

No. Outcome: future-pack-normalization / no-remediation-required. The preceding unaccepted source work has already been reverted.

## 12. Required Human Approval

Satisfied by the operator's explicit request. No additional approval is required for the deletion/synchronization; future implementation and commit gates remain separate.

## 13. Acceptance Criteria

- [x] exactly Core 0002 and project 0005–0006 proposal files are deleted;
- [x] active Pack indexes contain no links to the deleted files;
- [x] Decision 0006 and this Change Request are discoverable from their indexes;
- [x] Drafts 0007–0008 do not require accepted Pack 0006 or its absent runtime contracts;
- [x] historical references are explicitly marked historical/deleted and retained without rewriting validation results;
- [x] all other document references resolve, whitespace checks pass, and no source/test/config/accepted-evidence body changes occur.

## 14. Rollback Plan

Only upon separate operator direction, recover the three proposal revisions from Git and reverse this change's documentation edits with an explicit superseding decision. No broad reset or unrelated deletion.

## 15. Final Decision

Approved by the operator on 2026-10-06 under Decision 0006. No future Pack is accepted or executed by this change.

## 16. Required remediation

None.

## 17. Notes

Validation is document-only: deletion scope, active references, statuses/dependencies, historical notices, numbered-section integrity and Git diff/whitespace. No PHPUnit/browser/Artisan/database operation is required or claimed.

Validation Result (2026-10-06): passed. Exactly three proposal deletions, twelve modified documents and two new governance documents were verified; no other tracked/untracked or staged changes were present. All 91 active document-reference occurrences resolved, with nine explicitly historical deleted-file references retained. The numbered sections of both remaining Drafts and both roadmap Change Requests were intact. After removal of the newly added dated notices, the accepted Core 0001 Pack/Run/Review bodies matched HEAD exactly. `git diff --check` passed. Source/tests/config and HEAD `4650cf943ba33ae40a8c4bf61df67f312ede3731` were unchanged; no commit was made.
