# REMEDIATION-PACK-TMS-COVERAGE-MERGE-0002

Status: `accepted — operator accepted run 001 and authorized the remediation commit on 2026-10-10`

Generated: `2026-10-10`

Owner: TMS project

## 1. Task ID

`REMEDIATION-PACK-TMS-COVERAGE-MERGE-0002`

## 2. Task Title

Correct many-to-one merged-equivalent source mapping validation.

## 3. Related Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-COVERAGE-MERGE-0005.md`

Decision source: `docs/project/ai/decisions/TMS-DECISION-0012-automated-e2e-coverage-and-traceability.md`.

## 4. Goal

Permit multiple distinct `merged_equivalent` source cases to link to one existing executable replacement while retaining every other Pack 0010 mapping invariant.

## 5. Current Problem

The accepted mapping importer and static validator both build a set of replacement identities and reject the second merged entry that points to the same tuple. This makes Decision 0012 many-to-one reconciliation impossible and blocks Pack 0014 coverage reporting.

## 6. Required Change

- Remove replacement-identity uniqueness enforcement from in-memory import validation.
- Remove replacement-identity uniqueness enforcement against existing generated mappings.
- Remove replacement-identity uniqueness enforcement from static target-module validation.
- Keep source-case ID and direct executable-identity uniqueness unchanged.
- Add focused tests for same-import and later-import many-to-one merged mappings and validator acceptance.

## 7. Files to Read

- repository startup files, project profile and applicable shared rules;
- Decision 0012, accepted Pack/Run 0010, Change Request 0005;
- every file in sections 8–9.

Do not read `.env`, unrelated modules, target corpora, vendor source, or unrelated lifecycle records.

## 8. Files to Edit

- `app/Acceptance/Modules/NwidartTargetModuleCreator.php`
- `app/Acceptance/Modules/TargetModuleValidator.php`
- `tests/Unit/TargetModuleCreatorTest.php`
- `tests/Unit/TargetModuleValidatorTest.php`
- `docs/project/ai/remediations/REMEDIATION-PACK-TMS-COVERAGE-MERGE-0002.md`
- `docs/project/ai/remediations/REMEDIATIONS-INDEX.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-COVERAGE-MERGE-0005.md`
- `docs/project/ai/changes/CHANGES-INDEX.md`
- `docs/project/ai/packs/AI-PACK-TMS-QUERY-REPORT-EVIDENCE-0014.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/runs/RUNS-INDEX.md` after acceptance.

## 9. Files to Create

- `docs/project/ai/runs/REMEDIATION-PACK-TMS-COVERAGE-MERGE-0002-run-001.md` after post-execution approval and acceptance.

## 10. Do Not Change

Do not change `SourceCaseMapping` fields or dispositions, error-code names, operation request/result contracts, generated module layout, hierarchy rules, runtime catalog/execution, database schema/data, config, dependencies, `.env`, Core, target modules, API/Web/MCP, UI, or accepted Pack/Run history.

## 11. Configuration / Settings Impact

```text
Configuration / Settings Impact: No
Config Files Changed: No
Environment Variables Required: No
Admin Settings Changed: Not applicable
External Integration / Secret Settings Changed: No
Queue / Cache / Logging Settings Changed: No
Test / Development Setup Required: Yes — isolated temporary module root only
Operator Manual Setup Required: No
```

No configuration or settings impact. No follow-up required.

## 12. Multilingual / Translation Impact

No multilingual, translation, visible-text, validation-message, or RTL/LTR impact.

## 13. UI / Admin UI Impact

No UI or Admin UI impact.

## 14. Implementation Rules

- Delete only the three replacement-uniqueness checks; do not weaken source ID, executable identity, tuple completeness, hierarchy reference, managed-region, or collision validation.
- Keep `acceptance_source_mapping_duplicate` unchanged for repeated source IDs and repeated direct executable identities.
- A repeated replacement is valid only when each `SourceCaseMapping` independently satisfies the existing merged disposition requirements and the replacement resolves to an existing hierarchy tuple.
- Preserve dry-run/write parity and idempotent re-import of an exact source mapping.
- Tests use a unique temporary module root and synthetic identifiers only.

## 15. Architecture Constraints

Target owners continue to own source mappings; project code validates their generic contract. The remediation changes no owner boundary, provider interface, runtime identity, or operation/client contract.

## 16. Data Model / Migration / Relationship Impact

No database, model, migration, relationship, index, snapshot, backfill, retention, or data-loss impact.

## 17. Validation Rules

