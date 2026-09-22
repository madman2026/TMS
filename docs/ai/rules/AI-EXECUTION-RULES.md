# AI Execution Rules

## Purpose

These rules define how the AI Agent executes an AI Pack.

For documentation ownership, dependency synchronization, index maintenance, template synchronization, source update handling, periodic reviews, and documentation health checks, follow:

- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`

---

## Execution Unit

The AI Pack is the execution unit.

A Pack may contain one or more internal Tasks.

There is no separate `tasks/` folder.

---

## Before Implementation

Before changing files, the Agent must read the current AI Pack completely from beginning to end.

The Agent must not start implementation based only on partial sections, headings, search results, snippets, assumptions, or memory from previous Packs.

Before changing files, the Agent must identify:

- Pack ID and title
- goal
- allowed files to create
- allowed files to edit
- files to read/reference
- files that must not change
- plugin-first target path
- protected-area impact, if any
- tests to add
- tests to run
- stop conditions
- documentation maintenance impact, if any
- actual repository paths for files to create or edit
- command safety classification for any shell commands that may be run
- multilingual and RTL/LTR impact, if the Pack creates or modifies visible text, UI, validation messages, API messages, views, menus, permission labels, status labels, or owner-defined default/system text
- owner-declared API test artifact impact, if the Pack creates or modifies API endpoints, route paths, request/response contracts, authentication behavior, validation rules affecting API input, error codes, callback contracts, status endpoints, integration-test endpoints, or API examples
- configuration and settings impact, including config files, `.env` variables, Admin Settings, external-integration settings, secret storage, queue/cache/log settings, test setup, and operator manual setup
- UI/Admin UI impact, including Admin pages, settings pages, tables, forms, filters, buttons, actions, menus, dashboards, monitoring widgets, views, permissions, translations, and RTL/LTR impact
- error handling, error reporting, exception, logging, and traceability impact, including API error contracts, UI/Admin UI error messages, stable error codes, external-error normalization, retryable/permanent classification, structured log context, trace identifiers, sensitive data masking, error-specific tests, and operator troubleshooting impact
- owner-declared operator documentation impact, if the Pack creates or modifies operator-facing component behavior, administration UI, settings, permissions, reports, actions, workflows, troubleshooting, or recommended operator sequence

If the Pack requires confirmation, if the scope is unclear, or if required error handling, logging, traceability, error classification, or sensitive-data behavior is unspecified, the Agent must stop and ask before implementation.

---

## Operator Gate Before Implementation

Before implementing any AI Pack, the Agent must run or inspect the minimum Git pre-execution checks defined in:

```text
docs/ai/rules/GIT-AND-COMMIT-RULES.md
```

The Agent must report the result in chat before changing files.

The Agent must also read and report in chat the current Pack-specific operator checklist from:

```text
the current AI Pack
## 25. Operator Execution Checklist
### Before AI Execution
```

The Agent must also provide a short Pack intent summary in chat before implementation, using the operator communication language declared by the applicable project profile.

The summary must explain:

```text
Pre-execution Pack summary:

هدف این Pack:
- ...

