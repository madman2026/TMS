# AI-PACK-TMS-ACCEPTANCE-EXECUTION-CONTEXT-0019 — Bounded Browser Acceptance execution context

Status: accepted — implementation and validation passed; operator accepted the result and authorized commit 2026-10-11

Generated: 2026-10-11

Owner: TMS project with explicit Core and generated-DK migration scope

Change Request: `CHANGE-REQUEST-TMS-ACCEPTANCE-EXECUTION-CONTEXT-0009`

Execution / Acceptance / Run / Commit: execution and scoped validation completed; the operator approved post-execution review, accepted the result, and separately authorized commit on 2026-10-11. Run 001 is accepted.

## 1. Task ID

`AI-PACK-TMS-ACCEPTANCE-EXECUTION-CONTEXT-0019`

## 2. Task Title

Implement replay-safe per-scenario metadata and a safe explicit Browser execution context.

## 3. Goal

Create the minimum shared contract required for a target owner to make one generated Browser Scenario executable without affecting sibling scenarios, while delivering the exact immutable execution identity and a bounded target runtime to Scenario Steps without exposing target access data.

## 4. Context

Accepted DK Pack 0003 generated 139 Scenario descriptors using one shared `metadata()` result. The shared execution path holds the final provisioned `TargetContext`, but discards it when invoking `AcceptanceRunService`; Core constructs `TestContext` with only a Browser context and timeout. DK Pack 0004 therefore cannot safely distinguish its selected Scenario/Variant or navigate its isolated local synthetic target.

This Pack performs the shared clean cutover only. The existing DK generated component is mechanically migrated so the repository does not retain dual descriptor contracts. It does not mark any DK Scenario executable and does not implement a DK target runtime.

## 5. Related Release / Phase

Project-owned prerequisite between Decision-0015 stage 13 and the first DK stage-14 executable group.

## 6. Related Epic / Feature / Story

Shared Acceptance execution context for the first bounded Notification Delivery Operations Health Browser group.

## 7. Source References

