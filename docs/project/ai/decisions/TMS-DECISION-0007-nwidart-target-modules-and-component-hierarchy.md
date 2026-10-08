# TMS-DECISION-0007 — Nwidart target modules and component hierarchy

Status: Accepted
Scope: Project architecture / target-system ownership
Source: Operator decision
Date: 2026-10-07
Review At: execution of the first target-module bootstrap Pack
Blocking: Yes for target-module implementation
Closure Condition: superseded by a separately approved project architecture decision
Next Review At: `AI-PACK-TMS-DK-TARGET-MODULE-BOOTSTRAP-0016`

## Type

4. Project-level architecture decision

## Decision

Every independently tested target system is represented by a Laravel module that follows the installed `nwidart/laravel-modules` architecture. DK is one target module. A product area such as Notification Delivery is a first-class Component inside the DK module unless it becomes an independently deployed and independently tested target system.

The executable hierarchy is:

```text
App -> Component -> Suite -> Scenario -> Variant -> Step
```

`app_key`, `component_key`, `suite_key`, `scenario_key`, and `variant_key` are explicit, stable, language-neutral identifiers. `component_key` is not encoded only in tags or long scenario names. Executable scenarios remain source code and are registered explicitly through the target module provider. Filesystem scanning and database-defined executable workflows are not allowed.

Each target module must keep the standard Nwidart files (`module.json`, `composer.json`, providers, `app`, `config`, `database`, `tests`, and only-needed `routes`/`resources`) and an owner-local `docs/ai` root after ownership activation. App-wide authentication, accounts, fixtures, cleanup, actions, selectors, and oracles belong under the target module's shared Acceptance area. Component-specific catalogs, suites, scenarios, variants, actions, selectors, fixtures, and oracles remain under that Component.

Laravel and Nwidart generators and framework services must be used wherever they provide the required behavior. TMS-specific scaffolding may add only the missing Acceptance conventions and must preserve a simple operator workflow.

## Reason

TMS must onboard several systems without coupling shared runtime code to DK or ND. A Nwidart module gives each target an explicit provider, source boundary, test boundary, and future documentation owner. Components keep large systems navigable without turning every internal capability into an independently governed Laravel module.

## Impact

- Decision 0002 remains valid and is clarified: an Acceptance App is implemented as a Nwidart target module in this repository.
- Existing project descriptors, selectors, catalogs, and traceability must gain an explicit Component dimension.
- Core remains target-neutral.
- The first project Pack may bootstrap and activate a new module owner; later module-owned Packs must live in that owner's local Pack index.
- No DK or ND implementation is authorized by this decision alone.

## Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: Not applicable
UI / Admin UI Error Impact: Not applicable
Error Code Impact: Yes — validation must distinguish duplicate or missing hierarchy keys
Exception Handling Impact: Yes
External Error Normalization Impact: Not applicable
Retry / Failure Classification Impact: Not applicable
Status Model Impact: No
Logging Impact: Yes — execution context gains app/component/suite/scenario/variant identifiers
Traceability Impact: Yes
Sensitive Data Impact: No new sensitive value storage
Testing Impact: Yes
Documentation Impact: Yes
```

## Required Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-ROADMAP-0003.md` authorizes replacement of the earlier target-App roadmap and normalization of future Packs.

## Required Follow-up Updates

- Implement the hierarchy and scaffold through Packs 0010 and 0016.
- Update the Persian Acceptance CLI guide only when the related commands exist.
- Create DK-local Pack records only after Pack 0016 activates the DK documentation owner.

## Review / Resolution

```text
Reviewed In: operator discussion on reusable TMS architecture and Nwidart structure
Review Result: operator explicitly approved one target system per module and first-class Components inside the module
Resolution: accepted
Next Review At: first target-module bootstrap
Resolution Notes: internal Components are not Nwidart modules by default.
```
