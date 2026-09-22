# Conflict, Change, and Remediation Rules

This authority rule applies when resolving or reporting conflicts. It does not replace startup routing, Canonical file creation rules, or general documentation maintenance rules.

## Purpose

This file defines how the AI Agent must handle conflicts, Change Requests, and Remediation Packs.

This file owns:

* conflict handling
* authority handling during conflicts
* stop conditions caused by conflicts
* blocked Pack recovery workflow
* Change Request rules
* Remediation Pack rules
* Pack immutability after execution
* partial changes and revert behavior after blocked execution
* no-duplication boundaries between Decision Records, Change Requests, Remediation Packs, Canonical files, Runs, and Reviews

This file does not own:

* operator answer classification
* Canonical file creation or update rules
* Run Report or Agent Final Report rules
* Review rules
* Git or commit rules
* documentation maintenance and index rules
* template structure

Those topics are routed through `RULES-INDEX.md`.

---

## 1. Authority During Conflict

Authority handling in this file applies only when a conflict is found.

It does not replace startup routing, Canonical file rules, documentation maintenance rules, or general execution rules.

### 1.1 Current Implementation State

For determining what currently exists in the repository during conflict analysis:

```text
1. Actual source code in the repository
2. Accepted Remediation results
3. Accepted Run Reports
4. Accepted Reviews
```

Run Reports and Reviews provide execution evidence and context, but they do not override actual source code.

### 1.2 Intended Architecture and Official Contracts

For determining intended behavior, approved architecture, and official contracts during conflict analysis:

```text
1. AGENTS.md for agent behavior rules
2. Global Canonical Decisions
3. Release Canonical Decisions
4. Phase Canonical Decisions
5. Approved Guides
6. Current AI Pack
```

### 1.3 Reference-Only Sources

Reference-only sources may provide context, evidence, or historical reasoning, but they must not override implementation reality or approved canonical decisions.

```text
1. Decision Logs
2. Previous Run Reports
3. Offline documentation
4. Online documentation
```

### 1.4 No Silent Resolution

Authority order helps identify the higher-authority source.

Authority order does not authorize the AI Agent to silently resolve conflicts.

If two authoritative sources conflict, the Agent must stop, report the conflict, and follow the workflow in this file.

---

## 2. Conflict Rule

A conflict exists when one accepted or active source requires behavior, scope, structure, contract, or implementation that contradicts another accepted or active source.

Conflicts may involve:

* actual source code
* Pack scope
* current AI Pack instructions
* accepted Run Reports
* accepted Reviews
* operator instructions
* Decision Records
* Change Requests
* Remediation Packs
* Global Canonical files
* Release Canonical files
* Phase Canonical files
* Release or Phase Guides
* source/reference documentation

If implementation, Pack scope, code reality, operator instruction, guide content, AI Pack instructions, Review findings, Change Requests, Remediation Packs, or Canonical decisions conflict with accepted decisions or implemented contracts, the Agent must stop and report the conflict.

The Agent must not silently choose one side.

---

## 3. Stop Condition Rule

If a conflict affects scope, architecture, data, security, tests, contracts, accepted behavior, or previous execution history, the current Pack execution must stop.

The Agent must clearly state:

```text
Stop Condition Triggered.
Change Request Required: Yes / No / Unknown.
Remediation Required: Yes / No / Unknown.
Current Pack execution cannot continue safely.
```

The Agent must not present the blocked Pack as successfully completed.

The Agent may produce a blocked or stopped execution report when required, but it must not produce a successful completion report for the blocked Pack.

---

## 4. Conflict Workflow

When a conflict is detected, the Agent must:

1. stop the current implementation if the conflict affects scope, architecture, data, security, tests, contracts, accepted behavior, or execution history
2. identify the conflicting sources
3. summarize the conflicting decisions, contracts, instructions, findings, or implementation facts
4. check authority during conflict using this file
5. check Canonical validation boundaries when Canonical files are involved
6. classify operator answers when operator input is involved
7. identify whether a Decision Record exists or is required
8. identify whether a Change Request is required
9. identify whether a Remediation Pack is required
10. identify whether a Review is required
11. identify whether a Canonical update is required
12. continue only after the conflict path is clear

---

