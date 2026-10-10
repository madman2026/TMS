# AI-PACK-TMS-QUERY-REPORT-EVIDENCE-0014 — Query, reporting, evidence, and traceability

Status: `accepted — technical validation passed; operator accepted 2026-10-10`

Generated: `2026-10-07`

Regenerated: `2026-10-10`

Baseline: `1ebe0b96722e3bbdc80a00ad33756c90c6c2efb0`

Decision: accepted Decisions 0005, 0008–0012, and 0014–0016. Decision 0015 owns stage 8 and the single hierarchy contract. Decision 0013 is superseded history.

Execution / Acceptance / Commit: Packs 0010–0013, Core Pack 0003, Remediation 0001, and Remediation 0002 are accepted and committed. The operator approved execution, accepted the post-execution result, and separately authorized commit on 2026-10-10. Run 001 records the accepted execution; the commit containing this Pack and Run follows this pre-commit update.

## 1. Task ID

`AI-PACK-TMS-QUERY-REPORT-EVIDENCE-0014`

## 2. Task Title

Implement bounded client-neutral status, execution-report, coverage-traceability, and safe evidence-metadata services.

## 3. Goal

Expose one immutable typed read boundary for future CLI, Web, API, and MCP clients over durable operation, batch, item, attempt, Test, source-coverage, and evidence-metadata history without exposing Eloquent models, presentation schemas, raw artifacts, secrets, or unbounded result sets.

## 4. Context

Pack 0013 now owns durable operation/batch/item/attempt records and preserves every attempt and final outcome. Pack 0010 owns target source mappings and Remediation 0002 permits multiple distinct `merged_equivalent` cases to link to one executable replacement. Decision 0012 requires deterministic reconciliation of every owner-declared source case, while Decision 0014 requires semantic typed DTOs whose safe data can be presented independently by later clients.

This Pack reads accepted history; it does not change execution state. Target Apps continue to own mappings in source code. Project services validate and aggregate those mappings through one explicit coverage-capable provider contract.

## 5. Related Release / Phase

Decision 0015, stage 8. Pre-Release; no release-specific or phase-specific directory is activated.

## 6. Related Epic / Feature / Story

Operational status, attempt-aware execution reporting, complete source coverage reconciliation, and safe artifact-reference traceability.

## 7. Source References

- repository startup files, project profile/index, and applicable shared rules;
- Decisions 0005, 0008–0012, and 0014–0016;
- accepted Packs/Runs 0010–0013 and Core Pack 0003;
- accepted Change Request 0005 and Remediation Pack/Run 0002;
- current coverage mapping DTO/disposition, generated target stubs/validator, operation boundary, execution models/migrations, Test model, configuration, and related tests;
- installed Laravel 12 Eloquent cursor/eager-loading, migration, cast, relationship, and database-query behavior.

## 8. Files to Create

- `app/Contracts/AcceptanceCoverageProvider.php`
- `app/Acceptance/Reporting/Enums/EvidenceType.php`
- `app/Acceptance/Reporting/Data/EvidenceMetadataInput.php`
- `app/Acceptance/Reporting/Data/AcceptanceEvidenceView.php`
- `app/Acceptance/Reporting/Data/AcceptanceAttemptView.php`
- `app/Acceptance/Reporting/Data/AcceptanceReportItem.php`
- `app/Acceptance/Reporting/Data/AcceptanceStatusView.php`
- `app/Acceptance/Reporting/Data/AcceptanceReport.php`
- `app/Acceptance/Reporting/Data/CoverageCounts.php`
- `app/Acceptance/Reporting/Data/CoverageSourceCaseView.php`
- `app/Acceptance/Reporting/Data/AcceptanceCoverageView.php`
- `app/Acceptance/Reporting/AcceptanceReportingException.php`
- `app/Acceptance/Reporting/AcceptanceQueryService.php`
- `app/Acceptance/Reporting/AcceptanceReportService.php`
- `app/Acceptance/Reporting/CoverageTraceabilityService.php`
- `app/Acceptance/Reporting/AcceptanceEvidenceService.php`
- `app/Models/AcceptanceEvidence.php`
- `database/migrations/2026_10_10_000030_create_acceptance_evidence_table.php`
- `app/Acceptance/Operations/Handlers/GetAcceptanceStatus.php`
- `app/Acceptance/Operations/Handlers/GetAcceptanceReport.php`
- `app/Acceptance/Operations/Handlers/GetCoverageReport.php`
- `tests/Unit/AcceptanceReportingDataTest.php`
- `tests/Unit/CoverageTraceabilityServiceTest.php`
- `tests/Feature/AcceptanceQueryServiceTest.php`
- `tests/Feature/AcceptanceReportServiceTest.php`
- `tests/Feature/AcceptanceEvidenceTest.php`
- `tests/Feature/AcceptanceReportingOperationTest.php`

