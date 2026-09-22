# TMS-DECISION-0001 — Documentation ownership

Status: Accepted
Scope: Pack
Source: Operator answer
Date: 2026-09-22
Review At: when Core ownership boundaries materially change
Blocking: No
Closure Condition: superseded by a separately approved ownership decision
Next Review At: first Pack that changes Core ownership

## Type

4. Project-level ownership decision

## Decision

TMS uses three documentation layers. Reusable governance is owned by `docs/ai/`; repository-wide and cross-module documentation is owned by `docs/project/`; Core is activated as an independently governed Layer 3 owner under `Modules/Core/docs/ai/`.

Auth and Ecommerce remain removed and are neither active nor deferred documentation owners.

## Reason

The operator explicitly confirmed Core as an independent Layer 3 owner and accepted cleanup commit `a6e1574` as the documentation-bootstrap baseline. Source scope also shows Core-specific contracts alongside dependencies between the root application and Core, making explicit routing preferable to implicit folder-based ownership.

## Impact

- Root startup routes project work through the TMS project profile.
- Core work loads the Core router, manifest, and governance profile.
- Cross-module concerns stay project-owned.
- No application behavior changes.

## Files to Update

- `AGENTS.md`
- `docs/project/PROJECT-DOCS-INDEX.md`
- `docs/modules/MODULES-DOCS-INDEX.md`
- `Modules/Core/AGENTS.md`
- `Modules/Core/docs/ai/manifest.yaml`

## Required change request

No Change Request required for the approved bootstrap.

## future-work

Revisit only if Core becomes project-level shared infrastructure or another module is proposed as an independent owner.

## Review / Resolution

```text
Reviewed In: AI-PACK-TMS-DOCS-BOOTSTRAP-0001
Review Result: operator-approved ownership classification
Resolution: accepted
Next Review At: first Pack that changes Core ownership
Resolution Notes: Post-execution acceptance of generated documentation remains a separate gate.
```

## Notes

This decision accepts ownership classification only; it does not accept the generated Run Report or Documentation Maintenance Review.