امکانات یا تغییراتی که در این Pack اضافه/اصلاح می‌شود:
- ...
```

The Agent must not translate the full Pack.

The Agent must not claim that a feature will be completed unless it is explicitly inside the current Pack scope.

If the Pack is technical-only and does not add operator-visible functionality, the Agent must clearly state that fact in the profile-required operator language.

```text
This Pack adds no new operator-visible capability; its changes are technical or infrastructural.
```

If the Pack changes administration UI, settings, permissions, workflows, reports, integration behavior, data structure, migrations, API behavior, tests, documentation, or operator usage, the Agent must mention that impact in the summary.

After reporting the Git result, the `Before AI Execution` checklist, and the Pack intent summary, the Agent must wait for operator approval.

The Agent must not start implementation until the operator confirms that execution may continue.

If the branch is unexpected, the working tree contains unrelated uncommitted changes, the Pack intent summary exposes a scope mismatch, or the checklist exposes a blocker, the Agent must stop and ask the operator for guidance.


## Command Safety Rule

This section defines which shell commands the AI Agent may run for safe inspection, which commands require explicit Pack scope or operator approval, and which commands must not be run unless specifically approved.

These rules apply during AI Pack execution, review, remediation, documentation maintenance, source inspection, validation, and troubleshooting.

The goal is to prevent accidental file changes, dependency changes, database changes, cache changes, generated asset changes, service starts, long-running processes, external network access, or destructive operations.

### Command Classification

Before running any shell command, the Agent must classify the command as one of:

```text
safe-read-only
validation-or-test
mutating
destructive
long-running
external-network
unknown
```

The Agent must prefer safe read-only inspection commands.

If the command is `mutating`, `destructive`, `long-running`, `external-network`, or `unknown`, the Agent must not run it unless the current Pack explicitly allows it or the operator approves it.

### Safe Read-only Commands

The following commands are generally safe when used only for inspection and without mutating flags:

```bash
pwd
ls
find
tree
cat
head
tail
sed -n
grep
rg
wc
stat
file
du
```

Git inspection commands are safe when used read-only:

```bash
git status
git branch --show-current
git diff --name-only
git diff --stat
git diff
git log --oneline -5
git show --stat
```

Environment, version, dependency, and framework inspection commands are safe only when they are read-only and do not change cache, database, files, queues, generated assets, or runtime state. Resolve concrete command examples from the applicable project profile.

The Agent must avoid broad or expensive commands when a narrower command can answer the question.

### Validation or Test Commands

Validation and test commands may create temporary files, cache files, logs, test database records, coverage files, or snapshots.

They are not pure read-only commands.

They may be run only when the Pack asks for them, the validation rules require them, or the operator approves them.

Resolve concrete validation and test commands from the Pack and applicable project profile.

Formatting commands that modify files are mutating commands, not read-only validation commands.

These require Pack scope or operator approval.

### Mutating Commands

A mutating command changes files, generated assets, dependencies, cache, database state, queue state, configuration state, or application runtime state.

Mutating commands must not be run unless the current Pack explicitly allows them or the operator approves them.

CMS/framework maintenance, import, backup restore, update, component removal, theme removal, and cleanup commands are risky by default and must not be run unless the current Pack explicitly allows them or the operator approves the exact command and target.

Concrete dependency, build, migration, seeding, cache, queue, component, theme, publish, update, and cleanup commands are listed by the applicable project profile. Their presence in that profile does not authorize execution.

File-changing shell commands are mutating:

```bash
cp
mv
touch
mkdir
tee
sed -i
perl -pi
chmod
chown
ln
```

The Agent may only use file-changing commands when the current Pack scope allows creating or editing the target files.

### Risky and Forbidden Commands

The Agent must treat risky commands as unsafe by default.

Risky commands include any command that may:

```text
- modify source files;
- modify generated files;
- install, update, remove, or regenerate dependencies;
- change database schema or data;
- clear, rebuild, or warm application cache;
- publish, build, or overwrite assets;
- start workers, servers, watchers, daemons, or long-running processes;
- reset, clean, restore, overwrite, or delete repository files;
- access the external network;
- affect production, shared, or unknown environments.
```

The Agent must not run risky commands unless one of these is true:

```text
- the current Pack explicitly requires the command;
- the command is required by an approved validation or test step;
- the operator explicitly approves the exact command and target.
```

The following commands are forbidden unless the operator explicitly approves the exact command and exact target:

```bash
rm
rm -rf
rmdir
truncate
dd
Remove-Item -Recurse
git reset --hard
git clean
git clean -fd
git checkout -- .
git restore .
git restore path/to/file
drop database
docker compose down -v
```

The applicable project profile lists additional framework-specific destructive database, restore, component-removal, and theme-removal examples. Those examples are equally forbidden without exact approval.

If a destructive command is needed to recover from a blocked Pack, conflict, or failed execution, follow:

```text
docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md
docs/ai/rules/GIT-AND-COMMIT-RULES.md
```

### Dependency-changing Commands

The Agent must not run dependency-changing commands unless the Pack explicitly allows dependency changes or the operator approves them.

Resolve dependency-manager commands from the applicable project profile. Install, update, remove, audit-fix, lockfile rewrite, and dependency regeneration operations are dependency-changing even when a tool presents them as routine maintenance.

### Long-running Commands

Long-running commands must not be started unless the Pack explicitly requires them or the operator approves them.

Examples include development servers, workers, queue listeners, schedulers, watchers, supervisors, and container stacks. Resolve concrete commands from the applicable project profile.

The Agent must not start background processes silently.

If a long-running command is required, the Agent must state why it is needed and how it will be stopped or avoided.

### External Network Commands

Commands that access the external network must not be run unless the current Pack explicitly requires them or the operator approves them.

Examples include URL download clients, dependency-manager operations, remote Git fetch/pull, container pulls, package registry calls, and remote documentation imports.

Read-only network commands are still external-network commands.

The Agent must not fetch new dependencies, overwrite source references, or update external documentation silently.

### Database Safety Rule

The Agent must not run commands that change database schema or data unless explicitly allowed by the Pack or operator.

Read-only database inspection may be allowed only when the Pack requires it and the target database is clear.

The Agent must never run destructive database commands against an unknown, production, shared, or non-test database.

Migration commands must follow the Pack's Data / Migration Rules.

### Queue, Cache, and Runtime State Rule

The Agent must not start workers, process queues, clear caches, rebuild caches, publish assets, or modify runtime state unless explicitly allowed.

These commands may affect application behavior outside the current Pack scope.

Resolve concrete worker, queue, cache, optimization, asset-publish, build, development-server, and watcher commands from the applicable project profile.

### Required Report Before Risky Command

If a risky, mutating, destructive, long-running, external-network, or unknown command appears necessary, the Agent must stop and report:

```text
Command:
Command Classification:
Reason Needed:
Expected Effect:
Affected Files / Database / Runtime State:
Pack Allows It: Yes / No
Operator Approval Required: Yes / No
Safer Alternative:
```

The Agent must prefer safer alternatives such as read-only inspection, targeted file reads, `--dry-run`, `--test`, safe non-mutating validation, or reporting the required command as a follow-up instead of running it.

### Command Reporting Rule

Any command that is run during execution must be reported in the Agent Final Report or Run Report according to:

```text
docs/ai/rules/REPORTING-RULES.md
docs/ai/templates/RUN-REPORT-TEMPLATE.md
```

Reports must not claim a command was run if it was not run.

Reports must not claim validation passed unless the command actually passed.

### Stop Conditions

The Agent must stop and ask for operator guidance if:

```text
- the command may modify files outside Pack scope;
- the command may change database schema or data;
- the command may change dependencies;
- the command may clear, rebuild, or warm cache;
- the command may publish, build, or overwrite assets;
- the command may start a long-running process;
- the command may access the external network;
- the Agent cannot determine whether the command is safe;
- the command target is ambiguous;
- the command may affect production, shared, unknown, or non-test data.
```

## General Stop Conditions

The Agent must stop immediately and report the issue before continuing if any of the following conditions occurs:

```text id="tkwdm5"
- the project structure is unclear;
- the target file path does not match the current AI Pack and the mapping is not reliable;
- the implementation appears to require `migrate`, `migrate:fresh`, `db:wipe`, `db:seed`, or another database-changing command not explicitly approved by the Pack or operator;
- an external-service secret, API key, callback token, service token, webhook secret, signing key, raw secret, or sensitive credential may be exposed;
- a direct core modification appears necessary outside the approved plugin-first or Pack scope;
- the Task requires behavior, capability, or architecture outside the current Release or Phase;
- tests fail and the smallest safe fix is not clear;
- external-integration logic is being written outside the boundary approved by the applicable owner profile or Canonical files;
- asynchronous or externally executed work is being performed synchronously in a layer forbidden by the applicable owner contract;
- callback or inbound-event handling changes owner-managed state outside the approved processing and state-transition boundary;
- a sensitive target identifier, unsafe external payload, raw secret, token, Authorization header, or sensitive credential is returned through API, Admin UI, report, export, log, or AI execution report;
- the Agent discovers a conflict between the Pack, Canonical files, implementation state, source documentation, or accepted decisions;
- the Agent cannot determine whether the next action is safe, in scope, or allowed.
```

When a stop condition occurs, the Agent must report:

```text id="0vaixn"
Stop Condition:
Affected Pack:
Affected File(s):
Reason:
Risk:
Current State:
Recommended Next Action:
Operator Decision Needed: Yes / No
```

The Agent must not continue implementation until the operator provides direction, unless the Pack or an existing rule explicitly defines a safe next step.

## During Implementation

The Agent must:

- stay inside the Pack scope
- follow canonical decisions
- do not introduce owner-defined source, consumer, channel, or integration values that are not approved by the applicable profile or Canonical files
- do not introduce consumer-specific execution logic outside an approved owner boundary
- keep shared/public/core/application/domain code independent of a concrete external service, transport, consumer, or vendor unless the selected owner explicitly defines that boundary
- do not call external services or perform asynchronous external work from layers forbidden by the applicable owner profile or Canonical files
- follow the Plugin-first Development Rule in `docs/ai/rules/SCOPE-CONTROL-RULES.md`
- follow the current Release and Phase guides
- follow commenting, validation, security, migration, testing, and final-report rules
- avoid opportunistic refactoring
- stop on conflicts, unsafe ambiguity, or out-of-scope changes
- follow `DOCUMENT-MAINTENANCE-RULES.md` if AI documentation files are created or changed
- resolve generic AI Pack paths according to `SCOPE-CONTROL-RULES.md`
- stop on scope, plugin-first, protected-area, or architecture-boundary violations according to `docs/ai/rules/SCOPE-CONTROL-RULES.md`
- classify shell commands before running them; prefer safe read-only commands and do not run mutating, destructive, long-running, external-network, or unknown commands unless the Pack explicitly allows them or the operator approves them
- follow the Pack `Configuration / Settings Requirements` section and avoid introducing implicit config, env, Admin Settings, or secret-storage changes that are not documented in the Pack
- follow multilingual and directionality rules in the applicable profiles, Canonical files, and Pack `Multilingual / Translation Requirements` section when the Pack creates or modifies visible text, UI, validation messages, API messages, views, menus, permission labels, status labels, or owner-owned default/system text
- follow the Pack `UI / Admin UI Requirements` section and avoid introducing UI surfaces, routes, menus, forms, tables, actions, settings UI, dashboard UI, assets, or permission-gated UI behavior that are not documented in the Pack
- update or record required follow-up for owner-declared API test artifacts when the Pack changes API behavior
- follow the Pack `Operator Documentation Requirements` section and update or record required follow-up for the guide path declared by the applicable owner profile when operator-facing behavior changes
- follow `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md` when the Pack creates or modifies API errors, UI/Admin UI errors, exceptions, external-integration errors, callback/inbound-event errors, queue/job failures, status-transition errors, retry/dead-letter errors, logs, trace identifiers, or error visibility
- preserve stable, language-neutral `error_code` values and do not replace specific error behavior with vague generic errors
- keep human-readable error messages clear, translated when required, safe, and actionable
- normalize external-integration-specific errors before they affect owner-managed state, retry, reporting, or UI/API behavior
- use structured log events and include the relevant trace context required by the current Pack
- propagate and preserve the request, correlation, domain-record, operation, inbound-event, external-reference, and source identifiers declared by the applicable owner across the execution flow
- do not expose raw exceptions, stack traces, credentials, tokens, Authorization headers, secrets, sensitive target identifiers, or unsafe external payloads through API responses, Admin UI, logs, reports, tests, or execution reports
- implement and run error-specific tests that verify the actual error code, response contract, classification, state change, log context, trace identifiers, and masking behavior required by the Pack
- stop if the implementation requires inventing an unapproved error code, error classification, status transition, retry decision, logging policy, or sensitive-data visibility rule


---

## Development Style Rule

During implementation, the Agent must prefer small, focused, reviewable changes.

The Agent must:

```text id="lxz22s"
- work in small, incremental steps;
- keep each Task or Pack output easy to review;
- avoid creating large files unless the Pack scope or architecture requires it;
- keep Controllers, Requests, Admin actions, Forms, and Tables thin;
- place core business and execution logic in Services, Actions, Handlers, Jobs, or equivalent application-layer classes;
- use DTOs or explicit data objects when passing structured data between layers, when this improves clarity, validation, or service-readiness;
- use interfaces/contracts for external integrations and external-execution boundaries;
- use clear Enums, constants, or approved status definitions for statuses and state transitions;
- prefer small, focused tests that validate the changed behavior;
- deliver the result with an accurate Agent Final Report and, when required, a Run Report.
```

The Agent must not introduce large abstractions, broad refactors, generic frameworks, or unrelated cleanup only to satisfy style preferences.

Development style must support Pack scope, reviewability, service-ready architecture, and future maintainability.

## Document Scope Rule

The AI Agent must not place Release-specific or Phase-specific execution content inside global documentation files.

Global files must remain release-neutral and phase-neutral.

If the update is specific to a Release, place it under the relevant `release-{N}` folder.

If the update is specific to a Phase, place it under the relevant `phase-{X}` folder.

If the update is a global rule, template, routing rule, or structure rule, place it in the relevant global file.

For the complete rule, read:

- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`

