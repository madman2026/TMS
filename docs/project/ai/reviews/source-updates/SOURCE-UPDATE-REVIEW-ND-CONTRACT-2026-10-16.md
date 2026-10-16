# Source Update Review — N-D contract prerequisite / 2026-10-16

## 1. Review Metadata

- Review ID: `SOURCE-UPDATE-REVIEW-ND-CONTRACT-2026-10-16`
- Review Type: source-update-review
- Created Date: 2026-10-16
- Created By / Reviewed By: Codex
- Review Status: completed and refreshed after interruption; ND amendment implemented/offline validated; amendment accepted 2026-10-17; exact DK migration pending
- Owner: TMS project, because the result gates the cross-owner Decision-0024 sequence.
- Review Path: `docs/project/ai/reviews/source-updates/SOURCE-UPDATE-REVIEW-ND-CONTRACT-2026-10-16.md`

## 2. Update Trigger

Read section 23 for the resumed current-source result. Sections 2–22 retain the
initial preflight evidence at upstream `48887d5`; statements that no contract files
were changed describe that first inspection only, not the completed follow-up.

The operator authorized proceeding with the next identified Pack, project Remediation 0007. Its prerequisite review found that the frozen N-D Human Acceptance Contract v1 does not match the current upstream runtime. This records the preflight outcome, not a successful implementation Run.

| Snapshot | Commit |
|---|---|
| TMS preflight HEAD | `e7c81af9634267847947ef79ec9ccaaf4e2dc392` |
| Pinned upstream export | `7ec4b4dc8573f24252175bf9e1a21364b0a48d9c` |
| N-D runtime encoded by the export | `0ef0b92d9192d0162060d31efe08f55552b084f7` |
| Current upstream HEAD | `48887d5784b4c17eac7f4be087353ffb15a02751` |
| TMS consumer encoded by the export | `8fd3b427397a116b7e97f87606f6f1ecab470de0` |

Source Update Type: application source/reference drift. Update Method: read-only comparison of existing Git snapshots; no import, regeneration or overwrite performed.

## 3. Source Update Safety Check

Both working trees were clean before inspection. TMS branch: `main`. Upstream branch: `codex/improvement-debug-finalize-after-r4`. Previous versions are recoverable through Git. Upstream source, contract files, historical records and runtime state were not modified. No commit was authorized or created.

## 4. Diff / Update Notes Reviewed

Commands below were executed from their respective repositories. Git, hash and JSON inspections were read-only. The validator is an offline validation command: its inspected entry point reads local JSON/Git and does not bootstrap Laravel, read `.env`, call a target or start workers.

| Evidence | Result |
|---|---|
| `git status --short --branch`, `git diff --name-only`, `git rev-parse HEAD` | Clean baselines; exact identities above. |
| `git diff 0ef0b92d9192d0162060d31efe08f55552b084f7 HEAD -- platform/plugins/notification-delivery/src/Providers/NotificationDeliveryServiceProvider.php` | Navigation assets now register across authenticated Admin pages; other ND assets remain ND-page-local. |
| `git diff --name-only 0ef0b92d9192d0162060d31efe08f55552b084f7 HEAD -- platform/plugins/notification-delivery ':!platform/plugins/notification-delivery/docs'` | One runtime provider file and its navigation presentation test changed. |
| `git diff --name-only 7ec4b4dc8573f24252175bf9e1a21364b0a48d9c HEAD -- platform/plugins/notification-delivery/docs/ai/testing/contracts/human-v1` | No committed export changes. |
| PowerShell SHA-256 and byte-size comparison of all 22 artifacts against `ND-CORPUS-MANIFEST-0001` | Nine exact matches; thirteen match only after in-memory LF-to-CRLF expansion. Both byte sizes and hashes match after that expansion. No files rewritten. |
| PHP JSON inspection of definition/variant identities | 139 definitions; 24,668 variants; navigation counts below. No application code loaded. |
| `php platform/plugins/notification-delivery/docs/ai/testing/inventory/validate-human-acceptance-contract.php --contract=platform/plugins/notification-delivery/docs/ai/testing/contracts/human-v1 --source-root=.` | Exit 1: `HAC-V05 snapshot/changed-paths contract_snapshot_source_drift`. |

