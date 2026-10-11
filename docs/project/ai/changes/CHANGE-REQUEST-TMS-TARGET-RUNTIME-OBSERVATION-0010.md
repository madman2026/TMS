# CHANGE-REQUEST-TMS-TARGET-RUNTIME-OBSERVATION-0010 — Add bounded target runtime input and semantic Browser observation

## 1. Title

Add validated prerequisite-handle handoff to target Browser runtime resolution and an optional bounded target-semantic observation port.

## 2. Status

Implemented and validated by project Pack 0020 / Run 001 — pending operator acceptance; commit authorized 2026-10-11.

## 3. Related Release / Phase

Project-owned enabling work before DK stage-14 Pack 0004. It does not add, remove, or reorder a Decision-0015 roadmap stage.

## 4. Decision Source

Accepted `TMS-DECISION-0021`, triggered by the operator-approved stop/report outcome for DK Pack 0004 on 2026-10-11.

## 5. Proposed Change

Establish one clean shared cutover that:

1. changes `TargetBrowserRuntimeResolver::resolve(...)` to receive the final `TargetContext` and the exact validated `PrerequisiteExecutionData` for the same tuple/profile;
2. passes that object directly from `AcceptanceExecutionService` without copying, reparsing, resolving, logging, or persisting its values;
3. keeps raw `app-secret://...` dereferencing and all access-bearing material inside a target-owner resolver injected into the target implementation;
4. adds `BrowserTargetObserver`, an optional Core contract implemented only by runtimes that support bounded target-semantic facts;
5. adds a validated immutable `BrowserTargetObservation` with one observation key and a small bounded scalar/token fact map;
6. adds `TestContext::observeTarget(...)`, which validates the requested key, requires the optional observer, checks returned-key equality, and fails closed through the existing `acceptance_configuration_invalid` contract;
7. keeps existing `BrowserProbe`, `BrowserTargetRuntime`, target resource lifecycle, persisted results, and non-observer runtimes unchanged;
8. updates every in-repository resolver implementation/test double through one clean signature migration.

The proposed fact boundary accepts only language-neutral keys and scalar values required for objective assertions. It rejects nested data, arbitrary text, invalid UTF-8, overlong tokens, non-finite/out-of-range numbers, unknown key syntax, oversized maps, and observation-key mismatch.

## 6. Reason for Change

DK Pack 0004 cannot safely execute with the current source:

- final safe resource references reach the runtime resolver, but the validated prerequisite secret-reference handles do not;
- `BrowserProbe` intentionally exposes only generic visibility/layout/focus/semantics/media/viewport/touch facts and cannot return Operations Health computed/effective/freshness/reason/source values;
- carrying raw access values in `TargetContext`/`ResourceReference`, using static state, hardcoding a URL, reading raw DOM/text in Scenario code, or widening Core with DK-specific schemas would violate accepted boundaries.

The proposed design supplies missing context at the existing target-owner boundary and lets the target runtime normalize its own UI into bounded semantic facts without exposing target internals.

## 7. Scope of Impact

Affected areas:

- project target Browser runtime resolver contract and execution call site;
- Core optional target-observer contract, immutable fact value, and `TestContext` accessor;
- exact project/Core tests and anonymous resolver test doubles affected by the signature cutover;
- project and DK lifecycle documentation.

Not affected:

- target account/fixture/oracle/cleanup ordering or safe `ResourceReference` schema;
- prerequisite persistence/input contracts or actual secret-reference resolution;
- API/HTTP execution, CLI presentation, database schema, queues/jobs, callbacks, Provider behavior, configuration, `.env`, UI source, translations, dependencies, or target product source;
- real/staging/production/remote/shared targets;
- any DK scenario implementation or executable disposition.

## 8. Affected Files

The exact implementation boundary is owned by:

- `docs/project/ai/packs/AI-PACK-TMS-TARGET-RUNTIME-OBSERVATION-0020.md`.

DK-owned access resolution, runtime observation mapping, selectors, fixtures, Scenario Steps, oracle, cleanup, and actual Browser validation remain exclusively in a subsequently renormalized DK Pack 0004.

## 9. Backward Compatibility

External operator, API, persisted, database, configuration, and target contracts remain compatible. The PHP resolver method signature changes intentionally through one clean in-repository cutover; every first-party implementation/test double must be migrated in Pack 0020. Existing runtimes need not implement `BrowserTargetObserver` unless a Scenario requests target-semantic observations.

## 10. Risk Level

High. The change crosses project/Core ownership and touches the runtime resolution path. Risk is bounded by no external I/O, no concrete target implementation, no raw-value resolution, optional observer capability, strict value validation, focused fake-only tests, affected regressions, full suite, and separate DK/browser gates.

## 11. Migration or Remediation Needed

Yes. Standard project Pack 0020 performs the planned clean signature migration and adds the optional observer capability. This is not a post-defect Remediation Pack because no accepted implementation is being repaired; a missing future capability is being introduced deliberately.

## 12. Required Human Approval

Yes. The operator approved creating this Change Request and enabling Pack, then separately approved Pack 0020 execution, Run reporting, and commit on 2026-10-11. Run 001 acceptance, DK Pack 0004 execution, and target/browser access remain separate approvals.

## 13. Acceptance Criteria

- [x] resolver receives the exact final context and exact validated prerequisite object for the same tuple/profile;
- [x] shared code never resolves a secret reference or observes a raw value;
- [x] raw URL/session/credential/access values cannot enter safe references, lifecycle results, Test/Step persistence, logs, reports, exceptions, or docs;
- [x] non-observer runtimes retain existing prepare/open behavior;
- [x] target observations accept only bounded semantic facts and reject raw/nested/unsafe data before Scenario use;
- [x] missing observer, invalid key/fact, or mismatched returned key fails with the existing safe configuration code;
- [x] no new error code, logging event, persistence field, API/UI/configuration/dependency surface, or target-specific Core behavior is introduced;
- [x] PHP syntax, scoped formatting, focused tests, affected regressions, full PHPUnit suite, safe scans, and diff checks pass;
- [x] DK Pack 0004 remains non-executable until Pack 0020 is accepted and its separate target gate is satisfied.

## 14. Rollback Plan

Revert only Pack-0020-owned source/test changes, restore the prior resolver signature at every in-repository implementation/call site, remove the optional observer/value/accessor, and synchronize the related Decision/Change/Pack/DK lifecycle records. Do not use broad reset/restore/clean commands. No data, configuration, target, dependency, or environment rollback is required.

## 15. Final Decision

Implemented and validated through project Pack 0020 / Run 001. Commit was authorized on 2026-10-11; operator acceptance remains separately gated.

## 16. Required remediation

No Remediation Pack is currently required. If execution discovers an accepted-contract defect outside Pack 0020's exact scope, stop and open a separate Change Request or Remediation as classified by governance rules.

## 17. Notes

- Reuse existing `acceptance_configuration_invalid`, `resource_unavailable`, and existing Browser/Scenario failure contracts.
- The target owner may receive secret-reference handles, not raw secret values, through the shared resolver call.
- The semantic observation port is not permission to return raw labels, text, DOM, selector diagnostics, URLs, payloads, or arbitrary target data.
