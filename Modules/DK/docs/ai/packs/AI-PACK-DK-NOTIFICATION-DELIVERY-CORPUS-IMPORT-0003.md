# AI-PACK-DK-NOTIFICATION-DELIVERY-CORPUS-IMPORT-0003 — Notification Delivery source-corpus import and deterministic mappings

Status: `accepted — operator accepted the validated Pack and Remediations 0003–0004 on 2026-10-11; all 25 chunks remain applied/replay-idempotent; runtime catalog, coverage, focused regressions, and the 689-test full suite pass; commit authorized`

Generated: `2026-10-10`

Owner: DK

Decision: accepted project Decisions 0012 and 0015; accepted `DK-DECISION-0001`.

Depends On: accepted DK Packs 0001–0002 and their accepted Runs; accepted project Packs 0010, 0014, 0015, and 0016 with their accepted Runs.

Execution / Acceptance / Commit: the operator authorized read-only corpus/current-source analysis, accepted the Decision-0017 normalization, and authorized implementation/import execution on 2026-10-10. Import apply/replay completed; the runtime and full-suite findings were corrected through accepted project Remediations 0003–0004. The operator accepted the final validated result and separately authorized commit on 2026-10-11. Shared prevention and scale hardening remain tracked by `CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007` and do not block this accepted Run.

## 1. Task ID

`AI-PACK-DK-NOTIFICATION-DELIVERY-CORPUS-IMPORT-0003`

## 2. Task Title

Import the ND source corpus and establish deterministic source-to-executable traceability.

## 3. Goal

Analyze the complete authoritative Notification Delivery source corpus, preserve every stable source identifier, assign exactly one approved coverage disposition to every source case, deduplicate equivalent behavior deterministically, and import the resulting DK-owned mappings and non-executable scenario skeletons through the accepted target-module import boundary.

The intended corpus cardinality is exactly:

```text
source use cases: 139
source cases/scenarios: 24,668
```

Every source case must receive exactly one Decision-0012 disposition:

```text
automated_full
automated_partial
merged_equivalent
excluded_no_reliable_executor
excluded_human_judgment
```

This stage creates traceable mappings and generated `not-implemented` scenario skeletons only. It does not implement executable ND steps. Bounded executable scenario groups belong to stage 14; full coverage reconciliation belongs to stage 15.

## 4. Context

Decision 0015 assigns owner-local stages 11–15 to DK/related owners. Accepted DK Pack 0001 completed stage 11 and accepted DK Pack 0002 completed stage 12. The DK Pack index therefore makes stage 13 eligible for proposal.

Project Pack 0017 remains reserved for stage 16 second-target proof and scale hardening. This work is DK-local Pack 0003; it must not be numbered project Pack 0018 or modify project Pack 0017.

Decision 0012 requires all 139 use cases and 24,668 source cases to be analyzed and traceable. The operator-approved DK reference `ND-CORPUS-MANIFEST-0001` now pins the upstream Human Acceptance Contract v1 by source-repository commit, repository-relative path, schema/version, byte sizes, and SHA-256 values. Source-only structural validation succeeds with 139 definitions, 24,668 variants, 69 fixtures, and 1,042 state/action matrix entries.

The export encodes an older TMS consumer snapshot. Consumer-aware validation against the current TMS checkout therefore still reports `HAC-V05 source-manifest/snapshots/tms contract_current_commit`; the upstream frozen manifest is not rewritten. Current-source review shows that Browser/HTTP executors, fixed Browser observations, mapping/import contracts, and DK Acceptance source were added after that snapshot. The 7,822 `manual-only` variants contain objective assertions and no definition-level touch/contrast assertion, so they are direct automation candidates. The 1,969 upstream `blocked` variants also contain objective behavior; their only shared blocker is the absence of policy-enforced sensitive capture/telemetry/retention suppression.

Temporary project Decision 0017 accepts that residual risk only while TMS is local, single-operator, wholly synthetic, fake-provider-only, and free of real/retained/shared information. Inside that complete boundary, missing generalized information-protection enforcement is not by itself a valid exclusion. Full-corpus analysis therefore produces 24,668 `automated_full` and zero partial/merged/excluded/human-judgment mappings. All tuple-aware semantic signatures are unique. The manifest records the assertion projection, 139 collision-free scenario identities/files, 25 deterministic chunks, and the structure review of `scenario-variants`. Crossing any Decision-0017 trigger blocks execution and requires a separately accepted security Pack.

The accepted generic import boundary already exists:

- `acceptance.scenarios.import` accepts typed `SourceCaseMapping` values;
- `SourceCaseMappings::all()` is the DK-owned mapping registry;
- only full/partial mappings create non-executable skeletons;
- the importer updates exact managed regions and is idempotent per mapping;
- coverage reporting supports at most 25,000 source cases;
- CLI mapping documents are limited to 1,048,576 bytes, so the normalized 24,668-entry import must use deterministic bounded chunks after whole-corpus preflight validation.

