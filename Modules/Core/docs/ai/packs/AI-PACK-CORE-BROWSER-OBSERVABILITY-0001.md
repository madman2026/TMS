# AI-PACK-CORE-BROWSER-OBSERVABILITY-0001 — Browser Interaction and Accessibility Observations

Current Roadmap Notice (2026-10-06): `docs/project/ai/decisions/TMS-DECISION-0006-remove-cancelled-fixture-and-safeguard-packs.md` subsequently cancels/removes project 0006 and deletes the already superseded Core 0002/project 0005 proposals. References and next-Pack directions below are historical; Drafts 0007–0008 require normalization against accepted source. This notice changes no accepted Core behavior or validation result.

Status: `accepted — operator accepted 2026-10-06; current headless smoke passed`

Generated: `2026-10-05`

Decision: `docs/project/ai/decisions/TMS-DECISION-0004-cli-only-staged-generic-acceptance-roadmap.md`

Depends On: accepted `AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004` and its accepted Run 001

Normalized: `2026-10-05`, against HEAD `bbedbbf0e86e94e2497e8dd80c36493a84fc0b8a` on `main`.

Execution Approval: operator approved the normalized contract, isolated local Chrome tests, and continuation on main with pre-existing documentation changes on 2026-10-05. Final acceptance and commit remain separate gates.

Post-execution Approval: operator approved the post-execution gate and issuance of the chat-facing Agent Final Report on 2026-10-06. Classification: current-execution approval; no architecture or Canonical change. This approval is not final Pack acceptance or commit authorization.

Execution Gate: satisfied by the operator's approval after normalization on 2026-10-05. Post-execution gate approved on 2026-10-06. No dependency or browser installation is authorized. Pre-existing roadmap documentation changes were retained on main.

Final Acceptance: operator explicitly accepted the executed Pack on 2026-10-06 and requested next actions. Accepted Run: `Modules/Core/docs/ai/runs/AI-PACK-CORE-BROWSER-OBSERVABILITY-0001-run-001.md`. Review: `Modules/Core/docs/ai/reviews/REVIEW-CORE-BROWSER-OBSERVABILITY-0001-acceptance-001.md`. No commit was authorized at the acceptance step.

Commit Approval — 2026-10-06: operator separately instructed “کامیت کن”. Authorization covers the accepted Core implementation, finalized Run/Review/indexes, and the related approved roadmap/proposal documentation in the current working tree. No new Pack execution or target connection is authorized. This document is finalized before that authorized commit; resolve the resulting revision through Git history for this Pack/Run.

Current Evidence Notice — 2026-10-06: acceptance-time source inspection finds `withHeadless(true)` in the smoke fixture; the six current-source headless Chrome tests passed again (94 assertions, exit 0). The intervening audit's headed setup is not the current source and has no claimed validation. No source edit was made by acceptance maintenance. Installed Chromium launch flags limit browser security-policy conclusions; see the Run/Review.

Roadmap Supersession Notice — 2026-10-06: accepted project Decision 0005 cancels standalone Core Evidence Safety 0002/project Ephemeral Context 0005 and supersedes their future-prerequisite wording below. Historical implementation/validation text is retained. Testing/staging-only synthetic-data constraints and minimum output safeguards govern future project Packs 0006–0008; neither cancelled Pack is an execution prerequisite.

## 1. Task ID

`AI-PACK-CORE-BROWSER-OBSERVABILITY-0001`

## 2. Task Title

Add target-neutral Browser interaction and accessibility observation capabilities to Core.

## 3. Goal

Provide reusable typed Core services that let target Apps observe and assert browser-native layout, visibility, clipping, focus, keyboard behavior, accessible semantics, media preferences, responsive viewport behavior, and touch interaction without embedding target selectors or expectations in Core.

## 4. Context

Installed Playwright PHP `v1.5.0` exposes evaluation, keyboard/mouse, locator focus/bounding-box/tap, viewport, mobile/touch context options, and media emulation. Current TestContext exposes the raw page and owns idempotent cleanup. AcceptanceRunner constructs it directly. Preserve its constructor and Runner behavior; add a readonly page-bound BrowserProbe accessor.

