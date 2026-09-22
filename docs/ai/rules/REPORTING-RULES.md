# REPORTING-RULES

This file defines reporting rules for AI Pack execution.

It covers:

- Agent Final Report
- Run Report
- the relationship between chat-facing execution summaries and repository-stored audit records

This file owns reporting rules.

Report templates own report structure, headings, placeholders, and section-level output format.

---
## 1. Report Types

### Agent Final Report

Every executed AI Pack must end with an Agent Final Report.

The Agent Final Report is the operator-facing execution summary shown in chat after an AI Pack execution.

It must follow:

```text
docs/ai/templates/AGENT-FINAL-REPORT-TEMPLATE.md
```

The Agent Final Report helps the operator quickly review:

- what was done
- which files changed
- which tests were added or run
- whether scope was respected
- whether human review is needed
- whether documentation maintenance or follow-up updates are required

The Agent Final Report is not stored as a separate source-of-truth file.

---

### Run Report

The Run Report is the final persistent execution record for the completed Pack run.

It must follow:

```text
docs/ai/templates/RUN-REPORT-TEMPLATE.md
```

Run Reports are stored under:

```text
<owner-docs-root>/runs/
```

The Run Report exists for:

- audit history
- phase/release reviews
- future AI context
- troubleshooting
- remediation traceability
- execution status tracking

---

## 2. Run Report Creation Rule

The Agent Final Report must be produced after Pack implementation and required post-execution operator gate approval, before final operator acceptance review.

A Run Report must not be created immediately after the first Agent Final Report if the operator may still request fixes, clarifications, test corrections, scope corrections, or documentation updates.

Create the Run Report only after:

- the Pack implementation is complete
- required fixes from the operator have been applied
- required tests have been run or explicitly marked as not run
- scope compliance has been checked
- documentation maintenance has been checked
- required index updates have been completed or recorded as follow-up
- the operator is ready to commit or move to the next Pack

The Run Report must be created before the commit for that Pack, unless the operator explicitly says not to create a persistent Run Report.

If a Run Report is not created before commit, the Agent Final Report must state whether a Run Report is required as a follow-up update.

---

## 3. Relationship Between Agent Final Report and Run Report

The Agent Final Report is the chat-facing summary for the operator.

The Run Report is the repository-stored audit record.

The Agent Final Report is not stored as a separate source-of-truth file.

Do not copy the full Agent Final Report into the Run Report.

If a Run Report is created, any decision-relevant item from the Agent Final Report must be reflected in the appropriate Run Report section.

Decision-relevant items include:

- scope violations
- governance or maintenance files changed outside Pack implementation scope
- unauthorized out-of-scope files changed
- failed or skipped tests
- operator answers or decisions
- assumptions
- deviations from the original Pack
- canonical conflicts
- documentation maintenance findings
- index updates required but not performed
- Change Request or Remediation needs
- human review requirements
- required follow-up updates
- multilingual, translation, hardcoded text, or RTL/LTR issues
- owner-declared API test artifact updates required but not performed
- configuration, settings, environment variable, Admin Settings, external-service secret, test setup, or operator manual setup issues
- UI, Admin UI, settings UI, dashboard UI, permission-gated UI, or sensitive UI action issues

The Run Report may include additional audit metadata, stable references, lifecycle status, and traceability fields that are not required in the chat-facing Agent Final Report.

---

## 4. Agent Final Report Verbosity Rule

The Agent Final Report is the operator-facing chat summary.

By default, the Agent Final Report should be concise and should focus on the information the operator needs to review the completed Pack quickly.

The Agent Final Report may use a summary-first format when the detailed audit information will be recorded in the Run Report.

Sections or fields marked as optional in `AGENT-FINAL-REPORT-TEMPLATE.md` may be omitted from the default chat response when they are not decision-relevant, not blocking, and not needed for operator review.

If the operator asks for the full Agent Final Report, expanded details, optional fields, or a specific omitted section, the Agent must provide the requested optional details in chat.

Optional fields must not hide or omit:

* failed tests
* skipped required tests
* unauthorized or out-of-scope changes
* canonical conflicts
* operator decisions
* assumptions that affect implementation or future Packs
* deviations from the Pack
* documentation maintenance issues
* required index updates not performed
* Change Request or Remediation needs
* human review requirements
* blocking issues
* required follow-up updates
* governance or maintenance files changed
* multilingual, translation, hardcoded visible text, or RTL/LTR issues
* owner-declared API test artifact issues when API behavior changed
* configuration, settings, environment variable, Admin Settings, external-service secret, test setup, or operator manual setup issues
* UI, Admin UI, settings UI, dashboard UI, permission-gated UI, or sensitive UI action issues

If any optional section contains decision-relevant, blocking, or follow-up information, that section becomes required for the Agent Final Report.

The Run Report remains the persistent full audit record and must include all required execution, traceability, maintenance, and follow-up information according to `RUN-REPORT-TEMPLATE.md`.

---

## 5. Required Section Rule

All sections in the active report template are required unless explicitly marked as optional.

The AI Agent must not remove required section headings.

If a section is not applicable, write:

```text
Not applicable.
```

If required information is unavailable, write:

```text
Not available.
```

Do not remove a required section because there is no content for it.

For Agent Final Reports, optional sections may be omitted from the default chat-facing summary only when the Agent Final Report Verbosity Rule allows it.

For Run Reports, required audit sections must not be omitted unless the active Run Report template explicitly marks them as optional.

---

## 6. No Fabrication Rule

The AI Agent must not invent:

- execution results
- test outcomes
- changed files
- created files
- deleted files
- commands run
- approvals
- commits
- review decisions
- operator decisions
- accepted/rejected status
- remediation status
- index updates
- documentation maintenance results

Unknown information must be marked as:

```text
Not available.
```

Information that does not apply must be marked as:

```text
Not applicable.
```

---

## 7. Scope Reporting Rule

Reports must explicitly distinguish between:

* implementation files changed inside the Pack scope
* governance or maintenance files changed under the Governance Maintenance Exception
* unauthorized out-of-scope files changed outside both Pack scope and allowed governance maintenance

Governance and maintenance files required by active AI documentation rules are not unauthorized scope violations when they are directly related to the current Pack and are reported clearly.

If unauthorized or out-of-scope changes happened, the report must:

* list the files
* explain the reason
* mark human review as required
* record the issue under Scope Compliance
* record follow-up action if needed

The AI Agent must not hide out-of-scope changes.

### Governance Maintenance Reporting Rule

If governance or maintenance files were changed outside the Pack implementation scope, the report must list them separately.

Examples include:

* INDEX files
* Run Reports
* Review files
* Decision Logs
* Change Requests
* Remediation Packs
* Canonical files updated by approved decision/change/remediation/review
* template or rule indexes updated because a tracked file changed

Each governance or maintenance file must include:

```text
File:
Reason:
Required By:
Related Pack / Run / Review / Decision / Change / Remediation:
```

Governance and maintenance updates must not be counted as unauthorized scope violations if they satisfy `docs/ai/rules/SCOPE-CONTROL-RULES.md`.

If the governance or maintenance update was required but not performed, record it under:

```text
Required Follow-up Updates
```
---

## 8. Test Reporting Rule

Reports must distinguish between:

- tests added
- tests run
- test results
- tests not run

If tests were not run, the report must explain why.

If tests failed, the report must include:

- failure summary
- relevant evidence or command output summary
- required fix or follow-up

The AI Agent must not claim tests passed unless they were actually run and passed.

### Follow-up Fix Test Reporting Rule

If tests fail in an Agent Final Report and the issue is fixed later in the same execution chat before the Run Report is created, the Run Report must record both the initial failed test result and the final validation result.

The Run Report must not hide the initial failure.

The final Run Report status must reflect the final validated state.

If the fix was applied and the relevant tests were re-run successfully, the Run Report may mark the final test status as passed, but it must also record the initial failure and the fix summary.

If the fix was applied but the relevant tests were not re-run, the Run Report must mark the test status as not fully validated and list the missing validation under `Required Follow-up Updates` or `Blocking Issues`.

If the failure remains unresolved, the Run Report must mark the test status as failed and the run must not be marked ready for acceptance.

