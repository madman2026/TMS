# Git and Commit Rules

## Before Pack Execution

Run or inspect:

```bash
git status
git branch --show-current
```

Do not start Pack execution on an unexpected branch or with unrelated uncommitted changes unless the operator approves.

## After Pack Execution

Run or inspect:

```bash
git status
git diff --name-only
```

Optional deeper review:

```bash
git diff --stat
git diff
git log --oneline -5
```

Use git log --oneline -5 only when recent commit history is needed.

## Unauthorized Changes

If changed files are outside the Pack implementation scope, the operator must first classify them as one of:

- allowed Pack implementation changes
- allowed governance or maintenance updates
- unauthorized out-of-scope changes

Governance or maintenance updates are not unauthorized when they are directly related to the current Pack and allowed by the active AI documentation rules.

If changed files are outside both the Pack scope and allowed governance/maintenance updates, the operator must reject the task or request a targeted revert of unauthorized changes.

## Blocked Pack Commit Rule

If a Pack execution is stopped because of a canonical conflict, Change Request requirement, remediation requirement, unauthorized scope change, or unresolved operator decision, do not commit the partial Pack result.

The Agent must report the blocked state and follow the relevant conflict/change/remediation workflow.

Incomplete changes from a blocked Pack must be reverted unless the operator explicitly approves keeping them.

A blocked Pack must not receive an accepted Run Report.

After the required Change Request or Remediation Pack is completed and committed, the blocked Pack may be restarted, regenerated, or normalized against the updated canonical decisions.

## Pre-Commit Run Report and API Test Artifact Gate

Before committing any completed executable Pack, check whether a persistent Run Report is required and whether an API test artifact declared by the selected owner must be checked or updated.

Executable Packs include:

* standard AI Packs from `<owner-docs-root>/packs/`
* Remediation Packs from `<owner-docs-root>/remediations/`
* Review or documentation-maintenance Packs, if they are executed as Packs and produce committed changes

When the operator says to commit the completed Pack work, the Agent must not commit immediately.

Before creating the commit, the Agent must verify that required execution audit records, validation artifacts, and API test artifacts are complete.

If required, the Run Report must be created before the commit according to:

* `docs/ai/rules/REPORTING-RULES.md`
* `docs/ai/templates/RUN-REPORT-TEMPLATE.md`

Run Reports are stored under:

```text
<owner-docs-root>/runs/
```

Do not store Remediation Pack run reports under `<owner-docs-root>/remediations/`; store them under `<owner-docs-root>/runs/`.

Before commit, also verify that the Run Report records governance and maintenance status for the current Pack.

At minimum, check that required documentation, index, decision, canonical, change, remediation, review, and follow-up updates are either completed or recorded in the Run Report.

If the Pack created or modified any of the following, the Agent must check the applicable owner profile for a required API test artifact before commit:

```text
- API endpoint
- route path
- HTTP method
- request headers
- authentication or service token behavior
- request body
- query parameters
- validation rule affecting API input
- response body
- response status code
- error_code
- callback or inbound-event endpoint contract
- status endpoint contract
- external-integration test endpoint contract
- health endpoint contract
- API example
```

If an API test artifact update is required, resolve its approved collection/specification and environment/configuration paths from the applicable profile. Do not invent a central path.

If an environment or configuration artifact is used, update it only with safe placeholder variables. The Agent must not store real secrets, service tokens, external-service credentials, callback tokens, Authorization headers, sensitive real identifiers, or production URLs in API test artifacts.

Use placeholders such as:

```text
{{base_url}}
{{service_token}}
{{callback_token}}
{{request_id}}
{{domain_record_id}}
{{external_integration_id}}
{{sensitive_target}}
{{idempotency_key}}
```

Correct pre-commit sequence:

```text
Operator says: commit
  ↓
Check working tree and diff
  ↓
Check whether API behavior changed
  ↓
If API changed, check/update the owner-declared API test artifacts
  ↓
Create or update Run Report
  ↓
Ensure Run Report records API test artifact status
  ↓
Final git diff review
  ↓
Commit
```

The Run Report must be finalized after API test artifact impact is resolved, because the Run Report must record the final artifact status.

If the owner-declared API test artifact does not exist yet and the Pack introduced the first testable API endpoint, the Agent may create the initial artifact only if this is allowed by the Pack scope, testing rules, profile, or operator instruction.

If the required API test artifact cannot be updated safely before commit, the Agent must stop or record the missing update under `Required Follow-up Updates`, depending on whether it is blocking for the current Pack.

The commit must include the API test artifact update when it is directly required by the Pack's API changes.

Do not commit if the Run Report shows unresolved blocking issues, unsafe partial changes, required remediation, unresolved canonical conflict, required approval before acceptance, or blocking API test artifact updates that are not completed.

The commit message must not mention secrets, real credentials, real tokens, sensitive real identifiers, or sensitive external-service data.

## Commit Message

The commit message should mention:

- Release
- Phase
- Pack ID
- short purpose

For Remediation commits, include the Remediation Pack ID when applicable.

Only for Example:

```text
R{N} P{X} AI-PACK-ID: short imperative summary
R{N} P{X} REMEDIATION-PACK-ID: short imperative summary
```