No shared runtime change is authorized. If the accepted importer cannot safely complete the normalized import without a project-owned change, stop and route that conflict to the project owner.

## 5. Related Release / Phase

Decision 0015, stage 13 of the sixteen-stage roadmap.

## 6. Related Epic / Feature / Story

DK Notification Delivery corpus intake, deterministic disposition mapping, deduplication, source traceability, and non-executable scenario skeleton generation.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0012-automated-e2e-coverage-and-traceability.md`
- `docs/project/ai/decisions/TMS-DECISION-0015-clean-acceptance-hierarchy-cutover.md`
- `docs/project/ai/decisions/TMS-DECISION-0017-temporary-local-synthetic-data-protection-deferral.md`
- `Modules/DK/docs/ai/decisions/DK-DECISION-0001-isolated-target-foundation-boundary.md`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-CONTRACT-FAKE-0002.md`
- `Modules/DK/docs/ai/runs/AI-PACK-DK-NOTIFICATION-DELIVERY-CONTRACT-FAKE-0002-run-001.md`
- `Modules/DK/docs/ai/references/ND-CORPUS-MANIFEST-0001.md`
- current source paths listed in section 10

The reference manifest owns the portable source locator, provenance, artifact hashes/sizes, aggregate counts, compatibility evidence, disposition rules/totals, assertion projection, chunk manifest, and `scenario-variants` structure review. Raw corpus files remain in the pinned upstream repository and must not be copied into this Pack or TMS.

## 8. Files to Create

No implementation or test file is authorized until this normalized Draft is separately accepted for execution.

The normalized implementation is expected to create exactly:

- `Modules/DK/tests/Unit/Acceptance/Coverage/SourceCaseMappingsTest.php`;
- `Modules/DK/tests/Feature/NotificationDeliveryCorpusImportTest.php`;
- the exact finite list of generated files under `Modules/DK/app/Acceptance/Components/NotificationDelivery/Scenarios/` for `automated_full` and `automated_partial` identities only;
- after separately approved execution, `Modules/DK/docs/ai/runs/AI-PACK-DK-NOTIFICATION-DELIVERY-CORPUS-IMPORT-0003-run-001.md`.

The exact 139-file scenario list follows. Every class is derived from the normalized TMS scenario key recorded by section 17.

Directory prefix: `Modules/DK/app/Acceptance/Components/NotificationDelivery/Scenarios/`

