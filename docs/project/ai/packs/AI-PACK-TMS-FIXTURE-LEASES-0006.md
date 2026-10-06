# AI-PACK-TMS-FIXTURE-LEASES-0006 — Isolated Fixture Leases and Cleanup Lifecycle

Status: `ready — normalized proposal; separate execution approval required`

Generated: 2026-10-05
Normalized: 2026-10-06, against HEAD bbedbbf0e86e94e2497e8dd80c36493a84fc0b8a on main and accepted uncommitted Core Pack 0001 source.
Decision: `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`; unchanged CLI/ownership constraints from Decision 0004 apply.
Depends On: accepted project Pack 0004 / Run 001 and Core Browser Observability Pack 0001 / Run 001.
Execution Gate: not approved. Acceptance of Core Pack 0001 authorizes lifecycle maintenance/this proposal, not its implementation. Cancelled Core 0002/project 0005 are not prerequisites.

## 1. Task ID

AI-PACK-TMS-FIXTURE-LEASES-0006

## 2. Task Title

Add project-owned isolated synthetic fixture setup, leases, cleanup, and verification.

## 3. Goal

Wrap root acceptance execution with a minimal testing/staging target guard and optional App-owned fixture lifecycle. Allocate an opaque lease before setup; execute a fresh lease-bound scenario; clean and verify before persisting the final result. No Core contract change or target adapter implementation.

## 4. Context

AcceptanceRunService creates a pending Test, calls Core Runner, then persists safe Run/Step results. Runner owns browser/context cleanup. Registry eagerly retains scenario objects/metadata; TestContext has no fixture seam. An optional root App interface can bind a fresh scenario to an opaque lease without editing Core.

The environment guard applies to every RunService run, including non-fixture Apps. Apps without the new declaration fail as unknown before provider/Runner setup. This intentional root execution change prevents a legacy App bypass. Low-level Core Runner remains target-neutral; this Pack does not certify every direct caller. App constructors/registration/key/name/metadata/environment/provider selection must have no target/auth/browser I/O.

## 5. Related Release / Phase

Not applicable; pre-Release project orchestration.

## 6. Related Epic / Feature / Story

