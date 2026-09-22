# DOCUMENT-MAINTENANCE-RULES

## 1. Purpose

This file is the central maintenance rulebook for shared, project, and owned-component documentation in this repository.

It exists to prevent documentation drift, stale analysis, broken indexes, duplicated rules, inconsistent templates, incorrect Release/Phase placement, and hidden dependency problems between AI documentation files.

Use this file whenever an AI Agent or human operator creates, edits, moves, archives, accepts, rejects, supersedes, or materially changes any AI documentation file.

This file is the owner of documentation maintenance rules.
Other files may reference this file, but they must not duplicate the full maintenance logic.

---

## 2. Core Principles

### 2.1 One Owner Per Concept

Every important concept must have one primary owner file.

Other files may reference that owner, but they must not duplicate the same detailed content.

Examples:

| Concept | Owner File |
|---|---|
| Repository AI Agent entry behavior | `AGENTS.md` |
| Startup read order and startup budget | `docs/ai/start/START-HERE.md` |
| Context routing | `docs/ai/start/CONTEXT-MAP.md` |
| AI documentation structure | `docs/ai/start/AI-STRUCTURE-GUIDE.md` |
| Human explanation of structure | `docs/ai/STRUCTURE-GUIDE.md` |
| Project documentation discovery | `docs/project/PROJECT-DOCS-INDEX.md` |
| Independently owned component discovery | repository owner-discovery index |
| Owner-specific product and delivery context | path declared by the selected owner index or manifest |
| Official decisions | current Canonical path declared by the selected owner |
| Operator decision history | current Decision index declared by the selected owner |
| AI Pack executable format | `docs/ai/templates/AI-PACK-TEMPLATE.md` |
| Pack execution rules | `docs/ai/rules/AI-EXECUTION-RULES.md` |
| Documentation maintenance | `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md` |

### 2.2 Reference Instead of Duplicate

If a file needs information owned by another file, it must link to that file instead of copying the same full content.

Short summaries are allowed only when they help routing or orientation.

### 2.3 Indexes Are Navigation, Not Documentation

INDEX files are operational navigation files.

They may contain:

- file path
- scope
- purpose
- status
- read policy
- short notes

They must not become long explanatory documents.

### 2.4 Templates Own Executable Formats

Templates define the current executable structure of generated files.

Explanatory files must not redefine full template details unless explicitly required.

### 2.5 Global Files Must Stay Neutral

Global files must remain release-neutral and phase-neutral.

Release-specific content belongs under release-specific folders.

Phase-specific content belongs under phase-specific folders.

### 2.6 Content-based Owner Selection

Every document must have one primary ownership scope:

1. reusable AI-system governance;
2. repository-wide or cross-component knowledge;
3. one independently owned component;
4. project-owned analysis of a third-party component.

Choose the owner by inspecting:

- actual content and declared scope;
- source of truth;
- intended audience and consumers;
- dependencies and inbound references;
- whether the content remains valid without one project or component.

A filename, prefix, current directory, Release/Phase label, or technology name may support the classification but must never be the sole criterion.

Mixed-scope documents require an approved split or explicit ownership decision. Do not duplicate the same policy into multiple owners.

### 2.7 Transitional Authority

The ownership foundation may exist before the corpus is migrated. During that state:

- follow owner indexes and manifests for `current_documentation_entry`, target root, and migration status;
- current existing paths remain authoritative until a Pack explicitly migrates and activates replacements;
- do not treat an empty or partially created target root as the source of truth;
- do not copy execution status from another branch or migration attempt;
- update current and target routing atomically within the Pack that changes authority.

---

## 3. Content Ownership Model

The tables below describe ownership and synchronization relationships. Resolve owner-specific paths through the selected project entry, repository owner-discovery index, and owner manifest before creating or moving a file.

### 3.1 Startup and Routing Ownership

