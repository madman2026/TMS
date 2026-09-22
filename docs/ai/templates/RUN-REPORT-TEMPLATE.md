# RUN-REPORT-TEMPLATE

Use this template to create a persistent Run Report after executing an AI Pack.

A Run Report is a repository-stored audit record under:

```text
<owner-docs-root>/runs/
```

For reporting rules, required-section behavior, no-fabrication rules, lifecycle status rules, and the relationship between Agent Final Report and Run Report, read:

```text
docs/ai/rules/REPORTING-RULES.md
```

---

# [AI-PACK-ID] — Run [RUN-NUMBER]

## 1. Run Metadata

Use this section to identify the run as a stable execution record.

```text
Run Report ID:
Run Number:
Execution Date:
Execution Time:
Agent:
Model / Tool:
Created By:
Last Updated:
Run Report Path:
Created Before Commit:
Run Status:
```

Recommended `Run Status` values:

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

---

## 2. Pack Reference

Use this section to link the run to the exact AI Pack that was executed.

```text
Pack ID:
Pack Title:
Pack Type: standard-pack / remediation-pack
Pack Path:
Related Release:
Related Phase:
Related Epic / Feature / Story:
Pack Version:
Pack Status Before Run:
```

If the Pack was superseded, remediated, or replaced, record it here:

```text
Pack Lifecycle Note:
```

---

## 3. Pack Scope Summary

Summarize the execution scope from the Pack.

This section must not invent new scope.

```text
Goal:
Allowed Files / Areas:
Do Not Change:
Main Tasks:
Scope Notes:
```

---

## 4. Execution Context

Record the repository and execution context.

```text
Branch:
Commit Before Execution:
Commit After Execution:
Working Tree Before Execution:
Working Tree After Execution:
Diff Checked:
Git Diff Summary:
Environment:
Relevant Configuration:
```

If commit information is not available, write:

```text
Commit information not available.
```

---

## 5. Path Resolution Review

```text
Pack paths checked against actual repository structure: Yes / No
Generic paths found: Yes / No
Path mappings applied: Yes / No
Ambiguous paths found: Yes / No
Operator approval required: Yes / No / Not applicable
Path Mappings
Pack Path	Resolved Repository Path	Notes
...	...	...
```

If no mappings were needed, write:

No path mappings were needed.

---

## 6. Configuration / Settings Verification

Use this section when the Pack creates, modifies, depends on, or requires configuration, environment variables, Admin Settings, external-integration settings, secret settings, queue/cache/log settings, test setup, or operator manual setup.

```text
Configuration / Settings Impact: Yes / No
Pack Configuration / Settings Requirements reviewed: Yes / No
Pack Configuration / Settings Requirements completed: Yes / No / Not applicable
Config files checked: Yes / No / Not applicable
Environment variables checked: Yes / No / Not applicable
Admin Settings checked: Yes / No / Not applicable
External Integration / Secret settings checked: Yes / No / Not applicable
Test / Development setup checked: Yes / No / Not applicable
Operator manual setup required: Yes / No
Human review required: Yes / No
```

## 7. UI / Admin UI Verification

Use this section when the Pack creates, modifies, depends on, or removes UI, Admin UI, settings UI, dashboard UI, monitoring UI, tables, forms, filters, buttons, actions, menus, widgets, cards, modals, badges, views, or permission-gated UI behavior.

```text
UI Impact: Yes / No
Pack UI / Admin UI Requirements reviewed: Yes / No / Not applicable
Pack UI / Admin UI Requirements completed: Yes / No / Not applicable
Admin UI checked: Yes / No / Not applicable
Public UI checked: Yes / No / Not applicable
Settings UI checked: Yes / No / Not applicable
Dashboard / Monitoring UI checked: Yes / No / Not applicable
Tables / Forms checked: Yes / No / Not applicable
Actions / Buttons checked: Yes / No / Not applicable
Navigation / Menu checked: Yes / No / Not applicable
Permission-gated UI checked: Yes / No / Not applicable
Translation keys checked: Yes / No / Not applicable
RTL/LTR checked: Yes / No / Not applicable
Human review required: Yes / No
```

