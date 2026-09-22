# AI-PACK-<ID> — <Task Title>

## 1. Task ID

## 2. Task Title

## 3. Goal

## 4. Context

## 5. Related Release / Phase

## 6. Related Epic / Feature / Story

## 7. Source References

## 8. Files to Create

## 9. Files to Edit

## 10. Files to Read / Reference

List only the files that are required for this Pack.

Do not include broad folders or unrelated reference files.

### Project / Framework Source Reference Rule

If this Pack creates or modifies project-, framework-, CMS-, runtime-, or language-specific implementation, include the applicable project profile and only the source-guidance files it requires. Resolve concrete paths through the project index/profile; do not copy a technology-specific path into a reusable Pack.

Required files for this Pack:

- `...`

## 11. Configuration / Settings Requirements

Specify all configuration and settings required for this Pack.

Identify whether this Pack needs changes or setup in:

```text
- project / component config files
- `.env` variables
- framework administration settings
- owner-specific integration settings
- database-backed settings
- queue / cache / logging configuration
- test-only or development-only settings
```

### Config File Requirements

List static or deploy-time configuration that belongs in config files.

```text
Config files to create or edit:
- ...

Config keys required:
- Key:
  Purpose:
  Default value:
  Environment-specific: Yes / No
  Sensitive: Yes / No
```

If no config file changes are required, write:

```text
No config file changes required.
```

### Environment Variable Requirements

List required environment variables.

```text
Environment variables required:
- Name:
  Purpose:
  Example placeholder:
  Required for local/dev/test: Yes / No
  Required for production: Yes / No
  Sensitive: Yes / No
```

If no environment variables are required, write:

```text
No environment variables required.
```

### Admin Settings Requirements

List settings that must be configured through the applicable project or owner administration UI.

```text
Admin settings required:
- Setting:
  Location in Admin UI:
  Purpose:
  Default value:
  Required before testing: Yes / No
  Required before production: Yes / No
  Sensitive: Yes / No
  Operator action required: Yes / No
```

If the Pack must create or modify Admin Settings UI, list:

```text
Admin Settings UI changes:
- Page / Form / Field:
  Purpose:
  Permission required:
  Translation keys required:
  Validation required:
```

If no Admin Settings are required, write:

```text
No Admin Settings changes required.
```

### External Integration / Secret Settings Requirements

List external-integration configuration, credential, token, webhook secret, service token, signing key, or secret-storage requirements.

```text
External-integration settings:
- Non-sensitive config location:
  Example: `config_json`
- Sensitive secret location:
  Example: `secret_json`, encrypted storage, env-backed secret, or secret manager
- Masking required in UI/reports: Yes / No
- Rotation/revocation impact: Yes / No
```

If not applicable, write:

```text
No external-integration or secret settings required.
```

### Test / Development Setup Requirements

List any local, development, or testing setup required before this Pack can be tested.

```text
Test/development setup required:
- Required setup:
  Reason:
  Setup method:
  Safe environment:
  Command required:
  Operator approval required:
```

If no test/development setup is required, write:

```text
No test/development setup required.
```

### Settings Validation

Describe how the Agent should verify the required settings.

```text
Validation required:
- Config keys checked: Yes / No / Not applicable
- Env placeholders documented: Yes / No / Not applicable
- Admin settings created or documented: Yes / No / Not applicable
- Sensitive settings masked: Yes / No / Not applicable
- Tests/API test artifacts updated for setting-dependent behavior: Yes / No / Not applicable
```

### Operator Setup Notes

If the operator must manually configure something after implementation, list it here.

```text
Operator manual setup required:
- Step:
  Where:
  Value / Placeholder:
  Required before:
```

If no manual setup is required, write:

```text
No operator manual setup required.
```


## 12. Do Not Change

## 13. Clarification Questions Before Implementation

## 14. Multilingual / Translation Requirements

### Multilingual Impact

```text
Multilingual / Translation Impact: Yes / No
RTL/LTR Impact: Yes / No
API Message Translation Impact: Yes / No
Admin UI Translation Impact: Yes / No
Owner-defined Default Content Translation Impact: Yes / No
```