| Content | Owner | Related Files To Check When Changed |
|---|---|---|
| AI entry rules | `AGENTS.md` | `START-HERE.md`, `CONTEXT-MAP.md` |
| Startup read order | `docs/ai/start/START-HERE.md` | `AGENTS.md`, `CONTEXT-MAP.md`, `AI-DOCS-INDEX.md` |
| Context routing by situation | `docs/ai/start/CONTEXT-MAP.md` | `START-HERE.md`, relevant indexes |
| AI structure explanation | `docs/ai/start/AI-STRUCTURE-GUIDE.md` | `STRUCTURE-GUIDE.md`, `AI-DOCS-INDEX.md` |
| Human structure explanation | `docs/ai/STRUCTURE-GUIDE.md` | `AI-STRUCTURE-GUIDE.md`, `AI-DOCS-INDEX.md` |

### 3.2 Product and Delivery Ownership

| Content | Owner | Related Files To Check When Changed |
|---|---|---|
| Project goal | `PRODUCT-AND-DELIVERY-CONTEXT.md` | `GLOBAL-CANONICAL-DECISIONS.md` if it becomes official behavior |
| Owner/product role | applicable product/context document | applicable owner Canonical |
| Execution Layer vs Decision Layer | `GLOBAL-CANONICAL-DECISIONS.md` for official rule; `PRODUCT-AND-DELIVERY-CONTEXT.md` for explanation | AI Packs, guides |
| Full roadmap | `PRODUCT-AND-DELIVERY-CONTEXT.md` | `GUIDES-INDEX.md`, release guides |
| Release-specific guidance | `<owner-docs-root>/guides/release-{N}/RELEASE-{N}-SUMMARY.md` | `GUIDES-INDEX.md`, release canonical |
| Phase-specific guidance | `<owner-docs-root>/guides/release-{N}/phases/PHASE-{X}-SUMMARY.md` | `GUIDES-INDEX.md`, phase canonical |

### 3.3 Execution Ownership

| Content | Owner | Related Files To Check When Changed |
|---|---|---|
| AI Pack template | `docs/ai/templates/AI-PACK-TEMPLATE.md` | `TEMPLATES-INDEX.md`, `PRODUCT-AND-DELIVERY-CONTEXT.md`, `AI-STRUCTURE-GUIDE.md` |
| AI Pack list/status | `<owner-docs-root>/packs/PACKS-INDEX.md` | Run reports, Reviews |
| AI Pack implementation file | `<owner-docs-root>/packs/release-{N}/phase-{X}/...` | `PACKS-INDEX.md`, related guide/canonical |
| Run report template | `docs/ai/templates/RUN-REPORT-TEMPLATE.md` | `RUNS-INDEX.md`, `AGENT-FINAL-REPORT-TEMPLATE.md`, `REPORTING-RULES.md` |
| Run reports | `<owner-docs-root>/runs/release-{N}/phase-{X}/...` | `RUNS-INDEX.md`, `PACKS-INDEX.md`, reviews |
| Review templates | `docs/ai/templates/*REVIEW*.md` | `TEMPLATES-INDEX.md`, `REVIEWS-INDEX.md` |
| Reviews | `<owner-docs-root>/reviews/...` | `REVIEWS-INDEX.md`, relevant run/decision/change indexes |

### 3.4 Decision and Change Control Ownership

| Content | Owner | Related Files To Check When Changed |
|---|---|---|
| Operator answer classification | `QUESTION-ANSWER-DECISION-RULES.md` | `DECISIONS-INDEX.md`, canonical files |
| Decision records/logs | `<owner-docs-root>/decisions/...` | `DECISIONS-INDEX.md`, canonical files, guides |
| Canonical file creation, update, validation, promotion, finalization, and indexing rules | `docs/ai/rules/CANONICAL-RULES.md` | canonical templates, `CANONICAL-INDEX.md`, decisions, guides, pending Packs |
| Official canonical decisions and contracts | `<owner-docs-root>/canonical/...` | `CANONICAL-INDEX.md`, decisions, guides, pending Packs |
| Conflict/change/remediation workflow | `CONFLICT-CHANGE-REMEDIATION-RULES.md` | changes, remediations, packs |
| Change Requests | `<owner-docs-root>/changes/...` | `CHANGES-INDEX.md`, decisions, canonical, remediations |
| Remediation Packs | `<owner-docs-root>/remediations/...` | `REMEDIATIONS-INDEX.md`, `CHANGES-INDEX.md`, run/review indexes |

