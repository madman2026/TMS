# TMS-DECISION-0006 — Remove cancelled fixture and safeguard Pack proposals

Status: Accepted
Scope: Project
Source: Operator answer
Date: 2026-10-06
Review At: next normalization of project Packs 0007–0008
Blocking: No for this documentation cleanup; downstream execution still requires normalization
Closure Condition: superseded by a separately approved roadmap decision
Next Review At: project Pack 0007 normalization

## Type

6. Change Request / Remediation Trigger — explicit cancellation and deletion of proposal documents; no accepted runtime correction.

## Decision

The operator cancelled project Fixture Leases Pack 0006, requested rollback to the pre-execution commit, and then said “این پک و دوتا پک قبلی که اجرا نشده اند رو حذف کن .”. The two preceding unexecuted proposals are Core Evidence Safety Pack 0002 and project Ephemeral Execution Context Pack 0005, both already superseded by Decision 0005.

Delete those three Pack documents and remove their rows from the active Pack indexes. Do not archive copies or generate a replacement Pack. Preserve accepted Packs, source, Runs and Reviews; add dated current-roadmap notices where older records direct future work to the deleted proposal.

Project Pack 0006 was implemented and fake-tested in this chat, but never finally accepted or committed. Its seven tracked edits were restored and twelve created files removed; Git confirmed a clean main working tree matching HEAD 4650cf943ba33ae40a8c4bf61df67f312ede3731. No accepted Run was created. Its execution report is historical chat evidence, not current source or an accepted implementation contract. Core 0002 and project 0005 were never implemented.

Project Packs 0007 and 0008 remain Draft proposals. Remove their dependency on deleted Pack 0006 and mark them as needing normalization against actual accepted source. Do not assume a fixture lifecycle, target-environment guard, lease types or new fixture error codes exist. Future normalization must explicitly resolve any needed target/environment/resource-cleanup boundary without silently recreating the cancelled Pack.

Decision 0005 remains applicable for testing/staging-only synthetic data, App ownership, safe-value omission and deferred expanded settings/secret/capture frameworks. Decision 0004's unchanged CLI/ownership/staged-approval constraints remain applicable. This decision supersedes only the fixture prerequisite, proposal-retention instructions and next-Pack directions affected by these deletions.

## Reason

The operator stated that Pack 0006 is not needed and explicitly requested removal of it and the two earlier unexecuted proposals. Active navigation must reflect that cancellation and must not route future work to missing contracts or files.

## Impact

- Documentation/lifecycle only in this change; runtime remains at the accepted pre-0006 source.
- Core 0002 and project 0005–0006 have no active proposal document. Git history retains their historical revisions.
- Project 0007–0008 require new normalization and separate implementation approval; no execution is authorized here.
- No new Canonical file, runtime error code, exception, guard, configuration, schema, test, provider, target connection or commit.
- Error Handling / Logging / Traceability Impact: planning only. Pack 0006's reverted codes/events are not an available dependency; any future contract must be separately normalized and approved.
- Operator Documentation Impact: no implemented operator capability or guide remains from the reverted Pack; no new guide is needed for deletion.

## Files to Update

Deletion targets (historical Git paths, not active references after this change):

- `Modules/Core/docs/ai/packs/AI-PACK-CORE-EVIDENCE-SAFETY-0002.md`
- `docs/project/ai/packs/AI-PACK-TMS-EPHEMERAL-EXECUTION-CONTEXT-0005.md`
- `docs/project/ai/packs/AI-PACK-TMS-FIXTURE-LEASES-0006.md`

Synchronize project/Core Pack indexes, project Decision/Change indexes, project Drafts 0007–0008, and dated notices in Decisions 0004–0005, roadmap Change 0001, and the accepted Core 0001 Pack/Run/Review. Historical bodies and validation results stay intact.

## Required change request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0002.md` records this approved documentation-only revision. No Remediation is required: the unaccepted implementation was already completely reverted at the operator's direction.

## future-work

Normalize Pack 0007 against accepted source before any execution proposal, then reassess Pack 0008 after its predecessor is accepted. Preserve the current testing/staging, synthetic-data and safe-output requirements; do not infer concrete missing runtime behavior from old proposal text.

## Review / Resolution

```text
Reviewed In: operator cancellation, rollback and document-deletion requests on 2026-10-06
Review Result: explicit cancellation and deletion authorized; prior rollback verified clean
Resolution: accepted
Next Review At: project Pack 0007 normalization
Resolution Notes: approval covers these three document deletions and directly required documentation synchronization; no implementation, target I/O or commit
```

## Notes

The three deleted proposal identifiers remain in historical records solely for traceability. They must not be opened as current files or reinstated as execution prerequisites. Current cancellation is controlled by this record and the active indexes.
