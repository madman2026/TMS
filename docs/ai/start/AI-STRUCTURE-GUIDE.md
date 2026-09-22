# AI STRUCTURE GUIDE

## Purpose

This file explains the AI documentation structure for AI Agents.

It defines:

- why the shared `docs/ai` structure and owner-local documentation roots exist
- what each ownership scope and folder is responsible for
- when an AI Agent should read each folder
- when an AI Agent may create or update files
- how files are generated during Pack execution
- where operator answers, decisions, conflicts, reports, and remediations must be recorded

This file is written for AI Agents, not primarily for human readers.

Human-oriented structure explanation is available in:

```text
docs/ai/STRUCTURE-GUIDE.md
```

---

## Core Principle

The AI documentation system is designed to avoid loading all project context at the beginning of every chat.

The AI Agent must not read all documentation files by default.

Instead, the AI Agent must:

1. read the mandatory startup files
2. identify the current documentation owner
3. identify the current Release, Phase, and Pack when applicable
4. use the context map to select only the files needed for the current situation
5. read additional files only when the current work requires them
6. record execution results, decisions, and conflicts with the correct owner

---

## Documentation Ownership Model

The repository uses four ownership scopes:

| Scope | Target Entry | Owns |
|---|---|---|
| Shared AI-system governance | `docs/ai/AI-DOCS-INDEX.md` | Reusable startup, routing, rules, templates, lifecycle definitions, and structure guidance. |
| Repository / cross-component | `docs/project/PROJECT-DOCS-INDEX.md` | Repository architecture, cross-component knowledge, source analysis, third-party-component analysis, and repository lifecycle records. |
| Independently owned component | the repository owner-discovery index, then the owner-local router and manifest | Product, domain, execution, operator, and lifecycle knowledge specific to that owner. |
| Third-party component | project documentation | Project-owned analysis that must not be stored in replaceable third-party source. |

Ownership is determined from content, scope, audience, source of truth, consumers, and dependencies. Filename, prefix, current folder, Release/Phase label, and technology name are supporting evidence only.

Mixed-scope documents require an approved split or explicit ownership decision. Do not copy a shared rule into a plugin merely because the rule is used there.

### Profile Overlay

After selecting an owner, load its declared profiles after shared governance. A profile supplies stable paths, technologies, protected areas, domain bindings, and tighter owner constraints. It does not copy the shared lifecycle, disable shared safety gates, override Canonical authority, or expand Pack scope. Conflicts must use the Decision/Change/Remediation workflow.

Profiles are resolved from the selected project entry or owner manifest. Reusable governance must not hard-code a concrete project or component profile.

### Migration Status

Follow each manifest's declared current entry, profile, indexes, and lifecycle status. Do not inherit migration or acceptance state from another repository.

Current authority:

- independently governed owners use their owner-local Canonical, Guide, lifecycle, reference, testing, operator-guide, discovery, and execution-model paths;
- project references and repository lifecycle records use their active project-local paths;
- uncreated target paths are never authoritative merely because a guide describes them;
- no prior-branch Run or Review is execution evidence for the current branch.

---

## Mandatory Startup Files

For every new Codex chat, the AI Agent must start with:

```text
AGENTS.md
docs/ai/start/START-HERE.md
docs/ai/start/CONTEXT-MAP.md
```

The AI Agent must not read every file under `docs/ai` during startup.

After reading the mandatory startup files, the AI Agent must follow `CONTEXT-MAP.md` to decide what else is needed.

---

## Source of Truth Model

The structure separates different kinds of truth.

### 1. Current Implementation Truth

The current source code shows what is actually implemented.

Use source code to understand the current implementation state.

### 2. Intended Architecture and Delivery Truth

Canonical files define official decisions, constraints, and delivery rules.

Use canonical files to understand what the system is supposed to follow.

### 3. Execution Truth

Run reports show what happened during actual Pack execution.

Use run reports to understand completed work, deviations, tests, failures, and operator review results.

### 4. Decision History Truth

Decision logs show why a decision was made, where it came from, and whether it is temporary, accepted, rejected, or needs future review.

Use decision logs to preserve operator answers and design reasoning that should not be lost in chat history.

### 5. Change and Remediation Truth

Change requests and remediation Packs define controlled corrections after a conflict, mistake, or new decision.

