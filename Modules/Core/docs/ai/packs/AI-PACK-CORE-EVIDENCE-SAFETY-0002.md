# AI-PACK-CORE-EVIDENCE-SAFETY-0002 — Evidence Safety and Sensitive No-Capture Enforcement

Status: `superseded — standalone execution cancelled by Decision 0005`

Generated: `2026-10-05`

Decision: `docs/project/ai/decisions/TMS-DECISION-0004-cli-only-staged-generic-acceptance-roadmap.md`

Superseded By: `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`

Former Dependencies (historical): accepted project Pack 0004 and accepted Core Pack 0001

Execution Gate: Superseded; this historical proposal is not executable and is not a prerequisite for the remaining roadmap.

Scope Replacement (2026-10-06): the operator selected synthetic testing/staging execution and minimum safeguards inside project Packs 0006–0008 instead of this standalone capture-policy framework. No replacement standalone Pack is required. See Decision 0005 and `docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0001.md`. Sections 1–34 below preserve the original unexecuted Draft for history only; their normalization questions, safety-framework design, and execution instructions are inactive.

## 1. Task ID

`AI-PACK-CORE-EVIDENCE-SAFETY-0002`

## 2. Task Title

Enforce evidence modes and fail-closed sensitive no-capture behavior in Core.

## 3. Goal

Turn code-owned evidence modes into runtime safety controls that are established before Browser context/page creation and restrict TMS-controlled capture/observation channels throughout a scenario.

## 4. Context

Pack 0004 defines evidence-mode metadata and Core Pack 0001 provides observations. Current TMS stores Step/Run `data` as null and does not implement artifacts, but it also has no explicit policy guard proving that screenshot, video, trace, raw DOM, console, network, clipboard, storage, or telemetry capture remains disabled for sensitive flows.

## 5. Related Release / Phase

Not applicable; pre-Release Core security capability Pack.

## 6. Related Epic / Feature / Story

Reusable human-acceptance automation — safe evidence and sensitive execution prerequisite.

## 7. Source References

- Decisions 0002–0004;
- accepted predecessor Pack/Run records;
- current Core RunOptions, BrowserFactory, TestContext, Runner, StepResult, and tests;
- installed Playwright tracing, screenshot, event, route, storage, and video configuration interfaces.

## 8. Files to Create

Anticipated Core scope, to be finalized during normalization:

- a typed evidence-policy/capture-channel model;
- an evidence-policy guard/service;
- focused unit and local-browser security tests;
- owner-local Run Report after execution.

## 9. Files to Edit

Anticipated Core RunOptions, BrowserFactory, TestContext, Runner, result/observation boundary, provider binding, and exact affected tests. No path is executable authority until normalized.

## 10. Files to Read / Reference

Repository/project/Core routing and profiles; shared scope, execution, security/error/test/reporting rules; accepted predecessors; current Core source; exact installed Playwright implementation for every channel claimed controlled.

## 11. Configuration / Settings Requirements

Expected default: all capture disabled. No environment secret or Admin Setting. Any non-sensitive artifact directory, retention setting, or external telemetry integration remains out of scope and requires another Pack.

## 12. Do Not Change

Target Apps, root Profile/secret/fixture/catalog/batch behavior, database, API/UI, queue, dependency files, `.env`, vendor, artifact retention, or external targets.

## 13. Clarification Questions Before Implementation

Normalization must resolve:

1. exact TMS-controlled channel inventory and proof boundary;
2. whether `non-sensitive-visual` permits in-memory screenshot generation or remains policy-only until an artifact Pack;
3. how Step names/descriptions and exceptions are constrained so they cannot bypass the evidence allowlist.

## 14. Multilingual / Translation Requirements

No multilingual or translation changes expected.

## 15. UI / Admin UI Requirements

No UI changes required.

## 16. Operator Documentation Requirements

Expected operator documentation impact: yes, a future generic CLI guide must explain evidence modes and the guarantee boundary. Exact guide owner/path must be resolved during normalization; no guide is invented by this Draft.