- Import one automated replacement and at least two distinct merged source cases that reference it.
- Prove acceptance both in one import request and when a later import adds another merged entry.
- Prove the generated mapping file passes `TargetModuleValidator` without loading target code.
- Preserve existing duplicate-source, duplicate-direct-identity, invalid-replacement, idempotency, dry-run/write, rollback, and path-safety tests.
- Run scoped formatting and `git diff --check`; no target/network/browser/database/queue operation is permitted.

## 18. Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: Not applicable
UI / Admin UI Error Impact: Not applicable
Stable Error Code Impact: Yes — existing code usage is narrowed to its intended cases
Exception Handling Impact: No
External Error Normalization Impact: Not applicable
Callback / Inbound-event Error Impact: Not applicable
Retry / Permanent Failure Impact: No
Message / Attempt Status Impact: Not applicable
Structured Logging Impact: No
Traceability Impact: Yes — multiple source IDs retain distinct links to one replacement
Sensitive Data Impact: No
Error-specific Test Impact: Yes
Operator Documentation Impact: No
```

Existing defective behavior: the second valid merged source mapping is rejected with `acceptance_source_mapping_duplicate` solely because its replacement tuple was already referenced.

Required corrected behavior: shared replacement tuples are accepted; repeated source IDs and direct automated identities still return `acceptance_source_mapping_duplicate`. No new code, exception, classification, log event, message, API contract, or sensitive-data behavior is introduced.

Backward compatibility is additive for valid Decision 0012 input. Existing stored data, logs, API clients, database records, operation attempts, and history are unaffected. No migration, cleanup, reprocessing, or credential action is required.

## 19. Tests to Add

Extend the two existing focused test files:

- `TargetModuleCreatorTest`: same-import and later-import many-to-one merged references succeed; generated mappings remain distinct and no extra scenario is generated.
- `TargetModuleValidatorTest`: a generated file containing two merged mappings to one valid replacement passes; duplicate source/direct identity regressions remain covered.

## 20. Tests to Run

Only after execution approval:

```text
php artisan test --compact tests/Unit/TargetModuleCreatorTest.php tests/Unit/TargetModuleValidatorTest.php tests/Unit/SourceCaseMappingTest.php
php artisan test --compact tests/Feature/TargetModuleOperationServiceTest.php
vendor/bin/pint --dirty --format agent
git diff --check
```

Tests may write only inside their verified temporary module roots. Do not run persistent module creation or any database, target, browser, network, queue, dependency, cache, or environment command.

## 21. Acceptance Checklist

- [x] many-to-one merged replacements work in one and later imports;
- [x] static validation accepts the generated mapping set;
- [x] source ID and direct executable identity uniqueness remain enforced;
- [x] invalid replacement references remain rejected;
- [x] no new scenario is created for merged entries;
- [x] focused/regression tests, scoped Pint and diff check pass;
- [x] only declared implementation and governance paths change.

Execution evidence on 2026-10-10:

- focused unit command: 14 tests passed with 63 assertions;
- operation-service feature command: 2 tests passed with 16 assertions;
- `vendor/bin/pint --dirty --format agent`: passed;
- `git diff --check`: passed;
- no persistent target, database, network, browser, queue, dependency, cache, or environment operation was run.

## 22. Rollback / Safety Notes

Revert the two source edits and focused tests. The remediation touches no persistent module, database, configuration, dependency, target, or external system. Test temporary roots must be verified and removed by existing teardown logic.

## 23. Operator Review Checklist

Before:

- [x] Decision 0012 and the Pack 0010/source conflict were identified.
- [x] Change Request 0005 records the approved correction.
- [x] scope is limited to replacement multiplicity; all other mapping invariants remain fixed.
- [x] branch is `main`, synchronized with `origin/main`, and clean before documentation preparation.
- [x] operator confirmed execution after reading this Remediation Pack and gate.

After:

- [x] inspect the three removed replacement-uniqueness checks;
- [x] inspect same-import/later-import and validator regression evidence;
- [x] confirm focused tests, Pint, diff and changed-file classification;
- [x] accept or reject the remediation result;
- [x] authorize commit separately before Pack 0014 regeneration/execution.

## 24. Operator Documentation Impact

No operator-facing behavior changed and no operator guide or screenshot update is required.

## 25. Agent Final Report

Report the exact checks removed, preserved invariants, tests added/run/results, temporary-root isolation, changed-file classification, documentation maintenance, deviations, risks, acceptance status, and whether a commit was made.
