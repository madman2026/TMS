# AI-PACK-TMS-TARGET-RESOURCE-LIFECYCLE-0012 — Target resource lifecycle contracts

Status: `draft — stage 5 under Decision 0015; normalize after accepted Pack 0011`

Generated: `2026-10-07`

Decision: Decisions 0005, 0007, 0009, 0013, and 0014; Decision 0014 applied on 2026-10-08 (documentation only).

Current Roadmap Notice (2026-10-08): Decision 0015 supersedes former stage numbering and compatibility assumptions. Normalize this Draft against the single version-2 hierarchy contract before approval.

## 1. Task ID

`AI-PACK-TMS-TARGET-RESOURCE-LIFECYCLE-0012`

## 2. Task Title

Define minimal target-owned readiness, account, fixture, oracle, and cleanup boundaries.

## 3. Goal

Give automated scenarios safe resources without hardcoded target data or target behavior in shared TMS.

## 4. Context

Login and business tests need accounts/data and reliable cleanup. The cancelled generic fixture/secret proposals do not exist. This fresh Pack implements only the newly approved App-owned contracts.

## 5. Related Release / Phase

Decision 0013, stage 4.

## 6. Related Epic / Feature / Story

Target resource preparation and observable E2E assertions.

## 7. Source References

Decisions 0005–0007, 0009, 0013; accepted Packs 0009–0011 source; Decision 0006 only as the deletion boundary.

`docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md` — semantic DTOs and presentation boundaries.

## 8. Files to Create

- `app/Acceptance/Targets/Contracts/TargetReadinessProbe.php`
- `app/Acceptance/Targets/Contracts/TargetAccountResolver.php`
- `app/Acceptance/Targets/Contracts/TargetFixtureManager.php`
- `app/Acceptance/Targets/Contracts/TargetOracle.php`
- `app/Acceptance/Targets/Contracts/TargetCleanup.php`
- `app/Acceptance/Targets/Data/TargetContext.php`
- `app/Acceptance/Targets/Data/ResourceReference.php`
- `app/Acceptance/Targets/Data/CleanupResult.php`
- `app/Acceptance/Targets/TargetResourceCoordinator.php`
- `tests/Unit/TargetResourceCoordinatorTest.php`
- `tests/Unit/TargetResourceContractSafetyTest.php`

## 9. Files to Edit

- operation request/result contracts and prerequisite service from Packs 0009/0011
- hierarchy contracts from Pack 0010
- `app/Providers/AppServiceProvider.php`
- project Pack/Run/Review indexes required by execution reporting

## 10. Files to Read / Reference

Project profile; Decisions 0005, 0006, 0007, 0009, 0014; accepted predecessor source; scope/error/security/test rules; section 8–9 files.

## 11. Configuration / Settings Requirements

No shared target config, `.env`, Admin UI, external integration setting, or secret manager. Target modules will own environment/account/provider configuration. Shared tests use fakes and explicit testing/staging readiness results.

## 12. Do Not Change

Actual DK/ND resources, Profile credential storage, generic secret store/lease, target DB, Core executors, queue, commands, API/UI/MCP, dependencies, `.env`, production systems.

## 13. Clarification Questions Before Implementation

Normalization must approve lifecycle order, cleanup failure precedence, safe reference fields, readiness outcome contract, and which coordinator steps are always invoked on cancellation/failure.

Define typed readiness/resource/cleanup result fields and exact additional DTO paths before execution approval; do not specify client JSON or prompt layout as a lifecycle service contract.

## 14. Multilingual / Translation Requirements

No visible text, translation, API message, or RTL change. Codes and resource types are language-neutral.

## 15. UI / Admin UI Requirements

No UI/Admin UI.

## 16. Operator Documentation Requirements

Pack 0015 must explain readiness failures and App-owned account/resource setup. No target-specific setup is documented here.

## 17. Implementation Rules

- Lifecycle: validate readiness, resolve references, provision synthetic fixtures, execute, query oracles, cleanup in finally/cancel paths.
- App implementations own all target I/O and return normalized safe DTOs.
- Coordinator/operation results expose immutable readiness/reference/oracle/cleanup data and machine codes only. They neither encode CLI/API JSON nor call Prompts; each client presents the same safe DTO under Decision 0014.
- Shared coordinator never interprets credentials, selectors, target payloads, provider responses, or business rules.
- Cleanup is idempotent and reports safe partial failure.

## 18. Architecture Constraints

These are orchestration contracts, not a resource database or generic secret framework. Production/unknown readiness is rejected before target setup.

## 19. Validation Rules

Using fakes, prove lifecycle order, no setup on unsafe target, cleanup on pass/fail/exception/cancel, idempotent cleanup, typed oracle/cleanup result propagation, and reference/sentinel omission. Assert semantic DTOs independently of client rendering and retain the primary-versus-cleanup outcome distinction.

## 20. Security Rules

ResourceReference holds opaque non-secret IDs only. Raw credentials, cookies, personal identifiers, URLs with secrets, payloads, and external errors never cross shared DTO/log/report boundaries.

## 21. Error Handling / Logging / Traceability Requirements

Normalize target_not_ready, resource_unavailable, fixture_setup_failed, oracle_failed, cleanup_failed, and unsafe_target. Classify retry/operator action in DTOs. Logs include operation/batch/item IDs and resource type/reference hash when safe, never raw values. Cleanup failure cannot hide the primary execution outcome.

## 22. Data Model / Migration / Relationship Requirements

No migration/model. Resource state lives in target systems or future operation/item allowlisted snapshots. No retention/backfill/rollback data impact.

## 23. Commenting Requirements

Document lifecycle/finally behavior, primary-vs-cleanup failure precedence, and opaque reference boundary.

## 24. Testing Requirements

Unit tests with fake target adapters cover all lifecycle and error paths, call order, idempotency, cancellation, unsafe target rejection, structured logs, and sensitive sentinel omission. No real browser/provider/target/database I/O.

## 25. Acceptance Checklist

- [ ] target owners can supply account/fixture/oracle/cleanup adapters;
- [ ] shared code stays target-neutral;
- [ ] unsafe targets stop before setup;
- [ ] cleanup is deterministic and idempotent;
- [ ] no deleted secret/fixture framework is recreated.

## 26. Tests to Add

Exact tests in section 8 with fake adapters for success, each failure stage, cancel, and duplicate cleanup.

## 27. Tests to Run

Exact PHPUnit file list, operation/prerequisite regressions, and `git diff --check`. No external I/O.

## 28. Expected Output

Minimal target resource contracts/coordinator, fake-only tests, Run Report, and index updates.

## 29. Operator Execution Checklist

Before: approve lifecycle/error precedence. After: review fake pass/fail/cancel traces and sentinel omission.

## 30. Agent Final Report

Report contract boundaries, lifecycle evidence, errors/logging, tests, and deferred target implementations.

## 31. Review Checklist

Review target isolation, cleanup, safe references, testing/staging check, failure precedence, and absence of recreated cancelled infrastructure.

## 32. Rollback / Safety Notes

Source-only rollback. No target or DB cleanup should be required because execution tests use fakes.

## 33. Stop Conditions

Stop for target-specific shared code, real target access, secret persistence, production ambiguity, required DB/queue/Core change, or unaccepted predecessors.

## 34. Open Questions

Exact readiness proof and cleanup timeout contract are blocking normalization decisions.
