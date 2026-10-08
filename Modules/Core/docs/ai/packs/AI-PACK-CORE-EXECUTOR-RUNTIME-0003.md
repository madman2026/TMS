# AI-PACK-CORE-EXECUTOR-RUNTIME-0003 — Extensible executor and capability runtime

Status: `draft — stage 6 under Decision 0015; normalize after accepted project stage 5`

Generated: `2026-10-07`

Decision: project Decisions 0011 and 0013. Owner: Core.

Current Roadmap Notice (2026-10-08): Decision 0015 supersedes the former stage order and compatibility-adapter assumptions. Normalize this Draft after the hierarchy cutover and project stages 3–5; no legacy runtime disposition or adapter may be introduced.

## 1. Task ID

`AI-PACK-CORE-EXECUTOR-RUNTIME-0003`

## 2. Task Title

Add capability-neutral executor contracts with Browser and HTTP/API adapters.

## 3. Goal

Let scenarios request capabilities without direct Playwright PHP or HTTP-client coupling.

## 4. Context

Core currently owns a BrowserFactory/AcceptanceRunner path. Decision 0011 requires explicit capability routing, normalized results, and an extensible executor registry while preserving accepted browser behavior.

## 5. Related Release / Phase

Decision 0013, stage 5.

## 6. Related Epic / Feature / Story

Multi-executor E2E runtime.

## 7. Source References

Project Decisions 0002, 0011, 0012; Core profile; accepted Core Pack 0001/Run/Review; accepted project Packs 0009–0012 source.

## 8. Files to Create

- `Modules/Core/app/Contracts/AcceptanceExecutor.php`
- `Modules/Core/app/Contracts/ExecutorRegistry.php`
- `Modules/Core/app/Data/ExecutorCapability.php`
- `Modules/Core/app/Data/ExecutorRequest.php`
- `Modules/Core/app/Data/ExecutorResult.php`
- `Modules/Core/app/Enums/ExecutorResultStatus.php`
- `Modules/Core/app/Exceptions/ExecutorException.php`
- `Modules/Core/app/Services/ExplicitExecutorRegistry.php`
- `Modules/Core/app/Services/BrowserAcceptanceExecutor.php`
- `Modules/Core/app/Services/HttpAcceptanceExecutor.php`
- `Modules/Core/tests/Unit/ExplicitExecutorRegistryTest.php`
- `Modules/Core/tests/Unit/BrowserAcceptanceExecutorTest.php`
- `Modules/Core/tests/Unit/HttpAcceptanceExecutorTest.php`
- `Modules/Core/tests/Unit/ExecutorResultSafetyTest.php`

## 9. Files to Edit

- `Modules/Core/app/Contracts/AcceptanceScenario.php`
- `Modules/Core/app/Data/ScenarioMetadata.php`
- `Modules/Core/app/Enums/AutomationDisposition.php`
- `Modules/Core/app/Services/AcceptanceRunner.php`
- `Modules/Core/app/Providers/CoreServiceProvider.php`
- `Modules/Core/tests/Unit/ScenarioMetadataTest.php`
- existing Core runner/browser tests
- Core Pack/Run/Review indexes required by execution reporting

## 10. Files to Read / Reference

`Modules/Core/AGENTS.md`, Core manifest/profile, project profile and Decisions 0011–0012, files in sections 8–9, accepted Core Pack 0001/Run/Review, framework HTTP client source only as needed.

## 11. Configuration / Settings Requirements

No new Core config, env, Admin UI, external credential, or secret setting. Target modules supply base URLs, auth, requests, and policies through target-owned adapters/context. HTTP tests use Laravel fakes.

## 12. Do Not Change

Target modules/data, root operation or queue schema, commands, API/UI/MCP, Profile/Test/Step schema, dependencies, `.env`, real network behavior, accepted browser observation semantics.

## 13. Clarification Questions Before Implementation

