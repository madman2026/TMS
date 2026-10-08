# AI-PACK-TMS-OPERATOR-INPUT-APPROVAL-0011 — Operator input and approval workflow

Status: `draft — stage 4 under Decision 0015; normalize after accepted Pack 0010`

Generated: `2026-10-07`

Decision: Decisions 0008–0009, 0013, and 0014; Decision 0014 applied on 2026-10-08 (documentation only).

Current Roadmap Notice (2026-10-08): Decision 0015 supersedes former stage numbering and all compatibility assumptions. Normalize this Draft against the single version-2 hierarchy contract before approval.

## 1. Task ID

`AI-PACK-TMS-OPERATOR-INPUT-APPROVAL-0011`

## 2. Task Title

Persist declared non-secret inputs and approval gates independently of client process lifetime.

## 3. Goal

Let scenarios wait for validated data/approval and then run automatically through any client.

## 4. Context

Async execution cannot depend on an open terminal. Decision 0009 permits structured operation state and App-owned secret references while forbidding general TMS secret persistence.

## 5. Related Release / Phase

Decision 0013, stage 3.

## 6. Related Epic / Feature / Story

Operator prerequisites for automated E2E execution.

## 7. Source References

Decisions 0005, 0008, 0009, 0013; accepted Packs 0009–0010 source after execution; current Profile model only as compatibility reference.

`docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md` — typed prerequisite data and CLI-only prompting.

## 8. Files to Create

- `app/Acceptance/Prerequisites/Enums/PrerequisiteState.php`
- `app/Acceptance/Prerequisites/Enums/InputSensitivity.php`
- `app/Acceptance/Prerequisites/Data/InputRequirement.php`
- `app/Acceptance/Prerequisites/Data/ApprovalRequirement.php`
- `app/Acceptance/Prerequisites/Contracts/OperatorContextProvider.php`
- `app/Acceptance/Prerequisites/PrerequisiteService.php`
- `app/Models/AcceptanceOperationRequest.php`
- `app/Models/AcceptanceOperationInput.php`
- `app/Models/AcceptanceOperationApproval.php`
- `database/migrations/2026_10_07_000010_create_acceptance_operation_requests_table.php`
- `database/migrations/2026_10_07_000011_create_acceptance_operation_inputs_table.php`
- `database/migrations/2026_10_07_000012_create_acceptance_operation_approvals_table.php`
- `database/factories/AcceptanceOperationRequestFactory.php`
- `app/Acceptance/Operations/Handlers/DiscoverOperationRequirements.php`
- `app/Acceptance/Operations/Handlers/SubmitOperationInput.php`
- `app/Acceptance/Operations/Handlers/ApproveOperation.php`
- `tests/Feature/AcceptancePrerequisiteWorkflowTest.php`
- `tests/Unit/PrerequisiteServiceTest.php`

## 9. Files to Edit

- `app/Providers/AppServiceProvider.php`
- operation request/result contracts from Pack 0009
- target hierarchy contracts from Pack 0010
- project Pack/Run/Review indexes required by execution reporting

## 10. Files to Read / Reference

Project profile; Decisions 0005/0008/0009/0014; data/status/error/security/testing rules; files in sections 8–9; actual accepted predecessor source.

## 11. Configuration / Settings Requirements

Versioned config may define non-sensitive prerequisite TTL and maximum submitted value size. No `.env`, Admin UI, generic secret store, encryption key, or external integration. Queue config remains out of scope. Tests use isolated DB.

## 12. Do Not Change

Profile `extra` into a credential store, Core, target secret providers, queue/jobs, Web/API/MCP, auth/permissions, actual DK accounts, dependencies, `.env`, production data.

## 13. Clarification Questions Before Implementation

Normalization must approve exact columns/types, retention, actor-reference semantics, CLI-local operator context, TTL defaults, allowed non-secret input types, and synchronous literal-secret behavior. Default is to reject persisted literal secrets.

Define immutable requirement/input/approval result data and exact additional DTO paths during normalization. Client JSON schemas and prompt behavior remain Pack 0015 scope.

## 14. Multilingual / Translation Requirements

No UI/RTL change. Requirement keys/codes are language-neutral. Human prompt text may be owner-supplied metadata and must be safe; Persian CLI wording is Pack 0015 scope.