---

## 9. Error Handling / Logging / Traceability Reporting Rule

When an AI Pack creates, modifies, removes, validates, or depends on error handling, error reporting, API error contracts, Admin UI error behavior, exception handling, external-error normalization, structured logging, traceability, error classification, or sensitive-data masking, the Agent Final Report and Run Report must record the relevant impact and validation results.

Reports must follow:

```text
docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md
docs/ai/rules/TEST-AND-VALIDATION-RULES.md
```

The Agent Final Report must use the relevant section from:

```text
docs/ai/templates/AGENT-FINAL-REPORT-TEMPLATE.md
```

The Run Report must use the relevant verification section from:

```text
docs/ai/templates/RUN-REPORT-TEMPLATE.md
```

### Required Reporting Triggers

The Error Handling / Logging / Traceability report section becomes required when any of these conditions is true:

```text
- API error behavior was created or changed
- validation error behavior was created or changed
- authentication or authorization error behavior was created or changed
- Admin UI error messages were created or changed
- exception classes or exception handling were created or changed
- external-error normalization was created or changed
- callback error handling was created or changed
- queue, Job, retry, dead-letter, or status-transition failure behavior was created or changed
- stable error codes were added, changed, reused, or deprecated
- structured logs were added or changed
- request, correlation, domain-record, operation, inbound-event, or external-reference identifiers were added or changed
- retryable, permanent, or admin-action-required classification was added or changed
- sensitive-data masking, redaction, omission, encryption, or visibility behavior was added or changed
- required error-specific tests failed, were skipped, or were not run
- unresolved or follow-up error-handling work exists
```

If none of these conditions apply, the concise Agent Final Report may state:

```text
No error handling, logging, or traceability impact.
```

The persistent Run Report must still explicitly record whether impact existed when the active Run Report template requires that field.

### No Fabrication and Evidence Rule

Reports must not claim that error handling, logging, traceability, masking, or classification was verified unless the related implementation, test, command output, API response, UI behavior, database state, or log context was actually inspected.

Do not write:

```text
Error handling verified: Yes
Logging verified: Yes
Traceability verified: Yes
Sensitive data masking verified: Yes
```

based only on:

```text
- code was added
- the application returned a successful status
- one generic test passed
- a log method exists
- a trace field exists in one layer
- no visible exception occurred
```

Verification must be tied to evidence.

Evidence may include:

```text
- test name and result
- validation command and exit code
- inspected API response contract
- inspected Admin UI behavior
- database assertion
- structured log assertion
- trace propagation assertion
- sensitive-data absence or masking assertion
- external-error normalization assertion
- state-transition assertion
```

### Error Scenario Reporting Rule

Reports must list meaningful error scenarios that were introduced or changed.

For each scenario, record applicable information:

```text
- scenario
- trigger
- layer or component
- stable error_code
- HTTP status when applicable
- retryable classification
- permanent classification
- admin action requirement
- message or attempt status impact
- log event
- trace identifiers
- sensitive-data handling
```

Reports must not use vague summaries such as:

```text
Errors handled.
Logging improved.
External-integration errors fixed.
Traceability added.
```

without identifying the actual behavior.

### Error Code Reporting Rule

When error codes are added, changed, reused, or deprecated, reports must identify them explicitly.

Record:

```text
- error_code
- change type
- category
- where it is used
- HTTP status when applicable
- retryable/permanent behavior
- admin-action-required behavior
- translation behavior
- status impact
```

Human-readable messages may be summarized.

Do not copy long translated message catalogs into reports.

Stable error codes must be reported exactly.

### API Error Contract Reporting Rule

When an API error contract changes, reports must distinguish between:

```text
- contract implemented
- contract inspected
- contract tested
- contract not tested
```

A report must not mark the API error contract as verified when only the HTTP status code was checked.

Evidence should include applicable checks:

```text
- response status
- stable error_code
- response shape
- required fields
- field-level validation structure
- request_id or trace identifier
- absence of forbidden sensitive details
```

### UI / Admin UI Error Reporting Rule

When operator-facing error behavior changes, the report must state whether the following were implemented or verified:

