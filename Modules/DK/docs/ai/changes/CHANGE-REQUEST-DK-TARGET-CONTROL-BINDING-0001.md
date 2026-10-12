# CHANGE-REQUEST-DK-TARGET-CONTROL-BINDING-0001 — Bind Pack 0004 to the reusable target-control protocol

## 1. Title

Replace Pack 0004's unresolved direct target-control concept with the accepted generic protocol and DK-owned adapter mapping.

## 2. Status

Approved planning change — Pack 0004 normalization is authorized; source execution remains blocked on the three enabling Packs and a separate operator gate.

## 3. Related Release / Phase

TMS Acceptance roadmap / DK stage 14 / Pack 0004.

## 4. Decision Source

Accepted `DK-DECISION-0004` and project `TMS-DECISION-0022`.

## 5. Proposed Change

Add a DK adapter over the shared Target Control client, map the five Operations Health cases to stable recipes, resolve `app-secret` handles just in time through the shared port, consume the target's opaque lease context, and use one bounded semantic observation contract.

## 6. Reason for Change

Accepted Decision 0003 fixed lifecycle order but explicitly left recipes and concrete binding unresolved. The approved architecture now resolves them through reusable cross-project boundaries instead of embedding target/ND internals in TMS.

## 7. Scope of Impact

DK owner source/tests and Pack 0004 only. Shared client code belongs to Project Pack 0021; target Agent and ND Driver belong to their own repository owners. No product code, target command, target schema, or secret provider is changed here.

## 8. Affected Files

Current documentation: this Change Request, DK Decision 0004, Pack 0004, and owner indexes. Final source files are limited to Pack 0004 sections 8–9 after prerequisite Pack acceptance.

## 9. Backward Compatibility

Existing DK Packs 0001–0003, 139 scenario identities, 24,668 mappings, and 138 non-executable scenarios remain unchanged. Existing fail-closed target adapter behavior remains until Pack 0004 executes.

## 10. Risk Level

Medium due to secret-bound authenticated control, target mutation, frozen time, and cleanup; mitigated by owner separation, isolated target, lease ownership, bounded observations, and fail-closed gates.

## 11. Migration or Remediation Needed

No TMS data migration or remediation. Separate enabling implementation Packs are required in TMS and the target repository.

## 12. Required Human Approval

Documentation normalization and its exact documentation commit are approved. Pack execution, target/Browser access, fixture mutation, tests using a target, acceptance, implementation commit, and push are not approved.

## 13. Acceptance Criteria

- Five recipe keys and one observation schema match Decision 0004 exactly.
- TMS has no command/schema/model/database knowledge.
- The DK adapter resolves the handle only through the injected port and never retains or emits the secret.
- Lease-context reference remains opaque; every target resource is cleaned and baseline-equal.
- Only the selected 21 variants become executable after Pack 0004.
- Enabling Pack versions/capabilities are checked before any target access.

## 14. Rollback Plan

Revert only Pack-0004-owned DK adapter/scenario/test edits and restore its fail-closed sentinel behavior. Target cleanup uses the authenticated lease DELETE path before source rollback.

## 15. Final Decision

Approved for Pack 0004 normalization; execution remains blocked.

## 16. Required remediation

None. The Pack is unexecuted; this is prospective normalization.

## 17. Notes

The accepted target ND export remains source/traceability input; it is not a control-plane implementation.
