# AI-PACK-TMS-EPHEMERAL-EXECUTION-CONTEXT-0005 — Ephemeral Secret and Authentication Execution Context

Status: `superseded — standalone execution cancelled by Decision 0005`

Generated: `2026-10-05`

Decision: `docs/project/ai/decisions/TMS-DECISION-0004-cli-only-staged-generic-acceptance-roadmap.md`

Superseded By: `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`

Former Dependencies (historical): accepted project Pack 0004 and accepted Core Packs 0001–0002

Execution Gate: Superseded; this historical proposal is not executable and is not a prerequisite for the remaining roadmap.

Scope Replacement (2026-10-06): the operator rejected standalone secure credential infrastructure for synthetic testing/staging data. Minimum App-owned runtime-value and output/persistence safeguards are assigned to project Packs 0006–0008 by `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md` and `docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0001.md`. No replacement standalone Pack is required. Sections 1–34 below preserve the original unexecuted Draft for history only; their provider/storage/lease questions and execution instructions are inactive.

## 1. Task ID

`AI-PACK-TMS-EPHEMERAL-EXECUTION-CONTEXT-0005`

## 2. Task Title

Add generic ephemeral secret resolution and target-App authentication context boundaries.

## 3. Goal

Allow a code-owned target App to obtain scoped synthetic/test credentials through a generic provider, use them only inside one approved execution lease, establish target-specific authentication outside Core, and dispose of values without persisting or reporting them.

## 4. Context

Current Profile has an arbitrary `extra` field but no approved encrypted secret model or ephemeral provider. Decision 0003 requires a separate secret/Profile decision before authenticated external execution. Core Evidence Safety must exist first so secret use cannot begin before no-capture policy is active.

## 5. Related Release / Phase

Not applicable; pre-Release project security/orchestration Pack.

## 6. Related Epic / Feature / Story

Reusable human-acceptance automation — secure authenticated execution prerequisite.

## 7. Source References

- Decisions 0002–0004;
- accepted predecessor Pack/Run records;
- current Profile, AcceptanceRunService, command, Core TestContext/Runner, and safe persistence behavior;
- applicable shared security, configuration, error, test, and reporting rules.

## 8. Files to Create

Anticipated project scope, to be finalized during normalization:

- generic secret-reference/provider and lease contracts;
- execution-context/authentication orchestration boundary;
- fake/in-memory test provider only;
- focused project and Core integration tests where exact ownership requires them;
- project Run Report after execution.

## 9. Files to Edit

Anticipated AcceptanceRunService/command/Profile-facing mapping and, only if approved by normalized ownership, the minimal Core context seam. Exact paths are deferred and no edit is authorized by this Draft.

## 10. Files to Read / Reference

Repository/project/Core routing and profiles; shared execution/scope/security/config/test/error/reporting rules; accepted predecessors; current Profile/migrations/models/run service/command; exact new Core evidence APIs.

## 11. Configuration / Settings Requirements

Normalization must select the initial secret-provider ownership and storage boundary. Defaults:

- raw secrets are never stored in Profile `extra`, config, documentation, fixtures, tests, command arguments, reports, or database snapshots;
- source/config may contain only a non-sensitive provider key and secret reference;
- tests use an in-memory fake and `example-sensitive-value`-style sentinels;
- `.env` is never read by the Agent and no real value is committed.

Any environment-backed or encrypted provider requires exact config/placeholder/operator setup and tests in the normalized Pack.

## 12. Do Not Change

Target authentication workflows/selectors, target URLs, real credentials, Provider I/O, fixture creation, catalog/batch, API/UI, queue, scheduler, unrelated Profile CRUD, dependencies, `.env`, vendor, or target repositories.

## 13. Clarification Questions Before Implementation

Normalization must resolve:

1. initial provider type: environment reference, encrypted database storage, or external secret-manager adapter;
2. whether Profile stores a non-sensitive reference or the App supplies it in source/config;
3. exact lease lifetime/disposal proof and behavior after process interruption;
4. minimum Core seam, if any, needed to expose values without widening TestContext.

## 14. Multilingual / Translation Requirements

Expected no UI/API translation. Artisan-visible human messages, if added, require repository-convention translation resolution during normalization; stable codes remain language-neutral.

## 15. UI / Admin UI Requirements

