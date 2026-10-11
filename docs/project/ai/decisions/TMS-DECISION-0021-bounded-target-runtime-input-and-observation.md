# TMS-DECISION-0021 — Bounded target runtime input and semantic Browser observation

Status: Accepted — implemented and validated by Pack 0020 / Run 001; Run acceptance remains pending
Scope: Project / cross-owner Acceptance runtime contract
Source: Operator approval after DK Pack 0004 pre-execution blocker report
Date: 2026-10-11
Review At: Project Pack 0020 Run 001 acceptance and before DK Pack 0004 is re-approved
Blocking: Yes — DK Pack 0004 remains blocked until Pack 0020 Run 001 is accepted and Pack 0004 is renormalized
Closure Condition: Project Pack 0020 is implemented, validated, accepted, and DK Pack 0004 is renormalized against its accepted source
Next Review At: Project Pack 0020 Run 001 acceptance gate

## Type

6. Change Request / Remediation Trigger

## Decision

Create a project-owned Change Request and standard enabling Pack that make two bounded additions to the accepted Browser Acceptance path:

1. pass the already validated, in-memory `PrerequisiteExecutionData` to the App-keyed `TargetBrowserRuntimeResolver` together with the final provisioned `TargetContext`; and
2. add an optional Core-owned semantic target-observation port that a target runtime may implement and that `TestContext` exposes through a validated observation key and a bounded typed fact value.

The shared layer must not resolve `app-secret://...` references, receive raw secret values, persist runtime inputs, add a secret store, or carry access-bearing values in `TargetContext`, `ResourceReference`, lifecycle results, Test/Step data, logs, reports, or documentation. Target owners remain responsible for injecting their own reference-resolution boundary and for keeping any resolved URL/session/credential value private to one per-execution runtime object.

The observation port must not expose selectors, raw DOM, HTML, translated text, field values, URLs, cookies, storage, headers, console/network payloads, screenshots, traces, or arbitrary JavaScript. It may return only a small allowlisted map of language-neutral semantic fact keys to bounded booleans, integers, finite numbers, nulls, or short lowercase semantic tokens. The target owner maps target-specific DOM state to those facts inside its runtime boundary.

This decision did not itself approve project Pack 0020 execution, DK Pack 0004 execution, browser launch, target access, target product changes, or commit. The operator later approved the bounded Pack 0020 execution, reporting, and commit gates; acceptance remains separate and pending.

## Reason

Pre-execution inspection of DK Pack 0004 proved that the accepted shared contract stops short of two requirements needed by the selected Operations Health group:

- the runtime resolver receives final safe references but not the validated prerequisite handles needed by a target-owned access resolver; and
- Core browser observations intentionally omit raw/content-bearing values and therefore cannot express the target-specific computed/effective/freshness/reason/source facts required by the scenario.

Using static/global state, putting raw access values into `ResourceReference`, returning raw DOM/text, hardcoding target URLs, or widening `BrowserProbe` into a target-specific API would violate accepted boundaries. An explicit per-call input handoff plus an optional bounded semantic observer preserves target ownership and the existing fail-closed lifecycle.

## Impact

- `TargetBrowserRuntimeResolver` receives one additional validated in-memory argument through a clean in-repository signature cutover.
- `AcceptanceExecutionService` passes the exact prerequisite object already validated for the same full tuple/profile.
- Core gains an optional observer contract and bounded semantic observation value; the existing `BrowserTargetRuntime` and `BrowserProbe` contracts remain intact.
- `TestContext` validates observation keys, requires the runtime to implement the optional observer, and rejects mismatched/unsafe observations as `acceptance_configuration_invalid`.
- No database, API, UI, configuration, environment variable, queue, external integration, logging event, dependency, or target product behavior changes.
- DK Pack 0004 remains blocked until Pack 0020 is accepted and the operator separately confirms the runtime-only isolated local target procedure.

Configuration / Settings Impact: No
Error Handling Impact: Yes
API Error Contract Impact: Not applicable
UI / Admin UI Error Impact: Not applicable
Error Code Impact: No — reuse `acceptance_configuration_invalid`, `resource_unavailable`, and existing Browser/Scenario codes
Exception Handling Impact: Yes — invalid/missing observation capability fails with a fixed safe existing code
External Error Normalization Impact: Not applicable
Retry / Failure Classification Impact: No
Status Model Impact: No
Logging Impact: No new event or context
Traceability Impact: Yes — exact tuple/profile/prerequisite identity reaches runtime resolution without reparsing
Sensitive Data Impact: Yes — raw resolved values remain private, transient, omitted, and non-persisted
Testing Impact: Yes
Documentation Impact: Yes

## Files to Update

- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-RUNTIME-OBSERVATION-0010.md`
- `docs/project/ai/packs/AI-PACK-TMS-TARGET-RUNTIME-OBSERVATION-0020.md`
- project Decision/Change/Pack indexes
- the exact project/Core source and tests listed by Pack 0020 after separate execution approval
- DK Pack 0004 and its Pack index for dependency/blocker normalization

## Required change request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-RUNTIME-OBSERVATION-0010.md`

## future-work

- DK Pack 0004 must define the concrete owner-local secret-reference resolver, per-execution runtime, target-specific semantic fact allowlists, selectors, fixture behavior, oracle, and cleanup without access-bearing values in source or documentation.
- If the isolated target cannot provide a deterministic owner-local reference-resolution boundary or machine-readable/semantically mappable UI state, stop; Pack 0020 must not fabricate target behavior.
- Wider secret-manager infrastructure, generic raw DOM/text capture, evidence retention, screenshots/traces, and additional target groups require separate decisions and Packs.

## Review / Resolution

```text
Reviewed In: operator approval on 2026-10-11 after DK Pack 0004 blocker report; Pack 0020 execution, reporting, and commit approvals on 2026-10-11
Review Result: bounded implementation and validation completed; Run 001 pending operator acceptance
Resolution: implementation-complete-pending-acceptance
Next Review At: Project Pack 0020 Run 001 acceptance gate
Resolution Notes: DK Pack 0004 stays blocked pending accepted Pack 0020 Run 001, source-based renormalization, and its own runtime-only target gate
```

## Notes

No Canonical update is required. This is a narrow extension of Decisions 0009, 0011, 0012, 0015, and temporary 0017; it does not weaken their secret, lifecycle, traceability, or local-synthetic constraints.
