# CORE GOVERNANCE PROFILE

Status: active — bootstrap accepted
Owner: Core
Parent profile: `docs/project/ai/TMS-AI-PROFILE.md`

## Purpose

This profile binds shared governance to the independently owned Core module. It supplements, and does not duplicate or weaken, reusable rules.

## Owner boundary

- Owner source root: `Modules/Core/`
- Documentation root: `Modules/Core/docs/ai/`
- Entry and operational indexes: declared by `Modules/Core/docs/ai/manifest.yaml`
- Technical context: reusable contracts, HTTP/browser support, providers, and other implementation located inside Core

Core-owned documentation covers module-specific design, behavior, lifecycle, references, and operator knowledge. Root behavior, cross-module orchestration, repository lifecycle, and a contract consumed or defined across owner boundaries require project-level treatment.

## Execution constraints

- Read only the owner-local records needed for the active task.
- Do not infer authority from an empty index or an uncreated path.
- Do not modify Core source, tests, dependencies, configuration, or generated artifacts unless the approved Pack explicitly allows it.
- Never include real credentials, tokens, personal data, runtime payloads, or environment values in Core documentation.
- Record unresolved cross-boundary implementation observations at project level until ownership is separately decided.