UI Surfaces Changed
- Surface:
  Type:
  File:
  Route / URL:
  Permission:
  Change:

If none:

No UI surfaces changed.
UI Validation Results
- Validation:
  Result:
  Evidence:
  Notes:

If no UI validation was required:

No UI validation required.
UI Follow-up
Required Follow-up Updates:
- ...

If none:

No UI/Admin UI follow-up required.

---

## 8. Operator Documentation Verification

Use this section when operator-facing behavior changed.

```text
Operator Documentation Impact: Yes / No
Pack Operator Documentation Requirements reviewed: Yes / No / Not applicable
Operator Documentation Update Required: Yes / No / Not applicable
Operator Documentation Updated: Yes / No / Not applicable
```

Guide File:
- <operator guide path resolved from applicable owner profile>
Sections Updated:
- ...
Screenshots Added: Yes / No / Not applicable
Screenshots Pending: Yes / No / Not applicable
Human Review Required: Yes / No
Required Follow-up Updates:
- ...

If no operator-facing behavior changed, write:

Admin User Guide Verification:
- Not applicable.

---

## 9. Files Read / Referenced

List files read or referenced during execution.

```text
- `path/to/file`
```

Include source docs, canonical files, guides, rules, templates, and project files when they influenced the execution.

If no files were explicitly read or referenced, write:

```text
No files read or referenced.
```

---

## 10. Created Files

List files created during execution.

```text
- `path/to/file` — reason
```

If no files were created, write:

```text
No files created.
```

---

## 11. Modified Files

### 11.1 Implementation Files Modified

Files changed as part of the Pack implementation scope:

```text
- `path/to/file` — summary of change
```

If no implementation files were modified, write:

```text
No implementation files modified.
```

### 11.2 Governance / Maintenance Files Modified

Files changed because they were required by AI documentation, reporting, index, review, decision, canonical, change, or remediation rules:

- File:
  Reason:
  Required By:
  Related Record:

Example:

- File: `<owner-docs-root>/packs/PACKS-INDEX.md`
  Reason: Updated Pack status after execution.
  Required By: `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`
  Related Record: current Pack execution

- File: `<owner-docs-root>/runs/RUNS-INDEX.md`
  Reason: Added this Run Report.
  Required By: `docs/ai/rules/REPORTING-RULES.md`
  Related Record: current Run Report

If no governance or maintenance files were modified, write:

```text
No governance or maintenance files modified.
```

### 11.3 Unauthorized Out-of-Scope Files Modified

Files changed outside both the Pack implementation scope and allowed governance/maintenance updates:

- `path/to/file` — reason

If no unauthorized out-of-scope files were modified, write:

```text
No unauthorized out-of-scope files modified.
```

---

## 12. Deleted Files

List files deleted during execution.

```text
- `path/to/file` — reason
```

If no files were deleted, write:

```text
No files deleted.
```

---

## 13. Commenting Verification

- Pack commenting requirements reviewed: Yes / No
- Global commenting rules reviewed: Yes / No
- Required comments were added: Yes / No / Not applicable
- Commenting result matches Agent Final Report: Yes / No

### Verified Commented Files

- `path/to/file.ext`
  - Verified: Yes / No
  - Notes:

### Missing or Incomplete Comments

- None

Or:

- `path/to/file.ext`
  - Issue:
  - Required follow-up:

---

## 14. API Test Artifact Verification

Use this section when the Pack creates or modifies API endpoints, route paths, HTTP methods, request headers, authentication behavior, service token behavior, request bodies, query parameters, validation rules affecting API input, response bodies, response status codes, error codes, callback/inbound-event endpoint contracts, status endpoint contracts, external-integration test endpoint contracts, health endpoint contracts, or API examples.

API Test Artifact Impact: Yes / No
Applicable profile checked: Yes / No / Not applicable
API test artifact rules reviewed: Yes / No / Not applicable
Artifact checked: Yes / No / Not applicable
Artifact updated: Yes / No / Not applicable
Environment/configuration checked: Yes / No / Not applicable
Environment/configuration updated: Yes / No / Not applicable
Artifact Path:
Environment/configuration Path:
Sensitive values found: Yes / No
Human review required: Yes / No
API Changes Covered
- ...

