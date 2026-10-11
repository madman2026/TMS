# CHANGE-REQUEST-TMS-INFORMATION-PROTECTION-0006 — Defer information-protection capabilities inside the local synthetic boundary

## 1. Title

Temporarily defer dedicated information-protection capabilities for single-operator, local, wholly synthetic TMS work.

## 2. Status

Implemented — documentation and pending DK Pack normalized; no source remediation required.

## 3. Related Release / Phase

Current pre-release TMS Acceptance roadmap, including DK stages 13–15.

## 4. Decision Source

`docs/project/ai/decisions/TMS-DECISION-0017-temporary-local-synthetic-data-protection-deferral.md`.

## 5. Proposed Change

Treat policy-enforced capture/telemetry/retention suppression and the other information-protection capabilities listed by Decision 0017 as deferred requirements while every condition of the local, single-operator, fake/synthetic-data boundary holds. Normalize pending DK Pack 0003 so lack of those controls alone does not exclude its 1,969 `sensitive-no-capture` source variants.

Do not weaken the prohibitions on real secrets, personal/customer/production data, real targets/providers, external I/O, `.env` access or unapproved evidence/executor capabilities.

## 6. Reason for Change

The current work has one local operator and no real target information to protect. The operator chose to avoid building generalized protection infrastructure now but explicitly requires it before later higher-risk use.

## 7. Scope of Impact

Affected areas:

- project Decision 0005/0012 interpretation inside the temporary boundary;
- pending DK corpus mapping analysis, chunk manifest, scenario-file count and Pack acceptance criteria;
- future security planning and stop/review triggers.

No source, Config, Routes, API contracts, Permissions, Database, Models, Services, Jobs, Admin UI, dependency, target, provider, network or runtime execution is changed.

## 8. Affected Files

The exact documentation files are listed in Decision 0017. DK Pack 0003 and its corpus manifest are unexecuted Draft/reference records and may be normalized; accepted Pack/Run history remains unchanged.

## 9. Backward Compatibility

No, relative to the active planning constraint that treated missing sensitive-channel enforcement as an exclusion. There is no implemented runtime compatibility impact because no protection implementation is removed and DK Pack 0003 has not run.

## 10. Risk Level

Medium. The accepted residual risk is bounded to fake/synthetic local data and one operator. Crossing any recorded trigger without prior security implementation is prohibited.

## 11. Migration or Remediation Needed

No. Documentation and pending-Pack normalization are sufficient. A future implementation Pack is mandatory before a boundary trigger, but it is not remediation of accepted source.

## 12. Required Human Approval

Yes — supplied by the operator's explicit 2026-10-10 decision. Pack 0003 execution, acceptance and commit retain separate gates.

## 13. Acceptance Criteria

- [x] Decision 0017 records the exact temporary boundary, retained prohibitions and expiry triggers;
- [x] Decision/Change indexes and affected current-decision notices are synchronized;
- [x] DK Pack 0003 and its manifest map all 24,668 variants as `automated_full` only under Decision 0017;
- [x] mapping totals, assertion references, 139 scenario identities/files and deterministic chunks are recomputed;
- [x] no accepted Run/source/configuration/runtime behavior is rewritten;
- [x] future information-protection implementation remains mandatory before any trigger;
- [x] no execution or commit occurs through this Change Request.

## 14. Rollback Plan

Before commit, revert only this Change Request, Decision 0017, the two current-scope notices and the Decision-0017 normalization edits in pending DK Pack 0003/manifest/indexes through a reviewed patch. Do not use broad reset/clean operations or alter unrelated working-tree changes.

## 15. Final Decision

Approved and implemented as documentation/future-Pack normalization on 2026-10-10. No remediation or runtime execution is authorized.

## 16. Required remediation

None. Future information-protection implementation requires a separately scoped and accepted Pack before a boundary trigger.

## 17. Notes

This Change Request accepts temporary residual risk; it does not certify TMS for real sensitive information.
