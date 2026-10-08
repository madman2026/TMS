# TMS PROJECT PACKS INDEX

This index owns TMS project-wide and repository-governance Pack navigation. Module-owned Packs belong in the active module-local Pack index after that module's documentation owner is activated.

| Pack | Status | File |
|---|---|---|
| Three-layer documentation governance bootstrap (0001) | accepted — technical validation passed; operator accepted | `docs/project/ai/packs/AI-PACK-TMS-DOCS-BOOTSTRAP-0001.md` |
| Acceptance Runner minimal stabilization (0002) | accepted — technical validation passed; operator accepted | `docs/project/ai/packs/AI-PACK-TMS-ACCEPTANCE-RUNNER-STABILIZATION-0002.md` |
| Acceptance Command and App Registry (0003) | accepted — technical validation passed; operator accepted | `docs/project/ai/packs/AI-PACK-TMS-ACCEPTANCE-COMMAND-REGISTRY-0003.md` |
| Generic Acceptance Scenario Contracts (0004) | accepted — technical validation passed; operator accepted | `docs/project/ai/packs/AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004.md` |
| Lazy Scenario Catalog and Variant Dispatcher (0007) | accepted — technical validation passed; bounded catalog/dispatcher foundation accepted | `docs/project/ai/packs/AI-PACK-TMS-SCENARIO-CATALOG-DISPATCHER-0007.md` |
| Sequential Resumable Batch CLI Execution (0008) | superseded — historical Draft; do not normalize or execute | `docs/project/ai/packs/AI-PACK-TMS-BATCH-CLI-EXECUTION-0008.md` |
| Transport-neutral Acceptance operation layer (0009) | accepted — technical validation passed; operator accepted 2026-10-08 | `docs/project/ai/packs/AI-PACK-TMS-OPERATION-SERVICE-LAYER-0009.md` |
| Nwidart target module and Component SDK (0010) | ready for execution approval — normalized after accepted Remediation 0001 | `docs/project/ai/packs/AI-PACK-TMS-TARGET-MODULE-COMPONENT-SDK-0010.md` |
| Operator input and approval workflow (0011) | draft — normalize against Decision 0015 and accepted Packs 0010 | `docs/project/ai/packs/AI-PACK-TMS-OPERATOR-INPUT-APPROVAL-0011.md` |
| Target resource lifecycle contracts (0012) | draft — normalize against Decision 0015 and accepted predecessors | `docs/project/ai/packs/AI-PACK-TMS-TARGET-RESOURCE-LIFECYCLE-0012.md` |
| Async Queue and Bus Batch orchestration (0013) | draft — stage 7 under Decision 0015; normalize after Core stage 6 | `docs/project/ai/packs/AI-PACK-TMS-ASYNC-BATCH-ORCHESTRATION-0013.md` |
| Query, reporting, evidence, and traceability (0014) | draft — stage 8 under Decision 0015; normalize after Pack 0013 | `docs/project/ai/packs/AI-PACK-TMS-QUERY-REPORT-EVIDENCE-0014.md` |
| Complete Artisan operator client (0015) | draft — stage 9; legacy-projection clauses superseded by Decision 0015 | `docs/project/ai/packs/AI-PACK-TMS-ARTISAN-OPERATOR-CLIENT-0015.md` |
| DK target module and owner bootstrap (0016) | draft — stage 10 under Decision 0015 | `docs/project/ai/packs/AI-PACK-TMS-DK-TARGET-MODULE-BOOTSTRAP-0016.md` |
| Second-target proof and scale hardening (0017) | draft — stage 16; regenerate after owner-local stages 11–15 | `docs/project/ai/packs/AI-PACK-TMS-SECOND-TARGET-SCALE-HARDENING-0017.md` |

Read only the current Pack required for the active task. An executed Pack and Run remain unaccepted until the operator completes the stated acceptance gate.

Decision 0015 (`docs/project/ai/decisions/TMS-DECISION-0015-clean-acceptance-hierarchy-cutover.md`) owns the active sixteen-stage sequence and supersedes Decision 0013's ordering. Project Pack 0008 remains superseded. The project-owned cross-owner Remediation 0001 completed stage 2; normalized Pack 0010 is stage 3 and awaits separate execution approval. Core executor stage 6 remains indexed by Core. Project Pack 0017 is stage 16 and must be regenerated after owner-local stages 11–15. Those five target-specific Pack records must be created under activated DK/related owners after Pack 0016. Accepted Packs 0001–0004, 0007, and 0009 remain historical foundation evidence; their obsolete compatibility contracts do not override Decision 0015. Decision 0005's testing/staging, synthetic-data, target ownership, and safe-output constraints remain applicable.

Decision 0014 (`docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md`) still owns typed service DTOs and client-owned presentation. Decision 0015 supersedes only its legacy CLI-preservation clauses and reorders the roadmap; every execution/acceptance/commit gate remains separate.