```text
NdComponentAttemptsShow.php
NdComponentCallbacksShow.php
NdComponentCssAdminForm.php
NdComponentCssAdminTables.php
NdComponentCssContextualHelp.php
NdComponentCssDetailPages.php
NdComponentCssNavigationSections.php
NdComponentCssProviderForm.php
NdComponentDashboard.php
NdComponentDeadLettersShow.php
NdComponentFailedJobsIndex.php
NdComponentHasadminformexperience.php
NdComponentIntegrationEventsIndex.php
NdComponentJsAdminForm.php
NdComponentJsContextualHelp.php
NdComponentJsDetailActions.php
NdComponentJsNavigationSections.php
NdComponentJsProviderForm.php
NdComponentJsSensitiveReveals.php
NdComponentMessagesShow.php
NdComponentNotificationdeliverytableabstract.php
NdComponentOperationsHealth.php
NdComponentOperationsQueue.php
NdComponentProviderTemplatesProviderTemplateTextReadonly.php
NdComponentProvidersPartialsDropdownOverflowFix.php
NdComponentProvidersPartialsProviderFormActions.php
NdComponentProvidersPartialsProviderFormErrorSummary.php
NdComponentProvidersPartialsTestProviderActions.php
NdComponentProvidersPartialsTestProviderInfo.php
NdComponentProvidersSelectDefinition.php
NdComponentRoutingRulesPreview.php
NdComponentServiceClientsTokenCreated.php
NdComponentSettingsResetReview.php
NdComponentSettingsShow.php
NdComponentSharedContextualHelp.php
NdComponentSharedDetailPageHeader.php
NdComponentSharedDetailRelatedRecord.php
NdComponentSharedDetailSection.php
NdComponentSharedFormsActions.php
NdComponentSharedFormsErrorSummary.php
NdComponentSharedMethodApplicabilityController.php
NdComponentSharedNotificationActionFeedback.php
NdComponentSharedSelectProvider.php
NdComponentSharedSensitiveReveal.php
NdComponentSharedStatusBadge.php
NdComponentSourceServicesIndex.php
NdComponentSourceServicesReview.php
NdComponentSourceServicesShow.php
NdComponentTablesFilter.php
NdComponentTablesPartialsActionDropdownItem.php
NdComponentTablesPartialsRowActions.php
NdComponentUsageIndex.php
NdDashboard.php
NdDeliveryAttemptsIndex.php
NdDeliveryAttemptsRaw.php
NdDeliveryAttemptsShow.php
NdDeliveryAuditIndex.php
NdDeliveryCallbacksIndex.php
NdDeliveryCallbacksRaw.php
NdDeliveryCallbacksShow.php
NdDeliveryDashboard.php
NdDeliveryDeadLettersClose.php
NdDeliveryDeadLettersIndex.php
NdDeliveryDeadLettersRetry.php
NdDeliveryDeadLettersShow.php
NdDeliveryMessagesCancel.php
NdDeliveryMessagesIndex.php
NdDeliveryMessagesRetry.php
NdDeliveryMessagesRevealContent.php
NdDeliveryMessagesShow.php
NdDeliveryOperationsFailedJobs.php
NdDeliveryOperationsHealth.php
NdDeliveryOperationsQueue.php
NdDeliverySettingsEdit.php
NdDeliverySettingsReset.php
NdDeliverySettingsResetReview.php
NdDeliverySettingsUpdate.php
NdDeliveryUsageIndex.php
NdIntegrationEventsIndex.php
NdIntegrationEventsUpdate.php
NdProviderMethodsAjax.php
NdProviderMethodsCreate.php
NdProviderMethodsDisable.php
NdProviderMethodsEdit.php
NdProviderMethodsIndex.php
NdProviderMethodsStore.php
NdProviderMethodsTestForm.php
NdProviderMethodsTestSend.php
NdProviderMethodsUpdate.php
NdProvidersAjax.php
NdProvidersCreate.php
NdProvidersEdit.php
NdProvidersIndex.php
NdProvidersSetDefault.php
NdProvidersStore.php
NdProvidersTestForm.php
NdProvidersTestSend.php
NdProvidersToggleStatus.php
NdProvidersUpdate.php
NdRoutingRulesActivate.php
NdRoutingRulesAjax.php
NdRoutingRulesCreate.php
NdRoutingRulesDeactivate.php
NdRoutingRulesEdit.php
NdRoutingRulesIndex.php
NdRoutingRulesPreview.php
NdRoutingRulesStore.php
NdRoutingRulesUpdate.php
NdSenderLinesAjax.php
NdSenderLinesCreate.php
NdSenderLinesDisable.php
NdSenderLinesEdit.php
NdSenderLinesIndex.php
NdSenderLinesSetDefault.php
NdSenderLinesStore.php
NdSenderLinesTestForm.php
NdSenderLinesTestSend.php
NdSenderLinesUpdate.php
NdServiceClientsAjax.php
NdServiceClientsCreate.php
NdServiceClientsIndex.php
NdServiceClientsRevoke.php
NdServiceClientsRotate.php
NdServiceClientsStore.php
NdSourceServicesIndex.php
NdSourceServicesReview.php
NdSourceServicesShow.php
NdSourceServicesUpdate.php
NdTemplatesAjax.php
NdTemplatesCreate.php
NdTemplatesDisable.php
NdTemplatesEdit.php
NdTemplatesIndex.php
NdTemplatesStore.php
NdTemplatesTestForm.php
NdTemplatesTestSend.php
NdTemplatesUpdate.php
NdUpstreamNavigation.php
NdUpstreamSession.php
```

## 9. Files to Edit

No source or test file is authorized until this Pack is accepted for execution.

During an accepted execution, the intended exact edits are:

- `Modules/DK/app/Acceptance/Coverage/SourceCaseMappings.php` — managed mapping region only;
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php` — managed suite/scenario/variant/resolution regions only;
- `Modules/DK/app/Acceptance/DKAcceptanceApp.php` — catalog version only, from `v2` to `v3`;
- `Modules/DK/tests/Unit/DKAcceptanceAppTest.php` — exact catalog/version/count assertions only;
- `Modules/DK/tests/Feature/NotificationDeliveryComponentIntegrationTest.php` — corpus/catalog wiring assertions only;
- `tests/Feature/DKModuleBootstrapTest.php` — exact `v3` and non-empty catalog assertions only;
- `Modules/DK/docs/ai/references/ND-CORPUS-MANIFEST-0001.md` — execution verification/lifecycle evidence only; do not alter source hashes or mapping rules without a new review;
- `Modules/DK/docs/ai/references/REFERENCES-INDEX.md` — lifecycle/status synchronization only;
- this Pack — lifecycle/status and normalization synchronization only;
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`;
- `Modules/DK/docs/ai/runs/RUNS-INDEX.md` — only after an approved Run exists.

`Modules/DK/tests/Feature/DKTargetFoundationIntegrationTest.php` has no version/count assertion affected by this import and must remain byte-for-byte unchanged. No other root/project/Core file may be edited.

## 10. Files to Read / Reference

