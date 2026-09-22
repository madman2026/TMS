# Question, Answer, and Decision Rules

## Purpose

Operator answers during AI execution must not disappear into chat history. The Agent must classify each answer and record it in the correct place.

## Answer Categories

When the Agent asks a question and receives an operator answer, classify it as one of:

1. Clarification
2. Implementation Note
3. Pack*Local Decision
4. Phase/Release Decision
5. Temporary Decision / Revisit Later
6. Change Request / Remediation Trigger

## 1. Clarification

A clarification only helps the current execution and does not change architecture or future Packs.

Record in:

* Agent Final Report
* Run Report

Do not update canonical files.

## 2. Implementation Note

An implementation note affects how the current Pack is implemented but is not a formal architecture decision.

Record in:

* Run Report
* Human Review / Operator Review section

If it may affect future Packs, also add it to the relevant Decision Log.

## 3. Pack*Local Decision

A Pack*local decision is valid only for the current Pack.

Record in:

* Run Report
* Decision Log if it affects later Packs

## 4. Phase/Release Decision

A Phase or Release decision changes official behavior for the current Phase or Release.

Record in:

* relevant Decision Log
* relevant canonical file, only after operator approval or if the Pack explicitly includes canonical update scope

## 5. Temporary Decision / Revisit Later

A temporary decision is accepted for the current Release or Phase but must be revisited later.

Record in:

* relevant Decision Log
* relevant canonical file if it affects current behavior

Set:

* `Status: Temporary`
* `Review At: <Release or Phase planning/review>`

## 6. Change Request / Remediation Trigger

If the answer changes already accepted scope, contradicts canonical decisions, changes an implemented contract, or requires correction after execution, first record the decision in the relevant Decision Log.

Then follow:

* `docs/ai/rules/CONFLICT*CHANGE*REMEDIATION*RULES.md`
* `<owner-docs-root>/changes/CHANGES*INDEX.md`
* `<owner-docs-root>/remediations/REMEDIATIONS*INDEX.md`

## 7. Decision Persistence Rule

Any non*trivial operator answer that affects architecture, scope, contracts, future Packs, current or future Phase behavior, Release behavior, canonical decisions, Change Requests, or Remediation Packs must first be recorded in the relevant Decision Log.

Canonical updates, Change Requests, and Remediation Packs may be created from an approved Decision Log entry, but they must not replace the Decision Log.

Clarifications that only affect the current execution may be recorded only in the Agent Final Report or Run Report.

If an operator answer triggers a canonical update, Change Request, or Remediation Pack, the Agent must record the decision source in the relevant Decision Log before creating or updating those files, unless an approved Decision Log entry or formal Review already exists.

Each related Canonical update, Change Request, or Remediation Pack must reference the Decision Log entry that approved or triggered it.

If the AI Agent is unsure whether an operator answer is a decision or a clarification, it must classify it using `QUESTION*ANSWER*DECISION*RULES.md` before continuing or ask from operator.

## 8. Decision Impact Requirements

When an operator answer or accepted decision changes implementation behavior that affects configuration, settings, multilingual behavior, UI, Admin UI, or operator*facing documentation, the Agent must record the impact or record a Required Follow*up Update.

This applies when the decision changes any of these:

* configuration files
* environment variables
* Admin Settings
* provider settings
* secret settings
* queue / cache / logging settings
* test or development setup
* visible text
* translation files
* API messages
* validation messages
* RTL/LTR behavior
* Admin UI pages
* settings UI
* forms
* tables
* filters
* actions
* buttons
* menus
* dashboards
* monitoring UI
* permission*gated UI
* operator*facing workflow
* Admin User Guide content
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
* external-integration snapshot behavior
* owner-defined configuration snapshot behavior
* historical context preservation

If the decision requires an update that cannot be completed immediately, the Agent must record it as a Required Follow*up Update.

The Agent must not silently accept a decision that changes operator*visible behavior, configuration behavior, translation behavior, or UI behavior without identifying the affected documentation, Pack, Remediation Pack, Canonical file, guide, or report.

## 9. Operator Documentation Decision Impact Rule

When an operator answer or accepted decision changes how a human operator should configure, use, test, monitor, troubleshoot, or safely operate an owner component or its administration surface, the Agent must update the operator documentation declared by that owner's profile or record a Required Follow*up Update.

Resolve the primary guide, rules, template, and asset paths from the applicable owner profile.

This rule applies when the operator answer or accepted decision changes any of these:

* recommended operator workflow
* setup order
* external-integration management and test behavior
* owner-defined configuration, method, route, template, or token workflow
* Admin Settings behavior
* operational-record monitoring and reporting behavior
* callback or inbound-event behavior
* Retry behavior
* Cancel behavior
* Dead Letter behavior
* bulk-operation behavior
* permission or role behavior
* troubleshooting instructions
* operator*visible error handling
* sensitive admin action usage

If the guide update is required but cannot be completed in the current scope, the Agent must record it as:

```text
Required Follow*up Update:
* Update `docs/ADMIN*USER*GUIDE/ADMIN*USER*GUIDE.fa.md`
* Reason:
* Affected guide sections:
```

The Agent must not duplicate the full Admin User Guide content inside Decision Logs, Agent Final Reports, Run Reports, Change Requests, or Remediation Packs.

Those files may summarize the guide impact and reference the guide file.

## 10. Canonical Update Rule

The Agent must not update canonical files based on an operator answer unless:

1. the operator explicitly says this is a decision, or
2. the current Pack includes canonical update in scope, or
3. the Agent asks for confirmation and the operator approves.