After accepted execution, create `docs/project/ai/runs/AI-PACK-TMS-QUERY-REPORT-EVIDENCE-0014-run-001.md`.

## 9. Files to Edit

- `config/acceptance.php`
- `stubs/acceptance/TargetAcceptanceApp.stub`
- `app/Acceptance/Modules/TargetModuleValidator.php`
- `app/Acceptance/Operations/AcceptanceOperationService.php`
- `app/Acceptance/Operations/Data/OperationResult.php`
- `app/Models/AcceptanceExecutionAttempt.php`
- `app/Models/Test.php`
- `app/Providers/AppServiceProvider.php`
- `tests/Unit/AcceptanceHierarchyTest.php`
- `tests/Unit/TargetModuleCreatorTest.php`
- `tests/Unit/TargetModuleValidatorTest.php`
- `tests/Unit/AcceptanceOperationServiceTest.php`
- `docs/project/ai/packs/AI-PACK-TMS-QUERY-REPORT-EVIDENCE-0014.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md` after accepted execution.

No other implementation, target, Core, guide, decision, change, remediation, or accepted-history file is in scope.

## 10. Files to Read / Reference

Read every existing section 7–9 path, applicable repository rules, the named Decisions, accepted predecessor Pack/Run records, current execution factories where test setup needs them, and installed framework source only when an API detail cannot be established from project source. Do not read `.env`, target corpora, unrelated modules, raw captures, or unrelated lifecycle records.

## 11. Configuration / Settings Requirements

Extend `config/acceptance.php` with exact code-owned non-sensitive defaults:

```text
reporting.default_page_size = 50
reporting.max_page_size     = 250
reporting.max_export_rows   = 25000
reporting.max_source_cases  = 25000
```

Every value must be an integer and must satisfy:

```text
1 <= default_page_size <= max_page_size <= 1000
1 <= max_export_rows <= 25000
24668 <= max_source_cases <= 25000
```

Invalid or missing runtime configuration fails with `acceptance_configuration_invalid`; it never silently falls back inside a service. No environment variable, `.env.example`, Admin setting, storage credential, queue/cache/log setting, or operator setup is added. The `25000` bounds cover the approved 24,668-case source corpus with a small fixed ceiling.

Evidence metadata is retained indefinitely as audit history in this Pack. Nullable `available_until` records an external artifact-reference availability deadline; it is not a metadata deletion deadline. No prune command, scheduler, TTL, or automatic delete is introduced.

## 12. Do Not Change

Do not change execution transitions, retry/cancel/recovery behavior, queue jobs, target-resource/Core executor contracts, target mappings or scenarios, raw screenshot/video/trace/HAR/DOM/network/log capture or storage, storage drivers/credentials, target I/O, Web/API/MCP/CLI presentation, dependencies, vendor source, `.env`, production data, accepted history, or Core. Do not add JSON serialization, `toArray`, `JsonSerializable`, response objects, translated text, URLs, selectors, payloads, exception text, or arbitrary metadata maps to report DTOs or evidence rows.

## 13. Approved Normalization Decisions