- mandatory repository and DK startup routers, entry, manifest, and profiles;
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`;
- exact references in section 7;
- `Modules/DK/docs/ai/references/ND-CORPUS-MANIFEST-0001.md`, then the pinned upstream `README.md`, `source-manifest.json`, `scenario-definitions.json`, `scenario-variants.json`, their schemas, and `evidence-policy.json` only;
- `app/Acceptance/Coverage/Data/SourceCaseMapping.php`;
- `app/Acceptance/Coverage/Enums/CoverageDisposition.php`;
- `app/Acceptance/Modules/NwidartTargetModuleCreator.php`;
- `app/Acceptance/Modules/TargetModuleValidator.php`;
- `app/Acceptance/Reporting/CoverageTraceabilityService.php`;
- `app/Console/Acceptance/AcceptanceCommand.php`;
- `app/Console/Commands/ImportAcceptanceScenariosCommand.php`;
- `config/acceptance.php`;
- `Modules/DK/app/Acceptance/DKAcceptanceApp.php`;
- `Modules/DK/app/Acceptance/Coverage/SourceCaseMappings.php`;
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php`;
- nearby DK and generic import/coverage tests named by the normalized test plan;
- applicable Pack-generation, scope, execution, error/logging, testing, reporting, Git, commenting, and documentation-maintenance rules.

Resolve the accepted corpus only through the pinned source-repository commit and repository-relative root in the manifest. Read only the minimum named artifacts required for mapping. Do not bulk-read external systems, unrelated Packs/Runs, environment files, credentials, production exports, or target data.

### Project / Framework Source Reference Rule

Use the current project/DK profiles and accepted import/coverage source as implementation truth. Resolve version-sensitive PHPUnit/Laravel behavior only when the normalized tests require it. Do not add dependencies or replace the accepted importer with ad hoc filesystem writes.

## 11. Configuration / Settings Requirements

No product configuration, environment variable, Admin Setting, secret setting, queue/cache/log setting, or database-backed setting is required.

The existing read-only constraints are:

```text
config('acceptance.reporting.max_source_cases') = 25,000
CLI mapping document maximum = 1,048,576 bytes
```

The corpus contains exactly 24,668 cases and remains within the reporting ceiling. Mapping input is split into the 25 deterministic chunks recorded in the manifest, each strictly below the CLI byte limit, with no split, duplicate, or reordered source record. Chunks are transient execution inputs regenerated from the pinned source and exact projection; they are not committed as a second registry.

No `.env` or `.env.example` read/edit is permitted. No operator setup may contact a target or external service.

## 12. Do Not Change

Do not change shared application/Core contracts, importer/validator/coverage services, CLI size limits, configuration, dependencies, module metadata, service providers, target profile/adapter, ND fake contracts or behavior, routes, database, migrations, seeders, UI, API, MCP, queue jobs/workers, environment files, accepted history, real target systems, or external services.

Do not create executable steps, real provider/SMS behavior, callbacks, selectors, URLs, credentials, payloads, personal data, production values, browser/HTTP/network actions, or subjective human-test records.

Do not assign dispositions from counts, names, or assumptions. Do not delete or omit a source case because it is duplicated, excluded, unsupported, or subjective; preserve it as a traceable mapping.

## 13. Clarification Questions Before Implementation

All corpus-analysis questions are resolved by the manifest:

1. Human Acceptance Contract v1 at the pinned upstream commit/path is authoritative.
2. Notification Delivery owns the frozen export; DK owns the TMS projection.
3. `variant_id` remains the source-case ID; parent `scenario_key`, `suite`, the 16-hex variant suffix, and content-addressed assertion IDs provide stable traceability.
4. Raw corpus and transient chunk files are not committed. The safe manifest and generated DK source are the retained derivatives.
5. Current-capability review plus the temporary Decision-0017 synthetic/local risk boundary, not direct status renaming, produces 24,668 full and zero excluded dispositions.
6. Tuple-aware comparison proves zero equivalent duplicates; the 139 normalized scenario keys/classes are collision-free.
7. Catalog successor is `v3`; the exact generated files and 25 chunks are fixed.

There is no remaining clarification question. Operator acceptance and execution authorization remain separate gates. Any later change to the pinned source, current mapping/import contracts, or executor/evidence capability invalidates this normalization and requires a new review.

## 14. Multilingual / Translation Requirements

No multilingual, translation, locale, or RTL/LTR changes required. Source text is analysis input only and must not become runtime-visible text. Stable IDs, enum values, and error codes remain language-neutral.

## 15. UI / Admin UI Requirements

No UI changes required. No route, view, menu, form, table, action, dashboard, asset, permission, screenshot, or visual review is in scope.

## 16. Operator Documentation Requirements

No operator guide update is required. The existing Persian Acceptance CLI guide already documents `acceptance:scenarios:import`, the 1 MiB mapping limit, default dry-run, confirmed apply, and coverage command. Corpus-specific identities/chunks belong in this Pack and manifest, not the reusable CLI guide.

## 17. Implementation Rules

