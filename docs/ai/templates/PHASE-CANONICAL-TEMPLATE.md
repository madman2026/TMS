# PHASE-{X}-CANONICAL-DECISIONS

Use this template for Phase-level Canonical files.

A Phase Canonical contains official decisions and contracts that apply only to one Phase.

For Canonical rules, read:

```text
docs/ai/rules/CANONICAL-RULES.md
```

---

# Release {N} / Phase {X} — [Phase Name] Canonical Decisions

## 1. Document Purpose

This file is the official source of Phase-level decisions and contracts for Release {N} / Phase {X}.

This file contains only decisions and contracts that apply to this Phase.

Release-level decisions and shared cross-Phase contracts must live in the Release Canonical file.

This Phase Canonical must not contradict Global Canonical or Release Canonical.

If a Phase-level decision later affects multiple Phases inside the same Release, promote it to the Release Canonical after approval.

This file must not duplicate detailed Pack-level implementation instructions.

---

## 2. Phase Metadata

```text
Release:
Phase:
Phase Name:
Phase Status:
Created Date:
Last Updated:
Canonical Owner:
Related Phase Guide:
Related Release Canonical:
Related Decision Log:
```

Recommended `Phase Status` values:

```text
draft
active
accepted
closed
superseded
archived
```

---

## 3. Phase Scope

Phase goal:

```text
...
```

This Phase includes:

```text
- ...
- ...
- ...
```

This Phase produces:

```text
- ...
- ...
- ...
```

---

## 4. Phase Out of Scope

The following must not be implemented in this Phase unless approved by Change Request:

```text
- ...
- ...
- ...
```

If the item belongs to a later Phase, mention the target Phase when known.

---

## 5. Required Higher-Level Canonical Inputs

This Phase must follow:

```text
Global Canonical:
- `<owner-docs-root>/canonical/GLOBAL-CANONICAL-DECISIONS.md`

Release Canonical:
- `<owner-docs-root>/canonical/release-{N}/RELEASE-{N}-CANONICAL-DECISIONS.md`
```

Relevant higher-level decisions/contracts:

```text
- ...
- ...
- ...
```

If none are required beyond normal routing, write:

```text
No additional higher-level canonical inputs beyond the standard Global and Release Canonical files.
```

---

## 6. Phase-Level Canonical Decisions

Use this section for decisions that apply only to this Phase.

Examples may include:

```text
Phase implementation boundary:
Phase-specific service:
Phase-specific job:
Phase-specific migration:
Phase-specific admin behavior:
Phase-specific validation:
Phase-specific testing boundary:
```

Decisions:

```text
...
```

---

## 7. Phase Technical Contracts

Use this section for Phase-specific technical contracts.

These contracts must not override Release-level shared contracts.

---

### 7.1 API / Route Contracts

```text
Endpoint:
Method:
Purpose:
Request Contract:
Response Contract:
Phase Boundary:
```

If no Phase-specific API/route contract exists, write:

```text
No Phase-specific API/route contracts.
```

---

### 7.2 Data / Migration Contracts

```text
Table / Entity:
Purpose:
Fields:
Indexes:
Migration Notes:
Rollback Notes:
```

If no Phase-specific data/migration contract exists, write:

```text
No Phase-specific data or migration contracts.
```

---

### 7.3 Service / Job Contracts

```text
Service / Job:
Purpose:
Input:
Output:
Queue:
Error Handling:
```

If no Phase-specific service/job contract exists, write:

```text
No Phase-specific service or job contracts.
```

---

### 7.4 Provider / Integration Contracts

```text
Provider / Integration:
Purpose:
Adapter / Interface:
Normalization:
Failure Handling:
```

If no Phase-specific provider/integration contract exists, write:

```text
No Phase-specific provider or integration contracts.
```

---

### 7.5 Admin / UI Contracts

```text
Admin Area:
Purpose:
Permissions:
Visibility:
Allowed Actions:
Forbidden Actions:
```

If no Phase-specific admin/UI contract exists, write:

```text
No Phase-specific admin/UI contracts.
```

---

### 7.6 Testing / Validation Contracts

```text
Required Tests:
Required Validation:
Manual Checks:
Not Required In This Phase:
```

If no Phase-specific testing/validation contract exists, write:

```text
No Phase-specific testing or validation contracts.
```

---

## 8. Pack Normalization Requirements

Before executing Packs in this Phase, each Pack must be checked against:

```text
- Global Canonical
- Release Canonical
- this Phase Canonical
- AI Pack Template
- relevant rules
- relevant guides
```

Phase-specific Pack normalization checks:

```text
- ...
- ...
- ...
```

If no additional Phase-specific normalization check exists, write:

```text
No additional Phase-specific Pack normalization requirements.
```

---

## 9. Phase Stop Conditions

The AI Agent or operator must stop if:

```text
- ...
- ...
- ...
```

Stop conditions should include:

- contradiction with Release Canonical
- attempt to change earlier Phase contracts silently
- out-of-scope implementation
- unauthorized schema or API change
- security/privacy violation
- missing required decision approval
- need for Change Request or Remediation

---

## 10. Promotion Candidates

Use this section for Phase-level decisions that may need to be promoted to Release Canonical.

```text
- Decision:
  Reason it may affect multiple Phases:
  Promotion Status:
  Required Approval:
```

Recommended `Promotion Status` values:

```text
not-needed
candidate
approved
promoted
rejected
```

If none:

```text
No promotion candidates.
```

---

## 11. Phase Change Policy

If a Pack, Review, Source Update Review, or Remediation requires changing this Phase Canonical, follow:

```text
docs/ai/rules/CANONICAL-RULES.md
docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md
```

If the change affects more than this Phase, do not keep it only in this Phase Canonical.

Promote it to Release Canonical after approval.

Any non-trivial decision must be recorded in the relevant Decision Log first.

---

## 12. Temporary / Revisit-Later Decisions

Use this section only for accepted temporary decisions that affect this Phase.

```text
- Decision:
  Status: Temporary / Revisit Later
  Reason:
  Review At:
  Owner:
```

If none:

```text
No temporary or revisit-later Phase-level decisions.
```

---

## 13. Open Canonical Questions

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

## 14. Canonical Change References

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
