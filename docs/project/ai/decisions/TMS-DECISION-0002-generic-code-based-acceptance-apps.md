# TMS-DECISION-0002 — Generic code-based Acceptance Apps and Core boundary

Status: Accepted
Scope: Project
Source: Operator answer
Date: 2026-09-23
Review At: first Pack that introduces a real target-system App
Blocking: No
Closure Condition: superseded by a separately approved TMS architecture decision
Next Review At: first real Acceptance App Pack

## Type

4. Project-level architecture decision

## Decision

TMS is a Laravel-based platform for executing and managing Acceptance tests for multiple systems. It is not a Martfury-specific or DieselKhodro-specific test project.

Acceptance automation uses Playwright PHP and is implemented as source code. Each target system is represented by an independently bounded Acceptance App or Laravel module containing that system's scenarios, actions, selectors, fixtures, and integration adapters. Authentication is normally a suite or capability inside a target-system App; it is not a shared target-specific module by default.

Core owns only capability-neutral execution contracts and runtime behavior such as browser creation, context/page lifecycle, scenario and step execution, assertions, normalized results, and safe failure handling. Target-specific URLs, APIs, credentials, selectors, payloads, and product behavior must remain outside Core.

The first implementation Pack must stabilize only the minimum runner foundation needed to prove this boundary. It must not add a real target-system App, UI, queue orchestration, scheduling, parallel execution, retry policy, artifact management, or other maximal platform capabilities.

## Reason

The operator identified TMS as shared Acceptance-test infrastructure for several systems and confirmed that the former Auth module was an early structural experiment rather than a finished implementation. Source inspection showed useful Service/Action ideas in that historical module, but also target-specific coupling and incomplete behavior. A generic Core plus code-owned Apps preserves the useful organization without making Martfury the architecture of TMS.

## Impact

- Core must no longer contain DieselKhodro or Martfury clients, configuration, selectors, or product workflows.
- Browser and test execution context must not depend on an HTTP request or root Eloquent models.
- Executable test definitions remain in source control; the database stores execution/profile/history data rather than executable workflow definitions.
- Future target systems receive their own App/module boundary and may contain suites such as Auth, Ecommerce, or other business capabilities.
- `AI-PACK-TMS-ACCEPTANCE-RUNNER-STABILIZATION-0002` is the first implementation Pack for this decision.
- Auth and Ecommerce remain removed; this decision does not authorize restoring either historical source tree.

## Files to Update

- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/packs/AI-PACK-TMS-ACCEPTANCE-RUNNER-STABILIZATION-0002.md`
- `docs/project/ai/packs/PACKS-INDEX.md`

## Required change request

No Change Request is required. The repository is in pre-Release stabilization and no accepted runtime architecture assigns target-specific behavior to Core.

## future-work

- Add the first real target-system Acceptance App through a separate approved Pack.
- Decide the operator entry point, queue/scheduling model, artifact retention, secret/profile storage, and UI only when required by a later Pack.
- Revisit whether Auth remains a suite inside each App or becomes a reusable capability only after two real Apps prove a genuinely shared contract.

## Review / Resolution

```text
Reviewed In: operator discussion preceding AI-PACK-TMS-ACCEPTANCE-RUNNER-STABILIZATION-0002
Review Result: operator confirmed multi-system scope, Playwright PHP, Laravel structure, tests-as-code Apps, and minimal stabilization
Resolution: accepted
Next Review At: first real Acceptance App Pack
Resolution Notes: The historical Auth implementation is reference evidence only and must not be restored or copied as an accepted implementation.
```

## Notes

This decision establishes the product and ownership boundary. Acceptance of the implementation Pack and its eventual Run remain separate operator gates.