The consumer-aware validator was not rerun: source-only validation already blocks, and the manifest's consumer commit differs from current TMS. No PHPUnit, Browser, target, database or Provider execution was performed.

## 5. Updated Files / Areas

Existing upstream changes reviewed:

- `platform/plugins/notification-delivery/src/Providers/NotificationDeliveryServiceProvider.php`
- `platform/plugins/notification-delivery/tests/Feature/Admin/NotificationDeliveryNavigationPresentationTest.php`
- accepted upstream Remediation 0064 Run 001 and the cross-page applicability clause of `R4-CAN-035`.

No source or corpus file was updated by this review. Only TMS-owned review/navigation/status documentation is changed.

## 6. Affected Technical Areas

Affected: Admin navigation applicability, source snapshot/traceability, DK corpus compatibility and project execution sequencing. The inspected upstream runtime diff does not change routes, permissions, database schema, queue or Provider behavior. No claim is made about unreviewed components outside the ND diff.

The current provider registers navigation CSS/JavaScript when `AdminHelper::isInAdmin(true)` and `auth()->check()` hold. The frozen snapshot registered them only after the ND path guard. Accepted upstream tests cover Dashboard, non-ND Admin, ND pages, negative scopes and custom Admin prefix.

Navigation-related frozen families requiring successor review:

| Scenario key | Existing variants |
|---|---:|
| `nd.component.css.navigation-sections` | 431 |
| `nd.component.js.navigation-sections` | 431 |
| `nd.upstream.navigation` | 205 |
| Total | 1,067 |

These are affected-family candidates, not 1,067 failed cases or a proven complete migration allowlist. The owner must review cross-page coverage, source references and assertion impact before fixing successor identities/counts.

## 7. Reference Files Affected

`Modules/DK/docs/ai/references/ND-CORPUS-MANIFEST-0001.md` remains immutable historical export evidence, not proof of current-source compatibility. Its accepted hashes must not be replaced silently. The consumer reference index now points to this review for the current compatibility gate.

The thirteen byte-level differences are `personas.json`, `traceability.json` and all eleven schema files. They are completely explained by line-ending representation; this is separate from runtime drift. Future import must explicitly resolve canonical byte representation and integrity checks rather than silently accepting normalized hashes.

## 8. Source Docs Index Impact

TMS `SOURCE-DOCS-INDEX.md` and project `REFERENCES-INDEX.md`: checked; no category/path update required. DK `REFERENCES-INDEX.md`: compatibility/read-policy note required and updated. No raw corpus is copied into TMS.

## 9. Graph Files Impact

Graph, domain-graph and onboarding regeneration: not required for this bounded prerequisite review. No graph-derived conclusion was used.

## 10. Project Analysis Reliability

The TMS source-structure summary is a bootstrap-time observation and is not used as current implementation proof. Broader project analyses are not assessed. Current ordering comes from Decision 0024 and current lifecycle indexes.

## 11. Canonical Impact

No new product decision or Canonical change is proposed. Current upstream `R4-CAN-035` and accepted Remediation 0064 already own the changed navigation behavior. TMS Decision 0024 explicitly requires owner refresh and successor import when drift affects the contract. The obsolete export must not override those records.

## 12. Guide Impact

No TMS CLI or operator workflow implementation changed, so its guide needs no edit. The upstream accepted Run records its guide update. Any new contract-generation/import instructions belong to the successor Packs.

## 13. Pending Pack Impact

- Project Remediation 0007: blocked before normalization/source implementation until owner contract refresh, exact DK successor import/mapping migration and current capability inventory are resolved.
- Project Pack 0024 and DK Pack 0005: remain blocked downstream in Decision-0024 order.
- Project Pack 0022: no boundary trigger introduced by this offline review.
- Project Pack 0017: remains post-stage-15.

The operator's authorization to proceed is recorded; it is not an acceptance of a nonexistent implementation result or a waiver of the discovered prerequisite.

