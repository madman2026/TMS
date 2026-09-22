# Operator Workflow

## Purpose

This file defines the practical workflow that the human operator should follow when working with the AI Agent.

This file is an operator guide.

It tells the operator:

- when to start a new chat
- what to ask the Agent to read
- how to start a Pack
- how to check Agent output
- when to request a Run Report
- when to commit
- when to request a formal Review
- what to do when a conflict or blocked Pack appears
- what to do at the end of a Phase or Release

This file does not replace the detailed rule files.

Detailed behavior remains owned by the relevant rule files selected through:

```text
docs/ai/rules/RULES-INDEX.md
```

---

## 1. New Phase Chat

Open a new Codex chat for each Phase.

At the beginning of the chat, the operator should ask the Agent to:

1. check the current git branch
2. check git status
3. check git branch --show-current
4. make no file changes
5. read only the mandatory startup files first
6. follow the context map for additional files
7. load the project and owner profiles declared by the selected route or manifest
8. provide a short context summary before receiving the first Pack

Recommended operator prompt:

```text
Start a new Phase execution context.

First, check git branch and git status without changing files.

Then read only:

- AGENTS.md
- docs/ai/start/START-HERE.md
- docs/ai/start/CONTEXT-MAP.md

After that, use the context map to identify the minimum required files for the current Release and Phase, and load the project and owner profiles declared by the selected route or owner manifest.

Do not read unrelated Releases, Phases, Runs, Reviews, or reference files unless required.

Give me a short context summary before I provide the next AI Pack.
```

---

## 2. Phase Continuation Chat

A Phase may use more than one Codex chat when the Phase is large, long-running, sensitive, or context-heavy.

Use a continuation chat when:

* several Packs have already been executed in the same Phase
* the chat context becomes too long
* the Agent starts mixing old and current assumptions
* the next Pack is sensitive
* a conflict, Change Request, or Remediation changed the Phase context
* several days passed since the previous Pack execution
* the operator wants a clean execution context without leaving the current Phase

A continuation chat is not a new Phase.

It must continue the same Release and Phase with fresh minimal context.

### Final Codex Chat Scope Rule

The project execution management unit is the Phase, but the practical Codex chat execution unit may vary based on Phase size and sensitivity.

For small Phases, one Codex chat may be enough for the entire Phase.

For large, long-running, or context-heavy Phases, split the Phase execution into multiple continuation chats inside the same Phase.

For large Phases, the default recommendation is to open a new continuation chat every 4 to 6 Packs, or sooner if the context becomes heavy.

For sensitive Packs, open a continuation chat even if fewer than 4 Packs were executed.

Do not open a separate chat for every Pack unless one of these conditions is true:

* the Pack is sensitive
* the Pack touches migrations, security, providers, callbacks, status models, permissions, queues, or important contracts
* the previous chat context became too long
* the Agent starts mixing old and current assumptions
* a significant conflict, Change Request, or Remediation occurred
* several days passed since the previous Pack execution
* the operator wants a clean context for the next Pack execution

Using one chat for an entire Release is not recommended because a Release usually contains multiple Phases and many decisions, making the context too large and ambiguous.

In a continuation chat, the Agent must read only the minimum context relevant to the same Release, same Phase, and current Pack, including applicable project and owner profiles declared by the context map or owner manifest. It must not read all Packs, all Runs, all Reviews, unrelated Phase files, graph files, or full project analysis.

### Recommended Operator Prompt

```text
This is a continuation chat inside the same Release and Phase.

Do not treat this as a new Phase.

First, check git branch and git status without changing files.

Then read only:

- AGENTS.md
- docs/ai/start/START-HERE.md
- docs/ai/start/CONTEXT-MAP.md
- <owner-docs-root>/canonical/CANONICAL-INDEX.md
- <owner-docs-root>/guides/GUIDES-INDEX.md
- current Release canonical file
- current Phase canonical file
- current Release summary guide
- current Phase summary guide
- <owner-docs-root>/packs/PACKS-INDEX.md
- <owner-docs-root>/runs/RUNS-INDEX.md only to locate latest relevant Run Reports
- latest relevant accepted or executed Run Reports only if needed
- the current AI Pack only

Do not read all Packs, all Runs, all Reviews, unrelated Phase files, graph files, or full project analysis.

After reading the minimum required context, summarize:

- current Release
- current Phase
- last known accepted/executed Pack
- current Pack to execute
- relevant open follow-ups, decisions, changes, remediations, or blockers
- files you need before implementation

Do not start implementation until I provide or confirm the current Pack.
```

---

## 3. Before Each Pack

Before giving approval for Pack implementation, the operator should provide or confirm the current AI Pack and require the Agent to perform the pre-execution operator gate.

The Agent must not start implementation immediately after receiving the Pack.

The Agent should first summarize:

* Goal
* Scope
* Files it may create
* Files it may edit
* Files it must not change
* Tests to add
* Tests to run
* Stop conditions
* Any unclear instructions

Then the Agent must perform the pre-execution operator gate defined in:

```text
docs/ai/rules/AI-EXECUTION-RULES.md
```

The pre-execution gate includes:

* checking git status
* checking the current branch
* reporting the result in chat
* repeating the current Pack section:

  * `## 24. Operator Execution Checklist`
  * `### Before AI Execution`

The operator must review the pre-execution gate output before allowing implementation.

Recommended operator prompt:

```text
Here is the next AI Pack.

Before implementation:

1. Summarize the Pack scope.
2. Check git status and current branch.
3. Repeat the Pack `Before AI Execution` checklist.
4. Report the result in chat.
5. Wait for my approval before changing files.

Do not start implementation until I approve.
```

If the Agent asks clarification questions, the operator should answer clearly and ask the Agent to classify the answer if it affects scope, decisions, Canonical files, Change Requests, or Remediation.

---

## 4. During Pack Execution

During Pack execution, the operator should not ask the Agent to opportunistically improve unrelated areas.

If the Agent discovers an issue outside Pack scope, the operator should ask the Agent to:

1. stop if the issue affects safety, architecture, contracts, data, tests, or accepted behavior
2. report the issue
3. identify whether it is a clarification, decision, conflict, Change Request, or Remediation trigger
4. continue only if the issue is inside Pack scope or the operator explicitly approves the next path

The Agent should not silently expand scope.

---

## 5. Operator Answers and Decisions

When the operator answers a question, the operator should ask the Agent to classify the answer if it may affect:

- implementation scope
- architecture
- contracts
- Canonical files
- future Packs
- Change Requests
- Remediation Packs
- release or phase behavior

The operator does not need to create a formal Review for every answer.

The normal decision path is:

```text
Operator answer
→ classification
→ clarification or implementation note
→ decision if needed
→ Canonical update if official and allowed
→ Change Request if accepted contract must change
→ Remediation Pack if implementation correction is required
```

Decision classification is owned by the decision rules, not by this file.

---

## 6. After Each Pack

After Pack implementation, the Agent must not immediately produce the Agent Final Report.

First, the Agent must perform the post-execution operator gate defined in:

```text
docs/ai/rules/AI-EXECUTION-RULES.md
```

The post-execution gate includes:

* checking git status
* checking `git diff --name-only`
* comparing changed files against:

  * `Files to Create`
  * `Files to Edit`
  * allowed governance or maintenance updates
  * files explicitly approved by the operator
* classifying changed files as:

  * allowed Pack implementation changes
  * allowed governance or maintenance updates
  * unauthorized out-of-scope changes
* reporting the result in chat
* repeating the current Pack section:

  * `## 24. Operator Execution Checklist`
  * `### After AI Execution`

The operator must review the post-execution gate output before allowing the Agent to produce the Agent Final Report.

If the operator requests fixes, reversions, additional checks, or test corrections, the Agent must apply only approved in-scope actions and then repeat the post-execution gate.

Only after the operator approves the post-execution gate should the Agent produce the Agent Final Report.

Recommended operator prompt:

```text
Before producing the Agent Final Report:

1. Check git status.
2. Check git diff --name-only.
3. Compare changed files with Files to Create and Files to Edit.
4. Classify changed files as allowed implementation, allowed governance/maintenance, or unauthorized out-of-scope.
5. Repeat the Pack `After AI Execution` checklist.
6. Report the result in chat.
7. Wait for my approval before producing the Agent Final Report.
```

After the Agent Final Report is produced, the operator should review:

1. Agent Final Report
2. changed files
3. scope compliance
4. tests added
5. tests run
6. failed or skipped tests
7. human review section
8. documentation/index/canonical follow-up updates
9. decision/change/remediation needs
10. unauthorized file changes, if any

Do not commit only because the Agent says the task is complete.

The operator must validate the result.

---

## 7. Run Report Before Commit

For accepted Pack work that should be preserved as execution history, the Run Report must be created or verified before commit.

Normal flow:

```text
Pack execution
→ Post-execution operator gate
→ Operator approval
→ Agent Final Report
→ Operator Check
→ Run Report
→ Commit
```

Before commit, the operator should verify:

- Agent Final Report is complete
- Run Report exists if required
- git status is expected
- git diff is reviewed
- tests are complete or skipped tests are explained
- no unauthorized files changed
- required documentation/index updates are done or recorded as follow-up
- required decisions are recorded
- required Canonical updates are approved
- required Change Requests or Remediation Packs are handled
- Postman Collection impact is checked when the Pack changed API behavior
- Postman Collection is updated when required
- Postman Environment is updated when required, using placeholders only
- Postman updates, skipped updates, or required follow-ups are recorded in the Run Report