### 3.5 References and Source Analysis Ownership

| Content | Owner | Related Files To Check When Changed |
|---|---|---|
| Project reference list/read policy | `docs/project/references/REFERENCES-INDEX.md` | `PROJECT-DOCS-INDEX.md`, `AI-DOCS-INDEX.md`, `CONTEXT-MAP.md` |
| Source analysis summary | `PROJECT-ANALYSIS-SUMMARY.md` | `PROJECT-ANALYSIS-FULL.md`, pending Packs, guides/canonical if affected |
| Full source analysis | `PROJECT-ANALYSIS-FULL.md` | `PROJECT-ANALYSIS-SUMMARY.md`, references index |
| Graph file list | `GRAPH-FILES-INDEX.md` | `REFERENCES-INDEX.md`, graph files |
| Source docs list | `SOURCE-DOCS-INDEX.md` | `REFERENCES-INDEX.md`, source docs |
| Generated onboarding | `references/onboarding/*` | `REFERENCES-INDEX.md` |

---

## 4. Document Scope Rule

Shared governance files must stay owner-neutral, release-neutral, and phase-neutral.

Global files include:

- `AGENTS.md`
- `docs/ai/start/*`
- `docs/ai/rules/*`
- `docs/ai/templates/*`
- `docs/ai/AI-DOCS-INDEX.md`
- `docs/ai/STRUCTURE-GUIDE.md`

These files must not contain fixed project, component, technology, Release-specific, or Phase-specific execution state except an explicit transitional routing note.

Repository-specific record instances target `docs/project/`. Independently owned component records target the documentation root declared by that owner's manifest. During migration, existing paths remain active until the responsible Pack moves the content, updates inbound references/indexes, and validates the new authority.

Release-specific content must live under release-specific folders, such as:

- `<owner-docs-root>/canonical/release-{N}/`
- `<owner-docs-root>/guides/release-{N}/`
- `<owner-docs-root>/packs/release-{N}/`
- `<owner-docs-root>/runs/release-{N}/`
- `<owner-docs-root>/reviews/release-{N}/`
- `<owner-docs-root>/decisions/release-{N}/`
- `<owner-docs-root>/changes/release-{N}/`
- `<owner-docs-root>/remediations/release-{N}/`

Phase-specific content must live under phase-specific folders, such as:

- `<owner-docs-root>/guides/release-{N}/phases/`
- `<owner-docs-root>/canonical/release-{N}/phases/`
- `<owner-docs-root>/packs/release-{N}/phase-{X}/`
- `<owner-docs-root>/runs/release-{N}/phase-{X}/`
- `<owner-docs-root>/reviews/release-{N}/phase-{X}/`
- `<owner-docs-root>/decisions/release-{N}/phase-{X}/`
- `<owner-docs-root>/changes/release-{N}/phase-{X}/`
- `<owner-docs-root>/remediations/release-{N}/phase-{X}/`

A global file may reference release/phase files, but it must not duplicate their content.

If Release-specific or Phase-specific content is found in a global file, move it to the correct release/phase file and keep only a reference in the global file.

---

## 5. Dependency and Sync Model

Whenever a file changes, classify the change before deciding what else to update.

### 5.1 Change Type Classification

| Change Type | Meaning |
|---|---|
| Wording-only | Typos, grammar, formatting, no meaning change |
| Structural | File path, folder placement, section layout, template shape |
| Status | Draft, accepted, rejected, superseded, closed, remediated |
| Scope | Release, Phase, Pack, feature, or affected file scope changed |
| Decision | Official or operator decision added/changed |
| Execution | Pack/run/review outcome changed |
| Reference validity | Source analysis, graph, onboarding, or source docs may be stale |
| Workflow | Startup, execution, review, maintenance, or remediation process changed |

### 5.2 Sync Rule

If a change affects a file's path, status, purpose, scope, meaning, format, or official decision state, check the related owner/index/template files in the relevant sections below.

If a change is wording-only and does not affect path, status, purpose, scope, or meaning, no index update is required.

---

## 6. Change Impact Matrix