### Corpus intake and provenance

1. Work only from the operator-approved immutable snapshot; never query a real target.
2. Verify byte size and SHA-256 before analysis and again before apply.
3. Validate the declared schema before deriving mappings.
4. Reject missing/duplicate use-case IDs, source-case IDs, assertion IDs, malformed relationships, unsafe values, and count mismatches without modifying source.
5. Record only safe provenance and aggregate counts; omit raw sensitive/free-form corpus content from Pack, Run, logs, and final reports.

### Deterministic mapping and deduplication

1. Sort source cases by binary `variant_id`; preserve it unchanged as `sourceCaseId`.
2. Project assertion IDs exactly as specified by the manifest, deduplicate them, and sort them in binary order.
3. Map every upstream `not-implemented`, `manual-only`, and `blocked` variant to `automated_full`, with all projected IDs under `coveredAssertions`, only while every Decision-0017 boundary condition holds.
4. Treat upstream automation status as historical planning metadata, not a TMS disposition. The former `sensitive-no-capture` reason is accepted temporary residual risk, not an exclusion reason, inside the complete Decision-0017 boundary.
5. Produce exactly 24,668 full and zero partial, merged, no-reliable-executor, or human-judgment mappings.
6. For a direct mapping use Component `notification-delivery`, the parent definition `suite`, source `scenario_key` with dots replaced by hyphens, and `v.` plus the source 16-hex suffix as Variant.
7. Do not merge variants: all tuple-aware signatures are unique, and ignoring `tuple_values` would incorrectly collapse distinct declared dimensions.
8. No excluded or replacement mapping exists in this import. No replacement identity, chain, or cycle exists.

### Preflight, chunking, and apply

1. Regenerate and validate all 24,668 mappings together before the first write: exact disposition/assertion totals, source-ID/direct-identity uniqueness, zero replacements, 139 normalized keys/classes/files, and zero collision.
2. Recreate the exact 25 compact JSON chunks from the manifest and require every record count, byte count, first/last ID, and SHA-256 to match before dry-run.
3. Run every chunk through dry-run in order and aggregate its exact `FileChange` plan before apply.
4. Confirm no planned path falls outside normalized sections 8–9 and no existing non-managed content will be overwritten.
5. Apply chunks in the same order. A failed chunk stops the run; already applied chunks remain safely retryable and must be reported. Do not continue past failure.
6. Re-run the same chunks and require zero created/updated changes to prove idempotency.
7. Run target-module validation and complete coverage reporting only after all chunks apply.
8. Generated scenario classes remain `not-implemented`, contain no Steps or target behavior, and are not executed in this stage.

## 18. Architecture Constraints

- Preserve the only supported hierarchy: App → Component → Suite → Scenario → Variant → Step.
- ND remains an internal Component of DK, not a module or documentation owner.
- DK owns product-specific mappings; shared TMS owns generic mapping/import/reporting contracts and remains unchanged.
- Use the accepted managed-region importer and validator. No hand-written bypass, fallback format, legacy identity, or second mapping registry.
- Preserve the Pack-0002 deterministic no-real-SMS boundary.
- Stage 13 must not absorb stage-14 scenario behavior or stage-15 final reconciliation/remediation.

## 19. Validation Rules

- Corpus hash/schema/counts match the accepted manifest exactly.
- Exactly 139 unique source definitions and 24,668 unique source cases are represented.
- Dispositions equal 24,668 full and 0 partial, merged, no-reliable-executor, or human-judgment.
- Exactly 150,724 unique assertion IDs are collision-free; mappings carry 21–64 IDs, totaling 767,744 covered and 0 uncovered references.
- Direct identities total 24,668 across exactly 139 scenarios/files.
- Every executable mapping resolves to exactly one catalog tuple; every catalog variant has exactly one direct mapping once imported.
- Every merged replacement resolves directly; no missing target, chain, or cycle exists.
- Generated class names and hierarchy keys are collision-free.
- Every chunk is below the CLI byte ceiling and matches its recorded hash/count.
- Dry-run plans are deterministic; apply followed by replay is idempotent.
- DK catalog version is changed exactly once from `v2` to `v3`.
- No real target/provider/browser/network/queue/database/config/environment behavior occurs.

## 20. Security Rules

- Only synthetic/sanitized source material is permitted.
- The temporary Decision-0017 exception applies only to a local single-operator TMS, inert synthetic values, the fake ND Provider/simulated boundaries, no real target/provider/data, no retained/shared real information, and no remote/shared/external executor. If any condition is false or uncertain, stop before import or execution.
- This Pack does not claim TMS has information-protection enforcement. It neither adds nor enables screenshot/video/trace/HAR/DOM/console/network capture, telemetry, evidence retention, remote execution, or shared access.
- Never store, log, display, or commit credentials, tokens, URLs, selectors, message bodies, phone-like values, personal identifiers, Authorization headers, cookies, sessions, raw production payloads, or environment values.
- Reasons and assertion IDs must be bounded and safe; do not copy raw source prose when a stable safe identifier is sufficient.
- Corpus paths and absolute filesystem paths must not appear in runtime output, logs, Run reports, or final reports.
- Use placeholders for all negative-test sentinels and prove they are absent from committed mappings and generated skeletons.

