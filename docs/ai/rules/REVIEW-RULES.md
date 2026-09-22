# REVIEW-RULES

This file defines the rules for creating, selecting, using, updating, and acting on Reviews in `docs/ai`.

This file owns Review behavior.

Review templates own Review structure, headings, placeholders, and section-level output format.

---

## 1. Purpose

Reviews are formal evaluation records.

A Review answers:

```text
Is this output, execution, documentation state, Phase, Release, or consistency state acceptable?
```

Reviews must not replace:

- AI Packs
- Run Reports
- Decision Logs
- Canonical files
- Change Requests
- Remediation Packs

Reviews may trigger decisions, canonical updates, Change Requests, or Remediation Packs, but they do not replace those files.

---

## 2. Review vs Run Report

### Run Report

A Run Report records what happened during an executable Pack run.

It answers:

```text
What was executed?
What changed?
What tests ran?
Was scope respected?
What follow-up is required?
```

Run Reports are stored under:

```text
<owner-docs-root>/runs/
```

Run Reports must follow:

```text
docs/ai/templates/RUN-REPORT-TEMPLATE.md
```

Reporting behavior is owned by:

```text
docs/ai/rules/REPORTING-RULES.md
```

### Review

A Review evaluates one or more outputs, files, runs, decisions, documentation states, Phases, or Releases.

It answers:

```text
Is the result acceptable?
Is it consistent?
Can we proceed?
What must be fixed before continuing?
```

Reviews are stored under:

```text
<owner-docs-root>/reviews/
```

---

## 3. When a Review Is Required

A Review is required when one of these conditions is true:

- closing a Phase
- closing a Release
- validating a sensitive or high-impact Pack
- validating a sensitive Remediation Pack
- checking consistency across Packs, Runs, Canonical files, Decisions, Changes, or Remediations
- performing documentation maintenance review
- performing source/framework/project update review
- validating whether source/reference changes invalidate existing analysis, Packs, Canonical files, Guides, or previous executions
- operator explicitly requests a formal review
- a rule, Pack, Change Request, Remediation Pack, or Final Report requires a review
- validating multilingual, translation, hardcoded visible text, locale, direction, or RTL/LTR issues when an Agent Final Report, Run Report, Pack, Remediation Pack, or operator request marks translation quality, missing translations, locale convention, hardcoded text, API message translation, Admin UI translation, or RTL/LTR behavior as needing human review
- validating unresolved Postman Collection or API test artifact issues when an Agent Final Report, Run Report, Pack, Remediation Pack, or operator request marks API test artifact maintenance as needing human review
- validating unresolved configuration, settings, environment variable, Admin Settings, provider secret, test setup, or operator manual setup issues when an Agent Final Report, Run Report, Pack, Remediation Pack, or operator request marks configuration/settings work as needing human review
- validating unresolved UI, Admin UI, settings UI, dashboard UI, permission-gated UI, sensitive UI action, navigation, form, table, or visibility issues when an Agent Final Report, Run Report, Pack, Remediation Pack, or operator request marks UI work as needing human review

A Review is not required after every normal Pack if the Agent Final Report, Run Report, tests, and operator check are sufficient.

---

## 4. When a Review Is Optional

A Review is optional when:

- the Pack is small and low-risk
- no canonical decisions changed
- no Change Request or Remediation was involved
- no source/reference validity issue exists
- no documentation maintenance impact exists
- tests passed and scope was clean
- the operator accepts the Agent Final Report and Run Report without requesting formal review

In these cases, the Run Report and commit history may be enough.

---

## 5. Review Types

Use the smallest Review type that matches the situation.

### 5.1 General Review

Use template:

```text
docs/ai/templates/REVIEW-TEMPLATE.md
```

Use for:

