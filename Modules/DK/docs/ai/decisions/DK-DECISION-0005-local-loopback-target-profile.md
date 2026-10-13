# DK-DECISION-0005 — Local-loopback Acceptance Control target profile

Status: Accepted — operator approved 2026-10-13; target Binding Pack and TMS Pack 0023 / Run 001 accepted; DK source/target execution remain gated

Scope: DK owner / stage 14 / Pack 0004

Source: Human Operator decision and the accepted target-project local-loopback profile

Date: 2026-10-13

Review At: TMS project shared-client local-loopback Pack and DK Pack 0004 execution gates

Blocking: Yes — Pack 0004 cannot execute against HTTP until the shared client supports the exact direct-loopback exception

Closure Condition: accepted Pack 0004 Run or a superseding DK Decision

Next Review At: before DK Pack 0004 source execution

## Type

3. Pack-local binding Decision
6. Change Request trigger

## Decision

For the operator's development workstation only, DK Pack 0004 may consume the target project's `local-loopback` Acceptance Control profile:

1. TMS backend and DK run on the same operator-controlled workstation.
2. The DK target contains no real data and performs no real workload.
3. The target origin is exactly `http://127.0.0.1:8000`; `http://localhost:8000` is equivalent only when it resolves directly to loopback.
4. Pack 0004 continues to use the shared `TargetControlClient`; it must not implement or call a second client.
5. The DK prerequisite remains an opaque `app-secret://dk/...` handle for contract compatibility. The DK-local resolver maps only the exact approved local handle to `local-acceptance-control-v1`.
6. The resolved value is a public marker, not a secret. Existing short-lived/non-serializable handling may remain because it safely satisfies the shared client contract.
7. The shared client must gain a project-owned exception that permits HTTP only for the explicit local profile and a literal loopback host. Its HTTPS behavior remains unchanged otherwise.
8. Target-issued Browser lease-context values, sessions and resource references remain sensitive and ephemeral.
9. Existing recipe keys, observation contract, lifecycle ownership, clock behavior, cleanup, baseline equality, stable errors and no-Provider-I/O constraints remain unchanged.

The accepted strict isolated-target policy remains authoritative for remote, LAN, shared, staging, production, real-data or real-workload targets.

## Reason

The target project has approved a proportionate same-workstation profile. Recording it in DK avoids an unnecessary TLS and secret-provider deployment while preserving the reusable protocol and its ownership boundaries. Keeping the exception inside the shared client prevents target-specific transport logic from leaking into Pack 0004.

## Impact

### Configuration / Settings Impact

Pack 0004 may add versioned non-sensitive DK settings for profile, endpoint and opaque local handle. No `.env`, real credential provider or Admin Setting is needed.

### Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes — existing target_control_protocol_invalid/unauthorized/unavailable boundaries are reused
API Error Contract Impact: No protocol field or stable code change
UI / Admin UI Error Impact: No
Error Code Impact: No new code
Exception Handling Impact: No new public exception contract
External Error Normalization Impact: No semantic change
Retry / Failure Classification Impact: No
Status Model Impact: No
Logging Impact: No endpoint/marker/session/header logging
Traceability Impact: No identifier change
Sensitive Data Impact: Yes — public marker is non-sensitive; lease/session values remain omitted
Testing Impact: Yes — exact loopback acceptance and non-loopback rejection are required
Documentation Impact: Yes
```

### Data / Migration / UI Impact

No TMS schema, migration, UI, translation or product-data impact.

## Files to Update

- this Decision and `Modules/DK/docs/ai/decisions/DECISIONS-INDEX.md`;
- `Modules/DK/AGENTS.md` and `Modules/DK/docs/ai/GOVERNANCE-PROFILE.md`;
- `Modules/DK/docs/ai/changes/CHANGE-REQUEST-DK-TARGET-CONTROL-LOCAL-LOOPBACK-0003.md`;
- `Modules/DK/docs/ai/changes/CHANGE-REQUEST-DK-TARGET-CONTROL-BINDING-0001.md`;
- Pack 0004 and DK lifecycle indexes.

## Required change request

`CHANGE-REQUEST-DK-TARGET-CONTROL-LOCAL-LOOPBACK-0003`

## future-work

- create, execute, validate, and accept TMS Project Pack 0023, which adds the exact local-loopback HTTP exception to `HttpTargetControlClient` without weakening strict targets — complete 2026-10-13;
- execute and accept target-project Binding Pack 0002 — complete 2026-10-13;
- execute DK Pack 0004 only after both prerequisites and its separate gate;
- perform live Browser/target runs only at their later explicit gate.

## Review / Resolution

```text
Reviewed In: operator local DK/TMS deployment decision on 2026-10-13
Review Result: direct-loopback HTTP and public-marker profile accepted for the same workstation
Resolution: accepted / Pack 0004 normalized / source prerequisite pending
Next Review At: TMS shared-client and DK Pack 0004 execution gates
Resolution Notes: No source, live target, Browser, data, credential or network action is authorized by this Decision.
```

## Notes

This Decision narrows the local development profile; it does not make a LAN URL, container bridge, remote VM or staging host equivalent to loopback.