| If This Changes | Check / Update These |
|---|---|
| `AGENTS.md` top-level behavior | `START-HERE.md`, `CONTEXT-MAP.md`, `AI-DOCS-INDEX.md` |
| Startup read order or startup budget | `AGENTS.md`, `START-HERE.md`, `CONTEXT-MAP.md` |
| Context routing | `CONTEXT-MAP.md`, relevant indexes, `START-HERE.md` if startup impact exists |
| Folder structure or file placement | `AI-STRUCTURE-GUIDE.md`, `STRUCTURE-GUIDE.md`, `AI-DOCS-INDEX.md` |
| Documentation owner or manifest changes | `PROJECT-DOCS-INDEX.md`, `PLUGINS-DOCS-INDEX.md`, selected owner manifest, startup/context routing, affected lifecycle indexes |
| Document scope rule | `DOCUMENT-MAINTENANCE-RULES.md`, `AI-STRUCTURE-GUIDE.md`, `AI-EXECUTION-RULES.md`, `CONTEXT-MAP.md` |
| AI Pack template | `TEMPLATES-INDEX.md`, `PRODUCT-AND-DELIVERY-CONTEXT.md`, `AI-STRUCTURE-GUIDE.md`, pending Pack creation instructions |
| Run report template | `TEMPLATES-INDEX.md`, `RUNS-INDEX.md`, `REPORTING-RULES.md` , `AGENT-FINAL-REPORT-TEMPLATE.md` |
| Review template | `TEMPLATES-INDEX.md`, `REVIEWS-INDEX.md` |
| Decision template/rules | `TEMPLATES-INDEX.md`, `QUESTION-ANSWER-DECISION-RULES.md`, `DECISIONS-INDEX.md` |
| Change Request template/rules | `CHANGES-INDEX.md`, `CONFLICT-CHANGE-REMEDIATION-RULES.md`, `REMEDIATIONS-INDEX.md` |
| Remediation template/rules | `REMEDIATIONS-INDEX.md`, `CHANGES-INDEX.md`, `CONFLICT-CHANGE-REMEDIATION-RULES.md` |
| New AI Pack | `PACKS-INDEX.md` |
| AI Pack executed | `RUNS-INDEX.md`, `PACKS-INDEX.md`, review queue if required |
| Run accepted/rejected/remediated | `RUNS-INDEX.md`, `PACKS-INDEX.md`, `REVIEWS-INDEX.md` if reviewed |
| New decision | `DECISIONS-INDEX.md`, relevant decision log, canonical if official |
| Canonical decision changed | `CANONICAL-INDEX.md`, decision log, relevant guide, pending Packs |
| Guide changed | `GUIDES-INDEX.md`, relevant canonical, pending Packs if execution strategy changed |
| Change Request created/closed | `CHANGES-INDEX.md`, related decision/canonical/remediation files |
| Remediation Pack created/executed | `REMEDIATIONS-INDEX.md`, `CHANGES-INDEX.md`, `PACKS-INDEX.md`, `RUNS-INDEX.md` if executed |
| Reference file added/moved | `REFERENCES-INDEX.md`, specific reference index |
| Source docs changed | `SOURCE-DOCS-INDEX.md`, `REFERENCES-INDEX.md` if category/read policy changed |
| Graph files regenerated | `GRAPH-FILES-INDEX.md`, `REFERENCES-INDEX.md`, analysis summary if insights changed |
| Project analysis updated | `PROJECT-ANALYSIS-SUMMARY.md`, `PROJECT-ANALYSIS-FULL.md`, pending Packs, affected guides/canonical |
| framework/project/dependency/source update | Source Update Protocol below |
| File archived | active index, archive notes |

---

## 7. Index Maintenance Rule

INDEX files are operational navigation and status files.

Whenever an AI Agent creates, moves, archives, supersedes, rejects, accepts, closes, or materially changes a tracked AI documentation file, the related current owner INDEX file must be updated in the same execution if the current Pack scope allows documentation updates.

If the current Pack scope does not allow editing the related INDEX file, the AI Agent must report the required update in the Agent Final Report under `Required Follow-up Updates`.

