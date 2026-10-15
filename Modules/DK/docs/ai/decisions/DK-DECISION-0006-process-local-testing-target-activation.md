# DK-DECISION-0006 — Process-local testing target activation

Status: Accepted and implemented — live validation passed; operator accepted and authorized scoped commit 2026-10-15

Scope: DK owner / Pack 0004 local synthetic execution

Source: failed Batch 3 (`unsafe_target`), accepted Decisions 0001 and 0005, operator authorization that the current DK database is test-only

Date: 2026-10-15

## Decision

1. DK may expose one non-secret process environment selector, `DK_ACCEPTANCE_TARGET_PROFILE`.
2. The only activating value is the exact string `local-testing`. It maps to `enabled = true` and `environment = testing` for the existing fixed local-loopback control profile.
3. An absent, empty, or different value maps to `enabled = false` and `environment = unknown`. It must not select staging, production, a URL, a credential, or arbitrary target settings.
4. The operator sets the value only for the command process or current shell and removes it after the run. TMS does not create or edit `.env`.
5. The fixed loopback endpoint and opaque secret handle remain code-owned. Existing target-control, synthetic-data, cleanup, no-real-SMS, and exact Pack 0004 boundaries remain binding.
6. Config caching is not part of this workflow. If configuration is cached later, the operator must rebuild it deliberately with the intended profile and must not treat a shell change as immediately effective.

This Decision narrowly supersedes Decision 0001 clauses that prohibited every environment-backed/operator-supplied runtime value. All other Decision 0001 safety requirements remain active.

## Reason

The accepted local-loopback adapter and live test corpus exist, but the versioned default intentionally resolves to `unsafe_target`. Requiring source edits or arbitrary config mutation for every test run is error-prone. A single allow-listed profile makes explicit activation short, reversible, observable, and fail-closed.

## Impact

- Configuration: one non-secret optional process environment selector; no `.env` edit.
- Data: no migration or production/customer data authorization.
- Error contract: no new error code.
- UI: CLI/operator documentation only.
- Rollback: remove the config mapping and this activation workflow; default behavior remains fail-closed.

## Implementation owner

`CHANGE-REQUEST-DK-PROCESS-LOCAL-TARGET-ACTIVATION-0006` and `REMEDIATION-PACK-DK-PROCESS-LOCAL-TARGET-ACTIVATION-0003` own the DK source/test/documentation changes. Project Batch/CLI error UX is separately owned by project Change 0019 and Remediation 0013.

## Acceptance conditions

- exact `local-testing` activates only the existing testing loopback profile;
- missing and invalid values remain disabled/unknown;
- no `.env`, URL, secret, staging, or production selection is introduced;
- the exact Operations Health Batch can execute and clean up through the accepted target boundary;
- no commit occurs without separate operator authorization.