- targeted Pack review
- targeted Run review
- human/operator technical review
- review of a sensitive file or implementation area
- review when no specialized Review type applies
- targeted multilingual / translation / RTL-LTR review when visible text, translation files, API messages, Admin UI labels, validation messages, permission labels, locale/direction metadata, or RTL/LTR behavior need human review and no specialized Review type applies
- targeted configuration / settings review when config files, environment variables, Admin Settings, provider settings, secret storage, test setup, or operator manual setup need human review and no specialized Review type applies
- targeted UI / Admin UI review when Admin pages, settings pages, tables, forms, filters, actions, buttons, menus, dashboards, monitoring widgets, permission-gated UI, or sensitive UI actions need human review and no specialized Review type applies

Examples:

```text
<owner-docs-root>/reviews/release-{N}/phase-{X}/REVIEW-AI-PACK-ID.md
<owner-docs-root>/reviews/release-{N}/phase-{X}/REVIEW-RUN-ID.md
```

---

### 5.2 Consistency Review

Use template:

```text
docs/ai/templates/CONSISTENCY-REVIEW-TEMPLATE.md
```

Use when checking consistency across:

- Packs
- Run Reports
- Canonical files
- Decision Logs
- Change Requests
- Remediation Packs
- Guides
- Templates
- Rules
- Indexes
- source/reference assumptions

Use this Review type when the question is:

```text
Do these files agree with each other?
```

Examples:

```text
<owner-docs-root>/reviews/release-{N}/phase-{X}/CONSISTENCY-REVIEW-R{N}-P{X}-PACKS.md
<owner-docs-root>/reviews/release-{N}/CONSISTENCY-REVIEW-R{N}-CANONICAL-VS-PACKS.md
```

---

### 5.3 Final Review

Use template:

```text
docs/ai/templates/FINAL-REVIEW-TEMPLATE.md
```

Use when closing:

- a Phase
- a Release
- a major delivery checkpoint

A Final Review decides whether the Phase or Release can be:

- accepted
- closed
- moved forward
- blocked
- reopened
- remediated

Examples:

```text
<owner-docs-root>/reviews/release-{N}/phase-{X}/PHASE-{X}-FINAL-REVIEW.md
<owner-docs-root>/reviews/release-{N}/RELEASE-{N}-FINAL-REVIEW.md
```

---

### 5.4 Documentation Maintenance Review

Use template:

```text
docs/ai/templates/DOCS-MAINTENANCE-REVIEW-TEMPLATE.md
```

Use when reviewing:

- documentation health
- index correctness
- template/rule synchronization
- canonical/decision/guide synchronization
- global file release-neutrality and phase-neutrality
- source/reference validity
- graph/onboarding/reference drift
- archive/reference leakage
- periodic documentation maintenance
- source/framework/project update impact

Examples:

```text
<owner-docs-root>/reviews/docs-maintenance/DOCS-MAINTENANCE-REVIEW-YYYY-MM.md
<owner-review-root>/source-updates/SOURCE-UPDATE-REVIEW-FRAMEWORK-YYYY-MM-DD.md
<owner-review-root>/source-updates/SOURCE-UPDATE-REVIEW-PROJECT-YYYY-MM-DD.md
```

---

## 6. Source Update Review Rule

A Source Update Review is required when any source/reference update may affect AI assumptions, implementation conventions, architecture, or generated documentation.

Triggers include:

- framework update
- project/platform update
- dependency or runtime version update
- major dependency update
- plugin architecture change
- database schema change
- queue/config/auth/permission structure change
- provider integration change
- source code refactor affecting the active owner
- regenerated source graph
- regenerated domain graph
- regenerated onboarding files
- regenerated project analysis
- imported new source documentation
- overwritten source documentation
- source docs category/read policy changes

Source Update Reviews must use:

```text
docs/ai/templates/SOURCE-UPDATE-REVIEW-TEMPLATE.md
```

---

## 7. Source Update Safety Rule

Before importing, regenerating, or overwriting source documentation, graph files, onboarding files, or analysis files, the repository working tree must be checked.

Run or inspect:

```bash
git status
git diff --name-only
```

If the current source/reference files are not committed or otherwise recoverable, do not overwrite them unless the operator explicitly approves.

Source documentation, graph files, onboarding files, and analysis files may be overwritten only when one of these is true:

- the previous version is already committed in git
- a backup/archive copy has been created
- the operator explicitly approves overwriting without a recoverable previous version

