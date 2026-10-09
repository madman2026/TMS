# AI-PACK-TMS-OPERATOR-INPUT-APPROVAL-0011 — Operator input and approval workflow

Status: `accepted — technical validation passed; operator accepted on 2026-10-09`

Generated: `2026-10-07`

Normalized: `2026-10-08`

Decision: Decisions 0005, 0008–0010, and 0014–0016. Decision 0015 owns stage 4 and the single version-2 hierarchy. Decision 0016 owns the prerequisite operation names.

## 1. Task ID

`AI-PACK-TMS-OPERATOR-INPUT-APPROVAL-0011`

## 2. Task Title

Persist declared non-secret inputs and approval gates independently of client process lifetime.

## 3. Goal

Let a complete App/Component/Suite/Scenario/Variant request wait for validated operator data and approval, then become ready for later automated execution without depending on an open client process.

## 4. Context

Accepted Pack 0010 completed the required target-module predecessor. Decision 0009 permits structured prerequisite state and App-owned secret references while forbidding general TMS secret persistence. Decision 0014 requires typed client-neutral results. Decision 0015 requires one complete version-2 hierarchy identity.

This Pack owns prerequisite collection only. It does not dispatch a scenario, resolve a secret reference, access a target, or add an interactive client.

## 5. Related Release / Phase

Decision 0015, stage 4.

## 6. Related Epic / Feature / Story

