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

## Technology context

- PHP `^8.2` and Laravel `^12.0`
- Laravel Sanctum `^4.0`
- `nwidart/laravel-modules` `^12.0`
- Scribe `^5.8`
- PHPUnit `^11.5.3`
- Vite and Playwright-related development dependencies

These bindings provide context only. They do not authorize dependency, database, cache, queue, generated-documentation, asset, browser, or application commands.

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