Recommended operator prompt:

```text
Create the Run Report for this accepted Pack before commit.

Make sure it includes:

- Pack ID
- execution summary
- changed files
- tests run
- test results
- Postman Collection impact
- Postman Collection updates, if API behavior changed
- scope compliance
- documentation maintenance
- decisions/canonical/change/remediation notes
- required follow-up updates
```

Do not commit completed Pack work before Run Report and validation checks are complete.

---

## 8. Commit Readiness

The operator should commit only after:

1. the Pack output is accepted
2. the Run Report is created or verified
3. Postman Collection impact is checked and updated when the Pack changed API behavior
4. tests pass or skipped tests are explicitly justified
5. git diff has been reviewed
6. scope compliance is confirmed
7. unauthorized changes are reverted
8. required follow-up updates are either completed or recorded
9. no blocking conflict remains unresolved

Recommended operator prompt:

```text
Check whether this work is ready to commit.

Confirm:

- branch
- git status
- diff summary
- tests
- scope compliance
- Run Report status
- Postman Collection status, if API behavior changed
- unresolved blockers
```

Commit rules are owned by the git and operator workflow rules selected through `RULES-INDEX.md`.

---

## 9. Failed Tests or Validation Problems

If a test fails, the operator should not ask the Agent to rewrite the whole Pack.

The operator should ask the Agent to:

1. identify the failing test
2. explain the likely cause
3. identify the smallest required fix
4. confirm the fix is inside Pack scope
5. fix only that failure
6. rerun the relevant test
7. update the final report or Run Report with the initial failure and final result

Recommended operator prompt:

```text
A test failed.

Do not rewrite the whole Pack.

Identify the failing test, likely cause, affected file, and smallest fix.

Confirm the fix is inside Pack scope before changing files.
```

---

## 10. Unauthorized or Out-of-Scope Changes

If an unauthorized file changed, the operator should ask the Agent to:

1. identify the unauthorized file
2. explain why it changed
3. confirm whether the change is required
4. revert only the unauthorized change if not approved
5. report the scope issue in the Agent Final Report or Run Report

Recommended operator prompt:

```text
This file appears to be outside Pack scope:

<file path>

Explain why it changed.

If it is not explicitly allowed, revert only this unauthorized change.
```

If the out-of-scope change reveals a required product or architecture change, do not silently keep it.

Use the conflict/change/remediation workflow if required.

---

## 11. Conflict or Blocked Pack

If a conflict, accepted-contract change, cross-phase impact, Canonical/code mismatch, or unsafe partial implementation is discovered, the operator must not ask the Agent to continue the Pack normally.

The blocked Pack must not be committed as successful work.

Expected flow:

```text
Conflict found
→ Pack blocked
→ partial changes inspected
→ unsafe partial changes reverted
→ Decision Record if required
→ Change Request if required
→ Remediation Pack if required
→ Remediation execution
→ Run Report
→ Review if required
→ Commit remediation after validation
→ normalize or restart blocked Pack
```

Recommended operator prompt:

```text
Stop normal Pack execution.

Classify this as a conflict or blocked Pack if applicable.

Report:

- conflict type
- conflicting sources
- affected Pack
- partial changes
- whether revert is required
- whether Decision Record is required
- whether Change Request is required
- whether Remediation Pack is required
- whether Canonical update is required
- recommended next action
```

If the conflict requires only Canonical correction and no code or Pack correction, do not create a Remediation Pack unnecessarily.

If accepted behavior or an accepted contract changes, a Change Request may still be required even when no Remediation Pack is required.

---

## 12. When to Request a Formal Review

Do not request a formal Review after every normal Pack.

Request a formal Review only at official checkpoints or when risk requires it.

Formal Reviews should be requested for:

- sensitive Pack execution
- sensitive Remediation
- end of Phase
- end of Release
- consistency check across Packs, Runs, Decisions, and Canonical files
- source/vendor/dependency update impact
- major docs/ai maintenance or restructuring
- operator-requested formal validation

A formal Review is different from a normal operator check.

Normal operator check happens after every Pack.

Formal Review creates a persistent review record.

---

## 13. Sensitive Pack Review

Request a formal Review for a Pack when it affects sensitive or high-risk behavior.

Sensitive Pack examples:

- API contracts
- database schema or migrations
- status model
- provider adapter behavior
- callback processing
- security or permissions
- queue/job/retry/dead-letter behavior
- rate limit, quota, or cost behavior
- idempotency
- cross-phase contracts
- Canonical decisions

Recommended operator prompt:

```text
Create a formal Review for this sensitive Pack.

Check:

- scope compliance
- changed files
- tests
- contract impact
- Canonical impact
- security or data impact
- unresolved decisions
- whether the Pack is accepted, rejected, or needs remediation
```

---

