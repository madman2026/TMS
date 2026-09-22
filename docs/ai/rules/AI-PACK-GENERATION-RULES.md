# AI Pack Generation Rules

## Purpose

This file defines rules for creating or updating AI Packs.

AI Pack templates define structure and placeholders.

This file defines how the Agent must decide what content belongs in each Pack section.

## Canonical Decisions Requirement

When generating or updating an AI Pack, the Agent must follow the applicable Canonical files resolved through the selected owner's index or manifest.

The Agent must not create Pack instructions that conflict with canonical decisions.

## Source and Reference Documentation Path Rule

When generating, updating, reviewing, normalizing, or remediating an AI Pack, the Agent must not blindly copy old reference documentation paths from legacy Packs, old guides, chat history, or archived documentation.

The Agent must resolve current source and reference documentation paths through:

```text
<project-docs-root>/references/REFERENCES-INDEX.md
<project-docs-root>/references/SOURCE-DOCS-INDEX.md
docs/ai/start/CONTEXT-MAP.md
```

If a referenced source document has moved, been renamed, split, archived, or replaced, the Pack must use the current active path from the relevant index.

If the current path is unclear, the Agent must list the missing or ambiguous reference under `Required Follow-up Updates` instead of inventing a path.

Do not include broad reference folders in `Files to Read / Reference`.

List only the specific source or reference files required for the Pack.

## Architecture Constraints Selection

When generating or updating an AI Pack, the Agent must include only architecture constraints that are directly relevant to the Pack scope.

The Agent must not add unrelated architecture constraints to a Pack.

## Project and Framework Source-Guidance Constraints

When a Pack creates or modifies framework-, CMS-, runtime-, language-, or project-specific implementation, resolve the applicable project profile and include only the source-guidance files and concrete constraints required by the Pack.

The Pack must identify the applicable project profile under `Files to Read / Reference`. It must preserve framework integration boundaries, coding conventions, protected areas, source-vs-generated ownership, validation placement, error-handling conventions, and required language/runtime checks declared there.

Do not duplicate an entire project profile or source guide inside a Pack. Include the minimum relevant constraints in the appropriate Pack sections and keep reusable requirements technology-neutral.

Always retain these shared constraints:

- follow actual repository structure and current project conventions;
- keep domain/application behavior out of framework UI and integration adapters unless Canonical files assign it there;
- avoid duplicated decision logic, N+1-style access patterns, unsafe raw data access, unrelated schema changes, opportunistic refactors, and capabilities outside Release/Phase/Pack scope;
- run or report the syntax, formatting, static-analysis, and test commands required by the applicable profile and Pack.

## Test Quality Pack Generation Rule

When generating or updating an AI Pack, the Agent must define tests that verify the actual behavior introduced or changed by the Pack.

The Pack must not request vague or superficial tests such as:

```text id="t9wn4x"
- test that it works
- test success response
- check status code
- check record exists
- run basic feature test
```

Instead, the Pack must define what each test must prove.

For every test listed under `Tests to Add`, the Pack should state:

```text id="4gszcb"
- Test name:
- Purpose:
- Behavior proven:
- Main assertions:
- Required setup:
- Expected side effects:
- Negative/error cases:
- Related acceptance criteria:
```

When the Pack changes API behavior, tests must verify:

```text id="rdb1m4"
- expected HTTP status
- response shape
- required response fields
- stable error_code values
- validation error structure
- authentication and authorization behavior
- safe side effects
```

When the Pack changes database behavior, tests must verify:

```text id="3331gx"
- meaningful persisted fields
- relationships and foreign keys where testable
- unique/idempotency constraints where applicable
- snapshot fields where required
- delete/update behavior when in scope
```

When the Pack changes queue, job, event, external-integration, inbound-event, retry, or state behavior, tests must verify:

```text id="b6va4w"
- correct job/event dispatch
- correct service or adapter is used
- external calls are faked or mocked
- status transition is valid
- duplicate execution is prevented
- terminal state protection is respected
- retryable and permanent failures are classified correctly when applicable
```

When the Pack changes Admin UI, permissions, settings, or security-sensitive behavior, tests must verify:

```text id="m26g1e"
- permitted user behavior
- unauthorized/forbidden behavior
- sensitive data masking
- settings validation
- operator-visible error behavior
```

If a Pack does not require tests, it must explicitly say why.

Valid reasons may include:

```text id="2q0m0t"
- documentation-only change
- template-only change
- no executable behavior changed
- test coverage intentionally deferred with required follow-up
```

If test coverage is deferred, the Pack must record it under `Required Follow-up Updates`.

## Error Handling / Logging / Traceability Pack Generation Rule

