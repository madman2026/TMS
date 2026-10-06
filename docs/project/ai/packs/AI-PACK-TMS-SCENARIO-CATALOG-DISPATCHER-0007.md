# AI-PACK-TMS-SCENARIO-CATALOG-DISPATCHER-0007 — Lazy Scenario Catalog and Variant Dispatcher

Status: `draft — revised under Decision 0005; normalize after Pack 0006 acceptance`

Generated: `2026-10-05`

Decision: `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`; unchanged CLI/ownership constraints from Decision 0004 still apply.

Depends On: accepted project Packs 0004 and 0006, and accepted Core Browser Observability Pack 0001

Planning Revision: 2026-10-06; superseded Core Evidence Safety 0002 and project Ephemeral Execution Context 0005 are not prerequisites.

Execution Gate: Draft; not executable before normalization and separate operator approval.

## 1. Task ID

`AI-PACK-TMS-SCENARIO-CATALOG-DISPATCHER-0007`

## 2. Task Title

Replace eager scenario materialization with a generic lazy code-owned catalog, selection plan, and variant dispatcher.

## 3. Goal

Let TMS enumerate and select large scenario/variant sets by App, suite, capability, tag, automation disposition, evidence mode, and explicit keys without loading or executing the whole matrix and without making target contracts executable data.

## 4. Context

The current Registry eagerly iterates every Scenario during App registration and exposes only App/scenario key lookup. Large target contracts require lazy descriptors and variant expansion. Active predecessors establish generic metadata, browser observations, and synthetic testing/staging fixture prerequisites. Decision 0005 removes standalone secret/evidence frameworks; this Pack carries only safe catalog/plan boundaries and does not execute batches.

## 5. Related Release / Phase

Not applicable; pre-Release project catalog/orchestration Pack.

## 6. Related Epic / Feature / Story

Reusable human-acceptance automation — scalable code-owned scenario selection.

## 7. Source References

Decisions 0002–0005; accepted active predecessor source/Run records; current AcceptanceApp/Scenario contracts, Registry, command, Profile, and tests; current Laravel command discovery behavior. Decision 0005 controls the revised safeguard scope and dependencies.

## 8. Files to Create

Anticipated, subject to normalization:

- code-owned catalog/provider, scenario descriptor, variant descriptor/provider, selector, and immutable execution-plan contracts/data;
- `acceptance:list` and/or `acceptance:plan` Artisan commands with safe JSON output;
- focused unit/feature tests;
- project Run Report after execution.

## 9. Files to Edit

Anticipated AcceptanceApp contract, AcceptanceAppRegistry, AppServiceProvider registration, existing `acceptance:run` resolution path, and exact affected tests. No path is authorized until normalized.

## 10. Files to Read / Reference

Repository/project/Core routing/profiles; shared scope/execution/error/test/reporting rules; accepted active predecessors; Decision 0005; current App/Scenario/Registry/command/service/provider source and tests. Superseded proposals are not implementation prerequisites.

## 11. Configuration / Settings Requirements

Expected no environment, secret, Admin Setting, queue/cache/log-channel, or database configuration. Any plan safety cap/default selector belongs in versioned non-sensitive config only if normalization proves it necessary. Expanded operator-configurable browser/Profile/CLI options, option-resolution precedence, and effective-browser-settings snapshots remain deferred. No secret-manager/provider/store, authentication lease, or capture-policy framework is required.

## 12. Do Not Change

Target contract schema/importer, target selectors/actions/fixtures/auth, Runner/Browser behavior, expanded browser/Profile/CLI settings, generic secret infrastructure or capture guards, new capture/recording/export features, database/batch persistence, API/UI, queue/scheduler/parallelism/retry, dependencies, `.env`, vendor, or target repositories.

## 13. Clarification Questions Before Implementation

Normalization must resolve:

1. exact lazy provider/descriptor/variant interfaces after accepted predecessors;
2. selector grammar, precedence, stable ordering, and duplicate identity rules;
3. plan fingerprint/version inputs and bounded count behavior;
4. safe list/plan command output and limits;
5. compatibility strategy for the existing single-scenario command.

## 14. Multilingual / Translation Requirements

CLI JSON fields/error codes remain language-neutral. Any new human-readable command help/message must follow the actual project translation convention resolved during normalization. No UI/RTL impact.

## 15. UI / Admin UI Requirements

No UI changes required.

## 16. Operator Documentation Requirements

Operator Documentation Impact: Yes. The normalized Pack must document catalog inspection, selector syntax, plan-only behavior, caps, synthetic testing/staging scope, and the distinction between descriptor discovery and execution. Explain safe descriptor output and that evidence-mode metadata does not prove runtime capture enforcement; do not add a secret-manager/capture-policy setup workflow.

## 17. Implementation Rules