- `docs/project/ai/changes/CHANGE-REQUEST-TMS-ACCEPTANCE-EXECUTION-CONTEXT-0009.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007.md`
- `docs/project/ai/decisions/TMS-DECISION-0015-clean-acceptance-hierarchy-cutover.md`
- `Modules/DK/docs/ai/decisions/DK-DECISION-0002-first-bounded-nd-browser-acceptance-group.md`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004.md`
- current source files listed in section 10

## 8. Files to Create

- `Modules/Core/app/Contracts/BrowserTargetRuntime.php`
- `app/Acceptance/Targets/Contracts/TargetBrowserRuntimeResolver.php`
- `app/Acceptance/Targets/TargetBrowserRuntimeRegistry.php`
- `tests/Unit/TargetBrowserRuntimeRegistryTest.php`
- `tests/Feature/AcceptanceBrowserRuntimeHandoffTest.php`
- `docs/project/ai/runs/AI-PACK-TMS-ACCEPTANCE-EXECUTION-CONTEXT-0019-run-001.md` only after the post-execution reporting gate and operator authorization

## 9. Files to Edit

Production/shared source:

- `app/Providers/AppServiceProvider.php`
- `app/Acceptance/Execution/AcceptanceExecutionService.php`
- `app/Services/AcceptanceRunService.php`
- `app/Acceptance/Modules/NwidartTargetModuleCreator.php`
- `app/Acceptance/Modules/TargetModuleValidator.php`
- `stubs/acceptance/AcceptanceComponent.stub`
- `Modules/Core/app/Contracts/TestContext.php`
- `Modules/Core/app/Data/ExecutorRequest.php`
- `Modules/Core/app/Services/AcceptanceRunner.php`
- `Modules/Core/app/Services/BrowserAcceptanceExecutor.php`

Generated migration only:

- `Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php` — replace every generated descriptor's legacy shared metadata call with its exact keyed call and rename the default metadata hook; do not change suites, scenarios, variants, resolutions, metadata disposition, or any other DK behavior

Affected tests:

- `tests/Unit/TargetModuleCreatorTest.php`
- `tests/Unit/TargetModuleValidatorTest.php`
- `tests/Unit/AcceptanceHierarchyTest.php`
- `tests/Feature/AcceptanceRunPersistenceTest.php`
- `tests/Feature/AcceptancePrerequisiteWorkflowTest.php`
- `tests/Feature/AcceptanceOperationCommandContractTest.php`
- `tests/Feature/AcceptanceRunCommandTest.php`
- `Modules/Core/tests/Unit/TestContextBrowserProbeTest.php`
- `Modules/Core/tests/Unit/AcceptanceRunnerTest.php`
- `Modules/Core/tests/Unit/BrowserAcceptanceExecutorTest.php`
- `Modules/Core/tests/Feature/PlaywrightAcceptanceSmokeTest.php`
- `Modules/Core/tests/Feature/PlaywrightBrowserProbeSmokeTest.php`

Lifecycle/documentation:

- `docs/project/ai/changes/CHANGE-REQUEST-TMS-ACCEPTANCE-EXECUTION-CONTEXT-0009.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007.md`
- `docs/project/ai/changes/CHANGES-INDEX.md`
- `docs/project/ai/packs/AI-PACK-TMS-ACCEPTANCE-EXECUTION-CONTEXT-0019.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md` only if an authorized Run Report is created
- `Modules/DK/docs/ai/decisions/DK-DECISION-0002-first-bounded-nd-browser-acceptance-group.md`
- `Modules/DK/docs/ai/decisions/DECISIONS-INDEX.md`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004.md`
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`

## 10. Files to Read / Reference

- repository startup files and the project/Core owner entry/profile/manifest files
- `docs/ai/rules/AI-PACK-GENERATION-RULES.md`
- `docs/ai/rules/SCOPE-CONTROL-RULES.md`
- `docs/ai/rules/AI-EXECUTION-RULES.md`
- `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`
- `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md`
- `docs/ai/rules/GIT-AND-COMMIT-RULES.md`
- `docs/ai/rules/REPORTING-RULES.md`
- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`
- `.agents/skills/dependency-injection/SKILL.md` and `references/plugin.md`
- `.agents/skills/laravel-best-practices/SKILL.md` plus `rules/architecture.md`, `rules/style.md`, and `rules/error-handling.md`
- `.agents/skills/testing-best-practices/SKILL.md` plus `rules/naming.md`, `rules/assertions.md`, `rules/isolation.md`, and `rules/review.md`
- `app/Contracts/AcceptanceComponentProvider.php`
- `app/Data/ScenarioDescriptor.php`
- `app/Acceptance/Targets/Data/TargetContext.php`
- `app/Acceptance/Targets/Data/ResourceReference.php`
- `app/Acceptance/Targets/TargetResourceCoordinator.php`
- `app/Acceptance/Targets/TargetResourceRegistry.php`
- `app/Acceptance/Targets/TargetResourceAdapters.php`
- `Modules/Core/app/Contracts/AcceptanceScenario.php`
- `Modules/Core/app/Data/AcceptanceExecutionIdentity.php`
- `Modules/Core/app/Data/RunOptions.php`
- nearby source/tests for every file in sections 8–9

Do not read `.env`, external target data, browser profiles, unrelated owner Packs/Runs/Reviews, the raw ND corpus, or unrelated generated components.

## 11. Configuration / Settings Requirements

Configuration / Settings Impact: No versioned or runtime setting change.

No config file, environment variable, Admin Setting, external-integration setting, secret setting, queue/cache/log setting, or operator manual setup is introduced.

The accepted `core.acceptance.*` configuration remains authoritative and unchanged. Tests use explicit in-process runtime doubles with inert sentinel values. Real target URLs, sessions, credentials, tokens, cookies, profiles, and environment values are forbidden.