After overwrite/import/regeneration, review the diff before updating dependent AI documentation.

If the previous version is not recoverable, the Source Update Review must state:

```text
Previous source snapshot not available.
Exact diff could not be verified.
Review performed against the newly imported source/reference files only.
```

---

## 8. Source Update Review Workflow

When a Source Update Review is required, follow this workflow:

1. Check working tree status before importing, regenerating, or overwriting source/reference files.
2. Confirm that the previous source/reference version is recoverable through git, archive, backup, or explicit operator approval.
3. Import, regenerate, or overwrite the source/reference files only after the safety rule is satisfied.
4. Review the source diff, generated diff, or update notes.
5. Create or update a Source Update Review under `<owner-docs-root>/reviews/`.
6. Identify affected areas.
7. Validate whether `PROJECT-ANALYSIS-SUMMARY.md` and `PROJECT-ANALYSIS-FULL.md` are still reliable.
8. Decide whether graph files, domain graph files, onboarding files, or project analysis files must be regenerated.
9. Check `SOURCE-DOCS-INDEX.md` and `REFERENCES-INDEX.md`.
10. Check affected canonical files.
11. Check affected guides.
12. Check pending Packs in affected releases/phases.
13. Check previous Run Reports and Reviews if previous execution may be invalidated.
14. Create or update a Decision Log entry if the source update changes or challenges accepted behavior.
15. Create Change Requests if accepted behavior, scope, or canonical contracts must change.
16. Create Remediation Packs if previous execution is invalidated or controlled correction is required.
17. Record the result in the Source Update Review.
18. Update `REVIEWS-INDEX.md`.
19. Update other affected indexes according to the Review findings and `DOCUMENT-MAINTENANCE-RULES.md`.

## 9. Source Update Review Required Content

A Source Update Review must include at least:

```text
Source Update Type:
Previous Version Recoverable:
Diff Reviewed:
Affected Areas:
Reference Files Affected:
Source Docs Index Impact:
Graph Files Impact:
Project Analysis Reliability:
Canonical Impact:
Guide Impact:
Pending Pack Impact:
Previous Execution Impact:
Decision Required:
Change Request Required:
Remediation Required:
Required Follow-up Updates:
Review Decision:
```

### 9.1 Source Update Type

Use one or more:

```text
framework update
project/platform update
dependency or runtime update
dependency update
source code refactor
source documentation import
source documentation overwrite
graph regeneration
domain graph regeneration
onboarding regeneration
project analysis regeneration
provider integration change
database/schema change
queue/config/auth/permission change
```

### 9.2 Previous Version Recoverable

Use:

```text
Yes — via git
Yes — via archive/backup
No
Not verified
```

### 9.3 Affected Areas

Examples:

```text
plugin architecture
routes
permissions
forms/tables
admin UI
theme integration
queue
config
auth
provider integration
database schema
owner-specific assumptions
source documentation categories
graph-derived understanding
project analysis conclusions
```

### 9.4 Project Analysis Reliability

State whether these files remain reliable:

```text
docs/project/references/PROJECT-ANALYSIS-SUMMARY.md
docs/project/references/PROJECT-ANALYSIS-FULL.md
```

Use:

```text
Reliable
Partially stale
Stale
Requires regeneration
Not assessed
```

### 9.5 Graph Files Impact

State whether graph files must be regenerated.

Check:

```text
docs/project/references/GRAPH-FILES-INDEX.md
docs/project/references/graphs/
docs/project/references/onboarding/
```

### 9.6 Source Docs Index Impact

Check:

```text
docs/project/references/SOURCE-DOCS-INDEX.md
docs/project/references/REFERENCES-INDEX.md
```

### 9.7 Canonical and Guide Impact

Check affected:

```text
<owner-docs-root>/canonical/
<owner-docs-root>/guides/
```

Source updates must not automatically change canonical decisions.

If source changes challenge accepted behavior, create or update a Decision Log entry first.

Then, if required, create a Change Request or Canonical update according to the relevant rules.

### 9.8 Pack and Execution Impact

