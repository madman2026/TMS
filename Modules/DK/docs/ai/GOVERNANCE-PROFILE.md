# DK GOVERNANCE PROFILE

Status: active — bootstrap accepted
Owner: DK
Parent profile: `docs/project/ai/TMS-AI-PROFILE.md`

## Purpose

This profile binds shared governance to the independently owned DK target module. It supplements, and does not duplicate or weaken, reusable rules.

## Owner boundary

- Owner source root: `Modules/DK/`
- Documentation root: `Modules/DK/docs/ai/`
- Entry and operational indexes: declared by `Modules/DK/docs/ai/manifest.yaml`
- Technical context: the DK target boundary and its future internal Acceptance Components

Notification Delivery (ND) is reserved as a future Component at `Modules/DK/app/Acceptance/Components/NotificationDelivery/`. It is not a Nwidart module or separate documentation owner, and this bootstrap does not create it.

DK-owned documentation covers module-specific design, behavior, lifecycle, references, and operator knowledge. Root behavior, cross-module orchestration, shared Acceptance contracts/runtime, and repository lifecycle require project-level treatment.

## Execution constraints

- Read only the owner-local records needed for the active task.
- Do not infer authority from an empty index or an uncreated path.
- Require an accepted DK-local Pack before modifying DK source, tests, configuration, or generated artifacts after bootstrap acceptance.
- Restrict future target work to approved testing or staging environments with synthetic data.
- Never access a real target or include real credentials, tokens, accounts, URLs, selectors, payloads, personal data, production values, or environment values in DK source or documentation.
- Record unresolved cross-boundary observations at project level until ownership is separately decided.