If no API behavior changed, write:

No API behavior changes required API test artifact updates.
Requests Added
- Request Name:
  Method:
  Path:
  Purpose:

If no requests were added, write:

No API test requests added.
Requests Updated
- Request Name:
  Change:
  Reason:

If no requests were updated, write:

No API test requests updated.
Requests Removed or Deprecated
- Request Name:
  Reason:
  Replacement:

If no requests were removed or deprecated, write:

No API test requests removed or deprecated.
Environment Variables
Variables Added:
- ...

Variables Updated:
- ...

Variables Removed:
- ...

If no environment variables changed, write:

No API test environment/configuration variables changed.
Safety Review
Real secrets included: No
Real service tokens included: No
Real external-service credentials included: No
Real callback tokens included: No
Real Authorization headers included: No
Sensitive real identifiers included: No
Production URLs included: No
API Test Artifact Follow-up
Required Follow-up Updates:
- ...

If no follow-up is required, write:

No API test artifact follow-up required.

---

## 15. Multilingual / RTL-LTR Verification

Use this section when the Pack creates or modifies visible text, UI, views, menus, permission labels, status labels, validation messages, error messages, success messages, API messages, owner-defined default text, external-integration labels, settings labels, or translation files.

```text id="o12kcb"
Multilingual / RTL-LTR Impact: Yes / No
Multilingual rules reviewed: Yes / No / Not applicable
Translation files checked: Yes / No / Not applicable
Profile-required translations added: Yes / No / Not applicable
Translation keys used in code: Yes / No / Not applicable
Hardcoded user-facing text remaining: Yes / No
API messages use stable error codes: Yes / No / Not applicable
API messages use translation keys: Yes / No / Not applicable
RTL/LTR impact reviewed: Yes / No / Not applicable
Needs human review for translation quality: Yes / No
```

### Translation Files

```text
Translation Files by Required Locale:
- ...
```

If no translation files were changed, write:

```text
No translation files changed.
```

### Hardcoded Text Review

```text
Hardcoded User-facing Text Remaining:
- None
```

Or:

```text
- File:
  Text:
  Reason:
  Required Follow-up:
```

### RTL/LTR Notes

```text
RTL/LTR Considerations:
- ...
```

If not applicable, write:

```text
RTL/LTR review not applicable.
```

### Multilingual Follow-up

```text
Required Follow-up Updates:
- ...
```

If no follow-up is required, write:

```text
No multilingual or RTL/LTR follow-up required.
```

## 16. Commands Run

List commands executed during implementation or validation.

```text
| Command | Purpose | Result | Exit Code |
|---|---|---|---|
| `...` | ... / Initial validation / Validation after follow-up fix | passed / failed / not available | ... |
```

If no commands were run, write:

```text
No commands run.
```

---

## 17. Tests Added

List tests added during execution.

```text
- `path/to/test` — purpose
```

If no tests were added, write:

```text
No tests added.
```

---

## 18. Tests Run

List tests or validation commands run.

```text
| Test / Command | Result | Exit Code | Notes |
|---|---|---|---|
| `...` | passed / failed / not run | ... | ... |
```

If tests were not run, explain why:

```text
Tests not run because:
```

---

## 19. Test Results

Summarize test results.
If a test failed earlier in the same execution chat and was fixed before this Run Report was created, record both the initial failure and the final validation result.

```text
Passed:
- ...

Failed:
- ...

Not Run:
- ...
```

If there were no failures, write:

```text
Failure Summary:
- No failures.
Required Fix / Follow-up:
- Not applicable.
Test Evidence:
- ...
```

If any tests failed, include:

```text
Failure Summary:
- Initial test run failed.
- Issue was fixed later in the same execution chat.
- Final validation passed.

Required Fix / Follow-up:
- Fix applied.
- No remaining test follow-up required.

Test Evidence:
- Initial failed command: ...
- Final passed command: ...
```

---

## 20. Error Handling / Logging / Traceability Verification