No UI or Admin UI changes planned. Secret administration UI is explicitly out of scope.

## 16. Operator Documentation Requirements

Operator Documentation Impact: Yes. The normalized Pack must resolve/create the owner-approved CLI setup guide section for provider/reference configuration, safe environment, rotation/revocation, and prohibited values. No guide path is invented by this Draft.

## 17. Implementation Rules

- Resolve and activate evidence policy before resolving a secret.
- Pass opaque references into providers; never pass raw values through command arguments or persisted models.
- A lease exposes the minimum value only to the target-App authentication adapter for the current run and cannot be serialized.
- Authentication workflow, selectors, endpoint calls, and session assertions stay inside the target App.
- Dispose of in-memory references in `finally`; disposal failure must be observable through a safe stable error/status.
- Shared layers return only booleans/masked state/approved metadata.

## 18. Architecture Constraints

Core remains target-neutral. The project owns orchestration/provider registration. Target Apps own authentication behavior. Secret rotation/revocation must not require changing Core behavior. No ND-specific provider or field is allowed.

## 19. Validation Rules

The normalized Pack must prove pre-policy ordering, provider/reference validation, one-run lease isolation, non-serialization, disposal on success/failure, absence from Test/Step/log/CLI/exception channels, and unchanged unauthenticated scenario behavior.

## 20. Security Rules

This is a security-sensitive Pack. Use only fakes/sentinels. Raw secret values must be omitted from persistence, logs, reports, errors, command output, screenshots, traces, DOM dumps, network/console capture, storage, clipboard automation, and test names/descriptions.

## 21. Error Handling / Logging / Traceability Requirements

Expected scenarios: missing provider, invalid reference, unavailable secret, lease already closed, authentication-context setup failure, and disposal failure. Exact stable codes, retryability, Run status, structured events, identifiers, and masking assertions are deferred to normalization. API/UI impact remains none.

## 22. Data Model / Migration / Relationship Requirements

Deferred. The preferred minimal design adds no raw-secret column. If an encrypted store or Profile reference column is selected, the normalized Pack must fully specify migration, encryption, indexes, relationships, delete/retention behavior, rollback, and tests before execution.

## 23. Commenting Requirements

Explain lifetime, ownership, no-serialization, and disposal invariants; never include a realistic credential example.

## 24. Testing Requirements

Mandatory contract, ordering, isolation, disposal, error classification, structured-log, persistence-negative, command-output-negative, and sentinel-omission tests using fakes only.

## 25. Acceptance Checklist

- [ ] provider/storage decision recorded during normalization;
- [ ] evidence policy precedes secret resolution;
- [ ] raw values remain ephemeral and non-serializable;
- [ ] target auth stays outside Core/project generic behavior;
- [ ] success/failure/disposal paths leak no sentinel;
- [ ] no target/fixture/catalog/batch scope added.

## 26. Tests to Add

Normalize into named reference-resolution, lease isolation, one-use/closed behavior, ordering, disposal, missing-provider, provider-failure, logging/output/persistence omission, and regression tests.

## 27. Tests to Run

Deferred until normalization. No real secret, external authentication, target network, `.env` read, migration, or production-like state is authorized by this Draft.

## 28. Expected Output

A provider-neutral ephemeral execution context with safe fake-backed tests and documented setup boundary; no real target authentication implementation.

## 29. Operator Execution Checklist

### Before AI Execution

- Approve the normalized initial provider/storage choice and safe test environment.

### After AI Execution

- Verify sentinel absence and provider rotation/revocation boundary.

## 30. Agent Final Report

Report selected provider boundary, lifetime/disposal evidence, tests, security channels checked, and any manual operator setup without revealing values.

## 31. Review Checklist

Review ownership, pre-policy ordering, non-serialization, cleanup, error/log contracts, persistence, command output, and secret non-exposure.

## 32. Rollback / Safety Notes

Rollback must remove provider/lease wiring and any explicitly scoped non-sensitive reference schema while preserving history according to the normalized migration plan. Never delete or print secret values.

## 33. Stop Conditions

Stop if a raw secret must enter Profile `extra`, source, command arguments, fixtures, logs/results, a target-specific auth flow enters shared code, storage choice is unresolved, a migration is needed outside scope, or a predecessor is unaccepted.

## 34. Open Questions

The four normalization questions in section 13 block execution.
