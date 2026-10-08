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
| Nwidart target module and Component SDK (0010) | ready — normalized 2026-10-08; separate execution approval required | `docs/project/ai/packs/AI-PACK-TMS-TARGET-MODULE-COMPONENT-SDK-0010.md` |
| Operator input and approval workflow (0011) | draft — depends on Packs 0009–0010; typed-result decision applied | `docs/project/ai/packs/AI-PACK-TMS-OPERATOR-INPUT-APPROVAL-0011.md` |
| Target resource lifecycle contracts (0012) | draft — depends on Packs 0009–0011; typed-result decision applied | `docs/project/ai/packs/AI-PACK-TMS-TARGET-RESOURCE-LIFECYCLE-0012.md` |
| Async Queue and Bus Batch orchestration (0013) | draft — depends on stages 1–5; typed-result decision applied | `docs/project/ai/packs/AI-PACK-TMS-ASYNC-BATCH-ORCHESTRATION-0013.md` |
| Query, reporting, evidence, and traceability (0014) | draft — depends on Pack 0013; typed report/client presentation boundary applied | `docs/project/ai/packs/AI-PACK-TMS-QUERY-REPORT-EVIDENCE-0014.md` |
| Complete Artisan operator client (0015) | draft — depends on stages 1–7; Prompts/renderer/script/fallback decision applied | `docs/project/ai/packs/AI-PACK-TMS-ARTISAN-OPERATOR-CLIENT-0015.md` |
| DK target module and owner bootstrap (0016) | draft — depends on stages 1–8 | `docs/project/ai/packs/AI-PACK-TMS-DK-TARGET-MODULE-BOOTSTRAP-0016.md` |
| Second-target proof and scale hardening (0017) | draft — final stage; regenerate after stages 10–14 | `docs/project/ai/packs/AI-PACK-TMS-SECOND-TARGET-SCALE-HARDENING-0017.md` |

Read only the current Pack required for the active task. An executed Pack and Run remain unaccepted until the operator completes the stated acceptance gate.

Decision 0013 (`docs/project/ai/decisions/TMS-DECISION-0013-fifteen-stage-layered-delivery-roadmap.md`) owns the active sequence. Project Pack 0008 is superseded. Project Packs 0009–0016 are the current project-owned stages; Core executor stage 5 is indexed by the Core owner. Project Pack 0017 is the final stage and must be regenerated after owner-local stages 10–14. Those five target-specific Pack records must be created under the activated DK/related owners after Pack 0016, not under this index. Accepted Packs 0001–0004 and 0007 remain valid foundation. Decision 0005's testing/staging, synthetic-data, target ownership, and safe-output constraints remain applicable.

Decision 0014 (`docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md`) clarifies typed service DTOs and client-owned presentation in Packs 0009–0015, with Laravel Prompts limited to Pack 0015's interactive CLI. Its documentation acceptance changes no roadmap stage or execution/acceptance gate.
