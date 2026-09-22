# Test and Validation Rules

## Purpose

This file defines reusable test quality, validation, evidence, safety, and API test artifact rules. Concrete frameworks, commands, data names, artifact paths, endpoint scenarios, and owner-specific invariants come from the applicable project and owner profiles.

## Pack-Level Test Contract

Each Pack must define:

- Testing Requirements;
- Tests to Add;
- Tests to Run;
- Validation Rules;
- Acceptance Checklist.

The Agent must follow the Pack, applicable Canonical files, security/data rules, and owner profiles. A profile supplies concrete technology and product constraints but does not authorize commands or expand scope.

Database-changing, fixture-loading, migration, reset, wipe, seed, cleanup, or other state-changing commands require exact Pack scope or operator approval. Resolve concrete commands from the applicable project profile.

## Failed Tests

When a test fails, the Agent must:

1. identify the failing test and exact observed result;
2. determine the likely cause and affected file;
3. confirm the correction is inside Pack scope;
4. change only what is needed for the failure;
5. rerun the narrowest useful test before broader validation;
6. report unresolved failures honestly.

Do not rewrite unrelated implementation or weaken an assertion merely to make a test pass.

## Test Quality and Behavioral Assertions

Tests must verify real behavior, not superficial success. A useful test proves at least one Pack-owned contract, state change, side effect, invariant, failure condition, or protection boundary.

Insufficient checks include:

- status code only;
- response is non-empty only;
- a record exists without meaningful fields;
- a method returns a value without contract checks;
- no exception was thrown;
- a generic success message exists;
- an object or fixture can be created;
- a mock was called without checking the important input or result.

Meaningful assertions may verify:

- exact response status and stable shape where they matter;
- required fields and stable machine-readable error codes;
- validation and authorization behavior;
- meaningful persisted values and relationships;
- approved state transitions and forbidden transitions;
- expected or prohibited jobs, events, callbacks, and external-adapter interactions;
- idempotency, duplicate handling, retry, backoff, and dead-letter behavior;
- configuration/default behavior;
- translation-key and visible-text behavior;
- structured logs, trace propagation, audit/report visibility, and sensitive-data masking.

Tests should make Arrange, Act, and Assert intent clear. Prefer exact or contract-level assertions over incidental implementation details. A test that still passes when the protected behavior is broken is insufficient.

## Scenario Selection

Select only scenario groups relevant to the current Pack. Do not expand a small Pack into a broad test refactor.

### API or Request Boundary

- valid input;
- missing and invalid input;
- unauthenticated and forbidden access;
- stable response and error contract;
- no sensitive data exposure;
- safe side effects and negative side effects.

### Application Service or Handler

- valid input produces the expected domain result;
- invalid input is rejected at the correct boundary;
- state transition and persisted context are correct;
- duplicate/idempotent input behaves correctly;
- dependency failure is classified and handled correctly;
- external calls occur only through the approved adapter boundary.

### Queue, Job, or Background Execution

- expected work is dispatched with meaningful context;
- the worker reads the correct durable state;
- attempt/result state is recorded correctly;
- retryable and permanent failures diverge correctly;
- terminal states are protected;
- duplicate execution is safe.

### External Integration Adapter

- outbound mapping is correct;
- success and failure responses are normalized;
- timeout, rate-limit, temporary, permanent, and configuration failures are classified correctly;
- secrets are absent from results, logs, fixtures, and reports;
- real external services are not called unless explicitly authorized.

Use fakes, mocks, stubs, or safe recorded fixtures. Raw external wording must not be the only core assertion.

### Callback or Inbound Integration

- valid input is accepted and invalid authentication/signature is rejected;
- raw evidence is retained when required by the owner profile;
- normalization and reference resolution are correct;
- duplicate input does not repeat a transition;
- unknown references and terminal-state conflicts are handled safely.

### Persistence, Schema, and Relationships

- required structure exists when schema validation is in scope;
- relationships use the intended keys and nullability;
- indexes and uniqueness support integrity/query needs;
- delete/update behavior is explicit and safe;
- snapshots preserve historical context when required;
- no silent data-loss risk is introduced.

### Administration UI and Permissions

- permitted users can access and act;
- unauthorized or forbidden users cannot access or see sensitive data;
- sensitive actions are permission-gated and confirmed/audited where required;
- forms, tables, settings, error states, and workflow match the Pack;
- required locales and directionality are covered when applicable.

### Configuration and Settings

- defaults are respected;
- missing/invalid settings fail safely;
- sensitive settings are masked and never leaked;
- test/development placeholders replace real secrets;
- environment- or operator-required setup is documented.

## Anti-False-Positive Rule

Before accepting a test, ask: "If the intended behavior were broken, would this test fail for the right reason?"