## 21. Error Handling / Logging / Traceability Requirements

### Error Handling Impact

This Pack depends on existing `acceptance_source_mapping_invalid`, `acceptance_source_mapping_duplicate`, `acceptance_coverage_mapping_invalid`, `acceptance_coverage_incomplete`, `acceptance_export_limit_exceeded`, `target_module_managed_region_invalid`, `target_module_path_collision`, and normalized operation failure behavior. It must not add a new shared error code or exception class.

Every validation/import failure is permanent and non-retryable until the corpus/mapping/chunk defect is corrected, requires operator action, preserves already accepted source, and must not expose raw input. No HTTP/API/UI contract applies.

### Logging Requirements

Do not add product logging. Existing operation logging may include only approved safe context such as operation/correlation ID, module/app/component key, dry-run flag, aggregate change counts, and stable error code. Omit corpus content, mappings, assertion/reason text, source IDs in bulk, absolute paths, chunk content, and sensitive sentinels.

### Traceability Requirements

Traceability is source-owned and deterministic:

```text
corpus snapshot SHA-256
→ use-case ID
→ source-case ID
→ assertion IDs
→ disposition/reason
→ direct or replacement hierarchy tuple
→ generated skeleton when direct
→ coverage report row
```

The corpus manifest records pinned source artifacts, hashes, aggregate counts, compatibility evidence, exact assertion/disposition projections, and all 25 chunk hashes/counts. `SourceCaseMappings::all()` remains the executable source of mapping truth. No database persistence is introduced.

### Error Handling Test Requirements

Tests must prove count mismatch, duplicate IDs, invalid disposition details, unresolved/recursive replacements, duplicate direct identities, class collisions, managed-region corruption, oversized/non-manifest chunk, and sensitive sentinel input fail before the affected write and return only stable safe codes.

### Required Follow-up Updates

- Stage 14 implements the first independently bounded executable ND scenario group.
- Stage 15 implements remaining groups and final full-coverage reconciliation.
- Before any Decision-0017 boundary trigger, a separately accepted project security Pack must implement and validate the applicable information-protection controls.
- Regenerate project Pack 0017 only after accepted stages 13–15.
- If whole-corpus preflight or safe chunk execution requires a shared change, create a project-owned Change/Remediation record; do not expand this Pack.

## 22. Data Model / Migration / Relationship Requirements

No data model, migration, relationship, snapshot-column, index, constraint, seed, database backfill, or rollback change required. Corpus provenance is a documentation/source manifest, not a database record. No snapshot fields are required.

## 23. Commenting Requirements

Use existing generated/source conventions. Do not add per-mapping comments or copy corpus prose into PHP. Add a comment only where a non-obvious invariant cannot be expressed by type/name and the applicable commenting rules require it.

## 24. Testing Requirements

Tests must follow nearby PHPUnit conventions, use fixed synthetic fixtures, assert known exact values with strict comparisons, control time/randomness/I/O, and detect distinct defects. No test may contact a target or network, sleep, depend on execution order, or use production-like data.

Use a small representative fixture for branch/error tests. Load the generated production registry for the exact 139/24,668 assertions; do not duplicate 24,668 literals in a test method or second fixture. The isolated importer test may generate a deterministic bounded scale fixture, but must label it synthetic and assert the manifest totals/hashes independently from that fixture.

## 25. Acceptance Checklist

- [x] authoritative corpus identity is indexed and approved for read-only analysis;
- [x] manifest records schema/version, byte sizes, SHA-256 values, 139/24,668 counts, upstream status totals, and upstream-only storage classification;
- [x] all exact create/edit paths are normalized before execution;
- [x] every source case has exactly one deterministic proposed disposition;
- [x] mapping totals and per-disposition totals reconcile to 24,668;
- [x] Decision 0017 records the temporary synthetic/local risk acceptance, retained prohibitions, and mandatory stop/review triggers;
- [x] direct identities and generated class names are valid/deterministic; no merge exists;
- [x] analysis preflight proves whole-corpus IDs, assertions, counts, identities, classes, files, and chunks;
- [x] every recorded chunk is regenerated, hash-verified, dry-run, applied in order, and replay-idempotent;
- [x] all direct mappings resolve through the versioned DK catalog;
- [x] generated skeletons remain non-executable and contain no Steps/target behavior;
- [x] sensitive/no-I/O scans pass;
- [x] focused and affected regression tests pass;
- [x] Run/reference/Pack indexes are synchronized after approved execution;
- [x] no commit occurs without separate authorization.

