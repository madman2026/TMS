# RULES-INDEX

This file is the navigation index for global AI operational rules.

Use this index to select the minimum required rule file for the current situation.

Do not read all rule files by default.

Rules are global files.
They must remain release-neutral and phase-neutral.

Release-specific or phase-specific execution decisions must not be added to rule files.
Use the relevant canonical, guide, decision, change, remediation, run, or review files instead.

---

## Rule Selection Table

| Rule File | Purpose | Read Policy |
|---|---|---|
| `docs/ai/rules/AI-EXECUTION-RULES.md` | General AI Pack execution rules, execution boundaries, implementation behavior, and Pack workflow expectations. | Read when executing an AI Pack or when execution behavior is unclear. |
| `docs/ai/rules/AI-PACK-GENERATION-RULES.md` | Rules for generating or updating AI Packs, including how to select relevant architecture constraints and avoid duplicating unrelated rules inside Packs. | Read when generating, updating, reviewing, or remediating AI Packs. |
| `docs/ai/rules/OPERATOR-WORKFLOW.md` | Human operator workflow for executing AI Packs, reviewing Agent Final Reports, checking diffs/tests, requesting fixes, creating Run Reports before commit, committing accepted work, and moving to the next Pack. | Read when the operator workflow is unclear, when coordinating AI execution with human review, or before finalizing a Pack for commit. |
| `docs/ai/rules/SCOPE-CONTROL-RULES.md` | Scope control, allowed/forbidden file changes, no opportunistic refactor, and out-of-scope handling. | Read when checking whether a file may be changed, when scope is unclear, or when an unauthorized change is detected. |
| `docs/ai/rules/GIT-AND-COMMIT-RULES.md` | Git status checks, branch behavior, diff review, commit rules, and pre-commit requirements. | Read before commit, when checking branch/diff state, or when deciding whether work is ready to commit. |
| `docs/ai/rules/TEST-AND-VALIDATION-RULES.md` | Testing, validation, failed test handling, skipped test reporting, and test evidence expectations. | Read when adding tests, running tests, reporting test results, or handling failed/skipped tests. |
| `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md` | Rules for API and UI error contracts, stable error codes, provider error normalization, structured logging, traceability, sensitive data masking, and error behavior testing. | Read when a Pack creates or modifies error handling, API errors, Admin UI error messages, exceptions, provider errors, callback errors, logs, tracing, error codes, or sensitive error reporting. |
| `docs/ai/rules/COMMENTING-RULES.md` | Reusable code-comment and documentation-comment expectations. | Read when creating or editing code that needs comments, documentation comments, or Pack-specific commenting decisions; load the owner profile for language-specific conventions. |
| `docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md` | Classification of operator answers, clarifications, implementation notes, decisions, temporary decisions, and change requests. | Read whenever the operator answers a question or gives guidance that may affect implementation, scope, docs, or future Packs. |
| `docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md` | Conflict handling, Change Request rules, remediation rules, and canonical/code mismatch handling. | Read when a conflict is found, when accepted behavior must change, or when a remediation may be required. |
| `docs/ai/rules/REPORTING-RULES.md` | Reporting rules for Agent Final Reports and Run Reports, including required sections, no-fabrication, report relationship, Run Report timing, and follow-up reporting. | Read when producing an Agent Final Report, creating a Run Report, or checking reporting requirements before commit. |
| `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md` | Documentation ownership, sync rules, index maintenance, template synchronization, source update protocol, periodic review, and documentation health checks. | Read when creating, moving, archiving, or materially changing AI documentation, or when source/reference validity is affected. |
| `docs/ai/rules/REVIEW-RULES.md` | Review creation, review type selection, review status, review findings, review-triggered decisions, Change Requests, Remediation Packs, canonical updates, Source Update Reviews, and review index maintenance. | Read when creating any Review, selecting a Review template, closing a Phase or Release, performing a consistency review, performing a documentation maintenance review, performing a Source Update Review, or handling findings from a Review. |
| `docs/ai/rules/CANONICAL-RULES.md` | Canonical file creation, maintenance, promotion, conflict handling, remediation-related canonical updates, source update impact handling, and Global/Release/Phase canonical scope rules. | Read when creating, updating, finalizing, promoting, validating, or resolving conflicts in canonical decisions. |
| `docs/ai/rules/OPERATOR-GUIDE-RULES.md` | Reusable workflow for owner-specific operator guides. | Read when a Pack changes operator-facing behavior; resolve language, paths, permissions, and assets through the owner profile. |
| `docs/ai/rules/ADMIN-USER-GUIDE-RULES.md` | Compatibility alias for historical links. | Do not use for new work; follow `OPERATOR-GUIDE-RULES.md`. |


---

## Common Situations

