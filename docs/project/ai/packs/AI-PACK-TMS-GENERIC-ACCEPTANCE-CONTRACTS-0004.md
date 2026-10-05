# AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004 — Generic Acceptance Scenario Contracts

Status: `accepted — technical validation passed; operator accepted`

Executed: `2026-10-05`

Execution Approval: operator explicitly requested execution of Pack 0004 in the current chat.

Technical Validation: all four prescribed test commands passed — 66 tests, 462 assertions; PHP syntax checks passed for all 11 scoped PHP files; both Playwright smoke tests passed static discovery only, with no browser execution.

Post-execution Operator Gate: approved by the operator on `2026-10-05` for issuance of the Agent Final Report.

Reporting State: Agent Final Report issued and accepted in the current chat. Run Report created before commit at `docs/project/ai/runs/AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004-run-001.md`.

Operator Acceptance: accepted by the operator on `2026-10-05`; commit separately authorized in the same acceptance message.

Generated: `2026-10-05`

Decision: `docs/project/ai/decisions/TMS-DECISION-0004-cli-only-staged-generic-acceptance-roadmap.md`

Depends On: accepted Packs 0002 and 0003

## 1. Task ID

`AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004`

## 2. Task Title

Add generic code-owned scenario metadata, automation disposition, and evidence-mode contracts.

## 3. Goal

Create the smallest stable vocabulary required by all later generic TMS capabilities without adding target behavior or changing how a scenario is executed.

This Pack must:

1. add capability-neutral automation-disposition values for `automated`, `manual-only`, `blocked`, and `not-implemented`;
2. add capability-neutral evidence modes for `metadata-only`, `non-sensitive-visual`, and `sensitive-no-capture`;
3. add an immutable scenario-metadata value object containing suites, capabilities, tags, disposition, and evidence mode;
4. require every code-owned `AcceptanceScenario` to expose validated metadata;
5. make the explicit Registry retain and resolve that metadata without adding JSON execution, database workflows, automatic discovery, or target coupling;
6. update all existing fake Scenarios and prove the new contract through focused tests.

## 4. Context

Current Scenarios expose only key, name, and executable steps. Later browser, evidence, secret, fixture, catalog, and batch Packs need a shared code-owned classification contract before they can make safe selection and execution decisions.

Decision 0002 requires executable workflows to remain source code. Metadata is descriptive code-owned input; it must not make external JSON or database rows executable. The Registry may retain metadata but must continue explicit App registration.

## 5. Related Release / Phase

Not applicable. This is a pre-Release cross-module foundation Pack.

## 6. Related Epic / Feature / Story

Reusable multi-system human-acceptance automation prerequisites — generic contract foundation.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0002-generic-code-based-acceptance-apps.md`
- `docs/project/ai/decisions/TMS-DECISION-0003-cli-first-acceptance-execution.md`
- `docs/project/ai/decisions/TMS-DECISION-0004-cli-only-staged-generic-acceptance-roadmap.md`
- `docs/project/references/TMS-SOURCE-STRUCTURE-SUMMARY.md`

No target-system contract or external documentation is authoritative for this Pack.

## 8. Files to Create

- `Modules/Core/app/Enums/AutomationDisposition.php`
- `Modules/Core/app/Enums/EvidenceMode.php`
- `Modules/Core/app/Data/ScenarioMetadata.php`
- `Modules/Core/tests/Unit/ScenarioMetadataTest.php`
- `docs/project/ai/runs/AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004-run-001.md`, only after implementation, validation, scope review, and reporting gates are complete

## 9. Files to Edit

- `Modules/Core/app/Contracts/AcceptanceScenario.php`
- `app/Services/AcceptanceAppRegistry.php`
- `Modules/Core/tests/Unit/AcceptanceRunnerTest.php`
- `Modules/Core/tests/Feature/PlaywrightAcceptanceSmokeTest.php`
- `tests/Unit/AcceptanceAppRegistryTest.php`
- `tests/Feature/AcceptanceRunCommandTest.php`
- `tests/Feature/AcceptanceRunPersistenceTest.php`
- `docs/project/ai/packs/AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004.md`, lifecycle fields only after execution
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md`, only if a Run Report is created

Required governance maintenance directly related to this Pack is allowed through the governance maintenance exception and must be reported.

## 10. Files to Read / Reference