## 5. Blocked Pack Recovery Workflow

If a Pack is blocked by a conflict, the correct flow is:

1. stop the current Pack execution
2. report the blocked state
3. do not mark the Pack as successfully completed
4. inspect partial changes made during the blocked Pack
5. revert incomplete or unsafe partial changes unless the operator explicitly approves keeping them
6. create or update a Decision Record if a decision exists or is required
7. create a Change Request if accepted behavior, scope, or contract must change
8. create a Remediation Pack if controlled implementation correction is required
9. execute the Remediation Pack
10. report the Remediation execution using the reporting rules
11. create a Review only if review rules require it
12. update Canonical files only if canonical rules allow it
13. update related indexes only if documentation maintenance rules require it
14. commit only after validation and operator workflow requirements are satisfied
15. normalize the blocked Pack against the updated decisions, Canonical files, and implementation state
16. restart or re-execute the blocked Pack from a safe state
17. report the re-executed Pack using the reporting rules
18. commit the re-executed Pack only after validation

Example flow:

```text
Pack 3 starts
→ conflict found
→ Pack 3 blocked
→ partial Pack 3 changes inspected
→ unsafe partial changes reverted
→ Decision Record created or updated
→ Change Request created and approved
→ Remediation Pack created
→ Remediation Pack executed
→ Remediation reported
→ Remediation reviewed if required
→ Canonical files updated if required and allowed
→ related indexes synced if required
→ Remediation committed after validation
→ Pack 3 normalized
→ Pack 3 restarted or re-executed
→ Pack 3 reported
→ Pack 3 committed after validation
→ Phase Review updated at phase end if required
```

---

## 6. Blocked Pack Outcome Rule

When a Pack is blocked by a conflict, the original Pack must receive a clear outcome before work continues.

Allowed outcomes:

```text
blocked
re-run-after-remediation
needs-normalization
needs-regeneration
superseded
```

Use blocked when execution stopped and the Pack cannot safely continue yet.

Use re-run-after-remediation when the required remediation is small, additive, non-breaking, and the original Pack remains valid after remediation.

Use needs-normalization when the Pack may still be valid but must be checked against updated decisions, Canonical files, contracts, or implementation state before re-execution.

Use needs-regeneration when the Pack instructions no longer match the accepted contract and the Pack must be regenerated before execution.

Use superseded when the original Pack should not be reused and a replacement Pack must be created.

The Agent must not silently continue a blocked Pack after remediation.

Before returning to the blocked Pack, the Agent must determine whether the Pack should be re-run, normalized, regenerated, or superseded.

---

## 7. Canonical-Only Conflict Outcome Rule

Not every conflict requires a Remediation Pack.

If a conflict requires changing or correcting a Canonical file but does not require changing source code, executed Packs, migrations, tests, Run Reports, or accepted implementation, the Agent must classify the outcome before creating a Remediation Pack.

Allowed Canonical-only outcomes:

```text
canonical-correction
canonical-contract-change
future-pack-normalization
no-remediation-required
```

Use canonical-correction when the Canonical file is incomplete, stale, or inaccurate, but the accepted behavior does not change.

Use canonical-contract-change when an accepted Canonical contract changes, but the implementation already complies or no implementation has been executed yet.

Use future-pack-normalization when the Canonical update affects only pending or future Packs.

Use no-remediation-required when no source code, executed Pack, accepted Run, migration, test, or implementation correction is required.

If an accepted contract changes, a Change Request may still be required even when no Remediation Pack is required.

A Remediation Pack is required only when controlled implementation correction is needed

---

## 8. Partial Changes and Revert Rule

If a Pack is blocked after partial implementation, the Agent must identify whether partial changes exist.

If partial changes are incomplete, unsafe, unrelated after conflict resolution, or likely to confuse future execution, they should be reverted before creating or executing the Remediation Pack.

Partial changes may be kept only when all of the following are true:

1. the operator explicitly approves keeping them
2. they are safe
3. they do not violate current Canonical decisions
4. they are clearly reported
5. they do not hide the blocked state of the Pack

The Agent must never commit partial blocked Pack changes as a successful Pack result.

---

## 9. Change Request Rule

Use a Change Request when a previously accepted decision, scope, contract, Canonical decision, or implemented behavior must change.

