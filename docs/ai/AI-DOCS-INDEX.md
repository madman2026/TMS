# AI-DOCS-INDEX

This folder is the entry point for reusable, index-driven AI governance. Owner-specific records are routed through project or owner-local entry points.

## Ownership

- `docs/ai/` owns reusable startup, rules, templates, lifecycle definitions, structure guidance, and generic navigation.
- `docs/project/` owns repository-wide and cross-module knowledge, source analysis, references, and project lifecycle records.
- Independently governed owner content belongs under the documentation root declared by that owner's manifest.

Current status: `reusable-three-layer-governance-active`.

Owner entry points:

- `docs/project/PROJECT-DOCS-INDEX.md` — project and cross-component routing; it declares the project profile and owner-discovery entry
- owner-local entry, manifest, profile, and indexes — resolve these through the selected repository's owner-discovery routing

Determine ownership from content, scope, audience, source of truth, consumers, and dependencies. Profiles are overlays: they may bind or tighten owner requirements but may not disable shared workflow or safety gates, override Canonical authority, or expand Pack scope.

## Usage

Normal startup uses:

- `docs/ai/start/START-HERE.md`
- `docs/ai/start/CONTEXT-MAP.md`

Use this index only to discover reusable governance areas or the correct operational index.

## Rules and templates

- `docs/ai/rules/RULES-INDEX.md`
- `docs/ai/templates/TEMPLATES-INDEX.md`

## Structure and maintenance

- `docs/ai/start/AI-STRUCTURE-GUIDE.md`
- `docs/ai/STRUCTURE-GUIDE.md`
- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`

## Project records

- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/changes/CHANGES-INDEX.md`
- `docs/project/ai/remediations/REMEDIATIONS-INDEX.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md`
- `docs/project/ai/reviews/REVIEWS-INDEX.md`
- `docs/project/references/REFERENCES-INDEX.md`

## Independently governed owner records

Resolve Canonical, Guide, Decision, Change, Remediation, Pack, Run, Review, and Reference indexes through the selected owner's manifest.

## Operator guidance

- `docs/ai/rules/OPERATOR-WORKFLOW.md`
- `docs/ai/rules/OPERATOR-WORKFLOW-fa.md`
- `docs/ai/rules/OPERATOR-GUIDE-RULES.md`