Every AI Pack must explicitly state whether it creates, modifies, depends on, or removes error handling, error reporting, exception handling, logging, tracing, or error visibility behavior.

Every Pack must include an `Error Handling / Logging / Traceability Requirements` section.

If the Pack has no impact in this area, the section must explicitly say:

```text
No error handling, logging, or traceability changes required.
```

The Agent must not leave error behavior implicit.

When generating or updating a Pack, the Agent must identify whether the Pack affects:

```text
- API error responses
- validation errors
- authentication errors
- authorization and permission errors
- Admin UI error messages
- internal exceptions
- external-integration errors
- callback or inbound-event errors
- queue or job failures
- database or migration failures
- status-transition failures
- retry and dead-letter failures
- configuration and settings errors
- logging behavior
- trace identifiers
- audit or reporting visibility
- sensitive data exposure
```

### Error Scenario Definition Rule

For every meaningful error scenario introduced or changed by the Pack, the Pack must define:

```text
- scenario
- trigger
- layer where it occurs
- stable error_code
- human-readable message behavior
- HTTP status when applicable
- retryable or non-retryable classification
- temporary or permanent classification
- admin/operator action requirement
- status-transition impact
- log level
- required structured log context
- trace identifiers
- sensitive data handling
- required tests
```

The Pack must not use vague instructions such as:

```text
- handle errors
- return proper error
- add logging
- show validation message
- catch exception
- log failure
```

Instead, the Pack must define the exact expected behavior.

### Stable Error Code Rule

When a Pack creates or modifies an error condition, it must define a stable, language-neutral `error_code`.

Capability-neutral examples:

```text
invalid_input
unsupported_operation
external_integration_disabled
external_configuration_missing
external_timeout
external_rate_limited
external_credentials_invalid
idempotency_conflict
inbound_event_invalid_signature
inbound_event_duplicate
terminal_status_protected
retry_policy_exceeded
```

Do not use vague error codes such as:

```text
error
failed
bad_request
something_wrong
unknown_problem
```

Human-readable messages must use translation keys when visible to API consumers, admins, operators, or users.

Error codes must not be translated.

### API Error Contract Rule

When a Pack creates or modifies API error behavior, it must define:

```text
- endpoint
- HTTP method
- error scenario
- HTTP status
- error_code
- response structure
- required response fields
- validation error structure
- trace/request identifier
- safe details
- forbidden sensitive details
```

API tests must not verify only the HTTP status.

They must also verify the error contract, stable error code, response shape, and relevant side effects.

### UI / Admin UI Error Rule

When a Pack creates or modifies operator-facing error behavior, the Pack must define:

```text
- where the error is shown
- clear human-readable explanation
- suggested operator action
- whether retry is possible
- whether configuration or admin action is required
- error_code visibility
- trace identifier visibility
- permission required to view technical details
- translation requirements
```

Raw exception messages, stack traces, credentials, tokens, external-service secrets, and unsafe raw external payloads must not be displayed directly in Admin UI.

### Exception Handling Rule

When a Pack introduces or modifies exceptions, the Pack must define:

```text
- exception class
- responsibility
- where it may be thrown
- where it must be handled
- mapped error_code
- HTTP status when applicable
- retryability
- log level
- whether the raw message is safe for UI or API
```

Controllers, Jobs, Services, external adapters, and callback/inbound-event processors must not silently swallow exceptions.

Exceptions must either:

```text
- be handled with an explicit result and traceable log;
- be normalized and rethrown as an approved domain/application exception;
- be allowed to fail through the approved queue or framework failure mechanism.
```

### External Integration Error Normalization Rule

When a Pack creates or modifies an external integration, it must define how raw external errors map to stable internal behavior.

For each relevant external error, the Pack must specify:

```text
- external error code
- external error meaning
- normalized internal error_code
- retryable status
- permanent status
- admin action requirement
- expected owner-managed state impact
- expected operation-attempt impact, when applicable
- safe external error snapshot behavior
```

Core owner code must not depend on external-integration-specific error text.

Raw external responses must stay inside the approved adapter or integration-specific infrastructure boundary.

### Structured Logging Rule

When a Pack creates or modifies logging, the Pack must identify:

```text
- event name
- trigger
- log level
- purpose
- required context
- fields to mask or omit
```

Relevant log context may include:

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

The Pack must select only the context relevant to the event.

Do not request vague logs such as:

```text
Operation failed.
External integration error.
Inbound event failed.
Something went wrong.
```

Logs must be useful for tracing the exact failed operation.

### Traceability Rule

When a Pack affects a multi-step operation, it must define how trace identifiers are created, propagated, stored, returned, displayed, and logged.

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

The Pack must state which identifiers connect:

```text
API or command entry
→ persisted domain record
→ queued Job or background operation
→ operation attempt
→ external adapter call
→ callback or inbound event
→ result or report
→ operator UI
→ logs
```

### Sensitive Data Rule

The Pack must identify whether error handling or logging may expose sensitive values.

The Pack must explicitly forbid raw exposure of:

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

The Pack must define whether each sensitive value must be:

```text
masked
redacted
encrypted
omitted
permission-gated
```

### Error Testing Rule

When error behavior changes, the Pack must define tests that verify the actual behavior.

Required assertions may include:

```text
- stable error_code
- expected HTTP status
- expected API response shape
- expected validation error structure
- expected translated message behavior
- expected authorization result
- expected status transition
- expected database state
- expected retryable/permanent classification
- expected external-error normalization
- expected structured log event/context
- expected trace identifier
- no sensitive data exposure
```

Checking only the status code or only that an exception occurred is not sufficient.

The Pack must follow:

```text
docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md
docs/ai/rules/TEST-AND-VALIDATION-RULES.md
```

### Follow-up Rule

If required error handling, logging, tracing, translation, testing, UI, API, security, or documentation work is outside Pack scope, blocked by `Do Not Change`, unsafe, or requires operator action, the Pack must record it under:

```text
Required Follow-up Updates
```

## Configuration and Settings Requirements Rule

Every AI Pack must include a `Configuration / Settings Requirements` section.

This section must explicitly state whether the Pack requires configuration, environment variables, Admin Settings, external-integration settings, secret settings, queue/cache/log settings, test setup, or operator manual setup.

The Agent must not leave configuration or settings requirements implicit.

When generating or updating a Pack, classify each required setting into the correct owner:

```text
Config file:
- static or deploy-time defaults
- plugin defaults
- non-sensitive technical defaults
- values that should be versioned

Environment variable:
- environment-specific values
- secrets that should not be stored in source
- local/dev/test placeholders
- deploy-time secret references

Admin Settings:
- operational settings editable by admins
- external-integration enable/disable flags
- owner-defined routing or selection choices
- integration configuration managed through Admin UI
- non-sensitive runtime settings that operators may change

Encrypted / secret storage:
- external-service API keys
- service tokens
- callback tokens
- webhook secrets
- signing keys
- private credentials
```

The Pack must identify whether it needs changes or setup in:

```text
- project / component config files
- `.env` variables
- `.env.example`
- framework administration settings
- owner-specific integration settings
- database-backed settings
- queue / cache / logging configuration
- test-only or development-only settings
```

A Pack must not store sensitive values in plain-text config files, documentation, reports, seed data, fixtures, tests, API test artifacts, or administration UI.

Real secrets must not be included in Pack instructions, examples, tests, seed data, fixtures, documentation, reports, or API test artifacts.

Use placeholders only.

Examples:

```text
***REDACTED***
example-token
example-sensitive-value
https://example.test
```

The Agent must not commit real `.env` values.

If `.env.example` must be updated, the Pack must list it under `Files to Edit`.

If a Pack creates or modifies external-integration configuration, it must preserve the separation between non-sensitive configuration and sensitive secrets.

Example:

```text
public/configuration storage = non-sensitive integration config
encrypted or environment-backed secret storage = sensitive integration config
```

Secrets must not be stored in `config_json`.

Secrets must not be exposed in API responses, administration UI, logs, reports, exports, Run Reports, Agent Final Reports, tests, seed data, examples, documentation, or API test artifacts.

External-service, callback, webhook, signing, and credential values must use encrypted, masked, redacted, hashed, environment-backed, or future secret-manager-backed handling where applicable.

When a Pack depends on an existing setting, the Pack must state:

```text
- the setting name
- where it is stored
- whether the Agent must read it, create it, update it, or only document it
- whether operator setup is required before testing
```

When a Pack introduces a new setting, the Pack must state:

```text
- owner location: config file / env / Admin Settings / encrypted secret storage
- default value
- validation rules
- translation requirements for Admin UI labels/help text
- permission requirements for Admin UI settings
- test/development setup requirements
- whether environment examples, config files, settings UI, tests, owner-declared API artifacts, or documentation must be updated
```

If a Pack requires local, development, or testing setup, the Pack must document:

```text
- required setup
- reason
- setup method
- safe environment
- command required
- operator approval requirement
```

The Agent must not run database-changing, cache-changing, locale-changing, queue-changing, environment-changing, destructive, or production-affecting commands unless the Pack explicitly allows the exact command or the operator approves it.

Test data may be created only in local/development/testing environments.

Production data must not be created, mutated, or assumed.

