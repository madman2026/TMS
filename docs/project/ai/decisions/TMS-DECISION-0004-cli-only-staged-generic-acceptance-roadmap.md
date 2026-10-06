# TMS-DECISION-0004 — CLI-only staged generic Acceptance capability roadmap

Status: Accepted — roadmap partly superseded by Decision 0005
Scope: Project
Source: Operator answer
Date: 2026-10-05
Review At: normalization of the batch execution Pack or request for a second execution channel
Blocking: No
Closure Condition: superseded by a separately approved TMS execution-channel or capability-roadmap decision
Next Review At: `AI-PACK-TMS-BATCH-CLI-EXECUTION-0008` normalization

Supersession Notice (2026-10-06): `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md` replaces the standalone Core Evidence Safety 0002 / project Ephemeral Execution Context 0005 prerequisites with minimum safeguards inside Draft Packs 0006–0008 for synthetic testing/staging execution. Expanded operator-configurable test options are deferred. Follow Decision 0005 for the current chain; this original seven-Pack proposal is retained below as accepted history. CLI-only execution, code-owned Apps, staged approval, and the other scope exclusions remain applicable. Approved documentation change: `docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0001.md`.

## Type

4. Phase/Release Decision

## Decision

TMS will add the reusable prerequisites for target-system human-acceptance automation through seven staged standard AI Packs.

The current roadmap remains CLI-only. Artisan commands replace the previously considered API control plane for initial execution, planning, status, resume, cancel, and reporting. No API or UI execution surface is part of this roadmap.

The initial batch implementation is sequential, bounded, persisted, and resumable. Queue workers, parallel execution, scheduling, remote execution, retry automation, and an API remain separately deferred capabilities.

The seven-Pack chain is:

1. project-owned generic scenario metadata, automation-disposition, and evidence-mode contracts;
2. Core-owned browser interaction and accessibility observations;
3. Core-owned evidence safety and sensitive no-capture enforcement;
4. project-owned ephemeral secret and authentication execution context;
5. project-owned isolated fixture lease and cleanup lifecycle;
6. project-owned lazy scenario catalog and variant dispatcher;
7. project-owned sequential resumable batch execution through Artisan commands.

Only the first Pack may be generated as execution-ready now. Later Packs are created as Draft roadmap records and must be normalized against accepted predecessor source, Decisions, Runs, Reviews, current dependencies, and current repository state immediately before approval and execution.

Target-system Apps remain separate. No ND schema, selector, permission, URL, credential, fixture, payload, or workflow may enter shared TMS or Core implementation. A later target-specific Pack must map the target contract into the generic code-owned contracts.

## Reason

The operator selected Artisan/terminal execution instead of an API and authorized creation of the generic Pack chain. A staged design avoids freezing downstream database, failure, secret, evidence, and command contracts before their prerequisites exist, while still preserving the complete intended dependency chain.

The current accepted Decisions already establish tests-as-code Apps, the Core boundary, explicit registration, and CLI-first execution. This Decision extends that direction without contradicting the current implementation.

## Impact

- Seven standard Packs are created; none is a Remediation Pack.
- The API Pack is removed from the generic roadmap.
- CLI remains the only approved operator entry point for this roadmap.
- Queue, scheduler, parallel execution, remote execution, retry engine, UI, and API remain outside scope.
- Later Draft Packs cannot be executed until normalized and separately approved.
- Error codes, status transitions, persistence schema, retention, secret-provider choice, and exact file allowlists for downstream Packs remain deferred to their normalization gates.
- Sensitive values must be omitted from source, Profile `extra`, fixtures, logs, command output, reports, and persisted evidence. Only references, masked state, or allowlisted metadata may cross shared boundaries.

Error Handling Impact: Yes
API Error Contract Impact: Not applicable
UI / Admin UI Error Impact: Not applicable
Error Code Impact: Deferred to each normalized Pack
Exception Handling Impact: Deferred to each normalized Pack
External Error Normalization Impact: Not applicable
Retry / Failure Classification Impact: Yes — automated retry remains out of scope
Status Model Impact: Yes — the batch Pack must define it before implementation
Logging Impact: Yes — each Pack must define safe structured events when applicable
Traceability Impact: Yes — CLI Run/Batch identifiers must connect execution and reports
Sensitive Data Impact: Yes — fail-closed omission and no-capture requirements apply
Testing Impact: Yes — each Pack requires behavior and negative/security tests
Documentation Impact: Yes — Pack and Decision indexes are updated

## Files to Update

- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `Modules/Core/docs/ai/packs/PACKS-INDEX.md`
- the seven Pack files named by this Decision

## Required change request

No Change Request is required. Decision 0003 explicitly deferred later execution-channel and orchestration choices. CLI remains the existing channel and current behavior is not removed or contradicted.

## future-work

- Normalize and execute each Pack in dependency order.
- Create a separate target-system App Pack only after the generic chain reaches the capability needed by that target.
- Reconsider queue/parallel/API/UI only when measured execution needs justify a separate Decision and Pack.
- Perform a cross-Pack Review after Pack 0008 is executed; create a Remediation Pack only for concrete findings.

## Review / Resolution

```text
Reviewed In: operator discussion requesting generic TMS Packs after choosing Artisan instead of API
Review Result: operator authorized Pack creation and delegated the detailed-now versus staged-normalization strategy
Resolution: accepted
Next Review At: AI-PACK-TMS-BATCH-CLI-EXECUTION-0008 normalization or request for another execution channel
Resolution Notes: staged normalization selected; only the first Pack is execution-ready at creation time
```

## Notes

This Decision authorizes planning records, not execution. Every Pack retains its own approval, scope, validation, reporting, review, and commit gates.