| Situation | Read |
|---|---|
| Starting an AI Pack execution | `AI-EXECUTION-RULES.md`, current Pack, and any rules referenced by the Pack |
| Checking whether a file can be changed | `SCOPE-CONTROL-RULES.md` |
| Preparing to commit completed Pack work | `GIT-AND-COMMIT-RULES.md`, `REPORTING-RULES.md` |
| Creating the Agent Final Report | `REPORTING-RULES.md`, `docs/ai/templates/AGENT-FINAL-REPORT-TEMPLATE.md` |
| Creating the Run Report before commit | `REPORTING-RULES.md`, `docs/ai/templates/RUN-REPORT-TEMPLATE.md` |
| Handling failed or skipped tests | `TEST-AND-VALIDATION-RULES.md` |
| Adding or reviewing documentation comments | `COMMENTING-RULES.md`, then the applicable owner profile |
| Handling operator clarification or decision | `QUESTION-ANSWER-DECISION-RULES.md` |
| Handling code/canonical conflict | `CONFLICT-CHANGE-REMEDIATION-RULES.md` |
| Creating or changing AI documentation | `DOCUMENT-MAINTENANCE-RULES.md` |
| Updating index files | `DOCUMENT-MAINTENANCE-RULES.md` |
| Updating templates or report formats | `DOCUMENT-MAINTENANCE-RULES.md`, `REPORTING-RULES.md` |
| Reviewing source/framework/project update impact | `DOCUMENT-MAINTENANCE-RULES.md`, then the applicable project profile |
| Reviewing AI output before commit or moving to the next Pack | `OPERATOR-WORKFLOW.md`, `GIT-AND-COMMIT-RULES.md`, `REPORTING-RULES.md` |
| Creating or updating canonical files | `CANONICAL-RULES.md`, `DOCUMENT-MAINTENANCE-RULES.md` |
| Finalizing Phase or Release canonical decisions | `CANONICAL-RULES.md`, `REVIEW-RULES.md`, `DOCUMENT-MAINTENANCE-RULES.md` |
| Normalizing Packs against canonical decisions | `CANONICAL-RULES.md`, `SCOPE-CONTROL-RULES.md` |
| Resolving Phase/Release canonical conflict | `CANONICAL-RULES.md`, `CONFLICT-CHANGE-REMEDIATION-RULES.md` |
| Updating canonical files after Remediation | `CANONICAL-RULES.md`, `CONFLICT-CHANGE-REMEDIATION-RULES.md`, `REPORTING-RULES.md` |
| Checking source update impact on canonical decisions | `CANONICAL-RULES.md`, `REVIEW-RULES.md` |
| Generating a new AI Pack | `AI-PACK-GENERATION-RULES.md`, `docs/ai/templates/AI-PACK-TEMPLATE.md`, relevant canonical files, relevant guide files |
| Updating or remediating an existing AI Pack | `AI-PACK-GENERATION-RULES.md`, `DOCUMENT-MAINTENANCE-RULES.md`, relevant canonical files, relevant Change Request or Remediation Pack |
| Selecting architecture constraints for an AI Pack | `AI-PACK-GENERATION-RULES.md`, the selected owner's global Canonical file |
| Creating or updating an owner-specific operator guide | applicable owner profile, then its declared guide rules/template |
| Designing, implementing, or reviewing error handling and logging | `ERROR-HANDLING-AND-LOGGING-RULES.md`, `TEST-AND-VALIDATION-RULES.md` |

---

## Rule Ownership Notes

Rule files own behavior, not templates.

Templates own structure, headings, placeholders, and section-level output format.

Examples:

- Reporting behavior is owned by `docs/ai/rules/REPORTING-RULES.md`.
- Agent Final Report structure is owned by `docs/ai/templates/AGENT-FINAL-REPORT-TEMPLATE.md`.
- Run Report structure is owned by `docs/ai/templates/RUN-REPORT-TEMPLATE.md`.
- Documentation maintenance behavior is owned by `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`.
- AI Pack generation behavior is owned by `docs/ai/rules/AI-PACK-GENERATION-RULES.md`.
- AI Pack structure is owned by `docs/ai/templates/AI-PACK-TEMPLATE.md`.
- Owner-specific operator-guide behavior and structure are resolved through the applicable profile. Current transitional paths remain authoritative until their migration Pack is accepted.
- Error handling, error reporting, logging, and traceability behavior is owned by `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md`.


Do not duplicate full templates inside rule files.

Do not duplicate full rules inside templates.

---

## Maintenance

Update this index when:

- a new rule file is added
- a rule file is renamed, moved, archived, or removed
- a rule file's purpose changes materially
- the read policy for a rule changes
- a new common situation requires a specific rule routing entry

Do not update this index for minor wording, typo, or formatting changes inside a rule file if the file path, purpose, and read policy did not change.

For complete documentation maintenance rules, read:

```text
docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md
```
