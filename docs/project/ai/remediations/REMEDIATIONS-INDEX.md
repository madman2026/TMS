# TMS PROJECT REMEDIATIONS INDEX

| Remediation Pack | Status | Scope | File |
|---|---|---|---|
| Acceptance hierarchy hard cutover (0001) | accepted — validation passed; operator accepted 2026-10-08 | cross-owner Core/project runtime contract | `docs/project/ai/remediations/REMEDIATION-PACK-TMS-ACCEPTANCE-HIERARCHY-CUTOVER-0001.md` |
| Coverage merged-replacement correction (0002) | accepted — run 001 accepted; commit authorized | project source-mapping validation / Decision 0012 | `docs/project/ai/remediations/REMEDIATION-PACK-TMS-COVERAGE-MERGE-0002.md` |
| DK descriptor-import correction (0003) | accepted — operator accepted 2026-10-11; runtime/catalog/coverage validation passed; commit authorized | project / DK Pack 0003 runtime recovery / Decision 0018 | `docs/project/ai/remediations/REMEDIATION-PACK-TMS-DK-DESCRIPTOR-IMPORTS-0003.md` |
| Pack 0003 generic CLI test isolation (0004) | accepted — operator accepted 2026-10-11; focused, nearby, and full suite passed; commit authorized | project / DK Pack 0003 full-suite recovery / Decision 0019 | `docs/project/ai/remediations/REMEDIATION-PACK-TMS-PACK-0003-CLI-TEST-ISOLATION-0004.md` |
| Configuration surface and environment manifest (0005) | accepted — implementation and validation passed; operator accepted and authorized commit 2026-10-11 | project / cross-owner configuration consistency / Decision 0020 | `docs/project/ai/remediations/REMEDIATION-PACK-TMS-CONFIGURATION-SURFACE-0005.md` |

Remediation 0001 closes Change Request 0004 and is the implementation foundation for normalized Pack 0010. Remediations 0003–0004 are limited Pack 0003 recovery corrections; shared importer/validator prevention and scale/format hardening remain open in Change Request 0007. Remediation 0005 implements Change Request 0008 after a separate execution gate and never authorizes AI access to `.env`.