Use these files when an existing Pack, implementation, or canonical decision needs controlled adjustment.

---

## Document Scope Rule

AI documentation files must keep the correct scope.

Shared governance files must stay owner-neutral, release-neutral, and phase-neutral.

Global files include:

- `AGENTS.md`
- `docs/ai/start/*`
- `docs/ai/rules/*`
- `docs/ai/templates/*`
- `docs/ai/AI-DOCS-INDEX.md`
- `docs/ai/STRUCTURE-GUIDE.md`

These files must not contain fixed project, component, technology, Release-specific, or Phase-specific execution state unless a temporary migration note explicitly identifies the current and target owner.

During the transition, the existing owner-specific corpus may remain under `docs/ai/` only until its approved migration Pack executes. Its present location is not evidence that its content is reusable governance.

Release-specific content must live under release-specific folders, such as:

- `<owner-docs-root>/canonical/release-{N}/`
- `<owner-docs-root>/guides/release-{N}/`
- `<owner-docs-root>/packs/release-{N}/`
- `<owner-docs-root>/runs/release-{N}/`
- `<owner-docs-root>/reviews/release-{N}/`
- `<owner-docs-root>/decisions/release-{N}/`
- `<owner-docs-root>/changes/release-{N}/`
- `<owner-docs-root>/remediations/release-{N}/`

Phase-specific content must live under phase-specific folders, such as:

- `<owner-docs-root>/guides/release-{N}/phases/`
- `<owner-docs-root>/canonical/release-{N}/phases/`
- `<owner-docs-root>/packs/release-{N}/phase-{X}/`
- `<owner-docs-root>/runs/release-{N}/phase-{X}/`
- `<owner-docs-root>/reviews/release-{N}/phase-{X}/`
- `<owner-docs-root>/decisions/release-{N}/phase-{X}/`

A global file may reference release/phase files, but it must not duplicate their content.

If Release-specific or Phase-specific content is found in a global file, move it to the correct release/phase file and keep only a reference in the global file.

After migration, Release/Phase lifecycle paths are resolved below the selected project or plugin documentation root. The complete maintenance rule is defined in:

- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`

---

## Directory Responsibilities

## `docs/project/`

Target root for repository-wide knowledge, cross-component documentation, third-party-component analysis, and repository lifecycle record instances.

Follow the project index and do not assume undescribed subdirectories exist.

## Owner discovery

The repository's owner-discovery index routes to independently governed documentation and must not duplicate owner content.

## `<owned-component-root>/docs/ai/`

Target root for documentation whose content is owned by a first-party component. Each independently governed owner may declare a local router, entry point, and manifest.

Creating an entry point does not migrate the corpus. The manifest must distinguish current authoritative paths from target paths until migration is accepted.

## `docs/ai/start/`

Startup and routing files.

This folder tells the AI Agent how to begin a session and how to decide what to read next.

Typical files:

```text
START-HERE.md
CONTEXT-MAP.md
AI-STRUCTURE-GUIDE.md
```

### When to read

Always read `START-HERE.md` and `CONTEXT-MAP.md` at the beginning of a new Codex chat.

Read `AI-STRUCTURE-GUIDE.md` only when the AI Agent needs to understand documentation structure, file placement rules, document scope rules, or documentation update rules.

`AI-STRUCTURE-GUIDE.md` is not mandatory startup context.

### When to update

Update this folder when the AI documentation structure changes, when startup rules change, when routing behavior changes, or when documentation structure rules change.

---

## `docs/ai/rules/`

Global operational rules.

This folder contains rules that apply across releases, phases, and Packs.

Typical files:

```text
AI-EXECUTION-RULES.md
AI-PACK-GENERATION-RULES.md
OPERATOR-WORKFLOW.md
SCOPE-CONTROL-RULES.md
GIT-AND-COMMIT-RULES.md
TEST-AND-VALIDATION-RULES.md
COMMENTING-RULES.md
QUESTION-ANSWER-DECISION-RULES.md
CONFLICT-CHANGE-REMEDIATION-RULES.md
REPORTING-RULES.md
DOCUMENT-MAINTENANCE-RULES.md
```

### When to read

Read only the rule file that is relevant to the current situation.

Examples:

- executing a Pack: read `AI-EXECUTION-RULES.md`
- checking scope: read `SCOPE-CONTROL-RULES.md`
- handling operator answers: read `QUESTION-ANSWER-DECISION-RULES.md`
- deciding comment style: read `COMMENTING-RULES.md`
- writing the final response: read `REPORTING-RULES.md`
- maintaining AI documentation: read `DOCUMENT-MAINTENANCE-RULES.md`
- generating or updating an AI Pack: read `AI-PACK-GENERATION-RULES.md`

### When to update

Update rule files only when the general process changes.

Do not update rule files for a single Pack-specific decision.

---

## `<owner-docs-root>/canonical/`

Official architectural and delivery decisions.

Canonical files define accepted project-level, release-level, or phase-level decisions.

Typical structure:

```text
canonical/
  CANONICAL-INDEX.md
  GLOBAL-CANONICAL-DECISIONS.md
  release-{N}/
    RELEASE-{N}-CANONICAL-DECISIONS.md
    phases/
      PHASE-{X}-CANONICAL-DECISIONS.md