Check:

```text
<owner-docs-root>/packs/
<owner-docs-root>/runs/
<owner-docs-root>/reviews/
<owner-docs-root>/changes/
<owner-docs-root>/remediations/
```

Classify pending Packs as:

```text
unaffected
needs-review
needs-normalization
blocked
superseded
```

Classify previous executions as:

```text
unaffected
needs-review
invalidated
requires-remediation
```

---

## 10. Review Storage Rule

Reviews must be stored under:

```text
<owner-docs-root>/reviews/
```

Recommended structure:

```text
<owner-docs-root>/reviews/
  REVIEWS-INDEX.md
  release-{N}/
    RELEASE-{N}-FINAL-REVIEW.md
    phase-{X}/
      PHASE-{X}-FINAL-REVIEW.md
      REVIEW-...
      CONSISTENCY-REVIEW-...
  docs-maintenance/
    DOCS-MAINTENANCE-REVIEW-YYYY-MM.md
  source-updates/
    SOURCE-UPDATE-REVIEW-...
```

Release-specific reviews must live under the relevant release folder.

Phase-specific reviews must live under the relevant phase folder.

Global documentation maintenance reviews may live under:

```text
<owner-docs-root>/reviews/docs-maintenance/
```

Source update reviews may live under:

```text
<owner-docs-root>/reviews/source-updates/
```

If a source update affects a specific Release or Phase, the Source Update Review may either:

- live under `source-updates/` and reference affected releases/phases
- live under the affected release/phase review folder if the impact is local

Choose the location that makes future discovery easiest.

---

## 11. Review Status Rule

Use consistent Review statuses.

Recommended statuses:

```text
draft
pending
completed
accepted
rejected
blocked
needs-change
needs-remediation
superseded
archived
```

Use `completed` only when the Review is finished but does not directly accept or reject a deliverable.

Use `accepted` when the reviewed item is formally accepted.

Use `rejected` when the reviewed item is rejected.

Use `blocked` when review cannot proceed due to missing information or unresolved conflict.

Use `needs-change` when a Change Request is needed.

Use `needs-remediation` when a Remediation Pack is needed.

Use `superseded` when a newer Review replaces this Review.

Use `archived` when the Review is historical and no longer active.

If an index uses a smaller status set, map the Review status to the closest index status and record details in Notes.

---

## 12. Review Decision Rule

Every Review must end with a review decision.

Allowed review decisions:

```text
Accepted
Accepted with follow-up
Rejected
Blocked
Needs Change Request
Needs Remediation
Needs Consistency Review
Needs Documentation Maintenance
Needs Source Update Review
```

A Review decision must be supported by findings.

Do not mark a Review as accepted if there are unresolved blocking findings.

---

## 13. Review Findings Rule

Every finding must be classified.

Finding categories:

```text
scope
implementation
test
documentation
canonical
decision
change-request
remediation
consistency
source-reference
index
template
rule
security
performance
migration
multilingual
translation
rtl-ltr
hardcoded-text
api-test-artifact
postman
configuration
settings
environment
admin-settings
provider-secret
test-setup
operator-setup
ui
admin-ui
settings-ui
dashboard-ui
monitoring-ui
form
table
navigation
permission-ui
sensitive-ui-action
visibility
unknown
```

Finding severity:

```text
info
minor
major
blocking
```

Each finding should include:

```text
Finding:
Category:
Severity:
Evidence:
Affected Files:
Required Action:
Owner:
```

---

## 14. Review-Triggered Decision Rule

If a Review finding creates or confirms a non-trivial decision, record it in the relevant Decision Log first.

Examples:

- changing a canonical contract
- accepting a new Release-level behavior
- changing a Phase strategy
- approving a temporary exception
- deciding to supersede a Pack
- deciding to invalidate previous execution

Canonical updates, Change Requests, and Remediation Packs may be created from an approved Review finding, but they must not replace the Decision Log.

Decision Log entries must reference the Review that triggered them.

The Review must reference the Decision Log entry.

---

## 15. Review-Triggered Canonical Update Rule

A Review may recommend a Canonical update.

