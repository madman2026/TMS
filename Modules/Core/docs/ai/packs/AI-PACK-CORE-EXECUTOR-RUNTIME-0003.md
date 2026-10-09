# AI-PACK-CORE-EXECUTOR-RUNTIME-0003 — Extensible executor and capability runtime

Status: `accepted — operator accepted 2026-10-10; validation passed`

Generated: `2026-10-07`

Normalized: `2026-10-10`

Decision: project Decisions 0002, 0005, 0011, 0012, 0014, and 0015. Decision 0015 owns stage 6 and the single complete hierarchy identity. Decision 0013 is superseded history.

Depends On: accepted project Packs 0009–0012 and accepted Core Pack 0001. Current dependency gate passed on `main` at `b4b5653` with a clean working tree.

## 1. Task ID

`AI-PACK-CORE-EXECUTOR-RUNTIME-0003`

## 2. Task Title

Add capability-neutral executor contracts with Browser and HTTP adapters.

## 3. Goal

Provide one explicit, extensible Core execution boundary so target-owned scenarios can request Browser or HTTP capability without coupling project orchestration to Playwright PHP, Laravel's HTTP client, target URLs, credentials, payloads, or presentation concerns.

## 4. Context

Accepted project stages 1–5 provide the transport-neutral operation boundary, complete App/Component/Suite/Scenario/Variant identity, prerequisite workflow, and target resource lifecycle. Core currently exposes a browser-only `AcceptanceRunner`; this Pack wraps that accepted behavior as the first executor adapter and adds an isolated HTTP adapter.

Executor registration and capability routing are explicit source-code contracts. This Pack creates the Core boundary only. Project Pack 0013 will integrate it with durable sync/queue/batch orchestration. No target adapter, real target request, queue, persistence, or client presentation is introduced here.

Current source already has no `manual-only` enum value. `AutomationDisposition::BLOCKED` and `NOT_IMPLEMENTED` are code-owned catalog readiness classifications, not human-executed runtime scenarios; this Pack does not silently redefine or remove them. Decision 0012 source-corpus dispositions remain project reporting concepts and do not enter Core executor results.

## 5. Related Release / Phase

Decision 0015, stage 6.

## 6. Related Epic / Feature / Story