When adding or materially changing an important AI documentation file, the Agent must verify that the file is reachable from the relevant INDEX file and that its read policy or usage path is clear.

### 7.1 Required INDEX Updates

The concrete `docs/ai/...` paths below remain the current active indexes during the migration. After an owner-local index is explicitly activated, use the equivalent index declared by that owner's manifest or entry point.

| Event | Required INDEX Update |
|---|---|
| New or updated AI Pack | `<owner-docs-root>/packs/PACKS-INDEX.md` |
| New or updated Run Report | `<owner-docs-root>/runs/RUNS-INDEX.md` |
| New or updated Review | `<owner-docs-root>/reviews/REVIEWS-INDEX.md` |
| New or updated Decision Record or Decision Log | `<owner-docs-root>/decisions/DECISIONS-INDEX.md` |
| New or updated Change Request | `<owner-docs-root>/changes/CHANGES-INDEX.md` |
| New or updated Remediation Pack | `<owner-docs-root>/remediations/REMEDIATIONS-INDEX.md` |
| New or moved Canonical file | `<owner-docs-root>/canonical/CANONICAL-INDEX.md` |
| New or moved Guide file | `<owner-docs-root>/guides/GUIDES-INDEX.md` |
| New or moved project Reference file | `docs/project/references/REFERENCES-INDEX.md` |
| New or moved Template file | `docs/ai/templates/TEMPLATES-INDEX.md` |
| New or moved top-level AI documentation file | `docs/ai/AI-DOCS-INDEX.md` |

### 7.2 When Not To Update an INDEX

Do not update an INDEX file for minor wording, typo, or formatting changes when the file path, status, purpose, scope, read policy, and meaning did not change.

### 7.3 INDEX Format Rule

Prefer short tables.

Recommended columns:

```text
Scope | File | Purpose | Status | Read Policy | Notes
```

Use only the columns needed for that index.

Do not turn an INDEX into a long guide.


### 7.4 Important File Discoverability Rule

Every important AI documentation file must be discoverable through the relevant operational INDEX file.

INDEX files should make it clear how to find the file and when the file may be relevant.

At minimum, each INDEX should provide either:

- a table-level Read Rule, or
- a row-level Read Policy when files in that index have different read conditions.

Do not require row-level Read Policy for every Pack, Run Report, Change Request, or Remediation Pack when a table-level Read Rule is clearer and shorter.

Do not add exhaustive `Used By` or backlink lists to INDEX files.

Dependency and change-impact checks are owned by this file, especially the Content Ownership Model and Change Impact Matrix.

INDEX files must remain operational navigation files. They must not duplicate full rules, templates, canonical decisions, guide content, or workflow logic.

If an important file cannot be discovered from an INDEX file, or its read path is unclear, update the relevant INDEX if scope allows it.

If scope does not allow the update, record the missing INDEX/read-policy update under:

```text
Required Follow-up Updates
```

---

## 8. Template and Format Synchronization Rule

Templates own executable file formats.

If a template changes, do not manually duplicate the same format in explanatory files.

Instead:

1. update the template first
2. update `TEMPLATES-INDEX.md`
3. update explanatory references only if needed
4. report any legacy files that intentionally remain in an older format

### 8.1 Template Owners

| Format | Owner Template |
|---|---|
| AI Pack | `docs/ai/templates/AI-PACK-TEMPLATE.md` |
| Run Report | `docs/ai/templates/RUN-REPORT-TEMPLATE.md` |
| Review | `docs/ai/templates/REVIEW-TEMPLATE.md` |
| Consistency Review | `docs/ai/templates/CONSISTENCY-REVIEW-TEMPLATE.md` |
| Final Review | `docs/ai/templates/FINAL-REVIEW-TEMPLATE.md` |
| Decision Record | `docs/ai/templates/DECISION-RECORD-TEMPLATE.md` |
| Change Request | `docs/ai/templates/CHANGE-REQUEST-TEMPLATE.md` |
| Remediation Pack | `docs/ai/templates/REMEDIATION-PACK-TEMPLATE.md` |
| Agent execution pack report | `docs/ai/templates/AGENT-FINAL-REPORT-TEMPLATE.md` |
| Docs Maintenance Review | `docs/ai/templates/DOCS-MAINTENANCE-REVIEW-TEMPLATE.md` |