Use this section when the Pack creates, modifies, removes, validates, or depends on:

```text
- API error behavior
- validation errors
- authentication or authorization errors
- Admin UI error messages
- internal exceptions
- external-error normalization
- callback/inbound-event error handling
- queue or Job failure handling
- retry or dead-letter errors
- status-transition errors
- structured logging
- trace identifiers
- sensitive-data masking
```

### 20.1 Impact Summary

```text
Error handling impact: Yes / No
API error contract impact: Yes / No / Not applicable
UI / Admin UI error impact: Yes / No / Not applicable
Exception handling impact: Yes / No / Not applicable
External-error normalization impact: Yes / No / Not applicable
Callback / inbound-event error impact: Yes / No / Not applicable
Logging impact: Yes / No / Not applicable
Traceability impact: Yes / No / Not applicable
Error code impact: Yes / No / Not applicable
Sensitive-data handling impact: Yes / No / Not applicable
```

If the Pack has no impact in this area, write:

```text
No error handling, logging, or traceability impact.
```

---

### 20.2 Error Scenarios Implemented or Changed

Record each meaningful error scenario that was implemented or changed.

```text
- Scenario:
  Trigger:
  Layer / Component:
  Stable error_code:
  HTTP status:
  Human-readable message behavior:
  Retryable: Yes / No / Not applicable
  Permanent: Yes / No / Not applicable
  Admin action required: Yes / No / Not applicable
  Owner-managed state impact:
  Operation-attempt state impact:
  Log event:
  Trace identifiers:
  Sensitive-data handling:
```

If none:

```text
No error scenarios introduced or changed.
```

---

### 20.3 Error Codes Added, Changed, Reused, or Deprecated

```text
- error_code:
  Change type: added / changed / reused / deprecated
  Category:
  Used by:
  HTTP status:
  Retryable:
  Permanent:
  Admin action required:
  Translation key:
  Log level:
  Status impact:
```

If none:

```text
No error codes added, changed, reused, or deprecated.
```

---

### 20.4 API Error Contract Verification

```text
API error contract changed: Yes / No / Not applicable
API error contract verified: Yes / No / Not applicable
```

Verified endpoints:

```text
- Endpoint:
  HTTP method:
  Scenario:
  Expected HTTP status:
  Actual HTTP status:
  Expected error_code:
  Actual error_code:
  Required response fields present:
  Validation errors structure verified:
  request_id / trace_id verified:
  Forbidden sensitive details absent:
  Test / Evidence:
```

If the contract changed but was not verified, explain:

```text
API error contract not fully verified because:
- ...
```

The Run Report must not mark the API error contract as verified when only the HTTP status was checked.

---

### 20.5 UI / Admin UI Error Verification

```text
UI error behavior changed: Yes / No / Not applicable
UI error behavior verified: Yes / No / Not applicable
```

Verified UI behavior:

```text
- UI area:
  Scenario:
  Clear explanation shown:
  Suggested operator action shown:
  error_code shown when required:
  Trace identifier shown when required:
  Retry/admin-action guidance shown:
  Raw exception hidden:
  Stack trace hidden:
  Raw external payload hidden:
  Sensitive values masked:
  Translation keys used:
  Permission-gated technical details verified:
  Evidence:
```

If UI review requires human inspection, record it explicitly:

```text
Human UI review required:
- Area:
  Reason:
  Review focus:
```

---

### 20.6 Exception Handling Verification

```text
Exception handling changed: Yes / No / Not applicable
Exception handling verified: Yes / No / Not applicable
```

```text
- Exception class:
  Thrown by:
  Handled by:
  Mapped error_code:
  Retryable:
  Log level:
  Observable result:
  Silently swallowed: Yes / No
  Raw message exposed to UI/API: Yes / No
  Test / Evidence:
```

If an exception is intentionally allowed to fail through the framework or queue mechanism, record:

```text
Framework / queue failure path used:
Reason:
Expected observable result:
```

---

### 20.7 External Error Normalization Verification

```text
External-error normalization changed: Yes / No / Not applicable
External-error normalization verified: Yes / No / Not applicable
Real external service called during tests: No
```