Normalization must freeze capability keys, executor request/result fields, HTTP redirect/TLS defaults for fake tests, unsupported-result precedence, and runner compatibility adapter.

## 14. Multilingual / Translation Requirements

No translation/UI/RTL impact. Capability/error/status codes are language-neutral.

## 15. UI / Admin UI Requirements

No UI/Admin UI.

## 16. Operator Documentation Requirements

No direct operator workflow. Project Pack 0015 documents executor/capability diagnostics.

## 17. Implementation Rules

- Explicit code registration; no scanning or database executor definitions.
- Capability resolution occurs before target action.
- Browser adapter delegates accepted runner/browser services.
- HTTP adapter accepts a normalized target-owned request and returns allowlisted status/headers/body observations; raw auth and unsafe bodies are excluded.
- Unsupported is a typed outcome, not a raw exception.
- Remove or reject the legacy `manual-only` executable-catalog disposition according to Decision 0012; non-executable human-judgment history belongs in project source-mapping records.

## 18. Architecture Constraints

Core stays capability-neutral and contains no DK/ND URL, selector, credential, payload, fixture, provider, or business rule. Cross-owner contract is governed by project Decision 0011.

## 19. Validation Rules

Prove explicit resolution, duplicate/missing executor behavior, capability mismatch before execution, browser compatibility, HTTP fake success/failure, normalization, legacy manual-only rejection/migration behavior, and no real network calls.

## 20. Security Rules

Authorization headers, cookies, tokens, full sensitive URLs, raw bodies, DOM, and provider errors are omitted/redacted from DTOs/logs by default. Target adapters must opt into safe observations only.

## 21. Error Handling / Logging / Traceability Requirements

Define executor_not_found, capability_unsupported, executor_request_invalid, browser_execution_failed, http_transport_failed, and unsafe_executor_result. Map exceptions to retryable/permanent classifications without raw messages. Propagate operation/batch/item/attempt/correlation identifiers when supplied. No API/UI error contract.

## 22. Data Model / Migration / Relationship Requirements

No models/migrations/relationships/snapshots/backfill. Core returns DTOs; project orchestration owns persistence.

## 23. Commenting Requirements

Document capability matching, safe-result allowlist, and compatibility adapter invariants only.

## 24. Testing Requirements

Unit tests cover registry, both adapters, capability mismatch, exception normalization, retry classification, trace propagation, fake HTTP, browser regression, and sentinel omission. Existing headless smoke runs only if the normalized Pack confirms safe local prerequisites.

## 25. Acceptance Checklist

- [ ] scenario intent is executor-neutral;
- [ ] Browser behavior remains compatible;
- [ ] HTTP/API adapter is fake-tested;
- [ ] unsupported capability is deterministic;
- [ ] Core contains no target-specific or sensitive data.

## 26. Tests to Add

Exact tests in section 8 and focused edits to current Core unit/smoke tests.

## 27. Tests to Run

Normalized exact Core PHPUnit tests, project compatibility tests that consume the contract, optional approved headless smoke, and `git diff --check`. No target/network I/O.

## 28. Expected Output

Executor contracts/registry/adapters, compatibility path, tests, Core Run Report, index updates.

## 29. Operator Execution Checklist

Before: approve capabilities/result safety and smoke prerequisites. After: review Browser regression and fake HTTP/unsupported demonstrations.

## 30. Agent Final Report

Report Core boundary, capability keys, adapters, failures, tests, smoke status, files, and deferred target adapters.

## 31. Review Checklist

Review Core isolation, compatibility, real-network prevention, result safety, extension path, and exact scope.

## 32. Rollback / Safety Notes

Restore the accepted runner wiring without changing target/project data. No migration rollback.

## 33. Stop Conditions

Stop for target-specific Core code, real network/credentials, new dependency, unsafe result exposure, required project schema outside Pack, or unaccepted predecessors.

## 34. Open Questions

Exact capability taxonomy and compatibility lifetime are blocking normalization choices.