- `AGENTS.md`
- `docs/ai/start/START-HERE.md`
- `docs/ai/start/CONTEXT-MAP.md`
- `docs/project/PROJECT-DOCS-INDEX.md`
- `docs/project/ai/TMS-AI-PROFILE.md`
- `Modules/Core/AGENTS.md`
- `Modules/Core/docs/ai/manifest.yaml`
- `Modules/Core/docs/ai/GOVERNANCE-PROFILE.md`
- `docs/ai/rules/AI-EXECUTION-RULES.md`
- `docs/ai/rules/SCOPE-CONTROL-RULES.md`
- `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`
- `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md`
- `docs/ai/rules/GIT-AND-COMMIT-RULES.md`
- `docs/ai/rules/REPORTING-RULES.md`
- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`
- the three Decisions listed in section 7
- every implementation and test file listed in sections 8 and 9 that already exists

## 11. Configuration / Settings Requirements

No configuration or settings changes required.

- No config file changes required.
- No environment variables required.
- No Admin Settings changes required.
- No external-integration or secret settings required.
- No test/development setup required beyond the repository's existing PHP test environment.
- No operator manual setup required.

## 12. Do Not Change

- database schema, models, factories, seeders, or Profile storage;
- command signature or command output contract;
- runner/browser lifecycle or Playwright configuration;
- Step/Run result persistence;
- API, UI, queue, scheduler, parallelism, retry, artifact storage, secrets, fixtures, or real Apps;
- dependencies, lockfiles, `.env`, generated files, vendor code, or target repositories.

## 13. Clarification Questions Before Implementation

None. The enum values, metadata fields, validation boundary, ownership, and exclusions are resolved by this Pack and Decision 0004.

## 14. Multilingual / Translation Requirements

Multilingual / Translation Impact: No
RTL/LTR Impact: No
API Message Translation Impact: No
Admin UI Translation Impact: No
Owner-defined Default Content Translation Impact: No

No multilingual or translation changes required. Scenario names remain target-App-owned source values and are not changed by this Pack.

## 15. UI / Admin UI Requirements

UI Impact: No
Admin UI Impact: No
Public UI Impact: No

No UI changes required.

## 16. Operator Documentation Requirements

Operator Documentation Impact: No
Operator Documentation Update Required: No

No operator-facing behavior changed and no operator documentation update is required.

## 17. Implementation Rules

1. Use PHP backed enums with exact language-neutral values:
   - automation: `automated`, `manual-only`, `blocked`, `not-implemented`;
   - evidence: `metadata-only`, `non-sensitive-visual`, `sensitive-no-capture`.
2. `ScenarioMetadata` must be immutable and contain only:
   - unique ordered suite keys;
   - unique ordered capability keys;
   - unique ordered tags;
   - one automation disposition;
   - one evidence mode.
3. Every key/tag must match the existing shared key grammar `^[a-z0-9][a-z0-9._-]*$`; empty, duplicate, non-string, or malformed values are invalid.
4. Validation failures are programming/configuration failures and must throw `InvalidArgumentException` with a fixed safe message that contains no rejected value.
5. Add `metadata(): ScenarioMetadata` to `AcceptanceScenario`.
6. Registry entries must retain the metadata object and provide a typed metadata lookup for an existing App/scenario key pair.
7. Registry registration remains eager in this Pack; lazy catalog behavior belongs to Pack 0007.
8. Do not add default metadata that silently classifies an existing Scenario. Every current fake Scenario must declare its test-specific metadata explicitly.

## 18. Architecture Constraints

- Core owns capability-neutral enum/value contracts; the root Registry consumes them.
- Target Apps own concrete suite, capability, and tag values.
- Metadata is descriptive and code-owned. It must not contain closures, executable steps, selectors, URLs, credentials, payloads, fixture values, or target-specific behavior.
- This Pack must not parse the ND contract or define an ND-compatible schema.
- Preserve explicit source registration and Decision 0002's tests-as-code boundary.

## 19. Validation Rules

- Verify exact enum values and metadata immutability.
- Verify stable ordering is preserved and duplicates are rejected rather than silently removed.
- Verify rejected values never appear in exception messages or logs.
- Verify Registry metadata lookup returns the exact registered metadata object and unknown keys return `null` without mutation.
- Run focused Core and root tests; no browser launch, external network, persistent database, migration, queue, or target execution is allowed.
- Verify the full changed-path list against sections 8 and 9 plus allowed governance maintenance.

## 20. Security Rules

- Scenario metadata must not accept or expose credentials, secrets, URLs, selectors, request payloads, personal identifiers, or arbitrary result data.
- Exception text must be fixed and must omit invalid raw input.
- Tests use synthetic keys only.
- Never read `.env`, Profile `extra`, target contracts, or external payloads.

## 21. Error Handling / Logging / Traceability Requirements

Error Handling Impact: Yes
API Error Contract Impact: No
UI / Admin UI Error Impact: No
Internal Exception Impact: Yes
Logging Impact: No
Traceability Impact: No
Error Code Impact: No
Security / Sensitive Data Impact: Yes

Error scenario:

- Scenario: invalid scenario metadata construction
  - Trigger: malformed, empty, non-string, or duplicate metadata key/tag
  - Layer: Core value-object construction
  - Exception: `InvalidArgumentException`
  - Message: fixed safe technical message; raw value omitted
  - Retryable: No
  - Permanent: Yes until source is corrected
  - Run/status impact: no Test or Step may be created by this failure
  - Logging: no new log event in this Pack
  - Required test: exact exception class and absence of sample sensitive input

No API/UI error contract, stable error code, logging event, or new trace identifier is introduced. Existing command/run error contracts must remain unchanged.

## 22. Data Model / Migration / Relationship Requirements

Data / Migration Impact: No
New Tables Required: No
Existing Tables Modified: No
Audit / History Impact: No

No data model, migration, relationship, snapshot, index, backfill, or rollback changes required.

## 23. Commenting Requirements

- Add PHPDoc only where generic-array shapes or contract intent are not clear from types.
- Document why metadata cannot contain executable or sensitive target data.
- Do not add comments that restate enum cases or obvious validation code.

## 24. Testing Requirements

Test Quality Required: Yes
Behavioral Assertions Required: Yes
Contract Assertions Required: Yes
Database Assertions Required: No
Negative/Error Scenario Tests Required: Yes
Security/Secret Masking Tests Required: Yes

Tests must prove exact enum serialization, metadata validation/ordering/immutability, Registry lookup behavior, compatibility of all current fake Scenarios, and absence of rejected sample values from exceptions.

## 25. Acceptance Checklist

- [ ] exact automation and evidence values exist once in Core;
- [ ] `ScenarioMetadata` is immutable and rejects invalid/duplicate keys safely;
- [ ] every `AcceptanceScenario` implementation supplies explicit metadata;
- [ ] Registry retains and resolves metadata without changing execution behavior;
- [ ] no target, ND, secret, fixture, API, UI, queue, database, or dependency behavior is added;
- [ ] focused tests pass and changed paths remain inside scope;
- [ ] required indexes/reporting records are synchronized.

## 26. Tests to Add

### `ScenarioMetadataTest`

- Purpose: prove exact enum values and value-object invariants.
- Main assertions: ordered values preserved; invalid grammar, duplicates, empty and non-string entries rejected; fixed exception message omits `example-sensitive-value`.
- Side effects: none.

### Registry metadata cases

- Purpose: prove exact metadata object retention and lookup.
- Main assertions: known lookup returns identical object; unknown App/scenario returns `null`; registration does not mutate metadata; invalid scenario contract fails before command/run execution.

### Existing execution regressions

- Purpose: prove interface extension does not change current Runner, persistence, or command results.
- Main assertions: existing pass/fail/critical cleanup, Test/Step persistence, exit codes, JSON shape, and secret omission remain unchanged.

## 27. Tests to Run

```powershell
php artisan test Modules/Core/tests/Unit/ScenarioMetadataTest.php
php artisan test Modules/Core/tests/Unit/AcceptanceRunnerTest.php
php artisan test tests/Unit/AcceptanceAppRegistryTest.php
php artisan test tests/Feature/AcceptanceRunCommandTest.php tests/Feature/AcceptanceRunPersistenceTest.php
```

The Playwright smoke file must receive the required interface update and pass PHP syntax/static loading checks, but a real browser run is not authorized by this Pack.

Do not run migrations, seeds, browser installation, external network, persistent database, queue, cache, Scribe, asset, server, or watcher commands.

## 28. Expected Output

- three Core contract files and one focused Core unit test;
- explicit metadata method on the Scenario contract;
- Registry metadata retention/lookup;
- updated fake Scenario implementations;
- no observable change to existing command execution;
- a Run Report and index updates only after actual execution and reporting gates.

## 29. Operator Execution Checklist

### Before AI Execution

- Confirm Pack 0004 is the only implementation Pack authorized for execution.
- Confirm later Draft Packs must not be implemented in the same turn.

### After AI Execution

- Verify the enum names and values are generic and contain no target-system terminology.
- Verify no executable behavior was added to metadata.

## 30. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md` and include documentation-maintenance reporting. Report exact tests, changed files, scope, error/security impact, and the fact that later Packs remain Draft.

## 31. Review Checklist

- [ ] ownership matches project cross-module scope;
- [ ] Core types are capability-neutral;
- [ ] tests-as-code remains intact;
- [ ] invalid values are safely rejected;
- [ ] current execution behavior and persistence are unchanged;
- [ ] no downstream Draft scope leaked into this Pack.

## 32. Rollback / Safety Notes

Rollback removes the new enum/value files, restores the previous Scenario interface and Registry entry shape, and restores fake Scenario implementations. No database or external state exists. Do not use destructive Git commands.

## 33. Stop Conditions

Stop and report if:

- implementing metadata requires target-specific fields or parsing external JSON;
- a database, Profile, command, browser, dependency, API, UI, queue, or artifact change becomes necessary;
- a public contract cannot be extended without an unlisted implementation file;
- invalid metadata could expose raw values through an exception or log;
- any file outside sections 8 and 9 requires a non-governance change.

## 34. Open Questions

None blocking. Lazy enumeration, evidence enforcement, secret access, fixture lifecycle, and batch statuses are deliberately owned by later Draft Packs.
