# CHANGE-REQUEST-TMS-ROADMAP-0001 — Reduce the synthetic testing/staging roadmap

## 1. Title

Remove standalone evidence/secret Packs from the active roadmap and carry minimum safeguards into remaining Draft Packs.

## 2. Status

Implemented — approved documentation-only revision applied and validated on 2026-10-06.

## 3. Related Release / Phase

Not applicable; pre-Release generic CLI roadmap.

## 4. Decision Source

`docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md` records the operator's instruction to defer configurable settings and standalone secure credential management, and to carry only minimum safeguards forward for synthetic testing/staging data. It revises the future dependency chain in accepted Decision 0004.

## 5. Proposed Change

- Mark Core Evidence Safety 0002 and project Ephemeral Execution Context 0005 superseded/non-executable; retain their original Draft bodies as historical proposals.
- Add a dated partial-supersession notice to Decision 0004 without rewriting its original accepted reasoning or chain.
- Remove the two superseded prerequisites from project Packs 0006–0008. Preserve existing identifiers, Draft status, staged normalization, and future execution approval gates.
- Place the Decision 0005 minimum environment/output checks in Pack 0006, safe catalog/plan boundaries in Pack 0007, and safe persistence/log/report checks in Pack 0008, with corresponding fake-backed validation/acceptance requirements.
- Synchronize Decision, Change, project Pack, and Core Pack indexes. Do not generate a settings or replacement security implementation Pack.

## 6. Reason for Change

See Decision 0005. The target scope is testing/staging with synthetic data; the operator selected smaller inline safeguards instead of two independent infrastructure prerequisites.

## 7. Scope of Impact

Documentation/planning only. No Config, Route, API, Permission, Database, Model, Service, Job, Admin UI, executable test, dependency, environment, or current runtime change. No Pack is executed or accepted by this change.

## 8. Affected Files

- `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`
- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/decisions/TMS-DECISION-0004-cli-only-staged-generic-acceptance-roadmap.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0001.md`
- `docs/project/ai/changes/CHANGES-INDEX.md`
- `Modules/Core/docs/ai/packs/AI-PACK-CORE-EVIDENCE-SAFETY-0002.md`
- `Modules/Core/docs/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/packs/AI-PACK-TMS-EPHEMERAL-EXECUTION-CONTEXT-0005.md`
- `docs/project/ai/packs/AI-PACK-TMS-FIXTURE-LEASES-0006.md`
- `docs/project/ai/packs/AI-PACK-TMS-SCENARIO-CATALOG-DISPATCHER-0007.md`
- `docs/project/ai/packs/AI-PACK-TMS-BATCH-CLI-EXECUTION-0008.md`
- `docs/project/ai/packs/PACKS-INDEX.md`

## 9. Backward Compatibility

Yes for current runtime/source contracts. Future planning dependencies change explicitly through Decision 0005. Executed/accepted Pack history and validation evidence remain untouched.

## 10. Risk Level

Low for this documentation change. Future normalization must not turn the minimum requirements into a hidden generic secret/capture/settings framework or claim safeguards already exist in runtime.

## 11. Migration or Remediation Needed

No. Outcome: future-pack-normalization / no-remediation-required. No implemented behavior or data is corrected.

## 12. Required Human Approval

Satisfied by the operator's explicit 2026-10-06 instruction. No further approval is needed for this documentation revision. Future implementation and commit permissions remain separate.

## 13. Acceptance Criteria

- [x] Decision 0005 is discoverable and states the synthetic testing/staging scope and deferred expanded settings.
- [x] Both standalone Drafts and indexes consistently mark supersession and cancelled standalone execution.
- [x] Decision 0004 retains its historical body and has an explicit current supersession notice.
- [x] Active dependency lines in Packs 0006–0008 no longer require either superseded Pack.
- [x] Minimum safeguards have explicit owners, focused fake tests, acceptance requirements, and normalization points in the remaining Drafts.
- [x] No generic secret store/provider/lease, comprehensive capture guard, new settings layer, or expanded browser configuration is smuggled into remaining scope.
- [x] All remaining Packs remain Draft and preserve current format/identifiers, separate execution approval, CLI/ownership constraints, and synthetic-only tests.
- [x] Referenced files and index links resolve; relevant existing source, tests, and executed Core Pack 0001 remain byte-for-byte unchanged during this revision.

## 14. Rollback Plan

Reverse only this Change Request's documentation revisions after operator direction, restoring the previous planning dependencies/statuses with an explicit superseding decision. Preserve unrelated working-tree edits and execution history; do not use a broad Git reset or delete historical documents.

## 15. Final Decision

Approved by the operator on 2026-10-06 and implemented as documentation maintenance under Decision 0005. This completion does not accept or execute any future Pack.

## 16. Required remediation

None. No Remediation or replacement implementation Pack is required.

## 17. Notes

This Change Request is a governance record, not a new implementation Pack. Validation is limited to document structure, dependency/index consistency, reference existence, diff/whitespace review, and preservation of existing execution/source files. No PHPUnit, browser, Artisan, migration, external target, or `.env` operation is required for this change. Exact guide files and future runtime error/test contracts remain assigned to the normalization sections of the existing Drafts.

Validation completed on 2026-10-06: all 12 affected documents checked; five Pack records retain sections 1–34; the Change Request retains sections 1–17; all 56 checked owner-document references resolve; modified Pack/index statuses and active dependencies agree; seven relevant source/test/executed-Pack SHA-256 hashes are unchanged from the start of this revision; tracked diff and affected-document whitespace checks passed. No runtime test result is claimed.