If a required configuration or settings update is outside Pack scope, blocked by `Do Not Change`, unsafe, or requires operator action, the Agent must record it under `Required Follow-up Updates`.

If a Pack does not require any configuration or settings work, the section must explicitly say:

```text
No configuration or settings changes required.
```

Configuration and settings updates must remain inside Pack scope.

The Pack must not introduce implicit configuration, environment, Admin Settings, external-integration settings, secret-storage behavior, queue/cache/log setup, or test setup that is not documented in the `Configuration / Settings Requirements` section.

## Multilingual and RTL/LTR Pack Generation Rule

Every AI Pack must include a `Multilingual / Translation Requirements` section.

This section must explicitly state whether the Pack creates, modifies, or depends on multilingual text, translation files, locale behavior, direction behavior, Admin UI labels, API messages, validation messages, error messages, success messages, permission labels, status labels, external-integration labels, settings labels, owner-defined default text, or RTL/LTR layout behavior.

If the Pack has no multilingual or translation impact, the section must explicitly say:

```text
No multilingual or translation changes required.
```

The Agent must not leave multilingual, translation, locale, or RTL/LTR requirements implicit.

When a Pack creates or modifies user-facing, admin-facing, operator-facing, or API-consumer-facing text, UI, views, menus, permission labels, table columns, filters, forms, buttons, actions, status labels, validation messages, error messages, success messages, API messages, owner-defined default text, external-integration labels, settings labels, or any displayable text, include the relevant multilingual and RTL/LTR constraints in the Pack.

When applicable, include these constraints in the Pack's `Implementation Rules`, `Validation Rules`, `Security Rules`, `Expected Output`, or `Testing Requirements` sections:

```md
- Follow multilingual and directionality rules in the applicable owner profile and Canonical files.
- Follow the Pack `Multilingual / Translation Requirements` section.
- Do not hardcode user-facing, admin-facing, operator-facing, or API-consumer-facing strings in Controllers, Form Requests, Services, Jobs, Tables, Forms, Views, API Resources, or Admin UI code.
- Add translation keys for all new visible text.
- Provide every locale required by the applicable owner profile.
- Store translations in the owner path and format declared by the applicable profiles and actual project convention.
- Use the namespacing convention declared by the project profile; do not invent locale keys or global locale structure.
- API error codes must be stable and language-neutral; human-readable API messages must come from translation keys.
- When the applicable profile requires both directions, new UI must remain usable in LTR and RTL using actual project conventions.
- Technical integration, queue, retry, secret, inbound-event, and numeric config fields must not become per-locale unless explicitly approved.
- Do not create/remove global locales or run language/cache/route-changing commands unless the Pack explicitly allows it or the operator approves the exact command.
```

When generating or updating a Pack, the Agent must identify all visible text introduced or changed by the Pack, including:

```text
- Admin menu titles
- Page titles
- Table columns
- Filters
- Form labels
- Form placeholders
- Buttons
- Actions
- Status labels
- Permission labels
- Settings labels
- Help texts
- Success messages
- Error messages
- Validation messages
- API messages
- External-integration labels when user-facing
- Callback or inbound-event status labels
- Operation-attempt status labels
- external-integration test text
- owner-owned default/system text
```

For each visible text item and each locale required by the applicable profile, the Pack must state:

```text
- translation key
- localized values
- where it is used
- file or UI/API area affected
```

The Pack must identify translation files to create or edit.

Resolve translation roots, required locales, naming, file format, and namespace syntax from the applicable project and owner profiles. The Pack must follow the actual convention and must not invent a global locale structure.

Theme JSON translations must be used only for theme-related work when the active theme convention requires it.

API responses must use stable, language-neutral error codes, while human-readable messages must come from translation keys.

The Pack must identify API messages that require translation, including:

```text
- validation messages
- error messages
- success messages
- status messages
- message field values intended for humans
```

For API messages, the Pack must state:

```text
- endpoint
- HTTP status
- error_code when applicable
- translation key
- localized messages required by the applicable profile
```

An owner must not translate or rewrite upstream-provided content unless the Pack explicitly says that owner owns the content.

If content comes from a source service, the Pack must state whether the selected owner stores/processes a snapshot without translating it.

If the Pack creates or modifies Admin UI, Forms, Tables, settings pages, menus, permissions, filters, buttons, actions, or status labels, the Pack must include Admin UI translation requirements.

If the Pack creates or modifies views, Admin UI pages, tables, filters, badges, buttons, forms, or layout-sensitive text, the Pack must identify RTL/LTR impact.

The Pack must state whether RTL/LTR review is required and which UI areas must be checked.

If locale or direction metadata is required, the Pack must state:

```text
- locale field or metadata
- direction field or metadata
- storage location
- where it is used
```