Operator prerequisites for automated E2E execution.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`
- `docs/project/ai/decisions/TMS-DECISION-0008-transport-neutral-operations-and-artisan-client.md`
- `docs/project/ai/decisions/TMS-DECISION-0009-operator-input-approval-and-sensitive-values.md`
- `docs/project/ai/decisions/TMS-DECISION-0010-async-batch-state-and-recovery.md`
- `docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md`
- `docs/project/ai/decisions/TMS-DECISION-0015-clean-acceptance-hierarchy-cutover.md`
- `docs/project/ai/decisions/TMS-DECISION-0016-prerequisite-operation-naming.md`
- accepted Pack 0009 and Pack 0010 source and Run evidence
- project profile and the scope, execution, data/status, error/logging/traceability, testing, reporting, commenting, Git, and documentation-maintenance rules

Decision 0013 remains historical and does not control this Pack's stage or compatibility contract.

## 8. Files to Create

- `app/Acceptance/Prerequisites/Enums/PrerequisiteState.php`
- `app/Acceptance/Prerequisites/Enums/InputType.php`
- `app/Acceptance/Prerequisites/Enums/InputSensitivity.php`
- `app/Acceptance/Prerequisites/Enums/InputSource.php`
- `app/Acceptance/Prerequisites/Data/InputRequirement.php`
- `app/Acceptance/Prerequisites/Data/ApprovalRequirement.php`
- `app/Acceptance/Prerequisites/Data/PrerequisiteSchema.php`
- `app/Acceptance/Prerequisites/Data/OperatorContext.php`
- `app/Acceptance/Prerequisites/Data/ApprovalFact.php`
- `app/Acceptance/Prerequisites/Data/PrerequisiteOperationData.php`
- `app/Acceptance/Prerequisites/Contracts/OperatorContextProvider.php`
- `app/Acceptance/Prerequisites/LocalCliOperatorContextProvider.php`
- `app/Acceptance/Prerequisites/PrerequisiteException.php`
- `app/Acceptance/Prerequisites/PrerequisiteService.php`
- `app/Models/AcceptanceOperationRequest.php`
- `app/Models/AcceptanceOperationInput.php`
- `app/Models/AcceptanceOperationApproval.php`
- `database/migrations/2026_10_08_000002_create_acceptance_operation_requests_table.php`
- `database/migrations/2026_10_08_000003_create_acceptance_operation_inputs_table.php`
- `database/migrations/2026_10_08_000004_create_acceptance_operation_approvals_table.php`
- `database/factories/AcceptanceOperationRequestFactory.php`
- `app/Acceptance/Operations/Handlers/DiscoverOperationRequirements.php`
- `app/Acceptance/Operations/Handlers/SubmitOperationInput.php`
- `app/Acceptance/Operations/Handlers/ApproveOperation.php`
- `app/Acceptance/Operations/Handlers/CancelOperationRequest.php`
- `tests/Feature/AcceptancePrerequisiteWorkflowTest.php`
- `tests/Unit/PrerequisiteServiceTest.php`

Execution reporting may create the exact Pack Run Report required by the reporting rules.

## 9. Files to Edit

- `app/Data/VariantDescriptor.php`
- `app/Acceptance/Operations/AcceptanceOperationService.php`
- `app/Acceptance/Operations/Data/OperationResult.php`
- `app/Providers/AppServiceProvider.php`
- `tests/Unit/AcceptanceHierarchyTest.php`
- `tests/Unit/AcceptanceOperationRegistryTest.php`
- `tests/Unit/AcceptanceOperationServiceTest.php`
- `docs/project/ai/packs/AI-PACK-TMS-OPERATOR-INPUT-APPROVAL-0011.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md`

No equivalent-path substitution may cross into Core or a target module. Any newly required implementation path outside sections 8–9 is a stop condition.

## 10. Files to Read / Reference

Only the references in section 7, files in sections 8–9, directly used Laravel/Eloquent source, and directly affected predecessor tests may be read. Do not bulk-load unrelated Packs, Runs, Reviews, target source, or archived documents.

## 11. Configuration / Settings Requirements

No config or environment file is added or edited. Defaults are code-owned and versioned in `PrerequisiteSchema`:

- request TTL default: 86,400 seconds;
- request TTL accepted range: 300–604,800 seconds;
- approval TTL default: 3,600 seconds and never later than request expiry;
- scalar/reference maximum: 4,096 UTF-8 bytes, with secret references additionally limited to 255 bytes;
- string-list maximum: 64 items and 4,096 aggregate UTF-8 bytes.

A later approved Pack may move non-sensitive defaults to versioned config. No `.env`, generic secret store, encryption key, external integration, queue setting, or deployment setting is introduced here.

## 12. Do Not Change

- `Modules/Core/**`;
- target modules or actual DK/ND accounts/resources;
- Profile `extra` or any credential storage;
- commands, Web/API/MCP routes, auth, policies, permissions, queues, jobs, scheduler, or client rendering;
- Composer/npm dependencies, lockfiles, vendor, bootstrap, environment files, or production data;
- Pack 0010 generation/validation behavior;
- accepted version-2 list/plan/run client contracts.

## 13. Clarification Questions Before Implementation

No unresolved clarification remains. The normalized choices are:

1. `VariantDescriptor` carries exactly one immutable `PrerequisiteSchema`; omitted construction produces `PrerequisiteSchema::none()` and therefore one representation with no requirements.
2. Allowed input types are `string`, `integer`, `boolean`, `string_list`, and `secret_reference`.
3. `non_sensitive` requirements accept only the `literal` source. `secret_reference` requirements accept only the `secret_reference` source.
4. A persisted secret reference must match `app-secret://<current-app-key>/<opaque-id>`, where the opaque ID is 1–128 characters from `[A-Za-z0-9._~-]`. The reference is opaque to shared TMS and is not resolved in this Pack.
5. Raw/literal secrets are unsupported in every flow in this Pack, including synchronous calls, and return `secret_literal_forbidden`. Decision 0009 permits a later Pack to add an explicitly in-memory-only flow.
6. The local provider returns `actorType=local_cli` and `actorReference=local-cli`. This is workflow trace context, contains no OS username, and makes no authentication or authorization claim. Future authenticated clients must replace the provider at their boundary.
7. Records have no automatic deletion or purge in this Pack. They are retained until a separately approved retention/cleanup policy. Request and approval TTL affect validity, not deletion.
8. Expiry is applied transactionally when a request is discovered or mutated. No scheduler or queue is required.
9. Operator approval on 2026-10-08 selected resource-specific prerequisite operation names under Decision 0016; all other normalized choices were approved unchanged.

## 14. Multilingual / Translation Requirements

No visible text, translation, or RTL change. Keys, enum values, states, operation names, and error codes are language-neutral. Human prompt text and Persian guidance remain Pack 0015 scope.

## 15. UI / Admin UI Requirements

No UI or Admin UI. Services expose immutable safe metadata for later client adapters.

## 16. Operator Documentation Requirements

Pack 0015 must document discover, submit, approve, cancel, expiry, non-interactive structured input, the local actor limitation, and the prohibition on CLI secret literals. No guide is changed before that client exists.

## 17. Implementation Rules

### Schema and value contract

- `PrerequisiteSchema` contains `version`, request TTL, a unique ordered list of `InputRequirement`, and a unique ordered list of `ApprovalRequirement`; it exposes a deterministic SHA-256 fingerprint over its normalized semantic fields.
- `InputRequirement` contains key, type, sensitivity, required flag, approval-relevant flag, allowed sources, optional scalar allowlist, optional string/integer bounds, and list limit. Constructors reject inapplicable or contradictory constraints.
- `ApprovalRequirement` contains scope, required flag, TTL, and the unique input keys whose value changes invalidate that scope.
- Keys, schema versions, and scopes use the accepted hierarchy-key syntax and 64-character limit.
- Schemas validate before any persistence. Duplicate keys/scopes, unknown approval input keys, secret-reference/literal mismatches, invalid bounds/allowlists, or non-deterministic values return `prerequisite_schema_invalid` and create no record.

### Operations

Register exactly these lazy handlers:

| Operation | Request parameters | Behavior |
|---|---|---|
| `acceptance.prerequisite.request.prepare` | either `request_id`, or the complete `app_key`, `component_key`, `suite_key`, `scenario_key`, `variant_key`, and integer/string `profile_id` | Reads an existing request, or validates the exact catalog tuple and creates a UUID request using the shared operation ID. |
| `acceptance.prerequisite.input.submit` | `request_id`, `expected_lock_version`, and non-empty `inputs` map | Validates only declared keys, persists safe values/references, invalidates affected approvals, and recomputes state. |
| `acceptance.prerequisite.approval.grant` | `request_id`, `expected_lock_version`, and `scope` | Requires all inputs for that scope, records the current input fingerprint and actor context, and recomputes state. |
| `acceptance.prerequisite.request.cancel` | `request_id` and `expected_lock_version` | Cooperatively moves a nonterminal prerequisite request to `cancelled`; an identical repeat is idempotent. |

`OperationRequest` remains the generic version-2 envelope. The shared service validates these exact parameter shapes before handler resolution, issues operation/correlation UUIDs, accepts only `PrerequisiteOperationData` for these operations, and keeps all client encoding outside services.

### State transitions

`draft` exists only inside request creation before the same transaction computes the externally observable state.

| Current state | Event | Next state |
|---|---|---|
| `draft` | required input missing | `awaiting_input` |
| `draft` | inputs complete, approval missing | `awaiting_approval` |
| `draft` | no prerequisite missing | `ready` |
| `awaiting_input` | submit; required input still missing | `awaiting_input` |
| `awaiting_input` | submit; inputs complete, approval missing | `awaiting_approval` |
| `awaiting_input` | submit; all prerequisites complete | `ready` |
| `awaiting_approval` | approval-relevant input changes | `awaiting_approval` after affected approvals are revoked |
| `awaiting_approval` | final required approval recorded | `ready` |
| `ready` | approval-relevant input changes | `awaiting_approval` after affected approvals are revoked |
| `ready` | non-approval-relevant input changes | `ready` |
| `draft`, `awaiting_input`, `awaiting_approval`, `ready` | cancel | `cancelled` |
| `awaiting_input`, `awaiting_approval`, `ready` | `now >= expires_at` on discovery/mutation | `expired` |

`cancelled` and `expired` are terminal in this Pack. Execution states `queued` onward remain Pack 0013 scope. The service uses a transaction plus `lockForUpdate`; `lock_version` increments only on a real mutation. A stale different mutation returns `conflict`. Submitting the same normalized values, approving an already-valid identical scope/fingerprint, repeating cancel, or rediscovering does not mutate or increment and returns the current safe result.

### Typed result

`PrerequisiteOperationData` version 1 contains request ID, complete hierarchy identity, nullable profile ID, schema version/fingerprint, state, lock version, ISO-8601 UTC expiry, ordered safe requirement metadata, missing input keys, approval facts, missing approval scopes, and correlation ID. It contains no submitted literal value, secret reference, model, encoded JSON, display text, exit code, raw exception, or actor credential.

`ApprovalFact` contains scope, schema version, actor type/reference, input fingerprint, approved-at, expires-at, and revoked-at. A fact proves workflow approval only.

## 18. Architecture Constraints

- Shared TMS owns schema validation, neutral persistence, prerequisite state, expiry, and approval facts.
- Target Apps own schemas and later secret-reference resolution. This Pack does not create a generic secret provider or resource manager.
- The complete version-2 tuple is mandatory. No legacy/default Component, Suite, Variant, selector, or projection is allowed.
- Clients may call operations only; they do not write prerequisite models or reproduce transitions.
- Approval is never authentication, authorization, or permission evidence.
- Services contain no Prompts, console I/O, JSON renderer, translated text, HTTP response, or target I/O.

## 19. Validation Rules

Prove all table transitions and forbidden terminal transitions, exact tuple/schema lookup, schema fingerprint mismatch, required/missing input, type/bounds/allowlist validation, expiry, cancel, approval invalidation, safe idempotent repeats, stale-version conflict, actor trace, and secret-literal rejection.

Every mutation validates the current code-owned schema against stored `schema_version` and `schema_fingerprint`. Drift returns `schema_changed`, performs no mutation, and requires a new request; stored input is never reinterpreted under a changed schema.

Migration tests inspect columns, casts, indexes, unique constraints, foreign keys, relationship direction, restrictive delete behavior, and rollback ordering. Tests use UTC time control and SQLite in-memory database only.

## 20. Security Rules

- No raw password, token, cookie, session, Authorization value, secret-looking sentinel, or target payload may appear in argv, source, Profile `extra`, input/approval result DTOs, models other than an opaque reference field, logs, errors, reports, fixtures, or snapshots.
- `value_json` is permitted only for validated non-sensitive values. `secret_reference` is permitted only for validated App-owned reference syntax. Exactly one storage column is populated according to sensitivity.
- Input maps reject unknown keys, nested arrays/objects, floats, null for required keys, invalid UTF-8, over-limit content, and literal data for secret requirements.
- Logs contain keys and counts, never values, references, hashes derived from secret material, or submitted payloads.
- Tests use obvious inert sentinels and assert absence across database value columns, result data, exception messages, and captured logs.

## 21. Error Handling / Logging / Traceability Requirements

Add these stable codes to the shared result allowlist:

| Code | Result/classification | Meaning |
|---|---|---|
| `prerequisite_request_not_found` | rejected; permanent; no admin action | UUID does not identify a request |
| `prerequisite_schema_invalid` | failed; permanent; admin action required | code-owned schema violates invariants |
| `input_required` | rejected; permanent until operator action; no admin action | required input is missing |
| `approval_required` | rejected; permanent until operator action; no admin action | required approval is missing |
| `schema_changed` | rejected; permanent for this request; admin action required | stored schema identity differs from current code |
| `input_invalid` | rejected; permanent; no admin action | submitted key/type/constraint/source is invalid |
| `secret_literal_forbidden` | rejected; permanent; no admin action | secret requirement did not receive an allowed reference |
| `approval_stale` | rejected; permanent until reapproval; no admin action | approval fingerprint/expiry no longer matches |
| `request_expired` | rejected; permanent; no admin action | request is expired |
| `invalid_transition` | rejected; permanent; no admin action | event is illegal for current state |
| `conflict` | rejected; retryable; not permanent; no admin action | lock version is stale and mutation differs |
| `prerequisite_persistence_failed` | failed; retryability unknown; admin action required | safe fallback for unexpected database failure |

The shared operation service emits its existing failed-operation event once for rejected/failed calls. `PrerequisiteService` emits `tms.acceptance.prerequisite.state_changed` at `info` only for a real transition, with this fixed context allowlist: operation request ID, operation name, previous/current state, changed requirement keys, invalidated approval scopes, actor type/reference, correlation ID, and complete hierarchy keys. Null fields are omitted. No value/reference/raw payload/exception message is logged.

The request ID is the durable prerequisite identifier. The discovery operation correlation ID is persisted. Later calls retain their own shared result correlation ID while returning the durable request ID. This Pack makes no end-to-end executor trace claim.

## 22. Data Model / Migration / Relationship Requirements

### `acceptance_operation_requests`

| Column | Type / rule |
|---|---|
| `id` | UUID primary key; discovery operation ID |
| `correlation_id` | UUID, indexed |
| `app_key`, `component_key`, `suite_key`, `scenario_key`, `variant_key` | string(64) |
| `profile_id` | unsigned big integer; FK to `profiles.id`; restrict delete, cascade update; indexed |
| `schema_version` | string(64) |
| `schema_fingerprint` | char(64) |
| `state` | string(32), indexed with `expires_at` |
| `lock_version` | unsigned integer, default 0 |
| `expires_at` | timestamp, indexed through `(state, expires_at)` |
| `cancelled_at` | nullable timestamp |
| timestamps | UTC framework timestamps |

Add an index over the five hierarchy keys. No FK is possible for source-owned catalog identities.

### `acceptance_operation_inputs`

| Column | Type / rule |
|---|---|
| `id` | unsigned big integer primary key |
| `acceptance_operation_request_id` | UUID FK; restrict delete, cascade update |
| `key` | string(64) |
| `schema_version` | string(64) |
| `type` | string(32) |
| `sensitivity` | string(32) |
| `value_json` | nullable JSON; non-sensitive normalized scalar/list only |
| `secret_reference` | nullable string(255); secret-reference requirements only |
| `value_fingerprint` | char(64); SHA-256 over type-tagged normalized stored representation |
| `submitted_by_type` | string(32) |
| `submitted_by_reference` | string(128) |
| `submitted_at` | timestamp |
| timestamps | UTC framework timestamps |

Unique: `(acceptance_operation_request_id, key, schema_version)`. Index request plus key. Application invariants enforce exactly one of `value_json`/`secret_reference`; tests verify both database paths.

### `acceptance_operation_approvals`

| Column | Type / rule |
|---|---|
| `id` | unsigned big integer primary key |
| `acceptance_operation_request_id` | UUID FK; restrict delete, cascade update |
| `scope` | string(64) |
| `schema_version` | string(64) |
| `input_fingerprint` | char(64) over the scope's ordered approval-relevant input fingerprints |
| `actor_type` | string(32) |
| `actor_reference` | string(128) |
| `approved_at`, `expires_at` | timestamps |
| `revoked_at` | nullable timestamp; history is retained |
| timestamps | UTC framework timestamps |

Unique: `(acceptance_operation_request_id, scope, schema_version, input_fingerprint)`. Index `(acceptance_operation_request_id, scope, revoked_at)` and `expires_at`.

Models use `HasFactory`, explicit fillable allowlists, enum/array/immutable-datetime casts, and typed `belongsTo`/`hasMany` relationships. Request deletion is not exposed. All foreign keys restrict parent deletion, so operational history never cascade-deletes. `down()` drops approvals, inputs, then requests. Rollback destroys prerequisite history and may run only against the isolated test database during this Pack; no production-like migration or rollback is authorized. No backfill is needed because all tables are new.

## 23. Commenting Requirements

Follow `docs/ai/rules/COMMENTING-RULES.md`. Explain only the non-obvious schema fingerprint boundary, approval invalidation fingerprint, literal-secret prohibition, local actor limitation, and lock/idempotency ordering. Do not copy Pack prose into source or add unrelated comments.

## 24. Testing Requirements

- `PrerequisiteServiceTest` covers schema constructors/fingerprints, all input types and constraints, every state transition, expiration, approval invalidation, identical-repeat idempotency, stale conflict, schema drift, terminal protection, actor semantics, and no values in semantic DTOs.
- `AcceptancePrerequisiteWorkflowTest` uses `RefreshDatabase`, the in-memory SQLite connection, an in-test fake provider, and frozen UTC time. It proves migrations, relationships/FKs/indexes/uniques, four operations through `AcceptanceOperationService`, process-independent rediscovery, stable classifications/log contexts, rollback order by schema inspection, and sentinel absence.
- Existing hierarchy/registry/service tests prove default empty schemas, lazy registrations, request validation ordering, wrong result rejection, and no regression in previous operation types.
- Tests make no real target, browser, network, queue, command-prompt, external database, or secret-provider call.
- No seeders or production-like fixtures. Factory data is synthetic; each test rolls back through `RefreshDatabase`.

## 25. Acceptance Checklist

- [ ] each Variant has one immutable versioned prerequisite schema;
- [ ] all four operations are transport-neutral and return `PrerequisiteOperationData`;
- [ ] complete hierarchy identity and schema fingerprint are persisted;
- [ ] non-sensitive inputs and approval facts survive a new service/container resolution;
- [ ] request/approval expiry and every legal/illegal transition match sections 17–19;
- [ ] identical repeats are idempotent and stale different mutations conflict;
- [ ] approval-relevant changes revoke affected facts before recomputing state;
- [ ] literal secrets cannot persist or leak; only validated App-owned references can persist;
- [ ] local actor facts are explicitly unauthenticated workflow evidence;
- [ ] services remain client-neutral and existing version-2 clients regress cleanly.

## 26. Tests to Add

| Exact test file | Main behavior |
|---|---|
| `tests/Unit/PrerequisiteServiceTest.php` | immutable schema/value rules, state machine, concurrency/idempotency, expiry, approvals, safe DTOs, secret rejection |
| `tests/Feature/AcceptancePrerequisiteWorkflowTest.php` | schema/FKs/indexes/casts/relationships, four registered operations, persistence across resolution, classifications/logs/traces, sentinel omission |

Edit only the three exact regression files in section 9. Do not refactor the test framework.

## 27. Tests to Run

Only after separate execution approval. Run through the accepted absent-dotenv Laravel entrypoint with process-local `APP_ENV=testing`, `DB_CONNECTION=sqlite`, and `DB_DATABASE=:memory:`:

```text
test --compact tests/Unit/PrerequisiteServiceTest.php tests/Feature/AcceptancePrerequisiteWorkflowTest.php
test --compact tests/Unit/AcceptanceHierarchyTest.php tests/Unit/AcceptanceOperationRegistryTest.php tests/Unit/AcceptanceOperationServiceTest.php
test --compact tests/Unit/AcceptanceCatalogTest.php tests/Unit/AcceptancePlannerTest.php tests/Unit/AcceptanceVariantDispatcherTest.php tests/Unit/TargetModuleCreatorTest.php tests/Unit/TargetModuleValidatorTest.php
test --compact tests/Feature/AcceptanceOperationCommandContractTest.php tests/Feature/AcceptanceCatalogCommandTest.php tests/Feature/AcceptanceRunCommandTest.php tests/Feature/TargetModuleOperationServiceTest.php
list --format=json
```

Guarded form for each group:

```powershell
php -r 'define("LARAVEL_START", microtime(true)); require "vendor/autoload.php"; $pack11App = require "bootstrap/app.php"; $pack11App->loadEnvironmentFrom("__tms_pack_0011_no_dotenv__"); exit($pack11App->handleCommand(new \Symfony\Component\Console\Input\ArgvInput));' -- <exact group above>
```

Also run PHP lint on every created/edited PHP file, scoped `vendor/bin/pint --test` on those PHP files, `git diff --check`, a changed-path allowlist check, and static searches for forbidden Prompts/console/HTTP/queue/target calls and raw secret-sentinel leakage. Do not run `migrate:fresh`, rollback, seed, wipe, or any database command outside the in-memory test processes.

## 28. Expected Output

- exact prerequisite enums/DTOs/provider/service/exception;
- three models, three additive migrations, and one synthetic factory;
- four lazy operation handlers and shared result/service registration updates;
- Variant prerequisite schema integration;
- focused unit/feature/regression evidence;
- executed Run Report and Pack/Run index updates after implementation;
- Pack 0015 guide follow-up retained without premature client work.

## 29. Operator Execution Checklist

### Before AI Execution

- Approve the exact types, reference syntax, TTLs, indefinite no-purge retention, and three-table schema in sections 11, 13, and 22.
- Approve the local `local_cli`/`local-cli` actor limitation and that approval is not authorization.
- Approve complete rejection of literal secrets and App-owned opaque references as the only persisted sensitive input form.
- Approve the four operation names, parameter shapes, typed result, stable codes, state table, locking, and idempotency rules.
- Approve only in-memory SQLite/fake/synthetic validation and the exact file scope in sections 8–9.

### After AI Execution

- Inspect one request moving `awaiting_input -> awaiting_approval -> ready` and its persisted safe metadata.
- Change one approval-relevant input and confirm the approval is revoked and state returns to `awaiting_approval`.
- Demonstrate expiry, cancel, identical retry, stale conflict, schema drift, and terminal-state rejection.
- Inspect database rows, result DTOs, captured logs, and errors for the inert secret sentinel and confirm absence.
- Confirm existing version-2 list/plan/run and Pack 0010 module operations remain unchanged.

## 30. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md` and the documentation-maintenance section required by `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`. Report exact schema/transitions, actor limitation, retention/rollback impact, error/log contract, security scans, commands/results, changed paths, and deferred Pack 0015 client work.

## 31. Review Checklist

Review state correctness, tuple/schema identity, approval invalidation, authorization distinction, reference syntax, sentinel omission, actor trace, TTL/no-purge policy, concurrency/idempotency, restrictive FKs, rollback loss, typed result safety, client neutrality, regression evidence, and exact scope.

## 32. Rollback / Safety Notes

Source rollback is ordinary Git rollback. Migration rollback drops approvals before inputs before requests and permanently loses prerequisite history. It is authorized only inside the disposable in-memory test database during this Pack. Stop active prerequisite use before any future operator-approved real-database rollback. No target cleanup is needed because this Pack performs no target I/O.

## 33. Stop Conditions

Stop for:

- missing/revoked acceptance of Packs 0009 or 0010, or a changed accepted operation/hierarchy boundary;
- operator alteration or rejection of a normalized choice in sections 13, 17, 21, 22, or checklist 29;
- any requirement to persist or log a raw secret, resolve a reference, add a generic secret store, or infer authorization from approval;
- a required Core, target, command, Web/API/MCP, auth/policy, queue/job, scheduler, dependency, config, environment, or production-data change;
- an implementation path outside sections 8–9 other than mandatory governance maintenance;
- inability to prove restrictive history FKs and safe rollback on supported test schema;
- real target/browser/network/production-like database access;
- unresolved canonical/source conflict requiring Change Request or Remediation;
- missing separate execution approval.

No commit is authorized by this Pack.

## 34. Open Questions

No unresolved design question remains inside the normalized contract. Deployment retention cleanup, authenticated Web/MCP actor binding, synchronous in-memory literal-secret support, target reference resolution, client prompts/JSON, and queued execution remain explicitly deferred to later approved Packs.