- App registration remains explicit source code; no filesystem/module auto-discovery.
- Descriptors/variants are lazy iterables and must not be materialized globally during boot.
- Target Apps translate their own contract/source into generic code-owned providers; shared TMS does not parse ND JSON.
- Selection is deterministic, stable, duplicate-safe, and side-effect free.
- `manual-only`, `blocked`, and `not-implemented` remain visible/countable but are not execution-eligible.
- Listing/planning must not create Test/Step/fixture/secret/browser/external state.
- Listing/planning does not resolve usable test-account values or expose authenticated state. Descriptor/variant identity and fingerprints use approved non-secret metadata only; fake sentinel values must be absent from output and normalized errors/logs.
- Keep evidence-mode classification descriptive. Do not introduce the superseded runtime capture guard or a generic secret provider as a catalog prerequisite.
- Existing direct App/scenario execution remains compatible or receives an explicitly normalized migration path.

## 18. Architecture Constraints

Preserve tests-as-code. The database does not define executable workflows. Generic descriptors may contain safe identity/classification metadata only; actions, locators, fixtures, auth, payloads, and expectations remain target-App source behavior. Apply Decision 0005's minimum safe-output boundary without implementing target authentication, secret infrastructure, recording, or expanded browser settings.

## 19. Validation Rules

Prove lazy behavior with a provider that fails if over-enumerated; deterministic ordering/fingerprints; selector intersections; duplicate/invalid identity rejection; empty selection; disposition exclusion; safe JSON/errors/logs excluding fake credential/session/token sentinels; no credential resolution or other execution side effects; and existing command compatibility.

## 20. Security Rules

Catalog/plan output may include only approved keys, counts, classifications, and fingerprints. Exclude URLs, selectors, usable credentials, session cookies/tokens, authenticated storage, secret refs/values, fixture payloads, personal identifiers, step results, and target raw data. Use synthetic testing/staging descriptors and fakes. Minimum omission is owned here; no standalone secret/evidence prerequisite, runtime capture-proof claim, or new capture feature.

## 21. Error Handling / Logging / Traceability Requirements

Expected errors: invalid selector, unknown App/scenario/suite/capability/tag, duplicate identity, invalid descriptor/variant, plan cap exceeded, and catalog changed during planning. Exact stable codes, exit codes, safe JSON, log events, and fingerprint trace are deferred to normalization. Invalid provider/descriptor messages containing fake authentication sentinels must be normalized without leaking the value; no new current error code is defined here. No API/UI impact.

## 22. Data Model / Migration / Relationship Requirements

No database, migration, model relationship, snapshot, retention, or backfill is expected. Persisted batch plans belong to Pack 0008.

## 23. Commenting Requirements

Explain lazy iteration, identity/fingerprint invariants, selector precedence, and why descriptors cannot contain executable or sensitive data.

## 24. Testing Requirements

Mandatory lazy-enumeration, deterministic ordering, selector matrix, duplicate/invalid input, empty/cap, disposition, safe-output, minimum credential/session/token omission, no-side-effect, and single-run regression tests using synthetic providers. No standalone secret/capture infrastructure tests.

## 25. Acceptance Checklist

- [ ] exact generic provider/descriptor/variant contracts normalized;
- [ ] App registration remains explicit and lazy;
- [ ] deterministic selection/fingerprint proven;
- [ ] non-automated dispositions cannot enter executable plan;
- [ ] plan/list creates no execution state;
- [ ] output contains no target-sensitive data;
- [ ] fake credential/session/token sentinels do not enter descriptor output, fingerprints, errors, or logs, and planning performs no credential resolution;
- [ ] no expanded settings, secret framework, or comprehensive capture guard introduced;
- [ ] current single-scenario path remains valid.

## 26. Tests to Add

Normalize into named lazy-iteration, stable-order/fingerprint, selectors, duplicate identity, invalid/unknown/empty/cap, disposition exclusion, output allowlist, side-effect absence, and compatibility tests. Add a synthetic provider success/failure pair proving authentication sentinels never enter list/plan JSON, normalized errors/logs, or identity/fingerprint inputs, and no test-account acquisition occurs during planning.

## 27. Tests to Run

Deferred until normalization. No browser, target network, fixture, secret, persistent migration/database, queue, or batch execution is authorized by this Draft.

## 28. Expected Output

A generic lazy catalog/dispatcher and safe inspection/planning commands, with no batch execution or target importer.

## 29. Operator Execution Checklist

### Before AI Execution

- Approve normalized selector grammar, plan cap, and compatibility behavior.

### After AI Execution

- Inspect a synthetic large-provider plan and verify bounded lazy behavior and safe output.

## 30. Agent Final Report

Report exact selection contracts, laziness evidence, counts/fingerprint behavior, output fields, minimum omission tests, and zero execution side effects. Do not imply a runtime secret-management or no-capture framework exists.

## 31. Review Checklist

Review tests-as-code compliance, target neutrality, lazy behavior, deterministic identity/fingerprint, error/output safety, and backward compatibility.

## 32. Rollback / Safety Notes

Rollback restores the previous Registry/App contract and command resolution and removes new plan/list commands. No database or target state exists.

## 33. Stop Conditions

Stop if target JSON becomes executable in shared TMS, registration requires scanning, full materialization is required, non-automated variants become executable, sensitive fields enter descriptors/output, expanded settings or secret/capture infrastructure is required outside this scope, database/batch scope is needed, or an active predecessor is unaccepted. Superseded Packs are not prerequisites.

## 34. Open Questions

The five normalization questions in section 13 block execution.
