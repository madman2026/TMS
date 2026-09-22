# AI-PACK-TMS-DOCS-BOOTSTRAP-0001 — Three-layer documentation governance bootstrap

Status: `draft — generated; execution requires separate operator approval`

Generated: `2026-09-22`

## 1. Task ID

`AI-PACK-TMS-DOCS-BOOTSTRAP-0001`

## 2. Task Title

Bootstrap the reusable, project, and owned-module documentation layers for TMS.

## 3. Goal

Establish a minimal but complete three-layer documentation-governance foundation in TMS without changing application behavior:

1. reusable AI governance under `docs/ai/`;
2. TMS-wide and cross-module knowledge/lifecycle records under `docs/project/`;
3. owner-local documentation under active owned modules.

The Pack must adapt the proven governance model to TMS. It must not blindly copy Martfury, Botble, Notification Delivery, Release 4, migration-history, or plugin-specific bindings.

## 4. Context

Before this Pack was generated, TMS had no root `AGENTS.md`, documentation router, project profile, or owner-local documentation manifest. The only current documentation-governance files are this draft Pack and its project Pack index.

Repository inspection at baseline commit `73be9bf1a1a33a15c65c48cd146f83e01eedc2a0` found:

- Laravel `^12.0`, PHP `^8.2`, Sanctum `^4.0`, `nwidart/laravel-modules` `^12.0`, Scribe `^5.8`, PHPUnit `^11.5.3`, and Playwright PHP;
- `Core` as the only enabled and retained module after the operator-approved removal of the unused `Auth` and `Ecommerce` modules;
- root application dependencies on `Core` contracts and runtime traits;
- an operator-approved pre-bootstrap cleanup that removed both unused module trees, their direct root orchestrator/request/middleware dependencies, stale module cache, and stale generated Auth API documentation;
- only default example tests are present;
- operator-owned pre-existing changes in `composer.lock` and `package-lock.json`.

Preliminary ownership classification for this Pack:

| Area | Initial owner | Layer | Bootstrap action |
|---|---|---|---|
| reusable workflow, rules, templates, startup routing | shared AI governance | Layer 1 | import and TMS-neutralize |
| root application, cross-module orchestration, source analysis, repository lifecycle | TMS project | Layer 2 | create project entry/profile/indexes |
| `Modules/Core` | owned technical module | Layer 3 | activate owner-local routing |

Operator intent summary (Persian): این Pack فقط زیرساخت مستندات سه‌لایه را برای TMS ایجاد می‌کند، کد اجرایی را تغییر نمی‌دهد و Core را به‌عنوان owner محلی فعال می‌کند. ماژول‌های استفاده‌نشده Auth و Ecommerce پیش از اجرای این Pack با دستور مستقیم اپراتور حذف شده‌اند و نباید به‌عنوان owner یا مثال فعال بازگردند.

## 5. Related Release / Phase

Not applicable. This is the pre-Release documentation foundation Pack.

## 6. Related Epic / Feature / Story

Documentation governance bootstrap for TMS.

## 7. Source References

Reference implementation repository:

- root: `C:/Users/DieselKhodro/Documents/dk`
- approved source commit: `48ca17f75dfd49072cabaac86155cad73837eecb`
- reusable Core inventory: exactly the 37 `docs/ai/` files enumerated under `Files to Create — Layer 1`, plus a separately adapted root `AGENTS.md`

TMS source baseline:

- repository root: `C:/Users/DieselKhodro/Documents/TMS`
- cleanup base commit: `73be9bf1a1a33a15c65c48cd146f83e01eedc2a0`
- current pre-bootstrap working tree: operator-approved Auth/Ecommerce removal, this draft Pack/index, and pre-existing `composer.lock`/`package-lock.json` changes
- execution prerequisite: pin the accepted cleanup state to a fresh commit or explicitly approved clean baseline before executing this Pack

The source repository is design/reference input only. TMS becomes the source of truth for its copied and adapted documentation after this Pack is accepted.

## 8. Files to Create

### Layer 1 — reusable governance