```text
No multilingual or translation changes required.
```

### Locales Required

```text
Locales required by applicable profile:
- Locale key:
  Language/region:
  Direction:
```

### Translation Files to Create or Edit

```text
Translation files by locale:
- ...
```

```text
No translation files changed.
```

### Texts / Labels to Translate

```text
Texts to translate:
- Key:
  Values by required locale:
  Used in:
  File / UI / API:
```

```text
No visible text introduced or changed.
```

### Admin UI Translation Requirements

```text
Admin UI translations required:
- Menu title:
- Page title:
- Table columns:
- Filters:
- Form labels:
- Form placeholders:
- Buttons:
- Actions:
- Status labels:
- Permission labels:
- Settings labels:
- Help texts:
- Success messages:
- Error messages:
```

```text
No Admin UI translation changes required.
```

### API Message Translation Requirements

```text
API messages to translate:
- error_code:
  Translation key:
  Messages by required locale:
  HTTP status:
  Used in endpoint:
```

```text
No API message translation changes required.
```

### Owner / Integration Test Content Translation Requirements

```text
Owner-owned content to translate:
- Content name:
  Translation key:
  Values by required locale:
  Used in:
```

```text
Content is provided by an upstream service. The selected owner does not translate or rewrite it in this Pack.
```

### RTL/LTR Requirements

```text
RTL/LTR review required: Yes / No

Areas to check:
- Admin page layout:
- Table layout:
- Form layout:
- Button/action alignment:
- Badge/status display:
- required RTL locale rendering:
- required LTR locale rendering:
```

```text
No RTL/LTR layout impact.
```

### Locale / Direction Metadata

```text
Locale/direction metadata required:
- locale:
- direction:
- Storage location:
- Used by:
```

```text
No locale or direction metadata changes required.
```

### Translation Validation

```text
Validation required:
- profile-required translations added/updated: Yes / No / Not applicable
- No hardcoded visible text remains: Yes / No / Not applicable
- API messages use translation keys: Yes / No / Not applicable
- Admin UI labels use translation keys: Yes / No / Not applicable
- RTL/LTR impact reviewed: Yes / No / Not applicable
- Human review for locale wording required: Yes / No
```

### Operator Translation Review Notes

```text
Operator review required:
- Text / Area:
  Reason:
  Review focus:
```

```text
No operator translation review required.
```

## 15. UI / Admin UI Requirements

### UI Impact

```text id="rbn0oq"
UI Impact: Yes / No
Admin UI Impact: Yes / No
Public UI Impact: Yes / No
Settings UI Impact: Yes / No
Dashboard / Monitoring UI Impact: Yes / No
Table / Form Impact: Yes / No
Action / Button Impact: Yes / No
Navigation / Menu Impact: Yes / No
Permission-gated UI Impact: Yes / No
```

```text id="cxm7k7"
No UI changes required.
```

### UI Surfaces to Create or Edit

```text id="dj3u3b"
UI surfaces:
- Surface:
  Type: Admin page / Settings page / Table / Form / Modal / Widget / Dashboard / Menu / Action / Button / Filter / Badge / View
  Create or Edit:
  Purpose:
  Route / URL:
  Permission required:
  Related backend/API:
```

```text id="mn0yhg"
No UI surfaces created or edited.
```

### UI Files to Create or Edit

```text id="a6evh3"
UI files:
- File:
  Purpose:
  Create or Edit:
  Related route/controller/form/table:
```

```text id="rmmihm"
No UI files created or edited.
```

### Admin Menu / Navigation Requirements

```text id="jdy0ti"
Admin menu / navigation changes:
- Menu item:
  Parent menu:
  Route:
  Permission:
  Icon:
  Order:
  Translation key:
```

```text id="o6fmqr"
No Admin menu or navigation changes required.
```

### Forms / Fields Requirements

```text id="dzq3s1"
Forms / fields:
- Form:
  Field:
  Type:
  Required: Yes / No
  Validation:
  Default value:
  Help text:
  Translation key:
  Permission / visibility condition:
```

```text id="pf9jxx"
No form or field changes required.
```