If the Pack modifies project/framework administration UI, forms, tables, views, translations, or theme-facing text, include source guidance resolved from the applicable project profile.

If translation or RTL/LTR work is required but outside Pack scope, blocked by `Do Not Change`, unclear, or requires operator action, the Pack must record it under `Required Follow-up Updates`.

The Pack must include validation expectations for multilingual work, including whether:

```text
- profile-required translations were added or updated
- translation keys are used in code
- hardcoded visible text remains
- API messages use translation keys
- Admin UI labels use translation keys
- RTL/LTR impact was reviewed
- human review is needed for locale wording quality
```

Do not duplicate the full multilingual guide inside every Pack.

Include only the multilingual, translation, locale, and RTL/LTR requirements directly relevant to the Pack scope.

## UI / Admin UI Pack Generation Rule

Every AI Pack must include a `UI / Admin UI Requirements` section.

This section must explicitly state whether the Pack creates, modifies, depends on, or removes UI, Admin UI, settings UI, dashboard UI, monitoring UI, tables, forms, filters, buttons, actions, menus, widgets, cards, modals, badges, views, or permission-gated UI behavior.

If the Pack has no UI impact, the section must explicitly say:

```text id="hodmj5"
No UI changes required.
```

The Agent must not leave UI requirements implicit.

When generating or updating a Pack, the Agent must identify all UI surfaces introduced or changed by the Pack, including:

```text id="y01cmb"
- Admin pages
- Settings pages
- External-integration management pages
- External-integration test pages
- Domain-record or operation pages
- Operation-attempt pages
- Callback or inbound-event pages
- Dead-letter pages
- Retry / cancel action pages
- Monitoring pages
- Dashboard widgets
- Tables
- Table columns
- Table filters
- Bulk actions
- Forms
- Form fields
- Form validation messages
- Buttons
- Row actions
- Header actions
- Modals
- Confirmation dialogs
- Badges
- Status labels
- Empty states
- Error states
- Loading states
- Permission-denied states
- Navigation / menu items
```

For each UI surface, the Pack must state:

```text id="9xkhh1"
- UI surface name
- whether it is created or edited
- purpose
- route or URL when applicable
- related controller, form, table, view, or component
- required permission
- related backend/API behavior
- translation requirements
- validation requirements
```

If the Pack creates or modifies project/framework administration UI, forms, tables, views, menus, permissions, settings pages, routes, hooks, filters, or component UI, it must include source guidance resolved from the applicable project profile.

The Pack must not instruct the Agent to create UI outside the selected owner's declared root unless it explicitly allows the path or the operator approves it. Resolve concrete UI roots and framework conventions from the owner manifest and applicable profiles; do not hard-code one repository layout in a shared Pack template.

The Pack must identify whether UI work requires:

```text id="uhp405"
- new or updated routes
- new or updated permissions
- new or updated menu entries
- new or updated translation keys
- new or updated forms
- new or updated tables
- new or updated views
- new or updated settings UI
- new or updated assets
- new or updated tests
- new or updated owner-declared API test examples when UI depends on API behavior
```

UI changes must follow the applicable project and owner conventions.

The Pack must not introduce unrelated UI redesign, visual cleanup, layout refactor, asset rebuild, theme changes, or public/generated asset edits unless the Pack explicitly includes them in scope.

The Pack must not modify protected UI areas declared by the applicable profiles, public/generated assets, third-party UI, or vendor UI unless the Pack explicitly allows it or the operator approves it.

If the UI requires a protected-area change, the Pack must state:

```text id="dg5r0v"
- protected area
- reason
- why plugin-level extension is not enough
- approval requirement
- rollback notes
```

The Pack must identify all UI permissions and visibility rules.

Permission-gated UI must not expose actions, raw external data, secrets, inbound events, logs, audit data, or sensitive operational controls to unauthorized roles.

UI elements that trigger sensitive operations must state:

```text id="hl59z1"
- required permission
- confirmation requirement
- audit requirement
- success message
- error message
- failure handling
```

Sensitive UI operations may include:

```text id="cc37r4"
- external-integration test
- external-integration enable/disable
- retry
- manual retry
- cancel
- raw inbound-event payload view
- secret update
- service token management
- export
- bulk action
- admin override
```

UI forms must include validation requirements.

The Pack must state whether validation happens through:

```text id="g89sg1"
- Form Request
- framework-form validation
- service-level validation
- frontend UI constraints
- backend/API validation
```

The Pack must identify UI states that must be handled, including:

```text id="d7mi93"
- loading
- empty
- success
- error
- validation error
- disabled
- permission denied
- unavailable external-integration/configuration state
```

The Pack must identify table behavior when applicable, including:

```text id="x3cfcf"
- columns
- filters
- sorting
- searching
- pagination
- row actions
- bulk actions
- permission-gated actions
- empty state
```

The Pack must identify settings UI behavior when applicable, including:

```text id="gw7aad"
- setting field
- default value
- validation
- permission
- secret masking
- help text
- translation keys
- operator setup requirement
```

The Pack must coordinate UI requirements with:

```text id="mwo557"
- `Configuration / Settings Requirements`
- `Multilingual / Translation Requirements`
- `Security Rules`
- `Permission Rules`
- `Testing Requirements`
- owner-declared API test artifact requirements when UI depends on API behavior
```

The Pack must not duplicate full project UI guidance or multilingual guidance inside every Pack.

Include only UI requirements directly relevant to the Pack scope.

If UI work is required but outside Pack scope, blocked by `Do Not Change`, unclear, unsafe, or requires operator action, the Pack must record it under `Required Follow-up Updates`.

The Pack must include UI validation expectations, including whether:

```text id="xn6eaz"
- UI files were created or updated
- routes/menu entries were checked
- permissions were checked
- forms render and submit correctly
- tables load and filter correctly
- actions/buttons work correctly
- sensitive actions are permission-gated
- empty/error/loading states were handled
- translation keys were used
- RTL/LTR impact was reviewed
- operator UI review is required
```

## Data Model / Migration / Relationship Pack Generation Rule

Every AI Pack that creates, modifies, removes, or depends on database tables, columns, migrations, indexes, constraints, foreign keys, model relationships, data backfills, seed data, or rollback behavior must include a `Data Model / Migration / Relationship Requirements` section.

The Pack must not leave database relationships or referential actions implicit.

When a Pack creates or modifies a migration, it must explicitly define:

* tables to create or modify
* columns and their types
* nullability
* default values
* indexes
* unique constraints
* foreign keys
* model relationships
* inverse relationships when needed
* delete behavior
* update behavior
* data backfill requirements
* rollback/down behavior
* data-loss risk
* audit/history/retention impact

Every foreign key must specify:

```text
On delete: cascade / restrict / set null / no action
On update: cascade / restrict / no action
Reason:
```

The Agent must not use implicit foreign key behavior when the business meaning is unclear.

Avoid `cascadeOnDelete()` unless the Pack explicitly justifies that child records have no independent operational, audit, reporting, or historical value.

When an applicable owner profile identifies operational history, do not cascade-delete it by default.

Operational history may include:

* domain records or messages
* operation attempts
* external responses
* callbacks or inbound events
* raw inbound payloads
* result or status reports
* dead letters
* audit logs
* usage/cost records
* external-integration test records

Preferred default when unsure:

```text
On delete: restrict or no action
```

Use `set null` only when the relation is optional and historical records must remain after the parent record is removed.

Use `cascade` only when the child record is fully owned by the parent and has no independent audit, reporting, operational, or troubleshooting value.

The Pack must define matching Eloquent relationships when applicable.

For each relationship, the Pack must state:

```text
Model:
Relationship method:
Relationship type:
Related model:
Foreign key:
Local / owner key:
Inverse relationship:
Nullable relation:
```

Indexes and unique constraints must be explicitly tied to query, reporting, idempotency, or integrity needs.

If a Pack changes idempotency, routing, external mapping, state tracking, inbound-event processing, retry, bulk operations, quota, cost, or audit behavior, the Pack must check whether database constraints or indexes are required.

If data migration or backfill is required, the Pack must document safe environment, command, rollback risk, and operator approval requirement.

If no database, migration, relationship, or data integrity work is required, the Pack must explicitly say:

```text
No data model, migration, or relationship changes required.
```

### Snapshot Requirements Rule

When a Pack creates or modifies operational, historical, audit, reporting, inbound-event, operation-attempt, external-response, retry, dead-letter, usage, cost, configuration, route, or integration-related records, the Pack must decide whether snapshot fields are required.

A Pack must not rely only on live foreign keys when historical accuracy is required.

Use snapshot fields when a record must preserve the state of related data at the time of the event.

Snapshot may be required for:

- external-integration identity
- external operation or method
- owner-defined routing/configuration choice
- route selection
- owner-defined template/configuration reference
- owner-defined template/configuration values
- request payload
- external payload
- inbound-event payload
- sensitive target display/masked value
- source-system context
- routing decision context
- retry/debug context

For each snapshot, the Pack must state:

- table
- column
- source value
- when the snapshot is captured
- why live FK is not enough
- whether the value is sensitive
- whether masking/redaction is required

When the applicable owner profile requires historical snapshots, preserve event-time context even if related live configuration records are later changed, disabled, or deleted.