The accepted predecessor added descriptive ScenarioMetadata and EvidenceMode only; runtime evidence enforcement remains outside this Pack. No Core-local Canonical or Decision exists. Current source supersedes historical bootstrap observations.

Environment inspection found PHP `8.4.25`, Node Playwright `1.58.2`, and system Chrome. Node Playwright expects bundled Chromium revision `1208`; the inspected cache has revision `1243`, not `1208`. The new smoke tests explicitly use the installed PHP client's `chromium()->withChannel('chrome')->withHeadless(true)->launch()` with temporary contexts and NullLogger. They do not attach to an existing browser or personal profile. Approved execution validated this channel through six local smoke tests; other engines remain unverified.

## 5. Related Release / Phase

Not applicable; pre-Release Core capability Pack.

## 6. Related Epic / Feature / Story

Reusable human-acceptance automation — browser-native evidence replacement for automatable manual checks.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0002-generic-code-based-acceptance-apps.md`
- `docs/project/ai/decisions/TMS-DECISION-0003-cli-first-acceptance-execution.md`
- `docs/project/ai/decisions/TMS-DECISION-0004-cli-only-staged-generic-acceptance-roadmap.md`
- `docs/project/ai/runs/AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004-run-001.md` — accepted
- installed Playwright PHP 1.5 Page/Locator/input interfaces and browser/context builders

## 8. Files to Create

Exact Core implementation/test allowlist:

- `Modules/Core/app/Contracts/BrowserProbe.php`
- `Modules/Core/app/Data/BrowserObservation.php`
- `Modules/Core/app/Enums/BrowserObservationStatus.php`
- `Modules/Core/app/Exceptions/BrowserProbeException.php`
- `Modules/Core/app/Services/PlaywrightBrowserProbe.php`
- `Modules/Core/tests/Unit/BrowserObservationTest.php`
- `Modules/Core/tests/Unit/PlaywrightBrowserProbeTest.php`
- `Modules/Core/tests/Unit/TestContextBrowserProbeTest.php`
- `Modules/Core/tests/Feature/PlaywrightBrowserProbeSmokeTest.php`

Deferred lifecycle record, only after the reporting gate permits creation:

- `Modules/Core/docs/ai/runs/AI-PACK-CORE-BROWSER-OBSERVABILITY-0001-run-001.md`

## 9. Files to Edit

- `Modules/Core/app/Contracts/TestContext.php`
- `Modules/Core/docs/ai/packs/AI-PACK-CORE-BROWSER-OBSERVABILITY-0001.md`
- `Modules/Core/docs/ai/packs/PACKS-INDEX.md`
- `Modules/Core/docs/ai/runs/RUNS-INDEX.md`

TestContext adds only readonly `BrowserProbe $browserProbe`, initialized with the existing page and timeout. No provider binding or constructor change is required. No other source/test file is authorized for editing.

## 10. Files to Read / Reference

- `AGENTS.md`, `docs/ai/start/START-HERE.md`, `docs/ai/start/CONTEXT-MAP.md`
- `Modules/Core/AGENTS.md`, `Modules/Core/docs/ai/README.md`, `Modules/Core/docs/ai/manifest.yaml`, `Modules/Core/docs/ai/GOVERNANCE-PROFILE.md`, `docs/project/ai/TMS-AI-PROFILE.md`
- `docs/ai/rules/SCOPE-CONTROL-RULES.md`, `docs/ai/rules/AI-EXECUTION-RULES.md`, `docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md`, `docs/ai/rules/GIT-AND-COMMIT-RULES.md`, `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`, `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md`, `docs/ai/rules/COMMENTING-RULES.md`, `docs/ai/rules/REPORTING-RULES.md`, `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`
- For Pack/report maintenance: `docs/ai/rules/AI-PACK-GENERATION-RULES.md`, `docs/ai/templates/AI-PACK-TEMPLATE.md`, `docs/ai/templates/AGENT-FINAL-REPORT-TEMPLATE.md`, `docs/ai/templates/RUN-REPORT-TEMPLATE.md`
- `Modules/Core/docs/ai/packs/PACKS-INDEX.md`, `Modules/Core/docs/ai/runs/RUNS-INDEX.md`, `Modules/Core/docs/ai/canonical/CANONICAL-INDEX.md`, `Modules/Core/docs/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/references/REFERENCES-INDEX.md`, `docs/project/references/SOURCE-DOCS-INDEX.md`, `docs/project/references/TMS-SOURCE-STRUCTURE-SUMMARY.md`
- Exact Decision/Run paths in section 7 and `docs/project/ai/packs/AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004.md`
- `Modules/Core/app/Services/AcceptanceRunner.php`, `Modules/Core/app/Services/PlaywrightBrowserFactory.php`, `Modules/Core/app/Contracts/TestContext.php`, `Modules/Core/app/Contracts/BrowserFactory.php`, `Modules/Core/app/Contracts/StepResult.php`, `Modules/Core/app/Exceptions/AcceptanceExecutionException.php`
- `Modules/Core/app/Data/RunOptions.php`, `Modules/Core/app/Data/ScenarioMetadata.php`, `Modules/Core/app/Enums/EvidenceMode.php`, `Modules/Core/app/Traits/Assertion.php`, `Modules/Core/app/Traits/HasStep.php`
- `Modules/Core/tests/Unit/AcceptanceRunnerTest.php`, `Modules/Core/tests/Unit/ScenarioMetadataTest.php`, `Modules/Core/tests/Feature/PlaywrightAcceptanceSmokeTest.php`, `Modules/Core/app/Providers/CoreServiceProvider.php`, `composer.json`, `composer.lock`, `phpunit.xml`, `.gitignore`
- `vendor/playwright-php/playwright/src/Page/PageInterface.php`, `vendor/playwright-php/playwright/src/Locator/LocatorInterface.php`, `vendor/playwright-php/playwright/src/Locator/Locator.php`, `vendor/playwright-php/playwright/src/Input/KeyboardInterface.php`, `vendor/playwright-php/playwright/src/Input/MouseInterface.php`
- `vendor/playwright-php/playwright/src/Browser/BrowserContextInterface.php`, `vendor/playwright-php/playwright/src/Browser/BrowserContextBuilder.php`, `vendor/playwright-php/playwright/src/Browser/BrowserBuilder.php`, `vendor/playwright-php/playwright/src/PlaywrightFactory.php`, `vendor/playwright-php/playwright/src/PlaywrightClient.php`, `vendor/playwright-php/playwright/src/Configuration/PlaywrightConfig.php`, `vendor/playwright-php/playwright/src/Transport/ServerFinder.php`
- `node_modules/playwright/package.json`, `node_modules/playwright-core/browsers.json`

Installed dependency source is read-only evidence. Do not modify vendor/node_modules.

## 11. Configuration / Settings Requirements

No production configuration, environment file, Admin Setting, secret, queue, cache, or log-channel changes. Never read `.env` or cached application configuration. New tests extend PHPUnit TestCase without Laravel/database boot, using existing autoloading. Smoke setup uses an explicit existing system Chrome channel, timeout 5000 ms, NullLogger, fresh contexts, and synthetic `setContent` only. Block HTTP/HTTPS requests; no navigation, external fixture, screenshot, video, trace, or storage persistence.

### Operator Setup Notes

Approve isolated headless system Chrome smoke execution. If that channel cannot launch through the installed library, stop and report. Do not install/update a browser or dependency, change the factory, or silently substitute another channel.

## 12. Do Not Change

Target Apps/repositories, root application/persistence/commands, scenario catalog, evidence retention/capture enforcement, secrets/authentication, fixtures, database/schema, API/UI, queue/scheduler, dependencies/lockfiles, `.env`, configuration/providers, Runner/browser factory, existing assertion traits/tests, vendor/node_modules, generated assets, or browser installations. Preserve all pre-existing roadmap/Decision/index changes; no staging or commit is authorized.

## 13. Clarification Questions Before Implementation

The Draft questions are resolved as the proposed section 17 contract, pending approval:

1. Immutable operation-specific flat schemas, explicit available/unavailable/unsupported status, and a throwing requireAvailable guard.
2. Contrast is explicitly unsupported; incomplete compositing/background data cannot establish a correct contrast ratio. No accessibility dependency.
3. Deterministic unit/security/error tests plus real local Chrome smoke tests for browser behavior. Firefox, WebKit, bundled Chromium, real assistive technology, and human perception remain unverified.

No other deferred design question remains; execution authorization for the normalized proposal was received on 2026-10-05.

## 14. Multilingual / Translation Requirements

No UI/API/translation change or arbitrary locale string output. Machine tokens remain language-neutral. Synthetic LTR/RTL fixtures may verify geometry, without returning target wording.

## 15. UI / Admin UI Requirements

No UI changes required.

## 16. Operator Documentation Requirements

No operator documentation update required. This technical Core extension creates no new operator-facing command/API/UI. The contract is documented in this Pack and source PHPDoc; target integration/operator workflows remain future scope.

## 17. Implementation Rules

- Target Apps select locators and expected outcomes; Core only performs generic interactions/observations.
- Observations return allowlisted scalar, enum, bounded numeric, boolean, or normalized structural data—not raw DOM/HTML.
- Unavailable evidence is explicit and never converted to pass.
- Keyboard/focus interactions use native Playwright operations where available.
- Geometry uses locator/page APIs and bounded evaluation only when required.
- ARIA results must be normalized or bounded; raw target content must not enter logs/results by default.

### Exact observation contract

BrowserObservationStatus is a string-backed enum: AVAILABLE = available, UNAVAILABLE = unavailable, UNSUPPORTED = unsupported. BrowserObservation is final readonly with validated string operation, enum status, flat array data, and nullable string errorCode. Use a private constructor and validating factories available/unavailable/unsupported. `toArray()` returns only operation/status/data/errorCode. `requireAvailable(): array` throws BrowserProbeException for either failure status. No passed flag or implicit target assertion.

Available data must exactly match the complete schema below, with null errorCode. Reject missing/extra keys, nested arrays/objects/resources, arbitrary strings, wrong types, unknown tokens, NaN/Infinity, and out-of-range numbers. Copy scalar values out of caller references. Failure data is empty, with an allowlisted status-compatible error code. Invalid construction throws a fixed safe exception, never echoes rejected input.

PlaywrightBrowserProbe accepts PageInterface and a positive timeout; an invalid constructor timeout throws BrowserProbeException with browser_probe_invalid_argument before any page call. BrowserProbe exposes the following methods; all return BrowserObservation. Locators are caller-selected and must belong to that page; document caller responsibility. No selector, caller-supplied expression, text-entry input, arbitrary media options, or target expectation argument.

| Method / operation | Exact available data | Behavior / boundary |
|---|---|---|
| visibility(LocatorInterface) / visibility | visible: bool; enabled: bool | Native locator observations; hidden is an available false value. No match is unavailable. No occlusion claim. |
| layout(LocatorInterface) / layout | x, y, width, height, visibleFraction: finite numeric; clipped, overflowX, overflowY: bool | Native boundingBox plus fixed evaluator for viewport/ancestor CSS overflow intersection and own scroll overflow; CSS pixels, viewport-relative coordinates. Null/hidden box is unavailable. At most 64 ancestors. Subframes/shadow trees, CSS zoom/transforms, legacy clip/clip-path/mask, paint containment, or exceeded traversal bound are unsupported; no paint/human-perception claim. |
| focusState(LocatorInterface), focus(LocatorInterface) / focus | focused: bool | Compare target to document activeElement; focus uses native locator focus then observes actual state. False remains false. Subframe/shadow focus requiring composed-tree analysis is unsupported. |
| pressKey(string) / keyboard | dispatched: bool | Native page keyboard press, only Tab, Shift+Tab, Enter, Space, Escape, ArrowUp/Down/Left/Right, Home, End, PageUp, PageDown. Caller verifies resulting focus/behavior. No text entry or focus-order list. |
| scroll(float, float) / scroll | dispatched: bool | Native page mouse wheel; finite deltas in [-10000,10000]. Caller observes post-state. |
| semantics(LocatorInterface) / semantics | role: approved token or null; checked: bool/mixed/null; expanded, selected: bool/null; disabled, hasAriaLabel, hasLabelledBy, hasNativeLabel: bool | Fixed evaluator returns structural role/state/label-source presence only; no label/name/text/field value crosses into PHP. No computed accessible-name algorithm, ARIA snapshot/tree, audit, or screen-reader claim. |
| media(), emulateMedia(string, string) / media | colorScheme: dark/light/no-preference; reducedMotion: reduce/no-preference; forcedColors: bool | Fixed matchMedia observations; native emulateMedia then re-observe. Accept only the listed scheme/motion tokens plus no-override for reset. Missing media primitive is unsupported. |
| viewport(), resizeViewport(int, int) / viewport | width, height: int; deviceScaleFactor: finite numeric | Observe actual inner dimensions/devicePixelRatio; native setViewportSize for resize. Dimensions 1..16384; scale >0 and <=16. Core chooses no device/viewport. |
| tap(LocatorInterface) / touch | dispatched: bool | Fixed numeric touch-support check, native locator tap with <=5000 ms timeout. Zero touch points is unsupported; never fall back to click. Tests explicitly create hasTouch contexts. |
| contrast(LocatorInterface) / contrast | none | Always unsupported; do not read page/locator. No misleading baseline ratio. |

Approved roles: button, link, textbox, searchbox, checkbox, radio, combobox, listbox, option, heading, img, dialog, list, listitem, table, row, cell, columnheader, rowheader, region, alert, status, navigation, main, banner, contentinfo, form, search, progressbar, slider, spinbutton, switch, tab, tablist, tabpanel, menu, menuitem, separator, none, presentation. Unknown/absent role maps to null, not a guess. Implicit mapping is limited to standard button, linked anchor, text/search input, textarea, checkbox/radio/range/number input, select/option, heading, img, and list/listitem. Document exact mapping. Presence flags do not prove a computed accessible name.

Coordinates have absolute value <=1000000; dimensions 0..1000000; visibleFraction 0..1. Reject overbound responses instead of clamping them into evidence. A valid zero intersection is distinct from unavailable geometry. Never read text to measure overflow.

Preflight page open and exactly one locator match. No match and multiple matches have distinct codes. Use the existing timeout for evaluation/focus and <=5000 ms where native options accept timeout. No global timeout mutation, retry loop, navigation, subscription, or capture. Successful dispatch records only an interaction; Apps must separately assert post-state.

## 18. Architecture Constraints

- Core remains target-neutral and depends only on installed Playwright contracts.
- No selector, URL, product role, permission, field name, expected pixel value, or target workflow belongs in Core.
- Do not claim real assistive-technology or human-perception equivalence from browser automation.
- A dependency addition is protected and requires explicit normalized scope.
- TestContext initializes its probe for the same page; no DI binding, constructor change, Runner edit, or persistence integration.
- EvidenceMode stays descriptive. This Pack does not implement or claim sensitive no-capture enforcement.
- Follow actual structure/conventions; no duplicated decision logic, unrelated refactoring/schema changes, or later roadmap capability.

## 19. Validation Rules

Runtime validation matrix: isolated local system Chrome headless through installed Playwright PHP only. Firefox/WebKit/bundled Chromium remain unexecuted and unverified. Mocks prove contracts, not engine behavior.

Verify every schema/bound, native arguments, false/negative states, guard behavior, immutable copying, raw-value/exception omission, and cleanup. Browser tests assert actual post-state for focus/keyboard/wheel/tap and concrete geometry/media/viewport/semantics values. Startup failure is a blocker; required smoke tests must not become skips or be counted as passing. No external target or network fixture.

## 20. Security Rules

- Probes must not return raw field values, HTML, storage, clipboard, network payloads, console payloads, cookies, headers, tokens, or credentials.
- Sensitive surfaces must not be probed until Pack Core Evidence Safety 0002 is accepted.
- Use synthetic local pages only in tests.
- Normalize inside fixed browser evaluation; no caller JavaScript or raw DOM/text/label content returned to PHP. Do not call ariaSnapshot, textContent, innerHTML, inputValue, storage, clipboard, or capture APIs for probe output.
- Use NullLogger for smoke clients; no new log events, rejected arguments, raw results, selector/URL diagnostics, or exception chains. Synthetic secret-like strings must be omitted from outputs and errors.

## 21. Error Handling / Logging / Traceability Requirements

Impact: internal observation/exception normalization and sensitive omission. No API/UI error contract, durable status transition, retry engine, log/audit event, or trace identifier change.

BrowserProbeException extends RuntimeException and exposes only allowlisted errorCode, retryable=false, and optionally an approved operation token. Fixed message: `Browser probe could not provide the requested observation.` No rejected value, selector, raw library message, or previous exception. Invalid DTO construction and requireAvailable throw it; the existing HasStep/Runner safe failure boundary may handle it without modification.

Probe methods return explicit unavailable/unsupported observations and normalize library failures without retaining Throwable. Never classify by matching raw exception text. Exact codes:

| Code | Status / trigger | Classification / action |
|---|---|---|
| browser_probe_invalid_argument | unavailable; invalid key/media/viewport/delta/timeout | Permanent until caller fixes input; reject before mutation. |
| browser_probe_page_closed | unavailable; closed page | Permanent for that page; fresh context needed. |
| browser_probe_locator_missing | unavailable; zero matches | Current-state evidence absent; fix locator/state. |
| browser_probe_locator_ambiguous | unavailable; multiple matches | Permanent until selection is unambiguous. |
| browser_probe_geometry_unavailable | unavailable; null/hidden box | Current-state geometry absent; never pass. |
| browser_probe_invalid_payload | unavailable; malformed/unsafe/overbound response | Permanent for response; fix adapter/fixture. Invalid DTO construction throws with this code. |
| browser_probe_operation_failed | unavailable; unexpected library failure/race/timeout | Cause omitted; diagnose with synthetic fixtures. Do not retry possibly dispatched actions. |
| browser_probe_capability_unsupported | unsupported; documented layout/focus/media boundary | Permanent capability limitation; no fallback/pass. |
| browser_probe_touch_unsupported | unsupported; no touch capability | Permanent for context; caller selects a touch context separately. |
| browser_probe_contrast_unsupported | unsupported; contrast request | Permanent capability limitation; separate design/dependency approval needed. |

All are non-retryable under this Pack. No HTTP status, Admin permission/action workflow, log/audit event, or durable owner-state mutation. Error results retain fixed operation tokens/codes for local traceability; no Run/Batch identifier is introduced. Test exact classification/guard/no-dispatch behavior and omitted exception cause. No raw Playwright/DOM value is logged.

## 22. Data Model / Migration / Relationship Requirements

No data model, migration, relationship, snapshot, index, backfill, or retention changes expected.

## 23. Commenting Requirements

Document non-obvious normalization, coordinate, focus-order, accessibility, and browser-compatibility constraints. Do not restate Playwright calls.

## 24. Testing Requirements

Unit tests extend PHPUnit TestCase and mock installed interfaces. Assert exact outputs/types/bounds, correct native arguments, no calls on invalid input, false values retained, and safe exception properties/message with no cause. No Laravel/database boot.

Browser tests use fresh Chrome contexts, synthetic HTML, aborted HTTP/HTTPS routes, and finally cleanup for context/browser/client even on assertion failure. Verify actual interaction post-state; use bounded assertions/polling for asynchronous wheel effects, not arbitrary sleeps. No capture or persistent fixtures.

## 25. Acceptance Checklist

- [x] normalized capability/file/error/test matrix approved separately;
- [x] target-neutral contracts and safe observation bounds implemented;
- [x] unavailable evidence cannot pass;
- [x] no raw target/sensitive content escapes through the tested probe schemas/errors;
- [x] deterministic local tests cover positive and negative behavior;
- [x] no downstream evidence, secret, fixture, catalog, or batch scope leaks in.
- [x] TestContext constructor and existing cleanup/Runner behavior preserved;
- [x] Chrome smoke evidence distinguished from mocks and other unverified engines;
- [x] contrast/full accessible names/audits/assistive and perception boundaries explicitly reported.

## 26. Tests to Add

| Allowed file / named test groups | Behavior and main assertions |
|---|---|
| BrowserObservationTest: test_each_available_schema_is_validated; test_invalid_payloads_are_rejected_safely; test_failure_observations_cannot_be_required_as_available; test_caller_references_cannot_change_observation | Providers cover every operation, missing/extra/unsafe keys, wrong types/tokens/codes/status, nested content, bounds, NaN/Infinity, exact serialization, reference copying, safe error code/message and absent cause. |
| PlaywrightBrowserProbeTest: test_valid_observations_are_normalized; test_native_actions_use_approved_arguments; test_invalid_arguments_do_not_dispatch; test_missing_ambiguous_closed_and_library_failures_are_explicit; test_unsupported_capabilities_do_not_fallback; test_raw_content_cannot_escape | Interface mocks verify exact observations/false values/native arguments, no mutation on rejection, zero/multiple matches, closed page/null geometry, layout/focus/touch/contrast limits, malformed responses, and synthetic sensitive exception omission. No selector getter/text/HTML/snapshot/storage/log/capture calls. |
| TestContextBrowserProbeTest: test_context_creates_one_probe_for_its_page; test_context_cleanup_remains_idempotent | Existing timeouts/newPage/constructor behavior, readonly exposure, observation against that same page, close exactly once. |
| PlaywrightBrowserProbeSmokeTest: test_visibility_geometry_and_clipping_on_local_content; test_native_focus_tab_keyboard_and_scroll_observe_post_state; test_bounded_semantics_omit_local_content; test_media_and_responsive_viewport_are_observed; test_touch_and_unavailable_states_are_explicit | Real Chrome fixtures verify hidden/missing/ambiguous, exact rectangle/clipping/overflow, Tab/Shift+Tab focus, Enter/Space and wheel post-state, role/state/label flags with synthetic values omitted, media and responsive CSS, tap in hasTouch context/no-touch unsupported, closed page/null geometry fail-closed behavior and cleanup. |

Provider variants may split groups into additional methods inside these same files. No helper/fixture source file is needed.

Execution also added `test_geometry_and_focus_boundaries_are_unsupported_in_the_browser` inside the listed smoke file to verify zoom, containment, legacy clip, clip-path/mask, deep ancestry, shadow roots, and subframes against real local browser behavior. These are fail-closed boundaries of the bounded geometry/focus algorithm, not additional capabilities.

## 27. Tests to Run

From repository root, authorized only after normalized execution approval:

```powershell
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/BrowserObservationTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/PlaywrightBrowserProbeTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/TestContextBrowserProbeTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/AcceptanceRunnerTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Unit/ScenarioMetadataTest.php
php vendor/bin/phpunit --do-not-cache-result Modules/Core/tests/Feature/PlaywrightBrowserProbeSmokeTest.php
php vendor/bin/phpunit --list-tests Modules/Core/tests/Feature/PlaywrightAcceptanceSmokeTest.php
```

Classification: validation-or-test; new smoke also launches bounded browser/Node child processes. No persistent server/worker. Existing Acceptance smoke is discovery-only because the bundled revision is absent; do not execute/change it or claim its browser tests passed.

Run `php -l <path>` on each new PHP file and edited TestContext using exact section 8/9 paths. Run `git diff --check`, `git diff --name-only`, `git status --short --branch`; inspect untracked files explicitly because diff omits them. No configured static-analysis command exists in the inspected project. No dependency install, full suite, formatter mutation, artisan/composer script, cache/database/server, or external network command is authorized. Use the smallest in-scope test repair; startup failure remains a reported blocker.

### Observed execution validation — 2026-10-05

All prescribed execution commands completed with exit 0. Final results:

| Command target | Tests | Assertions | Evidence |
|---|---|---|---|
| BrowserObservationTest | 248 | 1236 | Deterministic contract/security unit tests |
| PlaywrightBrowserProbeTest | 97 | 1166 | Installed-interface mocks; final run after geometry boundary review |
| TestContextBrowserProbeTest | 2 | 18 | Context/probe/cleanup unit tests |
| AcceptanceRunnerTest | 16 | 64 | Existing Runner regression; mocked browser |
| ScenarioMetadataTest | 29 | 251 | Existing metadata regression |
| PlaywrightBrowserProbeSmokeTest | 6 | 94 | Actual headless system Chrome, synthetic local pages, finally cleanup |

Total: 398 tests / 2829 assertions. No test failed. The initial five-test Chrome run passed before the additional geometry-boundary review; the final six-test run passed after that review. All 10 new/edited PHP files passed php -l. Existing Acceptance smoke loaded/listed two methods only; it was not browser-executed. No Firefox/WebKit/bundled Chromium, full suite, external target, database, or accessibility/human-perception validation was performed. Scoped whitespace/path checks passed.

## 28. Expected Output

A reusable Core BrowserProbe/value/status/exception/service, TestContext accessor and focused tests; explicit supported/unsupported matrix, with no target integration. Update Pack/index lifecycle; create persistent Run/Run index only after the reporting gate.

## 29. Operator Execution Checklist

### Before AI Execution

- Approve this normalized matrix, schemas/errors, exact file allowlist, and unsupported contrast/name/assistive boundaries.
- Approve isolated headless system Chrome launch for synthetic local smoke tests; no dependency or browser installation.
- Confirm sensitive target surfaces remain prohibited until Evidence Safety 0002 acceptance.

### After AI Execution

- Verify Core output contains observations only and no target content/selectors.
- Review actual Chrome evidence separately from mocks and unsupported/unverified engines.

## 30. Agent Final Report

Follow reporting rules and Agent Final Report template after the post-execution approval gate. Distinguish unit/mock evidence, real Chrome smoke, discovery-only tests, and unsupported/manual boundaries. Include documentation maintenance and preserved pre-existing changes. Do not accept/create a persistent Run before the reporting gate.

## 31. Review Checklist

Review target neutrality, Playwright 1.5 compatibility, safe output bounds, unsupported behavior, browser cleanup, and test strength.

## 32. Rollback / Safety Notes

Rollback removes only new probe source/tests, the TestContext accessor, and this execution's lifecycle changes while preserving pre-existing documentation. No constructor/provider binding, database, or target state change exists. Destructive Git/file commands require separate approval for exact targets.

## 33. Stop Conditions

Stop before implementation if normalized approval/working-tree gate is absent. Stop during implementation for target selector/workflow, protected dependency/browser install, unsafe raw data need, missing required primitive, Chrome channel startup failure, sensitive probing, unauthorized file/owner boundary, conflict, unclear in-scope test repair, or predecessor Pack/Run no longer accepted.

## 34. Open Questions

No deferred implementation design fields remain. Operator approved this normalized proposal, local Chrome validation, and continuation on main with existing documentation changes on 2026-10-05, then approved the post-execution gate and Agent Final Report on 2026-10-06. Final acceptance and separate commit authorization were granted on 2026-10-06. Next Pack execution approval remains separate.

Required Follow-up Updates: persistent accepted Run, acceptance Review, and Core lifecycle indexes are complete and finalized for the operator-authorized commit. The next project Pack 0006 proposal is normalized under Decision 0005 and awaits separate execution approval. Unsupported contrast/full accessibility and verification on other engines require future approved scope. Chrome startup and scoped behavior were validated locally, including a current-source smoke repeat on 2026-10-06; no external setup/dependency update is authorized. This Pack is accepted; resolve its committed revision through Git history after the authorized commit.