---

## Index Maintenance Rule

If the current work creates, moves, archives, supersedes, rejects, accepts, closes, or materially changes a tracked AI documentation file, the related INDEX file must be checked.

If an INDEX update is required by active AI documentation rules and is directly related to the current Pack execution, it may be performed under the Governance Maintenance Exception defined in:

* `docs/ai/rules/SCOPE-CONTROL-RULES.md`

If the current Pack explicitly forbids editing the related INDEX file, or if the Agent is unsure whether the INDEX update is allowed, the Agent must not edit it silently.

Instead, it must report the required update under:

```text
Required Follow-up Updates
```

For the complete index maintenance rule and impact matrix, read:

* `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`

---

## Operator Gate After Implementation

After Pack implementation and before producing the Agent Final Report, the Agent must run or inspect the minimum Git post-execution checks defined in:

```text
docs/ai/rules/GIT-AND-COMMIT-RULES.md
```

The Agent must compare the changed files from `git diff --name-only` against:

* `Files to Create`
* `Files to Edit`
* allowed governance or maintenance updates
* files explicitly approved by the operator

The Agent must classify changed files as:

```text
allowed Pack implementation changes
allowed governance or maintenance updates
unauthorized out-of-scope changes
```

The Agent must report the Git result, changed-file comparison, and changed-file classification in chat.