- `AGENTS.md`
- `docs/ai/AI-DOCS-INDEX.md`
- `docs/ai/STRUCTURE-GUIDE.md`
- `docs/ai/start/START-HERE.md`
- `docs/ai/start/CONTEXT-MAP.md`
- `docs/ai/start/AI-STRUCTURE-GUIDE.md`
- `docs/ai/rules/ADMIN-USER-GUIDE-RULES.md`
- `docs/ai/rules/AI-EXECUTION-RULES.md`
- `docs/ai/rules/AI-PACK-GENERATION-RULES.md`
- `docs/ai/rules/CANONICAL-RULES.md`
- `docs/ai/rules/COMMENTING-RULES.md`
- `docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md`
- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`
- `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md`
- `docs/ai/rules/GIT-AND-COMMIT-RULES.md`
- `docs/ai/rules/OPERATOR-GUIDE-RULES.md`
- `docs/ai/rules/OPERATOR-WORKFLOW-fa.md`
- `docs/ai/rules/OPERATOR-WORKFLOW.md`
- `docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md`
- `docs/ai/rules/REPORTING-RULES.md`
- `docs/ai/rules/REVIEW-RULES.md`
- `docs/ai/rules/RULES-INDEX.md`
- `docs/ai/rules/SCOPE-CONTROL-RULES.md`
- `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`
- `docs/ai/templates/AGENT-FINAL-REPORT-TEMPLATE.md`
- `docs/ai/templates/AI-PACK-TEMPLATE.md`
- `docs/ai/templates/CHANGE-REQUEST-TEMPLATE.md`
- `docs/ai/templates/CONSISTENCY-REVIEW-TEMPLATE.md`
- `docs/ai/templates/DECISION-RECORD-TEMPLATE.md`
- `docs/ai/templates/DOCS-MAINTENANCE-REVIEW-TEMPLATE.md`
- `docs/ai/templates/FINAL-REVIEW-TEMPLATE.md`
- `docs/ai/templates/PHASE-CANONICAL-TEMPLATE.md`
- `docs/ai/templates/RELEASE-CANONICAL-TEMPLATE.md`
- `docs/ai/templates/REMEDIATION-PACK-TEMPLATE.md`
- `docs/ai/templates/REVIEW-TEMPLATE.md`
- `docs/ai/templates/RUN-REPORT-TEMPLATE.md`
- `docs/ai/templates/SOURCE-UPDATE-REVIEW-TEMPLATE.md`
- `docs/ai/templates/TEMPLATES-INDEX.md`

### Layer 2 — TMS project ownership

- `docs/project/PROJECT-DOCS-INDEX.md`
- `docs/project/ai/TMS-AI-PROFILE.md`
- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/decisions/TMS-DECISION-0001-documentation-ownership.md`
- `docs/project/ai/changes/CHANGES-INDEX.md`
- `docs/project/ai/remediations/REMEDIATIONS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md`
- `docs/project/ai/runs/AI-PACK-TMS-DOCS-BOOTSTRAP-0001-run-001.md`
- `docs/project/ai/reviews/REVIEWS-INDEX.md`
- `docs/project/ai/reviews/docs-maintenance/DOCS-MAINTENANCE-REVIEW-TMS-BOOTSTRAP-2026-09-22.md`
- `docs/project/references/REFERENCES-INDEX.md`
- `docs/project/references/SOURCE-DOCS-INDEX.md`
- `docs/project/references/TMS-SOURCE-STRUCTURE-SUMMARY.md`
- `docs/modules/MODULES-DOCS-INDEX.md`

### Layer 3 — active owned modules

For `Core`:

- `Modules/Core/AGENTS.md`
- `Modules/Core/docs/ai/README.md`
- `Modules/Core/docs/ai/manifest.yaml`
- `Modules/Core/docs/ai/GOVERNANCE-PROFILE.md`
- `Modules/Core/docs/ai/canonical/CANONICAL-INDEX.md`
- `Modules/Core/docs/ai/guides/GUIDES-INDEX.md`
- `Modules/Core/docs/ai/decisions/DECISIONS-INDEX.md`
- `Modules/Core/docs/ai/changes/CHANGES-INDEX.md`
- `Modules/Core/docs/ai/remediations/REMEDIATIONS-INDEX.md`
- `Modules/Core/docs/ai/packs/PACKS-INDEX.md`
- `Modules/Core/docs/ai/runs/RUNS-INDEX.md`
- `Modules/Core/docs/ai/reviews/REVIEWS-INDEX.md`
- `Modules/Core/docs/ai/references/REFERENCES-INDEX.md`

## 9. Files to Edit

- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/packs/AI-PACK-TMS-DOCS-BOOTSTRAP-0001.md`

The Pack file and index may be edited only for lifecycle status, execution evidence, acceptance state, and exact post-execution references.

## 10. Files to Read / Reference

TMS files:

- `composer.json`
- `package.json`
- `modules_statuses.json`
- `.gitignore`
- `routes/api.php`
- `app/Contracts/BaseAction.php`
- `app/Contracts/BaseService.php`
- `Modules/Core/module.json`
- `Modules/Core/composer.json`
- `Modules/Core/app/Contracts/DKAPI.php`
- `Modules/Core/app/Contracts/TestContext.php`
- `tests/Feature/ExampleTest.php`
- `tests/Unit/ExampleTest.php`

Reference repository files:

- `C:/Users/DieselKhodro/Documents/dk/AGENTS.md`
- `C:/Users/DieselKhodro/Documents/dk/docs/ai/AI-DOCS-INDEX.md`
- `C:/Users/DieselKhodro/Documents/dk/docs/ai/start/START-HERE.md`
- `C:/Users/DieselKhodro/Documents/dk/docs/ai/start/CONTEXT-MAP.md`
- `C:/Users/DieselKhodro/Documents/dk/docs/ai/start/AI-STRUCTURE-GUIDE.md`
- `C:/Users/DieselKhodro/Documents/dk/docs/ai/rules/RULES-INDEX.md`
- `C:/Users/DieselKhodro/Documents/dk/docs/ai/rules/AI-PACK-GENERATION-RULES.md`
- `C:/Users/DieselKhodro/Documents/dk/docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`
- `C:/Users/DieselKhodro/Documents/dk/docs/ai/templates/TEMPLATES-INDEX.md`
- the remaining exact Layer 1 source files mapped one-to-one from the 37-file allowlist in section 8

## 11. Configuration / Settings Requirements

No configuration or settings changes required.

- No config files are edited.
- No environment variables are introduced.
- `.env` must not be read, copied, logged, or modified.
- No Admin Settings, secret storage, queue, cache, or logging setup is required.
- No test/development setup is required.
- No operator manual setup is required after documentation generation.

## 12. Do Not Change

- application/runtime code under `app/`, `Modules/*/app/`, `routes/`, `database/`, `config/`, `resources/`, `tests/`, `bootstrap/`, or `public/`;
- `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, or `modules_statuses.json`;
- `.env`, `.env.example`, credentials, tokens, or local runtime state;
- `vendor/`, `node_modules/`, `storage/`, `.scribe/`, generated API docs, or generated assets;
- the accepted pre-bootstrap removal of the unused `Auth` and `Ecommerce` module trees;
- Release/Phase Canonical content that has not been approved for TMS;
- source-repository history or lifecycle records copied as if they were TMS execution evidence.

## 13. Clarification Questions Before Implementation

Execution requires operator confirmation that `Core` should be an independently governed owned technical module rather than project-level shared infrastructure.

The removal of `Auth` and `Ecommerce` is already an operator decision and is not an open ownership question in this Pack.

## 14. Multilingual / Translation Requirements

- Multilingual / Translation Impact: No
- RTL/LTR Impact: No
- API Message Translation Impact: No
- Admin UI Translation Impact: No
- Owner-defined Default Content Translation Impact: No

No runtime multilingual or translation changes required. Persian is allowed only for the operator-facing workflow/summary files inherited from the reusable governance source.

## 15. UI / Admin UI Requirements

No UI changes required.

## 16. Operator Documentation Requirements

- Operator Documentation Impact: Yes
- Operator Documentation Update Required: Yes
- Guide file: `docs/ai/rules/OPERATOR-WORKFLOW-fa.md`

The adapted Persian workflow must explain TMS owner selection and Pack approval gates. No application-facing operator guide, screenshots, Admin UI change, or runtime workflow change is required.

## 17. Implementation Rules

1. Capture the TMS commit, branch, working-tree status, and SHA-256 hashes of `composer.lock` and `package-lock.json` before changing documentation.
2. Verify the reference repository is clean and still resolves to the approved source commit.
3. Import only the exact 37-file `docs/ai/` allowlist and create the separately adapted root `AGENTS.md` router.
4. Rewrite project-specific routing so active TMS documentation contains no Martfury, Botble, Notification Delivery, `platform/plugins`, Release 4, or prior migration-state authority.
5. Preserve reusable lifecycle procedures and templates unless TMS routing requires a path-neutral substitution.
6. Create TMS project bindings in `TMS-AI-PROFILE.md`; do not embed Laravel/TMS constraints into reusable rules.
7. Classify ownership from actual source scope, consumers, dependencies, and module maturity—not from folder names alone.
8. Create owner-local routing and empty operational indexes only for `Core`.
9. Record the accepted pre-bootstrap removal of `Auth` and `Ecommerce` in `TMS-SOURCE-STRUCTURE-SUMMARY.md`; do not recreate them as documentation owners or examples.
10. Record remaining implementation inconsistencies in `TMS-SOURCE-STRUCTURE-SUMMARY.md` as observations only; do not repair them.
11. Keep active indexes concise and navigation-only.
12. Produce the Run Report and Documentation Maintenance Review only after validation.
13. Keep Pack/Run/Review statuses pending until the operator completes the post-execution acceptance gate.

## 18. Architecture Constraints

- Layer 1 must remain TMS-owner-neutral, Release-neutral, Phase-neutral, framework-neutral where the shared rule does not require a concrete binding.
- Layer 2 owns root application behavior, cross-module knowledge, external-source analysis, repository lifecycle records, and modules without an activated independent owner.
- Layer 3 owns only module-specific product/domain/technical knowledge and module-local lifecycle records.
- Profiles overlay shared governance; they do not copy it, weaken safety gates, or override Canonical decisions.
- Root `AGENTS.md` routes work. Module `AGENTS.md` files tighten scope locally.
- A manifest makes only created and indexed paths authoritative.
- Source code remains implementation truth; accepted Canonical files define intended architecture; accepted Run/Review records define execution truth.
- Do not create empty Release/Phase content merely to populate the tree.

## 19. Validation Rules

Validation must prove:

- every created documentation file is within section 8 and every edited file is within section 9;
- the two pre-existing lockfile changes remain byte-for-byte unchanged;
- no runtime/config/source/test/generated file changed;
- all Markdown repository-relative links and explicit repository paths resolve;
- all Markdown anchors used by links resolve;
- YAML manifests parse and their declared entry/profile/index paths exist;
- JSON files were not changed;
- each important documentation file is reachable from an operational index;
- startup reads route to TMS project or `Core` owner context without loading all documentation;
- `Auth` and `Ecommerce` are absent from active module discovery, owner-local routing, and active documentation authority;
- active TMS docs contain zero unauthorized references to `Martfury`, `Botble`, `Notification Delivery`, `platform/plugins`, `release-4`, or source Pack 0001–0005 lifecycle state;
- no archive path is treated as active authority;
- Pack, Run, Review, Decision, and index lifecycle states agree.

## 20. Security Rules

- Never read, copy, print, or modify `.env`.
- Do not include real URLs, credentials, tokens, headers, phone numbers, API payloads, or secret configuration values in documentation.
- Do not inventory `vendor/`, `node_modules/`, `storage/`, or generated browser/API artifacts beyond confirming they are excluded.
- Use placeholders for all sensitive examples.
- The source summary may name classes and paths but must not reproduce sensitive runtime data.

## 21. Error Handling / Logging / Traceability Requirements

No runtime error handling, logging, or traceability changes required.

Documentation-generation failures must stop execution and be reported without partial acceptance. No API error contract, UI error, exception, external integration, logging, trace identifier, or error-code changes are introduced.

## 22. Data Model / Migration / Relationship Requirements

No data model, migration, or relationship changes required.

No tables, columns, foreign keys, model relationships, snapshots, indexes, constraints, backfills, or rollback migrations are created or modified.

## 23. Commenting Requirements

No source-code comments or PHPDoc changes are allowed. Markdown notes must explain ownership decisions and reasons without duplicating full shared rules.

## 24. Testing Requirements

- Test Quality Required: Yes
- Behavioral Assertions Required: No
- Contract Assertions Required: Yes, for documentation routing/manifests
- Database Assertions Required: No
- Side-effect Assertions Required: Yes, absence of runtime changes
- Negative/Error Scenario Tests Required: Yes, forbidden-reference and broken-link scans
- Authorization/Permission Tests Required: No
- Idempotency Tests Required: Yes, duplicate index entries and duplicate file generation
- Queue/Event/Job Assertions Required: No
- External Integration Fake/Mock Required: No
- Security/Secret Masking Tests Required: Yes, documentation-only secret-pattern scan

No application test is required because this Pack is documentation-only and may not change executable behavior.

## 25. Acceptance Checklist

- [ ] Operator approved the Core ownership classification.
- [ ] Layer 1 contains exactly the approved reusable file set and TMS-neutral routing.
- [ ] Layer 2 has an active project entry, TMS profile, references, lifecycle indexes, and module discovery.
- [ ] Layer 3 routing exists for Core only.
- [ ] Auth and Ecommerce are not registered as active or deferred documentation owners.
- [ ] All indexes and manifests resolve.
- [ ] No foreign project identity or migration state remains authoritative.
- [ ] No application behavior or source/config/test file changed.
- [ ] Pre-existing lockfile changes are preserved exactly.
- [ ] Run and Documentation Maintenance Review are created, indexed once, and technically pass.
- [ ] Human Operator completed the separate post-execution acceptance gate.

## 26. Tests to Add

No application tests to add.

Create no persistent validation script unless the operator separately approves it. Validation commands may use temporary output outside the repository and must report:

- allowlist compliance;
- link/path/anchor integrity;
- manifest parse/path integrity;
- index reachability and duplicate entries;
- forbidden source-project identity leakage;
- secret-pattern scan limited to created documentation;
- unchanged lockfile hashes;
- zero executable-file changes.

## 27. Tests to Run

- `git status --short`
- `git diff --name-only`
- `git diff --check`
- exact created/edited-file allowlist comparison
- Markdown link, explicit path, and anchor validation across active TMS documentation
- YAML parse validation for module manifests
- active-index reachability validation
- duplicate entry validation for Pack, Run, Review, and Decision indexes
- forbidden-reference scan for source-project identities and migration-state leakage
- secret-pattern scan against newly created documentation only
- SHA-256 comparison for `composer.lock` and `package-lock.json`

Do not run Composer, npm, Artisan, database, cache, queue, asset, Scribe, Playwright, or application test commands for this documentation-only Pack.

## 28. Expected Output

- an active root AI router;
- a 37-file reusable governance layer adapted to TMS;
- a TMS project entry/profile and lifecycle/reference navigation;
- an accepted ownership decision after operator approval;
- owner-local routing/manifests/indexes for Core only;
- no active or deferred owner entry for the removed Auth and Ecommerce modules;
- one Run Report and one Documentation Maintenance Review;
- no executable behavior change.

## 29. Operator Execution Checklist

### Before AI Execution

- Confirm the Core ownership classification in section 13.
- Confirm the accepted Auth/Ecommerce cleanup state is the baseline for documentation bootstrap.
- Confirm the reference Core commit `48ca17f75dfd49072cabaac86155cad73837eecb` is still the approved source baseline.
- Confirm the existing `composer.lock` and `package-lock.json` changes must be preserved and excluded.

### After AI Execution

- Verify startup routing selects project or Core correctly.
- Verify Auth and Ecommerce are not discoverable as current modules or documentation owners.
- Accept or reject the generated Run and Documentation Maintenance Review before any commit.

## 30. Agent Final Report

Follow the generated TMS copies of:

- `docs/ai/rules/REPORTING-RULES.md`
- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`

The report must list the source and TMS baseline commits, all created/edited files, validation commands/results, preserved pre-existing changes, ownership decisions, unresolved implementation observations, and required follow-up updates.

## 31. Review Checklist

- [ ] Pack scope matches the actual diff.
- [ ] Shared Core is reusable and contains no TMS owner-specific constraints except routing placeholders where explicitly required.
- [ ] TMS-specific constraints live only in the project/module profiles and owner-local records.
- [ ] Ownership classification is content-based and operator-approved.
- [ ] Source implementation observations are not presented as Canonical decisions.
- [ ] No execution evidence from the reference repository is copied as TMS evidence.
- [ ] All active paths, indexes, and manifests are internally consistent.
- [ ] No unrelated cleanup, code repair, formatting, or dependency change occurred.

## 32. Rollback / Safety Notes

- Before execution, record the exact created-file allowlist and the hashes of the two modified lockfiles.
- Rollback is documentation-only: remove only files created by this Pack and restore the two lifecycle files to their pre-execution state.
- Never use a broad recursive delete against the repository root or `docs/` without validating every target against the Pack allowlist.
- Existing application files and operator-owned lockfile changes must remain recoverable and untouched.

## 33. Stop Conditions

Stop and request operator direction if:

- the ownership classification in section 13 is not approved;
- the reference repository commit or 37-file reusable inventory differs from the approved baseline;
- new TMS changes appear outside the known lockfiles before execution;
- any pre-existing `AGENTS.md` or documentation owner is discovered;
- a required source/project binding cannot be separated from reusable governance;
- execution would require editing application, config, test, dependency, generated, or secret files;
- link/manifest/index validation fails and cannot be corrected within this Pack;
- either removed module reappears in the source tree, module registry, generated route documentation, or module cache before execution.

## 34. Open Questions

1. Should Core remain independently governed long-term, or become project-level shared infrastructure?
2. What product name and one-sentence mission should replace the default Laravel README in a later, separately scoped Pack?
3. Should TMS adopt Release/Phase planning immediately, or keep lifecycle records global until the first product roadmap is approved?