If snapshot is not required, the Pack must explicitly say:

No snapshot fields required.

---
## External Integration Constraints

When a Pack creates or modifies an external adapter, external API, integration configuration, integration test/health operation, inbound-event normalization, status query, or other external integration, include:

```md id="v1zfov"
- Follow external-integration architecture rules in the applicable owner profile and Canonical files.
- Keep external calls inside the adapter or integration boundary declared by the owner.
- Do not invent transport, vendor, or owner-specific behavior in shared layers.
```

## Asynchronous Execution Constraints

When a Pack creates or modifies queue dispatch, jobs, workers, operation attempts, external calls, retry execution, or other asynchronous work, include:

```md id="k7daqg"
- Follow queue and execution rules in the applicable owner profile and Canonical files.
- Do not perform asynchronous external work synchronously in a layer forbidden by the owner contract.
- Route external calls through the approved adapter and execution pipeline.
```

## Callback and Inbound-event Constraints

When a Pack creates or modifies callbacks, webhooks, inbound-event validation/storage/processing, external report handling, state changes, or related permissions, include:

```md id="p99jye"
- Follow callback or inbound-event rules in the applicable owner profile and Canonical files.
- Keep acknowledgment and full processing behavior within the owner's latency and execution contract.
- Preserve raw evidence before normalization when the owner requires it and security rules allow it.
- Apply state changes through the approved state-management boundary.
- Prevent duplicate inbound events from applying the same effect more than once.
- Protect raw payloads, headers, signatures, tokens, and sensitive external metadata from unauthorized visibility.
```

## Status Model Constraints

When a Pack creates or modifies statuses, status enums, state machines, state services, attempts, callbacks, retry logic, cancellation logic, dead-letter logic, reports, administration status actions, or any code that changes an owner-managed status, include:

```md
- Follow status-model rules in the applicable owner profile and Canonical files.
- Do not update owner-managed statuses through uncontrolled direct mutations.
- Status changes must go through the approved State Service / Status Service / State Machine layer.
- Do not introduce new statuses or status transitions without approved canonical, release/phase, Change Request, or operator approval.
- Terminal states must not be downgraded by later jobs, callbacks, retries, or admin actions unless an approved State Machine rule explicitly allows it.
```

## Idempotency Constraints

When a Pack creates or modifies commands, request validation, idempotency keys, APIs, queue dispatch, operation attempts, retry logic, inbound-event processing, bulk operations, duplicate detection, accounting, quota enforcement, or external execution, include:

```md
- Follow Idempotency Rules in the applicable owner profile and Canonical files.
- Commands, retries, inbound events, and bulk operations must be protected from unsafe duplicate execution.
- Resolve the concrete idempotency scope and identifiers from the applicable owner profile and Canonical files.
- Duplicate commands must not create multiple active executions for the same logical operation.
- A retry may create a new attempt only according to the owner's domain and history contract.
- Duplicate inbound events must not apply the same state transition or side effect more than once.
- When payload comparison is part of the owner contract, the same idempotency key with a conflicting stable payload hash must follow the owner's approved conflict behavior.
```

## API Test Artifact Constraints

When a Pack creates or modifies API endpoints, routes, methods, headers, authentication, request input, validation, responses, status codes, error codes, callbacks, status/health contracts, integration-test endpoints, or API examples, include maintenance requirements for any API test artifact declared by the applicable owner profile.

When applicable, include these constraints in the Pack's `Validation Rules`, `Tests to Run`, `Expected Output`, or `Required Follow-up Updates` sections:

- Check whether the Pack affects an owner-declared API test artifact.
- Resolve concrete artifact paths and tool format from the applicable profile.
- Use safe placeholder variables only; do not store real secrets, tokens, credentials, Authorization values, sensitive identifiers, or production URLs.
- If the required artifact cannot be updated within Pack scope, record the missing update under `Required Follow-up Updates`.

If the Pack does not affect API behavior, do not add API test artifact requirements to the Pack.

API test artifact updates must remain directly related to the Pack's API changes and must not introduce unrelated endpoint examples or broad cleanup.

---

## Security and Secret Handling Constraints

When a Pack creates or modifies external-service configuration, credentials, API keys, callback tokens, service tokens, webhook secrets, signing keys, authentication headers, raw external payloads, inbound-event visibility, logs, audit records, reports, admin security views, exports, or any code that may expose sensitive data, include these constraints in the Pack's `Security Rules` section:

```md
- Follow Security and Privacy rules, including secret-handling rules, in the applicable owner profile and Canonical files.
- Do not store, log, display, export, return, or commit external-service secrets, API keys, callback tokens, service tokens, webhook secrets, signing keys, or credentials in plain text.
- Use masked, hashed, redacted, encrypted, or environment-backed secret handling where applicable.
- Do not expose sensitive target or personal identifiers in public APIs, normal Admin UI, reports, logs, exports, or AI execution reports.
- Raw external responses, inbound payloads, headers, signatures, tokens, and sensitive metadata must require explicit technical permission before visibility.
- Test fixtures, seeders, examples, screenshots, logs, and documentation must use placeholders instead of real secrets.
- Secret rotation and revocation must be possible without changing core owner behavior.
- Keep non-sensitive integration configuration separate from sensitive secrets; use the storage boundary declared by the applicable owner rather than assuming concrete field names in shared governance.
```

## Public/Core Capability-neutral Naming Constraints

When a Pack creates or modifies public APIs, shared services, application services, jobs, DTOs, models, enums, contracts, permissions, routes, config keys, database tables, UI concepts, method names, or generic domain code, include relevant capability-neutral naming constraints in the Pack.

When applicable, include these constraints in the Pack's `Architecture Constraints`, `Implementation Rules`, `Validation Rules`, `Data / Migration Rules`, `Configuration / Settings Requirements`, or `UI / Admin UI Requirements` sections:

```md
- Follow capability-neutral structure and naming rules in the applicable owner profile and Canonical files.
- An initial capability, adapter, transport, or integration must not become the architectural identity of a multi-capability owner.
- Use capability-neutral names in shared/public/core/application/domain layers when the owner is designed for multiple implementations.
- Capability-specific names are allowed only inside the corresponding implementation boundary.
- Do not implement additional capabilities unless they are explicitly in Release/Phase/Pack scope.
- Preserve extension boundaries required by the applicable owner profile.
```

If a Pack explicitly implements a capability-specific boundary or adapter, it may use capability-specific names only inside that boundary. The applicable owner profile may provide concrete allowed and forbidden examples.

Do not add this constraint to Packs that do not create or modify naming, public API, shared services, config, database structure, integration logic, or generic domain code.

## Consumers and Source Systems Constraints

When a Pack creates or modifies commands, request validation, service clients, owner-defined source identifiers, permissions, reporting scope, usage scope, quota scope, or consumer-specific behavior, include:

```md id="y5ah85"
- Follow consumer and source-system rules in the applicable owner profile and Canonical files; do not introduce new source identifiers or consumer-specific core logic without approval.
```

## Unrelated Constraints Rule

The Agent must not add unrelated architecture constraints to the generated Pack.

## Operator Execution Checklist Generation Rule

Every AI Pack must include an `Operator Execution Checklist` section.

This checklist must include only Pack-specific operator checks.

The Agent must not repeat global execution workflow, Git checks, generic scope checks, generic test checks, documentation maintenance checks, reporting checks, Run Report checks, or commit-readiness checks that are already defined in:

```text
docs/ai/rules/AI-EXECUTION-RULES.md
docs/ai/rules/GIT-AND-COMMIT-RULES.md
docs/ai/rules/REPORTING-RULES.md
docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md
docs/ai/rules/OPERATOR-WORKFLOW.md
```

Before-execution checklist items must be specific to the current Pack.

Valid examples:

```text
- Confirm external-integration test mode is allowed for this Pack.
- Confirm the selected external integration is approved for this Pack.
- Confirm required administration settings are available before testing.
- Confirm this Pack may create the initial owner-declared API test artifact.
- Confirm this Pack may touch project/framework administration settings.
```

After-execution checklist items must also be specific to the current Pack.

Valid examples:

```text
- Verify the external-integration test request appears in the expected admin report.
- Verify the new administration setting is visible in every profile-required locale.
- Verify the owner-declared API test request was added for the new endpoint.
- Verify raw inbound-event payload visibility is still permission-gated.
```

If no Pack-specific before-execution checks are required, the Pack must write:

```text
No Pack-specific before-execution operator checks required.
```

If no Pack-specific after-execution checks are required, the Pack must write:

```text
No Pack-specific after-execution operator checks required.
```

The Operator Execution Checklist must not be used to duplicate rules from global workflow files.

The purpose of this checklist is to surface only the extra human/operator checks that are unique to the current Pack.

## Operator Documentation Pack Generation Rule
Every AI Pack must include an `Operator Documentation Requirements` section. Resolve the applicable operator-guide rules, concrete guide path, required language, owner-specific workflows, permissions, sensitive actions, asset path, and template through the applicable owner manifest and governance profile.

Do not invent a shared rule path when the operator guide is owner-specific. Do not copy owner-specific examples or full operator instructions into this shared rule or into Pack/Run/Final Report bodies.

If the Pack has no operator-facing impact, write:

```text
No operator documentation update required.
```


---