The Agent must also report project/framework source-guidance status in chat when the Pack includes project-specific implementation or when the Pack `Files to Read / Reference` section lists source-guidance requirements.

This is a temporary chat-facing operator gate check.

The Agent must report:

```text
Project / Framework Source Guidance Status:
- Project- or framework-specific implementation detected: Yes / No
- Source guidance required by Pack or applicable profile: Yes / No / Not applicable
- Source guidance file(s):
  `<resolved from the applicable project profile>`
- Read in current Codex chat: Yes / No / Already read earlier / Not applicable
- Re-check required by current Pack: Yes / No / Not applicable
- Notes:
```

The Agent must not claim source guidance was read unless it was actually read in the current Codex chat or was already read earlier in the same Codex chat.

If source guidance was required by the Pack or applicable profile but was not read, the Agent must clearly state this in the chat-facing gate output.

The source-guidance status is part of the operator gate output and must not be copied into the Agent Final Report or Run Report unless it exposes a decision-relevant issue, blocker, missing required reference, implementation risk, or required follow-up.

The Agent must also report the Error Handling / Logging / Traceability execution status in chat when the Pack has impact in this area.

Use:

```text id="pimq3g"
Error Handling / Logging / Traceability Status:

- Error handling impact detected: Yes / No
- API error contract created or changed: Yes / No / Not applicable
- UI / Admin UI error behavior created or changed: Yes / No / Not applicable
- Stable error codes created or changed: Yes / No / Not applicable
- Exception handling created or changed: Yes / No / Not applicable
- External-error normalization created or changed: Yes / No / Not applicable
- Structured logs created or changed: Yes / No / Not applicable
- Trace identifiers created, propagated, or changed: Yes / No / Not applicable
- Retryable / permanent / admin-action classification verified: Yes / No / Not applicable
- Sensitive data masking or omission verified: Yes / No / Not applicable
- Error-specific tests added: Yes / No / Not applicable
- Error-specific tests run: Yes / No / Not applicable
- Error-specific tests passed: Yes / No / Not run / Not applicable
- Missing or unresolved error-handling work:
  - ...
- Required follow-up:
  - ...
```