1. Operation names are exactly `acceptance.status`, `acceptance.report`, and `acceptance.coverage`.
2. `acceptance.status` accepts exactly one of `operation_id` UUID or positive `batch_id`. `acceptance.report` requires positive `batch_id` and accepts optional positive `after_item_id` and bounded `limit`. `acceptance.coverage` requires `app_key` and accepts optional `after_source_case_id`, bounded `limit`, and a list of approved disposition values.
3. `AcceptanceQueryService` returns one `AcceptanceStatusView`; `AcceptanceReportService` returns cursor-bounded `AcceptanceReport`; `CoverageTraceabilityService` returns cursor-bounded `AcceptanceCoverageView`. Page cursors are semantic nullable item/source identifiers, not encoded client tokens.
4. Report pages order items by numeric ID, attempts by attempt number then ID, and evidence by ID. Coverage sorts source cases by binary source-case ID. The same accepted rows always produce the same semantic ordering.
5. `AcceptanceCoverageProvider` extends `AcceptanceComponentProvider` with `sourceCaseMappings(): iterable`. Generated target Apps implement this single enriched contract and delegate to their owner-local `SourceCaseMappings::all()`. Existing non-target test providers may remain hierarchy-only; a coverage query against one fails safely with `acceptance_coverage_mapping_invalid`.
6. Coverage validates each entry type, source-ID uniqueness, direct executable-identity uniqueness, complete catalog identity resolution, and one direct automated mapping for every executable catalog variant. Many merged cases may share one replacement; every replacement must resolve to an existing direct automated identity. Counts across all five dispositions must sum exactly to the source total.
7. Coverage pagination never weakens full reconciliation: the service validates and counts the complete bounded mapping set before returning a page. More than `reporting.max_source_cases` entries fails with `acceptance_export_limit_exceeded`.
8. Normal report methods are cursor-paged. Separate service-level `export` methods may materialize at most `reporting.max_export_rows` typed rows for Pack 0015 clients; this Pack adds no export operation or serialization.
9. Evidence storage is metadata-only. `AcceptanceEvidenceService` records one typed metadata input for an existing attempt and returns a safe view; it performs no file/network/storage access. Metadata history is never automatically deleted.
10. No schema, provider, DTO, cursor, count, evidence, retention, limit, error-classification, or operation-signature choice remains open.

## 14. Multilingual / Translation Requirements

No multilingual, visible-text, validation-message, or RTL/LTR change. DTO properties, enum values, operation names, and stable codes are language-neutral. Persian CLI labels and rendering belong to Pack 0015.

## 15. UI / Admin UI Requirements

No UI/Admin UI. No Blade, route, controller, API resource, MCP tool, table, form, action, dashboard, CSS, screenshot, or permission surface is created.

## 16. Operator Documentation Requirements

Do not edit the CLI guide. The Run Report must preserve a Pack 0015 follow-up describing status/report/coverage filters, disposition meaning, cursors/page limits, attempt history, evidence availability, safe export ceiling, and stable-code guidance.

## 17. Implementation Rules