## 26. Tests to Add

### `SourceCaseMappingsTest`

- Purpose: prove complete deterministic owner-local mapping semantics.
- Behavior proven: exact source count, exact disposition totals, unique stable IDs, direct identity uniqueness, replacement resolution, no chains/cycles, safe bounded reasons/assertions, and deterministic ordering.
- Main assertions: 24,668 mappings; disposition vector `24668/0/0/0/0`; 24,668 direct identities; 139 scenario keys; 150,724 unique assertion IDs; 767,744 covered and 0 uncovered references; 21–64 IDs per mapping; strict tuple invariants; binary ordering; no forbidden sentinel leakage; Decision-0017 boundary documented without claiming protection.
- Required setup: accepted mapping registry plus small isolated invalid mapping fixtures.
- Expected side effects: none.
- Negative/error cases: malformed/duplicate/unresolved/cyclic/unsafe mappings.
- Related acceptance criteria: mapping and traceability checklist items.

### `NotificationDeliveryCorpusImportTest`

- Purpose: prove the accepted importer can plan, apply, validate, and replay the normalized corpus without crossing the DK boundary.
- Behavior proven: deterministic bounded chunk order, identical dry-run plans, managed-region-only writes, exact generated file set, `not-implemented` skeletons, catalog resolution, replay idempotency, and zero external side effects.
- Main assertions: 25 exact chunk hashes/counts; 139 exact generated files; catalog `v3`; 24,668 catalog variants and direct mappings; 24,668 total source mappings; target-module validation succeeds; replay produces zero creates/updates; framework fakes report no notification/queue/event/HTTP activity.
- Required setup: isolated copy of the DK module structure and normalized safe chunk fixtures; no shared mutable fixture.
- Expected side effects: only the isolated test module filesystem changes.
- Negative/error cases: bad hash/order, oversized chunk, mid-sequence invalid mapping, collision, corrupted managed marker, retry after a completed prefix.
- Related acceptance criteria: preflight/chunk/apply/idempotency checklist items.

## 27. Tests to Run

```text
$files = @(
  'Modules/DK/app/Acceptance/Coverage/SourceCaseMappings.php',
  'Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php',
  'Modules/DK/app/Acceptance/DKAcceptanceApp.php',
  'Modules/DK/tests/Unit/Acceptance/Coverage/SourceCaseMappingsTest.php',
  'Modules/DK/tests/Feature/NotificationDeliveryCorpusImportTest.php',
  'Modules/DK/tests/Unit/DKAcceptanceAppTest.php',
  'Modules/DK/tests/Feature/NotificationDeliveryComponentIntegrationTest.php',
  'tests/Feature/DKModuleBootstrapTest.php'
) + (Get-ChildItem -LiteralPath 'Modules/DK/app/Acceptance/Components/NotificationDelivery/Scenarios' -Filter '*.php').FullName
$files | ForEach-Object { php -l $_; if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE } }
vendor/bin/pint --test Modules/DK/app/Acceptance/Coverage/SourceCaseMappings.php Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php Modules/DK/app/Acceptance/Components/NotificationDelivery/Scenarios Modules/DK/app/Acceptance/DKAcceptanceApp.php Modules/DK/tests/Unit/Acceptance/Coverage/SourceCaseMappingsTest.php Modules/DK/tests/Feature/NotificationDeliveryCorpusImportTest.php Modules/DK/tests/Unit/DKAcceptanceAppTest.php Modules/DK/tests/Feature/NotificationDeliveryComponentIntegrationTest.php tests/Feature/DKModuleBootstrapTest.php
php artisan test --compact Modules/DK/tests/Unit/Acceptance/Coverage/SourceCaseMappingsTest.php Modules/DK/tests/Feature/NotificationDeliveryCorpusImportTest.php Modules/DK/tests/Unit/DKAcceptanceAppTest.php Modules/DK/tests/Feature/NotificationDeliveryComponentIntegrationTest.php tests/Feature/DKModuleBootstrapTest.php
php artisan test --compact tests/Unit/TargetModuleCreatorTest.php tests/Unit/TargetModuleValidatorTest.php tests/Unit/CoverageTraceabilityServiceTest.php tests/Feature/TargetModuleOperationServiceTest.php
php artisan acceptance:app:validate DK --json
php artisan acceptance:coverage --app=dk --json
git diff --check
```

Run source scans for forbidden I/O, secrets/personal values, executable Step bodies, and modifications outside normalized scope. Do not run the full suite, migration, seeder, worker, browser, server, dependency, cache/config mutation, or network command unless separately authorized.

## 28. Expected Output