The Agent must not report an item as verified unless it was actually inspected or tested.

If the Pack has no error handling, logging, or traceability impact, the Agent must report:

```text id="9df6d0"
No error handling, logging, or traceability impact detected for this Pack.
```

If required error handling, logging, tracing, masking, or error tests are missing, the Agent must report the issue before requesting post-execution operator approval.

The Agent must not hide unresolved vague errors, missing trace context, missing error classification, unsafe sensitive-data exposure, or skipped required error tests.

The Agent must also read and report in chat the current Pack-specific operator checklist from:

```text
the current AI Pack

## 11. Configuration / Settings Requirements
### Operator Setup Notes

## 25. Operator Execution Checklist
### After AI Execution
```

After reporting the post-execution Git result, changed-file classification, Error Handling / Logging / Traceability status when applicable, and the `After AI Execution` checklist, the Agent must wait for operator approval before producing the Agent Final Report.

If the operator requests fixes, reversions, additional checks, or test corrections, the Agent must apply only approved in-scope actions and then repeat the post-execution gate before producing the Agent Final Report.

The full operator gate output is chat-facing and must not be copied into the Agent Final Report or Run Report.

If a gate result exposes a decision-relevant issue, blocker, scope violation, failed or skipped test, unauthorized change, documentation maintenance issue, or required follow-up, that issue must still be reflected in the appropriate Agent Final Report section and, if a Run Report is created, in the appropriate Run Report section.

---

## After Implementation

The Agent must produce an Agent Final Report using:

- `docs/ai/rules/REPORTING-RULES.md`

The operator must review:

- changed files
- human review section
- tests
- git diff
- scope violations
- unresolved questions
- documentation maintenance impact
- required follow-up updates
- verify whether owner-declared API test artifacts were checked, updated, not applicable, or require follow-up when API behavior changed
- verify that required configuration, environment, Admin Settings, external-integration settings, secret settings, test setup, and operator manual setup were completed, documented, or recorded as follow-up
- verify that profile-required locales, translation keys, API messages, administration UI labels, directionality considerations, and operator translation review notes were completed, documented, or recorded as follow-up
- verify that required UI surfaces, routes, menus, permissions, forms, tables, actions, settings UI, dashboard UI, translations, RTL/LTR considerations, and operator UI review notes were completed, documented, or recorded as follow-up
- verify that required owner-declared operator-guide updates, workflow notes, permission notes, screenshots, and guide follow-ups were completed, documented, or recorded as follow-up