```text
- clear explanation of the failure
- suggested operator action
- retry guidance
- admin-action-required guidance
- error_code visibility when required
- trace identifier visibility when required
- raw exception suppression
- stack-trace suppression
- raw external-payload suppression
- sensitive-data masking
- translation usage
- permission-gated technical details
```

If human UI inspection is required, the report must say so explicitly.

Do not claim UI behavior passed human review unless the operator actually reviewed it.

### Exception Handling Reporting Rule

When exception behavior changes, reports must identify:

```text
- exception class
- where it is thrown
- where it is handled
- mapped error_code
- observable result
- log behavior
- retry/failure behavior
- whether raw exception text is exposed
```

If an exception is intentionally allowed to reach the approved framework or queue failure mechanism, the report must state that explicitly.

Exceptions must not be reported as handled if they are silently swallowed without an observable result.

### External Error Normalization Reporting Rule

When external-integration error behavior changes, reports must identify applicable mappings between:

```text
External error
→ stable internal error_code
→ retryable/permanent classification
→ admin action requirement
→ message/attempt status
```

The report must state whether:

```text
- a fake, mock, stub, or safe fixture was used
- a real external service was called
- raw external text was stored or exposed
- external-service-sensitive data was masked or omitted
```

The Agent must not imply that a real external call occurred if only a fake or mock was used.

### Structured Logging Reporting Rule

When logging changes, reports must distinguish between:

```text
- log code added
- log event inspected
- log context verified
- log behavior tested
```

A report must not mark structured logging as verified merely because a `Log::error()`, `Log::warning()`, logger call, or logging statement exists.

Verification must include applicable fields such as:

```text
- stable event name
- error_code
- request_id or correlation_id
- domain-record identifiers
- operation identifiers
- inbound-event identifiers
- external-reference identifiers
- source identifiers
- retryable classification
- admin_action_required classification
- exception class
- masked or omitted sensitive fields
```

Only context fields relevant to the current Pack must be reported.

### Traceability Reporting Rule

When traceability changes, reports must describe the verified identifier flow.

Applicable flow:

```text
entry point
→ persisted request/message
→ queued Job
→ operation attempt
→ external call
→ callback or inbound event
→ report
→ Admin UI
→ logs
```

The report must distinguish between:

```text
- identifier created
- identifier propagated
- identifier stored
- identifier returned
- identifier logged
- identifier shown to operator
```

A field existing independently in multiple layers does not prove end-to-end traceability.

The same logical identifier or documented correlation relationship must connect the flow.

### Sensitive Data Reporting Rule

Reports must not include real sensitive values while describing masking or exposure checks.

Never include raw:

```text
- external-service API keys
- external-service secrets
- service tokens
- callback tokens
- webhook secrets
- signing keys
- Authorization headers
- credentials
- secret_json
- sensitive target or personal identifiers
- unsafe raw external payloads
```

Use safe labels such as:

```text
External-service API key was checked and remained redacted.
Authorization header was not present in the API response.
Sensitive target identifier was masked.
```

If sensitive exposure was found, the report must record:

```text
- exposure location
- sensitive data category
- severity
- fix applied
- re-validation result
- required follow-up
```

Do not copy the sensitive value itself into the report.

### Error Test Reporting Rule

Reports must distinguish between:

```text
- error-specific tests added
- error-specific tests run
- behavior verified
- tests failed
- tests not run
- false-positive risk reviewed
```

For important tests, report what behavior was proven.

Examples:

```text
- stable error_code verified
- response contract verified
- state transition verified
- retryable/permanent classification verified
- external-error normalization verified
- structured log context verified
- trace propagation verified
- sensitive-data masking verified
```

Do not describe a test as complete merely because it checked an HTTP status code or expected an exception.

### Initial Failure and Final Validation Rule

If an error-related test initially fails and is fixed later in the same execution, the Run Report must record:

```text
- initial failure
- root cause
- fix applied
- final test or validation result
```

The initial failure must not be removed from execution history.

If the fix was applied but the relevant error test was not rerun, the final state must be reported as:

```text
Not fully validated.
```

### Unresolved Error-handling Work Rule

Reports must explicitly list unresolved issues such as:

```text
- vague UI/API messages remain
- missing stable error_code
- missing external-error mapping
- missing retry/permanent classification
- missing log context
- broken or incomplete trace propagation
- sensitive-data visibility not verified
- required error tests not run
- unsupported error scenario
- human UI review still required
```

If unresolved work is blocking acceptance, the report must mark it as blocking.

The Agent must not hide unresolved Error Handling work inside a generic note.

### Required Follow-up Rule

When required Error Handling, Logging, Traceability, testing, translation, UI, API, security, documentation, or operator-review work was not completed, record it under:

```text
Required Follow-up Updates
```

Follow-up entries should state:

```text
- required work
- reason
- affected area
- blocking or non-blocking status
- expected owner or next Pack when known
```

## 9. Operator Answer and Decision Reporting Rule

If the operator answered clarification questions or made decisions during execution, the report must capture them.

Operator answers must be classified according to:

```text
docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md
```

The report must state whether the answer was recorded in:

- the current report only
- a Decision Log
- a Canonical file
- a Change Request
- a Remediation Pack
- required follow-up updates

The AI Agent must not silently treat operator answers as canonical decisions unless the decision rules allow it.

---

## 10. Canonical Decision Reporting Rule

If canonical decisions guided the execution, the report must list the relevant canonical files and decisions.

If a conflict with canonical decisions was found, the report must:

- state that a conflict was found
- describe the conflict
- avoid silently choosing one side
- refer to the conflict/change/remediation workflow

Relevant rule:

```text
docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md
```

---

## 11. Assumption Reporting Rule

If the AI Agent made assumptions during execution, the report must record them.

Each assumption must include:

- the assumption
- reason
- risk
- decision impact
- whether confirmation is needed

Assumptions must not be treated as operator decisions or canonical decisions.

---

## 12. Deviation Reporting Rule

If execution deviated from the original Pack, the report must record the deviation.

Each deviation must include:

- deviation
- reason
- whether the operator approved it
- approver, if available
- impact

Unapproved deviations require human review.

---

## 13. Documentation Maintenance Reporting Rule

If AI documentation was created, edited, moved, archived, or materially changed, the report must include documentation maintenance information according to the active report template.

The report must address:

- owner file checked
- owner/reference drift checked
- related indexes
- related templates
- related canonical/decision/guide files
- related pack/run/review files
- related change/remediation files
- reference/source validity
- required follow-up updates

For complete maintenance rules, read:

```text
docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md
```

If no documentation maintenance action was required, the report must explicitly state that no documentation maintenance updates were required.

---

## 14. Index Update Reporting Rule

If tracked AI documentation files were created, moved, archived, accepted, rejected, superseded, closed, or materially changed, the report must state whether related INDEX files were checked or updated.

The report must distinguish between:

* indexes checked
* index updates completed
* index updates not required
* index updates required but not performed
* index updates required but blocked by explicit Pack restriction or `Do Not Change`

Completed INDEX updates must be reported in the Agent Final Report and Run Report under the appropriate governance, maintenance, or index update sections.

INDEX updates required but not performed must be reported under:

```text
Required Follow-up Updates
```

Each completed INDEX update should include:

```text
Index File:
Reason:
Required By:
Related Pack / Run / Review / Decision / Change / Remediation:
```

Each required but not performed INDEX update should include:

```text
Required Update:
Reason:
Suggested Owner File:
Suggested Next Action:
```

INDEX updates required by active AI documentation rules are governance/maintenance updates.

They are not unauthorized out-of-scope changes if they satisfy the Governance Maintenance Exception defined in:

```text
docs/ai/rules/SCOPE-CONTROL-RULES.md
```

For complete index maintenance rules and impact matrix, read:

```text
docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md
```

---

## 15. Change and Remediation Reporting Rule

If the execution relates to a Change Request or Remediation Pack, the report must link it.

If the execution creates a need for a Change Request or Remediation Pack, the report must state that need under:

- Change / Remediation Links
- Human Review Needed
- Required Follow-up Updates

The AI Agent must not rewrite original Packs to hide remediation needs.

---

## 16. Required Follow-up Updates Rule

