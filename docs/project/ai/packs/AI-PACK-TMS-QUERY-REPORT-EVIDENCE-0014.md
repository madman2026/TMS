# AI-PACK-TMS-QUERY-REPORT-EVIDENCE-0014 — Query, reporting, evidence, and traceability

Status: `blocked — needs regeneration after committed Remediation 0002`

Generated: `2026-10-07`

Decision: Decisions 0008, 0010, 0012, 0013, and 0014; Decision 0014 applied on 2026-10-08 (documentation only).

Current Roadmap Notice (2026-10-08): Decision 0015 supersedes former stage numbering and requires all reports to use the complete hierarchy tuple without legacy projections. Normalize before approval.

Conflict Notice (2026-10-10): regeneration found that accepted Pack 0010 source rejects repeated merged replacement identities, which prevents Decision 0012 many-to-one reconciliation. Change Request 0005 is approved and Remediation 0002 must be executed, accepted, and committed before this Pack is regenerated and receives a new execution gate.

## 1. Task ID

`AI-PACK-TMS-QUERY-REPORT-EVIDENCE-0014`

## 2. Task Title

Create client-neutral status, coverage, execution report, and safe evidence-query services.

## 3. Goal

Provide one read model for CLI, future Web UI, and MCP across 139 use cases, source dispositions, batches, items, attempts, Tests, and evidence references.

## 4. Context

Async execution needs fast status and auditability. Decision 0012 requires complete source-corpus traceability and explicit full/partial/merged/excluded dispositions.

## 5. Related Release / Phase

Decision 0013, stage 7.

## 6. Related Epic / Feature / Story

Operational and coverage reporting.

## 7. Source References

Decisions 0008, 0010–0012; accepted stages 1–6; accepted Core Browser Observation contract.

`docs/project/ai/decisions/TMS-DECISION-0014-typed-service-results-and-client-presentation.md` — semantic report DTOs and client-owned serialization.

## 8. Files to Create

- `app/Acceptance/Reporting/Data/AcceptanceStatusView.php`
- `app/Acceptance/Reporting/Data/AcceptanceCoverageView.php`
- `app/Acceptance/Reporting/Data/AcceptanceReport.php`
- `app/Acceptance/Reporting/AcceptanceQueryService.php`
- `app/Acceptance/Reporting/AcceptanceReportService.php`
- `app/Acceptance/Reporting/CoverageTraceabilityService.php`
- `app/Models/AcceptanceEvidence.php`
- `database/migrations/2026_10_07_000030_create_acceptance_evidence_table.php`
- `app/Acceptance/Operations/Handlers/GetAcceptanceStatus.php`
- `app/Acceptance/Operations/Handlers/GetAcceptanceReport.php`
- `app/Acceptance/Operations/Handlers/GetCoverageReport.php`
- `tests/Feature/AcceptanceReportServiceTest.php`
- `tests/Feature/AcceptanceCoverageTraceabilityTest.php`
- `tests/Unit/AcceptanceQueryServiceTest.php`

## 9. Files to Edit

- execution models/services from Pack 0013
- project scenario/variant descriptor contracts
- `app/Models/Test.php`
- `app/Providers/AppServiceProvider.php`
- `app/Acceptance/Operations/Data/OperationResult.php` — typed report/view payload integration only
- existing browser observation/run persistence tests as needed

## 10. Files to Read / Reference

Project profile; Decisions 0010–0012 and 0014; reporting/error/security/data/test rules; actual accepted stage 1–6 source and Core observation DTO; section 8–9 files.

## 11. Configuration / Settings Requirements

Create report page-size/export-row limits and evidence metadata retention only if absent from `config/acceptance.php`. No raw capture is enabled. No `.env`, Admin UI, storage credential, or external integration change.

## 12. Do Not Change

Web/API/MCP adapters, target scenarios/mappings, raw screenshot/video/trace/HAR/DOM/network storage, secret management, queue transitions, dependencies, `.env`, real target.

## 13. Clarification Questions Before Implementation

Normalization must approve report DTO fields/version, pagination/cursor, evidence metadata fields/retention, source mapping input format, counts, merged-equivalent rules, and export limits.

Report/view DTO properties define semantic data, not JSON property order, empty-map encoding or a CLI/API response schema. Resolve exact additional DTO paths at normalization; client presentation contracts remain Pack 0015/later adapter scope.

## 14. Multilingual / Translation Requirements