```

### When to read

Read canonical files for the current release and phase before executing a Pack.

Do not read canonical files for unrelated releases or phases unless conflict analysis requires it.

### When to update

Canonical files may be updated only when an operator answer or review outcome becomes an official decision.

The AI Agent must not silently update canonical files from casual clarification.

Canonical updates require one of these conditions:

1. the operator explicitly says the answer is a decision
2. the current Pack includes canonical update in scope
3. the AI Agent asks for confirmation and the operator approves

---

## `<owner-docs-root>/guides/`

Release and phase execution summaries.

Guides explain what a release or phase is trying to achieve and how it should be approached.

Typical structure:

```text
guides/
  GUIDES-INDEX.md
  release-{N}/
    RELEASE-{N}-SUMMARY.md
    phases/
      PHASE-{X}-SUMMARY.md
```

### When to read

Read the guide for the current release and phase during startup or before executing the first Pack of that phase.

Do not read all release and phase guides by default.

### When to update

Update guides when release or phase execution strategy changes.

Do not put Pack-level implementation details here unless they affect the whole release or phase.

---

## `docs/project/references/`

Heavy or optional repository background context owned by the project.

References contain product context, source analysis, graph files, onboarding files, and source documentation.

Typical files and folders:

```text
REFERENCES-INDEX.md
PRODUCT-AND-DELIVERY-CONTEXT.md
PROJECT-ANALYSIS-SUMMARY.md
PROJECT-ANALYSIS-FULL.md
SOURCE-DOCS-INDEX.md
GRAPH-FILES-INDEX.md
graphs/
source-docs/
onboarding/
```

### When to read

Read `PRODUCT-AND-DELIVERY-CONTEXT.md` when starting a new release or phase, or when product-level context is unclear.

Read `PROJECT-ANALYSIS-SUMMARY.md` when the AI Agent needs general source understanding.

Read full analysis, graph JSON files, or source company docs only when the current Pack or conflict requires deeper understanding.

### When not to read

Do not read graph JSON files or full project analysis during normal startup.

Do not read all reference files by default.

### When to update

Update references when new product context files, analysis files, graph outputs, onboarding files, or source documentation files are added.

---

## `<owner-docs-root>/packs/`

Executable AI Packs.

An AI Pack is the main unit of work delivered to the AI Agent.

A Pack may contain one or more internal tasks, but there is no separate `tasks/` folder.

Typical structure:

```text
packs/
  PACKS-INDEX.md
  release-{N}/
    phase-{X}/
```

### Pack vs Task

```text
AI Pack = controlled executable work package
Task = internal work item inside a Pack
```

The Pack is the unit that includes:

- goal
- context
- allowed files
- forbidden files
- implementation rules
- architecture constraints
- validation rules
- security rules
- data and migration rules
- commenting requirements
- testing requirements
- operator checklist
- final report requirements
- stop conditions

### When to read

Read only the current Pack being executed.

Do not read all Packs in a release or phase unless performing review or planning.

### When to update

Do not modify original Packs after execution unless explicitly requested.

If a Pack needs correction after execution, create a Change Request or Remediation Pack instead of rewriting history.

---

## `<owner-docs-root>/runs/`

Execution reports.

Run reports describe what actually happened when a Pack was executed.

Typical structure:

```text
runs/
  RUNS-INDEX.md
  release-{N}/
    phase-{X}/
