# TMS-DECISION-0016 — Prerequisite operation naming

Status: Accepted
Scope: Project architecture / prerequisite operation contract
Source: Operator answer
Date: 2026-10-08
Review At: Pack 0015 normalization
Blocking: No after propagation to Pack 0011
Closure Condition: superseded by a separately approved operation-contract decision
Next Review At: Pack 0015 normalization

## Type

3. Pack-Local Decision with a required future-client propagation

## Decision

The Pack 0011 prerequisite operations use a singular workflow namespace and explicit resource/action segments:

```text
acceptance.prerequisite.request.prepare
acceptance.prerequisite.input.submit
acceptance.prerequisite.approval.grant
acceptance.prerequisite.request.cancel
```

These names replace the normalized draft names `acceptance.prerequisites.discover`, `acceptance.prerequisites.submit`, `acceptance.prerequisites.approve`, and `acceptance.prerequisites.cancel` before implementation. No alias, compatibility registration, or dual operation name is added.

## Reason

The operator requested clearer names before Pack 0011 execution. The selected pattern identifies the prerequisite workflow, affected resource, and action while remaining consistent with the transport-neutral dotted operation registry.

## Impact

- Pack 0011 handlers, registry entries, validation, logs, tests, and Run evidence use only the four names above.
- Parameter shapes, typed results, states, error codes, security constraints, and behavior remain unchanged.
- Pack 0015 must expose these exact names through its Artisan client and documentation.
- No API, UI, route, translation, migration, configuration, dependency, target, queue, or Core behavior changes.

Error Handling Impact: No
API Error Contract Impact: Not applicable
UI / Admin UI Error Impact: Not applicable
Error Code Impact: No
Exception Handling Impact: No
External Error Normalization Impact: Not applicable
Retry / Failure Classification Impact: No
Status Model Impact: No
Logging Impact: Yes — the structured `operation` field uses the accepted names
Traceability Impact: No — existing operation and correlation identifier behavior is unchanged
Sensitive Data Impact: No
Testing Impact: Yes — registry and service tests assert the accepted names
Documentation Impact: Yes — Pack 0011, this Decision, indexes, Run evidence, and future Pack 0015 documentation

## Files to Update

- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/packs/AI-PACK-TMS-OPERATOR-INPUT-APPROVAL-0011.md`
- `docs/project/ai/packs/PACKS-INDEX.md`
- Pack 0011 implementation/tests and Run Report
- Pack 0015 during its later normalization

## Required change request

None. The operation names were changed before Pack 0011 implementation or acceptance, so no implemented or accepted contract requires remediation.

## future-work

Pack 0015 must use and document the exact accepted names without aliases or legacy fallback.

## Review / Resolution

```text
Reviewed In: Pack 0011 pre-execution operator gate on 2026-10-08
Review Result: operator requested improved operation names and approved every other normalized Pack choice
Resolution: accepted
Next Review At: Pack 0015 normalization
Resolution Notes: Pack 0011 execution was authorized after recording the naming correction; commit remains separately gated.
```

## Notes

This decision authorizes only the naming correction and its direct Pack 0011 implementation. It does not expand Pack scope or authorize a commit.