Machine report fields/statuses/codes are language-neutral. No UI/RTL. Persian CLI labels/help are Pack 0015 scope.

## 15. UI / Admin UI Requirements

No UI/Admin UI. DTOs must be usable by future clients without exposing models.

## 16. Operator Documentation Requirements

Pack 0015 documents status/report/coverage filters, disposition meaning, attempt history, evidence availability, and safe troubleshooting.

## 17. Implementation Rules

- Queries are read-only, paginated, bounded, and eager-load safely.
- Reports aggregate from immutable descriptors/mappings and durable execution history.
- Every source case has one Decision 0012 disposition; merged items link replacements.
- Evidence table stores allowlisted metadata/reference/checksum/type/size/retention only; capture/storage implementation requires another Pack.
- Report/status/coverage services and handlers return immutable typed DTOs with safe fields, counts and identifiers, never encoded JSON, console strings, response objects or Eloquent models. Services do not implement client serialization/Prompts; adapters map these DTOs under Decision 0014.

## 18. Architecture Constraints

Project owns cross-module aggregation. Target owners own mapping detail and evidence generation. Core supplies normalized observations only.

## 19. Validation Rules

Prove count reconciliation, no double count, source trace links, partial/uncovered assertions, merged links, exclusions, attempt history, pagination/filtering, query bounds, and deterministic typed DTO values/item ordering. Client JSON shape/encoding is tested in Pack 0015; persistence/evidence metadata serialization remains governed by its own storage allowlist.

## 20. Security Rules

Allowlist every report/evidence field. Mask identifiers per target contract. Exclude credentials, account values, selectors, URLs with secrets, raw payload/body/DOM/log/exception, and storage credentials.

## 21. Error Handling / Logging / Traceability Requirements

Define report_not_found, report_query_invalid, coverage_mapping_invalid, coverage_incomplete, evidence_reference_invalid, and export_limit_exceeded. Query failures do not mutate execution. Logs use safe filters/counts and trace IDs. Report includes operation/batch/item/attempt/Test/source IDs where authorized.

## 22. Data Model / Migration / Relationship Requirements

One evidence metadata table linked to attempts/Test as approved. Specify columns/FKs/delete-update behavior/indexes/retention/checksum uniqueness and rollback. No binary/raw evidence columns. Coverage mappings remain source code unless a later decision changes ownership.

## 23. Commenting Requirements

Document count reconciliation, source mapping invariants, and evidence allowlist/retention only.

## 24. Testing Requirements

Feature/database tests cover all dispositions, mixed batch outcomes, attempt history, pagination, limit rejection, evidence relationships/retention, immutable typed result fields, deterministic semantic ordering, query counts, and sentinel omission. Call services directly; do not require JSON output or parse Artisan output to verify report behavior.

## 25. Acceptance Checklist

- [ ] status/report operations are client-neutral and bounded;
- [ ] coverage reconciles every source case exactly once;
- [ ] full/partial/merged/excluded detail is explicit;
- [ ] attempt and Test traceability works;
- [ ] evidence stores metadata only and reports are safe.

## 26. Tests to Add

Exact tests in section 8 plus migration/relationship, query-bound, typed DTO contract, storage-metadata allowlist, and security cases defined at normalization. CLI JSON contract cases belong to Pack 0015.

## 27. Tests to Run

Exact PHPUnit files, isolated migrations/rollback review, representative 24,668-row synthetic aggregation benchmark with no browser, predecessor regressions, and `git diff --check`.

## 28. Expected Output

Query/report/coverage services, evidence metadata schema, operations, tests, Run Report, and guide follow-up.

## 29. Operator Execution Checklist

Before: approve typed report/evidence schema and retention. After: inspect a mixed-outcome report DTO and full source-count reconciliation; review client JSON later in Pack 0015.

## 30. Agent Final Report

Report contracts/schema, coverage counts, query/performance evidence, masking, tests, and deferred capture/UI.

## 31. Review Checklist

Review reconciliation, trace links, query bounds, retention/FKs, typed data safety/stability, client-presentation independence, and scope.

## 32. Rollback / Safety Notes

Reports are read-only; evidence-metadata migration rollback may lose references and requires explicit safe test DB review.

## 33. Stop Conditions

Stop for incomplete/ambiguous source mapping, raw artifact storage requirement, secret exposure, unbounded query/export, unresolved schema/retention, or unaccepted Pack 0013.

## 34. Open Questions

Exact mapping interchange and evidence retention are blocking normalization decisions; visual artifact capture is deferred.