The pre-existing unrelated working-tree change sets `ACCEPTANCE_HEADLESS=false` in `phpunit.xml`. Preserve that file unchanged. Any Pack-0019 Core Browser smoke must use a temporary process-local `ACCEPTANCE_HEADLESS=true` override so no visible/headed Browser is launched; remove the process override after the commands.

Validation required:

- Config keys checked: Not applicable; no change
- Env placeholders documented: Not applicable
- Admin settings created or documented: Not applicable
- Sensitive settings masked: omission is mandatory
- API artifacts updated: Not applicable

## 12. Do Not Change

- any DK Scenario disposition, Step, selector, variant data, fixture, oracle, resource adapter, runtime adapter, catalog version, or Service Provider registration;
- the other 138 DK Scenario behaviors or any imported suite/scenario/variant/source-case identity;
- API/HTTP executor behavior, ND Fake behavior, callbacks, queues/workers/schedulers, notifications, Providers, or external network;
- CLI signatures/JSON shapes, database/migrations/models, persisted Test/Step schema, evidence policy, config, `.env*`, dependencies, lockfiles, vendor, generated public assets, caches, or target product source;
- raw URLs, sessions, credentials, tokens, cookies, resource IDs, personal data, or external payloads;
- accepted historical Packs/Runs/Reviews except the explicit lifecycle synchronization in section 9.

## 13. Clarification Questions Before Implementation

No design question remains. The operator approved:

- per-scenario `metadataFor(string $scenarioKey)` generation;
- immutable execution identity in `TestContext`;
- narrow target Browser runtime and final-context resolver ports;
- an App-keyed registry with no production fallback;
- clean generated-source migration and replay validation;
- reuse of existing safe result codes;
- deferral of API and Fake work.

The operator approved the mandatory pre-execution gate, post-execution review, final result, and separate commit gate on 2026-10-11. Run 001 is accepted.

## 14. Multilingual / Translation Requirements

Multilingual / Translation Impact: No.

No visible text, translation, locale behavior, direction behavior, API message, or Admin UI content changes. DK locale/theme variants are consumed only by later DK Pack 0004.

## 15. UI / Admin UI Requirements

No UI, Admin UI, public UI, settings UI, dashboard, form, table, action, navigation, permission, or screenshot change. This Pack is technical infrastructure only.

## 16. Operator Documentation Requirements

Operator Documentation Impact: No.

No operator-facing command or workflow changes. `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md` remains unchanged. DK Pack 0004 must separately document any eventual local-target setup if it creates an operator workflow.

## 17. Implementation Rules

1. Change generated descriptor lines to `metadataFor('<exact-scenario-key>')` and keep the default hook side-effect-free and fail-closed.
2. The importer must replace each exact legacy generated metadata call before sorting/merging; it must never append a second descriptor for the same scenario.
3. The validator must statically require the descriptor scenario key and `metadataFor()` key to match, while retaining its no-provider-loading boundary.
4. Mechanically migrate the existing DK component to the new generated contract without changing its effective metadata.
5. Define `BrowserTargetRuntime` as a narrow Core port. It may prepare the Browser context and open a semantic surface on a Page; it must expose no raw target getter, array export, serialization, or logging surface.
6. Define `TargetBrowserRuntimeResolver` at the project target boundary and resolve it by exact `appKey` through `TargetBrowserRuntimeRegistry`.
7. Register only the registry singleton in the root composition provider. Do not register a production fallback or no-op runtime.
8. Make the final `TargetContext` argument of `TargetResourceCoordinator`'s existing execution closure explicit in `AcceptanceExecutionService`, resolve the runtime there, then pass it through every Browser execution boundary.
9. Make runtime and identity constructor/method dependencies explicit. Do not use `app()`, `resolve()`, Laravel Context, static state, a global current variant, scenario-name parsing, or mutable singleton runtime data.
10. `TestContext` must run runtime preparation before creating its Page and expose semantic surface opening without exposing runtime internals.
11. Preserve cleanup behavior and all existing full-tuple persistence/logging. Do not persist or log the runtime or target context.
12. Use explicit test doubles constructed in tests. A test-only double is not a production registry fallback.

