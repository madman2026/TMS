# AGENT-FINAL-REPORT-TEMPLATE

Use this template for the Agent Final Report after executing an AI Pack.

The Agent Final Report is the operator-facing execution summary shown in chat.

It must be honest, complete, and scoped to the executed Pack.

For reporting rules, required-section behavior, no-fabrication rules, and the relationship between Agent Final Report and Run Report, read:

```text
docs/ai/rules/REPORTING-RULES.md
```

---

## 1. Pack ID

```text
...
```

---

## 2. Summary

Summarize the completed execution in a short factual form.

```text
...
```

---

## 3. Files Created — Optional in Concise Mode

```text
- `path/to/file` — reason
```

If no files were created, write:

```text
No files created.
```

---

## 4. Files Edited — Optional in Concise Mode

```text
- `path/to/file` — summary of edit
```

If no files were edited, write:

```text
No files edited.
```

---

## 5. Files Read / Referenced — Optional in Concise Mode Unless Required Source References Were Needed

```text
- `path/to/file`
```

Include Pack, canonical files, guides, rules, templates, source docs, or project files that influenced execution.

For owner- or technology-specific Packs, if the Pack requires source guidance declared by an applicable profile:

```text
docs/project/references/source-docs/cms/ai-assistant-guide.md
```

the Agent must explicitly state whether this file was read or not.

Use one of:

Applicable source guidance read:
- `<profile-declared-source-guidance-path>`

Applicable source guidance not read:
- Reason:
- Follow-up required: Yes / No

If owner-specific source guidance was not required for this Pack, write:

Applicable source guidance:
- Not applicable.

If no files were explicitly read or referenced, write:

```text
No files read or referenced.
```

---

## 6. Commenting Report — Optional in Concise Mode Unless Comments Were Required, Added, Missing, or Need Follow-up

- Commenting rules checked: Yes / No
- Comments required: Yes / No
- Comments added or updated: Yes / No / Not applicable
- Files with comments:
  - `path/to/file.php`
- If not applicable, reason:
  - ...
- Follow-up needed:
  - None

---

## 7. Multilingual / RTL-LTR Report — Optional in Concise Mode Unless Text, UI, API Messages, Validation Messages, or Translations Were Added or Modified

Use this section when the Pack creates or modifies visible text, UI, views, menus, permission labels, status labels, validation messages, error messages, success messages, API messages, owner-defined default text, external-integration labels, settings labels, or translation files.

```text id="4vmbgu"
Multilingual / RTL-LTR Impact: Yes / No
Translations Added: Yes / No / Not applicable
Translation Files by Required Locale:
- ...

Translation Keys Used:
- ...

Hardcoded User-facing Text Remaining: Yes / No
Hardcoded Text Notes:
- ...

API Messages Use Translation Keys: Yes / No / Not applicable
RTL/LTR Considerations Reviewed: Yes / No / Not applicable
RTL/LTR Notes:
- ...

Needs Human Review for Translation Quality: Yes / No
Required Follow-up Updates:
- ...
```

If the Pack did not create or modify visible text, UI, API messages, validation messages, or translation files, write:

```text
Multilingual / RTL-LTR Report:
- Not applicable.
```

## 8. Tests Added

```text
- `path/to/test` — purpose
```

If no tests were added, write:

```text
No tests added.
```

---

## 9. Tests Run

```text
- `command or test name`
```

If tests were not run, explain why:

```text
Tests not run because: ...
```

---

## 10. Test Results

If tests fail and the issue is fixed later in the same execution chat, the later report or Run Report must preserve the initial failure, the fix summary, and the final validation result.

```text
Passed:
- ...

Failed:
- ...

Not run:
- ...
```

If any tests failed, include:

```text
Failure Summary:
Required Fix / Follow-up:
```

---

## 12. Error Handling / Logging / Traceability Report — Optional in Concise Mode Unless Changed, Missing, Failed, or Follow-up Is Required

