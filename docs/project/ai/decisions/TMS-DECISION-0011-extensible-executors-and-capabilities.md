# TMS-DECISION-0011 — Extensible executors and capability routing

Status: Accepted
Scope: Cross-module execution contract / Core implementation boundary
Source: Operator decision
Date: 2026-10-07
Review At: Core executor Pack normalization
Blocking: Yes for non-browser execution and capability-based planning
Closure Condition: superseded by a separately approved executor contract
Next Review At: `AI-PACK-CORE-EXECUTOR-RUNTIME-0003`

## Type

4. Project-level cross-owner architecture decision

## Decision

Scenarios declare required capabilities and intent; they do not depend directly on Playwright PHP or a concrete HTTP client. Core owns capability-neutral executor contracts, an explicit executor registry, lifecycle, normalized results, and unsupported-capability outcomes. Browser and API/HTTP are the first executor families; later executors use the same extension point.

Planning resolves capabilities before dispatch. A Playwright PHP limitation does not by itself authorize deleting a scenario. The target owner must first evaluate another executor, an API path, a DOM/backend oracle, or a separately approved executor capability. If no reliable executable path exists, the traceability policy in Decision 0012 determines partial or excluded disposition.

Executor registration is explicit source code. Core must not contain target URLs, credentials, selectors, payloads, accounts, fixtures, or product rules. Target modules compose executor-neutral actions and target-owned adapters.

## Reason

Browser-only coupling would prevent API coverage and make the test corpus dependent on one package's limitations. Capability routing lets TMS maximize E2E coverage while preserving a target-neutral Core.

## Impact

- Existing Core browser runner remains a supported adapter and must be migrated without losing accepted browser behavior.
- Core Pack 0003 implements the reusable contract; project Packs consume it.
- Unsupported capabilities are visible planning/report outcomes, not raw runtime exceptions.
- This decision does not authorize real target I/O.

## Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: No
UI / Admin UI Error Impact: No
Error Code Impact: Yes — executor_not_found, capability_unsupported and normalized executor failures
Exception Handling Impact: Yes
External Error Normalization Impact: Yes at executor boundary
Retry / Failure Classification Impact: Yes
Status Model Impact: No new project state
Logging Impact: Yes
Traceability Impact: Yes
Sensitive Data Impact: Yes
Testing Impact: Yes
Documentation Impact: Yes
```

## Required Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0003.md`.

## Required Follow-up Updates

- Normalize and approve Core Pack 0003 after Packs 0009–0012 establish the request/resource contracts it consumes.
- Add target-specific executor adapters only in the target owner.

## Review / Resolution

```text
Reviewed In: operator discussion about Playwright PHP limits and alternative executors
Review Result: operator approved maximum executable coverage through Web App, API, and extensible executors
Resolution: accepted
Next Review At: Core Pack 0003 normalization
Resolution Notes: concrete executor limitations require a documented fallback analysis.
```
