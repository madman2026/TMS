# CONTEXT-MAP — When to Read Which Files

This file routes the AI Agent to the minimum required context.

## Owner selection

| Work scope | Read first | Rule |
|---|---|---|
| Shared AI governance | `docs/ai/AI-DOCS-INDEX.md` | Keep rules, templates, startup, and structure reusable. |
| Repository or cross-component | `docs/project/PROJECT-DOCS-INDEX.md`, then its project profile | Project records own cross-component and repository lifecycle context. |
| Owner discovery | The repository's owner-discovery index | Only activated owners are authoritative. |
| Owner-local work | The local `AGENTS.md`, entry, manifest, and profile | Resolve owner-local indexes from the manifest. |

Ownership depends on scope, audience, source of truth, consumers, and dependencies—not names or folders alone.

## Executing an AI Pack

Read:

- the selected owner entry and profile;
- the owner manifest when one exists;
- the current Pack only;
- related accepted Canonical or Decision records only when referenced or needed;
- the latest accepted Run only when prior execution evidence matters.

Do not read unrelated Packs, Runs, Reviews, or references.

## Generating or updating a Pack

Read:

- `docs/ai/rules/AI-PACK-GENERATION-RULES.md`
- `docs/ai/templates/AI-PACK-TEMPLATE.md`
- applicable accepted owner records

If documentation is created, moved, or materially changed, also read `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`.

## Documentation maintenance

Read `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md` when creating, editing, moving, archiving, accepting, rejecting, or materially changing documentation. Use the relevant owner indexes and manifest to identify authority and required synchronization.

## Source understanding

Start with the selected project's source-structure summary and source-documentation index. Read source files only as required by approved scope. Source observations do not become Canonical decisions unless separately accepted.

## Operator answer or decision

Read:

- `docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md`
- the selected owner's Decision index

If an answer changes intended architecture, record it at the proper owner and apply the required acceptance gate.

## Conflict, change, or remediation

Read:

- `docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md`
- `docs/ai/rules/CANONICAL-RULES.md`
- the selected owner's Change and Remediation indexes

## Structure or placement uncertainty

Read:

- `docs/ai/start/AI-STRUCTURE-GUIDE.md`
- `docs/ai/STRUCTURE-GUIDE.md`
- the selected project and owner-discovery indexes

## Task-specific rules

- comments: `docs/ai/rules/COMMENTING-RULES.md`
- final reporting: `docs/ai/rules/REPORTING-RULES.md`
- Git or commit: `docs/ai/rules/GIT-AND-COMMIT-RULES.md`
- tests and validation: `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`
- reviews: `docs/ai/rules/REVIEW-RULES.md`

When a tracked document is created or its lifecycle changes, check and update the related index within Pack scope.