## 14. Previous Execution Impact

No historical acceptance is revoked by this review. DK Pack 0003 proves its pinned import, not current-source compatibility or executable full coverage. DK Pack 0004 and project Remediation 0006 remain accepted. Their Runs are not rewritten. No partial source implementation requires rollback.

## 15. Decisions / Changes / Remediations

- Decision Required: no new TMS ordering decision; Decision 0024 already governs this condition.
- Change Request: existing project Change 0013 retains the downstream scope; upstream successor planning must follow N-D owner governance.
- Remediation Required: N-D-owned contract refresh, then an exact TMS/DK successor-import or mapping-migration Pack.
- Canonical Update Required: none established; the existing accepted navigation contract is the refresh input.

## 16. Required Actions

1. N-D owner: create a separately scoped contract-successor Pack against the current accepted source. Preserve human-v1 and its accepted history. Pin current runtime and consumer inputs; review cross-page navigation positive/negative scope, declarations, fixtures, matrix, traceability and validator compatibility. Specify exact files and validation commands before execution.
2. N-D owner: validate and accept the successor export, including its complete identity/count manifest. Do not assume the final count remains 24,668 or that all old assertions remain unchanged.
3. DK owner: create the exact successor-import/mapping-migration Pack from the accepted old/new identity and assertion delta. Resolve byte representation, preserve unchanged identities and reconcile changed/added/removed identities explicitly. Do not rerun broad generated writes under the historical Pack 0003 approval.
4. TMS project: resume Remediation 0007 normalization using the accepted drift disposition and current Browser/HTTP capability inventory, then execute its exact bounded source/test scope. Continue with Pack 0024 and DK Pack 0005 in order.

## 17. Findings

| Finding | Category | Severity | Evidence / action |
|---|---|---|---|
| Current runtime and frozen export snapshots differ materially | source-reference | blocking | Provider diff, accepted upstream Run 0064 and offline validator exit 1; owner successor required. |
| Current checkout byte representation differs from the intake manifest | source-reference | major for future import | 13/22 exact-byte mismatches fully explained by CRLF expansion; define explicit verified intake bytes in successor import. |
| Navigation-family scope needs renewed acceptance analysis | pack | blocking for normalization | Three existing families / 1,067 variants; assess actual coverage and assertion delta before successor mapping. |

## 18. Review Decision

Review Decision: **Needs Remediation**.

Can continue current Pack implementation: **No**, until Decision-0024 prerequisite remediation is accepted. Can continue bounded prerequisite planning/review: **Yes**. No broad Phase/Release acceptance is claimed. Stop condition triggered before source changes.

## 19. Required Follow-up Updates

The upstream owner must register its contract-successor Change/Pack/Run and acceptance. DK must register the consumer successor Pack and resulting reference/mapping/capability evidence. Then normalize the blocked TMS Pack and synchronize dependent indexes. These are explicit next tasks, not completed work.

## 20. Index Updates

- Project `REVIEWS-INDEX.md`: updated with this review.
- Project `REMEDIATIONS-INDEX.md` and Remediation 0007: updated with concrete preflight blocker and authorization context.
- Project Change 0013: linked to the finding; existing approved-planning status preserved.
- DK `REFERENCES-INDEX.md`: updated with the current compatibility warning.
- Pack indexes: checked; existing blocked statuses remain accurate.
- Run indexes: no update required; no implementation Run started or passed.
- Canonical, decision, guide, template and shared indexes: no update required.

## 21. Related Records

- `docs/project/ai/decisions/TMS-DECISION-0024-nd-mvp-sequence-and-owner-boundaries.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-ND-MVP-EXECUTION-0013.md`
- `docs/project/ai/remediations/REMEDIATION-PACK-TMS-EXECUTOR-ROUTING-0007.md`
- `Modules/DK/docs/ai/references/ND-CORPUS-MANIFEST-0001.md`
- Upstream: `platform/plugins/notification-delivery/docs/ai/runs/release-4/AI-PACK-ND-R4-REMEDIATION-0064-run-001.md`
- Upstream: `platform/plugins/notification-delivery/docs/ai/canonical/release-4/RELEASE-4-CANONICAL-DECISIONS.md`, `R4-CAN-035`

