# AI-PACK-TMS-CENTRAL-TEST-BROWSER-MODE-0018 — Central PHPUnit browser mode

Status: accepted — operator pre-approved execution, result, report, and commit 2026-10-11; focused validation passed

Generated: 2026-10-11

Owner: TMS project (cross-boundary test configuration with Core-owned browser tests)

Decision: Decision 0005 testing/staging and synthetic/local-browser constraints; accepted Core Browser Observability Pack 0001.

## 1. Task ID

AI-PACK-TMS-CENTRAL-TEST-BROWSER-MODE-0018

## 2. Task Title

Centralize Unit and Feature browser headless mode in phpunit.xml.

## 3. Goal

Make one PHPUnit environment value control headed/headless launch behavior for the repository's browser-backed tests while preserving the current headless default.

## 4. Context

`phpunit.xml` currently has no browser-mode setting. Core's Laravel configuration defaults `core.acceptance.headless` to `true`, `PlaywrightAcceptanceSmokeTest` relies on the `RunOptions` default, and `PlaywrightBrowserProbeSmokeTest` hardcodes `withHeadless(true)`. These paths can therefore drift. A pre-existing untracked `config/core.php` contains a separate hardcoded value and must not be changed or treated as Pack output.

## 5. Related Release / Phase

Out-of-sequence test-infrastructure maintenance; it does not change the active sixteen-stage roadmap.

## 6. Related Epic / Feature / Story

Developer test configuration and local browser observability.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`
- `Modules/Core/docs/ai/packs/AI-PACK-CORE-BROWSER-OBSERVABILITY-0001.md`
- current source files listed in section 10

## 8. Files to Create

- `tests/Support/BrowserTestConfiguration.php`
- `tests/Unit/Support/BrowserTestConfigurationTest.php`
- `docs/project/ai/runs/AI-PACK-TMS-CENTRAL-TEST-BROWSER-MODE-0018-run-001.md` only after the post-execution reporting gate

## 9. Files to Edit

- `phpunit.xml`
- `Modules/Core/config/config.php`
- `Modules/Core/tests/Feature/PlaywrightAcceptanceSmokeTest.php`
- `Modules/Core/tests/Feature/PlaywrightBrowserProbeSmokeTest.php`
- `tests/Feature/AcceptanceRunCommandTest.php`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md` only when a Run Report is authorized
- this Pack for lifecycle/status synchronization

## 10. Files to Read / Reference

- repository startup files and applicable shared execution/test/reporting rules
- `docs/project/ai/TMS-AI-PROFILE.md`
- Core router, manifest, entry, and governance profile
- `composer.json`
- `phpunit.xml`
- `Modules/Core/config/config.php`
- `Modules/Core/app/Data/RunOptions.php`
- `Modules/Core/app/Services/PlaywrightBrowserFactory.php`
- the two Core Feature smoke tests listed in section 9
- `tests/Feature/AcceptanceRunCommandTest.php`

Do not read environment files, unrelated Packs/Runs/Reviews, external targets, or browser profiles.

## 11. Configuration / Settings Requirements

Configuration / Settings Impact: Yes, test-focused.

- Add `ACCEPTANCE_HEADLESS=true` to `phpunit.xml`; changing this single value to `false` must select headed launch for browser-backed PHPUnit tests.
- Read `ACCEPTANCE_HEADLESS` only from the Core configuration file in application code, retaining `true` as the fallback outside PHPUnit.
- A test-support reader may read the PHPUnit environment directly for raw PHPUnit tests that do not boot Laravel; it must normalize booleans consistently and reject invalid values clearly.
- No `.env`, `.env.example`, Admin Settings, secrets, credentials, target URLs, database, queue, cache, or logging configuration changes.

## 12. Do Not Change

- the pre-existing untracked `config/core.php`
- production execution semantics beyond allowing the existing headless default to be supplied by `ACCEPTANCE_HEADLESS`
- browser engine/channel, launch arguments, viewport, timeouts, routes, fixtures, assertions, cleanup, or evidence behavior
- dependencies, Composer files, package files, modules status, database files, application data, `.env*`, or external systems
- unrelated source, tests, documentation, formatting, or roadmap records

## 13. Clarification Questions Before Implementation

No design question remains. The central key is `ACCEPTANCE_HEADLESS`; the committed PHPUnit default remains `true`.

## 14. Multilingual / Translation Requirements

No multilingual or translation changes required.

## 15. UI / Admin UI Requirements

No UI or Admin UI changes required. Setting the value to `false` may make the locally launched test browser visible; that is developer test behavior, not product UI.

## 16. Operator Documentation Requirements

Operator Documentation Impact: No. No operator-facing product behavior changed and no guide update is required.

## 17. Implementation Rules

- Use one named test-support abstraction for raw PHPUnit browser tests; do not duplicate environment parsing.
- Keep `env()` inside `Modules/Core/config/config.php`; Laravel/application code continues to read `config('core.acceptance.headless')`.
- Pass the resolved boolean explicitly to every real browser launch path in the two Core Feature smoke files.
- Preserve all existing local-only fixtures, network abortion, finally cleanup, and assertions.
- Do not modify the pre-existing untracked `config/core.php`.

## 18. Architecture Constraints

- `phpunit.xml` is the central test-runner source for the setting across Unit and Feature suites.
- Core remains the owner of browser runtime defaults and Core smoke tests; project test support only adapts the PHPUnit environment for tests that do not boot Laravel.
- No new dependency or production service abstraction.

## 19. Validation Rules

