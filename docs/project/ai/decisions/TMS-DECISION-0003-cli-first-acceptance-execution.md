# TMS-DECISION-0003 — CLI-first Acceptance execution

Status: Accepted
Scope: Project
Source: Operator answer
Date: 2026-09-23
Review At: first Pack that introduces a real target-system App or a second execution channel
Blocking: No
Closure Condition: superseded by a separately approved TMS execution-channel decision
Next Review At: first real Acceptance App Pack

## Type

4. Project-level architecture decision

## Decision

The first operator execution entry point for TMS Acceptance Runs is an Artisan command.

The command resolves code-defined Acceptance Apps and Scenarios through an explicit project-level registry, resolves an existing Profile, delegates execution and persistence to `AcceptanceRunService`, and returns a small language-neutral structured result with a deterministic process exit code.

App registration remains explicit source code. This decision does not authorize filesystem scanning, database-defined executable workflows, automatic module discovery, a real target-system App, API/UI execution, queues, scheduling, retries, or parallel execution.

## Reason

An Artisan command is the smallest controllable operator boundary that makes the accepted Pack 0002 foundation executable without prematurely introducing public API contracts, permissions, UI, background execution, or a target-specific integration. Explicit source registration preserves Decision 0002's tests-as-code model and makes future App ownership visible in code review.

## Impact

- The root project owns the Registry, Artisan command, Profile lookup, and command result formatting.
- Core remains limited to capability-neutral runner contracts and runtime behavior.
- Future target-system Apps register themselves explicitly through a provider or another approved code-owned extension point.
- Command failures expose stable error codes and safe identifiers only; raw exceptions, Profile `extra`, credentials, URLs, and arbitrary result payloads remain hidden.
- A later Pack must choose the first real App and separately decide secret/Profile use before authenticated external execution.

## Files to Update

- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/packs/AI-PACK-TMS-ACCEPTANCE-COMMAND-REGISTRY-0003.md`
- `docs/project/ai/packs/PACKS-INDEX.md`

## Required change request

No Change Request is required. Decision 0002 explicitly deferred the execution entry point, and the operator selected Command as the first channel before Pack 0003 implementation.

## future-work

- Add the first real target-system App through a separate approved Pack.
- Decide secure App-specific credential/Profile storage before an authenticated Scenario reads secrets.
- Revisit API, UI, queue, scheduler, retry, artifact, and parallel execution only when separately required.

## Review / Resolution

```text
Reviewed In: operator discussion after accepted AI-PACK-TMS-ACCEPTANCE-RUNNER-STABILIZATION-0002
Review Result: operator proposed Command as the next execution entry point and requested the next Pack
Resolution: accepted
Next Review At: first real Acceptance App Pack or second execution channel
Resolution Notes: Pack 0003 must add only the generic Registry and Command boundary; it must not create a production App or external test.
```

## Notes

This decision selects only the first execution channel. It does not make CLI the permanent or exclusive TMS interface.