### 8.2 Legacy Format Rule

Template evolution is expected.

Older Packs, Run Reports, Reviews, Decisions, Change Requests, Remediation Packs, or other AI documentation records may differ from the current template if they were created before the template changed.

Legacy files may remain in their historical format if changing them would rewrite execution history.

New files must follow the current template.

If legacy and current formats differ, note this in the related index or review.

---

## 9. Rule Synchronization Rule

When a rule file changes, check whether related templates, startup files, or execution files must change.

| Rule File Changed | Check These |
|---|---|
| `AI-EXECUTION-RULES.md` | `AI-PACK-TEMPLATE.md`, `AGENT-FINAL-REPORT-TEMPLATE.md`, `REPORTING-RULES.md`, `OPERATOR-WORKFLOW.md` |
| `SCOPE-CONTROL-RULES.md` | `AGENTS.md`, `AI-EXECUTION-RULES.md`, `AI-PACK-TEMPLATE.md` |
| `COMMENTING-RULES.md` | `AI-PACK-TEMPLATE.md`, pending Packs with commenting requirements |
| `TEST-AND-VALIDATION-RULES.md` | `AI-PACK-TEMPLATE.md`, `RUN-REPORT-TEMPLATE.md`, `AGENT-FINAL-REPORT-TEMPLATE.md`, `REPORTING-RULES.md` |
| `GIT-AND-COMMIT-RULES.md` | `OPERATOR-WORKFLOW.md`, `START-HERE.md` if startup git checks changed |
| `QUESTION-ANSWER-DECISION-RULES.md` | `DECISION-RECORD-TEMPLATE.md`, `DECISIONS-INDEX.md`, canonical update rules |
| `CONFLICT-CHANGE-REMEDIATION-RULES.md` | `CHANGE-REQUEST-TEMPLATE.md`, `REMEDIATION-PACK-TEMPLATE.md`, related indexes |
| `DOCUMENT-MAINTENANCE-RULES.md` | `AI-STRUCTURE-GUIDE.md`, `AI-EXECUTION-RULES.md`, `CONTEXT-MAP.md`, `TEMPLATES-INDEX.md` |

---

## 10. Canonical / Decision / Guide Synchronization Rule

### 10.1 If a Decision Is Added

Check:

- relevant decision log
- `DECISIONS-INDEX.md`
- relevant canonical file if the decision is official
- relevant guide if execution strategy changes
- pending Packs if implementation scope changes

### 10.2 If Canonical Changes

Check:

- `CANONICAL-INDEX.md`
- related decision log
- related guide
- pending Packs in the affected release/phase
- run/review files if previous execution may be invalidated

### 10.3 If a Guide Changes

Check:

- `GUIDES-INDEX.md`
- related canonical file
- pending Packs if execution strategy changed
- phase/release review if already executed

---

### 10.4 Canonical Maintenance Reference

For canonical file creation, update, validation, promotion, finalization, status handling, and canonical indexing, follow:
- `docs/ai/rules/CANONICAL-RULES.md`
For conflict, Change Request, and Remediation workflow, follow:
- `docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md`
Use this file only for documentation maintenance, ownership, sync, and index update rules related to canonical changes.

## 11. Pack / Run / Review Synchronization Rule

### 11.1 New Pack

Update:

- `PACKS-INDEX.md`

Check:

- current release guide
- current phase guide
- canonical references

### 11.2 Pack Executed

Update or create:

- Run Report if persistent reporting is required
- `RUNS-INDEX.md`
- `PACKS-INDEX.md` status if execution status changed

### 11.3 Run Accepted / Rejected / Failed / Remediated

Check/update:

- `RUNS-INDEX.md`
- `PACKS-INDEX.md`
- `REVIEWS-INDEX.md` if reviewed
- `CHANGES-INDEX.md` and `REMEDIATIONS-INDEX.md` if correction is required

### 11.4 Review
For review type selection, review status rules, review findings, and review-triggered decisions, changes, remediations, or canonical updates, follow:

- `docs/ai/rules/REVIEW-RULES.md`

---

## 12. Change / Remediation Synchronization Rule

### 12.1 Change Request Created

Update/check:

- `CHANGES-INDEX.md`
- decision log if the change came from an operator decision
- canonical file if accepted behavior changes

### 12.2 Change Request Approved

Create or update:

- Remediation Pack if implementation correction is required
- `REMEDIATIONS-INDEX.md`
- `CHANGES-INDEX.md` status

### 12.3 Remediation Executed

Update/check:

- `REMEDIATIONS-INDEX.md`
- `CHANGES-INDEX.md`
- `RUNS-INDEX.md` if execution report exists
- `PACKS-INDEX.md` if original Pack status changes
- `REVIEWS-INDEX.md` if review is required

---

## 13. Reference / Graph / Source Analysis Validity Rule

References are on-demand context. They can become stale when source code or source documentation changes.

### 13.1 Reference Added or Moved

Update/check:

- `REFERENCES-INDEX.md`
- specific index such as `SOURCE-DOCS-INDEX.md` or `GRAPH-FILES-INDEX.md`

### 13.2 Project Analysis Updated

Update/check:

- `PROJECT-ANALYSIS-SUMMARY.md`
- `PROJECT-ANALYSIS-FULL.md`
- `REFERENCES-INDEX.md`
- pending Packs affected by the new analysis
- canonical/guides if the analysis challenges current decisions

### 13.3 Graph Files Regenerated

Update/check:

- `GRAPH-FILES-INDEX.md`
- `REFERENCES-INDEX.md` if graph purpose or read policy changed
- `PROJECT-ANALYSIS-SUMMARY.md` if insights materially changed

### 13.4 Source Docs Updated

Update/check:

- `SOURCE-DOCS-INDEX.md`
- `REFERENCES-INDEX.md` if source doc categories changed
- relevant Packs if source docs affect implementation assumptions

---

## 14. Source / Framework / Project Update Protocol

For review type selection, review status rules, review findings, Source Update Reviews, and review-triggered decisions, changes, remediations, or canonical updates, follow:

- `docs/ai/rules/REVIEW-RULES.md`

---

## 15. Status Lifecycle Rule

Use consistent statuses across indexes and review files.

### Pack Status

```text
draft
ready
in-progress
executed
accepted
rejected
superseded
remediated
archived
```

### Run Status

```text
executed
accepted
failed
rejected
superseded
remediated
```

### Decision Status

```text
proposed
accepted
rejected
temporary
superseded
revisit-later
```

### Change Request Status

```text
draft
open
approved
rejected
implemented
closed
```

### Review Status

```text
draft
pending
completed
superseded
```

If a file uses a different status, either map it to the closest lifecycle status or record the reason in Notes.

---

## 16. Archive and Legacy Rule

Archive files are historical. They are not active source-of-truth files.

Read archive files only when:

- migrating old content
- checking historical decisions
- resolving old path history
- verifying what changed during a redesign

When archiving a file:

1. remove active references to the old path
2. update the relevant index
4. keep a short reason if the archive file may be revisited

Do not read archive files during normal startup or normal Pack execution.

---

## 17. Periodic Review Schedule

### After Every Pack

- Review Agent Final Report.
- Review changed files.
- Review tests.
- Review scope violations.
- Check required index updates.
- Check Required Follow-up Updates.
- Check decision/canonical/change/remediation needs.

### At the End of Every Phase

- Sync `PACKS-INDEX.md`.
- Sync `RUNS-INDEX.md`.
- Sync `REVIEWS-INDEX.md`.
- Sync `DECISIONS-INDEX.md`.
- Check `CHANGES-INDEX.md` and `REMEDIATIONS-INDEX.md`.
- Sync phase canonical with decision logs.
- Sync phase guide with execution reality.
- Review temporary/revisit-later decisions.
- Create or update Phase Review.
- Check global files for release/phase leakage.

### At the End of Every Release

- Review all Phase Reviews.
- Sync release canonical.
- Sync release guide.
- Review temporary decisions for next release planning.
- Close, carry forward, or remediate open changes.
- Validate references and source analysis.
- Create Release Final Review.