- Use PHP 8.2-compatible syntax, final readonly DTOs, explicit parameter/return types, constructor injection, enums, Eloquent relationships, and sibling conventions.
- DTO constructors validate versions, IDs, enum/state types, non-negative counts, cursor consistency, timestamps, and array element classes; copy list inputs; expose no model, collection, arbitrary object, serializer, client ordering convention, or human message.
- `AcceptanceStatusView` version 1 contains observed operation UUID, batch ID, correlation UUID, operation/batch states, mode, lock versions, matched/executable/skipped and current item-state counts, failure count, nullable stable error/classification, and lifecycle timestamps.
- `AcceptanceReport` version 1 contains the same stable aggregate identifiers/states/counts plus a list of `AcceptanceReportItem`, requested limit, nullable after-item ID, nullable next-item ID, and `hasMore`. Each item contains ID/ordinal, full hierarchy tuple, capability/catalog version, state, attempt count, stable primary/cleanup codes/classification/timestamps, and ordered `AcceptanceAttemptView` values.
- Each attempt view contains ID/number/state, nullable Test ID, executor capability/key, infrastructure-attempt count, executor-entered flag, stable primary/cleanup codes/classification/timestamps, and ordered safe evidence views. It never returns Test/Step `data`, names, descriptions, error messages, payloads, or Eloquent objects.
- `CoverageCounts` has one non-negative integer property for each of the five Decision-0012 dispositions and a checked total. `CoverageSourceCaseView` exposes source ID, disposition, nullable direct/replacement tuple fields, reason, covered/uncovered assertion keys, and version. `AcceptanceCoverageView` adds app/catalog version, counts, page/cursor fields, and ordered cases.
- `EvidenceMetadataInput` version 1 contains `EvidenceType`, opaque `referenceKey`, lowercase SHA-256, non-negative `sizeBytes`, approved media type, nullable dimensions/duration, immutable `capturedAt`, and nullable `availableUntil`. It contains no path, URL, query string, header, selector, credential, or raw body.
- Opaque evidence references match `^[A-Za-z0-9][A-Za-z0-9._:-]{0,190}$`; SHA-256 is 64 lowercase hex; size is `0..4294967295`; dimensions are `1..65535`; duration is `0..86400000`; `available_until` cannot precede capture. `EvidenceType` owns the exact type/media compatibility allowlist.
- Approved evidence types/media are: `screenshot` with `image/png|image/webp`; `video` with `video/webm|video/mp4`; `trace` with `application/json|application/zip`; `har` with `application/json`; `dom_snapshot` with `text/html|application/zip`; `network_trace` with `application/json|application/zip`; and `log_reference` with `text/plain|application/zip`. No raw content is accepted.
- Query services use selected columns, database aggregates, cursor predicates, and bounded eager loads. Status uses at most 5 SQL queries; a report page uses at most 10 regardless of page size. Coverage uses no database query and visits at most `max_source_cases + 1` mappings.
- `OperationResult` adds only the three top-level reporting DTO types to its typed data union. `AcceptanceOperationService` validates new parameter shapes, result/operation matches, stable codes, and safe classifications; query success is not logged. Failures use the existing `tms.acceptance.operation.failed` event with only operation, stable code, correlation ID, safe subject IDs/app key, and classification.
- Register handlers lazily in `AppServiceProvider`. Reporting services remain read-only except `AcceptanceEvidenceService::record`, whose only mutation is one metadata row.
- The target stub/validator must require the enriched coverage provider and exact delegation to `SourceCaseMappings::all()` without loading target code during static validation.

## 18. Architecture Constraints

The TMS project owns generic aggregation, persistence metadata, safety validation, and operation boundaries. Target owners own their source mappings and evidence generation. Core continues to supply normalized execution results only. Reporting services do not call targets, executors, jobs, commands, or client renderers and do not mutate operation/batch/item/attempt/Test state.

The complete App → Component → Suite → Scenario → Variant identity is preserved in report and coverage DTOs. No legacy projection, alias, default variant, fallback provider, client JSON schema, or target-class naming convention is introduced.

## 19. Validation Rules

- Status rejects neither/both identifiers, malformed identifiers, and missing rows; operation and batch identifiers must cross-resolve to the same one-to-one aggregate.
- Report rejects missing batches, cursor IDs outside that batch, invalid limits, and export sizes over the configured ceiling. Page `hasMore/nextItemId` must be derived by fetching at most one extra item.
- Counts reconcile database aggregate states and Pack-0013 durable counters without double counting. Attempt history includes every attempt; a retry never replaces an earlier attempt.
- Evidence metadata must belong to the returned attempt; Test trace is the attempt's nullable `test_id`. Expired external availability changes only the view's `available` flag and never removes metadata history.
- Coverage rejects hierarchy-only providers, non-`SourceCaseMapping` values, duplicate source IDs, duplicate direct identities, missing direct/replacement tuples, replacement cycles/non-direct replacements, incomplete catalog coverage, invalid disposition detail, and visit overflow.
- `automated_full`, `automated_partial`, `merged_equivalent`, `excluded_no_reliable_executor`, and `excluded_human_judgment` counts sum exactly to total. Merged replacements are not counted as additional executable identities.
- Filters change only returned page cases, never total reconciliation/counts. Unknown disposition filter values are rejected before provider traversal.
- The 24,668-entry synthetic coverage test completes within a PHPUnit-owned 5-second budget on the local test environment and remains below a 64 MiB incremental-memory ceiling; record observed values without treating timing as a production SLA.

