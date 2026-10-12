# AI-PACK-TMS-TARGET-CONTROL-PROTOCOL-0021 — Reusable target Acceptance Control protocol client

Status: `accepted — technical validation passed; operator accepted and authorized implementation commit 2026-10-12`

## 1. Task ID

`AI-PACK-TMS-TARGET-CONTROL-PROTOCOL-0021`

## 2. Task Title

Implement the target-neutral Acceptance Control protocol client and runtime secret-handle boundary.

## 3. Goal

Give every TMS target module one typed, target-neutral way to negotiate capabilities and acquire, inspect, and clean a run-owned target lease without knowing target commands, schemas, IDs, or implementation classes.

## 4. Context

The accepted `TargetResourceCoordinator`, `TargetFixtureManager`, `TargetCleanup`, `ResourceReference`, and Browser-runtime handoff already own TMS lifecycle orchestration. Decision 0022 adds only the missing external control transport and secret-resolution boundary. DK Pack 0004 is the first consumer; future DK components and non-DK systems must reuse the same contracts.

## 5. Related Release / Phase

Pre-Release cross-owner foundation; prerequisite for DK stage 14.

## 6. Related Epic / Feature / Story

Portable target fixture/clock/cleanup control for Browser Acceptance.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0022-target-acceptance-control-protocol.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-CONTROL-PROTOCOL-0011.md`
- accepted project Packs 0012, 0019, and 0020 and their Runs
- `app/Acceptance/Targets/TargetResourceCoordinator.php`
- `app/Acceptance/Targets/Contracts/TargetFixtureManager.php`
- `app/Acceptance/Targets/Contracts/TargetCleanup.php`
- `app/Acceptance/Prerequisites/Data/PrerequisiteExecutionData.php`
- OpenAPI 3.1 specification, RFC 9110, RFC 9457, and RFC 6750
- Laravel 12 HTTP client/testing documentation applicable to this repository

## 8. Files to Create

- `app/Acceptance/Targets/Control/Contracts/SecretHandleResolver.php`
- `app/Acceptance/Targets/Control/Contracts/TargetControlClient.php`
- `app/Acceptance/Targets/Control/Data/TargetControlCapabilities.php`
- `app/Acceptance/Targets/Control/Data/TargetControlCredential.php`
- `app/Acceptance/Targets/Control/Data/TargetControlLeaseRequest.php`
- `app/Acceptance/Targets/Control/Data/TargetControlLeaseResult.php`
- `app/Acceptance/Targets/Control/Exceptions/TargetControlException.php`
- `app/Acceptance/Targets/Control/Http/HttpTargetControlClient.php`
- `app/Acceptance/Targets/Control/TargetControlProtocol.php`
- `docs/project/references/ACCEPTANCE-CONTROL-PROTOCOL-V1.openapi.yaml`
- `tests/Unit/Acceptance/Targets/Control/TargetControlDataTest.php`
- `tests/Unit/Acceptance/Targets/Control/HttpTargetControlClientTest.php`
- `tests/Unit/Acceptance/Targets/Control/SecretHandleResolverContractTest.php`
- after approved execution, `docs/project/ai/runs/AI-PACK-TMS-TARGET-CONTROL-PROTOCOL-0021-run-001.md`

## 9. Files to Edit

- `app/Providers/AppServiceProvider.php` only if a stateless default HTTP client binding is required
- `docs/project/references/REFERENCES-INDEX.md`
- `docs/project/ai/packs/AI-PACK-TMS-TARGET-CONTROL-PROTOCOL-0021.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md` only after an approved Run exists

## 10. Files to Read / Reference

Mandatory startup, TMS project profile, Decisions 0015/0017/0021/0022, Change 0011, accepted target lifecycle/runtime Packs, exact files above, nearby tests, and applicable execution/security/testing/reporting rules.

### Project / Framework Source Reference Rule

Use repository source and accepted Runs first. Confirm Laravel HTTP-client behavior from the installed framework or official matching documentation. Do not infer a secret provider or target endpoint from local machine state.

## 11. Configuration / Settings Requirements

No committed endpoint, token, certificate, secret value, target command, or target recipe. Client timeouts and protocol limits are constructor inputs with safe defaults. Target modules own endpoint references and opaque handles through their approved configuration/prerequisite boundary.

### External Integration / Secret Settings Requirements

`TargetControlCredential` must be non-serializable, non-stringable, immutable, and short-lived. Resolve an `app-secret` handle immediately before request construction; redact Authorization and TLS material from all diagnostics; release references in `finally`.

### Test / Development Setup Requirements

Unit tests use `Http::fake()`/stray-request prevention and fake secret resolvers. No live target, Browser, database, queue, or external network.

### Settings Validation

Reject non-HTTPS endpoints except an explicitly test-only in-memory/fake transport. Reject unsupported scheme, protocol version, oversized response, redirect, missing capability, or unsafe timeout.

### Operator Setup Notes

The real secret-provider and endpoint binding is outside this Pack and must be verified at each target Pack's pre-execution gate without printing values.

## 12. Do Not Change

Core contracts, existing lifecycle semantics/error codes, DK module source, target repositories, `.env`, dependencies/lockfiles, database/schema, Browser code, public APIs, UI, translations, queues, or generated assets.

## 13. Clarification Questions Before Implementation

None at architecture level. Stop if exact Laravel version/client behavior or existing AppServiceProvider registration contradicts the listed scope.

## 14. Multilingual / Translation Requirements

No multilingual or operator-visible application text impact.

## 15. UI / Admin UI Requirements

No UI impact. UIX-01..12 are N/A because no web UI is created or changed.

## 16. Operator Documentation Requirements

Add only protocol/reference documentation in this Pack. Target-specific setup belongs to the consuming owner guide or a required follow-up before target execution.

## 17. Implementation Rules

Use constructor injection and immutable typed DTOs. Keep the client stateless. Validate capability/version before mutation. Use exact PUT/GET/DELETE paths and bounded JSON. Never let target-specific recipe mapping enter shared code.

## 18. Architecture Constraints

Ports and Adapters: application code depends on `TargetControlClient` and `SecretHandleResolver`; HTTP and secret-provider implementations depend inward. TMS owns protocol semantics; target systems own server/lease/driver implementations. No service locator or facade in domain/application classes.

## 19. Validation Rules

Validate URI, lifecycle UUID, App/component/recipe tokens, fixed instant, request digest, response schema, content type, size, status, stable code, and opaque reference shape. Reject unknown fields in security-sensitive envelopes where the schema requires closure.

## 20. Security Rules

Bearer credentials require TLS. No redirect, proxy inheritance, cookie jar, debug body, raw header, secret persistence, or automatic cross-host retry. Logs contain only protocol version, lifecycle hash, App/component/recipe keys, safe status/code, attempt count, and duration.

## 21. Error Handling / Logging / Traceability Requirements

### Error Handling Impact

Normalize transport/auth/version/capability/conflict/recipe/cleanup/invalid-response failures to Decision-0022 codes. Preserve existing `fixture_setup_failed` and `cleanup_failed` at the coordinator boundary; include the target-control code only as safe structured cause metadata.

### Error Scenarios

Timeout and transient 502/503/504 may be retryable within a strict budget for GET and digest-identical PUT/DELETE. Authentication, protocol/schema, capability, recipe, ownership, and digest mismatch are permanent. Cleanup uncertainty remains `cleanup_failed` and requires operator action.

### Logging Requirements

No request/response bodies, Authorization, endpoint query, handle, secret, raw exception, or resource reference. One safe structured event per completed operation is allowed only if existing logging rules require it.

### Traceability Requirements

Propagate `lifecycleId` and a safe request/correlation identifier. Do not persist the secret handle/value in operation results.

### Sensitive Data and Masking Requirements

Omit rather than mask secrets and raw references. Tests scan exceptions, JSON, logs, rendered results, and serialization attempts.

### Error Handling Test Requirements

Cover every stable code, classification, retry limit, malformed/problem response, redirect, oversized body, and non-exposure path.

### Required Follow-up Updates

DK Pack 0004 and target-system Packs must reference the accepted protocol artifact and conformance version.

## 22. Data Model / Migration / Relationship Requirements

No TMS data or migration impact. Target leases are never persisted by TMS.

## 23. Commenting Requirements

Comments explain security boundaries, idempotency, and non-obvious protocol constraints only.

## 24. Testing Requirements

### Test Quality Requirements

Directly construct contracts/adapters where possible; use exact assertions and named cases; prevent stray HTTP; no container boot merely to obtain dependencies.

### Behavior to Prove

Capability negotiation, exact request mapping, idempotency headers/digest, safe result mapping, retry rules, secret lifetime/non-serialization, and target-neutrality.

### Required Assertions

Exact method/path/body/header allowlist; no redirect; bounded timeout/body; same lifecycle/digest on retry; stable safe code; zero secret/handle/raw-reference leakage.

### Required Scenarios

Success for capabilities/PUT/GET/DELETE; all stable failures; duplicate same request; conflicting replay; timeout retry; malformed JSON/problem; secret resolver failure; serializing/stringifying credential rejection.

### Test Data Requirements

Obvious inert values only. Never use a real URL, token, certificate, account, or target reference.

## 25. Acceptance Checklist

- [x] exact files remain within scope;
- [x] OpenAPI and DTO/client behavior agree;
- [x] no target-specific internal appears in shared source;
- [x] secret non-exposure and retry/idempotency tests pass;
- [x] focused and full PHPUnit suites pass;
- [x] authoritative reference/Pack/Run indexes are synchronized;
- [x] no live target/network/Browser/database action occurred;
- [x] no implementation commit without separate authorization.

## 26. Tests to Add

The three files in section 8 cover immutable data validation, HTTP contract/error/idempotency behavior, and secret resolver/credential non-exposure. Add a focused integration test only if AppServiceProvider binding is edited.

## 27. Tests to Run

PHP syntax for changed PHP, scoped Pint check, the three focused unit files, affected target lifecycle/runtime tests, full PHPUnit, OpenAPI/YAML validation using an existing dependency/tool only, sensitive-token scans, `git diff --check`, and exact scope checks. No live HTTP.

## 28. Expected Output

One reusable `acceptance-control.v1` client boundary and contract artifact, ready for owner-local adapters without target coupling or secret persistence.

## 29. Operator Execution Checklist

### Before AI Execution

Confirm exact file allowlist, no dependency/config-secret change, and no live endpoint.

### After AI Execution

Review OpenAPI/client parity, safe error table, idempotency, secret scans, focused/full tests, diff, and documentation indexes.

## 30. Agent Final Report

Report exact files, protocol fields/limits, errors/retries, secret handling, tests/results, scope, documentation maintenance, and explicit non-execution of target/Browser/network work.

## 31. Review Checklist

Review inward dependency direction, lack of DK/ND knowledge, strict HTTP behavior, handle/value distinction, retry safety, bounded output, and compatibility with existing coordinator ports.

## 32. Rollback / Safety Notes

Source-only targeted rollback. No data or target cleanup. Do not remove existing lifecycle contracts.

## 33. Stop Conditions

Stop for target-specific coupling, secret persistence/logging, live network requirement, dependency addition, protocol ambiguity, unbounded response/retry, redirect, shared contract break, unexpected dirty-file overlap, or any need to read `.env`.

## 34. Open Questions

None for shared implementation. The concrete secret provider, endpoint, and target conformance are deliberately owner/deployment bindings and block only the consuming target run.
