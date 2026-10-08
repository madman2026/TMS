# AI-PACK-TMS-SECOND-TARGET-SCALE-HARDENING-0017 — Second-target proof and scale hardening

Status: `draft — stage 16 under Decision 0015; regenerate after owner-local stages 11–15`

Generated: `2026-10-07`

Decision: Decisions 0010–0013.

Current Roadmap Notice (2026-10-08): Decision 0015 supersedes former stage numbering and compatibility assumptions. Regenerate this final Pack from accepted stages 1–15 and the single version-2 hierarchy contract.

## 1. Task ID

`AI-PACK-TMS-SECOND-TARGET-SCALE-HARDENING-0017`

## 2. Task Title

Prove repeatable second-target onboarding and harden planning/execution/reporting at 24,668-scenario scale.

## 3. Goal

Demonstrate that the platform is reusable beyond DK and meets bounded performance, parallelism, recovery, and full traceability requirements.

## 4. Context

This Pack is intentionally drafted now but cannot be normalized until DK/ND stages 10–14 are accepted. It validates the architecture rather than adding another product integration.

## 5. Related Release / Phase

Decision 0013, stage 15.

## 6. Related Epic / Feature / Story

Platform readiness and reusable onboarding proof.

## 7. Source References

Decisions 0007–0013; all accepted stages 1–14 and their current source/Run/Reviews; measured DK corpus/report evidence.

## 8. Files to Create

- `tests/Feature/SecondTargetOnboardingTest.php`
- `tests/Feature/AcceptanceLargeCatalogTest.php`
- `tests/Feature/AcceptanceParallelRecoveryTest.php`
- `tests/Feature/AcceptanceFullTraceabilityTest.php`
- `docs/project/ai/guides/TARGET-APP-ONBOARDING.fa.md`

## 9. Files to Edit

- `config/acceptance.php`
- `app/Services/AcceptanceCatalog.php`
- `app/Services/AcceptancePlanner.php`
- `app/Acceptance/Execution/AcceptanceBatchService.php`
- `app/Acceptance/Execution/AcceptanceRecoveryService.php`
- `app/Acceptance/Reporting/AcceptanceQueryService.php`
- `app/Acceptance/Reporting/AcceptanceReportService.php`
- `docs/project/ai/guides/GUIDES-INDEX.md`
- `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`
- project Pack/Run/Review indexes required by execution reporting

## 10. Files to Read / Reference

Must be replaced at regeneration with exact accepted stage 1–14 source, Runs, Reviews, DK owner manifest/index, measured bottlenecks, and applicable performance/queue/data/testing rules. No broad repository read.

## 11. Configuration / Settings Requirements

Tune only approved non-sensitive catalog page, batch chunk, max in-flight, timeout, lock, and report limits based on measurements. No production env, Admin UI, target secret, or external integration. Tests use bounded synthetic generators and queue fakes/test workers.

## 12. Do Not Change

DK/ND business scenarios/contracts except separately approved concrete defects, real target systems, dependencies, `.env`, Web/API/MCP, production infrastructure, or subjective UX policy.

## 13. Clarification Questions Before Implementation

Regeneration must set quantitative time/memory/query/concurrency/recovery budgets from the execution environment, exact second-target proof method, data volume, worker topology, and concrete edit allowlist based on measured findings.

## 14. Multilingual / Translation Requirements

Create the Persian onboarding guide; machine outputs remain language-neutral. No UI/RTL. Validate technical terms and command examples.

## 15. UI / Admin UI Requirements

No UI/Admin UI.

## 16. Operator Documentation Requirements

Create end-to-end target onboarding guidance and update CLI scale/worker/recovery/troubleshooting sections with measured safe defaults.

## 17. Implementation Rules

- Prove a second target by scaffolding two isolated temporary target modules/apps in tests; do not commit a fake production module.
- Generate 139 use cases and 24,668 source mappings/scenarios synthetically with representative hierarchy/cardinality.
- Measure catalog laziness, planning, batch creation, bounded dispatch, status/report queries, retry/resume/cancel, and full reconciliation.
- Optimize only measured bottlenecks inside exact allowed files; no speculative redesign.

## 18. Architecture Constraints

The proof must use public scaffold/operation/executor/report contracts, not test-only shortcuts that bypass architecture. Target isolation and explicit registration are mandatory.

## 19. Validation Rules

Meet approved budgets with bounded memory/query counts, no full eager materialization, no duplicate logical execution, deterministic recovery, complete source dispositions, exact aggregate counts, and repeatable second-target onboarding.

## 20. Security Rules

Synthetic values only. Scan generated requests, jobs, DB, logs, reports, and guide examples for credential/token/session/personal-data sentinels. No network/real target.

## 21. Error Handling / Logging / Traceability Requirements

Exercise contention, timeout, stale worker, duplicate dispatch, partial failure, cancel race, report limit, and invalid second-target registration. Verify stable codes, classifications, trace chain, and no log flood/sensitive data. Add new codes only through an approved regeneration decision.

## 22. Data Model / Migration / Relationship Requirements

No planned migration. If measurements require schema/index changes, stop and regenerate the Pack with exact migration/FK/rollback/retention scope and required approval.

## 23. Commenting Requirements

Comment only measurement-backed bounds/algorithms and recovery invariants.

## 24. Testing Requirements

Meaningful scale/integration tests with explicit budgets and repeatability; second-target isolation; queue concurrency/recovery; full 139/24,668 reconciliation; query/memory instrumentation; security scans; all affected regressions. Avoid timing-only flaky assertions by using generous environment-calibrated upper bounds and structural bounds.

## 25. Acceptance Checklist

- [ ] second target onboards through public contracts;
- [ ] 24,668-scale plan/batch/report stays within approved bounds;
- [ ] parallel execution and recovery preserve idempotency;
- [ ] all 139 use cases/source cases reconcile;
- [ ] guides are accurate;
- [ ] no real target or speculative redesign.

## 26. Tests to Add

Exact four tests in section 8; regeneration may split them only with an exact updated file list.

## 27. Tests to Run

Regenerated Pack must state exact scale command, environment budgets, worker/fake mode, full affected PHPUnit suite, documentation checks, security scans, and `git diff --check`.

## 28. Expected Output

Measured reusable onboarding/scale/recovery proof, minimal evidence-backed tuning, Persian onboarding guide, Run Report, final Review, and index updates.

## 29. Operator Execution Checklist

Before: approve budgets/topology and regenerated files. After: inspect measurements, second-target tree, traceability reconciliation, recovery demo, and guides.

## 30. Agent Final Report

Report environment/budgets, before/after measurements, exact optimizations, second-target proof, 139/24,668 counts, recovery/security evidence, tests, and limitations.

## 31. Review Checklist

Review measurement validity, flake risk, architectural path, isolation, idempotency, full reconciliation, docs, security, and exact scope.

## 32. Rollback / Safety Notes

Rollback only evidence-backed tuning/config changes; temporary modules/data are confined to test storage. Preserve operational history and never reset/delete real target data.

## 33. Stop Conditions

Stop before regeneration, for missing stage 10–14 acceptance, undefined budgets, real target/network need, schema change without revised Pack, flaky/unbounded tests, or out-of-scope product defects.

## 34. Open Questions

All section 13 budgets and the exact accepted stage 10–14 source are intentionally unresolved until final-stage regeneration.