## 17. Implementation Rules

- Resolve policy before browser context/page creation.
- `sensitive-no-capture` prohibits TMS-controlled screenshot, video, trace, raw DOM, network/console payload, clipboard, browser storage, telemetry payload, and raw revealed/secret values.
- `metadata-only` returns only explicit safe fields.
- Unknown/missing policy fails closed; it never falls back to broader evidence.
- A child/variant may tighten but never widen parent policy.
- Result construction uses an allowlist, not post-capture redaction.
- Do not claim control over target-owned logs, OS/process inspection, third-party telemetry, or infrastructure outside TMS; unsupported guarantees must be explicit.

## 18. Architecture Constraints

Policy belongs to Core; target classification belongs to code-owned Apps. Core must not know ND policy files or target surface names. Evidence safety wraps observation/execution and cannot depend on target-specific exception text.

## 19. Validation Rules

The normalized Pack must prove policy is installed before context creation, prohibited calls cannot occur, unknown modes fail closed, safe metadata survives, sensitive sample values are absent from results/logs/exceptions, and browser cleanup still occurs on policy failure.

## 20. Security Rules

Fail-closed security Pack. Real secrets and sensitive target data are forbidden. Tests must plant synthetic sentinel values across input, DOM, exception, console/network/storage/clipboard fakes and prove their absence from every TMS-controlled output channel.

## 21. Error Handling / Logging / Traceability Requirements

Security/error impact: Yes. Expected scenarios include unsupported capture channel, evidence-policy violation, and policy setup failure. Exact stable codes, retry/permanent classification, Run status impact, safe structured events, and context fields are deferred to normalization. Raw prohibited values and raw Playwright messages must be omitted.

## 22. Data Model / Migration / Relationship Requirements

No database, migration, relationship, snapshot, or retention change expected. Persisted arbitrary evidence remains prohibited.

## 23. Commenting Requirements

Explain guarantee boundaries, pre-capture enforcement, and why post-capture redaction is insufficient.

## 24. Testing Requirements

Mandatory behavior, negative, failure-order, cleanup, logging-context, and secret-omission tests. Real-browser tests must use local synthetic content and may run only when authorized by the normalized Pack.

## 25. Acceptance Checklist

- [ ] exact controlled-channel boundary documented and tested;
- [ ] policy active before context/page creation;
- [ ] sensitive/unknown flows fail closed;
- [ ] no prohibited value reaches result/log/exception/CLI-facing data;
- [ ] no artifact retention or target-specific policy added;
- [ ] cleanup and existing execution regressions pass.

## 26. Tests to Add

Normalize into named policy-mode, prohibited-channel, unknown-policy, failure-order, result-allowlist, sentinel-omission, and cleanup tests.

## 27. Tests to Run

Deferred until normalization. No external target, real credential, artifact persistence, or network is authorized.

## 28. Expected Output

A Core evidence guard with explicit guarantee boundaries and tests; no target App, artifact store, or secret provider.

## 29. Operator Execution Checklist

### Before AI Execution

- Confirm the normalized controlled-channel list and whether any browser smoke test is authorized.

### After AI Execution

- Review the guarantee-boundary statement and sentinel non-exposure evidence.

## 30. Agent Final Report

Report each controlled channel, each ungoverned external boundary, actual tests, and security findings without including sentinel contents beyond safe placeholders.

## 31. Review Checklist

Review fail-closed order, allowlists, no-capture behavior, cleanup, logs, exceptions, and absence of security overclaims.

## 32. Rollback / Safety Notes

Rollback removes the guard/policy integration and restores prior Core signatures. No artifact, database, external, or target state may be created.

## 33. Stop Conditions

Stop for any required target policy/schema, capture-before-policy behavior, uncontrolled raw payload, new dependency, artifact persistence, Profile/secret access, or unaccepted predecessor.

## 34. Open Questions

The three normalization questions in section 13 are blocking execution.