### Tables / Filters / Columns Requirements

```text id="vzxhyd"
Tables / filters / columns:
- Table:
  Column / Filter / Bulk action:
  Purpose:
  Sortable: Yes / No / Not applicable
  Searchable: Yes / No / Not applicable
  Filterable: Yes / No / Not applicable
  Permission / visibility condition:
  Translation key:
```

```text id="y210cq"
No table, filter, or column changes required.
```

### UI Actions / Buttons Requirements

```text id="2ccrye"
UI actions / buttons:
- Action:
  Location:
  Purpose:
  Permission:
  Confirmation required: Yes / No
  Success message:
  Error message:
  Translation keys:
```

```text id="stfn3w"
No UI actions or buttons changed.
```

### Dashboard / Monitoring UI Requirements

```text id="mwz4ts"
Dashboard / monitoring UI:
- Widget / Card / Chart / Metric:
  Purpose:
  Data source:
  Refresh behavior:
  Empty state:
  Error state:
  Permission:
  Translation keys:
```

```text id="a3l4of"
No dashboard or monitoring UI changes required.
```

### UI State Requirements

```text id="y5d6f2"
UI states:
- Loading state:
- Empty state:
- Success state:
- Error state:
- Disabled state:
- Permission-denied state:
- Validation-error state:
```

```text id="v321yz"
No special UI states required.
```

### UI Permission / Visibility Requirements

```text id="uwak9o"
UI permission / visibility requirements:
- UI element:
  Required permission:
  Visible to:
  Hidden from:
  Disabled when:
```

```text id="plnx79"
No UI permission or visibility changes required.
```

### UI Translation Requirements

```text id="tgnwhu"
UI translation requirements:
- Text / Label:
  Translation key:
  Values by required locale:
  Used in:
```

```text id="b6mq1t"
No UI translation changes required.
```

### UI Validation Requirements

```text id="rcoyme"
UI validation required:
- UI renders without errors: Yes / No / Not applicable
- Required permissions checked: Yes / No / Not applicable
- Forms submit correctly: Yes / No / Not applicable
- Tables load correctly: Yes / No / Not applicable
- Filters/actions work correctly: Yes / No / Not applicable
- Empty/error states checked: Yes / No / Not applicable
- RTL/LTR checked: Yes / No / Not applicable
- Translation keys checked: Yes / No / Not applicable
```

### Operator UI Review Notes

```text id="ykd5yz"
Operator UI review required:
- UI area:
  Reason:
  Review focus:
```

```text id="iavfe2"
No operator UI review required.
```

## 16. Operator Documentation Requirements

```text
Operator Documentation Impact: Yes / No
Operator Documentation Update Required: Yes / No / Not applicable
Guide File:
- <resolve from applicable owner profile>
```

### Operator-facing Changes

```text
Operator-facing changes:
- New admin page:
- New menu item:
- New setting:
- New form:
- New table:
- New action/button:
- New external-integration workflow:
- New template/token workflow:
- New report/monitoring workflow:
- New permission:
- Changed recommended operator sequence:
```

If no operator-facing behavior changed, write:

```text
No operator-facing behavior changed.
```

### Guide Sections to Create or Update

```text
Guide sections to create or update:
- ...
```

If no guide update is required, write:

```text
No operator documentation update required.
```

### Screenshot Requirements

```text
Screenshots required: Yes / No / Not applicable
Screenshot type:
- Real Admin UI screenshot
- Conceptual diagram
- Not applicable

Suggested screenshot path:
- <resolve from applicable owner profile>
```

If screenshots are needed but cannot be captured in this Pack, write:

```text
Screenshot pending.
```

### Operator Workflow Notes

```text
Operator workflow notes:
- Before using this feature:
- Steps:
- After using this feature:
- Common mistakes:
- Required permission:
```

---

## 17. Implementation Rules

## 18. Architecture Constraints

List only the architecture constraints that are directly relevant to this Pack.

Follow:

```text
docs/ai/rules/AI-PACK-GENERATION-RULES.md
<applicable owner Canonical files resolved through its index/manifest>
```

Add Pack-specific architecture constraints here.

