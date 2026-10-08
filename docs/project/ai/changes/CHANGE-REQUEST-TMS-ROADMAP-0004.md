# CHANGE-REQUEST-TMS-ROADMAP-0004 — Replace compatibility migration with a clean hierarchy cutover

## 1. Title

Remove the legacy Acceptance provider/runtime paths and reorder the roadmap around one atomic hierarchy cutover.

## 2. Status

Closed — Remediation 0001 validated and operator accepted on 2026-10-08; Pack 0010 normalized for separate execution approval.

## 3. Related Release / Phase

Pre-Release TMS platform and target-onboarding roadmap.

## 4. Decision Source

`docs/project/ai/decisions/TMS-DECISION-0015-clean-acceptance-hierarchy-cutover.md`.

## 5. Proposed Change

Replace the temporary compatibility strategy in Pack 0010 and the active roadmap with a strict version-2 hierarchy cutover. Remove obsolete Core and project contracts through one cross-owner remediation, then normalize Pack 0010 as a clean Nwidart module/Component SDK over the remediated source.

The remediation must remove old runtime branches instead of retaining adapters or aliases. Pack 0010 and every successor must consume only the new hierarchy contract.

## 6. Reason for Change

The compatibility plan would permanently increase ambiguity and test surface while no production target App depends on the old provider model. The operator explicitly required complete replacement and prohibited leaving old compatibility code behind.

## 7. Scope of Impact

Affected areas:

- Core Acceptance contracts, metadata enum/value object, and exact Core tests;
- root App registry, catalog, selectors, plans, dispatcher, run operation, CLI adapters, persistence identity, logs, and exact tests;
- Decision/Change/Remediation/Pack indexes and successor Pack normalization status;
- operator CLI guide after the new command contract exists.

No target module, real target access, dependency, Web/API/MCP surface, queue, cache, secret store, Composer operation, or environment-file change is authorized.

## 8. Affected Files

Exact implementation scope is owned by `docs/project/ai/remediations/REMEDIATION-PACK-TMS-ACCEPTANCE-HIERARCHY-CUTOVER-0001.md`.

Governance scope includes this Change Request, Decision 0015, relevant decision/index notices, remediation and Run indexes, Pack 0010 lifecycle status, and successor Draft navigation.

## 9. Backward Compatibility

No.

The old provider contract, fallback discovery, `legacy` hierarchy defaults, `manual-only` runtime disposition, old selector meaning, old CLI projection/signature, and partial tuple identity are intentionally unsupported after cutover.

Accepted historical Pack/Run records are retained unchanged as history; they do not remain runtime contracts.

## 10. Risk Level

High. The change crosses the Core/project contract boundary and changes command, DTO, persistence-identity, logging, and test contracts. Risk is controlled through exact file scope, synthetic-only validation, no real target, and an atomic final diff.

## 11. Migration or Remediation Needed

Yes.

- `docs/project/ai/remediations/REMEDIATION-PACK-TMS-ACCEPTANCE-HIERARCHY-CUTOVER-0001.md`

No old/new bridge remediation is permitted. Database migration execution against a non-empty database is a separate operator action and must stop until existing record disposition is explicitly approved.

## 12. Required Human Approval

The architecture and documentation change are approved by the operator's explicit 2026-10-08 instruction. Remediation execution, Pack 0010 execution, acceptance, and commit require their normal separate gates.

## 13. Acceptance Criteria

- [x] no source reference to an obsolete Acceptance provider, snapshot/fallback hierarchy, reserved compatibility identity, or removed runtime disposition remains;
- [x] every registered App exposes the complete App/Component/Suite/Scenario/Variant contract;
- [x] selection, planning, dispatch, run requests/results, CLI JSON, logs, and new execution records use the complete tuple;
- [x] version-2 CLI/DTO behavior replaces the former projection without aliases or dual modes;
- [x] accepted historical records remain available but are not treated as current implementation instructions;
- [x] Core and project tests prove one contract and contain no compatibility test cases;
- [x] Pack 0010 was normalized after the remediation source was accepted;
- [x] future Pack indexes and status notes route to Decision 0015;
- [x] no target, dependency, environment, network, queue, cache, or unrelated source changes occurred.

## 14. Rollback Plan

Before commit, revert only the remediation's exact source/test/migration/guide files and its directly related governance records using a targeted reviewed patch. Do not use broad Git restore/reset/clean commands and do not alter accepted historical Pack/Run content.

After a database migration is applied, rollback is allowed only on a verified non-production database under a separately approved exact command. No automatic deletion or backfill of existing Test records is authorized.

## 15. Final Decision

Implemented and closed by accepted Remediation 0001. The persistent Run Report and Pack 0010 normalization are complete. Pack 0010 execution and commit remain separate gates.

## 16. Required remediation

`REMEDIATION-PACK-TMS-ACCEPTANCE-HIERARCHY-CUTOVER-0001`.

## 17. Notes

Pack 0010 is unexecuted, so its historical content may be normalized after remediation. Accepted Packs 0004, 0007, and 0009 remain immutable records; Decision 0015 and the remediation identify which runtime contracts they supersede.
