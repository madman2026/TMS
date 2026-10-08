# TMS-DECISION-0013 — Fifteen-stage layered delivery roadmap

Status: Superseded — retained as accepted history
Scope: Project roadmap / cross-owner delivery order
Source: Operator decision
Date: 2026-10-07
Review At: completion of each stage
Blocking: Yes for Pack ordering and ownership
Closure Condition: superseded by a separately approved roadmap decision
Next Review At: Pack 0009 normalization

Current Roadmap Notice (2026-10-08): Decision 0015 supersedes this delivery order with a sixteen-stage roadmap whose stage 2 is the atomic hierarchy-cutover remediation. This record remains historical and must not direct Pack execution.

## Type

4. Phase/Release decision

## Decision

The approved architecture is delivered through fifteen ordered stages. Stages 1–9 and 15 have legitimate current project/Core owners and receive Draft Pack records now. Stages 10–14 are deliberately generated only after Pack 0016 creates and activates the DK module owner and after the relevant DK/ND external-owner contracts are available. Creating project-level placeholder Packs for those owner-local implementations is forbidden.

| Stage | Owner | Delivery |
|---:|---|---|
| 1 | TMS project | Operation/application service layer — project Pack 0009 |
| 2 | TMS project | Nwidart target-module and Component SDK/scaffolding — project Pack 0010 |
| 3 | TMS project | Operator input and approval workflow — project Pack 0011 |
| 4 | TMS project | Target resource/readiness/fixture/oracle/cleanup contracts — project Pack 0012 |
| 5 | Core | Extensible executor registry and Browser/API capability runtime — Core Pack 0003 |
| 6 | TMS project | Async Queue/Job/Bus Batch orchestration — project Pack 0013 |
| 7 | TMS project | Query, reporting, evidence metadata, and traceability — project Pack 0014 |
| 8 | TMS project | Complete interactive and structured Artisan client — project Pack 0015 |
| 9 | TMS project | DK Nwidart module bootstrap and documentation-owner activation — project Pack 0016 |
| 10 | DK module | DK target profile, environment, auth/account, fixture/oracle/cleanup foundation — create after stage 9 |
| 11 | DK/ND owners | ND fake Provider, simulated Callback, worker/time/audit oracles and contract alignment — create after stage 10 |
| 12 | DK module | ND 139-use-case/24,668-scenario import, mapping, deduplication and traceability — create after stage 11 |
| 13 | DK module | First independently bounded ND scenario group — create after stage 12 |
| 14 | DK module | Remaining ND scenario groups and full coverage reconciliation — create after stage 13 |
| 15 | TMS project | Second-target onboarding proof, 24,668-scale validation, concurrency and recovery hardening — project Pack 0017 |

Every Draft is normalized against accepted predecessor source immediately before approval. Pack execution, acceptance, review, and commit remain separate gates. The earlier CLI-only seven-Pack roadmap and Draft Pack 0008 are superseded. Accepted Packs 0001–0004 and 0007 and Core Pack 0001 remain valid foundation evidence.

## Reason

The sequence builds reusable boundaries before target code, then activates the correct owner before DK/ND implementation. It provides the requested full plan without placing target-specific decisions in shared TMS/Core documentation.

## Impact

- Decision 0004's roadmap and project Draft Pack 0008 are superseded.
- Decision 0005's testing/staging, synthetic-data, App ownership, and safe-output constraints remain active.
- Decision 0006's deleted proposals remain deleted; the new Packs are fresh, narrower designs approved by this operator decision and Change Request 0003.
- Stage 15 cannot execute until the stage 10–14 Pack records exist and their required source is accepted.

## Error Handling / Logging / Traceability Impact

Each stage must implement the impacts declared by Decisions 0007–0012. This roadmap adds no runtime error code or state by itself.

## Required Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0003.md`.

## Required Follow-up Updates

- Create stages 10–14 in the owner-local indexes at their activation gates.
- Keep project, Core, DK, and ND contracts as references across owners rather than duplicate specifications.
- Update the roadmap status after each accepted Pack.

## Review / Resolution

```text
Reviewed In: operator request for complete Packs and subsequent ownership/Nwidart decisions
Review Result: operator approved the architecture decisions and requested their registration and Pack creation
Resolution: accepted
Next Review At: Pack 0009 normalization
Resolution Notes: ten current Pack records represent stages 1–9 and 15; five owner-local Packs are intentionally deferred until their owner exists.
```
