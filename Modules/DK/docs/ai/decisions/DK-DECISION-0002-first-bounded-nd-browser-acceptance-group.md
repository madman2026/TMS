# DK-DECISION-0002 — First bounded ND Browser Acceptance group

Status: Accepted — operator selected the Pack scope on 2026-10-11; no implementation, execution, acceptance, or commit authority is granted

Scope: DK stage 14 / `AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004`

Source: Operator answers on 2026-10-11

Date: 2026-10-11

Review At: normalization of DK Pack 0004 after project Pack 0019 is accepted

Blocking: Yes for expanding Pack 0004 beyond Browser Acceptance or starting it before the shared prerequisite is accepted

Closure Condition: superseded by a separately accepted DK scope decision or completed by accepted DK Pack 0004

Next Review At: before Pack 0004 execution approval

## Type

4. Phase/Release Decision

## Decision

The first independently bounded executable Notification Delivery group is limited to Browser Acceptance for the existing scenario identity:

```text
app: dk
component: notification-delivery
suite: admin.delivery
scenario: nd-component-operations-health
variants: all 21 currently imported variants for that scenario
```

Pack 0004 may propose the minimum DK-owned scenario, selector, variant, synthetic fixture, target-resource, cleanup, oracle, and test work required to execute this group. It must remain read-only at the product-behavior boundary and must preserve `provider_io=none`.

The following capabilities are deferred to later separately accepted Packs:

- API/HTTP scenario execution;
- creation or expansion of Notification Delivery fake behavior;
- real Provider, SMS, notification, callback, queue, worker, or scheduler behavior;
- any additional ND scenario or variant group;
- full stage-15 coverage reconciliation.

The accepted Pack-0002 in-memory fake remains historical/current source and must not be removed or modified by Pack 0004. Pack 0004 must not use this Decision to introduce new fake behavior. Safety assertions may prove that no Provider or external I/O occurred.

This Decision does not waive the accepted hierarchy, traceability, testing/staging-only, synthetic-data, no-real-target, no-real-secret, and temporary local/single-operator security boundaries. It does not authorize implementation or target access.

## Reason

The operator requested Acceptance first and explicitly deferred API, Fake, and other capability work. `nd-component-operations-health` is the smallest coherent read-only group whose 21 variants cover five health-evidence cases plus the locale/theme/viewport/focus matrix without a product mutation or Provider I/O.

Before project Pack 0019, source could not safely execute only this group: generated scenario descriptors shared one metadata result, and variant identity plus target-resource context did not reach scenario execution. Pack 0019 has now implemented and validated those shared prerequisites, while the DK target adapter remains fail-closed. Pack 0004 remains Draft and blocked until Pack 0019 receives an accepted Run and its DK-local runtime scope is re-reviewed.

## Impact

### Configuration / Settings Impact

- No configuration, `.env`, `.env.example`, Admin Setting, or secret-setting change is approved by this Decision.
- Any later runtime setup must use a local, isolated, synthetic target and runtime-resolved opaque values; no real URL, session, credential, token, or environment value may be written to source or documentation.
- Accepted configuration-surface Remediation 0005 owns the authoritative Acceptance configuration and is a satisfied prerequisite; Pack 0004 must not reopen or modify it.

### Multilingual / UI Impact

- No target UI or translation file change is approved.
- Pack 0004 must observe the existing English/LTR and Persian/RTL behavior represented by its 21 variants.
- Desktop/narrow and normal/focus behavior are Acceptance assertions, not redesign authority.

### Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes — Pack 0004 must reuse accepted lifecycle/browser failures
API Error Contract Impact: No
UI / Admin UI Error Impact: No source/UI change
Error Code Impact: No new code approved
Exception Handling Impact: No new public exception approved
External Error Normalization Impact: No
Retry / Failure Classification Impact: No new classification approved
Status Model Impact: No
Logging Impact: No new log event approved
Traceability Impact: Yes — the exact App/Component/Suite/Scenario/Variant identity must reach execution
Sensitive Data Impact: Yes — real/access-bearing values remain prohibited
Testing Impact: Yes — all 21 identities and zero Provider/external I/O require proof
Documentation Impact: Yes
```

### Data / Migration Impact

No database, migration, model, relationship, index, snapshot, backfill, seed, or persistent target-data change is approved by this Decision.

### Operator Documentation Impact

No operator-guide update is required merely to select this bounded group. If the eventual shared prerequisite or target integration changes CLI setup or troubleshooting, its owning Pack must update the project Persian Acceptance CLI guide or record a blocking follow-up.

## Files to Update

- `Modules/DK/docs/ai/decisions/DK-DECISION-0002-first-bounded-nd-browser-acceptance-group.md`
- `Modules/DK/docs/ai/decisions/DECISIONS-INDEX.md`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004.md`
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`

## Required change request

A project-owned Change Request and enabling Pack are required before Pack 0004 can be normalized for acceptance or execution. They are now:

- `docs/project/ai/changes/CHANGE-REQUEST-TMS-ACCEPTANCE-EXECUTION-CONTEXT-0009.md` — approved;
- `docs/project/ai/packs/AI-PACK-TMS-ACCEPTANCE-EXECUTION-CONTEXT-0019.md` — implemented and validated; post-execution review and accepted Run pending.

They define the minimum shared Acceptance extension needed to:

- assign executable metadata to one generated scenario without changing the other 138 scenarios;
- propagate exact variant identity into scenario execution;
- propagate only the safe target execution context required by the Browser executor;
- preserve deterministic import replay and managed-region ownership;
- prove safe-output and full-tuple traceability behavior.

This DK Decision does not authorize editing shared project/Core source through DK Pack 0004. Project Pack 0019 exclusively owns that implementation and the mechanical generated-source migration.

## future-work

- Complete post-execution review and accept the authorized Run for project Pack 0019 under its separate gates.
- Re-review DK Pack 0004 against the accepted shared contract and the accepted configuration surface.
- Obtain separate Pack-0004 acceptance and execution approval.
- Keep API, fake expansion, additional scenario groups, and stage-15 reconciliation in later Packs.

## Review / Resolution

```text
Reviewed In: operator request to create AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004
Review Result: operator selected Acceptance-only scope and deferred API, Fake, and other capabilities
Resolution: accepted
Next Review At: Pack 0004 normalization before execution approval
Resolution Notes: scope selection only; Change Request 0009 and project Pack 0019 now own the project prerequisite. DK Pack 0004 remains Draft until their accepted Run and a separate DK execution gate. No DK source execution, target access, Browser run, Pack acceptance, or commit is authorized.
```

## Notes

The 21 variants are the current accepted Pack-0003 catalog entries for `nd-component-operations-health`. A change to that count, identity set, suite, scenario, Provider-I/O policy, or evidence mode requires Pack normalization and operator review.
