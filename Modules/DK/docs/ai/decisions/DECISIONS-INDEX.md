# DK DECISIONS INDEX

| Decision | Status | Scope | File |
|---|---|---|---|
| Isolated target foundation boundary (0001) | accepted — operator accepted 2026-10-10; binding for DK stages 11–15 | DK owner / stages 11–15 | `Modules/DK/docs/ai/decisions/DK-DECISION-0001-isolated-target-foundation-boundary.md` |
| First bounded ND Browser Acceptance group (0002) | accepted — Acceptance-only scope; shared Pack 0019 is implemented/validated but its Run acceptance and the DK execution gate remain pending | DK stage 14 / Pack 0004 | `Modules/DK/docs/ai/decisions/DK-DECISION-0002-first-bounded-nd-browser-acceptance-group.md` |
| Operations Health fixture lease lifecycle (0003) | accepted — run-owned lease, target clock, reverse cleanup, and baseline verification approved 2026-10-12; recipe/binding architecture later resolved by Decision 0004 | DK stage 14 / Pack 0004 | `Modules/DK/docs/ai/decisions/DK-DECISION-0003-operations-health-fixture-lease-lifecycle.md` |
| Target-control binding and Operations Health recipes (0004) | accepted — generic protocol/DK adapter, five recipe keys, secret-handle boundary, and bounded observation fixed 2026-10-12; enabling Packs/execution remain gated | DK stage 14 / Pack 0004 | `Modules/DK/docs/ai/decisions/DK-DECISION-0004-target-control-binding-and-operations-health-recipes.md` |

Project bootstrap, hierarchy, target-resource, protocol, and coverage decisions remain project-owned. Read DK Decision 0001 for the binding isolated target boundary. Read DK Decisions 0002–0004 before reviewing or normalizing Pack 0004; they select the first Browser Acceptance group, defer API/Fake expansion, define its run-owned fixture/clock lifecycle, and fix the reusable target-control/recipe/observation binding.
