# TMS-DECISION-0010 — Async batch state and recovery

Status: Accepted
Scope: Project architecture / orchestration and persistence
Source: Operator decision
Date: 2026-10-07
Review At: async orchestration Pack normalization
Blocking: Yes for queued or parallel execution
Closure Condition: superseded by a separately approved orchestration decision
Next Review At: `AI-PACK-TMS-ASYNC-BATCH-ORCHESTRATION-0013`

## Type

4. Project-level architecture decision

## Decision

TMS uses Laravel Queue, Jobs, Events, locks, and Bus Batches for asynchronous execution. The application layer owns durable operation, batch, item, and attempt records; Artisan, Web UI, and MCP clients only request transitions and query results.

Approved states are:

```text
Operation: draft, awaiting_input, awaiting_approval, ready, queued, running,
           succeeded, failed, cancelling, cancelled, expired
Batch:     planned, queued, running, cancelling, cancelled, completed,
           completed_with_failures, failed, interrupted
Item:      pending, queued, running, passed, failed, skipped, blocked, cancelled
Attempt:   queued, running, succeeded, failed, abandoned
```

Terminal states cannot return to active states. Resume creates or continues only unfinished eligible items after compatibility and idempotency checks. Retry creates a new Attempt and never erases prior history. Cancel is cooperative, prevents new item dispatch, and invokes target cleanup boundaries. Independent items may run in parallel within configured limits; the same logical item is protected by an idempotency key and lock. Infrastructure retries and scenario/business retries are distinct. A non-idempotent action is not retried unless its scenario declares a safe retry contract.

## Reason

Tens of thousands of scenarios cannot rely on a single terminal process. Durable state, bounded concurrency, exact retry semantics, and interruption recovery are required for fast and reviewable execution.

## Impact

- Draft Pack 0008's sequential CLI-only design is superseded.
- New persistence, migrations, jobs, policies, locks, and worker configuration are required through Pack 0013.
- Queue transport is configurable; tests use fakes or a safe test connection.
- Reports must preserve attempts and final outcomes.

## Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: Future client mapping only
UI / Admin UI Error Impact: Future client mapping only
Error Code Impact: Yes
Exception Handling Impact: Yes
External Error Normalization Impact: Executor/App boundary
Retry / Failure Classification Impact: Yes
Status Model Impact: Yes — exact states above
Logging Impact: Yes
Traceability Impact: Yes — operation_id, batch_id, item_id, attempt_id and correlation_id
Sensitive Data Impact: Yes — payload snapshots are allowlisted
Testing Impact: Yes
Documentation Impact: Yes
```

## Required Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0003.md`.

## Required Follow-up Updates

- Pack 0013 must define the full transition table, schema, queue policy, locks, timeout and stale-work reconciliation.
- Pack 0014 must expose read models and attempt-aware reports.
- Pack 0015 must document queue prerequisites and operator recovery commands.

## Review / Resolution

```text
Reviewed In: operator request for complete async, queue, and batch behavior
Review Result: operator approved queued, batched, resumable and parallel execution
Resolution: accepted
Next Review At: Pack 0013 normalization
Resolution Notes: retries preserve history and cannot bypass idempotency or target cleanup.
```
