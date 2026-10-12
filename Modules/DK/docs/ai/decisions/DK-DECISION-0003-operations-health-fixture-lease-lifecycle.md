# DK-DECISION-0003 — Operations Health fixture lease lifecycle

Status: Accepted — operator approved the Pack-local fixture/clock lifecycle and its exact documentation commit on 2026-10-12; no implementation, target access, Browser execution, Pack acceptance, or implementation commit authority is granted

Scope: DK stage 14 / `AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004`

Source: Operator answer on 2026-10-12

Date: 2026-10-12

Review At: Pack 0004 final normalization before execution approval

Blocking: Partially resolved — lifecycle shape is fixed here; the former recipe/binding ambiguity was resolved by accepted `DK-DECISION-0004`, while enabling Pack implementations and execution gates remain blocking

Closure Condition: completed by an accepted Pack 0004 Run or superseded by a separately accepted DK decision

Next Review At: before Pack 0004 execution approval

## Type

3. Pack-Local Decision

## Decision

Pack 0004 will provision its two synthetic fixtures through one run-owned fixture lease tied to the exact `TargetContext::lifecycleId`. The target clock is controlled inside the isolated synthetic target, not by the TMS process clock.

Provisioning order is:

1. acquire an exclusive run-owned lease and capture a bounded baseline digest/count without raw target data;
2. freeze the target clock at the case-defined fixed instant;
3. provision `fixture.provider.enabled`;
4. provision `fixture.health.evidence` from the provider fixture;
5. return an opaque `ResourceReference` for every acquired resource, including partial acquisition on failure.

Cleanup always runs through the accepted coordinator `finally` path and reverses fixture acquisition:

1. remove the run-owned health-evidence resource;
2. remove the run-owned provider resource;
3. restore the target clock;
4. verify the bounded post-cleanup baseline equals the captured baseline;
5. close the lease and report every expected reference as cleaned or remaining.

`OperationsHealthFixture` owns this orchestration and depends through constructor injection on a narrow DK-owned synthetic target-control port. Unit tests use a directly constructed fake port. The concrete adapter is wired only at the DK composition root; no service locator, static mutable state, broad database query, product UI action, Provider I/O, external network, queue, worker, scheduler, or retained evidence is allowed.

Clock control must be scoped to the run/session where supported. If the target clock is global, Pack 0004 may run only against an exclusive isolated target instance with parallel execution disabled. Cleanup is idempotent only after lease ownership is verified; an unknown or mismatched lease must fail closed rather than delete anything.

At acceptance of this Decision, the exact synthetic recipes and concrete target-control adapter/path remained unresolved and could not be inferred from variant names. Accepted `DK-DECISION-0004` subsequently fixed their stable recipe keys, target-control binding, secret-handle boundary, and observation schema without changing this lifecycle.

## Reason

The existing `TargetResourceCoordinator` already guarantees cleanup in `finally`, preserves partial provisioning references, validates that cleanup accounts for every reference, and exposes stable fixture/cleanup failures. A run-owned lease makes target clock restoration, reverse cleanup, baseline verification, and ownership checks deterministic without leaking target identifiers or raw data.

## Impact

### Configuration / Settings Impact

- No config, environment variable, Admin Setting, secret value, schema, or dependency change is approved.
- The concrete source binding remains blocked on the enabling Packs and final Pack-0004 normalization; its architecture is resolved by `DK-DECISION-0004`.
- If the binding changes operator setup, the Persian Acceptance CLI guide must be updated before Pack acceptance.

### Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: No
UI / Admin UI Error Impact: No
Error Code Impact: No new code; reuse fixture_setup_failed and cleanup_failed
Exception Handling Impact: Yes — adapter exceptions fail closed through existing lifecycle normalization
External Error Normalization Impact: No external integration
Retry / Failure Classification Impact: No change to accepted classifications
Status Model Impact: No
Logging Impact: No new log event; raw references, baseline data, and target values omitted
Traceability Impact: Yes — lifecycleId owns the lease and every fixture reference
Sensitive Data Impact: Yes — raw target IDs/data remain private and unpersisted
Testing Impact: Yes
Documentation Impact: Yes
```

Provision failure returns `fixture_setup_failed` with all already-acquired references so cleanup can run. Incomplete cleanup, clock restoration failure, baseline mismatch, timeout, or ownership mismatch returns `cleanup_failed`. No new error code is authorized.

### Data / Migration Impact

No TMS or target schema change is approved. Any lease/control capability must already exist in the approved isolated test boundary or be routed through a separately normalized scope. Run-owned synthetic resources and baseline material must be removed before successful cleanup is reported.

### Multilingual / UI Impact

No UI, translation, visible text, permission, route, or product behavior change is approved.

## Files to Update

- `Modules/DK/docs/ai/decisions/DK-DECISION-0003-operations-health-fixture-lease-lifecycle.md`
- `Modules/DK/docs/ai/decisions/DECISIONS-INDEX.md`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004.md`
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`

## Required change request

No Change Request is required for this Pack-local lifecycle decision. If the concrete binding requires shared/Core changes, target schema, a new API, configuration-surface changes, or capabilities outside Pack 0004, stop and route them through the applicable Change/Remediation process.

## future-work

- exact five-case recipe and binding architecture was completed by `DK-DECISION-0004`;
- implement and accept the shared client, target Agent, and N-D driver Packs, then verify their final concrete paths;
- finalize sections 8–9 of Pack 0004;
- obtain separate Pack acceptance, execution, target-access, Browser-run, and implementation-commit approvals.

## Review / Resolution

```text
Reviewed In: operator discussion of Pack 0004 fixture provisioning, cleanup, and frozen clock
Review Result: operator approved the proposed lease-based lifecycle on 2026-10-12
Resolution: accepted
Next Review At: Pack 0004 final normalization before execution approval
Resolution Notes: lifecycle only. `DK-DECISION-0004` later resolved the case recipes and binding architecture; enabling implementation and execution remain blocked. No source implementation or execution is authorized.
```

## Notes

TMS test-time helpers may freeze the TMS process clock in unit tests, but they do not constitute target-clock control. Browser acceptance may proceed only after the target-side clock boundary is proven.