A Change Request owns:

```text
what must change
what is affected
risk
approval status
acceptance criteria
rollback plan
```

A Change Request must reference the originating Decision Record, Review, Run Report, Remediation, Canonical file, or conflict source when applicable.

Do not create a Change Request for small clarifications or normal Pack execution notes.

Do not duplicate full Decision Record reasoning inside the Change Request.

The Change Request template owns the file structure. Change Requests are tracked through:

- `<owner-docs-root>/changes/CHANGES-INDEX.md`
- `docs/ai/templates/CHANGE-REQUEST-TEMPLATE.md`

---

## 10. Remediation Pack Rule

Use a Remediation Pack when accepted or partially accepted work must be corrected after an approved Change Request, Review finding, failed validation, invalid implementation, or accepted conflict resolution.

A Remediation Pack owns:

```text
how the approved correction will be implemented
which files may be changed
which files must not be changed
which tests must be added or run
how completion will be validated
```

A Remediation Pack must reference the related Change Request and, when available, the originating Decision Record, Review, Run Report, or Canonical file.

Do not duplicate full Change Request content inside the Remediation Pack.

The Remediation Pack should implement the approved change. It should not redefine the approved problem or re-argue the decision.

The Remediation Pack template owns the file structure. Remediations are tracked through:

- `<owner-docs-root>/remediations/REMEDIATIONS-INDEX.md`
- `docs/ai/templates/REMEDIATION-PACK-TEMPLATE.md`

---

## 11. Remediation Impact Requirements

A Remediation Pack must explicitly state whether the approved correction affects:

* configuration or settings
* environment variables
* Admin Settings
* external-integration settings
* secret settings
* queue / cache / logging settings
* test or development setup
* multilingual or translation behavior
* visible text
* API messages
* validation messages
* RTL/LTR behavior
* UI or Admin UI
* settings UI
* tables
* forms
* filters
* actions
* buttons
* menus
* dashboards
* monitoring UI
* permission-gated UI
* Admin User Guide / operator-facing usage documentation
* database tables
* columns
* migrations
* foreign keys
* referential actions
* model relationships
* indexes
* unique constraints
* data backfills
* rollback behavior
* data-loss risk
* audit/history/retention behavior
* snapshot fields
* payload snapshots
* external-integration snapshot data
* route/template/token snapshots
* historical context preservation

If any of these areas are affected, the Remediation Pack must include the required changes or record a Required Follow-up Update.

If none are affected, the Remediation Pack must explicitly say that there is no impact.

A Remediation Pack should include these impact sections:

```text
Configuration / Settings Impact
Multilingual / Translation Impact
UI / Admin UI Impact
Operator Documentation Impact
Data Model / Migration / Relationship Impact
```

If an impact area is not applicable, the Remediation Pack must explicitly say that there is no impact.

The Agent must not treat a remediation as code-only when the correction changes operator-facing behavior, UI behavior, settings behavior, translation behavior, permissions, visible messages, or operational workflow.

## 12. Error Handling / Logging / Traceability Remediation Rule

When a conflict, failed validation, Review finding, accepted Run result, security finding, or implementation defect affects error handling, error reporting, logging, or traceability, the approved correction must be implemented through a Remediation Pack when controlled implementation work is required.

The Remediation Pack must follow:

```text
docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md
docs/ai/rules/TEST-AND-VALIDATION-RULES.md
docs/ai/templates/REMEDIATION-PACK-TEMPLATE.md
```

This rule applies when the defect involves:

```text
- vague, incomplete, misleading, or unsafe API errors
- unclear UI or Admin UI error messages
- missing or unstable error_code values
- incorrect HTTP status codes
- incorrect validation error structure
- raw exception exposure
- silently swallowed exceptions
- incorrect external-error mapping
- missing retryable, permanent, or admin-action-required classification
- incorrect owner-managed state or operation-attempt transitions
- incomplete or unstructured logs
- missing log context
- broken request or correlation tracing
- missing domain-record, operation, inbound-event, or external-reference identifiers
- sensitive data exposure
- missing or weak error-specific tests
```

### 12.1 Explicit Impact Rule

Every relevant Remediation Pack must explicitly state:

```text
Error Handling Impact: Yes / No
API Error Contract Impact: Yes / No / Not applicable
UI / Admin UI Error Impact: Yes / No / Not applicable
Stable Error Code Impact: Yes / No / Not applicable
Exception Handling Impact: Yes / No / Not applicable
External Error Normalization Impact: Yes / No / Not applicable
Retry / Permanent Failure Classification Impact: Yes / No / Not applicable
Owner-managed / Operation-attempt State Impact: Yes / No / Not applicable
Structured Logging Impact: Yes / No / Not applicable
Traceability Impact: Yes / No / Not applicable
Sensitive Data Impact: Yes / No / Not applicable
Historical Data or Log Impact: Yes / No / Unknown
Error-specific Test Impact: Yes / No / Not applicable
Operator Documentation Impact: Yes / No / Not applicable
```

If there is no impact, write:

```text
No error handling, logging, or traceability impact.
```

The Agent must not leave these impacts implicit.

### 12.2 Defect Evidence Rule

The Remediation Pack must identify the actual defective behavior and its evidence.

Record:

```text
Defective behavior:
- ...

Evidence source:
- source code
- failed test
- API response
- Admin UI behavior
- log output
- database state
- Run Report
- Review finding
- operator reproduction
- security finding

Root cause:
- ...

Required corrected behavior:
- ...
```

Do not create a Remediation Pack based only on vague statements such as:

```text
Improve errors.
Fix logging.
Make tracing better.
Handle exceptions properly.
```

The defect and corrected behavior must be observable and testable.

### 12.3 No Silent Contract Redefinition Rule

A Remediation Pack implements an approved correction.

It must not silently redefine:

```text
- stable error codes
- API response contracts
- HTTP status codes
- retryability
- permanent-failure behavior
- admin-action-required behavior
- owner-managed states
- operation-attempt states
- external-error mappings
- log visibility
- sensitive-data permissions
```

If the required corrected behavior has not already been approved, the Agent must identify whether a Decision Record or Change Request is required before implementing the remediation.

The Agent must not invent an error contract merely because the current implementation is incomplete.

### 12.4 Stable Error Code Correction Rule

When the remediation affects an error condition, it must explicitly state whether the related `error_code` is:

```text
- added
- corrected
- reused
- deprecated
- unchanged
```

Changing the meaning of an existing stable error code is a contract change.

The remediation must identify affected consumers such as:

```text
- API clients
- Admin UI
- tests
- owner-declared API test artifacts
- logs
- reports
- monitoring
- troubleshooting documentation
```

The Agent must not replace one vague error with another vague error.

### 12.5 API and UI Error Correction Rule

When an API error is corrected, the remediation must define:

```text
- endpoint
- HTTP method
- error scenario
- required HTTP status
- stable error_code
- required response structure
- request_id or trace identifier
- field-level validation structure when applicable
- sensitive fields that must not be returned
```

When an operator-facing error is corrected, the remediation must define:

```text
- UI area
- clear explanation of the failure
- suggested operator action
- retry guidance
- admin-action guidance
- error_code visibility
- trace identifier visibility
- permission required for technical details
- translation impact
```

Raw exceptions, stack traces, secrets, credentials, tokens, and unsafe external payloads must not be exposed through API or Admin UI.

### 12.6 Exception Correction Rule

When the remediation changes exception behavior, it must identify:

```text
- exception class
- where it is thrown
- where it is handled
- mapped error_code
- observable result
- log event
- retry or framework failure behavior
- whether the raw exception message is safe
```

Exceptions must not be silently swallowed.

An exception must result in an approved observable outcome such as:

```text
- normalized failure result
- persisted failed operation attempt, when required by the owner contract
- approved status transition
- structured log
- scheduled retry
- dead-letter transition
- approved framework or queue failure
```

### 12.7 External Error Normalization Correction Rule

When the defect involves an external-integration error, the Remediation Pack must define the mapping:

```text
External error code or condition
→ stable internal error_code
→ retryable or permanent classification
→ admin action requirement
→ owner-managed state impact
→ operation-attempt impact
→ safe external error snapshot
```

Core owner code must not depend directly on raw external-integration error text.