Mappings verified:

```text
- Integration:
  External error code:
  External error meaning:
  Normalized internal error_code:
  Retryable:
  Permanent:
  Admin action required:
  Expected owner-managed state:
  Expected operation-attempt state:
  Safe external error snapshot:
  Fake / Mock / Fixture used:
  Test / Evidence:
```

If no external-error normalization applies:

```text
No external-error normalization changes.
```

---

### 20.8 Structured Logging Verification

```text
Structured logging changed: Yes / No / Not applicable
Structured logging verified: Yes / No / Not applicable
```

Verified log events:

```text
- Event name:
  Trigger:
  Log level:
  Required context:
  Context verified:
  Trace identifiers verified:
  Sensitive fields masked or omitted:
  Exception class included when required:
  Test / Evidence:
```

Relevant context may include:

```text
request_id
correlation_id
domain_record_id
operation_id
inbound_event_id
external_integration_id
external_reference_id
source_system
source_reference
error_code
retryable
admin_action_required
exception_class
```

Do not mark logging as verified merely because `Log::error()` or another log method was called.

The required event name and meaningful context must also be checked.

---

### 20.9 Traceability Verification

```text
Traceability changed: Yes / No / Not applicable
Traceability verified: Yes / No / Not applicable
```

Verified identifier flow:

```text
- Entry point:
  Identifier created:
  Identifier value or safe example:
  Stored in:
  Propagated to:
  Returned through API:
  Passed to Job:
  Stored on domain record:
  Stored on operation attempt:
  Included in inbound event/report:
  Included in logs:
  Visible in Admin UI:
  Test / Evidence:
```

The same logical identifier must connect the related execution flow.

Separate unrelated identifiers at each layer do not prove end-to-end traceability.

---

### 20.10 Error Classification Verification

```text
Retryable / permanent classification verified: Yes / No / Not applicable
Admin-action-required classification verified: Yes / No / Not applicable
Status impact verified: Yes / No / Not applicable
```

Verified classifications:

```text
- error_code:
  Expected retryable:
  Actual retryable:
  Expected permanent:
  Actual permanent:
  Expected admin action required:
  Actual admin action required:
  Expected owner-managed state:
  Actual owner-managed state:
  Expected operation-attempt state:
  Actual operation-attempt state:
  Test / Evidence:
```

Any mismatch must be reported as a failed validation or unresolved issue.

---

### 20.11 Sensitive Data and Masking Verification

```text
Sensitive-data exposure checked: Yes / No / Not applicable
Sensitive-data handling verified: Yes / No / Not applicable
```

Areas checked:

```text
- API responses
- Admin UI
- logs
- exception messages
- external-error snapshots
- inbound-event payload visibility
- reports
- exports
- tests and fixtures
- owner-declared API test artifact files
- Agent Final Report
- Run Report
```

Data types checked:

```text
- external-service API keys
- external-service secrets
- service tokens
- callback tokens
- webhook secrets
- signing keys
- Authorization headers
- raw credentials
- secret_json
- sensitive target or personal identifiers
- raw external payloads containing secrets
```

Result:

```text
No raw secrets, credentials, tokens, Authorization headers, sensitive target/personal identifiers, or unsafe external payloads were exposed.
```

If exposure was found:

```text
- Location:
  Sensitive data type:
  Exposure:
  Severity:
  Fix applied:
  Re-validation result:
  Follow-up required:
```

---

### 20.12 Error-specific Tests Added

```text
- Test file:
  Test name:
  Error scenario:
  Behavior proven:
  Main assertions:
  Expected reason for failure if implementation is wrong:
```

If none:

```text
No error-specific tests added.
```

---

### 20.13 Error-specific Tests Run

```text
| Test / Command | Scenario | Result | Exit Code | Evidence |
|---|---|---|---|---|
| `...` | ... | passed / failed / not run | ... | ... |
```

If none were run:

```text
Error-specific tests not run because:
- ...
```

---

### 20.14 Error Test Results

Record both initial and final results when a failure was fixed during the same execution.

