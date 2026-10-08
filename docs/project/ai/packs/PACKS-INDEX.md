# TMS PROJECT PACKS INDEX

This index owns TMS project-wide and repository-governance Pack navigation. Module-owned Packs belong in the active module-local Pack index after that module's documentation owner is activated.

| Pack | Status | File |
|---|---|---|
| Three-layer documentation governance bootstrap (0001) | accepted — technical validation passed; operator accepted | `docs/project/ai/packs/AI-PACK-TMS-DOCS-BOOTSTRAP-0001.md` |
| Acceptance Runner minimal stabilization (0002) | accepted — technical validation passed; operator accepted | `docs/project/ai/packs/AI-PACK-TMS-ACCEPTANCE-RUNNER-STABILIZATION-0002.md` |
| Acceptance Command and App Registry (0003) | accepted — technical validation passed; operator accepted | `docs/project/ai/packs/AI-PACK-TMS-ACCEPTANCE-COMMAND-REGISTRY-0003.md` |
| Generic Acceptance Scenario Contracts (0004) | accepted — technical validation passed; operator accepted | `docs/project/ai/packs/AI-PACK-TMS-GENERIC-ACCEPTANCE-CONTRACTS-0004.md` |
| Lazy Scenario Catalog and Variant Dispatcher (0007) | accepted — technical validation passed; bounded catalog/dispatcher foundation accepted | `docs/project/ai/packs/AI-PACK-TMS-SCENARIO-CATALOG-DISPATCHER-0007.md` |
| Sequential Resumable Batch CLI Execution (0008) | draft — predecessor accepted; normalization and separate approval required | `docs/project/ai/packs/AI-PACK-TMS-BATCH-CLI-EXECUTION-0008.md` |
| Transport-neutral Acceptance operation layer (0009) | accepted — technical validation passed; operator accepted 2026-10-08 | `docs/project/ai/packs/AI-PACK-TMS-OPERATION-SERVICE-LAYER-0009.md` |

Read only the current Pack required for the active task. An executed Pack and Run remain unaccepted until the operator completes the stated acceptance gate.

Decision 0006 (`docs/project/ai/decisions/TMS-DECISION-0006-remove-cancelled-fixture-and-safeguard-packs.md`) cancels/removes project 0006 and deletes the already superseded Core 0002/project 0005 proposals. Project 0004, project 0007 and Core Browser Observability 0001 are accepted; their bounded evidence remains unchanged. Project 0008 remains Draft and now requires normalization against accepted source and separately approved successor architecture decisions before execution. No fixture lifecycle or target-environment guard from reverted Pack 0006 exists in current source. Decision 0005's testing/staging-only synthetic-data and safe-output constraints remain applicable; expanded settings and standalone secret/capture frameworks remain deferred.