### Monthly or After Several Major Packs

- Review `AI-DOCS-INDEX.md`.
- Review `REFERENCES-INDEX.md`.
- Check global files for release/phase leakage.
- Check whether templates and explanatory files drifted.
- Check whether archive files are incorrectly referenced.
- Check whether source analysis is still reliable.

### After Source / Framework / Project Update

Run the Source / Framework / Project Update Protocol.

---

## 18. Documentation Health Checklist

Use this checklist during maintenance reviews.

- [ ] Global files are release-neutral and phase-neutral.
- [ ] Release-specific content lives under release folders.
- [ ] Phase-specific content lives under phase folders.
- [ ] Each major concept has one owner file.
- [ ] Explanatory files do not duplicate template content.
- [ ] INDEX files match actual files and statuses.
- [ ] Templates match current file creation practice.
- [ ] Canonical files match accepted decision logs.
- [ ] Guides match current release/phase execution strategy.
- [ ] Pack statuses match run/review outcomes.
- [ ] Run reports match actual executions.
- [ ] Review indexes include real review files.
- [ ] Change Requests are open/closed correctly.
- [ ] Remediation Packs are linked to Change Requests.
- [ ] Reference files are still valid for the current source code.
- [ ] Graph files are not read by default.
- [ ] Source docs indexes are usable and not overloaded with raw image lists.
- [ ] Archive files are not used as active instructions.
- [ ] Required Follow-up Updates from prior reports are resolved or tracked.

---

## 19. Operator Maintenance Checklist

### After an AI Pack

- [ ] Confirm post-execution operator gate was completed.
- [ ] Review changed files from git diff --name-only.
- [ ] Confirm changed files match Pack scope or allowed governance maintenance.
- [ ] Approve Agent to produce Agent Final Report.
- [ ] Review Agent Final Report.
- [ ] Review human/operator review section.
- [ ] Run required tests.
- [ ] Confirm no unauthorized files changed.
- [ ] Confirm related indexes were updated or listed as follow-up.
- [ ] Confirm decisions were classified correctly.
- [ ] Confirm canonical changes were approved if any.
- [ ] Confirm Change Request or Remediation need was handled.
- [ ] Commit only after validation succeeds.

### During Documentation Maintenance

- [ ] Identify changed file type.
- [ ] Identify owner file.
- [ ] Check release/phase scope.
- [ ] Check related indexes.
- [ ] Check template/rule/canonical/guide sync.
- [ ] Check reference/source validity if source-related.
- [ ] Check whether review or change request is needed.
- [ ] Record unresolved updates.

---

## 20. AI Maintenance Flow

Whenever the AI Agent creates or edits AI documentation, it must follow this flow:

1. Identify the changed file type.
2. Identify the owner file for the affected concept.
3. Classify the change type.
4. Check whether the change is global, release-specific, or phase-specific.
5. Check whether an INDEX file must be updated.
6. Check whether template/rule/canonical/guide/decision/run/review sync is required.
7. Check whether reference/source validity is affected.
8. Check whether a Change Request, Remediation Pack, or Review is required.
9. Perform only updates allowed by the current Pack scope.
10. Report any required but unperformed updates in the Agent Final Report.

If the AI Agent is unsure, it must stop and ask the operator instead of silently choosing a file location or source of truth.

---

## 21. Agent Final Report Requirements

When AI documentation was created or changed, the Agent Final Report must include:

```text
Documentation Maintenance:
- Owner file checked:
- Related indexes updated:
- Related templates checked:
- Related canonical/decision/guide files checked:
- Related run/review/change/remediation files checked:
- Reference/source validity checked:
- Required Follow-up Updates:
```

If no maintenance action was required, state:

```text
Documentation Maintenance:
- No documentation maintenance updates required.
```

---

## 22. When Unsure

If the AI Agent cannot determine where a change belongs or what must be updated:

1. read `docs/ai/start/AI-STRUCTURE-GUIDE.md`
2. read this file
3. check the relevant index
4. ask the operator if still unclear

Do not guess.