## 18. Architecture Constraints

- Preserve the one executable identity: `App -> Component -> Suite -> Scenario -> Variant -> Step`.
- The project layer coordinates lifecycle and resolver selection; Core remains target-neutral; target owners implement and register adapters later.
- Ports are narrow, constructor-injected, and wired only at composition roots.
- The runtime object is per execution or otherwise stateless; no access-bearing state may live in a singleton registry/resolver.
- `ResourceReference` remains an opaque lifecycle identity, not a URL/session payload.
- No arbitrary `array`, generic options bag, closure service locator, or serialized target payload crosses the boundary.
- No legacy/new dual contract or permissive fallback remains after migration.

## 19. Validation Rules

- Prove new component generation uses the keyed metadata hook.
- Prove legacy generated descriptors migrate exactly once in dry-run/apply/replay and unrelated managed lines remain byte-stable.
- Prove mismatched metadata keys, duplicate descriptors, and malformed managed regions fail before write.
- Prove current DK migration preserves 10 suites, 139 scenarios, 24,668 variants/mappings, and all Scenario dispositions as non-executable.
- Prove the exact identity object/value reaches Scenario Steps.
- Prove resolver selection uses the exact App key and occurs after account/fixture references exist.
- Prove missing/throwing resolver returns `resource_unavailable` before Browser creation and acquired references still clean up.
- Prove runtime preparation occurs before Page creation and semantic surface opening uses only the supplied bounded key.
- Prove ExecutorRequest/runtime cannot serialize or enter normalized output, persistence, or logs.
- Run focused syntax/tests first, affected local Browser smoke next, then the complete suite.

## 20. Security Rules

- Use synthetic inert sentinels only; no real target or external network is authorized.
- Omit target URL/session/credential/token/cookie/raw reference values from exceptions, logs, DTOs, Test/Step data, CLI output, and documentation.
- No runtime `toArray()`, `JsonSerializable`, `__serialize()`, raw getter, or debug payload.
- Browser smoke may use only existing local synthetic/in-memory fixtures with stray routes blocked and guaranteed cleanup.
- Do not read `.env` or personal browser state.

## 21. Error Handling / Logging / Traceability Requirements

Error Handling Impact: Yes.

- Missing or unsafe App runtime resolution: fail before Browser creation with existing `resource_unavailable`; non-retryable/permanent under current lifecycle normalization; operator action required; cleanup acquired references.
- Invalid/mismatched execution identity or keyed metadata: existing `acceptance_configuration_invalid`; fail before Steps/navigation.
- Runtime preparation/Browser failure: normalize through existing Browser execution failure behavior; never expose raw exception messages.
- Scenario semantic-open/assertion failure: existing Scenario/Step/Browser failure handling owns the result.
- Cleanup failure: existing `cleanup_failed` remains visible and preserves its precedence.

No API/UI error contract, translated message, new public exception family, new stable operator error code, retry rule, state transition, or new log event is introduced.

Existing logs/persistence retain the complete language-neutral identity and operation/correlation identifiers. Runtime/TargetContext/ResourceReference content is forbidden. Tests must scan sentinel URL/session/reference values out of exception text, normalized results, model data, and captured logs.

## 22. Data Model / Migration / Relationship Requirements

No database, migration, model, relationship, index, constraint, backfill, seeder, factory, snapshot, retention, or application-data change.

## 23. Commenting Requirements

Follow `docs/ai/rules/COMMENTING-RULES.md`. Comment only the non-obvious generated-migration or security boundary. Do not narrate DI wiring or copy target/corpus details.

