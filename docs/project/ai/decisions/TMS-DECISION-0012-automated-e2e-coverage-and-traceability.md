# TMS-DECISION-0012 — Automated E2E coverage and traceability

Status: Accepted
Scope: Project test policy / all target Apps
Source: Operator-approved option 13
Date: 2026-10-07
Review At: first target corpus mapping
Blocking: Yes for scenario import and disposition
Closure Condition: superseded by a separately approved test-policy decision
Next Review At: first DK corpus-mapping Pack

Current Architecture Notice (2026-10-08): Decision 0015 makes removal of the `manual-only` runtime disposition part of the immediate hierarchy-cutover remediation. Source coverage exclusions remain represented only by the five mapping dispositions defined below.

## Type

4. Project-level test policy decision

## Decision

TMS tests observable target-system capabilities end to end from the perspective of external users and consumers through Web App, API, and extensible executors. Functional behavior, authorization and access control, integrations, state changes, and observable results have priority.

Every executable TMS scenario is automated. Operator input or approval may be a declared prerequisite, but operators do not execute scenario steps or assertions. Subjective UX/UI judgment is performed independently by humans and is not a required TMS test. Objective UI behavior such as visibility, focus, responsive state, and accessibility is automated when a reliable executor/oracle exists.

All 139 source use cases and 24,668 source scenarios must be analyzed and traceable. Each source case receives exactly one disposition:

```text
automated_full
automated_partial
merged_equivalent
excluded_no_reliable_executor
excluded_human_judgment
```

`automated_partial`, merged, and excluded entries require a reason, covered assertions, uncovered assertions, and a stable link to their executable replacement when applicable. The final executable count may differ from 24,668 because duplicates may merge and non-executable judgments may be excluded. A package limitation alone is not a valid exclusion until Decision 0011's fallback analysis is completed.

## Reason

The goal is maximal automated E2E coverage, not human test management. Traceability preserves proof that the complete source corpus was considered without forcing unreliable or subjective checks into runtime automation.

## Impact

- Manual-only runtime dispositions and manual Test records are not part of the new design.
- Import/mapping services must preserve source identifiers and produce deterministic coverage reports.
- Target owners decide product-specific mappings; shared TMS owns the generic disposition contract and aggregation.

## Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: No
UI / Admin UI Error Impact: Future clients only
Error Code Impact: Yes — invalid or incomplete mapping is a validation failure
Exception Handling Impact: Yes
External Error Normalization Impact: No
Retry / Failure Classification Impact: No
Status Model Impact: Yes — disposition values above
Logging Impact: Yes
Traceability Impact: Yes
Sensitive Data Impact: No new sensitive data
Testing Impact: Yes
Documentation Impact: Yes
```

## Required Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0003.md`.

## Required Follow-up Updates

- Implement generic mapping validation/reporting in Packs 0010 and 0014.
- Remove/deprecate the legacy Core `manual-only` executable-catalog disposition through Core Pack 0003; preserve any historical meaning only in source mapping records.
- Create DK/ND corpus and scenario Packs only after DK ownership activation.
- Record the approved isolated DK environment, fake ND Provider, simulated Callback, and no-real-SMS boundary in the relevant DK/ND owner decisions before target implementation.
- Record any excluded case with evidence; do not delete its source trace.

## Review / Resolution

```text
Reviewed In: operator confirmation of revised proposal 13
Review Result: operator explicitly approved maximal automated E2E coverage and separate human UX review
Resolution: accepted
Next Review At: first DK corpus mapping
Resolution Notes: all source cases are analyzed; not all must become one-to-one executable scenarios.
```
