# SOURCE-UPDATE-REVIEW-TEMPLATE

Use this template for Source Update Reviews.

A Source Update Review evaluates whether updates to a framework, application, language/runtime, dependencies, source documentation, graph files, onboarding files, project analysis, or source/reference assumptions affect AI documentation, canonical decisions, guides, pending Packs, previous executions, or implementation assumptions.

For Review rules, source update safety rules, review-triggered decisions, Change Requests, Remediation Packs, canonical updates, and index maintenance, read:

```text
docs/ai/rules/REVIEW-RULES.md
```

For documentation maintenance and index synchronization rules, read:

```text
docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md
```

---

# Source Update Review — [SOURCE / DATE]

## 1. Review Metadata

```text
Review ID:
Review Type: source-update-review
Review Title:
Created Date:
Created By:
Reviewed By:
Review Status:
Review Path:
```

Recommended `Review Status` values:

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

---

## 2. Update Trigger

```text
Trigger:
Source / Component:
Old Version / Snapshot:
New Version / Snapshot:
Update Method:
Update Scope:
```

Examples for `Trigger`:

```text
framework or CMS update
application or theme update
language/runtime update
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

Examples for `Update Method`:

```text
manual file replacement
vendor update
git merge
dependency-manager update
source docs import
graph regeneration command
analysis regeneration
operator-provided source package
```

---

## 3. Source Update Safety Check

Use this section before and after importing, regenerating, or overwriting source/reference files.

```text
Working Tree Checked Before Update: Yes / No
Command / Method Used:
Previous Version Recoverable:
Recovery Method:
Operator Approval Needed Before Overwrite: Yes / No
Operator Approval Received: Yes / No / Not applicable
```

Recommended values for `Previous Version Recoverable`:

```text
Yes — via git
Yes — via archive/backup
No
Not verified
```

If the previous version is not recoverable, state:

```text
Previous source snapshot not available.
Exact diff could not be verified.
Review performed against the newly imported source/reference files only.
```

---

## 4. Diff / Update Evidence

```text
Diff Reviewed: Yes / No
Update Notes Reviewed: Yes / No
Generated Output Reviewed: Yes / No
Evidence Summary:
```

Relevant commands or sources:

```text
git status
git diff --name-only
git diff --stat
git diff -- <path>
dependency-manager inventory
dependency-manager outdated check
source update notes
generated graph summary
generated analysis summary
```

List reviewed evidence:

```text
- Evidence:
  Source / Command:
  Result:
  Notes:
```

If no diff or update notes were available, write:

```text
Diff / update notes not available.
```

---

## 5. Updated Files / Areas

List files, folders, or source areas that were imported, regenerated, overwritten, or materially changed.

```text
Updated Source / Reference Areas:
- `path/to/file-or-folder` — update summary

Regenerated Files:
- `path/to/file` — generation source / command

Overwritten Files:
- `path/to/file` — previous version recoverable: Yes / No

New Files:
- `path/to/file` — reason

Deleted Files:
- `path/to/file` — reason
```

If none apply, write:

```text
No updated source/reference files identified.
```

---

## 6. Affected Technical Areas

Mark affected areas.

```text
Plugin architecture:
Routes:
Permissions:
Forms / Tables:
Admin UI:
Theme integration:
Queue:
Config:
Auth:
Provider integration:
Database schema:
Migrations:
Selected-owner assumptions:
Source documentation categories:
Graph-derived understanding:
Project analysis conclusions:
Other:
```

Use one of:

```text
unaffected
affected
check required
unknown
```

Add notes:

```text
Affected Area Notes:
- Area:
  Impact:
  Evidence:
```

---

## 7. Reference Files Affected

Check reference files and indexes.

```text
SOURCE-DOCS-INDEX.md:
REFERENCES-INDEX.md:
GRAPH-FILES-INDEX.md:
PROJECT-ANALYSIS-SUMMARY.md:
PROJECT-ANALYSIS-FULL.md:
Onboarding files:
Source docs folders:
Other reference files:
```

For each affected reference, use:

```text
unaffected
needs-review
needs-update
stale
requires-regeneration
not assessed
```

Notes:

```text
- Reference:
  Status:
  Required Action:
```

---

## 8. Source Docs Index Impact

```text
SOURCE-DOCS-INDEX.md update required: Yes / No
REFERENCES-INDEX.md update required: Yes / No
Source documentation categories changed: Yes / No
Read policy changed: Yes / No
New source documentation category needed: Yes / No
Reason:
```

Required updates:

```text
- Required Update:
  Owner File:
  Reason:
```

If no update is required, write:

```text
No source docs index updates required.
```

---

## 9. Graph Files Impact

```text
Graph regeneration required: Yes / No
Domain graph regeneration required: Yes / No
Onboarding regeneration required: Yes / No
Affected Graphs:
Reason:
```

Check:

```text
<project-docs-root>/references/GRAPH-FILES-INDEX.md
<project-docs-root>/references/graphs/
<project-docs-root>/references/onboarding/
```

If graph regeneration is required, list:

```text
- Graph / Onboarding File:
  Required Action:
  Reason:
```

If no graph impact exists, write:

```text
No graph or onboarding regeneration required.
```

---

## 10. Project Analysis Reliability

State whether existing project analysis remains reliable.

```text
PROJECT-ANALYSIS-SUMMARY.md:
PROJECT-ANALYSIS-FULL.md:
Reliability Status:
Reason:
```

Allowed `Reliability Status` values:

```text
Reliable
Partially stale
Stale
Requires regeneration
Not assessed
```

If partially stale or stale, specify:

```text
Stale Areas:
- Area:
  Reason:
  Required Action:
```

---

## 11. Canonical Impact

Source updates must not automatically change canonical decisions.

Check whether the update challenges accepted canonical behavior.

```text
Canonical Impact:
Affected Canonical Files:
Decision Required:
Canonical Update Required:
Reason:
```

Allowed `Canonical Impact` values:

```text
None
Check required
Update required
Conflict found
Not assessed
```

If canonical update is required, list:

```text
- Canonical File:
  Existing Decision / Contract:
  Source Update Impact:
  Required Decision Log Entry:
  Required Canonical Update:
```

If source changes challenge accepted behavior, create or update a Decision Log entry first.

---

## 12. Guide Impact

```text
Guide Impact:
Affected Guide Files:
Update Required:
Reason:
```

Allowed values:

```text
None
Check required
Update required
Not assessed
```

List affected guides:

```text
- Guide File:
  Impact:
  Required Action:
```

---

## 13. Pending Pack Impact

Check pending Packs in affected releases/phases.

```text
Pending Pack Impact:
Affected Releases:
Affected Phases:
Affected Packs:
Required Action:
```

Allowed classifications:

```text
unaffected
needs-review
needs-normalization
blocked
superseded
not assessed
```

List affected Packs:

```text
- Pack:
  Status:
  Impact:
  Required Action:
```

If no pending Pack is affected, write:

```text
No pending Packs affected.
```

---

## 14. Previous Execution Impact

Check whether previous Pack executions, Run Reports, Reviews, accepted outputs, or Remediation results are invalidated.

```text
Previous Execution Impact:
Affected Runs:
Affected Reviews:
Affected Accepted Outputs:
Required Action:
```

Allowed classifications:

```text
unaffected
needs-review
invalidated
requires-remediation
not assessed
```

List affected executions:

```text
- Run / Review / Output:
  Impact:
  Required Action:
```

If no previous execution is affected, write:

```text
No previous executions affected.
```

---

## 15. Decisions / Changes / Remediations

```text
Decision Required: Yes / No
Decision Log Entry:
Change Request Required: Yes / No
Change Request:
Remediation Required: Yes / No
Remediation Pack:
Canonical Update Required: Yes / No
```

If a decision is required:

```text
Decision Summary:
Decision Scope:
Suggested Decision Log:
```

If a Change Request is required:

```text
Change Request Summary:
Affected Scope:
Suggested Change Request Path:
```

If a Remediation Pack is required:

```text
Remediation Summary:
Affected Files / Areas:
Suggested Remediation Pack Path:
```

---

## 16. Required Actions

Use this section to consolidate required actions.

```text
- Required Action:
  Type:
  Reason:
  Owner File:
  Suggested Next Step:
```

Recommended action types:

```text
Decision Log
Change Request
Remediation Pack
Regenerate graph
Regenerate onboarding
Regenerate project analysis
Update SOURCE-DOCS-INDEX.md
Update REFERENCES-INDEX.md
Update GRAPH-FILES-INDEX.md
Update canonical
Update guide
Normalize pending Packs
Review previous Runs
Review previous Reviews
No action
```

If no required action exists, write:

```text
No required actions.
```

---

## 17. Findings

Every finding must be classified.

```text
- Finding:
  Category:
  Severity:
  Evidence:
  Affected Files:
  Required Action:
  Owner:
```

Recommended categories:

```text
source-reference
project-analysis
graph
onboarding
canonical
guide
pack
run
review
decision
change-request
remediation
index
template
rule
implementation
security
migration
unknown
```

Recommended severities:

```text
info
minor
major
blocking
```

If there are no findings, write:

```text
No findings.
```

---

## 18. Review Decision

```text
Review Decision:
Reason:
Can continue current Pack execution: Yes / No
Can continue current Phase execution: Yes / No
Can continue current Release execution: Yes / No
Blocking Issues:
```

Allowed decisions:

```text
Accepted
Accepted with follow-up
Blocked
Needs Change Request
Needs Remediation
Needs Documentation Maintenance
Needs Consistency Review
Needs Source Update Review
Rejected
```

Do not mark the review as accepted if unresolved blocking findings remain.

---

## 19. Required Follow-up Updates

```text
- Required Update:
  Reason:
  Owner File:
  Next Action:
```

If there are no required follow-up updates, write:

```text
No required follow-up updates.
```

---

## 20. Index Updates

Record index updates completed or required.

```text
REVIEWS-INDEX.md:
REFERENCES-INDEX.md:
SOURCE-DOCS-INDEX.md:
GRAPH-FILES-INDEX.md:
CANONICAL-INDEX.md:
GUIDES-INDEX.md:
PACKS-INDEX.md:
RUNS-INDEX.md:
DECISIONS-INDEX.md:
CHANGES-INDEX.md:
REMEDIATIONS-INDEX.md:
TEMPLATES-INDEX.md:
AI-DOCS-INDEX.md:
```

Use:

```text
updated
not required
required but not performed
not assessed
```

If an index update is required but not performed, list it under `Required Follow-up Updates`.

---

## 21. Related Records

```text
Related Reviews:
Related Decisions:
Related Change Requests:
Related Remediation Packs:
Related Canonical Files:
Related Guides:
Related Packs:
Related Run Reports:
Related Commits:
Related Branches:
```

If none are related, write:

```text
No related records.
```

---

## 22. Notes

```text
...
```
