# Error Handling and Logging Rules

## Purpose

This file defines reusable rules for error handling, error reporting, logging, and traceability.

These rules apply to API responses, Admin UI messages, internal exceptions, external-integration errors, callback or inbound-event errors, logs, reports, tests, and AI execution reports.

The goal is to make every error:

```text
- clear
- traceable
- actionable
- testable
- safe
- consistent
```

---

## 1. Core Error Handling Principles

Errors must not be vague.

The Agent must not introduce generic error behavior such as:

```text
Something went wrong.
Error occurred.
Failed.
Invalid request.
External integration error.
```

unless the real cause is unknown and the response still includes a stable `error_code` and trace identifier.

Every meaningful error must answer:

```text
- What failed?
- Where did it fail?
- Why did it fail, when safe to say?
- Is it retryable?
- Does it require admin/operator action?
- How can it be traced?
```

---

## 2. Stable Error Code Rule

When the applicable owner exposes machine-readable errors, it must use stable, language-neutral `error_code` values for API errors, internal classification, tests, logs, and operator troubleshooting.

Human-readable messages may be translated.

Error codes must not be translated.

Good examples:

```text
validation_failed
invalid_input
unsupported_operation
external_integration_disabled
external_configuration_missing
external_credentials_invalid
external_timeout
external_rate_limited
external_operation_rejected
idempotency_conflict
inbound_event_invalid_token
inbound_event_invalid_signature
inbound_event_duplicate
inbound_event_unknown_reference
terminal_status_protected
retry_policy_exceeded
dead_letter_created
unexpected_error
```

Bad examples:

```text
error
failed
bad_request
something_wrong
خطا
ناموفق
```

---

## 3. API Error Response Rule

API error responses must have a stable structure when the Pack creates or modifies API behavior.

A standard API error response should include:

```json
{
  "status": "error",
  "error_code": "external_integration_unavailable",
  "message": "The external integration is currently unavailable.",
  "request_id": "req_example",
  "details": {}
}
```

Validation errors should include field-level information when applicable:

```json
{
  "status": "error",
  "error_code": "validation_failed",
  "message": "The request contains invalid fields.",
  "request_id": "req_example",
  "errors": {
    "target": [
      {
        "error_code": "invalid_input",
        "message": "Target format is invalid."
      }
    ]
  }
}
```

Rules:

```text
- `message` is for humans.
- `error_code` is for clients, tests, logs, and troubleshooting.
- `request_id` or an equivalent trace identifier is required when available.
- `details` must not expose secrets, raw external-service credentials, raw tokens, or sensitive payloads.
```

---

## 4. Admin UI Error Message Rule

Admin UI errors must be clear, safe, and actionable.

Admin UI must not expose raw exceptions, raw external payloads, stack traces, secrets, tokens, authorization headers, callback secrets, or raw credentials.

A good Admin UI error should include, when applicable:

```text
- short human-readable explanation
- reason category
- suggested operator action
- error_code
- trace_id / request_id and owner-declared domain or operation identifiers when available
```

Example:

```text
عملیات انجام نشد.

دلیل:
سرویس خارجی در زمان مجاز پاسخ نداد.

اقدام پیشنهادی:
وضعیت سرویس خارجی و اتصال شبکه را بررسی کنید. اگر خطا موقت باشد، اجرای دوباره طبق سیاست Retry انجام می‌شود.

Error Code:
external_timeout

Trace ID:
req_example
```

---

## 5. Logging and Traceability Rule

Logs must contain enough structured context to trace the error.

A log entry should include applicable fields:

```text
event
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

Bad log:

```text
Send failed.
```

Better log context:

```json
{
  "event": "owner.operation_failed",
  "error_code": "external_timeout",
  "request_id": "req_example",
  "record_id": "record_example",
  "operation_id": "operation_example",
  "integration_key": "example-integration",
  "operation_type": "example-operation",
  "source_system": "example-source",
  "retryable": true,
  "admin_action_required": false,
  "exception_class": "ExternalTimeoutException"
}
```

The Agent must not add unstructured logs when structured context is required for troubleshooting.

---

## 6. External Integration Error Normalization Rule

External-integration-specific errors must be normalized before they affect core owner behavior.

Core services must not depend directly on raw external-service error text.

External adapters should map integration-specific errors into stable internal fields such as:

```text
error_code
external_error_code
external_error_message_snapshot
retryable
permanent
admin_action_required
failure_reason
```

Example:

```text
External integration raw error:
- integration: example-integration
- external_error_code: INVALID_INPUT
- external_message: required destination is missing

Normalized error:
- error_code: invalid_input
- retryable: No
- admin_action_required: No
```

Raw external payloads must be stored or displayed only according to security, masking, inbound-event, audit, and permission rules.

---

## 7. Error Classification Rule

Every important error should be classified when the Pack introduces or modifies error behavior.

Use:

```text
Error Code:
Category:
Retryable: Yes / No
Permanent: Yes / No
Admin Action Required: Yes / No
User / Operator Visible: Yes / No
Security Sensitive: Yes / No
Should Log: Yes / No
Should Audit: Yes / No
Affects Owner-managed State: Yes / No / Not applicable
```

Example:

```text
Error Code: external_timeout
Category: external_integration_error
Retryable: Yes
Permanent: No
Admin Action Required: No
User / Operator Visible: Yes
Security Sensitive: No
Should Log: Yes
Should Audit: Yes
Affects Owner-managed State: Yes
```

---

## 8. Sensitive Data Safety Rule

Errors, logs, UI messages, API responses, reports, tests, API-client examples, and AI execution reports must not expose sensitive values.

Never expose raw:

```text
external-service API key
external-service secret
service token
callback token
webhook secret
signing key
Authorization header
raw credential
raw secret_json
sensitive target or personal identifier
raw external payload containing secrets
```

Use safe placeholders:

```text
***REDACTED***
example-token
example-sensitive-value
https://example.test
```

Sensitive target or personal identifiers should be masked when full visibility is not explicitly allowed.

Example:

```text
98912****123
```

---

## 9. Error Testing Rule

Tests for error behavior must not check only HTTP status codes.

Weak test: assert only that the response status is an error.

Better test: assert the expected status, stable `status` and `error_code`, required `message` and trace identifier, field-error structure, and absence of sensitive details.

When error handling changes, tests should verify applicable behavior:

```text
- stable error_code
- expected HTTP status
- response shape
- validation error structure
- permission/auth behavior
- retryable/permanent classification
- admin_action_required classification
- status transition effect
- log context fields
- sensitive data masking
```

---

## 10. Pack Responsibility Rule

Any AI Pack that creates or modifies API behavior, Admin UI behavior, external integration, callback/inbound-event handling, state transitions, retry/dead-letter behavior, validation, settings, permissions, logs, reports, or tests must explicitly state whether error handling, logging, or traceability is affected.

If there is no error handling or logging impact, the Pack must explicitly say:

```text
No error handling, logging, or traceability changes required.
```