```text
Passed:
- ...

Failed:
- ...

Not Run:
- ...
```

When an initial failure was fixed:

```text
Initial Failure:
- Test:
- Failure:
- Root cause:

Fix Applied:
- File:
- Change:

Final Validation:
- Test / Command:
- Result:
```

The Run Report must not hide initial failed error tests.

---

### 20.15 False-positive Test Review

```text
Anti-false-positive review completed: Yes / No / Not applicable
```

For each important error test, confirm:

```text
- Would the test fail if the wrong error_code were returned?
- Would the test fail if the wrong status transition occurred?
- Would the test fail if retryable/permanent classification were reversed?
- Would the test fail if required trace context were missing?
- Would the test fail if sensitive data were exposed?
- Would the test fail if the external error were not normalized?
```

If a test would still pass while the intended behavior is broken, record:

```text
Weak or false-positive-prone test found:
- Test:
  Problem:
  Required improvement:
```

---

### 20.16 Missing or Unresolved Work

```text
- Issue:
  Area:
  Impact:
  Blocking: Yes / No
  Required action:
```

If none:

```text
No missing or unresolved error-handling work.
```

---

### 20.17 Required Follow-up Updates

```text
- ...
```

If none:

```text
No error handling, logging, or traceability follow-up required.
```

## 21. Implementation Summary

Summarize what was implemented.

Keep this section factual and tied to actual changes.

```text
...
```

Do not include future plans unless they are required follow-up updates.

---

## 22. Scope Compliance

State whether the execution respected Pack implementation scope and whether any governance maintenance exception was used.

```text
Implementation scope respected: Yes / No
Governance maintenance exception used: Yes / No
Governance / maintenance updates directly related to this Pack: Yes / No / Not applicable
Unauthorized out-of-scope changes detected: Yes / No
Files listed under Do Not Change modified: Yes / No
```

Implementation Scope Notes
...

Governance Maintenance Exception Notes

Use this section only if governance or maintenance files were changed outside the Pack implementation scope.

- File:
  Reason:
  Required By:
  Related Record:

Unauthorized Out-of-Scope Changes
- File:
  Reason:
  Impact:
  Human Review Required:

If none:

No unauthorized out-of-scope changes detected.

---

## 23. Owner-root / Protected Area Review

Use this section to record compliance with:

```text
docs/ai/rules/SCOPE-CONTROL-RULES.md
```

```text
Owner-root rule reviewed: Yes / No
Declared owner target:
Protected areas checked: Yes / No
Implementation stayed inside the selected owner's declared root: Yes / No / Not applicable
Protected areas changed: Yes / No
Protected-area change approved by operator: Yes / No / Not applicable
Extension points checked before protected-area change: Yes / No / Not applicable
Human review required: Yes / No
```

### Protected Areas Changed

If no protected areas were changed, write:

```text
No protected areas changed.
```

If protected areas were changed, list them:

```text
- File:
  Protected Area:
  Reason:
  Approval Source:
  Extension Points Checked:
  Risk Level:
  Rollback Notes:
  Human Review Required:
```

### Plugin-first Notes

Record any relevant notes:

```text
...
```

If there are no additional notes, write:

```text
No additional Plugin-first notes.
```

---

## 24. Canonical Decisions Applied

List canonical decisions that directly guided the execution.

```text
Canonical Conflict Found: Yes / No

Applied Decisions:
- Canonical File:
  Decision / Constraint Applied:

Conflicts:
- ...
```

If no canonical decisions were directly applied, write:

```text
No canonical decisions directly applied.
```

If a conflict with canonical decisions was found, record it here and also reference Change / Remediation sections if applicable.

---

## 25. Operator Answers / Decisions Captured

Record operator answers received during execution.

Classify them according to:

```text
docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md
```

Use this format:

```text
- Operator Answer:
  Classification:
  Recorded In:
  Canonical Update Required:
  Follow-up Required:
```

If there were no operator answers or decisions during execution, write:

```text
No operator answers or decisions were captured during this execution.
```

---

## 26. Deviations from Original Pack

Record any deviation from the Pack instructions.

