# CANONICAL-RULES

This file defines rules for creating, updating, validating, finalizing, promoting, and using Canonical files in `docs/ai`.

This file owns Canonical behavior.

Canonical templates own Canonical file structure, headings, placeholders, and section-level output format.

---

## 1. Purpose

Canonical files store the current official decisions and contracts that AI Agents must follow.

Canonical files answer:

```text
What is the accepted current truth?
What must Packs, Remediations, Reviews, and future work follow?
```

Canonical files must not become:

- chat history
- full decision history
- implementation logs
- run reports
- review reports
- Change Requests
- Remediation Packs
- general explanation files

History and reasoning belong in Decision Logs, Change Requests, Run Reports, and Reviews.

Canonical files must show the current accepted state.

---

## 2. Canonical Levels

### 2.1 Global Canonical

Global Canonical files define decisions that apply across the whole project.

Use for:

- project-wide architecture principles
- project-wide boundaries
- global naming or ownership rules
- cross-release rules
- global stop conditions
- project-wide non-goals
- project-wide execution constraints

Example path:

```text
<owner-docs-root>/canonical/GLOBAL-CANONICAL-DECISIONS.md
```

---

### 2.2 Release Canonical

Release Canonical files define decisions that apply to more than one Phase inside the same Release.

Use for:

- Release scope
- Release out of scope
- core Release decisions
- shared Release contracts
- shared API/status/config/provider contracts
- Release-level stop conditions
- Release-level promotion or conflict notes

Example path:

```text
<owner-docs-root>/canonical/release-{N}/RELEASE-{N}-CANONICAL-DECISIONS.md
```

A Release Canonical is effectively the shared cross-Phase canonical source for that Release.

Do not create a separate `Cross-Phase Contracts` source of truth unless the Release Canonical becomes too large and the operator approves splitting it.

---

### 2.3 Phase Canonical

Phase Canonical files define decisions that apply only to one Phase.

Use for:

- Phase scope
- Phase out of scope
- Phase-specific technical decisions
- Phase-specific contracts
- Phase-specific stop conditions
- Phase-specific Pack normalization requirements
- Phase-specific implementation boundaries

Example path:

```text
<owner-docs-root>/canonical/release-{N}/phases/PHASE-{X}-CANONICAL-DECISIONS.md
```

A Phase Canonical must not override Global or Release Canonical decisions.

---

## 3. Canonical Scope Rule

Use the narrowest canonical level that correctly covers the decision.

```text
Project-wide decision
→ Global Canonical

Decision affecting more than one Phase inside a Release
→ Release Canonical

Decision affecting only one Phase
→ Phase Canonical

Decision affecting only one Pack and no future work
→ Run Report / Pack notes, not Canonical
```

If a decision applies only to one Phase, do not record it in Release Canonical.

If a Phase-level decision later affects multiple Phases in the same Release, promote it to Release Canonical after approval.

If a Release-level decision later becomes project-wide, promote it to Global Canonical after approval.

---

## 4. Canonical Creation Rule

Canonical files must be created before generating AI Packs for a Release or Phase.

Before generating Packs for a Release:

1. create or update the Release Guide
2. create the initial Release Canonical
3. record Release-wide decisions and shared contracts
4. avoid Phase-specific implementation details

Before generating Packs for a Phase:

1. create or update the Phase Guide
2. create the initial Phase Canonical
3. record Phase-specific decisions and boundaries
4. confirm that the Phase Canonical does not contradict Global or Release Canonical files

Do not wait until after Pack execution to create the first canonical file for a Phase unless the Phase was already executed before the canonical system existed.

If a Phase was already executed, create an as-built Phase Canonical by reviewing:

- accepted Run Reports
- accepted Reviews
- current code
- Decision Logs
- existing Packs
- accepted Remediations
- relevant canonical files

---

## 5. Pack Generation and Normalization Rule

AI Packs must be generated from canonical decisions, not from chat memory alone.

Before creating or finalizing Packs for a Phase, normalize the Packs against:

- Global Canonical
- current Release Canonical
- current Phase Canonical
- current AI Pack Template
- relevant rules
- relevant guides
- relevant source/reference docs if required

Pack normalization must check:

- naming consistency
- route and permission consistency
- status and state transition consistency
- data model consistency
- API contract consistency
- provider/interface consistency
- queue/job/config consistency
- testing requirements
- commenting requirements
- scope boundaries
- stop conditions
- source documentation references
- final report and run report requirements

If a Pack contradicts a canonical decision, do not execute it until the contradiction is resolved.

---

## 6. Canonical Read Rule

Before executing a Pack, read only the canonical files relevant to the current work.

For a normal Release/Phase Pack, read:

- Global Canonical, if required by context map
- current Release Canonical
- current Phase Canonical

Do not read canonical files for unrelated Releases or Phases unless:

- conflict analysis requires it
- a Change Request references them
- a Remediation Pack affects them
- a Review requires cross-Release or cross-Phase validation
- the operator asks for it

---

## 7. Canonical Update Rule

Canonical files must be updated only when official accepted behavior changes.

Canonical updates may happen after:

- approved operator decision
- accepted Decision Log entry
- accepted Review finding
- approved Change Request
- successful Remediation Pack
- accepted Phase or Release final review
- Pack scope explicitly includes canonical updates

Canonical files must not be updated from casual clarification.

Canonical files must not be silently updated by the AI Agent.

If the AI Agent is unsure whether a canonical update is allowed, it must ask the operator.

---

## 8. Decision Log First Rule

Any non-trivial decision that may affect Canonical files must first be recorded in the relevant Decision Log unless an approved formal Review already acts as the decision source.

Canonical updates, Change Requests, and Remediation Packs may be created from an approved Decision Log entry, but they must not replace the Decision Log.

A Canonical update should reference the related Decision Log entry, Review, Change Request, or Remediation Pack when applicable.

Decision Logs own:

```text
why the decision was made
who approved it
when it was approved
what alternatives or constraints existed
whether it is temporary or revisit-later
```

Canonical files own:

```text
the current accepted decision or contract
```

---

## 9. Canonical Promotion Rule

A decision starts at the level where it is discovered.

Promote only when its scope becomes broader.

```text
Pack-local decision with future impact
→ relevant Decision Log
→ Phase Canonical if Phase-wide

Phase-level decision affecting multiple Phases in the same Release
→ Release Decision Log
→ Release Canonical after approval

Release-level decision affecting multiple Releases or the whole project
→ Global Decision Log
→ Global Canonical after approval
```

Do not promote decisions only to make them more visible.

Promote only when scope requires it.

---

## 10. Canonical Hierarchy Validation Rule

Canonical files must be validated against their parent authority level.

A Phase Canonical must not contradict the related Release Canonical.

A Release Canonical must not contradict Global Canonical decisions.

A Phase Canonical must not override Release or Global Canonical decisions.

A Release Canonical must not override Global Canonical decisions.

If a lower-level Canonical file appears to contradict a higher-level Canonical file, the Agent must not resolve the conflict inside this file.

The Agent must stop and follow:

```text
docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md
```

This file defines Canonical validation boundaries.

It does not own the full conflict/change/remediation workflow.

---

## 11. Canonical Conflict Boundary Rule

When creating, updating, validating, finalizing, or promoting Canonical files, the Agent must check whether the Canonical content conflicts with:

* higher-level Canonical files
* accepted implementation state
* approved Guides
* current or pending AI Packs
* accepted Reviews
* accepted Change Requests
* accepted Remediation Packs
* operator decisions that have been recorded as official decisions

If a conflict is found, the Agent must not silently choose one side.

The Agent must follow:

```text
docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md
```

Canonical files should record only the accepted final decision or contract after the conflict is resolved.

---

## 12. Earlier Contract Protection Rule

A later Phase Canonical must not silently rewrite an earlier accepted Phase, Pack, Run, Review, Change Request, Remediation, or Canonical contract.

If a later Canonical update requires changing an earlier accepted contract, the Agent must stop and follow:

```text
docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md
```

The Canonical file may be updated only after the required decision, Change Request, Remediation, Review, or operator approval exists.

---

## 13. Remediation and Canonical Update Rule

If a Remediation Pack changes an official decision or contract, update the relevant Canonical file only after the remediation is successfully implemented and accepted.

Do not update Canonical files as final truth before successful remediation unless the Canonical entry is explicitly marked as:

```text
proposed
pending-implementation
```

Preferred rule:

```text
Canonical reflects accepted current truth.
```

When updating Canonical after Remediation:

1. update the relevant existing section or contract in place
2. do not only append a historical note at the end
3. add a short canonical note near the changed contract if traceability is useful
4. reference the related Decision Log, Change Request, Review, or Remediation Pack
5. keep detailed history in Decision Logs, Change Requests, Reviews, Run Reports, or Remediation files

---

## 14. Canonical Edit Style Rule

Canonical files must be easy for AI Agents to consume.

Prefer:

- clear headings
- explicit decisions
- stable paths
- short contract blocks
- concrete allowed/disallowed behavior
- explicit stop conditions
- traceability notes only when helpful

Avoid:

- long narrative history
- repeated template content
- chat transcripts
- unapproved proposals mixed with accepted rules
- Pack-level implementation steps
- outdated decisions without status
- duplicate contracts in multiple sections

If a decision is superseded, either update the original decision in place or mark it clearly as superseded and point to the replacement decision.

---

## 15. Canonical Change References Rule

Canonical files should include short change references when a non-trivial Canonical update has a clear source, such as a Decision Log, Review, Change Request, Remediation Pack, or accepted Run Report.