```

### When to read

Read run reports when:

- reviewing completed work
- checking previous Pack execution
- investigating conflicts
- resuming a phase after previous work
- verifying whether a Pack was accepted, rejected, superseded, or remediated

Do not read all run reports during normal startup.

### When to create

Create a run report after a Pack execution if the workflow requires persistent execution records.

### When to update

Update the run index when a new run report is added or when a run status changes.

---

## `<owner-docs-root>/reviews/`

Human, phase, release, and final reviews.

Reviews summarize validation results and human assessment.

Typical structure:

```text
reviews/
  REVIEWS-INDEX.md
  release-{N}/
    phase-{X}/
```

### When to read

Read reviews when performing phase review, release review, final review, or when a previous review affects the current Pack.

### When to create

Create a review when an operator or review Pack completes a formal review step.

### When to update

Update reviews only when a formal review result changes or new review findings are added.

---

## `<owner-docs-root>/decisions/`

Decision history and operator-answer records.

This folder prevents important decisions from being lost inside chat history.

Typical structure:

```text
decisions/
  DECISIONS-INDEX.md
  release-{N}/
    RELEASE-{N}-DECISION-LOG.md
    phase-{X}/
      PHASE-{X}-DECISION-LOG.md
```

### When to read

Read decision logs when:

- operator answers affect architecture, scope, phase behavior, or future releases
- a decision is temporary and must be revisited later
- current implementation conflicts with remembered or documented decisions
- a Pack depends on a decision made during a previous Pack

### When to create or update

Create or update decision logs when an operator answer is classified as:

- Pack-local decision with future impact
- Phase decision
- Release decision
- Temporary decision
- Revisit-later decision
- Decision that affects future Packs
- Decision that may need canonical update

Clarifications that only affect the current execution may be recorded only in the run report and final report.

---

## `<owner-docs-root>/changes/`

Change Requests.

Change Requests document requested changes after a Pack, decision, or implementation needs correction.

Typical structure:

```text
changes/
  CHANGES-INDEX.md
  CHANGE-REQUEST-TEMPLATE.md
  release-{N}/
    phase-{X}/
```

### When to read

Read Change Requests when a current task is related to an existing change request or when conflict resolution requires formal change tracking.

### When to create

Create a Change Request when:

- an accepted decision must be changed
- completed implementation must be corrected beyond a simple test-fix
- a Pack outcome conflicts with canonical decisions
- operator requests a scope or behavior change after execution
- a decision must be formally reviewed before remediation

### When not to create

Do not create a Change Request for small clarifications or normal Pack execution notes.

---

## `<owner-docs-root>/remediations/`

Remediation Packs.

Remediation Packs are controlled correction Packs created after a Change Request or review finding.

Typical structure:

```text
remediations/
  REMEDIATIONS-INDEX.md
  REMEDIATION-PACK-TEMPLATE.md
  release-{N}/
    phase-{X}/
```

### When to read

Read Remediation Packs when executing a correction after an approved change, conflict, failed review, or invalid implementation.

### When to create

Create a Remediation Pack when a correction must be implemented after a formal Change Request or review finding.

### Important rule

Do not rewrite the original Pack when a remediation is required.

Keep the original Pack intact and create a new Remediation Pack.

---

## `docs/ai/templates/`

Reusable templates.

Templates define standard formats for new files.

Typical files:

```text
AI-PACK-TEMPLATE.md
RUN-REPORT-TEMPLATE.md
REVIEW-TEMPLATE.md
DECISION-RECORD-TEMPLATE.md
CHANGE-REQUEST-TEMPLATE.md
REMEDIATION-PACK-TEMPLATE.md
AGENT-FINAL-REPORT-TEMPLATE.md
```

### When to read

Read a template when creating a new file of that type.

### When to update

Update templates when the standard file format changes.

Do not update templates for one-off Pack-specific cases.

---

## How Files Are Generated

## During Pack Creation

When a new AI Pack is created:

1. read `docs/ai/rules/AI-PACK-GENERATION-RULES.md`
2. use `docs/ai/templates/AI-PACK-TEMPLATE.md`
3. place the Pack under the correct release and phase
4. update `<owner-docs-root>/packs/PACKS-INDEX.md`
5. ensure the Pack references relevant canonical and guide files
6. include only architecture constraints that are directly relevant to the Pack scope
7. do not duplicate global rules inside the Pack unless Pack-specific constraints are required

---

## During Documentation Maintenance

When the AI Agent creates, edits, moves, archives, supersedes, rejects, accepts, closes, or materially changes AI documentation files, it must follow:

- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`