## 22. Notes

No subagents were used. No real data, secrets, environment files, network, target, Browser, worker, database, dependency or application state was accessed or changed. Full executable DK coverage and Remediation 0007 implementation remain uncompleted. The offline validator failure is a prerequisite finding, not a newly introduced regression.

## 23. Resumed source recheck and authorized contract update

### Authorization and current inputs

The operator explicitly requested a fresh check of DK/ND and TMS and a contract
update wherever source conflicted, then requested continuation after interruption.
This authorizes the bounded contract correction; it does not constitute acceptance
of an unseen result, a new runtime implementation Pack or commit authority.

TMS HEAD remains `e7c81af9634267847947ef79ec9ccaaf4e2dc392` on `main`. Its initial
working changes were the six documentation paths from this review. Upstream is
clean at the start of the resumed inspection, on
`codex/improvement-debug-finalize-after-r4`, now at
`1b4416dd005d3102773580b000de82e8fe3416bf`. The previous upstream `48887d5` result is
therefore insufficient as a current-source assessment.

### Material findings and disposition

| Area | Current source finding | Contract disposition |
|---|---|---|
| Navigation | ND provider registers navigation assets throughout authenticated Admin, preserving the ND guard for page-local assets. | Add cross-page positive/negative scope and custom-prefix contexts to three existing navigation families. |
| Contextual help | Shared JS removes trigger title only after enhancement and handles dynamic roots/descendants. Blade retains conditional native fallback; some Provider-form callers deliberately set nativeTitle=false. | Require one enhanced surface, conditional no-JS fallback, accessible labels and dynamic/idempotent behavior in three help families; do not force titles onto intentional exceptions. |
| Health | PSR-20 clock replaces wall-clock reads; no checked_at keeps persisted reason or insufficient evidence; expiry at/before now is stale for checked evidence. | Preserve computed/effective separation and controlled clock/no-evidence boundary obligations in both health families. |
| ND driver | Five native command-backed recipes; HTTP inspection command registration; exact lease-owned projection, clock/config scope, baseline digest/count cleanup. | Record exact recipe/fixture, safe observation and fail-closed cleanup obligations; preserve local synthetic gates. |
| DK | Catalog v4 has 139 definitions and 24,668 base variants, but only component Operations Health has implemented Browser steps, covering 21 variants. | Record 138 definitions / 24,647 base variants still non-executable; delivery Operations Health remains a distinct skeleton. |
| Core/TMS | Core exposes Browser/HTTP typed requests and explicit registry, but batch/execution/run services force Browser. Selector cap is 1,000; catalog traversal budget 10,000. | Correct obsolete consumer prose; retain executor-neutral architecture and Remediation 0007/Pack 0024 as required work. |
| Prior runtime evidence | Accepted ND Remediation 0066 Run 001 reports 1,197 passes, one isolated lifecycle capability mismatch, plus Operations Health desktop overflow. | Disclose both unresolved observations. Do not lower requirements or claim current full-suite/layout success. Separate runtime triage remains necessary. |

The ND diff has 23 runtime/test paths, including 11 runtime paths. Each runtime
path has a source-backed requirement disposition. ND routes, permissions and schema
have no diff from the frozen source. Current project Acceptance Control is inspected
as the target integration boundary, not relabeled as an ND public API.

This is a contract-focused source review, not an exhaustive security or correctness
audit of every repository line. Bounded integrity inventories cover 691 upstream
ND/project integration files and 435 TMS/Core/DK files; inventory inclusion alone
is not evidence of semantic review or executed coverage.

### Implemented correction

ND Change 0071 / Remediation 0067 defines the exact scope and implements
`testing/contracts/human-v1.1/` with README, machine-readable amendment, closed schema
and standalone offline validator. This is an explicit composition with immutable
human-v1, not a silently rewritten historical corpus or a flat importer-compatible
export. It retains all 139 definitions and 24,668 identities; ten supplementary
requirements append obligations to eight families containing 3,696 base variants:
1,067 navigation, 2,424 help and 205 health. There are no removals or renames.