Do not add unrelated architecture constraints.

## 19. Validation Rules

## 20. Security Rules

## 21. Error Handling / Logging / Traceability Requirements

Follow:

```text
docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md
```

### Error Handling Impact

```text
Error Handling Impact: Yes / No
API Error Contract Impact: Yes / No
UI / Admin UI Error Impact: Yes / No
Internal Exception Impact: Yes / No
External Integration Error Impact: Yes / No
Callback / Inbound-event Error Impact: Yes / No
Logging Impact: Yes / No
Traceability Impact: Yes / No
Error Code Impact: Yes / No
Security / Sensitive Data Impact: Yes / No
```

If this Pack has no error handling, logging, or traceability impact, write:

```text
No error handling, logging, or traceability changes required.
```

### Error Scenarios

List every important error scenario introduced or changed by this Pack.

```text
Error scenarios:
- Scenario:
  Where it occurs:
  Trigger:
  Error category:
  Stable error_code:
  Human-readable message:
  Retryable: Yes / No
  Permanent: Yes / No
  Admin action required: Yes / No
  User / operator visible: Yes / No
  Affects owner-managed state: Yes / No / Not applicable
  Expected status transition:
  Log required: Yes / No
  Audit required: Yes / No
  Sensitive data risk: Yes / No
```

If no error scenarios are introduced or changed, write:

```text
No error scenarios introduced or changed.
```

### API Error Contract Requirements

For every API error introduced or changed by this Pack, specify:

```text
API errors:
- Endpoint:
  HTTP method:
  Scenario:
  HTTP status:
  error_code:
  Response message translation key:
  Required response fields:
  Field-level errors required: Yes / No
  request_id / trace_id required: Yes / No
  Safe details allowed:
  Sensitive details forbidden:
```

Expected standard error shape when applicable:

```json
{
  "status": "error",
  "error_code": "example_error_code",
  "message": "Human-readable translated message.",
  "request_id": "req_example",
  "details": {}
}
```

Validation error shape when applicable:

```json
{
  "status": "error",
  "error_code": "validation_failed",
  "message": "The request contains invalid fields.",
  "request_id": "req_example",
  "errors": {
    "field_name": [
      {
        "error_code": "field_error_code",
        "message": "Human-readable field error."
      }
    ]
  }
}
```

If no API error contract changes are required, write:

```text
No API error contract changes required.
```

### UI / Admin UI Error Requirements

For every operator-facing error introduced or changed by this Pack, specify:

```text
UI errors:
- UI area:
  Error scenario:
  Short explanation:
  Suggested operator action:
  error_code shown: Yes / No
  trace_id / request_id shown: Yes / No
  owner-declared domain / operation identifiers shown: Yes / No
  Permission required to view details:
  Raw exception visible: No
  Raw external payload visible: No
  Translation key:
```

UI messages must explain, when applicable:

```text
- what failed
- why it failed
- whether retry is possible
- whether operator action is required
- where the operator should check
- which trace identifier can be used
```

If no UI error changes are required, write:

```text
No UI or Admin UI error changes required.
```

### Error Code Requirements

List stable error codes created, changed, deprecated, or reused by this Pack.

```text
Error codes:
- error_code:
  Category:
  Purpose:
  Used by:
  HTTP status:
  Retryable: Yes / No
  Permanent: Yes / No
  Admin action required: Yes / No
  Translation key:
  Log level:
  Affects status:
```

Error codes must be:

```text
- stable
- language-neutral
- specific
- testable
- reusable across UI, API, logs, and reports when they describe the same condition
```

Do not use vague error codes such as:

```text
error
failed
bad_request
something_wrong
unknown_problem
```

If no error codes are introduced or changed, write:

```text
No error codes introduced or changed.
```

### Exception Requirements

List exceptions created, changed, mapped, or handled by this Pack.

```text
Exceptions:
- Exception class:
  Purpose:
  Thrown by:
  Handled by:
  Mapped error_code:
  Expected HTTP status:
  Retryable: Yes / No
  Log level:
  Raw message safe for UI/API: Yes / No
```