If a required update is outside the current Pack scope, the AI Agent must not perform it silently.

It must be listed under:

```text
Required Follow-up Updates
```

Each follow-up update must include:

- required update
- reason
- suggested owner file
- suggested next action

---

## 17. Human Review Reporting Rule

The report must state whether human review is needed.

Human review is required when:

- unauthorized files were changed outside both Pack implementation scope and allowed governance/maintenance updates
- governance or maintenance updates were performed and need operator verification
- tests failed or were not run when required
- canonical conflicts were found
- operator approval is needed
- assumptions affect decisions
- documentation maintenance is incomplete
- required follow-up updates remain
- Change Request or Remediation may be needed

Recommended review types:

```text
operator-review
technical-review
scope-review
security-review
documentation-review
phase-review
multilingual-review
api-test-artifact-review
```

---

## 18. Ready for Review Rule

The report must state whether the run is ready for operator or human review.

If the run is not ready for review, the report must list blocking issues.

Quality gate summaries must not claim a gate passed unless the underlying evidence supports it.

---

## 19. Run Report Status Rule

Run Reports may use lifecycle statuses such as:

```text
draft
executed
accepted
rejected
failed
needs-review
needs-remediation
superseded
archived
```

Acceptance status values may include:

```text
pending-review
accepted
rejected
needs-fix
needs-remediation
superseded
archived
```

Acceptance status must not be invented.

If a run has not been reviewed, use:

```text
pending-review
```

---

## 20. Traceability Reporting Rule

Run Reports must include stable references that help future audit, review, or remediation.

Traceability may include:

- related Pack
- related Run Reports
- related Reviews
- related Decisions
- related Change Requests
- related Remediation Packs
- related Commits
- related Branches

If no additional traceability is needed, the report must state that no additional traceability notes are needed.

---

## 21. Multilingual / Translation and RTL/LTR Reporting Rule

If a Pack creates or modifies user-facing, admin-facing, operator-facing, or API-consumer-facing text, UI, views, menus, permission labels, table columns, filters, forms, buttons, actions, status labels, validation messages, error messages, success messages, API messages, owner-defined default text, external-integration labels, settings labels, or translation files, the Agent Final Report and Run Report must include multilingual and RTL/LTR reporting.

If the Pack includes a `Multilingual / Translation Requirements` section, the Agent Final Report and Run Report must reflect whether those requirements were completed, not applicable, blocked, or recorded as follow-up.

The report must not only state that translations were added. It must also state whether the Pack-defined translation requirements were checked.

The report must state:

```text id="8jt5zh"
Multilingual / RTL-LTR Impact: Yes / No
Translations Added: Yes / No / Not applicable
Translation Files by Required Locale:
Hardcoded User-facing Text Remaining: Yes / No
API Messages Use Translation Keys: Yes / No / Not applicable
RTL/LTR Considerations Reviewed: Yes / No / Not applicable
Needs Human Review for Translation Quality: Yes / No
```

If translations were required but not added, the report must explain why and list the missing work under:

```text
Required Follow-up Updates
```

If hardcoded user-facing, admin-facing, operator-facing, or API-consumer-facing text remains, the report must:

```text id="e3np6t"
- list where it remains;
- explain why it remains;
- mark human review as required;
- list the required follow-up update.
```

If RTL/LTR behavior could not be checked, the report must state that clearly and explain whether this creates a review risk.

The Agent must not claim multilingual or RTL/LTR validation passed unless it actually checked the relevant translation files, UI/text changes, or Pack-defined validation steps.

Human review is required when:

```text id="vr17ey"
- translations required by the applicable profile are missing;
- hardcoded visible text remains;
- a required locale convention is unclear;
- RTL/LTR behavior may be affected but was not reviewed;
- translation quality needs human review;
- the Pack required multilingual validation but it was not completed.
```

## 22. UI / Admin UI Reporting Rule

If a Pack creates, modifies, depends on, or removes UI, Admin UI, settings UI, dashboard UI, monitoring UI, tables, forms, filters, buttons, actions, menus, widgets, cards, modals, badges, views, or permission-gated UI behavior, the Agent Final Report and Run Report must include UI/Admin UI reporting.