Integration-specific logic must remain within the approved adapter or integration-specific infrastructure boundary.

Tests must use a fake, mock, stub, or safe fixture unless a real external call is explicitly approved.

### 12.8 Error Classification and Status Correction Rule

When the remediation affects failure classification, it must explicitly verify:

```text
- retryable
- non-retryable
- temporary
- permanent
- admin action required
- user/operator visible
- audit required
- log severity
- owner-managed state
- operation-attempt state
- retry scheduling
- dead-letter behavior
```

The remediation must prevent invalid outcomes such as:

```text
- retrying permanent configuration failures indefinitely
- marking temporary external timeouts as permanent without policy
- retrying permanent invalid input without policy
- downgrading terminal owner-managed states
- reporting failed operation attempts as successful
```

### 12.9 Structured Logging Correction Rule

When logging is corrected, the Remediation Pack must define:

```text
- stable event name
- trigger
- log level
- required structured context
- trace identifiers
- fields to mask or omit
```

Applicable context may include:

```text
error_code
request_id
correlation_id
domain_record_id
operation_id
inbound_event_id
external_integration_id
external_reference_id
source_system
source_reference
retryable
admin_action_required
exception_class
```

Only relevant fields should be required.

A remediation must not be considered complete merely because a generic log statement was added.

Logs such as these are insufficient:

```text
Operation failed.
External integration error.
Inbound event failed.
Something went wrong.
```

### 12.10 Traceability Correction Rule

When traceability is affected, the Remediation Pack must define how the relevant identifier is:

```text
- created or accepted
- propagated
- persisted
- returned
- logged
- shown to an authorized operator
```

The remediation must identify the applicable flow:

```text
API or Admin entry point
→ persisted domain record
→ queued Job or background operation
→ operation attempt
→ external interaction
→ inbound event or result report
→ Admin UI
→ structured logs
```

Applicable identifiers may include:

```text
request_id
correlation_id
domain_record_id
operation_id
inbound_event_id
external_reference_id
source_system
source_reference
```

Independent identifiers at separate layers do not prove end-to-end traceability.

The remediation must define the correlation relationship when one identifier cannot be propagated through the entire flow.

### 12.11 Sensitive Data Remediation Rule

When a remediation addresses or may affect sensitive-data exposure, it must identify:

```text
- sensitive data category
- exposure location
- current unsafe behavior
- required handling
- authorized visibility
- historical cleanup requirement
- credential or token rotation requirement
- regression test requirement
```

Required handling may be:

```text
mask
redact
omit
encrypt
permission-gate
```

The Remediation Pack must never contain the real sensitive value.

