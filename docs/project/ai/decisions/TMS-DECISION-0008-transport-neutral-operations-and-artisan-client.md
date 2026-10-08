# TMS-DECISION-0008 — Transport-neutral operations and complete Artisan client

Status: Accepted
Scope: Project architecture / application boundary and clients
Source: Operator decision
Date: 2026-10-07
Review At: operation-layer Pack normalization
Blocking: Yes for new operator operations
Closure Condition: superseded by a separately approved execution-channel decision
Next Review At: `AI-PACK-TMS-OPERATION-SERVICE-LAYER-0009`

## Type

4. Project-level architecture decision

## Decision

All TMS capabilities are exposed through transport-neutral application/operation services. Artisan is the first complete client and must support App, Component, Suite and Scenario scaffolding/validation/import/registration; catalog and planning; input discovery/submission; approval; synchronous, asynchronous and batch execution; status, retry, resume and cancel; and reports.

Artisan commands contain presentation, prompting, option parsing, exit-code mapping, and result rendering only. They call the same typed services that future Web UI and MCP clients will call. Runtime and domain services must not depend on `Command`, terminal state, process lifetime, CLI arguments, or parsing console output.

Operations accept typed, versioned requests and return typed, language-neutral results with stable codes and trace identifiers. Interactive CLI and structured non-interactive/JSON modes are both required. Executable registrations stay in source code.

Accepted clarification (2026-10-08): `docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md` specifies semantic service DTOs, client-owned serialization/presentation and Laravel Prompts for the interactive Artisan adapter. It preserves this boundary and accepted legacy command compatibility.

## Reason

The operator needs a fast Artisan workflow now and Web UI/MCP clients later. A shared application boundary prevents duplicated orchestration and makes every client observe the same validation, authorization hook, state, result, and error contract.

## Impact

- Decision 0003 remains valid as the first-channel choice and is extended: CLI is complete but no longer the architecture boundary.
- Existing `acceptance:list`, `acceptance:plan`, and `acceptance:run` commands must be migrated to thin clients without breaking their accepted behavior unless a Pack explicitly versions it.
- Future HTTP/MCP adapters may be added without moving business logic out of application services.
- No Web UI, public API, or MCP server is implemented by this decision.

## Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: Future client mapping only
UI / Admin UI Error Impact: Future client mapping only
Error Code Impact: Yes — shared operation codes replace client-specific failure text
Exception Handling Impact: Yes
External Error Normalization Impact: No
Retry / Failure Classification Impact: Yes for operation results
Status Model Impact: Yes where operations are persisted
Logging Impact: Yes
Traceability Impact: Yes — every stateful operation receives operation_id and correlation_id
Sensitive Data Impact: Yes — requests/results must be safe for every client
Testing Impact: Yes
Documentation Impact: Yes
```

## Required Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0003.md`.

## Required Follow-up Updates

- Implement the application boundary in Pack 0009.
- Complete the Artisan surface and update `docs/project/ai/guides/ACCEPTANCE-CLI.fa.md` in Pack 0015.
- Design Web UI and MCP adapters only through later approved Packs.

## Review / Resolution

```text
Reviewed In: operator discussion on Artisan, Web UI, and MCP use
Review Result: operator required every operation through Artisan without making the system dependent on Artisan
Resolution: accepted
Next Review At: operation-layer Pack normalization
Resolution Notes: Artisan is a client of the system, not its service layer.
```