## 20. Security Rules

Allowlist every selected database column and DTO property. Never return or log selector snapshots, classification snapshots as raw maps, prerequisite values/references, profile fields, Test/Step data, Step descriptions/error messages, executor payloads, raw exceptions, resource handles, URLs, filesystem paths, browser content, DOM, HAR, trace/log content, credentials, tokens, headers, or storage configuration.

Tests use unique sensitive sentinels in forbidden Test/Step data, exception text, selector/classification snapshots, and evidence-like strings, then assert absence from DTOs, operation results, and logs. Evidence reference keys are opaque identifiers only; values containing `/`, `?`, `#`, `@`, whitespace, controls, or backslashes are rejected.

## 21. Error Handling / Logging / Traceability Requirements

Add these stable language-neutral codes:

```text
acceptance_report_not_found
acceptance_report_query_invalid
acceptance_coverage_mapping_invalid
acceptance_coverage_incomplete
acceptance_evidence_reference_invalid
acceptance_export_limit_exceeded
acceptance_reporting_failed
```

Classification:

- `acceptance_report_not_found`, `acceptance_report_query_invalid`, and `acceptance_export_limit_exceeded`: rejected, non-retryable, permanent, no admin action;
- `acceptance_coverage_mapping_invalid`, `acceptance_coverage_incomplete`, and `acceptance_evidence_reference_invalid`: failed, non-retryable, permanent, admin action required;
- `acceptance_reporting_failed`: failed, retryability unknown, permanence unknown, admin action required.

Shape failures remain `operation_request_invalid`; invalid runtime config remains `acceptance_configuration_invalid`. No status transition or retry policy changes. Report DTOs carry only authorized operation, batch, item, attempt, Test, source, and evidence IDs. Read failure logs contain no filter arrays, cursor contents beyond validated scalar identifiers, row data, exception message/chain, or evidence reference/checksum.

## 22. Data Model / Migration / Relationship Requirements

Create `acceptance_evidence`:

- `id` big integer primary key;
- `attempt_id` foreign key to `acceptance_execution_attempts`, `restrictOnDelete`, `cascadeOnUpdate`;
- `type` string(32);
- globally unique `reference_key` string(191);
- `checksum_sha256` char(64) with a non-unique index;
- `size_bytes` unsigned big integer;
- `media_type` string(127);
- nullable unsigned `width`, `height`, and `duration_ms`;
- `captured_at` timestamp and nullable `available_until` timestamp;
- framework timestamps;
- indexes `(attempt_id, captured_at)` and `available_until`.

There is no JSON, text/blob, URL/path, owner payload, secret, deletion marker, or storage-location column. Checksum is intentionally not unique because identical safe artifacts may prove multiple attempts; the opaque reference is unique.

Relationships: evidence belongs to one attempt; attempt has many evidence rows; Test reaches evidence through its one attempt using `hasManyThrough`. The attempt's nullable unique `test_id` remains the only Test linkage, preventing a second denormalized/mismatched Test foreign key. Evidence prevents deletion of its attempt; no cascade deletes audit metadata. Dropping this new table is destructive only to evidence metadata and is exercised only inside the PHPUnit in-memory SQLite database.

## 23. Commenting Requirements

