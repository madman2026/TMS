# TMS-DECISION-0015 — Clean Acceptance hierarchy cutover

Status: Accepted
Scope: Project architecture / cross-owner Acceptance contracts and roadmap
Source: Operator instruction
Date: 2026-10-08
Review At: completed on remediation acceptance, 2026-10-08
Blocking: prerequisite satisfied for Pack 0010; this decision remains binding for every later Acceptance Pack
Closure Condition: superseded by a separately approved project architecture decision
Next Review At: before any proposal to change the accepted hierarchy contract

## Type

6. Change Request / Remediation Trigger and Phase/Release Decision

## Decision

TMS adopts a clean cutover to the only supported executable identity:

```text
App -> Component -> Suite -> Scenario -> Variant -> Step
```

No runtime compatibility adapter, fallback provider, reserved `legacy` Component/Suite identity, default synthetic hierarchy, dual request shape, dual output projection, alias, deprecation window, or old/new branching is allowed for this transition.

The cutover removes the obsolete `AcceptanceApp::scenarios()` discovery contract, the optional `AcceptanceCatalogProvider` split, registry snapshot/materialization fallback, the classification-style `ScenarioMetadata::$suites` field, and the `manual-only` runtime disposition. Every registered App must implement one complete hierarchy-aware provider contract. Component, Suite, Scenario, and Variant identity is explicit from registration through planning, dispatch, operation DTOs, CLI presentation, logs, and new execution records.

Existing `acceptance:list`, `acceptance:plan`, and `acceptance:run` clients move to one version-2 hierarchy contract. Their old JSON shape, positional signature, selector meaning, and default-variant behavior are not retained. Machine output must declare its schema version. The run request identifies the full App/Component/Suite/Scenario/Variant tuple.

Accepted Pack and Run records remain immutable execution history. They may describe the former contract, but they are not active implementation instructions after this decision. Current source is corrected through one approved cross-owner remediation before Pack 0010 is normalized and executed.

The active roadmap is now:

| Stage | Owner | Delivery |
|---:|---|---|
| 1 | TMS project | Accepted transport-neutral operation foundation — Pack 0009 |
| 2 | TMS project with explicit Core scope | Atomic hierarchy hard cutover — Remediation 0001 |
| 3 | TMS project | Clean Nwidart target-module and Component SDK — Pack 0010 |
| 4 | TMS project | Operator input and approval workflow — Pack 0011 |
| 5 | TMS project | Target resource lifecycle contracts — Pack 0012 |
| 6 | Core | Extensible executor and capability runtime — Core Pack 0003 |
| 7 | TMS project | Async Queue/Bus Batch orchestration — Pack 0013 |
| 8 | TMS project | Query, reporting, evidence, and traceability — Pack 0014 |
| 9 | TMS project | Complete Artisan operator client — Pack 0015 |
| 10 | TMS project | DK Nwidart module and owner bootstrap — Pack 0016 |
| 11–15 | activated DK/related owners | Target profile, ND contract/fake, corpus import, bounded scenario groups, coverage reconciliation |
| 16 | TMS project | Second-target proof and scale hardening — Pack 0017 |

## Reason

The temporary compatibility design would leave two provider models and two identity contracts in the same codebase. That would spread transitional branches through registry, catalog, planner, dispatcher, CLI, tests, and future target modules. The operator explicitly rejected that approach and required the old capability to be replaced completely.

## Impact

- Decision 0007's hierarchy remains accepted and is tightened to require one complete provider contract.
- Decision 0008's transport-neutral service boundary remains accepted; its compatibility clause is superseded.
- Decision 0012's removal of manual-only runtime behavior is immediate rather than deferred.
- Decision 0013 is superseded as the active roadmap by the sixteen-stage sequence above.
- Decision 0014's typed DTO/client-owned presentation boundary remains accepted; its legacy CLI-preservation clauses are superseded.
- Accepted Packs 0004, 0007, and 0009 and their Runs remain historical evidence. Their obsolete runtime contracts are corrected by Remediation 0001.
- Pack 0010 is blocked until Remediation 0001 is accepted, then must be normalized against the clean source.
- Draft Packs 0011–0017 and Core Pack 0003 must be normalized or regenerated against this decision before execution.
- The cutover is intentionally backward incompatible. No production target App exists, so no target implementation migration is required.

## Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: Not applicable
UI / Admin UI Error Impact: No Web/Admin UI; CLI contract changes
Error Code Impact: Yes — obsolete compatibility-only paths/codes are removed; hierarchy validation remains explicit
Exception Handling Impact: Yes — no fallback from a failed hierarchy provider to old discovery
External Error Normalization Impact: Not applicable
Retry / Failure Classification Impact: No
Status Model Impact: Yes — manual-only runtime disposition is removed
Logging Impact: Yes — successful and failed execution context uses the complete hierarchy tuple
Traceability Impact: Yes — full tuple identity is propagated consistently
Sensitive Data Impact: No new sensitive values
Testing Impact: Yes — compatibility assertions are replaced by version-2 contract assertions
Documentation Impact: Yes
```

No raw exception, path, source-case payload, credential, selector, URL, or environment value becomes visible. Existing safe-output and synthetic-test constraints remain active.

## Files to Update

- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/decisions/TMS-DECISION-0008-transport-neutral-operations-and-artisan-client.md`
- `docs/project/ai/decisions/TMS-DECISION-0012-automated-e2e-coverage-and-traceability.md`
- `docs/project/ai/decisions/TMS-DECISION-0013-fifteen-stage-layered-delivery-roadmap.md`
- `docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0004.md`
- project/Core remediation and Pack indexes
- Pack 0010 and successor Draft status/navigation records

## Required change request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0004.md`.

## future-work

- Execute normalized Pack 0010 under its own gate.
- Normalize each later Pack immediately before execution; do not copy compatibility language from historical Packs/Runs.
- Decide migration handling for any non-empty operator database before applying the hierarchy execution-record migration. Tests remain empty, disposable, in-memory SQLite.

## Review / Resolution

```text
Reviewed In: operator response during the Pack 0010 pre-execution gate on 2026-10-08
Review Result: operator rejected the legacy adapter and required complete replacement by the new strategy and roadmap
Resolution: accepted
Next Review At: before any proposal to change the accepted hierarchy contract
Resolution Notes: Remediation 0001 was validated and accepted on 2026-10-08. Pack 0010 was normalized against the clean source; its execution and acceptance remain separate gates, as does commit.
```

## Notes

No source change is authorized by this Decision alone. No target, browser, external network, Composer, queue, cache, or environment operation is authorized.