The report must state:

```text id="v4mb6c"
UI Impact: Yes / No
Admin UI Impact: Yes / No / Not applicable
Public UI Impact: Yes / No / Not applicable
Settings UI Impact: Yes / No / Not applicable
Dashboard / Monitoring UI Impact: Yes / No / Not applicable
Tables / Forms Impact: Yes / No / Not applicable
Actions / Buttons Impact: Yes / No / Not applicable
Navigation / Menu Impact: Yes / No / Not applicable
Permission-gated UI Impact: Yes / No / Not applicable
Required but Not Completed: Yes / No
Reason:
Required Follow-up Updates:
```

If the Pack includes a `UI / Admin UI Requirements` section, the Agent Final Report and Run Report must reflect whether those requirements were completed, not applicable, blocked, or recorded as follow-up.

If UI files were created or changed, the report must list:

```text id="ixw2jq"
- file
- UI surface
- change summary
- related route/controller/form/table/view
- permission impact
```

If Admin menu or navigation changed, the report must list:

```text id="hqwf1v"
- menu item
- parent menu
- route
- permission
- translation key
```

If forms, fields, tables, filters, actions, buttons, dashboards, widgets, or monitoring UI changed, the report must list the affected UI areas and the validation performed.

If permission-gated UI changed, the report must state whether the relevant permissions were checked.

If sensitive operational UI changed, the report must state whether secrets, raw inbound-event data, external-service credentials, service tokens, audit data, exports, retry/cancel actions, integration-test actions, and admin override actions remain protected.

The Agent must not claim UI validation passed unless it actually checked the relevant files, UI requirements, permissions, translation keys, RTL/LTR impact, or Pack-defined validation steps.

Human review is required when:

```text id="m0im0o"
- required UI changes were not completed;
- UI permission or visibility behavior is unclear;
- sensitive UI actions may be exposed to unauthorized roles;
- UI depends on missing configuration or settings;
- translation keys or RTL/LTR review are required but missing;
- Admin UI behavior could not be validated;
- protected UI areas were changed;
- operator UI review is requested by the Pack.
```

If the Pack does not affect UI, UI impact may be reported as:

```text id="mem2xi"
UI Impact: No
UI/Admin UI Report:
- Not applicable.
```

## 23. Configuration / Settings Reporting Rule

If a Pack creates, modifies, depends on, or requires configuration, environment variables, Admin Settings, external-integration settings, secret settings, queue/cache/log settings, test setup, or operator manual setup, the Agent Final Report and Run Report must include configuration and settings reporting.

The report must state:

```text id="vdx6sn"
Configuration / Settings Impact: Yes / No
Config Files Changed: Yes / No / Not applicable
Environment Variables Required: Yes / No / Not applicable
Admin Settings Required: Yes / No / Not applicable
External Integration / Secret Settings Changed: Yes / No / Not applicable
Test / Development Setup Required: Yes / No / Not applicable
Operator Manual Setup Required: Yes / No / Not applicable
Required but Not Completed: Yes / No
Reason:
Required Follow-up Updates:
```

If the Pack includes a `Configuration / Settings Requirements` section, the Agent Final Report and Run Report must reflect whether those requirements were completed, not applicable, blocked, or recorded as follow-up.

If config files were changed, the report must list:

```text id="8srsqn"
- file
- key
- purpose
- default value or behavior change
- whether the value is environment-specific
- whether the value is sensitive
```

If environment variables are required, the report must list:

```text id="fy44pk"
- variable name
- purpose
- placeholder
- whether it is required for local/dev/test
- whether it is required for production
- whether it is sensitive
- whether `.env.example` was updated when required
```

If Admin Settings are required, the report must list:

```text id="7xlbc4"
- setting name
- Admin UI location
- purpose
- default value if applicable
- whether operator action is required
- whether the setting is required before testing
- whether the setting is required before production
```

If external-integration or secret settings are changed or required, the report must list:

```text id="ynscpm"
- setting name
- non-sensitive storage location
- sensitive storage location
- masking requirement
- rotation or revocation impact
```