## 11. Error Handling / Logging / Traceability Decision Impact Rule

When an operator answer or approved decision creates, changes, restricts, deprecates, or clarifies error-handling behavior, the Agent must explicitly record its Error Handling / Logging / Traceability impact.

This applies when the decision affects:

```text
- API error contracts
- validation error behavior
- UI or Admin UI error messages
- stable error_code values
- exception classes or exception mapping
- external-error normalization
- callback or inbound-event error behavior
- queue or Job failure behavior
- retry, permanent-failure, or dead-letter behavior
- owner-managed state or operation-attempt transitions
- structured log events or context
- request, correlation, domain-record, operation, inbound-event, or external-reference identifiers
- sensitive-data masking, redaction, omission, or visibility
- error-specific testing or operator troubleshooting
```

The Agent must not leave these effects implicit in chat history.

### Decision Impact Classification

For every relevant operator answer or decision, record:

```text
Error Handling Impact: Yes / No
API Error Contract Impact: Yes / No / Not applicable
UI / Admin UI Error Impact: Yes / No / Not applicable
Error Code Impact: Yes / No / Not applicable
Exception Handling Impact: Yes / No / Not applicable
External Error Normalization Impact: Yes / No / Not applicable
Retry / Failure Classification Impact: Yes / No / Not applicable
Status Model Impact: Yes / No / Not applicable
Logging Impact: Yes / No / Not applicable
Traceability Impact: Yes / No / Not applicable
Sensitive Data Impact: Yes / No / Not applicable
Testing Impact: Yes / No / Not applicable
Documentation Impact: Yes / No / Not applicable
```

If all impacts are No or Not applicable, record:

```text
No error handling, logging, or traceability decision impact.
```

### Required Decision Details

When impact exists, the related Decision Log entry must record applicable details:

```text
Affected error scenario:
Affected component or layer:

Previous behavior:
Approved behavior:

Stable error_code:
HTTP status:
Human-readable message behavior:

Retryable: Yes / No / Not applicable
Permanent: Yes / No / Not applicable
Admin action required: Yes / No / Not applicable

Owner-managed state impact:
Operation-attempt impact:

Exception mapping:
External error mapping:

Log event:
Log level:
Required structured context:

Trace identifiers:
Trace propagation or storage impact:

Sensitive values:
Required handling: mask / redact / omit / encrypt / permission-gate

Tests required:
Operator review required:
Admin User Guide impact:
```

Only applicable fields must be completed.

The decision record must be specific enough that later AI Packs do not need to guess the approved behavior.

### Stable Error Code Decision Rule

A decision that introduces or changes an error condition must explicitly determine whether it:

```text
- creates a new error_code
- reuses an existing error_code
- changes the meaning of an existing error_code
- deprecates an existing error_code
```

The Agent must not silently invent a new error code during implementation when the approved decision leaves the error contract unresolved.

If the exact error code is intentionally deferred, record:

```text
Error code decision: Deferred
Reason:
Required resolution point:
Implementation allowed before resolution: Yes / No
```

Changing the meaning of an existing stable error code must be treated as a contract-impacting decision.

### Classification Decision Rule

Decisions affecting failure behavior must explicitly define applicable classification:

```text
- retryable or non-retryable
- temporary or permanent
- admin action required or not required
- user/operator visible or internal only
- owner-managed state impact
- operation-attempt impact
- audit requirement
- log severity
```

The Agent must not infer retryability, permanent-failure behavior, admin-action requirements, or state transitions from vague wording.

### Traceability Decision Rule

When a decision affects traceability, it must state which identifiers are:

```text
- created
- accepted from an upstream service
- propagated
- persisted
- returned through API
- shown in Admin UI
- included in logs
- used to correlate inbound events or external reports
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

A decision must not claim end-to-end traceability merely because unrelated identifiers exist at separate layers.

### Sensitive Data Decision Rule

When a decision affects error details, technical visibility, raw external information, callbacks/inbound events, logs, reports, or Admin UI, it must state:

```text
- which data is sensitive
- where it may appear
- whether it must be masked, redacted, omitted, encrypted, or permission-gated
- which role or permission may view additional technical details
- whether raw values may be persisted
- whether tests are required to verify non-exposure
```

A decision must never include real secrets, tokens, credentials, Authorization headers, or other sensitive values.

### Decision Propagation Rule

When an approved decision has Error Handling / Logging / Traceability impact, the Agent must identify the required propagation targets.

Applicable targets may include:

```text
- relevant Decision Log
- Release or Phase canonical file
- AI Pack
- Remediation Pack
- API contract documentation
- Admin User Guide
- tests
- owner-declared API test artifact
- monitoring or troubleshooting documentation
- Run Report
```

The Decision Log remains the source of the approved decision.

AI Packs, canonical files, reports, and documentation must reference or implement the decision without redefining it inconsistently.

### Unresolved Decision Rule

If an operator answer identifies a problem but does not approve the expected behavior, classification, error code, visibility, logging policy, or traceability contract, record the item as unresolved.

Use:

```text
Status: Open / Temporary / Deferred

Unresolved items:
- ...

Implementation impact:
- blocked / partially allowed / follow-up required

Required resolution:
- ...
```

The Agent must not convert an unresolved error-handling discussion into an approved architecture or contract decision.

## 12. Required Agent Behavior

Before continuing after an operator answer, the Agent should state:

* answer category
* where it will be recorded
* whether canonical update is required
* whether operator confirmation is required
* whether the answer has Error Handling / Logging / Traceability impact and which Decision Log, canonical file, AI Pack, Remediation Pack, test, report, or operator documentation must receive the approved decision