Use this section when the Pack creates, modifies, validates, or depends on error handling, error reporting, exceptions, API errors, Admin UI errors, external-error normalization, logging, traceability, or sensitive-data masking.

```text
Error handling impact: Yes / No
API error contract changed: Yes / No / Not applicable
UI / Admin UI error behavior changed: Yes / No / Not applicable
Stable error codes added or changed: Yes / No / Not applicable
Exception handling added or changed: Yes / No / Not applicable
External-error normalization added or changed: Yes / No / Not applicable
Structured logging added or changed: Yes / No / Not applicable
Trace identifiers added, propagated, or changed: Yes / No / Not applicable
Retryable / permanent classification changed: Yes / No / Not applicable
Admin-action-required classification changed: Yes / No / Not applicable
Sensitive data masking or omission changed: Yes / No / Not applicable
```

### Error Scenarios Implemented or Changed

```text
- Scenario:
  Layer / Area:
  Stable error_code:
  HTTP status:
  Retryable:
  Permanent:
  Admin action required:
  Status impact:
  UI/API behavior:
  Log event:
  Trace identifiers:
```

If no error scenarios were introduced or changed, write:

```text
No error scenarios introduced or changed.
```

### Error Codes Added or Changed

```text
- error_code:
  Category:
  Used in:
  Translation behavior:
  Retryable:
  Admin action required:
```

If no error codes were added or changed, write:

```text
No error codes added or changed.
```

### API Error Contract Verification

```text
API error contract verified: Yes / No / Not applicable

Verified:
- HTTP status:
- response status:
- error_code:
- message field:
- request_id / trace_id:
- validation errors structure:
- sensitive fields excluded:
```

If not verified, explain:

```text
API error contract not verified because:
- ...
```

### UI / Admin UI Error Verification

```text
UI error behavior verified: Yes / No / Not applicable

Verified:
- error explanation is clear:
- suggested operator action is present:
- error_code is shown when required:
- trace identifier is shown when required:
- raw exceptions are hidden:
- raw external payload is hidden:
- sensitive values are masked:
- translations are used:
```

### Logging Verification

```text
Structured logging verified: Yes / No / Not applicable

Verified log events:
- Event:
  Level:
  Required context present:
  Trace identifiers present:
  Sensitive fields masked or omitted:
```

The report must not claim logging was verified unless the implementation or related tests were actually inspected.

### Traceability Verification

```text
Traceability verified: Yes / No / Not applicable

Verified flow:
- Entry point:
- request_id / correlation_id:
- domain-record identifier:
- operation identifier:
- inbound-event identifier:
- external-reference identifier:
- identifiers persisted:
- identifiers returned:
- identifiers logged:
- identifiers visible to operator:
```

### Error-specific Tests

```text
Error-specific tests added:
- ...

Error-specific tests run:
- ...

Verified behaviors:
- stable error_code
- response contract
- status transition
- retryable/permanent classification
- external-error normalization
- structured logging
- trace propagation
- sensitive-data masking
```

If no error-specific tests were required, write:

```text
No error-specific tests required.
```

If required tests were not run, explain:

```text
Required error-specific tests were not run because:
- ...
```

### Sensitive Data Verification

```text
Sensitive data exposure checked: Yes / No / Not applicable

Checked areas:
- API responses:
- Admin UI:
- logs:
- exception messages:
- reports:
- tests/fixtures:

Result:
- No raw secrets, credentials, tokens, Authorization headers, sensitive target/personal identifiers, or unsafe external payloads were exposed.
```

If exposure was found, list it explicitly:

```text
Sensitive data exposure found:
- Location:
  Data type:
  Impact:
  Required fix:
```

### Missing or Unresolved Error-handling Work

```text
- ...
```

If none:

```text
No missing or unresolved error-handling work.
```

### Required Follow-up Updates

```text
- ...
```

If none:

```text
No error handling, logging, or traceability follow-up required.
```

