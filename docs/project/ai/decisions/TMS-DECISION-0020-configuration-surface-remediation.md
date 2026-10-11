# TMS-DECISION-0020 — Complete and verify the repository configuration surface

Status: Accepted
Scope: Project / cross-owner configuration remediation
Source: Operator instruction
Date: 2026-10-11
Review At: Completed in Remediation 0005 run 001
Blocking: No — Remediation 0005 accepted
Closure Condition: Remediation 0005 proves every first-party configuration reference resolves and every active first-party `env()` key is represented safely in `.env.example`
Next Review At: Configuration-contract change or regression

## Type

6. Change Request / Remediation Trigger

## Decision

Create a project-owned remediation that inventories configuration references across the root application, Core, and DK; creates a versioned first-party configuration file only when a referenced namespace is genuinely unresolved; and makes `.env.example` a complete, safe manifest of active first-party `env()` references and their effective defaults.

Configuration namespaces supplied at runtime by an installed framework or package service provider are resolved dependencies, not missing first-party files. They must be verified against the installed dependency and must not be copied into the repository merely because no same-named file exists under root `config/`.

The AI Agent must not read or edit `.env`. The repository profile's environment-file safety rule remains binding. Remediation 0005 must document the exact safe values/placeholders that the operator may manually copy into `.env` after review.

The operator subsequently approved completing the existing root `config/core.php`, keeping it synchronized with `Modules/Core/config/config.php`, and declaring the four Core browser variables in `.env.example`. This exact Core slice is approved even though the broader environment-manifest remediation remains separately gated.

The operator then authorized adding the remaining declarations to `.env` and `.env.example`. The higher-priority repository prohibition on AI access to `.env` remains binding, so the AI implementation is limited to the complete `.env.example` manifest plus an operator-only manual synchronization checklist.

Only active executable `env()` calls count toward manifest completeness. Commented examples do not create an environment-variable contract. Adding a declaration must preserve the existing fallback semantics: secrets use empty/commented placeholders, computed defaults must not be replaced by a misleading literal, and no real credential, token, URL, identifier, or runtime value may be recorded.

## Reason

The operator requested a complete correction for configuration keys used around the repository without an owning configuration file and for related environment variables missing from `.env` and `.env.example`.

Generation-time source inspection found these facts:

- first-party code references the `acceptance`, `app`, `core`, `database`, `dk`, `modules`, `queue`, and `view` configuration roots;
- `acceptance`, `app`, `database`, and `queue` are supplied by root configuration files;
- `core` and `dk` are supplied by their module configuration files and service providers;
- `modules` is merged by the installed `nwidart/laravel-modules` service provider;
- `view` is supplied by the installed Laravel framework;
- therefore no unresolved configuration root is currently proven;
- 138 distinct active first-party `env()` keys were found in versioned configuration code, of which 81 are not declared in `.env.example`;
- no active `env()` call was found outside configuration files in the inspected first-party source;
- a pre-existing untracked `config/core.php` originated outside this remediation and was explicitly adopted only after the operator approved its exact completion scope.

## Impact

- Project configuration and environment-manifest consistency gain automated regression coverage.
- Root and module Core config expose the same browser, headless, timeout, and slow-motion keys through the same four environment variables and defaults.
- `.env.example` will be expanded without real secrets or production values.
- Genuine unresolved first-party configuration roots found during approved execution require a Pack amendment with exact file paths before creation; the current evidence does not authorize speculative `config/modules.php` or `config/view.php` copies.
- `.env` synchronization remains an operator-only step.
- No API, UI, database, queue execution, external integration, dependency, or runtime target behavior changes are approved.
- No Canonical update is required.

Configuration / Settings Impact: Yes
Error Handling Impact: No
API Error Contract Impact: Not applicable
UI / Admin UI Error Impact: Not applicable
Error Code Impact: Not applicable
Exception Handling Impact: No
External Error Normalization Impact: Not applicable
Retry / Failure Classification Impact: Not applicable
Status Model Impact: Not applicable
Logging Impact: No
Traceability Impact: No
Sensitive Data Impact: Yes — examples and reports must omit real values
Testing Impact: Yes
Documentation Impact: Yes

## Files to Update

- `.env.example`
- `config/core.php`
- `Modules/Core/config/config.php`
- `tests/Feature/ConfigurationSurfaceTest.php`
- project Decision, Change, Remediation, Run, and related indexes required by lifecycle rules

`.env` is an operator-only manual update and must not be read or edited by the AI Agent.

## Required change request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-CONFIGURATION-SURFACE-0008.md`

## future-work

- The root and module Core config are intentionally synchronized by Remediation 0005; changing the Core provider's recursive merge precedence remains outside this decision.
- Any future first-party config or `env()` reference must satisfy the regression contract added by Remediation 0005.

## Review / Resolution

```text
Reviewed In: operator instruction on 2026-10-11
Review Result: Remediation 0005 implementation and validation accepted
Resolution: resolved
Next Review At: Configuration-contract change or regression
Resolution Notes: the operator accepted the completed Core/configuration-manifest remediation and separately authorized commit on 2026-10-11; `.env` remains an operator-only manual synchronization surface
```

## Notes

This decision does not authorize reading or editing `.env`, publishing dependency configuration, changing dependency versions, or converting static configuration outside the exact approved Core browser settings into new environment variables.