```text
- Deviation:
  Reason:
  Operator Approved: Yes / No
  Approved By:
  Impact:
```

If there were no deviations, write:

```text
No deviations from the original Pack.
```

---

## 27. Assumptions Made

Record assumptions made by the AI Agent during execution.

```text
- Assumption:
  Reason:
  Risk:
  Decision Impact:
  Needs Confirmation: Yes / No
```

If no assumptions were made, write:

```text
No assumptions made.
```

---

## 28. Index Updates

Record related INDEX updates.

```text
Indexes Checked:
- ...

Completed:
- ...

Not required:
- ...

Required but not performed:
- ...

Required but blocked by Pack scope or Do Not Change:
- ...
```

If no index updates were required, write:

```text
No index updates required.
```

If an INDEX update was required but not performed, it must also be listed under:

Required Follow-up Updates

For complete index maintenance rules and impact matrix, read:

docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md
docs/ai/rules/REPORTING-RULES.md
docs/ai/rules/SCOPE-CONTROL-RULES.md

---

## 29. Documentation Maintenance

Use this section when AI documentation was created, edited, moved, archived, or materially changed.

```text
Maintenance Rule Applied:
- ...

Owner file checked:
- ...

Owner / Reference drift checked:
- ...

Related indexes updated:
- ...

Related templates checked:
- ...

Related canonical/decision/guide files checked:
- ...

Related pack/run/review files checked:
- ...

Related change/remediation files checked:
- ...

Reference/source validity checked:
- ...

Required Follow-up Updates:
- ...
```

If no documentation maintenance action was required, write:

```text
Documentation Maintenance:
- No documentation maintenance updates required.
```

For complete documentation maintenance rules, read:

```text
docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md
```

---

## 30. Change / Remediation Links

Record related Change Requests or Remediation Packs.

```text
Related Change Requests:
- Path:
  Status:

Related Remediation Packs:
- Path:
  Status:
```

If none are related, write:

```text
No related Change Requests or Remediation Packs.
```

---

## 31. Open Questions

List unresolved questions after execution.

```text
- Question:
  Owner:
  Impact:
```

If there are no open questions, write:

```text
No open questions.
```

---

## 32. Potential Risks

List risks identified during or after execution.

```text
- Risk:
  Impact:
  Suggested Mitigation:
```

If there are no known risks, write:

```text
No known risks.
```

---

## 33. Human Review Needed

State whether human review is needed.

```text
Human Review Needed: Yes / No
Review Type:
Reason:
Review Focus:
```

Recommended review types:

```text
operator-review
technical-review
scope-review
security-review
documentation-review
phase-review
```

---

## 34. Ready for Review

State whether the run is ready for operator or human review.

```text
Ready for Review: Yes / No
Quality Gate Summary:
- Scope: Passed / Failed / Needs review
- Tests: Passed / Failed / Not run
- Documentation Maintenance: Completed / Not required / Needs follow-up
- Human Review: Required / Not required
```

If not ready, explain why:

```text
Blocking Issues:
- ...
```

---

## 35. Acceptance Status

Record the current acceptance state of this run.

```text
Acceptance Status:
Accepted / Rejected By:
Reviewed By:
Review Date:
Review Notes:
```

Recommended values:

```text
pending-review
accepted
rejected
needs-fix
needs-remediation
superseded
archived
```

If accepted or rejected, record:

```text
Accepted / Rejected By:
Reviewed By:
Review Date:
Review Notes:
```

---

## 36. Required Follow-up Updates

Use this section when a required update was identified but could not be performed inside the current Pack scope.

```text
- Required Update:
  Reason:
  Suggested Owner File:
  Suggested Next Action:
```

If there are no required follow-up updates, write:

```text
No required follow-up updates.
```

---

## 37. Traceability Notes

Use this section for stable references that help future audit, review, or remediation.

```text
Related Pack:
Related Run Reports:
Related Reviews:
Related Decisions:
Related Change Requests:
Related Remediation Packs:
Related Commits:
Related Branches:
Rollback Notes:
```

If no additional traceability notes are needed, write:

```text
No additional traceability notes.
```
