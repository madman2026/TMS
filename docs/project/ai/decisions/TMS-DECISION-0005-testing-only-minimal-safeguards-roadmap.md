# TMS-DECISION-0005 — Testing/staging-only execution and minimal inline safeguards

Status: Accepted constraints — roadmap portions superseded by Decisions 0006 and 0013
Scope: Project
Source: Operator answer
Date: 2026-10-06
Review At: normalization of Pack 0006 and first target-App integration
Blocking: No
Closure Condition: superseded by a separately approved scope or safeguard decision
Next Review At: project Pack 0007 normalization

Current Roadmap Notice (2026-10-07): Decision 0013 replaces the remaining roadmap sequence. Testing/staging-only targets, synthetic data, target-App ownership, sensitive-value omission, and safe-output constraints remain active. Newly approved Packs must not reinstate the deleted generic secret/fixture proposals; they implement the narrower boundaries in Decisions 0009–0011.

Current Roadmap Notice (2026-10-06): `docs/project/ai/decisions/TMS-DECISION-0006-remove-cancelled-fixture-and-safeguard-packs.md` cancels/removes project 0006 and deletes the superseded Core 0002/project 0005 proposal files. Its fixture dependency, retention instructions and earlier next-Pack directions are superseded. The original decision/reasoning below remains historical; testing/staging, synthetic-data, App ownership, safe-output and deferred-settings constraints remain applicable.

## Type

6. Change Request / Remediation Trigger — revision of the accepted future roadmap; no implemented runtime correction.

## Decision

TMS targets testing/staging environments with synthetic business data and dedicated test accounts. Production targets, production data, and real customer data are outside the intended execution scope. Synthetic data is an explicit project constraint, not evidence that an active test-account password, session cookie, or token has no access value.

The operator deferred expanded operator-configurable test options and cancelled standalone execution of Core Evidence Safety Pack 0002 and project Ephemeral Secret and Authentication Execution Context Pack 0005. Preserve those unexecuted Draft documents as superseded historical proposals; they are not execution prerequisites or instructions for the remaining roadmap.

Do not generate a replacement settings, capture-policy, or secret-management Pack. Do not introduce a generic secret provider/store/manager, encrypted credential schema, ephemeral authentication lease framework, or comprehensive capture-channel guard through the remaining Packs. Existing browser options and runtime behavior remain unchanged by this documentation decision.

The remaining sequence after acceptance of project Pack 0004 and Core Browser Observability Pack 0001 is:

1. project Pack 0006 — isolated synthetic fixture leases and cleanup, with a minimal test-environment check and safe adapter/output boundaries;
2. project Pack 0007 — lazy scenario catalog and variant dispatcher, preserving safe descriptor and plan output;
3. project Pack 0008 — sequential resumable batch CLI execution, preserving safe Run/Batch history, logs, and reports.

Retain existing Pack identifiers. Every remaining Draft must still be normalized against actual accepted predecessor source and separately approved before implementation. This decision does not accept the executed Core Pack 0001 or authorize any source change, test run, target connection, database operation, dependency change, or commit.

### Minimum safeguards and ownership

- Pack 0006 must check the selected target environment before fixture, target authentication, or browser/target setup. Only explicitly approved testing/staging environments are eligible; production and unknown environments are rejected. A Laravel `APP_ENV` label alone is not proof of the target's environment. Normalize the smallest code-owned App/adapter signal needed; do not build a general settings system.
- Fixture setup, target authentication, URLs, selectors, and test-account acquisition remain owned by target Apps. Shared TMS orchestrates lifecycle and handles safe references/metadata only. No standalone secret-provider or authentication-context prerequisite is required. Target integration remains a separate future scope; shared implementation tests use fake adapters and synthetic content only.
- Use synthetic fixture data and fake provider inputs. Inert dummy literals/sentinels are allowed in fake tests. Usable test-account passwords, tokens, session cookies, and authenticated storage values must not be placed in source, Profile `extra`, plain-text versioned config, CLI arguments, Test/Step/Batch records, logs, exceptions, reports, or catalog descriptors. A future target App must resolve any usable value at runtime through its minimal existing approved environment-backed or equivalent boundary, without making it shared persisted data.
- Preserve current safe structured failures and allowlisted output. Pack 0006 verifies adapter output/error omission; Pack 0007 verifies list/plan omission; Pack 0008 verifies persistence/log/report omission on success, failure, and resume. Synthetic non-secret business fixtures need no new enterprise secret infrastructure.
- Do not add screenshot/video/trace/HAR recording, raw DOM/network/console export, an artifact store, retention management, or a full runtime no-capture proof in Packs 0006–0008. Existing evidence-mode metadata remains descriptive; do not claim that a universal capture guard has been implemented. Future recording or use of non-synthetic data requires a separately scoped decision.
- Fixture isolation, cleanup in supported process paths, cleanup-failure visibility, deterministic selection, bounded execution, and duplicate-safe resume remain required. CLI-only, tests-as-code ownership, and the deferred API/UI/queue/scheduler/parallel/retry boundaries remain unchanged.

## Reason

The operator confirmed that target execution will use testing/staging and fake data, rejected expanded settings for now, and requested that only minimum credential/evidence safeguards be carried into the existing future Packs. Two independent infrastructure Packs are disproportionate to that scope. Local output and lifecycle checks retain the useful safeguards without delaying fixture/catalog/batch work behind a generic secret or capture framework.

