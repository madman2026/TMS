# TMS SOURCE STRUCTURE SUMMARY

Status: current bootstrap observation — not Canonical
Observed: 2026-09-22
Cleanup baseline: `a6e1574`
Pre-execution HEAD: `2fbb0454967f15cfaa7b06f4cd7a6b853cde5110`

## Repository shape

- TMS is a Laravel 12 application on PHP 8.2 or later.
- The module registry contains only Core and marks it enabled.
- Core is the only retained module directory and the only activated independent documentation owner.
- The root application contains contracts that consume Core types; inspected Core contracts also consume root application models and enums. This is a cross-boundary dependency observation, not an approved architecture change.

## Accepted cleanup state

The operator accepted commit `a6e1574` as the cleanup baseline. Auth and Ecommerce were removed before this bootstrap. They are absent from active module discovery and must not be recreated as source trees, examples, active owners, or deferred owners by this Pack.

The current pre-execution HEAD adds only dependency-lock updates after that baseline. Those lockfiles are operator-owned, excluded from this Pack, and must remain byte-for-byte unchanged during documentation work.

## Dependency and tooling context

- Runtime context includes Laravel, Sanctum, and the Laravel modules package.
- Documentation/test tooling includes Scribe, PHPUnit, and Playwright-related packages.
- Only the default example Feature and Unit tests were present in the inspected test inputs.

## Implementation observations

These items are observations only. They are not Canonical decisions and are not repaired by this Pack.

- Root-to-Core and Core-to-root references create an ownership boundary that future implementation Packs should evaluate before moving contracts.
- `Modules/Core/composer.json` retains scaffold-like package metadata and an empty description.
- `Modules/Core/module.json` also has an empty description.
- `Modules/Core/app/Contracts/DKAPI.php` is a concrete external HTTP client placed under a Contracts namespace and exposes upstream failure text through exceptions.
- `Modules/Core/app/Contracts/TestContext.php` combines request-derived configuration, root application models/enums, and browser construction in one class.
- `app/Contracts/BaseService.php` contains formatting and unused-import observations; this bootstrap does not alter source.
- The inspected tests are examples and do not establish behavioral coverage for the retained application or Core boundaries.

## Documentation consequence

- Repository-wide and cross-module matters route through `docs/project/`.
- Core-specific technical and lifecycle matters route through `Modules/Core/docs/ai/`.
- No Release or Phase Canonical record is created by this bootstrap.

## Evidence boundary

This summary derives only from the bounded files listed in `docs/project/references/SOURCE-DOCS-INDEX.md`, Git metadata, and active module-directory discovery required by the Pack. It contains no runtime secrets or environment data.