A Canonical file may be updated from a Review only when:

- the Review finding is accepted
- the operator approves the canonical change, or the Review process explicitly has canonical update scope
- the relevant Decision Log entry exists, unless an approved formal Review already acts as the decision source
- the update is made in the correct Global, Release, or Phase Canonical file

Canonical files must be updated in the relevant section, not only by appending a historical note at the end.

A short canonical note may reference the Review, Decision Log, Change Request, or Remediation Pack.

---

## 16. Review-Triggered Change Request Rule

A Review must trigger a Change Request when a finding:

- changes accepted scope
- contradicts canonical decisions
- changes an already implemented contract
- invalidates previous execution
- requires behavior change beyond a small local fix
- requires formal approval before implementation

Change Requests are stored under:

```text
<owner-docs-root>/changes/
```

Change Requests must follow:

```text
docs/ai/templates/CHANGE-REQUEST-TEMPLATE.md
```

The Change Request must reference the Review that triggered it.

The Review must reference the Change Request.

---

## 17. Review-Triggered Remediation Rule

A Review must trigger a Remediation Pack when a finding requires controlled implementation correction.

Remediation Packs are stored under:

```text
<owner-docs-root>/remediations/
```

Remediation Packs must follow:

```text
docs/ai/templates/REMEDIATION-PACK-TEMPLATE.md
```

A Remediation Pack must reference:

- the Review finding
- related Decision Log entry, if any
- related Change Request, if any
- affected Pack/Run/Canonical files

The Review must reference the Remediation Pack.

---

## 18. Review and Index Maintenance Rule

Whenever a Review is created, moved, archived, accepted, rejected, superseded, completed, or materially changed, update:

```text
<owner-docs-root>/reviews/REVIEWS-INDEX.md
```

If the Review triggers or updates other tracked files, update the related index files as required:

```text
<owner-docs-root>/decisions/DECISIONS-INDEX.md
<owner-docs-root>/changes/CHANGES-INDEX.md
<owner-docs-root>/remediations/REMEDIATIONS-INDEX.md
<owner-docs-root>/canonical/CANONICAL-INDEX.md
<owner-docs-root>/guides/GUIDES-INDEX.md
<owner-docs-root>/packs/PACKS-INDEX.md
<owner-docs-root>/runs/RUNS-INDEX.md
docs/project/references/REFERENCES-INDEX.md
docs/ai/templates/TEMPLATES-INDEX.md
```

For complete documentation maintenance rules, read:

```text
docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md
```

---

## 19. Phase Final Review Rule

A Phase Final Review is required before a Phase is considered closed.

A Phase Final Review must check:

- all planned Packs for the Phase
- Pack statuses
- Run Reports
- failed, rejected, superseded, or remediated Runs
- all Reviews in the Phase
- tests and validation results
- Phase Canonical
- Release Canonical impact
- Phase Guide
- Decision Logs
- temporary/revisit-later decisions
- open Change Requests
- open Remediation Packs
- Required Follow-up Updates
- index consistency
- readiness for next Phase
- unresolved multilingual, translation, hardcoded visible text, or RTL/LTR findings
- unresolved Postman Collection or API test artifact findings
- unresolved configuration, settings, environment variable, Admin Settings, provider secret, test setup, or operator manual setup findings
- unresolved UI, Admin UI, settings UI, dashboard UI, permission-gated UI, sensitive UI action, navigation, form, table, or visibility findings

A Phase must not be closed if blocking findings remain unresolved.

---

## 20. Release Final Review Rule

A Release Final Review is required before a Release is considered closed.

A Release Final Review must check:

- all Phase Final Reviews
- Release Canonical
- Release Guide
- all open decisions
- temporary/revisit-later decisions
- open Change Requests
- open Remediation Packs
- accepted/remediated Pack outcomes
- unresolved source/reference validity issues
- unresolved documentation maintenance findings
- readiness for next Release
- unresolved multilingual, translation, hardcoded visible text, or RTL/LTR findings across the Release
- unresolved Postman Collection or API test artifact findings across the Release
- unresolved configuration, settings, environment variable, Admin Settings, provider secret, test setup, or operator manual setup findings across the Release
- unresolved UI, Admin UI, settings UI, dashboard UI, permission-gated UI, sensitive UI action, navigation, form, table, or visibility findings across the Release