Supplementary contexts still require explicit materialization and source-assertion
mapping in DK. Ten requirement IDs do not mean ten new variants or ten passing tests.
The fully materialized successor variant/assertion count is intentionally undefined.
DK reference `Modules/DK/docs/ai/references/ND-CONTRACT-AMENDMENT-0002.md` records this
intake boundary; immutable manifest 0001 and accepted mappings/Runs remain untouched.

The amendment pins 22 base artifacts by committed SHA-256/length, six current
authority files by committed SHA-256 and current source inventories by Git blob ID.
Canonical bytes are Git blob bytes; the thirteen historical LF/CRLF differences
remain disclosed rather than being hidden by replacing manifest 0001 hashes.

### Validation performed and limitations

- Standalone amendment validation with both source roots and `--self-test` passed:
  base strict JSON/schema and semantic joins; exact artifact set/hashes; scoped
  committed/live source comparison; runtime dispositions; requirement/definition
  joins and affected counts; exact 21 health identities; nine rejection probes.
- Initial integration check hit the base validator helper's 64 MB source-file bound
  on the 163 MB variants artifact. The amendment now checks the existing 256 MB
  artifact size limit before reading each committed artifact. The old validator
  was not modified; rerun passed.
- Initial Pint check reported formatting differences in the new validator. Scoped
  formatting was applied and the subsequent Pint check and PHP syntax check passed.
- Final whitespace, exact scope, conflict-marker and Markdown reference checks
  passed for all ten ND and seven TMS changed/new paths; results are recorded in the
  completed amendment README/Pack. No old source freshness gate was
  suppressed: human-v1's original validator still belongs to its historical snapshot.

No PHPUnit, Artisan, Browser, target, DB, worker or Provider execution occurred.
Offline success proves the listed integrity and relationship checks, not native
UI behavior, complete new-context coverage, all old HAC freshness/lifecycle gates,
runtime readiness or operator acceptance. The two historical runtime issues were
not reproduced or repaired in this documentation scope.

### Maintenance, remaining sequence and review disposition

ND maintenance: Change 0071, Remediation 0067, their indexes and testing index; the
Decision index's stale statement that accepted driver Pack 0003 was still blocked
is corrected against its accepted Pack/Run, without changing the Decision itself.
TMS maintenance: this review and its index, Change 0013, Remediation 0007 and index,
DK reference 0002 and references index. These are directly related governance
maintenance under the declared Pack scope; no unrelated source changes.

Current disposition: the authorized contract update is implemented and offline
validated; final acceptance is pending. Project Remediation 0007 remains blocked
until the amendment is accepted and an exact DK successor migration reconciles
new obligations/contexts/assertion identities. Then normalize/run Remediation 0007,
project segmentation Pack 0024 and DK Pack 0005's five scenario work groups, followed
by final coverage reconciliation. The five groups are not yet five finalized Packs;
the exact total of child Packs cannot be stated before normalization.

The distance to full coverage is therefore more than the 21-variant accepted slice:
24,647 base variants are still non-executable, plus any materialized supplementary
contexts. No delivery-time estimate or blanket complete-coverage claim is justified.
No subagents, commit or push. A persistent ND Run follows the reporting lifecycle
when the operator is ready to commit or proceed; no accepted Run is fabricated.

## 24. Operator acceptance and scoped commits — 2026-10-17

The operator accepted this review and the human-v1.1 amendment and authorized
scoped commits in both repositories. Accepted ND Remediation 0067 Run 001 is the
execution record; DK reference 0002 records the upstream commit and artifact hashes.
Earlier pending-acceptance text is historical chronology. DK migration and TMS
Remediation 0007 remain unexecuted; their results and the known runtime defects
are not accepted by this action. No push authorized.

Pre-commit TMS HEAD is `d5f60590b401f09aa75a7fc9827163c1bda4e5cb`; its changes
since the pinned source are documentation-only operator-diagnostics planning.
These committed changes are preserved; the runtime input pin remains unchanged.