Use PHPDoc for typed list/array shapes and concise invariant comments only at full-coverage reconciliation, cursor `limit + 1`, evidence allowlist, query-bound, and target-provider static-validation boundaries. Do not narrate ordinary code.

## 24. Testing Requirements

- DTO unit tests cover immutability, copying, versions, enums, counts, cursors, ordering inputs, forbidden serializers, and invalid values.
- Coverage unit tests cover all five dispositions, full count reconciliation, many-to-one merged links, direct/catalog completeness, hierarchy-only provider rejection, duplicates, missing replacements, filtering/pagination, overflow, deterministic ordering, and the 24,668-entry benchmark.
- Query/report feature tests use `RefreshDatabase` with synthetic rows and cover both status identifiers, not found/invalid queries, every mixed item/attempt outcome, retry history, Test linkage, cursor pages, export ceiling, deterministic ordering, selected-column safety, and 5/10 query ceilings.
- Evidence feature tests cover migration columns/indexes/FKs, relationship traversal, opaque reference/media/type/range validation, uniqueness, availability deadline behavior, indefinite metadata history, in-memory migration down/up, and raw sentinel omission.
- Operation feature/unit tests cover lazy registration, exact parameter matrices, typed payload matching, classifications, safe failure normalization/logs, correlation preservation, and direct service use without booting a console.
- Generated target tests prove the enriched provider returns owner-local mappings and static validation rejects missing/incorrect delegation without loading target code.
- No test requires a real target, browser, network, queue worker, external storage, filesystem artifact, or non-test database.

## 25. Acceptance Checklist

- [x] status/report operations are typed, read-only, deterministic, cursor-bounded, and query-bounded;
- [x] every attempt and nullable Test link is preserved without exposing unsafe model data;
- [x] all five coverage dispositions reconcile exactly once over the complete bounded source set;
- [x] many merged source cases resolve to one valid direct executable replacement;
- [x] every executable catalog variant has a direct source mapping and no mapping points outside the catalog;
- [x] evidence persistence contains only the exact safe metadata schema and opaque references;
- [x] DTOs/results/logs pass sentinel omission and transport-neutrality tests;
- [x] focused/full regression, in-memory migration, benchmark, Pint, and diff checks pass;
- [x] Run Report and indexes record only observed evidence.

## 26. Tests to Add

Create the six exact test files in section 8. Use behavior names covering: immutable DTO validation; complete five-disposition reconciliation; shared merged replacement; invalid/incomplete mapping; deterministic cursor pages; 24,668-case bound; aggregate status; mixed-outcome attempt history; retry preservation; query ceilings; export limit; metadata-only schema; Test-through-attempt relationship; expired availability without deletion; safe operation classification; target provider delegation; and sensitive sentinel omission.

Edit only the four existing tests listed in section 9 to cover the new operation result union/provider stub/static-validator boundary. Do not weaken or delete existing assertions.

## 27. Tests to Run

After implementation and before any acceptance request:

```text
php artisan test --compact tests/Unit/AcceptanceReportingDataTest.php tests/Unit/CoverageTraceabilityServiceTest.php
php artisan test --compact tests/Feature/AcceptanceQueryServiceTest.php tests/Feature/AcceptanceReportServiceTest.php tests/Feature/AcceptanceEvidenceTest.php tests/Feature/AcceptanceReportingOperationTest.php
php artisan test --compact tests/Unit/AcceptanceOperationServiceTest.php tests/Unit/AcceptanceHierarchyTest.php tests/Unit/TargetModuleCreatorTest.php tests/Unit/TargetModuleValidatorTest.php
php artisan test --compact tests/Feature/AcceptanceAsyncBatchTest.php tests/Feature/AcceptanceBatchPersistenceTest.php tests/Feature/TargetModuleOperationServiceTest.php
vendor/bin/pint --dirty --format agent
php artisan test --compact
git diff --check
```

