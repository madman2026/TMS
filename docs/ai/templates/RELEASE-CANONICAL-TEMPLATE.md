# RELEASE-{N}-CANONICAL-DECISIONS

Use this template for Release-level Canonical files.

A Release Canonical contains official decisions and shared contracts that affect more than one Phase inside the same Release.

For Canonical rules, read:

```text
docs/ai/rules/CANONICAL-RULES.md
```

---

# Release {N} — [Release Name] Canonical Decisions

## 1. Document Purpose

This file is the official source of Release-level decisions and shared contracts for Release {N}.

This file contains only decisions and contracts that affect more than one Phase inside Release {N}.

Phase-specific decisions must live in the relevant Phase Canonical file.

If a Phase-level decision later affects multiple Phases inside Release {N}, promote it to this file after approval.

This file must not duplicate detailed Pack-level implementation instructions.

---

## 2. Release Metadata

```text
Release:
Release Name:
Release Status:
Created Date:
Last Updated:
Canonical Owner:
Related Release Guide:
Related Decision Log:
```

Recommended `Release Status` values:

```text
draft
active
accepted
closed
superseded
archived
```

---

## 3. Release Scope

List the Phases included in this Release.

```text
Phase {X}: ...
Phase {Y}: ...
Phase {Z}: ...
```

Release goal:

```text
...
```

Release must establish:

```text
- ...
- ...
- ...
```

---

## 4. Release Out of Scope

The following must not be implemented in this Release unless approved by Change Request:

```text
- ...
- ...
- ...
```

---

## 5. Core Release Decisions

Use this section for the key Release-level decisions that define the identity, boundaries, and main runtime direction of this Release.

Examples may include:

```text
Plugin path:
API base:
Permission prefix:
Route name prefix:
Active channels:
Default provider:
Sending mode:
Manual send policy:
Provider test policy:
Queue policy:
Admin UI scope:
```

Decisions:

```text
...
```

---

## 6. Shared Release Contracts

This section records shared technical contracts for Release {N}.

These contracts may affect more than one Phase and must not be silently changed by a later Phase or Pack.

---

### 6.1 API Contracts

```text
Endpoint:
Method:
Purpose:
Request Contract:
Response Contract:
Notes:
```

Repeat as needed.

---

### 6.2 Route and Permission Contracts

```text
Route name prefix:
Permission prefix:
Reserved names:
Collision checks:
```

Constraints:

```text
...
```

Permissions:

```text
- ...
- ...
- ...
```

---

### 6.3 Config Contracts

```text
Config file:
Required keys:
Environment variables:
Default values:
Disabled MVP flags:
```

Example structure:

```text
...
```

---

### 6.4 Data / Model / Schema Contracts

```text
Entity:
Purpose:
Required Fields:
Relationships:
Indexes:
Retention / Audit Notes:
```

Repeat as needed.

---

### 6.5 Status and State Contracts

Message statuses:

```text
- ...
```

Callback statuses:

```text
- ...
```

Attempt statuses:

```text
- ...
```

State rules:

```text
- ...
- ...
- ...
```

---

### 6.6 Provider / Integration Contracts

```text
Provider interface:
Provider adapter:
Provider key:
Send result normalization:
Error normalization:
Provider test behavior:
```

Rules:

```text
- ...
- ...
- ...
```

---

### 6.7 Queue / Job Contracts

```text
Queue names:
Job names:
Retry behavior:
Callback processing behavior:
Timeout behavior:
```

Rules:

```text
- ...
- ...
- ...
```

---

### 6.8 Security / Privacy Contracts

```text
Authentication:
Service token behavior:
Callback token behavior:
Secret handling:
Recipient masking:
Logging restrictions:
```

Rules:

```text
- ...
- ...
- ...
```

---

### 6.9 Admin / Operations Contracts

```text
Admin screens:
Permissions:
Read-only views:
Provider test UI:
Operational visibility:
```

Rules:

```text
- ...
- ...
- ...
```

---

## 7. Release-Level Stop Conditions

The AI Agent or operator must stop if:

```text
- ...
- ...
- ...
```

Stop conditions should include violations of Release scope, security, privacy, provider boundaries, canonical conflicts, or unauthorized architectural changes.

---

## 8. Change Policy

If a later Phase, Pack, Review, Source Update Review, or Remediation requires changing a Release-level decision or shared Release contract, the AI Agent must stop and follow:

```text
docs/ai/rules/CANONICAL-RULES.md
docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md
```

Any non-trivial decision must be recorded in the relevant Decision Log first.

Change Requests and Remediation Packs must reference the affected Canonical decision or contract.

---

## 9. Promotion and Conflict Notes

Use this section for approved promotion or conflict notes.

```text
Promoted from Phase Canonical:
- Decision:
  Source:
  Reason:

Conflict Resolved:
- Conflict:
  Resolution:
  Source:
```

If none:

```text
No promotion or conflict notes.
```

---

## 10. Temporary / Revisit-Later Decisions

Use this section only for accepted temporary decisions that affect Release-level behavior.

```text
- Decision:
  Status: Temporary / Revisit Later
  Reason:
  Review At:
  Owner:
```

If none:

```text
No temporary or revisit-later Release-level decisions.
```

---

## 11. Open Canonical Questions

```text
- Question:
  Impact:
  Owner:
  Needed By:
```

If none:

```text
No open canonical questions.
```

---

## 12. Canonical Change References

This section is optional.

Use it for short traceability references only.

Do not use this section as a substitute for updating the actual contract in the correct section.

```text
| Source | Summary |
|---|---|
| `...` | ... |
```

If none:

```text
No canonical change references.
```