## 24. Testing Requirements

Test Quality Required: Yes. Behavioral, contract, negative/error, idempotency, ordering, cleanup, and sensitive-value omission assertions are required.

Tests must use project PHPUnit conventions, descriptive `test_...` names, explicit Arrange/Act/Assert separation, `assertSame()` for exact values, direct constructor injection for unit tests, and in-process fakes/mocks for the runtime boundary. No real URL, target, account, Browser profile, external HTTP, queue, sleep, or nondeterministic value.

## 25. Acceptance Checklist

- [x] exact file allowlist is preserved;
- [x] keyed metadata generation/migration/validation is deterministic and replay-safe;
- [x] current DK generated source is migrated with no behavior/cardinality change;
- [x] exact immutable identity reaches `TestContext` and Scenario Steps;
- [x] final target context resolves one exact App runtime after provisioning;
- [x] missing runtime fails closed before Browser startup;
- [x] prepare-before-Page and semantic-open behavior is proven;
- [x] cleanup runs for every acquired-resource failure path;
- [x] no runtime/access-bearing value is serialized, persisted, logged, or returned;
- [x] no API/Fake/config/data/queue/UI/external-network scope is added;
- [x] syntax, scoped Pint, focused tests, affected Browser smoke, full suite, safe scans, and diff checks pass;
- [x] lifecycle/index records are synchronized after the proper gates;
- [x] no commit occurs without separate operator authorization.

## 26. Tests to Add

### `TargetBrowserRuntimeRegistryTest`

- Proves exact App-key registration/lookup, duplicate rejection, invalid-key rejection, and absence of fallback.
- Uses two explicit resolver doubles and known keys; no container or Browser boot.

### `AcceptanceBrowserRuntimeHandoffTest`

- Proves account/fixture references exist before resolver invocation, exact identity/runtime propagation, missing/throwing resolver failure before Browser creation, and cleanup after resolution/execution failure.
- Uses synthetic lifecycle adapters, an explicit runtime double, blocked external I/O, and sentinel values that must be absent from result/log/persistence surfaces.

Existing generator, validator, runner, executor, persistence, command, and smoke tests are extended only for changed signatures and distinct behavioral decisions. Do not add duplicate tests that merely restate constructor types.

## 27. Tests to Run

After explicit execution approval:

1. `php -l` for every created/edited PHP file in sections 8–9.
2. Scoped `php vendor/bin/pint --test` for non-generated PHP files only. The large managed DK component is validated by syntax, exact generated-source assertions, replay, cardinality, and diff checks; do not format or rewrite unrelated generated lines.
3. Focused PHPUnit:
   - `tests/Unit/TargetBrowserRuntimeRegistryTest.php`
   - `tests/Feature/AcceptanceBrowserRuntimeHandoffTest.php`
   - `tests/Unit/TargetModuleCreatorTest.php`
   - `tests/Unit/TargetModuleValidatorTest.php`
   - `tests/Unit/AcceptanceHierarchyTest.php`
   - `tests/Feature/AcceptanceRunPersistenceTest.php`
   - `tests/Feature/AcceptancePrerequisiteWorkflowTest.php`
   - `tests/Feature/AcceptanceOperationCommandContractTest.php`
   - `tests/Feature/AcceptanceRunCommandTest.php`
   - `Modules/Core/tests/Unit/TestContextBrowserProbeTest.php`
   - `Modules/Core/tests/Unit/AcceptanceRunnerTest.php`
   - `Modules/Core/tests/Unit/BrowserAcceptanceExecutorTest.php`
4. Existing local-only Core Browser smoke:
   - `Modules/Core/tests/Feature/PlaywrightBrowserProbeSmokeTest.php`
   - `Modules/Core/tests/Feature/PlaywrightAcceptanceSmokeTest.php`
   - run both with a temporary process-local `ACCEPTANCE_HEADLESS=true` override; do not edit `phpunit.xml`