If the answer is no or unclear, strengthen the observable contract, state, side-effect, interaction payload, or negative assertion. Do not add tests solely to raise test counts or satisfy a checklist. Mocks and fakes must not hide the behavior under test.

## Error, Logging, and Traceability Tests

When a Pack affects error handling, exceptions, external error normalization, callbacks, background failures, state-transition failures, retry/dead-letter behavior, logging, trace identifiers, or masking, tests must verify the actual contract and security behavior.

### Error Contract and Classification

Verify applicable values:

- transport/status result;
- stable error code and response shape;
- validation field-error structure;
- retryable/permanent/admin-action classification;
- user/operator visibility;
- state and attempt impact;
- absence of forbidden sensitive details.

Do not couple tests to translated wording unless exact wording is an approved contract. Prefer stable codes, translation-key behavior, locale behavior, and message presence.

### Persisted Failure State

When failure affects durable state, assert the meaningful status, normalized code, classification flags, reason, retry schedule, attempt number, relationships, safe snapshots, and trace identifiers. When state must not change, assert the prohibited record or transition is absent.

### Exception Handling

Verify the exception type or stable property, handling layer, safe API/UI result, state effect, retry/failure behavior, and log/audit effect. Exceptions must not be silently swallowed. Avoid exact exception text unless it is a stable contract.

### Structured Logging

Checking only that a logging function was called is insufficient. Verify the stable event name, level, required trace/owner context, classification, and absence or masking of sensitive values. Avoid timestamps, stack formatting, file paths, and other unstable details unless contractually required.

### Traceability

When trace identifiers are required, verify the same identifier is created, propagated, stored, returned, logged, and exposed only in approved operator output across every applicable boundary. Unrelated identifiers at each layer do not prove traceability.

### Sensitive Data and Masking

Verify secrets, credentials, tokens, authorization values, sensitive recipients, unsafe raw payloads, and other profile-declared sensitive fields do not appear in responses, UI, logs, exceptions, reports, exports, audits, fixtures, or documentation. Use obvious fake values only and assert the approved masked form when masking is required.

### Protection Scenarios

Include applicable negative checks such as:

- invalid input creates no durable operation;
- unauthorized access exposes no details;
- forbidden roles cannot see raw/sensitive evidence;
- invalid integration configuration dispatches no real external work;
- duplicates do not repeat transitions or accounting;
- terminal states are not downgraded;
- permanent failures are not retried normally;
- temporary failures are not finalized prematurely;
- missing trace context does not silently become an untraceable generic failure.

For each error-related test, record or be able to explain the scenario, behavior proven, main assertions, and how the test would fail if implementation were wrong.

## Test Evidence and Reporting

For each command or test suite run, report:

- exact command or validation action;
- target and environment;
- pass/fail/skipped/not-run status;
- relevant output summary;
- limitations, generated state, and cleanup;
- required follow-up.

Do not claim a test, UI check, artifact check, migration, external call, or environment validation ran when it did not. Tests outside scope, unsafe, blocked, or dependent on unavailable infrastructure belong under `Required Follow-up Updates`.

## API Test Artifact Maintenance

When an applicable owner profile declares an API-client collection, executable specification, environment file, or equivalent manual API test artifact, check it whenever a Pack introduces or changes API behavior.

Resolve concrete paths, format, placeholders, and owner-specific endpoint scenarios from the applicable profile. Shared governance does not require a particular API client.

Check impact for changed endpoints, routes, methods, headers, authentication, input, validation, responses, status/error codes, callbacks, status/health contracts, integration-test endpoints, and examples.

Each request or scenario must include, when applicable:

- a clear name, variable-based URL, headers, and safe request example;
- expected success and error examples;
- contract assertions for status, shape, fields, error codes, traces, authentication/authorization, validation, idempotency, and safely observable side effects;
- relevant positive, negative, unauthenticated, forbidden, duplicate, unknown-resource, and terminal-state cases.

Status-code-only checks are insufficient. Authentication must use safe placeholders, an approved setup flow, or documented preconditions. Artifacts must not contain real credentials, tokens, secrets, recipients, Authorization values, production URLs, or fragile production-record dependencies.

Test data may be created only in a confirmed non-production environment and only with Pack, rule, profile, or operator authorization. Record required data, setup method, environment, cleanup need, and cleanup method. Never mutate production data or run destructive setup commands without exact authorization.

If the first testable endpoint is introduced and the required artifact does not exist, create it only when scope and owner rules allow it. If automation is incomplete, include request, expected response, preconditions, manual verification, and follow-up. Required but unsafe/out-of-scope updates must be listed under `Required Follow-up Updates`.

API test artifacts supplement and do not replace automated tests.