- one indexed safe corpus manifest with verified provenance and hashes;
- exactly 24,668 DK-owned source mappings covering 139 definitions;
- dispositions `24668/0/0/0/0` in Decision-0012 order;
- exactly 139 collision-free non-executable ND scenario skeletons and 24,668 catalog variants;
- DK catalog version advanced from `v2` to `v3`;
- complete direct/replacement traceability with no missing source case;
- deterministic chunk plan and idempotent replay evidence;
- focused tests and validation evidence;
- no real target, SMS/provider, callback endpoint, browser, network, queue worker, database, configuration, UI, API, MCP, or executable scenario behavior;
- after approved execution, one Run Report and synchronized owner indexes.

## 29. Operator Execution Checklist

### Before AI Execution

- Resolve the pinned source checkout and verify every manifest byte size and SHA-256 without copying the raw corpus into TMS.
- Accept this normalized Pack and the manifest's exact `24668/0/0/0/0` disposition vector, assertion projection, 139 files, catalog `v3`, and 25 chunks.
- Confirm every Decision-0017 boundary condition still holds; if not, do not authorize execution.
- Separately authorize execution; analysis approval does not authorize import/apply.

### After AI Execution

- Compare the Run totals/hashes with the accepted manifest.
- Inspect representative direct mappings from former `not-implemented`, `manual-only`, and `blocked` groups; partial/merged/excluded/human totals must remain zero.
- Verify all generated scenarios are `not-implemented`, all chunks replay unchanged, and coverage reports the complete source total.
- Verify no real target/sensitive/external-I/O behavior entered the diff.

## 30. Agent Final Report

Follow the reporting and documentation-maintenance rules. Report corpus logical identity/schema/size/hash, exact counts, disposition totals, deduplication totals, generated hierarchy/file totals, chunk hashes/counts, catalog version, dry-run/apply/replay results, coverage reconciliation, tests/commands, safe-output scans, scope, deviations, partial-apply recovery if any, lifecycle/index updates, and required follow-ups. Never include raw corpus content, sensitive values, absolute corpus paths, or claim executable scenario coverage.

## 31. Review Checklist

- Review owner/roadmap numbering and stage boundaries.
- Review corpus authority, safety, immutability, schema, hash, and exact counts.
- Review every disposition rule and evidence requirement.
- Review direct identity uniqueness, deterministic grouping, merged replacement resolution, and class collisions.
- Review whole-corpus preflight, chunk sizing/order/hash, failure stop, retry, and replay idempotency.
- Review managed-region-only edits and exact file scope.
- Review catalog/coverage completeness without treating skeletons as executable.
- Review test defect-detection value, isolation, scale-fixture determinism, and no external side effects.
- Review documentation/index synchronization and separate execution/acceptance/commit gates.

## 32. Rollback / Safety Notes

Before apply, preserve a source-only hash manifest of every allowed existing file. Rollback removes only Pack-created scenario/test/Run files, restores only Pack-managed regions and catalog assertions to their pre-Pack hashes, and synchronizes indexes. Preserve `ND-CORPUS-MANIFEST-0001` unless its reference lifecycle is separately changed. Never use broad reset/clean commands.

If apply stops after a completed chunk prefix, do not rollback automatically and do not continue. Record applied chunk hashes and Git diff, then either retry the same corrected plan idempotently or perform an operator-approved targeted rollback. No database, queue, cache, provider, callback, target, or external cleanup exists.

## 33. Stop Conditions

Stop and report if:

- the manifest cannot resolve the pinned source commit/path, any byte size or hash differs, or the corpus is mutable, unsafe, incomplete, or fails schema/count validation;
- this Pack has not been separately accepted and authorized for execution;
- any Decision-0017 condition is false, uncertain, or cannot be proven before execution, including real/non-synthetic data, a second user, shared/remote access, real target/provider, retained/shared information, or an external executor;
- any source case lacks a stable ID, assertion trace, or defensible disposition;
- an exclusion lacks required evidence or a merged replacement is indirect/unresolved;
- whole-corpus preflight, path containment, managed-region validation, dry-run determinism, class collision, chunk hash/size, or replay idempotency fails;
- the import requires a shared project/Core source/config/limit/dependency change;
- stage-14 executable behavior or stage-15 remediation is required;
- any real target, credential, URL, selector, payload, personal data, browser, HTTP/network, queue worker, database, environment, or production operation becomes necessary;
- unrelated working-tree changes overlap normalized files;
- a destructive, dependency-changing, cache-changing, database-changing, long-running, or external-network command appears necessary.

## 34. Open Questions

No corpus-analysis or normalization question remains. The operator chose to retain the safe applied import and accepted the exact descriptor-import and generic CLI test-isolation corrections through Decisions 0018–0019 and project Remediations 0003–0004 without rewriting this executed Pack's historical scope. Runtime catalog, target-module validation, coverage, focused tests, the 689-test full suite, scoped Pint, lint, and diff checks pass. The accepted Run Report is recorded and commit is authorized. Shared importer/validator prevention and scale/format hardening remain open in Change Request 0007 as non-blocking follow-up work before the applicable later lifecycle gate.