- Confirm `true` and `false` normalize to exact booleans.
- Confirm Laravel's `core.acceptance.headless` equals the shared PHPUnit test setting.
- Confirm both Core real-browser fixtures pass the shared setting to their launch options.
- Run browser smoke only with the committed headless default; headed execution is configurable but not launched during this Pack unless separately requested.
- Run PHP syntax, focused tests, `git diff --check`, scoped diff, and final working-tree inspection.

## 20. Security Rules

- Browser smoke remains local, synthetic, non-networked, and isolated from personal browser profiles.
- No secret, token, credential, target URL, user data, or raw payload may be added or emitted.

## 21. Error Handling / Logging / Traceability Requirements

Error Handling Impact: Yes, test configuration validation only.

- An invalid `ACCEPTANCE_HEADLESS` value in a raw PHPUnit test path must fail early with a safe configuration exception/message that names the setting but does not echo the supplied value.
- No API/UI error contract, application exception, logging, traceability, error-code, or sensitive-data behavior changes.
- Unit coverage must prove invalid values are rejected.

## 22. Data Model / Migration / Relationship Requirements

No data model, migration, relationship, snapshot, index, backfill, or retention changes required.

## 23. Commenting Requirements

Add comments only if needed to explain why raw PHPUnit tests cannot use Laravel `config()`; do not narrate obvious parsing or Playwright calls.

## 24. Testing Requirements

Test Quality Required: Yes.

- Unit coverage proves normalization of the supported PHPUnit boolean forms, fallback behavior, and safe rejection of invalid input without launching a browser.
- Existing Laravel Feature coverage proves the framework configuration resolves to the same centralized test value.
- Existing Core Feature smoke tests provide launch integration evidence with synthetic local content and guaranteed cleanup.
- No database assertion, authorization test, external integration, or new network behavior is applicable.

## 25. Acceptance Checklist

- [x] `phpunit.xml` owns the committed `ACCEPTANCE_HEADLESS` value.
- [x] Laravel Core config reads the same value and retains a `true` fallback.
- [x] Both real-browser Feature fixtures consume the same setting.
- [x] Unit coverage proves boolean parsing and invalid-value behavior without a browser.
- [x] Existing browser engine/channel, safety, and cleanup behavior remains unchanged.
- [x] The untracked `config/core.php` is unchanged.
- [x] Focused validation passes and no out-of-scope file is changed.

## 26. Tests to Add

- `BrowserTestConfigurationTest`: prove `true`/`false` values normalize exactly, the missing value defaults to `true`, and an invalid value fails safely without exposing that value.
- Extend `AcceptanceRunCommandTest::test_omitted_runtime_options_use_configured_defaults` to prove Laravel configuration and the shared PHPUnit setting agree.

## 27. Tests to Run

After operator approval, from repository root:

```powershell
php -l tests/Support/BrowserTestConfiguration.php
php -l tests/Unit/Support/BrowserTestConfigurationTest.php
php -l Modules/Core/config/config.php
php -l Modules/Core/tests/Feature/PlaywrightAcceptanceSmokeTest.php
php -l Modules/Core/tests/Feature/PlaywrightBrowserProbeSmokeTest.php
php -l tests/Feature/AcceptanceRunCommandTest.php
php vendor/bin/phpunit --do-not-cache-result tests/Unit/Support/BrowserTestConfigurationTest.php
php vendor/bin/phpunit --do-not-cache-result --filter test_omitted_runtime_options_use_configured_defaults tests/Feature/AcceptanceRunCommandTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Feature/PlaywrightBrowserProbeSmokeTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Feature/PlaywrightAcceptanceSmokeTest.php
git diff --check
git diff --name-only
git status --short --branch
```

Classification: syntax commands are safe read-only; PHPUnit/browser commands are validation-or-test and may create temporary processes/files. Browser tests may launch only local system Chrome with synthetic in-memory content and blocked routes. No server, dependency install, database migration, cache operation, external network request, formatter mutation, or headed browser validation is authorized.

## 28. Expected Output

One `ACCEPTANCE_HEADLESS` entry in `phpunit.xml`, one shared test parser, Core config wired to the same key, both Core browser smoke launch paths wired to the parser, focused regression coverage, and lifecycle documentation after the required gates.

## 29. Operator Execution Checklist

### Before AI Execution

- Approve this exact file allowlist and the `ACCEPTANCE_HEADLESS` name/default.
- Approve focused PHPUnit execution and isolated local headless Chrome smoke using only synthetic in-memory pages with network routes blocked.
- Confirm work may continue on `main` while preserving the pre-existing untracked `config/core.php`.

### After AI Execution

- Review the scoped diff and confirm `config/core.php` is unchanged.
- Confirm the committed default remains headless and headed mode was not launched during validation.
- Review focused test and local Chrome smoke results before authorizing the Run Report/final report.

## 30. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md` and the documentation-maintenance section. Distinguish parser/config tests from actual local Chrome smoke evidence and report the preserved pre-existing untracked file.

## 31. Review Checklist

Review single-source wiring, boolean normalization, Laravel config-cache compatibility, local-only browser safety, cleanup, exact scope, and unchanged default behavior.

## 32. Rollback / Safety Notes

Rollback removes the new test helper/test and reverts only the listed tracked edits. Do not delete or restore the user's untracked `config/core.php`; destructive Git/file commands require separate exact authorization.

## 33. Stop Conditions

Stop for missing execution approval, changed/unexpected branch, overlap with `config/core.php`, any required dependency/autoload edit, external target/network need, inability to keep browser fixtures synthetic/local, browser startup failure, unclear out-of-scope repair, or any owner/Pack conflict.

## 34. Open Questions

No open questions. The operator explicitly pre-approved execution, the resulting report, acceptance, and commit on 2026-10-11. Focused validation passed; the commit containing this Pack and its accepted Run is authorized.
