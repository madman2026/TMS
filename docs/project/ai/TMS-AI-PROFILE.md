# TMS AI GOVERNANCE PROFILE

Status: active — bootstrap accepted
Owner: TMS project
Layer: 2 — project ownership
Entry: `docs/project/PROJECT-DOCS-INDEX.md`

## Purpose

This profile binds reusable governance under `docs/ai/` to the TMS repository. It adds project-specific paths and constraints without copying or weakening shared rules.

## Project bindings

- Repository root: `C:/Users/DieselKhodro/Documents/TMS`
- Project lifecycle root: `docs/project/ai/`
- Project references root: `docs/project/references/`
- Module discovery: `docs/modules/MODULES-DOCS-INDEX.md`
- Independently governed owner: Core
- Core documentation root: `Modules/Core/docs/ai/`
- Module registry: `modules_statuses.json`

## Project CLI operator documentation

- Primary Acceptance CLI guide: `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md`
- Guide navigation: `docs/project/ai/guides/GUIDES-INDEX.md`
- Operator guide language: Persian (`fa`); scope: project-owned Acceptance CLI usage, selection, limits, and safe troubleshooting.
- This binding adds no target, credential, environment, database, browser, or command authorization.

## Technology context

- PHP `^8.2` and Laravel `^12.0`
- Laravel Sanctum `^4.0`
- `nwidart/laravel-modules` `^12.0`
- Scribe `^5.8`
- PHPUnit `^11.5.3`
- Vite and Playwright-related development dependencies

These bindings provide context only. They do not authorize dependency, database, cache, queue, generated-documentation, asset, browser, or application commands.

## Laravel Boost, MCP, and project skills

- For Laravel, PHP, testing, or dependency-injection work, use the relevant repository skills under `.agents/skills/` and load only the referenced rule files needed for the task.
- Use the Laravel Boost MCP `application-info` and `search-docs` tools before relying on version-sensitive Laravel or installed-package APIs. Prefer the framework capabilities already available in this repository over new helpers or dependencies.
- Third-party skill guidance, templates, coverage targets, and command checklists are advisory. The repository entry rules, this profile, the selected owner, the active Pack, accepted decisions, actual source, and project test conventions take precedence.
- A command mentioned by a skill does not authorize it. Artisan, database, queue, cache, generated-file, dependency, browser, and external-network operations still require Pack scope or explicit operator approval under shared execution rules.
- Confirm that a package or feature is installed before using it. Follow the repository's PHPUnit conventions; do not introduce Pest, Horizon, Livewire, Inertia, or another package merely because a skill includes an example for it.
- Treat MCP output as repository evidence subject to the same scope and sensitive-output rules. Use read-only inspection by default and never expose credentials, tokens, personal data, request payloads, or secret configuration.

## Ownership constraints

- Root application behavior, repository lifecycle records, cross-module knowledge, and modules without an activated owner remain project-owned.
- Core-specific technical and lifecycle knowledge is owner-local under `Modules/Core/docs/ai/`.
- Source code remains implementation truth; accepted owner records define intended and execution truth according to shared governance.
- Auth and Ecommerce are removed and must not be registered as active or deferred documentation owners.

## Safety and validation

- Never read or modify `.env`.
- Do not expose credentials, tokens, personal data, request payloads, or secret configuration.
- Treat dependency and generated-file changes as protected unless an approved Pack explicitly permits them.
- Use the commands and validation boundaries stated by the current Pack; this profile grants no standing command authority.