This includes:

- content ownership checks
- document scope checks
- index maintenance
- template synchronization
- canonical / decision / guide synchronization
- pack / run / review synchronization
- change / remediation synchronization
- reference and source analysis validity checks
- source update review

Do not duplicate the complete maintenance logic in this file. This file explains the structure; `DOCUMENT-MAINTENANCE-RULES.md` defines the maintenance system.

---

## During Pack Execution

When executing a Pack:

1. read mandatory startup files
2. read the current release and phase canonical files
3. read the current release and phase guide files
4. read the current Pack only
5. execute only within the Pack scope
6. do not modify files listed under `Do Not Change`
7. follow commenting, testing, scope, and final report rules
8. produce an Agent Final Report
9. operator reviews diff and tests
10. if persistent reporting is required, create or update the run report

---

## During Operator Questions and Answers

When the AI Agent asks a question and receives an operator answer:

1. classify the answer
2. decide where it must be recorded
3. do not update canonical files unless rules allow it
4. record the answer in the correct place

Possible classifications:

```text
Clarification
Implementation Note
Pack-Local Decision
Phase Decision
Release Decision
Temporary Decision
Revisit-Later Decision
Change Request Trigger
Remediation Trigger
```

Recording locations:

```text
Clarification
→ Agent Final Report or Run Report

Implementation Note
→ Run Report

Pack-Local Decision
→ Run Report, and Decision Log if it affects future Packs

Phase Decision
→ Phase Decision Log, and Phase Canonical if approved

Release Decision
→ Release Decision Log, and Release Canonical if approved

Temporary Decision
→ Decision Log with Review At field

Change Request Trigger
→ Change Request

Remediation Trigger
→ Remediation Pack after approved Change Request or review finding
```

---

## During Conflict Detection

When the AI Agent detects a conflict:

1. stop implementation if the conflict affects scope, architecture, data, security, or tests
2. report the conflict clearly
3. identify the conflicting sources
4. check `docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md`
5. do not silently choose between source code and canonical decisions
6. ask the operator whether to create a Change Request, update canonical, or create a Remediation Pack

---

## During Review

When reviewing a phase or release:

1. read relevant canonical files
2. read relevant guide files
3. read accepted run reports for the reviewed scope
4. read relevant decision logs
5. read relevant Change Requests and Remediations
6. create or update the review file
7. update the review index

---

## Read Policy

The AI Agent must follow this read policy:

```text
Always read:
- AGENTS.md
- docs/ai/start/START-HERE.md
- docs/ai/start/CONTEXT-MAP.md

Read only when relevant:
- rules/*
- canonical/current-release/*
- canonical/current-release/phases/current-phase/*
- guides/current-release/*
- guides/current-release/phases/current-phase/*
- current Pack
- relevant decision logs
- relevant run reports
- relevant review files

Do not read by default:
- all Packs
- all Runs
- all Reviews
- all Canonical files
- all Guides
- graph JSON files
- PROJECT-ANALYSIS-FULL.md
- archive/*
```

---

## Write Policy

The AI Agent must follow this write policy:

```text
May write:
- files explicitly allowed by the current Pack
- run reports when required
- decision logs when the operator answer classification requires it
- change requests when approved or requested
- remediation Packs when approved or requested
- index files when new files are created

Must not write:
- files listed in Do Not Change
- unrelated source code files
- unrelated documentation files
- canonical files without approval or Pack scope
- archive files unless performing migration
- templates unless template update is explicitly requested
```

---

## Naming Policy

Use the existing naming conventions for AI Packs, runs, reviews, decisions, changes, and remediations.

There is no separate `tasks/` folder.

Tasks belong inside AI Packs.

Recommended concept model:

```text
Release
  Phase
    AI Pack
      Task 1
      Task 2
      Task 3
```

---

## Final Rule

The AI Agent must treat this structure as an index-driven documentation system.

The goal is not to read everything.

The goal is to read the right file at the right time, execute within scope, and record important outcomes in the correct place.