## Impact

- Partially supersedes Decision 0004's seven-Pack dependency chain and comprehensive standalone evidence/secret prerequisites. Its CLI, ownership, staged-approval, and scope exclusions remain applicable.
- Supersedes only the unexecuted Core Pack 0002 and project Pack 0005 proposals. Accepted/executed Packs, source, tests, and historical validation evidence are not rewritten.
- Project Pack 0006 depends on accepted project Pack 0004 and accepted Core Pack 0001. Pack 0007 additionally depends on accepted Pack 0006; Pack 0008 additionally depends on accepted Packs 0006–0007. Neither superseded Pack is an active prerequisite.
- Configuration / Settings Impact: planning only; expanded browser/Profile/CLI option resolution and effective-settings snapshots are deferred. No config, environment, Admin Setting, or credential schema is changed now.
- Operator Documentation Impact: future Pack 0006 guide must explain testing/staging and synthetic-fixture boundaries; Pack 0007 guide explains safe selection; Pack 0008 guide explains safe reporting/resume. Exact guide paths are resolved during normalization under the applicable profiles; no guide is invented now.
- Canonical Update Required: No. No project/Core runtime Canonical file is activated by the current routers/indexes; record the approved revision here and synchronize roadmap records.

Error Handling Impact: Yes — future minimum environment/output checks
API Error Contract Impact: Not applicable
UI / Admin UI Error Impact: Not applicable
Error Code Impact: Deferred to normalization of Packs 0006–0008; no current code is created or redefined
Exception Handling Impact: Yes — retain safe normalized adapter failures without raw values
External Error Normalization Impact: Deferred to the separately scoped target adapter
Retry / Failure Classification Impact: No — automatic retry remains deferred
Status Model Impact: No current change; future fixture/batch failure precedence remains a normalization requirement
Logging Impact: Yes — future logs must omit usable authentication values
Traceability Impact: No new identifiers; retain existing Run identifiers and planned Batch/Test linkage
Sensitive Data Impact: Yes — minimum value omission remains despite synthetic business data
Testing Impact: Yes — focused fake-backed omission/environment checks within the remaining Packs
Documentation Impact: Yes — Decision, Change Request, supersession notices, Draft dependencies, and indexes

The future unsafe/unknown-environment error is operator-visible, rejects setup before side effects, and is not eligible for automatic retry. Exact error code, log level/context, and Run/Batch status mapping must be approved during Pack 0006 normalization and consumed consistently in Pack 0008. Implementation before that resolution: No. Active-value omission must be verified in allowed metadata and stable failures using inert fake sentinels; do not test with a real account or credential.

## Files to Update

- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/decisions/TMS-DECISION-0004-cli-only-staged-generic-acceptance-roadmap.md` — supersession notice only
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0001.md`
- `docs/project/ai/changes/CHANGES-INDEX.md`
- `Modules/Core/docs/ai/packs/AI-PACK-CORE-EVIDENCE-SAFETY-0002.md` — historical Git path; proposal deleted by Decision 0006
- `Modules/Core/docs/ai/packs/PACKS-INDEX.md`
- `docs/project/ai/packs/AI-PACK-TMS-EPHEMERAL-EXECUTION-CONTEXT-0005.md` — historical Git path; proposal deleted by Decision 0006
- `docs/project/ai/packs/AI-PACK-TMS-FIXTURE-LEASES-0006.md` — historical Git path; proposal deleted by Decision 0006
- `docs/project/ai/packs/AI-PACK-TMS-SCENARIO-CATALOG-DISPATCHER-0007.md`
- `docs/project/ai/packs/AI-PACK-TMS-BATCH-CLI-EXECUTION-0008.md`
- `docs/project/ai/packs/PACKS-INDEX.md`

## Required change request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0001.md` records the approved documentation-only revision of Decision 0004. The operator's 2026-10-06 instruction authorizes this revision. Outcome: future-pack-normalization / no-remediation-required. No implemented contract, source, migration, or test correction is performed, so no Remediation or replacement implementation Pack is required.

## future-work

- Normalize remaining Drafts in the revised dependency order, including exact paths, error contracts, focused tests, and operator guide requirements.
- Require a separately scoped target-App integration before any actual target I/O; it must use testing/staging and synthetic data only.
- Revisit deferred configurable options, secret infrastructure, or recording only upon a later explicit need and operator decision. They must not reappear as hidden prerequisites during normalization.

## Review / Resolution

```text
Reviewed In: operator discussion on 2026-10-06 following the test-options audit
Review Result: operator deferred configurable settings, cancelled the standalone evidence/secret approach, and requested minimum safeguards in later existing Packs because all target data is synthetic
Resolution: accepted
Next Review At: AI-PACK-TMS-FIXTURE-LEASES-0006 normalization or first target-App integration
Resolution Notes: approval covers documentation/planning revision only; no source execution, Core Pack 0001 acceptance, or commit authorization
```

## Notes

This approved scope reduction does not waive shared secret-omission, source ownership, Pack approval, or production-exclusion rules. It replaces the scale and placement of future implementation work. The deferred expanded test-settings proposal has no Pack file to cancel and is recorded here only.