Reports must not include real secrets, real service tokens, external-service credentials, callback tokens, webhook secrets, signing keys, Authorization headers, sensitive real identifiers, or production-only sensitive values.

If sensitive values are found in configuration examples, reports, logs, tests, seed data, documentation, or API test artifact files, the report must mark human review as required and treat the issue as security-sensitive.

If required configuration or settings work was not completed, the report must explain why and list the missing work under:

```text id="d279fh"
Required Follow-up Updates
```

If operator manual setup is required, the report must clearly state:

```text id="lpu2cw"
- setup step
- where to configure it
- placeholder value or expected value type
- when it is required before: testing / production / external-integration test / inbound-event test / background-worker run
```

The Agent must not claim configuration or settings validation passed unless it actually checked the relevant files, settings, placeholders, Pack-defined validation steps, or documented operator setup requirements.

Human review is required when:

```text id="y4gbp0"
- required configuration is missing;
- required `.env.example` updates were not made;
- real secrets or sensitive values appear in committed files or reports;
- external-integration secret/config separation is unclear or violated;
- Admin Settings are required but not implemented or documented;
- operator manual setup is required before testing or production;
- configuration or settings validation was required but not completed.
```

## 24. API Test Artifact Reporting Rule

If a Pack creates or modifies API endpoints, routes, methods, headers, authentication, request input, validation, responses, status codes, error codes, callbacks, status/health contracts, integration-test endpoints, or API examples, the Agent Final Report and Run Report must include reporting for any API test artifact declared by the applicable owner profile.

The report must state:

API Test Artifact Impact: Yes / No
Applicable Profile Checked: Yes / No / Not applicable
Artifact Checked: Yes / No / Not applicable
Artifact Updated: Yes / No / Not applicable
Environment/Configuration Updated: Yes / No / Not applicable
Artifact Path:
Environment/Configuration Path:
API Changes Covered:
Required but Not Updated: Yes / No
Reason:
Required Follow-up Updates:
If an owner-required API test artifact was not updated, the report must explain why and list the missing work under:

Required Follow-up Updates

If the declared artifact path does not exist and the Pack introduced the first testable API endpoint, the report must state whether the initial artifact was created or why it was not created.

The Agent must not claim API test artifact maintenance is complete unless the relevant artifact and environment/configuration files were checked or updated.

API test artifact files must not contain real secrets, service tokens, external-service credentials, callback tokens, Authorization headers, sensitive real identifiers, or production URLs.

If sensitive data is found in an API test artifact or its environment/configuration file, the report must mark human review as required and the issue must be treated as a security-sensitive finding.

If a Pack does not affect API behavior, API test artifact impact may be reported as:

API Test Artifact Impact: No
Artifact Updated: Not applicable

## 25. Template Ownership Rule

Reporting rules live in:

```text
docs/ai/rules/REPORTING-RULES.md
```

Agent Final Report structure lives in:

```text
docs/ai/templates/AGENT-FINAL-REPORT-TEMPLATE.md
```

Run Report structure lives in:

```text
docs/ai/templates/RUN-REPORT-TEMPLATE.md
```

Do not duplicate full report templates inside this rules file.

Do not place reporting rules inside templates except for short template usage notes or references back to this file.

Templates may include brief section-level instructions and placeholders needed to fill the report correctly.

## 26. Operator Documentation Reporting Rule

If a Pack, Remediation Pack, Review outcome, or accepted Decision changes operator-facing behavior in an owner component or its administration surface, the Agent Final Report and Run Report must report operator-documentation impact.

The report must state whether the guide was required, updated, not applicable, blocked, or recorded as follow-up.

The full guide content must not be duplicated inside the Agent Final Report or Run Report.

Resolve the source of operator instructions through the applicable owner profile.

Reports must include:

Operator Documentation Impact: Yes / No
Operator Documentation Update Required: Yes / No / Not applicable
Operator Documentation Updated: Yes / No / Not applicable
Guide File:
- <resolved from applicable owner profile>
Sections Updated:
- ...
Screenshots Added: Yes / No / Not applicable
Required Follow-up Updates:
- ...

If the guide update was required but not performed, the report must mark it as a Required Follow-up Update.

---
