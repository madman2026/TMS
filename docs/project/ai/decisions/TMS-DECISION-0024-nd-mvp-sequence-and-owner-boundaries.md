# TMS-DECISION-0024 — ND MVP delivery sequence and owner boundaries

Status: Accepted
Scope: Project architecture / DK stages 14–15 delivery order
Source: Operator clarification and instruction
Date: 2026-10-14
Review At: before any Decision-0017 boundary trigger and at stage-15 final reconciliation
Blocking: Yes for DK stage-15 normalization and execution order
Closure Condition: DK Pack 0005 final reconciliation is accepted or this sequence is superseded by a separately approved decision
Next Review At: after accepted DK Pack 0004 and the current N-D contract-drift review

## Type

4. Phase/Release Decision and 6. Change Request / Remediation Trigger

## Decision

The current MVP must execute the complete accepted Notification Delivery corpus in a local, single-operator, wholly synthetic, fake-provider-only and non-retained mode. It does not need production, staging, multi-user, remote, real-data, real-credential, real-Provider or retained-evidence readiness.

The MVP must preserve a low-cost path to those later modes through these owner boundaries:

- the TMS project owns catalog traversal, planning, bounded segmentation, orchestration, execution-policy handoff, persistence and reporting;
- Core owns reusable executor contracts, the executor registry and concrete executor implementations, but no target identity, selector, fixture, recipe or N-D rule;
- DK owns N-D scenario composition, selectors, oracles and target adapters;
- the target N-D owner owns product-side Acceptance Control recipes, fixtures, fake Provider behavior and target cleanup;
- an owner-specific Pack must not implement another owner's responsibility directly.

The binding execution order is:

1. execute and accept DK Pack 0004 as the 21-variant Browser vertical slice;
2. execute and accept project Remediation 0006 before any broad generated-corpus write;
3. review the current N-D Human Acceptance Contract against current N-D source;
4. if drift affects the contract, execute a separately approved N-D-owner contract-refresh Pack, followed by an exact TMS/DK successor import or mapping-migration Pack; otherwise record a no-change drift result;
5. execute and accept the project executor-routing remediation required to remove Browser-only application-layer handoff before any API/HTTP child Pack;
6. execute and accept the project bounded-corpus-segmentation Pack without removing the 1,000-item per-segment safety limit;
7. normalize DK Pack 0005 and generate bounded child Packs only after steps 1–6 are accepted;
8. execute child Packs in dependency order: Browser read-only; Admin mutation/permission/audit; API/HTTP; fake Provider/callback/queue/worker; synthetic-sensitive groups. A target-side driver/fixture Pack must precede the DK consumer Pack that needs it;
9. execute DK Pack 0005 final reconciliation only after every required child Pack is accepted and all 24,668 identities are accounted for with actual evidence.

The accepted target Binding Pack 0002 source implementation must not be re-executed. Its migration, activation, local server, live conformance, target access and Browser use remain separate runtime gates for DK Pack 0004.

Project Pack 0022 remains deferred while every Decision-0017 condition holds. It becomes blocking before staging, real or access-bearing data, retained evidence, a second user, shared/remote execution, an external executor or a real target/Provider. Project Pack 0017 remains deferred until accepted stage 15 and must then be regenerated. Superseded project Pack 0008 must never be executed.

## Reason

The operator selected the smallest safe MVP boundary but requires responsibility separation so later security, deployment and scale capabilities can replace project-owned policies and adapters without moving N-D behavior into Core or rewriting scenario ownership. Current source already provides reusable Browser and HTTP executor contracts, but the application execution path persists Browser as the capability and constructs Browser-only requests. Current selectors also intentionally cap one plan at 1,000 items, so the full corpus needs deterministic bounded segmentation rather than a raised or bypassed limit.

## Impact

- Decision 0015 remains the roadmap owner; this Decision fixes the internal order of stages 14–15.
- Decision 0011 remains the executor architecture owner; a remediation is required because current application-layer handoff is Browser-specific.
- Decision 0017 remains the temporary security boundary and is not weakened.
- DK Pack 0005 remains blocked until its declared prerequisites are accepted.
- New project Change Request 0013 owns the execution-routing and bounded-segmentation delta.
- New project Remediation 0007 owns the Browser-only application-handoff correction.
- New project Pack 0024 owns bounded corpus segmentation.
- Exact DK and N-D child Packs remain intentionally deferred until the drift and capability inventory are complete.

```text
Error Handling Impact: Yes
API Error Contract Impact: No
UI / Admin UI Error Impact: No
Error Code Impact: Deferred to Remediation 0007 and Pack 0024 normalization
Exception Handling Impact: Yes
External Error Normalization Impact: No
Retry / Failure Classification Impact: Yes
Status Model Impact: No current source change
Logging Impact: Yes
Traceability Impact: Yes
Sensitive Data Impact: Yes — real or access-bearing values remain prohibited
Testing Impact: Yes
Documentation Impact: Yes
```

No runtime error code changes are approved by this Decision alone. Normalized implementation Packs must preserve the complete App/Component/Suite/Scenario/Variant identity, segment identity and existing correlation/operation trace without logging selectors, payloads, target references or sensitive values.

Configuration / Settings Impact: No current change. Runtime activation remains separately gated.

Multilingual / Translation Impact: No current change.

UI / Admin UI Impact: No current change.

Operator Documentation Impact: Yes, deferred to accepted Pack 0024 and the final DK stage-15 reconciliation. The Persian Acceptance CLI guide must document the approved segmented full-corpus workflow after it exists.

Data Model / Migration / Relationship Impact: Unknown until Pack 0024 normalization. Any persistence change requires an exact migration and rollback scope in that Pack; no migration is approved here.

## Files to Update

- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-ND-MVP-EXECUTION-0013.md`
- `docs/project/ai/changes/CHANGES-INDEX.md`
- `docs/project/ai/remediations/REMEDIATION-PACK-TMS-EXECUTOR-ROUTING-0007.md`
- `docs/project/ai/remediations/REMEDIATIONS-INDEX.md`
- `docs/project/ai/packs/AI-PACK-TMS-BOUNDED-CORPUS-SEGMENTATION-0024.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `Modules/DK/docs/ai/changes/CHANGE-REQUEST-DK-NOTIFICATION-DELIVERY-COVERAGE-RECONCILIATION-0002.md`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-COVERAGE-RECONCILIATION-0005.md`
- applicable DK indexes

## Required change request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-ND-MVP-EXECUTION-0013.md`.

## future-work

- Run the separately gated Pack sequence above.
- Create exact N-D/DK child Packs only after current drift and capability classification.
- Normalize and execute Pack 0022 at the first Decision-0017 trigger.
- Regenerate Pack 0017 only after stage-15 acceptance.

## Review / Resolution

```text
Reviewed In: operator clarification on 2026-10-14
Review Result: operator selected the local synthetic MVP and required low-cost future evolution through strict owner separation
Resolution: accepted
Next Review At: after accepted DK Pack 0004 and current N-D contract-drift review
Resolution Notes: documentation and planning only; no source execution, target access, migration, Browser run, test run or commit is authorized
```

## Notes

All Pack execution, target access, Browser, migration, acceptance and commit gates remain separate. This Decision does not authorize `.env` access or any real external I/O.
