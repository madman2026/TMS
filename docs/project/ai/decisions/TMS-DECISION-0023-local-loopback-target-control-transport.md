# TMS-DECISION-0023 — Opt-in local-loopback HTTP target-control transport

Status: Accepted and implemented — operator accepted Project Pack 0023 / Run 001 and authorized the implementation commit on 2026-10-13

Scope: TMS project / shared Acceptance Control HTTP client

Source: Human Operator decisions on 2026-10-13 and accepted target-project local-loopback binding Run 001

Date: 2026-10-13

Review At: Project Pack 0023 acceptance and every proposal to widen the host, scheme, or activation boundary

Blocking: Yes for DK Pack 0004 source execution

Closure Condition: met by accepted Project Pack 0023 / Run 001

## Type

4. Project / cross-owner architecture Decision
6. Change Request trigger

## Decision

Decision 0022 remains strict by default: the shared HTTP client accepts HTTPS origins without an opt-in. A consuming owner may explicitly construct the client with `allowLocalLoopbackHttp=true`; only then may the client accept an `http` origin whose parsed host is exactly `127.0.0.1`, `localhost`, or `[::1]`.

The exception does not accept LAN addresses, wildcard listeners, alternative numeric encodings, host suffixes, credentials, query strings, fragments, or non-root paths. It does not resolve arbitrary hostnames, infer a local profile from process environment, read `.env`, disable redirect blocking, disable response bounds, alter retry behavior, or weaken secret-handle validation. HTTPS behavior remains unchanged.

Activation belongs to an owner-local Pack. The shared project Pack adds only the explicit constructor capability and tests; it does not configure DK, contact a target, start a listener, or authorize Browser execution.

## Reason

DK and TMS run on the operator's same workstation with synthetic/no-real data. The accepted target-project binding exposes the control Agent only through direct loopback HTTP. A narrow opt-in preserves Decision 0022's reusable strict default while allowing the approved local composition without creating a second client.

## Impact

### Configuration / Settings

No project configuration or environment value is added. The constructor option defaults to `false`; DK Pack 0004 owns any later opt-in composition.

### Error Handling / Logging / Traceability

Rejected origins continue to use `target_control_protocol_invalid`. No new error code, status, retry classification, log field, trace field, or sensitive value is introduced.

### Data / Migration

No database, migration, persistence, target resource, or external-system mutation.

### UI / Translation

No UI, Admin UI, translation, or public API impact.

## Files to Update

- `app/Acceptance/Targets/Control/Http/HttpTargetControlClient.php`
- `tests/Unit/Acceptance/Targets/Control/HttpTargetControlClientTest.php`
- Decision 0022, Change Requests 0011–0012, Pack 0023, Run 001, and their project indexes
- DK Pack 0004 and its owner-local Pack index only to synchronize the prerequisite state

## Required Change Request

`CHANGE-REQUEST-TMS-TARGET-CONTROL-LOCAL-LOOPBACK-0012`

## Future Work

- DK Pack 0004 may bind the option only for its accepted `local-loopback` profile after Pack 0023 Run acceptance.
- Live target, listener, migration, Browser, and cleanup gates remain separate.

## Review / Resolution

```text
Reviewed In: operator discussion of same-workstation DK/TMS execution and Pack 0004 normalization
Review Result: exact opt-in loopback HTTP exception approved
Resolution: accepted
Next Review At: Pack 0023 acceptance or any boundary-widening proposal
Resolution Notes: implementation and tests are authorized; commit, target access, and DK Pack 0004 execution are not implied.
```

## Notes

`localhost` is a permitted origin spelling only inside the explicit option. Listener and name-resolution verification remain deployment gates; the client never treats another hostname or resolved address as loopback.
