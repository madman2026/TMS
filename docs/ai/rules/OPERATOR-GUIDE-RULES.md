# OPERATOR GUIDE RULES

This reusable rule defines how a human-facing operator guide is created and maintained. Owner-specific paths, language, permissions, UI names, and screenshot locations are supplied by the applicable project or plugin profile; they are not hard-coded here.

## Required content

For every operator-facing capability, the guide explains what changed, where it is found, who may use it, prerequisites, the safe workflow, validation/testing, monitoring, common errors, recovery, and required permissions. Use workflow-first structure:

```text
Before using this feature:
- ...

Steps:
1. ...

After using this feature:
- ...

Common mistakes:
- ...

Required permission:
- ...
```

## Lifecycle

Create a guide when the first operator-facing capability is delivered. Update it after an accepted Pack, Remediation, Review outcome, or Decision changes operator-visible behavior, workflow, permissions, messages, monitoring, or recovery. If the guide is required but outside Pack scope, record a Required Follow-up Update.

## Safety and language

Use the owner profile's language and terminology rules. Do not invent UI or permissions. Mark unknown permissions as `To be confirmed.` Never present conceptual images as real screenshots; real screenshots must come from the implemented UI and be stored at the owner-declared asset path.

## Reporting and duplication

Run Reports and Agent Reports state only whether guide impact existed, whether an update was required, what was updated, and any follow-up. They do not duplicate guide content. Human review is required for unsafe instructions, stale screenshots, unclear permissions, or conflict with the implemented UI.

## Ownership

This file is reusable Core governance. The actual guide, template, assets, and owner-specific bindings remain with the project or plugin that owns the operator workflow.