A Release must not be closed if blocking findings remain unresolved.

---

## 21. Consistency Review Rule

A Consistency Review must be used when the main concern is alignment across documents or execution records.

A Consistency Review may check:

- Pack vs Pack consistency
- Pack vs Canonical consistency
- Pack vs Template consistency
- Run Report vs Pack consistency
- Run Report vs actual committed changes consistency
- Decision Log vs Canonical consistency
- Change Request vs Remediation Pack consistency
- Remediation Pack vs affected Pack consistency
- Review findings vs follow-up updates consistency
- Guide vs Canonical consistency
- Template vs Rules consistency
- Index vs actual files consistency
- References vs source docs consistency

Consistency findings must be classified by severity.

Blocking consistency findings must trigger a Change Request, Remediation Pack, canonical update, template update, or documentation maintenance action.

---

## 22. Documentation Maintenance Review Rule

A Documentation Maintenance Review must be used when reviewing the health and reliability of the AI documentation system.

It must check:

- global files remain release-neutral and phase-neutral
- owner files are still correct
- indexes match actual files
- templates match current practice
- rules and templates do not duplicate each other
- canonical files match accepted decision logs
- guides match execution reality
- Pack statuses match Runs and Reviews
- Run Reports match actual executions
- Change Requests and Remediation Packs are linked correctly
- reference files remain valid
- source docs indexes are usable
- graph files are not read by default
- archive files are not active instructions
- Required Follow-up Updates are resolved or tracked

This Review type must follow:

```text
docs/ai/templates/DOCS-MAINTENANCE-REVIEW-TEMPLATE.md
```

---

## 23. Review Output Rule

Every Review must clearly state:

```text
Review Type:
Review Scope:
Reviewed Files:
Findings:
Decision:
Required Follow-up Updates:
Index Updates Required:
```

If a Review has no findings, it must explicitly state:

```text
No findings.
```

If a Review has no required follow-up updates, it must explicitly state:

```text
No required follow-up updates.
```

### Review Required Section Rule

All sections in the active Review template are required unless explicitly marked as optional.

The AI Agent must not remove required Review section headings.

If a required Review section is not applicable, write:

```text
Not applicable.
```

If required information is unavailable, write:

Not available.

Do not remove a required Review section because there is no content for it.

If a Review has no findings, write:

No findings.

If a Review has no conflicts, write:

No conflicts.

If a Review has no required changes, write:

No required changes.

If a Review has no required follow-up updates, write:

No required follow-up updates.

If a Review has no index updates required, write:

No index updates required.

Review templates may include more specific fallback text for specialized sections, but they must not contradict this rule.

---

## 24. When Not To Create a Review

Do not create a Review only to repeat an Agent Final Report or Run Report.

Do not create a Review for small typo-only documentation changes unless the operator requests it.

Do not create a Review when the current task only requires updating an index and no validation judgment is needed.

Do not create a Review when a simple Run Report already captures the execution and no acceptance/consistency/checkpoint decision is needed.

---

## 25. Legacy Review Rule

Legacy Reviews may remain in their original format if rewriting them would distort history.

New Reviews must follow the current relevant Review template.

If a legacy Review format differs from the current template, note this in:

```text
<owner-docs-root>/reviews/REVIEWS-INDEX.md
```

---

## 26. Related Rules

Read these rules when applicable:

| Situation | Rule |
|---|---|
| Reporting behavior | `docs/ai/rules/REPORTING-RULES.md` |
| Documentation maintenance and index updates | `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md` |
| Operator decision classification | `docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md` |
| Conflict, Change Request, and Remediation workflow | `docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md` |
| Git and commit behavior | `docs/ai/rules/GIT-AND-COMMIT-RULES.md` |
| Canonical creation and maintenance | `docs/ai/rules/CANONICAL-RULES.md` |