Raw exception messages must not be exposed directly through API or Admin UI unless explicitly verified as safe.

If no exception changes are required, write:

```text
No exception changes required.
```

### External Integration Error Normalization Requirements

If this Pack creates or modifies external-integration behavior, specify how raw external errors are normalized.

```text
External error mappings:
- Integration:
  External error code:
  External error meaning:
  Internal error_code:
  Retryable: Yes / No
  Permanent: Yes / No
  Admin action required: Yes / No
  Expected owner-managed state impact:
  Expected operation-attempt impact:
  Safe external error snapshot required: Yes / No
```

Core owner services must not depend directly on raw external-integration error text.

If no external-error normalization changes are required, write:

```text
No external-error normalization changes required.
```

### Logging Requirements

List log events introduced or changed by this Pack.

```text
Log events:
- Event name:
  Trigger:
  Log level:
  Purpose:
  Required context:
    - request_id
    - correlation_id
    - domain_record_id
    - operation_id
    - inbound_event_id
    - external_integration_id
    - external_reference_id
    - source_system
    - source_reference
    - priority
    - error_code
    - retryable
    - admin_action_required
    - exception_class
  Sensitive fields to mask or exclude:
```

Use only the context fields applicable to the event.

Logs must not contain vague standalone messages such as:

```text
Operation failed.
External integration error.
Inbound event failed.
Something went wrong.
```

If no logging changes are required, write:

```text
No logging changes required.
```

### Traceability Requirements

Specify identifiers required to trace the full execution flow.

```text
Traceability:
- request_id required: Yes / No
- correlation_id required: Yes / No
- domain-record identifier required: Yes / No
- operation identifier required: Yes / No
- inbound-event identifier required: Yes / No
- external-reference identifier required: Yes / No
- source-system/reference identifier required: Yes / No
```

```text
Trace flow:
- Entry point:
- Identifier created:
- Identifier propagated through:
- Identifier stored in:
- Identifier returned through API:
- Identifier shown in Admin UI:
- Identifier written to logs:
```

If no traceability changes are required, write:

```text
No traceability changes required.
```

### Sensitive Data and Masking Requirements

List sensitive values that may appear in errors, logs, responses, reports, or UI.

```text
Sensitive data:
- Data:
  Possible exposure location:
  Required handling: mask / redact / encrypt / omit
  Authorized visibility:
  Test required: Yes / No
```

Never expose raw:

```text
- external-service API keys
- service tokens
- callback tokens
- webhook secrets
- signing keys
- Authorization headers
- secret_json
- raw credentials
- sensitive target or personal identifiers
- raw external payloads containing secrets
```

Safe placeholders:

```text
***REDACTED***
example-token
example-sensitive-value
https://example.test
```

If no sensitive data impact exists, write:

```text
No sensitive error or logging data impact.
```

### Error Handling Test Requirements

Tests must verify actual error behavior, not only an HTTP status code or generic exception.

```text
Error tests required:
- Scenario:
  Expected error_code:
  Expected HTTP status:
  Expected response shape:
  Expected translated message behavior:
  Expected status transition:
  Expected database state:
  Expected retryable/permanent classification:
  Expected log event/context:
  Expected sensitive data masking:
  Expected trace identifier:
```

Tests should verify applicable behavior:

```text
- stable error_code
- correct HTTP status
- correct response structure
- correct field-level validation errors
- correct authorization behavior
- correct external-error normalization
- correct retryable/permanent classification
- correct status transition
- correct structured log context
- correct trace identifiers
- no secret or sensitive data exposure
```

If no error-specific tests are required, write:

```text
No error-specific tests required.
```

### Operator Review Notes

```text
Operator review required:
- Error area:
  Reason:
  Review focus:
```

If no operator review is required, write:

```text
No operator error-handling review required.
```

### Required Follow-up Updates

```text
- ...
```

If none:

```text
No error handling, logging, or traceability follow-up required.
```

## 22. Data Model / Migration / Relationship Requirements

### Data / Migration Impact

