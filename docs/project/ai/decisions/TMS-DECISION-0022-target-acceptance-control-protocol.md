# TMS-DECISION-0022 — Versioned target Acceptance Control protocol

Status: Accepted and implemented by Pack 0021 — strict transport default extended only by accepted Decision 0023's explicit local-loopback HTTP option

Scope: TMS project / cross-owner Acceptance target-control boundary

Source: Human Operator approval on 2026-10-12 after review of the proposed reusable architecture

Date: 2026-10-12

Review At: every protocol-major-version or transport-boundary proposal

Blocking: Yes for DK Pack 0004 target-control integration

Closure Condition: accepted Project Pack 0021 Run plus one accepted target conformance implementation; local-loopback use additionally requires accepted Pack 0023

Next Review At: before Project Pack 0021 execution approval

## Type

4. Project / cross-owner architecture Decision
6. Change Request trigger

## Decision

TMS will control synthetic target state only through a target-neutral port and a versioned Acceptance Control protocol. TMS Core and shared orchestration must not know a target command, database schema, model, row identifier, component service, or cleanup query.

The protocol identity is `acceptance-control.v1`. Its minimum HTTP/JSON surface is:

- `GET /_acceptance/v1/capabilities`;
- `PUT /_acceptance/v1/leases/{lifecycleId}` for idempotent acquisition/provisioning;
- `GET /_acceptance/v1/leases/{lifecycleId}` for bounded status and recovery;
- `DELETE /_acceptance/v1/leases/{lifecycleId}` for idempotent owner-verified cleanup.

The request identifies only protocol version, target App, component key, stable recipe key, fixed instant, and lifecycle identity. The response exposes only capability/version data, opaque resource references, safe bounded state, and stable errors. Target commands, arguments, database IDs, raw fixtures, selectors, secrets, exception messages, and implementation classes never cross this boundary.

TMS owns the generic client port, HTTP adapter, protocol DTOs, conformance tests, and an `app-secret` handle-resolution port. Each TMS target module owns mapping from its accepted scenario/fixture identities to generic component/recipe keys. Each target system owns the server, lease store, component-driver registry, recipes, native command mapping, clock application, cleanup, and baseline proof.

An `app-secret://<app>/<opaque-handle>` value is a reference, not a secret. A constructor-injected system adapter resolves it just in time for one outbound control request. The resolved value is confined to memory, is not serializable, is never placed in DTO output, logs, exceptions, reports, Test/Step persistence, command arguments visible to the user, or repository configuration, and is released immediately after the request. The concrete secret provider is a deployment binding such as an OS credential store or managed secret service; TMS does not persist the value.

`PUT` and `DELETE` are retry-safe only for the same authenticated `lifecycleId`, component, recipe, and request digest. A mismatched replay fails closed. The target is authoritative for clock, fixture ownership, cleanup order, baseline comparison, lease expiry, and recovery.

The Browser path remains independent:

```text
TMS target-resource lifecycle -> target Acceptance Control protocol
TMS Browser runtime          -> target Admin UI
```

The control response may provide an opaque, signed lease-context reference for the Browser runtime; it must not provide a usable credential or raw target identifier.

## Reason

This boundary lets TMS reuse one lifecycle and transport contract across DK components and future systems while keeping target-specific commands, data models, configuration, and cleanup within their owner. It also preserves current `TargetResourceCoordinator` guarantees and prevents TMS from becoming coupled to Notification Delivery or Laravel internals.

## Impact

### Configuration / Settings

- Project Pack 0021 may add protocol/client configuration keys and timeout limits, but no secret value or target URL may be committed.
- A target module may carry an endpoint reference and opaque secret handle through its approved prerequisite/profile boundary.
- The concrete secret provider and target instance remain operator/deployment responsibilities.

### Error Handling / Logging / Traceability

```text
Error Handling Impact: Yes
API Error Contract Impact: Yes — acceptance-control.v1 only
UI / Admin UI Error Impact: No
Error Code Impact: Yes — protocol-scoped stable codes are required
Exception Handling Impact: Yes — transport/target exceptions are normalized and raw messages omitted
External Error Normalization Impact: Yes — HTTP/problem responses map to typed target-control failures
Retry / Failure Classification Impact: Yes — PUT/GET/DELETE retry semantics are explicit
Status Model Impact: No product status change; lease status is control-plane-only
Logging Impact: Yes — safe lifecycle/component/recipe/protocol context only
Traceability Impact: Yes — lifecycleId is the end-to-end control correlation key
Sensitive Data Impact: Yes — secret values and raw references are omitted
Testing Impact: Yes
Documentation Impact: Yes
```

Protocol errors use an RFC 9457-compatible safe envelope and stable codes including `target_control_unavailable`, `target_control_unauthorized`, `target_control_capability_mismatch`, `target_control_lease_conflict`, `target_control_recipe_rejected`, `target_control_cleanup_failed`, and `target_control_protocol_invalid`. Product/API error codes are unchanged.

### Data / Migration

TMS adds no database table for target leases or secrets. Target lease persistence is target-owned.

### UI / Translation

No TMS UI, Admin UI, translation, or public API behavior changes through this Decision.

## Files to Update

- `docs/project/ai/decisions/TMS-DECISION-0022-target-acceptance-control-protocol.md`
- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-CONTROL-PROTOCOL-0011.md`
- `docs/project/ai/changes/CHANGES-INDEX.md`
- `docs/project/ai/packs/AI-PACK-TMS-TARGET-CONTROL-PROTOCOL-0021.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- affected DK owner Decision, Change Request, Pack, and indexes

## Required change request

`CHANGE-REQUEST-TMS-TARGET-CONTROL-PROTOCOL-0011`

## future-work

- Project Pack 0021 / Run 001 is accepted and committed; preserve its target-neutral contract.
- Accept Project Pack 0023 Run before any owner enables the exact local-loopback HTTP option.
- Implement and accept the DK target's `acceptance-control.v1` server and ND component driver in their own repository/owner scopes.
- Bind DK Pack 0004 to those accepted capabilities before Browser execution.
- Propose `v2` rather than silently changing `v1` when an incompatible requirement appears.

## Review / Resolution

```text
Reviewed In: operator discussion of reusable target control, app-secret resolution, native ND fixture command reuse, and cross-system portability
Review Result: Ports-and-Adapters, versioned HTTP/JSON protocol, target-owned lease/clock/driver registry, and just-in-time secret-handle resolution approved and implemented by accepted Pack 0021; Decision 0023 adds the bounded opt-in loopback transport
Resolution: accepted
Next Review At: Pack 0023 acceptance or any protocol/transport-boundary proposal
Resolution Notes: Pack 0021 / Run 001 is accepted and committed. Concrete target access, owner-local binding, Browser execution, and target mutation remain separately gated.
```

## Notes

The protocol standardizes orchestration semantics, not target internals. A non-Laravel target may implement the same contract with different persistence, command, and clock adapters.

Decision 0023 does not replace this Decision. It preserves HTTPS as the default and permits HTTP only through a default-off constructor option for exact loopback origins.
