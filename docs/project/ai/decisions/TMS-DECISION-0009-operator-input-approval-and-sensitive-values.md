# TMS-DECISION-0009 — Operator input, approval, and sensitive values

Status: Accepted
Scope: Project architecture / operator workflow
Source: Operator decision
Date: 2026-10-07
Review At: input/approval Pack normalization
Blocking: Yes for scenarios that require external input or approval
Closure Condition: superseded by a separately approved input/security decision
Next Review At: `AI-PACK-TMS-OPERATOR-INPUT-APPROVAL-0011`

## Type

4. Project-level architecture decision

## Decision

An automated TMS scenario may declare required operator inputs and approval gates. After those requirements are satisfied through a predetermined workflow, TMS performs all executable actions and assertions automatically. TMS contains no manual-only runtime test type.

Each requirement is a versioned, code-owned schema that declares key, type, sensitivity, validation, source choices, scope, expiry, and whether approval is required. The workflow supports discovery, interactive entry, structured non-interactive submission, validation, review, approval, expiry, and cancellation through operation services.

Non-sensitive values and approval facts may be persisted as structured operation state. Usable passwords, tokens, cookies, session storage, and other secrets must not appear in command arguments, shell history, source, Profile `extra`, logs, reports, evidence, or general TMS tables. Asynchronous work receives an App-owned secret reference and resolves the value at runtime through a target-owned boundary. A literal secret may be used only for a synchronous in-memory flow explicitly supported by a later Pack and must never be persisted. Approval records are workflow evidence; they do not replace client authentication or authorization.

## Reason

Many E2E tests need an account, target data, a one-time value, or explicit confirmation. A declared workflow keeps execution automated while avoiding hardcoded data and an open terminal dependency for queued work.

## Impact

- Apps own requirement schemas, resource references, and sensitive resolution.
- Shared TMS owns neutral collection state, validation dispatch, approval facts, expiry, and traceability.
- A missing input or approval produces a waiting state, not a manually executed test.
- Decision 0005's testing/staging, synthetic-data, and safe-output constraints remain active.

## Approved State Model

```text
draft -> awaiting_input -> awaiting_approval -> ready
draft/awaiting_input/awaiting_approval/ready -> cancelled
awaiting_input/awaiting_approval/ready -> expired
```

Transitions may skip `awaiting_input` or `awaiting_approval` when the schema does not require them. Execution states are owned by Decision 0010.

## Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: Future client mapping only
UI / Admin UI Error Impact: Future client mapping only
Error Code Impact: Yes
Exception Handling Impact: Yes
External Error Normalization Impact: App-owned
Retry / Failure Classification Impact: Yes — missing/expired input is operator-action-required, not retryable execution failure
Status Model Impact: Yes — exact prerequisite states above
Logging Impact: Yes — values excluded; keys and state changes allowed
Traceability Impact: Yes
Sensitive Data Impact: Yes
Testing Impact: Yes
Documentation Impact: Yes
```

## Required Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0003.md`.

## Required Follow-up Updates

- Implement the workflow in Pack 0011 and target resource boundary in Pack 0012.
- Add Persian CLI instructions in Pack 0015.
- A future Web/MCP Pack must bind approval actors to its authenticated principal.

## Review / Resolution

```text
Reviewed In: operator discussion about automatic tests that may request data or confirmation
Review Result: operator approved predetermined input/approval workflows and rejected hardcoded target data
Resolution: accepted
Next Review At: Pack 0011 normalization
Resolution Notes: queued execution may use references, never raw CLI secrets.
```