```text
Data / Migration Impact: Yes / No
New Tables Required: Yes / No
Existing Tables Modified: Yes / No
Foreign Keys Required: Yes / No
Model Relationships Required: Yes / No
Indexes / Unique Constraints Required: Yes / No
Data Backfill Required: Yes / No
Soft Delete / Retention Impact: Yes / No
Audit / History Impact: Yes / No
Rollback Impact: Yes / No
```

If no data, migration, or relationship changes are required, write:

```text
No data model, migration, or relationship changes required.
```

### Tables to Create or Modify

```text
Tables:
- Table:
  Create or Modify:
  Purpose:
  Owned by selected owner: Yes / No
  Contains operational/audit/history data: Yes / No
```

If no tables are created or modified, write:

```text
No tables created or modified.
```

### Columns

```text
Columns:
- Table:
  Column:
  Type:
  Nullable: Yes / No
  Default:
  Indexed: Yes / No
  Unique: Yes / No
  Purpose:
  Sensitive: Yes / No
```

If no columns are created or modified, write:

```text
No columns created or modified.
```

### Foreign Keys and Referential Actions

Every foreign key must explicitly define delete and update behavior.

Do not leave cascade, restrict, set null, or no action implicit.

```text
Foreign keys:
- From:
    Table:
    Column:
  To:
    Table:
    Column:
  Relationship meaning:
  Nullable: Yes / No
  On delete: cascade / restrict / set null / no action
  On update: cascade / restrict / no action
  Reason for delete behavior:
  Reason for update behavior:
```

If no foreign keys are required, write:

```text
No foreign keys required.
```

### Model Relationships

Every database relationship must define the related Eloquent relationship when applicable.

```text
Model relationships:
- Model:
  Relationship method:
  Type: belongsTo / hasOne / hasMany / belongsToMany / morphTo / morphMany / other
  Related model:
  Foreign key:
  Local / owner key:
  Inverse relationship required: Yes / No
  Inverse relationship name:
  Nullable relation: Yes / No
```

If no model relationships are required, write:

```text
No model relationships required.
```

### Snapshot Requirements

Use this section when the Pack creates or modifies operational, historical, reporting, audit, inbound-event, external response, operation-attempt, retry, dead-letter, usage, cost, configuration, or integration-related records.

Snapshot Required: Yes / No
Snapshot Purpose:
- Audit
- Debug
- Reporting
- Retry stability
- Historical accuracy
- external-response reconstruction
- Operator troubleshooting
- Other:
Snapshot fields:
- Table:
  Column:
  Source:
  Snapshot timing:
  Reason:
  Sensitive: Yes / No
  Masking required: Yes / No
  Can change after original event: Yes / No

Examples of snapshot candidates:

external_integration_key_snapshot
external_integration_name_snapshot
external_method_snapshot
owner_configuration_snapshot
send_route_snapshot_json
template_ref_snapshot
template_tokens_snapshot_json
request_payload_snapshot_json
external_payload_snapshot_json
domain_payload_snapshot_json
sensitive_target_masked_snapshot
source_context_snapshot_json

If no snapshot is required, write:

No snapshot fields required.

### Indexes and Constraints

```text
Indexes and constraints:
- Table:
  Type: index / unique / composite unique / foreign key index / check constraint
  Columns:
  Purpose:
  Required for idempotency: Yes / No
  Required for query performance: Yes / No
```

If no indexes or constraints are required, write:

```text
No indexes or constraints required.
```

### Delete / Update Policy

```text
Delete / update policy:
- Entity:
  Can be deleted by user/admin: Yes / No
  Delete behavior:
  Related child data behavior:
  Audit/history preservation required: Yes / No
  Safe default:
  Reason:
```

Default rule:

```text
Do not use cascade delete for operational logs, operational history, audit data, inbound events, attempts, dead letters, usage/cost records, or external responses unless the Pack explicitly justifies it.
```

### Data Backfill / Migration Data Changes

```text
Data backfill required:
- Source:
  Target:
  Transformation:
  Safe environment:
  Command required:
  Operator approval required:
```

If no data backfill is required, write:

```text
No data backfill required.
```

### Rollback / Down Migration Requirements

