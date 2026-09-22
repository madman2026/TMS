# AGENTS.md — TMS Repository AI Entry Point

This file routes AI work in TMS. Repository files, not chat history, are the source of truth.

## Mandatory startup

At the start of a new task, read only:

1. `AGENTS.md`
2. `docs/ai/start/START-HERE.md`
3. `docs/ai/start/CONTEXT-MAP.md`

Then select the documentation owner and load only the context required for the current work.

- Repository-wide, cross-module, and lifecycle work starts at `docs/project/PROJECT-DOCS-INDEX.md` and uses `docs/project/ai/TMS-AI-PROFILE.md`.
- Module discovery starts at `docs/modules/MODULES-DOCS-INDEX.md`.
- Work owned by Core must also follow `Modules/Core/AGENTS.md` and the Core manifest.
- Do not load all Packs, Runs, Reviews, references, or source analysis by default.

## Documentation ownership

TMS uses three documentation layers:

1. reusable governance under `docs/ai/`;
2. TMS-wide and cross-module knowledge and lifecycle records under `docs/project/`;
3. owner-local knowledge and lifecycle records under an activated owner root.

Core is the only activated independent module owner. Its documentation root is `Modules/Core/docs/ai/`.

Ownership is determined from scope, audience, source of truth, consumers, and dependencies—not from folder names alone. Owner profiles overlay reusable rules; they may tighten requirements but may not disable shared safety gates, override accepted Canonical decisions, or expand Pack scope.

## Source of truth

For current implementation state, use actual repository source first, then accepted owner Run Reports and relevant Reviews. For intended architecture and delivery rules, use applicable routers, the selected owner manifest/profile, accepted Canonical and Decision records, shared rules, and the current approved Pack.

If source and accepted documentation conflict, report the conflict and follow `docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md`.

## Scope and execution

Stay within the active Pack's `Files to Create` and `Files to Edit`. Do not perform unrelated refactoring, cleanup, dependency changes, formatting, or redesign.

Follow:

- `docs/ai/rules/SCOPE-CONTROL-RULES.md`
- `docs/ai/rules/AI-EXECUTION-RULES.md`
- `docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md`
- `docs/ai/rules/GIT-AND-COMMIT-RULES.md`
- `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`
- `docs/ai/rules/REPORTING-RULES.md`
- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`

Do not commit unless the operator separately authorizes the commit.

## Subagents

Use subagents only when explicitly authorized by the operator or applicable higher-priority instructions. Every subagent remains bound by the same owner, Pack, safety, and reporting rules. The main agent remains responsible for integration and final validation.