Reusable acceptance automation — deterministic synthetic prerequisites.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0002-generic-code-based-acceptance-apps.md`
- `docs/project/ai/decisions/TMS-DECISION-0003-cli-first-acceptance-execution.md`
- `docs/project/ai/decisions/TMS-DECISION-0004-cli-only-staged-generic-acceptance-roadmap.md`
- `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`
- `docs/project/ai/runs/AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004-run-001.md`
- `Modules/Core/docs/ai/runs/AI-PACK-CORE-BROWSER-OBSERVABILITY-0001-run-001.md`
- `Modules/Core/docs/ai/reviews/REVIEW-CORE-BROWSER-OBSERVABILITY-0001-acceptance-001.md`

Source paths in section 10 control current implementation; superseded proposals are not inputs.

## 8. Files to Create

Exact implementation/test allowlist:

- `app/Contracts/TargetEnvironmentAwareApp.php`
- `app/Contracts/FixtureAcceptanceApp.php`
- `app/Contracts/FixtureProvider.php`
- `app/Data/FixtureLease.php`
- `app/Enums/TargetEnvironment.php`
- `app/Enums/FixtureLeaseState.php`
- `app/Exceptions/FixtureLifecycleException.php`
- `app/Services/FixtureLifecycle.php`
- `tests/Unit/FixtureLifecycleTest.php`
- `tests/Feature/AcceptanceFixturePersistenceTest.php`

Project operator documentation with new bindings proposed in section 16:

- `docs/project/guides/TMS-ACCEPTANCE-CLI-GUIDE.fa.md`
- `docs/project/guides/GUIDES-INDEX.md`

Deferred until final execution/reporting gate:

- `docs/project/ai/runs/AI-PACK-TMS-FIXTURE-LEASES-0006-run-001.md`

Fakes/recording logger stay inside these test files. No target recipe/shared fake source.

## 9. Files to Edit

- `app/Services/AcceptanceRunService.php`
- `tests/Feature/AcceptanceRunPersistenceTest.php` — environment-aware fake Apps and affected assertions only; retain regression tests.
- `tests/Feature/AcceptanceRunCommandTest.php` — real-service guard/fixture cases with mocked Runner; retain existing contract/default tests.
- `docs/project/ai/TMS-AI-PROFILE.md` — section 16 guide bindings only.
- `docs/project/PROJECT-DOCS-INDEX.md` — guide navigation only.
- `docs/project/ai/packs/AI-PACK-TMS-FIXTURE-LEASES-0006.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md`

Keep RunService's first Runner constructor argument and four-argument run signature. Add optional FixtureLifecycle dependency; if omitted, construct it with existing Runner/Laravel logger. Lifecycle takes AcceptanceRunner and PSR LoggerInterface; unit tests inject directly. No Core/provider-registration/Registry/command-source/schema/config edit.

## 10. Files to Read / Reference

- `AGENTS.md`, `docs/ai/start/START-HERE.md`, `docs/ai/start/CONTEXT-MAP.md`, `docs/project/PROJECT-DOCS-INDEX.md`, `docs/project/ai/TMS-AI-PROFILE.md`
- `Modules/Core/AGENTS.md`, `Modules/Core/docs/ai/README.md`, `Modules/Core/docs/ai/manifest.yaml`, `Modules/Core/docs/ai/GOVERNANCE-PROFILE.md`
- `docs/ai/rules/SCOPE-CONTROL-RULES.md`, `docs/ai/rules/AI-EXECUTION-RULES.md`, `docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md`, `docs/ai/rules/GIT-AND-COMMIT-RULES.md`, `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`, `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md`, `docs/ai/rules/COMMENTING-RULES.md`, `docs/ai/rules/REPORTING-RULES.md`, `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`, `docs/ai/rules/OPERATOR-GUIDE-RULES.md`
- `docs/ai/rules/AI-PACK-GENERATION-RULES.md`, `docs/ai/templates/AI-PACK-TEMPLATE.md`, `docs/ai/templates/AGENT-FINAL-REPORT-TEMPLATE.md`, `docs/ai/templates/RUN-REPORT-TEMPLATE.md`
- `docs/project/references/REFERENCES-INDEX.md`, `docs/project/references/SOURCE-DOCS-INDEX.md`, `docs/project/references/TMS-SOURCE-STRUCTURE-SUMMARY.md`
- Exact section 7 records and `docs/project/ai/packs/AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004.md`
- `app/Services/AcceptanceRunService.php`, `app/Services/AcceptanceAppRegistry.php`, `app/Console/Commands/RunAcceptanceCommand.php`, `app/Models/Profile.php`, `app/Models/Test.php`, `app/Models/Step.php`, `app/TestStatusEnum.php`
- `Modules/Core/app/Contracts/AcceptanceApp.php`, `Modules/Core/app/Contracts/AcceptanceScenario.php`, `Modules/Core/app/Contracts/StepResult.php`, `Modules/Core/app/Data/RunOptions.php`, `Modules/Core/app/Data/RunResult.php`, `Modules/Core/app/Data/ScenarioMetadata.php`, `Modules/Core/app/Services/AcceptanceRunner.php`, `Modules/Core/app/Exceptions/AcceptanceExecutionException.php`
- `tests/Feature/AcceptanceRunPersistenceTest.php`, `tests/Feature/AcceptanceRunCommandTest.php`, `tests/Unit/AcceptanceAppRegistryTest.php`, `tests/TestCase.php`, `phpunit.xml`, `composer.json`, `composer.lock`, `.gitignore`

Never read .env/cached config; do not load all Packs/references.

## 11. Configuration / Settings Requirements

No expanded browser/Profile/CLI settings or effective-settings snapshot. Existing RunOptions/defaults unchanged. Fixture support is code-owned and off unless App implements FixtureAcceptanceApp; enabled Apps must supply a provider, with no silent fallback.

TargetEnvironmentAwareApp supplies a pure code declaration independent of host APP_ENV. Only TESTING/STAGING proceed. Missing/throwing/UNKNOWN rejects; PRODUCTION forbids. This is a trusted adapter declaration, not remote attestation. Future target integration must prove its selected endpoint/account matches the declaration.

Tests require fake providers/mocked Runner/in-memory SQLite only. Section 27 permits temporary process test setup, not config/.env/credential changes.

## 12. Do Not Change

Core source/tests, target adapters/repos/recipes/auth/URLs/selectors, real providers/recipients, production/non-synthetic data, credential infrastructure, comprehensive capture guards, recording/export/artifact storage, expanded settings, catalog/batch/recovery, API/UI, queue/scheduler/parallel/retry, dependencies/vendor/node_modules, .env/cached config, schema/models/factories/seeders, Registry/command signature, browser installation or generated assets. Preserve accepted uncommitted Core/roadmap work.

## 13. Clarification Questions Before Implementation

Resolved as this proposal: process-local states/ordering in section 17; no durable leases/crash recovery (reassess Pack 0008); exact environment signal/errors in sections 17/21; cleanup failure overrides earlier execution failure while a safe primary code remains traceable. No deferred design field; separate execution approval remains required.

## 14. Multilingual / Translation Requirements

No UI/API translation. CLI preserves existing JSON and machine codes. Exception text is fixed internal English, never emitted to CLI. New operator guide is Persian with code blocks for commands/paths; no screenshot/RTL UI.

## 15. UI / Admin UI Requirements

No UI changes required.

## 16. Operator Documentation Requirements

Operator Documentation Impact: Yes. Current project profile/router has no CLI-guide binding. This proposal adds project-owned bindings only on implementation approval: rules `docs/ai/rules/OPERATOR-GUIDE-RULES.md`; guide index `docs/project/guides/GUIDES-INDEX.md`; guide `docs/project/guides/TMS-ACCEPTANCE-CLI-GUIDE.fa.md`; language Persian; audience trusted local CLI operator/App integrator; assets not applicable. No separate guide template is currently declared: use the workflow-first content required by the shared guide rule.

Add exact bindings to TMS-AI-PROFILE and guide navigation to PROJECT-DOCS-INDEX. Explain existing command prerequisites, code-owned testing/staging declaration, synthetic fixture activation, pending/finished/failed status, setup/cleanup ownership, failure codes/Test-id correlation and manual investigation of residual state. No new authorization role; require existing local CLI/repository/database access. Mark any unverified target-specific permission as to be confirmed.

Explain forced termination has no cleanup guarantee; a rerun creates a fresh lease, not recovery. Future target recipe/account documentation stays App-owned. No secret/capture/settings setup guide.

## 17. Implementation Rules

### Exact contracts

All new types are root App-owned:

| Type | Exact contract |
|---|---|
| TargetEnvironment | string-backed enum TESTING=testing, STAGING=staging, PRODUCTION=production, UNKNOWN=unknown. |
| TargetEnvironmentAwareApp | extends Core AcceptanceApp; targetEnvironment(): TargetEnvironment; pure, no I/O. |
| FixtureAcceptanceApp | extends TargetEnvironmentAwareApp; fixtureProvider(AcceptanceScenario): FixtureProvider; bindFixture(AcceptanceScenario, FixtureLease): AcceptanceScenario. Provider selection is pure; binding returns fresh scenario without changing/caching lease state in original Registry object. |
| FixtureProvider | prepare(FixtureLease): void; cleanup(FixtureLease): void; verifyCleanup(FixtureLease): bool. No setup payload return. Cleanup handles partial prepare and is idempotent by lease id. |
| FixtureLease | final readonly: locally generated UUID v4 id:string and positive testId:int only; validating constructor; toArray() returns exactly lease_id/test_id. No free metadata, recipe, credential, target locator, provider object or callback. |
| FixtureLeaseState | string-backed enum ALLOCATED=allocated, PREPARING=preparing, READY=ready, CLEANING=cleaning, CLOSED=closed, CLEANUP_FAILED=cleanup_failed. Manager-local; provider cannot set state. |
| FixtureLifecycleException | final RuntimeException; readonly allowlisted errorCode, retryable=false; fixed message “Fixture execution could not be completed safely.”; no previous Throwable/raw input. |
| FixtureLifecycle | inject Core AcceptanceRunner and PSR LoggerInterface; execute(int testId, AcceptanceApp, AcceptanceScenario, RunOptions): RunResult. Synchronous, no global/durable lease registry. |

### Ordering and states

1. RunService creates existing local pending Test and obtains existing options. Lifecycle checks declaration; reject before provider selection/binding/Runner. Test record is local orchestration, not target setup.
2. Permitted App without FixtureAcceptanceApp delegates directly to Runner with original App/scenario/options; guard still mandatory.
3. Enabled App supplies provider; wrong/missing/throwing selection fails. Allocate UUID lease with current Test id before target setup.
4. ALLOCATED -> PREPARING; enter finally-protected region before prepare so partial failure cleans. Success -> READY.
5. Bind fresh scenario; require different object identity and matching original key/name and all five ScenarioMetadata fields by strict value comparison. Current metadata has no toArray method. Throw/reused object/mismatch fails before Runner. Never register/persist the bound object.
6. Runner executes bound scenario with original App/options; it closes browser/context before returning/throwing. Shared code adds no authentication callback.
7. Finally: CLEANING; cleanup exactly once. If it succeeds, verify exactly once: true -> CLOSED, false/throw -> CLEANUP_FAILED. Cleanup throw -> CLEANUP_FAILED; do not claim verification or retry.
8. Apply section 21 precedence, then return/throw. Persist only after cleanup resolves; no provider/Runner I/O inside a new DB transaction and no finished result before verification.

Lease id is an isolation namespace; adapter privately maps synthetic resources by it. Each execute gets a fresh lease, even with same registered objects. Shared code attempts cleanup once; repeat-safe actual deletion remains provider responsibility. No public reopen/resume/cross-process deduplication. Finally covers ordinary returns/Throwable, not force-kill/OS crash/power loss/unhandled termination; no signal handling or Ctrl+C guarantee.

## 18. Architecture Constraints

Core/TestContext/metadata/RunOptions stay unchanged. Root overlay owns orchestration, adapter owns target mapping/authentication. Registration/metadata/provider selection/binding cannot acquire credentials or perform target setup. Future App integration must substantiate target environment; current fake-only tests cannot.

Preserve eager Registry and constructor compatibility. One environment decision in lifecycle, no duplicate command/controller check. Existing source extension suffices; no plugin/dependency addition or generic secret/capture/settings framework.

## 19. Validation Rules

Verify order, partial-setup cleanup, rejection with zero setup calls, fresh lease/scenario isolation, original options/metadata, Runner completion before cleanup, persistence after verification, precedence, safe errors/logs and output omission. Fakes must exercise the real lifecycle, not replace it.

Feature tests assert meaningful Test/Step fields, CLI JSON, ordered interactions and exact log context. No provider payload/sentinel in lease serialization, exceptions/cause, model JSON, command output or lifecycle logs. Do not claim control over every possible App/capture channel.

## 20. Security Rules

Synthetic business fixtures/testing-staging only. Active passwords/cookies/tokens/auth storage stay App runtime-only, absent from source/Profile/config/CLI/history/logs/exceptions/catalog. Fake sentinels allowed. Provider returns no raw payload; lease exposes only local UUID/Test id.

Normalize by stage/type, never exception text; no retained provider cause/class/endpoint/resource. No screenshot/video/trace/HAR/DOM/network/console export/artifact store. EvidenceMode remains descriptive. Code declaration is not proof against a mislabeled endpoint; actual target activation requires separate App scope.

## 21. Error Handling / Logging / Traceability Requirements

Root guard/fixture failures affect existing Test status/error_code and safe logs. No API/UI/HTTP/schema change.

| New stable code | Trigger |
|---|---|
| acceptance_target_environment_unknown | Missing interface, UNKNOWN or throwing declaration; zero provider/Runner setup. |
| acceptance_target_environment_forbidden | PRODUCTION; zero provider/Runner setup. |
| acceptance_fixture_provider_failed | Enabled App cannot supply valid provider; no prepare/Runner. |
| acceptance_fixture_setup_failed | Allocation/validation or prepare fails; partial allocated setup must clean. |
| acceptance_fixture_binding_failed | Binding throws/reuses object/changes key, name or metadata; no Runner; cleanup. |
| acceptance_fixture_cleanup_failed | Cleanup throws; failed terminal state, no claimed verification. |
| acceptance_fixture_cleanup_verification_failed | Verification false/throws; failed terminal state. |

All seven: retryable=false, operator-visible via existing CLI error_code, no automatic retry or new permission/action. No existing code changed/deprecated.

For allocated leases: cleanup_failed > cleanup_verification_failed > setup/binding failure > existing Runner/result failure. With verified cleanup preserve original Runner/result classification. Cleanup failure with RunResult returns a new result preserving keys/name/duration/steps, passed=false and cleanup code. Without result, throw safe FixtureLifecycleException. No fabricated Step. Earlier safe code appears only in approved primary_error_code log context.

RunService catches FixtureLifecycleException, converts it to the existing safe execution-failure recording path without previous provider cause, sets FAILED/error_code/data=null, returns refreshed Test. Preserve existing Core exceptions/persistence handling. PENDING -> FINISHED only after successful scenario and verified cleanup (or permitted non-fixture run); all failures -> FAILED. Passed steps may remain under a cleanup-failed Test. Final persistence error keeps acceptance_result_persistence_failed; cleanup log remains. Initial local Test creation failure starts no lifecycle.

Lifecycle failure event: tms.acceptance.fixture.failed, level error. Exact fields: test_id, lease_id (null before allocation), stage (environment/provider/setup/binding/cleanup/verification), lease_state (enum/null), error_code, primary_error_code (allowlisted code/null; unknown caller code maps to acceptance_scenario_failed), retryable=false. No arbitrary App/provider metadata/exception_class. Preserve existing tms.acceptance.run.failed, step and persistence events. No success log chatter. Test id correlates all traces; lease UUID stays runtime/lifecycle log, never Test data/CLI.

Test all codes/statuses/zero-side-effects, primary-cleanup combinations, exact log keys/level, fixed exception/no cause and sentinel omission. Pack 0008 must reuse guard/codes without resume bypass.

## 22. Data Model / Migration / Relationship Requirements

No schema/model/relationship/migration/backfill/retention change. Existing Test/Step fields and data=null suffice. Lease/state process-only; no credential/recipe/settings snapshot. Durable lease/recovery semantics deferred to Pack 0008 normalization.

## 23. Commenting Requirements

Document root/Core overlay, pure declaration scope, partial-prepare cleanup, fresh binding/Registry reuse, verified terminal state, precedence and forced-termination limits. Avoid restating ordinary calls or implying comprehensive capture enforcement.

## 24. Testing Requirements

Unit PHPUnit TestCase: fake App/provider, mocked Core Runner, recording PSR logger; no Laravel/DB/browser. Fakes retain synthetic resource markers by UUID; no HTTP/file/process/capture calls.

Feature Tests TestCase/RefreshDatabase: in-memory SQLite with safety assertion in beforeRefreshingDatabase(), before the trait restores/migrates/transacts, real lifecycle/RunService/command, mocked Runner. This local safety hook is allowed in the affected feature test files; do not edit shared Tests TestCase or global PHPUnit configuration. Preserve existing persistence/CLI/Registry regressions, no general test refactor.

## 25. Acceptance Checklist

- [ ] exact normalized allowlist/contracts/errors/tests/guide separately approved;
- [ ] Core/Registry/command signature/defaults unchanged;
- [ ] permitted targets succeed; missing/unknown/production reject before setup;
- [ ] partial prepare, binding, Runner and returned failure clean once; verify after cleanup success;
- [ ] fresh lease/scenario isolation and safe output/error/log boundaries proven;
- [ ] cleanup failure cannot persist FINISHED; primary failure trace retained safely;
- [ ] no real target/browser/provider or persistent DB I/O;
- [ ] Persian guide/bindings/indexes and crash/recovery limits updated;
- [ ] no settings/secret/capture/catalog/batch scope leak.

## 26. Tests to Add

| File / named groups | Behavior / key assertions |
|---|---|
| FixtureLifecycleTest: test_environment_guard_precedes_setup | Missing/UNKNOWN/throwing/PRODUCTION exact codes, retryable=false, no cause, null lease trace, zero provider/bind/Runner calls; host testing does not override target production. TESTING/STAGING success. |
| FixtureLifecycleTest: test_non_fixture_execution_preserves_runner_contract | Original App/scenario/options/result, guard required, no lease/provider work. |
| FixtureLifecycleTest: test_order_and_isolation_across_runs | Ordered prepare/bind/Runner/cleanup/verify; same UUID/Test id within run, different UUID/fresh scenario across two uses of registered objects; original metadata untouched. |
| FixtureLifecycleTest: test_partial_prepare_and_invalid_binding_cleanup | Fake marker created before sentinel throw then removed/verified; zero Runner. Binding throw/reused object/name/key/metadata mismatch fails and cleans. |
| FixtureLifecycleTest: test_cleanup_failure_precedence | Runner success/failed result/throw crossed with cleanup throw, verification false/throw/success. Assert final state/code, preserved steps, safe primary code, exactly one cleanup/no retry. |
| FixtureLifecycleTest: test_lease_and_errors_omit_provider_values | Invalid UUID/test id reject safely; serialization exact keys; sentinels from internal mappings/throws absent from outputs/message/cause/logs. Exact log shape/correlation. |
| AcceptanceFixturePersistenceTest: test_persistence_follows_verification | Real lifecycle/service, pending during setup/Runner/cleanup, no extra DB transaction around provider, FINISHED only after verify; Test/Step data null, steps preserved. |
| AcceptanceFixturePersistenceTest: test_failure_matrix_persists_safe_failed_state | Guard/setup/binding/cleanup/verify/Runner matrix: exact FAILED/error_code, no false FINISHED; zero Runner where forbidden; trace Test id; no sentinel in model/log JSON. Persistence failure remains safe and follows cleanup. |
| AcceptanceRunCommandTest: test_fixture_and_guard_cli_contract | Real service/lifecycle, fake registered App and in-memory Profile, mocked Runner. Failures exit 1 with existing status/test_id/app_key/scenario_key/error_code only; success exit 0; validation rejection exit 2 unchanged. No UUID/provider payload/sentinel. |

Provider repeat-safe deletion is an App contract; assert shared exactly-once cleanup under combined failure paths. Fake-only tests cannot prove unknown future adapters or cross-process recovery.

## 27. Tests to Run

Classification: validation-or-test; no browser/Node/server/worker/target process or real provider, persistent DB, migration/seed/install.

Check only existence of bootstrap/cache/config.php; if present, stop without reading/clearing it. Temporarily force process APP_ENV=testing, DB_CONNECTION=sqlite, DB_DATABASE=:memory:, CACHE_STORE=array, SESSION_DRIVER=array, QUEUE_CONNECTION=sync, MAIL_MAILER=array, BROADCAST_CONNECTION=null; restore previous process values in finally without printing them. Approval authorizes only this test-process setup. No .env/config change; feature tests assert effective sqlite/:memory: before fixtures/DB.

From root, after separate execution approval:

```powershell
php vendor/bin/phpunit --do-not-cache-result tests/Unit/FixtureLifecycleTest.php
php vendor/bin/phpunit --do-not-cache-result tests/Feature/AcceptanceFixturePersistenceTest.php
php vendor/bin/phpunit --do-not-cache-result tests/Feature/AcceptanceRunPersistenceTest.php
php vendor/bin/phpunit --do-not-cache-result tests/Feature/AcceptanceRunCommandTest.php
php vendor/bin/phpunit --do-not-cache-result tests/Unit/AcceptanceAppRegistryTest.php
```

php -l each PHP file in sections 8–9. git diff --check, git diff --name-only, git status --short --branch; explicitly inspect untracked files. No configured static-analysis command identified. No full suite/formatter/artisan/composer script/dependency install/Core browser test required. Fix/rerun narrowly; broaden only for changed unresolved concerns.

## 28. Expected Output

Root contracts/environment guard/opaque lease/synchronous lifecycle/RunService integration, fake-backed tests, Persian guide and safe failures/logs. No target integration or replacement security/settings Pack.

## 29. Operator Execution Checklist

### Before AI Execution

- Approve mandatory target declaration for every root RunService App; missing/production yields FAILED Test/exit 1 before setup.
- Approve fresh App binding, partial-prepare cleanup, cleanup precedence and process-only guarantees.
- Approve exact guide bindings and fake/in-memory test setup; no actual target/browser execution.

### After AI Execution

- Review Test failure even when steps passed but cleanup failed.
- Verify guide distinguishes confirmed cleanup from manual residual-state investigation and unsupported crash/force-kill recovery.

## 30. Agent Final Report

After post-execution gate, report guard/lifecycle/precedence, trace/omission evidence, tests versus unverified target behavior, guide/index changes and blockers. No fixture contents/usable credentials.

## 31. Review Checklist

Review owner boundary, pure metadata/provider selection, pre-setup guard, fresh leases/scenarios, partial cleanup, verification/precedence, Test/CLI/log contract, omission, in-memory validation and Persian recovery instructions.

## 32. Rollback / Safety Notes

Restore only this Pack's RunService/test changes and remove its new runtime/test/doc additions, preserving Core/roadmap. Removing integration also removes root guard; say so. No schema/target mutation or automatic target cleanup implied. No destructive Git/file operation authorized.

## 33. Stop Conditions

Stop without separate proposal approval; for unauthorized owner/protected/allowlist changes, target-specific shared code, real target/provider/browser I/O, production/non-synthetic data, credential exposure, missing environment/error/cleanup seam, schema/config/settings/secret/capture expansion, unsafe cached/persistent test DB, or unaccepted predecessors. Use conflict rules; do not bypass guards or reopen cancelled prerequisites.

## 34. Open Questions

No deferred design field. Separate normalized execution approval pending. Actual target proof/recipes/accounts and durable recovery/batch are future separately approved scope.

Required Follow-up Updates: after execution/final acceptance persist project Run 001 and update RUNS/PACKS; normalize Pack 0007 against accepted source, then Pack 0008 including recovery. No next-Pack or target implementation authorized before its gate.