## 13. API Test Artifact Report — Optional in Concise Mode Unless API Behavior Was Added or Modified

Use this section when the Pack creates or modifies API endpoints, route paths, request/response contracts, authentication behavior, validation rules affecting API input, error codes, callback/inbound-event contracts, status endpoints, external-integration test endpoints, health endpoints, or API examples.

API Test Artifact Impact: Yes / No
Applicable Profile Checked: Yes / No / Not applicable
Artifact Checked: Yes / No / Not applicable
Artifact Updated: Yes / No / Not applicable
Environment/Configuration Updated: Yes / No / Not applicable
Artifact Path:
Environment/Configuration Path:
API Changes Covered:
- ...

Requests Added:
- ...

Requests Updated:
- ...

Requests Removed / Deprecated:
- ...

Sensitive Values Used: No
Required but Not Updated: Yes / No
Reason:
Required Follow-up Updates:
- ...

If the Pack did not create or modify API behavior, write:

API Test Artifact Report:
- Not applicable.

## 14. Scope Compliance

```text
Implementation scope respected: Yes / No
Governance maintenance exception used: Yes / No
Governance / maintenance updates directly related to this Pack: Yes / No / Not applicable
Unauthorized out-of-scope changes detected: Yes / No
Files listed under Do Not Change modified: Yes / No
```

Implementation Scope Notes

```text
...
```

Governance / Maintenance Files Changed
- File:
  Reason:
  Required By:
  Related Record:

If none:

No governance or maintenance files changed.

Unauthorized Out-of-Scope Changes
- File:
  Reason:
  Impact:
  Human Review Required:

If none:

No unauthorized out-of-scope changes detected.

---

## 15. Configuration / Settings Report — Optional in Concise Mode Unless Configuration or Settings Were Added, Changed, Required, or Need Follow-up

Use this section when the Pack creates, modifies, depends on, or requires configuration, environment variables, Admin Settings, external-integration settings, secret settings, queue/cache/log settings, test setup, or operator manual setup.

```text
Configuration / Settings Impact: Yes / No
Pack Configuration / Settings Requirements Completed: Yes / No / Not applicable
Config Files Changed: Yes / No / Not applicable
Environment Variables Required: Yes / No / Not applicable
Admin Settings Required: Yes / No / Not applicable
External Integration / Secret Settings Changed: Yes / No / Not applicable
Test / Development Setup Required: Yes / No / Not applicable
Operator Manual Setup Required: Yes / No / Not applicable
```

Config Files
- File:
  Key:
  Change:

If none:

No config files changed.
Environment Variables
- Name:
  Purpose:
  Placeholder:
  Sensitive: Yes / No

If none:

No environment variables required.
Admin Settings
- Setting:
  Location:
  Purpose:
  Operator Action Required: Yes / No

If none:

No Admin Settings required.
External Integration / Secret Settings
- Setting:
  Storage:
  Sensitive: Yes / No
  Masking Required: Yes / No

If none:

No external-integration or secret settings changed.
Test / Development Setup
- Required setup:
  Environment:
  Operator approval required:

If none:

No test/development setup required.
Required Follow-up Updates
- ...

If none:

No configuration or settings follow-up required.

---

## 16. UI / Admin UI Report — Optional in Concise Mode Unless UI Was Added, Changed, Required, or Needs Follow-up

Use this section when the Pack creates, modifies, depends on, or removes UI, Admin UI, settings UI, dashboard UI, monitoring UI, tables, forms, filters, buttons, actions, menus, widgets, cards, modals, badges, views, or permission-gated UI behavior.

```text
UI Impact: Yes / No
Admin UI Impact: Yes / No / Not applicable
Public UI Impact: Yes / No / Not applicable
Settings UI Impact: Yes / No / Not applicable
Dashboard / Monitoring UI Impact: Yes / No / Not applicable
Tables / Forms Impact: Yes / No / Not applicable
Actions / Buttons Impact: Yes / No / Not applicable
Navigation / Menu Impact: Yes / No / Not applicable
Permission-gated UI Impact: Yes / No / Not applicable
UI Requirements Completed: Yes / No / Not applicable
```