## 14. Sensitive Remediation Review

Do not request a formal Review after every small Remediation.

Request a formal Review after Remediation when the Remediation changes or affects:

- accepted contracts
- Canonical decisions
- API request or response
- schema or migration
- status behavior
- callback/provider behavior
- security or permissions
- queue/retry/dead-letter behavior
- cross-phase behavior
- behavior required by a Change Request

Recommended operator prompt:

```text
Create a Remediation Review.

Check whether the Remediation:

- fully satisfies the Change Request
- updates the accepted contract correctly
- updates Canonical files if required
- includes required tests
- does not introduce unrelated changes
- allows the blocked Pack to continue, normalize, regenerate, or be superseded
```

---

## 15. Consistency Review

A Consistency Review is useful when multiple Packs, Runs, Decisions, or Canonical files may have drifted.

Request a Consistency Review when:

- several Packs have been executed in a Phase
- multiple decisions were made during execution
- Remediations changed accepted behavior
- Pack instructions may no longer match Canonical files
- Run Reports may not match actual implementation
- Phase Canonical and Release Canonical may conflict
- before Phase Final Review in a complex Phase

Recommended operator prompt:

```text
Create a Consistency Review for this Phase or scope.

Check consistency between:

- Packs
- Run Reports
- Decisions
- Canonical files
- Guides
- Change Requests
- Remediations
- current source code
```

---

## 16. End of Phase

At the end of every Phase, request a Phase Final Review.

The Phase Final Review should check:

- all required Packs for the Phase
- Run Reports
- tests
- open Decisions
- open Change Requests
- open Remediations
- Phase Canonical accuracy
- whether Phase Canonical must be finalized
- whether any Phase decision must be promoted to Release Canonical
- whether the Phase is ready to close

After every Phase, review Release Canonical.

Update Release Canonical only when a decision has become release-wide.

Recommended operator prompt:

```text
Create the Phase Final Review.

Check whether:

- all required Packs are accepted
- all Run Reports are complete
- tests are complete
- open decisions are resolved or tracked
- open Change Requests are resolved or tracked
- open Remediations are resolved or tracked
- Phase Canonical reflects accepted Phase truth
- any Phase decision must be promoted to Release Canonical
- the Phase can be closed
```

---

## 17. End of Release

At the end of every Release, request a Release Final Review.

The Release Final Review should check:

- all Phase Final Reviews
- accepted Phase Canonical files
- Release Canonical accuracy
- open Decisions
- open Change Requests
- open Remediations
- Release readiness
- whether any Release decision must be promoted to Global Canonical
- whether the project can move to the next Release

After every Release, review Global Canonical.

Update Global Canonical only when a decision has become project-wide.

Recommended operator prompt:

```text
Create the Release Final Review.

Check whether:

- all Phases are accepted
- Phase Final Reviews are complete
- Release Canonical reflects accepted Release truth
- open decisions are resolved or tracked
- open Change Requests are resolved or tracked
- open Remediations are resolved or tracked
- any Release decision must be promoted to Global Canonical
- the Release can be closed
```

---

## 18. Source Update Review

Request a Source Update Review when any important source changes may affect implementation assumptions or accepted decisions.

Examples:

- framework update
- project/platform update
- dependency update
- dependency update
- provider documentation update
- source documentation update
- generated analysis update
- graph/onboarding update

Recommended operator prompt:

```text
Create a Source Update Review.

Check whether the source update affects:

- accepted implementation
- Canonical decisions
- Packs
- Guides
- tests
- source/reference validity
- Change Request needs
- Remediation needs
```

Do not automatically update Canonical files only because source documentation changed.

---

## 19. Documentation Maintenance

When AI documentation changes, the operator must verify documentation maintenance.

At minimum, check:

- owner file
- related indexes
- template/rule sync
- Canonical/Decision/Guide sync
- Run/Review/Change/Remediation sync
- Required Follow-up Updates
- release/phase neutrality for global files

Recommended operator prompt:

```text
Check documentation maintenance impact.

Confirm:

- owner file checked
- related indexes updated or listed as follow-up
- related templates checked
- related rules checked
- related Canonical/Decision/Guide files checked
- related Run/Review/Change/Remediation files checked
- no release/phase-specific content leaked into global files
```

---

## 20. Moving to the Next Pack

Move to the next Pack only when:

- the current Pack is accepted or intentionally blocked/superseded
- Run Report is complete if required
- commit is complete if the work is accepted
- unresolved follow-ups are tracked
- no unsafe partial changes remain
- current branch status is understood
- the next Pack is still valid against current Canonical files and implementation state

Recommended operator prompt:

```text
Before moving to the next Pack, confirm:

- current Pack status
- Run Report status
- commit status
- unresolved follow-ups
- open decisions
- open changes/remediations
- whether the next Pack still needs normalization
```