```text
Rollback requirements:
- Migration:
  Down behavior:
  Data loss risk: Yes / No
  Manual backup required: Yes / No
  Operator approval required: Yes / No
```

### Validation Requirements

```text
Validation required:
- Migration creates expected tables: Yes / No / Not applicable
- Columns have correct types/nullability/defaults: Yes / No / Not applicable
- Foreign keys have explicit on delete behavior: Yes / No / Not applicable
- Foreign keys have explicit on update behavior: Yes / No / Not applicable
- Model relationships match database keys: Yes / No / Not applicable
- Indexes/unique constraints match query/idempotency needs: Yes / No / Not applicable
- Rollback behavior reviewed: Yes / No / Not applicable
- Data-loss risk reviewed: Yes / No / Not applicable
```


## 23. Commenting Requirements

Follow:

- `docs/ai/rules/COMMENTING-RULES.md`

Add Pack-specific commenting requirements here only if this Pack needs them.

## 24. Testing Requirements

Follow:

```text id="ekycds"
docs/ai/rules/TEST-AND-VALIDATION-RULES.md
```

### Test Quality Requirements

```text id="tosdok"
Test Quality Required: Yes / No
Behavioral Assertions Required: Yes / No
Contract Assertions Required: Yes / No
Database Assertions Required: Yes / No
Side-effect Assertions Required: Yes / No
Negative/Error Scenario Tests Required: Yes / No
Authorization/Permission Tests Required: Yes / No
Idempotency Tests Required: Yes / No
Queue/Event/Job Assertions Required: Yes / No
External Integration Fake/Mock Required: Yes / No
Security/Secret Masking Tests Required: Yes / No
```

Tests must verify real behavior, not only superficial success.

Do not add tests that only check:

```text id="irjuxn"
- status code only
- response is not empty only
- record exists only without checking meaningful fields
- method returns something only
- no exception was thrown only
```

### Behavior to Prove

```text id="2sqzix"
The tests must prove:
- ...
```

### Required Assertions

```text id="zkegwb"
Required assertions:
- Response contract:
- Database state:
- Status/state transition:
- Queue/job/event dispatch:
- Permission/auth behavior:
- Validation error behavior:
- External adapter/fake behavior:
- Idempotency behavior:
- Security/masking behavior:
```

### Required Scenarios

```text id="sljg85"
Required scenarios:
- Happy path:
- Invalid input:
- Unauthorized:
- Forbidden:
- Duplicate/idempotency:
- External-integration failure:
- Callback/inbound-event duplicate:
- Terminal state protection:
- Missing config/settings:
- Other:
```

If a scenario is not applicable, write:

```text id="iyl61i"
Not applicable because: ...
```

### Test Data Requirements

```text id="xt4vdq"
Test data required:
- Data:
  Setup method: factory / seeder / API setup / manual / fake / mock
  Safe environment: local / testing / development
  Cleanup required: Yes / No
```

Tests must use fake/example values and must not use real external-service credentials, real tokens, sensitive real identifiers, or production URLs.

## 25. Acceptance Checklist

## 26. Tests to Add

## 27. Tests to Run

## 28. Expected Output

Include expected source code, test, documentation, run/report, or index outputs specific to this Pack.

If this Pack creates, moves, archives, supersedes, rejects, accepts, closes, or materially changes any tracked AI documentation file, update the related INDEX file if Pack scope allows it.

For documentation maintenance rules, follow:

- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`

## 29. Operator Execution Checklist

### Before AI Execution

```text
Pack-specific before-execution operator checks:
- ...
```

If no Pack-specific before-execution checks are required, write:

```text
No Pack-specific before-execution operator checks required.
```

### After AI Execution

```text
Pack-specific after-execution operator checks:
- ...
```

If no Pack-specific after-execution checks are required, write:

```text
No Pack-specific after-execution operator checks required.
```


## 30. Agent Final Report

Follow:

- `docs/ai/rules/REPORTING-RULES.md`

If AI documentation was created or changed, include the Documentation Maintenance section defined in:

- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`

## 31. Review Checklist

## 32. Rollback / Safety Notes

## 33. Stop Conditions

## 34. Open Questions