UI Files
- File:
  UI Surface:
  Change:
  Related Route / Controller / Form / Table:

If none:

No UI files changed.
UI Permissions / Visibility
- UI Element:
  Permission:
  Visibility Rule:
  Checked: Yes / No / Not applicable

If none:

No UI permission or visibility changes.
UI Validation
- Check:
  Result:
  Notes:

If no UI validation was required:

No UI validation required.
Required Follow-up Updates
- ...

If none:

No UI/Admin UI follow-up required.

If the Pack did not create or modify UI behavior, write:

UI/Admin UI Report:
- Not applicable.

---

## 17. Operator Documentation Report — Optional in Concise Mode Unless Operator-facing Behavior Was Added, Changed, Required, or Needs Follow-up

Use this section when the Pack creates, modifies, removes, renames, or changes operator-facing behavior for an owner or its operator interface.

```text
Operator Documentation Impact: Yes / No
Operator Documentation Update Required: Yes / No / Not applicable
Operator Documentation Updated: Yes / No / Not applicable
```

Guide File:
- <owner-declared-operator-guide-path>
Sections Updated:
- ...
Screenshots Added: Yes / No / Not applicable
Screenshots Pending: Yes / No / Not applicable
Required Follow-up Updates:
- ...

If no operator-facing behavior changed, write:

Operator Documentation Report:
- Not applicable.

## 18. Canonical Decisions Applied — Optional in Concise Mode Unless Conflict or Decision-Relevant

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

---

## 19. Operator Answers / Decisions Captured — Optional in Concise Mode Unless Any Answer or Decision Was Captured

Use this section if the operator answered clarification questions or made decisions during execution.

```text
- Operator Answer:
  Classification:
  Recorded In:
  Canonical Update Required:
  Follow-up Required:
```

Classification must follow:

```text
docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md
```

If there were no operator answers or decisions during execution, write:

```text
No operator answers or decisions were captured during this execution.
```

---

## 20. Deviations from Original Pack — Optional in Concise Mode Unless Any Deviation Exists

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

## 21. Assumptions Made — Optional in Concise Mode Unless Any Assumption Affects Execution or Future Packs

Record assumptions made during execution.

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

## 22. Index Updates — Optional in Concise Mode Unless Indexes Were Updated, Required, Blocked, or Need Follow-up

```text
Indexes Checked:
- ...

Completed:
- ...

Not required:
- ...

Required but not performed:
- ...

Required but blocked by explicit Pack restriction or Do Not Change:
- ...
```

If no index updates were required, write:

```text
No index updates required.
```

---

## 23. Documentation Maintenance — Optional in Concise Mode Unless AI Documentation Was Changed or Follow-up Is Required

This section is required.

If AI documentation was created, edited, moved, archived, or materially changed, complete the checklist below.

```text
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

Related run/review/change/remediation files checked:
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

## 24. Change / Remediation Links — Optional in Concise Mode Unless Related or Required

```text
Related Change Requests:
- ...

Related Remediation Packs:
- ...
```

If none are related, write:

```text
No related Change Requests or Remediation Packs.
```

---

## 25. Human Review Needed

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

## 26. Ready for Review

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

## 27. Risks / Notes — Optional in Concise Mode Unless Risks or Notes Exist

```text
- ...
```

If there are no risks or notes, write:

```text
No known risks or additional notes.
```

---

## 28. Open Questions — Optional in Concise Mode Unless Open Questions Exist

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

## 29. Rollback Notes — Optional in Concise Mode Unless Rollback Notes Are Relevant

```text
...
```

If rollback is not applicable, write:

```text
Rollback not applicable.
```

---

## 30. Required Follow-up Updates

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
