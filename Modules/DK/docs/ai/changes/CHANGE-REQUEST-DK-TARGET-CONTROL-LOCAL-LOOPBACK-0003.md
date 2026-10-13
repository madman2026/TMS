# CHANGE-REQUEST-DK-TARGET-CONTROL-LOCAL-LOOPBACK-0003 — Consume the approved DK local-loopback target profile

## 1. Title

Normalize DK Pack 0004 to consume the target project's approved single-workstation local-loopback Acceptance Control profile.

## 2. Status

Approved by the Human Operator on 2026-10-13; Pack 0004 documentation normalized. Target Binding Pack 0002 and TMS Pack 0023 / Run 001 are accepted; DK execution remains separately gated.

## 3. Related Release / Phase

TMS Acceptance roadmap / DK stage 14 / Pack 0004.

## 4. Decision Source

Human Operator decision on 2026-10-13; target-project `CHANGE-REQUEST-MARTFURY-ACCEPTANCE-CONTROL-LOCAL-LOOPBACK-0003`; target-project `DEC-GLOBAL-0015` local-loopback addendum; and DK Decisions 0001 and 0004.

## 5. Proposed Change

Permit Pack 0004 to target the operator's local DK application at `http://127.0.0.1:8000` through the existing `acceptance-control.v1` client boundary when all of these conditions hold:

- TMS and DK run on the same operator-controlled workstation;
- the target contains no real data and performs no real workload;
- direct traffic remains on loopback;
- the existing opaque DK handle resolves only to the target project's fixed public local marker `local-acceptance-control-v1`;
- the marker is treated as protocol-shape compatibility, not a secret or credential;
- lease ownership, bounded observations, cleanup, baseline equality and Provider-I/O prohibitions remain unchanged.

The accepted shared `HttpTargetControlClient` currently rejects HTTP. A separate TMS project-owned Change/Pack must add the same direct-loopback-only exception before Pack 0004 source execution or live target traffic. Pack 0004 must not bypass the shared client or implement a second HTTP client.

## 6. Reason for Change

The strict HTTPS/secret-provider/isolated-database deployment assumptions exceed the operator's actual single-workstation, synthetic, no-real-data development context. The target project has formally approved a narrower local profile. TMS must record and consume that profile without weakening behavior for remote, shared, staging or production targets.

## 7. Scope of Impact

DK Pack 0004 documentation, DK target profile/configuration plan, DK-local public-marker resolver plan, shared-client prerequisite declaration, decisions and lifecycle indexes. No source is changed by this normalization.

## 8. Affected Files

- this Change Request;
- `Modules/DK/AGENTS.md`;
- `Modules/DK/docs/ai/GOVERNANCE-PROFILE.md`;
- `Modules/DK/docs/ai/decisions/DK-DECISION-0005-local-loopback-target-profile.md`;
- `Modules/DK/docs/ai/decisions/DECISIONS-INDEX.md`;
- `Modules/DK/docs/ai/changes/CHANGE-REQUEST-DK-TARGET-CONTROL-BINDING-0001.md`;
- `Modules/DK/docs/ai/changes/CHANGES-INDEX.md`;
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004.md`;
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`.

## 9. Backward Compatibility

Yes. The local profile is additive and exact. Strict HTTPS and real secret-provider requirements remain authoritative for every non-loopback or non-local target.

## 10. Risk Level

Medium for the bounded local profile; High if HTTP or the public marker is accepted outside direct loopback.

## 11. Migration or Remediation Needed

No DK Remediation Pack is required because Pack 0004 has not executed. A separate TMS project-owned prospective Change/Pack is required to extend the already accepted shared HTTP client; that is a prerequisite, not correction of executed DK work.

## 12. Required Human Approval

Documentation normalization is approved and TMS Pack 0023 / Run 001 is accepted. DK Pack 0004 source execution, target/Browser access, live traffic, implementation commits and push retain separate gates.

## 13. Acceptance Criteria

- Pack 0004 references the target project's accepted local profile and exact non-sensitive loopback endpoint/marker;
- DK router/profile permit only the same exact Pack-governed local exception while retaining all remote/real-target prohibitions;
- TMS keeps the shared `TargetControlClient` and `SecretHandleResolver` boundaries;
- DK-local resolution accepts only the exact local handle and returns only the fixed public marker;
- no real credential, `.env` value or target URL is introduced;
- the existing shared client is not bypassed;
- accepted Pack 0023 / Run 001 satisfies the project-owned direct-loopback HTTP prerequisite;
- non-loopback targets retain HTTPS and secret-provider requirements;
- cleanup, baseline, clock, observation, error and no-Provider-I/O contracts do not change.

## 14. Rollback Plan

Remove the local-loopback normalization and restore Pack 0004's strict deployment prerequisite. No source, data or target state is changed by this documentation record.

## 15. Final Decision

Approved for Pack 0004 documentation normalization. Shared-client Pack 0023 / Run 001 is accepted; source execution now awaits Pack 0004's own execution and target-access gates.

## 16. Required remediation

None for DK. Outcome: `canonical-contract-change` plus `future-pack-normalization`.

## 17. Notes

The local endpoint and marker are intentionally non-sensitive. Sessions and target-issued lease-context credentials remain sensitive and may not enter source, documentation, logs or persisted execution data.
