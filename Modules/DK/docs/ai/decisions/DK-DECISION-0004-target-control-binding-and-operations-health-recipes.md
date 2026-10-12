# DK-DECISION-0004 — Target-control binding and Operations Health recipes

Status: Accepted — operator approved the reusable adapter/recipe design and its exact documentation commit on 2026-10-12; implementation, target access, Browser execution, acceptance, and implementation commit remain separately gated

Scope: DK owner / stage 14 / Pack 0004

Source: Human Operator approval after review of the standard cross-system target-control architecture

Date: 2026-10-12

Review At: Pack 0004 final normalization after all three enabling Packs are accepted

Blocking: Yes — Pack 0004 execution requires accepted TMS Project Pack 0021, accepted target-project Acceptance Control Agent Pack, and accepted target ND Driver Pack

Closure Condition: accepted Pack 0004 Run or a superseding accepted DK Decision

Next Review At: before Pack 0004 execution approval

## Type

3. Pack-local binding Decision
6. Change Request trigger

## Decision

DK Pack 0004 will consume the project-owned `acceptance-control.v1` client through one DK-owned adapter. TMS Core, the DK scenario, and `OperationsHealthFixture` will not call `notifications:seed-inspection-data`, target HTTP routes, target models, or target database queries directly.

The binding is:

```text
TargetResourceCoordinator
  -> OperationsHealthFixture
  -> DKSyntheticTargetControl (narrow DK port)
  -> DKTargetControlAdapter
  -> generic TargetControlClient + SecretHandleResolver
  -> acceptance-control.v1
```

The DK adapter maps the five accepted component cases to these stable recipe keys and nothing else:

1. `operations-health.no-evidence.v1`
2. `operations-health.stale.v1`
3. `operations-health.fresh.v1`
4. `operations-health.config-invalid.v1`
5. `operations-health.empty-providers.v1`

Recipe semantics are target-owned. TMS knows only the key and expected bounded observation:

| Recipe | Required bounded outcome |
|---|---|
| `no-evidence` | one enabled synthetic Provider; `freshness=no_evidence`; no Health log; no Provider I/O |
| `stale` | one enabled synthetic Provider; checked evidence exists; expiry is `<=` frozen target time; `effective_status=unknown`; `freshness=stale`; stale reason |
| `fresh` | one enabled synthetic Provider; delivery-attempt evidence exists; expiry is `>` frozen target time; `freshness=fresh`; deterministic safe metrics |
| `config-invalid` | isolated request-scoped invalid Health configuration; `config_valid=false`; no persistent setting change |
| `empty-providers` | zero Providers in an exclusive disposable baseline; no deletion of unrelated Providers is permitted |

`empty-providers` is allowed only when the pre-lease baseline proves zero Providers. Otherwise provisioning fails closed; it never clears a shared database. Configuration and clock overrides are lease/request scoped and must be restored in `finally`.

The exact observation key is `notification-delivery.operations-health.v1`. It contains at most these sixteen safe facts: `config_valid`, `provider_count`, `administrative_status`, `computed_status`, `effective_status`, `freshness`, `reason_code`, `eligible_sample_count`, `failure_rate`, `latency_ms`, `telemetry_only`, `remote_probe_supported`, `last_evidence_source`, `last_evidence_outcome`, `clock_state`, and `lease_state`. Provider-specific facts are `null` for `empty-providers`. Values must satisfy `BrowserTargetObservation`; raw IDs, names, timestamps, text, DOM, selectors, and references are forbidden.

The DK Browser runtime uses the target-issued opaque lease-context reference for the Admin UI request and uses the control adapter for the semantic observation. Human-visible assertions use source-owned accessible roles/labels and translated text; they do not add or depend on test-only DOM selectors.

The `app-secret` prerequisite remains an opaque handle. `DKTargetControlAdapter` passes it to the injected shared `SecretHandleResolver` only immediately before an authenticated request. Neither the adapter nor the resolver registry retains the returned value. The target endpoint, concrete secret-provider binding, and usable value remain outside scenario data and repository documentation.

## Reason

This mapping resolves Pack 0004's target-control and five-recipe ambiguity without coupling TMS to ND. It also makes the same TMS client reusable for other DK components and the same protocol reusable for other systems.

## Impact

### Configuration / Settings Impact

- DK module configuration may identify a non-secret endpoint reference and the required opaque handle name only.
- No secret value, target URL value, or command argument is committed or reported.
- The target owns request-scoped configuration override and clock behavior.

### Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: No product API; acceptance-control.v1 only
UI / Admin UI Error Impact: No
Error Code Impact: No new TMS coordinator code; target-control causes remain safe metadata
Exception Handling Impact: Yes — adapter failures normalize through existing fixture/cleanup codes
External Error Normalization Impact: Yes
Retry / Failure Classification Impact: Yes — inherited from Decision 0022
Status Model Impact: No product status change
Logging Impact: No raw request/response/secret/reference/DOM logging
Traceability Impact: Yes — lifecycleId and recipe key correlate control and Browser paths
Sensitive Data Impact: Yes — omission required
Testing Impact: Yes
Documentation Impact: Yes
```

Provisioning failures remain `fixture_setup_failed`; cleanup/restore/baseline uncertainty remains `cleanup_failed`. The safe target-control cause code may be attached, but raw remote details may not.

### Data / Migration Impact

No TMS schema change. Target lease/data changes belong to the target-project and ND Packs. Pack 0004 creates no product fixture recipe.

### UI / Translation Impact

No target UI or translation source change is owned by Pack 0004.

## Files to Update

- this Decision and `Modules/DK/docs/ai/decisions/DECISIONS-INDEX.md`
- `Modules/DK/docs/ai/changes/CHANGE-REQUEST-DK-TARGET-CONTROL-BINDING-0001.md`
- `Modules/DK/docs/ai/changes/CHANGES-INDEX.md`
- Pack 0004 and `Modules/DK/docs/ai/packs/PACKS-INDEX.md`

## Required change request

`CHANGE-REQUEST-DK-TARGET-CONTROL-BINDING-0001`

## future-work

- accept and execute the three enabling Packs in dependency order;
- normalize Pack 0004's final exact source allowlist against their accepted implementations;
- verify the deployment secret-provider binding and isolated target manifest without exposing values;
- obtain separate Pack 0004 execution, target-access, Browser, acceptance, and implementation-commit approvals.

## Review / Resolution

```text
Reviewed In: operator review of command reuse, target/TMS responsibility split, app-secret handling, fixture recipes, and frozen clock
Review Result: generic protocol + target Agent + component Driver + DK adapter binding approved
Resolution: accepted
Next Review At: Pack 0004 final normalization
Resolution Notes: The five stable recipe keys and bounded observation schema are now fixed. Target-side implementations remain prerequisites and no execution is authorized.
```

## Notes

Target-side use of `notifications:seed-inspection-data` is an ND implementation fact and must not appear in shared TMS contracts or scenario output.
