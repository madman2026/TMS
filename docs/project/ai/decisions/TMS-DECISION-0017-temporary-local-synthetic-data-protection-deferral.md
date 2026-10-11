# TMS-DECISION-0017 — Temporary deferral of information-protection capabilities for local synthetic execution

Status: Temporary
Scope: Project / current local single-operator TMS and DK Acceptance work
Source: Operator decision
Date: 2026-10-10
Review At: before any real/non-synthetic data, additional user, shared or remote access, real target/provider, retained evidence, or production/staging execution
Blocking: Yes outside the temporary boundary; No inside the complete boundary below
Closure Condition: required information-protection capabilities are implemented and accepted, or this temporary risk acceptance is superseded
Next Review At: first trigger above or project stage 16 planning, whichever occurs first

## Type

5. Temporary Decision / Revisit Later and 6. Change Request / Remediation Trigger

## Decision

TMS temporarily defers dedicated information-protection capabilities because the current system has one local operator and the target data used by the current Acceptance work is fake/synthetic. The deferred capabilities include policy-enforced capture, telemetry and retention suppression; generalized redaction/masking; encrypted evidence storage; multi-user authorization/isolation; and security audit controls intended to protect real information.

This is a bounded risk acceptance, not a claim that TMS currently protects sensitive information. It applies only while all of the following remain true:

- TMS is local and used only by the current operator;
- target identities, accounts, recipients, content, credentials, tokens, fixtures and business data are inert synthetic values with no access value outside the test;
- execution uses the accepted fake ND Provider and simulated/local boundaries only;
- no real target, real provider, customer, production or staging data is accessed;
- no result, evidence or artifact containing real information is retained, shared or exported;
- no second user, remote/shared service, unattended external executor or externally reachable surface is introduced.

Within this boundary, missing policy-enforced suppression across screenshot, video, trace, HAR, DOM, console, network, telemetry and retention channels is not by itself a valid `excluded_no_reliable_executor` reason. The 1,969 ND corpus variants formerly blocked only by `sensitive-no-capture` may therefore be mapped to `automated_full` when their behavior is otherwise objectively executable. This changes the current DK Pack 0003 proposal to 24,668 full mappings and zero exclusions; it does not make its generated skeletons executable and does not authorize Pack execution.

The following baseline prohibitions remain active and are not deferred:

- never use or store a real password, token, cookie, session, Authorization header, personal datum, customer payload, phone number, target URL or production/staging value;
- never connect to a real target/provider or perform real SMS, callback or external-network activity;
- never read or modify `.env`, and never commit usable secrets or environment values;
- if a value is not demonstrably synthetic and inert, stop rather than treating it as fake;
- enabling a new evidence channel, external executor, remote service or retained artifact remains separate approved scope.

Any boundary trigger immediately ends this exception. Work that would cross the boundary is blocked until a separately accepted security Pack implements and validates the protections appropriate to that use.

## Reason

The operator is currently the only TMS user and the target dataset is intentionally fake. Building generalized protection infrastructure now would delay corpus import and synthetic Acceptance development without protecting real information. The operator explicitly requires those capabilities later, so the decision records a temporary deferral with objective expiry triggers instead of removing the requirement.

## Impact

- Decision 0005 remains authoritative for synthetic-only, no-production and no-real-secret boundaries. Its requirement for comprehensive capture/telemetry/retention protection is temporarily deferred only inside this Decision's complete boundary.
- Decision 0009 remains unchanged because usable credentials and other real secrets are still prohibited.
- Decision 0011 capability fallback remains required, but lack of sensitive-channel enforcement alone no longer excludes a wholly synthetic local scenario.
- Decision 0012's five dispositions remain unchanged. The current DK corpus assessment changes from `22699/0/0/1969/0` to `24668/0/0/0/0`.
- DK Pack 0003 and `ND-CORPUS-MANIFEST-0001` require normalization before Pack acceptance or execution.
- No source, configuration, database, dependency, runtime, UI, API, MCP, target, provider, network, evidence-capture or commit action is authorized by this Decision.
- No project Canonical file is active for this subject, so no Canonical update is required.
- Operator Documentation Impact: no current guide change; the current Pack does not add an operator-visible protection setting or execution capability. A future security Pack must update the applicable operator guide.

Error Handling Impact: No current runtime behavior
API Error Contract Impact: Not applicable
UI / Admin UI Error Impact: Not applicable
Error Code Impact: No
Exception Handling Impact: No
External Error Normalization Impact: Not applicable
Retry / Failure Classification Impact: No
Status Model Impact: Yes — pending DK coverage mappings only
Logging Impact: Yes — no real values are allowed in logs; synthetic-only operation does not imply protection
Traceability Impact: Yes — Packs/Runs must record that this temporary boundary was active
Sensitive Data Impact: Yes — real sensitive data remains prohibited; protection implementation is deferred
Testing Impact: Yes — tests must use inert synthetic fixtures and prove zero real/external I/O
Documentation Impact: Yes

No stable error code is added or changed. If a future implementation needs a boundary-enforcement error, its code, classification, visibility, logging and tests must be approved in that implementation Pack.

Required Follow-up Update:

- Create, accept and execute a project-owned information-protection Pack before any boundary trigger.
- Reason: this Decision accepts current synthetic/local residual risk but explicitly defers the controls required for real, shared, remote or retained information.
- Affected areas: capture/evidence channels, telemetry, retention, redaction/masking, encrypted storage, permissions/isolation, audit, logging, negative exposure tests, and the applicable operator guide.
- Blocking status: blocking before any trigger; non-blocking for the current complete boundary.

## Files to Update

- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md` — current-scope notice only
- `docs/project/ai/decisions/TMS-DECISION-0012-automated-e2e-coverage-and-traceability.md` — current-scope notice only
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-INFORMATION-PROTECTION-0006.md`
- `docs/project/ai/changes/CHANGES-INDEX.md`
- `Modules/DK/docs/ai/references/ND-CORPUS-MANIFEST-0001.md`
- `Modules/DK/docs/ai/references/REFERENCES-INDEX.md`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-CORPUS-IMPORT-0003.md`
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`

## Required change request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-INFORMATION-PROTECTION-0006.md` records the accepted planning/security-boundary change. Outcome: future-Pack normalization / no remediation required because no implemented source protection is removed and DK Pack 0003 has not been executed.

## future-work

- Before any boundary trigger, create, accept and execute a dedicated information-protection Pack covering the applicable capture, telemetry, retention, redaction, access-control, audit and evidence-storage requirements.
- Define threat model, protected data classes, evidence channels, retention policy, roles/permissions, safe error/log behavior and negative exposure tests in that Pack.
- Review this temporary decision at project stage 16 planning even if no earlier trigger occurs; explicitly carry forward, supersede or close it.

## Review / Resolution

```text
Reviewed In: operator instruction on 2026-10-10
Review Result: operator explicitly deferred current information-protection implementation because target data is fake and TMS currently has one user, while requiring later implementation
Resolution: carried-forward
Next Review At: first boundary trigger or project stage 16 planning
Resolution Notes: documentation/planning normalization only; no source execution, external I/O, Pack acceptance, or commit authorization
```

## Notes

Later discovery that any current value is real or access-bearing invalidates the factual basis of this temporary decision and triggers an immediate stop. “Synthetic” describes the data, not the security level of TMS.
