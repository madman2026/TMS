# TMS-DECISION-0025 — Execution-owned automatic prerequisite references

Status: Accepted — implementation validated and operator-accepted with scoped commit authorization on 2026-10-15
Scope: Project Acceptance prerequisite workflow and owner-local resolver bindings
Source: Operator UX instruction and approval
Date: 2026-10-15
Review At: before any resolver is allowed to return a literal secret or before remote/multi-user execution
Blocking: Yes for automatic prerequisite implementation
Closure Condition: Project Remediation 0012 and DK Remediation 0002 are accepted, or this decision is superseded

## Type

2. Architecture Decision and 6. Change Request / Remediation Trigger

## Decision

An operator must be able to run one exact Acceptance identity or a selector-based batch without discovering a Variant key merely to prepare a prerequisite, copying a prerequisite request ID, or maintaining a prerequisite-reference file when every required input is an app-owned safe reference.

The schema must explicitly distinguish operator-provided inputs from app-resolvable references. Automatic resolution is opt-in, is included in the schema fingerprint, and is valid only for required `secret_reference` inputs. The default remains operator-provided.

At execution time, the project prerequisite service may create and persist a new request automatically only when every required input is app-resolvable and the schema has no required approval. The request is bound to the exact App/Component/Suite/Scenario/Variant/Profile tuple, stores only validated opaque `app-secret://<app>/...` references, becomes the request used by that run or batch item, and retains existing correlation and operation traceability.

Mixed/manual inputs and approvals must keep the explicit workflow. No approval may be granted automatically. No "latest ready request" lookup, cross-execution reuse, secret literal, environment read, or service-locator lookup is permitted.

The project owns the policy, resolver port, registry, persistence, validation, errors, and execution integration. Each Acceptance App owns its resolver implementation and exact allowed mappings. DK may resolve only the exact Operations Health `credential` requirement for the accepted 21 variants to the existing opaque local handle; just-in-time conversion to the target credential remains at the target-control boundary.

## UX Outcome

The preferred single-run and batch commands omit prerequisite preparation, Variant-key discovery for setup, request-ID copying, and prerequisite files. Catalog selectors choose variants; TMS creates and attaches distinct prerequisite requests automatically. The explicit prerequisite commands remain available for inspection and manual schemas.

## Failure Contract

`prerequisite_reference_unavailable` is the stable failure when an automatic reference is declared but the app resolver is missing, declines the exact context, throws, or returns a value that fails the existing reference validator.

```text
Category: configuration / prerequisite resolution
Retryable: No
Permanent: Yes until configuration/source is corrected
Admin Action Required: Yes
User / Operator Visible: Yes
Security Sensitive: No value may be shown
Should Log: Yes, with safe identity/correlation context only
Should Audit: Through the existing prerequisite request/input records and transition log
```

## Data and Compatibility

No migration is required. Existing request and input tables already store the required identity, schema fingerprint, actor provenance, and opaque reference. DK changes its schema identity from `operations-health-v1` to `operations-health-v2`; old prepared requests therefore continue to fail closed with `schema_changed`.

Existing explicit `--request-id` and batch prerequisite-reference behavior remains supported. Existing schemas remain manual because the new policy defaults to operator-provided.

## Required Records

- `docs/project/ai/changes/CHANGE-REQUEST-TMS-AUTOMATIC-PREREQUISITE-REFERENCES-0018.md`
- `docs/project/ai/remediations/REMEDIATION-PACK-TMS-AUTOMATIC-PREREQUISITE-REFERENCES-0012.md`
- `Modules/DK/docs/ai/changes/CHANGE-REQUEST-DK-AUTOMATIC-OPERATIONS-HEALTH-PREREQUISITE-0005.md`
- `Modules/DK/docs/ai/remediations/REMEDIATION-PACK-DK-AUTOMATIC-OPERATIONS-HEALTH-PREREQUISITE-0002.md`

## Notes

The authorized source implementation and automated validation are complete under Project Remediation 0012 and DK Remediation 0002. Live target/Browser execution was not required for this source change and remains separately gated. Operator acceptance, commit, and push retain their separate gates. Decision 0009 remains authoritative for operator input, approvals, and sensitive values; Decisions 0021–0023 remain authoritative for the bounded runtime and target-control boundary.