Multi-executor E2E runtime and capability routing.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0002-generic-code-based-acceptance-apps.md`
- `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`
- `docs/project/ai/decisions/TMS-DECISION-0011-extensible-executors-and-capabilities.md`
- `docs/project/ai/decisions/TMS-DECISION-0012-automated-e2e-coverage-and-traceability.md`
- `docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md`
- `docs/project/ai/decisions/TMS-DECISION-0015-clean-acceptance-hierarchy-cutover.md`
- accepted project Pack 0009–0012 source and Run evidence
- accepted Core Pack 0001, Run 001, Review, and current Browser contracts
- installed Laravel Framework `12.69.2` HTTP client source and PHPUnit `11.5.56`
- project/Core profiles and the scope, execution, error/logging/traceability, security, testing, reporting, commenting, Git, and documentation-maintenance rules

Laravel Boost `search-docs` was not exposed in the current tool session. Normalization therefore used the installed framework source as the exact-version authority. Decision 0013 is historical and does not control stage numbering or compatibility.

## 8. Files to Create

- `Modules/Core/app/Contracts/AcceptanceExecutor.php`
- `Modules/Core/app/Contracts/ExecutorRegistry.php`
- `Modules/Core/app/Contracts/HttpResponseNormalizer.php`
- `Modules/Core/app/Data/ExecutorCapability.php`
- `Modules/Core/app/Data/ExecutorTraceContext.php`
- `Modules/Core/app/Data/ExecutorRequest.php`
- `Modules/Core/app/Data/ExecutorResult.php`
- `Modules/Core/app/Data/BrowserExecutorData.php`
- `Modules/Core/app/Data/BrowserStepData.php`
- `Modules/Core/app/Data/HttpExecutorRequest.php`
- `Modules/Core/app/Data/HttpExecutorData.php`
- `Modules/Core/app/Enums/ExecutorResultStatus.php`
- `Modules/Core/app/Exceptions/ExecutorException.php`
- `Modules/Core/app/Services/ExplicitExecutorRegistry.php`
- `Modules/Core/app/Services/BrowserAcceptanceExecutor.php`
- `Modules/Core/app/Services/HttpAcceptanceExecutor.php`
- `Modules/Core/tests/Unit/ExplicitExecutorRegistryTest.php`
- `Modules/Core/tests/Unit/BrowserAcceptanceExecutorTest.php`
- `Modules/Core/tests/Unit/HttpAcceptanceExecutorTest.php`
- `Modules/Core/tests/Unit/ExecutorResultSafetyTest.php`
- `Modules/Core/tests/Unit/ExecutorServiceProviderTest.php`

Execution reporting may create the exact Core Run Report required by the reporting rules after implementation.

## 9. Files to Edit

- `Modules/Core/app/Providers/CoreServiceProvider.php`
- `Modules/Core/docs/ai/packs/AI-PACK-CORE-EXECUTOR-RUNTIME-0003.md`
- `Modules/Core/docs/ai/packs/PACKS-INDEX.md`
- `Modules/Core/docs/ai/runs/RUNS-INDEX.md` — only after an executed Run Report is permitted

No edit to `AcceptanceRunner`, `AcceptanceScenario`, `ScenarioMetadata`, `AutomationDisposition`, `RunResult`, `StepResult`, browser probe source, or root project source is authorized by this Pack. Existing Core tests listed in section 10 are regression-only unless an implementation defect makes a minimal Pack-local test edit necessary and the operator separately approves that exact path.

## 10. Files to Read / Reference

- repository/Core routers, manifest, project profile, Core governance profile, and exact rules referenced in section 7;
- exact Decision, accepted Pack/Run/Review, and index paths in sections 7–9;
- `Modules/Core/app/Contracts/AcceptanceScenario.php`
- `Modules/Core/app/Contracts/TestContext.php`
- `Modules/Core/app/Data/AcceptanceExecutionIdentity.php`
- `Modules/Core/app/Data/RunOptions.php`
- `Modules/Core/app/Data/RunResult.php`
- `Modules/Core/app/Contracts/StepResult.php`
- `Modules/Core/app/Services/AcceptanceRunner.php`
- `Modules/Core/app/Providers/CoreServiceProvider.php`
- accepted Browser observation/probe contracts and their focused tests;
- `Modules/Core/tests/Unit/AcceptanceRunnerTest.php`
- `Modules/Core/tests/Unit/ScenarioMetadataTest.php`
- `Modules/Core/tests/Feature/PlaywrightAcceptanceSmokeTest.php`
- `app/Acceptance/Targets/Data/TargetContext.php`
- `app/Acceptance/Targets/Data/TargetExecutionOutcome.php`
- `app/Acceptance/Targets/TargetResourceCoordinator.php`
- `app/Services/AcceptanceVariantDispatcher.php`
- `app/Data/AcceptancePlan.php`
- `app/Data/AcceptancePlanItem.php`
- installed `Illuminate\Http\Client\Factory`, `PendingRequest`, `Response`, `ConnectionException`, and related fake source only as required;
- `composer.json`, `composer.lock`, and `phpunit.xml`.

Do not bulk-load unrelated Packs, Runs, Reviews, target modules, archives, vendor packages, or source references.

## 11. Configuration / Settings Requirements

No config, `.env`, Admin Setting, secret store, queue/cache/log-channel setting, dependency, or operator runtime setup is introduced.

HTTP defaults are code-owned invariants in `HttpExecutorRequest`: 3,000 ms connection timeout, 10,000 ms total timeout, redirects disabled, TLS verification enabled for HTTPS with no disable option, one transport attempt, 1 MiB maximum request body, and 1 MiB maximum accepted response body. A target request may reduce or increase timeouts only within 1–10,000 ms connection and 1–60,000 ms total bounds, with connection timeout not exceeding total timeout.

Tests use an injected Laravel HTTP factory with exact fakes and stray requests prevented. Browser tests use a fake `AcceptanceRunner`; no browser process is started during the required Pack validation. The accepted headless smoke is regression evidence only and is not rerun without separate operator approval.

## 12. Do Not Change

- root project source, project operation/resource/catalog/planner/dispatcher behavior, Pack 0013, or target modules;
- existing Browser runner/probe semantics, browser factory, TestContext lifecycle, RunResult/StepResult persistence contract, or accepted smoke setup;
- hierarchy identity, provider/scaffolding contracts, prerequisite/resource contracts, source mapping, or disposition reporting;
- real URLs, selectors, credentials, headers, cookies, tokens, accounts, fixtures, target payloads, provider-specific errors, or product rules;
- Web/API/MCP/Artisan clients, JSON/console presentation, prompts, translations, UI, database/schema, queue/jobs/scheduler, dependencies/lockfiles, `.env`, vendor, generated assets, browser installation, or external network state.

## 13. Clarification Questions Before Implementation

No unresolved clarification remains. The normalized choices are:

1. `ExecutorCapability` is an immutable validated key value object. First-party keys are exactly `browser` and `http`; future keys must use the same lowercase key grammar and require explicit registration.
2. `AcceptanceExecutor` exposes a stable executor key, its ordered unique capability list, and `execute(ExecutorRequest): ExecutorResult`. Executors never resolve the Laravel container.
3. `ExecutorRegistry` exposes registration, capability lookup, and execution. `ExplicitExecutorRegistry` is constructed from an explicit ordered executor list; there is no scanning, reflection discovery, database definition, fallback, alias, or default executor.
4. Duplicate executor keys, duplicate capability ownership, invalid advertised capability data, or invalid returned identity/capability are safe programming failures. They never select the first or last registration silently.
5. Registry resolution precedence is: invalid request -> `executor_request_invalid`; no registered owner -> `executor_not_found`; registered executor rejecting the request kind -> `capability_unsupported`; adapter failure -> adapter-specific normalized code; invalid adapter output -> `unsafe_executor_result`.
6. `ExecutorRequest` version 1 contains the complete `AcceptanceExecutionIdentity`, one `ExecutorCapability`, immutable `ExecutorTraceContext`, and exactly one typed payload: Browser (`AcceptanceScenario` plus `RunOptions`) or HTTP (`HttpExecutorRequest`). Mixed, missing, or mismatched payloads are invalid.
7. `ExecutorTraceContext` version 1 carries nullable `correlationId`, `operationId`, integer `batchId`, `itemId`, `attemptId`, and `testId`. UUID fields use the existing project UUID grammar; integer IDs are positive. Null means the upstream layer has not created that identity yet.
8. Browser execution delegates the accepted `AcceptanceRunner::run()` exactly once. The current runner remains unchanged as a stage-6 compatibility seam; Pack 0013 must route new orchestration through `ExecutorRegistry` before this seam can be reconsidered.
9. Browser result data contains only bounded scenario name, duration milliseconds, pass/fail, and ordered safe step summaries: one-based position, bounded name, pass/fail, nullable stable error code, duration milliseconds, and critical flag. Step description, raw error, result payload, exception message/chain, DOM, selector, and browser object are omitted.
10. HTTP request data is in-memory only: uppercase allowlisted method, HTTP/HTTPS URL without user-info or fragment, bounded headers/query/body, content type, timeouts, response byte limit, and a target-owned `HttpResponseNormalizer`. It is never serialized, persisted, logged, or returned.
11. The HTTP adapter injects `Illuminate\Http\Client\Factory`, disables redirects, preserves TLS verification, performs one request, and never calls `throw()` for HTTP status. Any 1xx–5xx response is a transport result supplied to the target normalizer.
12. `HttpResponseNormalizer` receives only status, headers, and bounded body in memory and returns `HttpExecutorData`. Target-owned normalization may expose only bounded scalar observations. Raw body, raw headers, response objects, credentials, and provider errors never enter `ExecutorResult`.
13. Connection/transport exceptions normalize to `http_transport_failed`; a thrown/invalid/unsafe target normalizer result becomes `unsafe_executor_result`. No raw external exception text or chain crosses the executor boundary.
14. Executor results use only `succeeded`, `failed`, or `unsupported`. HTTP transport success does not assert business success; target-owned observations/oracles decide expected status/content later.
15. Core provider wiring creates one singleton explicit registry with Browser and HTTP executors. Project/target owners extend it only through the approved registration boundary at their composition root; they may not modify Core internals or replace first-party capability ownership.

## 14. Multilingual / Translation Requirements

No visible text, translation, locale, or RTL/LTR change. Executor keys, statuses, error codes, trace fields, and observation keys are language-neutral. Human guidance remains client scope.

## 15. UI / Admin UI Requirements

No UI or Admin UI. Core returns immutable semantic DTOs only.

## 16. Operator Documentation Requirements

No operator guide change in this Pack. Project Pack 0015 must later document capability diagnostics and safe executor failures. Target-specific HTTP configuration and troubleshooting belong to the activated target owner, not Core.

## 17. Implementation Rules

### Contracts and registry

- Add `declare(strict_types=1)` to every new PHP file; use final readonly DTOs, TitleCase enum cases, typed parameters/returns/properties, constructor injection, and existing Core namespaces.
- `ExecutorCapability` validates 1–64 characters against the accepted lowercase key grammar and provides `Browser`/`Http` named constructors or constants without preventing later explicitly registered keys.
- `ExplicitExecutorRegistry` copies registrations defensively, validates executor and capability uniqueness, preserves deterministic registration order, and returns an unsupported typed result for missing capability ownership.
- Registry and adapters validate that returned identity, capability, executor key, result version, status/data/error combination, and trace context match the request before logging or returning.

### Browser adapter

- Accept only the `browser` capability and Browser payload; reject HTTP or other payloads before calling the runner.
- Delegate once to the accepted runner and convert `RunResult` into immutable safe `BrowserExecutorData` without changing runner/browser/context behavior.
- Preserve step order and the first safe failure classification; normalize invalid names, non-finite/negative durations, unapproved error codes, or unsafe nested values to `unsafe_executor_result` rather than leaking them.
- Catch `AcceptanceExecutionException` at the adapter boundary and return `browser_execution_failed` with its safe retryability. Unexpected throwables normalize to the same safe code without retaining the raw message or previous chain in the DTO.

### HTTP adapter

- Accept only the `http` capability and HTTP payload. Validate all request fields before constructing a pending request.
- Supported methods are `GET`, `HEAD`, `POST`, `PUT`, `PATCH`, `DELETE`, and `OPTIONS`. Redirects remain disabled; TLS verification cannot be disabled; no retry, pooling, async promise, cookie jar, sink, debug callback, or arbitrary Guzzle option is accepted.
- Header names must use standard token syntax; header/query counts and string sizes are bounded. Sensitive headers may exist only in the in-memory request and are omitted from errors, results, logs, fake assertions, and reports.
- Enforce the declared response-byte limit before invoking the target normalizer. Oversize or invalid response data returns `unsafe_executor_result`.
- Invoke the target normalizer exactly once. Copy and validate only its `HttpExecutorData` scalar allowlist; never return Laravel/Guzzle response objects or raw transport data.

### Composition root and compatibility

- `CoreServiceProvider` binds `ExecutorRegistry` as a singleton backed by `ExplicitExecutorRegistry` and resolves both first-party executors through constructor injection. It does not use facades inside executor services.
- Existing `BrowserFactory` binding and accepted runner/probe behavior remain unchanged.
- No project handler is migrated in this Pack. Pack 0013 owns integration with sync/queue/batch execution and must not bypass the registry for new orchestration.

## 18. Architecture Constraints

- Core owns capability keys, executor ports, explicit routing, safe DTO validation, first-party Browser/HTTP adapters, and executor-boundary normalization.
- Project orchestration owns operation/resource/queue state and durable persistence. Target owners own URLs, authorization, request construction, response interpretation, product assertions, fixtures, and external-specific mappings.
- The extension point is a narrow plugin boundary, not a service locator or backdoor. Executors and target normalizers may not write project models, bypass prerequisite/resource validation, resolve the container, or expose raw target data.
- The complete App/Component/Suite/Scenario/Variant identity is mandatory and unchanged. No legacy hierarchy, default tuple, old request projection, or manual runtime path is introduced.
- DTOs contain semantic data, not JSON order, console text, HTTP response contracts, Eloquent models, or client presentation.

## 19. Validation Rules

Using constructor-injected fakes, prove deterministic registration, duplicate rejection, capability lookup, missing-executor outcome, request/payload mismatch rejection, identity/trace preservation, unsupported precedence, invalid-result rejection, and no container lookup inside registry/adapters.

For Browser, prove one runner call, exact safe summary/order, accepted pass/fail behavior, exception normalization, retryability preservation, and omission of descriptions, raw errors, result arrays, exception messages/chains, selectors, DOM, and browser objects.

For HTTP, prove exact method/URL/header/body dispatch through Laravel's fake, default and bounded timeouts, redirects disabled, TLS verification retained, one attempt, 1xx–5xx delivery to the normalizer, connection failure normalization, response-size rejection, invalid normalizer rejection, and zero stray/real requests.

All DTOs reject invalid versions, keys, UUIDs/IDs, status/code/data combinations, non-finite/negative durations, duplicate observation keys, nested/non-scalar observations, invalid UTF-8/control text, oversized values, and caller-reference mutation.

## 20. Security Rules

- Authorization/Cookie headers, tokens, credentials, URL user-info/fragments, raw query/body, raw response headers/body, provider errors, redirects, cookies, DOM, selectors, console/network payloads, and exception text/chains never enter result/log/report DTOs.
- HTTP request values remain transient and in memory. Core adds no snapshot, cache, context propagation, persistence, failed-job payload, or serialized closure/object.
- Target normalizers must explicitly choose safe bounded scalar observations. Core validates structure and bounds but does not claim to understand target sensitivity; unsafe target normalization is a target-owner stop condition.
- Tests use inert sentinels and assert their absence from results, exception messages, logs, and serialized diagnostic views. `Http::preventStrayRequests()` or the injected factory equivalent is mandatory.

## 21. Error Handling / Logging / Traceability Requirements

Stable executor-boundary codes:

| Code | Status and classification | Trigger |
|---|---|---|
| `executor_request_invalid` | failed; non-retryable; permanent; no admin action | invalid version, identity, trace, capability, payload, bounds, method, URL, timeout, or exclusive-payload invariant |
| `executor_not_found` | unsupported; non-retryable; permanent; admin action required | no explicitly registered executor owns the requested capability |
| `capability_unsupported` | unsupported; non-retryable; permanent; no admin action | a selected executor cannot execute the supplied typed request |
| `browser_execution_failed` | failed; retryability copied from the normalized runner failure; permanence inverse; admin action only for retryable infrastructure failure | accepted Browser runner fails or returns a failed run |
| `http_transport_failed` | failed; retryable; non-permanent; no admin action | connection, timeout, TLS, or transport failure before a response is normalized |
| `unsafe_executor_result` | failed; non-retryable; permanent; admin action required | adapter/normalizer returns mismatched, invalid, oversized, nested, or otherwise unsafe data |

Registry construction errors throw `ExecutorException` with fixed `executor_registry_invalid`; this is a safe internal programming failure, not a client result contract. Request/runtime paths return `ExecutorResult` and do not throw raw transport/runner exceptions across the boundary.

Structured events are `tms.core.executor.completed` (info), `tms.core.executor.failed` (error for permanent/admin-action failures, warning for retryable failures), and `tms.core.executor.unsupported` (warning). Context allowlist: executor key, capability key, result status, error code, retryable, admin-action-required, complete hierarchy tuple, correlation/operation IDs, and non-null batch/item/attempt/Test IDs. Omit raw names, URLs, methods, headers, bodies, observations, provider codes, exception class/message/chain, credentials, and target identifiers beyond the approved hierarchy.

The registry logs once after validating the result. Adapters do not duplicate the boundary event. Trace values are accepted from upstream, copied unchanged into the result, and never generated by Core.

## 22. Data Model / Migration / Relationship Requirements

No table, migration, model, relationship, index, snapshot, backfill, retention, delete/update policy, or rollback command. Executor requests/results are in-memory DTOs. Durable batch/item/attempt/Test linkage belongs to Pack 0013.

## 23. Commenting Requirements

Follow `docs/ai/rules/COMMENTING-RULES.md`. Document only the explicit-registration invariant, payload exclusivity, transient sensitive HTTP request boundary, target-normalizer trust boundary, response-size limit, unsupported precedence, and accepted Browser delegation seam. Do not copy Pack prose or comment obvious code.

## 24. Testing Requirements

- `ExplicitExecutorRegistryTest` covers registration order, duplicate executor/capability rejection, missing ownership, capability dispatch, request/result matching, trace propagation, log-event selection, and no adapter call after request rejection.
- `BrowserAcceptanceExecutorTest` mocks the accepted runner and covers success, failed run, normalized runner exception, unexpected exception, safe ordered steps, duration conversion, and raw field omission.
- `HttpAcceptanceExecutorTest` uses Laravel's injected HTTP factory fake with stray requests prevented. It covers every allowed method, request options, redirect/TLS policy, 1xx–5xx normalization, connection failure, timeout bounds, oversize response, normalizer exception/invalid output, one request, and no retry.
- `ExecutorResultSafetyTest` covers every DTO/enum invariant, defensive copying, invalid UTF-8/control/size/nesting, exclusive payloads, classification combinations, identity/capability/trace mismatch, and sentinel omission.
- `ExecutorServiceProviderTest` proves singleton registry resolution, both first-party capability owners, existing BrowserFactory binding, and no implicit target executor.
- Existing AcceptanceRunner, ScenarioMetadata, Browser observation, and Playwright acceptance tests are regression-only. No real browser/network/target test is required for the new adapters.

## 25. Acceptance Checklist

- [x] Browser and HTTP are explicitly registered through one capability-neutral Core boundary;
- [x] complete hierarchy identity and safe trace context survive every result;
- [x] Browser adapter delegates accepted runner behavior without changing it;
- [x] HTTP execution uses one fake-proven request with bounded timeout/body, no redirects/retry, and enforced TLS verification;
- [x] unsupported and missing capabilities are deterministic typed results;
- [x] raw request/response/provider/browser/exception data never enters DTOs or logs;
- [x] target-specific normalizers extend the approved port without bypassing project lifecycle rules;
- [x] no project source, target module, persistence, queue, client, dependency, or environment file changes;
- [x] focused new tests and existing Core regressions pass.

## 26. Tests to Add

| Exact test file | Main behavior |
|---|---|
| `Modules/Core/tests/Unit/ExplicitExecutorRegistryTest.php` | explicit routing, duplicates, precedence, trace/log behavior, invalid result containment |
| `Modules/Core/tests/Unit/BrowserAcceptanceExecutorTest.php` | accepted runner delegation, safe summary, failure normalization and omission |
| `Modules/Core/tests/Unit/HttpAcceptanceExecutorTest.php` | fake-only HTTP transport, options, status handling, normalizer boundary, no stray/retry |
| `Modules/Core/tests/Unit/ExecutorResultSafetyTest.php` | immutable DTO/enum invariants, bounds, exclusive payloads, classification and sentinel safety |
| `Modules/Core/tests/Unit/ExecutorServiceProviderTest.php` | singleton composition-root wiring and first-party capability ownership |

## 27. Tests to Run

Only after separate execution approval:

```powershell
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/ExplicitExecutorRegistryTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/BrowserAcceptanceExecutorTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/HttpAcceptanceExecutorTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/ExecutorResultSafetyTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/ExecutorServiceProviderTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/AcceptanceRunnerTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/ScenarioMetadataTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/BrowserObservationTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/PlaywrightBrowserProbeTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/TestContextBrowserProbeTest.php
php vendor/bin/phpunit --do-not-cache-result --list-tests Modules/Core/tests/Feature/PlaywrightAcceptanceSmokeTest.php
```

Run `php -l` on every created/edited PHP file, scoped `vendor/bin/pint --test` on those files, `git diff --check`, exact changed-path allowlist/reference checks, all-34-section document validation, and static searches for container lookup, facades inside services, retry/redirect/TLS-disable options, raw sentinel exposure, and unexpected network calls.

Do not run the browser smoke, full suite, Artisan, migration, rollback, seed, queue, worker, cache, target, dependency, or external-network commands without separate authorization.

## 28. Expected Output

- capability value, trace/request/result DTOs, result status enum, safe exception, and explicit registry contracts;
- Browser adapter over the accepted runner and fake-only HTTP adapter with target normalization port;
- singleton Core provider wiring and deterministic structured executor logs;
- focused PHPUnit evidence and existing Core unit regressions;
- Core Run Report and Pack/Run index updates after implementation;
- explicit Pack 0013 integration and target-owner normalizer follow-ups.

## 29. Operator Execution Checklist

### Before AI Execution

- Approve first-party capability keys `browser` and `http`, exact registry precedence, and no scanning/fallback/default executor.
- Approve `ExecutorRequest`, `ExecutorTraceContext`, `ExecutorResult`, Browser/HTTP payload/result fields, and result statuses in sections 13 and 17.
- Approve Browser delegation without editing the accepted runner and deferral of project integration to Pack 0013.
- Approve HTTP method/URL/body/timeout limits, redirects disabled, TLS verification enforced, one attempt, and target-owned response normalizer boundary.
- Approve stable codes/classifications, one registry log event, context allowlist, sentinel/security constraints, exact files, and fake-only validation.

### After AI Execution

- Inspect registry resolution for Browser, HTTP, missing capability, duplicate registration, and mismatched result.
- Inspect one Browser pass/fail result and confirm step order remains while raw step payload/error/exception data is absent.
- Inspect fake HTTP 2xx, 4xx/5xx, connection failure, oversize body, and unsafe normalizer paths; confirm no real request or retry occurred.
- Inspect results, exceptions, captured logs, and diagnostics for header/body/URL/token/cookie/provider/DOM/selector sentinels and confirm absence.
- Confirm accepted Browser runner/probe behavior, project source, dependencies, environment, database, queue, and clients remain unchanged.

## 30. Agent Final Report

Follow reporting and documentation-maintenance rules. Report exact contracts/capabilities, Browser compatibility, HTTP transport/normalizer boundary, error classification, trace/log context, security scans, commands/results, changed paths, and deferred project/target/client work. Do not claim real target, browser-engine, queue, or network validation.

## 31. Review Checklist

Review Core/target/project isolation, explicit DI, deterministic routing, complete identity, trace copying, exclusive payloads, Browser regression preservation, HTTP timeout/redirect/TLS/retry policy, response normalization, raw-data omission, classification precedence, singleton wiring, test strength, client neutrality, and exact scope.

## 32. Rollback / Safety Notes

Source-only rollback: remove the new executor files/tests and restore the prior Core provider binding set. Existing runner/browser behavior remains directly available because this Pack does not modify it. No migration, queue, target resource, browser, network, or external cleanup should be required.

If any real external request, browser launch, target value, persisted request/result, dependency change, or project-source mutation occurs, stop immediately and report a scope/safety violation rather than attempting further execution.

## 33. Stop Conditions

Stop for:

- missing/revoked acceptance of project Packs 0009–0012 or Core Pack 0001;
- operator alteration or rejection of any normalized choice in sections 13, 17, 21, or checklist 29;
- any requirement to edit root project source, existing runner/probe result contracts, target modules, persistence, queue/jobs, client presentation, config/environment, dependencies, vendor, or browser installation;
- any fallback/default/scanned executor, legacy hierarchy projection, manual runtime path, or target-specific Core rule;
- any request to disable TLS verification, follow redirects implicitly, retry a state-changing request without a later approved idempotency policy, or accept arbitrary HTTP/Guzzle options;
- any raw URL/header/body/credential/cookie/token/provider/browser/exception content crossing result/log/report boundaries;
- unsafe target normalizer output, unbounded payload/result, stray/real network request, real browser/target access, destructive command, canonical/source conflict, or missing separate execution approval.

No commit is authorized by this Pack.

## 34. Open Questions

No unresolved design question remains inside the accepted Core contract. The operator approved the post-execution gate and explicitly authorized commit on 2026-10-10. Project Pack 0013 owns durable executor integration, queue/batch/item/attempt state, retries/idempotency, Test persistence mapping, cancellation, and recovery. Activated target-owner Packs own concrete HTTP request builders/normalizers and real target validation. Client presentation and operator guidance remain Pack 0015 scope.