For initial Canonical creation, wording-only cleanup, or changes with no meaningful traceability value, this section may remain empty.

Example:

```md
## Canonical Change References

| Source | Summary |
|---|---|
| `<decision-id>` / `<change-id>` / `<remediation-pack-id>` | Added an optional external reference to an API response for inbound-event traceability. |
```

This section is optional.

It must not replace updating the real contract in the correct section.

The complete reasoning must remain in Decision Logs, Change Requests, Reviews, Run Reports, or Remediation Packs.

---

## 16. Canonical Status Rule

Canonical files should represent accepted decisions by default.

If a Canonical file contains non-final items, mark them explicitly.

Recommended statuses:

```text
accepted
proposed
temporary
revisit-later
superseded
deprecated
pending-implementation
```

Do not mix proposed decisions with accepted decisions unless status is explicit.

Temporary or revisit-later decisions must include:

```text
Status:
Review At:
Reason:
```

Only `accepted` Canonical entries are binding by default.

Entries marked as `proposed`, `pending-implementation`, `temporary`, or `revisit-later` must not be treated as final accepted behavior unless the current Pack or operator explicitly allows using them.

Entries marked as `superseded` or `deprecated` must not be used as current truth.

---

## 17. Canonical Finalization Rule

At the end of every Phase:

1. review accepted Runs
2. review accepted Reviews
3. review Decision Logs
4. review Change Requests
5. review Remediation Packs
6. update Phase Canonical if accepted Phase behavior changed
7. review whether any Phase decision must be promoted to Release Canonical
8. ensure Phase Canonical does not contradict Release Canonical
9. update `CANONICAL-INDEX.md` if needed

At the end of every Release:

1. review all Phase final reviews
2. review Release Decision Log
3. review accepted Phase Canonical files
4. update Release Canonical if accepted Release-wide behavior changed
5. review whether any Release decision must be promoted to Global Canonical
6. ensure Release Canonical does not contradict Global Canonical
7. update `CANONICAL-INDEX.md` if needed

---

## 18. Canonical and Reviews Rule

Reviews may recommend Canonical updates.

A Canonical file may be updated from a Review only when:

- the Review finding is accepted
- the operator approves the canonical change, or the Review has explicit canonical update scope
- the relevant Decision Log entry exists, unless the approved formal Review already acts as the decision source
- the update is made in the correct Global, Release, or Phase Canonical file

Relevant rule:

```text
docs/ai/rules/REVIEW-RULES.md
```

---

## 19. Canonical and Source Updates Rule

Source updates must not automatically change Canonical files.

When framework, project, dependency, source documentation, graph, onboarding, or project-analysis updates challenge accepted canonical behavior:

1. create or update a Source Update Review
2. record a Decision Log entry if an accepted behavior may change
3. create a Change Request if accepted scope or contract must change
4. create a Remediation Pack if previous execution is invalidated
5. update Canonical files only after approval and implementation when required

Relevant rule:

```text
docs/ai/rules/REVIEW-RULES.md
```

---

## 20. Canonical Index Rule

Update `<owner-docs-root>/canonical/CANONICAL-INDEX.md` when:

- a Canonical file is created
- a Canonical file is moved
- a Canonical file is archived
- a Canonical file is superseded
- a Canonical file's purpose or scope changes
- a Canonical file's status changes
- a Canonical file's read policy changes
- a new Release or Phase Canonical is added
- a Canonical file is materially changed and the index tracks meaning/status/scope

Do not update `CANONICAL-INDEX.md` for typo-only or wording-only changes when path, scope, status, purpose, read policy, and meaning did not change.

---

## 21. Related Rules and Templates

Read these when applicable:

| Situation | File |
|---|---|
| Release Canonical template | `docs/ai/templates/RELEASE-CANONICAL-TEMPLATE.md` |
| Phase Canonical template | `docs/ai/templates/PHASE-CANONICAL-TEMPLATE.md` |
| Documentation maintenance and index updates | `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md` |
| Operator decision classification | `docs/ai/rules/QUESTION-ANSWER-DECISION-RULES.md` |
| Conflict, Change Request, and Remediation workflow | `docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md` |
| Review-triggered canonical updates | `docs/ai/rules/REVIEW-RULES.md` |
| Pack scope control | `docs/ai/rules/SCOPE-CONTROL-RULES.md` |
| Reporting behavior | `docs/ai/rules/REPORTING-RULES.md` |

### Canonical Template Ownership Boundary

Canonical templates own:

required headings
placeholder structure
section order
table formats
empty-state format

This rule file owns:

when Canonical files are created
what level they belong to
when they may be updated
how they are validated
how they are finalized
how they relate to decisions, reviews, changes, and remediations

Do not manually invent a new Canonical file structure unless the operator approves updating the related Canonical template first
