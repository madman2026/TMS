# DK-DECISION-0001 — Isolated target foundation boundary

Status: Accepted — operator accepted 2026-10-10

Scope: DK owner / stages 11–15

Source: Project Decisions 0005, 0007, 0009, 0012, and 0015; operator request and approval for the post-0016 DK target foundation

Date: 2026-10-10

Review At: operator approval completed 2026-10-10

Blocking: prerequisite satisfied for DK Pack 0001; this Decision remains binding for stages 11–15

Closure Condition: superseded by a separately approved DK owner decision.

Next Review At: before any proposal to enable target I/O or change this boundary

## Type

4. Phase/Release Decision

## Decision

Accepted by the operator:

1. DK target work remains limited to isolated testing or staging environments and synthetic data. Production, unknown environments, real customer data, and real target execution are rejected.
2. DK Pack 0001 creates a fail-closed, no-I/O target foundation only. It may register DK-owned implementations of the five accepted target resource ports, but it must not connect to DK, resolve a real credential, authenticate an account, provision a real fixture, launch a browser, call an API, or send a notification.
3. The versioned DK configuration defaults to `enabled = false` and `environment = unknown`. No environment variable, secret, URL, selector, account, token, payload, database-backed setting, or Admin Setting is introduced by the foundation Pack.
4. With the default profile, readiness returns the accepted `unsafe_target` result. A safe but disabled profile returns `target_not_ready`. A safe and explicitly enabled profile may pass readiness, but the foundation account adapter returns `resource_unavailable`, so execution still stops before fixture setup, scenario execution, oracle evaluation, or target I/O.
5. Foundation fixture and oracle methods remain rejecting safety sentinels. Cleanup succeeds only when no reference exists and otherwise returns the accepted `cleanup_failed` result. These behaviors must not be presented as real DK integration readiness.
6. The DK service provider is the composition root. It registers one `TargetResourceAdapters` aggregate for App key `dk` through the accepted `TargetResourceRegistry`; no shared project or Core source is changed.
7. Secret references, when introduced by a later accepted Pack, remain opaque `app-secret://dk/...` values inside the accepted prerequisite boundary. Pack 0001 neither creates nor resolves them.
8. Notification Delivery remains a future Component inside DK. Its fake Provider, simulated Callback, worker/time/audit oracles, and explicit no-real-SMS boundary belong to the next owner-local stage and require a separately accepted Pack before implementation.
9. No real SMS, notification, webhook, callback, provider request, target URL, account, credential, selector, browser request, HTTP request, queue worker, or external network operation is authorized by this decision.

This Decision does not authorize implementation by itself. DK Pack 0001 retains a separate execution approval, post-execution acceptance, and commit gate.

## Reason

Project Decision 0012 requires the isolated DK environment, fake ND Provider, simulated Callback, and no-real-SMS boundary to be recorded by the relevant owner before target implementation. The first DK-local Pack must also consume the accepted target-resource extension point without inventing target behavior or weakening the testing/staging and secret-omission rules.

A fail-closed foundation proves owner-local composition, configuration validation, and lifecycle registration while keeping all real target behavior and ND-specific contracts outside the current scope.

## Impact

### Configuration / Settings Impact

- Accepted DK config keys: `dk.target.enabled` with default `false`, and `dk.target.environment` with default `unknown`.
- No `.env`, `.env.example`, Admin Settings, database settings, secret storage, queue/cache/log configuration, or operator-supplied runtime value is introduced.
- Invalid or unsupported values fail closed; they do not activate target behavior.

### Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes — existing target-resource failures are selected explicitly
API Error Contract Impact: Not applicable
UI / Admin UI Error Impact: Not applicable
Error Code Impact: No new code; reuse unsafe_target, target_not_ready, resource_unavailable, fixture_setup_failed, oracle_failed, and cleanup_failed
Exception Handling Impact: No new public exception contract
External Error Normalization Impact: Not applicable; no external integration exists
Retry / Failure Classification Impact: No; reuse accepted classifications
Status Model Impact: No
Logging Impact: No new event; existing operation-boundary events remain authoritative
Traceability Impact: No new identifier; existing lifecycle_id, operation_id, and correlation_id remain authoritative
Sensitive Data Impact: Yes — real values remain forbidden and absent
Testing Impact: Yes — fail-closed behavior and non-exposure require direct tests
Documentation Impact: Yes — DK Decision and Pack indexes must track this proposal
```

### Multilingual / UI / Data Impact

- No visible text, translation, locale, RTL/LTR, UI, Admin UI, API, database, migration, relationship, snapshot, or operator-guide behavior is introduced.

## Files to Update

- `Modules/DK/docs/ai/decisions/DK-DECISION-0001-isolated-target-foundation-boundary.md`
- `Modules/DK/docs/ai/decisions/DECISIONS-INDEX.md`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-TARGET-FOUNDATION-0001.md`
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`
- `AGENTS.md`
- `docs/project/ai/TMS-AI-PROFILE.md`

If accepted and executed later, DK Pack 0001 and its Run/index records must reference this Decision.

## Required change request

No Change Request is required for this proposal because it does not change an accepted implementation contract. If implementation proves that shared project/Core contracts must change, stop and route that conflict to the project owner before editing shared source.

No Change Request or Remediation Pack is required for the project-governance correction because it synchronizes two stale routing/profile statements with accepted Pack 0016, its accepted Run, the active module index, the DK manifest, and current source. The operator explicitly approved this documentation-only correction on 2026-10-10.

## future-work

- Execute DK Pack 0001 only after a separate operator execution approval.
- After accepted DK Pack 0001, create the separate owner-local Pack for the ND fake Provider, simulated Callback, worker/time/audit oracles, and no-real-SMS contract.
- Introduce any environment-backed target configuration, secret-reference resolver, account authentication, target fixture, or external adapter only through a later accepted owner Pack and Decision when required.

## Review / Resolution

```text
Reviewed In: operator confirmation after initial generation of DK Pack 0001 on 2026-10-10
Review Result: operator accepted the isolated fail-closed boundary and requested resolution of its documented blockers
Resolution: accepted
Next Review At: before any proposal to enable target I/O or change this boundary
Resolution Notes: project owner-activation documentation drift was corrected. This Decision does not authorize source implementation, target access, test execution, Pack acceptance, or commit; Pack execution retains its separate gate.
```

## Notes

The project Pack number `0017` is already reserved for stage 16 second-target proof and scale hardening. The first owner-local DK Pack therefore uses local identifier `0001` and does not renumber or replace project Pack 0017.