## 15. UI / Admin UI Requirements

No UI/Admin UI. Services must expose enough metadata for future clients.

## 16. Operator Documentation Requirements

Pack 0015 must document discover/submit/approve/expire/cancel, interactive secret handling limits, and CI structured input, including Prompts versus non-interactive usage under Decision 0014. This Pack records the required guide update; no interactive UI is implemented here.

## 17. Implementation Rules

- Code-owned schemas validate before persistence.
- Discovery/submission/approval handlers return safe immutable DTOs for requirement keys/types/constraints, missing inputs, state and approval facts. Services contain no JSON renderer, translated CLI guidance or Prompts calls. Client prompt feedback cannot bypass schema/state checks or substitute for persisted approval.
- Persist only allowlisted non-secret values or App-owned secret references.
- Store approval actor reference, scope, schema version, timestamp, and expiry; never treat it as authorization.
- Use transactions and optimistic/state checks for transitions.
- Input changes invalidate stale approval when declared fields affect approved scope.

## 18. Architecture Constraints

Follow Decision 0009's exact prerequisite states. Client adapters cannot bypass service validation or write models directly.

## 19. Validation Rules

Prove legal/illegal transitions, schema version mismatch, required/missing input, expiry/cancel, approval invalidation, duplicate submission idempotency, actor trace, and literal-secret rejection/redaction. Assert typed requirement/state/approval data directly and safe omission without a terminal or JSON service return; Pack 0015 verifies prompt/structured-input equivalence.

## 20. Security Rules

No raw password/token/cookie/session/Authorization value in argv, models, logs, errors, reports, fixtures, or snapshots. Values marked secret accept references only for persisted/async use. Size/type/key allowlists are mandatory.

## 21. Error Handling / Logging / Traceability Requirements

Stable codes must cover input_required, approval_required, schema_changed, input_invalid, secret_literal_forbidden, approval_stale, request_expired, invalid_transition, and conflict. Logs record operation request ID, keys (not values), state, actor reference, and correlation ID. Security failures are safe and auditable.

## 22. Data Model / Migration / Relationship Requirements

Three tables in section 8. Operation request owns many inputs/approvals. Foreign keys and on-delete/on-update actions, unique `(request_id,key,schema_version)` constraints, state indexes, expiry index, JSON allowlist, retention, and rollback/data-loss behavior must be fully specified at normalization. Operational history must not cascade-delete by default.

## 23. Commenting Requirements

Document transition, approval invalidation, and sensitive-value invariants.

## 24. Testing Requirements

Feature/database tests prove schema, relationships, transition concurrency, idempotency, expiry, approval facts, safe result shapes, logs, and absence of sentinels across DB/log/error output. No real credential or target I/O.

## 25. Acceptance Checklist

- [ ] declared requirements are discoverable through operations;
- [ ] non-secret input and approval survive process exit;
- [ ] raw secrets cannot persist or leak;
- [ ] exact state/expiry/idempotency behavior is proven;
- [ ] services are client-neutral.

## 26. Tests to Add

Exact tests in section 8 plus migration/relationship and log-capture cases defined during normalization.

## 27. Tests to Run

Exact PHPUnit files, isolated migrations/rollback review, existing operation compatibility suite, and `git diff --check`. No external target.

## 28. Expected Output

Persisted prerequisite workflow, typed operations, migrations/models/factories/tests, Run Report, and guide follow-up.

## 29. Operator Execution Checklist

Before: approve schema/retention/actor and secret-reference policy. After: demonstrate waiting, submit, approve, expiry/cancel, and sentinel omission.

## 30. Agent Final Report

Report schema/transitions, sensitive-data tests, exact commands, rollback review, files, and deferred client UI.

## 31. Review Checklist

Review state correctness, authorization distinction, secret handling, actor trace, retention, idempotency, and DB integrity.

## 32. Rollback / Safety Notes

Stop active test operations before down migrations. Preserve/report history loss; do not run destructive production DB commands.

## 33. Stop Conditions

Stop for raw-secret persistence, unresolved schema/retention, missing migration safety, authorization ambiguity, queue dependency, production DB, or unaccepted predecessors.

## 34. Open Questions

Exact actor provider and whether synchronous in-memory literal secrets are supported must be resolved before approval.