5. `php vendor/bin/phpunit --do-not-cache-result` for the complete suite.
6. Targeted scans for legacy `metadata()` descriptor calls, duplicate descriptor identities, forbidden runtime serialization/getters, target/access-bearing sentinel leakage, and unauthorized scope.
7. `git diff --check`, scoped diff review, `git diff --name-only`, and final `git status --short --branch`.

No Composer/npm, database/migration/seed, server, queue/worker, cache/config mutation, external network, real target, or DK 21-variant Browser command is authorized.

## 28. Expected Output

- one keyed generated metadata hook with the same fail-closed default;
- one clean migration of the current DK generated component;
- one narrow Core Browser runtime port;
- one project resolver port and App-keyed registry;
- explicit identity/runtime propagation through execution, request, executor, runner, and context;
- focused regression coverage and an accepted Run only after later gates;
- DK Pack 0004 normalized to the exact shared contract but still unexecuted.

## 29. Operator Execution Checklist

### Before AI Execution

- Confirm execution on branch `main` while preserving the pre-existing DK Decision/Pack/index edits and the unrelated `phpunit.xml` value `ACCEPTANCE_HEADLESS=false`.
- Confirm the exact sections 8–9 file allowlist, including the mechanical generated DK component migration.
- Confirm reuse of existing result codes and no production fallback runtime.
- Confirm focused PHPUnit, local synthetic Core Browser smoke forced headless through a temporary process-local override, and the complete PHPUnit suite are authorized.
- Confirm no DK target, real/staging/production system, external network, database mutation, dependency operation, `.env` access, or commit is authorized.

### After AI Execution

- Review the scoped diff and generated migration/cardinality evidence.
- Confirm exact identity/runtime ordering and cleanup failure-path evidence.
- Confirm no access-bearing sentinel appears in output, logs, persistence, or docs.
- Review focused, Browser smoke, and complete-suite results before authorizing Run/final acceptance.

## 30. Agent Final Report

Follow reporting and documentation-maintenance rules. Report exact files, contract shape, generated migration counts, identity/runtime ordering, error/cleanup behavior, commands/results, safe scans, deviations, lifecycle updates, and follow-ups. Never report runtime target values.

## 31. Review Checklist

- Review owner boundaries and DI direction.
- Review no fallback/global/static/locator path exists.
- Review generator migration and validator key matching.
- Review generated DK behavior/cardinality preservation.
- Review runtime cannot serialize or leak.
- Review failure normalization, cleanup, full-tuple traceability, and test value.
- Review no API/Fake/config/data/UI/external scope entered.

## 32. Rollback / Safety Notes

Rollback only Pack-owned source/tests and invert the exact DK generated metadata migration. Preserve unrelated working-tree changes. No broad reset, restore, clean, delete, generated-tree rewrite, or target cleanup is authorized. No data/schema/config/dependency rollback exists.

## 33. Stop Conditions

Stop if:

- branch/working-tree state differs materially from the reported gate or an unrelated change overlaps an allowed file;
- a file outside sections 8–9 must change;
- clean migration would require a legacy fallback, duplicate descriptor, manual unrelated generated rewrite, or changed DK disposition/cardinality;
- runtime requires a raw getter, serialization, global/static state, arbitrary options array, service locator, or secret/config expansion;
- an existing stable result code cannot represent a failure safely;
- any target/access-bearing value would enter source, docs, output, logs, persistence, or exceptions;
- a real target, external network, database mutation, dependency/cache/config operation, or unapproved Browser behavior becomes necessary;
- Core Browser smoke cannot be proven headless without editing the out-of-scope `phpunit.xml`;
- focused tests, safe scans, or full suite fail outside an understood in-scope correction.

## 34. Open Questions

No blocking design question remains for this shared Pack. DK-local target runtime implementation/setup and the 21 real bounded Browser runs remain deferred to the separately normalized and approved DK Pack 0004.