Applicable sensitive data includes:

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
- unsafe raw external payloads
```

If sensitive data was previously written to logs, reports, exports, database snapshots, or external monitoring systems, the remediation must determine whether historical cleanup or secret rotation is required.

### 12.12 Historical Compatibility Rule

The remediation must identify whether existing data or contracts are affected:

```text
Existing API clients affected: Yes / No / Unknown
Existing error-code consumers affected: Yes / No / Unknown
Existing database records affected: Yes / No / Unknown
Existing domain-record and operation-attempt history affected: Yes / No / Unknown
Existing inbound-event or report records affected: Yes / No / Unknown
Existing logs affected: Yes / No / Unknown
Existing monitoring or alert rules affected: Yes / No / Unknown
Data repair required: Yes / No / Unknown
Reprocessing required: Yes / No / Unknown
```

Historical operational, inbound-event, result/report, and audit records must not be silently rewritten.

Any historical correction must be explicitly approved, scoped, reversible where practical, and reported.

### 12.13 Error-specific Validation Rule

A remediation affecting this area must add or run meaningful tests that prove the corrected behavior.

Applicable tests must verify:

```text
- stable error_code
- expected HTTP status
- API response structure
- UI-safe behavior
- retryable/permanent classification
- admin-action-required classification
- owner-managed and operation-attempt state transitions
- external-error normalization
- structured log event and context
- trace identifier propagation
- sensitive-data masking or absence
- prohibited behavior does not occur
```

A test that checks only an HTTP status, exception existence, generic failure state, or logging method call is insufficient.

The Agent must ask:

```text
If the original defect still existed, would this test fail?
```

If the answer is No or unclear, the test must be improved.

### 12.14 Completion and Follow-up Rule

The remediation must not be marked complete while any required item remains unresolved, including:

```text
- vague API or UI error remains
- stable error_code is missing
- external-error mapping is missing
- classification is unresolved
- status behavior is incorrect
- required log context is missing
- trace propagation is incomplete
- sensitive-data handling is unverified
- required error-specific test was not run
- operator UI review is still required
```

Unresolved work must be recorded under:

```text
Required Follow-up Updates
```

Each follow-up must state:

```text
- required work
- reason
- affected area
- blocking or non-blocking status
- expected owner or future Pack when known
```

## 13. Operator Documentation Impact in Remediation Packs

When a Remediation Pack fixes, changes, removes, renames, or corrects operator-facing behavior in an owner component or its administration surface, it must also update the operator documentation declared by the applicable owner profile or record a Required Follow-up Update.

Resolve the primary guide, rule, template, and asset paths from the applicable owner profile.

Operator-facing remediation includes changes to:

* Admin UI pages
* menus
* forms
* tables
* filters
* actions or buttons
* settings
* external-integration management and testing
* owner-defined configuration, method, and route management
* owner-defined template, token, or configuration workflow
* operational-record monitoring
* operational-record or attempt views
* callback or inbound-event views
* result or status-report views
* Retry workflow
* Cancel workflow
* Dead Letter workflow
* bulk-operation workflow
* monitoring dashboards
* reports
* permissions
* user roles
* troubleshooting behavior
* recommended operator sequence
* operator-visible error messages

The Remediation Pack must include an operator-documentation impact section with:

```text
Operator Documentation Impact: Yes / No
Operator Documentation Update Required: Yes / No / Not applicable
Guide File:
- <resolved from applicable owner profile>
Sections to Update:
- ...
Screenshots Required: Yes / No / Not applicable
Required Follow-up Updates:
- ...
```

If the Remediation Pack changes operator-facing behavior but cannot update the guide within its scope, it must record the guide update as a Required Follow-up Update.

If the remediation has no operator-facing impact, write:

```text
Operator Documentation Impact: No
Operator Documentation Update Required: Not applicable
```

The Remediation Pack must not duplicate the full operator guide content.

It may summarize the guide impact and link to the profile-declared guide path.

## 14. Pack Immutability Rule

Original AI Packs should remain immutable after execution.

Do not rewrite the original Pack to hide history.

Use Change Requests and Remediation Packs for corrections.

A Pack may be superseded, rejected, remediated, or referenced, but its executed historical content should not be silently rewritten.

---

## 15. Conflict Report Requirements

When reporting a conflict, include:

```text
Conflict Type:
Stop Condition Triggered: Yes / No
Blocked Pack:
Blocked Pack Outcome: blocked / re-run-after-remediation / needs-normalization / needs-regeneration / superseded / Unknown
Conflicting Sources:
Conflicting Decisions / Contracts / Facts:
Authority Analysis:
Affected Release:
Affected Phase:
Affected Pack:
Affected Runs:
Affected Reviews:
Affected Implementation Areas:
Partial Changes Exist: Yes / No / Unknown
Partial Changes Must Be Reverted: Yes / No / Unknown
Decision Record Required: Yes / No
Canonical Update Required: Yes / No / Unknown
Change Request Required: Yes / No / Unknown
Remediation Pack Required: Yes / No / Unknown
Review Required: Yes / No / Unknown
Operator Approval Required: Yes / No
Recommended Next Action:
```

This section defines conflict-specific report content only.

Full reporting rules are owned by the reporting rules.

---

## 16. No Duplication Rule

Decision Records, Change Requests, Remediation Packs, Canonical files, Run Reports, and Reviews must reference each other instead of duplicating full content.

Use this ownership model:

```text
Decision Record = why the decision was made
Canonical = current accepted decision or contract
Change Request = what must change
Remediation Pack = how the approved correction will be implemented
Run Report = what actually happened during execution
Review = whether the result was accepted or what was found
```

Do not duplicate full decision rationale when an approved Decision Record already exists.

Do not duplicate full Change Request content inside a Remediation Pack.

Do not duplicate full Remediation implementation details inside Canonical files.