Migration down/up validation must run inside `AcceptanceEvidenceTest` after explicitly asserting `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`. Do not run standalone migration/rollback commands, database clients, or persistent database writes. The benchmark is PHPUnit-owned and bounded; do not create files or start workers.

## 28. Expected Output

One enriched target coverage-provider contract, immutable status/report/coverage/evidence DTOs, four reporting/evidence services, three read operation handlers, one metadata-only evidence table/model, exact safe relationships, bounded configuration, comprehensive tests, one accepted Run Report after post-execution approval, synchronized indexes, and no client presentation, artifact capture, target I/O, or commit without separate authorization.

## 29. Operator Execution Checklist

Before:

- [x] Packs 0010–0013, Core Pack 0003, and required remediations are accepted and committed;
- [x] branch is `main`, clean, and ahead of `origin/main` only by accepted Remediation 0002 commit `1ebe0b9`;
- [x] exact provider, operation, DTO, cursor, ordering, count, and error contracts are fixed;
- [x] exact evidence schema, FK behavior, allowlist, availability, and indefinite metadata retention are fixed;
- [x] exact limits and 24,668-case benchmark bounds are fixed;
- [x] database mutation is limited to PHPUnit in-memory SQLite;
- [x] operator granted separate execution approval after reading this regenerated Pack and pre-execution report on 2026-10-10.

After:

- [x] inspect typed DTO/provider/service/handler boundaries;
- [x] inspect coverage reconciliation and shared merged-replacement evidence;
- [x] inspect migration/FKs/relationships, evidence allowlist, and sentinel scans;
- [x] confirm cursor/query/export/source bounds and benchmark evidence;
- [x] confirm focused/full tests, Pint, diff, and changed-file classification;
- [x] accept Pack execution on 2026-10-10;
- [x] authorize commit separately on 2026-10-10.

## 30. Agent Final Report

Report exact contracts, schema/FKs/indexes/retention, configuration, reconciliation counts, many-to-one mapping proof, query/page/export bounds, benchmark time/memory, evidence safety, error classifications/log behavior, migration safety proof, tests/results, formatting, diff, changed-file classification, deviations, residual risks, follow-up for Pack 0015, and whether a commit was made. Never claim artifact capture, external storage, target, browser, worker, client JSON, or UI evidence.

## 31. Review Checklist

Review DTO immutability and allowlists; operation parameter/result matching; provider ownership; full hierarchy/source reconciliation; many-to-one merged semantics; count and cursor correctness; N+1/query ceilings; report attempt/Test history; evidence reference/checksum/type/range validation; FK/delete/update behavior; indefinite audit metadata; sentinel absence; error classification; safe logging; database portability; target static validation; client-presentation independence; test value; and scope discipline.

## 32. Rollback / Safety Notes

Application rollback reverts the reporting/provider/model/config changes. Database rollback drops only `acceptance_evidence` metadata and is destructive to those references; runtime rollback is not authorized here. Pack execution may exercise down/up only in verified PHPUnit in-memory SQLite. Existing operation, batch, item, attempt, Test, Step, mapping, target, artifact, queue, and external data are not deleted or changed. No production-like database, target, browser, network, storage, queue, cache, or environment command is authorized.

## 33. Stop Conditions

Stop for an unaccepted predecessor; dirty/unrelated overlapping changes; provider/mapping behavior that contradicts Decision 0012 or Remediation 0002; inability to reconcile every executable catalog variant; unbounded query/export/source traversal; N+1 above the fixed ceilings; evidence raw content/path/URL/secret requirement; non-in-memory database mutation; real target/browser/network/storage/worker need; migration portability failure; required dependency/Core/client change; sensitive sentinel exposure; benchmark bound failure; focused regression failure not caused and fixed within scope; or any need to alter the fixed normalized choices.

## 34. Open Questions

None. Pack 0015 owns CLI prompts/rendering/JSON/export presentation and operator guidance. Web/API/MCP adapters, artifact capture/storage/download, metadata pruning, target-specific evidence generation, production retention policy, and authorization policy require later separately approved work.
