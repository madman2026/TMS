# CHANGE-REQUEST-TMS-CONFIGURATION-SURFACE-0008 — Complete and verify configuration and environment declarations

## 1. Title

Complete the versioned configuration surface and environment-variable example contract.

## 2. Status

Implemented and accepted — configuration, environment manifest, regression coverage, and validation completed; operator authorized commit on 2026-10-11.

## 3. Related Release / Phase

Out-of-sequence repository configuration maintenance; no active roadmap stage changes.

## 4. Decision Source

`docs/project/ai/decisions/TMS-DECISION-0020-configuration-surface-remediation.md`, accepted from the operator instruction on 2026-10-11.

## 5. Proposed Change

Establish a deterministic repository configuration contract that:

1. inventories literal and approved dynamic configuration reads in root, Core, and DK first-party source;
2. maps every referenced top-level namespace to a root file, an owner-local module config/provider, or a verified installed dependency provider;
3. creates a versioned first-party config file only for a genuinely unresolved first-party namespace and only after its exact path is added to the approved Remediation scope;
4. declares every active first-party `env()` key in `.env.example` with a safe representation of its existing default or placeholder semantics;
5. proves no first-party application code reads `env()` outside configuration files;
6. adds regression coverage for namespace resolution and `.env.example` completeness;
7. provides an operator-only checklist for applying reviewed defaults/placeholders to `.env` without the AI Agent reading or editing that file.
8. completes root and module Core browser configuration with synchronized env-backed defaults.

## 6. Reason for Change

Configuration references and environment declarations were not governed by one executable consistency check. The generation-time source audit found all referenced roots resolvable after framework/package providers are considered, but found 81 active first-party environment keys absent from `.env.example`. The manifest gap is now implemented; the remaining regression test is required to prevent future config/env drift.

## 7. Scope of Impact

Affected areas:

- versioned configuration metadata and environment examples;
- root/Core/DK configuration discovery;
- test/development setup documentation;
- focused configuration regression tests;
- project lifecycle documentation.

Not affected:

- API contracts, routes, permissions, Admin UI, database schema/data, models, jobs, queue execution, cache state, logs, translations, external targets, dependencies, or production secrets.

## 8. Affected Files

Implementation files:

- `.env.example`
- `config/core.php`
- `Modules/Core/config/config.php`
- `tests/Feature/ConfigurationSurfaceTest.php` (new)

Lifecycle files:

- Decision 0020 and `DECISIONS-INDEX.md`
- this Change Request and `CHANGES-INDEX.md`
- Remediation 0005 and `REMEDIATIONS-INDEX.md`
- a Run Report and run index only after the reporting gate

Protected/current-work files:

- `.env` must not be read or edited by the AI Agent;
- the pre-existing modified `phpunit.xml` must not be changed by this remediation;
- dependency config under `vendor/` is read-only evidence.

## 9. Backward Compatibility

Yes. The correction documents existing effective defaults and verifies current config resolution. It must not change runtime defaults, add a new production-required variable, or replace dependency-provided config with copied local snapshots.

## 10. Risk Level

Medium. `.env.example` is broadly consumed by local setup and CI bootstrap, and an incorrectly enabled empty variable can override a computed/default value. The remediation therefore distinguishes enabled scalar defaults from commented optional, sensitive, null, or computed examples.

## 11. Migration or Remediation Needed

Yes. `docs/project/ai/remediations/REMEDIATION-PACK-TMS-CONFIGURATION-SURFACE-0005.md` owns the approved correction.

## 12. Required Human Approval

Yes. The operator must separately approve Remediation 0005 execution, manually synchronize `.env` after reviewing the generated manifest, and accept or reject the result before any commit.

## 13. Acceptance Criteria

- [x] every active first-party `env()` reference in versioned config is declared exactly once in `.env.example`;
- [x] root and module Core config expose synchronized `ACCEPTANCE_BROWSER`, `ACCEPTANCE_HEADLESS`, `ACCEPTANCE_BROWSER_TIMEOUT_MS`, and `ACCEPTANCE_BROWSER_SLOW_MO_MS` defaults;
- [x] declarations preserve the effective config fallback and do not inject an empty value where absence is semantically different;
- [x] sensitive variables contain no real value and use safe placeholders/comments;
- [x] no active first-party `env()` call exists outside configuration files;
- [x] every first-party configuration root/key exercised by source resolves after application boot;
- [x] dependency-provided `modules` and `view` roots are verified without publishing/copying vendor config;
- [x] no speculative first-party config file is created when the namespace is already resolved;
- [x] focused tests, syntax checks, `git diff --check`, and scoped working-tree checks pass;
- [x] `.env`, the pre-existing `phpunit.xml` modification, dependencies, databases, caches, queues, and external systems remain untouched by this manifest execution; `config/core.php` changed only in the separately approved Core slice;
- [x] the operator receives a complete manual `.env` synchronization checklist.

## 14. Rollback Plan

Revert only the `.env.example` additions and remove the new focused test, then synchronize the directly related lifecycle records. Do not delete, restore, or overwrite `.env`, `config/core.php`, `phpunit.xml`, or unrelated user changes.

## 15. Final Decision

Approved and implemented. The operator accepted the completed remediation and separately authorized commit on 2026-10-11.

## 16. Required remediation

`docs/project/ai/remediations/REMEDIATION-PACK-TMS-CONFIGURATION-SURFACE-0005.md`

## 17. Notes

If approved execution discovers a genuinely unresolved first-party namespace, stop and amend the Remediation Pack with the exact config path, owner, defaults, environment keys, and tests before creating it.
